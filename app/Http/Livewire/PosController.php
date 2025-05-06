<?php

namespace App\Http\Livewire;

use App\Models\Presentacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use App\Mail\NewSale;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Denomination;
use App\Models\SaleDetail;
use Livewire\Component;
use App\Models\Customer;
use App\Models\User;
use App\Traits\CartTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Lotes;
use App\Models\Discounts;
use App\Traits\Utils;
//use DB;
use App\Models\Inspectors;
use Illuminate\Http\Request;
use App\Events\NuevoPedido;
use Carbon\Carbon;
//QUIKBOOKS
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\DataService\DataService;
use App\Models\quickbook_credentials;
use App\Services\QuickBooksService;
use App\Contracts\ServicePayServiceInterface;
use App\Contracts\DeliveryTypeServiceInterface;
use Exception;


class PosController extends Component
{
	use Utils;
	use CartTrait;

	public $total = 0, $itemsQuantity, $efectivo, $change, $cliente, $totalDescuento = 0;
	public $searchTerm = '';
	public $buscar = '';
	public $servicesAdd = [];
	protected $servicePay;
	protected $serviceDeliveryTypes;

	// --- NUEVAS PROPIEDADES ---//
	public $deliveryTypes;         // colección de tipos de entrega
	public $deliveryType = null;   // ID seleccionado
	public $deliveryDate = null;   // fecha seleccionada
	public $pendingPayment = null; // 'cash' o 'credit'
	public $montoService = 0;
	protected $totalWithService = 0;

	public function boot(ServicePayServiceInterface $servicePay, DeliveryTypeServiceInterface $serviceDeliveryTypes)
	{
		$this->servicePay = $servicePay;
		$this->serviceDeliveryTypes = $serviceDeliveryTypes;
	}

	public function comando(Request $request)
	{
		// Obtener las ventas que cumplen con las condiciones
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

		return response()->json(['message' => 'Proceso completado'], 200);

	}

	public function updatedDeliveryType()
	{
		if($this->deliveryType != 3){
			if($this->montoService === 0){
				$this->servicesAdd = $this->servicePay->getServicePayByCustomerId($this->cliente);
				foreach ($this->servicesAdd as $service) {
					if ($service->state === 1) {
						$this->montoService += number_format($service->amount, 2);
					}
				}
			}
			
		}else{
			$this->montoService = 0;
		}	
		
	}

	public function updatedCliente()
	{
		$this->totalDescuento = 0;
		$this->render();
		$this->emit('success');
	}

	public function DeleteItem()
	{
		$this->updatedCliente();

	}

	public function mount()
	{
		$cliente = Customer::find($this->cliente);
		$this->efectivo = 0;
		$this->change = 0;
		$this->cliente = 'Elegir';
		$this->total = Cart::getTotal();
		$this->itemsQuantity = Cart::getTotalQuantity();
		$this->deliveryTypes = $this->serviceDeliveryTypes->listCatalog();
	}
	public function showDeliveryModal(string $method)
	{
		// 1) Validar cliente
		if (!$this->cliente || $this->cliente === 'Elegir') {
			$this->emit('producto-creado');
			$this->emit('sale-error', 'Por favor, selecciona un cliente antes de continuar.');
			return;
		}

		// 2) Validar que haya productos en el carrito
		if ($this->itemsQuantity <= 0) {
			$this->emit('producto-creado');
			$this->emit('sale-error', 'El carrito está vacío. Agrega productos para continuar.');
			return;
		}

		// 3) Todo ok, abrimos el modal
		$this->pendingPayment = $method;
		$this->deliveryType = null;
		$this->deliveryDate = null;
		$this->dispatchBrowserEvent('openDeliveryModal');
	}


	public function confirmDelivery()
	{

		try {
			// reglas
			$rules = [
				'deliveryType' => 'required',
			];
			$messages = [
				'deliveryType.required' => 'Selecciona un tipo de entrega',
			];

			if ((int) $this->deliveryType != 2) {
				$rules = [
					'deliveryType' => 'required',
					'deliveryDate' => 'required',
				];
				$messages = [
					'deliveryType.required' => 'Selecciona un tipo de entrega.',
					'deliveryDate.required' => 'Selecciona una fecha correcta.',
				];
			}
		
			$this->validate($rules, $messages);
		
			// validación extra para Mismo Día
			if ((int) $this->deliveryType === 2) {
				// Hora actual en Bogotá
				$now = Carbon::now();
				// Punto de corte: hoy a las 09:00
				$cutoff = Carbon::today()->setHour(9)->setMinute(0)->setSecond(0);
				// Si ya pasó de las 9 AM, error
				if ($now->greaterThanOrEqualTo($cutoff)) {
					$this->emit('producto-creado');
					$this->emit('sale-error', 'Las entregas  mismo día son VALIDAS si hacen antes de las 9 AM.');
					return;
				}
				// Forzamos la fecha de entrega a hoy
				$this->deliveryDate = $now->toDateString();
			}
	
			// cerrar modal
			$this->dispatchBrowserEvent('closeDeliveryModal');

			// --- ajustar total con servicios ---
			/*$serviceFee = 0;
			if ($this->deliveryType != 3) {
				foreach ($this->servicesAdd as $serv) {
					if ($serv->state === 1) {
						$serviceFee += $serv->amount;
					}
				}
				$this->total += $serviceFee;
			}*/

			// disparar pago correspondiente
			if ($this->pendingPayment === 'credit') {
				$this->payWithCreditConfirmed();
			} else {
				$this->saveSaleConfirmed();
			}
			
			//$this->payWithCreditConfirmed();
		} catch (Exception $e) {
			$this->emit('producto-creado');
			$this->emit('sale-error', 'Error en el procesamiento : ' . $e->getMessage());
			return;
		}

	}

