<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    const PUBLIC_ASSETS_PATH = 'vendor/gabelbart/nova-geolocation-field';

    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
            $this->locales();
        });

        $this->publishes([
            __DIR__ . '/../dist' => public_path(static::PUBLIC_ASSETS_PATH),
        ], ['laravel-assets']);
        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path(static::PUBLIC_ASSETS_PATH),
        ], ['nova-geolocation-field-lang']);
        Nova::serving(function (ServingNova $event) {
            $this->novaLocales();
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

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/gabelbart/geolocation-field')
            ->group(__DIR__.'/../routes/api.php');
    }

    protected function locales()
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang');
        $this->loadJsonTranslationsFrom(lang_path(static::PUBLIC_ASSETS_PATH));
    }

    protected function novaLocales()
    {
        $keys = [
            'nova_geolocation_field.select_geocoded_address'
        ];

        $lang = array_combine(
            $keys,
            array_map(fn ($key) => __($key), $keys)
        );

        Nova::translations($lang);
    }
}
