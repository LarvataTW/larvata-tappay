<?php

namespace App\Services\Larvata\Tappay\Payments\Tappay;

use App\Services\Larvata\Tappay\Payments\Tappay\Traits\CalledResult;
use App\Services\Larvata\Tappay\Payments\Tappay\Traits\DeclareProperties;
use App\Services\Larvata\Tappay\Payments\Tappay\Traits\IsTradeSuccessful;
use App\Services\Larvata\Tappay\Payments\Tappay\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * 會員綁定卡片
 */
class BindCardService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    /**
     * @param int $member_id 會員編號
     * @param string $prime Tappay 信用卡 prime
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     * @param string $tappay_partner_key Tappay merchant id Tappay 店家編號
     */
    public function __construct($member_id, $prime, $tappay_partner_key = null, $tappay_merchant_id = null)
    {
        $this->app_url = config('larvata.tappay.app_url');

        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/card/bind";

        $this->frontend_redirect_url = config('larvata.tappay.frontend_redirect_url');
        $this->backend_bind_card_notify_url = config('larvata.tappay.backend_bind_card_notify_url');

        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');
        $this->merchant_id = $tappay_merchant_id ?? config('larvata.tappay.merchant_id');

        $this->prime = $prime;

        $this->member_class_name = config('larvata.tappay.member_class_name');
        $this->member_id = $member_id;

        $this->member_credit_card_class_name = config('larvata.tappay.member_credit_card_class_name');
    }

    public function call()
    {
        $this->load_member();
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();
    }

    private function load_member()
    {
        $this->member = $this->member_class_name::find($this->member_id);
    }

    private function make_payload()
    {
        $this->payload = [
            'prime' => $this->prime,
            'partner_key' => $this->partner_key,
            'merchant_id' => $this->merchant_id,
            'currency' => 'TWD',
            'cardholder' => [
                'phone_number' => $this->member->mobile ?? '',
                'name' => $this->member->name ?? '',
                'email' => $this->member->email ?? '',
                'member_id' => $this->member->id ?? ''
            ],
            'three_domain_secure' => true,
            'result_url' => [
                'frontend_redirect_url' => $this->frontend_redirect_url,
                'backend_notify_url' => $this->backend_bind_card_notify_url
            ]
        ];
    }

    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->update_or_create_member_credit_card();
            $this->data = [
                'payment_url' => $this->response_body_json['payment_url'] ?? '',
                'original_response' => $this->response_body_json
            ];
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
                    'last_four' => $this->response_body_json['card_info']['last_four'],
                    'card_token' => $this->response_body_json['card_secret']['card_token'],
                    'expiry_date' => $this->response_body_json['card_info']['expiry_date'],
                ],[
                    'issuer_zh_tw' => $this->response_body_json['card_info']['issuer_zh_tw'],
                    'bin_code' => $this->response_body_json['card_info']['bin_code'],
                    'card_key' => $this->response_body_json['card_secret']['card_key'],
                    'rec_trade_id' => $this->response_body_json['rec_trade_id'],
                    'state' => false
                ]);
        }
    }
}
