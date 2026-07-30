<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Admin: Show a list of all categories.
     */
    public function index()
    {
        // Only allow users with permission to view categories
        if (! auth()->user()->can('categories.view')) {
            abort(403);
        }

        return Inertia::render('Categories/Index', [
            'categories' => Category::latest()->get(),
        ]);
    }

    /**
     * Show the products in a specific category (for customers browsing the store).
     */
    public function show(Request $request, Category $category)
    {
        // Start building the product query for this category
        $productQuery = Product::with('category', 'subcategory')
            ->where('category_id', $category->id)
            ->where('is_active', true);

        // If the user searched for something, filter by product name
        if ($request->search) {
            $productQuery->where('name', 'like', '%'.$request->search.'%');
        }

        // If a subcategory filter is applied, only show products in that subcategory
        if ($request->subcategory_id) {
            $productQuery->where('subcategory_id', $request->subcategory_id);
        }

        // If a minimum price filter is set, only show products above that price
        if ($request->min_price !== null) {
            $productQuery->where('price', '>=', $request->min_price);
        }

        // If a maximum price filter is set, only show products below that price
        if ($request->max_price !== null) {
            $productQuery->where('price', '<=', $request->max_price);
        }

        // Sort the products based on the user's selection
        if ($request->sortBy === 'low-to-high') {
            $productQuery->orderBy('price', 'asc');
        } elseif ($request->sortBy === 'high-to-low') {
            $productQuery->orderBy('price', 'desc');
        } else {
            $productQuery->latest();
        }

        // Get the IDs of products in the user's cart (if logged in)
        $cartProductIds = [];
        if (auth()->check()) {
            $cartProductIds = Cart::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get the IDs of products in the user's wishlist (if logged in)
        $wishlistProductIds = [];
        if (auth()->check()) {
            $wishlistProductIds = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get subcategories with their product counts
        $subcategories = $category->subcategories()
            ->withCount('products')
            ->get();

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'products' => $productQuery->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'min_price', 'max_price', 'sortBy', 'subcategory_id']),
            'subcategories' => $subcategories,
            'cartProductIds' => $cartProductIds,
            'wishlistProductIds' => $wishlistProductIds,
        ]);
    }

    /**
     * Admin: Save a new category to the database.
     */
    public function store(Request $request)
    {
        // Only allow users with permission to create categories
        if (! auth()->user()->can('categories.create')) {
            abort(403);
        }

        // Validate the category data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        // If an image was uploaded, save it
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('categories', 'public');
        }

        // Create the category
        Category::create($validatedData);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Admin: Update an existing category in the database.
     */
    public function update(Request $request, Category $category)
    {
        // Only allow users with permission to update categories
        if (! auth()->user()->can('categories.update')) {
            abort(403);
        }

        // Validate the category data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'max:2048'],
        ]);

        // If a new image was uploaded, replace the old one
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // Save the new image
            $validatedData['image'] = $request->file('image')->store('categories', 'public');
        }

        // Update the category
        $category->update($validatedData);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Admin: Delete a category from the database.
     */
    public function destroy(Category $category)
    {
        // Only allow users with permission to delete categories
        if (! auth()->user()->can('categories.delete')) {
            abort(403);
        }

        // Delete the category image if it exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // Delete the category
        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
