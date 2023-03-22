<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation;

use Illuminate\Support\ServiceProvider;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    const PUBLIC_ASSETS_PATH = 'vendor/gabelbart/nova-geolocation-field';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../dist' => public_path(static::PUBLIC_ASSETS_PATH),
        ], ['laravel-assets']);
        Nova::serving(function (ServingNova $event) {
            Nova::script(
                'geolocation-field',
                public_path(static::PUBLIC_ASSETS_PATH . '/js/field.js')
            );
            Nova::script(
                'geolocation-field-manifest',
                public_path(static::PUBLIC_ASSETS_PATH . '/js/manifest.js')
            );
            Nova::script(
                'geolocation-field-vendor',
                public_path(static::PUBLIC_ASSETS_PATH . '/js/vendor.js')
            );
            Nova::style(
                'geolocation-field',
                public_path(static::PUBLIC_ASSETS_PATH . '/css/field.css')
            );
            Nova::style(
                'geolocation-field-leaflet',
                public_path(static::PUBLIC_ASSETS_PATH . '/css/leaflet.css')
            );
        });
    }

    public function register()
    {
        //
    }
}
