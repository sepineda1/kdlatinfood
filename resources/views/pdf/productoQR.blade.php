<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalle del Lote {{$data->CodigoBarras}} ,{{$data->Cantidad_Articulos}} Unidades</title>

    <style type="text/css">
    body {
        font-family: 'Montserrat', sans-serif;
        margin: 0;
        padding: 0;
    }

    .contenedor {
        display: flex;
        font-size: 25px;
        transform: rotate(180deg);
        /* Rotación de 50 grados */
        transform-origin: center center;
        /* Cambia el punto de origen de la rotación */
        /* Ajusta la posición para centrar el contenido rotado */
        position: absolute;
        left: 10%;
        top: 40%;
        right: -10%;
        bottom: 10%;
        /* Centra el contenido */
        transform: translate(-100%, -40%) rotate(90deg);
    }

    .izquierda {
        width: 50%;
        left: 10%;
        top: 10%;
        right: -500%;

    }

    .barcode {
        text-align: center;
        font-size: 40px;
        /* Tamaño de fuente más grande */
        margin-right: -40%;
    }

    .derecha {
        width: 60%;
        font-size: 50px;
        margin-left: 500px;
        /* Ajusta el margen izquierdo */
        position: absolute;
        top: -100px;
        /* Ajusta el margen superior si es necesario */
        bottom: 0px;
        /* Mantener el elemento alineado con la parte inferior */

    }

    ul {
        list-style-type: none;
    }
    li{
            font-size: 45px !important;
        }
    </style>

</head>

<body>
    <div class="contenedor">
        <div class="izquierda">
            <img src="data:image/png;base64, {!! base64_encode($qr) !!} ">
            <p class="barcode">{{ $lot ? $lot->CodigoBarras : '000000000' }} - {{ $prod->barcode }}</p>
        </div>
        <div class="derecha">
            <ul>
                <li>No. Lot: {{ $lot ? $lot->CodigoBarras : '000000000' }} - {{ $lot ? $lot->SKU : $prod->id }} </li>
                <li>Qty: {{$prod->stock_box}} </li>
                <li>Exp: {{ \Carbon\Carbon::parse($lot->Fecha_Vencimiento)->format('m-d-y') }}</li>
                <li>SKU:{{$prod->barcode}} </li>
                @php
                    $estado = ucfirst(strtolower($prod->product->estado));
                @endphp
                <li>Name: <small> {{ $prod->product->name }} {{$prod->size->size}} {{ $estado }} ({{ $prod->stock_items }} Unid.)</small></li>
                
                <br>
                <li>{{$hora}}</li>
            </ul>
        </div>
    </div>
</body>

</html>