<?php

namespace Bale\GupaPanel\Facades;

use Illuminate\Support\Facades\Facade;

class GupaPanel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Bale\GupaPanel\GupaPanel::class;
    }
}
