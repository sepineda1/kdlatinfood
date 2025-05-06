<div>
    <script>
          function buscarEnTabla(searchInputId, tableBodyId) {
        const searchInput = document.getElementById(searchInputId);
        const tableBody = document.getElementById(tableBodyId);
        const rows = tableBody.getElementsByTagName('tr');

            searchInput.addEventListener('keyup', function () {
                const filter = searchInput.value.toLowerCase();

                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j].textContent.toLowerCase().includes(filter)) {
                            match = true;
                            break;
                        }
                    }

                    rows[i].style.display = match ? '' : 'none';
                }
            });
    }
    </script>

<style>
    .dropdown-item{
        border-top: 1px solid #e4e0e0 !important;
        background-color: #ffff !important;
    }
    .dropdown-item:hover{
        background-color: #e4e0e0 !important;
    }
</style>

    @if($type == "CRUDO")
    <div class="row sales layout-top-spacing">

        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                       
                        <b>PRODUCTS | {{ $name_category }} (RAW)</b>

                        
                        <br>
                    </h4>
                
                    <ul class="tabs tab-pills">
                        @role('Admin')
                        <li>
                            <a href="javascript:void(0)" wire:click="$emit('modal-show1')" onclick="loader()" class="tabmenu bg-dark btn" >Add</a>
                            <!-- <button id="showNotificationBtn">Mostrar Notificación</button> data-toggle="modal"
                                data-target="#theModal"  -->

                            <script>
                            // Obtén una referencia al botón
                            var showNotificationBtn = document.getElementById('showNotificationBtn');

                            // Agrega un controlador de eventos al botón
                            showNotificationBtn.addEventListener('click', function() {
                                // Crear una notificación cuando se hace clic en el botón
                                Push.create('Hello World!', {
                                    body: 'Esta es una notificación de ejemplo.',
                                    icon: 'https://firebasestorage.googleapis.com/v0/b/latin-food-8635c.appspot.com/o/splash%2FlogoAnimadoNaranjaLoop.gif?alt=media&token=0f2cb2ee-718b-492c-8448-359705b01923',
                                    timeout: 4000,
                                    onClick: function() {
                                        window.focus();
                                        this.close();
                                    }
                                });
                            });

                            </script>

                            
                        </li>
                        @endcan

                        <li>
                            @if($productsOutOfStock->isNotEmpty())
                            <div class="dropdown">
                                <button class="btn btn-danger dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Productos Sin Stock
                                </button>
                                <div class="dropdown-menu dropdown-blur border animate__animated animate__fadeInDown"
                                    aria-labelledby="dropdownMenuButton" style="overflow-y: auto; max-height: 200px;">
                                    @foreach($productsOutOfStock as $product)
                                    <a class="dropdown-item bg-white" href="{{ url('lotes') }}">
                                        <div class="d-flex align-items-center">
                                            <span>{{ $product->barcode }} - {{ $product->product->name }} {{ $product->size->size }} {{ $product->product->estado }}</span>
                                            <p class="ml-2 mb-0 {{ $product->alerts >= $product->stock_items  ? 'text-danger' : '' }}"> - STOCK
                                                {{ $product->stock_items }} </p>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            <style type="text/css">
                            .dropdown-blur {
                                background-color: rgba(255, 255, 255, 0.8);
                                backdrop-filter: blur(5px);
                            }
                            </style>

                            @endif
                        </li>

                        <li>
                        <select name="" class="btn btn-secondary categorySelect" id="" onchange="redirectToUrl(this)">
                            @foreach($categories as $item)

                                <option @if($type=="CRUDO" && $category_id==$item->id) Selected @endif  value="{{ url($item->id.'/raw/products') }}" class="text-left" value="">{{ $item->name }} - RAW</option>
                                <option @if($type=="PRECOCIDO" && $category_id==$item->id) Selected @endif value="{{ url($item->id .'/precocked/products') }}" class="text-left" value="">{{ $item->name }} - PRE-COCKED</option>
                            @endforeach
                        </select>
                        </li>
                    </ul>
                </div>

                <div class="row justify-content-between d-none">
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="input-group mb-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background: #FF5100">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text" wire:model="search" placeholder="Buscar" class="form-control">
                        </div>
                        <ul class="list-group">
                            @if(!empty($filteredProducts))
                            @foreach($filteredProducts as $product)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/products/' . $product->image) }}"
                                        alt="{{ $product->name }}" style="width: 50px; height: auto;">
                                    <div class="ml-3">
                                        <h5>{{ $product->name }}</h5>
                                        <p>Stock: {{ $product->stock }}</p>
                                        <p>Estado:
                                            <span
                                                class="badge badge-{{ $product->estado == 'CRUDO' ? 'primary' : 'danger' }}">
                                                {{ $product->estado }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('product.detail', $product->id) }}"
                                        class="btn btn-warning mb-2 mr-2 btn-rounded" title="Detail">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                            class="css-i6dzq1">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                            <polyline points="15 3 21 3 21 9"></polyline>
                                            <line x1="10" y1="14" x2="21" y2="3"></line>
                                        </svg>
                                    </a>
                                    @role('Admin|Employee')
                                    @if ($product->TieneKey == 'SI')
                                    <button type="button" title="Add Cart"
                                    
                                        wire:click.prevent="ScanCode('{{$product->barcode}}')"
                                        onclick="loaderEx()"
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
                                    @endcan
                                    @if ($product->TieneKey == 'SI')
                                    <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                        href="{{ url('detail/pdf' . '/' . $product->id ) }}" title="print"
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
                                    @endif
                                </div>
                            </li>

                            @endforeach
                            @elseif(strlen($search) > 0)
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Sin resultados</h5>
                                    <p class="card-text">No se encontraron productos que coincidan con tu búsqueda.</p>
                                </div>
                            </div>
                            @endif
                        </ul>
                        <div wire:loading wire:target="updatedSearch" class="text-muted">
                            Cargando resultados...
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="input-group mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar productos...">
                    </div>
                    <table class="table table-bordered table-striped mt-1">
                                            <thead class="text-white" style="background: #FF5100;">
                                                <tr>
                                                    <th class="table-th text-white text-center text-nowrap">DETAIL</th>
                                                    <th class="table-th text-white text-center text-nowrap d-none">SKU</th>
                                                    <th class="table-th text-white text-center text-nowrap">Image</th>
                                                    <th class="table-th text-white text-center text-nowrap">NAME</th>
                                                    <th class="table-th text-white text-center text-nowrap">SABOR</th>
                                                    <th class="table-th text-white text-center text-nowrap">COST</th>
                                                    <th class="table-th text-white text-center text-nowrap">BOX ITEM</th>
                                                    <th class="table-th text-white text-center text-nowrap">PRICE</th>
                                                    <th class="table-th text-white text-center text-nowrap">IN
                                                        WOOCOMERCE</th>
                                                    </th>

                                                    <th class="table-th text-white text-center text-nowrap">ACTIONS</th>

                                                   
                                                </tr>
                                            </thead>
                                            <tbody id="tableBody">
                                                @foreach($data as $product)
                                                <tr>
                                                    <td>
                                                        <a target="_blank" href="{{ route('product.detail', $product->id) }}"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            title="Detail {{$product->name}}">
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <path
                                                                    d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6">
                                                                </path>
                                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                                            </svg>
                                                        </a>
                                                    </td>
                                                    <td class="d-none">
                                                        <h6 class="text-center">
                                                           
                                                                {{$product->barcode}}
                                                           
                                                        </h6>
                                                    </td>

                                                    <td>
                                                        <img width="100px" src="{{ asset('storage/products/' . $product->image) }}" alt="">
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">{{$product->name}}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">{{$product->sabor->nombre}}</h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-center">${{ number_format(round($product->cost,2), 2, '.', ''); }}</h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-center">{{$product->tam1}}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">${{ number_format(round($product->price * $product->tam1,2), 2, '.', ''); }}
                                                        </h6>
                                                    </td>

                                                    <!---<td>
                                                        <h6
                                                            class="text-center {{$product->stock <= $product->alerts ? 'text-danger' : '' }}">
                                                            {{round($product->stock/$product->tam1)}}
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <h6 class="text-center">{{$product->alerts}}</h6>
                                                    </td>-->


                                                    <td>
                                                        <h6 class="text-center">
                                                            {{ strtoupper($product->EstaEnWoocomerce) }}</h6>

                                                    </td>

                                                    <td class="text-center">
                                                        @role('Admin')
                                                        <a href="javascript:void(0)"
                                                            wire:click.prevent="EditProduct({{$product->id}})"
                                                            onclick="loaderEx()"
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
                                                            onclick="loaderQuickbooksAll('{{$product->id}}')"
                                                            class="btn btn-success mb-2 mr-2 btn-rounded" title="Crear
                                                            Producto en QuickBooks">
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
                                                        @if ($product->visible === 'no')
                                                        <a href="javascript:void(0)"
                                                            wire:click.prevent="novisible({{$product->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded "
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
                                                            wire:click.prevent="visible({{$product->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded "
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

                                                        <!-- <a href="javascript:void(0)" title="Delete Product"
                                                            onclick="Confirm('{{$product->id}}')"
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
                                                        </a> -->
                                                        @endcan
                                                        @role('Admin|Employee')
                                                        <a href="javascript:void(0)"
                                                            wire:click.prevent="showPresentations({{$product->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                                        </a>
                                                        {{-- @if ($product->TieneKey == 'SI')
                                                            <button type="button" title="Add Cart"
                                                                wire:click.prevent="ScanCode('{{$product->barcode}}')"
                                                                onclick="loaderEx()"
                                                                class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                                title="Delete">
                                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="css-i6dzq1">
                                                                    <circle cx="9" cy="21" r="1"></circle>
                                                                    <circle cx="20" cy="21" r="1"></circle>
                                                                    <path
                                                                        d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                                                    </path>
                                                                </svg>
                                                            </button> 
                                                        @endcan --}}
                                                        {{--@if(strtoupper($product->EstaEnWoocomerce) != 'SI')
                                                         <a class="btn btn-warning mb-2 mr-2 btn-rounded d-none"
                                                            title="Create in Woocomerce"
                                                            onclick="CrearWCProduct('{{$product->id}}')">
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <polyline points="16 16 12 12 8 16"></polyline>
                                                                <line x1="12" y1="12" x2="12" y2="21"></line>
                                                                <path
                                                                    d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                                                </path>
                                                                <polyline points="16 16 12 12 8 16"></polyline>
                                                            </svg>
                                                        </a> 
                                                        @endif--}}
                                                         @if ($product->TieneKey == 'NO')
                                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            title="Generate Key product"
                                                            wire:click.prevent="GenerateKey('{{ $product->id }}')"
                                                            onclick="loaderEx()"
                                                            >
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <path
                                                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                                </path>
                                                            </svg>
                                                        </a> 
                                                        @endif
                                                        @endcan
                                                    </td>
                                                    <!-- @if ($product->TieneKey == 'SI')
                                                    <td>
                                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            href="{{ url('detail/pdf/prod' . '/' . $product->id ) }}"
                                                            title="print" target="_blank" style="background:#f39022;">
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                                <path
                                                                    d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                                </path>
                                                                <rect x="6" y="14" width="12" height="8"></rect>
                                                            </svg></a>
                                                    </td>
                                                    @endif -->
                                                </tr>

                                              
                                                @endforeach
                                            </tbody>
                                        </table>
                    <!--<div class="accordion" id="accordionCategories1">
                        @foreach($categories as $category)
                        <div class="card">
                            <div class="card-header" id="heading{{$category->id}}">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse"
                                        data-target="#collapse{{$category->id}}" aria-expanded="true"
                                        aria-controls="collapse{{$category->id}}">
                                        <h3>{{$category->name}} <span class="badge badge-primary">Total de productos:
                                                {{count($category->products->where('estado', 'CRUDO'))}}</span></h3>
                                    </button>
                                </h5>
                            </div>

                            <div id="collapse{{$category->id}}" class="collapse"
                                aria-labelledby="heading{{$category->id}}" data-parent="#accordionCategories1">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>-->
                </div>
            </div>
        </div>
        
       
    </div>

    <script>
        // Llamar a la función para habilitar la búsqueda
    buscarEnTabla('searchInput', 'tableBody');

    </script>
    @endif
    
 
  
    @if($type === "PRECOCIDO")
    <div class="row sales layout-top-spacing">
        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>PRODUCTS | {{ $name_category }} (PRE-COCKED)</b> <br/>
                       
                    </h4>
                    <ul class="tabs tab-pills">
                        @role('Admin')
                            <li>
                                <a href="javascript:void(0)" class="tabmenu bg-dark btn" data-toggle="modal"
                                    data-target="#theModal">Add</a>
                                <!-- <button id="showNotificationBtn">Mostrar Notificación</button>-->

                                <script>
                                // Obtén una referencia al botón
                                var showNotificationBtn = document.getElementById('showNotificationBtn');

                                // Agrega un controlador de eventos al botón
                                showNotificationBtn.addEventListener('click', function() {
                                    // Crear una notificación cuando se hace clic en el botón
                                    Push.create('Hello World!', {
                                        body: 'Esta es una notificación de ejemplo.',
                                        icon: 'https://firebasestorage.googleapis.com/v0/b/latin-food-8635c.appspot.com/o/splash%2FlogoAnimadoNaranjaLoop.gif?alt=media&token=0f2cb2ee-718b-492c-8448-359705b01923',
                                        timeout: 4000,
                                        onClick: function() {
                                            window.focus();
                                            this.close();
                                        }
                                    });
                                });
                                </script>
                            </li>
                            @endcan

                        <li>
                        @if($productsOutOfStock->isNotEmpty())

                        
                            <div class="dropdown">
                                <button class="btn btn-danger dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Productos Sin Stock
                                </button>
                                <div class="dropdown-menu dropdown-blur border animate__animated animate__fadeInDown"
                                    aria-labelledby="dropdownMenuButton" style="overflow-y: auto; max-height: 200px;">
                                    @foreach($productsOutOfStock as $product)
                                    <a class="dropdown-item" href="{{ url('lotes') }}">
                                        <div class="d-flex align-items-center">
                                            <span>{{ $product->barcode }} - {{ $product->product->name }} {{ $product->size->size }} {{ $product->product->estado }}</span>
                                            <p class="ml-2 mb-0 {{ $product->stock_box < 90 ? 'text-danger' : '' }}"> - STOCK
                                                {{ $product->stock_box }} </p>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            <style type="text/css">
                            .dropdown-blur {
                                background-color: rgba(255, 255, 255, 0.8);
                                backdrop-filter: blur(5px);
                            }
                            </style>

                            @endif
                        </li>
                        <li>
                            <select name="" class="btn btn-secondary categorySelect" id="" onchange="redirectToUrl(this)">
                                    @foreach($categories as $item)

                                        <option @if($type=="CRUDO" && $category_id==$item->id) Selected @endif  value="{{ url($item->id.'/raw/products') }}" class="text-left" value="">{{ $item->name }} - RAW</option>
                                        <option @if($type=="PRECOCIDO" && $category_id==$item->id) Selected @endif value="{{ url($item->id .'/precocked/products') }}" class="text-left" value="">{{ $item->name }} - PRE-COCKED</option>
                                    @endforeach
                            </select>
                        </li>
                    </ul>
                 
                </div>
                <div class="widget-content">
                    <div class="input-group mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar productos...">
                    </div>
                <div class="table-responsive">
                                        <table class="table table-bordered table-striped mt-1">
                                            <thead class="text-white" style="background: #FF5100;">
                                                <tr>
                                                    <th class="table-th text-white text-center">DETAILS</th>
                                                    <th class="table-th text-white text-center d-none">SKU</th>
                                                    <th class="table-th text-white text-center">IMAGE</th>
                                                    <th class="table-th text-white text-center">NAME</th>
                                                    <th class="table-th text-white text-center">SABOR</th>
                                                    <th class="table-th text-white text-center">COST</th>
                                                    <th class="table-th text-white text-center">BOX ITEM</th>
                                                    <th class="table-th text-white text-center">PRICE</th>
                                                    <th class="table-th text-white text-center">IN WOOCOMERCE</th>
                                                    <th class="table-th text-white text-center">ACTIONS</th>                                                    
                                                </tr>
                                            </thead>
                                            <tbody id="tableBody">
                                                @foreach($data2 as $product2)
                                          

                                                <tr>
                                                    <td>
                                                        <a target="_blank" href="{{ route('product.detail', $product2->id) }}"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            title="Detail {{$product2->name}}">
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <path
                                                                    d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6">
                                                                </path>
                                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                                            </svg>
                                                        </a>
                                                    </td>
                                                    <td class="d-none">
                                                        <h6 class="text-center">
                                                           {{$product2->barcode}}
                                                        </h6>
                                                    </td>
                                                    <td>
                                                        <img width="100px" src="{{ asset('storage/products/' . $product2->image) }}" alt="">
                                                    </td>
                                                    <td>
                                                        <h6 class="text-center">{{$product2->name}}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">{{$product2->sabor->nombre}}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">${{ number_format(round($product2->cost,2), 2, '.', '') }}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">{{$product2->tam1}}</h6>
                                                    </td>

                                                    <td>
                                                        <h6 class="text-center">${{ number_format(round($product2->price * $product->tam1,2), 2, '.', '') }}
                                                        </h6>
                                                    </td>
                                    

                                                    <td>
                                                        <h6 class="text-center">
                                                            {{ strtoupper($product2->EstaEnWoocomerce) }}</h6>

                                                    </td>                                                    
                                                    <td class="text-center">
                                                        @role('Admin')
                                                           <a href="javascript:void(0)"
                                                                wire:click.prevent="EditProduct({{$product2->id}})"
                                                                onclick="loaderEx()"
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
                                                            onclick="loaderQuickbooksAll('{{$product2->id}}')"
                                                            class="btn btn-success mb-2 mr-2 btn-rounded" title="Crear
                                                            Producto en QuickBooks">
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

                                                        {{--

                                                        @endcan--}}

                                                        @if ($product2->visible === 'no')
                                                        <a href="javascript:void(0)"
                                                            wire:click.prevent="novisible({{$product2->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded "
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
                                                            wire:click.prevent="visible({{$product2->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded "
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

                                                        @if ($product2->TieneKey == 'NO')
                                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            title="Generate Key product"
                                                            wire:click.prevent="GenerateKey('{{ $product2->id }}')"
                                                            onclick="loaderEx()"
                                                            >
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <path
                                                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                                </path>
                                                            </svg>
                                                        </a> 
                                                        @endif  
                                                        @endcan
                                                        @role('Admin|Employee')
                                                        <a href="javascript:void(0)"
                                                            wire:click.prevent="showPresentations({{$product2->id}})"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                                        </a>                                                        
{{--                                                         @if ($product2->TieneKey == 'SI')                                                        
                                                        <button type="button" title="Add Cart"
                                                            wire:click.prevent="ScanCode('{{$product2->barcode}}')"
                                                            onclick="loaderEx()"
                                                            class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                            title="Delete">
                                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                                stroke="currentColor" stroke-width="2" fill="none"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="css-i6dzq1">
                                                                <circle cx="9" cy="21" r="1"></circle>
                                                                <circle cx="20" cy="21" r="1"></circle>
                                                                <path
                                                                    d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                                                </path>
                                                            </svg>
                                                        </button>  --}}
                                                        
                                                        
                                                    @endcan
                                                   {{-- @if(strtoupper($product2->EstaEnWoocomerce) != 'SI')
                                                     <a class="btn btn-warning mb-2 mr-2 btn-rounded d-none"
                                                        title="Create in Woocomerce"
                                                        onclick="CrearWCProduct('{{$product2->id}}')">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <polyline points="16 16 12 12 8 16"></polyline>
                                                            <line x1="12" y1="12" x2="12" y2="21"></line>
                                                            <path
                                                                d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                                            </path>
                                                            <polyline points="16 16 12 12 8 16"></polyline>
                                                        </svg>
                                                    </a> 
                                                    @endif--}}
                                                    
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
    <script>
        // Llamar a la función para habilitar la búsqueda
    buscarEnTabla('searchInput', 'tableBody');

    </script>
    @endif
        @include('livewire.products.presentaciones')
        @include('livewire.products.form')
        @include('livewire.products.presentacion-form')
    <script>
    $(document).ready(function() {
        // Controlar la apertura y cierre del acordeón 1
        $('#accordionCategories1 .collapse').on('show.bs.collapse', function() {
            $('#accordionCategories1 .collapse.show').collapse('hide');
        });

        // Controlar la apertura y cierre del acordeón 2
        $('#accordionCategories2 .collapse').on('show.bs.collapse', function() {
            $('#accordionCategories2 .collapse.show').collapse('hide');
        });
    });


    function redirectToUrl(element) {
        const url = element.value; // Obtiene el valor del <option> seleccionado

        if (url) {
            window.location.href = url; // Redirige a la URL
        }
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
                                                    function loaderEx() {
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
                                                    
                                                    
    </script>
    @push('scripts')
    <script>
    window.livewire.on('producto-creado', () => {
        swal.close();
    });

    document.addEventListener('DOMContentLoaded', function() {
		
		window.livewire.on('scan-ok', Msg => {			
			noty(Msg)
		}) });
    </script>
    @endpush
    @push('scripts')
    <script>
    window.livewire.on('creando', () => {

        swal({
            title: 'Creando producto en Quickbooks',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.emit('QuickboksCreate');
                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });

    });
    </script>
    @endpush
    <script>
    function CrearWCProduct(id) {
        swal({
            title: 'Creando producto en WooCommerce',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.emit('crearWC', id);
                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });
    }

    function loaderQuickbooksPresentacion(id) {
        swal({
            title: 'Procesando producto en Quickbooks',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.emit('QuickboksPresentacion', id);
                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });
    }

    function loaderQuickbooksAll(id){ //Actualizar los datos en la BD del Quickbooks
        swal({
            title: 'Actualizando productos en Quickbooks',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.emit('loaderQuickbooksAll', id);
                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });
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
                window.livewire.emit('deleteRow', id)
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
        window.livewire.on('product-WC', msg => {
            // noty
        });
        window.livewire.on('product-deleted', msg => {
            // noty
        });
        window.livewire.on('modal-show', msg => {
            $('#theModal').modal('show')
        });
        window.livewire.on('modal-show1', msg => {
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
        });

        $('#theModal').on('shown.bs.modal', function(e) {
            $('.product-name').focus()
        });


    });

    function Confirm(id) {
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
                window.livewire.emit('deleteRow', id)
                swal.close()
            }

        })
    }

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
    </script>
    <script>
    /*  window.addEventListener('DOMContentLoaded', (event) => {
        @if($productsOutOfStock -> isNotEmpty())
        // Función para mostrar la alerta
        function mostrarAlerta() {
            swal({
                title: 'WARNING',
                text: 'SOME PRODUCTS ARE OUT STOCK, PLEASE CHECK.',
                type: 'warning',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Ok',
                showCancelButton: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.isConfirmed) {}
            });
        }
        setInterval(mostrarAlerta, 3 * 60 * 1000);
        @endif
    });*/
    </script>

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
    /*  window.addEventListener('DOMContentLoaded', (event) => {
          @if($productsOutOfStock -> isNotEmpty())
          // Función para mostrar la alerta
          swal({
              title: 'WARNING',
              text: 'MANY PRODUCTS ARE OUT STOCK, PLEASE CHECK',
              type: 'warning',
              confirmButtonColor: '#3B3F5C',
              confirmButtonText: 'Ok',
              showCancelButton: false,
              allowOutsideClick: false,
          }).then((result) => {
              if (result.isConfirmed) {

              }
          });
          @endif
      });*/
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
        window.livewire.on('presentaciones-hide', msg => {
            $('#PresentacionesModal').modal('hide')
        });
        window.livewire.on('presentaciones-show', msg => {
            console.log("Mostrano Ventana.");
            $('#PresentacionesModal').modal('show')
        });
        window.livewire.on('presentaciones-crear-hide', msg => {
            $('#PreModal').modal('hide')
        });
        window.livewire.on('presentaciones-crear-show', msg => {
            $('#PreModal').modal('show')
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

        window.livewire.on('created-discount-show', msg => {
            showDiscountCreatedAlert();
        });

        window.livewire.on('destroy-discount-show', msg => {
            showDiscountDestroyAlert();
        });

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

    function showDiscountCreatedAlert() {
        console.log("Hola");
        swal({
            title: 'Descuento Creado!',
            text: 'El descuento ha sido creado exitosamente.',
            type: 'success',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar'
        });
    }

    function showDiscountDestroyAlert() {
        swal({
            title: 'Descuento Eliminado!',
            text: 'El descuento ha sido creado exitosamente.',
            type: 'success',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar'
        });
    }

  
    </script>

</div>