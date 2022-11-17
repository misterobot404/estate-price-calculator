<template>
    <div style="padding: 24px 28px">
        <div class="flex items-center">
            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" to='/history'/>
            <div class="q-ml-xs">Вернуться к пулу</div>
        </div>
        <div v-if="!loading" class="q-mt-md">
            <!-- Вывод эталона -->
            <div class="text-h7 text-bold q-mb-xs">Эталонный объект</div>
            <q-card @click="$router.push('/history/operation/' + main_operation_object.ОцениваемыйОбъект)" class="cursor-pointer">
                <q-card-section class="q-pa-lg">
                    <div class="text-bold text-h8">Квартира:&nbsp;&nbsp;{{ getObjectByOperation(main_operation_object).Местоположение }}</div>
                    <div class="flex q-mt-md">
                        <div class="q-mr-lg">
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span>
                                {{ (Number)(main_operation_object.Стоимость).toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">За кв.м. (с НДС)</div>
                        </div>
                        <div>
                            <div>
                                <span class="text-bold">Рыночная стоимость:</span>
                                {{ (((Number)(main_operation_object.Стоимость)) * getObjectByOperation(main_operation_object).ПлощадьКвартиры).toLocaleString('ru') }} ₽
                            </div>
                            <div class="text-caption">(с НДС)</div>
                        </div>
                    </div>
                </q-card-section>
            </q-card>
            <!-- Вывод списка других объектов пула -->
            <div class="q-mt-lg q-pt-xs">
                <div class="text-h7 text-bold">Список объектов</div>
                <div v-for="operation in other_operation_objects" class="flex cursor-pointer q-my-md" @click="$router.push('/history/operation/' + operation.ОцениваемыйОбъект)">
                    <q-icon name="o_label" style="font-size: 22px; padding-top: 2px"></q-icon>
                    <div class="col-grow q-ml-lg">
                        <div class="text-bold text-h8">Квартира:&nbsp;&nbsp;{{ getObjectByOperation(operation).Местоположение }}</div>
                        <div>Цена: {{ (((Number)(operation.Стоимость)) * getObjectByOperation(operation).ПлощадьКвартиры).toLocaleString('ru') }} ₽</div>
                        <q-separator class="q-mt-lg q-mb-sm"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import axios from "axios";
import {QSpinnerFacebook} from "quasar";

export default {
    name: "HistoryPool",
    data() {
        return {
            loading: true,

            // Информация об операциях по пулу
            operations_meta: null,

            operations: null,
            main_operation_object: null,
            other_operation_objects: [],

            // Информация об объектах в пуле
            objects: []
        }
    },
    methods: {
        getExcel(el_id) {
            axios.get(+el_id).then()
        },
        getObjectByOperation(operation) {
            return this.objects.find(el => el.id === operation.ОцениваемыйОбъект)
        },
        getOperationByObject(object) {
            return this.operations.find(el => el.ОцениваемыйОбъект === object_id)
        },
    },
    computed: {

    },
    beforeMount() {
        this.$q.loading.show({
            spinner: QSpinnerFacebook,
            spinnerSize: 120,
            backgroundColor: 'grey-4',
            spinnerColor: 'primary'
        })

        axios.get('/api/history/' + this.$route.params.pool_id)
            .then((response) => {
                // Получаем информация об объектах в пуле
                this.objects = response.data.data.objects;
                this.operations = [...response.data.data.operations_trans];

                // Получаем информацию об операциях
                this.operations_meta = response.data.data.operations_meta;
                let index_of_main_obj = response.data.data.operations_trans.findIndex(el => el.ОцениваемыйОбъект === this.operations_meta.Эталон);
                this.main_operation_object = response.data.data.operations_trans[index_of_main_obj];
                response.data.data.operations_trans.splice(index_of_main_obj, 1);
                this.other_operation_objects = response.data.data.operations_trans;

                this.loading = false;
                this.$q.loading.hide();
            })
    }
}

</script>

<style lang="sass">

</style>

