<div>
  
 
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Producto:</strong> {{ $presentacion->product->name ?? '' }}</p>
            <p><strong>Tamaño:</strong> {{ $presentacion->size->size ?? '' }}</p>
            <p><strong>Estado:</strong> {{ $presentacion->product->estado ?? '' }}</p>
            <p><strong>SKU:</strong> {{ $presentacion->barcode }}</p>
        </div>
    </div>

    <div class="form-inline mb-2">
        <select wire:model="sabor_id" class="form-control mr-2">
            <option value="Elegir">Elegir sabor</option>
            @foreach($sabores as $sabor)
                @if ($presentacion->product->sabor_id == $sabor->id)
                    <option value="{{ $sabor->id }}">{{ $sabor->nombre }} - {{ $sabor->stock }} (Stock)</option>
                @endif
            @endforeach
        </select>

        <input type="number" wire:model="libra_consumo" class="form-control mr-2" placeholder="Cantidad" step="0.01">

        <select wire:model="peso" class="form-control mr-2">
            <option value="Onzas">Onzas</option>
            <option value="Libras">Libras</option>
            <option value="Kilogramos">Kilogramos</option>
        </select>

        @if($selected_id)
            <button wire:click="update" class="btn btn-warning btn-rounded">Actualizar</button>
        @else
            <button wire:click="store" class="btn btn-warning btn-rounded">Agregar</button>
        @endif
    </div>

    <table class="table table-bordered">
        <thead style="background: #FF5100;">
            <tr class="text-center">
                <th class="text-white">Sabor</th>
                <th class="text-white">Cantidad</th>
                <th class="text-white">Stock Actual (LB)</th>
                <th class="text-white">PYR</th>
                <th class="text-white">Opciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consumos as $c)
            <tr class="text-center">
                <td>{{ $c->sabor->nombre }}</td>
                <td>{{ $c->libra_consumo }} {{ $c->peso }} / {{$c->getConsumoEnLibras()}} Libras</td>
                <td>{{ $c->sabor->stock }}</td>
                <td>{{ $c->getPYR() }} Productos Proyectados </td>
                <td>
                    <button wire:click="edit({{ $c->id }})" class="btn btn-sm btn-warning btn-rounded"><i class="fas fa-pencil-alt"></i></button>
                    <button wire:click="destroy({{ $c->id }})" class="btn btn-sm btn-danger btn-rounded"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
   
</div>

