<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    public function roles()
    {
        return $this->hasManyThrough(Rol::class, RelRolPermiso::class, "permiso", "clave", "clave", "rol");
    }
}
