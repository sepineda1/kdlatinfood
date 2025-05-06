<div>
<div class="row sales layout-top-spacing">

    <div class="col-sm-12">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h4 class="card-title">
                    <b>
                        | LIST</b>
                </h4>
                <ul class="tabs tab-pills">
                    <li>
                        <a href="javascript:void(0)" class="btn btn-primary mb-2 mr-2 btn-rounded" data-toggle="modal"
                            data-target="#theModal">Add</a>
                    </li>

                </ul>
            </div>
            @include('common.searchbox')
            <div class="widget-content">
                <div id="accordion" class="accordion">
                    @foreach($sabor as $sabores)
                    @if($sabores)
                    <div class="card">
                        <div class="card-header" id="heading{{ $sabores->id }}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse"
                                    data-target="#collapse{{ $sabores->id }}" aria-expanded="true"
                                    aria-controls="collapse{{ $sabores->id }}">
                                    <h3>{{ $sabores->nombre }}</h3>
                                </button>
                            </h5>
                        </div>

                        <div id="collapse{{ $sabores->id }}" class="collapse"
                            aria-labelledby="heading{{ $sabores->id }}" data-parent="#accordion">
                            <div class="accordion-body">
                                @php
                                $lotes = $lotesAsociados[$sabores->id] ?? [];
                                @endphp
                                @if(count($lotes) > 0)
                                <div id="accordion-lotes-{{ $sabores->id }}">
                                    @foreach($lotes as $codigoBarras => $lote)
                                    <div class="card">
                                        <div class="card-header"
                                            id="heading-lote-{{ $sabores->id }}-{{ $codigoBarras }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse-lote-{{ $sabores->id }}-{{ $codigoBarras }}"
                                                    aria-expanded="true"
                                                    aria-controls="collapse-lote-{{ $sabores->id }}-{{ $codigoBarras }}">
                                                    <h5> Lote {{ $loop->iteration }} - Código de Barras:
                                                        {{ $codigoBarras }}
                                                        @if(count($lote) > 1)
                                                        (Lote con {{ count($lote) }} productos)
                                                        @elseif(count($lote) == 1)
                                                        (Producto único)
                                                        @endif
                                                    </h5>

                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapse-lote-{{ $sabores->id }}-{{ $codigoBarras }}" class="collapse"
                                            aria-labelledby="heading-lote-{{ $sabores->id }}-{{ $codigoBarras }}"
                                            data-parent="#accordion-lotes-{{ $sabores->id }}">
                                            <div class="accordion-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered">
                                                        <thead class="text-white" style="background: #FF5100">
                                                            <tr>
                                                                <th class="table-th text-white text-center ">ID</th>
                                                                <th class="table-th text-white text-center ">SKU</th>
                                                                <th class="table-th text-white text-center ">NAME</th>
                                                                <th class="table-th text-white text-center ">Category
                                                                </th>
                                                                <th class="table-th text-white text-center ">Boxes</th>
                                                                <th class="table-th text-white text-center ">Acciones
                                                                </th>
                                                                <th class="table-th text-white text-center ">QR</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        
                                                            @foreach($lote as $item_lote)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <h6> {{ $item_lote->presentacion->id }} </h6>
                                                                </td>
                                                                <td class="text-center">
                                                                    <h6>{{ $codigoBarras }}-<b>{{ $item_lote->presentacion->barcode }} </b></h6>
                                                                </td>
                                                                <td class="text-center">
                                                                    <h6> {{ $item_lote->presentacion->product->name }} {{ $item_lote->presentacion->size->size }} {{ $item_lote->presentacion->product->estado }}</h6>
                                                                </td>
                                                                </td>
                                                                <td class="text-center">
                                                                    <h6> {{ $item_lote->presentacion->product->category->name }}</h6>
                                                                </td>
                                                                <td class="text-center">
                                                                    <h6>{{ $item_lote->Cantidad_Articulos }} </h6>
                                                                </td>
                                                                <td class="text-center">
                                                                        @if ($item_lote->presentacion->product->estado != "PRECOCIDO")
                                                                            <a style="background:#f39022;"
                                                                                href="javascript:void(0)"
                                                                                onclick="Cambio('{{$item_lote->presentacion->id}}', '{{$item_lote->id}}')"
                                                                                class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                                                title="Cambio a PRECOCIDO">
                                                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                                                    stroke="currentColor" stroke-width="2"
                                                                                    fill="none" stroke-linecap="round"
                                                                                    stroke-linejoin="round" class="css-i6dzq1">
                                                                                    <polyline points="16 16 12 12 8 16">
                                                                                    </polyline>
                                                                                    <line x1="12" y1="12" x2="12" y2="21">
                                                                                    </line>
                                                                                    <path
                                                                                        d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                                                                    </path>
                                                                                    <polyline points="16 16 12 12 8 16">
                                                                                    </polyline>
                                                                                </svg>
                                                                            </a>
                                                                        @endif
                                                                </td>                                                                                                                                
                                                                <td>

                                                                <button class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                                        type="button"
                                                                        wire:click.prevent="haveKey({{$item_lote->id}}, '{{$item_lote->presentacion->barcode}}', {{$item_lote->presentacion->product->id}})"
                                                                        title="print" 
                                                                        style="background:#f39022;">
                                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                                            stroke="currentColor" stroke-width="2"
                                                                            fill="none" stroke-linecap="round"
                                                                            stroke-linejoin="round" class="css-i6dzq1">
                                                                            <polyline points="6 9 6 2 18 2 18 9">
                                                                            </polyline>
                                                                            <path
                                                                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                                            </path>
                                                                            <rect x="6" y="14" width="12" height="8">
                                                                            </rect>
                                                                        </svg></button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach
                                </div>
                                @else
                                No hay lotes asociados a este sabor.
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>




            </div>



        </div>

        @include('livewire.LotesNew.form')
    </div> 
