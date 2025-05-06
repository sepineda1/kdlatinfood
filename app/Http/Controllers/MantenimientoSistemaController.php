<?php
namespace App\Http\Controllers;
use Carbon\Carbon;
use Exception;
class MantenimientoSistemaController extends Controller
{

    public static function existeMantenimiento($costumerID,$horaInicio = ["hour" => 0, "minute" => 0 , "second" => 0],$horaFin = ["hour" => 2, "minute" => 0 , "second" => 0])
    {
        try {
            $zonaHoraria = 'America/New_York';
            $horaServidor = Carbon::now($zonaHoraria);
            $horaInicioMantenimiento = Carbon::today($zonaHoraria)->setTime($horaInicio['hour'], $horaInicio['minute'], $horaInicio['second']);
            $horaFinMantenimiento = Carbon::today($zonaHoraria)->setTime($horaFin['hour'], $horaFin['minute'], $horaFin['second']);
            $message = '';
            $is_mantenimiento = false;

            if ($costumerID != 189 && $horaServidor->between($horaInicioMantenimiento, $horaFinMantenimiento)) {
                $message = "Para ofrecerle un mejor servicio, realizamos mantenimiento diario en nuestro sistema de ".$horaInicioMantenimiento." a " .$horaFinMantenimiento.". El sistema podría no estar disponible durante estas horas. ¡Gracias por su paciencia!";
                $is_mantenimiento = true;
            }
            return [ 
                'success' =>  $is_mantenimiento,
                'message' => $message,
                'horaServidor' => $horaServidor->format('H:i:s'),
                'horaInicioMantenimiento' => $horaInicioMantenimiento->format('H:i:s'),
                'horaFinMantenimiento' => $horaFinMantenimiento->format('H:i:s'),
                'existeMantenimiento' => $is_mantenimiento,
                'error' => false
            ]; 

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => true
            ];
        }

    }
   
}