	public function closeDeliveryModal(){
		$this->dispatchBrowserEvent('closeDeliveryModal');

	}

	public function payWithCreditConfirmed()
	{
		try {
			if ($this->itemsQuantity <= 0) {
				$this->emit('producto-creado');
				$this->emit('sale-error', 'Ingrese productos al carrito por favor');
				return;
			}

			$rules = [
				'efectivo' => "required"
			];

			$messages = [
				'efectivo.required' => 'Ingrese el monto a pagar'
			];

			$this->validate($rules, $messages);

			DB::beginTransaction(); // Inicia la transacción

			$cliente = Customer::find($this->cliente);

			if (!$cliente) {
				DB::rollBack(); // Revertir transacciones si no se encuentra el cliente
				$this->emit('producto-creado');
				$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
				return;
			}
			$items = Cart::getContent();
			foreach ($items as $item) {
				$product = Presentacion::find($item->id);
				if ($product->visible === "no") {
					$this->emit('producto-creado');
					$this->emit('sale-error', 'No se puede realizar la venta porque tienes un producto NO Disponible. ELIMINA EL PRODUCTO NO DISPONIBLE.');
					return;
				}
			}

			$montoDescontar = round(((float) $this->total + (float) $this->montoService) - (float) $this->totalDescuento, 2);
			if ($cliente->saldo >= $montoDescontar) {
				// Actualizar el saldo del cliente
				$cliente->saldo -= $montoDescontar;
				$cliente->save();

				
				/*$this->servicesAdd = $this->servicePay->getServicePayByCustomerId($this->cliente);
			foreach ($this->servicesAdd as $service) {
				if ($service->state === 1) {
					$this->total += number_format($service->amount, 2);
				}
			}*/
				// Crear la venta
				$sale = Sale::create([
					'total' => $this->total - $this->totalDescuento,
					'items' => $this->itemsQuantity,
					'cash' => $this->efectivo,
					'change' => $this->change,
					'user_id' => Auth()->user()->id,
					'CustomerID' => $this->cliente,
					'total_with_services' => $this->total + $this->montoService
				]);
				$SUMtotal = 0;
				if ($sale) {

					

					$totalCajas = 0;
					$productosNOvisibles = 0;
					foreach ($items as $item) {
						$product = Presentacion::find($item->id);

						if (!$product) {
							DB::rollBack(); // Revertir transacciones si no se encuentra el producto
							$this->emit('producto-creado');
							$this->emit('sale-error', 'Producto no encontrado');
							return;
						}

						// Calcular el número de cajas basado en la cantidad de items
						$cajas = $item->quantity;
						$totalCajas += $cajas;

						$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
						$discount = ($discount) ? $discount->discount : 0;
						if ($product->visible === "si") {
							SaleDetail::create([
								'price' => $item->price,
								'quantity' => $item->quantity,
								'presentaciones_id' => $item->id,
								'sale_id' => $sale->id,
								'CustomerID' => $this->cliente,
								'cajas' => $cajas,
								'discount' => $discount
							]);
							$SUMtotal += ($item->price - ($item->price * $discount) / 100) * $item->quantity;
							// Actualizar el stock del producto
							$product->stock_box -= $item->quantity;

						} else {
							$productosNOvisibles += $item->quantity;
						}

						/*if ($product->stock_box < 0) {
							DB::rollBack(); // Revertir si el stock no es suficiente
							$this->emit('producto-creado');
							$this->emit('sale-error', "Stock insuficiente para el producto {$product->id}");
							return;
						}*/

						$product->save();
					}

					// Guardar el total de cajas en la venta
					$sale->items = $sale->items - $productosNOvisibles;
					$sale->total_cajas = $totalCajas - $productosNOvisibles;
					$sale->save();
					$this->totalDescuento = 0;
					//$QUICK = new QuickBooksService();
					//$QUICK->create_invoice($sale->id);
					
					//Primero obtengo los servicios
					$services = $this->servicePay->getServicePayByCustomerId($this->cliente);
					foreach ($services as $service) {
						if ((int) $service->state === 1) {
							if ((int) $this->deliveryType != 3) {
								$this->servicePay->addServiceSale($sale->id, $service->id, $service->amount);
							}
						}
					}

					$this->serviceDeliveryTypes->add($sale->id, (int) $this->deliveryType, $this->deliveryDate);

				}
				$sale->total = ($sale->total != $SUMtotal) ? $SUMtotal : $sale->total;
				$sale->save();
				// Enviar correo electrónico al cliente
				$customer = Customer::find($this->cliente);
				$emailData = [
					'customer' => $customer,
					'sale' => $sale,
					'items' => $items,
				];
				// 

				try {
					Mail::to($customer->email)->send(new NewSale($emailData));
				} catch (Exception $e) {
					\Log::error('Error al enviar correo : ' . $e->getMessage());
				}

				// Limpiar el carrito y restablecer los valores
				Cart::clear();
				$this->efectivo = 0;
				$this->change = 0;
				$this->total = Cart::getTotal();
				$this->itemsQuantity = Cart::getTotalQuantity();
				$this->montoService = 0;
				$this->deliveryType = 0;

				// Registro de inspector
				$user = Auth()->user()->name;
				Inspectors::create([
					'user' => $user,
					'action' => 'Registro una venta',
					'seccion' => 'Sales',
				]);

				$ticket = $this->buildTicket($sale);
				$d = $this->Encrypt($ticket);
				$this->emit('print-ticket', $d);

				DB::commit(); // Confirma la transacción
				$this->emit('producto-creado');
				$this->emit('sale-ok', 'Venta registrada con éxito');

			} else {
				DB::rollBack(); // Revertir si el saldo del cliente es insuficiente
				$this->emit('producto-creado');
				$this->emit('sale-error', 'Saldo insuficiente para realizar la compra');
			}
		} catch (Exception $e) {
			DB::rollBack(); // Revertir en caso de excepción
			$this->emit('producto-creado');
			$this->emit('sale-error', $e->getMessage());
			throw $e;
		}
	}

