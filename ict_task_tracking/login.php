<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit;
}

// Define authorized users with their credentials
$authorizedUsers = [
    'MOFA' => [
        'password' => 'System@Adm1n2017!',
        'full_name' => 'MOFA Standard User'
    ],
    'MOFAAdmin' => [
        'password' => 'System@Adm1n2017!',
        'full_name' => 'MOFA Administrator'
    ]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if user exists and password matches
    if (array_key_exists($username, $authorizedUsers) && $password === $authorizedUsers[$username]['password']) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $authorizedUsers[$username]['full_name'];
        $_SESSION['is_admin'] = ($username === 'MOFAAdmin'); // Set admin flag
            
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Task Tracking - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>ICT Task Tracking</h1>
            <h2>Ministry of Foreign Affairs and Diaspora, Kenya</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>