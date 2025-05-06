<?php

namespace App\Http\Livewire;

use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\UpdateData;
use Automattic\WooCommerce\Client;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Presentacion;
use App\Models\Inspectors;
use App\Models\SaleDetail;
use App\Models\Discounts;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Traits\CartTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Exception\ServiceException;
use QuickBooksOnline\API\Facades\Customer as CustomerQB;
use App\Services\QuickBooksService;
use App\Services\WoocomerceService;
use App\Jobs\SyncClientJob;
use App\Models\quickbook_credentials;
use App\Contracts\ServicePayServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class ClientesController extends Component
{

    use WithFileUploads;
    use WithPagination;
    public $selected_id, $search;
    private $pagination = 5;
    public $buscar = '';
    public $customerId;
    public $email, $pageTitle, $componentName, $sumDetails, $countDetails, $reportType, $userId, $saleId;
    public $name, $last_name, $last_name2, $phone, $address, $document, $password, $saldo, $image, $ventas, $user,
    $img = 'https://kdlatinfood.com/intranet/public/storage/customers/6478295465836_.jpg', $input_presentacion_id, $input_discount, $input_user_id;

    public $state = '';
    public $city = '';
    public $isBtnEnabled = false;

    public $lat;
    public $lon;
    public $locationFound = false;

    public $catalogoServicePay = [];

    protected $quickBooksService;
    protected $woocomerceService;
    protected $servicePay;

    public $serviceStates = []; // Para controlar el toggle
    public $serviceAmounts = []; // Para controlar los montos



    public $states = [
        "FL" => "Florida",
        "NY" => "New York",
        "CA" => "California",
        "TX" => "Texas",
        "IL" => "Illinois",
        "GA" => "Georgia",
        "NC" => "North Carolina",
    ];


    public $citiesByState = [
        "FL" => [
            "Jacksonville",
            "Miami Beach",
            "Tampa",
            "Orlando",
            "St. Petersburg",
            "Hialeah",
            "Port St. Lucie",
            "Tallahassee",
            "Cape Coral",
            "Fort Lauderdale",
            "Pembroke Pines",
            "Hollywood",
            "Miramar",
            "Gainesville",
            "Coral Springs",
            "Clearwater",
            "Pompano Beach",
            "Lakeland",
            "Boca Raton",
            "Delray Beach",
            "Doral",
            "Kendall, Sunrise",
            "Weston",
            "Aventura",
            "Homestead"
        ],
        "NY" => [
            "New York City",
            "Buffalo",
            "Yonkers",
            "Rochester",
            "Syracuse",
            "Albany",
            "New Rochelle",
            "Mount Vernon",
            "Schenectady",
            "Utica",
            "White Plains",
            "Hempstead",
            "Niagara Falls",
            "Binghamton",
            "Freeport",
            "Valley Stream",
            "Long Beach",
            "Rome",
            "Ithaca",
            "Poughkeepsie"
        ],
        "CA" => [
            "Los Angeles",
            "San Diego",
            "San Jose",
            "San Francisco",
            "Fresno",
            "Sacramento",
            "Long Beach",
            "Oakland",
            "Bakersfield",
            "Anaheim",
            "Santa Ana",
            "Riverside",
            "Stockton",
            "Irvine",
            "Chula Vista",
            "Fremont",
            "Modesto",
            "San Bernardino",
            "Oxnard",
            "Fontana"
        ],
        "TX" => [
            "Houston",
            "San Antonio",
            "Dallas",
            "Austin",
            "Fort Worth",
            "El Paso",
            "Arlington",
            "Corpus Christi",
            "Plano",
            "Laredo",
            "Lubbock",
            "Irving",
            "Garland",
            "McKinney",
            "Amarillo",
            "Grand Prairie",
            "Brownsville",
            "Killeen",
            "Pasadena",
            "Mesquite"
        ],
        "IL" => [
            "Chicago",
            "Aurora",
            "Naperville",
            "Joliet",
            "Rockford",
            "Springfield",
            "Elgin",
            "Peoria",
            "Waukegan",
            "Cicero",
            "Champaign",
            "Bloomington",
            "Arlington Heights",
            "Evanston",
            "Schaumburg",
            "Bolingbrook",
            "Palatine",
            "Skokie",
            "Des Plaines",
            "Orland Park"
        ],
        "GA" => [
            "Atlanta",
            "Augusta",
            "Columbus",
            "Macon",
            "Savannah",
            "Athens",
            "Sandy Springs",
            "Roswell",
            "Johns Creek",
            "Albany",
            "Warner Robins",
            "Alpharetta",
            "Marietta",
            "Valdosta",
            "Smyrna",
            "Brookhaven",
            "Dunwoody",
            "Peachtree Corners",
            "Gainesville",
            "Newnan",
            "Milton",
            "Rome",
            "East Point",
            "Hinesville",
            "Dalton",
            "Lawrenceville"
        ],
        "NC" => [
            "Charlotte",
            "Raleigh",
            "Greensboro",
            "Durham",
            "Winston-Salem",
            "Fayetteville",
            "Cary",
            "Wilmington",
            "High Point",
            "Asheville",
            "Concord",
            "Gastonia",
            "Jacksonville",
            "Chapel Hill",
            "Rocky Mount",
            "Burlington",
            "Wilson",
            "Huntersville",
            "Kannapolis",
            "Apex",
            "Hickory",
            "Greenville",
            "Goldsboro",
            "New Bern",
            "Monroe",
            "Mint Hill",
            "Matthews",
            "Cornelius",
            "Salisbury",
            "Holly Springs",
        ],
    ];

    public $cities = [];

    public function mount()
    {
        $this->pageTitle = 'Listado';
        $this->componentName = 'Clientes';
        $this->details = [];
        $this->discounts = [];
        $this->presentaciones = [];
        $this->sumDetails = 0;
        $this->countDetails = 0;
        $this->reportType = 0;
        $this->userId = 0;
        $this->saleId = 0;
        $this->ventas;
        $this->user;
    }

    public function boot(QuickBooksService $quickBooksService, WoocomerceService $woocomerceService, ServicePayServiceInterface $servicePay)
    {
        $this->quickBooksService = $quickBooksService;
        $this->woocomerceService = $woocomerceService;
        $this->servicePay = $servicePay;
    }

    public function paginationView()
    {
        return 'vendor.livewire.bootstrap';
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function info($id)
    {
        $data = Customer::find($id);

        $this->emit('modal-show', 'details loaded');
    }

    public function putSales($id)
    {
        $this->ventas = Sale::where('CustomerID', $id)->get();
        $this->user = Customer::with('sale')->where('id', $id)->first();
        $this->emit('producto-creado');
        $this->emit('mostrar-venta');

    }

    public function discountsUser($id)
    {
        $this->input_user_id = $id;
        $this->discounts = Discounts::with('presentacion')->where('customer_id', $id)->orderBy('id', 'desc')->get();
        //$this->presentaciones = Presentacion::all();

        $this->presentaciones = Presentacion::whereNotIn('id', function ($query) use ($id) {
            $query->select('presentacion_id')
                ->from('discounts')
                ->where('customer_id', $id); // Filtra por el usuario
        })->get();

        $this->user = Customer::find($id);
        $this->emit('producto-creado');
        $this->emit('discount-show');
    }

    public function saveDiscount()
    {
        if ($this->input_presentacion_id != "" && $this->input_presentacion_id != 0 && $this->input_discount != "") {
            Discounts::create([
                "customer_id" => $this->input_user_id,
                "presentacion_id" => $this->input_presentacion_id,
                "discount" => $this->input_discount
            ]);

            $this->input_presentacion_id = 0;
            $this->input_discount = '';
        }
        //$this->discounts = Discounts::with('presentacion')->where('customer_id', $this->input_user_id)->orderBy('id', 'desc')->get();
        $this->emit('producto-creado');

    }

    public function deleteDiscount($id)
    {
        Discounts::destroy($id);
        $this->discounts = Discounts::with('presentacion')->where('customer_id', $this->input_user_id)->orderBy('id', 'desc')->get();
        $this->emit('producto-creado');

    }

    public function updateAddresInputs($direccion)
    {
        $this->address = $direccion;
        //Aqui actualizo la direccion.
    }

    public function cleanAddres()
    {

        $this->address = "";
        $this->locationFound = false;
        $this->isBtnEnabled = false;
    }

    public function updatedState($value)
    {
        $this->cities = $this->citiesByState[$value] ?? [];
        $this->city = ''; // Resetear la selección de ciudad
    }


    public function render()
    {
        $this->catalogoServicePay = $this->servicePay->listCatalog();
        $horaInicio = Carbon::now();
        if ($this->input_user_id != "") {

            $this->discounts = Discounts::with('presentacion')->where('customer_id', $this->input_user_id)->orderBy('id', 'desc')->get();
            $id = $this->input_user_id;
            $this->presentaciones = Presentacion::whereNotIn('id', function ($query) use ($id) {
                $query->select('presentacion_id')
                    ->from('discounts')
                    ->where('customer_id', $id); // Filtra por el usuario
            })->get();
        }
        //creando un cliente
        //$id = $this->woocomerceService->createClient("sebastyampi@gmail.com","Sebastyan","Pineda","12345678a");
        $data2 = Sale::with('client')->get();
        $data3 = SaleDetail::with('sales')->get();
        $data = Customer::with('sale')
            ->when($this->search, function ($query) {
                $query->where('name', $this->search);
            })
            ->get();

        $horaFin = Carbon::now();

        return view('livewire.clientes.clientes', [
            'data' => $data,
            'data2' => $data2,
            'data3' => $data3,
            'horaInicio' => $horaInicio,
            'horaFin' => $horaFin
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }
    /*public function createOrUpdateProductInQuickBooksAndUpdate($clientId)
    {
        // Llamar a la función para crear o actualizar el producto en QuickBooks
        $this->Client_QB($clientId);
    
        // Luego, llamar de nuevo para asegurarse de que se actualice después de la creación
        $this->Client_QB($clientId);
        $this->emit('producto-creado');
    }*/

    public function validateAddress()
    {
        if (empty($this->address)) {
            return;
        }

        $this->locationFound = false;
        $this->isBtnEnabled = false;

        // Generar cache key único para esta búsqueda
        $cacheKey = 'geo_google_' . md5($this->address);

        // Intentar rescatar del cache (por 12h)
        $result = Cache::remember($cacheKey, now()->addHours(12), function () {
            $response = Http::timeout(3)
                ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $this->address,
                    'key' => config('services.google_maps.key'),
                ]);

            if (!$response->ok()) {
                Log::warning("Geocoding error HTTP: {$response->status()}");
                return null;
            }

            $json = $response->json();
            return $json['status'] === 'OK' && !empty($json['results'])
                ? $json['results'][0]
                : null;
        });

        // Si hay resultado válido
        if ($result) {
            // Extraer lat/lng
            $loc = $result['geometry']['location'];
            $this->lat = $loc['lat'];
            $this->lon = $loc['lng'];

            // Confirmar que es USA
            $country = collect($result['address_components'])
                ->firstWhere('types', ['country', 'political']);

            if ($country && $country['short_name'] === 'US') {
                $this->locationFound = true;
                $this->isBtnEnabled = true;
                return;
            }
        }

        // Si aún no encontró, marca como inválida
        $this->locationFound = false;
        $this->isBtnEnabled = false;
    }

    /*public function validateAddress()
    {
        if (empty($this->address)) {
            return;
        }

        $this->locationFound = false;
        $this->isBtnEnabled = false;

        $opts = [
            "http" => [
                "header" => "User-Agent: MyAppName/1.0 (contacto@tuemail.com)\r\n"
            ]
        ];
        $context = stream_context_create($opts);

        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($this->address) . "&format=json&limit=1";

        try {
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);

            if (!empty($data)) {
                $country = $data[0]['display_name'] ?? '';

                if (strpos($country, "United States") !== false) {
                    $this->lat = $data[0]['lat'];
                    $this->lon = $data[0]['lon'];
                    $this->locationFound = true;
                    $this->isBtnEnabled = true;
                } else {
                    $this->locationFound = false;
                    $this->isBtnEnabled = false;
                }
            } else {
                $this->locationFound = false;
                $this->isBtnEnabled = false;
            }

        } catch (\Exception $e) {
            $this->locationFound = false;
            $this->isBtnEnabled = false;
        }
    }*/
    public function loaderQuickbooks($clientId)
    {
        $client = Customer::find($clientId);
        if ($client->QB_id) {
            $this->quickBooksService->update_client($client);
        } else {
            $this->quickBooksService->create_client($client);
        }
        $this->emit('producto-creado');
        $this->emit('global-msg', 'Cliente Actualizado en QB');

    }

    /*public function Client_QB($clientId)
    {
        $message = $this->quickBooksService->Client_QB($clientId);
        $this->emit('global-msg', $message);
        try {
            $config = config('quickbooks');
            $this->emit('global-msg', 'Actualizando token');
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

            // Obtener el cliente de la base de datos local
            $client = Customer::find($clientId);

            // Consultar QuickBooks con el nombre del cliente
            $query = "SELECT * FROM Customer WHERE DisplayName = '" . $client->name . " " . $client->last_name . "'";
            $customer = $dataService->Query($query);

            if (isset($customer) && !empty($customer) && count($customer) > 0) {
              //  dd($customer);
               // dd($client);
                $customer = $customer[0];
                $client->QB_id = $customer->Id;
                $client->save();
                $customer->Id = $client->id;
                $customer->GivenName = $client->name;
                $customer->FamilyName = $client->name;
                $customer->DisplayName = $client->name . ' ' . $client->last_name;
                $customer->Mobile = $client->phone;
                // $customer->PrimaryPhone->FreeFormNumber = $client->phone;
                //  $customer->PrimaryEmailAddr->Address =  $client->phone;
                    //dd($customer->Id);
               
                try {
                    //code...
                    $result = $dataService->Update($customer);
                    $this->emit('global-msg', 'Cliente Actualizado en QB');
                } catch (ServiceException $th) {
                    $this->emit('global-msg', 'Ocurrio un Error');
                   // dd($th);
                }
            } else {
                $cliente = Customer::findOrFail($clientId);
               // dd($cliente);
                // Crear un nuevo cliente en QuickBooks
                $qb_customer = CustomerQB::create([
                    "GivenName" => $cliente->name,
                    "DisplayName" => $cliente->name . ' ' . $cliente->last_name,
                    "PrimaryEmailAddr" => [
                        "Address" => $cliente->email
                    ],
                    "BillAddr" => [
                        "Line1" => $cliente->address,
                        // Agrega otros campos de dirección según sea necesario
                    ],
                    "PrimaryPhone" => [
                        "FreeFormNumber" => $cliente->phone
                    ]
                ]);
                try {
                    $result = $dataService->Add($qb_customer);
                   // $client->QB_id = $qb_customer->Id;
                   // $client->save(); 
                   //  dd($qb_customer);
                    $this->emit('global-msg', 'Cliente Crerado en QB');
                } catch (ServiceException $th) {
                    $this->emit('global-msg', 'Ocurrio un Error');
                   // dd($th);
                }


              
            }


            // Guardar el ID de QuickBooks en la base de datos local

        } catch (\Exception $e) {
            // Manejar la excepción
            dd($e->getMessage());
        }
    }
    /*public function Search_Client_QB($clientId)
    {
        try {
            $config = config('quickbooks');
            $this->emit('global-msg', 'Actualizando token');
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

            // Obtener el cliente de la base de datos local
            $client = Customer::findOrFail($clientId);

            // Consultar QuickBooks con el nombre del cliente
            $query = "SELECT * FROM Customer WHERE DisplayName = '" . $client->name . " " . $client->last_name . "'";
            $customer = $dataService->Query($query);

            dd($customer);
        } catch (\Exception $e) {
            // Manejar la excepción
            dd($e->getMessage());
        }
    }

    public function update_access_token()
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

    public function Store()
    {
        try {
            $user = Auth()->user()->name;
            $rules = [
                'name' => 'required|min:3',
                'last_name' => 'required|min:3',
                'email' => 'required|unique:customers|min:10',
                'password' => 'required|min:1',
                'address' => 'required|min:8',
                'phone' => 'required|min:10',
                'saldo' => 'required|integer|min:0'
            ];

            $messages = [
                'name.required' => 'Nombre del Cliente requerido',
                'name.min' => 'El nombre del Cliente debe tener al menos 3 caracteres',
                'last_name.required' => 'Apellido del Cliente requerido',
                'last_name.min' => 'El Apellido del Cliente debe tener al menos 3 caracteres',
                'email.required' => 'Email del Cliente requerido',
                'email.unique' => 'Ya existe este email asociado a una cuenta',
                'email.min' => 'El email del cliente debe tener al menos 10 caracteres',
                'password.required' => 'Contraseña del Cliente requerido',
                'password.min' => 'El Contraseña del Cliente debe tener al menos 8 caracteres',
                'address.required' => 'address del Cliente requerido',
                'address.min' => 'El address del Cliente debe tener al menos 8 caracteres',
                'phone.required' => 'phone del Cliente requerido',
                'phone.min' => 'El phone del Cliente debe tener al menos 10 caracteres',
                'saldo.required' => 'saldo del Cliente requerido',
                'saldo.integer' => 'El saldo debe ser un número entero'
            ];

            $this->validate($rules, $messages);
            if (Customer::where('email', $this->email)->exists()) {
                $this->emit('global-msg', 'El email ya existe en la base de datos local.');
                $this->emit('cliente-added', 'Cliente Agregado');
                $this->emit('producto-creado');
                return;
            }
            $client = Customer::create([
                'name' => $this->name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
                'address' => $this->address,
                'phone' => $this->phone,
                'saldo' => $this->saldo,
                //'firebase'=>'no',
            ]);


            if ($client) {
                //$this->AddClientsRunningInBackground($client->id);
                $this->quickBooksService->create_client($client);
            }

            if ($this->image) {      //  $this->image && $this->image instanceof UploadedFile       
                $customFileName = uniqid() . '_.' . $this->image->extension();
                $this->image->storeAs('public/customers', $customFileName);
                $client->image = $customFileName;
                $client->firebase = 'no';
            } else {
                // Si no se carga una imagen, asigna una imagen por defecto
                $client->image = 'noimg.jpg';
            }

            $client->save();

            try {

                Mail::to($this->email)->send(
                    new WelcomeEmail(
                        $this->name,
                        $this->last_name,
                        $this->last_name2,
                        $this->phone,
                        $this->address,
                        $this->document,
                        $this->password,
                        $this->saldo,
                        $this->email
                    )
                );

            } catch (Exception $e) {
                \Log::error('Error al enviar correo : ' . $e->getMessage());
            }
            /*$woocommerce = new Client(
                'https://kdlatinfood.com',
                'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
                'cs_723eab16e53f3607fd38984b00f763310cc4f473',
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                    'timeout' => 30,
                ]
            );
            $existingCustomer = $woocommerce->get('customers', ['email' => $this->email]);
            if (!empty($existingCustomer)) {                
                throw new \Exception('El cliente ya existe en WooCommerce.');
            } 
            try {
                $response = $woocommerce->post('customers', [
                    'email' => $this->email,
                    'first_name' => $this->name,
                    'last_name' => $this->last_name,
                    'password' => $this->password,
                    'username' => $this->email,
                    'avatar_url' => $this->img
        
                ]);
            } catch (\Automattic\WooCommerce\HttpClient\HttpClientException $e) {                                
                $this->emit('global-msg', 'Error al crear el cliente en WooCommerce: ' . $e->getMessage());
                return;
            }  

            // Verificar si la solicitud fue exitosa
            if ($response) {
                $createdCustomerId = $response->id;
                $client->woocommerce_cliente_id = $createdCustomerId;
                $client->save();
                \Log::info('User ID: ' . auth()->id());
                \Log::info('CSRF Token: ' . csrf_token());
    
            }*/
            //inspectors
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Creo al cliente: ' . $this->name,
                'seccion' => 'Customers'
            ]);


            $Catalogos = $this->servicePay->listCatalog();

            foreach ($Catalogos as $catalogo) {
                $this->servicePay->addServicePay($client->id, $catalogo->id, $this->serviceAmounts[$catalogo->id] ?? 0, $this->serviceStates[$catalogo->id] ?? 0);
            }


            $this->resetUI();
            $this->emit('global-msg', 'Cliente Agregado');
            $this->emit('cliente-added', 'Cliente Agregado');
            $this->emit('producto-creado');
        } catch (\Exception $e) {
            $errorString = '';
            if ($e instanceof ValidationException) {
                $errors = $e->errors();

                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }

                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            } else {
                $errorString = $e->getMessage();
            }

            $this->emit('global-msg', $errorString);
            $this->emit('producto-creado');
        }

    }

    private function AddClientsRunningInBackground($id)
    {
        $client = Customer::find($id);
        $clientData = [
            'id' => $client->id,
            'name' => $client->name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'address' => $client->address,
            'phone' => $client->phone
        ];

        dispatch(new SyncClientJob($clientData));
        //\Artisan::call('queue:work --once');
    }
    public function getDetails($saleId)
    {
        $this->details = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
            ->join('products as prod', 'prod.id', '=', 'p.products_id')
            ->select(
                'sale_details.id',
                'sale_details.price',
                'sale_details.quantity',
                'prod.name as product',
                'p.barcode'
            )
            ->where('sale_details.sale_id', $saleId)
            ->get();

        $suma = $this->details->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->sumDetails = $suma;
        $this->countDetails = $this->details->sum('quantity');
        $this->saleId = $saleId;

        $this->emit('show-modal', 'details loaded');
    }
    public function Edit($id)
    {
        $this->isBtnEnabled = false;
        $this->locationFound = false;

        $record = Customer::find($id);
        $this->name = $record->name;
        $this->last_name = $record->last_name;
        $this->last_name2 = $record->last_name2;
        $this->email = $record->email;
        $this->password = '';

        $this->address = $record->address;


        $this->selected_id = $record->id;
        $this->phone = $record->phone;
        $this->saldo = $record->saldo;
        $this->image = null;

        // Check if the woocommerce_cliente_id is not null before making the API request
        if ($record->woocommerce_cliente_id !== null) {
            // Obtener el ID del cliente en WooCommerce utilizando el email
            $woocommerceClient = $this->getWooCommerceClient()->get('customers', ['email' => $record->email]);
            if (!empty($woocommerceClient)) {
                $this->customerId = $woocommerceClient[0]['id'];
                $this->emit('global-msg', 'El cliente esta en woocomerce');
            } else {
                // No se encontró un cliente en WooCommerce, puedes manejarlo como desees
                $this->emit('global-msg', 'El cliente esta NO en woocomerce');
            }
        } else {
            // The woocommerce_cliente_id is null, so skip the WooCommerce API request
            // You might want to set a default value for $this->customerId in this case.
            $this->customerId = null;
        }
        $this->validateAddress();
        //logica de servicios
        $Catalogos = $this->servicePay->listCatalog();
        $CatalogosAsignados = $this->servicePay->getServicePayByCustomerId($this->selected_id);
        if ($CatalogosAsignados->isEmpty()) { //Esta Vacia
            //Crear todos los catalagos para ese usuario
            $CatalogosAsignados = collect();
            foreach ($Catalogos as $catalogo) {
                $nuevo = $this->servicePay->addServicePay($this->selected_id, $catalogo->id, 0, false);
                $CatalogosAsignados->push($nuevo); // ← agregas el nuevo objeto
            }

        }
        foreach ($Catalogos as $catalogo) {
            $this->catalogoServicePay[] = $catalogo;
            $service = $CatalogosAsignados->firstWhere('catalogo_service_id', $catalogo->id);

            if ($service) {
                $this->serviceStates[$catalogo->id] = (bool) $service->state;
                $this->serviceAmounts[$catalogo->id] = $service->amount;
            } else {
                $this->serviceStates[$catalogo->id] = false;
                $this->serviceAmounts[$catalogo->id] = 0;
            }
        }


        $this->emit('refreshComponent');
        $this->emit('modal-show', 'show Modal');
        $this->emit('producto-creado');
    }

    public function checkDeliveryFree($servicePayId)
    {
        $service = $this->catalogoServicePay->firstWhere('id', $servicePayId);

        if ($service && $service->id === $servicePayId) {
            $isActive = $this->serviceStates[$servicePayId] ?? false;

            if ($isActive) {
                // Activado => poner en 0 y deshabilitar input
                //$this->serviceAmounts[$servicePayId] = 0;
            } else {
                // Desactivado => permitir monto manual (ej. $100)
                if (!isset($this->serviceAmounts[$servicePayId]) || $this->serviceAmounts[$servicePayId] == 0) {
                    $this->serviceAmounts[$servicePayId] = null; // o puedes poner 100 como predeterminado
                }
            }
        }
    }



    public function Update()
    {
        try {
            $rules = [
                'name' => 'required|min:3',
                'last_name' => 'required|min:3',
                'email' => 'required|min:10',
                'address' => 'required|min:8',
                'phone' => 'required|min:10',
                'saldo' => 'required|integer|min:0'
            ];

            $messages = [
                'name.required' => 'Nombre del Cliente requerido',
                'name.min' => 'El nombre del Cliente debe tener al menos 3 caracteres',
                'last_name.required' => 'Apellido del Cliente requerido',
                'last_name.min' => 'El Apellido del Cliente debe tener al menos 3 caracteres',
                'email.required' => 'Email del Cliente requerido',
                'email.min' => 'El email del cliente debe tener al menos 10 caracteres',
                'address.required' => 'address del Cliente requerido',
                'address.min' => 'El address del Cliente debe tener al menos 8 caracteres',
                'phone.required' => 'phone del Cliente requerido',
                'phone.min' => 'El phone del Cliente debe tener al menos 10 caracteres',
                'saldo.required' => 'saldo del Cliente requerido',
                'saldo.integer' => 'El saldo debe ser un número entero'
            ];

            $this->validate($rules, $messages);

            $user = Customer::where('email', $this->email)->where('id', "!=", $this->selected_id)->count();
            if ($user > 0) {
                $this->emit('producto-creado');
                $this->emit('global-msg', 'Ya existe una cuenta asociada a ese correo.');
                return;
            }

            $this->emit('producto-creado');
            $clientee = Customer::find($this->selected_id);
            $DataCliente = [
                'name' => $this->name,
                'last_name' => $this->last_name,
                'last_name2' => $this->last_name2,
                'email' => $this->email,
                'address' => $this->address,
                'phone' => $this->phone,
                'saldo' => $this->saldo,
            ];
            if ($this->password != "") {
                $DataCliente['password'] = bcrypt($this->password);
            }
            $clientee->update($DataCliente);
            if ($this->image) {
                $customFileName = uniqid() . '_.' . $this->image->extension();
                $this->image->storeAs('public/customers', $customFileName);
                $clientee->image = $customFileName;
                $clientee->firebase = 'no';
                $clientee->save();
            }
            if ($clientee->QB_id) {
                $this->quickBooksService->update_client($clientee);
            } else {
                $this->quickBooksService->create_client($clientee);
            }


            // Actualizar los datos del cliente en WooCommerce
            if ($this->customerId) {
                $woocommerce = $this->getWooCommerceClient();
                $woocommerce->put("customers/{$this->customerId}", [
                    'email' => $this->email,
                    'first_name' => $this->name,
                    'last_name' => $this->last_name,
                    'password' => $this->password,
                    'username' => $this->email
                ]);
            }



            try {
                Mail::to($this->email)->send(
                    new UpdateData(
                        $this->name,
                        $this->last_name,
                        $this->last_name2,
                        $this->phone,
                        $this->address,
                        $this->document,
                        $this->password,
                        $this->saldo,
                        $this->email
                    )
                );
            } catch (Exception $e) {
                \Log::error('Error al enviar correo : ' . $e->getMessage());
            }

            $Catalogos = $this->servicePay->listCatalog();

            foreach ($Catalogos as $catalogo) {
                $service = $this->servicePay->getServicePayByCustomerByCatalogId($this->selected_id, $catalogo->id);
                if ($service) {
                    $this->servicePay->updateServicePay($service->id, $this->serviceAmounts[$catalogo->id] ?? 0, $this->serviceStates[$catalogo->id] ?? 0);
                } else {
                    $this->servicePay->addServicePay($this->selected_id, $catalogo->id, $this->serviceAmounts[$catalogo->id] ?? 0, $this->serviceStates[$catalogo->id] ?? 0);
                }
            }


            //$this->createOrUpdateProductInQuickBooksAndUpdate($clientee->id);
            $this->resetUI();
            $this->emit('global-msg', 'Cliente Editado');
            $this->emit('cliente-edit', 'Costumer Updated');
        } catch (Exception $e) {

            $errorString = '';
            if ($e instanceof ValidationException) {
                $errors = $e->errors();
                // Convertir los errores a una cadena                
                foreach ($errors as $fieldErrors) {
                    foreach ($fieldErrors as $error) {
                        $errorString .= $error . ' ';
                    }
                }
                // Eliminar el último espacio extra
                $errorString = rtrim($errorString);
            } else {
                $errorString = $e->getMessage();
            }

            $this->emit('global-msg', $errorString);
            $this->emit('producto-creado');
        }

    }

    private function getWooCommerceClient()
    {
        return new Client(
            'https://kdlatinfood.com', // URL de tu tienda WooCommerce
            'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
            'cs_723eab16e53f3607fd38984b00f763310cc4f473', // Secreto del consumidor de tu tienda WooCommerce
            [
                'wp_api' => true, // Habilitar la API de WordPress
                'version' => 'wc/v3', // Versión de la API de WooCommerce
            ]
        );
    }

    public function resetUI()
    {
        $this->name = '';
        $this->last_name = '';
        $this->last_name2 = '';
        $this->email = '';
        $this->password = '';
        $this->address = '';
        $this->phone = '';
        $this->saldo = '';
        $this->search = '';
        $this->selected_id = 0;
        $this->image = null;
        $this->isBtnEnabled = false;
        $this->locationFound = false;
    }
    protected $listeners = [
        'deleteRow' => 'Destroy',
        'deleteDiscount' => 'deleteDiscount',
        'Quickboks' => 'loaderQuickbooks',
        'updateAddresInputs' => 'updateAddresInputs'
    ];
    public function Destroy(Customer $cliente)
    {
        if ($cliente->Sale()->exists()) {
            // El cliente tiene ventas asociadas, emitir evento para mostrar SweetAlert de error
            $this->emit('cliente-has-sales', 'El cliente tiene ventas asociadas y no puede ser eliminado.');
            return;
        }
        // Verificar si el cliente tiene un ID de WooCommerce asociado
        if ($cliente->woocommerce_cliente_id) {
            // Crear una instancia del cliente WooCommerce
            $woocommerce = new Client(
                'https://kdlatinfood.com', // URL de tu tienda WooCommerce
                'ck_8e38a879e7f6ce0d56e34c525de194a60c2e2ce5',
                'cs_723eab16e53f3607fd38984b00f763310cc4f473',
                [
                    'wp_api' => true,
                    'version' => 'wc/v3',
                ]
            );

            try {
                // Eliminar el cliente de WooCommerce
                $woocommerce->delete("customers/{$cliente->woocommerce_cliente_id}", ['force' => true]);
                $this->emit('cliente-delete', 'Cliente Eliminado de WC');
            } catch (\Exception $e) {
                // Manejar la excepción
                Log::error("Error al eliminar cliente de WooCommerce: {$e->getMessage()}");
                dd($e);
                // Puedes emitir un mensaje de error o realizar otras acciones según sea necesario
            }
        }

        $imageTemp = $cliente->image;

        $response = $this->quickBooksService->delete_cliente($cliente->id);
        /*if ($response || !$cliente->QB_id) {
            $cliente->delete();
        }*/
        $cliente->delete();


        if ($imageTemp != null) {
            if (file_exists('storage/customers/' . $imageTemp)) {
                unlink('storage/customers/' . $imageTemp);
            }
        }
        $this->emit('producto-creado');
        $this->resetUI();
        $this->emit('global-msg', 'Cliente Eliminado');
        $this->emit('cliente-delete', 'Cliente Eliminado');
    }


    public function handleWebhook(Request $request)
    {
        // Verificar si la solicitud del webhook contiene datos de cliente
        if ($request->has('customer')) {
            $customer = $request->input('customer');

            // Obtener detalles del cliente
            $customerId = $customer['id'];
            $customerName = $customer['first_name'] . ' ' . $customer['last_name'];
            $customerEmail = $customer['email'];

            // Verificar si el cliente ya existe en tu CRM
            $existingCustomer = Customer::where('id', $customerId)->first();

            if ($existingCustomer) {
                // El cliente ya existe, realizar la actualización en tu CRM
                $existingCustomer->name = $customerName;
                $existingCustomer->email = $customerEmail;
                // Actualizar los demás campos necesarios
                $existingCustomer->save();

                \Log::info("Cliente actualizado en el CRM: ID: $customerId, Nombre: $customerName, Email: $customerEmail");
            } else {
                // El cliente no existe, crearlo en tu CRM
                $newCustomer = new Customer();
                $newCustomer->name = $customerName;
                $newCustomer->email = $customerEmail;
                // Establecer otros campos necesarios para el cliente
                $newCustomer->id = $customerId;
                // Guardar el nuevo cliente en tu CRM
                $newCustomer->save();

                \Log::info("Cliente creado en el CRM: ID: $customerId, Nombre: $customerName, Email: $customerEmail");
            }

            // Retornar una respuesta exitosa
            return response()->json(['status' => 'success']);
        }

        // Si no hay datos de cliente en la solicitud del webhook, retornar una respuesta de error
        return response()->json(['status' => 'error', 'message' => 'No se encontraron datos de cliente']);
    }


    public function createApi(Request $request)
    {
        try {
            $customer = new Customer();
            $customer->name = $request->input('name');
            $customer->last_name = $request->input('last_name');
            $customer->last_name2 = $request->input('last_name2');
            $customer->email = $request->input('email');
            $customer->password = Hash::make($request->input('password'));
            $customer->address = $request->input('address');
            $customer->phone = $request->input('phone');
            $customer->saldo = $request->input('saldo');
            // Otros campos del cliente
            $customer->save();

            return response()->json(['message' => 'Cliente creado con éxito'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el cliente'], 500);
        }
    }
    public function editApiaAdress(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);
            if (!$customer) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            $data = $request->only(['address']); // Solo permitir la actualización de 'address'

            // Actualizar el campo 'address'
            $customer->fill($data);
            $customer->save();

            return response()->json(['message' => 'Dirección del cliente actualizada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la dirección del cliente'], 500);
        }
    }


    public function editApi(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);
            if (!$customer) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            $data = $request->only(['name', 'last_name', 'last_name2', 'phone', 'address', 'image', 'firebase', 'urlFirebase']);

            // Actualizar los campos especificados
            $customer->fill($data);
            //  $customer->save();

            // Actualizar la imagen del cliente
            // if ($request->hasFile('image')) {
            //     $customFileName = uniqid() . '.' . $request->file('image')->extension();
            //     $request->file('image')->storeAs('public/customers', $customFileName);
            //     $customer->image = $customFileName;
            //     Log::info('cliente actualizado IMAGEN: '. $customFileName);

            //    // $customer->save();
            // }


            Log::info('cliente actualizado: ' . $customer);
            $customer->save();
            // Envío de correo al cliente
            try {
                Mail::to($customer->email)->send(
                    new UpdateData(
                        $customer->name,
                        $customer->last_name,
                        $customer->last_name2,
                        $customer->phone,
                        $customer->address,
                        $customer->document,
                        $customer->password,
                        $customer->saldo,
                        $customer->email
                    )
                );
            } catch (Exception $e) {
                \Log::error('Error al enviar correo : ' . $e->getMessage());
            }



            return response()->json(['message' => 'Cliente actualizado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el cliente'], 500);
        }
    }



    public function getByIdApi($id)
    {
        try {
            $customer = Customer::with([
                'sale.services.servicePay.catalogoService' => function ($query) {
                    $query->orderBy('created_at', 'desc'); // Ordenar por estado en orden descendente (PENDING primero)
                },
                'sale.salesDetails.product.product',
                'sale.deliveriesTypes.catalogEntry'
            ])->find($id);

            if (!$customer) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            return response()->json($customer, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener el cliente'], 500);
        }
    }


    public function FindUser($id)
    {
        try {
            $customer = Customer::find($id);
            if (!$customer) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            $customerData = [

                'name' => $customer->name,
                'last_name' => $customer->last_name,
                'last_name2' => $customer->last_name2,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'password' => $customer->password,
                'saldo' => $customer->saldo,
                'image' => asset('storage/customers/' . $customer->image),
                'woocommerce_cliente_id' => $customer->woocommerce_cliente_id,
                'firebase' => $customer->firebase,
                'urlFirebase' => $customer->urlFirebase
            ];

            return response()->json($customerData, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener el cliente'], 500);
        }
    }


    public function loginApi(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('customer')->attempt($credentials)) {
            $customer = Auth::guard('customer')->user();
            $token = $customer->createToken('customer-token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
}
