<?php
require_once 'config/session.php';
requireEmployee();

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get employee data
$query = "SELECT * FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

// Get leave requests
$query = "SELECT lr.*, u.username as approved_by_name 
          FROM leave_requests lr 
          LEFT JOIN users u ON lr.approved_by = u.id 
          WHERE lr.employee_id = ? 
          ORDER BY lr.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$employee['id']]);
$leave_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Requests - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">My Leave Requests</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <div class="user-role">Employee</div>
                    </div>
                    <a href="apply-leave.php" class="btn btn-primary btn-sm">Apply for Leave</a>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Leave Requests</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Days</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Action Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leave_requests as $leave): ?>
                                    <tr>
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
                                            <?php if ($leave['approved_at']): ?>
                                                <?php echo date('M d, Y', strtotime($leave['approved_at'])); ?><br>
                                                <small>by <?php echo htmlspecialchars($leave['approved_by_name']); ?></small>
                                            <?php else: ?>
                                                -
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
</body>
</html>
