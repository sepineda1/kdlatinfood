<div>
    <?php
        $count = ['Grande' => 0, 'Mediano' => 0, 'Pequeño' => 0];
        foreach ($product->presentaciones as $pre) {
            $size = $pre->size->size;
            if (isset($count[$size])) {
                $count[$size]++;
            }
        }
    ?>
    <div class="row sales layout-top-spacing">
        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Product | {{$product->name}} {{ ucfirst(strtolower($product->estado)) }} @if ($count['Grande'] > 0) <span class="boxes-letter-circle">G</span> @endif  @if ($count['Mediano'] > 0) <span class="boxes-letter-circle">M</span> @endif @if ($count['Pequeño'] > 0) <span class="boxes-letter-circle">P</span> @endif</b>
                    </h4>
                    <ul class="tabs tab-pills d-none">
                        <li>
                            <a href="javascript:void(0)" onclick="loader()" wire:click.prevent="EditProduct({{$product->id}})"
                                class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z">
                                    </path>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>


                <div class="widget-content">

                    <style>
                    body {

                        margin: 0;
                        padding: 0;
                        background-color: #f8f9fa;
                    }

                    .container {
                        max-width: 1200px;
                        margin: 50px auto;
                        background-color: #fff;
                        border-radius: 10px;
                        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                        padding: 30px;
                        display: flex;
                        align-items: flex-start;
                    }

                    .product-info {
                        flex-grow: 1;
                        
                    }

                    .product-image {
  
   
                        max-width: 200px;
                    }

                    h1 {
                        color: #333;
                        margin-bottom: 20px;
                    }

                    p {
                        color: #555;
                    }

                    ul {
                        list-style-type: none;
                        padding: 0;
                    }

                    li {
                        margin-bottom: 10px;
                    }

                    .badge {
                        padding: 5px 10px;
                        border-radius: 5px;
                        color: #fff;
                        font-size: 14px;
                    }

                    .badge-active {
                        background-color: #28a745;
                    }

                    .badge-inactive {
                        background-color: #dc3545;
                    }

                    .badge-yes {
                        background-color: #007bff;
                    }

                    .badge-no {
                        background-color: #6c757d;
                    }

                    .buttons-container {
                        margin-top: 20px;
                    }

                    .buttons-container button {
                        margin-right: 10px;
                    }

                    .long-description {
                        max-height: 100px;
                        max-width: 350;
                        /* Establece la altura máxima */
                        overflow: hidden;
                        /* Oculta el contenido que excede la altura máxima */
                        transition: max-height 0.3s ease;
                        /* Agrega una transición suave */
                    }

                    .long-description.expanded {
                        max-height: none;
                        /* Elimina el límite de altura cuando se expande */
                    }
                    .bg-primary-dash{
                        background: rgb(255,80,0);
                        background: linear-gradient(90deg, rgba(255,80,0,1) 38%, rgba(250,161,34,1) 87%);
                        color: #ffff !important;
                        border-radius: 15px;
                    }
                    .boxes-letter-circle{
                        background: rgb(255,80,0);
                        background: linear-gradient(90deg, rgba(255,80,0,1) 38%, rgba(250,161,34,1) 87%);
                        color: #ffff !important;
                        border-radius: 50%;
                        padding: 5px;
                        width: 15px !important;
                        height: 15px;
                    }
                    .text-star-color{
                        color: rgb(255,80,0);
                    }
                    </style>
                    <div class="container-fluid ">
                        <div class="product-info mb-2">
                            <p class="long-description"><b>Descriptión:</b>{{ $product->descripcion }}</p>
                            <p class="long-description"><b>Category: </b>{{ $product->category->name }}</p>
                            <p class="long-description"><b>Sabor: </b>{{ $product->sabor->nombre }}</p>
                            <p class="d-none">Visible en tienda: {{ $product->visible ? 'Sí' : 'No' }}
                            </p>
                            
                        </div>
                        <br>
                    </div>
                    <div class="container-fluid">
                        
                        <div class="row">
                            @php $k=0; @endphp
                            @foreach ($product->presentaciones as $pre)
                                <div class="col-md-3">

                                    <img style="border-radius:50%" src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="img-fluid product-image text-center">

                                    <div class="product-info mt-3">
                                        <div class="bg-primary-dash p-1 text-center h5">{{ $pre->size->size }} X {{ $pre->stock_items }} </div>
                                        <h4 class="d-none"><b>{{ $pre->size->size }} X {{ $pre->stocks_items }}</b></h4>
                                        <div class="description-container d-none">
                                            <p class="long-description">{{ $product->descripcion }}</p>
                                        </div>

                                        <ul>
                                            <li class="text-dark"><i class="fas fa-window-minimize"></i> Código de barras: {{ $pre->barcode }}</li>
                                            <li class="text-dark"><i class="fas fa-window-minimize"></i> Stock Box: {{ $pre->stock_box}} unidades</li>
                                            <li class="text-dark" style="font-size: 15px;"><i class="fas fa-window-minimize"></i> Precio: <b>$ {{ $pre->price }}</b></li>
                                            <li class="text-star-color"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></li>
                                        </ul>
                                        <p class="d-none">Visible en tienda: <span
                                                class="badge badge-{{ $pre->visible ? 'yes' : 'no' }} d-none"></span>
                                                {{ $pre->visible ? 'Sí' : 'No' }}
                                        </p>
                                        
            
                                        <div class="buttons-container">
            
                                            @if ($pre->visible === 'no')
                                            <a href="javascript:void(0)" onclick="loader()" wire:click.prevent="toggleVisibilityPresentacion({{$pre->id}},'si')"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Publicar">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path
                                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                                    </path>
                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                </svg>
                                            </a>
                                            @else
                                            <a href="javascript:void(0)" onclick="loader()" wire:click.prevent="toggleVisibilityPresentacion({{$pre->id}},'no')"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Ocultar">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                    </path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            @endif
                                            @php $can_delete_pre = $product->can_delete_pre;@endphp
                                            @if ($can_delete_pre[$k])
                                                <a href="javascript:void(0)" title="Delete Presentacion"
                                                    onclick="ConfirmDeletePresentacion('{{$pre->id}}')" class="btn btn-danger mb-2 mr-2 btn-rounded d-none"
                                                    title="Delete">
                                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
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
                                          
                                            @if ($product->TieneKey == 'SI')
                                            <button type="button" title="Add Cart"
                                                wire:click.prevent="ScanCode('{{$product->barcode}}')"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Delete">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <circle cx="9" cy="21" r="1"></circle>
                                                    <circle cx="20" cy="21" r="1"></circle>
                                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                                    </path>
                                                </svg>
                                            </button>
                                            @endif
                                            @if(strtoupper($product->EstaEnWoocomerce) != 'SI')
                                            <a class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Create in Woocomerce"
                                                wire:click.prevent="CrearProWoo('{{ $product->id }}')">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <polyline points="16 16 12 12 8 16"></polyline>
                                                    <line x1="12" y1="12" x2="12" y2="21"></line>
                                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                                    </path>
                                                    <polyline points="16 16 12 12 8 16"></polyline>
                                                </svg>
                                            </a>
                                            @endif
            
                                            @if ($pre->TieneKey == 'NO')
                                            <a class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Generate Key product"
                                                wire:click.prevent="GenerateKeyPresentacion('{{ $pre->id }}')"  onclick="loader()">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <path
                                                        d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                    </path>
                                                </svg>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @php $k++; @endphp
                            @endforeach
                        </div>
                    </div>
                    <div class="container d-none">
                        

                        <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}"
                            class="img-fluid product-image d-none">
                        <div class="product-info">
                            <h1>{{ $product->name }}</h1>
                            <div class="description-container">
                                <p class="long-description">{{ $product->descripcion }}</p>
                                @if (strlen($product->descripcion) > 100)

                                @endif
                            </div>

                            <script>
                            function toggleDescription() {
                                var description = document.querySelector('.long-description');
                                var button = document.getElementById('toggleButton');
                                description.classList.toggle('expanded');
                                if (description.classList.contains('expanded')) {
                                    button.textContent = 'Ver menos';
                                } else {
                                    button.textContent = 'Ver más';
                                }
                            }
                            </script>
                            <ul>
                                <li>Categoría: {{ $product->category->name }}</li>
                                <li>Sabor: {{ $product->sabor->nombre }}</li>
                                <li>Precio: $ {{ number_format(round($product->price * $product->tam1,2), 2, '.', '') }}</li>
                                <li>Stock: {{ $product->stock }} unidades</li>
                                <li>Código de barras: {{ $product->barcode }}</li>
                            </ul>
                            <p>Visible en tienda: <span
                                    class="badge badge-{{ $product->visible ? 'yes' : 'no' }}">{{ $product->visible ? 'Sí' : 'No' }}</span>
                            </p>
                            <p>Estado:
                                {{ $product->estado }}
                            </p>

                            <div class="buttons-container">

                                @if ($product->visible === 'no')
                                <a href="javascript:void(0)" onclick="loader()" wire:click.prevent="novisible({{$product->id}})"
                                    class="btn btn-warning mb-2 mr-2 btn-rounded" title="Publicar">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <path
                                            d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                        </path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </a>
                                @else
                                <a href="javascript:void(0)" onclick="loader()" wire:click.prevent="visible({{$product->id}})"
                                    class="btn btn-warning mb-2 mr-2 btn-rounded" title="Ocultar">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                        </path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                @endif
                                <a href="javascript:void(0)" title="Delete Product"
                                    onclick="Confirm('{{$product->id}}')" class="btn btn-danger mb-2 mr-2 btn-rounded d-none"
                                    title="Delete">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </a>
                                @if ($product->TieneKey == 'SI')
                                <button type="button" title="Add Cart"
                                    wire:click.prevent="ScanCode('{{$product->barcode}}')"
                                    class="btn btn-warning mb-2 mr-2 btn-rounded" title="Delete">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                        </path>
                                    </svg>
                                </button>
                                @endif
                                @if(strtoupper($product->EstaEnWoocomerce) != 'SI')
                                <a class="btn btn-warning mb-2 mr-2 btn-rounded" title="Create in Woocomerce"
                                    wire:click.prevent="CrearProWoo('{{ $product->id }}')">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <polyline points="16 16 12 12 8 16"></polyline>
                                        <line x1="12" y1="12" x2="12" y2="21"></line>
                                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                        </path>
                                        <polyline points="16 16 12 12 8 16"></polyline>
                                    </svg>
                                </a>
                                @endif

                                @if ($product->TieneKey == 'NO')
                                <a class="btn btn-warning mb-2 mr-2 btn-rounded" title="Generate Key product"
                                    wire:click.prevent="GenerateKey('{{ $product->id }}')">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                        stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                        class="css-i6dzq1">
                                        <path
                                            d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                        </path>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </div>
                        @if ($qr)
                        <div class="qr-code">
                            {{$qr}}
                        </div>
                        @else
                        <div class="qr-code">
                            <img width="200"
                                src="https://firebasestorage.googleapis.com/v0/b/latin-food-8635c.appspot.com/o/splash%2FlogoAnimadoNaranjaLoop.gif?alt=media&token=0f2cb2ee-718b-492c-8448-359705b01923"
                                alt="No QR">
                            <p>Sin QR</p>
                            @if ($product->TieneKey == 'NO')
                            <a class="btn btn-warning mb-2 mr-2 btn-rounded" title="Generate Key product"
                                wire:click.prevent="GenerateKey('{{ $product->id }}')">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                    <path
                                        d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                    </path>
                                </svg>
                            </a>
                            @endif
                        </div>
                        @endif

                    </div>

                </div>


            </div>


        </div>

    </div>
    @include('livewire.products.form')
    <script>
    Livewire.on('swal-loading', function(message) {
        Swal.fire({
            title: message,
            text: 'Por favor, espera...',
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: function() {
                Swal.showLoading();
            },
        });
    });

    Livewire.on('producto-creado', function() {
        Swal.fire('Éxito', 'Producto creado en WooCommerce', 'success').then(function() {
            location.reload();
        });
    });
    </script>
    <script>
    document.getElementById('create-product-button').addEventListener('click', function() {
        var productId = this.getAttribute('data-product-id');

        // Mostrar un mensaje de carga
        Swal.fire({
            title: 'Creando producto en WooCommerce',
            text: 'Por favor, espera...',
            showConfirmButton: false,
            allowOutsideClick: false,
            willOpen: function() {
                Swal.showLoading();
            },
        });

        // Realizar la solicitud AJAX para crear el producto en WooCommerce
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/create-product-in-woocommerce');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
            if (xhr.status === 200) {
                Swal.fire('Éxito', 'Producto creado en WooCommerce', 'success');
            } else {
                Swal.fire('Error', 'No se pudo crear el producto en WooCommerce', 'error');
            }
        };
        xhr.send(JSON.stringify({
            product_id: productId
        }));
    });
    </script>
    <script>
    function Confirm(id) {
        swal({
            title: 'CHECK',
            text: '¿CONFIRM DELETE THIS REG?',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Close',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Ok'
        }).then(function(result) {
            if (result.value) {
                window.livewire.emit('deleteRow', id)
                swal.close()
            }

        })
    }

    function ConfirmDeletePresentacion(id) {
        swal({
            title: 'CHECK',
            text: '¿CONFIRM DELETE THIS REG?',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Close',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Ok'
        }).then(function(result) {
            if (result.value) {
                window.livewire.emit('deletePre', id)
                swal.close()
            }

        })
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.livewire.on('product-added', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('product-updated', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('product-deleted', msg => {
            // noty
        });
        window.livewire.on('modal-show', msg => {
            $('#theModal').modal('show')
        });
        window.livewire.on('modal-hide', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('hidden.bs.modal', msg => {
            $('.er').css('display', 'none')
        });
        $('#theModal').on('hidden.bs.modal', function(e) {
            $('.er').css('display', 'none')
        })
        $('#theModal').on('shown.bs.modal', function(e) {
            $('.product-name').focus()
        })
    });

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
        }).then(function(result) {
            if (result.value) {
                window.livewire.emit('deleteRow', id)
                swal.close()
            }

        })
    }
    </script>
</div>