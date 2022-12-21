<?php

namespace Larvata\Tappay\Traits;

use Illuminate\Support\Facades\Http;

trait SendRequest
{
    /**
     * 發送 HTTP POST request 到 Tappay
     */
    private function send_request()
    {
        logger()->info('[呼叫 Tappay API] ' . $this->host.$this->api . ' with payload（' . json_encode($this->payload) . '）' );
        $this->response = Http::timeout(30)
            ->withHeaders([
                              'x-api-key' => $this->partner_key,
                              'Content-Type' => 'application/json'
                          ])->post($this->host.$this->api, $this->payload);

        $this->response_body_json = json_decode($this->response->body(), TRUE);
        logger()->info('[呼叫 Tappay API]  回應結果：' . json_encode($this->response_body_json));
    }
}
