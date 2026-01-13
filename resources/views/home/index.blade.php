{{-- resources/views/home/index.blade.php --}}
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
                        <div class="page-header-subtitle">Common dashboard berdasarkan Active Location</div>
                    </div>

                    <div class="col-auto mt-4">
                        <form method="GET" action="{{ route('home') }}" class="d-flex gap-2 align-items-center">
                            <label class="text-white-50 small mb-0">Location</label>
                            <select name="location_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}"
                                        @if($selected && (int)$selected->id === (int)$loc->id) selected @endif>
                                        {{ $loc->display_name ?? $loc->location_code }}
                                        @if((int)($loc->is_default ?? 0) === 1) (default) @endif
                                    </option>
                                @endforeach
                            </select>
                            <noscript>
                                <button class="btn btn-light btn-sm" type="submit">Apply</button>
                            </noscript>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n10">
        @include('partials.alert')

        @if(!$selected)
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Tidak ada location active.</div>
                </div>
            </div>
        @else

        {{-- SUMMARY CARDS --}}
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Selected Location</div>
                        <div class="h5 mb-0">{{ $selected->display_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $selected->location_code ?? '' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total SKU</div>
                        <div class="h3 mb-0">{{ $stats->total_sku ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Qty Available</div>
                        <div class="h3 mb-0">{{ $stats->qty_available ?? 0 }}</div>
                        <div class="text-muted small">Qty: {{ $stats->qty ?? 0 }} | Goods: {{ $stats->qty_goods ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Incoming / Outgoing</div>
                        <div class="h4 mb-0">
                            {{ $stats->qty_incoming ?? 0 }} / {{ $stats->qty_outgoing ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Qty by Cutting Center</h3>
                    </div>
                    <div class="card-body">
                        @if($byCuttingCenter->isEmpty())
                            <div class="text-muted">No data cutting_center untuk location ini.</div>
                        @else
                            <div id="chartCuttingCenter" style="width:100%; height:360px;"></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="card card-custom">
                    <div class="card-header">
                        <h4 class="card-title">Preventive Maintenance Plan vs Actual (Daily)</h4>
                    </div>
                    <div class="card-body">

                        <div id="pmDailyCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div hidden class="carousel-indicators">
                                @foreach ($otdcDailyByCC as $cc => $rows)
                                    <button type="button"
                                        data-bs-target="#pmDailyCarousel"
                                        data-bs-slide-to="{{ $loop->index }}"
                                        class="{{ $loop->first ? 'active' : '' }}">
                                    </button>
                                @endforeach
                            </div>

                            <div class="carousel-inner">
                                @foreach ($otdcDailyByCC as $cc => $rows)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <p class="text-center fw-bold mb-2">{{ $cc }}</p>

                                        <div class="chart-container">
                                            <div id="pm-chart-{{ \Illuminate\Support\Str::slug($cc, '_') }}"
                                                 class="chart-custom"style="width:100%; height:360px;">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button class="carousel-control-prev" type="button"
                                data-bs-target="#pmDailyCarousel" data-bs-slide="prev">
                                <span hidden class="carousel-control-prev-icon"></span>
                            </button>

                            <button class="carousel-control-next" type="button"
                                data-bs-target="#pmDailyCarousel" data-bs-slide="next">
                                <span hidden class="carousel-control-next-icon"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            <script>
                am5.ready(function () {

                    // ===============================
                    // HELPER: GENERATE DAY 1–31
                    // ===============================
                    function generateMonthDays() {
                        return Array.from({ length: 31 }, (_, i) => (i + 1).toString());
                    }

                    // ===============================
                    // CREATE CHART (SAMA POLA OTDC)
                    // ===============================
                    function createPMDailyChart(ccSlug, days, plannedData, actualData, percentageData) {

                        var root = am5.Root.new(`pm-chart-${ccSlug}`);
                        root.setThemes([am5themes_Animated.new(root)]);

                        var chart = root.container.children.push(
                            am5xy.XYChart.new(root, {
                                panX: false,
                                panY: false,
                                wheelX: "none",
                                wheelY: "none",
                                layout: root.verticalLayout
                            })
                        );

                        // ===== X AXIS (DAY 1–31) =====
                        var xAxis = chart.xAxes.push(
                            am5xy.CategoryAxis.new(root, {
                                categoryField: "date",
                                renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 25 }),
                                tooltip: am5.Tooltip.new(root, {})
                            })
                        );

                        xAxis.data.setAll(days.map(d => ({ date: d })));

                        // ===== SAFE Y AXIS =====
                        var maxPlanned = Math.max(...plannedData);
                        var maxActual  = Math.max(...actualData);

                        var yAxis = chart.yAxes.push(
                            am5xy.ValueAxis.new(root, {
                                min: 0,
                                max: (maxPlanned === 0 && maxActual > 0) ? maxActual : undefined,
                                strictMinMax: maxPlanned === 0 && maxActual > 0,
                                renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
                            })
                        );

                        var yAxisRight = chart.yAxes.push(
                            am5xy.ValueAxis.new(root, {
                                min: 0,
                                max: 120,
                                strictMinMax: true,
                                renderer: am5xy.AxisRendererY.new(root, {
                                    opposite: true,
                                    strokeOpacity: 0.1
                                })
                            })
                        );

                        // ===== PLANNED (OPTIONAL) =====
                        if (maxPlanned > 0) {
                            var planSeries = chart.series.push(
                                am5xy.ColumnSeries.new(root, {
                                    name: "Planned",
                                    xAxis: xAxis,
                                    yAxis: yAxis,
                                    valueYField: "planned",
                                    categoryXField: "date",
                                    tooltip: am5.Tooltip.new(root, {
                                        labelText: "{name}: {valueY}"
                                    })
                                })
                            );

                            planSeries.columns.template.setAll({
                                fill: am5.color("#36A2EB"),
                                width: am5.percent(80)
                            });

                            planSeries.data.setAll(
                                days.map((d, i) => ({
                                    date: d,
                                    planned: plannedData[i] || 0
                                }))
                            );
                        }

                        // ===== ACTUAL =====
                        var actualSeries = chart.series.push(
                            am5xy.ColumnSeries.new(root, {
                                name: "Actual",
                                xAxis: xAxis,
                                yAxis: yAxis,
                                valueYField: "actual",
                                categoryXField: "date",
                                tooltip: am5.Tooltip.new(root, {
                                    labelText: "{name}: {valueY}"
                                })
                            })
                        );

                        actualSeries.columns.template.setAll({
                            fill: am5.color("#FF9F40"),
                            width: am5.percent(80)
                        });

                        actualSeries.data.setAll(
                            days.map((d, i) => ({
                                date: d,
                                actual: actualData[i] || 0
                            }))
                        );

                        // ===== PERCENTAGE =====
                        var percentageSeries = chart.series.push(
                            am5xy.LineSeries.new(root, {
                                name: "Percentage Accuracy",
                                xAxis: xAxis,
                                yAxis: yAxisRight,
                                valueYField: "percentage",
                                categoryXField: "date",
                                stroke: am5.color(0x000000),
                                tooltip: am5.Tooltip.new(root, {
                                    labelText: "{valueY}%"
                                })
                            })
                        );

                        percentageSeries.strokes.template.setAll({ strokeWidth: 3 });

                        percentageSeries.data.setAll(
                            days.map((d, i) => ({
                                date: d,
                                percentage: percentageData[i] || 0
                            }))
                        );

                        percentageSeries.bullets.push(function (root, series, dataItem) {
                            var value = dataItem.dataContext.percentage;
                            return am5.Bullet.new(root, {
                                sprite: am5.Circle.new(root, {
                                    radius: 5,
                                    fill: value >= 100
                                        ? am5.color(0x00ff00)
                                        : am5.color(0xff0000),
                                    stroke: series.get("stroke"),
                                    strokeWidth: 3
                                })
                            });
                        });

                        // ===== LEGEND & CURSOR =====
                        var legend = chart.children.push(
                            am5.Legend.new(root, {
                                centerX: am5.p50,
                                x: am5.p50
                            })
                        );
                        legend.data.setAll(chart.series.values);

                        chart.set("cursor",
                            am5xy.XYCursor.new(root, {
                                behavior: "none",
                                xAxis: xAxis
                            })
                        );

                        chart.appear(1000, 100);
                    }

                    // ===============================
                    // DATA FROM BACKEND
                    // ===============================
                    const otdcDailyByCC = @json($otdcDailyByCC);

                    const days = generateMonthDays();

                    Object.keys(otdcDailyByCC).forEach(cc => {

                        const rows = otdcDailyByCC[cc];
                        const slug = cc.toLowerCase().replace(/[^a-z0-9]/g, "_");

                        // MAP BACKEND DATA → DAY INDEX
                        const plannedMap = {};
                        const actualMap  = {};

                        rows.dates.forEach((dateStr, i) => {
                            const day = new Date(dateStr).getDate(); // 1–31
                            plannedMap[day] = rows.planned[i] || 0;
                            actualMap[day]  = rows.actual[i]  || 0;
                        });

                        const planned = days.map(d => plannedMap[d] ?? 0);
                        const actual  = days.map(d => actualMap[d]  ?? 0);

                        const percentage = planned.map((p, i) =>
                            p > 0 ? Math.min((actual[i] / p) * 100, 100) : 0
                        );

                        createPMDailyChart(slug, days, planned, actual, percentage);
                    });

                });
            </script>
            {{-- <div class="col-lg-6 mb-2">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Stock Status</h3>
                    </div>
                    <div class="card-body">
                        @if($byStatus->isEmpty())
                            <div class="text-muted">No data.</div>
                        @else
                            <div id="chartStockStatus" style="width:100%; height:360px;"></div>
                        @endif
                    </div>
                </div>
            </div> --}}
        </div>





        {{-- TOP ITEMS (tetap table) --}}
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Top Items (by Qty Available)</h3>
                    </div>
                    <div class="card-body">
                        @if($topItems->isEmpty())
                            <div class="text-muted">No data.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered w-100" id="tableTopItems">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th class="text-end">Qty Available</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Goods</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topItems as $it)
                                            <tr>
                                                <td>{{ $it->code }}</td>
                                                <td class="text-break">{{ $it->name }}</td>
                                                <td>{{ $it->stock_status ?? '-' }}</td>
                                                <td class="text-end">{{ $it->qty_available ?? 0 }}</td>
                                                <td class="text-end">{{ $it->qty ?? 0 }}</td>
                                                <td class="text-end">{{ $it->qty_goods ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @endif
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // DataTables (Top Items only)
    if (window.$ && $.fn.DataTable) {
        $("#tableTopItems").DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            searching: false,
            paging: false,
            info: false
        });
    }

    // ======== DATA FROM BACKEND ========
    // byCuttingCenter: [{cutting_center, total_rows, total_qty}, ...]
    const cuttingCenterRows = @json($byCuttingCenter ?? []);
    // byStatus: [{stock_status, total_rows}, ...]
    const stockStatusRows = @json($byStatus ?? []);

    // ======== CHART: Cutting Center (Column) ========
    if (document.getElementById("chartCuttingCenter") && cuttingCenterRows.length) {
        const root = am5.Root.new("chartCuttingCenter");

        root.setThemes([
            am5themes_Animated.new(root)
        ]);

        const chart = root.container.children.push(
            am5xy.XYChart.new(root, {
                panX: true,
                panY: false,
                wheelX: "panX",
                wheelY: "zoomX",
                layout: root.verticalLayout
            })
        );

        const data = cuttingCenterRows.map(r => ({
            category: r.cutting_center ?? "(empty)",
            value: Number(r.total_qty ?? 0)
        }));

        const xAxis = chart.xAxes.push(
            am5xy.CategoryAxis.new(root, {
                categoryField: "category",
                renderer: am5xy.AxisRendererX.new(root, {
                    minGridDistance: 20
                }),
                tooltip: am5.Tooltip.new(root, {})
            })
        );
        xAxis.data.setAll(data);

        const yAxis = chart.yAxes.push(
            am5xy.ValueAxis.new(root, {
                renderer: am5xy.AxisRendererY.new(root, {})
            })
        );

        const series = chart.series.push(
            am5xy.ColumnSeries.new(root, {
                name: "Total Qty",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "value",
                categoryXField: "category",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{category}: {valueY}"
                })
            })
        );

        series.data.setAll(data);

        // legend optional
        const legend = chart.children.push(am5.Legend.new(root, {}));
        legend.data.setAll(chart.series.values);

        chart.set("cursor", am5xy.XYCursor.new(root, {}));

        series.appear(1000);
        chart.appear(1000, 100);
    }

    // ======== CHART: Stock Status (Pie) ========
    if (document.getElementById("chartStockStatus") && stockStatusRows.length) {
        const root2 = am5.Root.new("chartStockStatus");

        root2.setThemes([
            am5themes_Animated.new(root2)
        ]);

        const chart2 = root2.container.children.push(
            am5percent.PieChart.new(root2, {
                layout: root2.verticalLayout
            })
        );

        const data2 = stockStatusRows.map(r => ({
            category: r.stock_status ?? "(empty)",
            value: Number(r.total_rows ?? 0)
        }));

        const series2 = chart2.series.push(
            am5percent.PieSeries.new(root2, {
                name: "Stock Status",
                valueField: "value",
                categoryField: "category",
                tooltip: am5.Tooltip.new(root2, {
                    labelText: "{category}: {value}"
                })
            })
        );

        series2.data.setAll(data2);

        // legend
        const legend2 = chart2.children.push(am5.Legend.new(root2, {
            centerX: am5.percent(50),
            x: am5.percent(50),
            marginTop: 10
        }));
        legend2.data.setAll(series2.dataItems);

        series2.appear(1000, 100);
        chart2.appear(1000, 100);
    }
});
</script>
@endsection
