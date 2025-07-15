<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get statistics for dashboard
$totalTasks = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
$completedTasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Completed'")->fetchColumn();
$inProgressTasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'In Progress'")->fetchColumn();
$pendingTasks = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'")->fetchColumn();

// Get weekly task completion data with trend information
$weeklyData = $pdo->query("
    SELECT 
        DAYNAME(date_reported) AS day_name,
        COUNT(*) AS total_tasks,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks,
        ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) AS completion_rate
    FROM tasks
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DAYNAME(date_reported), DAYOFWEEK(date_reported)
    ORDER BY DAYOFWEEK(date_reported)
")->fetchAll(PDO::FETCH_ASSOC);

// Get enhanced task distribution by status with percentages
$statusDistribution = $pdo->query("
    SELECT 
        status,
        COUNT(*) AS count,
        ROUND((COUNT(*) / (SELECT COUNT(*) FROM tasks)) * 100, 1) AS percentage
    FROM tasks
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Get recent tasks
$recentTasks = $pdo->query("SELECT t.*, o.full_name 
                           FROM tasks t 
                           LEFT JOIN officers o ON t.assigned_to = o.officer_id 
                           ORDER BY date_reported DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Task Tracking - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header>
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <div class="dashboard-grid">
                <!-- Summary Cards -->
                <div class="stats-card total-tasks">
                    <h3><i class="fas fa-tasks"></i> Total Tasks</h3>
                    <p><?php echo $totalTasks; ?></p>
                    <div class="trend">
                        <span>All time tasks</span>
                    </div>
                </div>
                
                <div class="stats-card completed">
                    <h3><i class="fas fa-check-circle"></i> Completed</h3>
                    <p><?php echo $completedTasks; ?></p>
                    <div class="trend">
                        <span><?php echo $totalTasks > 0 ? round(($completedTasks/$totalTasks)*100, 1) : 0; ?>% completion rate</span>
                    </div>
                </div>
                
                <div class="stats-card in-progress">
                    <h3><i class="fas fa-spinner"></i> In Progress</h3>
                    <p><?php echo $inProgressTasks; ?></p>
                    <div class="trend">
                        <span><?php echo $totalTasks > 0 ? round(($inProgressTasks/$totalTasks)*100, 1) : 0; ?>% of total</span>
                    </div>
                </div>
                
                <div class="stats-card pending">
                    <h3><i class="fas fa-clock"></i> Pending</h3>
                    <p><?php echo $pendingTasks; ?></p>
                    <div class="trend">
                        <span><?php echo $totalTasks > 0 ? round(($pendingTasks/$totalTasks)*100, 1) : 0; ?>% of total</span>
                    </div>
                </div>
                
                <!-- Enhanced Weekly Performance Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> Weekly Performance</h3>
                    <canvas id="weeklyChart"></canvas>
                </div>
                
                <!-- Enhanced Task Status Distribution -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Task Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                
                <!-- Calendar -->
                <div class="calendar-card">
                    <h3><i class="far fa-calendar-alt"></i> Calendar</h3>
                    <div id="mini-calendar"></div>
                </div>
                
                <!-- Recent Tasks -->
                <div class="recent-tasks">
                    <h3><i class="fas fa-list-ul"></i> Recent Tasks</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Room No</th>
                                <th>Task Detail</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTasks as $task): ?>
                            <tr>
                                <td><?php echo $task['task_id']; ?></td>
                                <td><?php echo $task['room_number']; ?></td>
                                <td><?php echo substr($task['task_detail'], 0, 30) . '...'; ?></td>
                                <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo $task['status']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
    <script src="js/calendar.js"></script>
    <script>
        // Register the plugin to all charts
        Chart.register(ChartDataLabels);
        
        // Enhanced Weekly Task Completion Chart
        const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
        const weeklyChart = new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($day) { return "'" . $day['day_name'] . "'"; }, $weeklyData)); ?>],
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: [<?php echo implode(',', array_column($weeklyData, 'completed_tasks')); ?>],
                        backgroundColor: '#27ae60',
                        borderColor: '#27ae60',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    },
                    {
                        label: 'Total Tasks',
                        data: [<?php echo implode(',', array_column($weeklyData, 'total_tasks')); ?>],
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: '#3498db',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const dayData = <?php echo json_encode(array_column($weeklyData, 'completion_rate')); ?>;
                                return `Completion: ${dayData[context.dataIndex]}%`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: (value, context) => {
                            const dayData = <?php echo json_encode(array_column($weeklyData, 'completion_rate')); ?>;
                            return context.datasetIndex === 0 ? `${dayData[context.dataIndex]}%` : '';
                        },
                        color: '#27ae60',
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Enhanced Task Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($status) { return "'" . $status['status'] . "'"; }, $statusDistribution)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($statusDistribution, 'count')); ?>],
                    backgroundColor: [
                        '#e74c3c', // Pending
                        '#f39c12', // In Progress
                        '#27ae60'  // Completed
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const percentages = <?php echo json_encode(array_column($statusDistribution, 'percentage')); ?>;
                                return `${context.label}: ${context.raw} (${percentages[context.dataIndex]}%)`;
                            }
                        }
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const percentages = <?php echo json_encode(array_column($statusDistribution, 'percentage')); ?>;
                            return `${percentages[context.dataIndex]}%`;
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
</body>
</html>