	/*public function update_access_token()
			 {
				 $config = config('quickbooks');
				 $quickbook_credentials = quickbook_credentials::where('status', 1)->first();
				 // dd($quickbook_credentials);
				 if ($quickbook_credentials->count() > 0) {
					 $access_token = $quickbook_credentials->access_token;
					 $refresh_access_token = $quickbook_credentials->refresh_access_token;
				 } else {
					 $access_token = $config['access_token'];
					 $refresh_access_token = $config['refresh_access_token'];
				 }
				 $dataService = DataService::Configure([
					 'auth_mode' => 'oauth2',
					 'ClientID' => $config['client_id'],
					 'ClientSecret' => $config['client_secret'],
					 'RedirectURI' => $config['redirect_uri'],
					 'accessTokenKey' => $access_token,
					 'refreshTokenKey' => $refresh_access_token,
					 'QBORealmID' => $config['realm_id'],
					 'baseUrl' => $config['base_url'],
					 'token_refresh_interval_before_expiry' => $config['base_url'],
				 ]);
				 $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
				 $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($config['refresh_token']);
				 $accessTokenValue = $accessTokenObj->getAccessToken();
				 $refreshTokenValue = $accessTokenObj->getRefreshToken();

				 $dataArr['client_id'] = $config['client_id'];
				 $dataArr['client_secret'] = $config['client_secret'];
				 $dataArr['realm_id'] = $config['realm_id'];
				 $dataArr['redirect_uri'] = $config['redirect_uri'];
				 $dataArr['base_url'] = $config['base_url'];
				 $dataArr['status'] = 1;
				 $dataArr['access_token'] = $accessTokenValue;
				 $dataArr['refresh_token'] = $refreshTokenValue;

				 $quickbook_credentials->where('id', 1)->update($dataArr);
				 $this->emit('global-msg', 'Token Actualizado');
				 return ['access_token' => $accessTokenValue, 'refresh_token' => $refreshTokenValue];
			 }*/
	public function payWithCredit_test()
	{
		$config = config('quickbooks');
		$qb_credentials = $this->update_access_token();
		$dataService = DataService::Configure([
			'auth_mode' => 'oauth2',
			'ClientID' => $config['client_id'],
			'ClientSecret' => $config['client_secret'],
			'RedirectURI' => $config['redirect_uri'],
			'accessTokenKey' => $qb_credentials['access_token'],
			'refreshTokenKey' => $qb_credentials['refresh_token'],
			'QBORealmID' => $config['realm_id'],
			'baseUrl' => $config['base_url'],
		]);
		// Cliente de prueba
		$cliente = Customer::find(1);

		// Productos de prueba
		$product1 = [

			'id' => '22',
			'price' => 100.0,
			'qty' => 2,
		];

		$product2 = [

			'id' => '25',
			'price' => 150.0,
			'qty' => 3,
		];

		try {
			// Crear la factura en QuickBooks
			$qb_invoice = Invoice::create([
				"Line" => [
					[
						"DetailType" => "SalesItemLineDetail",
						"Amount" => 20,
						"SalesItemLineDetail" => [
							"ItemRef" => [
								"name" => "Tequeño Queso P.",
								"value" => 25
							],
							"Qty" => 6
						]
					]
				],
				"CustomerRef" => [
					"value" => 59
				]
			]);
			$result = $dataService->Add($qb_invoice);

			// Si llega aquí, la factura se creó correctamente
			// Agrega cualquier otra lógica que necesites después de crear la factura

		} catch (ServiceException $e) {
			// Manejar el error y enviarlo al log
			\Log::error("Error al crear la factura en QuickBooks: " . $e);
			// También podrías lanzar una excepción o manejar el error de otra manera según tus necesidades
		}

		// Manejar la respuesta de QuickBooks, etc.
	}

