<div>
    <div wire:ignore.self class="modal fade" id="verifModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($action === 'approve')
                            Tratamiento <span class="badge badge-success">Acceptable</span>
                        @else
                            Tratamiento <span class="badge badge-danger">Deficiency </span>
                        @endif
                    </h5>
                </div>
                <div class="modal-body">
                    @if ($logInfo != null)
                        <div class="mb-3">
                            <label class="text-dark">Producto</label>
                            <span class="badge badge-primary">{{ $logInfo->insumo->sabor->nombre }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-dark">Lote</label>
                            <span class="badge badge-primary">{{ $logInfo->insumo->CodigoBarras }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-dark">CCP</label>
                            <span class="badge badge-warning">{{ $logInfo->ccp_code }}</span>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label>Hora Verificación</label>
                        <input type="time" wire:model.defer="hora_verificacion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Hora Revisión Registros</label>
                        <input type="time" wire:model.defer="hora_revision" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click.prevent="save" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            // 1) Crea la instancia del modal de Bootstrap
            const modalEl = document.getElementById('verifModal');
            const bsVerifModal = new bootstrap.Modal(modalEl);

            // 2) Cuando Livewire dispare este evento, abre el modal
            window.addEventListener('show-verif-modal', () => {
                bsVerifModal.show();
            });

            // 3) Cuando Livewire dispare este, ciérralo
            window.addEventListener('hide-verif-modal', () => {
                bsVerifModal.hide();
            });

            // 4) SweetAlert
            window.addEventListener('swal', event => {
                Swal.fire(event.detail);
            });
        });
    </script>

</div>
