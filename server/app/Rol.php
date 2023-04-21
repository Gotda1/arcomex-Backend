<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = "roles";

    public function permisos()
    {
        return $this->hasManyThrough(Permiso::class, RelRolPermiso::class, "rol", "clave", "clave", "permiso");
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, "rol", "clave");
    }

    public function asignaciones()
    {
        return $this->hasManyThrough(Permiso::class, RelRolPermiso::class, "rol", "clave", "clave", "permiso")
            ->where("papa", "ASGN");
    }

    public function tienePermiso($permiso)
    {
        return $this->hasManyThrough(Permiso::class, RelRolPermiso::class, "rol", "clave", "clave", "permiso")
            ->where("clave", $permiso);
    }
}
