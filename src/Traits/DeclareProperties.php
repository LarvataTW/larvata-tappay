<?php

namespace Larvata\Tappay\Traits;

/**
 * 宣告需要的變數
 */
trait DeclareProperties
{
    private $host;
    private $api;
    private $partner_key;
    private $merchant_id;
    private $prime;
    private $rec_trade_id;
    private $three_domain_secure;
    private $payload;
    private $response;
    private $response_body_json;
    private $actions_exception;
    private $data;
    private $status;

    private $app_url;
    private $frontend_redirect_url;
    private $backend_payment_notify_url;
    private $backend_bind_card_notify_url;

    private $member_class_name;
    private $member_id;
    private $member;

    private $member_credit_card_class_name;
    private $member_credit_card_id;
    private $member_credit_card;

    private $order_class_name;
    private $order_id;
    private $payment_record_id;
    private $order;
    private $details;
    private $refund_amount;

    private string $order_number;
    private string $bank_transaction_id;
}
