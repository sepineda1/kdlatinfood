<div class="row sales layout-top-spacing">
    <div class="col-sm-12">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h4 class="card-title">
                    <b>INSPECTORS | LIST</b>
                </h4>
                <ul class="tabs tab-pills">
                    <li>
                        <a class="btn btn-rounded  notification-action-btn" href="{{ url('InspectorsPDF') }}"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-download-cloud mr-1">
                                <polyline points="8 17 12 21 16 17"></polyline>
                                <line x1="12" y1="12" x2="12" y2="21"></line>
                                <path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path>
                            </svg> Download Logs </a>
                    </li>
                </ul>
            </div>
            @include('common.searchbox')
            <div class="widget-content">
                <div class="row">
                    <div class="col-lg-12">
                        <h4>Resultado de la búsqueda:</h4>
                        @if($insumo->count() > 0)
                        <div class="accordion" id="accordionLotes">
                            @foreach($insumo->groupBy('CodigoBarras') as $codigoBarras => $lotes)
                            <div class="card">
                                <div class="card-header" id="heading{{ $codigoBarras }}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse"
                                            data-target="#collapse{{ $codigoBarras }}" aria-expanded="true"
                                            aria-controls="collapse{{ $codigoBarras }}">
                                            <h2> Código de Barras: {{ $codigoBarras }} </h2>
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapse{{ $codigoBarras }}" class="collapse show"
                                    aria-labelledby="heading{{ $codigoBarras }}" data-parent="#accordionLotes">
                                    <div class="card-body">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th class="table-th text-center"> Sabor</th>
                                                    <th class="table-th text-center">Cantidad de Artículos</th>
                                                    <th class="table-th text-center">Fecha de Creacion</th>
                                                    <th class="table-th text-center">Fecha de Vencimiento</th>
                                                    <th class="table-th text-center">Usuario</th>
                                                    <th class="table-th text-center">Producto relacionado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lotes as $item)
                                                <tr>
                                                    <td class="text-center">
                                                        <h5>{{ $item->presentacion->product->sabor->nombre }} </h5>
                                                    </td>
                                                    <td class="text-center">
                                                        <h5>{{ $item->Cantidad_Articulos }}</h5>
                                                    </td>
                                                    <td class="text-center">
                                                        <h5>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</h5>
                                                    </td>
                                                    <td class="text-center">
                                                        <h5>{{\Carbon\Carbon::parse( $item->Fecha_Vencimiento)->format('d-m-Y') }}</h5>
                                                    </td>
                                                    <td class="text-center">
                                                        <h5>{{ $item->User }}</h5>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($item->presentacion)
                                                        <h5>{{ $item->presentacion->product->name }} {{ $item->presentacion->size->size }} {{ $item->presentacion->product->estado }}</h5>
                                                        <!-- Aquí comienza el acordeón para mostrar los datos del producto -->
                                                        <div class="accordion"
                                                            id="accordionProductos{{ $item->presentacion->id }}">
                                                            <div class="card">
                                                                <div class="card-header"
                                                                    id="headingProducto{{ $item->presentacion->id }}">
                                                                    <h2 class="mb-0">
                                                                        <button class="btn btn-link" type="button"
                                                                            data-toggle="collapse"
                                                                            data-target="#collapseProducto{{ $item->presentacion->id }}"
                                                                            aria-expanded="true"
                                                                            aria-controls="collapseProducto{{ $item->presentacion->id }}">
                                                                            Datos del Producto
                                                                        </button>
                                                                    </h2>
                                                                </div>

                                                                <div id="collapseProducto{{ $item->presentacion->id }}"
                                                                    class="collapse "
                                                                    aria-labelledby="headingProducto{{ $item->presentacion->id }}"
                                                                    data-parent="#accordionProductos{{ $item->presentacion->id }}">
                                                                    <div class="card-body">
                                                                        <table class="table table-bordered">
                                                                            <tr>
                                                                                <th>SKU</th>
                                                                                <th>Nombre</th>
                                                                                <th>Fecha de Creacion</th>
                                                                                <th>User</th>
                                                                                <!-- Agrega más encabezados de columnas según tus necesidades -->
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                                    <h5>{{ $item->presentacion->barcode }}
                                                                                    </h5>
                                                                                </td>
                                                                                <td>
                                                                                    <h5>{{ $item->presentacion->product->name }}</h5>
                                                                                </td>
                                                                                <td>
                                                                                    <h5>{{ 
                                                                                    \Carbon\Carbon::parse( 
                                                                                    $item->presentacion->created_at
                                                                                    )->format('d-m-Y') }}
                                                                                    </h5>
                                                                                </td>
                                                                                <td>
                                                                                    <h5> {{ $item->User }}
                                                                                    </h5>
                                                                                </td>

                                                                                <!-- Agrega más datos de las columnas según tus necesidades -->
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Aquí termina el acordeón -->

                                                    </td>
                                                    @else
                                                    <h5>No hay productos asociados a este lote.</h5>
                                                    @endif
                                                </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                        @else
                        <p>No se encontraron resultados</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.querySelector(".table-search");

        if (!searchInput) return;

        searchInput.addEventListener("keyup", function () {
            const searchTerm = this.value.toLowerCase();
            const tables = document.querySelectorAll(".widget-content table");

            tables.forEach(table => {
                let visibleRowCount = 0;
                const rows = table.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    const html = row.innerHTML.toLowerCase();

                    // Match por contenido visible o por nombre de etiquetas HTML
                    const match = rowText.includes(searchTerm) || html.includes(`<${searchTerm}`);

                    row.style.display = match ? "" : "none";
                    if (match) visibleRowCount++;
                });

                // Ocultar o mostrar card si es una tabla dentro de accordion
                const card = table.closest(".card");
                if (card) {
                    const collapseSection = card.querySelector(".collapse");
                    if (collapseSection) {
                        card.style.display = visibleRowCount > 0 ? "" : "none";
                    }
                }
            });
        });
    });
</script>

