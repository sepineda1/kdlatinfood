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
    protected $fillable = [
        'name',
    ];    
}
