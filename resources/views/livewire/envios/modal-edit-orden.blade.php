<!-- MODAL -->
<div class="modal fade" id="modalEditarOrden" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Editar Orden #<span id="orderNumberEdit"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <!-- Tabla de Detalles -->
                @csrf
                <div id="editSaleDetailsContent">
                    <table class="table table-bordered">
                        <thead style="background:#ff5100;" class="text-white">
                            <tr>
                                <th class="text-white">SKU</th>
                                <th class="text-white">Presentación</th>
                                <th class="text-white">Cantidad</th>
                                <th class="text-white">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="editSaleDetailsTableBody">
                            <!-- JS Rellenará -->
                        </tbody>
                    </table>
                </div>

                <!-- Formulario para agregar nuevo -->
                <div class="form-inline">
                    <select id="selectPresentacion" class="form-control mr-2">
                        <option value="">Elegir Presentación</option>
                        @foreach ($presentaciones as $pre)
                            <option value="{{ $pre->id }}">{{ $pre->barcode }} - {{ $pre->product->name }}
                                {{ $pre->size->size }}</option>
                        @endforeach
                    </select>
                    <input type="number" id="inputCantidad" class="form-control mr-2" placeholder="Cantidad">
                    <button class="btn btn-primary" onclick="addDetalleOrden()">Añadir</button>
                </div>

                <div class="text-right mt-3">
                    <button class="btn btn-success" onclick="actualizarOrden()">Actualizar Orden</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editarOrden(saleID) {
        swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => swal.showLoading()
        });

        fetch(`/intranet/public/sale/details/${saleID}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'),
                    'Content-Type': 'application/json'
                }
            })

            .then(res => res.json())
            .then(data => {
                document.getElementById('editSaleDetailsTableBody').innerHTML = '';
                document.getElementById('orderNumberEdit').innerText = saleID;
                document.getElementById('modalEditarOrden').setAttribute('data-sale-id', saleID);

                data.saleDetails.forEach((item, idx) => {
                    const row = `
                    <tr data-index="${idx}" data-presentacion-id="${item.presentacion_id}">
                        <td>${item.sku}</td>
                        <td>${item.presentacion}</td>
                        <td><input type="number" value="${item.qty}" class="form-control cantidad-input" /></td>
                        <td><button class="btn btn-danger" onclick="removeDetalleOrden(${idx})">Eliminar</button></td>
                    </tr>`;
                    document.getElementById('editSaleDetailsTableBody').insertAdjacentHTML('beforeend',
                        row);
                });

                $('#modalEditarOrden').modal('show');
                swal.close();
            })
            .catch(err => {
                swal.fire('Error', 'No se pudieron cargar los datos', 'error');
                console.error(err);
            });
    }

    function addDetalleOrden() {
        const select = document.getElementById('selectPresentacion');
        const input = document.getElementById('inputCantidad');
        const presentacionID = select.value;
        const cantidad = parseInt(input.value);

        if (!presentacionID || isNaN(cantidad) || cantidad <= 0) {
            swal.fire('Atención', 'Selecciona una presentación válida y una cantidad numérica', 'warning');
            return;
        }

        const row = `
        <tr data-index="new-${Date.now()}" data-presentacion-id="${presentacionID}">
            <td>Nuevo</td>
            <td>${select.options[select.selectedIndex].text}</td>
            <td><input type="number" value="${cantidad}" class="form-control cantidad-input" /></td>
            <td><button class="btn btn-danger" onclick="this.closest('tr').remove()">Eliminar</button></td>
        </tr>`;
        document.getElementById('editSaleDetailsTableBody').insertAdjacentHTML('beforeend', row);

        // Reset inputs
        select.value = '';
        input.value = '';
    }

    function actualizarOrden() {
        const saleID = document.getElementById('modalEditarOrden').getAttribute('data-sale-id');
        const rows = document.querySelectorAll('#editSaleDetailsTableBody tr');
        const detalles = [];

        rows.forEach((row) => {
            const presentacionText = row.children[1].innerText;
            const cantidadInput = row.querySelector('.cantidad-input');
            const cantidad = parseInt(cantidadInput.value);

            if (isNaN(cantidad) || cantidad <= 0) {
                swal.fire('Error', 'Verifica que todas las cantidades sean válidas', 'error');
                return;
            }

            const presentacionID = row.getAttribute('data-presentacion-id');

            detalles.push({
                presentacion_id: presentacionID, // ✅ esto es el valor que espera Laravel
                cantidad: cantidad
            });

        });

        swal.fire({
            title: 'Actualizando...',
            allowOutsideClick: false,
            didOpen: () => swal.showLoading()
        });

        fetch(`/intranet/public/update/order/${saleID}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    detalles
                })
            })
            .then(res => res.json())
            .then(data => {
                swal.fire('Éxito', 'Orden actualizada correctamente', 'success');
                $('#modalEditarOrden').modal('hide');
                setTimeout(() => location.reload(), 1000); // Opcional
            })
            .catch(err => {
                swal.fire('Error', 'Hubo un problema actualizando la orden', 'error');
                console.error(err);
            });
    }
</script>
