<template>
    <div class="relative-position">
        <YandexMap
            :coordinates="[55.75, 37.57]"
            zoom="11"
            :controls="['typeSelector', 'trafficControl']"
        >
            <template v-if="objects">
                <YandexMarker v-for="object in objects" :coordinates="[object.coordx, object.coordy]" :marker-id="object.id"/>
            </template>
        </YandexMap>
        <div class="absolute-top-left q-pa-lg map__view-block custom-y-scroll">
            <q-card style="width: 320px; border-radius: .4rem">
                <q-card-section>
                    <template v-if="page === 'pools'">
                        <div class="flex">
                            <div class="text-h8">Загруженные категории</div>
                            <q-space/>
                            <q-btn flat round icon="delete" size="sm" class="text-grey-8" @click="breakCalculation()" :loading="clear_pools_process">
                                <q-tooltip>
                                    Очистить загруженные данные
                                </q-tooltip>
                            </q-btn>
                        </div>
                        <q-separator class="q-mt-sm"/>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 200px;">
                                <q-spinner-grid color="primary" size="2em"/>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="pool in pools" class="q-my-md flex">
                                <div>
                                    <q-img src="/images/pool-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md">
                                    <div class="text-negative">Не расчитано</div>
                                    <div class="text-h8" v-text="getNameOfRoomsById(pool.КоличествоКомнат)"/>
                                    <div class="text-small">
                                        Количество квартир: {{ pool.КоличествоОбъектов }}
                                    </div>
                                    <q-btn flat color="primary" label="Перейти к расчёту" no-caps class="q-mt-sm btn-background-primary" size="md" :to="'/calculator/pools/' + pool.id"/>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template v-else-if="page === 'objects'">
                        <div class="flex items-center">
                            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" to="/calculator/pools"/>
                            <div class="q-ml-xs text-h8">Вернуться к категориям</div>
                        </div>
                        <q-separator class="q-mt-sm"/>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 200px;">
                                <q-spinner-grid color="primary" size="3em"/>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="object in objects" class="q-my-md flex">
                                <div>
                                    <q-img src="/images/object-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md">
                                    <div class="text-bold" v-text="object.Местоположение"/>
                                    <div class="">
                                        Сегмент: {{ object.Сегмент }}
                                    </div>
                                    <div class="">
                                        Материал стен: {{ object.МатериалСтен }}
                                    </div>
                                    <div class="">
                                        Этаж расположения: {{ object.ЭтажРасположения }}
                                    </div>
                                    <div class="">
                                        Этажность дома: {{ object.ЭтажностьДома }}
                                    </div>
                                    <q-btn flat color="primary" label="Выбрать эталоном" no-caps class="q-mt-sm btn-background-primary" size="md"/>
                                </div>
                            </div>
                        </template>
                    </template>
                </q-card-section>
            </q-card>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { YandexMap, YandexMarker, loadYmap  } from 'vue-yandex-maps'

export default {
    components: {
        YandexMap,
        YandexMarker,

    },
    data() {
        return {
            data_loading: true,
            clear_pools_process: false,

            myMap: null,
            pools: null,
            objects: null,
        }
    },
    methods: {
        loadData() {
            let page = this.$route.params.id ? "objects" : "pools";
            this.data_loading = true;
            this.objects = null;

            // Если текущая страница - objects, то загружаем все пулы
            if (page === 'pools') {

                axios.get('/api/pools')
                    .then((response) => {
                        this.pools = response.data.data.pools;
                        this.data_loading = false;
                    })
            }
            // Если текущая страница - objects, то загружаем ещё объекты недвижимости для выбранного пула
            else if (page === 'objects') {
                axios.get('/api/pools/' + this.$route.params.id)
                    .then((response) => {
                        this.objects = response.data.data.objects;
                        this.data_loading = false;

                        this.showObjectsOnMap();
                    })
            }
        },
        getNameOfRoomsById(room_id) {
            let name = this.$store.getters.nameOfNumberRoomsById(room_id);

            switch (name) {
                case "1":
                    return "Однокомнатные квартиры";
                case "2":
                    return "Двухкомнатные квартиры";
                case "3":
                    return "Трёхкомнатные квартиры";
                case "4":
                    return "Четырёхкомнатные квартиры";
                case "5":
                    return "Пятикомнатные квартиры";
                case "6":
                    return "Шестикомнатные квартиры";
                case "7":
                    return "Семикомнатные квартиры";
                case "8":
                    return "Восьмикомнатные квартиры";
                case "> 9":
                    return "Девятикомнатные квартиры";
                case "Студия":
                    return "Студии";
                default:
                    return name;
            }
        },
        breakCalculation() {
            this.clear_pools_process = true;
            axios.post('/api/break_calculation', {group_id: this.pools[0].Группа})
                .then(() => this.$router.push("/calculator/upload"))
                .finally(() => this.clear_pools_process = false)
        },
    },
    computed: {
        page() {
            this.loadData();
            return this.$route.params.id ? "objects" : "pools"
        },
    },
    beforeMount() {
        this.loadData();
    },

    async mounted() {
        const settings = {
            apiKey: '253b2eae-b322-4893-a57c-3d63323b3558', // Индивидуальный ключ API
            lang: 'ru_RU', // Используемый язык
            coordorder: 'latlong', // Порядок задания географических координат
            debug: true, // Режим отладки
            version: '2.1' // Версия Я.Карт
        }

        await loadYmap(settings);

        ymaps.geocode('Нижний Новгород', {results: 1}).then(function (res) {
            // Выбираем первый результат геокодирования.
            var firstGeoObject = res.geoObjects.get(0),
                // Координаты геообъекта.
                coords = firstGeoObject.geometry.getCoordinates(),
                // Область видимости геообъекта.
                bounds = firstGeoObject.properties.get('boundedBy');

            firstGeoObject.options.set('preset', 'islands#darkBlueDotIconWithCaption');
            // Получаем строку с адресом и выводим в иконке геообъекта.
            firstGeoObject.properties.set('iconCaption', firstGeoObject.getAddressLine());

            console.log('Все данные геообъекта: ', firstGeoObject.properties.getAll());
        });
    }
}
</script>

<style scoped lang="scss">
.yandex-container {
    width: 100vw;
    height: calc(100vh - 138px);
}

.map__view-block {
    max-height: calc(100vh - 138px);
    overflow-y: scroll;
}

.custom-y-scroll::-webkit-scrollbar {
    width: 16px;
}

</style>
