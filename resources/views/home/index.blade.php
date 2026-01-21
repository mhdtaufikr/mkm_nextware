@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="home"></i></div>
                            Dashboard
                        </h1>
                        <div class="page-header-subtitle">
                            OTDP Inbound & Outbound per Cutting Center
                        </div>
                    </div>

                    <div class="col-auto mt-4">
                        <form method="GET" action="{{ route('home') }}"
                              class="d-flex gap-2 align-items-center">
                            <label class="text-white-50 small mb-0">Location</label>
                            <select name="location_id"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $selected && $selected->id == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->display_name ?? $loc->location_code }}
                                        @if((int)($loc->is_default ?? 0) === 1) (default) @endif
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n15">
        @include('partials.alert')

        @if(!$selected)
            <div class="card">
                <div class="card-body text-muted">
                    Tidak ada location active.
                </div>
            </div>
        @else

        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="card card-custom">
                    <div class="card-header">
                         <div class="card-title">OTDP Inbound</div>
                    </div>
                    <div class="card-body">
                        <div id="otdpInboundCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div hidden class="carousel-indicators">
                                @foreach ($otdpInbound as $cc => $rows)
                                    <button type="button" data-bs-target="#otdpInboundCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                @endforeach
                            </div>
                            <!-- Fix HTML - Pastikan slide pertama punya class active -->
                            <div class="carousel-inner">
                                @foreach ($otdpInbound as $cc => $rows)
                                    @php
                                        $totalPercentage = 0;
                                        $count = 0;
                                        $today = now()->day;
                                        foreach ($rows as $entry) {
                                            if ($entry->day <= $today && $entry->percentage !== null) {
                                                $totalPercentage += $entry->percentage;
                                                $count++;
                                            }
                                        }
                                        $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                                    @endphp
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">  <!-- ✅ PENTING: active class -->
                                        <div class="row">
                                            <div class="col-md-8">
                                                <table class="indicator-table mb-4">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Signal Indicator</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center py-3">
                                                                <span class="signal green">G</span> <span class="mx-2">≥ 95%</span>
                                                                <span class="signal yellow">Y</span> <span class="mx-2">≥ 85%</span>
                                                                <span class="signal red">R</span> <span class="mx-2">< 85%</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-4">
                                                <table class="indicator-table mb-4">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Average OTDC</th>
                                                            <th class="text-center">Signal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center py-3">{{ number_format($averagePercentage, 2) }}%</td>
                                                            <td class="text-center py-3">
                                                                <span class="signal {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                                                    {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>


                                        <p style="margin-top: -20px" class="text-center">{{ $cc }}</p>
                                        <div style="margin-top: -20px" class="chart-container">
                                            <!-- ✅ TAMBAHKAN HEIGHT EXPLICIT -->
                                            <div id="otdp-inbound-chart-{{ $cc }}" class="chart-custom" style="width: 100%; height: 300px;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#otdpInboundCarousel" data-bs-slide="prev">
                                <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#otdpInboundCarousel" data-bs-slide="next">
                                <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OTDP OUTBOUND -->
            <div class="col-md-6 mb-2">
                <div class="card card-custom">
                    <div class="card-header">
                        <div class="card-title">OTDP Outbound</div>
                    </div>
                    <div class="card-body">
                        <div id="otdpOutboundCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div hidden class="carousel-indicators">
                                @foreach ($otdpOutbound as $cc => $rows)
                                    <button type="button" data-bs-target="#otdpOutboundCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach ($otdpOutbound as $cc => $rows)
                                    @php
                                        $totalPercentage = 0;
                                        $count = 0;
                                        $today = now()->day;
                                        foreach ($rows as $entry) {
                                            if ($entry->day <= $today && $entry->percentage !== null) {
                                                $totalPercentage += $entry->percentage;
                                                $count++;
                                            }
                                        }
                                        $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                                    @endphp
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <table class="indicator-table mb-4">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Signal Indicator</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center py-3">
                                                                <span class="signal green">G</span> <span class="mx-2">≥ 95%</span>
                                                                <span class="signal yellow">Y</span> <span class="mx-2">≥ 85%</span>
                                                                <span class="signal red">R</span> <span class="mx-2">< 85%</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-4">
                                                <table class="indicator-table mb-4">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Average OTDC</th>
                                                            <th class="text-center">Signal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-center py-3">{{ number_format($averagePercentage, 2) }}%</td>
                                                            <td class="text-center py-3">
                                                                <span class="signal {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                                                    {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <p style="margin-top: -20px" class="text-center"><strong>{{ $cc }}</strong></p>
                                        <div style="margin-top: -20px" class="chart-container">
                                            <div id="otdp-outbound-chart-{{ $cc }}" class="chart-custom" style="width: 100%; height: 300px;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#otdpOutboundCarousel" data-bs-slide="prev">
                                <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#otdpOutboundCarousel" data-bs-slide="next">
                                <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @endif
    </div>

    <!-- Modal Detail OTDP (Single modal untuk Inbound & Outbound) -->
    <div class="modal fade" id="otdpDetailModal" tabindex="-1" aria-labelledby="otdpDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otdpDetailModalLabel">Detail OTDP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="modal-content" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Date:</strong> <span id="detail-date"></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Type:</strong> <span id="detail-type" class="text-uppercase badge bg-info"></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Cutting Center/Rack:</strong> <span id="detail-cc" class="badge bg-secondary"></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Plan:</strong> <span id="detail-plan-qty" class="text-primary fw-bold"></span> |
                                <strong>Actual:</strong> <span id="detail-actual-qty" class="text-success fw-bold"></span>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="planning-tab" data-bs-toggle="tab"
                                        data-bs-target="#planning" type="button" role="tab">
                                    Planning Data (<span id="planning-count">0</span>)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="actual-tab" data-bs-toggle="tab"
                                        data-bs-target="#actual" type="button" role="tab">
                                    Actual Data - Orders (<span id="actual-count">0</span>)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="detailTabsContent">
                            <!-- Planning Tab -->
                            <div class="tab-pane fade show active" id="planning" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Plan Date</th>
                                                <th>Cutting Center</th>
                                                <th>Code</th>
                                                <th>Qty</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody id="planning-table-body">
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4" class="text-end">Total:</th>
                                                <th id="planning-total-qty">0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Actual Tab -->
                            <div class="tab-pane fade" id="actual" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Detail ID</th>
                                                <th>Order ID</th>
                                                <th>Ref Number</th>
                                                <th>Code</th>
                                                <th>Serial Number</th>
                                                <th>SKU</th>
                                                <th>Product Name</th>
                                                <th>Rack</th>
                                                <th>Qty</th>
                                                <th>Order Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="actual-table-body">
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="8" class="text-end">Total:</th>
                                                <th id="actual-total-qty">0</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ✅ Declare selectedLocation HANYA SEKALI di awal, SEBELUM semua script -->
