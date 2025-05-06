<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Presentacion;
use App\Models\Sale;
use App\Models\Lotes;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Inspectors;
use App\Models\SaleDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ExportController extends Controller
{

    public function historial($cliente_id)
    {
        $cliente = Customer::find($cliente_id);

        if ($cliente) {
            $ventas = $cliente->sale;

            $pdf = PDF::loadView('pdf.historial', compact('ventas', 'cliente'));
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'Imprimio Historial',
                'seccion' => 'Reportes'
            ]);
            return $pdf->stream('historial.pdf');
        } else {
            // Manejar el caso cuando el cliente no existe
            $user = Auth()->user()->name;
            $inspector = Inspectors::create([
                'user' => $user,
                'action' => 'intento imprimir Historial de un cliente descocido',
                'seccion' => 'Reportes'
            ]);
            return "El cliente con ID $cliente_id no fue encontrado";
        }
    }

    public function InspectorsPDF()
    {
        $data = Inspectors::all(); // Obtén todos los registros

        $pdf = PDF::loadView('pdf.inspectors', compact('data')); // Carga la vista del PDF y pasa los datos necesarios

        return $pdf->download('Logs_For_Inspectors.pdf');
    }




    
    public function detail($id)
    {
        $data = Product::find($id);
        $lot = Lotes::find($data->id);
        $prod = Product::where('id', $data->id)->first();
        $qr =  QrCode::size(480)->generate($data->KeyProduct);
        $hora = now()->format('d/m/Y h:i A');
    
        // Generar el PDF con orientación horizontal
        $pdf = PDF::loadView('pdf.lote', compact('data', 'lot', 'prod', 'qr', 'hora'))
                    ->setPaper('landscape')
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Devolver el PDF en línea (stream)
        return $pdf->stream($data->name . '.pdf');
    }
    // recibe id de producto y barcode
    public function detail_LOTE($id,$prodBarcode,$idL)
    {   
        $lot = Lotes::find($idL);
        $lote = $lot;     
        $prod = Presentacion::where('products_id', $id)
            ->where('barcode', $prodBarcode)->first();  
        $data = $prod;
        $qr =  QrCode::size(480)->generate($prod->KeyProduct);
        $hora = now()->format('m/d/Y h:i A');
    
        // Generar el PDF con orientación horizontal
        $pdf = PDF::loadView('pdf.loteQR', compact('data', 'lot', 'prod', 'qr', 'hora','lote'))
                    ->setPaper('landscape')
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Devolver el PDF en línea (stream)
        return $pdf->stream($prod->product->name . '.pdf');
    }

    public function productoQR($id)
    {
        $data = Presentacion::find($id);
        $lot = Lotes::where('SKU',$id)
        ->where('Fecha_Vencimiento', '>=', now()) // Solo considera fechas futuras o actuales
        ->orderBy('Fecha_Vencimiento', 'asc')->first();
        if(!$lot){
            $lot = '';
        }
        //  $lot = Lotes::find($idL);
        $prod = Presentacion::where('id', $data->id)->first();
       // $lote = Lotes::where('id', $lot->id)->first();
        $qr =  QrCode::size(480)->generate($data->KeyProduct);
        $hora = now()->format('m/d/Y h:i A');
    
        // Generar el PDF con orientación horizontal
        $pdf = PDF::loadView('pdf.productoQR', compact('data', 'prod', 'qr', 'hora','lot'))
                    ->setPaper('landscape')
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Devolver el PDF en línea (stream)
        return $pdf->stream($data->product->name . '.pdf');
    }

    /*public function productoQR($id)
    {
        $data = Product::find($id);
      //  $lot = Lotes::find($idL);
        $prod = Product::where('id', $data->id)->first();
       // $lote = Lotes::where('id', $lot->id)->first();
        $qr =  QrCode::size(480)->generate($data->KeyProduct);
        $hora = now()->format('d/m/Y h:i A');
    
        // Generar el PDF con orientación horizontal
        $pdf = PDF::loadView('pdf.productoQR', compact('data', 'prod', 'qr', 'hora'))
                    ->setPaper('landscape')
                    ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Devolver el PDF en línea (stream)
        return $pdf->stream($data->name . '.pdf');
    }*/


    public function reportPDF($userId, $reportType, $dateFrom = null, $dateTo = null)
    {
        $data = [];

        if ($reportType == 0) // ventas del dia
        {
            $from = Carbon::parse(Carbon::now())->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse(Carbon::now())->format('Y-m-d')   . ' 23:59:59';
        } else {
            $from = Carbon::parse($dateFrom)->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse($dateTo)->format('Y-m-d')     . ' 23:59:59';
        }


        if ($userId == 0) {
            $data = Sale::join('users as u', 'u.id', 'sales.user_id')
                ->select('sales.*', 'u.name as user')
                ->whereBetween('sales.created_at', [$from, $to])
                ->get();
        } else {
            $data = Sale::join('users as u', 'u.id', 'sales.user_id')
                ->select('sales.*', 'u.name as user')
                ->whereBetween('sales.created_at', [$from, $to])
                ->where('user_id', $userId)
                ->get();
        }

        $user = $userId == 0 ? 'Todos' : User::find($userId)->name;
        $pdf = PDF::loadView('pdf.reporte', compact('data', 'reportType', 'user', 'dateFrom', 'dateTo'));

        /*
    $pdf = new DOMPDF();
    $pdf->setBasePath(realpath(APPLICATION_PATH . '/css/'));
    $pdf->loadHtml($html);
    $pdf->render();
    */
        /*
    $pdf->set_protocol(WWW_ROOT);
    $pdf->set_base_path('/');
*/
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Imprimio un reporte',
            'seccion' => 'Reportes'
        ]);
        return $pdf->stream('Ventas.pdf'); // visualizar
        //$customReportName = 'salesReport_'.Carbon::now()->format('Y-m-d').'.pdf';
        //return $pdf->download($customReportName); //descargar

    }


    public function reporteExcel($userId, $reportType, $dateFrom = null, $dateTo = null)
    {
        $reportName = 'Reporte de Ventas_' . uniqid() . '.xlsx';
        $user = Auth()->user()->name;
        $inspector = Inspectors::create([
            'user' => $user,
            'action' => 'Imprimio un reporte en excel',
            'seccion' => 'Reportes'
        ]);
        return Excel::download(new SalesExport($userId, $reportType, $dateFrom, $dateTo), $reportName);
    }

    public function invoice($sale_id){
        $imagePath = storage_path('app/public/logo.png');
        $logoBase64 = base64_encode(file_get_contents($imagePath));

        $imagePathFirma = storage_path('app/public/firmas/'.$sale_id.'.png');
        $Firma = base64_encode(file_get_contents($imagePathFirma));
        
        $Sale = Sale::with('salesDetails','user','client')->find($sale_id);
        $pdf = PDF::loadView('pdf.invoice', compact('Sale','logoBase64','Firma'))
        ->setPaper('landscape')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true ,'isRemoteEnabled' => true]);;
        return $pdf->download('invoice_'.$Sale->id . '.pdf');
    }
}
