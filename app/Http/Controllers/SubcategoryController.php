<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryController extends Controller
{
    /**
     * Admin: Show a list of all subcategories, optionally filtered by category.
     */
    public function index(Request $request)
    {
        // Only allow users with permission to view subcategories
        if (! auth()->user()->can('subcategories.view')) {
            abort(403);
        }

        // Start building the query
        $subcategoryQuery = Subcategory::with('category')->latest();

        // If a category filter is applied, only show subcategories in that category
        if ($request->category_id) {
            $subcategoryQuery->where('category_id', $request->category_id);
        }

        return Inertia::render('Subcategories/Index', [
            'subcategories' => $subcategoryQuery->paginate(10)->withQueryString(),
            'categories' => Category::select('id', 'uid', 'name')->get(),
            'filters' => $request->only(['category_id']),
        ]);
    }

    /**
     * Admin: Save a new subcategory to the database.
     */
    public function store(Request $request)
    {
        // Only allow users with permission to create subcategories
        if (! auth()->user()->can('subcategories.create')) {
            abort(403);
        }

        // Validate the subcategory data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        // Check if a subcategory with this name already exists in this category
        $subcategoryExists = Subcategory::where('category_id', $validatedData['category_id'])
            ->where('name', $validatedData['name'])
            ->exists();

        if ($subcategoryExists) {
            return back()->withErrors(['name' => 'Subcategory already exists in this category.']);
        }

        // Create the new subcategory
        Subcategory::create($validatedData);

        return back()->with('success', 'Subcategory created successfully.');
    }

    /**
     * Admin: Update an existing subcategory in the database.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        // Only allow users with permission to update subcategories
        if (! auth()->user()->can('subcategories.update')) {
            abort(403);
        }

        // Validate the subcategory data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        // Check if another subcategory with this name already exists in this category
        $duplicateExists = Subcategory::where('category_id', $validatedData['category_id'])
            ->where('name', $validatedData['name'])
            ->where('id', '!=', $subcategory->id)
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Subcategory already exists in this category.']);
        }

        // Update the subcategory
        $subcategory->update($validatedData);

        return back()->with('success', 'Subcategory updated successfully.');
    }

    /**
     * Admin: Delete a subcategory from the database.
     */
    public function destroy(Subcategory $subcategory)
    {
        // Only allow users with permission to delete subcategories
        if (! auth()->user()->can('subcategories.delete')) {
            abort(403);
        }

        $subcategory->delete();

        return back()->with('success', 'Subcategory deleted successfully.');
    }
}
