import Vuex from 'vuex'
import axios from "axios"
import router from "./router"

const store = new Vuex.Store({
    state: {
        token: window.localStorage.getItem('token'),
        user: JSON.parse(window.localStorage.getItem('user'))
    },
    getters: {
        isAuth: state => Boolean(state.token)
    },
    actions: {
        signup({state, commit, dispatch}, payload) {
            return axios.post('/api/signup', payload).then(() => dispatch('signin', payload))
        },
        signin({state, commit, dispatch}, payload) {
            return axios.post('/api/signin', payload)
                .then(response => commit('SIGNIN', {token: response.data.data.token, user: response.data.data.user}))
        },
        logout({commit}) {
            return axios.post('/api/logout')
                .then(() => commit('LOGOUT'))
        }
    },
    mutations: {
        SIGNIN: (state, payload) => {
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

            router.push('/signin');
        }
    }
})

export default store;

