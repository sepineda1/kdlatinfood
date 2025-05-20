<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CocinaLog extends Model
{
    protected $table = 'cocina_logs';

    protected $fillable = [
        'insumo_id',
        'fecha',
        'ccp_code',
        'observaciones'
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
    public function mediciones()
    {
        return $this->hasMany(CocinaMedicion::class, 'log_id');
    }
    public function verificacion()
    {
        return $this->hasMany(CocinaVerificacion::class, 'log_id');
    }
}
