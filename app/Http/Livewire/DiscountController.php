<?php
namespace App\Http\Livewire;
use App\Models\Discounts;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Component
{
    public $nombre = ''; // Ejemplo de propiedad pública.


    public function getDiscountCustomer_API($customer_id)
    {
        
        try {
			//$categories = Category::orderBy('name', 'asc')->get();
            $discounts = Discounts::with('customer','presentacion')->where('customer_id',$customer_id)->get();
			$response = [];
			foreach ($discounts as $discount) {
				$response[] = [
					'id' => $discount->id, // Convierte el ID a una cadena
					'customer' => $discount->customer,
                    'presentacion' =>  $discount->presentacion,
                    'product' =>  $discount->presentacion->product,
                    'discount' =>  $discount->discount,
					'created_at' => $discount->created_at,
					'updated_at' => $discount->updated_at,
				];
			}
			return response()->json($response);

		} catch (\Exception $e) {
			return response()->json([
				'message' => 'An error occurred',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
    }

    public function isDiscountAvailable_API($customer_id, $presentacion_id){
        try {
			//$categories = Category::orderBy('name', 'asc')->get();
            $discount = Discounts::with('customer', 'presentacion')
            ->where('customer_id', $customer_id)
            ->where('presentacion_id', $presentacion_id)
            ->first();

			$response = [];

            if(!empty($discount)){
                $response[] = [
                    'id' => $discount->id, // Convierte el ID a una cadena
                    'customer' => $discount->customer,
                    'presentacion' =>  $discount->presentacion,
                    'product' =>  $discount->presentacion->product,
                    'discount' =>  $discount->discount,
                    'created_at' => $discount->created_at,
                    'updated_at' => $discount->updated_at,
                ];
            }
		
			return response()->json($response);

		} catch (\Exception $e) {
			return response()->json([
				'message' => 'An error occurred',
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
    }


}