<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectOfPool extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'ОцениваемаяНедвижимость';

    protected $guarded = ['id'];
}
