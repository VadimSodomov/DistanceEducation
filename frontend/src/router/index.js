import { createRouter, createWebHistory } from 'vue-router';
import Auth from '@/pages/Auth.vue';
import AllCoursesPage from '@/pages/AllCoursesPage.vue';
import CoursePage from "@/pages/CoursePage.vue";
import store from "@/store/index.js";

const routes = [
  {
    path: '/',
    name: 'AllCoursesPage',
    component: AllCoursesPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'Auth',
    component: Auth
  },
  {
    path: '/course/:id',
    name: 'CoursePage',
    component: CoursePage,
    beforeEnter: (to, from, next) => {
      const id = Number(to.params.id);
      if (!isNaN(id)) {
        next();
      } else {
        next({ name: 'NotFound' });
      }
    },
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  if (to.name === 'Auth') {
    localStorage.removeItem('jwt-token');
    store.commit('clearUser');
  }
  next();
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('jwt-token');
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login');
  } else {
    next();
  }
});

export default router;