	public function payWithCreditApi(Request $request)
	{
		$data = $request->validate([
			'cliente' => 'required|integer',
			'total' => 'required|numeric',
			'efectivo' => 'required|numeric',
			'change' => 'required|numeric',
			'items' => 'required|array',
			'items.*.id' => 'required|integer',
			'items.*.quantity' => 'required|integer|min:1',
		]);



		$cliente = Customer::find($data['cliente']);

		if (!$cliente) {
			return response()->json(['error' => 'SELECCIONA UN CLIENTE'], 400);
		}

		if ($cliente->saldo >= $data['total']) {
			// Actualizar el saldo del cliente
			$cliente->saldo -= $data['total'];
			$cliente->save();

			// Crear la venta
			$sale = Sale::create([
				'total' => $data['total'],
				'items' => array_sum(array_column($data['items'], 'quantity')),
				'cash' => $data['efectivo'],
				'change' => $data['change'],
				'CustomerID' => $data['cliente'],
			]);

			if ($sale) {
				$totalCajas = 0;

				$lineItems = [];

				foreach ($data['items'] as $item) {
					$product = Product::find($item['id']);
					$cajas = ceil($item['quantity'] / $product->tam1);
					$totalCajas += $cajas;

					$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
					$discount = ($discount) ? $discount->discount : 0;
					// Guardar los detalles de la venta
					SaleDetail::create([
						'price' => $product->price,
						'quantity' => $item['quantity'],
						'product_id' => $product->id,
						'sale_id' => $sale->id,
						'CustomerID' => $data['cliente'],
						'cajas' => $cajas,
						'discount' => $discount
					]);

					// Actualizar el stock del producto
					$product->stock -= $item['quantity'];
					$product->save();


				}

				$this->totalDescuento = 0;
				$sale->total_cajas = $totalCajas;
				$sale->save();

				//$QUICK = new QuickBooksService();
				//$QUICK->create_invoice($sale->id); 
			}

			// Evento de nueva venta
			event(new NuevoPedido($sale));

			// Respuesta exitosa
			return response()->json(['message' => 'Compra realizada con éxito'], 200);
		} else {
			// Saldo insuficiente
			return response()->json(['error' => 'Saldo insuficiente para realizar la compra'], 400);
		}
	}

	private function showNotification($message)
	{
		// Lógica para mostrar la notificación usando Push.js
		echo '<script>Push.create("Compra Realizada", { body: "' . $message . '", timeout: 4000 });</script>';
	}
	public function payWithCredit()
	{
		try {
			if ($this->itemsQuantity <= 0) {
				$this->emit('producto-creado');
				$this->emit('sale-error', 'Ingrese productos al carrito por favor');
				return;
			}

			$rules = [
				'efectivo' => "required"
			];

			$messages = [
				'efectivo.required' => 'Ingrese el monto a pagar'
			];

			$this->validate($rules, $messages);

			DB::beginTransaction(); // Inicia la transacción

			$cliente = Customer::find($this->cliente);

			if (!$cliente) {
				DB::rollBack(); // Revertir transacciones si no se encuentra el cliente
				$this->emit('producto-creado');
				$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
				return;
			}
			$items = Cart::getContent();
			foreach ($items as $item) {
				$product = Presentacion::find($item->id);
				if ($product->visible === "no") {
					$this->emit('producto-creado');
					$this->emit('sale-error', 'No se puede realizar la venta porque tienes un producto NO Disponible. ELIMINA EL PRODUCTO NO DISPONIBLE.');
					return;
				}
			}

			if ($cliente->saldo >= number_format($this->total - $this->totalDescuento, 2)) {
				// Actualizar el saldo del cliente
				$cliente->saldo -= number_format($this->total - $this->totalDescuento, 2);
				$cliente->save();

				// Crear la venta
				$sale = Sale::create([
					'total' => $this->total,
					'items' => $this->itemsQuantity,
					'cash' => $this->efectivo,
					'change' => $this->change,
					'user_id' => Auth()->user()->id,
					'CustomerID' => $this->cliente,
				]);
				$SUMtotal = 0;
				if ($sale) {

					$totalCajas = 0;
					$productosNOvisibles = 0;
					foreach ($items as $item) {
						$product = Presentacion::find($item->id);

						if (!$product) {
							DB::rollBack(); // Revertir transacciones si no se encuentra el producto
							$this->emit('producto-creado');
							$this->emit('sale-error', 'Producto no encontrado');
							return;
						}

						// Calcular el número de cajas basado en la cantidad de items
						$cajas = $item->quantity;
						$totalCajas += $cajas;

						$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
						$discount = ($discount) ? $discount->discount : 0;
						if ($product->visible === "si") {
							SaleDetail::create([
								'price' => $item->price,
								'quantity' => $item->quantity,
								'presentaciones_id' => $item->id,
								'sale_id' => $sale->id,
								'CustomerID' => $this->cliente,
								'cajas' => $cajas,
								'discount' => $discount
							]);
							$SUMtotal += ($item->price - ($item->price * $discount) / 100) * $item->quantity;
							// Actualizar el stock del producto
							$product->stock_box -= $item->quantity;

						} else {
							$productosNOvisibles += $item->quantity;
						}

						if ($product->stock_box < 0) {
							DB::rollBack(); // Revertir si el stock no es suficiente
							$this->emit('producto-creado');
							$this->emit('sale-error', "Stock insuficiente para el producto {$product->id}");
							return;
						}

						$product->save();
					}

					// Guardar el total de cajas en la venta
					$sale->items = $sale->items - $productosNOvisibles;
					$sale->total_cajas = $totalCajas - $productosNOvisibles;
					$sale->save();
					$this->totalDescuento = 0;

					$services = $this->servicePay->getServicePayByCustomerId($this->cliente);
					foreach ($services as $service) {
						if ((int) $service->state === 1) {
							if ((int) $this->deliveryType != 3) {
								$this->servicePay->addServiceSale($sale->id, $service->id, $service->amount);
							}
						}
					}

					//$QUICK = new QuickBooksService();
					//$QUICK->create_invoice($sale->id);
				}
				$sale->total = ($sale->total != $SUMtotal) ? $SUMtotal : $sale->total;
				$sale->save();
				// Enviar correo electrónico al cliente
				$customer = Customer::find($this->cliente);
				$emailData = [
					'customer' => $customer,
					'sale' => $sale,
					'items' => $items,
				];
				// 

				try {
					Mail::to($customer->email)->send(new NewSale($emailData));
				} catch (Exception $e) {
					\Log::error('Error al enviar correo : ' . $e->getMessage());
				}

				// Limpiar el carrito y restablecer los valores
				Cart::clear();
				$this->efectivo = 0;
				$this->change = 0;
				$this->total = Cart::getTotal();
				$this->itemsQuantity = Cart::getTotalQuantity();

				// Registro de inspector
				$user = Auth()->user()->name;
				Inspectors::create([
					'user' => $user,
					'action' => 'Registro una venta',
					'seccion' => 'Sales',
				]);

				$ticket = $this->buildTicket($sale);
				$d = $this->Encrypt($ticket);
				$this->emit('print-ticket', $d);

				DB::commit(); // Confirma la transacción
				$this->emit('producto-creado');
				$this->emit('sale-ok', 'Venta registrada con éxito');

			} else {
				DB::rollBack(); // Revertir si el saldo del cliente es insuficiente
				$this->emit('producto-creado');
				$this->emit('sale-error', 'Saldo insuficiente para realizar la compra');
			}
		} catch (\Exception $e) {
			DB::rollBack(); // Revertir en caso de excepción
			$this->emit('producto-creado');
			$this->emit('sale-error', $e->getMessage());
			throw $e;
		}
	}

