<?php

namespace App\Services\Larvata\Tappay\Payments;

use App\Services\Larvata\Tappay\Payments\Traits\CalledResult;
use App\Services\Larvata\Tappay\Payments\Traits\DeclareProperties;
use App\Services\Larvata\Tappay\Payments\Traits\IsTradeSuccessful;
use App\Services\Larvata\Tappay\Payments\Traits\SendMessages;
use App\Services\Larvata\Tappay\Payments\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * 定期扣款作業
 */
class PayByCardTokenService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult, SendMessages;

    /**
     * @param int $order_id 訂單編號
     * @param boolean $three_domain_secure 是否使用 3D 驗證方式付款，預設為 true
     * @param string $details 交易品項內容，varchar(100)
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     * @param string $tappay_partner_key Tappay merchant id Tappay 店家編號
     */
    public function __construct($order_id, $details, $three_domain_secure = true, $tappay_partner_key = null, $tappay_merchant_id = null)
    {
        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/payment/pay-by-token";

        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');
        $this->merchant_id = $tappay_merchant_id ?? config('larvata.tappay.merchant_id');

        $this->three_domain_secure = $three_domain_secure;

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
        $this->member_credit_card = $this->order->member_credit_card;
    }

    private function make_payload()
    {
        $this->payload = [
            'card_key' => $this->member_credit_card->card_key ?? '',
            'card_token' => $this->member_credit_card->card_token ?? '',
            'partner_key' => $this->partner_key,
            'merchant_id' => $this->merchant_id,
            'amount' => $this->order->fee ?? $this->order->amount ?? 0,
            'currency' => strtoupper($this->order->currency ?? 'twd'),
            'order_number' => $this->order->order_number ?? '',
            'details' => $this->details,
            'three_domain_secure' => $this->three_domain_secure,
            'result_url' => [
                'frontend_redirect_url' => $this->frontend_redirect_url,
                'backend_notify_url' => $this->backend_payment_notify_url
            ]
        ];
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
                    (new $this->payment_success_callback_class_name($this->order))->call();
                }
            });
        } else {
            (new $this->payment_failure_callback_class_name($this->order))->call();
        }
    }
}
