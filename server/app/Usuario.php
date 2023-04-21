<?php

namespace App;


use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $fillable = [
        "owner_id", "usuario_papa_id", "rol", "clave", "nombre", "email", "alias", "telefono", "password", "token_fcm", "status", "usuario_registra"
    ];

    protected $hidden  = ["usuario_registra", "created_at", "updated_at", "password", "token_fcm"];
    public function rol()
    {
        return $this->belongsTo(Rol::class, "rol", "clave");
    }

    public function permisos()
    {
        return $this->hasMany(RelRolPermiso::class, "rol", "rol");
    }

    public function papa()
    {
        return $this->belongsTo(Usuario::class, "usuario_papa_id", "id");
    }

    public function hijos()
    {
        return $this->hasMany(Usuario::class, "usuario_papa_id", "id");
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, "usuario_id", "id");
    }

    public function direccionesAsignadas()
    {
        return $this->belongsToMany(Direccion::class, "asign_direcciones", "usuario_id", "direccion_id")->withPivot("created_at");
    }

    public function permisosAttached()
    {
        return $this->belongsToMany(Permiso::class, "asign_permisos", "usuario_id", "permiso")->withPivot("created_at");
    }

    public function permisosAsignados()
    {
        return $this->hasManyThrough(Permiso::class, AsignPermiso::class, "usuario_id", "clave", "id", "permiso");
    }

    public function ctAsignadas()
    {
        return $this->belongsToMany(ClaseTrabajo::class, "asign_clases_trabajo", "usuario_id", "clase_trabajo_id")->withPivot("created_at");
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ["id" => $this->id];
    }
}
