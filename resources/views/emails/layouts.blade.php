<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'LuxeRide'))</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', Arial, sans-serif;
            background-color: #f8f9fa;
            line-height: 1.6;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #C7A656 0%, #D4B366 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 8px 0 0 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 300;
        }

        /* Content Styles */
        .content {
            padding: 40px 30px;
        }

        .content h2 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
        }

        .content h3 {
            margin: 20px 0 15px 0;
            margin-top: 10px;
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
        }

        .content p {
            margin: 0 0 16px 0;
            color: #5a6c7d;
            font-size: 16px;
            line-height: 1.7;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .badge-success {
            background-color: #27ae60;
            color: white;
        }

        .badge-warning {
            background-color: #f39c12;
            color: white;
        }

        .badge-danger {
            background-color: #e74c3c;
            color: white;
        }

        .badge-info {
            background-color: #3498db;
            color: white;
        }

        .badge-primary {
            background-color: #C7A656;
            color: white;
        }

        /* Card/Box Styles */
        .card {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 30px;
            padding-top: 20px;
            margin: 30px 0;
            border-left: 4px solid #C7A656;
        }

        .card-white {
            background-color: #ffffff;
            border: 2px solid #e9ecef;
        }

        .card-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }

        .card-success {
            background-color: #d4edda;
            border-left-color: #28a745;
        }

        /* Detail Item Styles */
        .detail-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #7f8c8d;
            font-weight: 600;
            min-width: 120px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
            flex: 1;
            margin-left: 20px;
        }

        .detail-value.primary {
            color: #C7A656;
            font-weight: 600;
        }

        .detail-value.success {
            color: #27ae60;
            font-weight: 600;
        }

        .detail-value.price {
            color: #C7A656;
            font-weight: 700;
            font-size: 18px;
        }

        /* Button Styles */
        .btn-container {
            text-align: center;
            margin: 40px 0;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            color: #ffffff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-sm {
            padding: 12px 24px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #C7A656 0%, #D4B366 100%);
            box-shadow: 0 4px 15px rgba(199, 166, 86, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #3498db 0%, #5dade2 100%);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #ec7063 100%);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Alert Styles */
        .alert {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }

        .alert-success {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }

        /* Footer Styles */
        .footer {
            background-color: #2c3e50;
            padding: 30px;
            text-align: center;
            color: white;
        }

        .footer h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .footer-contact {
            margin: 0 0 20px 0;
            color: #bdc3c7;
            font-size: 14px;
        }

        .footer-divider {
            border-top: 1px solid #34495e;
            padding-top: 20px;
            margin-top: 20px;
        }

        .footer-copyright {
            margin: 0 0 10px 0;
            color: #95a5a6;
            font-size: 13px;
        }

        .footer-meta {
            margin: 0;
            color: #7f8c8d;
            font-size: 12px;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #7f8c8d;
        }

        .text-primary {
            color: #C7A656;
        }

        .text-success {
            color: #27ae60;
        }

        .text-warning {
            color: #f39c12;
        }

        .text-danger {
            color: #e74c3c;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 8px;
        }

        .mb-2 {
            margin-bottom: 16px;
        }

        .mb-3 {
            margin-bottom: 24px;
        }

        .mb-4 {
            margin-bottom: 32px;
        }

        .mt-0 {
            margin-top: 0;
        }

        .mt-1 {
            margin-top: 8px;
        }

        .mt-2 {
            margin-top: 16px;
        }

        .mt-3 {
            margin-top: 24px;
        }

        .mt-4 {
            margin-top: 32px;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .email-container {
                margin: 20px;
            }

            .header,
            .content,
            .footer {
                padding: 30px 20px;
            }

            .detail-item {
                flex-direction: column;
                gap: 8px;
            }

            .detail-value {
                margin-left: 0;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'LuxeRide') }}</h1>
            <p>{{ config('app.tagline', 'Premium Limousine Service') }}</p>
        </div>

        <!-- Main Content -->
        <div class="content">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            @hasSection('footer_content')
                @yield('footer_content')
            @else
                <div>
                    <h3>@yield('footer_title', 'Need Help?')</h3>
                    <p class="footer-contact">
                        @yield('footer_contact', '📞 (555) 000-0000 | ✉️ support@luxeride.com')
                    </p>
                </div>

                <div class="footer-divider">
                    <p class="footer-copyright">
                        © {{ date('Y') }} {{ config('app.name', 'LuxeRide') }}. All rights reserved.
                    </p>
                    <p class="footer-meta">
                        Email sent on {{ now()->format('M d, Y \a\t g:i A') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
