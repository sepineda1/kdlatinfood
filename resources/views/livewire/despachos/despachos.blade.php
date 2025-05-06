{{--
<div class="row sales layout-top-spacing">
    <div class="col-sm-12">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h4 class="card-title"><b>Deliveries</b> | List (Only customers with pending shipments are being displayed)</h4>
            </div>
          
            <div class="widget-content">
                <div id="accordion">
                    @foreach ($data as $cliente)
                        @php
                            $pendingSales = $cliente->sale()->where('status', 'PENDING')->get();
                            if ($pendingSales->count() === 0) {
                                continue;
                            }
                        @endphp
                        <div class="card">
                            <div class="card-header" id="heading{{$cliente->id}}">
                                <h3 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapse{{$cliente->id}}" aria-expanded="true" aria-controls="collapse{{$cliente->id}}">
                                        <h3>{{$cliente->id}}. {{$cliente->name}} {{$cliente->last_name}}</h3>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse{{$cliente->id}}" class="collapse" aria-labelledby="heading{{$cliente->id}}" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mt-1">
                                            <thead class="text-white" style="background: #FF5100">
                                                <tr>
                                                    <th class="table-th text-white">ID</th>
                                                  
                                                    <th class="table-th text-center text-white">Sku</th>
                                                    <th class="table-th text-center text-white">Client</th>
                                                    <th class="table-th text-center text-white">Address</th>
                                                    <th class="table-th text-center text-white">Item</th>
                                                    <th class="table-th text-center text-white">Status</th>
                                                    <th class="table-th text-center text-white">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pendingSales as $venta)
                                                    <tr>
                                                        <td class="text-center">
                                                            <h6>{{$venta->id}}</h6>
                                                        </td>

                                                        <td class="text-center">
                                                            @php
                                                                $selectShown = false;
                                                            @endphp
                                                            @foreach ($venta->salesDetails as $d)
                                                                @if (!$selectShown)
                                                                    <div>
                                                                        <select class="form-control">
                                                                            @foreach ($venta->salesDetails as $d)
                                                                                @foreach ($prod as $p)
                                                                                    @if ($d->presentaciones_id == $p->id)
                                                                                        <option value="#">{{ $p->barcode }} - Items:{{ $d->quantity }} -
                                                                                        {{ $p->product->name }}  {{ $p->size->size }}   {{ $p->product->estado }}  
                                                                                    </option>
                                                                                    @endif
                                                                                @endforeach
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @php
                                                                        $selectShown = true;
                                                                    @endphp
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                        
                                                        <td class="text-center">
                                                            <h6>{{$cliente->name}} {{$cliente->last_name}}</h6>
                                                        </td>
                                                        <td class="text-center">
                                                            <h6>{{$cliente->address}}</h6>
                                                        </td>
                                                        <td class="text-center">
                                                            <h6>{{$venta->items}}</h6>
                                                        </td>
                                                        <td class="text-center">
                                                            <h6>{{$venta->status}}</h6>
                                                        </td>
                                                        <td class="text-center">
                                                            <button wire:click.prevent="getDetails({{$venta->id}})" onclick="loader()" class="btn btn-warning mb-2 mr-2 btn-rounded">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="-0.5 -0.5 14 14" height="14" width="14"><g id="archive-box--box-content-banker-archive-file"><path id="Vector" stroke="#000" stroke-linecap="round" stroke-linejoin="round" d="M1.3928571428571428 4.642857142857143h10.214285714285715v6.5c0 0.24625714285714287 -0.09787142857142857 0.48248571428571424 -0.27197857142857146 0.6565928571428571S10.924828571428572 12.071428571428571 10.678571428571429 12.071428571428571h-8.357142857142858c-0.2462757142857143 0 -0.48245785714285716 -0.09787142857142857 -0.6566021428571429 -0.27197857142857146C1.4906914285714286 11.625342857142858 1.3928571428571428 11.389114285714287 1.3928571428571428 11.142857142857142V4.642857142857143v0Z" stroke-width="1"></path><path id="Vector_2" stroke="#000" stroke-linecap="round" stroke-linejoin="round" d="M12.535714285714286 3.7142857142857144V1.8571428571428572c0 -0.5128314285714286 -0.4157214285714286 -0.9285714285714286 -0.9285714285714286 -0.9285714285714286l-10.214285714285715 0c-0.5128360714285715 0 -0.9285714285714286 0.41574 -0.9285714285714286 0.9285714285714286v1.8571428571428572c0 0.5128314285714286 0.41573535714285714 0.9285714285714286 0.9285714285714286 0.9285714285714286l10.214285714285715 0c0.51285 0 0.9285714285714286 -0.41574 0.9285714285714286 -0.9285714285714286Z" stroke-width="1"></path><path id="Vector_3" stroke="#000" stroke-linecap="round" stroke-linejoin="round" d="M5.107142857142858 7.428571428571429h2.7857142857142856" stroke-width="1"></path></g></svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div style="clear: both;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @include('livewire.despachos.sales-detail')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.livewire.on('despacho-added', msg => {
            $('#theModal').modal('hide');
        });

        window.livewire.on('despacho-edit', msg => {
            $('#theModal').modal('hide');
        });

        window.livewire.on('despacho-delete', msg => {
            $('#theModal').modal('hide');
        });

        window.livewire.on('show-modal', msg => {
            $('#modalDetails').modal('show');
        });

        window.livewire.on('sale-error', msg => {
            Swal.fire({
                icon: 'error',
                title: 'Error de Venta',
                text: msg,
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#d33'
            });
        });
                window.livewire.on('hide-details-modal', msg => {
            $('#modalDetails').modal('hide');
        });

        window.livewire.on('modal-show', msg => {
            $('#theModal').modal('show');
        });

        window.livewire.on('modal-hide', msg => {
            $('#theModal').modal('hide');
        });

        window.livewire.on('hidden.bs.modal', msg => {
            $('.er').css('display', 'none');
        });

        $('#theModal').on('shown.bs.modal', function(e) {
            $('.Nombre_Lote').focus();
        });


          window.livewire.on('showedit', msg => {
            $('#Edit').modal('show');
        });
        window.livewire.on('hidedetailsedit', msg => {
            $('#Edit').modal('hide');
        });

    });

    function loader() {
        swal({
            title: 'Cargando datos',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();

                window.livewire.on('producto-creado', () => {
                    swal.close();
                });

            }
        });
    }

