<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation;

use Illuminate\Support\ServiceProvider;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('geolocation-field', __DIR__.'/../dist/js/field.js');
            Nova::style('geolocation-field', __DIR__.'/../dist/css/field.css');
        });
    }

    public function register()
    {
        //
    }
}
