let mix = require('laravel-mix')

require('./nova.mix')

const resourceRoot = '/vendor/gabelbart/nova-geolocation-field/'

mix
  .setPublicPath('dist')
  .setResourceRoot(resourceRoot)
  .webpackConfig({
    output: {
      publicPath: resourceRoot,
    },
  })
  .js('resources/js/field.js', 'js')
  .vue({
    extractStyles: true,
    version: 3,
  })
  .css('resources/css/field.css', 'css')
  .css('./node_modules/leaflet/dist/leaflet.css', 'css')
  .extract()
  .copy(
    './node_modules/leaflet/dist/images',
    './dist/leaflet/dist/images'
  )
  .nova('gabelbart/geolocation-field')
