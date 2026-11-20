<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Subcategory;
use App\Models\User;
use App\Support\ApplicationPermissions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (ApplicationPermissions::all() as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->adminRole->syncPermissions(ApplicationPermissions::all());

    $this->adminUser = User::factory()->create(['is_admin' => true]);
    $this->adminUser->assignRole('admin');

    $this->regularUser = User::factory()->create(['is_admin' => false]);
});

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/
describe('Admin Dashboard', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get('/dashboard')->assertRedirect('/login');
    });

    it('forbids users without dashboard.view permission', function () {
        $this->actingAs($this->regularUser)
            ->get('/dashboard')
            ->assertForbidden();
    });

    it('renders the admin dashboard for authorized admin', function () {
        $this->actingAs($this->adminUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Products/Dashboard', false));
    });
});

/*
|--------------------------------------------------------------------------
| Products CRUD
|--------------------------------------------------------------------------
*/
describe('Admin Products CRUD', function () {
    it('lists products for authorized admin', function () {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->actingAs($this->adminUser)
            ->get('/products')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Index', false)
                ->has('products.data')
                ->has('categories')
            );
    });

    it('renders product create page', function () {
        $this->actingAs($this->adminUser)
            ->get('/products/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Products/Create', false));
    });

    it('stores a newly created product', function () {
        $category = Category::factory()->create();

        $payload = [
            'name' => 'Artisanal Chronograph',
            'price' => 1450.00,
            'compare_price' => 1800.00,
            'stock' => 12,
            'is_active' => true,
            'is_trending' => true,
            'category_id' => $category->id,
            'description' => 'Exquisitely crafted master timepiece.',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/products', $payload);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Artisanal Chronograph',
            'price' => 1450.00,
            'compare_price' => 1800.00,
            'stock' => 12,
            'is_active' => true,
            'is_trending' => true,
            'category_id' => $category->id,
        ]);
    });

    it('stores a newly created product with multiple uploaded images', function () {
        Storage::fake('public');
        $category = Category::factory()->create();

        $image1 = UploadedFile::fake()->image('front.jpg', 600, 600);
        $image2 = UploadedFile::fake()->image('angle.jpg', 600, 600);

        $payload = [
            'name' => 'Royal Heritage Clock',
            'price' => 2200.00,
            'stock' => 5,
            'is_active' => true,
            'is_trending' => true,
            'category_id' => $category->id,
            'description' => 'Multi-angle artisanal clock.',
            'images' => [$image1, $image2],
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/products', $payload);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Royal Heritage Clock')->firstOrFail();
        expect($product->images)->toHaveCount(2)
            ->and($product->image)->toBe($product->images[0])
            ->and($product->gallery_images)->toHaveCount(2);

        Storage::disk('public')->assertExists($product->images[0]);
        Storage::disk('public')->assertExists($product->images[1]);
    });

    it('renders product edit page', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->adminUser)
            ->get("/products/{$product->uid}/edit")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/EditProduct', false)
                ->where('product.uid', $product->uid)
            );
    });

    it('updates an existing product', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Original Name',
            'price' => 500.00,
            'stock' => 5,
        ]);

        $updatePayload = [
            'name' => 'Updated Masterwork',
            'price' => 850.00,
            'compare_price' => 1200.00,
            'stock' => 20,
            'is_active' => true,
            'is_trending' => true,
            'category_id' => $category->id,
            'description' => 'Revised description.',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/products/{$product->uid}", $updatePayload);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Masterwork',
            'price' => 850.00,
            'compare_price' => 1200.00,
            'stock' => 20,
        ]);
    });

    it('updates a product retaining existing images and uploading new images', function () {
        Storage::fake('public');
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image' => 'products/existing1.jpg',
            'images' => ['products/existing1.jpg', 'products/existing2.jpg'],
        ]);

        $newImage = UploadedFile::fake()->image('new_angle.jpg', 600, 600);

        $updatePayload = [
            'name' => 'Updated Gallery Product',
            'price' => 790.00,
            'stock' => 15,
            'category_id' => $category->id,
            'existing_images' => ['products/existing1.jpg'],
            'images' => [$newImage],
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/products/{$product->uid}", $updatePayload);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        expect($product->images)->toHaveCount(2)
            ->and($product->images[0])->toBe('products/existing1.jpg')
            ->and($product->images[1])->toContain('products/');

        Storage::disk('public')->assertExists($product->images[1]);
    });

    it('toggles product trending status', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_trending' => false,
        ]);

        $this->actingAs($this->adminUser)
            ->post("/products/{$product->uid}/toggle-trending")
            ->assertRedirect(route('products.index'));

        expect($product->fresh()->is_trending)->toBeTrue();
    });

    it('deletes an existing product', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->adminUser)
            ->delete("/products/{$product->uid}")
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Categories CRUD
|--------------------------------------------------------------------------
*/
describe('Admin Categories CRUD', function () {
    it('lists categories for authorized admin', function () {
        Category::factory()->count(2)->create();

        $this->actingAs($this->adminUser)
            ->get('/categories')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Categories/Index', false));
    });

    it('creates a new category', function () {
        $payload = [
            'name' => 'Haute Joaillerie',
            'description' => 'Rare gems and fine jewelry.',
        ];

        $this->actingAs($this->adminUser)
            ->post('/categories', $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Haute Joaillerie',
            'description' => 'Rare gems and fine jewelry.',
        ]);
    });

    it('updates an existing category', function () {
        $category = Category::factory()->create(['name' => 'Old Category']);

        $payload = [
            'name' => 'Updated Category Name',
            'description' => 'Updated luxury description.',
        ];

        $this->actingAs($this->adminUser)
            ->put("/categories/{$category->uid}", $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
        ]);
    });

    it('deletes a category', function () {
        $category = Category::factory()->create(['name' => 'Temporary Category']);

        $this->actingAs($this->adminUser)
            ->delete("/categories/{$category->uid}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Subcategories CRUD
|--------------------------------------------------------------------------
*/
describe('Admin Subcategories CRUD', function () {
    it('lists subcategories for authorized admin', function () {
        $category = Category::factory()->create();
        Subcategory::create(['name' => 'Automatic Watches', 'category_id' => $category->id]);

        $this->actingAs($this->adminUser)
            ->get('/subcategories')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Subcategories/Index', false)
                ->has('subcategories.data')
                ->has('categories')
            );
    });

    it('creates a new subcategory', function () {
        $category = Category::factory()->create();

        $payload = [
            'name' => 'Tourbillon Pieces',
            'category_id' => $category->id,
        ];

        $this->actingAs($this->adminUser)
            ->post('/subcategories', $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subcategories', [
            'name' => 'Tourbillon Pieces',
            'category_id' => $category->id,
        ]);
    });

    it('updates an existing subcategory', function () {
        $category = Category::factory()->create();
        $subcategory = Subcategory::create(['name' => 'Old Subcategory', 'category_id' => $category->id]);

        $payload = [
            'name' => 'Updated Subcategory',
            'category_id' => $category->id,
        ];

        $this->actingAs($this->adminUser)
            ->put("/subcategories/{$subcategory->uid}", $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subcategories', [
            'id' => $subcategory->id,
            'name' => 'Updated Subcategory',
        ]);
    });

    it('deletes a subcategory', function () {
        $category = Category::factory()->create();
        $subcategory = Subcategory::create(['name' => 'Delete Me', 'category_id' => $category->id]);

        $this->actingAs($this->adminUser)
            ->delete("/subcategories/{$subcategory->uid}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('subcategories', [
            'id' => $subcategory->id,
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Admin Users Management
|--------------------------------------------------------------------------
*/
describe('Admin Users Management', function () {
    it('lists all users for authorized admin', function () {
        $this->actingAs($this->adminUser)
            ->get('/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/Users', false)
                ->has('users')
            );
    });

    it('renders the create user page', function () {
        $this->actingAs($this->adminUser)
            ->get('/users/create')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Products/CreateUser', false)
                ->has('roles')
            );
    });

    it('creates a new user and assigns role', function () {
        $payload = [
            'name' => 'Alexander Vance',
            'email' => 'alexander@atelier.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'admin',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/users', $payload);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'alexander@atelier.com',
            'name' => 'Alexander Vance',
        ]);

        $newUser = User::where('email', 'alexander@atelier.com')->first();
        expect($newUser->hasRole('admin'))->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| Admin FAQs
|--------------------------------------------------------------------------
*/
describe('Admin FAQs', function () {
    it('renders admin faqs index', function () {
        $this->actingAs($this->adminUser)
            ->get('/faqs')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Products/Faqs', false));
    });
});
