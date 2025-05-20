<?php

namespace App\Contracts;

interface CocinaServiceInterface
{
    public function allLogs(array $filters);
    public function getLogsByInsumoId(int $insumo_id);
    public function createLog(array $data);
    public function getLog(int $id);
    public function getMedicion(int $id);
    public function updateLog(int $id, array $data);
    public function saveMediciones(int $logId, array $mediciones);
    public function saveVerificacion(int $logId, array $verifData);
}
