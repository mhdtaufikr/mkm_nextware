<!-- Stock Strength Dashboard -->
<div class="col-md-12 mb-4">
    <div class="card card-custom">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i data-feather="alert-triangle" class="me-2"></i>
                        Stock Strength Analysis - Outbound Planning Tomorrow ({{ \Carbon\Carbon::tomorrow()->format('d M Y') }})
                    </h5>
                    <small class="text-muted">Simple comparison: Current Stock (qty) vs Planning Outbound Tomorrow. Click bar for detail.</small>
                </div>
                <div>
                    <span class="badge bg-danger me-1">CRITICAL: {{ $stockStrength->where('status', 'CRITICAL')->count() }}</span>
                    <span class="badge bg-warning me-1">EXACT: {{ $stockStrength->where('status', 'EXACT')->count() }}</span>
                    <span class="badge bg-info me-1">LOW: {{ $stockStrength->where('status', 'LOW')->count() }}</span>
                    <span class="badge bg-success">SAFE: {{ $stockStrength->where('status', 'SAFE')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($stockStrength->isEmpty())
                <div class="alert alert-info text-center">
                    <i data-feather="info" class="me-2"></i>
                    No outbound planning data for tomorrow
                </div>
            @else
                <!-- Chart Container -->
                <div id="stockStrengthChart" style="width: 100%; height: 400px;"></div>

                <!-- ✅ Toggle Button untuk Show/Hide Table -->
                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <h6 class="mb-0">Detailed Data Table</h6>
                    <button class="btn btn-outline-primary btn-sm" type="button" id="toggleTableBtn" data-bs-toggle="collapse" data-bs-target="#stockTableCollapse" aria-expanded="false" aria-controls="stockTableCollapse">
                        <i data-feather="chevron-down" id="toggleIcon" style="width: 16px; height: 16px;"></i>
                        <span id="toggleText">Show Table</span>
                    </button>
                </div>

                <!-- ✅ Collapsible Table Container -->
                <div class="collapse" id="stockTableCollapse">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="stockStrengthTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Product Name</th>
                                    <th>Cutting Center</th>
                                    <th class="text-end">Current Stock (qty)</th>
                                    <th class="text-end">Planned Qty (OUT)</th>
                                    <th class="text-end">Difference</th>
                                    <th>Rack Type</th>
                                    <th>Stock Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockStrength as $item)
                                    <tr class="table-{{ $item->status_color }}" data-stock='@json($item)'>
                                        <td>
                                            <span class="badge bg-{{ $item->status_color }}">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $item->code }}</strong></td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->cutting_center }}</span>
                                        </td>
                                        <td class="text-end"><strong>{{ number_format($item->current_stock) }}</strong></td>
                                        <td class="text-end">{{ number_format($item->planned_qty) }}</td>
                                        <td class="text-end">
                                            <strong class="{{ $item->difference < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ $item->difference >= 0 ? '+' : '' }}{{ number_format($item->difference) }}
                                            </strong>
                                        </td>
                                        <td>{{ $item->rack_type }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->stock_status }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary view-detail-btn"
                                                    data-code="{{ $item->code }}"
                                                    title="View Detail">
                                                <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-end">{{ number_format($stockStrength->sum('current_stock')) }}</th>
                                    <th class="text-end">{{ number_format($stockStrength->sum('planned_qty')) }}</th>
                                    <th class="text-end">
                                        <strong class="{{ $stockStrength->sum('difference') < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $stockStrength->sum('difference') >= 0 ? '+' : '' }}{{ number_format($stockStrength->sum('difference')) }}
                                        </strong>
                                    </th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Legend -->
                <div class="alert alert-light mt-3">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Formula:</strong> <code>Difference = Current Stock (qty) - Planned Qty (OUT)</code>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-danger">🔴 CRITICAL:</strong> Stock kurang (difference < 0)
                        </div>
                        <div class="col-md-3">
                            <strong class="text-warning">🟡 EXACT:</strong> Stock pas (difference = 0)
                        </div>
                        <div class="col-md-3">
                            <strong class="text-info">🔵 LOW:</strong> Stock rendah (0 < diff ≤ 20%)
                        </div>
                        <div class="col-md-3">
                            <strong class="text-success">🟢 SAFE:</strong> Stock aman (diff > 20%)
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Stock Detail (keep existing modal) -->
<div class="modal fade" id="stockDetailModal" tabindex="-1" aria-labelledby="stockDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockDetailModalLabel">Stock Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Summary Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-dark">Product Information</h6>
                                <p class="mb-1"><strong>Code:</strong> <span id="detail-code"></span></p>
                                <p class="mb-1"><strong>Name:</strong> <span id="detail-name"></span></p>
                                <p class="mb-1"><strong>Cutting Center:</strong> <span id="detail-cutting-center" class="badge bg-secondary"></span></p>
                                <p class="mb-0"><strong>Rack Type:</strong> <span id="detail-rack-type"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" id="detail-status-card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-dark">Stock Status</h6>
                                <p class="mb-1">
                                    <strong>Status:</strong>
                                    <span id="detail-status-badge" class="badge"></span>
                                </p>
                                <p class="mb-1">
                                    <strong>Current Stock:</strong>
                                    <span id="detail-current-stock" class="fs-4 fw-bold"></span>
                                </p>
                                <p class="mb-1">
                                    <strong>Planned Qty:</strong>
                                    <span id="detail-planned-qty" class="fs-4 fw-bold"></span>
                                </p>
                                <p class="mb-0">
                                    <strong>Difference:</strong>
                                    <span id="detail-difference" class="fs-4 fw-bold"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Comparison -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="mb-3">Visual Comparison</h6>
                        <div class="progress" style="height: 40px;">
                            <div id="stock-progress" class="progress-bar" role="progressbar" style="width: 50%">
                                <span id="stock-progress-text" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Current Stock</small>
                            <small class="text-muted">Planned Qty</small>
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

