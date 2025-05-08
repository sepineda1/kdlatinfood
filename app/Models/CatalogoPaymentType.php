<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoPaymentType extends Model
{
    use HasFactory;

    // Si tu tabla no sigue el plural tradicional, especifica el nombre:
    protected $table = 'catalogo_payment_types';

    // Campos que pueden asignarse masivamente
    protected $fillable = ['name', 'deleted'];
    
    protected $casts = [
        'deleted' => 'boolean',
    ];

    public function paymentSales()
    {
        return $this->hasMany(PaymentSale::class, 'payment_type_id');
    }

    // Aplica un filtro global para excluir los eliminados
    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->where('deleted', 0);
        });
    }
}
