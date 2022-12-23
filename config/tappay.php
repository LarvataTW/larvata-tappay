<?php

// Larvata Tappay 參數
return [
    'app_url' => env('APP_URL', 'http://localhost'),
    'host' => env('TAPPAY_HOST', 'https://sandbox.tappaysdk.com'),
    'partner_key' => env('TAPPAY_PARTNER_KEY', ''),
    'merchant_id' => env('TAPPAY_MERCHANT_ID', ''),
    'frontend_redirect_url' => env('FRONTEND_REDIRECT_URL', ''),
    'backend_payment_notify_url' => env('BACKEND_PAYMENT_NOTIFY_URL', ''),
    'backend_bind_card_notify_url' => env('BACKEND_BIND_CARD_NOTIFY_URL', ''),
    'member_class_name' => env('MEMBER_CLASS_NAME', 'App\Models\Member'),
    'member_credit_card_class_name' => env('MEMBER_CREDIT_CARD_CLASS_NAME', 'App\Models\MemberCreditCard'),
    'order_class_name' => env('ORDER_CLASS_NAME', 'App\Models\Order'),
    'payment_callback_class_name' => env('PAYMENT_CALLBACK_CLASS_NAME', 'App\Services\Order\Payment\TappayCallback'),
];
