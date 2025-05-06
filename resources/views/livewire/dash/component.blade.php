<div>
@role('Carrier')
<div class="row sales layout-top-spacing">

<div class="col-sm-12">
    <div class="widget widget-chart-one">
        <div class="widget-heading">
            <h4 class="card-title">
                <b>Shipping | Analist</b>
            </h4>

        </div>
        <div class="widget-content row">
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget-one ">
                    <div class="widget-content">
                        <div class="w-numeric-value">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-shopping-cart">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                    </path>
                                </svg>
                            </div>
                            <div class="w-content">
                                <span class="w-value">{{ $pendingCount }}</span>
                                <span class="w-numeric-title">Total Envios Pendientes</span>
                            </div>
                        </div>
                        <div class="w-chart" style="position: relative;">
                            <div id="total-orders" style="min-height: 295px;">
                                <div id="apexchartsy7e34bu7h" class="apexcharts-canvas apexchartsy7e34bu7h light"
                                    style="width: 410px; height: 295px;"><svg id="SvgjsSvg1949" width="410"
                                        height="295" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        xmlns:svgjs="http://svgjs.com/svgjs" class="apexcharts-svg"
                                        xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                        style="background: transparent;">
                                        <g id="SvgjsG1951" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(0, 125)">
                                            <defs id="SvgjsDefs1950">
                                                <clipPath id="gridRectMasky7e34bu7h">
                                                    <rect id="SvgjsRect1955" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <clipPath id="gridRectMarkerMasky7e34bu7h">
                                                    <rect id="SvgjsRect1956" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <linearGradient id="SvgjsLinearGradient1962" x1="0" y1="0" x2="0"
                                                    y2="1">
                                                    <stop id="SvgjsStop1963" stop-opacity="0.4"
                                                        stop-color="rgba(255,255,255,0.4)" offset="0.45"></stop>
                                                    <stop id="SvgjsStop1964" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                    <stop id="SvgjsStop1965" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                </linearGradient>
                                            </defs>
                                            <line id="SvgjsLine1954" x1="0" y1="0" x2="0" y2="170" stroke="#b6b6b6"
                                                stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0"
                                                width="1" height="170" fill="#b1b9c4" filter="none"
                                                fill-opacity="0.9" stroke-width="1"></line>
                                            <g id="SvgjsG1968" class="apexcharts-xaxis" transform="translate(0, 0)">
                                                <g id="SvgjsG1969" class="apexcharts-xaxis-texts-g"
                                                    transform="translate(0, -4)"></g>
                                            </g>
                                            <g id="SvgjsG1972" class="apexcharts-grid">
                                                <line id="SvgjsLine1974" x1="0" y1="170" x2="410" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                                <line id="SvgjsLine1973" x1="0" y1="1" x2="0" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                            </g>
                                            <g id="SvgjsG1958"
                                                class="apexcharts-area-series apexcharts-plot-series">
                                                <g id="SvgjsG1959" class="apexcharts-series" seriesName="Sales"
                                                    data:longestSeries="true" rel="1" data:realIndex="0">
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        fill="url(#SvgjsLinearGradient1962)" fill-opacity="1"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        fill="none" fill-opacity="1" stroke="#ffffff"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <g id="SvgjsG1960" class="apexcharts-series-markers-wrap">
                                                        <g class="apexcharts-series-markers">
                                                            <circle id="SvgjsCircle1980" r="0" cx="0" cy="0"
                                                                class="apexcharts-marker werl2y1vr no-pointer-events"
                                                                stroke="#ffffff" fill="#ffffff" fill-opacity="1"
                                                                stroke-width="2" stroke-opacity="0.9"
                                                                default-marker-size="0"></circle>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG1961" class="apexcharts-datalabels"></g>
                                                </g>
                                            </g>
                                            <line id="SvgjsLine1975" x1="0" y1="0" x2="410" y2="0" stroke="#b6b6b6"
                                                stroke-dasharray="0" stroke-width="1"
                                                class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine1976" x1="0" y1="0" x2="410" y2="0"
                                                stroke-dasharray="0" stroke-width="0"
                                                class="apexcharts-ycrosshairs-hidden"></line>
                                            <g id="SvgjsG1977" class="apexcharts-yaxis-annotations"></g>
                                            <g id="SvgjsG1978" class="apexcharts-xaxis-annotations"></g>
                                            <g id="SvgjsG1979" class="apexcharts-point-annotations"></g>
                                        </g>
                                        <rect id="SvgjsRect1953" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                            fill="#fefefe" opacity="1" stroke-width="0" stroke="none"
                                            stroke-dasharray="0"></rect>
                                        <g id="SvgjsG1970" class="apexcharts-yaxis" rel="0"
                                            transform="translate(-21, 0)">
                                            <g id="SvgjsG1971" class="apexcharts-yaxis-texts-g"></g>
                                        </g>
                                    </svg>
                                    <div class="apexcharts-legend"></div>
                                    <div class="apexcharts-tooltip dark">
                                        <div class="apexcharts-tooltip-series-group"><span
                                                class="apexcharts-tooltip-marker"
                                                style="background-color: rgb(255, 255, 255);"></span>
                                            <div class="apexcharts-tooltip-text"
                                                style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                <div class="apexcharts-tooltip-y-group"><span
                                                        class="apexcharts-tooltip-text-label"></span><span
                                                        class="apexcharts-tooltip-text-value"></span></div>
                                                <div class="apexcharts-tooltip-z-group"><span
                                                        class="apexcharts-tooltip-text-z-label"></span><span
                                                        class="apexcharts-tooltip-text-z-value"></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="resize-triggers">
                                <div class="expand-trigger">
                                    <div style="width: 411px; height: 296px;"></div>
                                </div>
                                <div class="contract-trigger"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget-one ">
                    <div class="widget-content">
                        <div class="w-numeric-value">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-shopping-cart">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                    </path>
                                </svg>
                            </div>
                            <div class="w-content">
                                <span class="w-value">{{ $currentCount }}</span>
                                <span class="w-numeric-title">Total Envios en Camino</span>
                            </div>
                        </div>
                        <div class="w-chart" style="position: relative;">
                            <div id="total-orders" style="min-height: 295px;">
                                <div id="apexchartsy7e34bu7h" class="apexcharts-canvas apexchartsy7e34bu7h light"
                                    style="width: 410px; height: 295px;"><svg id="SvgjsSvg1949" width="410"
                                        height="295" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        xmlns:svgjs="http://svgjs.com/svgjs" class="apexcharts-svg"
                                        xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                        style="background: transparent;">
                                        <g id="SvgjsG1951" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(0, 125)">
                                            <defs id="SvgjsDefs1950">
                                                <clipPath id="gridRectMasky7e34bu7h">
                                                    <rect id="SvgjsRect1955" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <clipPath id="gridRectMarkerMasky7e34bu7h">
                                                    <rect id="SvgjsRect1956" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <linearGradient id="SvgjsLinearGradient1962" x1="0" y1="0" x2="0"
                                                    y2="1">
                                                    <stop id="SvgjsStop1963" stop-opacity="0.4"
                                                        stop-color="rgba(255,255,255,0.4)" offset="0.45"></stop>
                                                    <stop id="SvgjsStop1964" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                    <stop id="SvgjsStop1965" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                </linearGradient>
                                            </defs>
                                            <line id="SvgjsLine1954" x1="0" y1="0" x2="0" y2="170" stroke="#b6b6b6"
                                                stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0"
                                                width="1" height="170" fill="#b1b9c4" filter="none"
                                                fill-opacity="0.9" stroke-width="1"></line>
                                            <g id="SvgjsG1968" class="apexcharts-xaxis" transform="translate(0, 0)">
                                                <g id="SvgjsG1969" class="apexcharts-xaxis-texts-g"
                                                    transform="translate(0, -4)"></g>
                                            </g>
                                            <g id="SvgjsG1972" class="apexcharts-grid">
                                                <line id="SvgjsLine1974" x1="0" y1="170" x2="410" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                                <line id="SvgjsLine1973" x1="0" y1="1" x2="0" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                            </g>
                                            <g id="SvgjsG1958"
                                                class="apexcharts-area-series apexcharts-plot-series">
                                                <g id="SvgjsG1959" class="apexcharts-series" seriesName="Sales"
                                                    data:longestSeries="true" rel="1" data:realIndex="0">
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        fill="url(#SvgjsLinearGradient1962)" fill-opacity="1"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        fill="none" fill-opacity="1" stroke="#ffffff"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <g id="SvgjsG1960" class="apexcharts-series-markers-wrap">
                                                        <g class="apexcharts-series-markers">
                                                            <circle id="SvgjsCircle1980" r="0" cx="0" cy="0"
                                                                class="apexcharts-marker werl2y1vr no-pointer-events"
                                                                stroke="#ffffff" fill="#ffffff" fill-opacity="1"
                                                                stroke-width="2" stroke-opacity="0.9"
                                                                default-marker-size="0"></circle>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG1961" class="apexcharts-datalabels"></g>
                                                </g>
                                            </g>
                                            <line id="SvgjsLine1975" x1="0" y1="0" x2="410" y2="0" stroke="#b6b6b6"
                                                stroke-dasharray="0" stroke-width="1"
                                                class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine1976" x1="0" y1="0" x2="410" y2="0"
                                                stroke-dasharray="0" stroke-width="0"
                                                class="apexcharts-ycrosshairs-hidden"></line>
                                            <g id="SvgjsG1977" class="apexcharts-yaxis-annotations"></g>
                                            <g id="SvgjsG1978" class="apexcharts-xaxis-annotations"></g>
                                            <g id="SvgjsG1979" class="apexcharts-point-annotations"></g>
                                        </g>
                                        <rect id="SvgjsRect1953" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                            fill="#fefefe" opacity="1" stroke-width="0" stroke="none"
                                            stroke-dasharray="0"></rect>
                                        <g id="SvgjsG1970" class="apexcharts-yaxis" rel="0"
                                            transform="translate(-21, 0)">
                                            <g id="SvgjsG1971" class="apexcharts-yaxis-texts-g"></g>
                                        </g>
                                    </svg>
                                    <div class="apexcharts-legend"></div>
                                    <div class="apexcharts-tooltip dark">
                                        <div class="apexcharts-tooltip-series-group"><span
                                                class="apexcharts-tooltip-marker"
                                                style="background-color: rgb(255, 255, 255);"></span>
                                            <div class="apexcharts-tooltip-text"
                                                style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                <div class="apexcharts-tooltip-y-group"><span
                                                        class="apexcharts-tooltip-text-label"></span><span
                                                        class="apexcharts-tooltip-text-value"></span></div>
                                                <div class="apexcharts-tooltip-z-group"><span
                                                        class="apexcharts-tooltip-text-z-label"></span><span
                                                        class="apexcharts-tooltip-text-z-value"></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="resize-triggers">
                                <div class="expand-trigger">
                                    <div style="width: 411px; height: 296px;"></div>
                                </div>
                                <div class="contract-trigger"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget-one ">
                    <div class="widget-content">
                        <div class="w-numeric-value">
                            <div class="w-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-shopping-cart">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6">
                                    </path>
                                </svg>
                            </div>
                            <div class="w-content">
                                <span class="w-value">{{ $finishedCount }}</span>
                                <span class="w-numeric-title">Total Envios terminados</span>
                            </div>
                        </div>
                        <div class="w-chart" style="position: relative;">
                            <div id="total-orders" style="min-height: 295px;">
                                <div id="apexchartsy7e34bu7h" class="apexcharts-canvas apexchartsy7e34bu7h light"
                                    style="width: 410px; height: 295px;"><svg id="SvgjsSvg1949" width="410"
                                        height="295" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        xmlns:svgjs="http://svgjs.com/svgjs" class="apexcharts-svg"
                                        xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                        style="background: transparent;">
                                        <g id="SvgjsG1951" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(0, 125)">
                                            <defs id="SvgjsDefs1950">
                                                <clipPath id="gridRectMasky7e34bu7h">
                                                    <rect id="SvgjsRect1955" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <clipPath id="gridRectMarkerMasky7e34bu7h">
                                                    <rect id="SvgjsRect1956" width="412" height="172" x="-1" y="-1"
                                                        rx="0" ry="0" fill="#ffffff" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0"></rect>
                                                </clipPath>
                                                <linearGradient id="SvgjsLinearGradient1962" x1="0" y1="0" x2="0"
                                                    y2="1">
                                                    <stop id="SvgjsStop1963" stop-opacity="0.4"
                                                        stop-color="rgba(255,255,255,0.4)" offset="0.45"></stop>
                                                    <stop id="SvgjsStop1964" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                    <stop id="SvgjsStop1965" stop-opacity="0.05"
                                                        stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                </linearGradient>
                                            </defs>
                                            <line id="SvgjsLine1954" x1="0" y1="0" x2="0" y2="170" stroke="#b6b6b6"
                                                stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0"
                                                width="1" height="170" fill="#b1b9c4" filter="none"
                                                fill-opacity="0.9" stroke-width="1"></line>
                                            <g id="SvgjsG1968" class="apexcharts-xaxis" transform="translate(0, 0)">
                                                <g id="SvgjsG1969" class="apexcharts-xaxis-texts-g"
                                                    transform="translate(0, -4)"></g>
                                            </g>
                                            <g id="SvgjsG1972" class="apexcharts-grid">
                                                <line id="SvgjsLine1974" x1="0" y1="170" x2="410" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                                <line id="SvgjsLine1973" x1="0" y1="1" x2="0" y2="170"
                                                    stroke="transparent" stroke-dasharray="0"></line>
                                            </g>
                                            <g id="SvgjsG1958"
                                                class="apexcharts-area-series apexcharts-plot-series">
                                                <g id="SvgjsG1959" class="apexcharts-series" seriesName="Sales"
                                                    data:longestSeries="true" rel="1" data:realIndex="0">
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        fill="url(#SvgjsLinearGradient1962)" fill-opacity="1"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 170L 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896C 410 59.6103896103896 410 59.6103896103896 410 170M 410 59.6103896103896z"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <path id="apexcharts-area-0"
                                                        d="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        fill="none" fill-opacity="1" stroke="#ffffff"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                        stroke-dasharray="0" class="apexcharts-area" index="0"
                                                        clip-path="url(#gridRectMasky7e34bu7h)"
                                                        pathTo="M 0 92.72727272727272C 15.944444444444445 92.72727272727272 29.611111111111114 59.6103896103896 45.55555555555556 59.6103896103896C 61.5 59.6103896103896 75.16666666666667 70.64935064935064 91.11111111111111 70.64935064935064C 107.05555555555556 70.64935064935064 120.72222222222224 26.493506493506487 136.66666666666669 26.493506493506487C 152.61111111111111 26.493506493506487 166.2777777777778 65.12987012987011 182.22222222222223 65.12987012987011C 198.16666666666669 65.12987012987011 211.83333333333334 4.415584415584391 227.7777777777778 4.415584415584391C 243.72222222222226 4.415584415584391 257.3888888888889 65.12987012987011 273.33333333333337 65.12987012987011C 289.2777777777778 65.12987012987011 302.94444444444446 26.493506493506487 318.8888888888889 26.493506493506487C 334.83333333333337 26.493506493506487 348.5 70.64935064935064 364.44444444444446 70.64935064935064C 380.3888888888889 70.64935064935064 394.05555555555554 59.6103896103896 410 59.6103896103896"
                                                        pathFrom="M -1 170L -1 170L 45.55555555555556 170L 91.11111111111111 170L 136.66666666666669 170L 182.22222222222223 170L 227.7777777777778 170L 273.33333333333337 170L 318.8888888888889 170L 364.44444444444446 170L 410 170">
                                                    </path>
                                                    <g id="SvgjsG1960" class="apexcharts-series-markers-wrap">
                                                        <g class="apexcharts-series-markers">
                                                            <circle id="SvgjsCircle1980" r="0" cx="0" cy="0"
                                                                class="apexcharts-marker werl2y1vr no-pointer-events"
                                                                stroke="#ffffff" fill="#ffffff" fill-opacity="1"
                                                                stroke-width="2" stroke-opacity="0.9"
                                                                default-marker-size="0"></circle>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG1961" class="apexcharts-datalabels"></g>
                                                </g>
                                            </g>
                                            <line id="SvgjsLine1975" x1="0" y1="0" x2="410" y2="0" stroke="#b6b6b6"
                                                stroke-dasharray="0" stroke-width="1"
                                                class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine1976" x1="0" y1="0" x2="410" y2="0"
                                                stroke-dasharray="0" stroke-width="0"
                                                class="apexcharts-ycrosshairs-hidden"></line>
                                            <g id="SvgjsG1977" class="apexcharts-yaxis-annotations"></g>
                                            <g id="SvgjsG1978" class="apexcharts-xaxis-annotations"></g>
                                            <g id="SvgjsG1979" class="apexcharts-point-annotations"></g>
                                        </g>
                                        <rect id="SvgjsRect1953" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                            fill="#fefefe" opacity="1" stroke-width="0" stroke="none"
                                            stroke-dasharray="0"></rect>
                                        <g id="SvgjsG1970" class="apexcharts-yaxis" rel="0"
                                            transform="translate(-21, 0)">
                                            <g id="SvgjsG1971" class="apexcharts-yaxis-texts-g"></g>
                                        </g>
                                    </svg>
                                    <div class="apexcharts-legend"></div>
                                    <div class="apexcharts-tooltip dark">
                                        <div class="apexcharts-tooltip-series-group"><span
                                                class="apexcharts-tooltip-marker"
                                                style="background-color: rgb(255, 255, 255);"></span>
                                            <div class="apexcharts-tooltip-text"
                                                style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                <div class="apexcharts-tooltip-y-group"><span
                                                        class="apexcharts-tooltip-text-label"></span><span
                                                        class="apexcharts-tooltip-text-value"></span></div>
                                                <div class="apexcharts-tooltip-z-group"><span
                                                        class="apexcharts-tooltip-text-z-label"></span><span
                                                        class="apexcharts-tooltip-text-z-value"></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="resize-triggers">
                                <div class="expand-trigger">
                                    <div style="width: 411px; height: 296px;"></div>
                                </div>
                                <div class="contract-trigger"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endcan
