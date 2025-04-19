import store from './store';
import './assets/main.css'
import Aura from '@primeuix/themes/aura';
import {createApp} from 'vue'
import App from './App.vue'
import router from './router';
import PrimeVue from 'primevue/config';
import {definePreset} from "@primeuix/themes";
import 'primeflex/primeflex.css';
import 'primeicons/primeicons.css'
import {ToastService} from "primevue";


const color = 'teal'
const MyPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: `{${color}.50}`,
            100: `{${color}.100}`,
            200: `{${color}.200}`,
            300: `{${color}.300}`,
            400: `{${color}.400}`,
            500: `{${color}.500}`,
            600: `{${color}.600}`,
            700: `{${color}.700}`,
            800: `{${color}.800}`,
            900: `{${color}.900}`,
            950: `{${color}.950}`
        }
    }
});

const app = createApp(App);
app.use(PrimeVue, {
    theme: {
        preset: MyPreset,
        options: {
            darkModeSelector: '.my-app-dark',
        },
        icons: true,
    },
    icons: true,
});
app.use(ToastService)
app.use(router);
app.use(store)
app.mount('#app');

