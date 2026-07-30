<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Users: Submit a new review for a product.
     */
    public function store(Request $request)
    {
        // Validate the review data
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if the user already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        // Create the review with "pending" status (needs admin approval)
        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Review submitted! It will appear after admin approval.');
    }

    /**
     * Users: Update their existing review.
     */
    public function update(Request $request, Review $review)
    {
        // Make sure the review belongs to the current user
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate the updated data
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Update the review and set it back to pending for re-approval
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Review updated! It will appear after admin approval.');
    }

    /**
     * Users: Delete their own review.
     */
    public function destroy(Review $review)
    {
        // Make sure the review belongs to the current user
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted!');
    }

    /**
     * Admin: Show all reviews with pagination.
     */
    public function index()
    {
        // Only allow users with permission to view reviews
        if (! auth()->user()->can('reviews.view')) {
            abort(403);
        }

        return Inertia::render('Products/Reviews', [
            'reviews' => Review::with('user', 'product')
                ->latest()
                ->paginate(15),
        ]);
    }

    /**
     * Admin: Show a single review in detail.
     */
    public function show(Review $review)
    {
        // Only allow users with permission to view reviews
        if (! auth()->user()->can('reviews.view')) {
            abort(403);
        }

        return Inertia::render('Products/ReviewShow', [
            'review' => Review::with('user', 'product')->findOrFail($review->id),
        ]);
    }

    /**
     * Admin: Approve a pending review.
     */
    public function approve(Review $review)
    {
        // Only allow users with permission to update reviews
        if (! auth()->user()->can('reviews.update')) {
            abort(403);
        }

        $review->update(['status' => 'approved']);

        return back()->with('success', 'Review approved.');
    }

    /**
     * Admin: Reject a pending review.
     */
    public function reject(Review $review)
    {
        // Only allow users with permission to update reviews
        if (! auth()->user()->can('reviews.update')) {
            abort(403);
        }

        $review->update(['status' => 'rejected']);

        return back()->with('success', 'Review rejected.');
    }

    /**
     * Admin: Delete any review.
     */
    public function adminDestroy(Review $review)
    {
        // Only allow users with permission to delete reviews
        if (! auth()->user()->can('reviews.delete')) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
