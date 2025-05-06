@include('common.modalHead')

<div class="row">
@csrf

	@if ($select_options_products && $select_options_list_products && count($select_options_list_products) > 0)
		<div class="col-sm-12 col-md-12">
			<div class="form-group">
				<label>Seleccione la presentación</label>
				<select class="form-control" onchange="loader(); @this.call('onChangeProductData', $event.target.value)" wire:change="onChangeProductData($event.target.value)" name="" id="">
					@foreach ($select_options_list_products as $itemOptions)
						<option value="{{ $itemOptions->id }}">{{ $itemOptions->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
	@endif

	<div class="col-sm-12 col-md-12">
		<div class="form-group">
			<label>Name</label>
			<input type="text" wire:model="name" class="form-control product-name" placeholder="ej: Empanada" autofocus>
			@error('name') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-12">
		<div class="form-group">
			<label>Descripcion</label>
			<input type="text" wire:model="descripcion" class="form-control " placeholder="ej: descripcion" autofocus>
			@error('descripcion') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-12">
		<div class="form-group">
			<label>Sabor</label>
			<select wire:model='saborID' class="form-control ">
				<option value="Elegir" disabled>Elegir</option>
				@foreach($sabores as $sabor)
				<option value="{{$sabor->id}}">{{$sabor->nombre}}</option>
				@endforeach
			</select>
			@error('saborID') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-12">
		<div class="form-group custom-file">
			<input type="file" class="custom-file-input" wire:model="image" accept="image/x-png, image/gif, image/jpeg">
			<label class="custom-file-label">Imágen {{$image}}</label>
			@error('image') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<br><br>

	<div class="col-md-12">
		<div class="alert alert-warning mt-2">
			@if ($selected_id > 0 )
				<p><b><i class="fas fa-exclamation-triangle"></i> Si deseas editar alguno de los siguientes datos, selecciona la presentación asociada a este productos.</b></p>
			@else
				<p><b><i class="fas fa-exclamation-triangle"></i> Los siguientes datos que aparecen en el formulario son netamentes infomativos, <b>NO</b> se utilizaran en procedimiento internos de inventarios.</b></p>
			@endif
		
		</div>
	</div>

	

	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Code</label>
			@if($selected_id>0)
				<input type="text" wire:model="barcode" class="form-control" readonly>
			@else

			<input type="text" wire:model="barcode" name="barcode" class="form-control" placeholder="SKU">

			@endif
			@error('barcode') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>



	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Costo</label>
			<input type="text" wire:model.lazy="cost" wire:change="calcularPrecio" @if($selected_id>0) disabled @endif class="form-control" placeholder="ej: 0.00">
			@error('cost') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>
	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Box 1</label>
			<input type="number"  wire:model="tam1" wire:change="calcularPrecio" @if($selected_id>0) disabled @endif  class="form-control" placeholder="ej: 30">
			@error('tam1') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>
	<div class="col-sm-12 col-md-4 d-none">
		<div class="form-group">
			<label>Box 2</label>
			<input type="number"  wire:model="tam2" class="form-control" placeholder="ej: 30">
			@error('tam2') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-6">
		<div class="form-group">
			<label>Price</label>
			<input type="text" data-type='currency' wire:model="price" readonly  class="form-control" placeholder="ej: 0.00">
			@error('price') <span class="text-danger er">{{ $message}}</span>@enderror
			<small><span class="alert alert-warning">Este precio es el que se muestra en la APP Movil.</span></small>
		</div>
	</div>

	<div class="col-sm-12 col-md-4 d-none">
		<div class="form-group">
			<label>Stock</label>
			<input type="number" wire:model="stock" class="form-control" placeholder="ej: 0">
			@error('stock') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-4 d-none">
		<div class="form-group">
			<label>Alert</label>
			<input type="number" wire:model="alerts" class="form-control" placeholder="ej: 10">
			@error('alerts') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>
	
	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Size</label>
			<select wire:model='size_id' class="form-control " @if($selected_id>0) disabled @endif>
				<option value="Elegir" disabled>Elegir</option>
				@foreach($sizes as $size)
				<option value="{{$size->id}}">{{$size->size}}</option>
				@endforeach
			</select>
			@error('size_id') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Category</label>
			<select wire:model='categoryid' class="form-control" @if($selected_id>0) disabled @endif>
				<option value="Elegir" disabled>Elegir</option>
				@foreach($categories as $category)
				<option value="{{$category->id}}">{{$category->name}}</option>
				@endforeach
			</select>
			@error('categoryid') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>

	
	<div class="col-sm-12 col-md-4">
		<div class="form-group">
			<label>Estado</label>
			<select wire:model='estado' class="form-control"  @if($selected_id>0) disabled @endif>
				<option value="Elegir" disabled>Elegir</option>

				<option value="CRUDO">CRUDO</option>
				<option value="PRECOCIDO">PRE-COCIDO</option>


			</select>
			@error('estado') <span class="text-danger er">{{ $message}}</span>@enderror
		</div>
	</div>





</div>




@include('common.modalFooter')