<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_officer'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $employment_type = $_POST['employment_type'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO officers (full_name, email, phone_number, employment_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone_number, $employment_type]);
            $success = "Officer added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding officer: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_officer'])) {
        $officer_id = $_POST['officer_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];
        $employment_type = $_POST['employment_type'];
        
        try {
            $stmt = $pdo->prepare("UPDATE officers SET full_name = ?, email = ?, phone_number = ?, employment_type = ? WHERE officer_id = ?");
            $stmt->execute([$full_name, $email, $phone_number, $employment_type, $officer_id]);
            $success = "Officer updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating officer: " . $e->getMessage();
        }
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $officer_id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("UPDATE officers SET is_active = FALSE WHERE officer_id = ?");
        $stmt->execute([$officer_id]);
        $success = "Officer deactivated successfully!";
    } catch (PDOException $e) {
        $error = "Error deactivating officer: " . $e->getMessage();
    }
}

// Fetch all active officers
$officers = $pdo->query("SELECT * FROM officers WHERE is_active = TRUE ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Task Tracking - Officers</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <header>
                <h1>ICT Officers Management</h1>
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
                    <h2><?php echo isset($_GET['edit']) ? 'Edit Officer' : 'Add New Officer'; ?></h2>
                    <form method="post">
                        <?php if (isset($_GET['edit'])): 
                            $edit_id = $_GET['edit'];
                            $edit_officer = $pdo->query("SELECT * FROM officers WHERE officer_id = $edit_id")->fetch(PDO::FETCH_ASSOC);
                        ?>
                            <input type="hidden" name="officer_id" value="<?php echo $edit_officer['officer_id']; ?>">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?php echo $edit_officer['full_name']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo $edit_officer['email']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" value="<?php echo $edit_officer['phone_number']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Employment Type</label>
                                <select name="employment_type" required>
                                    <option value="Attaché" <?php echo $edit_officer['employment_type'] == 'Attaché' ? 'selected' : ''; ?>>Attaché</option>
                                    <option value="Intern" <?php echo $edit_officer['employment_type'] == 'Intern' ? 'selected' : ''; ?>>Intern</option>
                                    <option value="Employee" <?php echo $edit_officer['employment_type'] == 'Employee' ? 'selected' : ''; ?>>Employee</option>
                                </select>
                            </div>
                            <button type="submit" name="update_officer" class="btn">Update Officer</button>
                            <a href="officers.php" class="btn btn-cancel">Cancel</a>
                        <?php else: ?>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" required>
                            </div>
                            <div class="form-group">
                                <label>Employment Type</label>
                                <select name="employment_type" required>
                                    <option value="Attaché">Attaché</option>
                                    <option value="Intern">Intern</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>
                            <button type="submit" name="add_officer" class="btn">Add Officer</button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="table-container">
                    <h2>ICT Officers List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($officers as $officer): ?>
                            <tr>
                                <td><?php echo $officer['officer_id']; ?></td>
                                <td><?php echo $officer['full_name']; ?></td>
                                <td><?php echo $officer['email']; ?></td>
                                <td><?php echo $officer['phone_number']; ?></td>
                                <td><?php echo $officer['employment_type']; ?></td>
                                <td class="actions">
                                    <a href="officers.php?edit=<?php echo $officer['officer_id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="officers.php?delete=<?php echo $officer['officer_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to deactivate this officer?')"><i class="fas fa-trash-alt"></i></a>
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
</body>
</html>