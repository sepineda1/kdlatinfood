<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Contracts\CocinaServiceInterface;
use App\Models\Insumo;
use App\Models\Presentacion;
use Exception;

class CocinaController extends Component
{
    public $step = 1;
    public $insumos;
    public $products;
    public $insumo_id;
    public $selected_insumo_id;
    public $logs = [];
    public $presentacion_id;
    public $fecha;
    public $ccp_code;
    public $observaciones;
    public $mediciones = [];
    public $medicionesVerification = [];
    public $hora_verificacion;
    public $hora_revision_registros;
    public $logId;

    protected $map = [
        '1B'   => ['cook_min', 'cook_120'],
        '1B-1' => ['cook_min', 'cook_120'],
        '2B'   => ['cook_min', 'cook_120', 'chill_120_80', 'chill_80_55', 'chill_le40'],
        '2B-1' => ['cook_min', 'cook_120', 'chill_120_80', 'chill_80_55', 'chill_le40'],
    ];

    protected $service;

    protected $rules = [
        'insumo_id'       => 'required|exists:insumos,id',
        'fecha'           => 'required|date',
        'ccp_code'        => 'required|in:1B,1B-1,2B,2B-1',
        'observaciones'   => 'nullable|string',
    ];

    public function boot(CocinaServiceInterface $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->insumos  = Insumo::all();
        $this->products = Presentacion::where('visible', 'si')->get();
    }

    protected $listeners = [
        'logVerified' => 'refreshLogs'
    ];

    public function refreshLogs($logId)
    {
        $this->logs = $this->service->getLogsByInsumoId($this->selected_insumo_id);
    }

    public function updatedSelectedInsumoId()
    {
        if ($this->selected_insumo_id) {
            $this->logs = $this->service
                ->getLogsByInsumoId($this->selected_insumo_id);
        } else {
            $this->logs = [];
        }
    }
    public function updatedCcpCode($value)
    {
        // Vuelve a poblar $mediciones con su fase correspondiente
        $this->mediciones = [];

        foreach ($this->map[$value] as $idx => $fase) {
            $this->mediciones[$idx] = [
                'fase'        => $fase,
                'hora'        => null,
                'temperatura' => null,
            ];
        }
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            try{
                $data = $this->validate();
                $log  = $this->service->createLog(array_merge($data, ['observaciones' => $this->observaciones]));
                $this->logId = $log->id;
                $this->step  = 2;
                $this->emit('producto-creado');
                return;
            }catch (Exception $e){
             
                $this->emit('sale-error', 'Hubo un error: ' . $e->getMessage());
            }
      
        }

        if ($this->step === 2) {
            try{
                foreach ($this->mediciones as &$m) {
                    $m['user_id'] = Auth::id();
                }
                $this->service->saveMediciones($this->logId, $this->mediciones);
                $this->dispatchBrowserEvent('swal', ['title' => 'Mediciones guardadas', 'icon' => 'success']);
                $this->step = 3;
                $this->emit('producto-creado');
                return;
            }catch (Exception $e){
                $this->emit('sale-error', 'Hubo un error: ' . $e->getMessage());
            }
        }
    }

    public function saveVerificacion()
    {
        $data = [
            'hora_verificacion'       => $this->hora_verificacion,
            'hora_revision_registros' => $this->hora_revision_registros,
            'verificador_id'          => Auth::id(),
            'revisor_id'              => Auth::id(),
        ];
        $this->service->saveVerificacion($this->logId, $data);
        $this->dispatchBrowserEvent('swal', ['title' => 'Verificación completada', 'icon' => 'success']);
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.cocina.cocina', [
            'insumos'  => $this->insumos,
            'products' => $this->products,
            'logs'     => $this->logs,
        ])
            ->extends('layouts.theme.app')
            ->section('content');
    }
}
