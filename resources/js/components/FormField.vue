<template>
  <DefaultField
    class="geolocation-form-field"
    :errors="errors"
    :field="currentField"
    :full-width-content="fullWidthContent"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div
        class="geolocation-form-field__container"
        :class="errorClasses"
      >
        <l-map
          ref="map"
          v-model:zoom="zoom"
          :max-zoom="18"
          :min-zoom="1"
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

const ADDRESS_FIELD_PROPS = Object.seal([
  'streetField',
  'streetNumberField',
  'postalCodeField',
  'cityField',
  'countryField',
  'regionField',
]);

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
  data: () => ({
    bounds: null,
    zoom: 10,
    currentValue: null,
    newValue: null,
    value: null,
  }),
  computed: {
    cHasCurrentValue () {
      return this.value?.latitude !== null
        && this.value?.longitude !== null
        && !isNaN(this.value?.latitude)
        && !isNaN(this.value?.longitude)
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
    }
  },
  created () {
    this.registerFieldChangeListeners()
  },
  mounted () {
    this.value = JSON.parse(this.currentField.value)

    if (this.cHasCurrentValue) {
      this.setCurrentValue(this.value.latitude, this.value.longitude)
      this.setNewValue(this.value.latitude, this.value.longitude)
    } else {
      this.setNewValue(this.currentField.defaultLatitude, this.currentField.defaultLongitude)
    }

    this.zoom = this.field.defaultZoom
  },
  methods: {
    onUpdateBounds (bounds) {
      this.bounds = bounds
    },
    registerFieldChangeListeners () {
      for (const property in ADDRESS_FIELD_PROPS) {
        if (this.currentField[property]) {
          Nova.$on(
            this.getFieldAttributeChangeEventName(this.currentField[property]),
            value => this.onChangeAddressProperty(property, value)
          )
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

    },
    onChangeLatitude (value) {
      this.setNewLatitude(value)
    },
    onChangeLongitude (value) {
      this.setNewLongitude(value)
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
        event => this.onUpdateCenter(event.target.getCenter())
      )
    },
    onUpdateCenter (center) {
      this.setNewValue(center.lat, center.lng)
      this.emitNewValue(center.lat, center.lng)
    },
    panMapToNewValue () {
      this.$refs.map.leafletObject.panTo(this.newValue)
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
