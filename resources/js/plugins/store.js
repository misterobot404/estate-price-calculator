import Vuex from 'vuex'
import axios from "axios"
import router from "./router"

const store = new Vuex.Store({
    state: {
        // user
        token: window.localStorage.getItem('token'),
        user: JSON.parse(window.localStorage.getItem('user')),

        // Флаг отображающий, входил ли пользователь в calculation хоть раз
        // Необходим для первичной инициализации состояния калькулятора
        entry_to_calculation: false,

        // Справочники: ТипСостояния, ТипСегмента, ТипМатериалаСтен, ТипКоличестваКомнат
        // Структура: Id - название
        reference_books: null
    },
    getters: {
        isAuth: state => Boolean(state.token),
        nameOfConditionById: (state) => (id) => state.reference_books.type_of_condition.find(condition => condition.id === id).Название,
        nameOfNumberRoomsById: (state) => (id) => state.reference_books.type_of_number_rooms.find(el => el.id === id).Название,
        nameOfSegmentById: (state) => (id) => state.reference_books.type_of_segment.find(segment => segment.id === id).Название,
        nameOfWallById: (state) => (id) => state.reference_books.type_of_wall.find(wall => wall.id === id).Название,
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
        },
        ENTRY_TO_CALCULATION_DONE: (state) => {
            state.entry_to_calculation = true;
        },
        SET_REFERENCE_BOOKS: (state, reference_books) => {
            state.reference_books = reference_books;
        },

    }
})

export default store;

