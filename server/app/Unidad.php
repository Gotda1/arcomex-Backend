<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = "unidades";

    protected $fillable = ["clave", "abreviatura", "nombre", "status"];
}
