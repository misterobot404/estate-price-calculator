# -*- coding: cp1251 -*-

import sys
import json
import time
import math
import datetime
import requests
import psycopg2


rows_count = 20000

"""
Необходимое количество записей
"""
if 1 < len(sys.argv):
    rows_count = sys.argv[1]
    print(rows_count)


"""
Функция для подключения к юазе данных
"""
def sql_connect():
    return psycopg2.connect(
        host="192.168.77.66", port="5432", 
        dbname="postgres", 
        user="hackaton", password="p2eEK)J34YMfsJa"
    )


"""
Выполняет запрос к API
kwards: Параметры url
return: список объявлений
"""
def get_data(kwards={}):

    req = "https://ads-api.ru/main/api?user=valerazbanovqs@gmail.com&token=18d79b6cf2715733470f43c0c18d2575&category_id=2&source=1&city=Москва&is_actual=1"

    for key in kwards:
        val = kwards[key]

        if not val is None:
            req += "&{0}={1}".format(key, val)
        
    obj = json.loads(requests.get(req).content)

    if obj["code"] != 200:
        print(req, obj["code"])
        return []

    print(req, obj["code"], len(obj["data"]))
    return obj["data"]


"""
Получает или создаёт значение атрибута в заданной таблице
conn: подключение к базе данных
table_name: название таблицы
value: искомое значение
return: ключ, соответствующий значению
"""
def get_param_id(conn, table_name, value):

    if value is None:
        raise Exception("Значение должно быть определено")

    cursor = conn.cursor()
    sql  = "SELECT id from {0} WHERE Название = '{1}'".format(table_name, value)
    cursor.execute(sql)
    rows = cursor.fetchall()

    if len(rows) <= 0:
        sql  = "INSERT INTO {0}(Название) VALUES (%s) RETURNING Id;".format(table_name);
        cursor.execute(sql, [value])
        conn.commit()

        print("Add", value, "to", table_name)
        return cursor.fetchone()[0]

    return rows[0][0]

"""
Получает атрибут объявления
encoder: конвертирует значение параметра в нужный вид
path: название таблицы
obj: анализируемый объект
default: значение по умолчанию
return: атрибут объявления
"""
def get_param(encoder, path, obj, default):

    for key in path.split('/'):
        if key in obj:
            obj = obj[key]
        else:
            return default;

    return encoder(obj)

"""
Преобразует значение сегмента
"""
class encode_segment():

    def __init__(self, year):
        self.year = year

    def __call__(self, input):

        if input != "Вторичка":
            return input

        if self.year is None:
            return input

        year = self.year
        cur_year = datetime.datetime.now().year

        if cur_year - 4 < year:
            return "Новостройка"

        if 1989 <= year:
            return "Современное жилье"

        if 1930 <= year and year <= 1956:
            return "Сталинка"

        if 1956 <= year and year <= 1985:
            return "Xрущевка"

        if year < 1989:
            return "Старый жилой фонд"

        return input

"""
Преобразует значение материала стен
"""
def encode_wall_material(input):

    if input == "Монолитный":
        return "монолит"

    if input == "Панельный":
        return "панель"       
    
    if input == "Блочный":
        return "блок"

    if input == "Кирпичный":
        return "кирпич"

    if input == "Деревянный":
        return "дерево"

    return None

"""
Преобразует значение площади
"""
def encode_area(input):
    return float(input.split(' ')[0])

"""
Преобразует значение наличия балкона
"""
def encode_balcony(input):
    return 1

"""
Преобразует значение состояния
"""
class encode_condition():

    def __init__(self, отделка):
        self.отделка = отделка

    def __call__(self, input):

        if self.отделка == "Без отделки":
            input = "Муниципальный"
        
        if input == "Без ремонта":
            return "Без отделки"
        else:
            input += " ремонт"

        return input

