import {createRouter, createWebHistory} from "vue-router"
import Home from "./pages/Home"
import Login from "./pages/Login"
import store from "./store";

const routes = [
    {
        path: '/',
        redirect: '/home'
    },
    {
        path: '/login',
        component: Login,
        meta: {
            hideLayout: true
        }
    },
    {
        path: '/home',
        component: Home,
        meta: {
            middlewareAuth: true,
        }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    // checking access to the router
    if (to.matched.some(record => record.meta.middlewareAuth) && !store.getters['auth/isAuth']) {
        next('/login')
    }
    else next()
})

export default router
