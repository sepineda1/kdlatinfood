<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Operario;
use App\Models\MapsDriver;
class Envio extends Model
{
    use HasFactory;
    protected $fillable =['id_sale','id_transport'];

    public function sales(){
        return $this->belongsTo(Sale::class, 'id_sale');
    }
    public function transport(){
        return $this->belongsTo(Sale::class, 'id_transport');
    }
    public function operario(){
        return $this->belongsTo(Operario::class);
    }
      public function clientes(){
        return $this->belongsTo(Customer::class);
    }
    public function maps_driver(){
        return $this->belongsTo(MapsDriver::class,'id','envio_id');
    }

}
