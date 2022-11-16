<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::created(function ($user) {
            // Создаём базовый список конфигов для этого юзера
            SettingList::insert([
                [
                    'Название_списка' => 'Москва 2022',
                    'user_id' => $user->id
                ]
            ]);

            $setting_list = SettingList::where('user_id', $user->id)->first();

            // Стандартные конфиги для нового юзера
            Setting::insert([
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на торг',
                    'Данные' => '{"rowcolNames": null, "table":-0.045, "pharamName":"bargain", "isPercent": 1}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на состояние отделки',
                    'Данные' => '{"rowcolNames":["Без отделки", "Эконом", "Улучшеный"], "table":[[0, -13400, -20100],[134000, 0, -6700],[20100, 6700, 0]], "pharamName":"renovation", "isPercent":0}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на этаж расположения квартиры',
                    'Данные' => '{"rowcolNames":["Первый этаж", "Средний этаж", "Последний этаж"], "table":[[0, -0.07, -0.031],[0.075, 0, 0.042],[0.032, -0.04, 0]], "pharamName": "floorType", "isPercent": 1}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на площадь кухни',
                    'Данные' => '{"rowcolNames":["<7", "7-10", "10-15"], "table":[[0, -0.029, 0.083],[0.03, 0, -0.055],[-0.09, 0.058, 0]], "pharamName":"squareKitchen", "isPercent":1}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на площадь квартиры',
                    'Данные' => '{"rowcolNames":["<30", "30-50", "50-65", "65-90", "90-120", ">120"], "table":[[0, 0.06, 0.14, 0.21, 0.28, 0.31],[-0.06, 0, 0.07, 0.14, 0.21, 0.24],[-0.12, -0.07, 0, 0.06, 0.13, 0.16],[-0.17, -0.12, -0.06, 0, 0.06, 0.09],[-0.22, -0.17, -0.11, -0.06, 0, 0.03],[-0.24, -0.19, -0.13, -0.08, -0.03, 0]], "pharamName":"square","isPercent":1}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на наличие балкона/лоджии',
                    'Данные' => '{"rowcolNames":["0", "1"], "table":[[0, -0.05],[0.053, 0]], "pharamName":"balcony", "isPercent":1}',
                    'Справочники_списки_id' => $setting_list->id,
                ],
                [
                    'user_id' => $user->id,
                    'Название' => 'Корректировка на расстояние до метро',
                    'Данные' => '{"rowcolNames":["<5", "5-10", "10-15", "15-30", "30-60", "60-90"], "table":[[0, 0.07, 0.12, 0.17, 0.24, 0.29],[-0.07, 0, 0.04, 0.09, 0.15, 0.2],[-0.11, -0.04, 0, 0.05, 0.11, 0.15],[-0.15, -0.08, -0.05, 0, 0.06, 0.1],[-0.19, -0.13, -0.1, -0.06, 0, 0.04],[-0.22, -0.17, -0.13, -0.09, -0.04, 0]], "pharamName":"metroDistance", "isPercent":1}',
                    'Справочники_списки_id' => $setting_list->id,
                ]
            ]);
        });
    }
}
