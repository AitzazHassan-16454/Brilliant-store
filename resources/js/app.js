//

import { createApp, h } from 'vue'
import { createInertiaApp, Head , Link} from '@inertiajs/vue3'
const pages = import.meta.glob('./Pages/**/*.vue') 


createInertiaApp({
    title : (title) => `Brilliant - ${title}`,
      resolve: async (name) => {
        const page = await pages[`./Pages/${name}.vue`]()
         page.default.layout =
        page.default.layout ||
        (await import('./Layout/Default.vue')).default
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