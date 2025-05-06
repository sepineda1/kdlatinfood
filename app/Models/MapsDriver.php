<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapsDriver extends Model
{
    use HasFactory;

    protected $table = 'maps_driver';

      protected $fillable = [
        'envio_id',
        'location',
        'estado',    
    ];
    public function envio()
    {
        return $this->belongsTo(Envio::class, 'envio_id');
    }
    
}
