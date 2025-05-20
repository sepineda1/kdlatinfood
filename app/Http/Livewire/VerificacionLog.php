<?php

namespace App\Http\Livewire;
use App\Contracts\CocinaServiceInterface;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class VerificacionLog extends Component
{
    protected $service;
    public $logId, $action;         // id del log, 'approve'|'deny'
    public $hora_verificacion;
    public $hora_revision;
    public $logInfo = null;
    

    protected $listeners = ['open'];

    public function mount() { /* nada aquí */ }

    public function boot(CocinaServiceInterface $service)
    {
        $this->service = $service;
    }

    public function open($logId, $action)
    {
        $this->logInfo = $this->service->getLog($logId);
        $this->logId  = $logId;
        $this->action = $action;
        $this->hora_verificacion = now()->format('H:i');
        $this->hora_revision     = now()->format('H:i');

        $this->dispatchBrowserEvent('show-verif-modal');
    }

    public function save()
    {
        $this->validate([
            'hora_verificacion' => 'required|date_format:H:i',
            'hora_revision'     => 'required|date_format:H:i',
        ]);
        //$this->service->saveVerificacion($this->logId, );

        
        $verifData = [
            'log_id' =>  $this->logId,
            'verificador_id'         => Auth::id(),
            'revisor_id'             => Auth::id(),
            'hora_verificacion'      => $this->hora_verificacion,
            'hora_revision_registros'=> $this->hora_revision,
            'estado'               =>  $this->action,
        ];

         $this->service->saveVerificacion($this->logId, $verifData);

    
        $this->dispatchBrowserEvent('swal', [
          'title'=>'Listo',
          'text'=>'Verificación registrada',
          'icon'=>'success'
        ]);
            $this->dispatchBrowserEvent('hide-verif-modal');
        // opcional: emitir un evento para refrescar el padre
        $this->emitUp('logVerified', $this->logId);
    }

    public function closeModal(){

        $this->dispatchBrowserEvent('hide-verif-modal');
    }

    public function render()
    {
        return view('livewire.verificacion-log');
    }
}
