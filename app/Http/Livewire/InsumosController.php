<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\User;
use App\Models\Insumo;
use App\Models\Sabores;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\Utils;

class InsumosController extends Component
{
    public $idSabor, $search, $CodigoBarras, $User, $Cantidad_Articulos,
        $Fecha_Vencimiento, $SKU, $selected_id, $pageTitle, $componentName, $peso;
    public $name, $barcode;
    public $categoryid;

    use Utils;

    public function mount()
    {
        $this->pageTitle = 'Listado';
        $this->componentName = 'Lotes';
        $this->idSabor = 'Elegir';
    }

    public function render()
    {
        $sabor = Sabores::with('insumos')
            ->when($this->search, function ($query) {
                $query->where('nombre', $this->search);
            })
            ->get();
        $sabores = Sabores::with('insumos')
            ->where('nombre', $this->search)->get();
        $insumo = Insumo::all();


        return view('livewire.insumos.insumos', ['sabor' => $sabor, 'sabores' => $sabores, 'insumo' => $insumo])->extends('layouts.theme.app')
            ->section('content');
    }

    public function emptySupplies()
    {
        $this->CodigoBarras = '';
        $this->Fecha_Vencimiento = '';
        $this->User = '';
        $this->Cantidad_Articulos = '';
        $this->peso = 'Onzas';
    }

    public function obtenerInsumo()
    {
        // Asegúrate de que idSabor tiene un valor válido
        if ($this->idSabor && $this->idSabor !== 'Elegir') {
            // Realiza acciones con el ID seleccionado
            /*$insumoSeleccionado = Insumo::where('idSabor', $this->idSabor)->first();
            $this->emptySupplies();
            if ($insumoSeleccionado) {                
                $this->CodigoBarras = $insumoSeleccionado->CodigoBarras;
                $this->Fecha_Vencimiento = $insumoSeleccionado->Fecha_Vencimiento;

                $this->User = $insumoSeleccionado->User;
                $this->emit('insumoSeleccionado', $insumoSeleccionado->nombre);
            }else{
                $fechaV = Carbon::now()->addMonths(1);
                $this->CodigoBarras = $this->generateCodigoBarras();
                $this->Fecha_Vencimiento = $fechaV->format('Y-m-d');
                $this->User = Auth()->user()->name;
            }*/
            $fechaV = Carbon::now()->addMonths(1);
            $this->CodigoBarras = $this->generateCodigoBarras();
            $this->Fecha_Vencimiento = $fechaV->format('Y-m-d');
            $this->User = Auth()->user()->name;
        }
    }

    public function LoteInsumo()
    {
        $this->emit('show-modal', 'details loaded');
    }

    //CODIGO DEL PAIS + CODIGO DE LA EMPRESA + CODIGO JILIANO
    //2024140 ()

    public function generateCodigoBarras()
    {
        /*$barcodeNumber = "770" . str_pad(mt_rand(0, 99999), 6, '0', STR_PAD_LEFT);
        $num = $barcodeNumber;
        $parte_num = substr($num, 3);
        $nuevo = "770" . str_pad($parte_num + 1, strlen($parte_num), "0", STR_PAD_LEFT);*/

        return $this->getCodigoBarras();
    }


    public function Store()
    {
        /*$barcodeNumber = "770" . str_pad(mt_rand(0, 99999), 6, '0', STR_PAD_LEFT);
        $num = $barcodeNumber;
        $parte_num = substr($num, 3);
        $nuevo = "770" . str_pad($parte_num + 1, strlen($parte_num), "0", STR_PAD_LEFT);

        $user = Auth()->user()->name;
        $texto = strval($user);
        $texL = str_replace('$', '', trim($texto));*/

        try {
            $rules = [
                'Fecha_Vencimiento' => 'required|date',
                'Cantidad_Articulos' => 'required',
                'CodigoBarras'      => 'required',
                'idSabor'           => 'required',
                'peso'           => 'required|in:Onzas,Libras,Kilogramos'
            ];
            $messages = [
                'Fecha_Vencimiento.required' => 'La fecha de vencimiento es obligatoria',
                'Fecha_Vencimiento.date'     => 'La fecha de vencimiento debe ser una fecha válida',
                'Cantidad_Articulos.required' => 'La cantidad de artículos es obligatoria',
                'CodigoBarras.required'      => 'El código de barras es obligatorio',
                'idSabor.required'           => 'El sabor es obligatorio',
                'peso.required'           => 'La unidad de Peso es Requerido',
            ];
            $this->validate($rules, $messages);
            $amount = 0;
            switch ($this->peso) {
                case 'Onzas':
                    $amount = $this->Cantidad_Articulos / 16;
                    break;
                case 'Libras':
                    $amount = $this->Cantidad_Articulos;
                    break;
                case 'Kilogramos':
                    $amount = $this->Cantidad_Articulos * 2.20462;
                    break;
            }

            $Lot = Insumo::create([
                'Fecha_Vencimiento' => $this->Fecha_Vencimiento,
                'Cantidad_Articulos' => $amount,
                'CodigoBarras' => $this->CodigoBarras,
                'idSabor' => $this->idSabor,
                'User' => $User = Auth()->user()->name,
                'peso' => $this->peso
            ]);
            $insumo = Insumo::all();
            $this->updateSaborStock($this->idSabor, $amount);
            $this->emptySupplies();
            $this->render();

            $this->emit('producto-creado');
            $this->emit('lote-added', 'Lote Agregado');
            $this->emit('global-msg', 'Lote de insumo CREADO');
        } catch (\Exception $e) {
            //throw $th;
            $this->emit('sale-error',  $e->getMessage());
            throw $e;
        }
    }
    public function updateSaborStock($productId, $addedStock)
    {
        $product = Sabores::findOrFail($productId);
        $currentStock = $product->stock;
        $newStock = $currentStock + $addedStock;
        $product->update(['stock' => $newStock]);
        $this->emit('global-msg', "SE ACTUALIZO EL STOCK DEL SABOR");
    }
    public  function resetUI()
    {
        $this->emit('producto-creado');
        $this->Cantidad_Articulos = 0;
        $this->idSabor = 'Elegir';
        $this->emptySupplies();
    }

    //api
    public function showAll()
    {
        try {
            $insumos = Insumo::with('sabor')->get();

            return response()->json($insumos);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    //api
    public function createApi(Request $request)
    {
        try {
            $request->validate([
                'idSabor' => 'required|not_in:Elegir',
                'Cantidad_Articulos' => 'required'
            ]);

            /*$barcodeNumber = "770" . str_pad(mt_rand(0, 99999), 6, '0', STR_PAD_LEFT);
            $num = $barcodeNumber;
            $parte_num = substr($num, 3);
            $nuevo = "770" . str_pad($parte_num + 1, strlen($parte_num), "0", STR_PAD_LEFT);*/
            $nuevo = $this->getCodigoBarras();

            $insumo = Insumo::create([
                'Fecha_Vencimiento' => Carbon::now()->addMonths(1),
                'Cantidad_Articulos' => $request->Cantidad_Articulos,
                'CodigoBarras' => $nuevo,
                'idSabor' => $request->idSabor,
                'User' => Auth()->user()->name
            ]);

            $this->updateSaborStock($request->idSabor, $request->Cantidad_Articulos);

            return response()->json([
                'message' => 'Insumo created successfully',
                'data' => $insumo,
            ], Response::HTTP_CREATED);
            
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function FindApi($barcode)
    {
        try {
            $insumo = Insumo::with('sabor')->where('CodigoBarras', $barcode)->first();

            if (!$insumo) {
                return response()->json([
                    'message' => 'Insumo not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $insumo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
