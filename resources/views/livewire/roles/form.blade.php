<div wire:ignore.self class="modal fade" id="theModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark">
        <h5 class="modal-title text-white">
        	<b>{{$componentName}}</b> | {{ $selected_id > 0 ? 'EDITAR' : 'CREAR' }}
        </h5>
        <h6 class="text-center text-warning" wire:loading>POR FAVOR ESPERE</h6>
      </div>
      <div class="modal-body">

        <div class="row">
          <div class="col-sm-12">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <span class="fas fa-edit">

                  </span>
                </span>
              </div>
              <input type="text" wire:model.lazy="roleName" class="form-control" placeholder="ej: Admin" maxlength="255">
            </div>
            @error('roleName') <span class="text-danger er">{{ $message }}</span> @enderror
          </div>
        </div>


      </div>
      <div class="modal-footer">

        <button type="button" wire:click.prevent="resetUI()" class="btn btn-dark close-btn text-info" data-dismiss="modal">CERRAR</button>

        @if($selected_id < 1)
        <button type="button" wire:click.prevent="CreateRole()" onclick="loaderSave()" class="btn btn-dark close-modal" >GUARDAR</button>
        @else
        <button type="button" wire:click.prevent="UpdateRole()" onclick="loader()" class="btn btn-dark close-modal" >ACTUALIZAR</button>
        @endif


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

</div>
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
              window.livewire.on('sale-error', (errorMessage) => {
                  swal.close(); // Cierra el diálogo actual
                  swal({
                      icon: 'error',
                      title: 'Error al Actualizar',
                      text: errorMessage || 'Ocurrió un error inesperado.',
                      confirmButtonText: 'Cerrar'
                  });
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

              window.livewire.on('producto-creado', () => {
                  swal.close();
              });
              window.livewire.on('sale-error', (errorMessage) => {
                  swal.close(); // Cierra el diálogo actual
                  swal({
                      icon: 'error',
                      title: 'Error al crear',
                      text: errorMessage || 'Ocurrió un error inesperado.',
                      confirmButtonText: 'Cerrar'
                  });
              });
          }
      });
  }
</script>