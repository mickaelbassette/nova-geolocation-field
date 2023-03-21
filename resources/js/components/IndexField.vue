<template>
  <span class="flex items-center">
    <Icon
      class="mr-2"
      type="globe-alt"
    />
    <span v-if="fieldValue">{{ fieldValue }}</span>
    <span v-else>&mdash;</span>
  </span>
</template>

<script>
export default {
  props: {
    resourceName: {
      type: String,
      required: true,
    },
    field: {
      type: Object,
      required: true,
    },
  },

  computed: {
    fieldValue () {
      if (this.field.displayedAs) {
        return this.field.displayedAs
      }

      const {
        latitude,
        longitude,
      } = JSON.parse(this.field.value)

      const precision = 6

      return latitude !== null && longitude !== null
        ? `${parseFloat(latitude).toFixed(precision)}, ${parseFloat(longitude).toFixed(precision)}`
        : null
    },
  },
}
</script>
