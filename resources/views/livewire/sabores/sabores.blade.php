<div>

    <script type="text/javascript">
        function deleteFlavor(idFlavor) {            
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
                    
                    window.livewire.emit('deleteFlavor', idFlavor);
                    swal.close();
                    loaderDeleteFlavor();
                }
            });
        }
        function loaderDeleteFlavor() {
        swal({
            title: 'Borrando sabor',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();                
                window.livewire.on('flavor-delete', (errorMessage) => {
                    swal.close(); // Cierra el diálogo actual
                });                
                // Cierra el cuadro y muestra un error si ocurre un problema
                window.livewire.on('error-delete-flavor', (errorMessage) => {
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
    <div class="row sales layout-top-spacing">
        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Materia Prima | Analisis</b>
                    </h4>

                </div>

                <style>
                .bg-night-fade {
                    background: linear-gradient(135deg, #FF5100 0%, #FF5100 100%);
                }

                .widget-content {
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                }

                .widget-heading {
                    font-size: 1.2em;
                    font-weight: bold;
                }

                .widget-subheading {
                    font-size: 1em;
                    color: #ddd;
                }

                .widget-numbers span {
                    font-size: 2em;
                    font-weight: bold;
                }

                .widget-content-wrapper {
                    padding: 15px;
                }

                .text-white {
                    color: #fff;
                }
                </style>


                <div class="row">
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total Materia Prima</div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{$totalStock}} lb</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                   <!-- <div class="col-md-6 col-xl-4 d-none">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">PYR mas alto </div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{$highestResult}} lb</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4 d-none">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Proyeccion Stock Final</div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                        style="white-space: nowrap; overflow: hidden;font-size: 9px; text-overflow: ellipsis;">
                                        <span>{{$totalPYR}} lb</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>-->

                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total Tipo Materia Prima</div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{ $totalSabores }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Sabor Mas Vendido</div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                        style="white-space: nowrap; overflow: hidden;font-size: 7px; text-overflow: ellipsis;">
                                        <span class="small">{{ $nombreSabor }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                   
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Utima Materia Prima Agregada</div>
                                    <div class="widget-subheading">Ultimo Reporte</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                        style="white-space: nowrap; overflow: hidden;font-size: 7px; text-overflow: ellipsis;">
                                        <span>{{$ultimoSaborCreado->nombre}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="row sales layout-top-spacing">
            <div class="col-sm-12">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h4 class="card-title">
                            <b>{{ $componentName}} | List</b>
                        </h4>
                        <ul class="tabs tab-pills">
                            <li>
                                <a href="javascript:void(0)" class="btn btn-warning mb-2 mr-2 btn-rounded"
                                    data-toggle="modal" data-target="#theModal"> Nueva Materia Prima<svg viewBox="0 0 24 24"
                                        width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg></a>
                            </li>
                        </ul>
                    </div>
                    @include('common.searchbox')
                    <div class="widget-content">
                        <div class="table-responsive">
                            <table class="table table-bordered table striped mt-1 searchable-table">
                                <thead class="text-white" style="background: #FF5100">
                                    <tr>
                                        <th class="table-th text-white text-center">Name</th>
                                        <th class="table-th text-white text-center">Stock Crudo(Lb)</th>
                                        <!--<th class="table-th text-white text-center ">Pry Stock</th>
                                        <th class="table-th text-white text-center d-none">Libra consumo</th>-->
                                        <th class="table-th text-white text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $coin)
                                    <tr>
                                        <td class="text-center">
                                            <h6>{{$coin->nombre}}</h6>
                                        </td>

                                        <td class="text-center">
                                            <h6>{{ isset($coin->stock) ? $coin->stock : 'Sin Stock' }} lb</h6>
                                        </td>
                                        <!--<td class="text-center">
                                            <h6>{{ isset($coin->stock) ? $coin->stock * $coin->libra_consumo : 'Sin Stock' }}
                                                lb (Aproximacion) </h6>
                                        </td>
                                        <td class="text-center">
                                            <h6>{{$coin->libra_consumo}}</h6>-->
                                        </td>                                       
                                        <td class="text-center">
                                            <a href="javascript:void(0)" onclick="deleteFlavor('{{$coin->id}}')"
                                                class="btn btn-danger mb-2 mr-2 btn-rounded" title="Delete">
                                                <svg viewBox="0 0 24 24" width="24" height="24"
                                                    stroke="currentColor" stroke-width="2" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="css-i6dzq1">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                    </path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                            </a>                                             
                                            <a href="javascript:void(0)" wire:click="Edit({{$coin->id}})"
                                                class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                    stroke-width="2" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round" class="css-i6dzq1">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z">
                                                    </path>
                                                </svg>
                                            </a>

                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

   

        @include('livewire.sabores.form')
        @include('common.searchjs')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.livewire.on('show-modal', msg => {
                console.log('sabores')
                $('#theModal').modal('show')
            });
            window.livewire.on('sabor-added', msg => {
                $('#theModal').modal('hide')
            });
            window.livewire.on('sabor-updated', msg => {
                $('#theModal').modal('hide')
            });
        });
        </script>
    </div>
</div>