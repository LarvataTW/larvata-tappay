<?php

namespace App\Services\Larvata\Tappay\Payments;

use App\Services\Larvata\Tappay\Payments\Traits\CalledResult;
use App\Services\Larvata\Tappay\Payments\Traits\DeclareProperties;
use App\Services\Larvata\Tappay\Payments\Traits\IsTradeSuccessful;
use App\Services\Larvata\Tappay\Payments\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * Tappay 退款作業
 */
class RefundService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    private $order_id;
    private $order;

    /**
     * @param $order_id int 訂單編號
     */
    public function __construct($order_id, $refund_amount = 0, $tappay_partner_key = null)
    {
        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/transaction/refund";
        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');

        $this->order_class_name = config('larvata.tappay.order_class_name');
        $this->order_id = $order_id;

        $this->refund_amount = $refund_amount;
    }

    public function call()
    {
        $this->load_order();
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();
    }

    private function load_order()
    {
        $this->order = $this->order_class_name::find($this->order_id);
    }

    private function make_payload()
    {
        $this->payload = [
            'partner_key' => $this->partner_key,
            'rec_trade_id' => $this->order->rec_trade_id ?? '',
            'amount' => $this->refund_amount ?? $this->order->amount ?? 0,
        ];
    }

    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->actions_exception = DB::transaction(function () {
                $this->order->update([
                                         'refund_id' => $this->response_body_json['refund_id'],
                                     ]);
            });
        }
    }
}
