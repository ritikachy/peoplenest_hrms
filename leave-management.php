<?php
require_once 'config/session.php';
requireAdmin();

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Handle leave approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $leaveId = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $query = "UPDATE leave_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $leaveId]);
        $success = "Leave request approved successfully!";
    } elseif ($action === 'reject') {
        $reason = $_POST['rejection_reason'] ?? 'No reason provided';
        $query = "UPDATE leave_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id'], $reason, $leaveId]);
        $success = "Leave request rejected successfully!";
    }
}

// Get all leave requests
$query = "SELECT lr.*, e.first_name, e.last_name, e.employee_id, u.username as approved_by_name
          FROM leave_requests lr 
          JOIN employees e ON lr.employee_id = e.id 
          LEFT JOIN users u ON lr.approved_by = u.id
          ORDER BY lr.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$leaveRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Leave Management</h1>
                <div class="user-menu">
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Leave Requests</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Days</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaveRequests as $leave): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($leave['employee_id']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                        <td><?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></td>
                                        <td><?php echo $leave['days_requested']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($leave['reason'], 0, 50)) . (strlen($leave['reason']) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $leave['status'] === 'pending' ? 'warning' : ($leave['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                <?php echo ucfirst($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                                        <td>
                                            <?php if ($leave['status'] === 'pending'): ?>
                                                <a href="?action=approve&id=<?php echo $leave['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this leave request?')">Approve</a>
                                                <button class="btn btn-danger btn-sm" onclick="rejectLeave(<?php echo $leave['id']; ?>)">Reject</button>
                                            <?php else: ?>
                                                <small>
                                                    <?php echo $leave['status'] === 'approved' ? 'Approved' : 'Rejected'; ?> by <?php echo htmlspecialchars($leave['approved_by_name']); ?><br>
                                                    on <?php echo date('M d, Y', strtotime($leave['approved_at'])); ?>
                                                </small>
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
