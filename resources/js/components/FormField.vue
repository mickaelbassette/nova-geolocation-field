<template>
  <DefaultField
    class="geolocation-form-field"
    :errors="errors"
    :field="currentField"
    full-width-content
    :show-help-text="showHelpText"
  >
    <template #field>
      <div>
        <Card
          v-for="result in cGeocodingResults"
          :key="result.place_id"
          class="mb-2 px-3 py-2 flex justify-between items-center bg-gray-200 dark:bg-gray-700"
        >
          <div class="text-lg">
            {{ result.formatted_address }}
          </div>
          <div>
            <DefaultButton
              @click.stop.prevent="onClickSelectAddress(result)"
            >{{ __('nova_geolocation_field.select_geocoded_address') }}</DefaultButton>
          </div>
        </Card>
      </div>
      <div
        class="geolocation-form-field__container"
        :class="errorClasses"
      >
        <l-map
          ref="map"
          v-model:zoom="zoom"
          :max-zoom="18"
          :min-zoom="1"
          :options="{ doubleClickZoom: false }"
          @ready="onMapReady"
          @update:bounds="onUpdateBounds"
        >
          <l-tile-layer v-bind="currentField.tileLayer" />
          <l-marker
            v-if="cHasCurrentValue"
            :lat-lng="currentValue"
            :options="{ opacity: 0.5 }"
          />
          <l-marker
            v-if="cHasNewValue"
            :lat-lng="newValue"
          />
        </l-map>
      </div>
      <p
        v-if="hasError"
        class="my-2 text-danger"
      >
        {{ firstError }}
      </p>
    </template>
  </DefaultField>
</template>

<script>
import {
  DependentFormField,
  HandlesValidationErrors,
} from 'laravel-nova'

import {
  LMap,
  LMarker,
  LTileLayer,
} from '@vue-leaflet/vue-leaflet'

import {
  useMapUtils,
} from './useMapUtils'

const ADDRESS_FIELD_PROPS = Object.seal([
  'streetField',
  'streetNumberField',
  'postalCodeField',
  'cityField',
  'countryField',
  'regionField',
])

