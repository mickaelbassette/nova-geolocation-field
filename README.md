# Nova Geolocation Field

THIS IS A FORK FROM [JACOB FRICKE](https://gitlab.com/jacobfricke/nova-geolocation-field)
COMPATIBLE WITH NOVA 5

This package for [Laravel Nova](https://nova.laravel.com/) provides an input control for selecting geo-coordinates on a map.
 In addition, it's possible to geocode addresses.

The field will not write values on the model. Instead, it will sync with fields for latitude and longitude you need to provide.

## Requirements

* `php: ^8.0`
* `laravel/nova: ^4.0`

## Installation / Getting started

Install the package via Composer:

```
composer require gabelbart/nova-geolocation-field
```

Publish static assets:
```
php  artisan vendor:publish --tag=laravel-assets --ansi
```

Set up your Resource class:

1. Add the `\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation` field
2. Add fields for latitude and longitude

```php
<?php

namespace App\Nova;

use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

use Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation;

class User extends Resource
{
    public static $model = \App\Models\User::class;
    public static $title = 'name';
    public static $search = ['id'];

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Hidden::make('latitude'),
            Hidden::make('longitude'),
            Geolocation::make('Map position'),
        ];
    }

    // snip...
}
```

*Note: you can also use Number fields where the user can see/enter the selected latitude/longitude.
 The sync works in both directions.*

## Usage

### Options

#### `defaultLatitude(float $latitude)`

Set the default latitude for the map center.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->defaultLatitude(52.520008); // Berlin
```

#### `defaultLatitude(float $latitude)`

Set the default latitude for the map center.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->defaultLongitude(13.404954); // Berlin
```

#### `defaultZoom(int $zoom)`

Set the default [zoom](https://wiki.openstreetmap.org/wiki/Zoom_levels) for the map.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->defaultZoom(10);
```

#### `latitudeField(string $fieldName)`

Attribute-name of the latitude field.

```php
\Laravel\Nova\Fields\Number::make('Latitude', 'my_latitude');
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->latitudeField('my_latitude');
```

#### `longitudeField(string $fieldName)`

Attribute-name of the latitude field.

```php
\Laravel\Nova\Fields\Number::make('Longitude', 'my_longitude');
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->longitudeField('my_longitude');
```

#### `streetField(string $fieldName)`

Attribute-name of the street field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->streetField('street');
```

#### `streetNumberField(string $fieldName)`

Attribute-name of the street-number/house-number field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->streetNumberField('street_number');
```

#### `postalCodeField(string $fieldName)`

Attribute-name of the postal-code field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->postalCodeField('postal_code');
```

#### `cityField(string $fieldName)`

Attribute-name of the city field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->cityField('city');
```

#### `countryField(string $fieldName)`

Attribute-name of the country field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->countryField('country');
```

#### `regionField(string $fieldName)`

Attribute-name of the region/state field.

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->regionField('region');
```

#### `selectionMode(string $fieldName)`

Change the selection mode.
 The position on the map can be selected either by moving the mal (`\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::SELECTION_MODE_MOVE`) 
 or by double-clicking the map (`\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::SELECTION_MODE_DBCLICK`).

*`SELECTION_MODE_MOVE` is the default setting.*

```php
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->selectionMode(\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::SELECTION_MODE_DBCLICK);
```

#### `enableGeocoding(bool $flag = true)`

Enable the geocoding feature.
 For the geocoding feature to work you need to install and set up `spatie/geocoder:^3`.
 Also, you need to provide at least the `streetField`, `cityField` and `countryField` option. 

```php
\String::make('Street');
\String::make('City');
\String::make('Country');
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->cityField('street')
  ->cityField('city')
  ->cityField('country')
  ->enableGeocoding();
```

#### `writeBackGeocodedAddress(bool $flag = true)`

This option requires configured address fields and enabled geocoding.
 When a geocoded address is selected, the values for the address components get written back to the input fields. 

```php
\String::make('Street');
\String::make('Street Number');
\String::make('City');
\String::make('Zip');
\String::make('Country');
\String::make('Region');
\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::make('Map position')
  ->writeBackGeocodedAddress()
  ->cityField('street')
  ->cityField('street_number')
  ->cityField('city')
  ->postalCodeField('zip')
  ->cityField('country')
  ->regionField('region')
  ->enableGeocoding();
```

### Change caching of geocoding results

By default, geocoding requests will be cached for 7 days.
 Each time the cache is hit again this period is refreshed.

#### Disable caching

Call the `\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::disableGeocodingCache` static method in your service provider to disable the cache.
 This will result in a higher load on the Google geocoding API thus probably costs will increase.

#### Change cache duration

To change the TTL of the geocoding cache call the `\Gabelbart\Laravel\Nova\Fields\Geolocation\Geolocation::geocodingCacheTtl` static method in your service provider.
 It accepts either a time in minutes or a valid `\DateInterval` instance. 
