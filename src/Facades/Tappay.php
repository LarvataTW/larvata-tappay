<?php

namespace Larvata\Tappay\Facades;

use Illuminate\Support\Facades\Facade;

class Tappay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tappay';
    }
}
