<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoDeliveryType extends Model
{
    use HasFactory;

    // Si tu tabla no sigue el plural tradicional, especifica el nombre:
    protected $table = 'catalogo_delivery_type';

    // Campos que pueden asignarse masivamente
    protected $fillable = [
        'name',
    ];

    public function deliveryTypes()
    {
        return $this->hasMany(DeliveryType::class, 'deliverytype_id');
    }
}
