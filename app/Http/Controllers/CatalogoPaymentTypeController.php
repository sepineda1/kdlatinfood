<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentTypeServiceInterface;
use Illuminate\Http\Request;
use Exception;

class CatalogoPaymentTypeController extends Controller
{   
    private $paymentService;

    public function __construct(PaymentTypeServiceInterface $paymentService){
        $this->paymentService = $paymentService;
    }

    public function getAll(){
        return  response()->json(["data" => $this->paymentService->getAll()], 200);
    }

    public function getById(int $id) {
        try {
            $paymentType = $this->paymentService->getById($id);

            if (!$paymentType) {
                return response()->json([
                    "data" => [],
                    'message' => 'Tipo de pago no encontrado'
                ], 404);
            }
            return response()->json(["data" => $paymentType], 200);

        } catch (Exception $e) {
            return response()->json([
                "data" => [],
                'message' => 'Error al obtener el tipo de pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPaymentSale(int $sale_id,int $payment_type_id, float $amount){
        $response = $this->paymentService->addPaymentSale($sale_id, $payment_type_id, $amount);
        if($response === null){
            return  response()->json(["data" => [], "message" => "Hubo un error al agregar." ], 500);
        }
        return  response()->json(["data" => $response], 200);
    }

    public function getPaymentSaleBySaleId(int $sale_id){
        $response = $this->paymentService->getPaymentSaleBySaleId($sale_id);
        if($response === null){
            return  response()->json(["data" => [], "message" => "Hubo un error al agregar." ], 500);
        }
        return  response()->json(["data" => $response], 200);
    }
    
}
