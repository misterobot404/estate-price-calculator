import "quasar/dist/quasar.prod.css";

import { createApp } from 'vue'
import App from "./App";
import router from './router'
import {Quasar} from 'quasar'
import langRu from 'quasar/lang/ru'

createApp(App)
    .use(router)
    .use(Quasar, {lang: langRu})
    .mount('#app')