	private function updateWooCommerceStock($barcode, $stock)
	{
		// Configurar la URL y los datos para la solicitud a la API de WooCommerce
		$url = 'https://kdlatinfood.com/wp-json/wc/v3/products';
		$productId = null;

		// Buscar el producto en WooCommerce por SKU ($barcode)
		$response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')
			->get($url, ['sku' => $barcode]);

		if ($response->successful()) {
			$products = $response->json();
			if (!empty($products)) {
				// Obtener el ID del producto en WooCommerce
				$productId = $products[0]['id'];
			}
		}

		if ($productId) {
			// Actualizar el stock del producto en WooCommerce
			$response = Http::withBasicAuth('ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5', 'cs_723eab16e53f3607fd38984b00f763310cc4f473')
				->put("$url/$productId", ['stock_quantity' => $stock]);

			if ($response->successful()) {
				// El stock se actualizó correctamente en WooCommerce
				// Puedes realizar alguna acción adicional si lo deseas
				// Por ejemplo, registrar una entrada en los archivos de registro

			} else {
				// Error al actualizar el stock en WooCommerce
				// Puedes manejar el error según tus necesidades

			}
		} else {
			// No se encontró el producto en WooCommerce por SKU
			// Puedes manejar esta situación según tus necesidades

		}
	}
	public function updatedSearch()
	{
		$this->resetPage();
	}
	public function render()
	{
		$data2 = Customer::all();

		$data3 = Customer::where('name', 'like', '%' . $this->buscar . '%')
			->orWhere('last_name', 'like', '%' . $this->buscar . '%')
			->get();
		$filteredClientes = Customer::where('name', 'LIKE', '%' . $this->searchTerm . '%')
			->orWhere('last_name', 'LIKE', '%' . $this->searchTerm . '%')
			->get();

		$Cart = Cart::getContent()->sortBy('name')->map(function ($item) {
			$presentacion = Presentacion::where('id', $item->id)->first();
			if (!isset($presentacion->id)) {
				$this->removeItem($item->id);
				return false;
			}
			if ($presentacion->visible == 'no') {
				$this->updatePrice($item->id, 0);
			}
			$item->presentacion = $presentacion;
			if ($this->cliente) {

				$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
				if ($discount) {
					$cant = round($item->price * $item->quantity, 2);
					$this->totalDescuento = +round(($discount->discount * $cant) / 100, 2);
					//$this->total = $this->total -  number_format(($discount->discount * $cant) / 100,2);
				}

				$item->discount = $discount;

			}
			return $item;
		});

		//$this->total = $this->total - $this->totalN;

		return view('livewire.pos.component', [
			'data2' => $data2,
			'data3' => $data3,
			'filteredClientes' => $filteredClientes,
			'denominations' => Denomination::orderBy('value', 'desc')->get(),
			'cart' => $Cart
		])
			->extends('layouts.theme.app')
			->section('content');
	}

