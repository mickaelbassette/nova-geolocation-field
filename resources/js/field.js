import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-geolocation-field', IndexField)
  app.component('detail-geolocation-field', DetailField)
  app.component('form-geolocation-field', FormField)
})
