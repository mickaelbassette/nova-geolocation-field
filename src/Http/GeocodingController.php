<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation\Http;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

use Carbon\CarbonInterval;

use Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation;

class GeocodingController extends Controller
{
    public function __invoke(GeocodingRequest $request): array
    {
        if (!class_exists('\Spatie\Geocoder\Facades\Geocoder')) {
            return [];
        }

        $address = $request->validated();

        $cacheKey = array_filter(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $address
        ));
        ksort($cacheKey);
        $cacheKey = static::class . ":" . hash('md5', json_encode($cacheKey));

        if (Geolocation::shouldCacheGeocodingResults()) {
            $result = Cache::get($cacheKey, fn () => $this->performGeocoding($request, $address));
            // Touches the cache everytime its hit
            Cache::set($cacheKey, Geolocation::getGeocodingCacheTtl());

            return $result;
        } else {
            return $this->performGeocoding($request, $address);
        }
    }

    protected function performGeocoding(GeocodingRequest $request, array $address): array
    {
        $addressString = $address[GeocodingRequest::STREET];
        if ($request->has(GeocodingRequest::STREET_NUMBER)
            && !empty($address[GeocodingRequest::STREET_NUMBER])
        ) {
            $addressString .= " {$address[GeocodingRequest::STREET_NUMBER]}";
        }
        if ($request->has(GeocodingRequest::POSTAL_CODE)
            && !empty($address[GeocodingRequest::POSTAL_CODE])
        ) {
            $addressString .= ", {$address[GeocodingRequest::POSTAL_CODE]}";
        } else {
            $addressString .= ",";
        }
        $addressString .= " {$address[GeocodingRequest::CITY]}, {$address[GeocodingRequest::COUNTRY]}";
        if ($request->has(GeocodingRequest::REGION)
            && !empty($address[GeocodingRequest::REGION])
        ) {
            $addressString .= " {$address[GeocodingRequest::REGION]}";
        }

        return \Spatie\Geocoder\Facades\Geocoder::getAllCoordinatesForAddress($addressString);
    }
}