	// agregar efectivo / denominations
	public function ACash($value)
	{
		try {
			$cliente = Customer::find($this->cliente);
			if (!$cliente) {
				DB::rollBack(); // Revertir transacciones si no se encuentra el cliente
				$this->emit('producto-creado');
				$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
				return;
			}

			$rules = [
				'efectivo' => "required"
			];

			$messages = [
				'efectivo.required' => 'Ingrese el monto a pagar'
			];

			$this->validate($rules, $messages);

			$this->efectivo += ($value == 0 ? number_format($this->total - $this->totalDescuento, 2) : $value);
			$this->change = ($this->efectivo - number_format($this->total - $this->totalDescuento, 2));

		} catch (\Exception $e) {
			$this->emit('sale-error', $e->getMessage());
			throw $e;
		}
	}

	// escuchar eventos
	protected $listeners = [
		'scan-code' => 'ScanCode',
		'removeItem' => 'removeItem',
		'clearCart' => 'clearCart',
		'saveSale' => 'saveSale',
		'refresh' => '$refresh',
		'print-last' => 'printLast'
	];


	// buscar y agregar producto por escaner y/o manual


	public function ScanCode($barcode, $cant = 1)
	{
		$product = Presentacion::where('barcode', $barcode)->first();

		if ($product == null || empty($product)) {
			$this->emit('scan-notfound', 'El producto no está registrado*');
		} else {
			if ($this->InCart($product->id)) {
				$this->IncreaseQuantity($product);
				return;
			}

			if ($product->stock_box < 1) {
				$this->emit('no-stock', 'Stock insuficiente *');
				return;
			}
			Cart::add($product->id, $product->product->name, $product->price, $cant, $product->product->imagen, $product->size->size);
			$this->total = Cart::getTotal();
			$this->itemsQuantity = Cart::getTotalQuantity();

			$this->emit('scan-ok', 'PRODUCTO AGREGADO');
		}
	}





	// incrementar cantidad item en carrito
	public function increaseQty(string $presentacionId, $cant = 1)
	{
		$presentacion = Presentacion::find($presentacionId);
		$this->IncreaseQuantity($presentacion, $cant);
		$this->emit('success');

	}


	// actualizar cantidad item en carrito
	public function updateQty(Presentacion $product, $cant = 1)
	{
		if ($cant <= 0)
			$this->removeItem($product->id);
		else
			$this->UpdateQuantity($product, $cant);
	}

	// decrementar cantidad item en carrito
	public function decreaseQty($productId)
	{
		$this->decreaseQuantity($productId);
		$this->emit('success');
	}

	// vaciar carrito
	public function clearCart()
	{
		try {
			//$this->updateLotesEstadoToNormal();
			$this->trashCart();
		} catch (\Exception $th) {
			$this->emit('sale-error', $th->getMessage());
		}
	}


	public function cleanValue($value)
	{
		return number_format(str_replace(",", "", $value), 2, '.', '');
	}


	// guardar venta
	// Método saveSale()

	public function saveSaleConfirmed()
	{

		if ($this->total <= 0) {
			$this->emit('sale-error', 'AGREGA PRODUCTOS A LA VENTA');
			$this->emit('producto-creado');
			return;
		}
		if ($this->cliente <= 0) {
			$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
			$this->emit('producto-creado');
			return;
		}
		if ($this->efectivo <= 0) {
			$this->emit('sale-error', 'INGRESA EL EFECTIVO');
			$this->emit('producto-creado');
			return;
		}
		if ($this->total > $this->efectivo) {
			$this->emit('sale-error', 'EL EFECTIVO DEBE SER MAYOR O IGUAL AL TOTAL');
			$this->emit('producto-creado');
			return;
		}
		$cliente = Customer::find($this->cliente);

		if (!$cliente) {
			// Manejar el caso en el que no se encuentre el cliente seleccionado
			$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
			$this->emit('producto-creado');
			return;
		}

		$items = Cart::getContent();
		foreach ($items as $item) {
			$product = Presentacion::find($item->id);
			if ($product->visible === "no") {
				$this->emit('producto-creado');
				$this->emit('sale-error', 'No se puede realizar la venta porque tienes un producto NO Disponible. ELIMINA EL PRODUCTO NO DISPONIBLE.');
				return;
			}
		}


		DB::beginTransaction();

		$rules = [
			'CustomerID' => 'required|not_in:Elegir',

		];

		$messages = [
			'CustomerID.not_in' => 'Elige una opción',


		];
		try {
			$sale = Sale::create([
				'total' => $this->total - $this->totalDescuento,
				'items' => $this->itemsQuantity,
				'cash' => $this->efectivo + $this->montoService,
				'change' => $this->change,
				'user_id' => Auth()->user()->id,
				'CustomerID' => $this->cliente,
				'total_with_services' => $this->total + $this->montoService
			]);

			if ($sale) {
				$itemsNOvisible = 0; //Presentaciones NO VISIBLES
				$SUMtotal = 0;
				foreach ($items as $item) {

					//$product = Product::where('id', $item->id)->first();

					$cliente = Customer::find($this->cliente);
					$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
					$discount = $discount ? $discount->discount : 0;
					$product = Presentacion::find($item->id);


					if ($product->visible == 'si') {
						SaleDetail::create([
							'price' => $item->price,
							'quantity' => $item->quantity,
							'presentaciones_id' => $item->id,
							'sale_id' => $sale->id,
							'CustomerID' => $this->cliente,
							'discount' => $discount
						]);

						$product->stock_box -= $item->quantity;
						$product->save();
						$SUMtotal += ($item->price - ($item->price * $discount) / 100) * $item->quantity;

					} else {
						$itemsNOvisible += $item->quantity;
					}

					// Actualizar el stock del producto permitiendo números negativos


					$this->emit('producto-creado');
					//$this->updateWooCommerceStock($product->barcode, $product->stock);
				}
				$sale->total = ($sale->total != $SUMtotal) ? $SUMtotal : $sale->total;
				$sale->items = $sale->items - $itemsNOvisible;
				$sale->save();
				//$QUICK = new QuickBooksService();
				//$QUICK->create_invoice($sale->id); 
				$this->totalDescuento = 0;

				$services = $this->servicePay->getServicePayByCustomerId($this->cliente);
				foreach ($services as $service) {
					if ((int) $service->state === 1) {
						if ((int) $this->deliveryType != 3) {
							$this->servicePay->addServiceSale($sale->id, $service->id, $service->amount);
						}
					}
				}

				$this->serviceDeliveryTypes->add($sale->id, (int) $this->deliveryType, $this->deliveryDate);

			}
			// Enviar correo electrónico al cliente
			$customer = Customer::find($this->cliente);
			$emailData = [
				'customer' => $customer,
				'sale' => $sale,
				'items' => $items,
			];
			//	

			try {
				Mail::to($customer->email)->send(new NewSale($emailData));
			} catch (Exception $e) {
				\Log::error('Error al enviar correo : ' . $e->getMessage());
			}

			DB::commit();
			Cart::clear();
			$this->efectivo = 0;
			$this->change = 0;
			$this->total = Cart::getTotal();
			$this->itemsQuantity = Cart::getTotalQuantity();
			$this->emit('sale-ok', 'Venta registrada con éxito');
			$user = Auth()->user()->name;
			$inspector = Inspectors::create([
				'user' => $user,
				'action' => 'Registro una venta ',
				'seccion' => 'Sales'
			]);
			$ticket = $this->buildTicket($sale);
			$d = $this->Encrypt($ticket);
			$this->emit('print-ticket', $d);
		} catch (Exception $e) {
			DB::rollback();
			$this->emit('sale-error', $e->getMessage());
		}
	}

