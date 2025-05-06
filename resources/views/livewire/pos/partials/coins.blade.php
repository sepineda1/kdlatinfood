<!--
This software, includin any associated code, documentation and related material, is licensed solely by Oyarcegroup.com by accessing or using this software, you agree to comply with the following terms and conditions.
 This coding is licensed under the international standards IEEE and STHT, 833-3901-0093, the share, reproduction, sale or distribution without the consent of OyarceGroup.com is totally prohibited and may be criminally punished.

Oyarcegroup.com retains full ownership of this software, including all intellectual property rights associated with it. This license does not grant you any ownership rights or licenses except those explicitly provided herein.-->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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
            title: 'Realizando venta',
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
<div class="row mt-3">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">


                <div class="text-center">
                    <h4><b> <i class="fas fa-users"></i> Denominations</b></h4>
                </div>

              
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text" wire:model="buscar" class="form-control" placeholder="Buscar cliente...">
                        <select wire:model="cliente" id="cliente" name="cliente" class="form-control"
                            onchange="loaderText('Buscando Descuentos...')">
                            <option value="Elegir" disabled>Elegir</option>
                            @foreach ($data3 as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->name }}-{{ $cliente->last_name }}-
                                    ${{ $cliente->saldo }} USD</option>
                            @endforeach
                        </select>
                        @error('cliente')
                            <span class="text-danger er">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

				{{--
				<div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label>Tipo de Entrega</label>
                        <select wire:model="deliveryType" class="form-control" name="" id="">
							<option value="0">Selecciona el tipo de entrega</option>
                            @foreach ($deliveryTypes as $dt)
                                <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($deliveryType && $deliveryType != 2)
					<div class="col-sm-12 col-md-12">
						<div class="form-group mt-3">
							<label>Fecha y hora de entrega</label>
							<input type="datetime-local" wire:model="deliveryDate" class="form-control"
								min="{{ now()->format('Y-m-d\TH:i') }}">
						</div>
					</div>
                @endif	--}}


                <style type="text/css">
                    select[wire:model="cliente"] option {
                        display: none;
                    }
                </style>


                <div class="container">
                    <div class="row">
                        @foreach ($denominations as $d)
                            <div class="col-sm mt-2">

                                <button wire:click.prevent="ACash({{ $d->value }})"
                                    class="btn btn-dark btn-block den">
                                    {{ $d->value > 0 ? '$' . number_format($d->value, 2, '.', '') : 'Efectivo Exacto' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-sm mt-2">
                    {{-- <button wire:click.prevent="payWithCredit" 
				onclick="loader()"
				class="btn btn-dark btn-block den">
					Pagar con credito Cliente
				</button> --}}

                    <button wire:click.prevent="showDeliveryModal('credit')" class="btn btn-dark btn-block">Pagar con
                        crédito</button>
                </div>
                <div class="connect-sorting-content mt-4">
                    <div class="card simple-title-task ui-sortable-handle">
                        <div class="card-body">
                            <div class="input-group input-group-md mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-gp hideonsm"
                                        style="background: #f39022; color:white">Cash F8
                                    </span>
                                </div>
                                <input type="number" id="cash" wire:model="efectivo" wire:keydown.enter="saveSale"
                                    class="form-control text-center" value="{{ $efectivo }}">
                                <div class="input-group-append">
                                    <span wire:click="$set('efectivo', 0)" class="input-group-text"
                                        style="background: #3B3F5C; color:white">
                                        <i class="fas fa-backspace fa-2x"></i>
                                    </span>
                                </div>
                                @error('efectivo')
                                    <span class="text-danger er">{{ $message }}</span>
                                @enderror
                            </div>
                            <h4 class="text-muted">Devuelta: ${{ number_format($change, 2) }}</h4>

                            <div class="row justify-content-between mt-5">
                                <div class="col-sm-12 col-md-12 col-lg-6">
                                    @if ($total > 0)
                                        <button onclick="Confirm('','clearCart','SURE TO DELETE CART?')"
                                            class="btn btn-dark mtmobile">
                                            CANCEL F4
                                        </button>
                                    @endif
                                </div>

                                {{-- <div class="col-sm-12 col-md-12 col-lg-6">
								@if ($efectivo >= $total && $total > 0)
								<button wire:click.prevent="saveSale"
								onclick="loader()"
								class="btn btn-dark btn-md btn-block" style="  backgound: #FF5100;">SAVE F6</button>
								@endif
							</div> --}}
                                <div class="col-sm-12 col-md-12 col-lg-6">
                                    @if ($efectivo >= $total && $total > 0)
                                        <button wire:click.prevent="showDeliveryModal('cash')"
                                            class="btn btn-dark btn-md btn-block">SAVE F6</button>
                                    @endif
                                </div>

                            </div>




                        </div>
                        <div class="col-sm-12 mt-1 text-center">
                            <p class="text-muted" style="  color: #FF5100;">Print Last F7</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
