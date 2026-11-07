//

import { createApp, h } from 'vue'
import { createInertiaApp, Head , Link} from '@inertiajs/vue3'
const pages = import.meta.glob('./Pages/**/*.vue') 


createInertiaApp({
    title : (title) => `${window.__SITE_NAME__ || 'Brilliant'} - ${title}`,
      resolve: async (name) => {
        const page = await pages[`./Pages/${name}.vue`]()
        if (page.default.layout === undefined) {
          page.default.layout = (await import('./Layout/Default.vue')).default
        }
        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Head', Head)
            .component('Link', Link)
            .mount(el)
    },

})