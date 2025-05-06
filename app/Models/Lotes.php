<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Presentacion;
use App\Models\Category;
use App\Models\Insumo;


class Lotes extends Model
{
    use HasFactory;

    protected $table = 'lotes';
    protected $fillable = [
        'Nombre_Lote',
        'CodigoBarras',
        'Cantidad_Articulos',
        'Fecha_Vencimiento',
        'SKU',
        'User',
        'sabor_id',
        'CustomerID'
    ];
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'sabor_id');
    }
        public function sabor()
    {
        return $this->belongsTo(Sabores::class, 'sabor_id');
    }
    public function producto(){
        return $this->belongsTo(Product::class, 'SKU', 'id');
    }
    public function presentacion(){
        return $this->belongsTo(Presentacion::class, 'SKU', 'id');
    }

    public function cliente()
{
    return $this->belongsTo(Customer::class);
}
 public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'lot_id');
    }


}
