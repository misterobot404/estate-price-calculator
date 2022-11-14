<template>
    <div class="column col-grow justify-center relative-position">
        <div class="position-absolute-center">
            <q-file
                v-model="file_of_objects"
                @update:model-value="v => parseExcel(v)"
                borderless
                bg-color="#FAFAFA"
                accept=".xlsx, .xls"
                :input-style="{ height: '500px', width: '800px', borderRadius: '1rem' }"
            />
        </div>
        <div class="position-absolute-center load-file-placeholder">
            <template v-if="file_of_objects_loading">
                <q-spinner-gears
                    color="primary"
                    class="q-mt-md"
                    size="8em"
                />
                <div class="text-h8 q-mt-md" v-text="loading_msg"/>
            </template>
            <template v-else>
                <q-img src="/images/load-file-placeholder.svg" width="248px"/>
                <div class="text-h8 q-mt-md">Загрузите файл</div>
                <div class="q-mt-sm">Выберите или перенесите сюда Excel файл со списком<br>объектов недвижимости для оценки</div>
                <q-btn flat color="primary" label="Образец файла" href="/Example_dataset.xlsx" target="_blank" class="example_excel_btn" no-caps/>
            </template>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            file_of_objects: null,
            file_of_objects_loading: false,
            loading_msg: "Преобразование Excel файла...",
        }
    },
    methods: {
        parseExcel(file) {
            const formData = new FormData();
            formData.append('file_of_objects', file);

            this.file_of_objects_loading = true;
            axios.post('/api/parse_file_of_objects', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                .then((response) => {
                    this.loading_msg = "Получение координат недвижимости..."
                    // Далее обогащаем записи объектов оценки координатами
                    let array_of_promises = [];
                    let objects = response.data.data.objects;
                    objects.forEach((item, index, arr) => {
                        array_of_promises.push(
                            axios.get("https://geocode-maps.yandex.ru/1.x/?format=json&apikey=253b2eae-b322-4893-a57c-3d63323b3558&geocode=" + item.Местоположение)
                                .then((res) => {
                                    let point = res.data.response.GeoObjectCollection.featureMember[0].GeoObject.Point.pos.split(' ');
                                    arr[index].coordx = point[0];
                                    arr[index].coordy = point[1];
                                })
                        )
                    });
                    Promise.all(array_of_promises).then(() => {
                       axios.post("/api/update_object_coords", {objects: objects})
                            .then(() => this.$router.push("/calculator/pools"))
                            .finally(() => this.file_of_objects_loading = false)
                    });

                })
                .catch(() => {
                    axios.post('/api/break_calculation');

                    this.$q.notify({
                        message: "Ошибка разбора данных. Сравните структуру загружаемого файла с примером.",
                        icon: 'error_outline',
                    })
                    this.file_of_objects_loading = false;
                })
        },

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
