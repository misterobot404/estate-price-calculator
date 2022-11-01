import {createRouter, createWebHistory} from "vue-router"
import Calculator from "./pages/Calculator"
import History from "./pages/History"
import Settings from "./pages/Settings"
import Signin from "./pages/Signin"
import Signup from "./pages/Signup"
import store from "./store"

const routes = [
    {
        path: '/',
        redirect: '/calculator'
    },
    {
        path: '/signin',
        component: Signin,
        meta: {
            hideLayout: true
        }
    },
    {
        path: '/signup',
        component: Signup,
        meta: {
            hideLayout: true
        }
    },
    {
        path: '/calculator',
        component: Calculator,
        meta: {
            middlewareAuth: true,
        }
    },
    {
        path: '/history',
        component: History,
        meta: {
            middlewareAuth: true,
        }
    },
    {
        path: '/settings',
        component: Settings,
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
    /*if (to.matched.some(record => record.meta.middlewareAuth) && !store.getters['auth/isAuth']) {
        next('/signin')
    }
    else */next()
})

export default router
