<?php

namespace App\Listeners;

use App\Events\NuevoPedido;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionPedido
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\NuevoPedido  $event
     * @return void
     */
    public function handle(NuevoPedido $event)
    {
        $pedido = $event->pedido;

        $client = new Client([
            'base_uri' => 'https://fcm.googleapis.com/fcm/',
            'timeout'  => 2.0,
        ]);

        $serverKey = '5a62b87bb12224d235b75f24fb2f3501e964fba5';

        try {
            $response = $client->post('send', [
                'headers' => [
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'to' => 'd7f1JP6lTYiPUW1xanSmIx:APA91bFTajy0qxVxhCBgUylDB3O5kNEF0H0M9NI8P_TWi51G39VIPy8IKy6aJwGbzcicIagWcgX2M9WUqvz8P2DMfX_SeqGAjHLqWjkVvqXdBD32TR77NOzi1OXnLG2m8GZEEZjM0CRF',
                    'notification' => [
                        'title' => 'Nuevo pedido',
                        'body' => 'Se ha creado un nuevo pedido.',
                    ],
                    'data' => [
                        'pedido_id' => $pedido->id,
                        // Puedes agregar más datos que necesites en tu aplicación
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode === 200) {
                // La notificación se envió correctamente
                // Puedes registrar o manejar la respuesta de Firebase aquí
            } else {
                // Error al enviar la notificación
                Log::error('Error al enviar la notificación FCM. Código de estado: ' . $statusCode);
            }
        } catch (\Exception $e) {
            // Error de red u otro error
            Log::error('Error al enviar la notificación FCM: ' . $e->getMessage());
        }
    }
}
