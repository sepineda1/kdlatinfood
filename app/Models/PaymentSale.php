<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSale extends Model
{
    use HasFactory;

    protected $table = 'payment_sale';

    protected $fillable = [
        'sale_id',
        'payment_type_id',
        'amount',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(CatalogoPaymentType::class);
    }
}
