<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ObjectOfPool;
use App\Models\Pool;
use App\Models\TypeOfHaveBalcon;
use App\Models\TypeOfNumberRooms;
use App\Models\TypeOfSegment;
use App\Models\TypeOfWall;
use App\Models\TypeOfCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;

class CalculationController extends Controller
{
    public function getCalculationStatus()
    {
        // Формируем окончательный ответ в нужном формате
        return response()->json([
            "message" => null,
            "data" => [
                "calculation_status" => Group::where('Пользователь', auth()->id())->where('Статус', null)->exists()
            ]
        ]);
    }

    public function getReferenceBooks()
    {
        return response()->json([
            "message" => null,
            "data" => [
                "type_of_condition" => TypeOfCondition::all(),
                "type_of_number_rooms" => TypeOfNumberRooms::all(),
                "type_of_segment" => TypeOfSegment::all(),
                "type_of_wall" => TypeOfWall::all()
            ]
        ]);
    }

    public function getPools()
    {
        // Текущая расчитываемая группа
        $group = Group::where('Пользователь', auth()->id())->where('Статус', null)->first();

        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "pools" => Pool::where('Группа', $group->id)->get()
            ]
        ]);
    }

    /* Входные данные Excel.
    Ключ ячейки - значение:
    0 => Адрес
    1 => Количество комнат
    2 => Сегмент (Новостройка, современное жилье, старый жилой фонд)
    3 => Этажность дома
    4 => Материал стен (Кирпич, панель, монолит)
    5 => Этаж расположения
    6 => Площадь квартиры, кв.м
    7 => Площадь кухни, кв.м
    8 => Наличие балкона/лоджии
    9 => Удаленность от станции метро, мин. пешком
    10 => Состояние (без отделки, муниципальный ремонт, с современная отделка)
    */
    public function parseFileOfObjects()
    {
        // Парсим excel для получения объектов для оценки
        $spreadsheet = IOFactory::load(request('file_of_objects'));
        $sheet = $spreadsheet->getActiveSheet();
        $objects = $sheet->toArray();
        array_shift($objects);

        // В таблицу "Группы" добавляется новая строка, передаём только id пользователя,
        // Статус: NULL - новая, 1 - посчитано архив, 2 - не посчитано архив.
        // Получаем обратно id группы
        $group = Group::create([
            'Пользователь' => auth()->id()
        ]);

        // В таблицу "Пул" надо вставить строки для тех пулов которые есть в файле
        // Параметры: id группы, id из таблицы ТипКоличестваКомнат
        $created_pools = [];
        // Перебираем все объекты недвижимости. Добавляем их в таблицу недвижимости и создаём для них пул по необходимости
        foreach ($objects as $object) {
            // Проверяем существует ли такой пул, если пул не был создан, то создаём новый
            if (!in_array($object['1'], array_column($created_pools, "КоличествоКомнат"), true)) {
                // Получаем id для этого типа количества комнат
                TypeOfNumberRooms::where('Название', $object['1'])->first();

                // Создаём новый пул в базе
                $pool = Pool::create([
                    'Группа' => $group->id,
                    'КоличествоКомнат' => TypeOfNumberRooms::where('Название', $object['1'])->first()->id,
                    "КоличествоОбъектов" => 0
                ]);

                // Сохраняем добавленный пул в массив для уменьшения количества запросов к базе
                $created_pools[] = [
                    "id" => $pool->id,
                    "КоличествоКомнат" => $object['1'],
                    "КоличествоОбъектов" => 0,
                ];
            }

            // Получаем пул в который включена эта квартира
            $using_pool = null;
            foreach ($created_pools as $index => $pool) {
                if ($pool['КоличествоКомнат'] === $object['1']) {
                    ++$created_pools[$index]["КоличествоОбъектов"];
                    $using_pool = $pool;
                    break;
                }
            }

            // В таблицу "ОцениваемаяНедвижимость" добавляем данные из файла.
            // Передаём id пула, соответствующего конкретной записи, и параметры недвижимости.
            // Надо вставлять ключи из ТипКоличестваКомнат, ТипСегмента, ТипМатериалаСтен, ТипНаличияБалконаЛоджии, ТипСостояния.
            ObjectOfPool::create([
                'Пул' => $using_pool['id'],
                'Местоположение' => $object['0'],
                'КоличествоКомнат' => TypeOfNumberRooms::where('Название', $using_pool['КоличествоКомнат'])->first()->id,
                'Сегмент' => TypeOfSegment::where('Название', $object['2'])->first()->id,
                'ЭтажностьДома' => $object['3'],
                'МатериалСтен' => TypeOfWall::where('Название', $object['4'])->first()->id,
                'ЭтажРасположения' => $object['5'],
                'ПлощадьКвартиры' => $object['6'],
                'ПлощадьКухни' => $object['7'],
                'НаличиеБалконаЛоджии' => $object['8'] === "Да" ? 1 : 0,
                'МетроМин' => $object['9'],
                'Состояние' => TypeOfCondition::where('Название', $object['10'])->first()->id
            ]);
        }

        // Устанавливаем поле КоличествоОбъектов для пулов
        foreach ($created_pools as $pool) {
            Pool::where('id', $pool['id'])->update(['КоличествоОбъектов' => $pool["КоличествоОбъектов"]]);
        }

        // Формируем окончательный ответ в нужном формате
        return response()->json(["message" => "Данные успешно загружены", "data" => null], 204);
    }
}
