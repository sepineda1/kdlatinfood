<div wire:ignore.self class="modal fade" id="PreModal" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>Presentación</b> | {{ $selected_id > 0 ? 'Edit' : 'Create' }}
                </h5>
                <button type="button" wire:loading class="btn btn-success close-btn text-info">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span style="color:white">PLEASE WAIT</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @csrf
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" wire:model.lazy="barcode" name="barcode" class="form-control"
                                placeholder="SKU">
                            @error('barcode') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div> 
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Costo</label>
                            <input type="text" data-type='cost' wire:model="cost" class="form-control"
                                placeholder="ej: 0.00">
                            @error('cost') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Box Items</label>
                            <input type="number" wire:model="stock_items" class="form-control" placeholder="ej: 30">
                            @error('stock_items') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Price</label>
                            <input type="text" data-type='precio' readonly wire:model="price" class="form-control"
                                placeholder="ej: 0.00">
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
                                    <option value="{{$sabor->id}}">{{$sabor->name}} - {{$sabor->barcode}} - {{$sabor->estado}}</option>
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

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" wire:click.prevent="resetUIPresentacion()" class="btn btn-dark close-btn text-info"
                    data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>
                @if($open_modal_create) 
                <button type="button" wire:click.prevent="StorePresentacion()"  onclick="loaderEx()" class="btn btn-dark close-btn text-info"
                   >
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Save
                </button>
                @else

                <button type="button" wire:click.prevent="UpdatePresentacion()"  onclick="loaderEx()" class="btn btn-dark close-btn text-info"
                   >
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Save Update
                </button>
                @endif
                
            </div>
        </div>
    </div>
</div>
