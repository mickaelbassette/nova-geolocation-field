<template>
  <DefaultField
    class="map-form-field"
    :errors="errors"
    :field="currentField"
    :full-width-content="fullWidthContent"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div
        class="map-form-field__container"
        :class="errorClasses"
      >
        <l-map
          ref="map"
          v-model:zoom="zoom"
          max-zoom="18"
          min-zoom="1"
          @ready="onMapReady"
        >
          <l-tile-layer v-bind="tileLayer" />
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
    },
    panMapToNewValue () {
      this.$refs.map.leafletObject.panTo(this.newValue)
    },
    setCurrentValue (latitude, longitude) {
      this.currentValue = [latitude, longitude]
    },
    setNewValue (latitude, longitude) {
      this.newValue = [latitude, longitude]
    },
    setInitialValue () {
      this.value = this.currentField.value || ''
    },
    fill (formData) {
      const value = JSON.parse(this.currentField.value)
      value.latitude = this.newValue[0]
      value.longitude = this.newValue[1]

      formData.append(this.currentField.attribute, JSON.stringify(value))
    },
  },
}
</script>

<style
  lang="scss"
>
  .map-form-field {
    .map-form-field__container {
      max-width: 100%;
      width: 100%;
      height: 350px;
    }
  }
</style>
