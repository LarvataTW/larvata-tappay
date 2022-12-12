<?php

namespace App\Services\Larvata\Tappay\Payments\Tappay\Traits;

trait IsTradeSuccessful
{
    /**
     * 判斷 API 回應的交易代碼是否成功
     */
    private function is_trade_successful()
    {
        return $this->response->successful() &&
            array_key_exists('status', $this->response_body_json) &&
            $this->response_body_json['status'] === 0; // 交易代碼，成功的話為0
    }
}
