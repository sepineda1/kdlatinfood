<div wire:ignore.self class="modal fade" id="theModal" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>{{$componentName}}</b> | {{ $selected_id > 0 ? 'Edit' : 'Create' }}
                </h5>


                <button type="button" wire:loading class="btn btn-success close-btn text-info">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span style="color:white">PLEASE WAIT</span>
                </button>

            </div>
            <div class="modal-body">

<div class="row">
	
<div class="col-sm-12 col-md-8">
	<div class="form-group">
		<label >Nombre</label>
		<input type="text" wire:model.lazy="name" 
		class="form-control" placeholder="ej: ALberto"  >
		@error('name') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>
<div class="col-sm-12 col-md-4">
	<div class="form-group">
		<label >Teléfono</label>
		<input type="text" wire:model.lazy="phone" 
		class="form-control" placeholder="ej: 351 115 9550" maxlength="10" >
		@error('phone') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>
<div class="col-sm-12 col-md-6">
	<div class="form-group">
		<label >Email</label>
		<input type="text" wire:model.lazy="email" 
		class="form-control" placeholder="ej: alberto@gmail.com"  >
		@error('email') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>
<div class="col-sm-12 col-md-6">
	<div class="form-group">
		<label >Contraseña</label>
		<input type="password" wire:model.lazy="password" 
		class="form-control"   >
		@error('password') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>
<div class="col-sm-12 col-md-6">
	<div class="form-group">
		<label >Estatus</label>
		<select wire:model.lazy="status" class="form-control">
			<option value="Elegir" selected>Elegir</option>
			<option value="Active" selected>Activo</option>
			<option value="Locked" selected>Bloqueado</option>
		</select>
		@error('status') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>
<div class="col-sm-12 col-md-6">
	<div class="form-group">
		<label >Asignar Role</label>
		<select wire:model="profile" class="form-control">
			<option value="Elegir" selected>Elegir</option>
			@foreach($roles as $role)
			<option value="{{$role->name}}" selected>{{$role->name}}</option>
			@endforeach
		</select>
		@error('profile') <span class="text-danger er">{{ $message}}</span>@enderror
	</div>
</div>

<div class="col-sm-12 col-md-6">
	<div class="form-group">
		<label >Imágen de Perfil</label>
		<input type="file" wire:model="image" accept="image/x-png, image/jpeg, image/gif" class="form-control">
		@error('image') <span class="text-danger er">{{ $message}}</span>@enderror

	</div>
</div>
</div>

</div>
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
            title: 'Actualizando datos',
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
    function loaderSave() {
    swal({
        title: 'Guardando datos',
        text: 'Por favor, espere...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        onOpen: () => {
            swal.showLoading();

            // Cierra el cuadro si el producto se crea correctamente
            window.livewire.on('producto-creado', () => {
                swal.close();
            });

            // Cierra el cuadro y muestra un error si ocurre un problema
            window.livewire.on('sale-error', (errorMessage) => {
                swal.close(); // Cierra el diálogo actual
                swal({
                    icon: 'error',
                    title: 'Error al guardar',
                    text: errorMessage || 'Ocurrió un error inesperado.',
                    confirmButtonText: 'Cerrar'
                });
            });
        }
    });
}
</script>
<div class="modal-footer">

    <button type="button" wire:click.prevent="resetUI()" class="btn btn-dark close-btn text-info" data-dismiss="modal">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
            stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        Close
    </button>

    @if($selected_id < 1)
        <button type="button" wire:click.prevent="Store()" onclick="loaderSave()"
            class="btn btn-warning mb-2 mr-2 btn-rounded close-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-folder">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            Save
        </button>
    @else
	    <button type="button" wire:click.prevent="Update()" onclick="loaderSave()"
            class="btn btn-warning mb-2 mr-2 btn-rounded close-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-folder">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            Save
        </button>
    @endif

</div>
</div>
</div>
</div>