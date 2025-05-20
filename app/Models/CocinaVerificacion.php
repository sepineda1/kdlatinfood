<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CocinaVerificacion extends Model
{
    protected $table = 'cocina_verificaciones';

    protected $fillable = [
        'log_id',
        'verificador_id',
        'revisor_id',
        'hora_verificacion',
        'hora_revision_registros',
        'estado'
    ];

    public function log()
    {
        return $this->belongsTo(CocinaLog::class, 'log_id');
    }
    public function verificador()
    {
        return $this->belongsTo(User::class, 'verificador_id');
    }
    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }
}
