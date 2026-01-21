<!-- Stock Strength Dashboard -->
<div class="col-md-12 mb-2"> <!-- ✅ mb-4 → mb-2 -->
    <div class="card card-custom">
        <div class="card-header py-2"> <!-- ✅ Tambah py-2 untuk compact header -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0"> <!-- ✅ h5 → h6 -->
                        <i data-feather="alert-triangle" class="me-2" style="width: 16px; height: 16px;"></i>
                        Stock Strength Analysis - Tomorrow ({{ \Carbon\Carbon::tomorrow()->format('d M Y') }})
                    </h6>
                    <small class="text-muted" style="font-size: 0.75rem;">Stock (qty) vs Planning Outbound Tomorrow. Click bar for detail.</small>
                </div>
                <div>
                    <span class="badge bg-danger me-1" style="font-size: 0.7rem;">CRITICAL: {{ $stockStrength->where('status', 'CRITICAL')->count() }}</span>
                    <span class="badge bg-warning me-1" style="font-size: 0.7rem;">EXACT: {{ $stockStrength->where('status', 'EXACT')->count() }}</span>
                    <span class="badge bg-info me-1" style="font-size: 0.7rem;">LOW: {{ $stockStrength->where('status', 'LOW')->count() }}</span>
                    <span class="badge bg-success" style="font-size: 0.7rem;">SAFE: {{ $stockStrength->where('status', 'SAFE')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="card-body py-2"> <!-- ✅ py-2 untuk compact body -->
            @if($stockStrength->isEmpty())
                <div class="alert alert-info text-center mb-0">
                    <i data-feather="info" class="me-2"></i>
                    No outbound planning data for tomorrow
                </div>
            @else
                <!-- ✅ Chart Container - Height dikurangi dari 400px → 280px -->
                <div id="stockStrengthChart" style="width: 100%; height: 280px;"></div>

                <!-- ✅ Toggle Button untuk Show/Hide Table -->
                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                    <h6 class="mb-0" style="font-size: 0.9rem;">Detailed Data Table</h6>
                    <button class="btn btn-outline-primary btn-sm" type="button" id="toggleTableBtn" data-bs-toggle="collapse" data-bs-target="#stockTableCollapse" aria-expanded="false" aria-controls="stockTableCollapse" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                        <i data-feather="chevron-down" id="toggleIcon" style="width: 14px; height: 14px;"></i>
                        <span id="toggleText">Show Table</span>
                    </button>
                </div>

                <!-- Collapsible Table Container -->
                <div class="collapse" id="stockTableCollapse">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="stockStrengthTable" style="font-size: 0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Product Name</th>
                                    <th>Cutting Center</th>
                                    <th class="text-end">Current Stock</th>
                                    <th class="text-end">Planned Qty</th>
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
                                            <span class="badge bg-{{ $item->status_color }}" style="font-size: 0.7rem;">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $item->code }}</strong></td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $item->cutting_center }}</span>
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
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ $item->stock_status }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary view-detail-btn"
                                                    data-code="{{ $item->code }}"
                                                    title="View Detail"
                                                    style="padding: 0.15rem 0.4rem;">
                                                <i data-feather="eye" style="width: 12px; height: 12px;"></i>
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

                <!-- ✅ Legend - Compact -->
                <div class="alert alert-light mt-2 mb-0 py-2" style="font-size: 0.75rem;">
                    <div class="row">
                        <div class="col-md-12 mb-1">
                            <strong>Formula:</strong> <code>Difference = Current Stock (qty) - Planned Qty (OUT)</code>
                        </div>
                        <div class="col-md-3">
                            <strong class="text-danger">🔴 CRITICAL:</strong> Stock kurang
                        </div>
                        <div class="col-md-3">
                            <strong class="text-warning">🟡 EXACT:</strong> Stock pas
                        </div>
                        <div class="col-md-3">
                            <strong class="text-info">🔵 LOW:</strong> Stock rendah
                        </div>
                        <div class="col-md-3">
                            <strong class="text-success">🟢 SAFE:</strong> Stock aman
                        </div>
                    </div>
                </div>
            @endif
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

