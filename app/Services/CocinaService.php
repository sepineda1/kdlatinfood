<?php

namespace App\Services;

use App\Models\CocinaLog;
use App\Models\CocinaMedicion;
use App\Models\CocinaVerificacion;
use App\Contracts\CocinaServiceInterface;
use Illuminate\Support\Arr;

class CocinaService implements CocinaServiceInterface
{
    public function allLogs(array $filters)
    {
        $query = CocinaLog::with('insumo', 'verificacion');
        if (!empty($filters['fecha'])) {
            $query->where('fecha', $filters['fecha']);
        }
        if (!empty($filters['ccp_code'])) {
            $query->where('ccp_code', $filters['ccp_code']);
        }
        return $query->paginate(15);
    }

    public function getLogsByInsumoId(int $insumo_id)
    {
        return CocinaLog::with('insumo', 'verificacion')->where('insumo_id', $insumo_id)->orderBy('id', 'desc')->get();
    }

    public function createLog(array $data)
    {
        return CocinaLog::create($data);
    }

    public function getLog(int $id)
    {
        return CocinaLog::with('mediciones.user', 'verificacion.verificador', 'verificacion.revisor')->findOrFail($id);
    }

    public function getMedicion(int $id)
    {
        return CocinaMedicion::with('log', 'user')->findOrFail($id);
    }

    public function updateLog(int $id, array $data)
    {
        $log = $this->getLog($id);
        $log->update($data);
        return $log;
    }

    /*public function saveMediciones(int $logId, array $mediciones)
    {
        CocinaMedicion::where('log_id', $logId)->delete();
        foreach ($mediciones as $m) {
            $data = array_filter($m, function ($v) {
                return $v !== null && $v !== '';
            });
            $data['log_id'] = $logId;
            CocinaMedicion::create($data);
        }
        /*foreach ($mediciones as $m) {
            CocinaMedicion::create(array_merge($m, ['log_id' => $logId]));
        }
    }*/

    public function saveMediciones(int $logId, array $mediciones)
    {
        CocinaMedicion::where('log_id', $logId)->delete();
        foreach ($mediciones as $m) {
            if (empty($m['hora']) || $m['hora'] === null || $m['temperatura'] === null) {
                continue;
            }
            $data = Arr::only($m, ['fase', 'hora', 'temperatura', 'user_id']);
            $data['log_id'] = $logId;

            CocinaMedicion::create($data);
        }
    }

    public function saveVerificacion(int $logId, array $verifData)
    {
        return CocinaVerificacion::create($verifData);
        /*return CocinaVerificacion::updateOrCreate([
            'log_id' => $logId
        ], $verifData);*/
    }
}
