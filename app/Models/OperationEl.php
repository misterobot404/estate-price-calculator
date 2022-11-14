<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationEl extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'Операция_ОцениваемаяНедвижимость';

    protected $guarded = [];
}
