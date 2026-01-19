<?php
require_once 'config/session.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$current_user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 1. FETCH NOTIFICATIONS BASED ON ROLE
// For Admin: user_id = 1. For Employees: user_id = their own ID.
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$current_user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. MARK AS READ (Clears the red badge count)
$updateRead = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$updateStmt = $conn->prepare($updateRead);
$updateStmt->execute([$current_user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .notif-container { max-width: 800px; margin: 0 auto; }
        .notif-item { 
            background: #fff; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 15px; 
            border-left: 6px solid #6c5ce7; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .notif-item:hover { transform: translateY(-2px); }
        .notif-admin { border-left-color: #f59e0b; } /* Orange for Admin alerts */
        .notif-msg { margin: 0 0 10px 0; color: #1f2937; font-size: 1rem; line-height: 1.5; }
        .notif-time { font-size: 0.85em; color: #9ca3af; display: flex; align-items: center; gap: 5px; }
        .empty-state { text-align: center; padding: 60px; color: #94a3b8; background: white; border-radius: 15px; }
        .empty-icon { font-size: 4rem; color: #e2e8f0; margin-bottom: 20px; display: block; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php 
        if ($role === 'admin') {
            include 'includes/admin-sidebar.php'; 
        } else {
            include 'includes/employee-sidebar.php';
        }
        ?>
        
        <div class="main-content">
            <div class="top-bar" style="padding: 20px 30px; background: white; border-bottom: 1px solid #edf2f7;">
                <h1 class="page-title" style="margin:0; font-size: 1.5rem; color: #111827;">Notifications</h1>
            </div>
            
            <div class="content-area" style="padding: 30px;">
                <div class="notif-container">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-bell-slash empty-icon"></i>
                            <h3>All caught up!</h3>
                            <p>No new notifications at this time.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="notif-item <?php echo ($role === 'admin') ? 'notif-admin' : ''; ?>">
                                <p class="notif-msg">
                                    <?php if ($role === 'admin'): ?>
                                        <i class="fa-solid fa-circle-exclamation" style="color: #f59e0b; margin-right: 8px;"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-check" style="color: #6c5ce7; margin-right: 8px;"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($n['message']); ?>
                                </p>
                                <div class="notif-time">
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>