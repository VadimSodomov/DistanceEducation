import './styles/app.css';
import 'typeface-roboto';
import '@fortawesome/fontawesome-free/css/all.min.css';

import { createApp } from 'vue';
import Auth from "./pages/Auth.vue";
import AllCoursesPage from "./pages/AllCoursesPage.vue";
import CoursePage from "./pages/CoursePage.vue";

if (document.getElementById('auth-page')) {
  createApp(Auth).mount('#auth-page');
}

if (document.getElementById('main-page')) {
  createApp(AllCoursesPage).mount('#main-page');
}

if (document.getElementById('course-page')) {
  createApp(CoursePage).mount('#course-page');
}