<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Str;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicesData = [
            [
                'name' => 'Airport Transportation',
                'banner_image' => 'images/banners/airport_transportation_banner.jpg',
                'description' => 'Experience seamless and stress-free airport transfers with our premium limousine service. We monitor your flight in real-time to ensure punctual pickups and drop-offs, even if your flight is early or delayed. Our professional, uniformed chauffeurs provide a meet-and-greet service at arrivals, assist with your luggage, and ensure a comfortable ride in our luxury sedans and SUVs. Start and end your journey with reliability and style.',
                'short_description' => 'Reliable, stress-free airport transfers with professional chauffeurs and luxury vehicles.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_airport.jpg',
                        'title' => 'Problem Solved: Airport Commute Hassles',
                        'description' => 'No more worrying about airport parking, navigating traffic, or the unreliability of other ride options. We provide punctual, door-to-door service tailored to your flight schedule.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_airport.jpg',
                        'title' => 'Ideal For: All Travelers',
                        'description' => 'Business travelers requiring punctuality, families needing space and comfort, tourists seeking a smooth start to their vacation, and anyone valuing a dependable, high-quality airport transfer experience.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_airport.jpg',
                        'title' => 'Your Benefits: Comfort & Peace of Mind',
                        'description' => 'Enjoy on-time arrivals with real-time flight tracking, professional meet-and-greet service, assistance with luggage, and a fleet of clean, comfortable vehicles suited to your needs.',
                    ],
                ],
                'features' => [
                    'Flight Tracking & Adjusted Pickup Times',
                    'Professional, Uniformed Chauffeurs',
                    '24/7 Availability',
                    'Meet-and-Greet Service at Arrivals',
                    'Spacious Luggage Capacity',
                    'Easy Online Booking & Payment',
                ],
                'technologies' => [
                    'Real-Time Flight Tracking',
                    'Luxury Sedans & SUVs',
                    'Online Reservation System',
                ],
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Corporate Service',
                'banner_image' => 'images/banners/corporate_service_banner.jpg',
                'description' => 'Elevate your business travel with our reliable and discreet corporate transportation services. We understand the importance of punctuality and professionalism for executives and their clients. Our fleet of premium vehicles and experienced chauffeurs ensure a comfortable and productive environment on the go.',
                'short_description' => 'Professional and reliable transportation for executives and corporate events.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_corporate.jpg',
                        'title' => 'Problem Solved: Executive Transport Challenges',
                        'description' => 'Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_corporate.jpg',
                        'title' => 'Ideal For: Businesses & Executives',
                        'description' => 'Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_corporate.jpg',
                        'title' => 'Your Benefits: Efficiency & Professionalism',
                        'description' => 'Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
                    ],
                ],
                'features' => [
                    'Discreet & Professional Chauffeurs',
                    'On-Time Guarantee for Meetings',
                    'Wi-Fi Equipped Vehicles (on request)',
                    'Monthly Invoicing for Corporate Accounts',
                ],
                'technologies' => [
                    'GPS Fleet Management',
                    'Corporate Account Portal',
                ],
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Prom & Graduation',
                'banner_image' => 'images/banners/prom_graduation_banner.jpg',
                'description' => 'Elevate your business travel with our reliable and discreet corporate transportation services. We understand the importance of punctuality and professionalism for executives and their clients. Our fleet of premium vehicles and experienced chauffeurs ensure a comfortable and productive environment on the go.',
                'short_description' => 'Professional and reliable transportation for executives and corporate events.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_prom.jpg',
                        'title' => 'Problem Solved: Executive Transport Challenges',
                        'description' => 'Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_prom.jpg',
                        'title' => 'Ideal For: Businesses & Executives',
                        'description' => 'Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_prom.jpg',
                        'title' => 'Your Benefits: Efficiency & Professionalism',
                        'description' => 'Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
                    ],
                ],
                'features' => [
                    'Discreet & Professional Chauffeurs',
                    'On-Time Guarantee for Meetings',
                    'Wi-Fi Equipped Vehicles (on request)',
                    'Monthly Invoicing for Corporate Accounts',
                ],
                'technologies' => [
                    'GPS Fleet Management',
                    'Corporate Account Portal',
                ],
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Chauffeur Service',
                'banner_image' => 'images/banners/chauffeur_service_banner.jpg',
                'description' => 'Elevate your business travel with our reliable and discreet corporate transportation services. We understand the importance of punctuality and professionalism for executives and their clients. Our fleet of premium vehicles and experienced chauffeurs ensure a comfortable and productive environment on the go.',
                'short_description' => 'Professional and reliable transportation for executives and corporate events.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_chauffeur.jpg',
                        'title' => 'Problem Solved: Executive Transport Challenges',
                        'description' => 'Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_chauffeur.jpg',
                        'title' => 'Ideal For: Businesses & Executives',
                        'description' => 'Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_chauffeur.jpg',
                        'title' => 'Your Benefits: Efficiency & Professionalism',
                        'description' => 'Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
                    ],
                ],
                'features' => [
                    'Discreet & Professional Chauffeurs',
                    'On-Time Guarantee for Meetings',
                    'Wi-Fi Equipped Vehicles (on request)',
                    'Monthly Invoicing for Corporate Accounts',
                ],
                'technologies' => [
                    'GPS Fleet Management',
                    'Corporate Account Portal',
                ],
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Concert',
                'banner_image' => 'images/banners/concert_banner.jpg',
                'description' => 'Elevate your business travel with our reliable and discreet corporate transportation services. We understand the importance of punctuality and professionalism for executives and their clients. Our fleet of premium vehicles and experienced chauffeurs ensure a comfortable and productive environment on the go.',
                'short_description' => 'Professional and reliable transportation for executives and corporate events.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_concert.jpg',
                        'title' => 'Problem Solved: Executive Transport Challenges',
                        'description' => 'Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_concert.jpg',
                        'title' => 'Ideal For: Businesses & Executives',
                        'description' => 'Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_concert.jpg',
                        'title' => 'Your Benefits: Efficiency & Professionalism',
                        'description' => 'Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
                    ],
                ],
                'features' => [
                    'Discreet & Professional Chauffeurs',
                    'On-Time Guarantee for Meetings',
                    'Wi-Fi Equipped Vehicles (on request)',
                    'Monthly Invoicing for Corporate Accounts',
                ],
                'technologies' => [
                    'GPS Fleet Management',
                    'Corporate Account Portal',
                ],
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Black car',
                'banner_image' => 'images/banners/black_car_banner.jpg',
                'description' => 'Elevate your business travel with our reliable and discreet corporate transportation services. We understand the importance of punctuality and professionalism for executives and their clients. Our fleet of premium vehicles and experienced chauffeurs ensure a comfortable and productive environment on the go.',
                'short_description' => 'Professional and reliable transportation for executives and corporate events.',
                'attributes' => [
                    'problem_solved' => [
                        'image_path' => 'images/services/problem_solved_black_car.jpg',
                        'title' => 'Problem Solved: Executive Transport Challenges',
                        'description' => 'Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                    ],
                    'target_audience' => [
                        'image_path' => 'images/services/target_audience_black_car.jpg',
                        'title' => 'Ideal For: Businesses & Executives',
                        'description' => 'Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                    ],
                    'client_benefits' => [
                        'image_path' => 'images/services/client_benefits_black_car.jpg',
                        'title' => 'Your Benefits: Efficiency & Professionalism',
                        'description' => 'Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
                    ],
                ],
                'features' => [
                    'Discreet & Professional Chauffeurs',
                    'On-Time Guarantee for Meetings',
                    'Wi-Fi Equipped Vehicles (on request)',
                    'Monthly Invoicing for Corporate Accounts',
                ],
                'technologies' => [
                    'GPS Fleet Management',
                    'Corporate Account Portal',
                ],
                'is_active' => true,
                'order' => 6,
            ],
        ];

        foreach ($servicesData as $service) {
            $service['slug'] = Str::slug($service['name']);

            Service::create($service);
        }
    }
}
