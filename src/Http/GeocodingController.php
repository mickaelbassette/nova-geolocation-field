<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation\Http;

use Illuminate\Routing\Controller;

class GeocodingController extends Controller
{
    public function __invoke(GeocodingRequest $request): array
    {
        if (!class_exists('\Spatie\Geocoder\Facades\Geocoder')) {
            return [];
        }

        $address = $request->validated();

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
