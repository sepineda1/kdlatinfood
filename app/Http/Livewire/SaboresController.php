<?php

namespace App\Http\Livewire;

use App\Models\Sabores;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class SaboresController extends Component
{
    public $nombre, $description, $descripcion, $selected_id, $search, $pageTitle, $componentName;
    public $totalSabores, $totalStock, $totalPYR, $highestResult, $topSellingSabor,$ultimoSaborCreado,$libra_consumo;
    public function mount()
    {
        $this->totalPYR = Sabores::sum(DB::raw('stock * libra_consumo'));
        $this->highestResult = Sabores::select(DB::raw('MAX(stock * libra_consumo) as max_result'))->value('max_result');
        // Obtener el sabor que más se vende
       
         //   dd($topSellingSabor);
        $this->totalSabores = Sabores::count();
        $this->ultimoSaborCreado = Sabores::latest('created_at')->first();

        $this->totalStock = Sabores::sum('stock');
        ;
        $this->pageTitle = 'List';
        $this->componentName = 'Sabores';
    }
    public function render()
    {
        $this->topSellingSabor = Sabores::select(
            'sabores.id',
            'sabores.nombre')
               ->join('products', 'products.sabor_id', '=', 'sabores.id')
               ->join('sale_details', 'sale_details.presentaciones_id', '=', 'products.id')
               ->groupBy('sabores.id','sabores.nombre')
   
               ->first();
        //$data=Sabores::all();
        if ($this->topSellingSabor) {
            $topSellingSaborEQ = Sabores::find($this->topSellingSabor->id);
            $nombreSabor = $topSellingSaborEQ->nombre;
        } else {
            $nombreSabor = 'Ningún sabor';
        }
        

        $data = Sabores::orderBy('nombre')
            ->when($this->search, function ($query) {
                $query->where('nombre', $this->search);
            })
            ->whereRaw("nombre NOT LIKE '%ELIMINAR%'")
            ->get();
        return view('livewire.sabores.sabores', ['data' => $data,
       'nombreSabor'=>$nombreSabor

        ])->extends('layouts.theme.app')
            ->section('content');
    }
    public function Edit($id)
    {
        $record = Sabores::find($id);
        $this->nombre = $record->nombre;
        $this->description = $record->descripcion;
        $this->libra_consumo = $record->libra_consumo;
        $this->selected_id = $record->id;


        $this->emit('show-modal', 'show modal!');
    }



    public function Store()
    {
        try {
            $rules = [
                'nombre' => 'required|unique:sabores|min:3'
            ];
    
    
    
            $this->validate($rules);
    
            $sabor = Sabores::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->description,
                'libra_consumo' => 0,
                //'libra_consumo' => $this->libra_consumo
            ]);
    
    
    
            $this->emit('producto-creado');
            $this->resetUI();
            $this->emit('global-msg', 'Sabor Creado');
            $this->emit('sabor-added', 'Categoría Registrada');
		} catch (\Exception $e) {
			$this->emit('sale-error',  $e->getMessage());
			throw $e;
		}
    }


    public function Update()
    {
        try {
            $rules = [
                'nombre' => "required|min:3,{$this->selected_id}"
            ];
    
    
    
            $this->validate($rules);
    
    
            $sabor = Sabores::find($this->selected_id);
            $sabor->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->description,
                'libra_consumo' => 0
                //'libra_consumo' => $this->libra_consumo,
            ]);
    
    
            $this->emit('producto-creado');
            $this->resetUI();
            $this->emit('global-msg', 'Sabor Actualizado');
            $this->emit('sabor-updated', 'Categoría Actualizada');
		} catch (\Exception $e) {
			$this->emit('sale-error',  $e->getMessage());
			throw $e;
		}
    }


    public function resetUI()
    {
        $this->nombre = '';

        $this->description = '';
        $this->selected_id = 0;
    }



    protected $listeners = ['deleteFlavor' => 'Destroy'];


    public function Destroy(Sabores $sabor)
    {
        
        try {          
            $sabor->delete();
            $this->emit('flavor-delete', 'Se elimino sabor');
        } catch (\Throwable $th) {
            if (strpos($th->getMessage(), 'foreign key constraint') !== false) {                
                $this->emit('error-delete-flavor', "El sabor no puede ser eliminado, debido a que se encuentra ligado a productos.");
            } else {
                $this->emit('error-delete-flavor',  $th->getMessage());
            }
        }
    }

    public function showAll()
    {
        try {
            $sabores = Sabores::orderBy('nombre')
                ->when($this->search, function ($query) {
                    $query->where('nombre', $this->search);
                })
                ->get();

            return response()->json($sabores);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createApi(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|unique:sabores|min:3',
                'description' => 'required'
            ]);

            $sabor = Sabores::create([
                'nombre' => $request->nombre,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Sabor created successfully',
                'data' => $sabor,
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

    public function findApi($id)
    {
        try {
            $sabor = Sabores::findOrFail($id);

            return response()->json([
                'data' => $sabor,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Sabor not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