export default {
  components: {
    LMap,
    LMarker,
    LTileLayer,
  },
  mixins: [
    DependentFormField,
    HandlesValidationErrors,
  ],
  props: {
    resourceName: {
      type: String,
      required: true,
    },
    resourceId: {
      type: String,
      required: true,
    },
    field: {
      type: Object,
      required: true,
    },
  },
  setup: (props, context) => ({
    ...useMapUtils(props, context),
  }),
  data: () => ({
    bounds: null,
    zoom: 10,
    currentValue: null,
    newValue: null,
    value: null,
    address: {},
    ignoreAddressChanges: false,
    geocoding: {
      timeout: null,
      loading: false,
      cache: {},
      result: [],
    },
  }),
  computed: {
    cGeocodingResults () {
      return this.geocoding.result?.filter(result => !((
        this.cHasNewValue
        && result.lat === this.newValue[0]
        && result.lng === this.newValue[1]
      ) || (
        !this.cHasNewValue
        && this.cHasCurrentValue
        && result.lat === this.value.latitude
        && result.lng === this.value.longitude
      ))) ?? []
    },
    cResource () {
      const result = {}

      let parent = this.$parent
      while (parent.$parent && !parent.$data.fields) {
        parent = parent.$parent
      }

      if (parent.$data.fields) {
        for (const field of Object.values(parent.$data.fields)) {
          result[field.attribute] = field.value
        }
      }

      return result
    },
    cDebounce () {
      return Nova.config('debounce')
    },
    cHasRequiredAddressComponents () {
      const propertyExists = property => !!this.address[property]

      return propertyExists('street')
        && propertyExists('city')
        && propertyExists('country')
    },
    cSelectViaMove () {
      return !this.field.selectionMode
        || this.field.selectionMode === 'move'
    },
    cSelectViaDbclick () {
      return this.field.selectionMode
        && this.field.selectionMode === 'dbclick'
    },
    cHasCurrentValue () {
      return this.value?.latitude !== null
        && this.value?.longitude !== null
        && !isNaN(this.value?.latitude)
        && !isNaN(this.value?.longitude)
    },
    cHasNewValue () {
      return this.newValue?.[0] !== null
        && this.newValue?.[1] !== null
        && !isNaN(this.newValue?.[0])
        && !isNaN(this.newValue?.[1])
    },
    cNewValueIsWithinBounds () {
      if (!this.newValue || !this.bounds) {
        return true
      }

      const latitude = this.newValue[0]
      const longitude = this.newValue[1]

      return latitude > this.bounds._southWest.lat
        && latitude < this.bounds._northEast.lat
        && longitude > this.bounds._southWest.lng
        && longitude < this.bounds._northEast.lng
    },
  },
  created () {
    this.registerFieldChangeListeners()
  },
  mounted () {
    this.value = JSON.parse(this.currentField.value)

    if (this.cHasCurrentValue) {
      this.setCurrentValue(this.value.latitude, this.value.longitude)
      this.setNewValue(this.value.latitude, this.value.longitude)
    }

    this.zoom = this.field.defaultZoom
  },
  beforeUnmount () {
    this.endObservingMapVisibility()
  },
  methods: {
    onClickSelectAddress (result) {
      this.setNewValue(result.lat, result.lng)
      this.emitNewValue(result.lat, result.lng)
      if (this.currentField.writeBackGeocodedAddress) {
        this.writeBackGeocodedAddress(result)
      }
    },
    onUpdateBounds (bounds) {
      this.bounds = bounds
    },
    registerFieldChangeListeners () {
      for (const property of ADDRESS_FIELD_PROPS) {
        if (this.currentField[property]) {
          Nova.$on(
            this.getFieldAttributeChangeEventName(this.currentField[property]),
            value => this.onChangeAddressProperty(property, value)
          )
          this.onChangeAddressProperty(property, this.cResource[this.currentField[property]])
        }
      }

      Nova.$on(
        this.getFieldAttributeChangeEventName(this.currentField.latitudeField),
        value => this.onChangeLatitude(value)
      )
      Nova.$on(
        this.getFieldAttributeChangeEventName(this.currentField.longitudeField),
        value => this.onChangeLongitude(value)
      )
    },
    onChangeAddressProperty (property, value) {
      switch (property) {
        case 'streetField':
          this.address.street = value
          break
        case 'streetNumberField':
          this.address.street_number = value
          break
        case 'postalCodeField':
          this.address.postal_code = value
          break
        case 'cityField':
          this.address.city = value
          break
        case 'countryField':
          this.address.country = value
          break
        case 'regionField':
          this.address.region = value
          break
      }

      if (!this.ignoreAddressChanges
        && this.cHasRequiredAddressComponents
        && this.currentField.enableGeocoding
      ) {
        this.geocodeDebounced()
      }
    },
    onChangeLatitude (value) {
      this.setNewLatitude(value)
    },
    onChangeLongitude (value) {
      this.setNewLongitude(value)
    },
    cancelDebouncedGeocoding () {
      if (this.geocoding.timeout) {
        clearTimeout(this.geocoding.timeout)
      }

      this.geocoding.timeout = null
    },
    geocodeDebounced () {
      this.cancelDebouncedGeocoding()
      this.geocoding.timeout = setTimeout(() => this.geocode(), this.cDebounce)
    },
    async geocode () {
      this.geocoding.loading = true
      const address = JSON.parse(JSON.stringify(this.address))

      const cacheKey = JSON.stringify(address)

      if (!this.geocoding.cache[cacheKey]) {
        try {
          const response = await Nova.request().post('/nova-vendor/gabelbart/geolocation-field/geocode', this.address)
          this.geocoding.cache[cacheKey] = response.data
        } catch (e) {
          this.geocoding.cache[cacheKey] = []
        } finally {
          this.geocoding.loading = false
        }
      }

      this.geocoding.result = this.geocoding.cache[cacheKey]

      return this.geocoding.result
    },
    listenToValueChanges (value) {
      this.setNewValue(value.latitude, value.longitude)
      this.panMapToNewValue()
    },
    onMapReady (map) {
      const center = this.cHasCurrentValue
        ? [this.value.latitude, this.value.longitude]
        : [this.currentField.defaultLatitude, this.currentField.defaultLongitude]
      map.panTo(center)
      map.addEventListener(
        'move',
        event => this.onMoveMap(event.target.getCenter())
      )
      map.addEventListener(
        'dblclick',
        event => this.onDbclickMap(event)
      )
      map.setZoom(this.currentField.defaultZoom)

      this.startObservingMapVisibility(this.$refs.map)
    },
    onMoveMap (center) {
      if (this.cSelectViaMove) {
        this.setNewValue(center.lat, center.lng)
        this.emitNewValue(center.lat, center.lng)
      }
    },
    onDbclickMap (event) {
      if (this.cSelectViaDbclick) {
        const position = event.latlng
        this.setNewValue(position.lat, position.lng)
        this.emitNewValue(position.lat, position.lng)
      }
    },
    panMapToNewValue () {
      if (this.cHasNewValue) {
        this.$refs.map.leafletObject.panTo(this.newValue)
      }
    },
    setCurrentValue (latitude, longitude) {
      this.currentValue = [latitude, longitude]
    },
    emitNewValue (latitude, longitude) {
      Nova.$emit(
        this.getFieldAttributeValueEventName(this.currentField.latitudeField),
        latitude
      )
      Nova.$emit(
        this.getFieldAttributeValueEventName(this.currentField.longitudeField),
        longitude
      )
    },
    emitAddressComponent (prop, value) {
      const attribute = this.currentField[prop]
      if (attribute) {
        Nova.$emit(
          this.getFieldAttributeValueEventName(attribute),
          value
        )
      }
    },
    setNewValue (latitude, longitude) {
      this.newValue = [latitude, longitude]
      if (!this.cNewValueIsWithinBounds) {
        this.panMapToNewValue()
      }
    },
    setNewLatitude (latitude) {
      this.setNewValue(latitude, this.newValue?.[1] ?? this.defaultLongitude)
    },
    setNewLongitude (longitude) {
      this.setNewValue(this.newValue?.[0] ?? this.defaultLatitude, longitude)
    },
    setInitialValue () {
      this.value = this.currentField.value || ''
    },
    fill (formData) {
    },
    writeBackGeocodedAddress (result) {
      this.ignoreAddressChanges = true

      for (const component of result.address_components) {
        if (component.types.includes('street_number')) {
          this.address.street_number = component.long_name
          this.emitAddressComponent('streetNumberField', component.long_name)
        } else if (component.types.includes('route')) {
          this.address.street = component.long_name
          this.emitAddressComponent('streetField', component.long_name)
        } else if (component.types.includes('locality')) {
          this.address.city = component.long_name
          this.emitAddressComponent('cityField', component.long_name)
        } else if (component.types.includes('administrative_area_level_1')) {
          this.address.region = component.long_name
          this.emitAddressComponent('regionField', component.long_name)
        } else if (component.types.includes('country')) {
          const value = this.writeBackGeocodedCountryLongFormat ? component.long_name : component.short_name

          this.address.country = value
          this.emitAddressComponent('countryField', value)
        } else if (component.types.includes('postal_code')) {
          this.address.postal_code = component.long_name
          this.emitAddressComponent('postalCodeField', component.long_name)
        }
      }

      this.ignoreAddressChanges = false
    },
  },
}
</script>

<style
  lang="scss"
>
  .geolocation-form-field {
    .geolocation-form-field__container {
      max-width: 100%;
      width: 100%;
      height: 350px;
    }
  }
</style>
