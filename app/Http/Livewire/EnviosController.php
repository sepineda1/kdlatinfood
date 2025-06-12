<?php

namespace App\Http\Livewire;

//mails
use App\Mail\EnvioActual;
use App\Mail\EnvioFin;
use App\Models\Presentacion;
use Exception;
use Illuminate\Support\Facades\Mail;
use Automattic\WooCommerce\Client;
use App\Models\Product;
use App\Models\Lotes;
use App\Models\Sale;
use App\Models\SaleDetail;
use Livewire\Component;
use App\Models\Envio;
use App\Models\Customer;
use App\Models\Operario;
use Illuminate\Http\Request;
use App\Models\Inspectors;
use App\Models\PaymentSale;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class EnviosController extends Component
{
    public $selected_id, $venta, $pageTitle, $saleId, $detalle, $componentName, $countDetails;
    public $details;
    public $envio;

    public $saleDetails;

    public $selectedStatus;

    public function render()
    {
        $operario = Operario::all();
        $cliente = Customer::all();
        $lotes = Lotes::all();
        $sale = Sale::all();
        $data3 = Envio::with('operario')->get();
        $data = Envio::with('sales')->get();
        $data2 = Envio::with('transport')->get();
        $presentaciones = Presentacion::all();
        return view('livewire.envios.envios', ['presentaciones' => $presentaciones, 'data' => $data, 'lotes' => $lotes, 'sale' => $sale, 'cliente' => $cliente, 'operario' => $operario, 'data2' => $data2, 'data3' => $data3, 'details' => $this->details])
            ->extends('layouts.theme.app')
            ->section('content');
    }
    public function mount()
    {
        $this->pageTitle = 'List';
        $this->componentName = 'Envios';
        $this->details = [];
        $this->sumDetails = 0;
        $this->countDetails = 0;
        $this->saleId = 0;
        $this->qrResult = 0;
        $this->detalle = 0;
    }

    public function QR($saleId)
    {
        $this->envio = Envio::where('id_sale', $saleId)->first();

        if ($this->envio) {
            $saleDetails = SaleDetail::where('sale_id', $this->envio->id_sale)->get();

            foreach ($saleDetails as $detail) {
                $lot = Lotes::find($detail->lot_id);
                if ($lot) {
                    $detail->codigoBarras = $lot->CodigoBarras;
                } else {
                    $detail->codigoBarras = 'No encontrado';
                }
            }

            $this->saleDetails = $saleDetails;
        }
        $this->emit('barcode-show', 'details loaded');
    }

    public function updateDetails($details)
    {
        $this->details = $details;
    }


    public function updateActual($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->status_envio = 'ACTUAL';
        $sale->save();

        // Envío del correo electrónico
        try{
            $cliente = Customer::findOrFail($sale->CustomerID);
            Mail::to($cliente->email)->send(new EnvioActual($sale));
        }catch(Exception $e){
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }

        /*$user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Se cambio el estado del pedido #' . $id. 'a ACTUAL',
            'seccion' => 'Envios'
        ]);*/

        return response()->json(['success' => true]);
        // Envío del correo electrónico

    }

    public function processQRCode($id, $keyProduct)
    {
        // Buscar la venta por su ID
        $sale = Sale::find($id);

        // Verificar si se encontró la venta
        if ($sale) {
            // Obtener los detalles de venta que coincidan con el product_id proporcionado
            $saleDetails = SaleDetail::where('sale_id', $sale->id)
                ->whereHas('product', function ($query) use ($keyProduct) {
                    $query->where('KeyProduct', $keyProduct);
                })
                ->first();

            // Verificar si se encontraron detalles de venta para el producto
            if ($saleDetails->isNotEmpty()) {
                // Devolver una respuesta de éxito con los detalles de venta del producto
                return response()->json(['saleDetails' => $saleDetails]);
            } else {
                // No se encontraron detalles de venta para el producto
                return response()->json(['message' => 'El producto no está en la venta.']);
            }
        } else {
            // No se encontró la venta

        }
    }

    /*public function processQRCode($id, $presentacion_id ,$keyProduct)
    {
        // Buscar la venta por su ID
        $sale = Sale::find($id);

        // Verificar si se encontró la venta
        if ($sale) {
            // Obtener los detalles de venta que coincidan con el product_id proporcionado
            $saleDetails = SaleDetail::where('sale_id', $sale->id)->where('presentaciones_id',$presentacion_id)->first();
            // Verificar si se encontraron detalles de venta para el producto
            if ($saleDetails->isNotEmpty()) {
                $presentacion = Presentacion::find($presentacion_id);
                if($presentacion->KeyProduct == $keyProduct){
                    // Devolver una respuesta de éxito con los detalles de venta del producto
                    return response()->json(['saleDetails' => $saleDetails]);
                }
                return response()->json(['message' => 'El QR no corresponde al producto.']);
                
            } else {
                // No se encontraron detalles de venta para el producto
                return response()->json(['message' => 'El producto no está en la venta.']);
            }
        } else {
            // No se encontró la venta
            return response()->json(['message' => 'La venta no existe.']);
        }
    }*/

    public function saleDetails($saleID)
    {
        try {
            $sale = Sale::find($saleID);
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
                            'codigoBarras' => $details->lot->CodigoBarras ?? '',
                        ];
                        $detailsArray[] = $detailObject;
                    }
                    return response()->json(['saleDetails' => $detailsArray]);
                } else {
                    return response()->json(['message' => 'El producto no está en la venta.']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }

    }

    public function BusquedaQRCode(Request $request, $qr, $ventaId)
    {
        // Buscar la venta por su ID
        $sale = Sale::find($ventaId);

        // Verificar si se encontró la venta
        if ($sale) {
            // Obtener los detalles de venta de la venta correspondiente al ventaId
            $saleDetails = SaleDetail::where('sale_id', $ventaId)->get();

            // Verificar si hay detalles de venta para la venta
            if ($saleDetails->isNotEmpty()) {
                $productMatch = false;
                $allScanned = true;

                foreach ($saleDetails as $saleDetail) {
                    if ($saleDetail->product->KeyProduct === $qr) {
                        if (!$saleDetail->scanned) {
                            // El código QR coincide con un KeyProduct y no ha sido escaneado previamente
                            $productMatch = true;
                            $LoteForPresentacion = $this->haveLot($saleDetail->presentaciones_id);
                            $saleDetail->lot_id = $LoteForPresentacion->id;
                            $saleDetail->scanned = true;
                            $saleDetail->save();

                            $sale->fecha_escaneo = now();
                            $sale->save();
                        }
                    }
                    // Verificar si hay algún producto que no haya sido escaneado
                    if (!$saleDetail->scanned) {
                        $allScanned = false;
                    }
                }

                // Verificar si se encontró una coincidencia
                if ($productMatch) {
                    // Verificar si se han escaneado todos los productos
                    if ($allScanned) {
                        $sale->fecha_escaneo = now();


                        $sale->save();
                        return response()->json(['message' => 'All Codebars are inserted']);
                    } else {
                        return response()->json(['message' => 'Pase al siguiente producto.']);
                    }
                } else {
                    // El código QR no coincide con ningún KeyProduct de los productos en la venta
                    return response()->json(['message' => '¡Código QR incorrecto!']);
                }
            } else {
                // No se encontraron detalles de venta para la venta
                return response()->json(['message' => 'No se encontraron detalles de venta para la venta.']);
            }
        } else {
            // No se encontró la venta
            return response()->json(['message' => 'No se encontró la venta.']);
        }
    }
    public function updateActualSales()
    {
        $salesToUpdate = Sale::where('status', 'PAID')
            ->where('status_envio', 'PENDIENTE')
            ->get();

        foreach ($salesToUpdate as $sale) {
            // Obtener los detalles de la venta
            $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();

            // Verificar si todos los detalles tienen scanned = 1
            $allDetailsScanned = $saleDetails->every(function ($detail) {
                return $detail->scanned == 1;
            });

            if ($allDetailsScanned) {
                // Actualizar el estado de envío de la venta
                $sale->update(['status_envio' => 'ACTUAL']);

            }
        }
    }
    public function updateSalesStatusAPI(Request $request)
    {
        try {
            // Obtener las ventas que cumplen con las condiciones
            $salesToUpdate = Sale::where('status', 'PAID')
                ->where('status_envio', 'PENDIENTE')
                ->get();

            $updatedSales = []; // Almacena los IDs de las ventas actualizadas

            foreach ($salesToUpdate as $sale) {
                // Obtener los detalles de la venta
                $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();

                // Verificar si todos los detalles tienen scanned = 1
                $allDetailsScanned = $saleDetails->every(function ($detail) {
                    return $detail->scanned == 1;
                });

                if ($allDetailsScanned) {
                    // Actualizar el estado de envío de la venta
                    $sale->update(['status_envio' => 'ACTUAL']);

                    // Almacenar el ID de la venta actualizada
                    $updatedSales[] = $sale->id;
                }
            }

            $response = [
                'success' => true,
                'message' => 'Proceso de actualización completado correctamente.',
                'updated_sales' => $updatedSales,
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            \Log::error($e);

            $response = [
                'success' => false,
                'message' => 'Hubo un error en el servidor.',
                'exception' => get_class($e),
            ];

            return response()->json($response, 500);
        }
    }

    public function haveLot($id)
    {
        return Lotes::where('SKU', $id)
            ->where('Fecha_Vencimiento', '>=', now()) // Solo considera fechas futuras o actuales
            ->orderBy('Fecha_Vencimiento', 'asc')->first();
    }

    public function verifyQRCode(Request $request)
    {
        try {
            $qrCode = $request->input('qrCode');
            $ventaId = $request->input('ventaId');

            // Recuperar las claves de productos escaneadas para esta venta (si ya existen)
            $scannedProductKeys = Cache::get('scanned_product_keys_' . $ventaId, []);

            // Buscar la venta por su ID
            $sale = Sale::find($ventaId);

            if ($sale) {
                // Obtener los detalles de venta de la venta correspondiente al ventaId
                $saleDetails = SaleDetail::where('sale_id', $ventaId)->get();

                if ($saleDetails->isNotEmpty()) {
                    $productMatch = false;

                    foreach ($saleDetails as $saleDetail) {
                        if ($saleDetail->product->KeyProduct === $qrCode && !$saleDetail->scanned) {

                            // El código QR coincide con un KeyProduct y no ha sido escaneado previamente
                            $LoteForPresentacion = $this->haveLot($saleDetail->presentaciones_id);
                            $saleDetail->lot_id = $LoteForPresentacion->id;
                            $productMatch = true;
                            $saleDetail->scanned = true;
                            $saleDetail->save();
                            $sale->fecha_escaneo = now();
                            $sale->save();
                            // Registrar la clave del producto escaneado
                            $scannedProductKeys[] = $qrCode;
                            Cache::put('scanned_product_keys_' . $ventaId, $scannedProductKeys, 60);

                            // Verificar si se han escaneado todas las claves de productos
                            $allScanned = $this->checkIfAllScanned($ventaId);

                            if ($allScanned) {
                                // Llamar a la función updateActual si todas las claves de productos se han escaneado
                                $this->updateActual($ventaId);
                                // Borrar la caché cuando todos los códigos QR han sido escaneados
                                Cache::forget('scanned_product_keys_' . $ventaId);


                                $sale->fecha_escaneo = now();
                                $sale->save();

                                return response()->json(['message' => 'Todos los codigos QR han sido escaneados.']);
                                //$this->updateActualSales();
                            } else {
                                $this->updateActualSales();
                                return response()->json(['message' => 'Pase al siguiente producto.']);
                            }
                        }
                    }

                    if (!$productMatch) {
                        return response()->json(['message' => 'Codigo QR incorrecto para esta venta.']);
                        //$this->updateActualSales();
                    }
                } else {
                    return response()->json(['message' => 'No se encontraron detalles de venta para la venta.']);
                }
            } else {
                return response()->json(['message' => 'No se encontro la venta.']);
            }
        } catch (\Exception $e) {
            \Log::error($e);

            $response = [
                'success' => false,
                'message' => 'Hubo un error en el servidor.',
                'exception' => $e->getMessage(),
            ];

            return response()->json($response, 500);
        }
    }


    //VerificarQR y Numero de Cajas
    public function verifyQRCode1(Request $request)
    {
        $qrCode = $request->input('qrCode');
        $saleDetailID = $request->input('saleDetail');
        $quantity = $request->input('quantity');

        $saleDetail = SaleDetail::find($saleDetailID);
        if (!$saleDetail) {
            return response()->json(['message' => 'No se encontraron detalles de venta para la venta.'], 400);
        }

        $sale = Sale::find($saleDetail->sale_id);
        if (!$sale) {
            return response()->json(['message' => 'No se encontro la venta.'], 400);
        }

        if ($saleDetail->scanned) {
            return response()->json(['message' => 'Este Producto ya fué escaneado.'], 409);
        }

        if ($saleDetail->product->KeyProduct !== $qrCode) {
            return response()->json(['message' => 'Codigo QR incorrecto para esta venta.'], 400);
        }

        if ((int) $saleDetail->quantity !== (int) $quantity) {
            return response()->json([
                'message' => 'El número de Cajas ingresadas NO corresponde al pedido. El número correcto es: ' . $saleDetail->quantity . " y me enviastes : " . $quantity
            ], 400);
        }

        try {

            DB::beginTransaction();
            $LoteForPresentacion = $this->haveLot($saleDetail->presentaciones_id);
            $saleDetail->lot_id = $LoteForPresentacion->id;
            $saleDetail->scanned = true;
            $saleDetail->save();

            $sale->fecha_escaneo = now();
            $sale->save();

            $scannedProductKeys = Cache::get('scanned_product_keys_' . $sale->id, []);
            $scannedProductKeys[] = $qrCode;
            Cache::put('scanned_product_keys_' . $sale->id, $scannedProductKeys, 60);

            $allScanned = $this->checkIfAllScanned($sale->id);

            if ($allScanned) {
                $this->updateActual($sale->id);
                Cache::forget('scanned_product_keys_' . $sale->id);

                $sale->fecha_escaneo = now();
                $sale->save();
            }

            DB::commit();

            return response()->json([
                'message' => $allScanned
                    ? 'Todos los codigos QR han sido escaneados.'
                    : 'Pase al siguiente producto.'
            ], 200);


        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);

            $response = [
                'success' => false,
                'message' => 'Hubo un error en el servidor.',
                'exception' => $e->getMessage(),
            ];

            return response()->json($response, 500);
        }
    }

    // Función para verificar si se han escaneado todas las claves de productos
    /*private function checkIfAllScanned($ventaId, $saleDetails)
    {
        $scannedProductKeys = Cache::get('scanned_product_keys_' . $ventaId, []);

        foreach ($saleDetails as $saleDetail) {
            if (!$saleDetail->scanned) {
                // Si hay algún producto no escaneado, retorna falso
                return false;
            }

            // Verificar si la clave del producto está registrada en las claves escaneadas
            if (!in_array($saleDetail->product->KeyProduct, $scannedProductKeys)) {
                // Si falta una clave en las escaneadas, retorna falso
                return false;
            }
        }

        // Si todas las claves de productos se han escaneado, retorna verdadero
        return true;
    }*/

    private function checkIfAllScanned($ventaId)
    {
        $pending = SaleDetail::where('sale_id', $ventaId)
            ->where('scanned', false)
            ->count();

        return $pending === 0;
    }

    public function updateFinApi($id)
    {
        $sale = Sale::findOrFail($id);

        $sale->status_envio = 'FIN';
        $sale->fecha_firma = now();
        $sale->save();

        $cliente = Customer::findOrFail($sale->CustomerID);

        try{
            Mail::to($cliente->email)->send(new EnvioFin($sale));
        }catch(Exception $e){
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully']);
    }
    public function updateFin($id)
    {
        //Venta fué finalizada
        $sale = Sale::findOrFail($id);
        $sale->fecha_firma = now();
        $sale->status_envio = 'FIN';
        $sale->save();

        $cliente = Customer::findOrFail($sale->CustomerID);
        
        try{
            Mail::to($cliente->email)->send(new EnvioFin($sale));
        }catch(Exception $e){
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }
        

        // Check if the WooCommerce order ID is not null before making the API call
        /* if ($sale->woocommerce_order_id !== null) {
             $wooCommerceOrderId = $sale->woocommerce_order_id;
             $wooCommerceClient = new \Automattic\WooCommerce\Client(
                 'https://kdlatinfood.com', // URL de tu tienda WooCommerce
                 'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
                 'cs_723eab16e53f3607fd38984b00f763310cc4f473',
                 [
                     'wp_api' => true,
                     'version' => 'wc/v3',
                 ]
             );
             $wooCommerceClient->put('orders/' . $wooCommerceOrderId, ['status' => 'completed']);
         }*/

        return response()->json(['success' => true]);
    }

    public function updateFin2($id, Request $request)
    {
        $sale = Sale::findOrFail($id);
        $sale->fecha_firma = now();
        $sale->status_envio = 'FIN';
        $sale->save();

        $cliente = Customer::findOrFail($sale->CustomerID);
        

        try{
            Mail::to($cliente->email)->send(new EnvioFin($sale));
        }catch(Exception $e){
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }
        

        // Obtener la firma del cuerpo de la solicitud
        $firma = $request->input('firma');

        if (empty($firma)) {
            return response()->json(['error' => 'Firma no proporcionada'], 400);
        }

        // Verificar y limpiar la cadena base64
        $firma = preg_replace('#^data:image/\w+;base64,#i', '', $firma);

        // Comprobar si la base64 está correcta
        if (base64_decode($firma, true) === false) {
            return response()->json(['error' => 'Firma base64 inválida'], 400);
        }

        // Generar el nombre del archivo basado en el ID de la venta
        $nombreImagen = $id . '.png';

        // Definir la ruta en la que se guardará la firma
        $rutaFirma = 'public/firmas/' . $nombreImagen;

        // Asegurarse de que la carpeta exista
        if (!Storage::exists('public/firmas')) {
            Storage::makeDirectory('public/firmas');
        }

        // Almacenar la firma en la carpeta especificada
        Storage::put($rutaFirma, base64_decode($firma));

        // Obtener la URL pública de la imagen almacenada
        $urlDescarga = Storage::url($rutaFirma);

        // Devolver la respuesta con el URL de la firma
        return response()->json([
            'success' => true,
            'message' => 'Firma guardada correctamente',
            'firma_url' => $urlDescarga
        ]);
    }


    public function guardarFirma(Request $request)
    {
        // Obtener la imagen de la firma del cuerpo de la solicitud
        $firma = $request->input('firma');

        // Generar un nombre único para la imagen
        $nombreImagen = 'firma_' . time() . '.png';

        // Almacenar la imagen en el almacenamiento temporal de Laravel
        $rutaTemporal = 'temp/' . $nombreImagen;
        Storage::put($rutaTemporal, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firma)));

        // Obtener la URL de descarga de la imagen
        $urlDescarga = Storage::temporaryUrl($rutaTemporal, now()->addMinutes(5));
        /*$user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Se recibio la firma del cliente',
            'seccion' => 'Envios'
        ]);*/
        // Devolver la URL de descarga en la respuesta
        return response()->json(['url' => $urlDescarga]);
    }

    //Controlador Añadido Vista Envios Agregar Detalle de Ventas
    public function updateOrder(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $detalles = $request->input('detalles');

        DB::beginTransaction();
        try {
            // Eliminar los existentes
            SaleDetail::where('sale_id', $id)->delete();

            foreach ($detalles as $detalle) {
                SaleDetail::create([
                    'sale_id' => $id,
                    'quantity' => $detalle['cantidad'],
                    // Aquí deberías buscar el ID real de la presentación
                    'presentaciones_id' => Presentacion::where('barcode', $detalle['presentacion'])->first()->id,
                ]);
            }

            $sale->status_envio = 'PENDIENTE';
            $sale->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentSaleRegistrer(Request $request)
    {
        try {
            // Validar los datos de la solicitud
            $request->validate([
                'payment_sale_id' => 'required|exists:payment_sale,id',
                'id_user' => 'required|exists:users,id',
                'cash' => 'required|numeric|gt:0',
            ]);            
            $paymentSale = PaymentSale::find($request->payment_sale_id);        
            if (!$paymentSale) {
                return response()->json(['message' => 'No existe el registro de Pago'], 404);
            }

            if ($request->cash < $paymentSale->amount) {
                return response()->json([
                    'message' => "El valor recibido debe ser mayor o igual a {$paymentSale->amount}"
                ], 404);
            }

            $paymentSale->id_user = $request->id_user;
            $paymentSale->cash = $request->cash;
            $paymentSale->save();
            return response()->json(['message' => 'Cantidad actualizada con éxito']);

        } catch (\Exception $e) {
            // Manejo de errores en caso de excepción
            return response()->json(['message' => 'Error al actualizar la cantidad', 'error' => $e->getMessage()], 500);
        }
    }    
}
