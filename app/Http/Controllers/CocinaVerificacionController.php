<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Contracts\CocinaServiceInterface;

class CocinaVerificacionController extends Controller
{
    protected $service;

    public function __construct(CocinaServiceInterface $service) {
        $this->service = $service;
    }

    public function create(int $logId)
    {
        $log = $this->service->getLog($logId);
        return view('produccion.cocina.verify', compact('log')); }

    public function store(Request $req, int $logId)
    {
        $data = $req->validate([
            'hora_verificacion'       => 'required|date_format:H:i',
            'hora_revision_registros' => 'required|date_format:H:i',
        ]);
        $data['verificador_id'] = Auth::id();
        $data['revisor_id']     = Auth::id();
        $this->service->saveVerificacion($logId, $data);
        return redirect()->route('produccion.cocina.index')
                         ->with('success','Verificación completada.');
    }
}