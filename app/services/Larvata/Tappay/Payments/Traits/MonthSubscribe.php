<?php

namespace App\Services\Larvata\Tappay\Payments\Tappay\Traits;

use App\Models\Membership;

trait MonthSubscribe
{
    /**
     *  月訂閱期數
     */
    private function month_subscribe_periods_qty()
    {
        $periods = $this->load_periods();
        return $periods->max();
    }

    private function load_periods()
    {

//        return Membership::where('member_id', $this->member->id)
//            ->where('plan_id', $this->plan->id ?? '')
//            ->orderBy('order_id', 'asc')
//            ->pluck('order_id');

        return Membership::where('member_id', $this->member->id)
            ->where('plan_id', $this->plan->id ?? '')
            ->orderBy('order_id', 'asc')
            ->pluck('period');
    }
}
