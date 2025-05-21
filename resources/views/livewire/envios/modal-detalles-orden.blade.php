<!-- Modal de Detalles -->
<div class="modal fade" id="modalSaleDetails" tabindex="-1" role="dialog"
aria-labelledby="saleDetailsLabel" aria-hidden="true">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title" id="saleDetailsLabel">Detalles de la Orden #<span
                    id="orderNumber"></span></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">
            <div id="loadingSpinner" style="display: none;" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p>Cargando detalles...</p>
            </div>
            <input type="hidden" id="orderSelect">
            <div id="saleDetailsContent" style="display: none;">
                <table class="table table-bordered">
                    <thead style="background: #ff5100">
                        <tr>
                            <th class="text-white">SKU</th>
                            <th class="text-white">Presentación</th>
                            <th class="text-white">Cantidad</th>
                            <th class="text-white">Scan</th>
                            <th class="text-white">Codigo de Barras</th>
                        </tr>
                    </thead>
                    <tbody id="saleDetailsTableBody">
                        <!-- Detalles se insertan aquí con JS -->
                    </tbody>
                </table>
                <div id="displayButton">

                </div>
            </div>
        </div>

    </div>
</div>
</div>