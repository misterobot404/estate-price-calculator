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
                    <q-img src="/images/logo-name.svg" width="410px"/>
                    <q-space/>
                    <div class="flex items-center cursor-pointer">
                        <q-btn no-caps flat>
                            <div class="text-weight-medium q-mr-sm" style="font-size: 15px">Администратор сервиса</div>
                            <q-icon name="o_account_circle" color="primary" size="26px"/>
                        </q-btn>
                        <q-menu>
                            <div class="row no-wrap q-pa-md">
                                <div class="column">
                                    <div class="text-h8 q-mb-xs">Настройки</div>
                                    <q-toggle label="Тёмная тема"/>
                                    <q-toggle label="Не сохранять сессию"/>
                                </div>

                                <q-separator vertical inset class="q-mx-lg"/>

                                <div class="column items-center">
                                    <q-avatar size="72px">
                                        <img src="/images/user-avatar.png">
                                    </q-avatar>

                                    <div class="text-subtitle1 q-mt-xs q-mb-sm" v-text="user.name"/>

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
                    <q-route-tab to="/calculator" label="Оценка недвижимости"/>
                    <q-route-tab to="/history" label="Отчёты"/>
                    <q-route-tab to="/settings" label="Настройка"/>
                    <q-route-tab to="/guide" label="Инструкция"/>
                </q-tabs>
            </div>
            <router-view/>
        </q-layout>
    </template>
</template>

<script>
import {mapState} from "vuex";

export default {
    data() {
        return   {
            logout_loading: false,
        }
    },
    methods: {
        logout() {
            this.logout_loading = true;
            this.$store.dispatch('logout').finally(() => this.logout_loading = false);
        }
    },
    computed: {
        ...mapState(['user'])
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
    line-height: 2rem;
    letter-spacing: 0.0125em;
}

.text-h8 {
    font-size: 16px;
    font-weight: 500;
    line-height: 2rem;
    letter-spacing: 0.0125em;
}
</style>
