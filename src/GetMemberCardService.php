<?php

namespace Larvata\Tappay;

use Larvata\Tappay\Traits\CalledResult;
use Larvata\Tappay\Traits\DeclareProperties;
use Larvata\Tappay\Traits\IsTradeSuccessful;
use Larvata\Tappay\Traits\SendRequest;
use Illuminate\Support\Facades\DB;

/**
 * 取得會員卡片列表資料
 */
class GetMemberCardService
{
    use DeclareProperties, SendRequest, IsTradeSuccessful, CalledResult;

    public function __construct($member_id, $tappay_partner_key = null, $tappay_merchant_id = null)
    {
        $this->app_url = config('larvata.tappay.app_url');

        $this->host = config('larvata.tappay.host');
        $this->api = "/tpc/direct-pay/get-member-card";

        $this->partner_key = $tappay_partner_key ?? config('larvata.tappay.partner_key');
        $this->merchant_id = $tappay_merchant_id ?? config('larvata.tappay.merchant_id');

        $this->member_class_name = config('larvata.tappay.member_class_name');
        $this->member_id = $member_id;
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
            'partner_key' => $this->partner_key,
            'member_id' => $this->member->id ?? 0,
        ];
    }

    /**
     * 呼叫 API 回應交易成功後的作業
     * cards 結構文件 https://docs.tappaysdk.com/tutorial/zh/advanced.html#response24
     */
    private function after_actions()
    {
        if($this->is_trade_successful()) {
            $this->actions_exception = DB::transaction(function () {
                $this->data = $this->response_body_json['cards'];
            });
        }
    }
}
