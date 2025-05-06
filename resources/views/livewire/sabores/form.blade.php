<div>
	@include('common.modalHead')
	<div class="row">

		<div class="col-sm-12 col-md-6">
			<div class="form-group">
				<label>Name</label>
				<input type="text" wire:model.lazy="nombre" class="form-control product-name" placeholder="ej: Pollo"
					autofocus>
				@error('nombre') <span class="text-danger er">{{ $message}}</span>@enderror
			</div>
		</div>		
		<div class="col-sm-12 col-md-6">
			<div class="form-group">
				<label>Description (Optional)</label>
				<input type="text" wire:model.lazy="description" class="form-control product-name"
					placeholder="ej: Pollo" autofocus>
				@error('description') <span class="text-danger er">{{ $message}}</span>@enderror
			</div>
		</div>
		<!--<div class="col-sm-12 col-md-6">
			<div class="form-group">
				<label>Libra Consumo</label>
				<input type="number" wire:model.lazy="libra_consumo" class="form-control product-name"
					placeholder="ej: 0.666" autofocus>
				@error('libra_consumo') <span class="text-danger er">{{ $message}}</span>@enderror
			</div>
		</div>-->

	</div>

	@include('common.modalFooter')
</div>