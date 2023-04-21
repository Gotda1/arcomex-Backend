<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClasificacionAdquisidor extends Model
{
    protected $table = "clasificaciones_adquisidores";

    protected $hidden  = ["usuario_registra", "created_at", "updated_at"];
}
