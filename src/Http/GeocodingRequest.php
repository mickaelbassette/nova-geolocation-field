<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation\Http;

use Illuminate\Foundation\Http\FormRequest;

class GeocodingRequest extends FormRequest
{
    const STREET = 'street';
    const STREET_NUMBER = 'street_number';
    const POSTAL_CODE = 'postal_code';
    const CITY = 'city';
    const COUNTRY = 'country';
    const REGION = 'region';

    public function rules(): array
    {
        return [
            static::STREET => [
                'string',
                'required',
            ],
            self::STREET_NUMBER => [
                'string',
                'nullable',
            ],
            self::POSTAL_CODE => [
                'string',
                'nullable',
            ],
            self::CITY => [
                'string',
                'required',
            ],
            self::COUNTRY => [
                'string',
                'required',
            ],
            self::REGION => [
                'string',
                'nullable',
            ],
        ];
    }
}
