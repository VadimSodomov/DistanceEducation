import './styles/app.css';
import 'typeface-roboto';

import { createApp } from 'vue';
import Auth from "./pages/Auth.vue";

if (document.getElementById('auth-page')) {
  createApp(Auth).mount('#auth-page');
}