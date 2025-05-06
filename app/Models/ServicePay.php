<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServicePay extends Model
{
    use HasFactory;

    protected $table = 'service_pay';

    protected $fillable = [
        'customer_id',
        'catalogo_service_id',
        'amount',
        'deleted',
        'state'
    ];

    protected $casts = [
        'deleted' => 'boolean',
        'amount' => 'float',
    ];

    public function catalogoService()
    {
        return $this->belongsTo(CatalogoService::class, 'catalogo_service_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceSales()
    {
        return $this->hasMany(ServiceSale::class, 'service_pay_id');
    }
    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->where('deleted', 0);
        });
    }
}
