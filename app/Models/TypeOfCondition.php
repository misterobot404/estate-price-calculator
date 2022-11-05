<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfCondition extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'ТипСостояния';

    protected $fillable = [

    ];
}
