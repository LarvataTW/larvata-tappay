<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Larvata\Tappay\Traits\SendRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * 直接付款
 */
class PayByPrimeService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    /**
     * @param int $order_id 訂單編號
     * @param string $prime Tappay 信用卡 prime
     * @param string $details 交易品項內容，varchar(100)
     * @param boolean $three_domain_secure 是否使用 3D 驗證方式付款，預設為 true
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     * @param string $tappay_partner_key Tappay merchant id Tappay 店家編號
     */
    public function __construct($order_id, $prime, $details, $three_domain_secure = true, $tappay_partner_key = null, $tappay_merchant_id = null)
    {
        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/payment/pay-by-prime";

        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');
        $this->merchant_id = $tappay_merchant_id ?? config('larvata.tappay.merchant_id');

        $this->three_domain_secure = $three_domain_secure;

        $this->prime = $prime;

        $this->order_class_name = config('larvata.tappay.order_class_name');
        $this->order_id = $order_id;
        $this->details = $details;

        $this->frontend_redirect_url = config('larvata.tappay.frontend_redirect_url');
        $this->backend_payment_notify_url = config('larvata.tappay.backend_payment_notify_url');

        $this->payment_success_callback_class_name = config('larvata.tappay.payment_success_callback_class_name');
        $this->payment_failure_callback_class_name = config('larvata.tappay.payment_failure_callback_class_name');
    }

    public function call()
    {
        $this->load_order_datas();
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();
    }

    private function load_order_datas()
    {
        $this->order = $this->order_class_name::find($this->order_id);
        $this->member = $this->order->member;
    }

    private function make_payload()
    {
        $this->payload = [
            'prime' => $this->prime,
            'partner_key' => $this->partner_key,
            'merchant_id' => $this->merchant_id,
            'amount' => $this->order->fee,
            'currency' => 'TWD',
            'order_number' => $this->order->order_number,
            'details' => $this->details,
            'cardholder' => [
                'phone_number' => $this->member->mobile,
                'name' => $this->member->name,
                'email' => $this->member->email,
                'member_id' => $this->member->id
            ],
            'three_domain_secure' => $this->three_domain_secure,
            'result_url' => [
                'frontend_redirect_url' => $this->frontend_redirect_url,
                'backend_notify_url' => $this->backend_payment_notify_url
            ],
            'remember' => true,
        ];
    }

    private function send_request()
    {
        $this->response = Http::timeout(30)
            ->withHeaders([
                              'x-api-key' => $this->partner_key,
                              'Content-Type' => 'application/json'
                          ])->post($this->host.$this->api, $this->payload);
    }

    /**
     * 呼叫 API 回應交易成功後的作業
     */
    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->actions_exception = DB::transaction(function () {
                if($this->three_domain_secure) {
                    $this->order->update([
                                             'rec_trade_id' => $this->response_body_json['rec_trade_id'],
                                         ]);

                    $this->data = [
                        'action' => 'tappay',
                        'payment_url' => $this->response_body_json['payment_url']
                    ];
                } else {
                    $this->update_or_create_member_credit_card();
                    (new $this->payment_success_callback_class_name($this->order))->call();
                }
            });
        } else {
            (new $this->payment_failure_callback_class_name($this->order))->call();
        }
    }

    /**
     * 新增或更新會員信用卡資料
     */
    private function update_or_create_member_credit_card()
    {
        if(class_exists($this->member_credit_card_class_name)) {
            $this->member_credit_card_class_name::updateOrCreate(
                [
                    'member_id' => $this->member->id,
                    'card_token' => $this->response_body_json['card_secret']['card_token'],
                    'card_key' => $this->response_body_json['card_secret']['card_key']
                ],
                [
                    'issuer_zh_tw' => $this->response_body_json['card_info']['issuer_zh_tw'],
                    'bin_code'     => $this->response_body_json['card_info']['bin_code'],
                    'last_four'    => $this->response_body_json['card_info']['last_four'],
                    'expiry_date'  => $this->response_body_json['card_info']['expiry_date'],
                ]);
        }
    }
}
