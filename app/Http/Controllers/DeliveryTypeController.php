<?php
namespace App\Http\Controllers;
use Livewire\Component;
use App\Contracts\DeliveryTypeServiceInterface;
use Illuminate\Http\Request;
use Exception;
class DeliveryTypeController extends Controller
{   
    public $service;

    public function __construct(DeliveryTypeServiceInterface $service)
    {
        $this->service = $service;
    }
   
    public function getDetailDeliveryTypeAPI(int $id){
        try{

            $new = $this->service->find($id);
            return response()->json(["data" => $new], 200);
        }
        catch(Exception $e){
            return response()->json(["data" => $e->getMessage()], 500);
        }
    }   

    public function storeDeliveryTypeAPI(Request $request)
    {
        try{
            $data = $request->validate([
                'sale_id'  => 'required|integer|exists:sales,id',
                'deliverytype_id'    => 'required|integer|exists:catalogo_delivery_type,id',
                'date' => 'required|date',
            ]);
    
            $new = $this->service->add($data['sale_id'], $data['deliverytype_id'], $data['date']);
            return response()->json(["data" => $new], 200);
        }
        catch(Exception $e){
            return response()->json(["data" => $e->getMessage()], 500);
        }
    }

    public function updateDeliveryTypeAPI(Request $request, int $id)
    {
        try{
            $data = $request->validate([
                'sale_id'  => 'sometimes|integer|exists:sales,id',
                'deliverytype_id'    => 'sometimes|integer|exists:catalogo_delivery_type,id',
                'date' => 'sometimes|date',
            ]);
            $updated = $this->service->update($id, $data);
            return response()->json(["data" => $updated], 200);

        }catch(Exception $e){
            return response()->json(["data" => $e->getMessage()], 500);
        }
    }

    public function getBySaleDeliveryTypeAPI(int $saleId){
        try{
            $data = $this->service->getBySaleId($saleId);
            return response()->json(["data" => $data], 200);

        }catch(Exception $e){
            return response()->json(["data" => $e->getMessage()], 500);
        }
    }

    public function listCatalog(){
        try{
            $data = $this->service->listCatalog();
            return response()->json(["data" => $data], 200);

        }catch(Exception $e){
            return response()->json(["data" => $e->getMessage()], 500);
        }
    }
}