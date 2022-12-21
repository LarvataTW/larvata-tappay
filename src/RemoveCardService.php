<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Larvata\Tappay\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * 移除會員卡片資料
 */
class RemoveCardService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    /**
     * @param int $member_id 會員編號
     * @param int $member_credit_card_id 會員信用卡編號
     * @param string $tappay_partner_key Tappay partner key Tappay 金鑰
     */
    public function __construct($member_id, $member_credit_card_id, $tappay_partner_key = null)
    {
        $this->host = config('larvata.tappay.host');
        $this->api = '/tpc/card/remove';
        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');

        $this->member_class_name = config('larvata.tappay.member_class_name');
        $this->member_id = $member_id;

        $this->member_credit_card_class_name = config('larvata.tappay.member_credit_card_class_name');
        $this->member_credit_card_id = $member_credit_card_id;
    }

    public function call()
    {
        $this->load_member_credit_card();
        $this->make_payload();
        $this->send_request();
        $this->after_actions();
        return $this->called_result();
    }

    private function load_member_credit_card()
    {
        $this->member_credit_card = $this->member_credit_card_class_name::query()
            ->where('member_id', $this->member_id)
            ->where('id', $this->member_credit_card_id)
            ->first();
    }

    private function make_payload()
    {
        $this->payload = [
            'partner_key' => $this->partner_key,
            'card_token' => $this->member_credit_card->card_token ?? '',
            'card_key' => $this->member_credit_card->card_key ?? '',
        ];
    }

    /**
     * 呼叫 API 回應交易成功後的作業
     */
    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->actions_exception = DB::transaction(function () {
                $this->remove_member_credit_card();
            });
        }
    }

    /**
     * 移除會員卡片資料
     */
    private function remove_member_credit_card()
    {
        $this->member_credit_card->delete();
    }
}
