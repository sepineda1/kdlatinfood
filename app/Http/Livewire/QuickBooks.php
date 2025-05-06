<?php

namespace App\Http\Livewire;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use Illuminate\Http\Request;
use Livewire\Component;
use App\Models\quickbook_credentials;
use App\Services\QuickBooksService;
use App\Models\Customer as Cliente;
use Carbon\Carbon;

class QuickBooks extends Component
{
    protected $quickBooksService;

    public function boot(QuickBooksService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
    }

    public function render(Request $request)
    {
        /*$config = config('quickbooks');
            $this->emit('global-msg', 'Actualizando token');
            $qb_credentials = $this->update_access_token(); //obtiene el token nuevo
            $dataService = DataService::Configure([
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' => $config['client_secret'],
                'RedirectURI' => $config['redirect_uri'],
                'accessTokenKey' => $qb_credentials['access_token'],
                'refreshTokenKey' => $qb_credentials['refresh_token'],
                'QBORealmID' => $config['realm_id'],
                'baseUrl' => $config['base_url'],
            ]);*/

             
         

    // Obtener datos de QuickBooks
     //$query = "SELECT * FROM Customer";
        $customers = $this->quickBooksService->SQL_Quickboox('Customer');

        //$query = "SELECT * FROM Invoice";
        $invoices = $this->quickBooksService->SQL_Quickboox('Invoice');
 
        //$query = "SELECT * FROM Payment";
        $payments = $this->quickBooksService->SQL_Quickboox('Payment');

        //$query = "SELECT * FROM Vendor";
        $vendors = $this->quickBooksService->SQL_Quickboox('Vendor');

        //$query = "SELECT * FROM Account WHERE AccountType='Income'";
        $income = $this->quickBooksService->SQL_Quickboox('Account',"AccountType='Income'");


        $data = new \stdClass(); // Crea un objeto vacío
        $invoicesNew = []; // Inicializa el array
            
        foreach ($invoices as $invoice) {
            $customer = Cliente::where('QB_id', $invoice->CustomerRef)->first();
            $data->DocNumber = $invoice->DocNumber;
            $data->TxnDate = Carbon::parse($invoice->TxnDate)->format('m-d-Y');;
            $data->CustomerRef = $invoice->CustomerRef;
            $data->CustomerName = $customer ? $customer->name . " " . $customer->last_name : 'Cliente Desconocido';
            $data->TotalAmt = $invoice->TotalAmt;
            $data->Balance = $invoice->Balance;
    
            // Si necesitas guardar varias facturas, mejor usa un array de objetos
            $invoicesNew[] = clone $data;
        }
    
        //dd($income);
    return view('livewire.quickbooks-view', compact('customers', 'invoicesNew','invoices', 'payments', 'vendors'))->extends('layouts.theme.app')
            ->section('content');
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
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper(); //Hace la autenticacion
        $accessTokenObj = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($config['refresh_token']); //Obtiene el nuevo token con el refresh token
        $accessTokenValue = $accessTokenObj->getAccessToken(); //Obtiene el token
        $refreshTokenValue = $accessTokenObj->getRefreshToken(); // Obtiene el refresh token 

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
}
