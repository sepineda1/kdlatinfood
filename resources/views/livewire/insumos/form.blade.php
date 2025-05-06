<div wire:ignore.self class="modal fade" id="modalINSUMO" tabindex="-1" role="dialog"
    style="backdrop-filter: blur(10px);" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">
                    <b>CREAR LOTE DE INSUMO</b>
                </h5>
                <h6 class="text-center text-warning" wire:loading>PLEASE WAIT</h6>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sabor">Sabor</label>
                           
                            <select wire:model="idSabor" wire:change="obtenerInsumo" id="sku" name="sku" class="form-control">
                                <option value="Elegir" disabled>Elegir</option>
                                @foreach($sabor as $data)
                                    <option value="{{$data->id}}">{{$data->nombre}}- Stock {{$data->stock}} lb</option>              
                                @endforeach
                            </select>
                            @error('idSabor') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" wire:model.lazy="Cantidad_Articulos" class="form-control"
                                placeholder="ej: 0">
                            @error('Cantidad_Articulos') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="stock">Unidad de Peso</label>
                            <select wire:model.lazy="peso" class="form-control mr-2">
                                <option value="Onzas">Onzas</option>
                                <option value="Libras">Libras</option>
                                <option value="Kilogramos">Kilogramos</option>
                            </select>
                            @error('peso') <span class="text-danger er">{{ $message}}</span>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigoBarras">Código de Barras</label>
                            <input type="text" wire:model.lazy="CodigoBarras" name="CodigoBarras" class="form-control"
                                placeholder="Generando Código de Barras..." disabled wire:loading.attr="disabled"
                                id="CodigoBarras">                            
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fechaVencimiento">Fecha de Vencimiento</label>
                            <input type="date" wire:model.lazy="Fecha_Vencimiento" class="form-control" readonly>                            
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user">User</label>
                            <input type="text" wire:model.lazy="User" class="form-control" placeholder="{{$User}}" disabled>                            
                        </div>
                    </div>
                </div>

           
                
            </div>

            <div class="modal-footer">

                <button type="button" wire:click.prevent="resetUI()" class="btn btn-dark close-btn text-info"
                    data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>


                @if($selected_id < 1) <button type="button" wire:click.prevent="Store()" onclick="loaderSave()"
                    class="btn btn-dark close-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-folder">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Save
                    </button>

                    @else
                    <button type="button" wire:click.prevent="Update()" class="btn btn-dark close-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-check-circle">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Update
                    </button>

                    @endif
                    @push('scripts')
                    <script>
                    window.livewire.on('producto-creado', () => {
                        swal.close();
                    });
                    </script>
                    @endpush
                    <script>
                    function loader() {
                        swal({
                            title: 'Actualizando datos',
                            text: 'Por favor, espere...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            onOpen: () => {
                                swal.showLoading();

                                window.livewire.on('producto-creado', () => {
                                    swal.close();
                                });

                                 // Cierra el cuadro y muestra un error si ocurre un problema
                                window.livewire.on('sale-error', (errorMessage) => {
                                    swal.close(); // Cierra el diálogo actual
                                    swal({
                                        icon: 'error',
                                        title: 'Error al Actualizar',
                                        text: errorMessage || 'Ocurrió un error inesperado.',
                                        confirmButtonText: 'Cerrar'
                                    });
                                });

                            }
                        });
                    }

                    function loaderSave() {
                        swal({
                            title: 'Guardando datos',
                            text: 'Por favor, espere...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            onOpen: () => {
                                swal.showLoading();

                                window.livewire.on('producto-creado', () => {
                                    swal.close();
                                });
                                 // Cierra el cuadro y muestra un error si ocurre un problema
                                window.livewire.on('sale-error', (errorMessage) => {
                                    swal.close(); // Cierra el diálogo actual
                                    swal({
                                        icon: 'error',
                                        title: 'Error al Guardar',
                                        text: errorMessage || 'Ocurrió un error inesperado.',
                                        confirmButtonText: 'Cerrar'
                                    });
                                });
                            }
                        });
                    }
                    </script>

            </div>
        </div>
    </div>
</div>