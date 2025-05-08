<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\CatalogoPaymentType;
use App\Models\PaymentSale;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\PaymentTypeServiceInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentTypeService implements PaymentTypeServiceInterface
{
    public function getAll(): Collection
    {
        return CatalogoPaymentType::all();
    }

    public function getById(int $id): ?CatalogoPaymentType
    {
        return CatalogoPaymentType::find($id);
    }

    public function addPaymentSale(int $sale_id,int $payment_type_id, float $amount): ?PaymentSale //Agrego los pagos
    {
        try{
            return PaymentSale::create([
                "sale_id" => $sale_id,
                "payment_type_id" => $payment_type_id,
                "amount" => $amount
            ]);
        }catch(Exception $e){  
            Log::error('Error al insertar un tipo de pago : ' . $e->getMessage());
            return null;
        }
    }

    public function getPaymentSaleBySaleId(int $sale_id): Collection //Obtengo los pagos por venta
    {
        try{
            return PaymentSale::where('sale_id', $sale_id)->get();
        }catch(Exception $e){  
            Log::error('Error al insertar un tipo de pago : ' . $e->getMessage());
            return collect();
        }
    }
}

