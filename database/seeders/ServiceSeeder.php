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
                'problem_solved_image' => 'images/services/problem_solved_airport.jpg',
                'problem_solved_desc' => '**Problem Solved: Airport Commute Hassles** No more worrying about airport parking, navigating traffic, or the unreliability of other ride options. We provide punctual, door-to-door service tailored to your flight schedule.',
                'target_audience_image' => 'images/services/target_audience_airport.jpg',
                'target_audience_desc' => '**Ideal For: All Travelers** Business travelers requiring punctuality, families needing space and comfort, tourists seeking a smooth start to their vacation, and anyone valuing a dependable, high-quality airport transfer experience.',
                'client_benefits_image' => 'images/services/client_benefits_airport.jpg',
                'client_benefits_desc' => '**Your Benefits: Comfort & Peace of Mind** Enjoy on-time arrivals with real-time flight tracking, professional meet-and-greet service, assistance with luggage, and a fleet of clean, comfortable vehicles suited to your needs.',
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
                'problem_solved_image' => 'images/services/problem_solved_corporate.jpg',
                'problem_solved_desc' => '**Problem Solved: Executive Transport Challenges** Eliminate concerns about transport logistics for important meetings, client pickups, or executive travel. We provide seamless, timely, and confidential services.',
                'target_audience_image' => 'images/services/target_audience_corporate.jpg',
                'target_audience_desc' => '**Ideal For: Businesses & Executives** Executives, corporate clients, event planners, and businesses looking for premium, reliable transportation solutions that reflect their company\'s image.',
                'client_benefits_image' => 'images/services/client_benefits_corporate.jpg',
                'client_benefits_desc' => '**Your Benefits: Efficiency & Professionalism** Punctual service, discreet chauffeurs, comfortable and well-maintained vehicles, and flexible booking options to meet demanding corporate schedules.',
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
                'description' => 'Make your special night unforgettable with our premium prom and graduation transportation services. Our luxury vehicles and professional chauffeurs ensure you arrive in style and on time for your milestone events.',
                'short_description' => 'Luxury transportation for prom, graduation, and other special occasions.',
                'problem_solved_image' => 'images/services/problem_solved_prom.jpg',
                'problem_solved_desc' => '**Problem Solved: Special Event Transportation** No need to worry about parking, designated drivers, or coordinating multiple vehicles. We provide safe, reliable, and stylish transportation for your special night.',
                'target_audience_image' => 'images/services/target_audience_prom.jpg',
                'target_audience_desc' => '**Ideal For: Students & Families** High school and college students celebrating prom, graduation, or other special events, as well as parents who want to ensure their children have safe and reliable transportation.',
                'client_benefits_image' => 'images/services/client_benefits_prom.jpg',
                'client_benefits_desc' => '**Your Benefits: Safety & Style** Arrive in style with our luxury vehicles, enjoy professional and courteous service, and have peace of mind knowing you have reliable transportation for your special night.',
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
                'description' => 'Experience the ultimate in luxury and convenience with our professional chauffeur services. Whether you need airport transfers, business travel, or a night out on the town, our experienced chauffeurs provide safe, reliable, and discreet transportation tailored to your needs.',
                'short_description' => 'Premium chauffeur services for any occasion, providing comfort and professionalism.',
                'problem_solved_image' => 'images/services/problem_solved_chauffeur.jpg',
                'problem_solved_desc' => '**Problem Solved: Reliable Luxury Transportation** Eliminate the stress of driving and parking while enjoying the comfort and convenience of a professional chauffeur service for all your transportation needs.',
                'target_audience_image' => 'images/services/target_audience_chauffeur.jpg',
                'target_audience_desc' => '**Ideal For: Discerning Clients** Business professionals, special event attendees, tourists, and anyone who values comfort, reliability, and professional service for their transportation needs.',
                'client_benefits_image' => 'images/services/client_benefits_chauffeur.jpg',
                'client_benefits_desc' => '**Your Benefits: Luxury & Convenience** Enjoy door-to-door service, professional and courteous chauffeurs, well-maintained luxury vehicles, and the ability to relax or work while in transit.',
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
                'name' => 'Concert & Event Transportation',
                'banner_image' => 'images/banners/concert_banner.jpg',
                'description' => 'Arrive at concerts and special events in style and avoid the hassle of parking and traffic. Our professional drivers will drop you off at the door and be ready to pick you up when the show is over, ensuring a stress-free experience from start to finish.',
                'short_description' => 'Hassle-free transportation to concerts, sports events, and other special occasions.',
                'problem_solved_image' => 'images/services/problem_solved_concert.jpg',
                'problem_solved_desc' => '**Problem Solved: Event Transportation Headaches** No more worrying about parking, traffic, or designated drivers. We handle the transportation so you can focus on enjoying your event.',
                'target_audience_image' => 'images/services/target_audience_concert.jpg',
                'target_audience_desc' => '**Ideal For: Event-Goers** Music lovers, sports fans, theater enthusiasts, and anyone attending concerts, games, shows, or other events who wants a stress-free transportation experience.',
                'client_benefits_image' => 'images/services/client_benefits_concert.jpg',
                'client_benefits_desc' => '**Your Benefits: Stress-Free Event Experience** Enjoy door-to-door service, skip the parking hassle, travel in comfort with your group, and have a safe ride home after the event.',

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
                'name' => 'Black Car Service',
                'banner_image' => 'images/banners/black_car_banner.jpg',
                'description' => 'Travel in style and comfort with our premium black car service. Perfect for business travel, special occasions, or when you simply want to arrive in luxury, our professional chauffeurs and immaculate vehicles ensure a first-class experience.',
                'short_description' => 'Premium black car service for business, leisure, and special occasions.',
                'problem_solved_image' => 'images/services/problem_solved_blackcar.jpg',
                'problem_solved_desc' => '**Problem Solved: Premium Transportation Needs** Eliminate the stress of transportation with our reliable, comfortable, and luxurious black car service that caters to your schedule and preferences.',
                'target_audience_image' => 'images/services/target_audience_blackcar.jpg',
                'target_audience_desc' => '**Ideal For: Discerning Travelers** Business professionals, special event attendees, and anyone who values comfort, style, and reliability in their transportation.',
                'client_benefits_image' => 'images/services/client_benefits_blackcar.jpg',
                'client_benefits_desc' => '**Your Benefits: Premium Experience** Enjoy the comfort of luxury vehicles, professional and discreet chauffeurs, on-time service, and the ability to work or relax during your ride.',
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