</div>






<script>
    document.addEventListener('DOMContentLoaded', function() {

        window.livewire.on('show-modal', msg => {
            $('#theModal').modal('show')
        });
        window.livewire.on('lote-added', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('lote-updated', msg => {
            $('#theModal').modal('hide')
        });
        window.livewire.on('producto-creado', msg =>  {
            console.log('kkkkkkk')
        });
        window.livewire.on('abrir-qr', msg => {        
            window.open(msg, '_blank');
        });
    });

    function Cambio(id, id_lote) {
        Swal.fire({
            title: '¿Pasar de Crudo a Pre-Cocido? (Boxes)',
            html: '<input type="number" id="pre" class="swal2-input" placeholder="Ingrese la cantidad">',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar',
            preConfirm: function() {
                var inputValue = document.getElementById('pre').value;
                var mensaje = "ID: " + id + ", Cantidad: " + inputValue + "Id del lote: " + id_lote;
                //  alert(mensaje);

                // Luego, emite el evento con el ID y la cantidad
                window.livewire.emit('Cambio', id, inputValue, id_lote);
            }
        }).then(function(result) {
            if (result.value) {
                // El usuario hizo clic en "Aceptar"
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
    const input = document.querySelector(".table-search");

    input.addEventListener("keyup", function () {
        const query = this.value.toLowerCase();

        document.querySelectorAll("#accordion .card").forEach(card => {
            const header = card.querySelector(".card-header");
            const saborTitulo = header?.querySelector("h3")?.textContent.toLowerCase() || "";

            let matchEtiquetaPrincipal = saborTitulo.includes(query);
            let matchLote = false;

            // Filtrar filas internas (lotes)
            card.querySelectorAll("table tbody tr").forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const isMatch = rowText.includes(query);
                row.style.display = isMatch ? "" : "none";
                if (isMatch) matchLote = true;
            });

            // Mostrar la tarjeta si hay match en la etiqueta principal o en alguno de sus lotes
            if (matchEtiquetaPrincipal || matchLote) {
                card.style.display = "";
                card.classList.add("show"); // opcional para que se mantenga visible
            } else {
                card.style.display = "none";
            }
        });
    });
});



    </script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('input[name^="cantidad"]').forEach(input => {
            input.addEventListener('input', function () {
                let value = this.value.trim();
    
                // Permitir números enteros (e.g. 1, 2, 10) o exactamente 0.2
                const isValid = /^\d+$/.test(value) || value === '0.2';
    
                if (!isValid) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
    </script>
    
</div>