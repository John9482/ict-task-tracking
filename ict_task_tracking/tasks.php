<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_task'])) {
        $room_number = $_POST['room_number'];
        $task_detail = $_POST['task_detail'];
        $assigned_to = $_POST['assigned_to'];
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (room_number, task_detail, assigned_to, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$room_number, $task_detail, $assigned_to, $status]);
            $success = "Task added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding task: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_task'])) {
        $task_id = $_POST['task_id'];
        $room_number = $_POST['room_number'];
        $task_detail = $_POST['task_detail'];
        $assigned_to = $_POST['assigned_to'];
        $status = $_POST['status'];
        $issue_solved = $_POST['issue_solved'];
        $date_solved = $issue_solved == 'Yes' ? date('Y-m-d H:i:s') : null;
        $remarks = $_POST['remarks'];
        
        try {
            $stmt = $pdo->prepare("UPDATE tasks SET room_number = ?, task_detail = ?, assigned_to = ?, status = ?, issue_solved = ?, date_solved = ?, remarks = ? WHERE task_id = ?");
            $stmt->execute([$room_number, $task_detail, $assigned_to, $status, $issue_solved, $date_solved, $remarks, $task_id]);
            $success = "Task updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating task: " . $e->getMessage();
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE task_id = ?");
        $stmt->execute([$task_id]);
        $success = "Task deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error deleting task: " . $e->getMessage();
    }
}

// Fetch all tasks with officer names
$tasks = $pdo->query("SELECT t.*, o.full_name 
                     FROM tasks t 
                     LEFT JOIN officers o ON t.assigned_to = o.officer_id 
                     ORDER BY date_reported DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch active officers for dropdown
$officers = $pdo->query("SELECT * FROM officers WHERE is_active = TRUE ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Task Tracking - Tasks</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header>
                <h1>Task Management</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="content-section">
                <div class="form-container">
                    <h2><?php echo isset($_GET['edit']) ? 'Edit Task' : 'Add New Task'; ?></h2>
                    <form method="post">
                        <?php if (isset($_GET['edit'])): 
                            $edit_id = $_GET['edit'];
                            $edit_task = $pdo->query("SELECT * FROM tasks WHERE task_id = $edit_id")->fetch(PDO::FETCH_ASSOC);
                        ?>
                            <input type="hidden" name="task_id" value="<?php echo $edit_task['task_id']; ?>">
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="text" name="room_number" value="<?php echo $edit_task['room_number']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Task Details</label>
                                <textarea name="task_detail" required><?php echo $edit_task['task_detail']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Assigned To</label>
                                <select name="assigned_to" required>
                                    <option value="">Select Officer</option>
                                    <?php foreach ($officers as $officer): ?>
                                        <option value="<?php echo $officer['officer_id']; ?>" <?php echo $edit_task['assigned_to'] == $officer['officer_id'] ? 'selected' : ''; ?>>
                                            <?php echo $officer['full_name']; ?> (<?php echo $officer['employment_type']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" required>
                                    <option value="Pending" <?php echo $edit_task['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $edit_task['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $edit_task['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Issue Solved?</label>
                                <select name="issue_solved" required>
                                    <option value="No" <?php echo $edit_task['issue_solved'] == 'No' ? 'selected' : ''; ?>>No</option>
                                    <option value="Yes" <?php echo $edit_task['issue_solved'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks"><?php echo $edit_task['remarks']; ?></textarea>
                            </div>
                            <button type="submit" name="update_task" class="btn">Update Task</button>
                            <a href="tasks.php" class="btn btn-cancel">Cancel</a>
                        <?php else: ?>
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="text" name="room_number" required>
                            </div>
                            <div class="form-group">
                                <label>Task Details</label>
                                <textarea name="task_detail" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Assigned To</label>
                                <select name="assigned_to" required>
                                    <option value="">Select Officer</option>
                                    <?php foreach ($officers as $officer): ?>
                                        <option value="<?php echo $officer['officer_id']; ?>">
                                            <?php echo $officer['full_name']; ?> (<?php echo $officer['employment_type']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <button type="submit" name="add_task" class="btn">Add Task</button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-container">
                    <h2>Task List</h2>
                    <div class="search-filter">
                        <input type="text" id="searchInput" placeholder="Search tasks...">
                        <select id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <table id="tasksTable">
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Room No</th>
                                <th>Task Detail</th>
                                <th>Assigned To</th>
                                <th>Date Reported</th>
                                <th>Status</th>
                                <th>Issue Solved</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo $task['task_id']; ?></td>
                                <td><?php echo $task['room_number']; ?></td>
                                <td><?php echo substr($task['task_detail'], 0, 30) . (strlen($task['task_detail']) > 30 ? '...' : ''); ?></td>
                                <td><?php echo $task['full_name'] ?? 'Unassigned'; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($task['date_reported'])); ?></td>
                                <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo $task['status']; ?></span></td>
                                <td><?php echo $task['issue_solved']; ?></td>
                                <td class="actions">
                                    <a href="tasks.php?edit=<?php echo $task['task_id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="tasks.php?delete=<?php echo $task['task_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this task?')"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
    <script src="js/task-filter.js"></script>
</body>
</html>