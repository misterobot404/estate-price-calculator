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
    /*public function createSettingList()
    {
        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "settings" => Setting::where('user_id', auth()->id())->where('Год', $year)->get()
            ]
        ]);
    }*/

    /*public function saveSettingList($list_id)
    {
        $settings = json_decode(request('settings'));

        foreach ($settings as $setting_lc) {
            Setting::where('id', $setting_lc->id)->update(['Данные' => json_encode($setting_lc->Данные)]);
        }

        // Пулы для этой группы
        return response()->json([], 204);
    }*/
}
