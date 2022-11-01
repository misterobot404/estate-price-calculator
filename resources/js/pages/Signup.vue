<template>
  <div class="window-width window-height flex flex-center">
    <form @submit.prevent="signup" style="min-width: 430px">
      <q-img src="/images/logo-name.svg"/>
      <q-input outlined v-model="login" label="Логин" class="q-mt-lg" required/>
      <q-input outlined v-model="email" label="Электронная почта" type="email" class="q-mt-sm" required/>
      <q-input name="password" outlined v-model="password" label="Пароль" type="password" class="q-mt-sm" required
               onchange="if(this.checkValidity()) form.confirm_password.pattern = this.value; else form.confirm_password.pattern = ''"/>
      <q-input name="confirm_password" outlined v-model="confirm_password" label="Подтвердите пароль" type="password" class="q-mt-sm" required
               onchange="this.setCustomValidity(this.validity.patternMismatch ? 'Пароли не совпадают' : '')"/>
      <q-btn color="primary" label="Зарегистрироваться" class="q-mt-md full-width" type="submit" :loading="loading"/>
      <div class="text-center q-mt-md">
        <q-btn to="/signin" label="Уже есть учётная запись" flat rounded color="primary" no-caps/>
      </div>
    </form>
  </div>
</template>

<script>
import router from "../router";

export default {
  data() {
    return {
      login: null,
      email: null,
      password: null,
      confirm_password: null,

      loading: false,
    }
  },
  methods: {
    signup() {
      this.loading = true;
      setTimeout(() => {
        router.push('/calculator').then(() =>
            this.$q.notify({
              color: 'primary',
              message: 'Учётная запись успешно зарегистрирована',
              icon: 'check_circle_outline',
              actions: [
                { label: 'Понятно', color: 'white'}
              ]
            })
        )
        this.loading = false
      }, 1600)
    },
  },
}
</script>

