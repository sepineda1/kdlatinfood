@include('common.modalHead')

<div class="row">
    @csrf


    <!-- <div class="col-sm-12 col-md-8">
        <div class="form-group">
            <label>Descripcion</label>
            <input type="text" wire:model.lazy="descripcion" class="form-control " placeholder="ej: descripcion"
                autofocus>
            @error('descripcion') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div> -->


    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>SKU</label>
            <input type="text" wire:model.lazy="barcode" name="barcode" class="form-control" placeholder="SKU">
            @error('barcode') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>



    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>Costo</label>
            <input type="text" data-type='cost' wire:model.lazy="cost" class="form-control" placeholder="ej: 0.00">
            @error('cost') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>
    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>Box Items</label>
            <input type="number" wire:model.lazy="stock_items" class="form-control" placeholder="ej: 30">
            @error('stock_items') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>

    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>Price</label>
            <input type="text" data-type='precio'  readonly  wire:model="price" class="form-control" placeholder="ej: 0.00">
            @error('price') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>

    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>stock of boxes</label>
            <input type="number" wire:model.lazy="stock_box" class="form-control" placeholder="ej: 0">
            @error('stock_box') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>

    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>Alert</label>
            <input type="number" wire:model.lazy="alerts" class="form-control" placeholder="ej: 10">
            @error('alerts') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="form-group">
            <label>Producto Relacionado</label>
            <select wire:model='products_id' class="form-control ">
                <option value="Elegir" disabled>Elegir</option>
                @foreach($prod as $sabor)
                    <option value="{{$sabor->id}}" {{ $products_id == $sabor->id ? 'selected' : '' }} >{{$sabor->name}} - {{$sabor->barcode}}</option>
                @endforeach
            </select>
            @error('products_id') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>
    <div class="col-sm-12 col-md-4">
        <div class="form-group">
            <label>Size</label>
            <select wire:model='sizes_id' class="form-control ">
                <option value="Elegir" disabled>Elegir</option>
                @foreach($sizes as $size)
                    <option value="{{$size->id}}">{{$size->size}}</option>
                @endforeach
            </select>
            @error('sizes_id') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div>



    <!-- 
    <div class="col-sm-12 col-md-8">
        <div class="form-group custom-file">
            <input type="file" class="custom-file-input" wire:model="image" accept="image/x-png, image/gif, image/jpeg">
            <label class="custom-file-label">Imágen {{$image}}</label>
            @error('image') <span class="text-danger er">{{ $message}}</span>@enderror
        </div>
    </div> -->



</div>




@include('common.modalFooter')