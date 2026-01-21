   <!-- OTDP OUTBOUND -->
   <div class="col-md-6 mb-2">
    <div class="card card-custom">
        <div class="card-header py-2">
            <div class="card-title">OTDP Outbound</div>
        </div>
        <div class="card-body py-2">
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
                                // ✅ Kalau > 100%, ambil 100 aja
                                $percentage = min($entry->percentage, 100);
                                $totalPercentage += $percentage;
                                $count++;
                            }
                        }
                        
                        $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                    @endphp
                
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <table class="indicator-table mb-2" style="font-size: 0.75rem;">
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

                            <p style="margin-top: -10px; font-size: 0.9rem;" class="text-center"><strong>{{ $cc }}</strong></p>
                            <!-- ✅ Chart height dikurangi: 300px → 220px -->
                            <div style="margin-top: -10px" class="chart-container">
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

            // ✅ Y-axis kanan: max 100% untuk percentage
            var yAxisRight = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                min: 0,
                max: 120, // ✅ Ubah dari 120 → 100
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

            // Planned Series
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

            // Actual Series
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

           // ✅ Percentage Series dengan Tooltip di-cap 100%
            var percentageSeries = chart.series.push(am5xy.LineSeries.new(root, {
                name: "OTDP %",
                xAxis: xAxis,
                yAxis: yAxisRight,
                valueYField: "percentage_display",
                categoryXField: "date",
                tooltip: am5.Tooltip.new(root, {
                    labelText: "{name}: {percentage_capped}%" // ✅ Tampilkan nilai yang sudah di-cap
                }),
                stroke: am5.color(0x000000),
                fill: am5.color(0x000000)
            }));

            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });

            // ✅ Data dengan percentage di-cap untuk display DAN tooltip
            percentageSeries.data.setAll(percentageData.slice(0, endDate).map((value, i) => {
                const actualValue = value || 0;
                const displayValue = Math.min(actualValue, 100); // ✅ Cap di 100%
                const cappedValue = Math.min(actualValue, 100).toFixed(2); // ✅ Cap untuk tooltip juga

                return {
                    date: (i + 1).toString(),
                    percentage_display: displayValue,      // Untuk posisi dot (max 100)
                    percentage_capped: cappedValue,        // ✅ Untuk tooltip (max 100)
                    percentage_actual_raw: actualValue     // Raw value untuk logic (optional)
                };
            }));

            // ✅ Bullets dengan warna berdasarkan nilai asli
            percentageSeries.bullets.push(function(root, series, dataItem) {
                var actualValue = dataItem.dataContext.percentage_actual_raw || parseFloat(dataItem.dataContext.percentage_display);
                var bulletColor = actualValue < 100 ? am5.color(0xff0000) : am5.color(0x00ff00);

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
