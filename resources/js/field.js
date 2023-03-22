import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('IndexGeolocationField', IndexField)
  app.component('DetailGeolocationField', DetailField)
  app.component('FormGeolocationField', FormField)
})
