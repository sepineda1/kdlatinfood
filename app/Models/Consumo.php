<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Presentacion;
use App\Models\Sabores;

class Consumo extends Model
{
    use HasFactory;

    protected $table = 'consumo';

    protected $fillable = [
        'producto_id',
        'presentacion_id',
        'sabor_id',
        'libra_consumo',
        'peso',
    ];

    public function producto()
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'presentacion_id');
    }

    public function sabor()
    {
        return $this->belongsTo(Sabores::class, 'sabor_id');
    }

    public function getConsumoEnLibras(){

        switch ($this->peso) {
            case 'Onzas':
                return $this->libra_consumo / 16;
            case 'Libras':
                return $this->libra_consumo;
            case 'Kilogramos':
                return $this->libra_consumo * 2.20462;
                //return floor($this->sabor->stock * $this->libra_consumo * 2.20462);
        }
    } 
    public function getPYR(){
        switch ($this->peso) {
            case 'Onzas':
                return floor($this->sabor->stock /  ($this->libra_consumo / 16));
            case 'Libras':
                return floor($this->sabor->stock / $this->libra_consumo);
            case 'Kilogramos':
                return floor($this->sabor->stock / ($this->libra_consumo * 2.20462));
        }
    }
}
