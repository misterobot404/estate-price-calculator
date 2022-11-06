<template>
    <div>
        <div class="q-pa-md flex" style="align-items: baseline;">
            <h6 style="margin: 0%; ">История отчётов за</h6>
            <div class="q-gutter-md" style="margin: 0%; ">
                <q-select v-model="model" :options="options" option-value="id" option-label="desc" option-disable="inactive"
                          emit-value map-options style="max-width: 100px; max-width: 300px" />
            </div>
        </div>

        <div class="flex">
            <div class="q-pa-md navigation" style="max-width: fit-content; max-height: fit-content;">

                <q-list padding class="rounded-borders text-primary">
                    <q-item clickable v-ripple :active="link === 'January'" @click="link = 'January'"
                            active-class="my-menu-link">
                        <q-item-section>Январь</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'February'" @click="link = 'February'"
                            active-class="my-menu-link">
                        <q-item-section>Февраль</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'March'" @click="link = 'March'"
                            active-class="my-menu-link">
                        <q-item-section>Март</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <q-item clickable v-ripple :active="link === 'April'" @click="link = 'April'"
                            active-class="my-menu-link">
                        <q-item-section>Апрель</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'May'" @click="link = 'May'" active-class="my-menu-link">
                        <q-item-section>Май</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'June'" @click="link = 'June'" active-class="my-menu-link">
                        <q-item-section>Июнь</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <q-item clickable v-ripple :active="link === 'July'" @click="link = 'July'" active-class="my-menu-link">
                        <q-item-section>Июль</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'August'" @click="link = 'August'"
                            active-class="my-menu-link">
                        <q-item-section>Август</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'September'" @click="link = 'September'"
                            active-class="my-menu-link">
                        <q-item-section>Сентябрь</q-item-section>
                    </q-item>

                    <q-separator spaced />

                    <q-item clickable v-ripple :active="link === 'October'" @click="link = 'October'"
                            active-class="my-menu-link">
                        <q-item-section>Октябрь</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'November'" @click="link = 'November'"
                            active-class="my-menu-link">
                        <q-item-section>Ноябрь</q-item-section>
                    </q-item>

                    <q-item clickable v-ripple :active="link === 'December'" @click="link = 'December'"
                            active-class="my-menu-link">
                        <q-item-section>Декабрь</q-item-section>
                    </q-item>
                </q-list>
            </div>

            <!-- report card -->

            <q-card flat bordered class="report-card bg-grey-1 ">
                <q-card-section>
                    <div class="row items-center no-wrap">
                        <div class="col">
                            <div class="text-h6">Our Planet</div>
                            <div class="text-subtitle2">by John Doe</div>
                        </div>

                        <div class="col-auto">
                            <q-btn color="grey-7" round flat icon="more_vert">
                                <q-menu cover auto-close>
                                    <q-list>
                                        <q-item clickable>
                                            <q-item-section>Скачать</q-item-section>
                                        </q-item>
                                        <q-item clickable>
                                            <q-item-section>Удалить</q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-menu>
                            </q-btn>
                        </div>
                    </div>
                </q-card-section>

                <q-card-section>
                    lorem
                </q-card-section>
            </q-card>
        </div>
    </div>
</template>

<script>
import { ref, VueElement } from 'vue'


export default {
    setup() {
        return {
            model: ref(2022),
            options: [
                '2020', '2021', '2022'
            ],
            link: ref('January'),
        }
    }
}

</script>

<style lang="sass">
.navigation
  margin: 0 1% 0 1%
  background: #FFFFFF
  border-radius: 8px


.my-menu-link
  color: #038DD2
  background: #E6F6FF
  border-radius: 8px

.report-card
  max-height: fit-content

</style>

