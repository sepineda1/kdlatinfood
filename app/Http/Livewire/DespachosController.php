<?php

namespace App\Http\Livewire;

use App\Mail\Despachos;
use App\Models\Cliente;
use App\Models\Customer;
use App\Mail\EnvioCamino;
use App\Models\Presentacion;
use App\Models\Sale;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Models\Envio;
use Illuminate\Support\Facades\Log;
use Automattic\WooCommerce\Client;
use App\Models\Product;
use App\Models\Operario;
use App\Models\Lotes;
use App\Models\SaleDetail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Traits\CartTrait;
use App\Models\Inspectors;
use Illuminate\Http\Request;
use App\Services\QuickBooksService;
use App\Models\Discounts;
use Illuminate\Support\Facades\DB;
use App\Traits\SaleTrait;

class DespachosController extends Component
{
    use SaleTrait;

    // --- New properties for live polling and sound ---
    public $deliveries = [];
    public $lastCount = 0;


    public $selected_id, $saleId, $lotCount, $sumDetails, $reportType, $countDetails, $pageTitle, $componentName, $userId, $saleData, $saldoModal = 0, $tipopagoModal = '', $tipoSaldo = '';
    public $showModalSaldo = false;
    //public $quantities = [];
    public $newProducts = [
        'sku' => '',
        'name' => '',
        'items' => 0,
    ];

    public $newRowKey = 0;


    public $selectedProducts = [];
    //public $addProduct = false;

    protected $quickBooksService;

    public $clienteSeleccionadoId;

    public $tipoVenta = '';

    public $TotalCalculado = 0;

    public function boot(QuickBooksService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
    }

    public function render()
    {
        try {
            $prod = Presentacion::all();

            $data0 = Customer::all();
            $data = Customer::with('sale')->get();
            $data2 = Sale::all();
            $data3 = SaleDetail::with('sales')->get();

            $lotes = Lotes::all();
            return view('livewire.despachos.despachos', ['data' => $data, 'lotes' => $lotes, 'prod' => $prod, 'data2' => $data2, 'data0' => $data0, 'data3' => $data3, 'deliveries' => $this->deliveries])
                ->extends('layouts.theme.app')
                ->section('content');
            ;
        } catch (Exception $e) {
            $this->emit('global-msg', 'Hubo un error : ' . $e);
        }

    }
    public function mount()
    {
        $this->pageTitle = 'Listado';
        $this->componentName = 'Despacho';
        $this->details = [];
        //$this->detailsEdit = [];
        $this->sumDetails = 0;
        $this->countDetails = 0;
        $this->reportType = 0;
        $this->userId = 0;
        $this->saleId = 0;

        // initialize deliveries and count
        $this->refreshDeliveries();
    }

    public function refreshDeliveries()
    {
        try{
            $this->deliveries = Sale::where('status_envio', 'PENDIENTE')->where('status', 'PENDING')->orderBy('id', 'desc')->get();
            // Detección de nuevas entregas
            $current = $this->deliveries->count();
            if ($this->lastCount > 0 && $current > $this->lastCount) {
                // Busca el último pedido PENDING / PENDIENTE
                $newSale = Sale::where('status_envio', 'PENDIENTE')
                               ->where('status', 'PENDING')
                               ->orderBy('id', 'desc')
                               ->first();
                $clienteNombre = $newSale->customer->name.' '.$newSale->customer->last_name;
                $this->emit('nuevaEntrega', $newSale->id, $clienteNombre);
            }
            $this->lastCount = $current;

        }catch(Exception $e){
            \Log::error('Error en refreshDeliveries: '.$e->getMessage());
            $this->emit('refresh-error');
        }
    }
    public function getAllSalesPending()
    {
        try {
            // Obtener las ventas pendientes y cargar las relaciones necesarias
            $sales = Sale::with(['salesDetails.product.product', 'customer', 'salesDetails.lot','services.servicePay.catalogoService',  'deliveriesTypes.catalogEntry']) // Relación con Product a través de Presentacion
                ->orderBy('id', 'desc')
                ->where('status_envio', 'PENDIENTE')
                ->where('status', 'PENDING')
                ->get();

            return response()->json(['success' => true, 'data' => $sales], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener las ventas.'], 500);
        }
    }


    public function getAllSales()
    {
        try {
            // Obtén todas las ventas ordenadas por su fecha de creación en orden ascendente (de la primera a la última)
            $sales = Sale::with('salesDetails.product.product', 'customer', 'salesDetails.lot','services.servicePay.catalogoService', 'deliveriesTypes.catalogEntry')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($sale) {
                    $address = str_replace('USA', '', $sale->customer->address);
                    $address = rtrim($address, ", \t\n\r\0\x0B");
                    $sale->customer->address = $address;
                    return $sale;
                });

            return response()->json(['success' => true, 'data' => $sales], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener las ventas.'], 500);
        }
    }
    public function getSaleDetails($id)
    {
        try {
            // Obtén los detalles de la venta con el ID proporcionado, incluyendo producto y size 
            // Enviar habilitado y no eliminados
            // Que venga dentro de la orden
            $sale = Sale::with(['salesDetails.product.product', 'salesDetails.product.size', 'customer', 'salesDetails.lot','services.servicePay.catalogoService','deliveriesTypes.catalogEntry'])
                ->where('id', $id)
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Venta no encontrada.'], 404);
            }

            return response()->json(['success' => true, 'data' => $sale], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener los detalles de la venta.'], 500);
        }
    }

    public function getSaleDetailsPendiente($id)
    {
        try {
            // Obtén los detalles de la venta con el ID proporcionado
            $sale = Sale::with('salesDetails.product', 'customer','services.servicePay.catalogoService')
                ->where('id', $id)
                ->where('status_envio', 'PENDIENTE')
                ->first();

            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Venta no encontrada.'], 404);
            }

            return response()->json(['success' => true, 'data' => $sale], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener los detalles de la venta.'], 500);
        }
    }

    public function resetUI()
    {
    }
    // para ver el detalle en la vista salesdetails
    public function getDetails($saleId)
    {
        $this->details = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
            ->join('products as prod', 'prod.id', 'p.products_id')
            ->join('sizes as s', 's.id', 'p.sizes_id')
            ->select(
                'sale_details.id',
                'sale_details.price',
                'sale_details.quantity',
                'p.barcode',
                'prod.name as product_name',
                'prod.estado as product_estado',
                's.size as size_name'
            )
            ->where('sale_details.sale_id', $saleId)
            ->get();



        $suma = $this->details->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->sumDetails = $suma;
        $this->countDetails = $this->details->sum('quantity');
        $this->saleId = $saleId;
        $this->saleData = Sale::find($saleId);
        $this->emit('producto-creado');
        $this->emit('show-modal', 'details loaded');
    }


    protected $listeners = [
        'CargarPedido' => 'Cargar',
        'EditarPedido' => 'EditPedido',
        'GuardarEditado' => 'GuardarEditado',
        /*'removeProduct' => 'removeProduct',
        'aumentarCantidad' => 'addProductToSaleButton' ,
        'decrementarCantidad' => 'minusProductToSaleButton',
        'confirm-decrement-after-delete' => 'confirmDecrementAfterDelete',
        'confirm-cash-order' => 'confirmCashOrder'*/
    ];
    public function GuardarEditado($id)
    {

        $sale = Sale::findOrFail($id);
        $sale->editado = 'si';
        $sale->save();

        $cliente = $sale->customer;

        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Se edito el pedido #' . $sale->id,
            'seccion' => 'Despachos'
        ]);