<script>
    const selectedLocation = @json($selected);

    // ✅ Global function untuk load modal (OUTSIDE am5.ready)
    function loadDetailModal(type, cuttingCenter, day) {
        const modal = new bootstrap.Modal(document.getElementById('otdpDetailModal'));

        $('#modal-loading').show();
        $('#modal-content').hide();

        modal.show();

        $.ajax({
            url: "{{ route('home.detail') }}",
            type: 'GET',
            data: {
                location_code: selectedLocation.location_code,
                external_id: selectedLocation.external_id,
                type: type,
                day: day,
                cutting_center: cuttingCenter
            },
            success: function(response) {
                $('#modal-loading').hide();
                $('#modal-content').show();

                // Fill summary
                $('#detail-date').text(response.summary.date);
                $('#detail-type').text(response.summary.type);
                $('#detail-cc').text(response.summary.cutting_center);
                $('#detail-plan-qty').text(response.summary.total_plan_qty);
                $('#detail-actual-qty').text(response.summary.total_actual_qty);

                // Update counts
                $('#planning-count').text(response.planning.length);
                $('#actual-count').text(response.actual.length);

                // Fill planning table
                let planningHtml = '';
                let planningTotal = 0;
                if (response.planning.length > 0) {
                    response.planning.forEach(item => {
                        planningTotal += parseInt(item.qty) || 0;
                        planningHtml += `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.plan_date}</td>
                                <td><span class="badge bg-secondary">${item.cutting_center}</span></td>
                                <td>${item.code || '-'}</td>
                                <td class="text-end">${item.qty}</td>
                                <td><span class="badge bg-info">${item.type}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    planningHtml = '<tr><td colspan="6" class="text-center text-muted">No planning data for this date</td></tr>';
                }
                $('#planning-table-body').html(planningHtml);
                $('#planning-total-qty').text(planningTotal);

                // Fill actual table
                let actualHtml = '';
                let actualTotal = 0;
                if (response.actual.length > 0) {
                    response.actual.forEach(item => {
                        actualTotal += parseInt(item.qty) || 0;
                        const statusColor = item.status.toLowerCase() === 'done' ? 'success' : 'warning';
                        actualHtml += `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.order_id}</td>
                                <td>${item.ref_number || '-'}</td>
                                <td>${item.code || '-'}</td>
                                <td>${item.serial_number || '-'}</td>
                                <td><strong>${item.sku}</strong></td>
                                <td>${item.product_name}</td>
                                <td><span class="badge bg-secondary">${item.rack || '-'}</span></td>
                                <td class="text-end">${item.qty}</td>
                                <td>${new Date(item.order_date).toLocaleString('id-ID', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</td>
                                <td><span class="badge bg-${statusColor}">${item.status}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    actualHtml = '<tr><td colspan="11" class="text-center text-muted">No actual data for this date</td></tr>';
                }
                $('#actual-table-body').html(actualHtml);
                $('#actual-total-qty').text(actualTotal);
            },
            error: function(xhr) {
                $('#modal-loading').hide();
                $('#modal-content').show();
                $('#modal-content').html(`
                    <div class="alert alert-danger">
                        <h5>Error loading data</h5>
                        <p>${xhr.responseJSON?.message || xhr.responseText || 'Unknown error'}</p>
                    </div>
                `);
            }
        });
    }
</script>

<!-- ✅ INBOUND CHART SCRIPT -->
<script>
    am5.ready(function() {
        const inboundData = @json($otdpInbound);
        const rootsInbound = {}; // Rename untuk avoid conflict

        function createOTDPInboundChart(ccName, plannedData, actualData, percentageData, endDate) {
            const chartElement = document.getElementById(`otdp-inbound-chart-${ccName}`);
            if (!chartElement) return;

            if (rootsInbound[ccName]) {
                rootsInbound[ccName].dispose();
            }

            var root = am5.Root.new(`otdp-inbound-chart-${ccName}`);
            rootsInbound[ccName] = root;

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                layout: root.verticalLayout
            }));

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "date",
                tooltip: am5.Tooltip.new(root, {}),
                renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 30 })
            }));

            xAxis.data.setAll(Array.from({ length: endDate }, (_, i) => ({ date: (i + 1).toString() })));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
            }));

            var yAxisRight = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                max: 120,
                strictMinMax: true,
                renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
            }));

            yAxis.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Quantity",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            yAxisRight.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Percentage (%)",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            var planSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Planned Qty",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "plan",
                categoryXField: "date",
                clustered: true,
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
            }));

            planSeries.columns.template.setAll({
                fill: am5.color("#36A2EB"),
                width: am5.percent(80),
                cursorOverStyle: "pointer"
            });

            planSeries.columns.template.events.on("click", function(ev) {
                const day = parseInt(ev.target.dataItem.dataContext.date);
                loadDetailModal('inbound', ccName, day);
            });

            planSeries.data.setAll(plannedData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                plan: value || 0
            })));

            var actualSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Actual Qty",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "actual",
                categoryXField: "date",
                clustered: true,
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
            }));

            actualSeries.columns.template.setAll({
                fill: am5.color("#FF9F40"),
                width: am5.percent(80),
                cursorOverStyle: "pointer"
            });

            actualSeries.columns.template.events.on("click", function(ev) {
                const day = parseInt(ev.target.dataItem.dataContext.date);
                loadDetailModal('inbound', ccName, day);
            });

            actualSeries.data.setAll(actualData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                actual: value || 0
            })));

            var percentageSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "OTDP %",
                xAxis: xAxis,
                yAxis: yAxisRight,
                valueYField: "percentage",
                categoryXField: "date",
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}%" }),
                stroke: am5.color(0x000000),
                fill: am5.color(0x000000)
            }));

            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
            percentageSeries.data.setAll(percentageData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                percentage: value || 0
            })));

            percentageSeries.bullets.push(function(root, series, dataItem) {
                var value = dataItem.dataContext.percentage;
                var bulletColor = value < 100 ? am5.color(0xff0000) : am5.color(0x00ff00);
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        strokeWidth: 3,
                        stroke: series.get("stroke"),
                        radius: 5,
                        fill: bulletColor
                    })
                });
            });

            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50
            }));

            legend.data.setAll(chart.series.values);

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none",
                xAxis: xAxis
            }));

            cursor.lineY.set("visible", false);

            chart.appear(1000, 100);
            actualSeries.appear();
            planSeries.appear();
            percentageSeries.appear();
        }

        function renderActiveChart() {
            const activeSlide = document.querySelector('#otdpInboundCarousel .carousel-item.active');
            if (!activeSlide) return;

            const chartDiv = activeSlide.querySelector('[id^="otdp-inbound-chart-"]');
            if (!chartDiv) return;

            const ccName = chartDiv.id.replace('otdp-inbound-chart-', '');

            if (rootsInbound[ccName]) return;

            const data = inboundData[ccName];
            if (!data) return;

            const plannedData = Array(31).fill(0);
            const actualData = Array(31).fill(0);
            const percentageData = Array(31).fill(0);

            data.forEach(entry => {
                const day = entry.day - 1;
                if (day >= 0 && day < 31) {
                    plannedData[day] = parseInt(entry.plan_qty, 10) || 0;
                    actualData[day] = parseInt(entry.act_qty, 10) || 0;
                    percentageData[day] = parseFloat(entry.percentage) || 0;
                }
            });

            createOTDPInboundChart(ccName, plannedData, actualData, percentageData, 31);
        }

        setTimeout(renderActiveChart, 300);

        const carousel = document.getElementById('otdpInboundCarousel');
        if (carousel) {
            carousel.addEventListener('slid.bs.carousel', renderActiveChart);
        }
    });
