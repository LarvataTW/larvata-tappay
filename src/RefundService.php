<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Larvata\Tappay\Traits\SendRequest;

/**
 * Tappay 退款作業
 */
class RefundService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    /**
     * @param $rec_trade_id string Tappay 交易字串
     * @param $refund_amount int 退款金額
     * @param $tappay_partner_key string Tappay partner key
     */
    public function __construct($rec_trade_id, $refund_amount = 0, $tappay_partner_key = null)
    {
        $this->host = config('tappay.host');
        $this->api = "/tpc/transaction/refund";
        $this->rec_trade_id = $rec_trade_id;
        $this->partner_key = $tappay_partner_key ?? config('tappay.partner_key');
        $this->refund_amount = $refund_amount;
    }

    public function call()
    {
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();
    }

    private function make_payload()
    {
        $this->payload = [
            'partner_key' => $this->partner_key,
            'rec_trade_id' => $this->rec_trade_id ?? '',
            'amount' => $this->refund_amount ?? $this->order->amount ?? 0,
        ];
    }

    private function after_actions()
    {
        if($this->is_trade_successful()) {
            // 設定要回傳的 data 資料結構
            $this->data = [
                'refund_id' => $this->response_body_json['refund_id']
            ];
        }
    }
}
