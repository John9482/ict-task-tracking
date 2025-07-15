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

// Build query
$query = "SELECT t.task_id, t.room_number, t.task_detail, o.full_name AS assigned_to, 
                 t.date_reported, t.date_solved, t.status, t.issue_solved, t.remarks
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
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=ICT_Tasks_Report_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Task ID', 
    'Room Number', 
    'Task Detail', 
    'Assigned To', 
    'Date Reported', 
    'Date Solved', 
    'Status', 
    'Issue Solved',
    'Remarks'
]);

// Add data rows
foreach ($tasks as $task) {
    fputcsv($output, [
        $task['task_id'],
        $task['room_number'],
        $task['task_detail'],
        $task['assigned_to'] ?? 'Unassigned',
        date('d/m/Y H:i', strtotime($task['date_reported'])),
        $task['date_solved'] ? date('d/m/Y H:i', strtotime($task['date_solved'])) : 'N/A',
        $task['status'],
        $task['issue_solved'],
        $task['remarks'] ?? ''
    ]);
}

fclose($output);
exit;