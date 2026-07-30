<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use App\Support\ApplicationPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissionNames = $user
            ? $this->permissionNamesFor($user)
            : collect();
        $permissions = $permissionNames->flip()->map(fn () => true);

        return array_merge(parent::share($request), [

            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => Str::lower($user->role),
                        'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                    ]
                    : null,
            ],

            'permissions' => [
                'all' => $permissionNames,
                'can' => $permissions,
                'canViewDashboard' => $user
                    ? $user->can('dashboard.view')
                    : false,
                'isAdmin' => $user
                    ? collect($user->getRoleNames())
                        ->map(fn ($role) => Str::lower($role))
                        ->contains('admin')
                    : false,
                'isSalesman' => $user
                    ? collect($user->getRoleNames())
                        ->map(fn ($role) => Str::lower($role))
                        ->intersect(['saleman', 'salesman'])
                        ->isNotEmpty()
                    : false,
            ],

            'cartCount' => $user
                ? Cart::where('user_id', $user->id)->count()
                : 0,

            'wishlistCount' => $user
                ? Wishlist::where('user_id', $user->id)->count()
                : 0,

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'coupon' => fn () => $request->session()->get('coupon'),
            ],

        ]);
    }

    /**
     * @return Collection<int, string>
     */
    private function permissionNamesFor(User $user): Collection
    {
        if ($user->hasRole('admin')) {
            return collect(ApplicationPermissions::all());
        }

        return $user->getAllPermissions()->pluck('name')->values();
    }
}
