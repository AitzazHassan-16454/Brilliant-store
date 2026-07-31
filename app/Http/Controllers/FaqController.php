<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Public: Show the FAQ page.
     */
    public function index()
    {
        return Inertia::render('Faq', [
            'faqs' => Faq::active()->ordered()->get(),
        ]);
    }

    /**
     * Admin: Show a list of all FAQs.
     */
    public function adminIndex()
    {
        // Only allow users with permission to view FAQs
        if (! auth()->user()->can('faqs.view')) {
            abort(403);
        }

        return Inertia::render('Products/Faqs', [
            'faqs' => Faq::ordered()->get(),
        ]);
    }
}
