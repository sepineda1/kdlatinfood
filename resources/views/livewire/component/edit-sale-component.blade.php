<div>
    <div wire:ignore.self class="modal fade" id="editSaleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="max-width:90%;">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <h5 class="modal-title text-white">
                        <b><i class="fas fa-shopping-cart"></i> Shop Cart #{{ $saleId }} Edit</b>
                    </h5>
                    <h6 class="text-center text-warning" wire:loading>PLEASE WAIT</h6>
                </div>
                @php
                    $totalCalculado = 0;

                    foreach ($detailsEdit as $item) {
                        $itemTotal = $item->price * $item->quantity;

                        if ($item->discount > 0) {
                            $itemTotal -= (($item->price * $item->discount) / 100) * $item->quantity;
                        }

                        $totalCalculado += $itemTotal;
                    }
                    //SI pagó en efectivo
                    $saldoN = 0;
                    if (isset($saleData->cash) && $saleData->cash > 0) {
                        $totalPago = $saleData->cash - $saleData->change;
                        $saldoN = $totalCalculado - $totalPago;
                    } else {
                        //Pago a Credito
                        $saldoN = isset($saleData->total) ? $totalCalculado - $saleData->total : 0;
                    }
                    $TipoPago = isset($saleData->id) && $saleData->cash > 0 ? 'EFECTIVO' : 'CREDITO';
                @endphp
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="card">
                        <div class="card-body" style="font-size: 15px">
                            <div class="col-md-6">
                                <p><b><i class="fas fa-user"></i> Cliente :
                                        {{ isset($saleData->customer->name) ? $saleData->customer->full_name : ' Cliente No Encontrado' }}</b>
                                </p>
                                <p><b><i class="fas fa-shopping-bag"></i> Total :
                                        ${{ isset($saleData->id) ? $saleData->total : 'Venta No Encontrada' }}</b></p>
                            </div>
                        </div>
                    </div>
                    <form>
                        <button type="button" wire:click.prevent="toggleAddProduct"
                            class="btn btn-warning mt-1">Agregar
                            Producto</button>
                        <table class="table table-bordered table-striped mt-1">
                            <thead class="text-white" style="background: #FF5100">
                                <tr>
                                    <th class="table-th text-white text-center">FOLIO</th>
                                    <th class="table-th text-white text-center">SKU</th>
                                    <th class="table-th text-white text-center">PRODUCT</th>
                                    <th class="table-th text-white text-center">QTY</th>
                                    <th class="table-th text-white text-center">PRICE</th>
                                    <th class="table-th text-white text-center">DISCOUNT</th>
                                    <th class="table-th text-white text-center">TOTAL</th>
                                    <th class="table-th text-white text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $k = 1;
                                $total = 0; ?>
                                @foreach ($detailsEdit as $key => $d)
                                    <tr>
                                        @php
                                            $presentacionData = json_decode($d->product, true);
                                            $productData = json_decode($d->product->product, true);
                                            //$productData = json_decode($d->product->product, true);
                                            $size = json_decode($d->product->size, true);
                                        @endphp
                                        <?php
                                        $subtotal = round($d->discount > 0 ? ($d->price - ($d->price * $d->discount) / 100) * $d->quantity : $d->price * $d->quantity, 2);
                                        $total += $subtotal;
                                        
                                        ?>
                                        <td class='text-center'>
                                            <p> <b>
                                                    {{ $k++ }} </b></p>
                                        </td>
                                        <td class='text-center'>
                                            <p>{{ $presentacionData['barcode'] ?? 'N/A' }}</p>
                                        </td>
                                        <td class='text-center'>
                                            <p><b>{{ $productData['name'] . ' ' . $size['size'] . ' ' . $productData['estado'] ?? 'N/A' }}</b>
                                            </p>
                                        </td>
                                        <td class='text-center d-flex'>
                                            {{-- wire:click="minusProductToSaleButton({{$key}})"   wire:click="addProductToSaleButton({{$key}})" --}}
                                            <button type="button" class="btn btn-rounded "
                                                onclick="pedirCantidad({{ $key }})"><i
                                                    class="fas fa-minus"></i></button>
                                            <input type="number" style="max-width:100px;"
                                                wire:model="quantities.{{ $key }}" readonly
                                                class="form-control text-center btn-rounded"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                                            <button type="button" class="btn btn-rounded "
                                                onclick="pedirCantidad({{ $key }}, 'aumentar')"><i
                                                    class="fas fa-plus"></i></button>
                                        </td>
                                        <td class="text-center">
                                            <b>${{ $d->price }}</b>
                                        </td>
                                        <td class="text-center">
                                            {{ $d->discount == null ? '0.00' : $d->discount }}%
                                        </td>
                                        <td class="text-center">
                                            <b>${{ $subtotal }}</b>
                                        </td>
                                        <td class='text-center'>
                                            <button type="button" onclick="removeProduct({{ $key }})"
                                                class="btn btn-danger btn-rounded"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach

                                <!-- Agregar nueva fila para productos -->
                                @if ($addProduct)
                                    <tr>
                                        <td class='text-center'>

                                        </td>
                                        <td class='text-center' colspan="2">
                                            {{-- <input
                                                type="text"
                                                id="searchProduct"
                                                class="form-control mb-2"
                                                placeholder="🔍 Buscar producto..."
                                            > --}}
                                            {{-- <select wire:model="newProducts.sku" class="form-control" id="selectSku">
                                                <option value="">Seleccione SKU</option>
                                                @foreach ($prod as $product)
                                                    @if ($product->visible == 'si')
                                                        <option value="{{ $product->barcode }}">
                                                            {{ $product->barcode }} -
                                                            {{ $product->product->name }} {{ $product->size->size }}
                                                            {{ $product->product->estado }}</option>
                                                    @endif
                                                @endforeach
                                            </select>- --}}

                                            <!-- Datalist que alimenta el input -->

                                            <div class="input-group mb-2">
                                                <input type="text" id="searchProduct" list="productosList"
                                                       class="form-control" placeholder="🔍 Buscar SKU o nombre..."
                                                       autocomplete="off">
                                                <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Borrar búsqueda">
                                                  <i class="fas fa-paint-brush"></i>
                                                </button>
                                              </div>
                                              

                                            {{-- Input oculto para Livewire --}}
                                            <input type="hidden" id="skuInput" wire:model.lazy="newProducts.sku">

                                            <datalist id="productosList">
                                                <option value="">Seleccione...</option>
                                                @foreach ($prod as $product)
                                                    @if ($product->visible === 'si')
                                                        <option
                                                            value="{{ $product->barcode }} - {{ $product->product->name }} {{ $product->size->size }} {{ $product->product->estado }}">
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </datalist>
                                        </td>
                                        <td class='text-center d-none'>
                                            <input type="text" wire:model="newProducts.name"
                                                class="form-control d-none">
                                        </td>
                                        <td class='text-center'>
                                            <input type="number" wire:model="newProducts.items" class="form-control"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                                        </td>
                                        <td class='text-center'>
                                            <!--<input type="number" wire:model="newProducts.items" class="form-control">-->
                                            <input id="inputPrice" wire:model="newProducts.price" class="form-control text-center"
                                                type="number" step="0.01" disabled />
                                        </td>
                                        <td class='text-center'>
                                            <!--<input type="number" wire:model="newProducts.items" class="form-control">-->
                                            <input id="inputDiscount" wire:model="newProducts.discount" class="form-control"
                                                type="number" step="0.01" disabled />
                                        </td>
                                        <td class='text-center' colspan="2">
                                            <button type="button" wire:click.prevent="removeNewProduct"
                                                class="btn btn-danger btn-rounded"><i
                                                    class="fas fa-times"></i></button>
                                            <button type="button" onclick="loader()"
                                                wire:click.prevent="addProductRow({{ isset($saleData->id) && $saleData->cash > 0 ? true : false }})"
                                                class="btn btn-success btn-rounded"><i
                                                    class="fas fa-save"></i></button>
                                        </td>

                                    </tr>
                                    <script>
                                        const search = document.getElementById('searchProduct');
                                        const skuHidden = document.getElementById('skuInput');
                                        const clearBtn = document.getElementById('clearSearch');

                                        // Cuando el usuario selecciona (o sale del input)…
                                        search.addEventListener('change', () => {
                                            // extraemos sólo el SKU antes de ' - '
                                            const raw = search.value.split(' - ')[0].trim();
                                            // asignamos al input oculto y disparamos change para Livewire
                                            skuHidden.value = raw;
                                            skuHidden.dispatchEvent(new Event('change'));
                                        });

                                        // opcional: si pulsas Enter en el buscador, fuerza el cambio y quita foco
                                        search.addEventListener('keydown', e => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                search.blur(); // esto lanzará 'change'
                                            }
                                        });

                                        // Borrador: limpia ambos campos
                                        clearBtn.addEventListener('click', () => {
                                            search.value = '';
                                            skuHidden.value = '';
                                            skuHidden.dispatchEvent(new Event('change'));
                                            search.focus();
                                        });
                                    </script>
                                @endif
                            </tbody>

                            <tfoot>
                                <tr style="background: #e4e4e4">
                                    <td class='text-center'></td>
                                    <td class='text-center' colspan="2"></td>
                                    <td class='text-center '>Total</td>
                                    <td class='text-center '><b>${{ $total }}</b></td>
                                    <td class='text-center'></td>
                                    <td class='text-center' colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal"
                        onclick="location.reload()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade" id="modalSaldo" tabindex="-1" role="dialog"
        aria-labelledby="modalSaldoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark ">
                    <h5 class="modal-title text-white" id="modalSaldoLabel"><i class="fas fa-splotch"></i> ¿Estas
                        seguro
                        de realizar esta operación?</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <p style="font-size:18px;">Para agregar este producto, Se recibe en <b>EFECTIVO</b> la suma de <b>
                            ${{ number_format($TotalCash, 2) }} </b>.</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="addProductRow({{ false }})" onclick="loader()"
                        class="btn btn-success"><i class="fas fa-coins"></i> Aceptar</button>
                    <button type="button" class="btn btn-danger text-dark" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .swal2-container {
            z-index: 999999 !important;
            pointer-events: auto !important;
        }

        .swal2-popup {
            pointer-events: auto !important;
        }

        body.swal2-shown {
            overflow: auto !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ----------------------------------------------------------------
            // Helper: SweetAlert Loader
            // ----------------------------------------------------------------

            // ----------------------------------------------------------------
            // Confirmar eliminación de toda la orden
            // ----------------------------------------------------------------
            Livewire.on('confirm-delete-order-modal', ({
                quantity,
                saleDetailID,
                hasCash
            }) => {
                Swal.fire({
                    title: '¿Eliminar orden?',
                    text: "Si restas esta cantidad, se eliminará la orden completa. ¿Deseas continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#f0f0f0',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(result => {
                    if (result.isConfirmed || result.value) {
                        loader();
                        Livewire.emit('confirm-decrement-after-delete', {
                            quantity,
                            saleDetailID,
                            hasCash
                        });
                    }
                });
            });

            // ----------------------------------------------------------------
            // Confirmar operación financiera (añadir/quitar con efectivo)
            // ----------------------------------------------------------------
            Livewire.on('confirm-cash-order-modal', ({
                saleId,
                barcode,
                quantity,
                text,
                setting,
                saleDetailID,
                hasCash
            }) => {
                Swal.fire({
                    title: '¿Deseas realizar esta operación financiera?',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, aceptar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#f0f0f0',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(result => {
                    if (result.isConfirmed || result.value) {
                        loader();
                        Livewire.emit('confirm-cash-order-automatic', {
                            saleId,
                            barcode,
                            quantity,
                            setting,
                            saleDetailID,
                            hasCash
                        });
                    }
                });
            });

            // ----------------------------------------------------------------
            // Error de venta
            // ----------------------------------------------------------------
            Livewire.on('sale-error', msg => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Venta',
                    text: msg,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#d33'
                });
            });

            Livewire.on('sale-error', msg => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Venta',
                    text: msg,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#d33'
                });
            });
            Livewire.on('productAdded', () => {
                document.getElementById('searchProduct').value = '';
                document.getElementById('skuInput').value = '';
                document.getElementById('inputPrice').value = 0;
                document.getElementById('inputDiscount').value = 0;
            });

            // ----------------------------------------------------------------
            // Preguntar cantidad a aumentar/disminuir
            // ----------------------------------------------------------------


            window.pedirCantidad = function(key, accion = 'disminuir') {
                const esAumentar = accion === 'aumentar';
                Swal.fire({
                    title: esAumentar ? '¿Cuántos ítems deseas aumentar?' :
                        '¿Cuántos ítems deseas quitar?',
                    input: 'number',
                    inputAttributes: {
                        min: 1,
                        step: 1,
                    },
                    inputValue: 1,
                    inputPlaceholder: 'Ingresa una cantidad',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    preConfirm: value => {
                        const n = parseInt(value);
                        if (!n || n < 1) {
                            Swal.showValidationMessage('Por favor, ingresa un número mayor a 0');
                            return false;
                        }
                        return n;
                    }
                }).then(result => {
                    if (result.isConfirmed || result.value) {
                        loader();
                        const cantidad = result.value;
                        const evento = esAumentar ? 'aumentarCantidad' : 'decrementarCantidad';
                        Livewire.emit(evento, key, cantidad);
                    }
                });
            };

            // ----------------------------------------------------------------
            // Confirmar eliminación de un producto (ítem) individual
            // ----------------------------------------------------------------
            window.removeProduct = function(key) {
                Swal.fire({
                    title: '¿Eliminar este producto?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#f0f0f0',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(result => {
                    loader();
                    if (result.isConfirmed || result.value) {
                        Livewire.emit('removeProduct', key);
                    }
                });
            };

            // ----------------------------------------------------------------
            // Control de modales Bootstrap + Livewire
            // ----------------------------------------------------------------
            window.addEventListener('showModalSaldo', () => $('#modalSaldo').modal('show'));
            window.addEventListener('hideModalSaldo', () => $('#modalSaldo').modal('hide'));
            Livewire.on('keepModalOpen', () => {
                $('.modal-backdrop').remove();
                $('#editSaleModal').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                });
            });
            Livewire.on('hideModalEdit', () => {
                $('#editSaleModal').modal('hide');
                $('.modal-backdrop').remove();
                // recarga la página actual
                window.location.reload();
            });

            window.addEventListener('showEditModal', () => {
                $('.modal-backdrop').remove();
                $('#editSaleModal').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false
                });
            });
            window.addEventListener('hideEditModal', () => {
                $('#editSaleModal').modal('hide');
                $('.modal-backdrop').remove();
            });
        });
    </script>


</div>
