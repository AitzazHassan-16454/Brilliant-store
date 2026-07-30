<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    /**
     * Admin: Show a list of all coupons.
     */
    public function index()
    {
        // Only allow users with permission to view coupons
        if (! auth()->user()->can('coupons.view')) {
            abort(403);
        }

        return Inertia::render('Coupons/Index', [
            'coupons' => Coupon::latest()->get(),
        ]);
    }

    /**
     * Admin: Save a new coupon to the database.
     */
    public function store(Request $request)
    {
        // Only allow users with permission to create coupons
        if (! auth()->user()->can('coupons.create')) {
            abort(403);
        }

        // Validate the coupon data
        $validatedData = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'active' => ['sometimes', 'boolean'],
        ]);

        // Convert the coupon code to uppercase so it's case-insensitive
        $validatedData['code'] = strtoupper($validatedData['code']);

        // Create the coupon
        Coupon::create($validatedData);

        return back()->with('success', 'Coupon created successfully.');
    }

    /**
     * Admin: Update an existing coupon in the database.
     */
    public function update(Request $request, Coupon $coupon)
    {
        // Only allow users with permission to update coupons
        if (! auth()->user()->can('coupons.update')) {
            abort(403);
        }

        // Validate the coupon data
        $validatedData = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,'.$coupon->id],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'active' => ['sometimes', 'boolean'],
        ]);

        // Convert the coupon code to uppercase
        $validatedData['code'] = strtoupper($validatedData['code']);

        // Update the coupon
        $coupon->update($validatedData);

        return back()->with('success', 'Coupon updated successfully.');
    }

    /**
     * Admin: Delete a coupon from the database.
     */
    public function destroy(Coupon $coupon)
    {
        // Only allow users with permission to delete coupons
        if (! auth()->user()->can('coupons.delete')) {
            abort(403);
        }

        $coupon->delete();

        return back()->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Validate a coupon code and calculate the discount.
     * This is called when the user enters a coupon code during checkout.
     */
    public function validateCode(Request $request)
    {
        // Validate the request data
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        // Look up the coupon by its code (case-insensitive)
        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        // If the coupon doesn't exist, show an error
        if (! $coupon) {
            return back()->with('error', 'Invalid coupon code.');
        }

        // Check if the coupon is still valid
        if (! $coupon->isValid()) {
            // Figure out the specific reason why it's invalid
            if ($coupon->expires_at && $coupon->expires_at->isPast()) {
                $reason = 'This coupon has expired.';
            } elseif ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
                $reason = 'This coupon has reached its usage limit.';
            } elseif (! $coupon->active) {
                $reason = 'This coupon is inactive.';
            } else {
                $reason = 'This coupon is no longer valid.';
            }

            return back()->with('error', $reason);
        }

        // Check if the order meets the minimum amount
        if ($request->subtotal < $coupon->min_order) {
            return back()->with('error', 'Minimum order of $'.$coupon->min_order.' required for this coupon.');
        }

        // Calculate the discount amount based on the coupon
        $discountAmount = $coupon->calculateDiscount($request->subtotal);

        // Return the coupon details and discount to the frontend
        return back()->with([
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discountAmount,
            ],
        ]);
    }
}
