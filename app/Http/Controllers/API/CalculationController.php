<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ObjectOfPool;
use App\Models\Operation;
use App\Models\OperationEl;
use App\Models\Pool;
use App\Models\Setting;
use App\Models\SettingList;
use App\Models\TypeOfNumberRooms;
use App\Models\TypeOfSegment;
use App\Models\TypeOfWall;
use App\Models\TypeOfCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

        // Получаем все пулы для этой группы
        $all_pools = Pool::where('Группа', $group->id)->get();
        $res_pools = [];
        // Перебираем все пулы
        foreach ($all_pools as $pool) {
            // Проверяем есть ли операция по этому пуллу, если есть с кодом 4, не добавляем в результирующий массив
            $pools_calc_4 = Operation::where('Пул', $pool->id)->where(['Статус' => 4])->first();
            $pools_calc_3 = Operation::where('Пул', $pool->id)->where(['Статус' => 3])->first();
            if (!$pools_calc_4) {
                // Ставим статус - требуется подтверждение
                if ($pools_calc_3) {
                    $pool->status = 'Требуется подтверждение';
                    $res_pools[] = $pool;
                } // Ставим статус - не расчитан
                else {
                    $pool->status = 'Не расчитано';
                    $res_pools[] = $pool;
                }
            }
        }

        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "pools" => $res_pools
            ]
        ]);
    }

    public function getObjects($id)
    {
        $oper_meta = Operation::where('Пул', $id)->where(['Статус' => 3])->first();
        // Данный пул имеет 3 статут - расчитан, но не подтверждён
        if ($oper_meta) {
            // Получаем список цен на квартиры этого пула
            $operations = OperationEl::where('Операция', $oper_meta->id)->get();
        }

        // Объекты для этого пула
        return response()->json([
            "message" => null,
            "data" => [
                "objects_price" => $oper_meta ? $operations : null,
                "objects" => ObjectOfPool::where('Пул', $id)->get()
            ]
        ]);
    }

    public function getObjectAndAnalogs($pool_id, $object_id)
    {
        $object = ObjectOfPool::where('id', $object_id)->first();

        $analogs = (DB::select(
            'SELECT * from НайтиАналоги('.$object->КоличествоКомнат.','.$object->Сегмент.','.$object->ЭтажностьДома.','.$object->МатериалСтен.','.$object->ЭтажРасположения.','.$object->ПлощадьКвартиры.','.$object->ПлощадьКухни.','.$object->НаличиеБалконаЛоджии.','.$object->МетроМин.','.$object->Состояние.')'
        ));

        // Объекты для этого пула
        return response()->json([
            "message" => null,
            "data" => [
                "object" => $object,
                "analogs" => $analogs
            ]
        ]);
    }

    public function getAllCalculationObjects()
    {
        // Текущая расчитываемая группа
        $group = Group::where('Пользователь', auth()->id())->where('Статус', null)->first();


        // Текущие расчитываемые пулы
        $pools = Pool::where('Группа', $group->id)->get();
        $objects = [];
        foreach ($pools as $pool) {
            array_push($objects, ...ObjectOfPool::where('Пул', $pool->id)->get());
        }


        // Объекты для этого пула
        return response()->json([
            "message" => null,
            "data" => [
                "objects" => $objects
            ]
        ]);
    }

    public function breakCalculation()
    {
        Group::where('Пользователь', auth()->id())->delete();

        return response()->json([
            "message" => null,
            "data" => null
        ], 204);
    }

    public function completedCalculationPool()
    {
        $oper_meta = Operation::where('Пул', request('pool_id'))->where(['Статус' => 3])->update(['Статус' => 4]);

        return response()->json([
            "message" => null,
            "data" => null
        ], 204);
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
        $result_objects_db = [];
        foreach ($objects as $object) {
            // Проверяем существует ли такой пул, если пул не был создан, то создаём новый
            if (!in_array($object['1'], array_column($created_pools, "КоличествоКомнат"), true)) {
                // Получаем id для этого типа количества комнат
                TypeOfNumberRooms::where('Название', $object['1'])->first();

                // Создаём новый пул в базе
                $pool = Pool::create([
                    'Группа' => $group->id,
                    'КоличествоКомнат' => TypeOfNumberRooms::where('Название', $object['1'])->first()->id,
                    "КоличествоОбъектов" => 0,
                    "user_id" => auth()->id()
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
            $result_objects_db[] = ObjectOfPool::create([
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
        // Необходимо вернуть список созданных объектов для дальнейшего обогащения
        return response()->json([
            "message" => "Данные успешно загружены. Необходимо добавить координаты",
            "data" => [
                "objects" => $result_objects_db
            ]
        ]);
    }

    public function updateObjectCoords()
    {
        $objects = request('objects');
        // Устанавливаем поле КоличествоОбъектов для пулов
        foreach ($objects as $el) {
            $object = ObjectOfPool::find($el["id"]);
            $object->coordx = $el["coordy"];
            $object->coordy = $el["coordx"];
            $object->save();
        }

        // Формируем окончательный ответ в нужном формате
        // Необходимо вернуть список созданных объектов для дальнейшего обогащения
        return response()->json([], 204);
    }

    public function setupOperation()
    {
        Operation::where('Пул', request('pool_id'))->delete();

        $operation = Operation::where(['Пул' => request('pool_id')]);
        if ($operation) {
            Operation::where(['Пул' => request('pool_id')])->delete();
        }


        $operation = Operation::create([
            'Пул' => request('pool_id'),
            'Эталон' => request('object_id'),
            'Аналоги' => json_encode(request('analogs'))
        ]);


        return response()->json([
            "message" => null,
            "data" => [
                'operation' => Operation::where('Пул', request('pool_id'))->first()
            ]
        ]);
    }

    public function saveOperations()
    {
        // Сохраняем информацию в Операции
        // Сохраняем коэффициенты которые были использованы для расчёта эталона и других объектов пула
        Operation::where('Пул', request('operation_meta')["pool_id"])->update(['Коэффициенты' => json_encode(request('operation_meta')['coof'])]);
        // Статус для операции
        // 3 - Не подтверждён
        // 4 - Расчёт по пулу окончен
        Operation::where('Пул', request('operation_meta')["pool_id"])->update(['Статус' => 3]);

        $operation = Operation::where('Пул', request('operation_meta')["pool_id"])->first();

        // Записываем все операции которые были сделаны
        foreach (request('operations') as $value) {
            OperationEl::create([
                'Операция' => $operation->id,
                'ОцениваемыйОбъект' => $value['object_id'],
                'Стоимость' => $value['price_m']
            ]);
        }
        return response()->json([
            "message" => null,
            "data" => []
        ], 204);
    }

    public function getOperation($operation_id)
    {
        $operation = Operation::find($operation_id)->orderBy('id', 'desc')->first();

        return response()->json([
            "message" => null,
            "data" => [
                'operation' => $operation,
                'object' => ObjectOfPool::where('id', $operation['Эталон'])->first(),
            ]
        ]);
    }

    // Получить историю расчёта пулов
    public function getHistory()
    {
        // Все пулы пользователя
        $pools = Pool::where('user_id', auth()->id())->get();

        // Выбираем пулы, статус расчета по которым 4 (завершён)
        $history = [];
        foreach ($pools as $pool) {
            // Проверяем статус пула
            if (Operation::where('Пул', $pool->id)->where('Статус', 4)->first()) {
                $history[] = $pool;
            }
        }

        // Объекты для этого пула
        return response()->json([
            "message" => null,
            "data" => [
                "history" => $history,
            ]
        ]);
    }

    // Получить историю расчёта пулов
    public function getExcel($id)
    {
        // Все пулы пользователя
        $pool = Pool::where('id', $id)->first();

        // Получаем все объекты из этого пула
        $objects = ObjectOfPool::where('Пул', $pool->id)->get();

        // Получаем цену для объектов пула
        $operation_meta = Operation::where('Пул', $pool->id)->where(['Статус' => 4])->first();
        $operations = OperationEl::where('Операция', $operation_meta->id)->get();


        // Создаём структуру
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Местоположение');
        $sheet->setCellValue('B1', 'Количество комнат');
        $sheet->setCellValue('C1', 'Сегмент');
        $sheet->setCellValue('D1', 'Этажность дома');
        $sheet->setCellValue('E1', 'Материал стен');
        $sheet->setCellValue('F1', 'Этаж расположения');
        $sheet->setCellValue('G1', 'Площадь квартиры, кв.м');
        $sheet->setCellValue('H1', 'Площадь кухни, кв.м');
        $sheet->setCellValue('I1', 'Наличие балкона/лоджии');
        $sheet->setCellValue('J1', 'Удаленность от станции метро, мин. пешком');
        $sheet->setCellValue('K1', 'Состояние');
        $sheet->setCellValue('L1', 'Рассчитанная стоимость');
        $sheet->setCellValue('M1', 'Рассчитанная стоимость за метр');

        $i = 2; // Начинаем с двойки, нумерации в таблице идёт с 1-цы
        foreach ($objects as $key => $row) {
            $sheet->setCellValue('A'.$i, $row['Местоположение']);
            $sheet->setCellValue('B'.$i, (TypeOfNumberRooms::where('id', $row['КоличествоКомнат'])->first())['Название']);
            $sheet->setCellValue('C'.$i, (TypeOfSegment::where('id', $row['Сегмент'])->first())['Название']);
            $sheet->setCellValue('D'.$i, $row['ЭтажностьДома']);
            $sheet->setCellValue('E'.$i, (TypeOfWall::where('id', $row['МатериалСтен'])->first())['Название']);
            $sheet->setCellValue('F'.$i, $row['ЭтажРасположения']);
            $sheet->setCellValue('G'.$i, $row['ПлощадьКвартиры']);
            $sheet->setCellValue('H'.$i, $row['ПлощадьКухни']);
            $sheet->setCellValue('I'.$i, $row['НаличиеБалконаЛоджии']);
            $sheet->setCellValue('J'.$i, $row['МетроМин']);
            $sheet->setCellValue('K'.$i, (TypeOfCondition::where('id', $row['Состояние'])->first())['Название']);
            $sheet->setCellValue('L'.$i, $operations[$key]['Стоимость'] * $row['ПлощадьКвартиры']);
            $sheet->setCellValue('M'.$i, $operations[$key]['Стоимость']);
            $i++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $writer = new Xlsx($spreadsheet);
        $writer->save('excel/'.$pool->id.'.xlsx');


        return response()->download(public_path('excel/'.$pool->id.'.xlsx'));
    }

    public function getExcelWithML($user_id)
    {
        // Текущая расчитываемая группа
        $group = Group::where('Пользователь', $user_id)->first();

        // Получаем все пулы этого пользователя
        $all_pools = Pool::where('Группа', $group->id)->get();

        $objects = [];

        // Перебираем все пулы
        foreach ($all_pools as $pool) {
            // Проверяем есть ли операция по этому пуллу, если есть с кодом 4, не добавляем в результирующий массив
            $pools_calc_4 = Operation::where('Пул', $pool->id)->where(['Статус' => 4])->first();
            if (!$pools_calc_4) {
                foreach (ObjectOfPool::where('Пул', $pool->id)->get() as $object) {
                    $objects[] = $object;
                }
            }
        }

        $res_values = [];
        // Для каждого элемента получаем цену из нейронной сети
        foreach ($objects as &$object) {
            $response = Http::post('http://127.0.0.1:5000/price', [
                0 => [
                    "coordx" => (float)$object['coordx'],
                    "coordy" => (float)$object['coordy'],
                    "КоличествоКомнат" => $object['КоличествоКомнат'],
                    "Сегмент" => $object['Сегмент'],
                    "ЭтажностьДома" => $object['ЭтажностьДома'],
                    "МатериалСтен" => $object['МатериалСтен'],
                    "ЭтажРасположения" => $object['ЭтажРасположения'],
                    "ПлощадьКвартиры" => (int)$object['ПлощадьКвартиры'],
                    "ПлощадьКухни" => (int)$object['ПлощадьКухни'],
                    "НаличиеБалконаЛоджии" => $object['НаличиеБалконаЛоджии'],
                    "МетроМин" => (int)$object['МетроМин'],
                    "Состояние" => $object['Состояние']
                ]
            ]);
            $res_values[] = json_decode($response->body())[0];
        }

        // Создаём структуру
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Местоположение');
        $sheet->setCellValue('B1', 'Количество комнат');
        $sheet->setCellValue('C1', 'Сегмент');
        $sheet->setCellValue('D1', 'Этажность дома');
        $sheet->setCellValue('E1', 'Материал стен');
        $sheet->setCellValue('F1', 'Этаж расположения');
        $sheet->setCellValue('G1', 'Площадь квартиры, кв.м');
        $sheet->setCellValue('H1', 'Площадь кухни, кв.м');
        $sheet->setCellValue('I1', 'Наличие балкона/лоджии');
        $sheet->setCellValue('J1', 'Удаленность от станции метро, мин. пешком');
        $sheet->setCellValue('K1', 'Состояние');
        $sheet->setCellValue('L1', 'Рассчитанная стоимость');
        $sheet->setCellValue('M1', 'Рассчитанная стоимость за метр');

        $i = 2; // Начинаем с двойки, нумерации в таблице идёт с 1-цы
        foreach ($objects as $key => $row) {
            $sheet->setCellValue('A'.$i, $row['Местоположение']);
            $sheet->setCellValue('B'.$i, (TypeOfNumberRooms::where('id', $row['КоличествоКомнат'])->first())['Название']);
            $sheet->setCellValue('C'.$i, (TypeOfSegment::where('id', $row['Сегмент'])->first())['Название']);
            $sheet->setCellValue('D'.$i, $row['ЭтажностьДома']);
            $sheet->setCellValue('E'.$i, (TypeOfWall::where('id', $row['МатериалСтен'])->first())['Название']);
            $sheet->setCellValue('F'.$i, $row['ЭтажРасположения']);
            $sheet->setCellValue('G'.$i, $row['ПлощадьКвартиры']);
            $sheet->setCellValue('H'.$i, $row['ПлощадьКухни']);
            $sheet->setCellValue('I'.$i, $row['НаличиеБалконаЛоджии']);
            $sheet->setCellValue('J'.$i, $row['МетроМин']);
            $sheet->setCellValue('K'.$i, (TypeOfCondition::where('id', $row['Состояние'])->first())['Название']);
            $sheet->setCellValue('L'.$i, $res_values[$key]);
            $sheet->setCellValue('M'.$i, (int)($res_values[$key] / $row['ПлощадьКвартиры']));
            $i++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $writer = new Xlsx($spreadsheet);
        $file_name = "Нейронная сеть";
        $writer->save('excel/'.$file_name.'.xlsx');

        return response()->download(public_path('excel/'.$file_name.'.xlsx'));
    }
}
