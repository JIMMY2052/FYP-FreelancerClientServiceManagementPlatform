<?php
require_once 'config.php';


if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: admin_login.php');
    exit();
}

$conn = getDBConnection();


$freelancer_count = $conn->query("SELECT COUNT(*) as count FROM freelancer")->fetch_assoc()['count'];
$client_count = $conn->query("SELECT COUNT(*) as count FROM client")->fetch_assoc()['count'];
$total_users = $freelancer_count + $client_count;


$active_freelancers = $conn->query("SELECT COUNT(*) as count FROM freelancer WHERE Status = 'active'")->fetch_assoc()['count'];
$active_clients = $conn->query("SELECT COUNT(*) as count FROM client WHERE Status = 'active'")->fetch_assoc()['count'];

// Get gig statistics
$total_gigs = $conn->query("SELECT COUNT(*) as count FROM gig")->fetch_assoc()['count'];

// Get today's gig posts
$today_gigs = $conn->query("SELECT COUNT(*) as count FROM gig WHERE DATE(CreatedAt) = CURDATE()")->fetch_assoc()['count'];

// Get job statistics
$total_jobs = $conn->query("SELECT COUNT(*) as count FROM job")->fetch_assoc()['count'];

// Get today's job posts (jobs posted today)
$today_jobs = $conn->query("SELECT COUNT(*) as count FROM job WHERE DATE(PostDate) = CURDATE()")->fetch_assoc()['count'];

// Get monthly data for charts
// Monthly users
$daily_users = [];
$result = $conn->query("
    SELECT DATE(JoinedDate) as date, COUNT(*) as count
    FROM (
        SELECT JoinedDate FROM freelancer UNION ALL SELECT JoinedDate FROM client
    ) combined
    WHERE JoinedDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(JoinedDate)
    ORDER BY date ASC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daily_users[$row['date']] = (int)$row['count'];
    }
}

// Daily active users
$daily_active = [];
$result = $conn->query("
    SELECT DATE(JoinedDate) as date, COUNT(*) as count
    FROM (
        SELECT JoinedDate FROM freelancer WHERE Status = 'active' UNION ALL SELECT JoinedDate FROM client WHERE Status = 'active'
    ) combined
    WHERE JoinedDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(JoinedDate)
    ORDER BY date ASC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daily_active[$row['date']] = (int)$row['count'];
    }
}

// Daily gigs
$daily_gigs = [];
$result = $conn->query("
    SELECT DATE(CreatedAt) as date, COUNT(*) as count
    FROM gig
    WHERE CreatedAt >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(CreatedAt)
    ORDER BY date ASC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daily_gigs[$row['date']] = (int)$row['count'];
    }
}

// Daily jobs
$daily_jobs = [];
$result = $conn->query("
    SELECT DATE(PostDate) as date, COUNT(*) as count
    FROM job
    WHERE PostDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(PostDate)
    ORDER BY date ASC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daily_jobs[$row['date']] = (int)$row['count'];
    }
}

// Calculate daily inactive users (total - active)
$daily_inactive = [];
$all_dates = array_unique(array_merge(array_keys($daily_users), array_keys($daily_active)));
foreach ($all_dates as $date) {
    $daily_inactive[$date] = ($daily_users[$date] ?? 0) - ($daily_active[$date] ?? 0);
}

