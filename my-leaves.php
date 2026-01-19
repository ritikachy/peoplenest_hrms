<?php
require_once 'config/session.php';
requireEmployee(); // Ensures only employees can access this page
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = "";
$error = "";

// --- NEW: HANDLE LEAVE SUBMISSION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_leave'])) {
    $emp_id = $_POST['emp_db_id']; 
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];

    // Calculate requested days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $days = $start->diff($end)->days + 1;

    try {
        $conn->beginTransaction();

        // 1. Insert the Leave Request
        $insertQuery = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->execute([$emp_id, $leave_type, $start_date, $end_date, $days, $reason]);

        // 2. TRIGGER NOTIFICATION FOR ADMIN (User ID 1)
        $admin_id = 1; 
        $emp_name = $_SESSION['full_name'];
        $notif_msg = "New " . ucfirst($leave_type) . " request from $emp_name ($days Days).";
        
        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        $notifStmt->execute([$admin_id, $notif_msg]);

        $conn->commit();
        $success = "Leave request submitted! Admin has been notified.";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// 1. Get employee data including leave balance
$query = "SELECT * FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Fetch unread notifications count for the bell icon
$notifQuery = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0";
$notifStmt = $conn->prepare($notifQuery);
$notifStmt->execute([$_SESSION['user_id']]);
$unreadCount = $notifStmt->fetchColumn();

// 3. Get leave requests history
$historyQuery = "SELECT lr.*, u.username as admin_name 
                 FROM leave_requests lr 
                 LEFT JOIN users u ON lr.approved_by = u.id 
                 WHERE lr.employee_id = ? 
                 ORDER BY lr.created_at DESC";
$historyStmt = $conn->prepare($historyQuery);
$historyStmt->execute([$employee['id']]);
$leave_requests = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Requests - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .balance-card {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: white; padding: 25px; border-radius: 15px;
            margin-bottom: 30px; display: inline-block; min-width: 280px;
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.2);
        }
        .balance-number { font-size: 3rem; font-weight: 800; display: block; margin: 10px 0; }
        .notif-link { position: relative; font-size: 1.2rem; color: #4b5563; margin-right: 15px; }
        .notif-badge {
            position: absolute; top: -5px; right: -8px;
            background: #ef4444; color: white; font-size: 0.7rem;
            padding: 2px 6px; border-radius: 50%; border: 2px solid white;
        }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background: white; border-bottom: 1px solid #eee;">
                <h1 class="page-title" style="margin:0;">Leave Dashboard</h1>
                <div class="top-bar-right" style="display: flex; align-items: center;">
                    <a href="notifications.php" class="notif-link">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>
                        <small>(ID: <?php echo $_SESSION['emp_id']; ?>)</small>
                    </div>
                </div>
            </div>
            
            <div class="content-area" style="padding: 30px;">
                <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

                <div class="balance-card">
                    <small>Available Leave Balance</small>
                    <span class="balance-number"><?php echo $employee['leave_balance']; ?> Days</span>
                    <a href="apply-leave.php" class="btn" style="background: white; color: #6c5ce7; font-weight: bold; padding: 8px 15px; border-radius: 5px; text-decoration: none;">Request New Leave</a>
                </div>

                <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                    <div class="card-header" style="padding: 20px; border-bottom: 1px solid #eee;">
                        <h3 class="card-title" style="margin:0;">Leave Application History</h3>
                    </div>
                    <div class="card-body">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; background: #f8fafc;">
                                    <th style="padding: 15px;">Type</th>
                                    <th style="padding: 15px;">Duration</th>
                                    <th style="padding: 15px;">Days</th>
                                    <th style="padding: 15px;">Status</th>
                                    <th style="padding: 15px;">Admin Notes</th>
                                    <th style="padding: 15px;">Processed On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leave_requests)): ?>
                                    <tr><td colspan="6" style="padding: 20px; text-align:center;">No leave history found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($leave_requests as $leave): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 15px;"><strong><?php echo ucfirst($leave['leave_type']); ?></strong></td>
                                        <td style="padding: 15px;"><?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></td>
                                        <td style="padding: 15px;"><?php echo $leave['days_requested']; ?></td>
                                        <td style="padding: 15px;">
                                            <span class="status-badge status-<?php echo $leave['status']; ?>">
                                                <?php echo strtoupper($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 15px; color: #6b7280; font-style: italic;">
                                            <?php echo $leave['status'] === 'rejected' ? htmlspecialchars($leave['rejection_reason']) : '-'; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if ($leave['approved_at']): ?>
                                                <?php echo date('M d, Y', strtotime($leave['approved_at'])); ?><br>
                                                <small style="color: #6c5ce7;">By: <?php echo htmlspecialchars($leave['admin_name']); ?></small>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">Awaiting Review</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>