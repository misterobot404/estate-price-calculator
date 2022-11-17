<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SettingController extends Controller
{
    public function getSettings($list_id)
    {
        // Пулы для этой группы
        return response()->json([
            "message" => null,
            "data" => [
                "settings" => Setting::where('user_id', auth()->id())->where('Справочники_списки_id', $list_id)->get()
            ]
        ]);
    }

    public function saveSettings()
    {
        $settings = json_decode(request('settings'));

        foreach ($settings as $setting_lc) {
            Setting::where('id', $setting_lc->id)->update(['Данные' => json_encode($setting_lc->Данные)]);
        }

        // Пулы для этой группы
        return response()->json([], 204);
    }

}
