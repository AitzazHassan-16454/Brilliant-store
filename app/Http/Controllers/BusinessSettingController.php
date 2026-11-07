<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BusinessSettingController extends Controller
{
    public function edit(): Response
    {
        $this->authorizeAccess('settings.view');

        return Inertia::render('Settings/Index', [
            'settings' => BusinessSetting::shared(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAccess('settings.update');

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_hours' => ['nullable', 'string', 'max:120'],
            'footer_description' => ['nullable', 'string', 'max:200'],
        ]);

        $settings = BusinessSetting::query()->first()
            ?? new BusinessSetting(BusinessSetting::defaults());

        $settings->fill(Arr::except($validated, ['logo', 'remove_logo']));

        if ($request->boolean('remove_logo') && $settings->logo) {
            Storage::disk('public')->delete($settings->logo);
            $settings->logo = null;
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo) {
                Storage::disk('public')->delete($settings->logo);
            }

            $settings->logo = $request->file('logo')->store('branding', 'public');
        }

        $settings->save();

        return back()->with('success', 'Business settings updated.');
    }

    private function authorizeAccess(string $permission): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->hasRole('admin') || $user->can($permission)),
            403
        );
    }
}
