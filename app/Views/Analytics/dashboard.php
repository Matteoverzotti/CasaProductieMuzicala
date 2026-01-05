<?php
/**
 * @var array $summary
 * @var array $pageViewStats
 * @var array $topPages
 * @var array $deviceStats
 * @var array $browserStats
 * @var array $osStats
 * @var array $referrerStats
 * @var array $hourlyStats
 * @var int $activeUsers
 * @var string $startDate
 * @var string $endDate
 * @var string $csrf_token
 */
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analiză Website</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
    <header>
        <h1>Analiză Website</h1>
        <nav>
            <a href="/">← Înapoi la pagina principală</a>
        </nav>
    </header>

    <main>
        <?php if (!empty($flash)): ?>
            <p style="color: <?= $flash['type'] === 'error' ? 'red' : 'green' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </p>
        <?php endif; ?>

        <!-- Date Filter -->
        <section>
            <h2>Filtrare după dată</h2>
            <form method="GET">
                <label>De la: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"></label>
                <label>Până la: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"></label>
                <button type="submit">Filtrează</button>
            </form>
            <p>
                <strong>Descarcă raport:</strong>
                <a href="/admin/analytics/export?start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>&format=excel">Excel (.xlsx)</a> |
                <a href="/admin/analytics/export?start_date=<?= htmlspecialchars($startDate) ?>&end_date=<?= htmlspecialchars($endDate) ?>&format=pdf">PDF</a>
            </p>
        </section>

        <hr>

        <!-- Summary -->
        <section>
            <h2>Sumar</h2>
            <p><strong>Utilizatori activi acum:</strong> <?= $activeUsers ?> (ultimele 5 minute)</p>
            <ul>
                <li><strong>Total vizualizări:</strong> <?= number_format($summary['total_views'] ?? 0) ?></li>
                <li><strong>Sesiuni unice:</strong> <?= number_format($summary['unique_sessions'] ?? 0) ?></li>
                <li><strong>Utilizatori autentificați:</strong> <?= number_format($summary['unique_users'] ?? 0) ?></li>
                <li><strong>Zile monitorizate:</strong> <?= $summary['days_tracked'] ?? 0 ?></li>
            </ul>
        </section>

        <hr>

        <!-- Charts -->
        <section>
            <h2>Vizualizări pe zi</h2>
            <div id="viewsChart" style="width: 100%; height: 300px;"></div>
        </section>

        <hr>

        <section>
            <h2>Dispozitive</h2>
            <div id="deviceChart" style="width: 100%; height: 300px;"></div>
        </section>

        <hr>

        <section>
            <h2>Browsere</h2>
            <div id="browserChart" style="width: 100%; height: 300px;"></div>
        </section>

        <hr>

        <section>
            <h2>Sisteme de operare</h2>
            <div id="osChart" style="width: 100%; height: 300px;"></div>
        </section>

        <hr>

        <section>
            <h2>Trafic pe ore</h2>
            <div id="hourlyChart" style="width: 100%; height: 300px;"></div>
        </section>

        <hr>

        <!-- Top Pages Table -->
        <section>
            <h2>Pagini populare</h2>
            <?php if (empty($topPages)): ?>
                <p>Nu există date.</p>
            <?php else: ?>
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Pagină</th>
                            <th>Vizualizări</th>
                            <th>Vizitatori unici</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topPages as $page): ?>
                            <tr>
                                <td><?= htmlspecialchars($page['page_url']) ?></td>
                                <td><?= number_format($page['views']) ?></td>
                                <td><?= number_format($page['unique_visitors']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <hr>

        <!-- Referrers Table -->
        <section>
            <h2>Surse de trafic</h2>
            <?php if (empty($referrerStats)): ?>
                <p>Nu există date.</p>
            <?php else: ?>
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sursă</th>
                            <th>Vizite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrerStats as $ref): ?>
                            <tr>
                                <td><?= htmlspecialchars($ref['referrer_domain']) ?></td>
                                <td><?= number_format($ref['visits']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <script>
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawAllCharts);

        function drawAllCharts() {
            drawViewsChart();
            drawDeviceChart();
            drawBrowserChart();
            drawOsChart();
            drawHourlyChart();
        }

        function drawViewsChart() {
            var rawData = <?= json_encode($pageViewStats) ?>;
            
            if (rawData.length === 0) {
                document.getElementById('viewsChart').innerHTML = '<p>Nu există date pentru perioada selectată.</p>';
                return;
            }
            
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Data');
            data.addColumn('number', 'Vizualizări');
            data.addColumn('number', 'Sesiuni unice');
            
            rawData.forEach(function(row) {
                data.addRow([row.date, parseInt(row.total_views), parseInt(row.unique_sessions)]);
            });

            var options = {
                curveType: 'function',
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('viewsChart'));
            chart.draw(data, options);
        }

        function drawDeviceChart() {
            var rawData = <?= json_encode($deviceStats) ?>;
            
            if (rawData.length === 0) {
                document.getElementById('deviceChart').innerHTML = '<p>Nu există date.</p>';
                return;
            }
            
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Dispozitiv');
            data.addColumn('number', 'Vizualizări');
            
            var deviceLabels = {
                'desktop': 'Desktop',
                'mobile': 'Mobil',
                'tablet': 'Tabletă',
                'unknown': 'Necunoscut'
            };
            
            rawData.forEach(function(row) {
                data.addRow([deviceLabels[row.device_type] || row.device_type, parseInt(row.views)]);
            });

            var options = {
                pieHole: 0.4,
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.PieChart(document.getElementById('deviceChart'));
            chart.draw(data, options);
        }

        function drawBrowserChart() {
            var rawData = <?= json_encode($browserStats) ?>;
            
            if (rawData.length === 0) {
                document.getElementById('browserChart').innerHTML = '<p>Nu există date.</p>';
                return;
            }
            
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Browser');
            data.addColumn('number', 'Vizualizări');
            
            rawData.forEach(function(row) {
                data.addRow([row.browser || 'Necunoscut', parseInt(row.views)]);
            });

            var options = {
                pieHole: 0.4,
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.PieChart(document.getElementById('browserChart'));
            chart.draw(data, options);
        }

        function drawOsChart() {
            var rawData = <?= json_encode($osStats) ?>;
            
            if (rawData.length === 0) {
                document.getElementById('osChart').innerHTML = '<p>Nu există date.</p>';
                return;
            }
            
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Sistem de operare');
            data.addColumn('number', 'Vizualizări');
            
            rawData.forEach(function(row) {
                data.addRow([row.os || 'Necunoscut', parseInt(row.views)]);
            });

            var options = {
                pieHole: 0.4,
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.PieChart(document.getElementById('osChart'));
            chart.draw(data, options);
        }

        function drawHourlyChart() {
            var rawData = <?= json_encode($hourlyStats) ?>;
            
            if (rawData.length === 0) {
                document.getElementById('hourlyChart').innerHTML = '<p>Nu există date.</p>';
                return;
            }
            
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Ora');
            data.addColumn('number', 'Vizualizări');
            
            var hourMap = {};
            rawData.forEach(function(row) {
                hourMap[parseInt(row.hour)] = parseInt(row.views);
            });
            
            for (var i = 0; i < 24; i++) {
                data.addRow([i.toString().padStart(2, '0') + ':00', hourMap[i] || 0]);
            }

            var options = {
                legend: { position: 'none' }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('hourlyChart'));
            chart.draw(data, options);
        }
    </script>
</body>
</html>
