<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Discounts;
use App\Models\Presentacion;
use App\Models\Sale;
use App\Models\SaleDetail;
use Exception;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Contracts\DeliveryTypeServiceInterface;
use App\Contracts\ServicePayServiceInterface;
use Carbon\Carbon;
use Log;

class CarritoController extends Controller
{
    protected $deliveryService;
    protected $servicePay;

    public function __construct(DeliveryTypeServiceInterface $deliveryService, ServicePayServiceInterface $servicePay)
    {
        $this->deliveryService = $deliveryService;
        $this->servicePay = $servicePay;
    }
    /**
     * Agregar un producto al carrito.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {

        try {
            $request->validate([
                'presentaciones_id' => 'required|exists:presentaciones,id',
                'id_cliente' => 'required|exists:customers,id',
                'items' => 'required|integer|min:1'
            ]);
    
            $pr = Presentacion::where('id',$request->presentaciones_id)->first();
                if ($pr->visible == 'no') {
                    return response()->json(['success' => false, 'message' => 'El producto esta Agotado'], 409);
                }
            // Verificar si ya existe el producto en el carrito del cliente
            $carrito = Carrito::where('presentaciones_id', $request->presentaciones_id)
                ->where('id_cliente', $request->id_cliente)
                ->first();
    
            if ($carrito) {
                // Si el producto ya está en el carrito, actualizar la cantidad
                $carrito->items += $request->items;
                $carrito->save();
            } else {
     
                Carrito::create([
                    'presentaciones_id' => $request->presentaciones_id,
                    'id_cliente' => $request->id_cliente,
                    'items' => $request->items,
                    'discount' =>  $request->discount
                ]);
            }
    
            return response()->json(['success' => true, 'message' => 'Producto agregado al carrito'], 200);
        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra durante el proceso
            return response()->json(['success' => false, 'message' => 'Erro en el servidor ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener el carrito de un cliente por su ID.
     *
     * @param  int  $id_cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartByCustomer($id_cliente)
    {
        // Cargar el carrito con las relaciones de presentación y producto
        $carrito = Carrito::with(['producto.product', 'cliente.ServicePay.catalogoService'])
            ->where('id_cliente', $id_cliente)
            ->whereHas('cliente.ServicePay', function ($query) {
                $query->where('state', 1)
                    ->where('deleted', 0);  
            })
            ->get();

        if ($carrito->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 200);
        }

        return response()->json(['success' => true, 'data' => $carrito], 200);
    }


    /**
     * Eliminar un producto del carrito.
     *
     * @param  int  $carrito_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function decrementProductFromCart(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|exists:customers,id',
            'presentaciones_id' => 'required|exists:presentaciones,id',
        ]);

        // Buscar el producto en el carrito según el id_cliente y presentaciones_id
        $carrito = Carrito::where('id_cliente', $request->id_cliente)
            ->where('presentaciones_id', $request->presentaciones_id)
            ->first();

        if (!$carrito) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito'], 404);
        }

        // Decrementar la cantidad de items en 1
        if ($carrito->items > 1) {
            $carrito->items -= 1;
            $carrito->save();
            return response()->json(['success' => true, 'message' => 'Cantidad de producto decrementada'], 200);
        } else {
            // Si la cantidad es 1, eliminar el producto del carrito
            $carrito->delete();
            return response()->json(['success' => true, 'message' => 'Producto eliminado del carrito'], 200);
        }
    }


    /**
     * Vaciar todos los productos del carrito de un cliente.
     *
     * @param  int  $id_cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCart($id_cliente)
    {
        $carrito = Carrito::where('id_cliente', $id_cliente)->delete();

        return response()->json(['success' => true, 'message' => 'Carrito vaciado'], 200);
    }

    public function eliminarPresentacion($id_carrito, $id_presentacion)
    {
        try {            
            $carrito = Carrito::where('id', $id_carrito)
                              ->where('presentaciones_id', $id_presentacion)
                              ->first();
        
            if (!$carrito) { 
                return response()->json(['success' => false, 'message' => 'La información es incorrecta'], 401);
            }
        
            $carrito->delete();
        
            return response()->json(['success' => true, 'message' => 'Producto eliminado'], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage() ?: 'Ocurrió un error en el servidor'
            ], 500);
        }  
    }
        
    public function getTotalCartByCustomer($id_cliente)
    {
        // Obtener los productos en el carrito del cliente
        $carrito = Carrito::with('producto') // Relacionamos con el modelo Presentacion
            ->where('id_cliente', $id_cliente)
            ->get();

        if ($carrito->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 404);
        }

        // Calcular el total sumando el precio de cada producto multiplicado por la cantidad
        $total = $carrito->reduce(function ($sum, $item) {
            return $sum + ($item->producto->price * $item->items); // Multiplicamos el precio por la cantidad
        }, 0);

        return response()->json([
            'success' => true,
            'total' => $total
        ], 200);
    }
    public function payCart(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:customers,id'
        ]);

        try {
            // Obtener el cliente
            $cliente = Customer::find($request->cliente_id);

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
            }

            // Obtener el carrito del cliente
            $carrito = Carrito::with('producto')->where('id_cliente', $cliente->id)->get();

            if ($carrito->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 404);
            }

            // Calcular el total del carritoimage.png
            $total = $carrito->reduce(function ($sum, $item) use ($cliente) {
                $discount = Discounts::where('presentacion_id', $item->presentaciones_id)->where('customer_id', $cliente->id)->first(); 
                if($discount){
                    return $sum + (($item->producto->price - ($item->producto->price * $discount->discount)/100) * $item->items);
                }
                return $sum + ($item->producto->price * $item->items);
            }, 0);

            // Verificar si el cliente tiene saldo suficiente
            if ($cliente->saldo < $total) {
                return response()->json(['success' => false, 'message' => 'Saldo insuficiente para realizar la compra'], 400);
            }

            // Restar el saldo del cliente
            $cliente->saldo -= $total;
            $cliente->save();

            // Registrar la venta
            $sale = Sale::create([
                'total' => $total,
                'items' => $carrito->sum('items'),
                'user_id' => 11,
                'CustomerID' => $cliente->id,
            ]);

            // Registrar los detalles de la venta
            foreach ($carrito as $item) {
                SaleDetail::create([
                    'price' => $item->producto->price,
                    'quantity' => $item->items,
                    'presentaciones_id' => $item->presentaciones_id,
                    'sale_id' => $sale->id,
                    'CustomerID' => $cliente->id,
                    'discount' => $item->discount
                ]);

                // Actualizar el stock de los productos
                $producto = $item->producto;
                $producto->stock_box -= $item->items;
                $producto->save();
            }



            // Vaciar el carrito del cliente
            Carrito::where('id_cliente', $cliente->id)->delete();

            // Emitir evento de éxito de la venta
            return response()->json(['success' => true, 'message' => 'Venta registrada con éxito', 'sale_id' => $sale->id], 200);

        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra durante el proceso
            return response()->json(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()], 500);
        }
    }

    public function payCart2(Request $request)
    {
        
        try {
            Log::info('Payload recibido:', $request->all());

            $request->validate([
                'cliente_id' => 'required|exists:customers,id',
                'delivery_type' => 'required|exists:catalogo_delivery_type,id',
                'date' => 'required|date'
            ]);
            // Obtener el cliente
            $cliente = Customer::find($request->cliente_id);

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
            }
            
            $montoService = 0;
            /*if(isset((int) $request->delivery_type != 3)){
               
            }*/

