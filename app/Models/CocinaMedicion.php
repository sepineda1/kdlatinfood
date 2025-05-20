<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CocinaMedicion extends Model
{
    protected $table = 'cocina_mediciones';

    protected $fillable = [
        'log_id',
        'user_id',
        'fase',
        'hora',
        'temperatura'
    ];

    public function log()
    {
        return $this->belongsTo(CocinaLog::class, 'log_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