<!-- Chart Script with Click Event -->
<script>
    am5.ready(function() {
        const stockData = @json($stockStrength);

        if (stockData.length === 0) return;

        var root = am5.Root.new("stockStrengthChart");
        root.setThemes([am5themes_Animated.new(root)]);

        var chart = root.container.children.push(am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            wheelX: "panX",
            wheelY: "zoomX",
            layout: root.verticalLayout
        }));

        // Create axes
        var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
            categoryField: "code",
            renderer: am5xy.AxisRendererX.new(root, {
                minGridDistance: 30
            }),
            tooltip: am5.Tooltip.new(root, {})
        }));

        xAxis.get("renderer").labels.template.setAll({
            rotation: -45,
            centerY: am5.p50,
            centerX: am5.p100,
            paddingRight: 15,
            fontSize: 10
        });

        var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {})
        }));

        yAxis.children.unshift(am5.Label.new(root, {
            rotation: -90,
            text: "Quantity",
            y: am5.p50,
            centerX: am5.p50
        }));

        // ✅ Series 1: Current Stock (qty) with Click Event
        var stockSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: "Current Stock (qty)",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "current_stock",
            categoryXField: "code",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{name}: {valueY}\n{name}"
            })
        }));

        stockSeries.columns.template.setAll({
            fill: am5.color("#36A2EB"),
            strokeOpacity: 0,
            width: am5.percent(60),
            cursorOverStyle: "pointer"
        });

        stockSeries.columns.template.events.on("click", function(ev) {
            const dataItem = ev.target.dataItem.dataContext;
            showStockDetail(dataItem);
        });

        // ✅ Series 2: Planned Qty with Click Event
        var planSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
            name: "Planned Qty (OUT)",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "planned_qty",
            categoryXField: "code",
            tooltip: am5.Tooltip.new(root, {
                labelText: "{name}: {valueY}"
            })
        }));

        planSeries.columns.template.setAll({
            fill: am5.color("#FF6384"),
            strokeOpacity: 0,
            width: am5.percent(60),
            cursorOverStyle: "pointer"
        });

        planSeries.columns.template.events.on("click", function(ev) {
            const dataItem = ev.target.dataItem.dataContext;
            showStockDetail(dataItem);
        });

        // ✅ Series 3: Difference Line
        var differenceSeries = chart.series.push(am5xy.LineSeries.new(root, {
            name: "Difference",
            xAxis: xAxis,
            yAxis: yAxis,
            valueYField: "difference",
            categoryXField: "code",
            stroke: am5.color("#000000"),
            tooltip: am5.Tooltip.new(root, {
                labelText: "Difference: {valueY}"
            })
        }));

        differenceSeries.strokes.template.setAll({
            strokeWidth: 2
        });

        differenceSeries.bullets.push(function(root, series, dataItem) {
            var value = dataItem.dataContext.difference;
            var color = value < 0 ? am5.color(0xff0000) : am5.color(0x00ff00);

            var bullet = am5.Bullet.new(root, {
                sprite: am5.Circle.new(root, {
                    radius: 6,
                    fill: color,
                    stroke: am5.color(0xffffff),
                    strokeWidth: 2,
                    cursorOverStyle: "pointer"
                })
            });

            bullet.events.on("click", function(ev) {
                showStockDetail(dataItem.dataContext);
            });

            return bullet;
        });

        // Set data
        var chartData = stockData.map(item => ({
            code: item.code,
            name: item.name,
            cutting_center: item.cutting_center,
            rack_type: item.rack_type,
            stock_status: item.stock_status,
            current_stock: item.current_stock,
            planned_qty: item.planned_qty,
            difference: item.difference,
            status: item.status,
            status_color: item.status_color
        }));

        xAxis.data.setAll(chartData);
        stockSeries.data.setAll(chartData);
        planSeries.data.setAll(chartData);
        differenceSeries.data.setAll(chartData);

        var legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50
        }));

        legend.data.setAll(chart.series.values);

        var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
            behavior: "zoomX"
        }));
        cursor.lineY.set("visible", false);

        chart.appear(1000, 100);
    });

    // ✅ Function to show stock detail in modal
    function showStockDetail(data) {
        $('#detail-code').text(data.code);
        $('#detail-name').text(data.name);
        $('#detail-cutting-center').text(data.cutting_center);
        $('#detail-rack-type').text(data.rack_type || '-');

        const statusBadge = $('#detail-status-badge');
        statusBadge.removeClass().addClass('badge bg-' + data.status_color);
        statusBadge.text(data.status);

        $('#detail-current-stock').text(data.current_stock.toLocaleString());
        $('#detail-planned-qty').text(data.planned_qty.toLocaleString());

        const diffElement = $('#detail-difference');
        diffElement.text((data.difference >= 0 ? '+' : '') + data.difference.toLocaleString());
        diffElement.removeClass().addClass('fs-4 fw-bold ' + (data.difference < 0 ? 'text-danger' : 'text-success'));

        const statusCard = $('#detail-status-card');
        statusCard.removeClass().addClass('card bg-' + data.status_color + ' bg-opacity-25');

        const percentage = data.planned_qty > 0 ? (data.current_stock / data.planned_qty * 100) : 100;
        const progressBar = $('#stock-progress');
        const progressText = $('#stock-progress-text');

        if (percentage >= 100) {
            progressBar.removeClass().addClass('progress-bar bg-success');
            progressText.text('Stock Sufficient (' + percentage.toFixed(1) + '%)');
        } else if (percentage >= 80) {
            progressBar.removeClass().addClass('progress-bar bg-warning');
            progressText.text('Stock Low (' + percentage.toFixed(1) + '%)');
        } else {
            progressBar.removeClass().addClass('progress-bar bg-danger');
            progressText.text('Stock Critical (' + percentage.toFixed(1) + '%)');
        }

        progressBar.css('width', Math.min(percentage, 100) + '%');

        const modal = new bootstrap.Modal(document.getElementById('stockDetailModal'));
        modal.show();
    }

    // ✅ Document Ready
    $(document).ready(function() {
        // ✅ Toggle button icon & text animation
        $('#stockTableCollapse').on('show.bs.collapse', function () {
            $('#toggleText').text('Hide Table');
            $('#toggleIcon').attr('data-feather', 'chevron-up');
            feather.replace();
        });

        $('#stockTableCollapse').on('hide.bs.collapse', function () {
            $('#toggleText').text('Show Table');
            $('#toggleIcon').attr('data-feather', 'chevron-down');
            feather.replace();
        });

        // Click event for table action buttons
        $(document).on('click', '.view-detail-btn', function() {
            const row = $(this).closest('tr');
            const stockData = row.data('stock');
            showStockDetail(stockData);
        });

        // Initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });
</script>

<style>
    .table-danger {
        background-color: #f8d7da !important;
    }
    .table-warning {
        background-color: #fff3cd !important;
    }
    .table-info {
        background-color: #cff4fc !important;
    }
    .table-success {
        background-color: #d1e7dd !important;
    }

    .bg-opacity-25 {
        --bs-bg-opacity: 0.25;
    }

    .view-detail-btn {
        padding: 0.25rem 0.5rem;
    }

    /* ✅ Smooth animation untuk collapse */
    .collapse {
        transition: height 0.35s ease;
    }

    .collapsing {
        transition: height 0.35s ease;
    }
</style>
