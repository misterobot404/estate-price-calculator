import "quasar/dist/quasar.prod.css";
import { createApp } from 'vue'
import App from "./App";
import router from './plugins/router'
import store from "./plugins/store";
import {Quasar, Notify} from 'quasar'
import langRu from 'quasar/lang/ru'
import "./plugins/axios"

createApp(App)
    .use(router)
    .use(store)
    .use(Quasar, {
        plugins: {Notify},
        lang: langRu
    })
    .mount('#app')
