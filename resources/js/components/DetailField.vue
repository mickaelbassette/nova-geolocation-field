<template>
  <PanelItem
    class="geolocation-detail-field"
    :field="field"
    :index="index"
  >
    <template #value>
      <div class="geolocation-detail-field__container">
        <l-map
          ref="map"
          v-model:zoom="zoom"
          :max-zoom="18"
          :min-zoom="1"
          @ready="onMapReady"
          @update:center="onUpdateCenter"
        >
          <l-tile-layer v-bind="field.tileLayer" />
          <l-marker
            v-if="cHasValue"
            :lat-lng="marker"
          />
        </l-map>
      </div>
    </template>
  </PanelItem>
</template>

<script>
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
  props: {
    index: {
      type: [Number, String],
      required: true,
    },
    resource: {
      type: Object,
      required: true,
    },
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
    marker: null,
    value: null,
  }),
  computed: {
    cHasValue () {
      return this.value.latitude !== null
        && this.value.longitude !== null
        && !isNaN(this.value.latitude)
        && !isNaN(this.value.longitude)
    },
  },
  mounted () {
    this.value = JSON.parse(this.field.value)

    if (this.cHasValue) {
      this.setMarker(this.value.latitude, this.value.longitude)
    }

    this.zoom = this.field.defaultZoom
  },
  methods: {
    onMapReady (map) {
      const center = this.cHasValue
        ? [this.value.latitude, this.value.longitude]
        : [this.field.defaultLatitude, this.field.defaultLongitude]
      map.panTo(center)
    },
    onUpdateCenter () {
      if (this.cHasValue) {
        this.padMapToMarker()
      }
    },
    setMarker (latitude, longitude) {
      this.marker = [latitude, longitude]
    },
    padMapToMarker: function () {
      this.$refs.map.leafletObject.panInside(this.marker, {
        paddingTopLeft: [25, 50],
        paddingBottomRight: [25, 25],
      })
    },
  },
}
</script>

<style
  lang="scss"
>
.geolocation-detail-field {
  .geolocation-detail-field__container {
    max-width: 100%;
    width: 100%;
    height: 350px;
  }
}
</style>
