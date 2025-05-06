<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = ['price','quantity','presentaciones_id','sale_id','lot_id','scanned','cajas','discount'];
    public function sales(){
        return $this->hasMany(Sale::class ,'id');
    }
    public function product()
    {
        return $this->belongsTo(Presentacion::class, 'presentaciones_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lotes::class, 'lot_id');
    }

}
