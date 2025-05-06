<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Consumo;
use App\Models\Sabores;
use App\Models\Presentacion;
use Exception;

class ConsumoController extends Component
{
    public $presentacion_id;
    public $producto_id;
    public $sabor_id = 'Elegir';
    public $libra_consumo;
    public $peso = 'Onzas';
    public $consumos = [];
    public $presentacion;


    public $selected_id;

    public function mount($presentacion_id)
    {
        $this->presentacion_id = $presentacion_id;
        $this->presentacion = Presentacion::with('product', 'size')->findOrFail($presentacion_id);
        $this->producto_id = $this->presentacion->products_id;
        $this->loadConsumos();

        $this->emit('consumo-cargado'); // Esto lo usaremos para abrir el modal
    }

    public function loadConsumos()
    {
        $this->consumos = Consumo::where('presentacion_id', $this->presentacion_id)->get();
    }

    public function resetInput()
    {
        $this->sabor_id = 'Elegir';
        $this->libra_consumo = '';
        $this->peso = 'Onzas';
        $this->selected_id = null;
    }

    public function store()
    {
        try {
            $presentacion = Presentacion::find($this->presentacion_id);
            $this->validate([
                'sabor_id' => 'required|numeric|exists:sabores,id',
                'libra_consumo' => 'required|numeric',
                'peso' => 'required|in:Onzas,Libras,Kilogramos'
            ]);
            $sabor = Sabores::find($this->sabor_id);
            if($sabor->stock > 0){
                Consumo::create([
                    'producto_id' => $presentacion->products_id,
                    'presentacion_id' => $this->presentacion_id,
                    'sabor_id' => $this->sabor_id,
                    'libra_consumo' => $this->libra_consumo,
                    'peso' => $this->peso
                ]);
        
                $this->resetInput();
                $this->loadConsumos();
                $this->emit('global-msg', 'Consumo agregado correctamente');
            }else{
                $this->emit('global-msg', '⚠️ Sabor sin stock. No se puede agregar este consumo a la presentación.');
                $this->emit('messageForm', '⚠️ Sabor sin stock. No se puede agregar este consumo a la presentación. ¿Desea agregarlo?');
            }

        } catch (\Exception $e) {
            $this->emit('global-msg', '⚠️Completa todos los campos : '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $consumo = Consumo::find($id);
        $this->selected_id = $consumo->id;
        $this->sabor_id = $consumo->sabor_id;
        $this->libra_consumo = $consumo->libra_consumo;
        $this->peso = $consumo->peso;
    }

    public function update()
    {
        $this->validate([
            'sabor_id' => 'required|numeric|exists:sabores,id',
            'libra_consumo' => 'required|numeric',
            'peso' => 'required|in:Onzas,Libras,Kilogramos'
        ]);

        $consumo = Consumo::find($this->selected_id);
        $consumo->update([
            'sabor_id' => $this->sabor_id,
            'libra_consumo' => $this->libra_consumo,
            'peso' => $this->peso
        ]);

        $this->resetInput();
        $this->loadConsumos();
        $this->emit('global-msg', 'Consumo actualizado correctamente');
    }

    public function destroy($id)
    {
        Consumo::find($id)->delete();
        $this->loadConsumos();
        $this->emit('global-msg', 'Consumo eliminado');
    }

    public function render()
    {
        // Obtener los sabores ya asignados a esta presentación y producto
        $saboresAsignados = Consumo::where('presentacion_id', $this->presentacion_id)
                            ->where('producto_id', $this->producto_id)
                            ->pluck('sabor_id');
    
        // Solo mostrar los sabores que NO han sido asignados aún
        $sabores = Sabores::whereNotIn('id', $saboresAsignados)
                    ->orderBy('nombre')
                    ->get();
    
        return view('livewire.consumo.consumo-controller', compact('sabores'));
    }
    
}