	public function saveSale()
	{

		if ($this->total <= 0) {
			$this->emit('sale-error', 'AGREGA PRODUCTOS A LA VENTA');
			$this->emit('producto-creado');
			return;
		}
		if ($this->cliente <= 0) {
			$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
			$this->emit('producto-creado');
			return;
		}
		if ($this->efectivo <= 0) {
			$this->emit('sale-error', 'INGRESA EL EFECTIVO');
			$this->emit('producto-creado');
			return;
		}
		if ($this->total > $this->efectivo) {
			$this->emit('sale-error', 'EL EFECTIVO DEBE SER MAYOR O IGUAL AL TOTAL');
			$this->emit('producto-creado');
			return;
		}
		$cliente = Customer::find($this->cliente);

		if (!$cliente) {
			// Manejar el caso en el que no se encuentre el cliente seleccionado
			$this->emit('sale-error', 'SELECCIONA UN CLIENTE');
			$this->emit('producto-creado');
			return;
		}

		$items = Cart::getContent();
		foreach ($items as $item) {
			$product = Presentacion::find($item->id);
			if ($product->visible === "no") {
				$this->emit('producto-creado');
				$this->emit('sale-error', 'No se puede realizar la venta porque tienes un producto NO Disponible. ELIMINA EL PRODUCTO NO DISPONIBLE.');
				return;
			}
		}


		DB::beginTransaction();

		$rules = [
			'CustomerID' => 'required|not_in:Elegir',

		];

		$messages = [
			'CustomerID.not_in' => 'Elige una opción',


		];
		try {
			$sale = Sale::create([
				'total' => number_format($this->total - $this->totalDescuento, 2),
				'items' => $this->itemsQuantity,
				'cash' => $this->efectivo,
				'change' => $this->change,
				'user_id' => Auth()->user()->id,
				'CustomerID' => $this->cliente,
			]);

			if ($sale) {
				$itemsNOvisible = 0; //Presentaciones NO VISIBLES
				$SUMtotal = 0;
				foreach ($items as $item) {

					//$product = Product::where('id', $item->id)->first();

					$cliente = Customer::find($this->cliente);
					$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
					$discount = $discount ? $discount->discount : 0;
					$product = Presentacion::find($item->id);


					if ($product->visible == 'si') {
						SaleDetail::create([
							'price' => $item->price,
							'quantity' => $item->quantity,
							'presentaciones_id' => $item->id,
							'sale_id' => $sale->id,
							'CustomerID' => $this->cliente,
							'discount' => $discount
						]);

						$product->stock_box -= $item->quantity;
						$product->save();
						$SUMtotal += ($item->price - ($item->price * $discount) / 100) * $item->quantity;

					} else {
						$itemsNOvisible += $item->quantity;
					}

					// Actualizar el stock del producto permitiendo números negativos


					$this->emit('producto-creado');
					//$this->updateWooCommerceStock($product->barcode, $product->stock);
				}
				$sale->total = ($sale->total != $SUMtotal) ? $SUMtotal : $sale->total;
				$sale->items = $sale->items - $itemsNOvisible;
				$sale->save();
				//$QUICK = new QuickBooksService();
				//$QUICK->create_invoice($sale->id); 
				$this->totalDescuento = 0;
			}
			// Enviar correo electrónico al cliente
			$customer = Customer::find($this->cliente);
			$emailData = [
				'customer' => $customer,
				'sale' => $sale,
				'items' => $items,
			];
			//	

			try {
				Mail::to($customer->email)->send(new NewSale($emailData));
			} catch (Exception $e) {
				\Log::error('Error al enviar correo : ' . $e->getMessage());
			}

			DB::commit();
			Cart::clear();
			$this->efectivo = 0;
			$this->change = 0;
			$this->total = Cart::getTotal();
			$this->itemsQuantity = Cart::getTotalQuantity();
			$this->emit('sale-ok', 'Venta registrada con éxito');
			$user = Auth()->user()->name;
			$inspector = Inspectors::create([
				'user' => $user,
				'action' => 'Registro una venta ',
				'seccion' => 'Sales'
			]);
			$ticket = $this->buildTicket($sale);
			$d = $this->Encrypt($ticket);
			$this->emit('print-ticket', $d);
		} catch (Exception $e) {
			DB::rollback();
			$this->emit('sale-error', $e->getMessage());
		}
	}






