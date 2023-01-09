<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Larvata\Tappay\Traits\SendRequest;
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
     * @param string $rec_trade_id 交易識別碼
     * @param string $order_number 訂單編號
     * @param string $bank_transaction_id 銀行端的訂單編號
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     */
    public function __construct($rec_trade_id, $order_number, $bank_transaction_id, $tappay_partner_key = null)
    {
        $this->host = config('tappay.host');
        $this->api = "/tpc/transaction/query";

        $this->rec_trade_id = $rec_trade_id;
        $this->order_number = $order_number;
        $this->bank_transaction_id = $bank_transaction_id;
        $this->partner_key = $tappay_partner_key ?? config('tappay.partner_key');
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
                'rec_trade_id' => $this->rec_trade_id ?? '',
                'order_number' => $this->order_number ?? '',
                'bank_transaction_id' => $this->bank_transaction_id ?? ''
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
