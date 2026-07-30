<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    /**
     * Show all items in the user's wishlist.
     */
    public function index()
    {
        // Get all wishlist items for the logged-in user, with product and category info
        $wishlistItems = Wishlist::with('product.category')
            ->where('user_id', auth()->id())
            ->get();

        // Get the IDs of products already in the cart (to show "Add to Cart" buttons)
        $cartProductIds = Cart::where('user_id', auth()->id())
            ->pluck('product_id')
            ->toArray();

        return Inertia::render('Wishlist', [
            'wishlistItems' => $wishlistItems,
            'cartProductIds' => $cartProductIds,
        ]);
    }

    /**
     * Add or remove a product from the wishlist.
     * If the product is already in the wishlist, remove it.
     * If it's not in the wishlist, add it.
     */
    public function toggle(Request $request)
    {
        // Make sure the product ID is provided and exists
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if the product is already in the user's wishlist
        $existingItem = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingItem) {
            // If it's already in the wishlist, remove it
            $existingItem->delete();
        } else {
            // If it's not in the wishlist, add it
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
            ]);
        }

        return back();
    }

    /**
     * Remove a specific item from the wishlist.
     */
    public function destroy($id)
    {
        // Find the wishlist item that belongs to the logged-in user and delete it
        Wishlist::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail()
            ->delete();

        return back()->with('success', 'Removed from wishlist.');
    }
}
