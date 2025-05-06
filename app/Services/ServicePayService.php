<?php

namespace App\Services;
use App\Models\ServiceSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\ServicePayServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\CatalogoService;
use App\Models\ServicePay;
class ServicePayService implements ServicePayServiceInterface
{

    public function listCatalog(): Collection
    {
        return CatalogoService::all();
    }
    public function getCatalogById(int $id): CatalogoService
    {
        try {
            return CatalogoService::findOrFail($id);

        }catch (\Exception $e) {
            throw new \Exception("Error: " . $e->getMessage());
        }
    }
    public function addServicePay(int $customer_id, int $catalogo_service_id, $amount, bool $state): ServicePay
    {
        try {
            // Validación de negocio: verificar si ya existe
            $exists = ServicePay::where('customer_id', $customer_id)
                ->where('catalogo_service_id', $catalogo_service_id)
                ->exists();

            if ($exists) {
                //sirve para lanzar manualmente una excepcion
                throw new \Exception("Este servicio ya fue asignado a este cliente.");
            }

            // Crear el nuevo registro
            return ServicePay::create([
                'customer_id' => $customer_id,
                'catalogo_service_id' => $catalogo_service_id,
                'amount' => $amount,
                'deleted' => 0,
                'state' => $state
            ]);
        } catch (\Exception $e) {

            throw new \Exception("Error al crear el ServicePay: " . $e->getMessage());
        }
    }

    public function getServicePay(): Collection
    {
        return ServicePay::all();
    }
    public function getServicePayByCustomerId(int $customer_id): Collection
    {
        return ServicePay::where('customer_id', $customer_id)->get();
    }
    public function getServicePayById(int $id): ServicePay
    {
        try {
            return ServicePay::findOrFail($id);

        }catch (\Exception $e) {
            throw new \Exception("Error: " . $e->getMessage());
        }
    }
    public function getServicePayByCustomerByCatalogId(int $customer_id, int $catalog_id): ServicePay
    {
        return ServicePay::where('customer_id', $customer_id)->where('catalogo_service_id', $catalog_id)->first();
    }

    public function updateServicePay(int $id,$amount,bool $state) : bool
    {
       $service = ServicePay::where('id',$id)->first();
        return $service->update([
            'amount' => $amount,
            'state' => $state,
        ]);
    }

    public function addServiceSale(int $sale_id,int $service_pay_id ,$amount): ServiceSale
    {
        try {
            // Validación de negocio: verificar si ya existe
            $exists = ServiceSale::where('sale_id', $sale_id)
                ->where('service_pay_id', $service_pay_id)
                ->exists();

            if ($exists) {
                //sirve para lanzar manualmente una excepcion
                throw new \Exception("Este servicio ya fue asignado a esta venta");
            }

            // Crear el nuevo registro
            return ServiceSale::create([
                'sale_id' => $sale_id,
                'service_pay_id' => $service_pay_id,
                'amount' => $amount,
                'deleted' => 0
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error al crear el ServiceSale: " . $e->getMessage());
        }
    }

    public function getServiceSaleBySaleId(int $sale_id):ServiceSale
    {
        try {
            return ServiceSale::where('sale_id',$sale_id)->first();

        }catch (\Exception $e) {
            throw new \Exception("Error: " . $e->getMessage());
        }
    }
}
