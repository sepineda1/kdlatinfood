<?php
namespace App\Http\Livewire;

namespace App\Http\Controllers;
use Livewire\Component;
use App\Models\Presentacion;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Payment;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\DataService\DataService;
use Illuminate\Http\Request;

class QuickBooksView extends Controller
{
    public function render(){ 

        $config = config('quickbooks');
        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' => $config['client_secret'],
            'RedirectURI' => $config['redirect_uri'],
            'accessTokenKey' => $config['access_token'],
            'refreshTokenKey' => $config['refresh_token'],
            'QBORealmID' => $config['realm_id'],
            'baseUrl' => $config['base_url'],
        ]);

    // Obtener datos de QuickBooks
    $customers = $dataService->Query("SELECT * FROM Customer");
    $invoices = $dataService->Query("SELECT * FROM Invoice");
    $payments = $dataService->Query("SELECT * FROM Payment");
    $vendors = $dataService->Query("SELECT * FROM Vendor");
    
    $data = new \stdClass(); // Crea un objeto vacío
    $invoicesNew = []; // Inicializa el array

    foreach ($invoices as $invoice) {
        $customer = Presentacion::where('QB_id', $invoice->CustomerRef)->first();

        $data->DocNumber = $invoice->DocNumber;
        $data->TxnDate = $invoice->TxnDate;
        $data->CustomerRef = $invoice->CustomerRef;
        $data->CustomerName = $customer ? $customer->name . " " . $customer->last_name : 'Cliente Desconocido';
        $data->TotalAmt = $invoice->TotalAmt;
        $data->Balance = $invoice->Balance;

        // Si necesitas guardar varias facturas, mejor usa un array de objetos
        $invoicesNew[] = clone $data;
    }

    return view('livewire.quickbooks-view', compact('customers','invoicesNew', 'invoices', 'payments', 'vendors'));
    } 
}