        $this->emit('hidedetailsedit', 'Lote Agregado');
    }


    /*public function EditPedido($id)
    {
        $this->emit('hide-details-modal', 'Lote Agregado');

        $sale = Sale::findOrFail($id); // Obtener el pedido por su ID
        $this->emit('showedit', 'Lote Agregado');

        $detallesPedido = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
            ->join('products as prod', 'prod.id', '=', 'p.products_id')
            ->select('sale_details.quantity', 'sale_details.price', 'prod.name as product', 'sale_details.lot_id')
            ->where('sale_details.sale_id', $sale->id)
            ->get();

        // Obtener los detalles originales de la venta
        $this->detailsEdit = SaleDetail::where('sale_id', $id)->get();

        $this->saleData = Sale::findOrFail($id);

        // Inicializar las listas de productos y cantidades con los detalles originales
        foreach ($this->detailsEdit as $key => $detail) {
            $this->selectedProducts[$key] = $detail->product->product->products_id;
            $this->quantities[$key] = $detail->quantity;
        }

    }*/

    /*public function toggleAddProduct()
    {
        $this->addProduct = !$this->addProduct;
    }*/

    /*public function addProductRow()
    {
        // Valida que tengas una venta seleccionada
        if (!$this->saleId) {
            return;
        }

        // Crea un nuevo registro en la tabla de sale_details
        $newSaleDetail = new SaleDetail();
        $newSaleDetail->sale_id = $this->saleId; // Asigna el ID de la venta seleccionada
        $this->saleData = Sale::find($this->saleId);
        $newSaleDetail->presentaciones_id = $this->getProductIdBySku($this->newProducts['sku']); // Obtén el ID del producto por SKU
        $newSaleDetail->quantity = $this->newProducts['items'];
        $newSaleDetail->price = $this->getProductPRICEBySku($this->newProducts['sku']);
        $newSaleDetail->save();

        $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();

        // Inicializar las listas de productos y cantidades con los detalles originales
        foreach ($this->detailsEdit as $key => $detail) {
            $this->selectedProducts[$key] = $detail->product->product->products_id;
            $this->quantities[$key] = $detail->quantity;
        }

        // Recarga los detalles de la venta
        $this->details = SaleDetail::where('sale_id', $this->saleId)->get();

        $this->emit('global-msg', "Producto Agregado a la venta");

        // Restablece los valores de $newProducts
        $this->newProducts = [
            'sku' => '',
            'name' => '',
            'items' => 0,
            'price' => 0,
            'discount' => '0.00',
            'total' => ''
        ];
    }*/

    /*public function updatedNewProductsSku($value)
    {
        try {
            $this->saleData = Sale::find($this->saleId);
            $presentacion = Presentacion::where('barcode', $value)->first();
            $discount = Discounts::where('presentacion_id', $presentacion->id)->where('customer_id', $this->saleData->CustomerID)->first();


            if ($presentacion) {
                $this->newProducts['price'] = $presentacion->price;
                $this->newProducts['discount'] = isset($discount) ? $discount->discount : 0;
                //$this->newProducts['name'] = $presentacion->product->name ?? '';
            } else {
                $this->newProducts['price'] = 0;
                $this->newProducts['discount'] = 0;
                //$this->newProducts['name'] = '';
            }

        } catch (\Exception $e) {
            //throw $th;
            $this->emit('sale-error', $e->getMessage());
        }


    }*/
    public function increaseQuantity($quantity,$saleDetailId,$isCash = false){
        $saleDetail = SaleDetail::find($saleDetailId);
        if(!$saleDetail){
            return response()->json(['message' => 'No existe la orden.'], 404);
        }

        if($quantity < 1){
            return response()->json(['message' => 'No se puede AUMENTAR 0 productos.'], 404);
        }

        $presentacion = Presentacion::find($saleDetail->presentaciones_id);
        if($presentacion->stock_box > $quantity){
            return response()->json(['message' => 'Inventario AGOTADO para este producto.'], 404);
        }

        $cashAdicional = ($saleDetail->price - ($saleDetail->price * $saleDetail->discount) / 100) * $quantity;
        $Sale = Sale::find($saleDetail->sale_id);

        if(!$isCash){
            if($Sale->customer->saldo - $cashAdicional < 0){
                return response()->json(['message' => 'El Usuario NO tiene saldo disponible'], 404);
            }    
        }
  
        try{
            DB::beginTransaction();
            //decremente cantidad de stock
            $presentacion->stock_box -= $quantity;
            $presentacion->save();
            //aumenta cantidad de productos
            $saleDetail->quantity += $quantity;
            $saleDetail->save();
            //Dinero en Producto
            $cashAdicional = ($saleDetail->price - ($saleDetail->price * $saleDetail->discount) / 100) * $quantity;
            //busco en la venta cuantos items son y actualizo
            $Sale->total +=  $cashAdicional;
            $Sale->items += $quantity;
            $Sale->save();

            if($isCash){
                //Agregando al cupo del cliente
                $Sale->cash += $cashAdicional;
                $Sale->save();
            }else{
                //Agregando al cupo del cliente
                $customer = Customer::find($Sale->customer->id);
                $customer->saldo -= $cashAdicional; 
                $customer->save();
            }
            DB::commit();
            return response()->json(['message' => 'Proceso realizado correctamente.'], 200);
        }
        catch(Exception $e){
            DB::rollBack();
            return response()->json(['message' => 'Hubo un error: ' . $e->getMessage()], 500);
        }
    }
    /*public function decrementQuantityCore($quantity,$saleDetailId,$isCash = false,$responseJSON = true, $userID = null){
        //DISMINUIR CANTIDAD
        $saleDetail = SaleDetail::find($saleDetailId);
        if(!$saleDetail){
            //return response()->json(['message' => 'No existe la orden.'], 404);
            return $this->responses('No existe la orden.', 404, $responseJSON, 'sale-error');
            
        }

        if($quantity < 1){
            return $this->responses('No se puede DECREMENTAR 0 productos.', 404, $responseJSON, 'sale-error');
            //return response()->json(['message' => 'No se puede DECREMENTAR 0 productos.'], 404);
        }

        if((int) $saleDetail->quantity - (int) $quantity < 0){
            //return response()->json(['message' => 'La cantidad de producto que deseas quitar excede la cantidad existente.'], 404);
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
                if(!$responseJSON){
                    $this->emit('hidedetailsedit', 'Lote Agregado');
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
            
            if(!$responseJSON){
                $this->detailsEdit = SaleDetail::where('sale_id', $SaleID)->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($SaleID);
                $this->details = SaleDetail::where('sale_id', $SaleID)->get();
                $this->emit('producto-creado');
            }
            
            DB::commit();
            
            return $this->responses('Proceso realizado correctamente.', 200, $responseJSON, 'global-msg', ["SaleDeleted" => $SaleDeleted]);
            //return response()->json(['message' => 'Proceso realizado correctamente.'], 200);
            //return $this->responses('La cantidad de producto que deseas quitar excede la cantidad existente.', 404, $responseJSON, 'sale-error');

        }catch(Exception $e){   
            DB::rollBack();
            return $this->responses('Hubo un error: ' . $e->getMessage(), 500, $responseJSON, 'sale-error');
            //return response()->json(['message' => 'Hubo un error: ' . $e->getMessage()], 500);
        }
    }*/
    
    /*public function addProductRow($isCash = false){
         // Valida que tengas una venta seleccionada
        if (!$this->saleId) {
            return;
        }
        if (!$this->newProducts['sku']) {
            $this->emit('sale-error', 'Selecciona una Presentación.');
            return;
        }

        $presentacion_id = $this->getProductIdBySku($this->newProducts['sku']);
        $pre = Presentacion::find($presentacion_id);
        if ($pre->visible === "no") {
            $this->emit('sale-error', 'La presentacion NO está disponible.');
            return;
        }

        $Sale = Sale::findOrFail($this->saleId);
        if($isCash){
            if($Sale->cash != 0){
                $discount = Discounts::where('presentacion_id', $pre->id)->where('customer_id', $Sale->CustomerID)->first();
                $discount = $discount ? $discount->discount : 0;
                $price = $pre->price - ($discount * $pre->price) / 100;
                $this->TotalCalculado = $price * $this->newProducts['items'];
                $this->emit('producto-creado');
                $this->dispatchBrowserEvent('showModalSaldo');
                return;
            }    
        }
        $response = $this->addProductoToSaleCore($this->saleId,$this->newProducts['sku'],$this->newProducts['items'], false);
        if($response){
            $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($this->saleId);
            $this->details = SaleDetail::where('sale_id', $this->saleId)->get();
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
            ];
        }
    }*/
  
    /*public function addProductoToSaleCore($saleId, $barcode, $quantity, $responseJSON = false, $userID = null)
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

            if ($ModelSale->cash > 0) { //SI ES VENTA efectivo
                $ModelSale->cash += $price * $quantity; 
                $ModelSale->save();

            }else{
                $ModelCostumer = Customer::find($ModelSale->customer->id);
                $ModelCostumer->saldo -= $price * $quantity;
                $ModelCostumer->save();
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
            if($responseJSON){
                DB::commit();
                return $this->responses('Producto agregado a la venta con éxito.', 200, $responseJSON, 'global-msg');

            }else{
                DB::commit();
                $this->detailsEdit = SaleDetail::where('sale_id', $saleId)->get();
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
                ];
            }

        } catch (Exception $e) {
            DB::rollBack();
            return $this->responses($e->getMessage(), 500, $responseJSON, 'global-msg');
            //return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function haveLot($id)
    {
        return Lotes::where('SKU', $id)
            ->where('Fecha_Vencimiento', '>=', now()) // Solo considera fechas futuras o actuales
            ->orderBy('Fecha_Vencimiento', 'asc')->first();
    }

    public function responses($content, $code, $ResponseJSON, $nameEmit = '', $params = [])
    {
        if ($ResponseJSON) {
            return response()->json(array_merge(['message' => $content], $params), $code);
        } else {
            $this->emit($nameEmit, $content);
            return;
        }
    }*/

    public function addProductToSale(Request $request)
    {
        try {

            $request->validate([
                'sale_id' => 'required|integer', // Asegúrate de que sale_id sea un número entero
                'barcode' => 'required|string', // Asegúrate de que barcode sea una cadena de texto
                'quantity' => 'required|integer',
                'userID' => 'required|integer', // Asegúrate de que quantity sea un número entero
            ]);
            $saleId = $request->input('sale_id');
            $barcode = $request->input('barcode');
            $quantity = $request->input('quantity');
            $userID = $request->input('userID');

            return $this->addProductoToSaleCore($saleId, $barcode, $quantity, true, $userID);
           
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function decrementQuantityToSale($quantity, $saleDetailId, $userID)
    {
        try{
            return $this->decrementQuantityCore( $quantity, $saleDetailId, false, true, $userID);

        } catch (Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /*public function addProductToSale(Request $request)
    {
        try {
            // Valida los parámetros de la solicitud
            $request->validate([
                'sale_id' => 'required|integer', // Asegúrate de que sale_id sea un número entero
                'barcode' => 'required|string', // Asegúrate de que barcode sea una cadena de texto
                'quantity' => 'required|integer', // Asegúrate de que quantity sea un número entero
            ]);

            //acceder a la venta del cliente


            // Obtiene los valores de la solicitud
            $saleId = $request->input('sale_id');
            $barcode = $request->input('barcode');
            $quantity = $request->input('quantity');


            // Valida que tengas una venta seleccionada (puedes hacer esto según tu lógica específica)
            if (!$saleId) {
                return response()->json(['message' => 'No se ha seleccionado una venta.'], 400);
            }

            // Obtiene el ID del producto por SKU (código de barras)
            $productId = $this->getProductIdBySku($barcode);

            if (!$productId) {
                return response()->json(['message' => 'No se encontró un producto con el código de barras proporcionado.'], 404);
            }

            $sale = Sale::find($saleId);
            $customer = $sale->CustomerID;
            $discount = Discounts::where('customer_id', $customer)->where('presentacion_id', $productId)->first();



            $product = Presentacion::find($productId);
            $tamanoCaja = $product->tam1;
            $newItems = $quantity; // Cantidad de productos en las cajas
            $price = $this->getProductPRICEBySku($barcode);

            // Busca si ya existe un detalle de venta para el producto en la venta especificada
            $existingSaleDetail = SaleDetail::where('sale_id', $saleId)
                ->where('presentaciones_id', $productId)
                ->first();

            if ($existingSaleDetail) {
                // Si el detalle de venta ya existe, actualiza la cantidad y el precio
                $existingSaleDetail->quantity += $newItems; // Suma la nueva cantidad de productos a la cantidad existente
                $existingSaleDetail->price = $price; // Actualiza el precio si es necesario
                $existingSaleDetail->cajas += $quantity; // Actualiza el precio si es necesario //NO HAY NECESIDAD
                $existingSaleDetail->save();
            } else {
                // Si no existe, crea un nuevo registro en la tabla de sale_details
                $newSaleDetail = new SaleDetail();
                $newSaleDetail->sale_id = $saleId;
                $newSaleDetail->presentaciones_id = $productId;
                $newSaleDetail->quantity = $newItems; // Cantidad de productos en las cajas
                $newSaleDetail->cajas = $quantity; // Cantidad de cajas
                $newSaleDetail->price = $price;
                if ($discount) {
                    $newSaleDetail->discount = $discount->discount;
                }

                $newSaleDetail->save();
            }

            // Actualiza el total en la tabla principal Sale
            $sale = Sale::find($saleId);
            $sale->total += ($newItems * $price); // Suma al total el precio de los productos en las cajas
            $sale->items += $newItems; // Actualiza la cantidad total de productos en la venta
            $sale->total_cajas += $quantity;


            $sale->save();

            // Puedes retornar una respuesta de éxito si lo deseas
            return response()->json(['message' => 'Producto agregado a la venta con éxito.'], 200);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }*/



    /*public function ModalShow($saldoModal, $tipopagoModal, $tipoSaldo)
    {
        $this->tipoSaldo = $tipoSaldo;
        $this->saldoModal = $saldoModal;
        $this->tipopagoModal = $tipopagoModal;
        $this->dispatchBrowserEvent('showModalSaldo');
        $this->emit('producto-creado');
    }

    public function AcceptModal()
    {

        $isSuccess = false;
        $sale = Sale::find($this->saleId);
        $customer = Customer::find($this->saleData->customer->id);
        if ($this->tipopagoModal == "CREDITO") {
            if ($this->tipoSaldo == 'Pendiente') {

                //$this->saleData->total += abs($this->saldoModal); 
                //$this->saleData->save();
                //$this->saleData->customer->saldo -= abs($this->saldoModal);
                //$this->saleData->customer->save();
                $sale->total += abs($this->saldoModal);
                $sale->save();
                $customer->saldo -= abs($this->saldoModal);
                $customer->save();
                $isSuccess = true;

            } else {
                //$this->saleData->total -= abs($this->saldoModal);
                $sale->total -= abs($this->saldoModal);
                $sale->save();
                $customer->saldo += abs($this->saldoModal);
                $customer->save();

                //$this->saleData->customer->saldo += abs($this->saldoModal);
                //$this->saleData->customer->save();
                $isSuccess = true;
            }
        } else {
            if ($this->tipoSaldo == 'Pendiente') {
                /*$this->saleData->total += abs($this->saldoModal);
                $this->saleData->cash += abs($this->saldoModal);
                $this->saleData->save();

                $sale->total += abs($this->saldoModal);
                $sale->cash += abs($this->saldoModal);
                $sale->save();

                $isSuccess = true;
            } else {
                /*$this->saleData->total -= abs($this->saldoModal);
                $this->saleData->change += abs($this->saldoModal);
                $this->saleData->save();

                //$sale->total -= abs($this->saldoModal);
                $sale->change += abs($this->saldoModal);
                $sale->save();

                $isSuccess = true;
            }
        }
        if ($isSuccess) {
            $this->saleData = Sale::find($this->saleId);
            $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();
            $this->dispatchBrowserEvent('hideModalSaldo');
            $this->emit('global-msg', "Venta Ajustada Correctamente.");
            $this->emit('producto-creado');
        }
    }*/


    public function updateSaleAPI(Request $request)
    {
        try {
            // Validar los datos de la solicitud
            $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            // Obtener los datos de la solicitud
            $saleId = $request->input('sale_id');
            $productId = $request->input('product_id');
            $newQuantity = $request->input('quantity');

            // Buscar el detalle de venta correspondiente
            $saleDetail = SaleDetail::where('sale_id', $saleId)
                ->where('product_id', $productId)
                ->first();

            if (!$saleDetail) {
                return response()->json(['message' => 'Detalle de venta no encontrado'], 404);
            }
            // Calcular el cambio en la cantidad
            $oldQuantity = $saleDetail->quantity;
            $quantityChange = $newQuantity - $oldQuantity;

            // Actualizar la cantidad en el detalle de venta
            $saleDetail->quantity = $newQuantity;
            $saleDetail->save();

            // Actualizar el total en la tabla principal Sale
            $sale = Sale::find($saleId);
            $sale->total += ($quantityChange * $saleDetail->product->price);
            $sale->save();

            // Puedes devolver una respuesta de éxito
            return response()->json(['message' => 'Cantidad actualizada con éxito']);

        } catch (\Exception $e) {
            // Manejo de errores en caso de excepción
            return response()->json(['message' => 'Error al actualizar la cantidad', 'error' => $e->getMessage()], 500);
        }
    }


    /*public function updateSale()
    {
        if (!$this->saleId) {
            return;
        }

        $totalItems = 0;

        foreach ($this->quantities as $key => $newQuantity) {
            if ($newQuantity > 0) {
                if (isset($this->details[$key])) {
                    $detail = $this->details[$key];

                    // Cálculo del ajuste de inventario
                    $previousQuantity = $detail->quantity;
                    $difference = $newQuantity - $previousQuantity;

                    // Actualizar el detalle
                    $detail->quantity = $newQuantity;
                    $detail->save();

                    // Actualizar el inventario de la presentación
                    $presentacion = $detail->product;
                    if ($presentacion) {
                        $presentacion->stock_box -= $difference;
                        $presentacion->save();
                    }

                    $totalItems += $newQuantity;
                }
            } else {
                $this->emit('sale-error', 'La cantidad tiene que ser mayor a Cero.');
                return;
            }
        }

        // Actualizamos solo el número total de ítems, no el total de la venta
        $sale = Sale::find($this->saleId);
        $sale->items = $totalItems;
        $sale->save();

        $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();

        $this->details = SaleDetail::where('sale_id', $this->saleId)->get();
        $this->render();
        $this->emit('global-msg', "Cantidades actualizadas correctamente y stock ajustado.");
    }*/

    /*public function quantityUpdate($value)
    {
        if ($value > 0) {
            $this->updateSale();
        } else {
            $this->emit('sale-error', 'La cantidad tiene que ser mayor a Cero.');
        }
    }*/

    /*public function quantityUpdate1($quantity, $key){

        $saleDetailID = $this->detailsEdit[$key]['id'];
        if($quantity > 0){
            $this->emit('sale-error', 'La cantidad tiene que ser mayor a Cero.');
            return;
        }
        $saleDetail = SaleDetail::find($saleDetailID);
        //Como saber SI deseo aumentar o deseo decrementar
        if((int) $quantity > (int) $saleDetail->quantity ){
            //deseo aumentar
            $unidadesXAumentar = (int) $quantity - (int) $saleDetail->quantity;
            $pre = Presentacion::find($saleDetail->presentaciones_id);
            $this->addProductoToSaleCore($saleDetail->sale_id, $pre->barcode, $unidadesXAumentar);
        }else{
            //deseo decrementar
            if((int) $saleDetail->quantity > (int) $quantity ){
                $this->decrementQuantityCore(2, 1, true);
            }else{
                //las cantidades iguales no debe hacer nada
            }
        }
    }*/

    /*public function addProductToSaleButton($key,$quantity = 1){
        $saleDetailID = $this->detailsEdit[$key]['id'];
        $saleDetail = SaleDetail::find($saleDetailID);
        $pre = Presentacion::find($saleDetail->presentaciones_id);
        $sale = Sale::find($saleDetail->sale_id);
        if($sale->cash > 0){

            $discount = Discounts::where('presentacion_id', $pre->id)->where('customer_id', $sale->CustomerID)->first();
            $discount = $discount ? $discount->discount : 0;
            $price = $pre->price - ($discount * $pre->price) / 100;
            $total = $price * $quantity;
            $this->emit('producto-creado');
            $text = "Para agregar este producto, Se recibe en EFECTIVO la suma de $". number_format($total,2);
            $this->emit('confirm-cash-order-modal', [
                'saleId' => $sale->id,
                'barcode' => $pre->barcode,
                'quantity' => $quantity,
                'text' => $text,
                'setting' => 'add',
                'saleDetailID' => $saleDetailID,
                'hasCash' => $sale->cash > 0
            ]);
            return;
        }
        $response = $this->addProductoToSaleCore($saleDetail->sale_id, $pre->barcode, $quantity);
        if($response){
            $this->detailsEdit = SaleDetail::where('sale_id', $saleDetail->sale_id)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->saleData = Sale::find($saleDetail->sale_id);
            $this->details = SaleDetail::where('sale_id', $saleDetail->sale_id)->get();
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
            ];
        }
    }*/
    /*public function minusProductToSaleButton($key,$quantity = 1){
        $saleDetailID = $this->detailsEdit[$key]['id'];
        $saleDetail = SaleDetail::find($saleDetailID);
        $sale = Sale::find($saleDetail->sale_id);
        $pre = Presentacion::find($saleDetail->presentaciones_id);
        /*if((int) $saleDetail->quantity - (int) $quantity < 1 && $sale->items === $quantity){
            //$this->emit('sale-error', 'Si restas esta cantidad, automaticamente se eliminará la orden.');
            $this->emit('producto-creado');
            $this->emit('confirm-delete-order', [
                'quantity' => $quantity,
                'saleDetailID' => $saleDetailID,
                'hasCash' => $sale->cash > 0
            ]);
            return;
        }
        if($sale->cash > 0){
            $textAdicional = '';
            if((int) $saleDetail->quantity - (int) $quantity < 1 && $sale->items === $quantity){
                $textAdicional = 'Si restas esta cantidad, automaticamente se eliminará la orden.';
            }
            //$discount = Discounts::where('presentacion_id', $pre->id)->where('customer_id', $sale->CustomerID)->first();
            $price = $saleDetail->price - ($saleDetail->discount * $saleDetail->price) / 100;
            $total = $price * $quantity;
            $this->emit('producto-creado');
            $text = "Para quitar este producto, Se hace la devolución en EFECTIVO la suma de $". number_format($total, 2)." ". $textAdicional;
            $this->emit('confirm-cash-order-modal', [
                'saleId' => $sale->id,
                'barcode' => $pre->barcode,
                'quantity' => $quantity,
                'text' => $text,
                'setting' => 'minus',
                'saleDetailID' => $saleDetailID,
                'hasCash' => $sale->cash > 0
            ]);
            return;
        }else{
            if((int) $saleDetail->quantity - (int) $quantity < 1 && $sale->items === $quantity){
                //$this->emit('sale-error', 'Si restas esta cantidad, automaticamente se eliminará la orden.');
                $this->emit('producto-creado');
                $this->emit('confirm-delete-order', [
                    'quantity' => $quantity,
                    'saleDetailID' => $saleDetailID,
                    'hasCash' => $sale->cash > 0
                ]);
                return;
            }
        }
        $response = $this->decrementQuantityCore($quantity,$saleDetailID, $sale->cash > 0 ? true : false , false);
        if($response){
            $this->detailsEdit = SaleDetail::where('sale_id', $sale->id)->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($sale->id);
                $this->details = SaleDetail::where('sale_id', $sale->id)->get();
                $this->emit('producto-creado');
        }
    }*/
    /*public function confirmDecrementAfterDelete($data)
    {
        $response = $this->decrementQuantityCore(
            $data['quantity'],
            $data['saleDetailID'],
            $data['hasCash'],
            false
        );
        if($response){
            $Sale = SaleDetail::find($data['saleDetailID']);
            $this->detailsEdit = SaleDetail::where('sale_id', $Sale->id)->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($Sale->id);
                $this->details = SaleDetail::where('sale_id', $Sale->id)->get();
                $this->emit('producto-creado');
        }
    }*/

    /*public function confirmCashOrder($data)
    {
        if($data['setting'] == 'add'){
            //add
            $response = $this->addProductoToSaleCore($data['saleId'], $data['barcode'], $data['quantity']);
            if($response){
                $this->detailsEdit = SaleDetail::where('sale_id', $data['saleId'])->get();
                foreach ($this->detailsEdit as $key => $detail) {
                    $this->selectedProducts[$key] = $detail->product->product->products_id;
                    $this->quantities[$key] = $detail->quantity;
                }
                $this->saleData = Sale::find($data['saleId']);
                $this->details = SaleDetail::where('sale_id', $data['saleId'])->get();
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
                ];
            }
        }else{
            //minus
            $response = $this->decrementQuantityCore(
                $data['quantity'],
                $data['saleDetailID'],
                $data['hasCash'],
                false
            );

            if($response){
                $this->detailsEdit = SaleDetail::where('sale_id', $data['saleId'])->get();
                    foreach ($this->detailsEdit as $key => $detail) {
                        $this->selectedProducts[$key] = $detail->product->product->products_id;
                        $this->quantities[$key] = $detail->quantity;
                    }
                    $this->saleData = Sale::find($data['saleId']);
                    $this->details = SaleDetail::where('sale_id', $data['saleId'])->get();
                    $this->emit('producto-creado');
            }
        }
        
    }*/

    /*public function updateSale()
    {
        // Asegúrate de tener la venta seleccionada
        if (!$this->saleId) {
            return;
        }

        $totalItems = 0;
        // Recorre las cantidades actualizadas y actualiza los registros en la base de datos
        foreach ($this->quantities as $key => $quantity) {
            if ($quantity > 0) {
                // Obtén el detalle de venta correspondiente por su índice
                if (isset($this->details[$key])) {
                    $detail = $this->details[$key];
                    // Actualiza la cantidad en el detalle de venta
                    $detail->quantity = $quantity;
                    $detail->save();
                    $totalItems += $detail->quantity;
                }
            } else {
                $this->emit('sale-error', 'La cantidad tiene que ser mayor a Cero.');
                return;
            }
        }

        $sale = Sale::find($this->saleId);
        $sale->items = $totalItems;
        $sale->save();

        // Limpiar las cantidades después de la actualización
        // $this->quantities = [];
        $this->emit('global-msg', "Cantidades actualizadas");
        // Puedes agregar un mensaje de éxito o realizar otras acciones después de la actualización
    }*/

    /*public function getProductIdBySku($sku)
    {
        // Busca el producto por SKU en la base de datos
        $product = Presentacion::where('barcode', $sku)->first();

        // Si se encuentra el producto, devuelve su ID; de lo contrario, devuelve null
        if ($product) {
            return $product->id;
        } else {
            return null;
        }
    }*/
    /*public function getProductPRICEBySku($sku)
    {
        // Busca el producto por SKU en la base de datos
        $product = Product::where('barcode', $sku)->first();

        // Si se encuentra el producto, devuelve su ID; de lo contrario, devuelve null
        if ($product) {
            return $product->price;
        } else {
            return null;
        }
    }*/

    public function getProductPRICEBySku($sku)
    {
        // Busca el producto por SKU en la base de datos
        $product = Presentacion::where('barcode', $sku)->first();

        // Si se encuentra el producto, devuelve su ID; de lo contrario, devuelve null
        if ($product) {
            return $product->price;
        } else {
            return null;
        }
    }


    /*public function removeNewProduct()
    {
        // Restablecer los valores de $newProducts
        $this->newProducts = [
            'sku' => '',
            'name' => '',
            'items' => 0,
        ];

        // Ocultar el formulario de adición
        $this->addProduct = false;
    }*/


    /*public function removeProduct($key)
    {
        // Asegúrate de tener la venta seleccionada
        if (!$this->saleId) {
            return;
        }

        // Obtén el ID del detalle de venta que deseas eliminar
        $saleDetailId = $this->details[$key]['id'];

        // Elimina el registro del detalle de venta de la base de datos
        $delete = SaleDetail::where('id', $saleDetailId)->delete();
        if ($delete) {
            $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();
            $this->emit('global-msg', "Producto Eliminado");
        }

    }*/

    /*public function removeProduct($key)
    {
        // Asegúrate de tener la venta seleccionada
        if (!$this->saleId) {
            return;
        }

        // Obtén el ID del detalle de venta que deseas eliminar
        $saleDetailId = $this->details[$key]['id'];

        // Llama al método reutilizable que maneja la eliminación
        $response = $this->removeProductFromSale($saleDetailId);
        
        // Puedes verificar si la respuesta fue exitosa
        if ($response->getStatusCode() === 200) {
            // Refresca los detalles de la venta
            $this->detailsEdit = SaleDetail::where('sale_id', $this->saleId)->get();
            foreach ($this->detailsEdit as $key => $detail) {
                $this->selectedProducts[$key] = $detail->product->product->products_id;
                $this->quantities[$key] = $detail->quantity;
            }
            $this->render();
            $this->emit('global-msg', "Producto Eliminado");
        }
    }*/

    /*public function removeProduct($key){
        if (!$this->saleId) {
            return;
        }
        $saleDetailId = $this->details[$key]['id'];
        $saleDetail = SaleDetail::where('id', $saleDetailId)->first();
        $this->minusProductToSaleButton($key, $saleDetail->quantity);
    }*/

    public function removeProductFromSale($saleDetailId, $userID = null)
    {
        $saleDetail = SaleDetail::where('id', $saleDetailId)->first();

        $saleID = $saleDetail->sale_id;
        if (!$saleDetail) {
            return response()->json(['message' => 'El detalle de venta no existe o no pertenece a esta venta.'], 404);
        }

        $presentacion_id = $saleDetail->presentaciones_id;
        $quantity = $saleDetail->quantity;
        $price = $saleDetail->product->price;
        $cajas = $saleDetail->cajas;
        $discount = $saleDetail->discount;
        $saleDetail->delete();

        $sale = Sale::find($saleDetail->sale_id);
        if ($discount > 0) {
            $price -= ($discount * $price) / 100;
        }
        $sale->total -= ($quantity * $price);
        $sale->total_cajas -= $cajas;
        $sale->save();

        $Customer = Customer::find($sale->CustomerID);
        $Customer->saldo += $quantity * $price;
        $Customer->save();

        $sale->items -= $quantity;
        $sale->save();

        $product = Presentacion::find($presentacion_id);
        $product->stock_box += $quantity; //Cantidad de Cajas que hay en el inventario
        $product->save();

        $QUICK = new QuickBooksService();
        $QUICK->updateInvoice($saleID);

        if($userID != null){
            try{
                $user = User::find($userID);
                Inspectors::create([
                    'user' => $user->name,
                    'action' => 'Ha eliminada todas las cajas de la presentación '.$product->full_name.' de la orden #'. $saleID. '(Estado : '. $sale->status_envio.')',
                    'seccion' => 'Despachos'
                ]);
            }catch(Exception $e){
                \Log::error('No se pudo agregar el mensaje al inspector en la seccion de eliminar producto de la orden: ' . $e);
            }
        }

        return response()->json([
            'message' => 'Producto eliminado de la venta con éxito.',
        ], 200);
    }


    public function removeSale($saleId, $userID = null)
    {
        DB::beginTransaction();

        try {
            $saleDetails = SaleDetail::where('sale_id', $saleId)->get();

            foreach ($saleDetails as $detail) {
                $product = Presentacion::find($detail->presentaciones_id);
                if ($product) {
                    $product->stock_box += $detail->quantity;
                    $product->save();
                }

                $detail->delete();
            }

            $sale = Sale::find($saleId);
            if ($sale) {
                $sale->delete();
                $QUICK = new QuickBooksService();
                $QUICK->deleteInvoice($saleId);
            }

            DB::commit();
            if($userID != null){
                try{
                    $user = User::find($userID);
                    Inspectors::create([
                        'user' => $user->name,
                        'action' => 'Ha eliminado la orden #'. $saleId,
                        'seccion' => 'Despachos'
                    ]);
                }catch(Exception $e){
                    \Log::error('No se pudo agregar el mensaje al inspector en la seccion elminar orden: ' . $e);
                }
            }

            return response()->json(['message' => 'Venta eliminada con éxito.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar la venta: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar la venta.'], 500);
        }

    }

    public function Cargar($id)
    {
        $sale = Sale::findOrFail($id); // Obtener el pedido por su ID



        $wooCommerceOrderId = $sale->woocommerce_order_id;

        // Verificar si existe el woocommerce_order_id
        if ($wooCommerceOrderId) {
            $wooCommerceClient = new \Automattic\WooCommerce\Client(
                'https://kdlatinfood.com', // URL de tu tienda WooCommerce
                'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
                'cs_723eab16e53f3607fd38984b00f763310cc4f473',
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                ]
            );
            $wooCommerceClient->put('orders/' . $wooCommerceOrderId, ['status' => 'on-hold']);
            Log::info('estado actualizado en woocomerce');
        } else {
            Log::info('No se encontró el woocommerce_order_id');
        }

        // Actualizar el estado del pedido
        $sale->status = 'PAID';
        $sale->status_envio = 'PENDIENTE';
        $sale->fecha_carga = now();
        $sale->save();
        // Crear un nuevo registro en la tabla "Envio"
        $envios = Envio::where('id_sale',$sale->id)->count();
        if($envios < 1){
            $envio = new Envio();
            $envio->id_sale = $sale->id;
            // Asignar aleatoriamente un ID de transportista
            $transportista = Operario::inRandomOrder()->first();
            $envio->id_transport = $transportista->id;
            $envio->save();
        }

        // Obtener los detalles del pedido
        $detallesPedido = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
            ->join('products as prod', 'prod.id', '=', 'p.products_id')
            ->select('sale_details.quantity', 'sale_details.price', 'prod.name as product', 'sale_details.lot_id')
            ->where('sale_details.sale_id', $sale->id)
            ->get();

        //$this->quickBooksService->create_invoice($sale->id);

        // Obtener el cliente asociado al pedido
        $cliente = $sale->customer;
        $this->saleData = Sale::find($this->saleId);

        // Enviar el correo electrónico al cliente

        try {
            Mail::to($cliente->email)->send(new Despachos($sale, $envio, $detallesPedido, $cliente));
        } catch (Exception $e) {
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Se cargo el pedido #' . $sale->id,
            'seccion' => 'Despachos'
        ]);

        $this->emit('hide-details-modal', 'Lote Agregado');
    }
    public function cargarSale($id)
    {
        try {

            $sale = Sale::findOrFail($id);
            $wooCommerceOrderId = $sale->woocommerce_order_id;
            // Tu lógica para actualizar el estado en WooCommerce aquí...

            // Actualizar el estado del pedido
            $sale->status = 'PAID';
            $sale->fecha_carga = now();
            $sale->status_envio = 'PENDIENTE';
            $sale->save();

            $QUICK = new QuickBooksService();
            $QUICK->create_invoice($id); // Revisar porque bota error

            // Crear un nuevo registro en la tabla "Envio"

            $envios = Envio::where('id_sale',$sale->id)->count();
            if($envios < 1){
                $envio = new Envio();
                $envio->id_sale = $sale->id;
    
                // Asignar aleatoriamente un ID de transportista
                $transportista = Operario::inRandomOrder()->first();
                $envio->id_transport = $transportista->id;
                $envio->save();
    
            }

            //Obtener los detalles del pedido
            /*$detallesPedido = SaleDetail::join('products as p', 'p.id', 'sale_details.product_id')
                ->select('sale_details.quantity', 'sale_details.price', 'p.name as product', 'sale_details.lot_id')
                ->where('sale_details.sale_id', $sale->id)
                ->get();*/

            // Obtener el cliente asociado al pedido
            $cliente = $sale->customer;
            $this->saleData = Sale::find($this->saleId);

            // Enviar el correo electrónico al cliente
            /*try {
                Mail::to($cliente->email)->send(new Despachos($sale, $envio, $detallesPedido, $cliente));
            } catch (Exception $e) {
                \Log::error('Error al enviar correo : ' . $e->getMessage());
            }*/
            // 

            // Registrar la acción en el sistema
            /*$user = Auth()->user()->name;
            Inspectors::create([
                'user' => $user,
                'action' => 'Se cargo el pedido #' . $sale->id,
                'seccion' => 'Despachos'
            ]);*/

            return response()->json(['success' => true, 'message' => 'Pedido cargado exitosamente'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar el pedido: ' . $e->getMessage()], 500);
        }
    }
  
    //agregados para editar ventan en Ontrasnite

    public function saleDetails($id)
    {
        try {
            $sale = Sale::find($id);
            if ($sale) {
                $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();

                if ($saleDetails->isNotEmpty()) {
                    $detailsArray = [];
                    foreach ($saleDetails as $details) {
                        $detailObject = (object) [
                            'sku' => $details->product->barcode,
                            'presentacion_id' => $details->product->id,
                            'presentacion' => $details->product->product->name . " " . $details->product->size->size . " " . $details->product->product->estado,
                            'qty' => $details->quantity,
                            'scanned' => $details->scanned,
                        ];
                        $detailsArray[] = $detailObject;
                    }
                    return response()->json(['saleDetails' => $detailsArray]);
                } else {
                    return response()->json(['message' => 'El producto no está en la venta.']);
                }
            } else {
                return response()->json(['message' => 'La venta no fue encontrada.']);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function updateOrder(Request $request, $id)
    {
        try {
            $venta = Sale::find($id);
            $productosExistentes = SaleDetail::where('sale_id', $id)->pluck('presentaciones_id')->toArray();

            $nuevosInsertados = false;

            foreach ($request->detalles as $detalle) {
                $detalleExistente = SaleDetail::where('sale_id', $id)
                    ->where('presentaciones_id', $detalle['presentacion_id'])
                    ->first();

                if ($detalleExistente) {
                    $diferencia = $detalle['cantidad'] - $detalleExistente->quantity;

                    if ($diferencia > 0) {
                        // Se aumentó la cantidad, descontar del stock
                        $presentacion = Presentacion::find($detalle['presentacion_id']);
                        $presentacion->stock_box -= $diferencia;
                        $presentacion->save();
                    }

                    $detalleExistente->update(['quantity' => $detalle['cantidad']]);
                } else {
                    // Nuevo producto
                    SaleDetail::create([
                        'sale_id' => $id,
                        'presentaciones_id' => $detalle['presentacion_id'],
                        'quantity' => $detalle['cantidad'],
                        'price' => 0 // puedes ajustar esto si es necesario
                    ]);

                    $presentacion = Presentacion::find($detalle['presentacion_id']);
                    $presentacion->stock_box -= $detalle['cantidad'];
                    $presentacion->save();

                    $nuevosInsertados = true;
                }
            }

            if ($nuevosInsertados) {
                $venta->update(['status_envio' => 'PENDIENTE']);
            }

            return response()->json(['success' => true, 'message' => 'Orden actualizada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}