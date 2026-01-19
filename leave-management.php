<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = "";
$error = "";

// --- NEW: FETCH UNREAD NOTIFICATIONS COUNT FOR TOP BAR ---
$notifCountQuery = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$notifCountStmt = $conn->prepare($notifCountQuery);
$notifCountStmt->execute([$_SESSION['user_id']]);
$unreadCount = $notifCountStmt->fetch(PDO::FETCH_ASSOC)['unread'];

// --- 1. HANDLE ACTIONS (APPROVE/REJECT) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $leaveId = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $checkQuery = "SELECT lr.*, e.leave_balance, e.id as emp_db_id, e.user_id as target_user_id 
                      FROM leave_requests lr 
                      JOIN employees e ON lr.employee_id = e.id 
                      WHERE lr.id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([$leaveId]);
        $data = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            if ($data['leave_balance'] >= $data['days_requested']) {
                try {
                    $conn->beginTransaction();
                    
                    // Update Status
                    $query = "UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$_SESSION['user_id'], $leaveId]);

                    // Deduct Balance
                    $updateBal = "UPDATE employees SET leave_balance = leave_balance - ? WHERE id = ?";
                    $stmtBal = $conn->prepare($updateBal);
                    $stmtBal->execute([$data['days_requested'], $data['emp_db_id']]);

                    // INSERT NOTIFICATION
                    $notifMsg = "Your leave request (" . $data['leave_type'] . ") from " . $data['start_date'] . " has been APPROVED. 🎉";
                    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $notifStmt->execute([$data['target_user_id'], $notifMsg]);

                    // Sync Attendance
                    $start = new DateTime($data['start_date']);
                    $end = new DateTime($data['end_date']);
                    $end->modify('+1 day'); 
                    $period = new DatePeriod($start, new DateInterval('P1D'), $end);

                    $attnQuery = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, 'On Leave') 
                                 ON DUPLICATE KEY UPDATE status = 'On Leave'";
                    $attnStmt = $conn->prepare($attnQuery);

                    foreach ($period as $dt) {
                        $attnStmt->execute([$data['emp_db_id'], $dt->format('Y-m-d')]);
                    }

                    $conn->commit();
                    $success = "Leave approved! Employee notified.";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Transaction failed: " . $e->getMessage();
                }
            } else {
                $error = "Insufficient balance!";
            }
        }
    } elseif ($action === 'reject') {
        $reason = $_POST['rejection_reason'] ?? 'No reason provided';
        $findStmt = $conn->prepare("SELECT e.user_id, lr.start_date FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id WHERE lr.id = ?");
        $findStmt->execute([$leaveId]);
        $reqData = $findStmt->fetch();

        if ($reqData) {
            $query = "UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id'], $reason, $leaveId]);

            // INSERT REJECTION NOTIFICATION
            $notifMsg = "Your leave request for " . $reqData['start_date'] . " was rejected. Reason: " . $reason;
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifStmt->execute([$reqData['user_id'], $notifMsg]);

            $success = "Leave rejected and employee notified.";
        }
    }
}

// --- 2. FETCH DATA FOR TABLE ---
$query = "SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_code, e.leave_balance, u.username as approved_by_name
          FROM leave_requests lr 
          JOIN employees e ON lr.employee_id = e.id 
          LEFT JOIN users u ON lr.approved_by = u.id
          ORDER BY lr.status = 'pending' DESC, lr.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Management - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .notif-badge { background: #ef4444; color: white; padding: 2px 6px; border-radius: 50%; font-size: 10px; position: absolute; top: -5px; right: -5px; }
        .btn-notif { position: relative; margin-right: 15px; text-decoration: none; font-size: 1.2em; }
        .balance-tag { background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85em; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="page-title">Leave Management</h1>
                
                <div class="user-menu" style="display: flex; align-items: center;">
                    <a href="notifications.php" class="btn-notif">
                        🔔
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
                <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">All Leave Requests</h3></div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Days</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaveRequests as $leave): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($leave['emp_code']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                    <td><small><?php echo $leave['start_date']; ?> to <?php echo $leave['end_date']; ?></small></td>
                                    <td><?php echo $leave['days_requested']; ?></td>
                                    <td><span class="balance-tag"><?php echo $leave['leave_balance']; ?> Left</span></td>
                                    <td>
                                        <span class="badge badge-<?php echo $leave['status'] === 'pending' ? 'warning' : ($leave['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($leave['status'] === 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $leave['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve & sync attendance?')">Approve</a>
                                            <button class="btn btn-danger btn-sm" onclick="rejectLeave(<?php echo $leave['id']; ?>)">Reject</button>
                                        <?php else: ?>
                                            <small>By: <?php echo htmlspecialchars($leave['approved_by_name'] ?? 'System'); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function rejectLeave(leaveId) {
            const reason = prompt('Please enter rejection reason:');
            if (reason) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?action=reject&id=' + leaveId;
                const reasonInput = document.createElement('input');
                reasonInput.type = 'hidden';
                reasonInput.name = 'rejection_reason';
                reasonInput.value = reason;
                form.appendChild(reasonInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>