	public function savePurchase()
	{
		if ($this->total <= 0) {
			$this->emit('sale-error', 'AGEGA PRODUCTOS A LA VENTA');
			return;
		}
		if ($this->efectivo <= 0) {
			$this->emit('sale-error', 'INGRESA EL EFECTIVO');
			return;
		}
		if (number_format($this->total - $this->totalDescuento, 2) > $this->efectivo) {
			$this->emit('sale-error', 'EL EFECTIVO DEBE SER MAYOR O IGUAL AL TOTAL');
			return;
		}

		DB::beginTransaction();

		try {

			$sale = Sale::create([
				'total' => number_format($this->total - $this->totalDescuento, 2),
				'items' => $this->itemsQuantity,
				'cash' => $this->efectivo,
				'change' => $this->change,
				'user_id' => Auth()->user()->id,
				'cliente_id' => $this->cliente,
			]);

			if ($sale) {
				$itemsNOvisible = 0; //Presentaciones NO VISIBLES
				$items = Cart::getContent();
				foreach ($items as $item) {

					$discount = Discounts::where('customer_id', $this->cliente)->where('presentacion_id', $item->id)->first();
					$discount = ($discount) ? $discount->discount : 0;
					$product = Presentacion::find($item->id);

					if ($product->visible == "si") { //me aseguro de que la presentacion se ha visible
						SaleDetail::create([
							'price' => $item->price,
							'quantity' => $item->quantity,
							'product_id' => $item->id,
							'sale_id' => $sale->id,
							'discount' => $discount
						]);
						//update stock
						$product->stock_box -= $item->quantity;
						$product->save();
					} else {
						$itemsNOvisible += $item->quantity;
					}
				}
				$sale->items = $sale->items - $itemsNOvisible;
				$sale->save();

				$this->totalDescuento = 0;
				//$QUICK = new QuickBooksService();
				//$QUICK->create_invoice($sale->id); 
			}


			DB::commit();
			//$this->printTicket($sale->id);
			Cart::clear();
			$this->efectivo = 0;
			$this->change = 0;
			$this->total = Cart::getTotal();
			$this->itemsQuantity = Cart::getTotalQuantity();
			$this->emit('sale-ok', 'Venta registrada con éxito');
			$ticket = $this->buildTicket($sale);
			$d = $this->Encrypt($ticket);
			$this->emit('print-ticket', $d);

			//$this->emit('print-ticket', $sale->id);

		} catch (Exception $e) {
			DB::rollback();
			$this->emit('sale-error', $e->getMessage());
		}
	}


	public function printTicket($ventaId)
	{
		return \Redirect::to("print://$ventaId");
	}



	public function buildTicket($sale)
	{
		$details = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
			->join('products as prod', 'prod.id', '=', 'p.products_id')
			->select('sale_details.*', 'prod.name')
			->where('sale_id', $sale->id)
			->get();

		// opcion 1
		/*
																	$products ='';
																	$info = "folio: $sale->id|";
																	$info .= "date: $sale->created_at|";		
																	$info .= "cashier: {$sale->user->name}|";
																	$info .= "total: $sale->total|";
																	$info .= "items: $sale->items|";
																	$info .= "cash: $sale->cash|";
																	$info .= "change: $sale->change|";
																	foreach ($details as $product) {
																		$products .= $product->name .'}';
																		$products .= $product->price .'}';
																		$products .= $product->quantity .'}#';
																	}

																	$info .=$products;
																	return $info;
																	*/

		// opcion 2
		$sale->user_id = $sale->user->id;
		$r = $sale->toJson() . '|' . $details->toJson();
		//$array[] = json_decode($sale, true);
		//$array[] = json_decode($details, true);
		//$result = json_encode($array, JSON_PRETTY_PRINT);

		//dd($r);
		return $r;
	}


	public function printLast()
	{
		$lastSale = Sale::latest()->first();

		if ($lastSale)
			$this->emit('print-last-id', $lastSale->id);
	}

}
