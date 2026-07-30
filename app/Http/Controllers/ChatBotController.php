<?php

namespace App\Http\Controllers;

use App\Ai\Agents\OrderSupportAgent;
use Illuminate\Http\Request;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolResult;

class ChatBotController extends Controller
{
    /**
     * Handle the chat request from the frontend.
     * This method receives a message from the user, sends it to the AI agent,
     * and streams the response back as Server-Sent Events (SSE).
     */
    public function chat(Request $request)
    {
        // Step 1: Validate the incoming data from the user
        $validatedData = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        // Step 2: Create a new AI agent that will handle the conversation
        $agent = new OrderSupportAgent;

        // Step 3: If the user is logged in, let the agent know who they are
        if ($request->user()) {
            $agent->forUser($request->user());
        }

        // Step 4: Try to get a response from the AI agent
        try {
            // If there is a conversation ID, continue the previous conversation
            $conversationId = $validatedData['conversation_id'] ?? null;

            if ($conversationId) {
                $agent->continue($conversationId, $request->user());
            }

            // Check if the user uploaded an image
            $attachments = [];

            if ($request->hasFile('image')) {
                $attachments[] = $request->file('image');
            }

            // Send the message to the AI and get a streaming response
            $streamResponse = $agent->stream($validatedData['message'], $attachments);
        } catch (RateLimitedException $e) {
            // The AI is being used too much right now
            return response()->json([
                'error' => 'The AI assistant is temporarily rate-limited. Please wait a moment and try again.',
            ], 429);
        } catch (\Exception $e) {
            // Something else went wrong
            return response()->json([
                'error' => 'Failed to start AI stream.',
            ], 500);
        }

        // Step 5: Return the response as a stream of events (SSE)
        return response()->stream(function () use ($streamResponse) {
            try {
                // Loop through each event from the AI
                foreach ($streamResponse as $event) {
                    // If the event contains new text, send it to the browser
                    if ($event instanceof TextDelta) {
                        $eventData = json_encode([
                            'type' => 'text-delta',
                            'content' => $event->delta,
                        ]);

                        yield 'data: '.$eventData."\n\n";
                    }

                    // If the event contains product information, send it to the browser
                    if ($event instanceof ToolResult && $event->successful) {
                        $products = $this->extractProducts($event);

                        if ($products !== null) {
                            $eventData = json_encode([
                                'type' => 'products',
                                'products' => $products,
                            ]);

                            yield 'data: '.$eventData."\n\n";
                        }
                    }
                }

                // Send the conversation ID so the frontend can save it
                if ($streamResponse->conversationId) {
                    $eventData = json_encode([
                        'type' => 'conversation',
                        'conversation_id' => $streamResponse->conversationId,
                    ]);

                    yield 'data: '.$eventData."\n\n";
                }
            } catch (\Exception $e) {
                // Choose the right error message
                if ($e instanceof RateLimitedException) {
                    $errorMessage = 'The AI assistant is temporarily rate-limited. Please wait a moment and try again.';
                } else {
                    $errorMessage = 'An error occurred during streaming.';
                }

                $eventData = json_encode([
                    'type' => 'error',
                    'content' => $errorMessage,
                ]);

                yield 'data: '.$eventData."\n\n";
            }

            // Tell the browser the stream is finished
            yield "data: [DONE]\n\n";
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Extract product data from a tool result event.
     * The AI agent uses tools to look up products, and this method
     * pulls the product information out of the tool's response.
     */
    private function extractProducts(ToolResult $event): ?array
    {
        // Get the tool result object from the event
        $toolResult = $event->toolResult;

        // If there is no result, or the result is not text, stop here
        if ($toolResult === null) {
            return null;
        }

        if (! is_string($toolResult->result)) {
            return null;
        }

        // Try to decode the JSON result into an array
        $decodedData = json_decode($toolResult->result, true);

        // If the JSON is invalid, stop here
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Only extract products from certain tool types
        $toolName = $toolResult->name;
        $validToolNames = ['GetCategoryProducts', 'GetProductInfo'];

        if (in_array($toolName, $validToolNames) && isset($decodedData['products'])) {
            return $decodedData['products'];
        }

        // No products found
        return null;
    }
}
