<?php

namespace App\Services;

use Exception;
use QuickBooksOnline\API\Core\Http\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Core\OAuth\OAuth2Client;
use QuickBooksOnline\API\Facades\InventoryAdjustment;
use QuickBooksOnline\API\Facades\Customer as CustomerQB;
use QuickBooksOnline\API\Facades\Item as ItemQB;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\ServiceException;
use App\Models\quickbook_credentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\FacadesLog;
use App\Models\Presentacion;
use QuickBooksOnline\API\Facades\Invoice;
use App\Models\Customer;
use App\Models\SaleDetail;
use App\Models\Discounts;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;


class QuickBooksService
{
    protected $dataService;
    protected static $disabled = true; //¿Está desabilitado Si o No?


    public static function disable($status = true)
    {
        self::$disabled = $status;
    }

    public function __construct()
    {
        if (!self::$disabled) {
            $this->initializeDataService();
        }
    }

    public function initializeDataService()
    {
        $qb_credentials = $this->getOrRefreshAccessToken();
        $this->setDataService($qb_credentials['access_token'], $qb_credentials['refresh_token']);
    }

    protected function setDataService($access_token, $refresh_token)
    {
        if (self::$disabled) {
            $this->dataService = null;
            return;
        }
        Log::debug('QuickBooks config:', config('quickbooks'));
        $config = config('quickbooks');
        $this->dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['redirect_uri'],
            'accessTokenKey' => $access_token,
            'refreshTokenKey' => $refresh_token,
            'QBORealmID' => $config['realm_id'],
            'baseUrl' => $config['base_url'],
        ]);
    }

    private function getOrRefreshAccessToken()
    {
        if (self::$disabled) {
            return ['access_token' => 'disabled', 'refresh_token' => 'disabled'];
        }
        try {
            // Obtener el token de la caché
            $tokens = Cache::get('quickbooks_access_token');
            $expiresAt = Cache::get('quickbooks_access_token_expires_at');

            // Si no existe token en caché, obtener uno nuevo
            if (!$tokens || !$expiresAt) {
                return $this->updateAccessToken();
            }

            // Verificar si el token ha expirado
            if (now()->timestamp >= $expiresAt) {
                return $this->updateAccessToken();
            }

            return $tokens;
        } catch (Exception $e) {
            Log::error("Error al actualizar el token de QuickBooks: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    private function updateAccessToken()
    {
        // Intenta adquirir el lock durante hasta 5 segundos
        return Cache::lock('qbo_token_refresh', 10)->block(5, function () {
            // Aquí dentro sólo entrará una petición a la vez
            $config = config('quickbooks');
            $qbCred = quickbook_credentials::where('status', 1)->firstOrFail();

            $this->setDataService($qbCred->access_token, $qbCred->refresh_token);
            $helper = $this->dataService->getOAuth2LoginHelper();
            $newTokenObj = $helper->refreshAccessTokenWithRefreshToken($qbCred->refresh_token);

            $accessTokenValue  = $newTokenObj->getAccessToken();
            $refreshTokenValue = $newTokenObj->getRefreshToken();

            // Guarda en BD y Cache…
            $qbCred->update([
                'access_token'  => $accessTokenValue,
                'refresh_token' => $refreshTokenValue,
            ]);

            Cache::put('quickbooks_access_token', [
                'access_token'  => $accessTokenValue,
                'refresh_token' => $refreshTokenValue,
            ], 3600);
            Cache::put('quickbooks_access_token_expires_at', now()->timestamp + 3600);

            return [
                'access_token'  => $accessTokenValue,
                'refresh_token' => $refreshTokenValue,
            ];
        });
    }

    /************* CONSULTAS SQL ************/
    public function SQL_Quickboox($table, $where = "")
    {
        if (self::$disabled) return [];
        if ($where) {
            return $this->dataService->Query("SELECT * FROM $table WHERE $where");
        }
        return $this->dataService->Query("SELECT * FROM $table");
    }

    /************* GESTION DE PRODUCTOS E INVENTARIOS ************/
    private function search_product($name)
    {
        if (self::$disabled) return null;
        return $this->dataService->Query("SELECT * FROM Item WHERE Name = '{$name}'");
    }
    public function deleteProduct($productId)
    {
        if (self::$disabled) return true;
        return $this->delete_product($productId);
    }
    protected function getNameProduct($product)
    {
        return ucfirst(strtolower("{$product->product->name} {$product->size->size} {$product->product->estado} ({$product->stock_items} Unidades)"));
    }

    public function create_product($product)
    {
        if (self::$disabled) {
            return true;
        }

        try {
            // 1) Inicializa el DataService (¡imprescindible!)
            $this->initializeDataService();

            // 2) Comprueba si ya existe en QB
            $query  = "SELECT * FROM Item WHERE Sku = '$product->barcode'";
            $result = $this->dataService->Query($query);
            $result = is_array($result) ? $result : [];

            if (count($result) > 0) {
                $item = $result[0];
                $product->QB_id = $item->Id;
                $product->save();
                $this->update_product($product);
                return true;
            }

            // 3) Construye nombre y busca dinámicamente los IDs de cuenta
            $name_product  = $this->getNameProduct($product);
            $incomeAcctId  = $this->findAccountIdByName('Sales of Product Income');
            $assetAcctId  = $this->findAccountIdByName('Inventory Asset-1');
            $cogsAcctId    = $this->findAccountIdByName('Cost of Goods Sold');

            // 4) Valida que los hayas encontrado
            if (! $incomeAcctId || ! $cogsAcctId) {
                Log::error("Faltan cuentas QB para crear el producto", [
                    'incomeAcctId' => $incomeAcctId,
                    'cogsAcctId'   => $cogsAcctId,
                ]);
                return false;
            }

            $qb_product = ItemQB::create([
                "TrackQtyOnHand" => true,
                "Name" => $name_product,
                "QtyOnHand" => $product->stock_box,
                "UnitPrice" => $product->price,
                "Description" => $product->product->descripcion,
                "Sku" => $product->barcode,
                "Active" => $product->product->estado === 'activo',
                "SalesTaxIncluded" => true,
                "IncomeAccountRef" => [
                    "name" => $name_product,
                    "value" => $incomeAcctId
                ],
                "AssetAccountRef" => [
                    "name" => "Inventory Asset",
                    "value" => $assetAcctId
                ],
                "InvStartDate" => $product->created_at,
                "Type" => "Inventory",
                "ExpenseAccountRef" => [
                    "name" => "Cost of Goods Sold",
                    "value" => $cogsAcctId
                ]
            ]);

            $createdProduct = $this->dataService->Add($qb_product);

            if (! $createdProduct) {
                $error = $this->dataService->getLastError();
                Log::error("Error al agregar producto en QB", [
                    'body' => $error?->getResponseBody(),
                    'status' => $error?->getHttpStatusCode(),
                ]);
                return false;
            }

            // 6) Guarda el QB_id en tu BD
            $product->QB_id = $createdProduct->Id;
            $product->save();

            return true;
        } catch (ServiceException $e) {
            Log::error("ServiceException al agregar producto en QB: " . $e->getMessage());
            return false;
        }
    }


    //METODO EN SEGUNDO PLANO
    public function create_product_sync($product)
    {
        if (self::$disabled) return true;
        $qb_product = ItemQB::create([
            "TrackQtyOnHand" => true,
            "Name" => $product['name'],
            "QtyOnHand" => $product['stock_box'],
            "UnitPrice" => $product['price'],
            "Description" => $product['descripcion'],
            "Sku" => $product['barcode'],
            "Active" => $product['estado'] === 'activo',
            "SalesTaxIncluded" => true,
            "IncomeAccountRef" => [
                "name" => $product['name'],
                "value" => "79"
            ],
            "AssetAccountRef" => [
                "name" => "Inventory Asset",
                "value" => "81"
            ],
            "InvStartDate" => $product['created_at'],
            "Type" => "Inventory",
            "ExpenseAccountRef" => [
                "name" => "Cost of Goods Sold",
                "value" => "80"
            ]
        ]);

        try {
            $createdProduct = $this->dataService->Add($qb_product);
            $product = Presentacion::find($product['id']);
            $product->QB_id = $createdProduct->Id;
            $product->save();

            return true;
        } catch (ServiceException $e) {
            //return 'Error al crear producto en QuickBooks';
            return false;
        }
    }

    public function update_product($product)
    { //reviso....
        if (self::$disabled) return true;
        $query = "select * from Item where Sku = '$product->barcode'";
        $result = $this->dataService->Query($query);
        $result = is_array($result) ? $result : [];
        if (count($result) > 0) {
            $item = $result[0];
            $product->QB_id = $item->Id;
            $product->save();

            $qb_product = $item;
            $qb_product->UnitPrice = $product->price;
            $qb_product->Description = $product->product->descripcion;
            $qb_product->Sku = $product->barcode;
            $qb_product->Active = $product->product->estado === 'activo';
            $qb_product->SalesTaxIncluded = true;
            $qb_product->QtyOnHand = $product->stock_box;

            try {
                $product->QB_id = $qb_product->Id;
                $product->save();

                $result = $this->dataService->Update($qb_product);
                //return 'Producto actualizado en QuickBooks';
                return true;
            } catch (ServiceException $e) {
                return false;
                //return 'Error al actualizar producto en QuickBooks';
            }
        }
        //$name_product = $this->getNameProduct($product);
        if ($product->QB_id == null) {
            return true;
        }
        //$qb = $this->search_product($name_product);
        //dd($qb);

    }



    private function delete_product($productID)
    {
        if (self::$disabled) return true;
        try {
            $product = Presentacion::find($productID);
            $name_product = $this->getNameProduct($product);
            $qb = $this->search_product($name_product);
            $qb_product = $qb[0];
            $qb_product->Active = "false";
            $result = $this->dataService->Update($qb_product);
            if ($result) {
                $product->QB_id = null;
                $product->save();

                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }


    /************* GESTION DE CLIENTES ************/


    private function search_client($client)
    {
        if (self::$disabled) return null;
        $query = "SELECT * FROM Customer WHERE Id = '" . $client->QB_id . "'";
        return $this->dataService->Query($query);
    }

    public function create_client($client)
    {
        if (self::$disabled) {
            return true;
        }

        try {
            // 1) Asegura que DataService esté inicializado
            $this->initializeDataService();

            // 2) Intenta encontrar en QB por email
            $email = $client->email;
            $found = $this->dataService->Query(
                "SELECT * FROM Customer WHERE PrimaryEmailAddr.Address = '{$email}'"
            );
            $found = is_array($found) ? $found : [];

            if (count($found) > 0) {
                // --- YA EXISTE: actualizamos sus datos en QB ---
                $qbCustomerObj = $found[0];
                $qbCustomerObj->GivenName        = $client->name;
                $qbCustomerObj->FamilyName       = $client->last_name;
                $qbCustomerObj->FullyQualifiedName = $client->name . ' ' . $client->last_name;
                $qbCustomerObj->PrimaryPhone     = ["FreeFormNumber" => $client->phone];
                $qbCustomerObj->BillAddr         = ["Line1" => $client->address];

                $updated = $this->dataService->Update($qbCustomerObj);
                if ($updated && isset($updated->Id)) {
                    $client->QB_id = $updated->Id;
                    $client->save();
                    return true;
                }
            } else {
                // --- NO EXISTE: creamos uno nuevo ---
                $qb_customer = CustomerQB::create([
                    "GivenName"       => $client->name,
                    "FamilyName"      => $client->last_name,
                    "DisplayName"     => $client->name . ' ' . $client->last_name,
                    "PrimaryEmailAddr" => ["Address" => $client->email],
                    "BillAddr"        => ["Line1" => $client->address],
                    "PrimaryPhone"    => ["FreeFormNumber" => $client->phone],
                ]);

                $created = $this->dataService->Add($qb_customer);
                if ($created && isset($created->Id)) {
                    $client->QB_id = $created->Id;
                    $client->save();
                    return true;
                }
            }

            Log::warning("QuickBooks: No se pudo crear ni actualizar el cliente", [
                'client_id' => $client->id,
                'QB_id'     => $client->QB_id,
            ]);
            return false;
        } catch (ServiceException $e) {
            Log::error('QuickBooks ServiceException: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Error general creando/actualizando cliente en QuickBooks: ' . $e->getMessage());
            return false;
        }
    }

    public function update_client($client)
    {
        if (self::$disabled) return true;
        try {
            $customer = $this->search_client($client);
            $customer = $customer[0];
            $customer->GivenName = $client->name;
            $customer->FamilyName = $client->last_name;
            $customer->DisplayName = $client->name . ' ' . $client->last_name;
            $customer->Mobile = $client->phone;

            $this->dataService->Update($customer);
            $client->QB_id = $customer->Id;
            $client->save();

            return true;
        } catch (ServiceException $e) {

            return false;
        }
    }

    public function delete_cliente($clientId)
    {
        if (self::$disabled) return true;
        try {
            $client = Customer::findOrFail($clientId);
            if (!$client->QB_id) {
                return "Cliente no tiene ID en QuickBooks.";
            }

            $query = "SELECT * FROM Customer WHERE Id = '" . $client->QB_id . "'";
            $customer = $this->dataService->Query($query);

            if (empty($customer)) {
                return "Cliente no encontrado en QuickBooks.";
            }

            $customer = $customer[0];
            $customer->Active = "false";
            $this->dataService->Update($customer);
            $client->QB_id = null;
            $client->save();

            return true;
        } catch (Exception $e) {

            return false;
        }
    }

    public function create_invoice($sale_id)
    {
        if (self::$disabled) {
            return true;
        }

        // 1) Inicializa QuickBooks DataService
        $this->initializeDataService();

        // 2) Recupera la venta y sus detalles
        $sale        = Sale::findOrFail($sale_id);
        $saleDetails = SaleDetail::where('sale_id', $sale->id)->get();
        $cliente     = Customer::findOrFail($sale->CustomerID);

        $lineasFactura = [];
        $totalAmount   = 0;
        $descuento     = 0;

        // 3) Construye las líneas de ítems
        foreach ($saleDetails as $detalle) {
            $presentacion = Presentacion::findOrFail($detalle->presentaciones_id);

            // Si no existe en QB, créalo
            if (! $presentacion->QB_id) {
                $this->create_product($presentacion);
                $presentacion->refresh();
            }

            // Si aun así no tiene QB_id, loguea y salta
            if (! $presentacion->QB_id) {
                Log::error("Factura: producto sin QB_id", [
                    'presentacion_id' => $presentacion->id,
                    'barcode'         => $presentacion->barcode,
                ]);
                continue;
            }

            $qty    = $detalle->quantity;
            $amount = $detalle->price * $qty;

            $lineasFactura[] = [
                'DetailType'          => 'SalesItemLineDetail',
                'Amount'              => $amount,
                'SalesItemLineDetail' => [
                    'ItemRef' => ['value' => (string) $presentacion->QB_id],
                    'Qty'     => $qty,
                ],
            ];

            $totalAmount += $amount;

            // Calcula el descuento de este ítem
            $disc = Discounts::where('customer_id', $sale->CustomerID)
                ->where('presentacion_id', $presentacion->id)
                ->first();
            if ($disc) {
                $lineDiscount = $amount * ($disc->discount / 100);
                $descuento   += $lineDiscount;
            }
        }

        // 4) Si hay descuento, agrega una sola línea de descuento
        if ($descuento > 0) {
            $DiscountsId    = $this->findAccountIdByName('Discounts Given');
            $lineasFactura[] = [
                'DetailType'         => 'DiscountLineDetail',
                'Amount'             => $descuento,
                'DiscountLineDetail' => [
                    'PercentBased'       => false,
                    'DiscountAccountRef' => ['value' => $DiscountsId], // tu cuenta de descuentos
                ],
            ];
            $totalAmount -= $descuento;
        }

        // 5) Asegura que el cliente exista en QB
        if (! $cliente->QB_id) {
            $this->create_client($cliente);
            $cliente->refresh();
        }
        if (! $cliente->QB_id) {
            Log::error("Factura: cliente sin QB_id", ['cliente_id' => $cliente->id]);
            return false;
        }

        // 6) Crea el objeto Invoice para QuickBooks
        $qbInvoice = Invoice::create([
            'TotalAmt'    => $totalAmount,
            'Line'        => $lineasFactura,
            'CustomerRef' => ['value' => (string) $cliente->QB_id],
            'DocNumber'   => (string) $sale_id,
        ]);

        // 7) Lo envía a QuickBooks y maneja errores Discounts Given


        try {
            $factura = $this->dataService->Add($qbInvoice);

            if (! $factura) {
                $err = $this->dataService->getLastError();
                Log::error("QuickBooks: error al crear invoice", [
                    'httpStatus'   => $err?->getHttpStatusCode(),
                    'responseBody' => $err?->getResponseBody(),
                ]);
                return false;
            }

            Log::info("Factura creada en QB, ID: {$factura->Id}");
            $this->adjustments_inventory($saleDetails);
            return true;
        } catch (ServiceException $e) {
            Log::error("QuickBooks ServiceException al crear invoice: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Exception al crear invoice en QB: " . $e->getMessage());
            return false;
        }
    }





    public function adjustments_inventory($saleDetails)
    {
        if (self::$disabled) return true;
        foreach ($saleDetails as $producto) {
            // Buscar la información del producto en tu base de datos
            $pre = Presentacion::find($producto->presentaciones_id);
            if ($pre) {
                $QB_Id = $pre->QB_id; // ID del producto en QuickBooks
                $item = $this->dataService->FindById('Item', $QB_Id);
                $item->QtyOnHand = $pre->stock_box;
                $item->InvStartDate = date('Y-m-d');
                $updatedItem = $this->dataService->Update($item);
            }
        }
    }

    /**
     * Actualiza una factura existente en QuickBooks según los detalles de la venta.
     */
    public function updateInvoice(int $sale_id): bool
    {
        if (self::$disabled) {
            return true;
        }

        $this->initializeDataService();

        // 1) Recupero la factura existente
        $existing = $this->dataService->Query(
            "SELECT * FROM Invoice WHERE DocNumber = '{$sale_id}'"
        );
        if (empty($existing)) {
            Log::error("updateInvoice: no existe factura DocNumber={$sale_id}");
            return false;
        }
        /** @var IPPInvoice $qbInvoice */
        $qbInvoice = $existing[0];

        // 2) Construyo un "template" de IPPInvoice usando el facade,
        //    con todos los productos + posibles descuentos
        $sale        = Sale::findOrFail($sale_id);
        $saleDetails = SaleDetail::where('sale_id', $sale_id)->get();
        $cliente     = Customer::findOrFail($sale->CustomerID);

        // Aseguro QB_id de cliente y productos...
        // (idéntico a antes, omitiendo por brevedad)

        $lineItems = [];
        $totalAmt  = 0;
        $descuento = 0;
        foreach ($saleDetails as $det) {
            $prod   = Presentacion::findOrFail($det->presentaciones_id);
            $qty    = $det->quantity;
            $amt    = $det->price * $qty;
            $totalAmt += $amt;

            $lineItems[] = [
                'DetailType'          => 'SalesItemLineDetail',
                'Amount'              => $amt,
                'SalesItemLineDetail' => [
                    'ItemRef' => ['value' => (string)$prod->QB_id],
                    'Qty'     => $qty,
                ],
            ];

            // Acumulo descuento por ítem si existe...
            $disc = Discounts::where('customer_id', $sale->CustomerID)
                ->where('presentacion_id', $prod->id)
                ->first();
            if ($disc) {
                $d = $amt * ($disc->discount / 100);
                $descuento += $d;
            }
        }

        if ($descuento > 0) {
            $discAcct = $this->findAccountIdByName('Discounts Given');
            $lineItems[] = [
                'DetailType'         => 'DiscountLineDetail',
                'Amount'             => $descuento,
                'DiscountLineDetail' => [
                    'PercentBased'       => false,
                    'DiscountAccountRef' => ['value' => (string)$discAcct],
                ],
            ];
            $totalAmt -= $descuento;
        }

        // 3) Uso el facade para generar un IPPInvoice “limpio”
        $template = Invoice::create([
            'TotalAmt'    => $totalAmt,
            'Line'        => $lineItems,
            'CustomerRef' => ['value' => (string)$cliente->QB_id],
            'DocNumber'   => (string)$sale_id,
        ]);

        // 4) Copio sus líneas y total al objeto real
        $qbInvoice->Line     = $template->Line;      // ahora son objetos IPPLine
        $qbInvoice->TotalAmt = $template->TotalAmt;

        // 5) Lo envío a QuickBooks
        try {
            $updated = $this->dataService->Update($qbInvoice);
            if (! $updated) {
                $err = $this->dataService->getLastError();
                Log::error("updateInvoice: fallo al actualizar", [
                    'status' => $err?->getHttpStatusCode(),
                    'body'   => $err?->getResponseBody(),
                ]);
                return false;
            }
            Log::info("updateInvoice: factura actualizada, ID={$updated->Id}");
            $this->adjustments_inventory($saleDetails);
            return true;
        } catch (\Exception $e) {
            Log::error("updateInvoice: excepción: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Elimina (borra) una factura en QuickBooks por su DocNumber.
     */
    public function deleteInvoice(int $sale_id): bool
    {
        if (self::$disabled) {
            return true;
        }

        // 1) Inicializar
        $this->initializeDataService();

        // 2) Buscar la factura
        $existing = $this->dataService->Query(
            "SELECT * FROM Invoice WHERE DocNumber = '{$sale_id}'"
        );
        if (empty($existing)) {
            Log::warning("deleteInvoice: no se encontró factura DocNumber={$sale_id}");
            return false;
        }
        $qbInvoice = $existing[0];

        // 3) Ejecutar el delete
        try {
            $deleted = $this->dataService->Delete($qbInvoice);
            if (! $deleted) {
                $err = $this->dataService->getLastError();
                Log::error("deleteInvoice: error al eliminar", [
                    'httpStatus'   => $err?->getHttpStatusCode(),
                    'responseBody' => $err?->getResponseBody(),
                ]);
                return false;
            }
            Log::info("deleteInvoice: factura eliminada en QB, ID={$qbInvoice->Id}");
            return true;
        } catch (Exception $e) {
            Log::error("deleteInvoice: excepción al eliminar invoice QB: {$e->getMessage()}");
            return false;
        }
    }


    public function testConnection()
    {
        if (self::$disabled) {
            return (object) [
                'CompanyName' => 'MOCK QuickBooks Company',
                'status' => 'success'
            ];
        }
        try {
            $this->initializeDataService();
            $companyInfo = $this->dataService->getCompanyInfo();
            dd($companyInfo);
            if (!$companyInfo) {
                // Obtener el error detallado
                $error = $this->dataService->getLastError();
                if ($error) {
                    Log::error('QuickBooks Error: ' . $error->getResponseBody());
                    Log::error('QuickBooks Error Details: ' . print_r($error, true));
                }
                Log::error("Error de conexión a QuickBooks: " . json_encode($error));
                return response()->json(['error' => 'Conexión fallida', 'detalle' => $error], 500);
            }
        } catch (Exception $e) {
            Log::error("Error al obtener información de la compañía: " . $e->getMessage());
            return response()->json(['error' => 'Error de conexión al obtener información de la compañía', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function listAllCustomers(): array
    {
        // 1) Asegúrate de que el servicio esté habilitado y configurado
        if (self::$disabled) {
            return [];
        }
        $this->initializeDataService();

        // 2) Hacemos la consulta SQL sobre el recurso Customer
        //    Puedes pedir solo los campos que necesites (Id, DisplayName, PrimaryEmailAddr, etc.)
        $query = "SELECT Id, DisplayName, PrimaryEmailAddr FROM Customer";
        $customers = $this->dataService->Query($query);

        // 3) Si quieres devolver un array simple con id => nombre:
        $result = [];
        if (is_array($customers)) {
            foreach ($customers as $c) {
                $result[] = [
                    'id'   => $c->Id,
                    'name' => $c->DisplayName,
                    'email' => $c->PrimaryEmailAddr->Address ?? null,
                ];
            }
        }

        return $result;
    }

    public function customersList()
    {
        $clientes = $this->listAllCustomers();

        // Empieza a armar el HTML
        $html  = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<title>Clientes QuickBooks</title>';
        // Opcional: un poco de estilo inline
        $html .= '<style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; }
                    th { background: #f4f4f4; }
                  </style>';
        $html .= '</head><body>';
        $html .= '<h3>Lista de Clientes QuickBooks</h3>';

        if (count($clientes)) {
            $html .= '<table>';
            $html .= '<thead><tr><th>ID</th><th>Nombre</th><th>Email</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($clientes as $c) {
                $email = $c['email'] ?? '—';
                $html .= "<tr>
                              <td>{$c['id']}</td>
                              <td>{$c['name']}</td>
                              <td>{$email}</td>
                          </tr>";
            }
            $html .= '</tbody></table>';
        } else {
            $html .= '<p>No se encontraron clientes en QuickBooks.</p>';
        }

        $html .= '</body></html>';

        // Devuelve el HTML puro
        return response($html, 200)
            ->header('Content-Type', 'text/html');
    }

    public function customersWithCrmMatch()
    {

        $qbClients = $this->listAllCustomers();

        $qbByEmail = [];
        foreach ($qbClients as $qb) {
            if (!empty($qb['email'])) {
                $qbByEmail[strtolower($qb['email'])] = $qb;
            }
        }

        // Cabecera HTML + estilos
        $html  = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<title>CRM vs QuickBooks</title>';
        $html .= '<style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; }
                    th { background: #f4f4f4; text-align: left; }
                  </style>';
        $html .= '</head><body>';
        $html .= '<h3>Clientes CRM y su match en QuickBooks</h3>';
        $html .= '<table><thead>
                    <tr>
                      <th>CRM ID</th>
                      <th>CRM Nombre Completo</th>
                      <th>CRM Email</th>
                      <th>QB ID</th>
                      <th>QB Nombre</th>
                      <th>QB Email</th>
                    </tr>
                  </thead><tbody>';

        // Recorre todos los clientes del CRM
        $crmCustomers = Customer::all();
        foreach ($crmCustomers as $crm) {
            // Prepara datos CRM
            $crmId    = $crm->id;
            $crmFull  = trim("{$crm->name} {$crm->last_name} {$crm->last_name2}");
            $crmEmail = $crm->email;

            $matchQB = null;

            // 2) Intentar match por email
            $emailKey = strtolower($crmEmail);
            if (isset($qbByEmail[$emailKey])) {
                $matchQB = $qbByEmail[$emailKey];
            } else {
                // 3) Si no, match por nombre limpiado
                // Limpia el nombre CRM (quita paréntesis y símbolos)
                $noParenCRM = preg_replace('/[()]/', ' ', $crmFull);
                $cleanCRM   = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $noParenCRM);
                $crmCleanL  = strtolower(trim(preg_replace('/\s+/', ' ', $cleanCRM)));

                foreach ($qbClients as $qb) {
                    // Limpia el nombre QB igual que antes
                    $noParenQB = preg_replace('/[()]/', ' ', $qb['name']);
                    $cleanQB   = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $noParenQB);
                    $qbCleanL  = strtolower(trim(preg_replace('/\s+/', ' ', $cleanQB)));

                    // Comprueba si uno contiene al otro
                    if (str_contains($qbCleanL, $crmCleanL) || str_contains($crmCleanL, $qbCleanL)) {
                        $matchQB = $qb;
                        break;
                    }
                }
            }

            // Si hubo match, saca sus datos; si no, guiones
            if ($matchQB) {
                $crm->QB_id = $matchQB['id'];
                $crm->save();

                $qbId    = $matchQB['id'];
                $qbName  = $matchQB['name'];
                $qbEmail = $matchQB['email'] ?? '—';
            } else {
                $qbId    = '—';
                $qbName  = '—';
                $qbEmail = '—';
            }

            // Escapa y concatena la fila
            $html .= '<tr>'
                .  "<td>" . e($crmId) .   "</td>"
                .  "<td>" . e($crmFull) . "</td>"
                .  "<td>" . e($crmEmail) . "</td>"
                .  "<td>" . e($qbId) .    "</td>"
                .  "<td>" . e($qbName) .  "</td>"
                .  "<td>" . e($qbEmail) . "</td>"
                .  '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html');
    }

    public function findAllAccounts(): array
    {
        if (self::$disabled) {
            return [];
        }

        // Asegura que dataService esté listo
        $this->initializeDataService();

        $all   = [];
        $start = 1;
        $max   = 1000;

        do {
            // FindAll automatically usa paginación
            $batch = $this->dataService->FindAll('Account', $start, $max);
            if (!is_array($batch) || count($batch) === 0) {
                break;
            }

            // Añade este lote al total
            $all = array_merge($all, $batch);

            // Avanza la posición
            $start += $max;
        } while (count($batch) === $max);

        return $all;
    }

    public function listAccounts(QuickBooksService $qbService)
    {
        // Trae todas las cuentas
        $accounts = $this->findAllAccounts();

        // Genera la tabla HTML
        $html  = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<title>Chart of Accounts QB</title>';
        $html .= '<style>
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border:1px solid #ddd; padding:8px; }
                    th { background:#f4f4f4; }
                </style>';
        $html .= '</head><body>';
        $html .= '<h3>Chart of Accounts en QuickBooks</h3>';
        $html .= '<table><thead>
                    <tr><th>Id</th><th>Name</th><th>AccountType</th><th>SubType</th></tr>
                </thead><tbody>';

        foreach ($accounts as $acct) {
            $html .= '<tr>'
                .  "<td>{$acct->Id}</td>"
                .  "<td>" . htmlspecialchars($acct->Name, ENT_QUOTES) . "</td>"
                .  "<td>{$acct->AccountType}</td>"
                .  "<td>{$acct->AccountSubType}</td>"
                .  '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Busca en QuickBooks la primera cuenta cuyo Name contenga $needle,
     * recorriendo **todas** las páginas de resultados.
     */
    public function findAccountIdByName(string $needle = 'Sales of Product Income'): ?string
    {   
        if (self::$disabled) {
            return null;
        }
        // Asegúrate de tener dataService listo
        if (! $this->dataService) {
            $this->initializeDataService();
        }

        $start = 1;
        $max   = 1000;

        do {
            // FindAll trae hasta $max cuentas, empezando en $start
            $batch = $this->dataService->FindAll('Account', $start, $max);
            $batch = is_array($batch) ? $batch : [];

            foreach ($batch as $acct) {
                if (stripos($acct->Name, $needle) !== false) {
                    return $acct->Id;
                }
            }

            // Avanza el puntero
            $start += $max;
        } while (count($batch) === $max);

        return null;
    }
}
