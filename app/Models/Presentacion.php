<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'presentaciones';

    // Define the primary key
    protected $primaryKey = 'id';

    // Indicates if the model should be timestamped (Laravel uses 'created_at' and 'updated_at' by default)
    public $timestamps = true;

    // Define the columns that are mass assignable
    protected $fillable = [
        'products_id',
        'sizes_id',
        'barcode',
        'stock_box',
        'alerts',
        'stock_items',
        'price',
        'TieneKey',
        'KeyProduct',
        'costo',
        'visible'
    ];

    public function getFullNameAttribute(): string
    {
        $producto = optional($this->product)->name   ?? '';
        $tam      = optional($this->size)->size      ?? '';
        $estado   = optional($this->product)->estado ?? '';

        return trim("{$producto} {$tam} {$estado}");
    }

    protected $appends = ['full_name'];
    // Define the relationships with the products and sizes models
    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Sizes::class, 'sizes_id', 'id');
    }

    public function lots()
	{
		return $this->belongsTo(Lotes::class);
	}

    public function consumos()
    {
        return $this->hasMany(Consumo::class, 'presentacion_id', 'id');
    }

    public function consumoPorSabor($saborNombre)
    {
        return $this->consumos()
            ->whereHas('sabor', function($query) use ($saborNombre) {
                $query->where('id', $saborNombre);
            })
            ->first(); 
    }

}
