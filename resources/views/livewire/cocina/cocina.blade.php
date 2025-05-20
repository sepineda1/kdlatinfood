<div>
    @php
        $map = [
            '1B' => ['cook_min', 'cook_120'],
            '1B-1' => ['cook_min', 'cook_120'],
            '2B' => ['cook_min', 'cook_120', 'chill_120_80', 'chill_80_55', 'chill_le40'],
            '2B-1' => ['cook_min', 'cook_120', 'chill_120_80', 'chill_80_55', 'chill_le40'],
        ];
        $labels = [
            'cook_min' => 'Temperatura mínima',
            'cook_120' => 'Enfriar a 120°F',
            'chill_120_80' => '120→80°F',
            'chill_80_55' => '80→55°F',
            'chill_le40' => '≤40°F',
        ];
    @endphp
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Cabecera de la tabla */
        thead {
            background-color: #ffe9df;
            color: white;
        }

        thead th {
            color: #000 !important;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        /* Filas alternas */
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Hover sobre filas */
        tbody tr:hover {
            background-color: #f1f1f1;
        }
    </style>

    <livewire:verificacion-log />
    <!-- FILTRO DE HISTORIAL POR INSUMO -->
    <div class="card mt-1">
        <div class="card-header bg-white">
            <h2><i class="fas fa-drumstick-bite"></i> Cocina</h2>
        </div>
        <div class="card-body">
            <div class="card mb-4 mt-4">
                <div class="card-header text-white" style="background: #FF5100"><i class="fas fa-industry"></i> Planta de
                    Procesamientos de Lotes de Alimentos</div>
                <div class="card-body">
                    <p>Selecione el Lote para conocer el historial del tratamiento dado en la planta:</p>
                    <select wire:model="selected_insumo_id" class="form-control form-select mb-3">
                        <option value="">-- Seleccionar Insumo --</option>
                        @foreach ($insumos as $ins)
                            <option value="{{ $ins->id }}">{{ $ins->CodigoBarras }} - {{ $ins->sabor->nombre }} -
                                {{ $ins->convertirPeso('Onzas', $ins->Cantidad_Articulos) }} Onzas</option>
                        @endforeach
                    </select>

                    @if (count($logs) > 0)
                        @php $k = 0; @endphp
                        @foreach ($logs as $log)
                            @php $k++; @endphp
                            <div class="card mb-3">
                                <div class="card-header">
                                    <img src="{{ asset('assets/img/cocinero.png') }}" width="50" alt="">
                                    <b>Tratamiento #{{ $k }} -
                                        {{ \Carbon\Carbon::parse($log['fecha'])->format('m-d-Y') }}</b>
                                    <h4><b>{{ $log['insumo']['sabor']['nombre'] }} (CCP:
                                            {{ $log['ccp_code'] }})</b></h4>
                                </div>
                                <div class="card-body">

                                    <p><strong>Producto:</strong> <b>{{ $log['insumo']['CodigoBarras'] }} -
                                            {{ $log['insumo']['sabor']['nombre'] }} -

                                            <span
                                                class="badge badge-success">{{ \Carbon\Carbon::parse($log['insumo']['created_at'])->format('m-d-Y') }}
                                                (Fecha Creación)
                                                -
                                                {{ \Carbon\Carbon::parse($log['insumo']['Fecha_Vencimiento'])->format('m-d-Y') }}
                                                (Fecha de Vencimiento) </span>
                                        </b></p>
                                    <p><strong>Observaciones:</strong> {{ $log['observaciones'] }}</p>


                                    <!-- Mediciones -->
                                    <table class="table mb-2 table-bordered" style="border-radius: 15px !important">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-sticky-note"></i> Fecha</th>
                                                <th><i class="fas fa-sticky-note"></i> Fase</th>
                                                <th><i class="far fa-clock"></i> Hora</th>
                                                <th><i class="fas fa-temperature-low"></i> Temp (°F)</th>
                                                <th><i class="fas fa-user"></i> Usuario</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($log['mediciones'] as $med)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($med['created_at'])->format('m-d-Y') }}</td>
                                                    <td>{{ $labels[$med['fase']] ?? $med['fase'] }}</td>
                                                    <td>{{ $med['hora'] }}</td>
                                                    <td>{{ $med['temperatura'] }}</td>
                                                    <td>{{ $med['user']['name'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="text-right">
                                        <button
                                            wire:click.prevent="$emitTo(
                                                                'verificacion-log',
                                                                'open',
                                                                {{ $log['id'] }},
                                                                'approve'
                                                            )"
                                            class="btn btn-rounded ">Acceptable  <img
                                                src="{{ asset('assets/img/comprobado.png') }}" width="25"
                                                alt=""></button>
                                        <button
                                            wire:click.prevent="$emitTo(
                                                                'verificacion-log',
                                                                'open',
                                                                {{ $log['id'] }},
                                                                'deny'
                                                            )"
                                            class="btn btn-rounded ">Deficiency <img src="{{ asset('assets/img/cerrar.png') }}"
                                                width="25" alt=""></button>
                                    </div>
                                    <table class="table  table-bordered">
                                        <thead>
                                            <tr>
                                                   <th><i class="fas fa-sticky-note"></i> Fecha</th>
                                                <th><i class="fas fa-sticky-note"></i> Fase</th>
                                                <th><i class="fas fa-check"></i> Estado</th>
                                                <th><i class="fas fa-check-double"></i> Hora Verif.</th>
                                                <th><i class="far fa-clock"></i> Hora Revisión</th>
                                                <th><i class="fas fa-user"></i> Verificador</th>
                                                 <th><i class="fas fa-user"></i> Revisor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (!empty($log['verificacion']) && count($log['verificacion']) > 0)
                                                @foreach ($log['verificacion'] ?? [] as $ver)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($ver['created_at'])->format('m-d-Y') }}</td>
                                                        <td>{{ $log['ccp_code'] }}</td>
                                                        <td>
                                                            @if ($ver['estado'] == 'approve')
                                                                <img
                                                                    src="{{ asset('assets/img/comprobado.png') }}" width="20"
                                                                    alt=""> Acceptable
                                                            @else
                                                                <img
                                                                    src="{{ asset('assets/img/cerrar.png') }}" width="20"
                                                                    alt=""> Deficiency
                                                            @endif
                                                        </td>
                                                        <td>{{ $ver['hora_verificacion'] }}</td>
                                                        <td>{{ $ver['hora_revision_registros'] }}</td>
                                                        <td>{{ $ver['verificador']['name'] }}</td>
                                                        <td>{{ $ver['revisor']['name'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center"> <span
                                                            class="badge badge-danger"> Aun no se ha verificado por el
                                                            jefe de Operaciones.</span></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @if ($step == 1)
                <div class="card mb-3">
                    <div class="card-header text-white" style="background: #FF5100"><b>Formulario de Tratamiento de
                            Lotes de Materia Prima</b>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-1">
                                <img src="{{ asset('assets/img/fabrica.png') }}" width="100%" alt="">
                            </div>
                            <div class="col-md-11">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label>Insumo</label>
                                        <select wire:model="insumo_id" class="form-control form-select">
                                            <option value="">Seleccionar lote de insumo</option>
                                            @foreach ($insumos as $insumo)
                                                <option value="{{ $insumo->id }}">{{ $insumo->CodigoBarras }} -
                                                    {{ $insumo->sabor->nombre }} -
                                                    {{ $insumo->convertirPeso('Onzas', $insumo->Cantidad_Articulos) }}
                                                    Onzas
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label>Fecha</label>
                                        <input type="date" wire:model="fecha" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label>CCP</label>
                                        <select wire:model="ccp_code" class="form-control form-select">
                                            <option value="">Seleccionar...</option>
                                            <option value="1B">1B</option>
                                            <option value="1B-1">1B-1</option>
                                            <option value="2B">2B</option>
                                            <option value="2B-1">2B-1</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label>Observaciones</label>
                                    <textarea wire:model="observaciones" class="form-control" rows="2"></textarea>
                                </div>
                                <button onclick="loaderSave()" wire:click="nextStep"
                                    class="btn btn-primary mt-3">Siguiente <i class="fas fa-arrow-right"></i></button>
                            </div>

                        </div>

                    </div>
                </div>
            @endif

            @if ($step == 2)
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">Mediciones - CCP {{ $ccp_code }}</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fase</th>
                                    <th>Hora</th>
                                    <th>Temp (°F)</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($map[$ccp_code] as $idx => $fase)
                                    <tr>
                                        <td>{{ $labels[$fase] }}</td>
                                        <td><input type="time" wire:model="mediciones.{{ $idx }}.hora"
                                                class="form-control"></td>
                                        <td><input type="number" step="0.1"
                                                wire:model="mediciones.{{ $idx }}.temperatura"
                                                class="form-control">
                                        </td>
                                        <input type="hidden" wire:model="mediciones.{{ $idx }}.fase"
                                            value="{{ $fase }}">
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button onclick="loaderSave()" wire:click="nextStep" class="btn btn-secondary">Guardar
                            Mediciones <i class="fas fa-save"></i></button>
                    </div>
                </div>
            @endif

            @if ($step == 3)
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">¡Tratamiento Realizado!</h4>
                    <p>¡Felicidades! El tratamiento se ha completado exitosamente.</p>
                    <hr>
                    <p class="mb-0">Recuerda que siempre puedes usar utilidades de margen para mantener todo
                        organizado.</p>
                </div>
            @endif
            {{-- @if ($step == 3)
                <div class="card">
                    <div class="card-header bg-success text-white">Verificación</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fase</th>
                                    <th>Hora</th>
                                    <th>Temp</th>
                                    <th>Aceptar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mediciones as $m)
                                    <tr>
                                        <td>{{ $labels[$m['fase']] }}</td>
                                        <td>{{ $m['hora'] }}</td>
                                        <td>{{ $m['temperatura'] }}</td>
                                        <td>
                                            <select wire:model="medicionesVerification.{{ $loop->index }}"
                                                class="form-select">
                                                <option value="1">✓</option>
                                                <option value="0">✗</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label>Hora Verificación</label>
                                <input type="time" wire:model="hora_verificacion" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Hora Revisión Registros</label>
                                <input type="time" wire:model="hora_revision_registros" class="form-control">
                            </div>
                        </div>
                        <button wire:click="saveVerificacion" class="btn btn-success mt-3">Finalizar <i
                                class="fas fa-check"></i></button>
                    </div>
                </div>
            @endif --}}


        </div>
    </div>
    <script>
        window.addEventListener('swal', event => {
            Swal.fire(event.detail);
        });

        window.livewire.on('producto-creado', () => {
            swal.close();
        });

        function loaderSave() {
            swal({
                title: 'Guardando datos',
                text: 'Por favor, espere...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                onOpen: () => {
                    swal.showLoading();

                    // Cierra el cuadro si el producto se crea correctamente
                    window.livewire.on('producto-creado', () => {
                        swal.close();
                    });

                    // Cierra el cuadro y muestra un error si ocurre un problema
                    window.livewire.on('sale-error', (errorMessage) => {
                        swal.close(); // Cierra el diálogo actual
                        swal({
                            icon: 'error',
                            title: 'Error al guardar',
                            text: errorMessage || 'Ocurrió un error inesperado.',
                            confirmButtonText: 'Cerrar'
                        });
                    });
                }
            });
        }
    </script>

</div>
