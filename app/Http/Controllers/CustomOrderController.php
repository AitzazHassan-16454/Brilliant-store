<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomOrderController extends Controller
{
    /**
     * Admin: Show a list of all custom order requests.
     */
    public function index()
    {
        // Only allow users with permission to view orders
        if (! auth()->user()->can('orders.view')) {
            abort(403);
        }

        return Inertia::render('Products/CustomOrders', [
            'customOrders' => CustomOrder::latest()->get(),
        ]);
    }

    /**
     * Show the form to submit a new custom order request.
     */
    public function create()
    {
        return Inertia::render('CustomOrder');
    }

    /**
     * Save a new custom order request to the database.
     */
    public function store(Request $request)
    {
        // Validate the custom order form data
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:5000'],
            'reference_image' => ['nullable', 'image', 'max:2048'],
            'budget_min' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'desired_date' => ['nullable', 'date', 'after:today'],
        ]);

        // If the user uploaded a reference image, save it
        if ($request->hasFile('reference_image')) {
            $validatedData['reference_image'] = $request->file('reference_image')->store('custom-orders', 'public');
        }

        // Attach the logged-in user's ID to the order
        $validatedData['user_id'] = auth()->id();

        // Create the custom order
        CustomOrder::create($validatedData);

        return redirect('/')->with('success', 'Your custom order request has been submitted! We will contact you soon.');
    }
}
