<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\CatalogoPaymentType;


interface PaymentTypeServiceInterface{

    public function getAll() : Collection;

    public function getById(int $id) : ?CatalogoPaymentType;
}