</script> --}}


{{-- resources/views/livewire/despachos/despachos.blade.php --}}

{{-- Audio de notificación --}}

<div>
    <audio id="alertSound" src="{{ asset('assets/audio/apple.mp3') }}" preload="auto"></audio>

    <div class="row sales layout-top-spacing">
        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Deliveries</b> | List (Only customers with pending shipments are being displayed)
                    </h4>
                    <button id="enableAudioBtn" class="btn btn-sm btn-primary mb-3">
                        🔔 Haga clic para activar notificaciones de sonido
                    </button>

                </div>
                <div class="widget-content">


                    <style>
                        .cardUiGradient {
                            background: #FF4C00;
                            /*background: linear-gradient(90deg, rgba(255, 76, 0, 1) 17%, rgba(255, 136, 0, 1) 100%, rgba(255, 161, 0, 1) 50%);*/
                            border: none !important;
                        }

                        @keyframes flash-orange {

                            0%,
                            100% {
                                background-color: inherit;
                            }

                            50% {
                                background-color: rgba(255, 200, 0, 0.5);
                            }
                        }

                        .flash {
                            animation: flash-orange 0.5s ease-in-out infinite;
                            border-radius: 50px;
                            
                        }
                    </style>

                    <div class="row" wire:poll.5s="refreshDeliveries">

                        @foreach ($deliveries as $venta)
                            <div class="col-md-12" id="order-card-{{ $venta->id }}">

                                <div class="card cardUiGradient btn-rounded mt-2 text-center">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img width="150px"
                                                    src="https://kdlatinfood.com/intranet/public/assets/img/box.png"
                                                    alt="">
                                            </div>
                                            <div class="col-md-9">

                                                <p class="text-white" style="font-size: 4em">Cliente: <b>
                                                        {{ $venta->customer->name }}
                                                        {{ $venta->customer->last_name }} </b></p>
                                                <h6 class="text-white" style="font-size: 3.5em"><b>Order ID:
                                                        #{{ $venta->id }}</b> - Total:
                                                    ${{ $venta->total }} - Delivery: {{ $venta->deliveriesTypes[0]->catalogEntry->name }} </h6>
                                                <p><button wire:click.prevent="getDetails({{ $venta->id }})"
                                                        onclick="loader()"
                                                        class="btn btn-white btn-rounded btn-block d-none">Sale
                                                        Details</button>
                                                </p>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Modales fuera del polling para no perder contenido  
                @include('livewire.despachos.sales-detail')
                @livewire('component.edit-sale-component') --}}
            </div>
        </div>
    </div>

    {{-- Modal de detalles protegido de re-render --}}
    <div wire:ignore.self class="modal fade" id="modalDetails" tabindex="-1" role="dialog"
        style="backdrop-filter: blur(10px);">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {{-- Contenido dinámico de detalles aquí --}}
            </div>
        </div>
    </div>

   <script>
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('alertSound');
    const enableBtn = document.getElementById('enableAudioBtn');

    // 1) Desbloquea audio y SpeechSynthesis con el primer click en cualquier parte
    function unlockAudio() {
        // Despierta el canal de audio
        audio.play()
             .then(() => { audio.pause(); audio.currentTime = 0; })
             .catch(() => {});
        // Despierta SpeechSynthesis
        const dummy = new SpeechSynthesisUtterance('');
        speechSynthesis.speak(dummy);
        // Quitamos el botón y el listener
        enableBtn.remove();
        document.body.removeEventListener('click', unlockAudio);
    }
    enableBtn.addEventListener('click', unlockAudio);
    document.body.addEventListener('click', unlockAudio, { once: true });

    // Carga voces, espera a que estén disponibles
    function loadVoices() {
        return new Promise(resolve => {
            let v = speechSynthesis.getVoices();
            if (v.length) return resolve(v);
            speechSynthesis.onvoiceschanged = () => resolve(speechSynthesis.getVoices());
        });
    }

    // 2) Alerta TTS con beep + mensajes diferidos
    async function playNuevoPedido(orderId, customerName) {
        // Primero el beep
        try { await audio.play(); } catch(_) {}
        // Después el TTS
        const voices = await loadVoices();
        const voice = voices.find(v=>/es/.test(v.lang)&&/female/i.test(v.name))
                   || voices.find(v=>/es/.test(v.lang))
                   || voices[0];

        const msg1 = new SpeechSynthesisUtterance('¡Nuevo pedido!');
        Object.assign(msg1, { voice, lang: voice.lang, rate: 0.7, pitch: 1.2, volume: 1 });
        msg1.onend = () => {
            setTimeout(() => {
                const msg2 = new SpeechSynthesisUtterance(
                    `Número de orden ${orderId} de ${customerName}`
                );
                Object.assign(msg2, { voice, lang: voice.lang, rate: 0.7, pitch: 1.2, volume: 1 });
                speechSynthesis.speak(msg2);
            }, 3000);
        };
        speechSynthesis.speak(msg1);
    }

    // 3) Conecta con Livewire
    if (window.livewire) {
        livewire.on('nuevaEntrega', (orderId, clienteNombre) => {
            playNuevoPedido(orderId, clienteNombre);

            // Efecto flash durante 1 minuto
            const card = document.getElementById(`order-card-${orderId}`);
            if (card) {
                card.classList.add('flash');
                setTimeout(() => card.classList.remove('flash'), 60000);
            }
        });

        // Si el polling de Livewire falla, recarga la página
        livewire.on('refresh-error', () => {
            console.warn('Error en polling, recargando la página…');
            location.reload();
        });
    }

    // ------ Listeners existentes para modales y errores ------
    window.livewire.on('despacho-added',     msg => { $('#theModal').modal('hide'); });
    window.livewire.on('despacho-edit',      msg => { $('#theModal').modal('hide'); });
    window.livewire.on('despacho-delete',    msg => { $('#theModal').modal('hide'); });
    window.livewire.on('show-modal',         msg => { $('#modalDetails').modal('show'); });
    window.livewire.on('hide-details-modal', msg => { $('#modalDetails').modal('hide'); });
    window.livewire.on('sale-error', msg => {
        Swal.fire({
            icon: 'error',
            title: 'Error de Venta',
            text: msg,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#d33'
        });
    });

    // loader()
    function loader() {
        swal({
            title: 'Cargando datos',
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.on('producto-creado', () => swal.close());
            }
        });
    }
});
</script>

</div>