            //control tipo de entrega
            if(isset($request->delivery_type)){
                if((int)$request->delivery_type != 3){
                    $services = $this->servicePay->getServicePayByCustomerId($cliente->id);
                    foreach ($services as $service) {
                        if((int) $service->state === 1){
                            $montoService += $service->amount;
                        }
                    }
                }

                if((int)$request->delivery_type === 2){
                    $horaIngresada = Carbon::parse($request->date);
                    $esHoy = $horaIngresada->isToday();
                    $esAntesDeLas9 = $horaIngresada->lt(Carbon::today()->addHours(9)); // 9:00 AM hoy

                    if (!($esHoy && $esAntesDeLas9)) {
                        //NO Cumple la condición
                        return response()->json(['success' => false, 'message' => 'No se puede enviar el pedido el mismo dia porque el pedido tiene que hacerse antes de las 9AM.'], 404);
                    }
                }
            }
    
            // Obtener el carrito del cliente
            $carrito = Carrito::with('producto')->where('id_cliente', $cliente->id)->get();

            if ($carrito->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 404);
            }

            // Calcular el total del carritoimage.png
            $total = $carrito->reduce(function ($sum, $item) use ($cliente) {
                $discount = Discounts::where('presentacion_id', $item->presentaciones_id)->where('customer_id', $cliente->id)->first(); 
                if($discount){
                    return $sum + (($item->producto->price - ($item->producto->price * $discount->discount)/100) * $item->items);
                }
                return $sum + ($item->producto->price * $item->items);
            }, 0);

            // Verificar si el cliente tiene saldo suficiente
            if ($cliente->saldo < $total) {
                return response()->json(['success' => false, 'message' => 'Saldo insuficiente para realizar la compra'], 400);
            }

            // Restar el saldo del cliente
            $cliente->saldo -= $total + $montoService;
            $cliente->save();

            // Registrar la venta
            $sale = Sale::create([
                'total' => $total,
                'items' => $carrito->sum('items'),
                'user_id' => 11,
                'CustomerID' => $cliente->id,
                'total_with_services' => $total + $montoService
            ]);

            // Registrar los detalles de la venta
            foreach ($carrito as $item) {
                SaleDetail::create([
                    'price' => $item->producto->price,
                    'quantity' => $item->items,
                    'presentaciones_id' => $item->presentaciones_id,
                    'sale_id' => $sale->id,
                    'CustomerID' => $cliente->id,
                    'discount' => $item->discount
                ]);

                // Actualizar el stock de los productos
                $producto = $item->producto;
                $producto->stock_box -= $item->items;
                $producto->save();
            }

            if(isset($sale)){
                //Primero obtengo los servicios
                $services = $this->servicePay->getServicePayByCustomerId($cliente->id);
                foreach ($services as $service) {
                    if((int) $service->state === 1){
                        if((int)$request->delivery_type != 3){ 
                            $this->servicePay->addServiceSale($sale->id,$service->id,$service->amount);
                        }
                    }
                }
                
            }

            try{
                $parsedDate = Carbon::parse($request->date)
    ->format('Y-m-d H:i:s');
                if(isset($request->delivery_type)){
                    $this->deliveryService->add($sale->id, $request->delivery_type, $parsedDate);
                }
            }catch(Exception $e){
                Log::error('No se pudo agregar un tipo de entrega: ' . $e->getMessage());
            }
            

            // Vaciar el carrito del cliente
            Carrito::where('id_cliente', $cliente->id)->delete();

            // Emitir evento de éxito de la venta
            return response()->json(['success' => true, 'message' => 'Venta registrada con éxito', 'sale_id' => $sale->id], 200);

        } catch (\Exception $e) {
            // Manejar cualquier error que ocurra durante el proceso
            return response()->json(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()], 500);
        }
    }

    public function payCart3(Request $request)
    {
        
        try {
            Log::info('Payload recibido:', $request->all());

            $request->validate([
                'cliente_id' => 'required|exists:customers,id',
                'delivery_type' => 'required|exists:catalogo_delivery_type,id',
                'date' => 'required|int'
            ]);

            $fecha = Carbon::createFromTimestampMs((int) $request->date)
            ->format('Y-m-d H:i:s');

            // Obtener el cliente
            $cliente = Customer::find($request->cliente_id);

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
            }
            
            $montoService = 0;
            /*if(isset((int) $request->delivery_type != 3)){
               
            }*/

            //control tipo de entrega
            if(isset($request->delivery_type)){
                if((int)$request->delivery_type != 3){
                    $services = $this->servicePay->getServicePayByCustomerId($cliente->id);
                    foreach ($services as $service) {
                        if((int) $service->state === 1){
                            $montoService += $service->amount;
                        }
                    }
                }

                if((int)$request->delivery_type === 2){
                    $horaIngresada = Carbon::parse($fecha);
                    $esHoy        = $horaIngresada->isToday();
                    $esAntesDeLas9= $horaIngresada->lt(Carbon::today()->addHours(9));
            

                    if (!($esHoy && $esAntesDeLas9)) {
                        //NO Cumple la condición
                        return response()->json(['success' => false, 'message' => 'No se puede enviar el pedido el mismo dia porque el pedido tiene que hacerse antes de las 9AM.'], 404);
                    }
                }
            }
    
            // Obtener el carrito del cliente
            $carrito = Carrito::with('producto')->where('id_cliente', $cliente->id)->get();

            if ($carrito->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío'], 404);
            }

            // Calcular el total del carritoimage.png
            $total = $carrito->reduce(function ($sum, $item) use ($cliente) {
                $discount = Discounts::where('presentacion_id', $item->presentaciones_id)->where('customer_id', $cliente->id)->first(); 
                if($discount){
                    return $sum + (($item->producto->price - ($item->producto->price * $discount->discount)/100) * $item->items);
                }
                return $sum + ($item->producto->price * $item->items);
            }, 0);

            // Verificar si el cliente tiene saldo suficiente
            if ($cliente->saldo < $total) {
                return response()->json(['success' => false, 'message' => 'Saldo insuficiente para realizar la compra'], 400);
            }

            // Restar el saldo del cliente
            $cliente->saldo -= $total + $montoService;
            $cliente->save();

            // Registrar la venta
            $sale = Sale::create([
                'total' => $total,
                'items' => $carrito->sum('items'),
                'user_id' => 11,
                'CustomerID' => $cliente->id,
                'total_with_services' => $total + $montoService
            ]);

            // Registrar los detalles de la venta
            foreach ($carrito as $item) {
                SaleDetail::create([
                    'price' => $item->producto->price,
                    'quantity' => $item->items,
                    'presentaciones_id' => $item->presentaciones_id,
                    'sale_id' => $sale->id,
                    'CustomerID' => $cliente->id,
                    'discount' => $item->discount
                ]);

                // Actualizar el stock de los productos
                $producto = $item->producto;
                $producto->stock_box -= $item->items;
                $producto->save();
            }

            if(isset($sale)){
                //Primero obtengo los servicios
                $services = $this->servicePay->getServicePayByCustomerId($cliente->id);
                foreach ($services as $service) {
                    if((int) $service->state === 1){
                        if((int)$request->delivery_type != 3){ 
                            $this->servicePay->addServiceSale($sale->id,$service->id,$service->amount);
                        }
                    }
                }
                
            }

            try{
                //$parsedDate = Carbon::parse($request->date)->format('Y-m-d H:i:s');
                if(isset($request->delivery_type)){
                    $this->deliveryService->add($sale->id, $request->delivery_type, $fecha);
                }
            }catch(Exception $e){
                Log::error('No se pudo agregar un tipo de entrega: ' . $e->getMessage());
            }
            

            // Vaciar el carrito del cliente
            Carrito::where('id_cliente', $cliente->id)->delete();

            // Emitir evento de éxito de la venta
            return response()->json(['success' => true, 'message' => 'Venta registrada con éxito', 'sale_id' => $sale->id], 200);

        } catch (Exception $e) {
            // Manejar cualquier error que ocurra durante el proceso
            return response()->json(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()], 500);
        }
    }
}
