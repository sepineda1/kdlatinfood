
<div class="row sales layout-top-spacing">
	
	<div class="col-sm-12">
		<div class="widget widget-chart-one">
			<div class="widget-heading">
				<h4 class="card-title">
					<b>Sizes | LIST</b>
				</h4>
				<ul class="tabs tab-pills">		
					@can('Category_Create')	
					<li>
						<a href="javascript:void(0)" class="btn btn-primary mb-2 mr-2 btn-rounded" data-toggle="modal" data-target="#theModal" 
						>Add</a>
					</li>	
					@endcan
				</ul>
			</div>
			@include('common.searchbox')
			
			
			<div class="widget-content">		
				

				<div class="table-responsive">
					<table class="table table-bordered table striped mt-1 searchable-table">
						<thead class="text-white" style="background: #FF5100; border-radius: 10px;">
							<tr>
								<th class="table-th text-white">id</th>
								<th class="table-th text-white text-center">size</th>
								<th class="table-th text-white text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
   
    @foreach($sizes as $sizes)
        <tr>
            <td><h6>{{$sizes->id}}</h6></td>
            <td class="text-center">
            <h6>{{$sizes->size}}</h6>
            </td>
            <td class="text-center">
                @can('Category_Update')
					<a href="javascript:void(0)" onclick="deleteSize('{{$sizes->id}}')"
						class="btn btn-danger mb-2 mr-2 btn-rounded" title="Delete">
						<svg viewBox="0 0 24 24" width="24" height="24"
							stroke="currentColor" stroke-width="2" fill="none"
							stroke-linecap="round" stroke-linejoin="round"
							class="css-i6dzq1">
							<polyline points="3 6 5 6 21 6"></polyline>
								<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
								<line x1="10" y1="11" x2="10" y2="17"></line>
								<line x1="14" y1="11" x2="14" y2="17"></line>
						</svg>
                    </a> 
                    <a href="javascript:void(0)" wire:click="Edit({{$sizes->id}})" 
						class="btn btn-warning mb-2 mr-2 btn-rounded" 
						title="Edit">
                    	<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" 
							stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" 
							class="css-i6dzq1">
						<path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    </a>					
                @endcan
                
            </td>
        </tr>
    @endforeach
</tbody>

			</table>
			
		</div>

	</div>


</div>


</div>

@include('livewire.sizes.form')
@include('common.searchjs')
</div>


<script>
	document.addEventListener('DOMContentLoaded', function(){

		window.livewire.on('show-modal', msg =>{
			$('#theModal').modal('show')
		});
        // Cierra el cuadro y muestra un error si ocurre un problema
        window.livewire.on('error-editar-size', (errorMessage) => {
            swal.close(); // Cierra el diálogo actual
            swal({
                icon: 'error',
                title: 'Error inesperado',
                text: errorMessage || 'Ocurrió un error inesperado.',
                confirmButtonText: 'Cerrar'
            });
        });		
		window.livewire.on('category-added', msg =>{
			$('#theModal').modal('hide')
		});
		window.livewire.on('category-updated', msg =>{
			$('#theModal').modal('hide')
		});


	});



	function Confirm(id)
	{	

		swal({
			title: 'CONFIRMAR',
			text: '¿CONFIRMAS ELIMINAR EL REGISTRO?',
			type: 'warning',
			showCancelButton: true,
			cancelButtonText: 'Cerrar',
			cancelButtonColor: '#fff',
			confirmButtonColor: '#3B3F5C',
			confirmButtonText: 'Aceptar'
		}).then(function(result) {
			if(result.value){
				window.livewire.emit('deleteRow', id)
				swal.close()
			}

		})
	}


	function deleteSize(idSize) {            
            swal({
                title: '¿CONFIRM DELETE THIS REG? ',
                text: 'THIS ACTION CAN BE REVERTED',
                type: 'warning',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                cancelButtonColor: '#fff',
                confirmButtonColor: '#3B3F5C',
                confirmButtonText: 'Aceptar'
            }).then(function(result) {
                if (result.value) {
                    
                    window.livewire.emit('deleteSize', idSize);
                    swal.close();
                    loaderDeleteSize();
                }
            });
        }
        function loaderDeleteSize() {
        swal({
            title: 'Borrando tamaño',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();                
                window.livewire.on('size-delete', (errorMessage) => {
                    swal.close(); // Cierra el diálogo actual
                });                
                // Cierra el cuadro y muestra un error si ocurre un problema
                window.livewire.on('error-delete-size', (errorMessage) => {
                    swal.close(); // Cierra el diálogo actual
                    swal({
                        icon: 'error',
                        title: 'Error al eliminar registro',
                        text:errorMessage || 'Ocurrió un error inesperado.',
                        ext: errorMessage || 'Ocurrió un error inesperado.',
                        confirmButtonText: 'Cerrar'
                    });
                });

            }
        });
    }

</script>