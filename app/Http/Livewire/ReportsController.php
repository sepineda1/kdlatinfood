<?php

namespace App\Http\Livewire;
use Illuminate\Support\Facades\DB;
use App\Contracts\ServicePayServiceInterface;
use Livewire\Component;
use App\Models\Customer;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;



class ReportsController extends Component
{    
    
    public $componentName, $data, $details, $sumDetails, $countDetails,
        $reportType, $userId, $dateFrom, $dateTo, $saleId, $saleService = null;

    protected $servicePay;

    public function boot (ServicePayServiceInterface $servicePay){
        $this->servicePay = $servicePay;
    }
    public function mount()
    {
        $this->componentName = 'Reportes de Ventas';
        $this->data = [];
        $this->details = [];
        $this->sumDetails = 0;
        $this->countDetails = 0;
        $this->reportType = 0;
        $this->userId = 0;
        $this->saleId = 0;
       
    }

    public function render()
    {

        $totalSales = Sale::sum('total_with_services');
        $totalSalesCount = Sale::count();
        $totalClientes = Customer::count();
        $this->SalesByDate();        
        
        return view('livewire.reports.component', [
            'users' => User::orderBy('name', 'asc')->get(),
            'totalSales'=>$totalSales,
            'totalSalesCount'=>$totalSalesCount,
            'totalClientes'=>$totalClientes
        ])->extends('layouts.theme.app')
            ->section('content');        
    }

    public function SalesByDate()
    {
        if ($this->reportType == 0) // ventas del dia
        {
            $from = Carbon::parse(Carbon::now())->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse(Carbon::now())->format('Y-m-d')   . ' 23:59:59';
        } else {
            $from = Carbon::parse($this->dateFrom)->format('Y-m-d') . ' 00:00:00';
            $to = Carbon::parse($this->dateTo)->format('Y-m-d')     . ' 23:59:59';
        }



        if ($this->userId == 0) {
            $this->data = Sale::join('users as u', 'u.id', 'sales.user_id')
                ->select('sales.*', 'u.name as user')
                ->whereBetween('sales.created_at', [$from, $to])
                ->get();
        } else {
            $this->data = Sale::join('users as u', 'u.id', 'sales.user_id')
                ->select('sales.*', 'u.name as user')
                ->whereBetween('sales.created_at', [$from, $to])
                ->where('user_id', $this->userId)
                ->get();
        }
    }

    public function claseModal(){
        $this->saleService = null;
    }

    public function getDetails($saleId1)
    {
        try { 
           
            $this->details = SaleDetail::join('presentaciones as p', 'p.id', 'sale_details.presentaciones_id')
            ->join('products as prod', 'prod.id', 'p.products_id')
            ->join('sizes as s', 's.id', 'p.sizes_id')
            ->select('sale_details.id', DB::raw("CONCAT(prod.name, ' ',s.size, ' ',prod.estado) as product"),'sale_details.price', 'sale_details.quantity','sale_details.discount')
            ->where('sale_details.sale_id', $saleId1)
            ->get();

            /*$this->details = Sale::join('sale_details as d', 'd.sale_id', 'sales.id')
            ->join('presentaciones as p', 'p.id', 'd.presentaciones_id')
            ->join('products as pr', 'pr.id', 'p.products_id')
            ->join('sizes as s', 's.id', 'p.sizes_id')
            ->select('d.sale_id',  DB::raw("CONCAT(pr.name, ' ',s.size, ' ',pr.estado) as product"), 'd.quantity', 'd.price', 'd.discount')
            ->whereBetween('sales.created_at', [$fi, $ff])
            ->where('sales.status', 'Paid')
            ->where('sales.user_id', $this->userid)
            ->where('sales.id', $sale->id)
            ->get();*/

            //
            $suma = $this->details->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $this->sumDetails = $suma;
            $this->countDetails = $this->details->sum('quantity');
            $this->saleId = $saleId1;

            $data = $this->servicePay->getServiceSaleBySaleId($saleId1);
            $this->saleService = $data ?:  null;

            $this->emit('show-modal', 'details loaded');           
        } catch (\Throwable $th) {
            //throw $th;
            $this->emit('show-modal', $th->getMessage());   
        }
    }
}
