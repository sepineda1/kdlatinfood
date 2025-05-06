<style>
    .modal-dialog.modal-fullscreen {
        width: 95%;
        height: 95%;
        margin: 0;
        max-width: none;
        position: absolute;
        left: 2.5%;
        top: 2.5%;
        transform: translate(-50%, -50%);
    }
</style>

<div wire:ignore.self class="modal fade" id="theModal" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>{{ $componentName }}</b> | {{ $selected_id > 0 ? 'Edit' : 'Create' }}
                </h5>


                <button type="button" wire:loading class="btn btn-success close-btn text-info">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span style="color:white">PLEASE WAIT</span>
                </button>

            </div>
            <div class="modal-body">


                <div class="row">
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Usuario</label>
                            <input type="text" wire:model.lazy="User" class="form-control"
                                placeholder="{{ $User }}" value="{{ $User }}" readonly>
                            @error('User')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Fecha de Vencimiento</label>
                            <input type="date" wire:model.lazy="Fecha_Vencimiento"
                                placeholder="{{ $Fecha_Vencimiento }}" class="form-control" readonly>
                            @error('Fecha_Vencimiento')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Sabor</label>
                            <select name="Sabor" wire:model="Sabor" onchange="loaderCarga()" class="form-control" data-live-search="true">
                                <option value="Elegir" disabled>Elegir</option>
                                @foreach ($sabor as $sabor)
                                    <option value="{{ $sabor->id }}">{{ $sabor->nombre }}</option>
                                @endforeach
                            </select>
                            @error('Sabor')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-8">
                        <div class="form-group">
                            <label>Lote</label>
                            <select name="insumo" wire:model.lazy="LoteInsumo" class="form-control" onchange="loaderCarga()">
                                <option value="Elegir" disabled>Elegir</option>
                                @foreach ($insumo as $lotes)
                                    <option value="{{ $lotes->id }}-{{ $lotes->CodigoBarras }}">
                                        {{ $lotes->CodigoBarras }} - {{ $lotes->Fecha_Vencimiento }} -
                                        Stock: {{ $lotes->Cantidad_Articulos }} (Libras) </option>
                                @endforeach
                            </select>
                            @error('LoteInsumo')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror


                        </div>
                    </div>
                </div>

                <div id="subform-table">
                    <h5 class="title text-center">
                        <b></b> Final Product
                    </h5>
                    <div class="row">
                        <!-- ... -->
                        <div class="col-sm-12">
                            <!-- ... -->
                            <div class="table-responsive" wire:key="subform-table">
                                <table class="table table-bordered table-striped mt-1">
                                    <thead class="text-white" style="background: #FF5100">
                                        <tr>
                                            <th class="table-th text-white text-center">SKU</th>
                                            <th class="table-th text-white text-center">Libra Consumo</th>
                                            <th class="table-th text-white text-center">PYR</th>
                                            <th class="table-th text-white text-center">Unidades X Caja</th>
                                            <th class="table-th text-white text-center">Cajas Maximas</th>

                                            <th class="table-th text-white text-center">Cantidad de Cajas</th>
                                            <th class="table-th text-white text-center">Total de Libras</th>
                                            <th class="table-th text-white text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($subform)
                                            @foreach ($subform as $index => $item)
                                                <tr wire:key="subform-{{ $index }}">
                                                    <td class="text-center">
                                                        <span class="alert-warning p-1"><i class="fas fa-exclamation-circle"></i> Solo aparecerán aquellos productos que son VISIBLES y que Tienen KEY asignadas.</span>
                                                        <select name="product[]"
                                                            wire:model="subform.{{ $index }}.BAR"
                                                            class="form-control mt-2" onchange="loaderCarga()" >
                                                           
                                                            <option value="Elegir" disabled>Elegir</option>
                                                            @foreach ($presentacion as $pre)
                                                                <!--<option value="{{ $pre->id }}">{{ $pre->barcode }} - {{ $pre->product->name }} {{ $pre->size->size }} - {{ $pre->product->estado }}
                                                    - Stock: {{ $pre->tam1 == 0 ? 0 : round($pre->stock_items / $pre->tam1) }} Boxes</option>-->
                                                                @php
                                                                    $chicken = $pre->consumoPorSabor($Sabor);
                                                                    $libra_consumo = isset($chicken->libra_consumo)
                                                                        ? $chicken->getConsumoEnLibras()
                                                                        : 0;
                                                                @endphp
                                                                <option data-consume="{{ $libra_consumo }}"
                                                                    value="{{ $pre->id }}">{{ $pre->barcode }} -
                                                                    {{ $pre->product->name }} {{ $pre->size->size }} -
                                                                    {{ $pre->product->estado }}
                                                                    - Stock: {{ $pre->stock_box }} Boxes </option>
                                                            @endforeach
                                                        </select>
                                                        @error("subform.{$index}.BAR")
                                                            <span class="text-danger er">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                    <td class="text-center">
                                                        <p><b>{{ $subform[$index]['libra_consumo'] ?? '' }}</b></p>
                                                    </td>
                                                    <td class="text-center">
                                                        <p><b>{{ $subform[$index]['PYR'] ?? '' }}</b></p>
                                                    </td>
                                                    <td class="text-center">
                                                        <p><b>{{ $subform[$index]['stock_items'] ?? '' }} </b></p>
                                                    </td>
                                                    <td class="text-center">
                                                        <p><b>{{ $subform[$index]['MAX'] ?? '' }} </b></p>
                                                    </td>

                                                    <td class="text-center">
                                                        <input type="text" name="cantidad[]" onchange="loaderCarga()"
                                                            wire:model.lazy="subform.{{ $index }}.CANT"
                                                            class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                                                        @error("subform.{$index}.CANT")
                                                            <span class="text-danger er">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                    <td class="text-center">
                                                        <p><b class="sum_total{{ $index }}">{{ $subform[$index]['total_libras'] . ' Libras' ?? '' }}
                                                            </b></p>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-danger mb-2 mr-2 btn-rounded"
                                                            wire:click.prevent="removeItem({{ $index }})"
                                                            onclick="loader()">
                                                            Eliminar
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>

                                </table>
                            </div>
                            <button class="btn btn-primary mb-2 mr-2 btn-rounded" wire:click.prevent="addItem"
                                onclick="loader()">Añadir más</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end text-white">
                    <div class="p-3 rounded " style="background: #FF5100">
                        <b> Stock Restante : {{ $stockReal }} Libras</b>
                    </div>

                </div>
                <script>
                    document.addEventListener('livewire:load', function() {
                        Livewire.on('tableRendered', function() {
                            // Encontrar el contenedor de la tabla del subformulario dentro de la modal
                            var subformTable = document.querySelector(' #subform-table');

                            // Forzar una actualización de Livewire solo para la tabla del subformulario
                            Livewire.find(subformTable.getAttribute('wire:id')).call('render');
                        });
                    });
                </script>

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
                            title: 'Actualizando',
                            text: 'Por favor, espere...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            onOpen: () => {
                                swal.showLoading();

                                window.livewire.on('producto-creado', () => {
                                    swal.close();
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
                            }
                        });
                    }
                </script>

            </div>
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
                        title: 'Cargando datos',
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

                function loaderCarga() {
                    swal({
                        title: 'Cargando datos',
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

                            // Cierra el cuadro si el producto se crea correctamente
                            window.livewire.on('producto-creado', () => {
                                swal.close();
                            });

                            // Cierra el cuadro y muestra un error si ocurre un problema
                            window.livewire.on('sale-error', (errorMessage) => {
                                swal.close(); // Cierra el diálogo actual
                                swal({
                                    icon: 'error',
                                    title: 'Error al guardar',
                                    text: errorMessage || 'Ocurrió un error inesperado.',
                                    confirmButtonText: 'Cerrar'
                                });
                            });
                        }
                    });
                }
            </script>

            <script>
                document.addEventListener('livewire:load', function() {

                    window.livewire.on('show-loader', () => {
                        swal({
                            title: 'Cargando datos',
                            text: 'Por favor espere...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                swal.showLoading();
                            }
                        });
                    });

                    window.livewire.on('hide-loader', () => {
                        swal.close();
                    });

                });
            </script>

            <div class="modal-footer">

                <button type="button" wire:click.prevent="resetUI()" class="btn btn-dark close-btn text-info"
                    data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>



                <button type="button" wire:click.prevent="closeModal()"
                    class="btn btn-dark close-btn text-info d-none" data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>



                @if ($selected_id < 1)
                    <button type="button" wire:click.prevent="Store()" onclick="loaderSave()"
                        class="btn btn-warning mb-2 mr-2 btn-rounded close-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-folder">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                            </path>
                        </svg>
                        Save
                    </button>
                @else
                    <button type="button" wire:click.prevent="Update()" onclick="loader()"
                        class="btn btn-outline-primary btn-rounded mb-2 close-modal">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Update
                    </button>
                @endif


            </div>
        </div>
    </div>
</div>