// Calculate daily platform health percentage
$daily_health = [];
foreach ($all_dates as $date) {
    $total = $daily_users[$date] ?? 0;
    $active = $daily_active[$date] ?? 0;
    $daily_health[$date] = $total > 0 ? round(($active / $total) * 100) : 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WorkSnyc</title>
    <link rel="icon" type="image/png" href="/images/tabLogo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="admin-layout">
    <div class="admin-sidebar">
        <?php include '../includes/admin_sidebar.php'; ?>
    </div>

    <div class="admin-layout-wrapper">
        <?php include '../includes/admin_header.php'; ?>

        <main class="admin-main-content">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <div>
                        <h1>Dashboard</h1>
                        <p>Welcome back, Admin! Here's an overview of your platform.</p>
                    </div>
                    <button onclick="toggleAllCharts()" style="padding: 10px 20px; background-color: #7c3aed; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600;">View All Charts</button>
                </div>

                <div class="stats-grid">
                    <div class="stat-card" onclick="openChart('totalUsers', 'Total Users (Last 30 Days)', <?php echo json_encode($daily_users); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Users</h3>
                            <i class="stat-card-icon fas fa-users"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $total_users; ?></p>
                        <div class="stat-card-change positive">
                            ↑ Freelancers: <?php echo $freelancer_count; ?> | Clients: <?php echo $client_count; ?>
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('activeUsers', 'Active Users (Last 30 Days)', <?php echo json_encode($daily_active); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Users</h3>
                            <i class="stat-card-icon fas fa-check-circle"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $active_freelancers + $active_clients; ?></p>
                        <div class="stat-card-change positive">
                            ↑ Freelancers: <?php echo $active_freelancers; ?> | Clients: <?php echo $active_clients; ?>
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('inactiveUsers', 'Inactive Users (Last 30 Days)', <?php echo json_encode($daily_inactive); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Inactive Users</h3>
                            <i class="stat-card-icon fas fa-times-circle"></i>
                        </div>
                        <p class="stat-card-value"><?php echo ($freelancer_count - $active_freelancers) + ($client_count - $active_clients); ?></p>
                        <div class="stat-card-change">
                            Freelancers: <?php echo $freelancer_count - $active_freelancers; ?> | Clients: <?php echo $client_count - $active_clients; ?>
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('platformHealth', 'Platform Health (%) (Last 30 Days)', <?php echo json_encode($daily_health); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Platform Health</h3>
                            <i class="stat-card-icon fas fa-chart-bar"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $total_users > 0 ? round((($active_freelancers + $active_clients) / $total_users) * 100) : 0; ?>%</p>
                        <div class="stat-card-change positive">
                            Active user rate
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('totalGigs', 'Total Gigs Posted (Last 30 Days)', <?php echo json_encode($daily_gigs); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Gigs Posted</h3>
                            <i class="stat-card-icon fas fa-briefcase"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $total_gigs; ?></p>
                        <div class="stat-card-change positive">
                            Active service offerings
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('todayGigs', 'Today\'s Gigs Posted (Last 30 Days)', <?php echo json_encode($daily_gigs); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Today's Gigs Posted</h3>
                            <i class="stat-card-icon fas fa-star"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $today_gigs; ?></p>
                        <div class="stat-card-change positive">
                            Posted today
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('totalJobs', 'Total Jobs Posted (Last 30 Days)', <?php echo json_encode($daily_jobs); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Jobs Posted</h3>
                            <i class="stat-card-icon fas fa-tasks"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $total_jobs; ?></p>
                        <div class="stat-card-change positive">
                            Total active projects
                        </div>
                    </div>

                    <div class="stat-card" onclick="openChart('todayJobs', 'Today\'s Job Posts (Last 30 Days)', <?php echo json_encode($daily_jobs); ?>)" style="cursor: pointer;">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Today's Job Posts</h3>
                            <i class="stat-card-icon fas fa-calendar-plus"></i>
                        </div>
                        <p class="stat-card-value"><?php echo $today_jobs; ?></p>
                        <div class="stat-card-change positive">
                            Posted today
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Recent Users</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px;">
                                    <p style="color: #9ca3af;">Go to <a href="admin_manage_users.php" style="color: #7c3aed; text-decoration: none;">Manage Users</a> to view and manage all users</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Chart Modal -->
    <div id="chartModal" class="chart-modal" style="display: none;">
        <div class="chart-modal-content">
            <div class="chart-modal-header">
                <h2 id="chartTitle">Chart Title</h2>
                <button class="chart-modal-close" onclick="closeChart()">&times;</button>
            </div>
            <div class="chart-modal-body">
                <canvas id="chartCanvas"></canvas>
            </div>
        </div>
    </div>

    <!-- All Charts Modal -->
    <div id="allChartsModal" class="all-charts-modal" style="display: none;">
        <div class="all-charts-modal-content">
            <div class="all-charts-modal-header">
                <h2>Platform Analytics - All Charts</h2>
                <button class="chart-modal-close" onclick="closeAllCharts()">&times;</button>
            </div>
            <div class="all-charts-modal-body">
                <div class="charts-grid">
                    <div class="chart-wrapper">
                        <h3>Total Users (Last 30 Days)</h3>
                        <canvas id="chartTotalUsers"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Active Users (Last 30 Days)</h3>
                        <canvas id="chartActiveUsers"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Inactive Users (Last 30 Days)</h3>
                        <canvas id="chartInactiveUsers"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Platform Health % (Last 30 Days)</h3>
                        <canvas id="chartPlatformHealth"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Total Gigs Posted (Last 30 Days)</h3>
                        <canvas id="chartTotalGigs"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Today's Gigs Posted (Last 30 Days)</h3>
                        <canvas id="chartTodayGigs"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Total Jobs Posted (Last 30 Days)</h3>
                        <canvas id="chartTotalJobs"></canvas>
                    </div>
                    <div class="chart-wrapper">
                        <h3>Today's Job Posts (Last 30 Days)</h3>
                        <canvas id="chartTodayJobs"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        let currentChart = null;
        let allCharts = {};

        function generateMonthLabels(data) {
            const months = Object.keys(data).sort();
            return months.map(month => {
                const [year, monthNum] = month.split('-');
                const date = new Date(year, parseInt(monthNum) - 1);
                return date.toLocaleString('default', {
                    month: 'short',
                    year: '2-digit'
                });
            });
        }

        function generateDayLabels(data) {
            const dates = Object.keys(data).sort();
            return dates.map(date => {
                const d = new Date(date + 'T00:00:00');
                return d.toLocaleString('default', {
                    month: 'short',
                    day: 'numeric'
                });
            });
        }

        function getMonthlyValues(data) {
            const months = Object.keys(data).sort();
            return months.map(month => data[month] || 0);
        }

        function getDailyValues(data) {
            const dates = Object.keys(data).sort();
            return dates.map(date => data[date] || 0);
        }

        function createChartConfig(chartType, chartTitle, chartData) {
            const labels = generateDayLabels(chartData);
            const values = getDailyValues(chartData);

            let borderColor = '#7c3aed';
            let backgroundColor = 'rgba(124, 58, 237, 0.1)';

            const colorSchemes = {
                'totalUsers': {
                    border: '#3b82f6',
                    bg: 'rgba(59, 130, 246, 0.1)'
                },
                'activeUsers': {
                    border: '#10b981',
                    bg: 'rgba(16, 185, 129, 0.1)'
                },
                'inactiveUsers': {
                    border: '#ef4444',
                    bg: 'rgba(239, 68, 68, 0.1)'
                },
                'platformHealth': {
                    border: '#f59e0b',
                    bg: 'rgba(245, 158, 11, 0.1)'
                },
                'totalGigs': {
                    border: '#8b5cf6',
                    bg: 'rgba(139, 92, 246, 0.1)'
                },
                'todayGigs': {
                    border: '#f97316',
                    bg: 'rgba(249, 115, 22, 0.1)'
                },
                'totalJobs': {
                    border: '#06b6d4',
                    bg: 'rgba(6, 182, 212, 0.1)'
                },
                'todayJobs': {
                    border: '#ec4899',
                    bg: 'rgba(236, 72, 153, 0.1)'
                }
            };

            if (colorSchemes[chartType]) {
                borderColor = colorSchemes[chartType].border;
                backgroundColor = colorSchemes[chartType].bg;
            }

            return {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartTitle,
                        data: values,
                        borderColor: borderColor,
                        backgroundColor: backgroundColor,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: borderColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                },
                                color: '#374151',
                                padding: 10
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            };
        }

        function openChart(chartType, chartTitle, chartData) {
            const modal = document.getElementById('chartModal');
            const titleElement = document.getElementById('chartTitle');
            const canvas = document.getElementById('chartCanvas');

            titleElement.textContent = chartTitle;
            modal.style.display = 'flex';

            // Destroy previous chart if it exists
            if (currentChart) {
                currentChart.destroy();
            }

            const config = createChartConfig(chartType, chartTitle, chartData);
            currentChart = new Chart(canvas, config);
        }

        function toggleAllCharts() {
            const modal = document.getElementById('allChartsModal');
            if (modal.style.display === 'flex') {
                closeAllCharts();
                return;
            }

            modal.style.display = 'flex';

            // Destroy existing charts
            Object.values(allCharts).forEach(chart => {
                if (chart) chart.destroy();
            });
            allCharts = {};

            // Create all charts
            setTimeout(() => {
                const chartsData = [{
                        id: 'chartTotalUsers',
                        type: 'totalUsers',
                        title: 'Total Users',
                        data: <?php echo json_encode($daily_users); ?>
                    },
                    {
                        id: 'chartActiveUsers',
                        type: 'activeUsers',
                        title: 'Active Users',
                        data: <?php echo json_encode($daily_active); ?>
                    },
                    {
                        id: 'chartInactiveUsers',
                        type: 'inactiveUsers',
                        title: 'Inactive Users',
                        data: <?php echo json_encode($daily_inactive); ?>
                    },
                    {
                        id: 'chartPlatformHealth',
                        type: 'platformHealth',
                        title: 'Platform Health %',
                        data: <?php echo json_encode($daily_health); ?>
                    },
                    {
                        id: 'chartTotalGigs',
                        type: 'totalGigs',
                        title: 'Total Gigs',
                        data: <?php echo json_encode($daily_gigs); ?>
                    },
                    {
                        id: 'chartTodayGigs',
                        type: 'todayGigs',
                        title: 'Today Gigs',
                        data: <?php echo json_encode($daily_gigs); ?>
                    },
                    {
                        id: 'chartTotalJobs',
                        type: 'totalJobs',
                        title: 'Total Jobs',
                        data: <?php echo json_encode($daily_jobs); ?>
                    },
                    {
                        id: 'chartTodayJobs',
                        type: 'todayJobs',
                        title: 'Today Jobs',
                        data: <?php echo json_encode($daily_jobs); ?>
                    }
                ];

                chartsData.forEach(chart => {
                    const canvas = document.getElementById(chart.id);
                    if (canvas) {
                        const config = createChartConfig(chart.type, chart.title, chart.data);
                        allCharts[chart.id] = new Chart(canvas, config);
                    }
                });
            }, 100);
        }

        function closeAllCharts() {
            const modal = document.getElementById('allChartsModal');
            modal.style.display = 'none';
            Object.values(allCharts).forEach(chart => {
                if (chart) chart.destroy();
            });
            allCharts = {};
        }

        function closeChart() {
            const modal = document.getElementById('chartModal');
            modal.style.display = 'none';
            if (currentChart) {
                currentChart.destroy();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('chartModal');
            const allModal = document.getElementById('allChartsModal');

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeChart();
                }
                if (event.target === allModal) {
                    closeAllCharts();
                }
            });

            // Close on ESC key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeChart();
                    closeAllCharts();
                }
            });
        });
    </script>

    <style>
        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .dashboard-header p {
            margin: 8px 0 0 0;
            color: #6b7280;
        }

        .chart-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .chart-modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 90%;
            max-height: 80vh;
            overflow: auto;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .chart-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .chart-modal-header h2 {
            margin: 0;
            color: #1f2937;
            font-size: 20px;
        }

        .chart-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .chart-modal-close:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .chart-modal-body {
            padding: 24px;
            position: relative;
            height: 500px;
            width: 100%;
        }

        #chartCanvas {
            max-height: 100%;
            width: 100% !important;
            height: 100% !important;
        }

        .all-charts-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9998;
            animation: fadeIn 0.3s ease;
        }

        .all-charts-modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-width: 1400px;
            width: 95%;
            max-height: 90vh;
            overflow: auto;
            animation: slideIn 0.3s ease;
        }

        .all-charts-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            background: white;
        }

        .all-charts-modal-header h2 {
            margin: 0;
            color: #1f2937;
            font-size: 24px;
        }

        .all-charts-modal-body {
            padding: 24px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }

        .chart-wrapper {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            position: relative;
            height: 400px;
        }

        .chart-wrapper h3 {
            margin: 0 0 12px 0;
            color: #1f2937;
            font-size: 14px;
            font-weight: 600;
        }

        .chart-wrapper canvas {
            display: block;
            height: calc(100% - 35px) !important;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .all-charts-modal-content {
                width: 98%;
                max-height: 95vh;
            }
        }

        </body></html>