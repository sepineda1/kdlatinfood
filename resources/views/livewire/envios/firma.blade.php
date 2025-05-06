
<div>
  <div wire:ignore.self class="modal fade" id="modalFirma" tabindex="-1" role="dialog"  style="backdrop-filter: blur(10px);">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-dark">
          <h5 class="modal-title text-white">
            <b>Costumer Sign Received</b>
          </h5>
          <h6 class="text-center text-warning" wire:loading>PLEASE WAIT</h6>
        </div>
        <div class="modal-body">
          <style>
            .clear {
              border-color: red;
            }
          </style>
          <div class="row">
            <h3 class="text-center">Please enter the sign:</h3><br>
            <h3 class="text-center"><strong>Only the Costumer digital sign.</strong></h3><br>
            <div class="wrapper">
              <canvas id="signature-pad" class="signature-pad aligncenter" width="500" height="200"></canvas>
            </div>
            <button class="aligncenter tabmenu" id="clear">Clear</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" id="btnConfirmarFirma">Confirm</button>
          <button type="button" class="btn btn-dark close-btn text-info" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <style type="text/css">
    .row {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .wrapper {
      margin-top: 20px;
      position: relative;
      width: 400px;
      height: 200px;
      -moz-user-select: none;
      -webkit-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }

    .signature-pad {
      position: absolute;
      left: 0;
      top: 0;
      width: 400px;
      height: 200px;
      background-color: white;
      border: 2px solid black;
    }

    #clear {
      margin-top: 20px;
      display: block;
      border-radius: 5px;
    }
  </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
  <script type="text/javascript">
  function loader() {
                                                        swal({
                                                            title: 'Cargando datos',
                                                            text: 'Por favor, espere...',
                                                            allowOutsideClick: false,
                                                            allowEscapeKey: false,
                                                            showConfirmButton: false,
                                                            onOpen: () => {
                                                                swal.showLoading();

                                                                window.livewire.on('producto-creado',
                                                            () => {
                                                                    swal.close();
                                                                    
                                                                });
                                                               
                                                            }
                                                        });
                                                    }

    var canvas = document.getElementById('signature-pad');
    var signaturePad = new SignaturePad(canvas, {
      backgroundColor: 'rgb(255, 255, 255,1)', // necessary for saving image as JPEG; can be removed if only saving as PNG or SVG
      penColor: 'rgb(0, 0, 0)' // Color del trazo
    });

    // Limpiar el canvas al hacer clic en "Clear"
    document.getElementById('clear').addEventListener('click', function () {
      signaturePad.clear();
    });

    // Generar y descargar la imagen de la firma al hacer clic en "Confirmar Firma"
    document.getElementById('btnConfirmarFirma').addEventListener('click', function () {
      if (signaturePad.isEmpty()) {
        alert('Please, enter any sign before confirm.');
        return;
      }
      loader();
      // Generar la imagen de la firma en formato base64
      var signatureImage = signaturePad.toDataURL();

      console.log(signatureImage);
      // Crear un elemento de enlace para la descarga
      /*var downloadLink = document.createElement('a');
      downloadLink.href = signatureImage;
      downloadLink.download = 'firma.png';
      downloadLink.target = '_blank';

      // Agregar el enlace al documento y hacer clic en él para descargar la imagen
      document.body.appendChild(downloadLink);
      downloadLink.click();

      // Eliminar el enlace del documento
      document.body.removeChild(downloadLink);*/
      var saleId = document.getElementById('IdFirma').value;
      fetch('/intranet/public/update-fin/' + saleId, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                firma: signatureImage
                                            })
                                        })
                                        .then(function(response) {
                                            if (response.ok) {
                                                // Estado actualizado correctamente
                                                //alert('Delivery Status changed.');
                                                window.open('invoice/pdf/' + saleId, '_blank');
                                                window.location.reload(); // Recargar la página
                                            } else {
                                                // Error al actualizar el estado
                                                alert('Error - Delivery status not changed.');
                                            }
                                        })
                                        .catch(function(error) {
                                            // Error en la petición AJAX
                                            alert('Error in AJAX request.');
                                        });
    });

    
  </script>
</div>
