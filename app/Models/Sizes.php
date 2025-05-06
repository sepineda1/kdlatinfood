<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sizes extends Model
{
    use HasFactory;
    protected $table = 'sizes';
    protected $fillable = [
        'size',
        'lb'
        
    ];
    public function products()
    {
        return $this->hasMany(Product::class, 'size_id');
    }
}
