<?php

namespace App\Http\Livewire;
use App\Models\MapsDriver;
use App\Models\Operario;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;


class MapsDriverController extends Component
{

    public $driver_en_ruta = [], $direccion;
    public function render()
    {
        //Aqui traigo todos los conductores que estan en ruta
        $this->driver_en_ruta = MapsDriver::with('envio')->where('estado', 'En ruta')->get();

        return view('livewire.maps.component')
            ->extends('layouts.theme.fullScreen')
            ->section('content');
    }


    public function getDriver(Request $request)
    {
        $driverId = $request->input('driver_id');
        if ($driverId) {
            $drivers = Operario::with('envios.maps_driver')
                               ->where('id', $driverId) // Filtramos por el id del conductor
                               ->get();
        } else {
            $drivers = Operario::with('envios.maps_driver')->get(); // Obtener todos los conductores
        }
        $drivers = Operario::with('envios.maps_driver')->get();

        $dataSend = [];
        foreach ($drivers as $driver) {
            $data["id"] = $driver->id;
            $data["name"] = $driver->nombre . " " . $driver->apellido;
            $data["company"] = $driver->compañia;
            $data["de_planta"] = $driver->de_Planta;
            $data["edad"] = $driver->edad;
            $data['envios'] = [];

            foreach ($driver->envios as $envio) {
                // Inicializamos el array de envio para cada entrega
                $envioData = [];
                $envioData["id"] = $envio->id;
                $envioData["orden_id"] = $envio->sales->id;
                $venta = $envio->sales;
                $maps_driver = $envio->maps_driver;
                $customer = $envio->sales->customer;

                $envioData["customer_id"] = $customer->id;
                $envioData["customer_name"] = $customer->name . " " . $customer->lastname;
                $envioData["customer_address"] = $customer->address;
                $envioData["customer_phone"] = $customer->phone;
                $envioData["customer_image"] = $customer->image;
                $map = $this->getLatLonFromAddress($customer->address);
                $envioData["customer_latitude"] = $map['lat'];
                $envioData["customer_longitude"] = $map['lon'];
                if (!empty($maps_driver)) {

                    // Asignamos el estado del envio
                    $envioData["state"] = $maps_driver->estado;
                    $location = $maps_driver->location; 
                    // Si el estado es 'En Ruta', obtenemos las coordenadas
                    if (!empty($location)) {
                        // Extrae latitud y longitud correctamente
                        $driver = DB::table('maps_driver')
                            ->where('id', $maps_driver->id)
                            ->selectRaw('ST_Y(location) as latitude, ST_X(location) as longitude')
                            ->first();
                        
                        if ($driver) {
                            $envioData["location_latitude_driver"] = $driver->latitude;
                            $envioData["location_longitude_driver"] = $driver->longitude;
                        }
                    }
                } else {
                    // Si no existe el maps_driver, el estado es 'Pendiente'
                    $envioData["state"] = 'Pendiente';
                }

                // Añadimos el envio al array 'envios'
                $data['envios'][] = $envioData;
            }
            $dataSend[] = $data;
        }




        return response()->json(['success' => true, 'drivers' => $dataSend], 200);
    }

    public function getCoordenateEnRuta($envio_id){
        //Obtener el estado de la ruta y las coordenas
        $maps = MapsDriver::where('envio_id', $envio_id)->first();
        $envioData = [];
        if($maps->estado == "En Ruta"){
            $driver = DB::table('maps_driver')
            ->where('id', $maps->id)
            ->selectRaw('ST_Y(location) as latitude, ST_X(location) as longitude')
            ->first();
            $envioData["latitude"] = $driver->latitude;
            $envioData["longitude"] = $driver->longitude;
        }
        
        return response()->json(['success' => true, 'envioData' => $envioData], 200);
    }

    public function getExistEnRoute($operarioId){
        $enRutaCount = DB::table('envios')
        ->join('maps_driver', 'envios.id', '=', 'maps_driver.envio_id')
        ->where('envios.id_transport', $operarioId)
        ->where('maps_driver.estado', 'En Ruta')
        ->count();
        $existRoute = $enRutaCount > 0 ? true : false; 
        return response()->json(['success' => true, 'existRoute' => $existRoute], 200);
    }


    public function getLatLonFromAddress($address)
    {
        // Verificamos si la dirección está vacía
        if (empty($address)) {
            return null;  // Si la dirección está vacía, retornamos null
        }

        // Verificamos si la dirección ya está en caché
        $cache = Cache::get('latlon_' . md5($address));  // Usamos hash de la dirección como clave
        if ($cache) {
            return $cache;  // Retornamos las coordenadas de la caché
        }

        // Si no está en caché, realizamos la petición HTTP
        $opts = [
            "http" => [
                "header" => "User-Agent: MyAppName/1.0 (contacto@tuemail.com)\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&format=json&limit=1";

        try {
            $response = file_get_contents($url, false, $context);
            $data = json_decode($response, true);

            if (!empty($data)) {
                // Extraemos latitud y longitud
                $lat = $data[0]['lat'] ?? null;
                $lon = $data[0]['lon'] ?? null;

                if ($lat && $lon) {
                    // Guardamos las coordenadas en caché
                    $coordinates = ['lat' => $lat, 'lon' => $lon];
                    Cache::put('latlon_' . md5($address), $coordinates, now()->addHours(24));  // Guardamos por 24 horas
                    return $coordinates;
                }
            }
        } catch (\Exception $e) {
            return null;  // Si ocurre un error, retornamos null
        }

        return null;  // Si no se encuentra la dirección, retornamos null
    }



}