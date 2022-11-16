<template>
    <div class="one-display relative-position">
        <YandexMap
            v-if="page === 'pools' || page === 'objects'"
            :coordinates="[55.75, 37.57]"
            :zoom="11"
            :detailed-controls="map_detailed_controls"
            :controls="map_controls"
        >
            <YandexClusterer v-if="objects">
                <YandexMarker v-for="object in objects" :coordinates="[object.coordx, object.coordy]" :marker-id="object.id"/>
            </YandexClusterer>
        </YandexMap>
        <YandexMap
            v-else-if="page === 'object'"
            :coordinates="[55.75, 37.57]"
            :zoom="11"
            :detailed-controls="map_detailed_controls"
            :controls="map_controls"
        >
            <template v-if="analogs && selected_object">
                <YandexMarker :coordinates="[selected_object.coordx, selected_object.coordy]" :options="{'preset': 'islands#redDotIcon'}" marker-id="1000000"/>

                <YandexMarker
                    v-for="analog in analogs"
                    :ref="'analog_'+analog.id"
                    :coordinates="[analog.coordx, analog.coordy]"
                    :options="{'preset':  'islands#blackIcon'}"
                    :marker-id="analog.id"
                >
                    <template #component>
                        <div>
                            <div class="flex justify-center">
                                <q-img src="/images/object-icon.svg" width="60px" no-spinner/>
                            </div>
                            <div class="q-mt-sm">
                                <div class="text-bold text-center q-mb-sm">{{ analog.Стоимость }} ₽</div>
                                <div v-text="analog.Местоположение"/>
                                <div>Состояние: {{ analog.Состояние.toLowerCase() }}</div>
                                <div>Площадь квартиры: {{ analog.ПлощадьКвартиры }} кв. м.</div>
                                <div>Количество комнат: {{ analog.КоличествоКомнат }}</div>
                                <div>Этаж расположения: {{ analog.ЭтажРасположения }} этаж</div>
                                <div class="flex justify-center">
                                    <q-btn @click.="selectAnalog(analog.id)" class="q-mt-md" style="width: 200px" label="Выбрать аналог" size="sm" no-caps color="primary"/>
                                </div>
                            </div>
                        </div>
                    </template>
                </YandexMarker>
            </template>
        </YandexMap>

        <div class="absolute-top-left q-pa-lg map__view-block custom-y-scroll">
            <q-card style="min-width: 340px; border-radius: .4rem">
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
                        <q-separator class="q-mt-sm q-mb-lg"/>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 180px; padding-bottom: 16px">
                                <q-spinner-grid color="primary" size="2em"/>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="(pool, index) in pools" class="q-my-md flex">
                                <div>
                                    <q-img src="/images/pool-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md col-grow">
                                    <div class="text-negative">{{ pool.status }}</div>
                                    <div class="text-h8" v-text="getNameOfRoomsById(pool.КоличествоКомнат)"/>
                                    <div class="text-small">
                                        Количество квартир: {{ pool.КоличествоОбъектов }}
                                    </div>
                                    <q-btn flat color="primary" label="Перейти к расчёту" no-caps class="q-mt-sm btn-background-primary" size="md" :to="'/calculator/pools/' + pool.id"/>
                                    <q-separator class="q-mt-md" v-show="pools.length - 1 !== index"/>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template v-else-if="page === 'objects'">
                        <div class="flex items-center">
                            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" to="/calculator/pools"/>
                            <div class="q-ml-xs text-h8">Вернуться к категориям</div>
                        </div>
                        <q-separator class="q-mt-sm q-mb-lg"/>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 180px; padding-bottom: 16px">
                                <q-spinner-grid color="primary" size="2em"/>
                            </div>
                        </template>
                        <template v-else>
                            <div v-for="(object, index) in objects" class="q-my-md flex">
                                <div>
                                    <q-img src="/images/object-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md col-grow">
                                    <div v-if="objects_price" class="text-h8 text-primary">
                                        {{ Math.floor(objects_price[index].Стоимость * object.ПлощадьКвартиры).toLocaleString('ru') }} ₽
                                    </div>
                                    <div class="text-bold" v-text="object.Местоположение"/>
                                    <div class="">Состояние: {{ $store.getters.nameOfConditionById(object.Состояние).toLowerCase() }}</div>
                                    <div class="">Площадь квартиры: {{ object.ПлощадьКвартиры }} кв. м.</div>
                                    <div class="">Количество комнат: {{ $store.getters.nameOfNumberRoomsById(object.КоличествоКомнат) }}</div>
                                    <div class="">Этаж расположения: {{ object.ЭтажРасположения }} этаж</div>
                                    <div>
                                        <q-icon name="more_horiz" @click="extended_el_id === object.id ? extended_el_id = null : extended_el_id = object.id" class="cursor-pointer text-grey-8"/>
                                    </div>
                                    <q-slide-transition>
                                        <div v-show="extended_el_id === object.id">
                                            <div class="">Этажность дома: {{ object.ЭтажностьДома }}</div>
                                            <div class="">Площадь кухни: {{ object.ПлощадьКухни }} кв. м.</div>
                                            <div class="">Сегмент: {{ $store.getters.nameOfSegmentById(object.Сегмент).toLowerCase() }}</div>
                                            <div class="">Материал стен: {{ $store.getters.nameOfWallById(object.МатериалСтен).toLowerCase() }}</div>
                                            <div class="">Наличие балкона/лоджии: {{ object.НаличиеБалконаЛоджии ? "да" : "нет" }}</div>
                                            <div class="">Время до метро (пешком): {{ object.МетроМин }} мин.</div>
                                        </div>
                                    </q-slide-transition>
                                    <q-btn flat color="primary" label="Выбрать эталоном" no-caps class="q-mt-sm btn-background-primary" size="md" :to="'/calculator/pools/' + object.Пул + '/' + object.id"/>
                                    <q-separator class="q-mt-md" v-show="objects.length - 1 !== index"/>
                                </div>
                            </div>
                            <div v-if="objects_price">
                                <q-separator class="q-my-md"/>
                                <q-btn color="primary" class="full-width" @click="endCalc()">Завершить расчёт пула</q-btn>
                            </div>
                        </template>
                    </template>
                    <template v-else-if="page === 'object'">
                        <div class="flex items-center">
                            <q-btn flat round icon="arrow_back" size="sm" class="text-grey-8" :to="'/calculator/pools/'+$route.params.pool_id"/>
                            <div class="q-ml-xs text-h8">Эталонная квартира</div>
                        </div>
                        <q-separator class="q-mt-sm q-mb-lg"/>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 180px; padding-bottom: 16px">
                                <q-spinner-grid color="primary" size="2em"/>
                            </div>
                        </template>
                        <template v-else>
                            <div class="q-my-md flex">
                                <div>
                                    <q-img src="/images/object-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md col-grow">
                                    <div class="text-bold" v-text="selected_object.Местоположение"/>
                                    <div class="">Состояние: {{ $store.getters.nameOfConditionById(selected_object.Состояние).toLowerCase() }}</div>
                                    <div class="">Площадь квартиры: {{ selected_object.ПлощадьКвартиры }} кв. м.</div>
                                    <div class="">Количество комнат: {{ $store.getters.nameOfNumberRoomsById(selected_object.КоличествоКомнат) }}</div>
                                    <div class="">Этаж расположения: {{ selected_object.ЭтажРасположения }} этаж</div>
                                    <div>
                                        <q-icon name="more_horiz" @click="extended_el_id === selected_object.id ? extended_el_id = null : extended_el_id = selected_object.id" class="cursor-pointer text-grey-8"/>
                                    </div>
                                    <q-slide-transition>
                                        <div v-show="extended_el_id === selected_object.id">
                                            <div class="">Этажность дома: {{ selected_object.ЭтажностьДома }}</div>
                                            <div class="">Площадь кухни: {{ selected_object.ПлощадьКухни }} кв. м.</div>
                                            <div class="">Сегмент: {{ $store.getters.nameOfSegmentById(selected_object.Сегмент).toLowerCase() }}</div>
                                            <div class="">Материал стен: {{ $store.getters.nameOfWallById(selected_object.МатериалСтен).toLowerCase() }}</div>
                                            <div class="">Наличие балкона/лоджии: {{ selected_object.НаличиеБалконаЛоджии ? "да" : "нет" }}</div>
                                            <div class="">Время до метро (пешком): {{ selected_object.МетроМин }} мин.</div>
                                        </div>
                                    </q-slide-transition>
                                    <template v-show="false">
                                        <q-btn flat color="primary" label="Выбрать эталоном" no-caps class="q-mt-sm btn-background-primary" size="md"
                                               :to="'/calculator/pools/' + selected_object.Пул + '/' + selected_object.id"/>
                                    </template>
                                </div>
                            </div>
                            <div>
                                <q-btn color="primary" icon="calculate" class="full-width" outline label="Рассчитать" no-caps @click="calculationSetup()"/>
                            </div>
                        </template>
                    </template>
                </q-card-section>
            </q-card>

            <template v-if="page === 'object'">
                <q-card style="min-width: 340px; border-radius: .4rem" class="q-mt-sm" v-if="selected_analogs?.length">
                    <q-card-section>
                        <div class="text-h8">Выбранные аналоги ({{ selected_analogs.length }}/5)</div>
                        <div class="q-my-md">
                            <div v-for="(selected_analog, index) in selected_analogs" class="q-my-md flex">
                                <div>
                                    <q-img src="/images/object-icon.svg" width="40px" no-spinner/>
                                </div>
                                <div class="q-ml-md col-grow">
                                    <div class="text-bold">{{ Number(selected_analog.Стоимость).toLocaleString('ru') }} ₽</div>
                                    <div v-text="selected_analog.Местоположение"/>
                                    <div>Состояние: {{ selected_analog.Состояние.toLowerCase() }}</div>
                                    <div>Площадь квартиры: {{ selected_analog.ПлощадьКвартиры }} кв. м.</div>
                                    <div>Количество комнат: {{ selected_analog.КоличествоКомнат }}</div>
                                    <div>Этаж расположения: {{ selected_analog.ЭтажРасположения }} этаж</div>
                                    <div>
                                        <q-icon name="more_horiz" @click="extended_el_id === selected_analog.id ? extended_el_id = null : extended_el_id = selected_analog.id" class="cursor-pointer text-grey-8"/>
                                    </div>
                                    <q-slide-transition>
                                        <div v-show="extended_el_id === selected_analog.id">
                                            <div>Этажность дома: {{ selected_analog.ЭтажностьДома }}</div>
                                            <div>Площадь кухни: {{ selected_analog.ПлощадьКухни }} кв. м.</div>
                                            <div>Сегмент: {{ selected_analog.Сегмент.toLowerCase() }}</div>
                                            <div>Материал стен: {{ selected_analog.МатериалСтен.toLowerCase() }}</div>
                                            <div>Наличие балкона/лоджии: {{ selected_analog.НаличиеБалконаЛоджии ? "да" : "нет" }}</div>
                                            <div>Время до метро (пешком): {{ selected_analog.МетроМин }} мин.</div>
                                        </div>
                                    </q-slide-transition>
                                    <q-btn flat color="primary" label="Отменить выбор" no-caps class="q-mt-sm btn-background-primary" size="md" @click="removeSelectAnalog(selected_analog.id)"/>
                                    <q-separator class="q-mt-md" v-show="selected_analogs.length - 1 !== index"/>
                                </div>
                            </div>
                        </div>
                    </q-card-section>
                </q-card>
                <q-card style="min-width: 340px; border-radius: .4rem" class="q-mt-sm">
                    <q-card-section>
                        <div class="text-h8">Подобрано</div>
                        <template v-if="data_loading">
                            <div class="flex justify-center items-center" style="height: 180px; padding-bottom: 16px">
                                <q-spinner-grid color="primary" size="2em"/>
                            </div>
                        </template>
                        <template v-else>
                            <template v-if="analogs?.length">
                                <template v-if="analogs.length === selected_analogs.length">
                                    <div class="flex column items-center content-center text-center">
                                        <q-img
                                            class="q-mt-md"
                                            src="/images/no-data.svg"
                                            no-spinner
                                            style="max-width: 150px"
                                        />
                                        <div class="q-my-md">Выбраны все доступные аналоги</div>
                                    </div>
                                </template>
                                <div v-else v-for="(analog, index) in analogs">
                                    <div class="flex q-my-md" v-if="!selected_analogs.find(el => el.id === analog.id)">
                                        <div>
                                            <q-img src="/images/object-icon.svg" width="40px" no-spinner/>
                                        </div>
                                        <div class="q-ml-md col-grow">
                                            <div class="text-bold">
                                                {{ Number(analog.Стоимость).toLocaleString('ru') }} ₽
                                            </div>
                                            <div v-text="analog.Местоположение"/>
                                            <div>Состояние: {{ analog.Состояние.toLowerCase() }}</div>
                                            <div>Площадь квартиры: {{ analog.ПлощадьКвартиры }} кв. м.</div>
                                            <div>Количество комнат: {{ analog.КоличествоКомнат }}</div>
                                            <div>Этаж расположения: {{ analog.ЭтажРасположения }} этаж</div>
                                            <div>
                                                <q-icon name="more_horiz" @click="extended_el_id === analog.id ? extended_el_id = null : extended_el_id = analog.id" class="cursor-pointer text-grey-8"/>
                                            </div>
                                            <q-slide-transition>
                                                <div v-show="extended_el_id === analog.id">
                                                    <div>Этажность дома: {{ analog.ЭтажностьДома }}</div>
                                                    <div>Площадь кухни: {{ analog.ПлощадьКухни }} кв. м.</div>
                                                    <div>Сегмент: {{ analog.Сегмент.toLowerCase() }}</div>
                                                    <div>Материал стен: {{ analog.МатериалСтен.toLowerCase() }}</div>
                                                    <div>Наличие балкона/лоджии: {{ analog.НаличиеБалконаЛоджии ? "да" : "нет" }}</div>
                                                    <div>Время до метро (пешком): {{ analog.МетроМин }} мин.</div>
                                                </div>
                                            </q-slide-transition>
                                            <q-btn flat color="primary" label="Выбрать аналог" no-caps class="q-mt-sm btn-background-primary" size="md"
                                                   @click="selectAnalog(analog.id)"/>
                                            <q-separator class="q-mt-md" v-show="analogs.length - 1 !== index"/>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex column items-center content-center">
                                    <q-img
                                        class="q-mt-md"
                                        src="/images/no-data.svg"
                                        no-spinner
                                        style="max-width: 150px"
                                    />
                                    <div class="q-mt-md">Аналоги не найдены</div>
                                </div>
                            </template>
                        </template>
                    </q-card-section>
                </q-card>
            </template>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import {YandexMap, YandexMarker, YandexClusterer, loadYmap} from 'vue-yandex-maps'

