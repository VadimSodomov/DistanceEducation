import './styles/app.css';
import 'typeface-roboto';

import { createApp } from 'vue';
import Auth from "./pages/Auth.vue";
import MainPage from './pages/MainPage.vue';;

if (document.getElementById('auth-page')) {
  createApp(Auth).mount('#auth-page');
}

if (document.getElementById('main-page')) {
  createApp(MainPage).mount('#main-page');
}