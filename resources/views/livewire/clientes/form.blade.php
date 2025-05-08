<!--
This software, includin any associated code, documentation and related material, is licensed solely by Oyarcegroup.com by accessing or using this software, you agree to comply with the following terms and conditions.
 This coding is licensed under the international standards IEEE and STHT, 833-3901-0093, the share, reproduction, sale or distribution without the consent of OyarceGroup.com is totally prohibited and may be criminally punished.

Oyarcegroup.com retains full ownership of this software, including all intellectual property rights associated with it. This license does not grant you any ownership rights or licenses except those explicitly provided herein.-->
<div wire:ignore.self class="modal fade" id="theModal" tabindex="-1" role="dialog" style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content ">
            <div class="modal-header " style="background: #ff5100;">
                <h5 class="modal-title text-white">
                    <b>{{ $componentName }}</b> | {{ $selected_id > 0 ? 'Edit' : 'Create' }}
                </h5>


                <button type="button" wire:loading class="btn btn-success close-btn text-info">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span style="color:white">PLEASE WAIT</span>
                </button>

            </div>
            <div class="modal-body">

                <div class="row">

                    <div class="col-sm-12 col-md-5">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" wire:model.lazy="name" class="form-control" placeholder="Ex: Kenny">
                            @error('name')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-sm-12 col-md-5">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" wire:model.lazy="last_name" class="form-control"
                                placeholder="Ex: Gutierrez">
                            @error('last_name')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>



                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" wire:model.lazy="phone" class="form-control"
                                placeholder="Ex: 786 554 9831" maxlength="10">
                            @error('phone')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label>Wallet</label>
                            <input type="text" wire:model.lazy="saldo" class="form-control" placeholder="$100"
                                maxlength="10">
                            @error('saldo')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>





                    <div class="col-sm-12 col-md-5">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" wire:model.lazy="email" class="form-control"
                                placeholder="Ex: juan@latinfood.com">
                            @error('email')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" wire:model.lazy="password" class="form-control">
                            @error('password')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-8">
                        <div class="form-group ">
                            <label>Sube una foto</label>
                            <input type="file" class="form-control" wire:model="image"
                                accept="image/x-png, image/gif, image/jpeg">
                            @error('image')
                                <span class="text-danger er">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 row">

                        <div class="col-sm-12 col-md-12 ">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" wire:model.lazy="address" readonly class="form-control"
                                    placeholder="Address" id="address" style="border: none;">
                                @error('address')
                                    <span class="text-danger er">{{ $message }}</span>
                                @enderror
                                @if ($selected_id < 1 && $address != '' && !$isBtnEnabled)
                                    <span class="text-danger er">Dirección no encontrada.</span>
                                @endif

                            </div>
                        </div>

                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Estado</label>
                                <select wire:model="state" id="state" class="form-control addres">
                                    <option value="">Seleccione un estado</option>
                                    @foreach ($states as $stateKey => $stateName)
                                        <option value="{{ $stateKey }}">{{ $stateName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input
                                    type="text"
                                    wire:model.lazy="city"
                                    id="city"
                                    class="form-control addres"
                                    placeholder="Ingrese la ciudad"
                                >

                            </div>
                        </div>

                        {{--<div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <select wire:model="city" id="city" class="form-control addres">
                                    <option value="">Seleccione una ciudad</option>
                                    @foreach ($cities as $cityOption)
                                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>--}}

                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" class="form-control addres" id="street"
                                    placeholder="Ingrese la calle y número">
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-2">
                            <div class="form-group">
                                <label>Código Postal:</label>
                                <input type="text" class="form-control addres" id="zipcode"
                                    placeholder="33101 ">
                            </div>
                        </div>

                        @if (!empty($address))
                            <div class="col-md-12">
                                <style>
                                    .direccion {
                                        background-color: #fcf1ec;
                                        border-radius: 10px;
                                        font-weight: 400;
                                    }
                                </style>
                                <div class="direccion p-2">
                                    <div class="d-flex">
                                        <img width="80px" class="pr-3"
                                            src="{{ asset('../storage/app/public/box.png') }}" alt="">
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <p style="font-size:12px;"><b><i class="fas fa-user"> </i> Name :</b>
                                                    {{ $name }} {{ $last_name }}</p>
                                                <p style="font-size:12px;"><b><i class="fas fa-box-open"></i> Order
                                                        shipping address :</b> <span
                                                        id="textAddresCheck">{{ $address }} </span> </p>
                                                @if (!$isBtnEnabled)
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        wire:click="validateAddress()"><i
                                                            class="fas fa-exclamation"></i> Check Address</button>
                                                @else
                                                    <button type="button" class="btn btn-success btn-sm"><i
                                                            class="fas fa-check"></i> verified address
                                                    </button>
                                                @endif

                                                @if ($selected_id > 1)
                                                    <button type="button" wire:click="cleanAddres()"
                                                        class="btn btn-danger btn-sm"><i
                                                            class="fas fa-trash"></i></button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        @endif
                        <div class="col-md-12">

                            <hr>

        
                            <table class="tabla-profesional">
                                <thead>
                                    <tr>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Nombre del Servicio</th>
                                        <th scope="col">Costo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($catalogoServicePay as $servicePay)
                                        <tr>
                                            <td>
                                                <div class="toggle-container">
                                                    <input type="checkbox" id="chk{{ $servicePay->id }}"
                                                        wire:model="serviceStates.{{ $servicePay->id }}"
                                                        wire:click="checkDeliveryFree({{ $servicePay->id }})"
                                                        class="toggle-checkbox">
                                                    <label for="chk{{ $servicePay->id }}"
                                                        class="toggle-label"></label>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $servicePay->name }}
                                            </td>
                                            <td>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text">$</span>

                                                    <input type="number"
                                                        min="0"
                                                        wire:change="checkDeliveryFree({{ $servicePay->id }})"
                                                        wire:model.lazy="serviceAmounts.{{ $servicePay->id }}"
                                                        class="form-control"
                                                        @if ($servicePay->id === 1 && ($serviceStates[$servicePay->id] ?? false)) readonly @endif>

                                                </div>

                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>


                        <div>

                        </div>
                    </div>


                </div>


            </div>
            @push('scripts')
                <script>
                    window.livewire.on('producto-creado', () => {
                        swal.close();
                    });
                </script>
            @endpush
            <script>
                document.querySelectorAll('.addres').forEach(input => {
                    input.addEventListener('blur', function() {
                        updateAddressAttribute();
                    });
                });

                function updateAddressAttribute() {
                    let state = document.getElementById('state').value;
                    let city = document.getElementById('city').value;
                    let street = document.getElementById('street').value;
                    let zipcode = document.getElementById('zipcode').value;


                    let direccion =
                        `${street}, ${city}, ${state}, ${zipcode}, USA`; // Suponiendo que tienes una función que obtiene la dirección
                    Livewire.emit('updateAddresInputs', direccion);
                }

                function cleanAddres() {
                    document.getElementById('state').selectIndex = 0;
                    document.getElementById('city').selectIndex = 0;
                    document.getElementById('street').value = "";
                    document.getElementById('zipcode').value = "";
                    loader();
                }

                function VerificateAddres() {
                    var textAddresCheck = document.getElementById("textAddresCheck").textContent;

                    var isValidate = getCoordinates(textAddresCheck);
                    console.log(isValidate);
                    if (isValidate) {
                        document.getElementById("btnSave").classList.remove('d-none');
                        return;
                    }
                    document.getElementById("btnSave").classList.add('d-none');
                    alert("La direccion no es valida.");
                }

                function loader() {
                    swal({
                        title: 'Actualizando datos',
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

                function loadAddress() {
                    swal({
                        title: 'Verificando Dirección',
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
            <div class="modal-footer">

                <button type="button" wire:click.prevent="resetUI()" class="btn btn-dark close-btn text-info"
                    data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>



                <button type="button" wire:click.prevent="closeModal()"
                    class="btn btn-dark close-btn text-info d-none" data-dismiss="modal">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    Close
                </button>



                @if ($selected_id < 1)
                    @if ($isBtnEnabled)
                        <button type="button" id="btnSave" wire:click.prevent="Store()" onclick="loader2()"
                            class="btn btn-warning mb-2 mr-2 btn-rounded close-modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                </path>
                            </svg>
                            Save
                        </button>
                    @endif
                @else
                    <button type="button" wire:click.prevent="Update()" onclick="loader2()"
                        class="btn btn-outline-primary btn-rounded mb-2 close-modal">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"
                            stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"
                            class="css-i6dzq1">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Update
                    </button>
                @endif


            </div>
        </div>
    </div>
</div>
