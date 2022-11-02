<template>
    <div class="column col-grow justify-center relative-position">
        <!-- Нет ни одного загруженного объекта -->
        <template v-if="objects.length">
            <div id="map" style="width: 600px; height: 400px"></div>
        </template>
        <template v-else>
            <div class="position-absolute-center">
                <q-file
                    v-model="file_of_objects"
                    @update:model-value="v => getObjects(v)"
                    borderless
                    bg-color="#FAFAFA"
                    accept=".xlsx, .xls"
                    :input-style="{ height: '500px', width: '800px', borderRadius: '1rem' }"
                />
            </div>
            <div class="position-absolute-center load-file-placeholder">
                <q-img src="/images/load-file-placeholder.svg" width="248px"/>
                <template v-if="file_of_objects_loading">
                    <div class="text-h8 q-mt-md">Получение данных...</div>
                    <q-spinner-gears
                        color="primary"
                        class="q-mt-md"
                        size="6em"
                    />
                </template>
                <template v-else>
                    <div class="text-h8 q-mt-md">Загрузите файл</div>
                    <div class="q-mt-sm">Выберите или перенесите сюда Excel файл со списком<br>объектов недвижимости для оценки</div>
                    <q-btn flat color="primary" label="Образец файла" href="/Example_pool.xlsx" target="_blank" class="example_excel_btn" no-caps/>
                </template>
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            objects: [],
            file_of_objects: null,
            file_of_objects_loading: false,
        }
    },
    methods: {
        getObjects(file) {
            const formData = new FormData();
            formData.append('file_of_objects', file);

            this.file_of_objects_loading = true;
            axios.post('/api/parse_file_of_objects', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                .then(() => {
                    this.objects.push({});
                    // Функция ymaps.ready() будет вызвана, когда
                    // загрузятся все компоненты API, а также когда будет готово DOM-дерево.
                    ymaps.ready(init);

                    function init() {
                        // Создание карты.
                        var myMap = new ymaps.Map("map", {
                            // Координаты центра карты.
                            // Порядок по умолчанию: «широта, долгота».
                            // Чтобы не определять координаты центра карты вручную,
                            // воспользуйтесь инструментом Определение координат.
                            center: [55.76, 37.64],
                            // Уровень масштабирования. Допустимые значения:
                            // от 0 (весь мир) до 19.
                            zoom: 7
                        });
                    }
                })
                .finally(() => this.file_of_objects_loading = false)
        }
    },
}
</script>

<style scoped lang="scss">
.example_excel_btn {
    pointer-events: all;
    margin-top: 16px;
}
.position-absolute-center {
    position: absolute;
    top: 350px;
    left: 50%;
    transform: translate(-50%, -50%);
}

.load-file-placeholder {
    width: 800px;
    height: 520px;
    pointer-events: none;
    background-color: #FAFAFA;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed #1976d2;
    border-radius: 1rem;
    text-align: center;
}
</style>
