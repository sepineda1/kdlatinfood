<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceSale extends Model
{
    use HasFactory;

    protected $table = 'service_sale';

    protected $fillable = [
        'sale_id',
        'service_pay_id',
        'amount',
        'deleted',
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'amount' => 'float',
    ];

    public function servicePay()
    {
        return $this->belongsTo(ServicePay::class, 'service_pay_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->where('deleted', 0);
        });
    }
}
