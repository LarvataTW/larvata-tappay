<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Illuminate\Support\Facades\DB;

/**
 * 付款作業 - 3D 驗證結果處理類別
 * 依據訂單類型來決定後續作業
 * todo 需要有 controller 和 route 設定，來執行此 callback 作業
 */
class CallbackService
{
    use DeclareProperties, CalledResult;

    public function __construct($status, $rec_trade_id)
    {
        $this->status = $status;
        $this->rec_trade_id = $rec_trade_id;

        $this->order_class_name = config('larvata.tappay.order_class_name');
        $this->payment_success_callback_class_name = config('larvata.tappay.payment_success_callback_class_name');
        $this->payment_failure_callback_class_name = config('larvata.tappay.payment_failure_callback_class_name');
    }

    public function call()
    {
        $this->load_order_datas();
        $this->do_successful_actions();
        $this->do_failed_actions();
        return $this->called_result();
    }

    /**
     * 讀取訂單相關資料
     */
    private function load_order_datas()
    {
        $this->order = $this->order_class_name::firstWhere('rec_trade_id', $this->rec_trade_id);
        if(is_null($this->order)) {
            logger()->error("[Larvata Tappay][付款作業 - 處理 3D 驗證結果] 找不到訂單，交易代碼：{$this->rec_trade_id}");
        }
    }

    /**
     * 交易成功後的處理作業
     */
    private function do_successful_actions()
    {
        if ($this->status === 0) {
            $this->actions_exception = DB::transaction(function () {
                if(isset($this->order)) {
                    (new $this->payment_success_callback_class_name($this->order))->call();
                }
            });
        }
    }

    /**
     * 交易失敗後的處理作業
     */
    private function do_failed_actions()
    {
        if ($this->status !== 0) {
            $this->actions_exception = DB::transaction(function () {
                if(isset($this->order)) {
                    (new $this->payment_failure_callback_class_name($this->order))->call();
                }
            });
        }
    }
}
