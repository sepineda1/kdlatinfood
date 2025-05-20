<?php
// app/Http/Livewire/CocinaLogController.php
namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Contracts\CocinaServiceInterface;
use App\Models\Insumo;
use App\Models\Presentacion;

class CocinaLogController extends Component
{
    public $mode = 'index';         // index, show, create, edit
    public $logs = [];
    public $lote;
    public $loteId;

    // form fields
    public $insumo_id;
    public $product_id;
    public $fecha;
    public $ccp_code;
    public $observaciones;

    public $filters = [
        'fecha'    => null,
        'ccp_code' => null,
    ];

    protected $service;

    protected $rules = [
        'insumo_id'     => 'required|exists:insumos,id',
        'product_id'    => 'required|exists:presentacion,id',
        'fecha'         => 'required|date',
        'ccp_code'      => 'required|in:1B,1B-1,2B,2B-1',
        'observaciones' => 'nullable|string',
    ];

    public function boot(CocinaServiceInterface $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->loadLogs();
    }

    public function updatedFilters()
    {
        $this->loadLogs();
    }

    public function loadLogs()
    {
        $this->logs = $this->service->allLogs($this->filters);
        $this->mode = 'index';
    }

    public function show($id)
    {
        $this->loteId = $id;
        $this->lote   = $this->service->getLog($id);
        $this->lote->load(['insumo.sabor','presentacion','mediciones','verificaciones']);
        $this->mode = 'show';
    }

    public function create()
    {
        $this->resetForm();
        $this->mode = 'create';
    }

    public function store()
    {
        $data = $this->validate();
        $log  = $this->service->createLog($data);
        $this->show($log->id);
    }

    public function edit($id)
    {
        $this->lote   = $this->service->getLog($id);
        $this->insumo_id     = $this->lote->insumo_id;
        $this->product_id    = $this->lote->presentacion_id;
        $this->fecha         = $this->lote->fecha;
        $this->ccp_code      = $this->lote->ccp_code;
        $this->observaciones = $this->lote->observaciones;
        $this->mode = 'edit';
    }

    public function update()
    {
        $data = $this->validate();
        $this->service->updateLog($this->lote->id, $data);
        $this->loadLogs();
    }

    public function destroy($id)
    {
        $this->service->deleteLog($id);
        $this->loadLogs();
    }

    private function resetForm()
    {
        $this->reset(['insumo_id','product_id','fecha','ccp_code','observaciones']);
    }

    public function render()
    {
        $insumos  = Insumo::all();
        $products = Presentacion::where('visible','si')->get();

        return view('livewire.cocina-log-controller', [
            'insumos'  => $insumos,
            'products' => $products,
        ])
        ->extends('layouts.theme.app')
        ->section('content');
    }
}
