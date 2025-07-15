<div class="sidebar">
    <div class="logo">
        <h2>ICT Task Tracking</h2>
        <p>Ministry of Foreign Affairs and Diaspora, Kenya</p>
    </div>
    
    <nav class="nav-menu">
        <ul>
            <li>
                <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> Tasks
                </a>
            </li>
            <li>
                <a href="officers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'officers.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> ICT Officers
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="notification-bell">
        <i class="fas fa-bell"></i>
        <?php
        // Count pending tasks for notification
        $pending_count = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'Pending'")->fetchColumn();
        if ($pending_count > 0): ?>
            <span class="notification-count"><?php echo $pending_count; ?></span>
        <?php endif; ?>
    </div>
</div>