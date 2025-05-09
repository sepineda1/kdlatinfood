<div wire:ignore.self class="modal fade" id="modalDetails" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark">
        <h5 class="modal-title text-white">
        	<b>Detalle de Venta # {{$saleId}}</b>
        </h5>
        <h6 class="text-center text-warning" wire:loading>POR FAVOR ESPERE</h6>
      </div>
      <div class="modal-body">

        <div class="table-responsive">
          <table class="table table-bordered table striped mt-1">
            <thead class="text-white" style="background: #FF5100">
              <tr>
                <th class="table-th text-white text-center">FOLIO</th>
                <th class="table-th text-white text-center">PRODUCTO</th>
                <th class="table-th text-white text-center">PRECIO</th>
                <th class="table-th text-white text-center">DISCOUNT</th>
                <th class="table-th text-white text-center">DISCOUNTED PRICE</th>
                <th class="table-th text-white text-center">CANT</th>
                <th class="table-th text-white text-center">IMPORTE</th>
              </tr>
            </thead>
            <tbody>
              @php
                  $total = 0;
              @endphp
              @foreach($details as $d)
              @php
                  $discount = $d->discount > 0 ? ($d->discount * $d->price)/100 : 0 ;
                  $price = $d->price - $discount;
                  $totalRow = $price * $d->quantity;
                  $total += $totalRow;
              @endphp
              <tr>
                <td class='text-center'><h6>{{$d->id}}</h6></td>
                <td class='text-center'><h6>{{$d->product}}</h6></td>
                <td class='text-center'><h6>{{number_format($d->price,2)}}</h6></td>
                <td class='text-center'><h6>{{$d->discount > 0 ?  $d->discount : 0 }}%</h6></td>
                <td class='text-center'><h6>{{number_format($price,2)}}</h6></td>
                <td class='text-center'><h6>{{number_format($d->quantity,0)}}</h6></td>
                <td class='text-center'><h6>{{number_format($totalRow,2)}}</h6></td>               
                
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              @php
                  $total_service = 0;
              @endphp
              @if ($saleService != null)
                <tr>
                  <td class='text-center'><h6></h6></td>
                  <td class='text-center'><h6>{{ $saleService->servicePay->catalogoService->name }}</h6></td>
                  <td class='text-center'><h6></h6></td>
                  <td class='text-center'><h6></h6></td>
                  <td class='text-center'><h6>${{number_format($saleService->amount,2)}}</h6></td>
                  <td class='text-center'><h6></h6></td>
                  <td class='text-center'><h6></h6></td>

                </tr>
                @php
                    $total_service += $saleService->amount;
                @endphp
              @endif
              <tr>
                <td colspan="3"><h5 class="text-center font-weight-bold">TOTALES</h5></td>
                <td><h5 class="text-center">{{$countDetails}}</h5></td>
                <td><h5 class="text-center">
                  ${{number_format($total + $total_service,2)}}
                </h5></td>
              </tr>
            </tfoot>
          </table>         
        </div>


      </div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-dark close-btn text-info" wire:click="claseModal()" data-dismiss="modal">CERRAR</button>
      </div>
    </div>
  </div>
</div>