</script>

<!-- ✅ OUTBOUND CHART SCRIPT -->
<script>
    am5.ready(function() {
        const outboundData = @json($otdpOutbound);
        const rootsOutbound = {};

        function createOTDPOutboundChart(ccName, plannedData, actualData, percentageData, endDate) {
            const chartElement = document.getElementById(`otdp-outbound-chart-${ccName}`);
            if (!chartElement) return;

            if (rootsOutbound[ccName]) {
                rootsOutbound[ccName].dispose();
            }

            var root = am5.Root.new(`otdp-outbound-chart-${ccName}`);
            rootsOutbound[ccName] = root;

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                layout: root.verticalLayout
            }));

            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                categoryField: "date",
                tooltip: am5.Tooltip.new(root, {}),
                renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 30 })
            }));

            xAxis.data.setAll(Array.from({ length: endDate }, (_, i) => ({ date: (i + 1).toString() })));

            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
            }));

            var yAxisRight = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                max: 120,
                strictMinMax: true,
                renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
            }));

            yAxis.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Quantity",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            yAxisRight.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Percentage (%)",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            var planSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Planned Qty",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "plan",
                categoryXField: "date",
                clustered: true,
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
            }));

            planSeries.columns.template.setAll({
                fill: am5.color("#36A2EB"),
                width: am5.percent(80),
                cursorOverStyle: "pointer"
            });

            planSeries.columns.template.events.on("click", function(ev) {
                const day = parseInt(ev.target.dataItem.dataContext.date);
                loadDetailModal('outbound', ccName, day);
            });

            planSeries.data.setAll(plannedData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                plan: value || 0
            })));

            var actualSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                name: "Actual Qty",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "actual",
                categoryXField: "date",
                clustered: true,
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
            }));

            actualSeries.columns.template.setAll({
                fill: am5.color("#FF9F40"),
                width: am5.percent(80),
                cursorOverStyle: "pointer"
            });

            actualSeries.columns.template.events.on("click", function(ev) {
                const day = parseInt(ev.target.dataItem.dataContext.date);
                loadDetailModal('outbound', ccName, day);
            });

            actualSeries.data.setAll(actualData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                actual: value || 0
            })));

            var percentageSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "OTDP %",
                xAxis: xAxis,
                yAxis: yAxisRight,
                valueYField: "percentage",
                categoryXField: "date",
                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}%" }),
                stroke: am5.color(0x000000),
                fill: am5.color(0x000000)
            }));

            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
            percentageSeries.data.setAll(percentageData.slice(0, endDate).map((value, i) => ({
                date: (i + 1).toString(),
                percentage: value || 0
            })));

            percentageSeries.bullets.push(function(root, series, dataItem) {
                var value = dataItem.dataContext.percentage;
                var bulletColor = value < 100 ? am5.color(0xff0000) : am5.color(0x00ff00);
                return am5.Bullet.new(root, {
                    sprite: am5.Circle.new(root, {
                        strokeWidth: 3,
                        stroke: series.get("stroke"),
                        radius: 5,
                        fill: bulletColor
                    })
                });
            });

            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50
            }));

            legend.data.setAll(chart.series.values);

            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none",
                xAxis: xAxis
            }));

            cursor.lineY.set("visible", false);

            chart.appear(1000, 100);
            actualSeries.appear();
            planSeries.appear();
            percentageSeries.appear();
        }

        function renderActiveChart() {
            const activeSlide = document.querySelector('#otdpOutboundCarousel .carousel-item.active');
            if (!activeSlide) return;

            const chartDiv = activeSlide.querySelector('[id^="otdp-outbound-chart-"]');
            if (!chartDiv) return;

            const ccName = chartDiv.id.replace('otdp-outbound-chart-', '');

            if (rootsOutbound[ccName]) return;

            const data = outboundData[ccName];
            if (!data) return;

            const plannedData = Array(31).fill(0);
            const actualData = Array(31).fill(0);
            const percentageData = Array(31).fill(0);

            data.forEach(entry => {
                const day = entry.day - 1;
                if (day >= 0 && day < 31) {
                    plannedData[day] = parseInt(entry.plan_qty, 10) || 0;
                    actualData[day] = parseInt(entry.act_qty, 10) || 0;
                    percentageData[day] = parseFloat(entry.percentage) || 0;
                }
            });

            createOTDPOutboundChart(ccName, plannedData, actualData, percentageData, 31);
        }

        setTimeout(renderActiveChart, 300);

        const carousel = document.getElementById('otdpOutboundCarousel');
        if (carousel) {
            carousel.addEventListener('slid.bs.carousel', renderActiveChart);
        }
    });
</script>
@endsection
