<?php

namespace App\Services\Larvata\Tappay\Payments\Traits;

trait CalledResult
{
    /**
     * 呼叫此服務類別的結果
     */
    private function called_result()
    {
        $result = [
            'success' => true,
            'message' => $this->message ?? ''
        ];

        if(isset($this->data)) {
            $result['data'] = $this->data;
        }

        Logger()->info($this->actions_exception);
        if(isset($this->actions_exception) && !is_null($this->actions_exception)) {
            $result['success'] = false;
            $result['message'] = '呼叫 API 回應交易成功後的作業，執行失敗（' . $this->actions_exception->getMessage() . '）';
        }

        if(isset($this->response)) {
            if($this->response->successful()) {
                if(!$this->is_trade_successful()) {
                    $result['success'] = false;
                    $result['message'] = '呼叫 API 回應交易失敗（' . $this->response_body_json['status'] . ':' . $this->response_body_json['msg'] . '）';
                }
            } else {
                $result['success'] = false;
                $result['message'] = '呼叫 API 發生錯誤（' . $this->response->status() . ':' . json_encode($this->response_body_json) . '）';
            }
        }

        if(isset($this->validate_result) && !$this->validate_result['success']) {
            $result['success'] = false;
            $result['message'] = '檢查作業不正確（' . $this->validate_result['message'] . '）';
        }

        logger()->info($this::class . ' 執行' .
                        ($result['success'] ? '成功' : '失敗') .
                        $result['message']);

        return $result;
    }
}
