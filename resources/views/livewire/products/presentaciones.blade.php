<style>
    .modal-dialog.modal-fullscreen {
        width: 95%;
    height: 95%;
    margin: 0;
    max-width: none;
    position: absolute;
    left: 2.5%;
    top: 2.5%;
    transform: translate(-50%, -50%);
}
</style>

<div wire:ignore.self class="modal fade" id="PresentacionesModal" tabindex="-1" role="dialog"
    style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>Presentaciones </b>
                </h5>
                <ul class="tabs tab-pills">
                        @role('Admin')
                        <li>
                            <a href="javascript:void(0)" class="btn btn-light" wire:click="openPresentacionCrear({{$selected_id}})" data-toggle="modal"
                                data-target>Add</a>
                        </li>
                        @endcan
                    </ul>
            </div>
            <div class="modal-body">
              
                @if(isset($presentations) && count($presentations) > 0)

                    <table class="table table-bordered table-striped mt-1">
                        <thead class="text-white" style="background: #FF5100;">
                            <tr>
                                <th class="table-th text-white text-center text-nowrap">SKU</th>
                                <th class="table-th text-white text-center text-nowrap">Producto ID </th>
                  
                                <th class="table-th text-white text-center text-nowrap">Tamaño</th>
                                <th class="table-th text-white text-center text-nowrap">Estado </th>
                                <th class="table-th text-white text-center text-nowrap">Costo</th>
                                <th class="table-th text-white text-center text-nowrap">Box Items </th>
                                <th class="table-th text-white text-center text-nowrap">Precio x Box</th>
                                <th class="table-th text-white text-center text-nowrap">Stock Box</th>
                                <th class="table-th text-white text-center text-nowrap">Min. Stock</th>
                                <th class="table-th text-white text-center text-nowrap">Actions</th>
                                <th class="table-th text-white text-center text-nowrap">QR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($presentations[0]->id))
                            @foreach($presentations as $presentation)
                            <tr>
                              
                                <td>
                                    <h6 class="text-center">
                                        <a  href="{{ route('product.detail', $presentation->id) }}">
                                            {{$presentation->barcode}}
                                        </a>
                                    </h6>
                                </td>
                                <td class="text-center"><h6>{{ $presentation->name}}</h6></td>
     
                                <td class="text-center"><h6>{{ $presentation->size }}</h6></td>
                                <td class="text-center"><h6>{{ $presentation->estado }}</h6></td>
                        
                                <td class="text-center"><h6>{{ number_format(round($presentation->costo,2), 2, '.', ''); }}</h6></td>
                                <td class="text-center"><h6>{{ $presentation->stock_items }}</td>
                                <td class="text-center"><h6>{{ number_format(round($presentation->price,2), 2, '.', ''); }}</h6></td>

                                <td class="text-center {{$presentation->stock_box <= $presentation->alerts ? 'text-danger' : '' }}"><h6>{{ $presentation->stock_box }}</h6></td>
                                <td class="text-center"><h6>{{ $presentation->alerts }}</h6></td>                                 
                                <!--<td class="text-center">
                                    @can('Category_Update')
                                        <a href="javascript:void(0)" onclick="loader()" wire:click="EditProduct({{$presentation->id}})"
                                            class="btn btn-warning mb-2 mr-2 btn-rounded d-none" title="Edit">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </a>
                                    @endcan
                                    <button type="button" title="Add Cart"
                                        wire:click.prevent="ScanCodePresentacion('{{$presentation->barcode}}')"
                                        onclick="loaderEx()" class="btn btn-warning mb-2 mr-2 btn-rounded" title="Delete">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                            class="css-i6dzq1">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                            </path>
                                        </svg>
                                    </button>
                                    @if ($presentation->TieneKey == 'NO')
                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded" title="Generate Key product"
                                            wire:click.prevent="GenerateKeyPresentacion('{{ $presentation->id }}')"
                                            onclick="loaderEx()">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path
                                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                </path>
                                            </svg>
                                        </a>
                                        
                                    @endif
                                    @if ($presentation->visible === 'no')
                                                    <a href="javascript:void(0)"
                                                        wire:click.prevent="novisible({{$presentation->id}})"
                                                        onclick="loaderEx()"
                                                        class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                        title="Publicar">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <path
                                                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                                            </path>
                                                            <line x1="1" y1="1" x2="23" y2="23"></line>
                                                        </svg>
                                                    </a>
                                                    @else
                                                    <a href="javascript:void(0)"
                                                        wire:click.prevent="visible({{$presentation->id}})"
                                                        onclick="loaderEx()"
                                                        class="btn btn-warning mb-2 mr-2 btn-rounded"
                                                        title="Ocultar">
                                                        <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                            </path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </a>
                                                    @endif
                                </td>-->
                  
                                    <td class="text-center" >
                                        @role('Admin')
                                        <a href="javascript:void(0)"
                                            wire:click.prevent="EditPresentacion({{$presentation->id}})"
                                            onclick="loaderEx()"
                                            class="btn btn-warning mb-2 mr-2 btn-rounded" title="Edit">
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                            stroke="currentColor" stroke-width="2" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            class="css-i6dzq1">
                                                            <path d="M12 20h9"></path>
                                                            <path
                                                                d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z">
                                                            </path>
                                                        </svg>
                                        </a>

                                        {{--@if ($presentation->QB_id == '')
                                        <a href="javascript:void(0)"
                                            onclick="loaderQuickbooksPresentacion('{{$presentation->id}}')"
                                            class="btn btn-success mb-2 mr-2 btn-rounded" title="Crear
                                            Producto en QuickBooks" @if(!empty($presentation->QB_id))
                                            style="display: none;" @endif>
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z">
                                                </path>
                                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z">
                                                </path>
                                            </svg>
                                        </a>
                                        @endif--}}
                                        @if ($presentation->visible === 'no')
                                        <a href="javascript:void(0)"
                                            wire:click.prevent="novisiblep({{$presentation->id}})"
                                            onclick="loaderEx()"
                                            class="btn btn-warning mb-2 mr-2 btn-rounded"
                                            title="Publicar">
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path
                                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c-7 0-11 8-11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                                </path>
                                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                            </svg>
                                        </a>
                                        @else
                                        <a href="javascript:void(0)"
                                            wire:click.prevent="visiblep({{$presentation->id}})"
                                            onclick="loaderEx()"
                                            class="btn btn-warning mb-2 mr-2 btn-rounded"
                                            title="Ocultar">
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                </path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        @endif
                                        @if ($presentation->lot == 0)
                                        <a href="javascript:void(0)" title="Delete Product"
                                            onclick="ConfirmPre('{{$presentation->id}}')"
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
                                        @endif
                                        <button type="button" title="Add Cart"
                                        wire:click.prevent="ScanCodePresentacion('{{$presentation->barcode}}')"
                                        onclick="loaderEx()" class="btn btn-warning mb-2 mr-2 btn-rounded" title="Delete">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                            class="css-i6dzq1">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                            </path>
                                        </svg>
                                    </button> 

                                    <button wire:click="abrirModalConsumo({{ $presentation->id }})"  onclick="loaderEx()" class="btn btn-primary mb-2 mr-2 btn-rounded">
                                        <i class="fab fa-cuttlefish"></i>
                                    </button>

                                        @endcan
                                        @role('Admin|Employee')
                                        @if ($presentation->TieneKey == 'SI' )                                            
                                        <button type="button" title="Add Cart"
                                            wire:click.prevent="ScanCode('{{$presentation->barcode}}')"
                                            onclick="loaderEx()"
                                            class="btn btn-warning mb-2 mr-2 btn-rounded d-none"
                                            title="Delete">
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <circle cx="9" cy="21" r="1"></circle>
                                                <circle cx="20" cy="21" r="1"></circle>
                                                <path
                                                    d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                                </path>
                                            </svg>
                                        </button>
                                        @endcan
                                        @if(strtoupper($presentation->EstaEnWoocomerce) != 'SI')
                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded d-none"
                                            title="Create in Woocomerce"
                                            onclick="CrearWCProduct('{{$presentation->id}}')">
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <polyline points="16 16 12 12 8 16"></polyline>
                                                <line x1="12" y1="12" x2="12" y2="21"></line>
                                                <path
                                                    d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3">
                                                </path>
                                                <polyline points="16 16 12 12 8 16"></polyline>
                                            </svg>
                                        </a>
                                        @endif
                                        @if ($presentation->TieneKey == 'NO')
                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                            title="Generate Key product"
                                            wire:click.prevent="GenerateKeyPresentacion('{{ $presentation->id }}')"
                                            onclick="loaderEx()"
                                            >
                                            <svg viewBox="0 0 24 24" width="24" height="24"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <path
                                                    d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4">
                                                </path>
                                            </svg>
                                        </a>
                                        @endif
                                        @endcan
                                    </td>
                               
                                <td>
                                    
                                 
                                        @if ($presentation->TieneKey == 'SI' && $presentation->lot > 0)
                                        <a class="btn btn-warning mb-2 mr-2 btn-rounded"
                                            href="{{ url('detail/pdf/prod' . '/' . $presentation->id) }}" title="print"
                                            target="_blank" style="background:#f39022;">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                class="css-i6dzq1">
                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                <path
                                                    d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                                </path>
                                                <rect x="6" y="14" width="12" height="8"></rect>
                                            </svg></a>
                                        @endif
                                  
                                
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            
                           
                        </tbody>
                    </table>
                @else
                    <p>No hay presentaciones disponibles para este producto.</p>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-dark close-btn text-info" data-dismiss="modal">Cerrar</button>
                
            </div>
        </div>
    </div>
</div>

<script>
    function ConfirmPre(id) {
        swal({
            title: 'CONFIRM',
            text: 'CONFIRM DELETE THIS REG?',
            type: 'warning',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#fff',
            confirmButtonColor: '#3B3F5C',
            confirmButtonText: 'Aceptar'
        }).then(function(result) {
            if (result.value) {
                window.livewire.emit('deletePre', id)
                swal.close();
              
            }

        })
    }               
</script>

<div wire:ignore.self class="modal fade" id="modalConsumo" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">
                    <b>Consumo de Presentación</b>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                
                @if($presentacion_id_consumo)
                    @livewire('consumo-controller', ['presentacion_id' => $presentacion_id_consumo], key('consumo-'.$presentacion_id_consumo))
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.livewire.on('abrir-modal-consumo', () => {
            $('#modalConsumo').modal('show');
            setTimeout(()=>{
                Swal.close();
            },2000);
        });
    });
</script>

