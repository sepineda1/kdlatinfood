<div class="row">

    <div class="col-sm-12">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h4 class="card-title">
                    <b>Drivers | Maps</b>
                </h4>
                <ul class="tabs tab-pills">

                </ul>


            </div>

            <style>
                .driver {
                    margin-top: 40px;
                }

                .driver-list {
                    display: flex;
                    background: #ffff !important;

                }

                .driver-list .item_driver {
                    padding: 10px;
                    border-left: 1px solid #ddd;
                    cursor: pointer;
                    color: #000 !important;

                }


                .driver-list .item_driver:hover {
                    background: #d6d2d2 !important;
                }

                .driver-list .item_driver .item_driver_pending {
                    background-color: #f38021;
                    padding: 5px;
                    color: #fff;
                    border-radius: 5px;
                }

                .driver-list .item_driver_active {
                    background: #d6d2d2 !important;
                }


                .tableNew {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                    /* Tamaño de letra pequeño */
                }

                .tableNew th,
                .tableNew td {
                    padding: 4px 4px;
                    /* Reducir el padding */
                    border: 1px solid #ddd;
                    text-align: center;
                }

                .tableNew thead {
                    background-color: #f8f9fa;
                    font-weight: bold;
                }

                .tableNew tbody tr:hover {
                    background-color: #f1f1f1;
                }

                /* Alternar colores de filas */
                .tableNew tbody tr:nth-child(even) {
                    background-color: #f9f9f9;
                }

                .span_status {
                    padding: 3px;
                    border-radius: 4px;
                }

                .status_danger {
                    background-color: #f29090;
                    color: #660505;
                }

                .status_success {
                    background-color: #c9f290;
                    color: #2c5506;
                }

                .status_informative {
                    background-color: #bbbdfb;
                    color: #021e57;
                }

                @keyframes flashColor {
                    0% {
                        background-color: #f6c5b1;
                    }

                    /* Color original */
                    50% {
                        background-color: #ffe5e5;
                    }

                    /* Color claro */
                    100% {
                        background-color: #f6c5b1;
                    }

                    /* Regresa al original */
                }

                .flash {
                    animation: flashColor 1s ease-in-out infinite;
                    /* Duración: 1s */
                }

                .rute_dark {
                    color: #000 !important;
                }

                .btm {
                    border: none;
                    padding: 5px;
                    border-radius: 5px;
                }

                .btm_success {
                    background-color: #c9f290;
                }

                .btm_danger {
                    background-color: #fbc7bb;
                }

                .btm_informative {
                    background-color: #bbbdfb;
                }

                .btm_orange {
                    margin-top: 5px;

                    background-color: #f38021;
                    color: #fff;
                    display: block
                }

                /* Estilo del mapa */
                #map {
                    height: 400px;
                    position: sticky;
                    top: 0;
                    z-index: 1;
                }

                .custom-tooltip {
                    background-color: white;
                    color: black;
                    font-size: 12px;
                    /* Tamaño de fuente pequeño */
                    padding: 5px;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
                    /* Sombra sutil */
                }

                .leaflet-routing-container {
                    display: none !important;
                    /* Esto ocultará el contenedor de la ruta */
                }

                .leaflet-routing-instructions {
                    display: none !important;
                    /* Esto ocultará las instrucciones de la ruta */
                }
            </style>

            <div class="widget-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="driver d-flex justify-content-between">
                            <div class="driver-list">
                                <div class="item_driver d-none">
                                    Carlos Perez
                                    <span class="item_driver_pending">30</span>
                                </div>


                            </div>
                            <div class="d-flex">
                                <select name="" id="filtroEstado" style="border:none;width: 250px;">
                                    <option value="">Todos</option>
                                    <option value="En ruta">En ruta</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Entregado">Entregado</option>
                                </select>
                                <input type="text" id="searchInput" class="text-center mx-2"
                                    style="border:none;width: 250px; " placeholder="#orden">
                                <a href="{{ url('dash') }}" class="btm btm_orange">Close <i
                                        class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                    <div class="col-md-12">
                        <table class="tableNew" id="envios-table">
                            <thead>
                                <tr style="color:#000;">
                                    <th scope="col" class="text-center"># orden</th>
                                    <th scope="col" class="text-center">State</th>
                                    <th scope="col" class="text-center">Address</th>
                                    <th scope="col" class="text-center">Customer</th>
                                    <th scope="col" class="text-center">Phone</th>
                                    <th scope="col" class="text-center">Driver</th>
                                    <th scope="col" class="text-center">Options</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" style="color:#000;" id="tableBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            getDriversList();
        });
        var SEND_ID_RUTE = 0;
        var startLat_ROUTE = 0;
        var startLng_ROUTE = 0;
        var endtLat_ROUTE = 0;
        var endtLng_ROUTE = 0;
        var customerName_ROUTE = '';
        var driverName_ROUTE = '';
        var routeTooltip = null;

        // Inicializar el mapa centrado en una ubicación
        var map = L.map('map').setView([40.7128, -74.0060], 13); // Coordenadas de Nueva York
        var routeControl;
        // Cargar capa de mapa
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        var markersLayer = L.layerGroup().addTo(map);

        // Suponiendo que los datos de los conductores se han obtenido del backend
        /*var conductores = [{
                id: 1,
                nombre: "Juan Pérez",
                lat: 40.7128,
                lon: -74.0060
            },
            {
                id: 2,
                nombre: "Carlos Gómez",
                lat: 40.7138,
                lon: -74.0070
            }
        ];

        // Define colores personalizados para los marcadores
        var colores = {
            1: 'blue', // Color para el conductor con ID 1
            2: 'green' // Color para el conductor con ID 2
        };

        // Mostrar los conductores en el mapa
        conductores.forEach(function(conductor) {
            L.marker([conductor.lat, conductor.lon], colores) //este es el que me pinta el conductor en el mapa
                .addTo(map)
                .bindPopup(conductor.nombre);
        }); */
        //setInterval(getDriversList, 5000);

        setInterval(getCoordenateEnRuta, 5000);

        function getCoordenateEnRuta() {
            if (SEND_ID_RUTE != 0) {
                let url = `/intranet/public/get-coordenate_route/${SEND_ID_RUTE}`;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Convertir la respuesta a JSON
                        var responseData = JSON.parse(xhr.responseText);
                        if (responseData.envioData.length > 0) {
                            startLat_ROUTE = responseData.envioData.latitude;
                            startLng_ROUTE = responseData.envioData.longitude;

                            updateMap(startLat_ROUTE, startLng_ROUTE,
                                driverName_ROUTE, 'https://kdlatinfood.com/intranet/public/storage/moto.png');
                            updateMap(endtLat_ROUTE, endtLng_ROUTE, customerName_ROUTE);
                            drawRoute(startLat_ROUTE, startLng_ROUTE, endtLat_ROUTE, endtLng_ROUTE);
                        } else {
                            getDriversList();
                        }
                        // Opcional: Mostrar mensaje de éxito con datos
                        //Swal.fire('Éxito', 'Respuesta: ' + JSON.stringify(responseData), 'success');
                    } else {
                        //Swal.fire('Error', 'No se pudo obtener la data', 'error');
                    }
                };
                xhr.send();
            }else{
                const urlParams = new URLSearchParams(window.location.search);
                const selectedDriverId = urlParams.get("driver_id");
                let url = `/intranet/public/get-exist-enroute/${selectedDriverId}`;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // Convertir la respuesta a JSON
                        var responseData = JSON.parse(xhr.responseText);
                        if (responseData.existRoute) {
                            getDriversList();
                        } 
                        // Opcional: Mostrar mensaje de éxito con datos
                        //Swal.fire('Éxito', 'Respuesta: ' + JSON.stringify(responseData), 'success');
                    } else {
                        //Swal.fire('Error', 'No se pudo obtener la data', 'error');
                    }
                };
                xhr.send();
            }
        }


        function getDriversList() {

            // Obtener el parámetro driver_id de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDriverId = urlParams.get("driver_id");

            // Construir la URL de la solicitud
            let url = '/intranet/public/get-driver-maps';

            if (selectedDriverId) {
                url += `?driver_id=${selectedDriverId}`;
            }

            // Realizar la solicitud AJAX para crear el producto en WooCommerce
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Convertir la respuesta a JSON
                    var responseData = JSON.parse(xhr.responseText);
                    console.log(responseData);
                    renderTable(responseData);

                    // Opcional: Mostrar mensaje de éxito con datos
                    //Swal.fire('Éxito', 'Respuesta: ' + JSON.stringify(responseData), 'success');
                } else {
                    //Swal.fire('Error', 'No se pudo obtener la data', 'error');
                }
            };
            xhr.send();
        }

        function getRandomPastelColor() {
            // Generar un color fuerte aleatorio (evitar tonos muy claros)
            const r = Math.floor(Math.random() * 156) + 100; // Rango de 100 a 255
            const g = Math.floor(Math.random() * 156) + 100; // Rango de 100 a 255
            const b = Math.floor(Math.random() * 156) + 100; // Rango de 100 a 255

            return `rgb(${r}, ${g}, ${b})`; // Retornar en formato RGB
        }

        function renderTable(jsonData) {
            clearRoute();
            const driverList = document.querySelector(".driver-list");
            driverList.innerHTML = '';

            // Obtener el parámetro driver_id de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDriverId = urlParams.get("driver_id");

            // Limpiar la tabla
            const tableBody = document.querySelector("#envios-table tbody");
            tableBody.innerHTML = '';

            jsonData.drivers.forEach(driver => {

                if (selectedDriverId === null || selectedDriverId === "") {
                    window.location.href = `?driver_id=${driver.id}`;
                }

                const driverName = driver.name;
                const pedidosCount = driver.envios.length;

                // Generamos un color pastel aleatorio para cada conductor
                const driverColor = getRandomPastelColor();

                const driverItem = document.createElement("div");
                driverItem.classList.add("item_driver");
                if (selectedDriverId == driver.id.toString()) {
                    driverItem.classList.add("item_driver_active");
                }

                driverItem.setAttribute("data-id", driver.id);
                driverItem.innerHTML = `${driverName} <span class="item_driver_pending">${pedidosCount}</span>`;

                driverItem.addEventListener("click", function() {
                    const driverId = this.getAttribute("data-id");
                    window.location.href = `?driver_id=${driverId}`;
                });

                driverList.appendChild(driverItem);

                // Si hay un driver_id en la URL, solo mostramos los envíos de ese conductor
                if (selectedDriverId && selectedDriverId !== driver.id.toString()) {
                    return; // Omitimos los conductores que no coinciden con el filtro
                }

                // Ordenar los envíos, primero los que están en "En Ruta"
                const sortedEnvios = driver.envios.sort((a, b) => {
                    if (a.state === "En Ruta" && b.state !== "En Ruta") return -1;
                    if (a.state !== "En Ruta" && b.state === "En Ruta") return 1;
                    return 0;
                });

                if (sortedEnvios.length == 0) {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td colspan="7">No existen envios para este conductor.</td>                  
                    `;
                    tableBody.appendChild(row);
                    return;
                }

                // Agregar los envíos del conductor seleccionado a la tabla
                sortedEnvios.forEach(envio => {
                    const row = document.createElement("tr");
                    if (envio.state == "En Ruta") {

                        row.classList.add('flash');
                        SEND_ID_RUTE = envio.id;

                        startLat_ROUTE = envio.location_latitude_driver;
                        startLng_ROUTE = envio.location_longitude_driver;
                        endtLat_ROUTE = envio.customer_latitude;
                        endtLng_ROUTE = envio.customer_longitude;
                        customerName_ROUTE = envio.customer_name;
                        driverName_ROUTE = driverName;

                        updateMap(startLat_ROUTE, startLng_ROUTE,
                            driverName_ROUTE, 'https://kdlatinfood.com/intranet/public/storage/moto.png'
                        );
                        updateMap(endtLat_ROUTE, endtLng_ROUTE, customerName_ROUTE);
                        drawRoute(startLat_ROUTE, startLng_ROUTE, endtLat_ROUTE, endtLng_ROUTE);
                    }
                    row.innerHTML = `
                        <td>${envio.orden_id}</td>
                        <td><span class="estado span_status ${getStatusClass(envio.state)}">${envio.state}</span></td>
                        <td>${envio.customer_address}</td>
                        <td>${envio.customer_name}</td>
                        <td>${envio.customer_phone}</td>
                        <td>${driverName}</td>
                        <td>
                            <button class="btm btm_success"><i class="fas fa-route"></i></button>
                            <button class="btm btm_informative" onclick="updateMap(${envio.customer_latitude},${envio.customer_longitude},'${envio.customer_name}')"><i class="fas fa-map-marker-alt"></i></button>
                        </td>                    
                    `;

                    tableBody.appendChild(row);
                });
            });
        }



        function clearRoute() {
            // Limpiar las rutas (si existe una)
            if (routeControl) {
                routeControl.remove();
                routeControl = null; // Opcional: Limpiar la variable de control de la ruta
            }

            // Limpiar los marcadores en el mapa
            markersLayer.clearLayers();

            if (routeTooltip) {
                map.removeLayer(routeTooltip); // Remueve el marcador con el Tooltip
                routeTooltip = null; // Limpiar la variable
            }
        }

        function getStatusClass(state) {
            switch (state) {
                case 'Pendiente':
                    return 'status_danger';
                case 'En Ruta':
                    return 'status_informative';
                case 'Entregado':
                    return 'status_success';
                default:
                    return ''; // Si no hay coincidencia, no se asigna clase
            }
        }

        // Función de búsqueda
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchTerm = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tableBody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });


        const filtroEstado = document.getElementById('filtroEstado');


        filtroEstado.addEventListener('change', function() {
            const tablaPedidos = document.getElementById('envios-table');
            console.log('hola');
            const estadoSeleccionado = filtroEstado.value.toLowerCase();
            const filas = tablaPedidos.getElementsByTagName('tr');

            for (let i = 1; i < filas.length; i++) { // Empezar desde 1 para no contar el encabezado
                const estadoCelda = filas[i].getElementsByClassName('estado')[0];
                if (estadoCelda) {
                    const estado = estadoCelda.textContent || estadoCelda.innerText;
                    if (estado.toLowerCase().includes(estadoSeleccionado) || estadoSeleccionado === "") {
                        filas[i].style.display = ''; // Mostrar fila
                    } else {
                        filas[i].style.display = 'none'; // Ocultar fila
                    }
                }
            }
        });


        function updateMap(lat, lon, customerName, iconUrl = null) {




            // Centrar el mapa en la nueva ubicación
            //map.setView([lat, lon], 13);

            // Definir el ícono personalizado si se proporciona una URL de imagen
            var customIcon;
            if (iconUrl) {
                customIcon = L.icon({
                    iconUrl: iconUrl, // URL de la imagen (como la moto)
                    iconSize: [64, 64], // Tamaño del ícono
                    iconAnchor: [32, 64], // Ajustamos el ancla al centro de la parte inferior
                    popupAnchor: [0, -64] // Ajustamos el popup
                });
            } else {
                // Usar un marcador de color naranja por defecto si no se proporciona imagen
                customIcon = L.icon({
                    iconUrl: 'https://kdlatinfood.com/intranet/public/storage/maps.png', // Marcador naranja
                    iconSize: [64, 64], // Tamaño del ícono
                    iconAnchor: [32, 64], // Ancla en la parte inferior
                    popupAnchor: [0, -48] // Ajustamos la posición del popup
                });
            }

            // Colocar el marcador en la nueva ubicación
            L.marker([lat, lon], {
                    icon: customIcon
                }).addTo(markersLayer)
                .bindPopup("<b>" + customerName + "</b><br>Ubicación: " + lat + ", " + lon)
                .openPopup();
        }

        function drawRoute(startLat, startLng, endLat, endLng) {
            // Si ya existe una ruta, la eliminamos
            if (routeControl) {
                map.removeControl(routeControl);
            }

            // Crear nueva ruta sin instrucciones
            routeControl = L.Routing.control({
                waypoints: [
                    L.latLng(startLat, startLng),
                    L.latLng(endLat, endLng)
                ],
                routeWhileDragging: false, // Desactivamos la opción para que no se actualicen instrucciones al arrastrar
                showAlternatives: false, // Desactivamos las rutas alternativas
                createMarker: function() {
                    return null; // No crear marcadores de los pasos
                },
                router: L.Routing.osrmv1({
                    geometry: 'polyline',
                    profile: 'driving',
                    steps: false, // Desactivamos los pasos de la ruta
                    alternatives: false // Desactivar las alternativas de rutas
                })
            }).addTo(map);

            // Una vez que la ruta se haya añadido al mapa, agregar el Tooltip con fondo blanco
            routeControl.on('routesfound', function(e) {
                var minutes = Math.round(e.routes[0].summary.totalTime / 60); // Tiempo estimado en minutos
                var distance = (e.routes[0].summary.totalDistance / 1000).toFixed(2); // Distancia en kilómetros

                var routeInfo = "Ruta: " + minutes + " min | Distancia: " + distance + " km";

                // Calcular el punto medio de la ruta para colocar el Tooltip en el centro
                var latLngs = e.routes[0].coordinates;
                var midIndex = Math.floor(latLngs.length / 2);
                var midLatLng = latLngs[midIndex];

                // Crear el Tooltip y agregarlo al mapa
                routeTooltip = L.marker(midLatLng) // Ubicación del Tooltip en el centro de la ruta
                    .addTo(map)
                    .bindTooltip(routeInfo, {
                        permanent: true, // Mantener el Tooltip visible
                        direction: 'top', // Dirección del Tooltip
                        className: 'custom-tooltip' // Usamos una clase CSS personalizada
                    })
                    .openTooltip();
            });
        }
    </script>
</div>
