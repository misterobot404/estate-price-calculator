<template>
    <div class="q-px-lg q-py-lg">
        <q-timeline :layout="layout" color="secondary">
            <q-timeline-entry title="Приступая к работе" color="blue" side="left">
                <div class="q-pa-md">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Загрузите файл со списком объектов во вкладку 'Оценка недвижимости' "
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA">
                                    Приведите файл в формат .xlsx и создайте в нём структуру, как в
                                    <span> <a href="/Example_pool.xlsx"> шаблоне</a></span>. Нажмите на зону загрузки и выберите файл
                                    со списком объектов. Чтение файла начнётся автоматически. <br>
                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>


            <q-timeline-entry title="Выбор эталона" color="blue" side="right">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Откройте необходимую группу квартир, сгруппированных по количествух комнат и выберите эталон или нажмите на кнопку расчёта, чтобы автоматически рассчитать стоимость объектов"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    Автоматический режим рассчитает стоимость объектов недвижимости на основе алгоритмов машинного
                                    обучения.

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Подбор аналогов" color="blue" side="left">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Система автоматически подберёт аналоги, вам останется лишь выбрать из подобранного списка квартир те, которые являются наиболее подходящими, а затем нажмите «Рассчитать»"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    Добавление аналогов происходит путём нажатия кнопки "Выбрать аналог" внизу карточки объекта. При
                                    нажатии на
                                    "Подробнее", Вы можете ознакомиться с характеристиками объекта. Для отмены выбора нажмите "Отменить
                                    выбор"

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Выбор объектов-аналогов" subtitle="Подбор аналогов" side="right" color="blue"
                              icon="search">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Ознакомьтесь с таблицей объектов сравнения, а также при необходимости измените корректирующие коэффиценты аналогов. Цена эталона будет изменять автоматически при корректировке коэффицентов"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    Для изменения корректирующих коэффицентов аналогов выберите список корректировок,сохранённых во
                                    вкладке "Настройка". Также вы можете отключить применение коэффицента, установив галочку в первом
                                    столбце необходимого коэффицента.

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Расчёт цены всех объектов класса" subtitle="" side="left" color="blue">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Рассчитайте цену для всех квартир данного класса или скачайте расчёт стоимости эталона в формате Excel"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    На этой странице выводится информация о полноте и достоверности информации. Если необходимо
                                    будет пересчитать цену объектов, вы сможете сделать это, нажав на соответствующую кнопку.
                                    При нажатии на кнопку "Рассчитать для всех объектов" будет рассчитана цена стоимости всех объектов в
                                    данном классе. Также будет выведена информация о полноте и достоверности информации. Если необходимо
                                    будет пересчитать цену объектов, вы сможете сделать это, нажав на соответствующую кнопку. <br>
                                    При нажатии на карточку какого-либо объекта в левом меню, откроется страница с подробностями расчёта
                                    цены данного объекта. На данной странице вы также можете произвести какие-либо изменения
                                    корректирующих
                                    коэффицентов

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Итоги расчёта стоимости объектов" subtitle="" side="right" color="blue">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator label="Ознакомьтесь со расчётами цен в группе"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    При нажатии на кнопку "Рассчитать для всех объектов" будет рассчитана цена стоимости всех объектов в
                                    данном классе. Также будет выведена информация о полноте и достоверности информации. Если необходимо
                                    будет пересчитать цену объектов, вы сможете сделать это, нажав на соответствующую кнопку.

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Итоги расчёта стоимости объектов" subtitle="" side="left" color="blue">
                <div class="q-pa-md" style="padding:0% ;">
                    <q-list>
                        <q-expansion-item expand-separator
                                          label="Далее вы можете пересчитать, если цены вас не устроили или экспортировать расчёт в excel или перейти к списку групп"
                                          caption="Разверните, чтобы прочитать подробнее">
                            <q-card>
                                <q-card-section style="background-color: #FAFAFA;">
                                    Для перерасчёта стоимости вы можете вернуться назад, нажав на соответствующую кнопку или
                                    скорректировать необходимые коэффиценты на определённом объекте, нажав на него.

                                </q-card-section>
                            </q-card>
                        </q-expansion-item>
                    </q-list>
                </div>
            </q-timeline-entry>

            <q-timeline-entry title="Повторите шаги выше для остальных групп объектов" subtitle="" side="right" color="blue">
            </q-timeline-entry>
        </q-timeline>
    </div>
</template>

<script>
import {useQuasar} from 'quasar'
import {computed} from 'vue'
import {ref} from 'vue'

export default {
    setup() {
        const $q = useQuasar()

        return {
            layout: computed(() => {
                return $q.screen.lt.sm ? 'dense' : ($q.screen.lt.md ? 'comfortable' : 'loose')
            }),
        }
    }
}
</script>

<style>
.q-timeline__heading-title {
    font-size: 36px !important;
}
</style>
