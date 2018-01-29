<?php

namespace App\Fcore\Facades;

use Illuminate\Support\Facades\Facade;

class Fast extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Fcore\Fast::class;
    }
}
