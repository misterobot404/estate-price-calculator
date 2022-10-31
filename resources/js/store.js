import Vuex from 'vuex'
import axios from "axios"
import router from "./router"

const store = new Vuex.Store({
    state: {
        token: window.localStorage.getItem('token')
    },
    getters: {
        isAuth: state => state.token
    },
    actions: {
        login({state, commit, dispatch}, payload) {
            return axios.post('/api/login', payload)
                .then(response => {
                    commit('LOGIN', {token: response.data.data.token, user: response.data.data.user});
                })
        },
        logout({commit}) {
            return axios.post('/api/logout')
                .then(_ => {
                    commit('LOGOUT');
                    commit('tiles/SET_TILES', null, {root: true});
                })
        }
    },
    mutations: {
        LOGIN: (state, payload) => {
            state.token = payload.token;
            state.user = payload.user;

            // add token to axios header
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + state.token;
            // saving auth token between sessions
            window.localStorage.setItem('token', state.token);
            window.localStorage.setItem('user', JSON.stringify(state.user));
        },
        LOGOUT: state => {
            state.token = null;
            state.user = null;

            window.localStorage.removeItem('token');
            window.localStorage.removeItem('user');

            // remove token to axios header
            delete axios.defaults.headers.common['Authorization'];

            // if the user was on page with auth middleware
            if (router.currentRoute.meta.middlewareAuth) router.push('/');
        }
    }
})

export default store;

