@extends('layout.main')
@section('content')
<!-- Resources -->
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

<style>
    #reportChart,
    #leadChart {
        width: 100%;
        height: 380px;
    }
</style>

    <h2>Dashboard Content</h2>

    <div class="row g-3">

        <!-- Card 1 -->
        <div class="col-xl-4 col-md-6 col-12">
            <div class="card stat-card stat-primary customShadows-card">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="mb-1 fw-bold">{{ $todayReports }}</h3>
                        <p class="mb-0 text-muted">Today's Reports</p>
                    </div>

                    <div class="stat-icon">📊</div>

                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col-xl-4 col-md-6 col-12">
            <div class="card stat-card stat-success">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="mb-1 fw-bold">{{ $totalReports }}</h3>
                        <p class="mb-0 text-muted">Total Reports</p>
                    </div>

                    <div class="stat-icon">📈</div>

                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col-xl-4 col-md-6 col-12">
            <div class="card stat-card stat-purple">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h3 class="mb-1 fw-bold">{{ $totalLeads }}</h3>
                        <p class="mb-0 text-muted">Total Leads</p>
                    </div>

                    <div class="stat-icon">🎯</div>

                </div>
            </div>
        </div>

    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <h6 class="mb-0">Language-wise Reports</h6>
                    <select id="reportRangeFilter" class="form-control form-select" style="width: 160px;">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                <div id="reportChart"></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6 class="mb-2">Leads — Last 7 Days</h6>
                <div id="leadChart"></div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Recent Logins</h6>
                    <a href="{{ url('/logs') }}" class="btn-custom btn-secondary-gradient">View All Logs</a>
                </div>
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Login At</th>
                            <th>Logout At</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLogins as $log)
                            <tr>
                                <td>{{ $log->user_name }}</td>
                                <td>{{ $log->user_email }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->login_at)->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if ($log->logout_at)
                                        {{ \Carbon\Carbon::parse($log->logout_at)->format('d M Y, h:i A') }}
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No login activity yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        am5.ready(function() {

            /* ================================
               CHART 1 — Language-wise Reports (filterable)
            ================================= */
            var reportRoot = am5.Root.new("reportChart");
            reportRoot.setThemes([am5themes_Animated.new(reportRoot)]);

            var reportChart = reportRoot.container.children.push(am5xy.XYChart.new(reportRoot, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var reportXRenderer = am5xy.AxisRendererX.new(reportRoot, {
                minGridDistance: 30
            });

            var reportXAxis = reportChart.xAxes.push(am5xy.CategoryAxis.new(reportRoot, {
                maxDeviation: 0.3,
                categoryField: "language",
                renderer: reportXRenderer,
                tooltip: am5.Tooltip.new(reportRoot, {})
            }));

            var reportYAxis = reportChart.yAxes.push(am5xy.ValueAxis.new(reportRoot, {
                maxDeviation: 0.3,
                min: 0,
                renderer: am5xy.AxisRendererY.new(reportRoot, {
                    strokeOpacity: 0.1
                })
            }));

            var reportSeries = reportChart.series.push(am5xy.ColumnSeries.new(reportRoot, {
                name: "Reports",
                xAxis: reportXAxis,
                yAxis: reportYAxis,
                valueYField: "count",
                categoryXField: "language",
                tooltip: am5.Tooltip.new(reportRoot, {
                    labelText: "{categoryX}: {valueY}"
                })
            }));

            reportSeries.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0,
                fill: am5.color(0x156082)
            });

            var reportCursor = reportChart.set("cursor", am5xy.XYCursor.new(reportRoot, {}));
            reportCursor.lineY.set("visible", false);

            function loadReportChart(range) {
                $.get('/dashboard/report-stats', { range: range }, function(res) {
                    var data = res.data && res.data.length ? res.data : [{ language: 'No Data', count: 0 }];
                    reportXAxis.data.setAll(data);
                    reportSeries.data.setAll(data);
                    reportSeries.appear(600);
                });
            }

            loadReportChart('today');

            $('#reportRangeFilter').on('change', function() {
                loadReportChart($(this).val());
            });

            /* ================================
               CHART 2 — Leads, last 7 days
            ================================= */
            var leadRoot = am5.Root.new("leadChart");
            leadRoot.setThemes([am5themes_Animated.new(leadRoot)]);

            var leadChart = leadRoot.container.children.push(am5xy.XYChart.new(leadRoot, {
                panX: false,
                panY: false,
                wheelX: "none",
                wheelY: "none",
                paddingLeft: 0
            }));

            var leadXAxis = leadChart.xAxes.push(am5xy.CategoryAxis.new(leadRoot, {
                maxDeviation: 0.3,
                categoryField: "label",
                renderer: am5xy.AxisRendererX.new(leadRoot, { minGridDistance: 30 }),
                tooltip: am5.Tooltip.new(leadRoot, {})
            }));

            var leadYAxis = leadChart.yAxes.push(am5xy.ValueAxis.new(leadRoot, {
                maxDeviation: 0.3,
                min: 0,
                renderer: am5xy.AxisRendererY.new(leadRoot, { strokeOpacity: 0.1 })
            }));

            var leadSeries = leadChart.series.push(am5xy.ColumnSeries.new(leadRoot, {
                name: "Leads",
                xAxis: leadXAxis,
                yAxis: leadYAxis,
                valueYField: "count",
                categoryXField: "label",
                tooltip: am5.Tooltip.new(leadRoot, {
                    labelText: "{categoryX}: {valueY}"
                })
            }));

            leadSeries.columns.template.setAll({
                cornerRadiusTL: 5,
                cornerRadiusTR: 5,
                strokeOpacity: 0,
                fill: am5.color(0x2e7d32)
            });

            var leadCursor = leadChart.set("cursor", am5xy.XYCursor.new(leadRoot, {}));
            leadCursor.lineY.set("visible", false);

            $.get('/dashboard/lead-stats', function(res) {
                var data = res.data || [];
                leadXAxis.data.setAll(data);
                leadSeries.data.setAll(data);
                leadSeries.appear(600);
            });

        }); // end am5.ready()
    </script>
@endsection
