<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;
    protected $table = 'carritos';
    protected $fillable = ['items', 'presentaciones_id', 'id_cliente','discount'];

    public function cliente()
    {
        return $this->belongsTo(Customer::class, 'id_cliente');
    }
    public function producto()
    {
        return $this->belongsTo(Presentacion::class, 'presentaciones_id');
    }
}