"""
Преобразует значение строки (одного объявления)
"""
def read_row(row):

    if row["param_1943"] != "Продам":
        raise Exception("Отсутствует информация о типе объявления")

    id = get_param(int, "id", row, 0)
    coordx = get_param(float, "coords/lat", row, None)
    coordy = get_param(float, "coords/lng", row, None)
    Местоположение = get_param(str, "address", row, None)
    КоличествоКомнат = get_param(str, "params/Количество комнат", row, None)
    year = get_param(int, "params2/О доме/Год постройки", row, 0)
    Сегмент = get_param(encode_segment(year), "param_1957", row, None)
    ЭтажностьДома = get_param(int, "params/Этажей в доме", row, None)
    МатериалСтен = get_param(encode_wall_material, "params2/О доме/Тип дома", row, None)
    ЭтажРасположения = get_param(int, "params/Этаж", row, None)
    ПлощадьКвартиры = get_param(encode_area, "params/Площадь", row, None)
    ПлощадьКухни = get_param(encode_area, "params2/О квартире/Площадь кухни", row, ПлощадьКвартиры * 0.15)
    НаличиеБалконаЛоджии = get_param(encode_balcony, "params2/О квартире/Балкон или лоджия", row, 0)
    Метро = get_param(str, "metro", row, None)
    МетроКМ = get_param(float, "km_do_metro", row, None)
    МетроМин = math.ceil(МетроКМ / 5 * 60)
    Отделка = get_param(str, "params2/О квартире/Отделка", row, None)
    Состояние = get_param(encode_condition(Отделка), "params2/О квартире/Ремонт", row, None)
    Стоимость = get_param(float, "price", row, None)

    return {
        "id" : id, 
        "coordx" : coordx, 
        "coordy" : coordy, 
        "Местоположение" : Местоположение,
        "КоличествоКомнат" : КоличествоКомнат,
        "Сегмент" : Сегмент,
        "ЭтажностьДома" : ЭтажностьДома,  
        "МатериалСтен" : МатериалСтен,
        "ЭтажРасположения" : ЭтажРасположения,  
        "ПлощадьКвартиры" : ПлощадьКвартиры,  
        "ПлощадьКухни" : ПлощадьКухни,
        "НаличиеБалконаЛоджии" : НаличиеБалконаЛоджии, 
        "Метро" : Метро,  
        "МетроКМ" : МетроКМ,  
        "МетроМин" : МетроМин,  
        "Состояние"  : Состояние, 
        "Стоимость"  : Стоимость, 
    }

"""
Преобразует значение наличия балкона
"""
def replace_id(conn, obj):
    obj["КоличествоКомнат"] = get_param_id(conn, "ТипКоличестваКомнат", obj["КоличествоКомнат"])
    obj["Сегмент"] = get_param_id(conn, "ТипСегмента", obj["Сегмент"])
    obj["МатериалСтен"] = get_param_id(conn, "ТипМатериалаСтен", obj["МатериалСтен"])
    obj["Состояние"] = get_param_id(conn, "ТипСостояния", obj["Состояние"])
    return obj


if __name__ == "__main__":

    # Производим подключение к базе данных
    conn = sql_connect()
    cursor = conn.cursor()

    # Временной интервал
    step = 30
    date1 = datetime.datetime.now() - datetime.timedelta(hours=7) - datetime.timedelta(minutes=step)
    date2 = datetime.datetime.now() - datetime.timedelta(hours=7)

    count = 0

    # Запрос для добавления нового объекта недвижимости
    sql  = "INSERT INTO Недвижимость VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s) ON CONFLICT (Id) DO NOTHING;"

    while count < rows_count:

        # Получаем блок данных
        batch = get_data({"date1" : date1, "date2" : date2})
        time.sleep(5)

        date1 -= datetime.timedelta(minutes=step)
        date2 -= datetime.timedelta(minutes=step)

        # Перебираем объекты
        for row in batch:

            try:
            
                # Получаем атрибуты
                obj = read_row(row)

                # Кодируем нужные атрибуты
                obj = replace_id(conn, obj)

                # Добавляем объект в базу данных
                val = list(obj.values())
                cursor.execute(sql, val)
                count += 1

                # Фиксируем каждые 100 записей
                if count % 100 == 0:
                    conn.commit()

            except Exception as exc:
                print(exc)



    cursor.close()
    conn.close()




