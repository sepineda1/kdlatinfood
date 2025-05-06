<?php

namespace App\Http\Controllers;

use App\Models\ErroresImpresora;
use Illuminate\Http\Request;

class ErroresImpresoraController extends Controller
{
    /**
     * Agregar un producto al carrito.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SaveError(Request $request)
    {
        $request->validate([
            'trace_error' => 'required',
        ]);

        try {
            // Crear un nuevo registro en el carrito
            ErroresImpresora::create([
                'trace_error' => $request->trace_error,
            ]);

            return response()->json(['success' => true, 'message' => 'Error Guardado'], 200);

        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra
            return response()->json(['success' => false, 'message' => 'Hubo un error al guardar. Inténtelo de nuevo.', 'error' => $e->getMessage()], 500);
        }
    }
}
