<template>
    <template v-if="$route.meta.hideLayout">
        <q-layout>
            <router-view/>
        </q-layout>
    </template>
    <template v-else>
        <q-layout class="column">
            <div class="q-px-lg q-pt-lg q-header--bordered">
                <div class="flex items-center">
                    <q-img src="/images/logo-name.svg" width="410px" no-spinner/>
                    <q-space/>
                    <div class="q-mr-xs">
                        <q-btn flat round>
                            <q-icon name="apps" class="text-grey-8"/>
                        </q-btn>
                        <q-menu class="q-px-lg q-py-md" style="min-width: 330px; border-radius: .4rem">
                            <div>Калькуляторы расчёта цены квартиры:</div>
                            <q-separator class="q-my-sm"/>
                            <div class="flex">
                                <q-btn flat href="https://www.avito.ru/evaluation/realty/" target="_blank" size="lg">
                                    <img src="/images/Avito.svg" alt="Avito" width="64"/>
                                </q-btn>
                                <q-btn flat href="https://www.cian.ru/kalkulator-nedvizhimosti/" target="_blank" size="lg">
                                    <img src="/images/Cian.svg" alt="Cian" width="64"/>
                                </q-btn>
                                <q-btn flat href="https://realty.ya.ru/calculator-stoimosti" target="_blank" size="lg">
                                    <img src="/images/Yandex.png" alt="Yandex" width="60"/>
                                </q-btn>
                            </div>
                        </q-menu>
                    </div>
                    <div>
                        <q-btn round flat>
                            <q-avatar size="32px">
                                <img src="/images/user-avatar-icon.png" alt="Иконка аватара"/>
                            </q-avatar>
                        </q-btn>
                        <q-menu style="border-radius: .4rem">
                            <div class="row no-wrap q-pa-md">
                                <div class="column">
                                    <div class="q-mb-sm">Настройки</div>
                                    <q-toggle label="Не сохранять сессию" v-model="c_session_no_save"/>
                                    <q-toggle label="Тёмная тема (beta)" disable v-model="c_dark_mode"/>
                                </div>

                                <q-separator vertical inset class="q-mx-lg"/>

                                <div class="column items-center">
                                    <q-avatar size="72px">
                                        <img src="/images/user-avatar.png" alt="Аватар пользователя">
                                    </q-avatar>

                                    <div class="q-mt-xs q-mb-sm text-no-wrap" v-text="user.name"/>

                                    <q-btn
                                        color="primary"
                                        label="Выйти"
                                        unelevated
                                        size="sm"
                                        @click="logout"
                                        :loading="logout_loading"
                                        v-close-popup
                                    />
                                </div>
                            </div>
                        </q-menu>
                    </div>
                </div>
                <q-tabs align="left" class="text-body q-mt-md" no-caps active-color="primary">
                    <q-route-tab :to="true_to_path_for_calc" label="Оценка недвижимости"/>
                    <q-route-tab :to="true_to_path_for_history" label="Отчёты"/>
                    <q-route-tab to="/settings" label="Настройка"/>
                    <q-route-tab to="/guide" label="Инструкция"/>
                </q-tabs>
            </div>
            <router-view/>
        </q-layout>
    </template>
</template>

<script>
import {mapState, mapMutations} from "vuex";

export default {
    data() {
        return {
            last_calculation_page: "/calculator",
            last_history_page: "/history",
            dark_mode: false,
            session_no_save: false,
            logout_loading: false,
        }
    },
    methods: {
        ...mapMutations(['LOGOUT']),
        logout() {
            this.logout_loading = true;
            this.$store.dispatch('logout').finally(() => this.logout_loading = false);
        }
    },
    computed: {
        ...mapState(['user']),
        c_dark_mode: {
            get() {
                return this.dark_mode
            },
            set(value) {
                this.$q.localStorage.set("dark_mode", value);
                this.dark_mode = value;

                this.$q.dark.set(value);
            }
        },
        c_session_no_save: {
            get() {
                return this.session_no_save
            },
            set(value) {
                this.$q.localStorage.set("session_no_save", value);
                this.session_no_save = value;
            }
        },
        true_to_path_for_calc() {
            let path = this.$route.path;
            if (path.includes("/calculator/")) {
                this.last_calculation_page = path;
                return path;
            } else {
                return this.last_calculation_page;
            }
        },
        true_to_path_for_history() {
            let path = this.$route.path;
            if (path.includes("/history/")) {
                this.last_history_page = path;
                return path;
            } else {
                return this.last_history_page;
            }
        }
    },
    beforeMount() {
        this.dark_mode = this.$q.localStorage.getItem("dark_mode") ?? false;
        this.$q.dark.set(this.dark_mode);

        this.session_no_save = this.$q.localStorage.getItem("session_no_save") ?? false;
        if (this.session_no_save) {
            this.LOGOUT();
        }
    }
}
</script>

<style lang="scss">

@font-face {
    font-family: "Lato";
    font-weight: 400;
    src: url("/fonts/Lato-Regular.ttf") format("truetype");
}

@font-face {
    font-family: "Lato";
    font-weight: 500;
    src: url("/fonts/Lato-Medium.ttf") format("truetype");
}

@font-face {
    font-family: "Lato";
    font-weight: 600;
    src: url("/fonts/Lato-Bold.ttf") format("truetype");
}

body {
    font-family: 'Lato', 'Roboto', sans-serif;
    background-color: #FAFAFA;
}

.text-h7 {
    font-size: 18px;
    font-weight: 500;
    line-height: 1.85rem;
    letter-spacing: 0.0125em;
}

.text-h8 {
    font-size: 16px;
    font-weight: 500;
    line-height: 1.7rem;
    letter-spacing: 0.0125em;
}

.text-small {
    font-size: 14px;
}

.v-enter-active,
.v-leave-active {
    transition: opacity 0.2s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}

.btn-background-primary {
    background-color: #e9f1f9
}

.text-bold {
    font-weight: 600 !important;
}
</style>
