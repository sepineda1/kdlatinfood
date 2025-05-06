<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryType extends Model
{
    use HasFactory;

    protected $table = 'delivery_type';

    protected $fillable = [
        'sale_id',
        'deliverytype_id',
        'date',
    ];

    protected $casts = [ //Convertir los valores cuando los establezco en la columna
        'date'   => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function catalogEntry()
    {
        return $this->belongsTo(CatalogoDeliveryType::class, 'deliverytype_id');
    }

   
}
