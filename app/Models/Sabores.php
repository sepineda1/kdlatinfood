<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Insumo;

class Sabores extends Model
{
    use HasFactory;
        protected $fillable = ['nombre','descripcion','stock','libra_consumo'];
          public function insumos()
    {
        return $this->hasMany(Insumo::class, 'idSabor', 'id');
    }
}
