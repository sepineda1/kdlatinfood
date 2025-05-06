<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatalogoService extends Model
{
    use HasFactory;

    protected $table = 'catalogo_service';

    protected $fillable = [
        'name',
        'deleted',
    ];

    protected $casts = [
        'deleted' => 'boolean',
    ];

    public function servicePays()
    {
        return $this->hasMany(ServicePay::class, 'catalogo_service_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->where('deleted', 0);
        });
    }
}
