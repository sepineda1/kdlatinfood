<div>
    <div class="row sales layout-top-spacing">

        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Reporte De Ventas</b>
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
                        <!-- <div class="col-md-6 col-xl-4">
                            <div class="card mb-3 widget-content bg-night-fade text-white">
                                <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Total Sales</div>
                                        <div class="widget-subheading">Last year expenses</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="widget-numbers"><span>{{$totalSales}}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="col-md-6 col-xl-4">
                            <div class="card mb-3 widget-content bg-night-fade text-white">
                                <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Total Orders</div>
                                        <div class="widget-subheading">Last year expenses</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="widget-numbers"><span>{{$totalSalesCount}}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <div class="card mb-3 widget-content bg-night-fade text-white">
                                <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                    <div class="widget-content-left">
                                        <div class="widget-heading">Total Clients</div>
                                        <div class="widget-subheading">Last year expenses</div>
                                    </div>
                                    <div class="widget-content-right">
                                        <div class="widget-numbers"><span>{{$totalClientes}}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <div class="row sales layout-top-spacing">

        <div class="col-sm-12">
            <div class="widget">
                <div class="widget-heading">
                    <h4 class="card-title text-center"><b>Reporte De Ventas Consolidado</b></h4>
                </div>            
                <div class="widget-content">
                    <div class="row">
                        <div class="col-sm-12 col-md-3">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h6>Selecciona El User</h6>
                                    <div class="form-group">
                                        <select wire:model="userId" class="form-control">
                                            <option value="0">All</option>
                                            @foreach($users as $user)
                                            <option value="{{$user->id}}">{{$user->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <h6>Selecciona El Tipo de Reporte</h6>
                                    <div class="form-group">
                                        <select wire:model="reportType" class="form-control">
                                            <option value="0">Ventas Del Dia</option>
                                            <option value="1">Ventas Por Fecha</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <h6>Fecha Desde</h6>
                                    <div class="form-group">
                                        <input type="text" wire:model="dateFrom" class="form-control flatpickr"
                                            placeholder="Click para elegir">
                                    </div>
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <h6>Fecha Hasta</h6>
                                    <div class="form-group">
                                        <input type="text" wire:model="dateTo" class="form-control flatpickr"
                                            placeholder="Click para elegir">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <button wire:click="$refresh" class="btn btn-dark btn-block">
                                        Consultar
                                    </button>

                                    <a class="btn btn-dark btn-block {{count($data) <1 ? 'disabled' : '' }}"
                                        href="{{ url('report/pdf' . '/' . $userId . '/' . $reportType . '/' . $dateFrom . '/' . $dateTo) }}"
                                        target="_blank">Generar PDF</a>

                                    <a class="btn btn-dark btn-block {{count($data) <1 ? 'disabled' : '' }}"
                                        href="{{ url('report/excel' . '/' . $userId . '/' . $reportType . '/' . $dateFrom . '/' . $dateTo) }}"
                                        target="_blank">Exportar Excel</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-9">
                            <!--TABLAE-->
                            <div class="table-responsive">
                                <table class="table table-bordered table striped mt-1">
                                    <thead class="text-white" style="background: #FF5100">
                                        <tr>
                                            <th class="table-th text-white text-center">FOLIO</th>
                                            <th class="table-th text-white text-center">TOTAL</th>
                                            <th class="table-th text-white text-center">ITEMS</th>
                                            <th class="table-th text-white text-center">STATUS</th>
                                            <th class="table-th text-white text-center">USER</th>
                                            <th class="table-th text-white text-center">DATE</th>
                                            <th class="table-th text-white text-center">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($data) <1) <tr>
                                            <td colspan="7">
                                                <h5>Sin Resultados</h5>
                                            </td>
                                            </tr>
                                            @endif
                                            @foreach($data as $d)
                                            <tr>
                                                <td class="text-center">
                                                    <h6>{{$d->id}} </h6>
                                                </td>
                                                <td class="text-center">
                                                    <h6>${{number_format($d->total_with_services,2)}}</h6>
                                                </td>
                                                <td class="text-center">
                                                    <h6>{{$d->items}}</h6>
                                                </td>
                                                <td class="text-center">
                                                    <h6>{{$d->status}}</h6>
                                                </td>
                                                <td class="text-center">
                                                    <h6>{{$d->user}}</h6>
                                                </td>
                                                <td class="text-center">
                                                    <h6>
                                                        {{\Carbon\Carbon::parse($d->created_at)->format('d-m-Y')}}
                                                    </h6>
                                                </td>                                            
                                                <td class="text-center">
                                                    <button type="button" wire:click="getDetails('{{$d->id}}')"
                                                        class="btn btn-dark btn-sm">
                                                        <i class="fas fa-list"></i>
                                                    </button>

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
        </div>
        @include('livewire.reports.sales-detail')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
      //eventos
      window.livewire.on('show-modal', Msg => { 
        console.log('zxczxc');      
        $('#modalDetails').modal('show')
    })


    flatpickr(document.getElementsByClassName('flatpickr'), {
        enableTime: false,
        dateFormat: 'Y-m-d',
        locale: {
            firstDayofWeek: 1,
            weekdays: {
                shorthand: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
                longhand: [
                    "Sunday",
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday",
                ],
            },
            months: {
                shorthand: [
                    "Ene",
                    "Feb",
                    "Mar",
                    "Abr",
                    "May",
                    "Jun",
                    "Jul",
                    "Ago",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dic",
                ],
                longhand: [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December",
                ],
            },

        }

    })


  
})

function rePrint(saleId) {
    console.log('se prtinter modal')
    window.open("print://" + saleId, '_self').close()
}
</script>