<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Setting;
use App\Models\SettingList;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SettingListController extends Controller
{
    public function getSettingLists()
    {
        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "setting_lists" => SettingList::where('user_id', auth()->id())->get()
            ]
        ]);
    }

    // При создании списка настроек необходимо так же создать стандартный набор
    public function createSettingList()
    {
        // Создаём новый список справочников
        $new_setting_list = SettingList::create([
            'Название_списка' => request('name'),
            'user_id' => auth()->id()
        ]);

        // Смотрим на основе какого списка справочников необходимо создать новый
        $parent_settings = Setting::where('Справочники_списки_id', request('parent_id'))->get();

        foreach ($parent_settings as $setting) {
            Setting::create([
                'Название' => $setting['Название'],
                'Данные' => $setting['Данные'],
                'Справочники_списки_id' => $new_setting_list->id,
                'user_id' => auth()->id()
            ]);
        }

        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "setting_lists" => SettingList::where('user_id', auth()->id())->get()
            ]
        ]);
    }

    public function deleteSettingList($list_id)
    {
        SettingList::where('id', $list_id)->delete();

        return response()->json([
            "message" => null,
            "data" => [
                "setting_lists" => SettingList::where('user_id', auth()->id())->get()
            ]
        ]);
    }
}
