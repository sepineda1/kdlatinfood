<div  wire:ignore.self class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Selecciona tipo de entrega</h5>
                <button type="button" class="close" wire:click="closeDeliveryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                           
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h4><b> <i class="fas fa-coins"></i> Resumen de Ventas</b></h4>
                                        </div>
                                        <div class="">
                                            <div class="simple-title-task ui-sortable-handle">
                                                <div class="card-body">
    
                                                    <div class="task-header">
                                                        <div>
                                                            <h6>Total: ${{ number_format($total, 2) }}</h6>
                                                            <hr>
                                                            <h6>Descuentos =
                                                                ${{ isset($totalDescuento) ? $totalDescuento : '0' }}</h6>
                                                            <hr>
                    
                                                                @if (isset($servicesAdd[0]))
                                                                    @foreach ($servicesAdd as $service)
                                                                        @if ($service->state === 1 && $deliveryType != 3)
                                                                            <h6>{{ $service->catalogoService->name }} = ${{isset($service->amount) ? $service->amount : "0"}} 
                                                                                @if ($service->id)
                                                                                <br />	<span class="text-danger"><small><b>Solo para entregas programadas o mismo dia.</b></small></span>
                                                                                @endif
                                                                        </small></h6>
                                                                            <hr>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                               
                                                            <h5><b>Total A Pagar =
                                                                    ${{ isset($totalDescuento) ? number_format(($total + $montoService) - $totalDescuento, 2) : number_format($total + $montoService, 2) }}</b>
                                                            </h5>
                                                            <input type="hidden" id="hiddenTotal"
                                                                value="{{ isset($totalDescuento) ? number_format(($total + $montoService) - $totalDescuento, 2) : number_format($total + $montoService, 2) }}">
                                                        </div>
                                                        <div>
                                                            <hr>
                                                            <span class="badge badge-success">ARTICULOS:
                                                                {{ $itemsQuantity }}</span>
                                                        </div>
    
    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
    
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select wire:model="deliveryType" class="form-control">
                                <option value="0">Seleccione el tipo de entrega: </option>
                                @foreach ($deliveryTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
        
                        </div>
        
                        @if ($deliveryType && $deliveryType != 2)
                            <div class="form-group mt-3">
                                <label>Fecha y hora de entrega</label>
                                <input type="datetime-local" wire:model.defer="deliveryDate" class="form-control"
                                    min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('deliveryDate')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="alert alert-warning">
                            <p><b class="badge badge-warning">IMPORTANTE!</b></p>
                            <b>Tipo de Venta : {{ $pendingPayment }}</b> <br />
                            @if ($pendingPayment === 'credit')

                                Se restarán del cupo credito del usuario, la suma de <b> ${{ isset($totalDescuento) ? number_format(($total + $montoService) - $totalDescuento, 2) : number_format($total + $montoService, 2) }}</b>
                                correspondiente al valor de la venta.

                            @else

                                Se recibira, la suma en <b> EFECTIVO </b> de <b> ${{ isset($totalDescuento) ? number_format(($total + $montoService) - $totalDescuento, 2) : number_format($total + $montoService, 2) }}</b>
                                correspondiente al valor de la venta.

                            @endif
                           
                        </div>
                    </div>
                </div>
              

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeDeliveryModal()" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" wire:click="confirmDelivery" class="btn btn-primary"
                    onclick="loader()">Confirmar</button>
            </div>
        </div>
    </div>
</div>
