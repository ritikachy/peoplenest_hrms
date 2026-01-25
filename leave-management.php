<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = "";
$error = "";

// --- FETCH UNREAD NOTIFICATIONS ---
$notifCountQuery = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$notifCountStmt = $conn->prepare($notifCountQuery);
$notifCountStmt->execute([$_SESSION['user_id']]);
$unreadCount = $notifCountStmt->fetch(PDO::FETCH_ASSOC)['unread'];

// --- 1. HANDLE ACTIONS (APPROVE/REJECT) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $leaveId = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        // Updated Query: Get all specific balance columns
        $checkQuery = "SELECT lr.*, e.id as emp_db_id, e.user_id as target_user_id,
                              e.sick_balance, e.casual_balance, e.annual_balance, 
                              e.maternity_balance, e.emergency_balance
                       FROM leave_requests lr 
                       JOIN employees e ON lr.employee_id = e.id 
                       WHERE lr.id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute([$leaveId]);
        $data = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Determine which balance column to use based on request type
            $type = strtolower($data['leave_type']);
            $balanceColumn = $type . "_balance"; 
            $currentBalance = $data[$balanceColumn];

            if ($currentBalance >= $data['days_requested']) {
                try {
                    $conn->beginTransaction();
                    
                    // A. Update Leave Request Status
                    $query = "UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$_SESSION['user_id'], $leaveId]);

                    // B. Deduct from the CORRECT Balance Column (Dynamic)
                    $updateBal = "UPDATE employees SET $balanceColumn = $balanceColumn - ? WHERE id = ?";
                    $stmtBal = $conn->prepare($updateBal);
                    $stmtBal->execute([$data['days_requested'], $data['emp_db_id']]);

                    // C. Notify Employee
                    $notifMsg = "Your " . ucfirst($type) . " leave request from " . $data['start_date'] . " has been APPROVED. 🎉";
                    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $notifStmt->execute([$data['target_user_id'], $notifMsg]);

                    // D. Sync Attendance (Mark days as 'leave')
                    $start = new DateTime($data['start_date']);
                    $end = new DateTime($data['end_date']);
                    $end->modify('+1 day'); 
                    $period = new DatePeriod($start, new DateInterval('P1D'), $end);

                    $attnQuery = "INSERT INTO attendance (employee_id, date, status) VALUES (?, ?, 'leave') 
                                  ON DUPLICATE KEY UPDATE status = 'leave'";
                    $attnStmt = $conn->prepare($attnQuery);
                    foreach ($period as $dt) {
                        $attnStmt->execute([$data['emp_db_id'], $dt->format('Y-m-d')]);
                    }

                    $conn->commit();
                    $success = "Leave approved and balance deducted from " . ucfirst($type) . "!";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Transaction failed: " . $e->getMessage();
                }
            } else {
                $error = "Insufficient " . ucfirst($type) . " balance! (Available: $currentBalance)";
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

            $notifMsg = "Your leave request for " . $reqData['start_date'] . " was rejected. Reason: " . $reason;
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifStmt->execute([$reqData['user_id'], $notifMsg]);

            $success = "Leave rejected and employee notified.";
        }
    }
}

// --- 2. FETCH DATA FOR TABLE (Updated to show all balances) ---
$query = "SELECT lr.*, e.first_name, e.last_name, e.employee_id as emp_code, 
                 e.sick_balance, e.casual_balance, e.annual_balance, e.maternity_balance, e.emergency_balance,
                 u.username as approved_by_name
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
        .balance-grid { font-size: 0.75em; display: grid; grid-template-columns: 1fr 1fr; gap: 2px; }
        .balance-tag { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; border: 1px solid #ddd; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center;">
                <h1 class="page-title">Leave Management</h1>
                <div class="user-menu" style="display: flex; align-items: center;">
                    <a href="notifications.php" class="btn-notif">🔔<?php if ($unreadCount > 0): ?><span class="notif-badge"><?php echo $unreadCount; ?></span><?php endif; ?></a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
                <?php if ($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Days</th>
                                    <th>Current Balances</th>
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
                                    <td><span class="badge"><?php echo ucfirst($leave['leave_type']); ?></span></td>
                                    <td><small><?php echo $leave['start_date']; ?> to <?php echo $leave['end_date']; ?></small></td>
                                    <td><?php echo $leave['days_requested']; ?></td>
                                    <td>
                                        <div class="balance-grid">
                                            <span class="balance-tag">S: <?php echo $leave['sick_balance']; ?></span>
                                            <span class="balance-tag">C: <?php echo $leave['casual_balance']; ?></span>
                                            <span class="balance-tag">A: <?php echo $leave['annual_balance']; ?></span>
                                            <span class="balance-tag">E: <?php echo $leave['emergency_balance']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $leave['status'] === 'pending' ? 'warning' : ($leave['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                            <?php echo ucfirst($leave['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($leave['status'] === 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $leave['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve & deduct balance?')">Approve</a>
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