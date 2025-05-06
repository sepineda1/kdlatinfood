<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\CatalogoService;
use App\Models\ServicePay;
use App\Models\ServiceSale;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

interface ServicePayServiceInterface 
{
    public function listCatalog(): Collection;
    public function getCatalogById(int $id) : CatalogoService;
    public function addServicePay(int $customer_id,int $catalogo_service_id ,float $amount, bool $state) : ServicePay;
    public function getServicePay() :  Collection;
    public function getServicePayByCustomerId(int $customer_id) : Collection;
    public function getServicePayById(int $id) : ServicePay;
    public function getServicePayByCustomerByCatalogId(int $customer_id, int $catalog_id) : ServicePay;
    public function updateServicePay(int $id,$amount,bool $state): bool;
    public function addServiceSale(int $sale_id,int $service_pay_id ,float $amount) : ServiceSale;
    public function getServiceSaleBySaleId(int $sale_id) : ServiceSale;

}   