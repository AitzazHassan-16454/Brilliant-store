<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I place a custom order?',
                'answer' => 'Head over to the Custom Order page and fill out the form with your name, contact details, description, and any reference images. You can also message us directly on WhatsApp for the fastest response — we\'ll help shape the perfect piece for your space.',
            ],
            [
                'question' => 'How long does a custom piece take to complete?',
                'answer' => 'Most custom pieces are completed within 2 to 4 weeks depending on the complexity, size, and medium. Once you share your idea, we\'ll confirm a timeline and keep you updated throughout the process.',
            ],
            [
                'question' => 'Can you work from my own reference images?',
                'answer' => 'Absolutely. Attach screenshots, inspiration, or room photos to your custom order request. Reference images help us understand your preferred style, colors, and sizing so the final piece matches your vision.',
            ],
            [
                'question' => 'Do you offer shipping outside your country?',
                'answer' => 'Yes, we ship internationally. Shipping costs and delivery times vary by destination, and we\'ll confirm all details with you before your order moves into production.',
            ],
            [
                'question' => 'What are the payment options?',
                'answer' => 'We accept major credit cards, debit cards, and bank transfers. For custom pieces, a deposit is usually required to start production and the balance is due before shipping.',
            ],
            [
                'question' => 'Can I return or exchange a custom order?',
                'answer' => 'Because custom pieces are made uniquely for you, they are non-returnable. However, if your piece arrives damaged or does not match the agreed design, we\'ll make it right with a rework or replacement.',
            ],
            [
                'question' => 'How are ready-made products packaged?',
                'answer' => 'Every piece is carefully packed with protective wrapping and durable packaging to ensure it arrives in perfect condition.',
            ],
            [
                'question' => 'How can I track my order?',
                'answer' => 'Once your order ships, you\'ll receive a confirmation email with tracking details so you can follow your package from our studio to your door.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                ['answer' => $faq['answer'], 'sort_order' => $index, 'is_active' => true]
            );
        }
    }
}
