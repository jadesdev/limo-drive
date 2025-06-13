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
        $faqsData = [
            [
                'question' => 'What types of vehicles does Ikenga Limo offer?',
                'answer' => "<p>Ikenga Limo offers a luxury fleet including:</p><ul><li>3 Ford Expedition Max (Black)</li><li>2 Chevrolet Suburban (Black)</li><li>2 Lincoln Navigator (Black)</li><li>2 Cadillac XT6</li><li>1 Cadillac Limo</li><li>1 BMW X7 Series</li></ul>",
                'is_active' => true,
                'order' => 1,
            ],
            [
                'question' => 'Is Ikenga Limo licensed and insured?',
                'answer' => '<p>Yes, Ikenga Limo is fully licensed and insured, providing you with peace of mind for every journey. All our chauffeurs are professionally trained and certified.</p>',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'question' => 'How are the limo service rates determined?',
                'answer' => '<p>Our rates are determined based on several factors including the type of vehicle selected, the duration of the service, distance traveled, and any specific requests or amenities. We offer transparent pricing with no hidden fees. Please contact us for a custom quote.</p>',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'question' => 'Can I book Ikenga Limo for airport transfers?',
                'answer' => '<p>Absolutely! We specialize in reliable and luxurious airport transfers. We monitor flight schedules to ensure timely pickups and drop-offs, accommodating any delays or early arrivals.</p>',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'question' => 'Do you offer corporate transportation solutions?',
                'answer' => '<p>Yes, we provide comprehensive corporate transportation services tailored to meet the needs of businesses and executives. This includes transport for meetings, events, and VIP clients.</p>',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'question' => 'Can I customize a limo package for my event?',
                'answer' => '<p>Certainly! We are happy to work with you to customize a transportation package that perfectly suits your special event, whether it\'s a wedding, prom, concert, or any other occasion. Contact us to discuss your requirements.</p>',
                'is_active' => true,
                'order' => 6,
            ],

        ];

        foreach ($faqsData as $faqItem) {
            Faq::create($faqItem);
        }
    }
}
