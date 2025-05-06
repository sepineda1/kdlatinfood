<div class="row sales layout-top-spacing">

    <div class="col-sm-12">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h4 class="card-title">
                    <b>{{$componentName}} | LIST</b>
                </h4>
                <ul class="tabs tab-pills">
                    @can('Category_Create')
                        <li>
                            <a href="javascript:void(0)" onclick="resetUI()" class="btn btn-primary mb-2 mr-2 btn-rounded" data-toggle="modal"
                                data-target="#theModal">Add</a>
                        </li>
                    @endcan
                </ul>
            </div>
           

            <div class="widget-content">
                <!-- Campo de búsqueda -->
            <div class="input-group mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar productos...">
            </div>

                <div class="table-responsive">
                    <table class="table table-bordered table striped mt-1">
                        <thead class="text-white" style="background: #FF5100; border-radius: 10px;">
                            <tr>
                                <th class="table-th text-white text-center">SKU</th>
                                <th class="table-th text-white text-center">Cost</th>
                                <th class="table-th text-white text-center">Box Items</th>
                                <th class="table-th text-white text-center">Price x Box</th>
                                <th class="table-th text-white text-center">Stock Box</th>
                                <th class="table-th text-white text-center">Min. Stock</th>
                                <th class="table-th text-white text-center">dependent product</th>
                                <th class="table-th text-white text-center">size</th>
                                <th class="table-th text-white text-center">State</th>
                                <th class="table-th text-white text-center">Actions</th>
                                <th class="table-th text-white text-center">QR</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">

                            @foreach($presentacion as $pre)
                                <tr>

                                    <td class="text-center">
                                        <h6>{{$pre->barcode}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{ number_format(round($pre->costo,2), 2, '.', '') }}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{$pre->stock_items}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{ number_format(round($pre->price,2), 2, '.', '') }}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6 class="{{$pre->stock_box <= $pre->alerts ? 'text-danger' : '' }}">{{$pre->stock_box}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{$pre->alerts}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{$pre->name}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{$pre->size}}</h6>
                                    </td>
                                    <td class="text-center">
                                        <h6>{{$pre->estado}}</h6>
                                    </td>
                                   
                                    <td class="text-center">
                                        @role('Admin')
                                            @can('Category_Update')
                                      
                                            <a href="javascript:void(0)" onclick="loader()" wire:click="Edit({{$pre->id}})"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                </svg>
                                            </a>
                                         
                                            @endcan

                                            @if ($pre->visible === 'no')
                                            <a href="javascript:void(0)"
                                                wire:click.prevent="novisible({{$pre->id}})"
                                                onclick="loader()"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                title="Publicar">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path
                                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                                    </path>
                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                </svg>
                                            </a>
                                            @else
                                            <a href="javascript:void(0)"
                                                wire:click.prevent="visible({{$pre->id}})"
                                                onclick="loader()"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                title="Ocultar">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                    </path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            @endif
                                            @if ( $pre->lot == 0)
                                                <a href="javascript:void(0)" title="Delete Product"
                                                onclick="ConfirmPre('{{$pre->id}}')"
                                                class="btn btn-danger mb-2 mr-2 btn-rounded" title="Delete">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                    </path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                </a>
                                            @endif

                                            {{--@if ( $pre->QB_id == "")
                                                <a onclick="loaderQuickbooks({{$pre->id}})" class="btn btn-success btn-rounded" href="#">
                                                    <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z">
                                                    </path>
                                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z">
                                                    </path>
                                                </svg>
                                                </a>
                                            @endif--}}
                                          
                                        @endcan
                                        

                                        @if ($pre->TieneKey == 'NO')
                                            <a class="btn btn-warning mb-2 mr-2 btn-rounded" title="Generate Key product"
                                                wire:click.prevent="GenerateKey('{{ $pre->id }}')" onclick="loaderEx()">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path
                                                        d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                    </path>
                                                </svg>
                                            </a>
                                        @endif

                                       
                                    </td>
                                        @if ($pre->TieneKey == 'SI' && $pre->lot > 0)
                                            <td>
                                                <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                    href="{{ url('detail/pdf/prod' . '/' . $pre->id) }}" title="print"
                                                    target="_blank" style="background:#f39022;">
                                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                        class="css-i6dzq1">
                                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                        <path
                                                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                        </path>
                                                        <rect x="6" y="14" width="12" height="8"></rect>
                                                    </svg></a>
                                            </td>
                                        @endif
                                       
                                       

                                
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

            </div>


        </div>


    </div>

    <!-- JavaScript -->
    <script>
        // Referencia al campo de búsqueda y la tabla
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');
        const rows = tableBody.getElementsByTagName('tr');

        // Evento para detectar cambios en el input
        searchInput.addEventListener('keyup', function () {
            const filter = searchInput.value.toLowerCase();

            // Iterar sobre cada fila de la tabla
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let match = false;

                // Iterar sobre cada celda de la fila
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }

                // Mostrar u ocultar la fila según el resultado
                rows[i].style.display = match ? '' : 'none';
            }
        });
    </script>

    @include('livewire.presentaciones.form')
</div>

@push('scripts')
    <script>
        window.livewire.on('producto-creado', () => {
            swal.close();
        });
    </script>
@endpush
<script>

    function loaderQuickbooks(id) {
        swal({
            title: 'Creando producto en Quickbooks',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.emit('Quickboks', id);
                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });
    }

    
    document.addEventListener('DOMContentLoaded', function () {

        window.livewire.on('show-modal', msg => {
            $('#theModal').modal('show')
        });
        window.livewire.on('hide-modal', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('category-added', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('category-updated', msg => {
            $('#theModal').modal('hide')
        });


    });

    function ConfirmPre(id) {
        swal({
            title: 'CONFIRM',
            text: 'CONFIRM DELETE THIS REG?',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar'
        }).then(function(result) {
            if (result.value) {
                window.livewire.emit('deletePre', id)
                swal.close()
            }

        })
    }


    function Confirm(id) {

        swal({
            title: 'CONFIRMAR',
            text: '¿CONFIRMAS ELIMINAR EL REGISTRO?',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar'
        }).then(function (result) {
            if (result.value) {
                window.livewire.emit('deleteRow', id)
                swal.close()
            }

        })
    }

    function loader() {
                                                        swal({
                                                            title: 'Cargando datos',
                                                            text: 'Por favor, espere...',
                                                            allowOutsideClick: false,
                                                            allowEscapeKey: false,
                                                            showConfirmButton: false,
                                                            onOpen: () => {
                                                                swal.showLoading();

                                                                window.livewire.on('producto-creado',
                                                            () => {
                                                                    swal.close();
                                                                });
                                                               
                                                            }
                                                        });
                                                    }


</script>