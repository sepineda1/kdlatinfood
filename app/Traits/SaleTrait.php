<?php
namespace App\Traits;
use App\Models\SaleDetail;
use App\Models\Sale;
use App\Models\Presentacion;
use App\Models\Discounts;
use App\Models\User;
use App\Models\Inspectors;
use App\Models\Customer;
use App\Services\QuickBooksService;
use Exception;
use Illuminate\Support\Facades\DB;
trait SaleTrait{


    // ----------------------------------------------------------------
    // AGREGAR UN PRODUCTO A LA VENTA
    // ----------------------------------------------------------------
    public function addProductoToSaleCore($saleId, $barcode, $quantity, $responseJSON = false, $userID = null)
    {
        try {

            $ModelSale = Sale::find($saleId);
            if (!isset($ModelSale->id)) {
                return $this->responses('La ORDEN no existe', 404, $responseJSON, 'sale-error');
            }

            $ModelPresentacion = Presentacion::where('barcode', $barcode)->first();

            if (isset($ModelPresentacion->id)) {

                if ($ModelPresentacion->visible === "no") {
                    return $this->responses('La presentacion NO se encuentra disponible.', 404, $responseJSON, 'sale-error');
                }
            } else {
                return $this->responses('La presentacion NO existe.', 404, $responseJSON, 'sale-error');
            }

            if ($quantity == 0 || $quantity < 0) {
                return $this->responses('La cantidad NO puede ser CERO o menor a CERO.', 404, $responseJSON, 'sale-error');
            }

            if ($ModelSale->customer->saldo <= 0) {
                return $this->responses('El cliente NO tiene saldo Disponible.', 404, $responseJSON, 'sale-error');
            }

          
            $isUpdate = false;
            $Discount = 0;

            $presentacionExist = SaleDetail::where('sale_id', $saleId)->where('presentaciones_id', $ModelPresentacion->id)->first();
            $discount = Discounts::where('presentacion_id', $ModelPresentacion->id)->where('customer_id', $ModelSale->CustomerID)->first();

            if ($presentacionExist) {

                if ($presentacionExist->discount > 0) {

                    if ($discount) {

                        if ($discount->discount != $presentacionExist->discount) {
                            $Discount = $discount->discount;
                        } else {
                            $isUpdate = true;
                            $Discount = $discount->discount;
                        }
                    } else {

                        $Discount = 0;
                    }
                } else {

                    if ($discount) {
                        $Discount = $discount->discount;
                    } else {
                        $isUpdate = true;
                    }
                }

            } else {
                if ($discount) {
                    $Discount = $discount->discount;
                }
            }

            $price = $ModelPresentacion->price;
            if ($Discount > 0) {
                $price -= ($Discount * $price) / 100;
            }

            $saldo = $ModelSale->customer->saldo - ($price * $quantity);
            if ($saldo <= 0) {
                return $this->responses('El cliente NO tiene saldo Disponible.', 404, $responseJSON, 'sale-error');
            }

            DB::beginTransaction();
            if ($isUpdate) { //Si actualiza
                $ModelPresentacion->stock_box += $presentacionExist->quantity;
                $ModelPresentacion->save();

                $presentacionExist->quantity += $quantity;
                $presentacionExist->save();

                $ModelPresentacion->stock_box -= $presentacionExist->quantity;
                $ModelPresentacion->save();


            } else { //Si agrego

                $newSaleDetail = new SaleDetail();
                $newSaleDetail->sale_id = $saleId;
                $newSaleDetail->presentaciones_id = $ModelPresentacion->id;
                $newSaleDetail->quantity = $quantity;
                $newSaleDetail->price = $ModelPresentacion->price;
                if ($Discount > 0) {
                    $newSaleDetail->discount = $Discount;
                }
                
                $newSaleDetail->save();

                if($ModelSale->status_envio === "ACTUAL"){
                    //Simular escaneo
                    $LoteForPresentacion = $this->haveLot($ModelPresentacion->id);
                    $newSaleDetail->lot_id = $LoteForPresentacion->id;
                    $newSaleDetail->scanned = true;
                    $newSaleDetail->save();
                    $ModelSale->fecha_escaneo = now();
                    $ModelSale->save();
                }
     
                $ModelPresentacion->stock_box -= $newSaleDetail->quantity;
                $ModelPresentacion->save();
            }

            $price = $ModelPresentacion->price;
            if ($Discount > 0) {
                $price = $ModelPresentacion->price - ($Discount * $ModelPresentacion->price) / 100;
            }
            $ModelSale->total += $price * $quantity;
            $ModelSale->items += $quantity;
            $ModelSale->save();

            if ($ModelSale->cash > 0) { 
                $ModelSale->cash += $price * $quantity; 
                $ModelSale->save();

            }else{
                $ModelCostumer = Customer::find($ModelSale->customer->id);
                $ModelCostumer->saldo -= $price * $quantity;
                $ModelCostumer->save();
            } 

            if(!$responseJSON){
                $userID = Auth()->user()->id;
            }
            if($userID != null){
                try{
                    $user = User::find($userID);
                    $accion = sprintf(
                        'Se agregó %d unid. (sin escanear) del producto #%d %s a la orden #%d (estado: %s)',
                        $quantity,
                        $ModelPresentacion->id,
                        $ModelPresentacion->full_name,
                        $ModelSale->id,
                        $ModelSale->status_envio
                    );
                    Inspectors::create([
                        'user' => $user->name,
                        'action' => $accion,
                        'seccion' => 'Despachos'
                    ]);
                }catch(Exception $e){
                    \Log::error('No se pudo agregar el mensaje al inspector en la seccion de agregar producto a la orden: ' . $e);
                }
 
            }
            $QUICK = new QuickBooksService();
            $QUICK->updateInvoice($saleId);

            if($responseJSON){
                DB::commit();
                return $this->responses('Producto agregado a la venta con éxito.', 200, $responseJSON, 'global-msg');

            }else{
                DB::commit();
                /*$this->detailsEdit = SaleDetail::where('sale_id', $saleId)->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($saleId);
                $this->details = SaleDetail::where('sale_id', $saleId)->get();
                $this->emit('global-msg', "Producto agregado a la venta");
                $this->emit('producto-creado');
                $this->dispatchBrowserEvent('hideModalSaldo');
                $this->newProducts = [
                    'sku' => '',
                    'name' => '',
                    'items' => 0,
                    'price' => 0,
                    'discount' => '0.00',
                    'total' => ''
                ];*/
                return true;
            }

        } catch (Exception $e) {
            DB::rollBack();
            return $this->responses($e->getMessage(), 500, $responseJSON, 'global-msg');

        }

    }
    // ----------------------------------------------------------------
    // DECREMENTAR PRODUCTO DE LA VENTA
    // ----------------------------------------------------------------

