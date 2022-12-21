<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Illuminate\Support\Facades\DB;

/**
 * 會員綁定卡片 - 3D 驗證結果處理類別
 *  建立會員綁定卡片資料
 *  透過 BindCardService 取得 payment_url（3D 驗證頁面 URL），會員完成 3D 驗證作業後，Tappay 會呼叫 callback，
 *  由此回應的成功與否來決定是否新建/更新會員綁定卡片資料。
 * todo 需要有 controller 和 route 設定，來執行此 callback 作業
 */
class BindCardCallbackService
{
    use DeclareProperties, IsTradeSuccessful, CalledResult;

    /**
     * @param string $rec_trade_id Tappay 交易字串
     */
    public function __construct($rec_trade_id)
    {
        $this->rec_trade_id = $rec_trade_id;
        $this->member_credit_card_class_name = config('larvata.tappay.member_credit_card_class_name');
    }

    public function call()
    {
        $this->load_member_credit_card();
        return $this->update_member_credit_card_state();
    }

    private function load_member_credit_card()
    {
        if(class_exists($this->member_credit_card_class_name)) {
            $this->member_credit_card = $this->member_credit_card_class_name::firstWhere('rec_trade_id', $this->rec_trade_id);
        }
    }

    /**
     * 更新會員信用卡資料狀態
     */
    private function update_member_credit_card_state()
    {
        return $this->member_credit_card->update(['state' => true]);
    }
}
