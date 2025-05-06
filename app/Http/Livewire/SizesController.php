<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Sizes;

class SizesController extends Component
{
    public $size, $search, $image, $selected_id, $pageTitle, $componentName;
    public $sizeId;
    public function mount()
    {
        $this->search = '';
        $this->pageTitle = 'Listado';
        $this->componentName = 'Sizes';


    }
    protected $rules = [
        'size' => 'required|string|max:255',
    ];

    public function render()
    {
        $sizes = Sizes::all();
        return view('livewire.sizes.sizes-controller', ['sizes' => $sizes])
            ->extends('layouts.theme.app')
            ->section('content');
    }

    public function Store()
    {
        try {
            $this->validate();

            Sizes::create([
                'size' => $this->size,
            ]);
            $this->emit('producto-creado');
            $this->resetUI();
            $this->emit('category-added', 'Categoría Registrada');
            $this->emit('global-msg', "Tamaño creado correctamente ");
		} catch (\Exception $e) {
			$this->emit('sale-error',  $e->getMessage());
			throw $e;
		}
    }

    public function Edit($id)
    {
        try {
            $size = Sizes::findOrFail($id);
            $this->selected_id = $id;
            $this->size = $size->size;
            $this->emit('show-modal', 'show modal!');
        } catch (\Throwable $th) {            
            $this->emit('error-editar-size', 'ERROR');
        }
    }

    public function Update()
    {
        try {
            $this->validate();

            $size = Sizes::findOrFail($this->selected_id);
            $size->update([
                'size' => $this->size,
            ]);
            $this->emit('producto-creado');
            $this->emit('global-msg', "Tamaño actualizado correctamente");
            $this->emit('category-updated', 'Categoría Actualizada');
            $this->resetUI();
		} catch (\Exception $e) {
			$this->emit('sale-error',  $e->getMessage());
			throw $e;
		}
    }

    public function delete($id)
    {
        Sizes::find($id)->delete();
        session()->flash('message', 'Tamaño eliminado correctamente.');
    }

    protected $listeners = ['deleteSize' => 'Destroy'];


    public function Destroy(Sizes $size)
    {
        
        try {          
            $size->delete();
            $this->emit('size-delete', 'Se elimino tamaño');
        } catch (\Throwable $th) {
            if (strpos($th->getMessage(), 'foreign key constraint') !== false) {                
                $this->emit('error-delete-size', "El tamaño no puede ser eliminado, debido a que se encuentra ligado a productos.");
            } else {
                $this->emit('error-delete-size',  $th->getMessage());
            }
        }
    }    

    public function resetUI()
    {
        $this->size = '';
        $this->selected_id = 0;
    }
}
