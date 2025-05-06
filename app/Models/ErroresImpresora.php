<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErroresImpresora extends Model
{
    use HasFactory;
    protected $table = 'errores_impresora';

    // Define the primary key
    protected $primaryKey = 'id';

    // Indicates if the model should be timestamped (Laravel uses 'created_at' and 'updated_at' by default)
    public $timestamps = true;

    // Define the columns that are mass assignable
    protected $fillable = [
        'trace_error',
    ];
}