    public function decrementQuantityCore($quantity,$saleDetailId,$isCash = false,$responseJSON = true, $userID = null){
   
        $saleDetail = SaleDetail::find($saleDetailId);
        if(!$saleDetail){
            return $this->responses('No existe la orden.', 404, $responseJSON, 'sale-error');
            
        }

        if($quantity < 1){
            return $this->responses('No se puede DECREMENTAR 0 productos.', 404, $responseJSON, 'sale-error');
        }

        if((int) $saleDetail->quantity - (int) $quantity < 0){
            return $this->responses('La cantidad de producto que deseas quitar excede la cantidad existente.', 404, $responseJSON, 'sale-error');
        }

        try{
            $SaleDeleted = false;
            DB::beginTransaction();
            //agregar en inventario la cantidad decrementada
            $presentacion = Presentacion::find($saleDetail->presentaciones_id);
            $presentacion->stock_box += $quantity;
            $presentacion->save();

            //dinero en Producto
            $cashSobrante = ($saleDetail->price - ($saleDetail->price * $saleDetail->discount) / 100) * $quantity;

        
            //busco en la venta cuantos items son y actualizo
            $Sale = Sale::find($saleDetail->sale_id);
            $SaleID = $Sale->id;
            $Sale->total -=  $cashSobrante;
            $Sale->items -= $quantity;
            $Sale->save();

            //actualizar la nueva cantidad en la venta
            if(!$responseJSON){
                $userID = Auth()->user()->id;
            }
            if((int) $saleDetail->quantity - (int) $quantity > 0){
                $saleDetail->quantity -= $quantity;
                $saleDetail->save();

                if($userID != null){
                    try{
                        $user = User::find($userID);
                        Inspectors::create([
                            'user' => $user->name,
                            'action' => 'Ha eliminado '.$quantity.' '.$presentacion->full_name.' de la orden #'. $SaleID. '(Estado : '. $Sale->status_envio.')',
                            'seccion' => 'Despachos'
                        ]);
                    }catch(Exception $e){
                        \Log::error('No se pudo agregar el mensaje al inspector en la seccion de elminar producto de la orden: ' . $e);
                    }
                }
            }else{
                //elimina el detalle
                $saleDetail->delete();

                if($userID != null){
                    try{
                        $user = User::find($userID);
                        Inspectors::create([
                            'user' => $user->name,
                            'action' => 'Ha eliminada todas las cajas de la presentación '.$presentacion->full_name.' de la orden #'. $SaleID. '(Estado : '. $Sale->status_envio.')',
                            'seccion' => 'Despachos'
                        ]);
                    }catch(Exception $e){
                        \Log::error('No se pudo agregar el mensaje al inspector en la seccion elminar orden: ' . $e);
                    }
                }
            }
            if($Sale->items < 1 || $Sale->total == 0){
                //eliminar de la venta
                $Sale1 = Sale::find($SaleID);
                $status = $Sale1->status_envio;
                $Sale1 ->delete();
                $SaleDeleted = true;
                $QUICK = new QuickBooksService();
                $QUICK->deleteInvoice($SaleID);

                if(!$responseJSON){
                    $this->emit('hideModalEdit');
                }
                if($userID != null){
                    try{
                        $user = User::find($userID);
                        Inspectors::create([
                            'user' => $user->name,
                            'action' => 'Ha eliminado la orden #'. $SaleID . '(Estado : '. $status.')',
                            'seccion' => 'Despachos'
                        ]);
                    }catch(Exception $e){
                        \Log::error('No se pudo agregar el mensaje al inspector en la seccion elminar orden: ' . $e);
                    }
                }  

            }else{
                $QUICK = new QuickBooksService();
                $QUICK->updateInvoice($SaleID);
            }

            if($isCash){
                //Agregando al cupo del cliente
                if(isset($Sale->id)){
                    $Sale->cash -= $cashSobrante;
                    $Sale->save();
                }
            }else{
                //Agregando al cupo del cliente
                $customer = Customer::find($Sale->customer->id);
                $customer->saldo += $cashSobrante; 
                $customer->save();
            }
            DB::commit();
            if(!$responseJSON){
                /*$this->detailsEdit = SaleDetail::where('sale_id', $SaleID)->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($SaleID);
                $this->details = SaleDetail::where('sale_id', $SaleID)->get();
                $this->emit('producto-creado');*/
                return true;
            }else{
                return $this->responses('Proceso realizado correctamente.', 200, $responseJSON, 'global-msg', ["SaleDeleted" => $SaleDeleted]);
            }   
            
            //return response()->json(['message' => 'Proceso realizado correctamente.'], 200);
            //return $this->responses('La cantidad de producto que deseas quitar excede la cantidad existente.', 404, $responseJSON, 'sale-error');

        }catch(Exception $e){   
            DB::rollBack();
            return $this->responses('Hubo un error: ' . $e->getMessage(), 500, $responseJSON, 'sale-error');
            //return response()->json(['message' => 'Hubo un error: ' . $e->getMessage()], 500);
        }
    }

    // ----------------------------------------------------------------
    // ENVIO DE RESPUESTA FORMATO JSON
    // ----------------------------------------------------------------
    public function responses($content, $code, $ResponseJSON, $nameEmit = '', $params = [])
    {
        if ($ResponseJSON) {
            return response()->json(array_merge(['message' => $content], $params), $code);
        } else {
            $this->emit($nameEmit, $content);
            return;
        }
    }
}