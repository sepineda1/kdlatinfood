<div class="">

    <div class="connect-sorting-content">
        <div class="card simple-title-task ui-sortable-handle">
            <div class="card-body">
          
                @if($total > 0)
                <div class="table-responsive tblscroll" style="max-height: 650px; overflow: hidden">
                    <div class="text-center"><h4><b> <i class="fas fa-egg"></i> Productos</b></h4></div>
                    <table class="table table-bordered table-striped mt-1">
                        <thead class="text-white" style="background: #FF5100">
                            
                            <tr>
                                <th style="font-size: 12px" ></th>
                                <th style="font-size: 12px" class="table-th text-left text-white">DESCRIPTION</th>
                                <th style="font-size: 12px" class="table-th text-center text-white">PRICE</th>
                                <th style="font-size: 12px" width="13%" class="table-th text-center text-white">QUANTITY</th>
                                <th style="font-size: 12px" class="table-th text-center text-white">AMOUNT</th>
                                <th style="font-size: 12px" width="20%" class="table-th text-center text-white">DISCOUNTED PRICE</th>
                                <th style="font-size: 12px" class="table-th text-center text-white">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
               
                            @foreach($cart as $item)
                            <tr>
                                <td class="text-center table-th">
                                    @if(count($item->attributes) > 0)
                                    <span>
                                        <img src="{{ asset('storage/products/' . $item->attributes[0]) }}"
                                            alt="imágen de producto" height="60" width="90" class="rounded">
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <p><b>{{$item->name}} {{$item->presentacion->size->size}}  {{$item->presentacion->product->estado}} </b></p>
                                </td>
                                @if ($item->presentacion->visible == "no")
                                    <td colspan="4"><span class="text-danger"><b>No Disponible</b></span></td>
                                @else    
                                    <td class="text-center"><h6>${{number_format($item->price,2)}}</h6></td>
                                    <td>
                                        <input type="number" id="r{{$item->id}}"
                                            wire:change.lazy="updateQty({{$item->id}}, $('#r' + {{$item->id}}).val() )"
                                            style="font-size: 1rem!important" class="form-control text-center"
                                            value="{{$item->quantity}}">
                                    </td>
                                    <td class="text-center">
                                        <h6 class="d-flex" >
                                            ${{number_format($item->price * $item->quantity,2)}} <span style="color:red;font-weight:bold;font-size:12px;background:#ffd7d7;border-radius:50%;">{{ isset($item->discount) ? "-". $item->discount->discount . "%" : "" }}</span>
                                        </h6>
                                    </td>
                                    <td class="text-center" >
                                        <h6>
                                            ${{ isset($item->discount) 
                                                ? number_format(($item->price * $item->quantity) - number_format((($item->discount->discount / 100) * ($item->price * $item->quantity)),2), 2)
                                                : number_format($item->price * $item->quantity, 2)
                                            }}
                                        </h6>
                                    </td> 
                                @endif
                               
                                <td class="text-center">
                                    <button wire:click.prevent="DeleteItem"
                                        onclick="Confirm('{{$item->id}}', 'removeItem', '¿CONFIRMAS ELIMINAR EL REGISTRO?')"
                                        class="btn btn-outline-dark btn-rounded btn-sm mbmobile">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @if ($item->presentacion->visible != "no")
                                        <button wire:click.prevent="decreaseQty({{$item->id}})" onclick="loaderText('Cargando...')"
                                            class="btn btn-outline-dark btn-rounded btn-sm mbmobile">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button wire:click.prevent="increaseQty({{$item->id}})" onclick="loaderText('Cargando...')"
                                            class="btn btn-outline-dark btn-rounded btn-sm mbmobile">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <h5 class="text-center text-muted">Agrega Productos Al Carrito</h5>
                @endif

                <!--
		<div wire:loading.inline wire:target="saveSale">
			<h4 class="text-danger text-center">Guardando Venta...</h4>
		</div>
	-->



            </div>
        </div>
    </div>


</div>