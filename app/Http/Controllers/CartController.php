<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Show all items in the user's cart.
     */
    public function index()
    {
        // Get all cart items for the logged-in user, with product and category info
        $cartItems = Cart::with('product.category')
            ->where('user_id', auth()->id())
            ->get();

        // Show the Cart page with the cart items
        return inertia('Cart', [
            'cartItems' => $cartItems,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request)
    {
        // Make sure the product ID is provided and exists
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        // Find the product or show a 404 error
        $product = Product::findOrFail($request->product_id);

        // Check if the product is still available for purchase
        if (! $product->is_active) {
            return back()->with('error', 'This product is no longer available.');
        }

        // Check if the user already has this product in their cart
        $existingCartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        // How many of this product are already in the cart?
        $currentQuantity = $existingCartItem ? $existingCartItem->qty : 0;

        // Make sure we don't add more than what's in stock
        if ($currentQuantity >= $product->stock) {
            return back()->with('error', 'Not enough stock for "'.$product->name.'".');
        }

        if ($existingCartItem) {
            // If the product is already in the cart, increase the quantity by 1
            $existingCartItem->increment('qty');
        } else {
            // If it's a new product in the cart, add it with quantity 1
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'qty' => 1,
            ]);
        }

        // Go back to the previous page
        return back();
    }

    /**
     * Increase the quantity of a cart item.
     */
    public function increase($id)
    {
        // Find the cart item that belongs to the logged-in user
        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Check if we can add more (not exceeding stock)
        if ($cartItem->qty >= $cartItem->product->stock) {
            return back()->with('error', 'No more stock available for "'.$cartItem->product->name.'".');
        }

        // Increase the quantity by 1
        $cartItem->increment('qty');

        return back();
    }

    /**
     * Decrease the quantity of a cart item.
     */
    public function decrease($id)
    {
        // Find the cart item that belongs to the logged-in user
        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Only decrease if the quantity is more than 1 (minimum is 1)
        if ($cartItem->qty > 1) {
            $cartItem->decrement('qty');
        }

        return back();
    }

    /**
     * Remove an item from the cart completely.
     */
    public function destroy($id)
    {
        // Find the cart item that belongs to the logged-in user
        $cartItem = Cart::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Delete the item from the cart
        $cartItem->delete();

        return back();
    }
}
