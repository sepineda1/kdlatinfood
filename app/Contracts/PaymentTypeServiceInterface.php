<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\CatalogoPaymentType;
use App\Models\PaymentSale;


interface PaymentTypeServiceInterface{

    public function getAll() : Collection;

    public function getById(int $id) : ?CatalogoPaymentType;

    public function addPaymentSale(int $sale_id,int $payment_type_id, float $amount): ?PaymentSale ;

    public function getPaymentSaleBySaleId(int $sale_id): Collection;

}