<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Role;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Show the home page with all active products.
     * This is the main customer-facing product listing.
     */
    public function home(Request $request)
    {
        // Start building the query for active products only
        $productQuery = Product::with('category')->where('is_active', true);

        // If the user searched for a product name, filter by it
        if ($request->search) {
            $productQuery->where('name', 'like', '%'.$request->search.'%');
        }

        // If a specific category is selected, filter by it
        if ($request->category && $request->category !== 'All') {
            $productQuery->where('category_id', $request->category);
        }

        // Sort the products based on user selection
        if ($request->sortBy === 'low-to-high') {
            $productQuery->orderBy('price', 'asc');
        } elseif ($request->sortBy === 'high-to-low') {
            $productQuery->orderBy('price', 'desc');
        } else {
            $productQuery->latest();
        }

        // Get IDs of products in the user's cart (if logged in)
        $cartProductIds = [];
        if (auth()->check()) {
            $cartProductIds = Cart::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get IDs of products in the user's wishlist (if logged in)
        $wishlistProductIds = [];
        if (auth()->check()) {
            $wishlistProductIds = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get the most recently added product to feature on the page
        $latestProduct = Product::with('category')->latest()->first();

        // Calculate some store statistics
        $storeStats = [
            'products' => Product::where('is_active', true)->count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
        ];

        // Get trending products (products with 5-star reviews, sorted by review count)
        $trendingProducts = Product::with('category')
            ->where('is_active', true)
            ->whereHas('reviews', function ($query) {
                $query->where('status', 'approved');
            })
            ->withCount(['reviews' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->withAvg(['reviews' => function ($query) {
                $query->where('status', 'approved');
            }], 'rating')
            ->having('reviews_avg_rating', '=', 5)
            ->orderByDesc('reviews_count')
            ->take(8)
            ->get();

        return Inertia::render('Home', [
            'products' => $productQuery->paginate(10)->withQueryString(),
            'categories' => Category::select('id', 'uid', 'name', 'description', 'image')
                ->withCount('products')
                ->get(),
            'cartProductIds' => $cartProductIds,
            'wishlistProductIds' => $wishlistProductIds,
            'latestProduct' => $latestProduct,
            'stats' => $storeStats,
            'trendingProducts' => $trendingProducts,
        ]);
    }

    /**
     * Admin: Show all products (including inactive ones).
     */
    public function index()
    {
        // Only allow users with permission to view products
        if (! auth()->user()->can('products.view')) {
            abort(403);
        }

        return Inertia::render('Products/Index', [
            'products' => Product::with('category')->latest()->paginate(10),
            'categories' => Category::select('id', 'uid', 'name')->get(),
        ]);
    }

    /**
     * Admin: Show the dashboard with store statistics and analytics.
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Only allow users with permission to view the dashboard
        if (! $user->can('dashboard.view')) {
            abort(403);
        }

        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();

        // --- Calculate revenue trends ---
        $revenueThisMonth = (float) Order::where('created_at', '>=', $startOfThisMonth)->sum('total');
        $revenueLastMonth = (float) Order::whereBetween('created_at', [$startOfLastMonth, $startOfThisMonth])->sum('total');

        $revenueTrend = 0;
        if ($revenueLastMonth > 0) {
            $revenueTrend = round(($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth * 100, 1);
        }

        // --- Calculate order trends ---
        $ordersThisMonth = Order::where('created_at', '>=', $startOfThisMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $startOfThisMonth])->count();

        $ordersTrend = 0;
        if ($ordersLastMonth > 0) {
            $ordersTrend = round(($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth * 100, 1);
        }

        // --- Calculate user registration trends ---
        $usersThisMonth = User::where('created_at', '>=', $startOfThisMonth)->count();
        $usersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $startOfThisMonth])->count();

        $usersTrend = 0;
        if ($usersLastMonth > 0) {
            $usersTrend = round(($usersThisMonth - $usersLastMonth) / $usersLastMonth * 100, 1);
        }

        // --- Calculate product creation trends ---
        $productsThisMonth = Product::where('created_at', '>=', $startOfThisMonth)->count();
        $productsLastMonth = Product::whereBetween('created_at', [$startOfLastMonth, $startOfThisMonth])->count();

        $productsTrend = 0;
        if ($productsLastMonth > 0) {
            $productsTrend = round(($productsThisMonth - $productsLastMonth) / $productsLastMonth * 100, 1);
        }

        // --- Monthly revenue for the last 6 months ---
        $monthlyRevenue = collect(range(5, 0))->map(function ($monthsAgo) use ($now) {
            $monthDate = $now->copy()->subMonths($monthsAgo);

            $revenueForMonth = (float) Order::whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('total');

            return [
                'month' => $monthDate->format('M'),
                'revenue' => $revenueForMonth,
            ];
        });

        // --- Products grouped by category (for chart) ---
        $productsByCategory = Category::withCount('products')
            ->get()
            ->filter(function ($category) {
                // Only include categories that have products
                return $category->products_count > 0;
            })
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->products_count,
                ];
            })
            ->values();

        // --- Recent 5 orders (for quick view) ---
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer' => $order->shipping_name ?? $order->user?->name ?? 'Guest',
                    'total' => (float) $order->total,
                    'status' => $order->status,
                    'date' => $order->created_at->diffForHumans(),
                ];
            });

        // --- Pending reviews (for quick view) ---
        $pendingReviewList = Review::with(['product:id,name', 'user:id,name'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'product' => $review->product->name,
                    'user' => $review->user->name,
                    'rating' => $review->rating,
                    'date' => $review->created_at->diffForHumans(),
                ];
            });

        // Count products with low stock (less than 10 items)
        $lowStockCount = Product::where('stock', '<', 10)
            ->where('is_active', true)
            ->count();

        return Inertia::render('Products/Dashboard', [
            'productsCount' => Product::count(),
            'totalStock' => (int) Product::sum('stock'),
            'categoriesCount' => Category::count(),
            'subcategoriesCount' => Subcategory::count(),
            'usersCount' => User::count(),
            'rolesCount' => Role::count(),
            'ordersCount' => Order::count(),
            'couponsCount' => Coupon::count(),
            'pendingReviewsCount' => Review::where('status', 'pending')->count(),
            'revenue' => (float) Order::sum('total'),
            'revenueThisMonth' => $revenueThisMonth,
            'revenueTrend' => $revenueTrend,
            'ordersThisMonth' => $ordersThisMonth,
            'ordersTrend' => $ordersTrend,
            'usersThisMonth' => $usersThisMonth,
            'usersTrend' => $usersTrend,
            'productsThisMonth' => $productsThisMonth,
            'productsTrend' => $productsTrend,
            'monthlyRevenue' => $monthlyRevenue,
            'productsByCategory' => $productsByCategory,
            'recentOrders' => $recentOrders,
            'pendingReviewList' => $pendingReviewList,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    /**
     * Show a single product's details.
     */
    public function show(Product $product)
    {
        // If the product is not active, show a 404 page
        if (! $product->is_active) {
            abort(404);
        }

        // Get IDs of products in the user's cart (if logged in)
        $cartProductIds = [];
        if (auth()->check()) {
            $cartProductIds = Cart::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get IDs of products in the user's wishlist (if logged in)
        $wishlistProductIds = [];
        if (auth()->check()) {
            $wishlistProductIds = Wishlist::where('user_id', auth()->id())
                ->pluck('product_id')
                ->toArray();
        }

        // Get all approved reviews for this product
        $reviews = Review::with('user')
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->latest()
            ->get();

        // Check if the logged-in user has already reviewed this product
        $userReview = null;
        if (auth()->check()) {
            $userReview = Review::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->first();
        }

        return Inertia::render('Product', [
            'product' => $product->load('category'),
            'cartProductIds' => $cartProductIds,
            'wishlistProductIds' => $wishlistProductIds,
            'reviews' => $reviews,
            'userReview' => $userReview,
        ]);
    }

    /**
     * Admin: Show the form to create a new product.
     */
    public function create()
    {
        // Only allow users with permission to create products
        if (! auth()->user()->can('products.create')) {
            abort(403);
        }

        return Inertia::render('Products/Create', [
            'categories' => Category::select('id', 'uid', 'name')->get(),
        ]);
    }

    /**
     * Admin: Save a new product to the database.
     */
    public function store(Request $request)
    {
        // Only allow users with permission to create products
        if (! auth()->user()->can('products.create')) {
            abort(403);
        }

        // Validate the product data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'between:0,999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
        ]);

        // Convert the is_active checkbox to a boolean
        $validatedData['is_active'] = $request->boolean('is_active');

        // If an image was uploaded, save it
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        // Create the product
        Product::create($validatedData);

        return redirect()->route('products.index');
    }

    /**
     * Admin: Show the form to edit an existing product.
     */
    public function edit(Product $product)
    {
        // Only allow users with permission to update products
        if (! auth()->user()->can('products.update')) {
            abort(403);
        }

        return Inertia::render('Products/EditProduct', [
            'product' => $product->load('category'),
            'categories' => Category::select('id', 'uid', 'name')->get(),
        ]);
    }

    /**
     * Admin: Update an existing product in the database.
     */
    public function update(Request $request, Product $product)
    {
        // Only allow users with permission to update products
        if (! auth()->user()->can('products.update')) {
            abort(403);
        }

        // Validate the product data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'price' => ['required', 'numeric', 'between:0,999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'max:2048'],
            'description' => ['nullable', 'string'],
        ]);

        // Convert the is_active checkbox to a boolean
        $validatedData['is_active'] = $request->boolean('is_active');

        // If a new image was uploaded, replace the old one
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Save the new image
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        // Update the product
        $product->update($validatedData);

        return redirect()->route('products.index');
    }

    /**
     * Admin: Delete a product from the database.
     */
    public function destroy(Product $product)
    {
        // Only allow users with permission to delete products
        if (! auth()->user()->can('products.delete')) {
            abort(403);
        }

        // Delete the product image if it exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index');
    }
}
