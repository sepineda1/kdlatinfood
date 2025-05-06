@include('common.modalHead')


<div class="row">
	
<div class="col-sm-12">
	<div class="input-group">
		<div class="input-group-prepend">
			<span class="input-group-text">
				<span class="fas fa-edit">
					
				</span>
			</span>
		</div>
		<input type="text" wire:model.lazy="size" class="form-control" placeholder="ej: Small" maxlength="255">
	</div>
	@error('size') <span class="text-danger er">{{ $message }}</span> @enderror
</div>





</div>





@include('common.modalFooter')