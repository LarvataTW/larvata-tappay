<?php

namespace App\Services\Larvata\Tappay\Payments;

use App\Services\Larvata\Tappay\Payments\Traits\CalledResult;
use App\Services\Larvata\Tappay\Payments\Traits\DeclareProperties;
use App\Services\Larvata\Tappay\Payments\Traits\IsTradeSuccessful;
use App\Services\Larvata\Tappay\Payments\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * 查詢訂單付款結果
 */
class RecordService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    // 交易資料狀態
    const RECORD_STATUS = [
        -1 => '交易錯誤',
        0 => '銀行已授權交易，但尚未請款',
        1 => '交易完成',
        2 => '部分退款',
        3 => '完全退款',
        4 => '待付款',
        5 => '取消交易'
    ];

    /**
     * @param int $order_id 訂單編號
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     */
    public function __construct($order_id, $tappay_partner_key = null)
    {
        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/transaction/query";

        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');

        $this->order_class_name = config('larvata.tappay.order_class_name');
        $this->order_id = $order_id;
    }

    public function call()
    {
        $this->load_order();
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();

    }

    private function make_payload()
    {
        $this->payload = [
            'partner_key' => $this->partner_key,
            'filters' => [
                'rec_trade_id' => $this->order->rec_trade_id ?? ''
            ]
        ];
    }

    /**
     * 呼叫 API 回應交易成功後的作業
     * trade_records 結構文件 https://docs.tappaysdk.com/tutorial/zh/reference.html#trade_records
     */
    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->data = $this->response_body_json['trade_records'];
        }
    }

    private function load_order()
    {
        $this->order = $this->order_class_name::find($this->order_id);
    }
}
