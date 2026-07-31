<?php

namespace App\Support;

class ApplicationPermissions
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'dashboard.view',
            'users.view',
            'users.create',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'subcategories.view',
            'subcategories.create',
            'subcategories.update',
            'subcategories.delete',
            'orders.view',
            'orders.update',
            // 'orders.manage',
            'faqs.view',
            'coupons.view',
            'coupons.create',
            'coupons.update',
            'coupons.delete',
            'reviews.view',
            'reviews.update',
            'reviews.delete',
        ];
    }
}
