<div class="row">
	
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="text-center"><h4><b> <i class="fas fa-coins"></i> Resumen de Ventas</b></h4></div>
				<div class="">
					<div class="simple-title-task ui-sortable-handle">
						<div class="card-body">
							
							<div class="task-header">
								<div>
									<h6>Total: ${{number_format($total,2)}}</h6>
									<hr>
									<h6>Descuentos = ${{isset($totalDescuento) ? $totalDescuento : "0"}}</h6>
									<hr>
									{{-- Recorrer con un foreach para ver los servicios 
									@if (isset($servicesAdd[0]))
										@foreach ($servicesAdd as $service)
											@if ($service->state === 1)
												<h6>{{ $service->catalogoService->name }} = ${{isset($service->amount) ? $service->amount : "0"}} 
													@if ($service->id)
													 <br />	<span class="text-danger"><small><b>Solo para entregas programadas o mismo dia.</b></small></span>
													@endif
											</small></h6>
												<hr>
											@endif
										@endforeach
									@endif
									--}}
									<h5><b>Total A Pagar = ${{isset($totalDescuento) ? number_format($total - $totalDescuento,2) : number_format($total,2)}}</b></h5>
									<input type="hidden" id="hiddenTotal" value="{{isset($totalDescuento) ? number_format($total - $totalDescuento,2) : number_format($total,2)}}">
								</div>
								<div>
									<hr>
									<span class="badge badge-success">ARTICULOS: {{$itemsQuantity}}</span>
								</div>


							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>