import "quasar/dist/quasar.prod.css";

import { createApp } from 'vue'
import App from "./App";
import router from './router'
import {Quasar, Notify} from 'quasar'
import langRu from 'quasar/lang/ru'

createApp(App)
    .use(router)
    .use(Quasar, {
        plugins: {Notify},
        lang: langRu
    })
    .mount('#app')
