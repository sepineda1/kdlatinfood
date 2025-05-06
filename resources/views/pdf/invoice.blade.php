<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $Sale->id }}</title>
    <style>
        body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
    color: #333;
}

.invoice-container {
    max-width: 800px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.header h1 {
    font-size: 32px;
    color: #ff7300; /* Color naranja */
    margin: 0;
}

.header p {
    font-size: 14px;
    color: #666;
}

.details {
    margin-bottom: 20px;
}

.details .section {
    display: flex;
    justify-content: space-between;
}

.details .section div {
    width: 48%;
}

.details h3 {
    color: #ff7300;
    margin-bottom: 10px;
}

.table-container {
    margin-bottom: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    text-align: left;
    padding: 8px;
    border: 1px solid #ddd;
}

table th {
    background: #ff7300;
    color: #fff;
}

table tbody tr:nth-child(even) {
    background: #f9f9f9;
}

.footer {
    text-align: center;
    font-size: 14px;
    color: #666;
    margin-top: 20px;
}

.img {
    height: 80px;
    width: auto;
    display: block; /* Hace que la imagen se comporte como un bloque */
    margin-left: auto; /* Centra la imagen */
    margin-right: auto;
}

.signature {
    width: 40%;
    border-top: 1px solid #333;
    text-align: center;
    padding-top: 10px;
    margin-top: 20px;
}

p {
    margin: 5px 0; /* Ajusta la distancia entre párrafos */
    font-size: 12px;
}

h3 {
    font-size: 14px;
}

.invoice-details {
    margin-top: 30px;
}

.invoice-item {
    display: flex;
    justify-content: space-between; /* Esto asegura que el texto y los valores estén alineados a los extremos */
    width: 100%;
    body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
    color: #333;
}

.invoice-container {
    max-width: 800px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.header h1 {
    font-size: 32px;
    color: #ff7300; /* Color naranja */
    margin: 0;
}

.header p {
    font-size: 14px;
    color: #666;
}

.details {
    margin-bottom: 20px;
}

.details .section {
    display: flex;
    justify-content: space-between;
}

.details .section div {
    width: 48%;
}

.details h3 {
    color: #ff7300;
    margin-bottom: 10px;
}

.table-container {
    margin-bottom: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    text-align: left;
    padding: 8px;
    border: 1px solid #ddd;
}

table th {
    background: #ff7300;
    color: #fff;
}

table tbody tr:nth-child(even) {
    background: #f9f9f9;
}

.footer {
    text-align: center;
    font-size: 14px;
    color: #666;
    margin-top: 20px;
}

.img {
    height: 80px;
    width: auto;
    display: block; /* Hace que la imagen se comporte como un bloque */
    margin-left: auto; /* Centra la imagen */
    margin-right: auto;
}

.signature {
    width: 20%;
    border-top: .5px solid #333;
    text-align: center;
    padding-top: 10px;
    margin-top: 20px;
    font-size: 12px
}

p {
    margin: 5px 0; /* Ajusta la distancia entre párrafos */
    font-size: 12px !important;
}

h3 {
    font-size: 14px;
}

.invoice-details {
    margin-top: 30px;
}

.invoice-item {
    display: flex;
    justify-content: space-between; /* Esto asegura que el texto y los valores estén alineados a los extremos */
    width: 100%;
    margin-bottom:-2px;
    font-size: 12px;
}

.invoice-item span {
    display: inline-block;
}

.invoice-item .label {
    text-align: left;
    width: 60%; /* Asigna el 60% del espacio al texto */
}

.invoice-item .value {
    text-align: right;
    width: 40%; /* Asigna el 40% del espacio al valor */
}

td, th {
    font-size: 12px;
}

.invoice-item span {
    display: inline-block;
}

.invoice-item .label {
    text-align: left;
    width: 70%; /* Asigna el 60% del espacio al texto */
}

.invoice-item .value {
    text-align: right;
    width: 100%; /* Asigna el 40% del espacio al valor */
}

td, th {
    font-size: 12px;
}

.page-break {
        page-break-before: always;
    }
    </style>
</head>

<body>
    <div class="invoice-container">

        
        <!-- Encabezado -->
        <div class="header">
            <img src="data:image/png;base64,{{ $logoBase64 }}" width="140px" style="opacity:.5;margin-top:-70px;" alt="Logo del negocio">
            <h1>Invoice #{{ $Sale->id }}</h1>
            <p>Thank you for your purchase. Below are the details of the items you have purchased.</p>
        </div>

        <!-- Detalles del cliente y factura -->
        <div class="details">
            <div class="section">
                <div>
                    <h3>Detalles de la Factura</h3>
                    <p>Date :  {{ now()->format('m/d/Y h:i A') }}</p>
                    <p>Driver Name: {{ $Sale->user->name }}</p>
                    <p>Salesman: @if($Sale->user->profile === 'ADMIN')
                        Administrator
                    @elseif($Sale->user->profile === 'ACCOUNTANT')
                        Accountant
                    @elseif($Sale->user->profile === 'CARRIER')
                        Carrier
                    @elseif($Sale->user->profile === 'EMPLOYEE')
                        Employee
                    @else
                        Unknown Profile
                    @endif</p>
                </div>

                <div>
                    <h3>K&D Latin Food</h3>
                    <p>7341 NW 79th Terrece</p>
                    <p>Phone: 786-582-3953</p>
                    <p>Email: kdlatinfood@gmail.com</p>
                </div>

                <div>
                    <h3>{{ $Sale->client->name }}</h3>
                    <p>{{ $Sale->client->address }}</p>
                    <p>Phone: {{ $Sale->client->phone }}</p>
                    <p>Terms: DUE ON RECEIPT</p>
                </div>
           
               
            </div>
        </div>

        <!-- Tabla de artículos -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>QTY</th>
                        <th style="text-align: right">Price</th>
                        <th style="text-align: right">Discount</th>
                        <th style="text-align: right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 0;
                        $cont = 0;
                        $discount = 0;
                    @endphp
                    @forelse ($Sale->salesDetails as $saleDetail)
                        @php
                            $i++;

                            $price = ($saleDetail->discount > 0) ?  $saleDetail->price - (($saleDetail->discount*$saleDetail->price)/100) : $saleDetail->price;
                            $discount += ($saleDetail->discount > 0) ? (($saleDetail->discount*$saleDetail->price)/100) * $saleDetail->quantity : 0;
                            $total = $saleDetail->quantity * $price;
                            $cont += $total;
                        @endphp
                    <tr>
                            <td>{{  $i }}</td>
                            <td>{{ $saleDetail->product->product->name }} {{ $saleDetail->product->size->size }}  {{ $saleDetail->product->product->estado }}</td>
                            <td>{{ $saleDetail->quantity }}</td>
                            <td style="text-align: right">${{  number_format($saleDetail->price, 2, ',', '.') }}</td>
                            <td style="text-align: right">{{ $saleDetail->discount }}%</td>
                            <td style="text-align: right">${{  number_format($total, 2, ',', '.')  }}</td>
                        </tr>
                    @empty
                        
                    @endforelse
                
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
                        <td style="text-align: right;color:red;">-${{ number_format($discount, 2, ',', '.') }}</td>
                        <td style="text-align: right"><strong>${{ number_format($cont, 2, ',', '.') }}</strong></td>
                    </tr>
                    
                </tbody>
            </table>
        </div>

        <div class="invoice-details">
            <div class="invoice-item">
                <span class="label">Sales:</span>
                <span class="value">${{ number_format($cont, 2, ',', '.') }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Net Amount:</span>
                <span class="value">${{ number_format($cont, 2, ',', '.') }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Discount:</span>
                <span class="value">${{ number_format($discount, 2) }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Sales Tax:</span>
                <span class="value">${{ number_format($Sale->sales_tax, 2) }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Total Due:</span>
                <span class="value">${{ number_format($Sale->total_due, 2) }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Total Payment:</span>
                <span class="value">${{ number_format($Sale->total_payment, 2) }}</span>
            </div>
            <div class="invoice-item">
                <span class="label">Invoice Balance:</span>
                <span class="value">${{ number_format($cont, 2, ',', '.') }}</span>
            </div>
        </div>
        
        <img src="data:image/png;base64,{{ $Firma }}" width="140px" style="background-color: #333"  alt="Logo del negocio">
        <div class="signature" style="margin-top: -5px">
            
            Customer's Signature
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>Thank you for your trust in our company. We look forward to serving you again!</p>
        </div>
    </div>

    <div class="page-break"></div>
    
    <div class="invoice-container">

        
        <!-- Encabezado -->
        <div class="header">
            <img src="data:image/png;base64,{{ $logoBase64 }}" width="140px" style="opacity:.5;margin-top:-70px;" alt="Logo del negocio">
            <h1>Package slip #{{ $Sale->id }}</h1>
            <p>Thank you for your purchase. Below are the details of the items you have purchased.</p>
        </div>

        <!-- Detalles del cliente y factura -->
        <div class="details">
            <div class="section">
                <div>
                    <h3>Detalles de la Factura</h3>
                    <p>Date :  {{ now()->format('m/d/Y h:i A') }}</p>
                    <p>Driver Name: {{ $Sale->user->name }}</p>
                    <p>Salesman: @if($Sale->user->profile === 'ADMIN')
                        Administrator
                    @elseif($Sale->user->profile === 'ACCOUNTANT')
                        Accountant
                    @elseif($Sale->user->profile === 'CARRIER')
                        Carrier
                    @elseif($Sale->user->profile === 'EMPLOYEE')
                        Employee
                    @else
                        Unknown Profile
                    @endif</p>
                </div>

                <div>
                    <h3>K&D Latin Food</h3>
                    <p>7341 NW 79th Terrece</p>
                    <p>Phone: 786-582-3953</p>
                    <p>Email: kdlatinfood@gmail.com</p>
                </div>

                <div>
                    <h3>{{ $Sale->client->name }}</h3>
                    <p>{{ $Sale->client->address }}</p>
                    <p>Phone: {{ $Sale->client->phone }}</p>
                    <p>Terms: DUE ON RECEIPT</p>
                </div>
           
               
            </div>
        </div>

        <!-- Tabla de artículos -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>QTY</th>

                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 0;
                        $cont = 0;
                    @endphp
                    @forelse ($Sale->salesDetails as $saleDetail)
                        @php
                            $i++;
                            $total = $saleDetail->quantity * $saleDetail->price;
                            $cont += $total;
                        @endphp
                    <tr>
                            <td>{{  $i }}</td>
                            <td>{{ $saleDetail->product->product->name }} {{ $saleDetail->product->size->size }}  {{ $saleDetail->product->product->estado }}</td>
                            <td>{{ $saleDetail->quantity }}</td>
                        </tr>
                    @empty
                        
                    @endforelse
                

                    
                </tbody>
            </table>
        </div>

    
        
        <img src="data:image/png;base64,{{ $Firma }}" width="140px" style="background-color: #333"  alt="Logo del negocio">
        <div class="signature" style="margin-top: -5px">
            
            Customer's Signature
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>Thank you for your trust in our company. We look forward to serving you again!</p>
        </div>
    </div>
</body>

</html>
