<div>



    @push('scripts')
        <script>
            window.livewire.on('producto-creado', () => {
                swal.close();
            });
        </script>
    @endpush





    <script type="text/javascript">
        function Borrar(clienteId) {
            swal({
                title: '¿CONFIRM DELETE THIS REG? ',
                text: 'THIS ACTION CAN BE REVERTED',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                cancelButtonColor: '#fff',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Aceptar'
            }).then(function(result) {
                if (result.value) {

                    window.livewire.emit('deleteRow', clienteId);
                    swal.close();
                    loaderCliente();
                }
            });
        }

        function Update(clienteId) {
            swal({
                title: '¿CONFIRM DELETE THIS REG? ',
                text: 'THIS ACTION CAN BE REVERTED',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                cancelButtonColor: '#fff',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Aceptar'
            }).then(function(result) {
                if (result.value) {

                    window.livewire.emit('Edit', clienteId);
                    swal.close();
                    loaderCliente();
                }
            });
        }

        function loaderCliente() {
            swal({
                title: 'Borrando Cliente',
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

        document.addEventListener('livewire:load', function() {
            window.livewire.on('cliente-has-sales', function(message) {
                swal({
                    title: 'Error',
                    text: message,
                    type: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    </script>

    <div>
        <style type="text/css">
            .izquierda {
                width: 50%;
                float: left;
            }

            .large-text {
                font-size: 1.2rem;
                /* Ajusta el tamaño de fuente según tu preferencia */
            }

            .derecha {
                width: 50%;
                float: right;
            }

            .font-mini {
                font-size: 12px !important;

            }

            .btn-group .btn {
                padding: 5px 8px;
                /* Reduce el tamaño del relleno */
                font-size: 12px;
                /* Tamaño de la fuente más pequeño */
                border-radius: 4px;
                /* Bordes ligeramente redondeados */
                line-height: 1;
                /* Reduce la altura del botón */
                display: inline-flex;
                /* Asegura el contenido centrado */
                justify-content: center;
                align-items: center;
            }

            .btn-group .btn a {
                text-decoration: none;
                /* Quita el subrayado de los enlaces */
            }

            .btn-group i {
                font-size: 12px !important;
                /* Ajusta el tamaño de los íconos */
            }


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

            .form-price {
                border: none !important;
                font-size: 20px !important;
                text-align: center;
            }

            .card-space {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                /* Número máximo de líneas */
                -webkit-box-orient: vertical !important;
                overflow: hidden;
                text-overflow: ellipsis;
                height: 2.6em;
                /* Fuerza dos líneas visuales (ajusta según tamaño de fuente) */
                line-height: 1.3em;
                /* Ajusta según necesidad visual */
            }

            .toggle-container {
                display: flex;
                align-items: center;
                cursor: pointer !important;
            }

            .toggle-checkbox {
                display: none;
            }

            .toggle-label {
                width: 50px;
                height: 28px;
                background-color: #ccc;
                border-radius: 30px;
                position: relative;
                transition: background-color 0.3s;
                cursor: pointer !important;
            }

            .toggle-label::after {
                content: "";
                position: absolute;
                width: 22px;
                height: 22px;
                background-color: white;
                border-radius: 50%;
                top: 3px;
                left: 3px;
                transition: transform 0.3s;
            }

            .toggle-checkbox:checked+.toggle-label {
                background-color: #ff5100;
                /* naranja */
            }

            .toggle-checkbox:checked+.toggle-label::after {
                transform: translateX(22px);
            }

            .tabla-profesional {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                background-color: #fff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            }

            .tabla-profesional thead {
                background-color: #2c3e50;
                color: white;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .tabla-profesional th,
            .tabla-profesional td {
                padding: 16px 20px;
                text-align: left;
            }

            .tabla-profesional tbody tr {
                border-top: 1px solid #e0e0e0;
            }

            .tabla-profesional tbody tr:hover {
                background-color: #f1f1f1;
                cursor: pointer;
            }

            .tabla-profesional td {
                color: #333;
                font-size: 15px;
            }

            .tabla-profesional th {
                font-size: 14px;
                font-weight: 600;
            }
        </style>

        <div class="row sales layout-top-spacing">

            <div class="col-sm-12 ">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h4 class="card-title"><b>Lista de</b> | Clientes</b></h4>
                        <ul class="tabs tab-pills">

                            <li><a href="javascript:void(0);" class="btn btn-primary mb-2 mr-2 btn-rounded"
                                    data-toggle="modal" data-target="#theModal">Nuevo Cliente</a></li>
                        </ul>
                    </div>
                    @include('common.searchbox')
                    <style>

                    </style>

                    <div class="widget-content">
                        <div class="row">
                            @foreach ($data as $cliente)
                                <div class="col-md-2">
                                    <div class="card"
                                        style="border-radius: 15px;background-color:#f9f9f9;border:none !important;">
                                        <div class="card-body">
                                            @php
                                                $path = 'customers/' . $cliente->image;
                                            @endphp
                                            <img width="100px" height="100px"
                                                src="{{ Storage::disk('public')->exists($path) ? asset('storage/' . $path) : asset('storage/noimg.jpg') }}"
                                                class="text-center mb-4" style="border-radius:50%;"
                                                alt="Imagen del usuario">
                                            <h6 class="card-title card-space">{{ $cliente->name }}
                                                {{ $cliente->last_name }}</h6>
                                            <p class="card-text">
                                            <p class="font-mini d-none"><strong>Email: </strong>{{ $cliente->email }}
                                            </p>
                                            <p class="font-mini"><strong>Balance: </strong><span
                                                    style="color:#f39022 !important;">$ {{ $cliente->saldo }}</span></p>
                                            <p class="font-mini d-none"><strong>Address:</strong>
                                                {{ $cliente->address }}</p>
                                            <p class="font-mini d-none"><strong>Number Phone:</strong>
                                                {{ $cliente->phone }}</p>
                                            </p>
                                            <button onclick="loader()"
                                                wire:click.prevent="putSales({{ $cliente->id }})"
                                                class="btn btn-sm d-flex" style="background: #ffff !important;"><span><i
                                                        class="fas fa-dollar-sign"></i> Details</span> <span
                                                    class="badge badge-sm badge-success ml-2">{{ $cliente->sale->count() }}</span></button>
                                        </div>
                                        <div class="card-footer d-flex justify-content-center">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-secondary">
                                                    <a href="{{ url('historial/pdf' . '/' . $cliente->id) }}"
                                                        class="text-white"><i class="fas fa-print"></i></a>
                                                </label>
                                                <label class="btn btn-danger" onclick="Borrar('{{ $cliente->id }}')">
                                                    <a href="javascript:void(0)" class="text-white"><i
                                                            class="fas fa-trash-alt"></i></a>
                                                </label>
                                                <label class="btn btn-warning" onclick="cleanAddres()"
                                                    wire:click.prevent="Edit({{ $cliente->id }})">
                                                    <a href="javascript:void(0)" class="text-white"> <i
                                                            class="fas fa-user-edit"></i> </a>
                                                </label>
                                                <label class="btn btn-success"
                                                    onclick="loaderQuickbooks('{{ $cliente->id }}')">
                                                    <a href="javascript:void(0)" class="text-white"><i
                                                            class="fas fa-book-open"></i></a>
                                                </label>
                                                <label class="btn btn-primary" onclick="cleanInput()"
                                                    wire:click.prevent="discountsUser({{ $cliente->id }})">
                                                    <a href="javascript:void(0)" class="text-white"><i
                                                            class="fas fa-shopping-cart"></i></a>
                                                </label>
                                            </div>

                                            <!--<a href="{{ url('historial/pdf' . '/' . $cliente->id) }}"
                                                class="btn btn-warning btn-sm mb-1 mr-1 btn-rounded d-none" title="Print History"
                                                style="background:#f39022;" target="_blank">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                    <path
                                                        d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                    </path>
                                                    <rect x="6" y="14" width="12" height="8"></rect>
                                                </svg>
                                            </a>
                                            <a href="javascript:void(0)" onclick="Borrar('{{ $cliente->id }}')"
                                                class="btn btn-danger mb-1 mr-1 btn-rounded d-none" title="Delete">
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
                                            <a href="javascript:void(0)" wire:click.prevent="Edit({{ $cliente->id }})"
                                                class="btn btn-warning mb-1 mr-1 btn-rounded d-none" title="Edit">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M12 20h9"></path>
                                                    <path
                                                        d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z">
                                                    </path>
                                                </svg>
                                            </a>
                                          
                                            <a href="javascript:void(0)"
                                                onclick="loaderQuickbooks('{{ $cliente->id }}')"
                                                class="btn btn-success mb-1 mr-1 btn-rounded d-none" title="Crear Cliente
                                                en QuickBooks">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z">
                                                    </path>
                                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z">
                                                    </path>
                                                </svg>
                                            </a>-->
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="accordion">

                            @foreach ($data as $cliente)
                                <div class="card d-none">

                                    <div class="card-header" id="heading{{ $cliente->id }}">

                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-link" data-toggle="collapse"
                                                data-target="#collapse{{ $cliente->id }}" aria-expanded="true"
                                                aria-controls="collapse{{ $cliente->id }}">
                                                <div>
                                                    <h3>{{ $cliente->id }}. {{ $cliente->name }}
                                                        {{ $cliente->last_name }}</h3>
                                                </div>
                                            </button>
                                            <div class="ml-auto">
                                                <div class="text-right">
                                                    <p class="mb-0">Actions</p>
                                                    <a href="{{ url('historial/pdf' . '/' . $cliente->id) }}"
                                                        class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                        title="Print History" style="background:#f39022;"
                                                        target="_blank">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                            <path
                                                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                            </path>
                                                            <rect x="6" y="14" width="12" height="8"></rect>
                                                        </svg>
                                                    </a>
                                                    <a href="javascript:void(0)"
                                                        onclick="Borrar('{{ $cliente->id }}')"
                                                        class="btn btn-danger mb-2 mr-2 btn-rounded" title="Delete">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path
                                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                            </path>
                                                            <line x1="10" y1="11" x2="10"
                                                                y2="17"></line>
                                                            <line x1="14" y1="11" x2="14"
                                                                y2="17"></line>
                                                        </svg>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="loader()"
                                                        wire:click.prevent="Edit({{ $cliente->id }})"
                                                        class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <path d="M12 20h9"></path>
                                                            <path
                                                                d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z">
                                                            </path>
                                                        </svg>
                                                    </a>

                                                    <a href="javascript:void(0)"
                                                        onclick="loaderQuickbooks('{{ $cliente->id }}')"
                                                        class="btn btn-success mb-2 mr-2 btn-rounded"
                                                        title="Crear Cliente
                                                    en QuickBooks">
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

                                                </div>
                                            </div>
                                        </div>



                                    </div>
                                    <div class="contenedor">
                                        <div class="izquierda">

                                            <div id="collapse{{ $cliente->id }}" class="collapse"
                                                aria-labelledby="heading{{ $cliente->id }}"
                                                data-parent="#accordion">
                                                <div class="card-body">

                                                    <style>
                                                        .customer-profile {
                                                            display: flex;
                                                            align-items: center;
                                                            gap: 20px;
                                                        }

                                                        .customer-profile img {
                                                            border-radius: 50%;
                                                            height: 100px;
                                                            width: 100px;
                                                        }

                                                        .customer-details {
                                                            text-align: center;
                                                        }

                                                        .customer-details p {
                                                            font-size: 19px;
                                                            font-weight: bold;
                                                        }

                                                        hr {
                                                            border-top: 1px solid #ccc;
                                                        }
                                                    </style>

                                                    <div class="customer-profile">
                                                        @if ($cliente->firebase == 'si')
                                                            <img src="{{ $cliente->urlFirebase }}"
                                                                alt="imagen de ejemplo">
                                                        @elseif($cliente->image)
                                                            <img src="{{ asset('../storage/app/public/customers/' . $cliente->image) }}"
                                                                alt="imagen de ejemplo">
                                                        @else
                                                            <img src="{{ asset('../storage/app/public/noimg.jpg') }}"
                                                                alt="imagen de ejemplo">
                                                        @endif
                                                        <div class="customer-details">
                                                            <p>Costumer Details:</p>
                                                            <hr>
                                                            <p><strong>Full Name: </strong>{{ $cliente->name }}
                                                                {{ $cliente->last_name }} {{ $cliente->last_name2 }}
                                                            </p>
                                                            <p><strong>Email: </strong>{{ $cliente->email }}</p>
                                                            <p><strong>Balance: $</strong>{{ $cliente->saldo }}</p>
                                                            <p><strong>Address:</strong> {{ $cliente->address }}</p>
                                                            <p><strong>Number Phone:</strong> {{ $cliente->phone }}</p>
                                                        </div>
                                                    </div>

                                                </div>
                                                <td>

                                                </td>

                                            </div>

                                        </div>
                                        <style type="text/css">
                                            p {
                                                font-size: 19px;
                                                font-weight: bold;
                                            }
                                        </style>



                                        <div id="collapse{{ $cliente->id }}" class="collapse derecha"
                                            aria-labelledby="heading{{ $cliente->id }}" data-parent="#accordion">
                                            <br>
                                            <p class="text-center">Purchase Details</p>
                                            <hr>
                                            <div class="accordion" id="purchaseAccordion">
                                                <div class="card">
                                                    <div class="card-header" id="purchaseHeading">
                                                        <h4 class="card-title">
                                                            <button class="btn btn-link btn-block text-left"
                                                                type="button" data-toggle="collapse"
                                                                data-target="#purchaseCollapse" aria-expanded="false"
                                                                aria-controls="purchaseCollapse">
                                                                <h3 class="text-center">More Info:</h3>
                                                                <div class="d-flex align-items-center">
                                                                    <p class="mb-0">Total Sales</p>
                                                                    <span
                                                                        class="badge badge-success ml-2">{{ $cliente->sale->count() }}</span>
                                                                </div>
                                                            </button>
                                                        </h4>
                                                    </div>

                                                    <div id="purchaseCollapse" class="collapse"
                                                        aria-labelledby="purchaseHeading"
                                                        data-parent="#purchaseAccordion">
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped mt-1">
                                                                    <thead class="text-white"
                                                                        style="background: #FF5100">
                                                                        <tr>
                                                                            <th
                                                                                class="table-th text-center text-white">
                                                                                Sale
                                                                                ID</th>
                                                                            <th
                                                                                class="table-th text-center text-white">
                                                                                Total Spent</th>
                                                                            <th
                                                                                class="table-th text-center text-white">
                                                                                ITEMS</th>
                                                                            <th
                                                                                class="table-th text-center text-white">
                                                                                STATUS</th>
                                                                            <th
                                                                                class="table-th text-center text-white">
                                                                                DETAIL</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($cliente->sale as $venta)
                                                                            <tr>
                                                                                <td class="text-center">
                                                                                    <h6> {{ $venta->id }}</h6>
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <h6> $ {{ $venta->total }}</h6>
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <h6> {{ $venta->items }}</h6>
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <h6> {{ $venta->status }}</h6>
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <button
                                                                                        wire:click.prevent="getDetails({{ $venta->id }})"
                                                                                        class="btn btn-dark btn-sm">
                                                                                        <i class="fas fa-list"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>





                                        </div>
                                        <div style="clear: both;"></div>
                                    </div>
                                </div><br>
                            @endforeach
                            @include('livewire.clientes.form')
                            @include('livewire.clientes.sales-detail')
                            @include('livewire.clientes.info')
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
                                        title: 'Procesando cliente en Quickbooks',
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

                                function loader(id) {
                                    swal({
                                        title: 'Creando datos',
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
                                document.addEventListener('DOMContentLoaded', function() {

                                    window.livewire.on('cliente-added', msg => {
                                        $('#theModal').modal('hide') //agregar lote
                                    });

                                    window.livewire.on('cliente-edit', msg => {
                                        $('#theModal').modal('hide') //editar lote
                                    });
                                    window.livewire.on('cliente-delete', msg => {
                                        $('#theModal').modal('hide') //eliminar lote
                                    });

                                    window.livewire.on('modal-show', msg => {
                                        $('#theModal').modal('show')
                                    });
                                    window.livewire.on('show-modal', msg => {
                                        $('#modalDetails').modal('show')
                                    });
                                    window.livewire.on('modal-hide', msg => {
                                        $('#theModal').modal('hide')
                                    });


                                    window.livewire.on('hidden.bs.modal', msg => {
                                        $('.er').css('display', 'none')
                                    });


                                    window.livewire.on('mostrar-venta', msg => {
                                        $('#Modale').modal('show')
                                    });
                                    window.livewire.on('cerrar-venta', msg => {
                                        $('#Modale').modal('hide')
                                    });

                                    window.livewire.on('discount-show', msg => {
                                        $('#discountUser').modal('show')
                                    });
                                    window.livewire.on('discount-hide', msg => {
                                        $('#discountUser').modal('hide')
                                    });

                                })
                            </script>
                        </div>
                    </div>
                </div>
            </div>

        </div>





    </div>



    <div wire:ignore.self class="modal fade" id="Modale" tabindex="-1" role="dialog"
        style="backdrop-filter: blur(10px);">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header " style="background: #ff5100;">
                    <h5 class="modal-title text-white">
                        <b>Purchase Details</b>
                    </h5>
                    <button type="button" wire:loading class="btn btn-success close-btn text-info">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span style="color:white">PLEASE WAIT</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-2">
                                @if (!empty($user))
                                    @if ($user->firebase == 'si')
                                        <img style="border-radius:50%;" width="100%"
                                            src="{{ $user->urlFirebase }}" alt="imagen de ejemplo">
                                    @elseif($user->image)
                                        <img style="border-radius:50%;" width="100%"
                                            src="{{ asset('../storage/app/public/customers/' . $user->image) }}"
                                            alt="imagen de ejemplo">
                                    @else
                                        <img style="border-radius:50%;" width="100%"
                                            src="{{ asset('../storage/app/public/noimg.jpg') }}"
                                            alt="imagen de ejemplo">
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-8">
                                @if (!empty($user))
                                    <p>Email : {{ $user->email }}</p>
                                    <p><strong>Balance: $</strong>{{ $cliente->saldo }}</p>
                                    <p><strong>Address:</strong> {{ $cliente->address }}</p>
                                    <p><strong>Number Phone:</strong> {{ $cliente->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped mt-4">
                        <thead class="text-white" style="background: #FF5100">
                            <tr>
                                <th class="table-th text-center text-white">Sale
                                    ID</th>
                                <th class="table-th text-center text-white">
                                    Total Spent</th>
                                <th class="table-th text-center text-white">
                                    ITEMS</th>
                                <th class="table-th text-center text-white">
                                    STATUS</th>
                                <th class="table-th text-center text-white">
                                    DETAIL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($ventas))
                                @foreach ($ventas as $venta)
                                    <tr>
                                        <td class="text-center">
                                            <h6> {{ $venta->id }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <h6> $ {{ $venta->total }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <h6> {{ $venta->items }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <h6> {{ $venta->status }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="loader()"
                                                wire:click.prevent="getDetails({{ $venta->id }})"
                                                class="btn btn-dark btn-sm">
                                                <i class="fas fa-list"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark close-btn text-info" data-dismiss="modal">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Close
                    </button>

                </div>
            </div>
        </div>
    </div>

    <style>

    </style>

    <div wire:ignore.self class="modal fade" tabindex="-1" id="discountUser" role="dialog"
        style="backdrop-filter: blur(10px);">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header " style="background: #ff5100;">
                    <h5 class="modal-title text-white">
                        <b><i class="fas fa-percent"></i> <span class="p-2"
                                style="background: #f2814d;">Descuentos</span> Preferenciales</b>
                    </h5>
                    <button type="button" wire:loading class="btn btn-success close-btn text-info">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span style="color:white">PLEASE WAIT</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-4">
                                @if ($user)
                                    <div class="text-center" style="background: #f7f7f7;border-radius:15px;">
                                        <img style="width: 100px !important;border-radius:50%;"
                                            src="{{ $user->image != '' ? asset('../storage/app/public/customers/' . $user->image) : asset('../storage/app/public/users/sinfondo.jpg') }}"
                                            class="card-img-top mb-4" alt="...">
                                        <h5 class="card-title">{{ $user->id }}. {{ $user->name }}
                                            {{ $user->last_name }}</h5>
                                        <p class="card-text">
                                        <p class="font-mini"><strong>Email: </strong>{{ $user->email }}</p>
                                        <p class="font-mini"><strong>Balance: </strong><span
                                                style="color:#f39022 !important;">$ {{ $user->saldo }}</span></p>
                                        <p class="font-mini"><strong>Address:</strong> {{ $user->address }}</p>
                                        <p class="font-mini"><strong>Number Phone:</strong> {{ $user->phone }}</p>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <form action="form-inline">
                                    <div class="form-group">
                                        @if ($user)
                                            <input type="hidden" value="{{ $user->id }}"
                                                ire:model.lazy="input_user_id">
                                        @endif

                                        <div class="form-group mb-2">
                                            <label><i class="fas fa-search"></i> Buscar producto</label>
                                            <input type="text" id="searchPresentacion" class="form-control mt-1"
                                                placeholder="Escribe un producto...">
                                        </div>



                                        <div class="form-group mb-2">
                                            <label><i class="fas fa-weight-hanging"></i> Elija un producto</label>
                                            <select wire:model.lazy="input_presentacion_id" id="presentacion_select"
                                                class="form-control" size="6">
                                                <option value="">-- Seleccione un producto --</option>
                                                @foreach ($presentaciones as $item)
                                                    <option value="{{ $item->id }}"
                                                        data-price="{{ number_format($item->price, 2, '.', '') }}">
                                                        {{ $item->product->name }} {{ $item->size->size }}
                                                        {{ $item->product->estado }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for=""><i class="fas fa-percent"></i> Descuento</label>
                                        <input type="number" wire:model.lazy="input_discount" id="descuento"
                                            class="form-control form-price"
                                            style="background-color: #f8f7f7 !important;">
                                    </div>
                                    <div>
                                        <button class="btn w-100 text-white" wire:click.prevent="saveDiscount()"
                                            onclick="cleanInput()" style="background-color: #fa7233"><i
                                                class="fas fa-save"></i> Guardar Descuento</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background: #f7f7f7;border-radius:15px;">
                                    <div class="form-group ">
                                        <label class="text-dark font-weight-bold " for=""> Precio
                                            Normal</label>
                                        <input type="text" id="priceNormal" readonly
                                            class="form-control form-price text-dark bg-white"
                                            style="background-color: #ffff !important;">
                                    </div>
                                    <div class="form-group ">
                                        <label class="text-dark font-weight-bold" for=""> Precio con
                                            Descuento</label>
                                        <input type="text" id="priceDiscount" readonly
                                            class="form-control form-price text-dark " id="priceDiscount"
                                            style="background-color: #ffff !important;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="form-group">
                            <input type="text" id="searchDiscount" onkeyup="searchTable()"
                                style="border: none; background:#fffefe;box-shadow: 10px 10px 15px rgba(193, 192, 192, 0.3);"
                                class="form-control" placeholder="Buscar producto.">
                        </div>

                        <table class="table table-bordered table-striped mt-4">
                            <thead class="text-white" style="background: #ff5100;">
                                <tr>
                                    <th class="table-th text-center text-white">
                                        ID</th>
                                    <th class="table-th text-center text-white">
                                        Producto </th>
                                    <th class="table-th text-center text-white">
                                        Descuento (%)</th>
                                    <th class="table-th text-center text-white">
                                        Precio sin Descuento</th>
                                    <th class="table-th text-center text-white">
                                        Precio con Descuento</th>
                                    <th class="table-th text-center text-white">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tableDiscount">
                                @if (!empty($discounts))
                                    @foreach ($discounts as $item)
                                        <tr class="text-center">
                                            <td style="font-size: 17px;">{{ $item->id }}</td>
                                            <td style="font-size: 17px;">{{ $item->presentacion->product->name }}
                                                {{ $item->presentacion->size->size }}
                                                {{ $item->presentacion->product->estado }}</td>
                                            <td style="font-size: 17px;"><span class="text-white p-2 rounded"
                                                    style="background-color:#fa7233;">{{ $item->discount }}%</span>
                                            </td>
                                            <td style="font-size: 17px;">
                                                ${{ number_format(round($item->presentacion->price, 2), 2, '.', '') }}
                                            </td>
                                            <td style="font-size: 17px;">
                                                <b>${{ number_format(round($item->presentacion->price - ($item->presentacion->price * $item->discount) / 100, 2), 2, '.', '') }}</b>
                                            </td>
                                            <td>
                                                <button onclick="Confirm({{ $item->id }})"
                                                    class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark close-btn text-info" data-dismiss="modal">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Close
                    </button>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let zIndex = 1050; // Bootstrap modal default z-index

            // Maneja el evento cuando se abre un modal
            $('.modal').on('show.bs.modal', function() {
                zIndex += 10;
                $(this).css('z-index', zIndex);

                // Ajustar el backdrop
                setTimeout(() => {
                    $('.modal-backdrop').not('.stacked')
                        .css('z-index', zIndex - 1)
                        .addClass('stacked');
                }, 0);
            });

            // Maneja el evento cuando se cierra un modal
            $('.modal').on('hidden.bs.modal', function() {
                zIndex -= 10;
                if ($('.modal.show').length > 0) {
                    // Asegurarse de que el backdrop vuelva al modal activo
                    $('.modal-backdrop').css('z-index', zIndex - 1);
                }
            });
        });

        function buscarTarjetas(palabraClave) {
            // Selecciona todas las tarjetas del contenedor
            const tarjetas = document.querySelectorAll('.card');
            let resultados = [];

            // Itera sobre cada tarjeta
            tarjetas.forEach((tarjeta) => {
                const colMd = tarjeta.closest('.col-md-2'); // Selecciona el elemento padre con la clase .col-md-2

                // Verifica si el texto dentro de la tarjeta incluye la palabra clave
                if (tarjeta.innerText.toLowerCase().includes(palabraClave.toLowerCase())) {
                    resultados.push(tarjeta); // Agrega la tarjeta a los resultados
                    tarjeta.style.border = "2px solid #f39022"; // Resalta la tarjeta
                    if (colMd) {
                        colMd.style.display = "block"; // Muestra el contenedor padre si coincide
                    }
                } else {
                    tarjeta.style.border = ""; // Restablece el borde
                    if (colMd) {
                        colMd.style.display = "none"; // Oculta el contenedor padre si no hay coincidencia
                    }
                }
            });

            // Retorna los resultados
            if (resultados.length > 0) {
                console.log(`Se encontraron ${resultados.length} tarjeta(s) que coinciden:`, resultados);
            } else {
                console.log("No se encontraron coincidencias.");
            }
        }


        document.getElementById('buscarInput').addEventListener('input', function() {
            const palabraClave = this.value; // Obtiene el valor del input
            buscarTarjetas(palabraClave); // Llama a la función de búsqueda
        });


        function applyDiscount(priceInputId, discountInputId, priceDiscountOutputId) {
            // Obtener los elementos por sus IDs
            var priceInput = document.getElementById(priceInputId);
            var discountInput = document.getElementById(discountInputId);
            var priceDiscountOutput = document.getElementById(priceDiscountOutputId);

            // Validar que los elementos existan
            if (!priceInput || !discountInput || !priceDiscountOutput) {
                console.error('Uno o más elementos no se encontraron.');
                return;
            }

            // Obtener valores y calcular el descuento
            var priceNormal = parseFloat(priceInput.value) || 0; // Obtener el precio original
            var discount = parseFloat(discountInput.value) || 0; // Obtener el porcentaje de descuento

            // Calcular el precio con descuento
            var discountedPrice = priceNormal - (priceNormal * discount / 100);

            // Mostrar el precio con descuento en el input correspondiente
            priceDiscountOutput.value = discountedPrice.toFixed(2); // Limitar a dos decimales
        }



        document.addEventListener('DOMContentLoaded', function() {
            var selectElement = document.getElementById('presentacion_select');
            var priceInput = document.getElementById('priceNormal');
            var discountInput = document.getElementById('descuento');
            var priceDiscountInput = document.getElementById('priceDiscount');

            // Función para actualizar el precio cuando se cambia la opción
            selectElement.addEventListener('change', function() {
                // Obtener el atributo 'price' del option seleccionado
                var selectedOption = selectElement.options[selectElement.selectedIndex];
                var price = selectedOption.getAttribute('price');

                // Asignar el valor del precio al input
                priceInput.value = price;
                applyDiscount('priceNormal', 'descuento', 'priceDiscount');
            });

            // Función para calcular el descuento
            discountInput.addEventListener('input', function() {
                applyDiscount('priceNormal', 'descuento', 'priceDiscount');
            });



        });


        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchDiscount");
            filter = input.value.toLowerCase();
            table = document.getElementById("tableDiscount");
            tr = table.getElementsByTagName("tr");

            // Itera a través de todas las filas de la tabla y oculta las que no coinciden con la búsqueda
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                if (td.length > 0) {
                    // Recorre las columnas de cada fila
                    var showRow = false;
                    for (var j = 0; j < td.length; j++) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            showRow = true; // Si la columna coincide con la búsqueda, muestra la fila
                        }
                    }
                    // Muestra u oculta la fila dependiendo si hay coincidencia
                    if (showRow) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }


        function Confirm(id) {
            swal({
                title: 'CHECK',
                text: '¿CONFIRM DELETE THIS REG?',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                cancelButtonColor: '#fff',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Aceptar'
            }).then(function(result) {

                if (result.value) {
                    window.livewire.emit('deleteDiscount', id)

                    swal.close()
                }
            })
        }

        function cleanInput() {
            var selectElement = document.getElementById('presentacion_select');
            var priceInput = document.getElementById('priceNormal');
            var discountInput = document.getElementById('descuento');
            var priceDiscountInput = document.getElementById('priceDiscount');

            priceInput.value = '';
            discountInput.value = '';
            priceDiscountInput.value = '';
            selectElement.selectedIndex = 0;
            loader();
        }

        function cleanLoader() {

            var selectElement = document.getElementById('presentacion_select');
            var priceInput = document.getElementById('priceNormal');
            var discountInput = document.getElementById('descuento');
            var priceDiscountInput = document.getElementById('priceDiscount');

            if (priceInput.value != '' && discountInput.value != '' && priceDiscountInput.value != '' && selectElement
                .selectedIndex != 0) {
                loader();

                priceInput.value = '';
                discountInput.value = '';
                priceDiscountInput.value = '';
                selectElement.selectedIndex = 0;
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchPresentacion');
            const selectEl = document.getElementById('presentacion_select');
            const priceInput = document.getElementById('priceNormal');
            const discountEl = document.getElementById('descuento');
            const outDiscount = document.getElementById('priceDiscount');
            const maxRows = 6;

            // Filtrado en vivo
            searchInput.addEventListener('input', () => {
                const term = searchInput.value.toLowerCase();
                let count = 0;
                Array.from(selectEl.options).forEach(opt => {
                    if (opt.value === '' || opt.text.toLowerCase().includes(term)) {
                        opt.style.display = '';
                        count++;
                    } else {
                        opt.style.display = 'none';
                    }
                });
                selectEl.size = Math.min(count, maxRows + 1);
                // resetear precios si la selección desaparece
                const sel = selectEl.selectedIndex;
                if (sel > -1 && selectEl.options[sel].style.display === 'none') {
                    selectEl.selectedIndex = 0;
                    clearPrices();
                }
            });

            // Al cambiar selección
            selectEl.addEventListener('change', () => updatePrice());

            // Al cambiar descuento
            discountEl.addEventListener('input', () => applyDiscount());

            function updatePrice() {
                const opt = selectEl.options[selectEl.selectedIndex];
                const price = parseFloat(opt.getAttribute('data-price') || 0);
                priceInput.value = price.toFixed(2);
                applyDiscount();
            }

            function applyDiscount() {
                const p = parseFloat(priceInput.value) || 0;
                const d = parseFloat(discountEl.value) || 0;
                outDiscount.value = (p - (p * d / 100)).toFixed(2);
            }

            function clearPrices() {
                priceInput.value = '';
                discountEl.value = '';
                outDiscount.value = '';
            }

            // Emitir evento Livewire
            window.saveDiscountJS = function() {
                const id = selectEl.value;
                const d = discountEl.value;
                if (!id || !d) {
                    swal('Atención', 'Seleccione un producto y especifique un descuento.', 'warning');
                    return;
                }
                swal({
                    title: 'Guardando...',
                    text: '',
                    buttons: false,
                    closeOnClickOutside: false
                });
                window.livewire.emit('saveDiscount', id, d);
            };
        });
    </script>
</div>