export default {
    components: {YandexMap, YandexMarker, YandexClusterer},
    data() {
        return {
            map_controls: ['typeSelector', 'trafficControl', 'fullscreenControl'],
            map_detailed_controls: {zoomControl: {position: {right: 10, top: 50}}},

            data_loading: true,
            extended_el_id: null,
            clear_pools_process: false,

            pools: null,
            objects: null,

            // Данный параметр возвращается тогда, когда статус пула - 3 (расчитан, но не подтверждён)
            objects_price: null,

            selected_object: [],
            analogs: null,
            selected_analogs: [],

            last_selected_marker: null,

            dismiss_notify: () => void 0,

            map: null
        }
    },
    methods: {
        loadData() {
            this.data_loading = true;
            this.pools = null;
            this.objects = null;
            this.selected_object = [];
            this.analogs = null;
            this.selected_analogs = [];
            this.objects_price = null;

            // Если текущая страница - pools/pool_id/object_id
            if (this.$route.params.object_id) {
                axios.get('/api/pools/' + this.$route.params.pool_id + '/' + this.$route.params.object_id)
                    .then((response) => {
                        this.selected_object = response.data.data.object;

                        let distance_res = [];
                        this.analogs = [];
                        let max_distance = 0;

                        response.data.data.analogs.forEach(el => {
                            distance_res.push(ymaps.coordSystem.geo.getDistance([this.selected_object.coordx, this.selected_object.coordy], [el.coordx, el.coordy]))
                        })

                        if (!this.$route.query.max_distance) {
                            // Определяем, какое минимальное расстояние нам нужно взять для расчётов
                            if (distance_res.filter(el => el <= 1000).length >= 3) {
                                max_distance = 1000;
                            } else if (distance_res.filter(el => el <= 1500).length >= 3) {
                                max_distance = 1500;
                            } else if (distance_res.filter(el => el <= 2000).length >= 3) {
                                max_distance = 2000;
                            } else if (distance_res.filter(el => el <= 2500).length >= 3) {
                                max_distance = 2500;
                            } else if (distance_res.filter(el => el <= 3000).length >= 3) {
                                max_distance = 3000;
                            } else max_distance = 5000;
                        } else {
                            max_distance = 1500;
                        }

                        let local_id = 0;
                        response.data.data.analogs.forEach(el => {
                            let dist = ymaps.coordSystem.geo.getDistance([this.selected_object.coordx, this.selected_object.coordy], [el.coordx, el.coordy]);

                            if (dist < max_distance) {
                                el.id = --local_id;
                                this.analogs.push(el);
                            }
                        })

                        // Сортировка аналогов по количеству соответствующих признаков
                        this.sortAnalogs();

                        if (max_distance > 1500) {
                            this.dismiss_notify();
                            this.dismiss_notify = this.$q.notify({
                                message: 'Минимальное расстояние для поиска аналогов увеличено до ' + max_distance + ' метров',
                                icon: 'straighten',
                                color: 'primary',
                                timeout: 0,
                                actions: [
                                    {
                                        label: 'Не увеличивать расстояние', color: 'blue-grey-2', handler: () => {
                                            window.location.replace(this.$route.path + "?max_distance=1500");
                                        }
                                    },
                                    {label: 'Понятно', color: 'white', handler: () => {}}
                                ]
                            })
                        } else if (this.$route.query.max_distance) {
                            this.dismiss_notify();
                            this.dismiss_notify = this.$q.notify({
                                message: 'Расстояние для поиска аналогов составляет ' + max_distance + ' метров',
                                icon: 'straighten',
                                color: 'primary',
                                timeout: 0,
                                actions: [
                                    {label: 'Понятно', color: 'white', handler: () => {}},
                                    {
                                        label: 'Изменить на оптимальное расстояние', color: 'white', handler: () => {
                                            window.location.replace(this.$route.path);
                                        }
                                    }
                                ]
                            })
                        }

                        // Выбор первых трёх наиболее релевантных аналогов по умолчанию
                        if (this.analogs.length < 3) {
                            this.$q.notify({message: 'Количество подобранных аналогов недостаточно для расчёта', icon: 'announcement'})
                        } else {
                            this.map.then(() => {
                                this.analogs.every((el, index) => {
                                    if (index > 5) {
                                        return false;
                                    }
                                    this.selectAnalog(this.analogs[index].id);
                                    return true;
                                })
                            });
                        }

                        this.data_loading = false;
                    })
            }
            // Если текущая страница - pools/pool_id
            else if (this.$route.params.pool_id) {
                // Загружаем ещё объекты недвижимости для выбранного пула
                axios.get('/api/pools/' + this.$route.params.pool_id)
                    .then((response) => {
                        this.objects = response.data.data.objects;
                        this.data_loading = false;

                        this.objects_price = response.data.data.objects_price;
                    })
            }
            // Если текущая страница - pools
            else {
                // Загружаем все пулы для текущей расчитываемой группы
                axios.get('/api/pools')
                    .then((response) => {
                        this.pools = response.data.data.pools;
                        this.data_loading = false;
                    })
                // Загружаем ещё объекты недвижимости для отображения на карте
                axios.get('/api/all_calculation_objects')
                    .then((response) => this.objects = response.data.data.objects)
            }
        },
        // Сортировка аналогов по количеству соответствующих признаков
        sortAnalogs() {
            // Получаем объект и приводим его поля к виду как у аналогов
            let obj = JSON.parse(JSON.stringify(this.selected_object));
            obj.КоличествоКомнат = this.$store.getters.nameOfNumberRoomsById(obj.КоличествоКомнат);
            obj.МатериалСтен = this.$store.getters.nameOfWallById(obj.МатериалСтен);
            obj.Сегмент = this.$store.getters.nameOfSegmentById(obj.Сегмент);
            obj.Состояние = this.$store.getters.nameOfConditionById(obj.Состояние);

            // Создать массив массивов (количество массивов = количеству ключей у целевого объекта)
            let sort_arr_temp = [];
            Object.keys(this.selected_object).forEach(() => {
                sort_arr_temp.push([]);
            })

            // Проходимся по всем аналогам
            this.analogs.forEach((analog) => {
                // Считаем, сколько у аналога и объекта общих свойств
                let shared_count = 0;
                Object.keys(analog).forEach(key => {
                    if (obj[key] === analog[key]) {
                        ++shared_count;
                    }
                })
                sort_arr_temp[shared_count].push(analog);
            })

            // Убираем пустые массивы
            sort_arr_temp = sort_arr_temp.filter(el => el.length);

            // Заносим в analogs с обратной стороны с переводом структуры массив-массив в массив
            this.analogs = sort_arr_temp.reverse().flat();
        },
        getNameOfRoomsById(id) {
            let name = this.$store.getters.nameOfNumberRoomsById(id);

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
            axios.post('/api/break_calculation')
                .then(() => this.$router.push("/calculator/upload"))
                .finally(() => this.clear_pools_process = false)
        },
        selectAnalog(id) {
            if (this.selected_analogs.find(el => el.id === id)) {
                this.$q.notify({message: 'Аналог уже используется', icon: 'announcement'})
            } else if (this.selected_analogs.length >= 5) {
                this.$q.notify({message: 'Достигнуто максимальное количество выбранных аналогов', icon: 'announcement'})
            } else {
                this.$refs['analog_' + id][0].options.set({'preset': 'islands#redIcon'});
                this.selected_analogs.push(this.analogs.find(el => el.id === id));
            }
        },
        removeSelectAnalog(id) {
            let index = this.selected_analogs.findIndex(el => el.id === id);

            this.$refs['analog_' + id][0].options.set({'preset': 'islands#blackIcon'});
            this.selected_analogs.splice(index, 1);
        },
        calculationSetup() {
            if (this.selected_analogs.length < 3 || this.selected_analogs.length > 5) {
                this.$q.notify({message: 'Для корректности расчётов выберите от 3 до 5 аналогов', icon: 'announcement'})
            } else {
                axios.post('/api/setup_operation', {
                    pool_id: this.$route.params.pool_id,
                    object_id: this.selected_object.id,
                    analogs: this.selected_analogs,
                })
                    .then((response) => this.$router.push("/calculator/operation/" + response.data.data.operation.id))
            }
        },
        endCalc() {
            axios.post('/api/completed_calc_pool', {pool_id: this.$route.params.pool_id,})
                .then((response) => this.$router.push("/calculator"))
        }
    },
    computed: {
        page() {
            // Первичная загрузка так же происходит отсюда
            this.loadData();

            if (this.$route.params.object_id) {
                return "object"
            } else if (this.$route.params.pool_id) {
                return "objects"
            } else {
                return "pools"
            }
        },
    },
    beforeMount() {
        const settings = {
            apiKey: '253b2eae-b322-4893-a57c-3d63323b3558', // Индивидуальный ключ API
            lang: 'ru_RU', // Используемый язык
            coordorder: 'latlong', // Порядок задания географических координат
            debug: false, // Режим отладки
            version: '2.1' // Версия Я.Карт
        }
        this.map = loadYmap({settings});
    },

    // Удаляем notify при переходе со страницы
    beforeRouteLeave(to, from) {
        this.dismiss_notify();
    },
}
</script>

<style lang="scss" scopes>
.yandex-container {
    width: 100vw;
    height: calc(100vh - 138px);
}

.yandex-balloon {
    min-height: 250px;
    width: 220px;
}

.map__view-block {
    max-height: calc(100vh - 138px);
    overflow-y: scroll;
    scroll-behavior: smooth;
}

.custom-y-scroll::-webkit-scrollbar {
    width: 16px;
}

.one-display {
    max-height: 100vh;
    overflow: hidden;
}
</style>
