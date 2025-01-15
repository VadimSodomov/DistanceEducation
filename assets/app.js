import './styles/app.css';
import 'typeface-roboto';

import { createApp } from 'vue';
import Auth from "./pages/Auth.vue";
import Sidebar from "./components/Sidebar.vue";

if (document.getElementById('auth-page')) {
  createApp(Auth).mount('#auth-page');
}

if (document.getElementById('sidebar')) {
  createApp(Sidebar).mount('#sidebar');
}