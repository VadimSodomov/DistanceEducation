import {createRouter, createWebHistory} from 'vue-router';
import Auth from '@/pages/Auth.vue';
import MyCoursesPage from "@/pages/MyCoursesPage.vue";
import SubscribeCoursesPage from "@/pages/SubscribeCoursesPage.vue";
import CoursePage from "@/pages/CoursePage.vue";
import store from "@/store/index.js";

const routes = [
    {
        path: '/',
        redirect: '/login'
    },
    {
        path: '/my-courses-page',
        name: 'MyCoursesPage',
        component: MyCoursesPage,
        meta: {requiresAuth: true}
    },
    {
        path: '/subscribe-courses-page',
        name: 'SubscribeCoursesPage',
        component: SubscribeCoursesPage,
        meta: {requiresAuth: true}
    },
    {
        path: '/login',
        name: 'LoginPage',
        component: Auth
    },
    {
        path: '/course',
        name: 'CoursePage',
        component: CoursePage,
        meta: {requiresAuth: true}
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
    if (to.name === 'LoginPage') {
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