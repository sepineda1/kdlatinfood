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
                // Cierra el cuadro y muestra un error si ocurre un problema
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


    
    <button type="button" wire:click.prevent="closeModal()" class="btn btn-dark close-btn text-info d-none" data-dismiss="modal">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
            stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        Close
    </button>



    @if($selected_id < 1) <button type="button" wire:click.prevent="Store()" onclick="loaderSave()"
            class="btn btn-warning mb-2 mr-2 btn-rounded close-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-folder">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
            Save
        </button>

    @else
        <button type="button" wire:click.prevent="Update()" onclick="loader()"
            class="btn btn-outline-primary btn-rounded mb-2 close-modal">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Update
        </button>

    @endif


</div>
</div>
</div>
</div>