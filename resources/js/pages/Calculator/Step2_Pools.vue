<template>
    <div class="relative-position">
        <div id="map"></div>
        <div class="absolute-top-left q-pa-lg">
            <transition>
                <q-card v-if="pools">
                    <q-card-section>
                        <div v-for="pool in pools" class="flex">
                            <div>
                                <div class="text-h8" v-text="getNameOfRoomsById(pool.КоличествоКомнат)"/>
                                <div class="text-subtitle2" v-text="pool.КоличествоОбъектов"/>
                            </div>
                        </div>
                    </q-card-section>
                </q-card>
            </transition>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            loading: false,
            pools: null,
        }
    },
    methods: {
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
        initMap() {
            ymaps.ready().then(() => {
                let myMap = new ymaps.Map('map', {
                    center: [55.76, 37.64],
                    zoom: 11,
                }, {autoFitToViewport: 'always'});
                // Удаляем нерабочие кнопки-функции с карты
                myMap.controls.remove('geolocationControl');
                myMap.controls.remove('searchControl');
                myMap.controls.remove('zoomControl');
                myMap.controls.remove('fullscreenControl');
            });
        }
    },
    beforeMount() {
        this.initMap();

        this.loading = true;
        // Получаем данные о пулах
        axios.get('/api/pools')
            .then((response) => {
                this.pools = response.data.data.pools
            })
            .finally(() => this.loading = false)
    }
}
</script>

<style scoped>
#map {
    width: 100vw;
    height: calc(100vh - 138px);
}
</style>
