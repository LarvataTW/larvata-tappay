<?php

// Larvata Tappay 參數
return [
    'larvata' => [
        'tappay' => [
            'app_url' => env('APP_URL', 'http://localhost'),
            'host' => env('TAPPAY_HOST', 'https://sandbox.tappaysdk.com'),
            'partner_key' => env('TAPPAY_PARTNER_KEY', ''),
            'merchant_id' => env('TAPPAY_MERCHANT_ID', ''),
            'frontend_redirect_url' => env('FrontendRedirectURL', ''),
            'backend_payment_notify_url' => env('BACKEND_PAYMENT_NOTIFY_URL', ''),
            'backend_bind_card_notify_url' => env('BACKEND_BIND_CARD_NOTIFY_URL', ''),
            'member_class_name' => env('MEMBER_CLASS_NAME', 'Member'),
            'member_credit_card_class_name' => env('MEMBER_CREDIT_CARD_CLASS_NAME', 'MemberCreditCard'),
            'order_class_name' => env('ORDER_CLASS_NAME', 'Order'),
            'payment_success_callback_class_name' => env('PAYMENT_SUCCESS_CALLBACK_CLASS_NAME', 'Order'),
            'payment_failure_callback_class_name' => env('PAYMENT_FAILURE_CALLBACK_CLASS_NAME', 'Order'),
        ]
    ]
];
