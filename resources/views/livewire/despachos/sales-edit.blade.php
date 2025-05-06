<div wire:ignore.self class="modal fade" id="Edit" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog " role="document" style="max-width: 80%;">
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

                //$saldo = isset($saleData->cash) && $saleData->cash > 0 ? $saleData->cash - $totalCalculado: "0.00";

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


            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="card">
                    <div class="card-body" style="font-size: 15px">
                        <div class="row">
                            <div class="col-md-6">
                                <p><b><i class="fas fa-user"></i> Cliente :
                                        {{ isset($saleData->customer->name) ? $saleData->customer->full_name : ' Cliente No Encontrado' }}</b>
                                </p>
                                <p><b><i class="fas fa-shopping-bag"></i> Total :
                                        ${{ isset($saleData->id) ? $saleData->total : 'Venta No Encontrada' }}</b></p>
                            </div>

                        </div>

                    </div>
                </div>
                <form wire:submit.prevent="updateSale">
                    <button type="button" wire:click.prevent="toggleAddProduct"
                        class="btn btn-warning btn-rounded mt-1"><i class="fas fa-plus"></i> Agregar
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
                                        <select wire:model="newProducts.sku" class="form-control" id="selectSku">
                                            <option value="">Seleccione SKU</option>
                                            @foreach ($prod as $product)
                                                @if ($product->visible == 'si')
                                                    <option value="{{ $product->barcode }}">{{ $product->barcode }} -
                                                        {{ $product->product->name }} {{ $product->size->size }}
                                                        {{ $product->product->estado }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class='text-center d-none'>
                                        <input type="text" wire:model="newProducts.name" class="form-control d-none">
                                    </td>
                                    <td class='text-center'>
                                        <input type="number" wire:model="newProducts.items" class="form-control"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')">
                                    </td>
                                    <td class='text-center'>
                                        <!--<input type="number" wire:model="newProducts.items" class="form-control">-->
                                        <input wire:model="newProducts.price" class="form-control text-center"
                                            type="number" step="0.01" disabled />
                                    </td>
                                    <td class='text-center'>
                                        <!--<input type="number" wire:model="newProducts.items" class="form-control">-->
                                        <input wire:model="newProducts.discount" class="form-control" type="number"
                                            step="0.01" disabled />
                                    </td>
                                    <td class='text-center' colspan="2">
                                        <button type="button" wire:click.prevent="removeNewProduct"
                                            class="btn btn-danger btn-rounded"><i class="fas fa-times"></i></button>
                                        <button type="button" onclick="loader()"
                                            wire:click.prevent="addProductRow({{ isset($saleData->id) && $saleData->cash > 0 ? true : false }})"
                                            class="btn btn-success btn-rounded"><i class="fas fa-save"></i></button>
                                    </td>

                                </tr>
                            @endif




                        </tbody>

                        <tfoot>
                            <tr style="background: #e4e4e4">
                                <td class='text-center'>

                                </td>
                                <td class='text-center' colspan="2">

                                </td>
                                <td class='text-center d-none'>

                                </td>
                                <td class='text-center '>
                                    Total
                                </td>
                                <td class='text-center '>

                                    <b>${{ $total }}</b>
                                </td>
                                <td class='text-center'>


                                </td>
                                <td class='text-center' colspan="2">

                                </td>

                            </tr>
                        </tfoot>
                    </table>




            </div>
            <div class="modal-footer">
                <button type="submit" wire:click.prevent="updateSale"
                    class="btn btn-primary btn-rounded d-none">Actualizar
                    Venta</button>
                <button type="button" class="btn btn-dark close-btn text-info" data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                    Close
                </button>
            </div>
            </form>
        </div>
    </div>
    <script>
        function removeProduct(key) {
            swal({
                title: 'CONFIRM LOAD DELIVERY',
                text: 'THIS ACTION CANT RETURN',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#fff',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Confirm'
            }).then(function(result) {
                if (result.value) {


                    // Emitir el evento 'CargarPedido' con el ID del pedido
                    window.livewire.emit('removeProduct', key);

                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {

            window.livewire.on('hidden.bs.modal', msg => {
                $('.er').css('display', 'none')
            });
        })
    </script>
</div>

<div wire:ignore.self class="modal fade" id="modalSaldo" tabindex="-1" role="dialog"
    aria-labelledby="modalSaldoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark ">
                <h5 class="modal-title text-white" id="modalSaldoLabel"><i class="fas fa-splotch"></i> ¿Estas seguro
                    de realizar esta operación?</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size:18px;">Para agregar este producto, Se recibe en <b>EFECTIVO</b> la suma de <b>
                        ${{ number_format($TotalCalculado, 2) }} </b>.</p>
            </div>
            <div class="modal-footer">
                <button wire:click="addProductRow({{ false }})" onclick="loader()"
                    class="btn btn-success"><i class="fas fa-coins"></i> Aceptar</button>
                <button type="button" class="btn btn-danger text-dark" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('showModalSaldo', () => {
        $('#modalSaldo').modal('show');
    });
    window.addEventListener('hideModalSaldo', () => {
        $('#modalSaldo').modal('hide');
    });
</script>
{{--
<div wire:ignore.self class="modal fade" id="modalSaldo" tabindex="-1" role="dialog" aria-labelledby="modalSaldoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header bg-dark ">
          <h5 class="modal-title text-white" id="modalSaldoLabel"><i class="fas fa-splotch"></i> {{ $tipoSaldo == "Pendiente" ? "Saldar Adeudo" : "Devolución de Dinero"  }} </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de realizar esta Operación?</p>
          <div class="form-group">
            <label for="montoPago">Cantidad </label>
            <input type="number" readonly class="form-control" value="{{abs($saldoModal)}}" />
          </div>
          @if ($tipopagoModal == 'CREDITO')
            @if ($tipoSaldo == 'Pendiente')
            <p> <i class="fas fa-sticky-note"></i> Se descontará <b>${{ abs($saldoModal) }}</b> del cupo del cliente.. Actualmente el cupo del cliente es <b>${{ number_format($saleData->customer->saldo,2)}}</b>, y quedará en <b>${{ number_format($saleData->customer->saldo - abs($saldoModal),2) }}</b>.</p>
            @endif
            @if ($tipoSaldo == 'Favor')
            <p><i class="fas fa-sticky-note"></i> Se agregará <b>${{ abs($saldoModal) }}</b> al cupo del cliente.. Actualmente el cupo del cliente es <b>${{ number_format($saleData->customer->saldo,2)}}</b>, y quedará en <b>${{ number_format($saleData->customer->saldo + abs($saldoModal),2) }}</b>.</p>
            @endif
          @endif

          @if ($tipopagoModal == 'EFECTIVO')
            @if ($tipoSaldo == 'Pendiente')
            <p><i class="fas fa-sticky-note"></i> Se Recibirá al cliente, la suma de <b>${{ abs($saldoModal) }}</b> correspondiente al SALDO PENDIENTE de esta Orden.</p>
            @endif
            @if ($tipoSaldo == 'Favor')
            <p><i class="fas fa-sticky-note"></i> Se Devolverá al cliente, la suma de <b>${{ abs($saldoModal) }}</b> correspondiente al SALDO A FAVOR de esta Orden.</p>
            @endif
          @endif
        </div>
        <div class="modal-footer">
          <button wire:click="AcceptModal" onclick="loader()" class="btn btn-success"><i class="fas fa-coins"></i> Aceptar</button>
          <button type="button" class="btn btn-danger text-dark" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    window.addEventListener('showModalSaldo', () => {
        $('#modalSaldo').modal('show');
    });
    window.addEventListener('hideModalSaldo', () => {
        $('#modalSaldo').modal('hide');
    });
</script>
--}}

<script>
    function pedirCantidad(key, accion = 'disminuir') {
        const esAumentar = accion === 'aumentar';

        Swal.fire({
            title: esAumentar ? '¿Cuántos Items deseas Aumentar?' : '¿Cuántos Items deseas Quitar?',
            input: 'number',
            inputAttributes: {
                min: '1',
                step: '1',
                inputmode: 'numeric'
            },
            inputPlaceholder: 'Ingresa una cantidad',
            showCancelButton: true,
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#f0f0f0',
            // Enfocar input al abrir (asegura interacción inmediata)
            didOpen: () => {
                const input = Swal.getInput();
                if (input) {
                    input.removeAttribute('readonly'); // Por si algún navegador lo impone
                    input.focus();
                }
            },
            preConfirm: (cantidad) => {
                if (!cantidad || parseInt(cantidad) < 1) {
                    Swal.showValidationMessage('Por favor, ingresa un número válido mayor a 0');
                    return; // ← esto detiene el proceso si la validación falla
                }
                return parseInt(cantidad); // ← este valor llega al .then(result.value)
            }
        }).then((result) => {
            // Aquí usamos un retraso con setTimeout para dar tiempo a que el modal se cierre.
            setTimeout(() => {
                console.log(result);
                if (result.value) {
                    // Emitir evento Livewire según acción
                    if (esAumentar) {
                        loader();
                        Livewire.emit('aumentarCantidad', key, parseInt(result.value));
                    } else {
                        loader();
                        Livewire.emit('decrementarCantidad', key, parseInt(result.value));
                    }
                }
            }, 50); // Este retraso de 50ms da tiempo a que el modal termine de cerrarse
        });
    }

    
</script>
