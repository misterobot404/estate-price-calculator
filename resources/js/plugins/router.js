import {createRouter, createWebHistory} from "vue-router"
import History from "../pages/History"
import Settings from "../pages/Settings"
import Guide from "../pages/Guide"
import Signin from "../pages/Signin"
import Signup from "../pages/Signup"
import store from "./store"
import Entry from "../pages/Calculator/Entry"
import Upload from "../pages/Calculator/Step1_Upload"
import Pools from "../pages/Calculator/Step2_Pools"
import Calculation from "../pages/Calculator/Step3_Calculation"

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
        component: Entry,
        meta: {
            middlewareAuth: true,
        }
    },
    {
        path: '/calculator/upload',
        component: Upload,
        meta: {
            middlewareAuth: true,
            needSetStepCalculation: true
        }
    },
    {
        path: '/calculator/pools/:id?',
        component: Pools,
        meta: {
            middlewareAuth: true,
            needSetStepCalculation: true
        }
    },
    {
        path: '/calculator/calculation',
        component: Calculation,
        meta: {
            middlewareAuth: true,
            needSetStepCalculation: true
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
    {
        path: '/guide',
        component: Guide,
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
    if (to.matched.some(record => record.meta.middlewareAuth) && !store.getters['isAuth']) {
        next('/signin')
    }
    if (to.matched.some(record => record.meta.needSetStepCalculation) && !store.state['entry_to_calculation']) {
        next('/calculator')
    }
    else if (to.matched.some(record => !record.meta.middlewareAuth) && store.getters['isAuth']) {
        next('/')
    }
    else next()
})

export default router
