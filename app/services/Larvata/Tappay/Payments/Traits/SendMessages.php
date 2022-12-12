<?php

namespace App\Services\Larvata\Tappay\Payments\Tappay\Traits;

use App\Models\MessageNotify;

trait SendMessages
{
    private function send_messages($title = null)
    {
        if(isset($this->member->mobile)) {
            $action = ['notification_type' => "new_consumption"];
            $data = [
                'title' => $title ?? $this->product_name(),
                'is_schedule' => true,                                    // 是否透過排程發送
                'receive_at' => now(),                                    // 發送時間
                'receive_type' => 4,                                      // 發送類型
                'receive_object' => $this->member->mobile,                // 發送對象
                'type' => 2,                                              // 類型
                'is_top' => 0,                                            // 是否置頂
                'status' => '',                                           // 狀態
                'content' => '',                                          // 公告內容
                'description' => '',                                      // 活動說明
                'plan_id' => $this->plan->id ?? null,                     // 方案編號
                'action' => $action                                       // 推播動作
            ];

            MessageNotify::create($data);
        }
    }
}
