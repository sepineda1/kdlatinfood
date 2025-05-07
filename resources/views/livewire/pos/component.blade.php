<div>

    <div class="row layout-top-spacing">

        <div class="col-sm-12 col-md-8">
           
            <!-- DETALLES -->
            @include('livewire.pos.partials.detail')
        </div>

        <div class="col-sm-12 col-md-4">
            <!-- TOTAL -->
            @include('livewire.pos.partials.total')

            <!-- DENOMINATIONS -->
            @include('livewire.pos.partials.coins')

            @include('livewire.pos.partials.delivery-modal')

        </div>
    </div>

</div>

<script src="{{ asset('js/keypress.js') }}"></script>
<script src="{{ asset('js/onscan.js') }}"></script>

<script>

window.addEventListener('openDeliveryModal', e => $('#deliveryModal').modal('show'));
window.addEventListener('closeDeliveryModal', e => $('#deliveryModal').modal('hide'));

try {

    onScan.attachTo(document, {
        suffixKeyCodes: [13],
        onScan: function(barcode) {
            console.log(barcode)
            window.livewire.emit('scan-code', barcode)
        },
        onScanError: function(e) {
            //console.log(e)
        }
    })

    console.log('Scanner ready!')


} catch (e) {
    console.log('Error de lectura: ', e)
}


function loaderText(texto) {
        swal({
            title: texto,
            text: 'Por favor, espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: () => {
                swal.showLoading();
                window.livewire.on('success', () => {
                    swal.close();
                });
            }
        });
    }


</script>


@include('livewire.pos.scripts.shortcuts')
@include('livewire.pos.scripts.events')
@include('livewire.pos.scripts.general')