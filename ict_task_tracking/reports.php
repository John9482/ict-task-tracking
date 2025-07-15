<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$status = $_GET['status'] ?? '';
$officer_id = $_GET['officer_id'] ?? '';

// Build query for filtered tasks
$query = "SELECT t.*, o.full_name 
          FROM tasks t 
          LEFT JOIN officers o ON t.assigned_to = o.officer_id 
          WHERE date_reported BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if (!empty($status)) {
    $query .= " AND t.status = ?";
    $params[] = $status;
}

if (!empty($officer_id)) {
    $query .= " AND t.assigned_to = ?";
    $params[] = $officer_id;
}

$query .= " ORDER BY date_reported DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$filtered_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics for the period
$total_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE date_reported BETWEEN ? AND ?");
$total_tasks->execute([$start_date, $end_date]);
$total_tasks = $total_tasks->fetchColumn();

$completed_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Completed' AND date_reported BETWEEN ? AND ?");
$completed_tasks->execute([$start_date, $end_date]);
$completed_tasks = $completed_tasks->fetchColumn();

$pending_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE status = 'Pending' AND date_reported BETWEEN ? AND ?");
$pending_tasks->execute([$start_date, $end_date]);
$pending_tasks = $pending_tasks->fetchColumn();

// NEW: Get officer performance statistics
$officer_performance = $pdo->prepare("
    SELECT 
        o.officer_id,
        o.full_name,
        o.employment_type,
        COUNT(t.task_id) AS total_tasks,
        SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks,
        SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress_tasks,
        SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) AS pending_tasks,
        ROUND(SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) / COUNT(t.task_id) * 100, 2) AS completion_rate
    FROM officers o
    LEFT JOIN tasks t ON o.officer_id = t.assigned_to AND t.date_reported BETWEEN ? AND ?
    WHERE o.is_active = TRUE
    GROUP BY o.officer_id
    ORDER BY total_tasks DESC
");
$officer_performance->execute([$start_date, $end_date]);
$officer_stats = $officer_performance->fetchAll(PDO::FETCH_ASSOC);

// Fetch officers for filter dropdown
$officers = $pdo->query("SELECT * FROM officers WHERE is_active = TRUE ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Task Tracking - Reports</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header>
                <h1>Reports and Statistics</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <div class="report-filters">
                <h2>Filter Reports</h2>
                <form method="get" action="reports.php">
                    <div class="filter-row">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo $status == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Officer</label>
                            <select name="officer_id">
                                <option value="">All Officers</option>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?php echo $officer['officer_id']; ?>" <?php echo $officer_id == $officer['officer_id'] ? 'selected' : ''; ?>>
                                        <?php echo $officer['full_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn">Apply Filters</button>
                    <a href="reports.php" class="btn btn-cancel">Reset</a>
                </form>
            </div>
            
            <div class="report-summary">
                <h2>Summary for <?php echo date('F Y', strtotime($start_date)); ?></h2>
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Tasks</h3>
                        <p><?php echo $total_tasks; ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Completed</h3>
                        <p><?php echo $completed_tasks; ?></p>
                        <p><?php echo $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100, 2) : 0; ?>%</p>
                    </div>
                    <div class="summary-card">
                        <h3>Pending</h3>
                        <p><?php echo $pending_tasks; ?></p>
                        <p><?php echo $total_tasks > 0 ? round(($pending_tasks / $total_tasks) * 100, 2) : 0; ?>%</p>
                    </div>
                </div>
            </div>
            
            <!-- NEW: Officer Performance Section -->
            <div class="officer-performance">
                <h2>Officer Performance</h2>
                <div class="performance-stats">
                    <table>
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Employment Type</th>
                                <th>Total Tasks</th>
                                <th>Completed</th>
                                <th>In Progress</th>
                                <th>Pending</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($officer_stats as $officer): ?>
                            <tr>
                                <td><?php echo $officer['full_name']; ?></td>
                                <td><?php echo $officer['employment_type']; ?></td>
                                <td><?php echo $officer['total_tasks']; ?></td>
                                <td><?php echo $officer['completed_tasks']; ?></td>
                                <td><?php echo $officer['in_progress_tasks']; ?></td>
                                <td><?php echo $officer['pending_tasks']; ?></td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $officer['completion_rate']; ?>%">
                                            <?php echo $officer['completion_rate']; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="report-details">
                <h2>Task Details</h2>
                <div class="export-options">
                    <a href="export.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&status=<?php echo $status; ?>&officer_id=<?php echo $officer_id; ?>" class="btn btn-export">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Room No</th>
                            <th>Task Detail</th>
                            <th>Assigned To</th>
                            <th>Date Reported</th>
                            <th>Date Solved</th>
                            <th>Status</th>
                            <th>Issue Solved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_tasks as $task): ?>
                        <tr>
                            <td><?php echo $task['task_id']; ?></td>
                            <td><?php echo $task['room_number']; ?></td>
                            <td><?php echo $task['task_detail']; ?></td>
                            <td><?php echo $task['full_name'] ?? 'Unassigned'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($task['date_reported'])); ?></td>
                            <td><?php echo $task['date_solved'] ? date('d/m/Y H:i', strtotime($task['date_solved'])) : 'N/A'; ?></td>
                            <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo $task['status']; ?></span></td>
                            <td><?php echo $task['issue_solved']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>