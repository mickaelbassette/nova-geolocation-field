<?php

namespace Gabelbart\Laravel\Nova\Fields\Geolocation;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Geolocation extends Field
{
    use SupportsDependentFields;

    const DEFAULT_LATITUDE_FIELD = 'latitude';
    const DEFAULT_LONGITUDE_FIELD = 'longitude';
    const DEFAULT_LATITUDE = 52.520008;
    const DEFAULT_LONGITUDE = 13.404954;
    const DEFAULT_ZOOM = 10;

    public $component = 'geolocation-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->latitudeField(static::DEFAULT_LATITUDE_FIELD);
        $this->longitudeField(static::DEFAULT_LONGITUDE_FIELD);

        $this->defaultLatitude(static::DEFAULT_LATITUDE);
        $this->defaultLongitude(static::DEFAULT_LONGITUDE);
        $this->defaultZoom(static::DEFAULT_ZOOM);
    }

    public function defaultLatitude(float $latitude): static
    {
        return $this->withMeta([__FUNCTION__ => $latitude]);
    }

    public function defaultLongitude(float $longitude): static
    {
        return $this->withMeta([__FUNCTION__ => $longitude]);
    }

    public function defaultZoom($zoom): static
    {
        return $this->withMeta([__FUNCTION__ => $zoom]);
    }

    public function latitudeField($fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    public function longitudeField($fieldName): static
    {
        return $this->withMeta([__FUNCTION__ => $fieldName]);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $result = json_decode($request->{$requestAttribute}, false);

            $model->{$result->latitudeField} = $this->isValidNullValue($result->latitude)
                ? null
                : $result->latitude;
            $model->{$result->longitudeField} = $this->isValidNullValue($result->longitude)
                ? null
                : $result->longitude;
        }
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
            'latitudeField' => $latitudeField,
            'longitudeField' => $longitudeField,
            'latitude' => !is_null($latitude) ? (float) $latitude : null,
            'longitude' => !is_null($longitude) ? (float) $longitude : null,
        ]);
    }
}
