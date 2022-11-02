import axios from "axios";
import store from "./store";

axios.interceptors.response.use(
    response => response,
    error => {
        // Обращение к защищенному ресурсу без прав
        if (error.response.status === 401 || error.response.status === 403) {
            store.commit("LOGOUT");
        } else return Promise.reject(error.response);
    });

// check auth
let authToken = window.localStorage.getItem('token');
if (authToken) {
    // add token to axios header
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + authToken;
}
