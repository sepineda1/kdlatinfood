<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sabores;

class Insumo extends Model
{
    use HasFactory;
      protected $fillable = [
        'idSabor',
        'CodigoBarras',
        'Cantidad_Articulos',
        'Fecha_Vencimiento',
        'User',
        'peso'
        
    ];
    public function sabor()
    {
        return $this->belongsTo(Sabores::class, 'idSabor');
    }
      public static function getLotesBySabor($idSabor)
    {
        return self::where('idSabor', $idSabor)->get();
    }

    public function getConsumoEnLibras(){
      switch ($this->peso) {
          case 'Onzas':
              return $this->Cantidad_Articulos / 16;
          case 'Libras':
              return $this->Cantidad_Articulos;
          case 'Kilogramos':
              return $this->Cantidad_Articulos * 2.20462;
              //return floor($this->sabor->stock * $this->libra_consumo * 2.20462);
      }
    }

    public function convertirPeso($a = 'Libras', $valorEnLibras)
    {
        //$valorEnLibras = $this->Cantidad_Articulos;
        switch ($a) {
            case 'Onzas':
                return $valorEnLibras * 16;
            case 'Libras':
                return $valorEnLibras;
            case 'Kilogramos':
                return $valorEnLibras / 2.20462;
            default:
                return null; // opción no válida
        }
    }
}
