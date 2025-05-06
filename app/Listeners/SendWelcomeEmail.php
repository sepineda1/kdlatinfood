<?php

namespace App\Listeners;

use App\Events\CustomerCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendWelcomeEmail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  CustomerCreated  $event
     * @return void
     */
    public function handle(CustomerCreated $event)
    {
        $customer = $event->customer;

        // Aquí puedes personalizar el contenido del correo de bienvenida
        $data = [
            'name' => $customer->name,
            'email' => $customer->email,
            // Agrega más datos del cliente si los necesitas
        ];

        // Envía el correo de bienvenida
        try{
            Mail::to($customer->email)->send(new WelcomeEmail($data));
        }catch(Exception $e){
            \Log::error('Error al enviar correo : ' . $e->getMessage());
        }
       
    }
}




