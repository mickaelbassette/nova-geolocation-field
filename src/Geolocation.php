<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation;

use Illuminate\Support\Facades\Log;

use Carbon\CarbonInterval;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Geolocation extends Field
{
    use SupportsDependentFields;

    protected static bool $cacheGeocodingResults = true;
    protected static \DateInterval $geocodingCacheTtl;

    const DEFAULT_LATITUDE_FIELD = 'latitude';
    const DEFAULT_LONGITUDE_FIELD = 'longitude';
    const DEFAULT_LATITUDE = 52.520008;
    const DEFAULT_LONGITUDE = 13.404954;
    const DEFAULT_ZOOM = 10;
    const TILE_LAYER_OPENSTREETMAP = [
        'layer-type' => 'base',
        'name' => 'OpenStreetMap',
        'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; <a target="_blank" href="https://osm.org/copyright">OpenStreetMap</a> contributors'
    ];

    const SELECTION_MODE_DBCLICK = 'dbclick';
    const SELECTION_MODE_MOVE = 'move';

    public $component = 'geolocation-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->latitudeField(static::DEFAULT_LATITUDE_FIELD);
        $this->longitudeField(static::DEFAULT_LONGITUDE_FIELD);

        $this->defaultLatitude(static::DEFAULT_LATITUDE);
        $this->defaultLongitude(static::DEFAULT_LONGITUDE);
        $this->defaultZoom(static::DEFAULT_ZOOM);
        $this->tileLayer(static::TILE_LAYER_OPENSTREETMAP);
    }

    public static function disableGeocodingCache(bool $flag = true)
    {
        static::$cacheGeocodingResults = !$flag;
    }
    public static function shouldCacheGeocodingResults(): bool
    {
        return static::$cacheGeocodingResults;
    }
    public static function geocodingCacheTtl(int|\DateInterval $duration)
    {
        static::$geocodingCacheTtl = is_int($duration)
            ? CarbonInterval::minutes($duration)
            : $duration;
    }
    public static function getGeocodingCacheTtl(): \DateInterval
    {
        return !empty(static::$geocodingCacheTtl)
            ? static::$geocodingCacheTtl
            : CarbonInterval::days(7);
    }

    public function defaultLatitude(float $latitude): static
    {
        return $this->withMeta([__FUNCTION__ => $latitude]);
    }

    public function defaultLongitude(float $longitude): static
    {
        return $this->withMeta([__FUNCTION__ => $longitude]);
    }

    public function defaultZoom(int $zoom): static
    {
        return $this->withMeta([__FUNCTION__ => $zoom]);
    }

    public function latitudeField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function longitudeField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function streetField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function streetNumberField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function postalCodeField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function cityField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function countryField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function regionField(string $fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function tileLayer(array $config): static
    {
        return $this->withMeta([__FUNCTION__ => $config]);
    }

    public function selectionMode(string $config): static
    {
        return $this->withMeta([__FUNCTION__ => $config]);
    }

    public function writeBackGeocodedAddress(bool $flag = true): static
    {
        return $this->withMeta([__FUNCTION__ => $flag]);
    }

    public function writeBackGeocodedCountryLongFormat(bool $flag = true): static
    {
        return $this->withMeta([__FUNCTION__ => $flag]);
    }

    public function enableGeocoding(bool $flag = true): static
    {
        if (!class_exists('\Spatie\Geocoder\Facades\Geocoder')) {
            Log::warning('gabelbart/nova-geolocation-field:'
                . ' geocoding was enabled but the spatie/geocoder package is not installed!'
                . ' In order for geocoding to work you need to install it via `composer require spatie/geocoder:^3`!');
        }
        if (empty(config('geocoder.key'))) {
            Log::warning('gabelbart/nova-geolocation-field:'
                . ' geocoding was enabled but no api-key was set for spatie/geocoder!'
                . ' In order for geocoding to work you need to configure an api-key for Googles geocoding API'
                . ' via env variable `GOOGLE_MAPS_GEOCODING_API_KEY` or config `geocoder.key`!');
        }

        return $this->withMeta([__FUNCTION__ => $flag]);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve($resource, $attribute = null)
    {
        $latitudeField = $this->meta['latitudeField'];
        $longitudeField = $this->meta['longitudeField'];

        $latitude = $resource->{$latitudeField};
        $longitude = $resource->{$longitudeField};

        $this->value = json_encode([
            'latitude' => !is_null($latitude) ? (float) $latitude : null,
            'longitude' => !is_null($longitude) ? (float) $longitude : null,
        ]);
    }
}
