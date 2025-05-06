<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Discounts extends Model
{
    use HasFactory;

    protected $table = 'discounts';
     protected $fillable = [
        'customer_id',
        'presentacion_id',
        'discount',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class ,'customer_id');
    }
    public function presentacion(){
        return $this->belongsTo(Presentacion::class, 'presentacion_id', 'id');
    }
}