</div>
@role('Employee')
<div class="row sales layout-top-spacing">
        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Products | Analist</b>
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
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total de Productos</div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{$totalProd}} </span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total de Productos PreCocido </div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{$totalProdCr}} </span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total de Productos Crudos</div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                       >
                                        <span>{{$totalProdCc}} </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Total de sabores</div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"><span>{{ $totalProd }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Product Mas Vendido</div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                        style="white-space: nowrap; overflow: hidden;font-size: 7px; text-overflow: ellipsis;">
                                        <span class="small">{{ $nombreSabor  }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <div class="card mb-3 widget-content bg-night-fade text-white">
                            <div class="widget-content-wrapper d-flex justify-content-between align-items-center">
                                <div class="widget-content-left">
                                    <div class="widget-heading">Ultimo Pruducto Agregado</div>
                                    <div class="widget-subheading">Last year expenses</div>
                                </div>
                                <div class="widget-content-right">
                                    <div class="widget-numbers"
                                        style="white-space: nowrap; overflow: hidden;font-size: 7px; text-overflow: ellipsis;">
                                        <span>{{$ultimoSaborCreado->name}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endrole
    <div class="row layout-top-spacing mt-5">
        @role('Accountant|Admin')
        <div class="col-sm-12 col-md-6">
            <div class="widget widget-chart-one">
                <h4 class="p-3 text-center text-theme-1 font-bold" style="color: #FF5100;">Top de Productos</h4>
                <div id="chartTop5">
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div class="widget widget-chart-one">
                <h4 class="p-3 text-center text-theme-1 font-bold" style="color: #FF5100;">Ventas Semanales</h4>
                <div id="areaChart">
                </div>
            </div>
        </div>
        @endrole
    </div>



    <div class="row pt-5">
    @role('Accountant|Admin')
        <div class="col-sm-12 ">
            <div class="widget widget-chart-one">
                <h4 class="p-3 text-center text-theme-1 font-bold" style="  color: #FF5100;">Ventas por Año: {{$year}}
                </h4>
                <div id="chartMonth">
                </div>
            </div>
        </div>
        @endrole
    </div>
    
    @include('livewire.dash.script')
   