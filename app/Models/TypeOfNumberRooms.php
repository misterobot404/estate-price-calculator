<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfNumberRooms extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'ТипКоличестваКомнат';

    protected $fillable = [

    ];
}
