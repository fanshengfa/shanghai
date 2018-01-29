<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        Validator::extend('lng', function($attribute, $value, $parameters) {
            if(!is_numeric($value)) {
                return false;
            } elseif($value>180 || $value<-180) {
                return false;
            }
            return true;
        });
        Validator::extend('lat', function($attribute, $value, $parameters) {
            if(!is_numeric($value)) {
                return false;
            } elseif($value>90 || $value<-90) {
                return false;
            }
            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
