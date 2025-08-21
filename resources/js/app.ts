/* resources/js/app.ts */
import 'leaflet/dist/leaflet.css'
import '../css/app.css'

import { createInertiaApp }    from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h }         from 'vue'
import type { DefineComponent } from 'vue'
import { ZiggyVue }             from 'ziggy-js'
import { initializeTheme }      from './composables/useAppearance'

import Toast, { POSITION }      from 'vue-toastification'
import 'vue-toastification/dist/index.css'

import L from 'leaflet'
import markerIcon2x  from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon    from 'leaflet/dist/images/marker-icon.png'
import markerShadow  from 'leaflet/dist/images/marker-shadow.png'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title  : title => (title ? `${title} - ${appName}` : appName),
  resolve: name  =>
    resolvePageComponent(
      `./pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./pages/**/*.vue')
    ),

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(Toast, {
        position  : POSITION.TOP_CENTER,
        timeout   : 3000,
        draggable : false,
        showCloseButtonOnHover: true,
        hideProgressBar: true,
      })
      .mount(el)
  },

  progress: { color: '#4B5563' },
})

L.Icon.Default.mergeOptions({
  iconRetinaUrl : markerIcon2x,
  iconUrl       : markerIcon,
  shadowUrl     : markerShadow,
})


initializeTheme()   // keep this one-liner
