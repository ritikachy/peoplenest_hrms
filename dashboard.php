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


if (!$employee) {
    die("Employee not found. Please check your login session or database.");
}

// Get attendance summary for current month
$query = "SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
            COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_days,
            COUNT(CASE WHEN status = 'leave' THEN 1 END) as leave_days
          FROM attendance 
          WHERE employee_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
$stmt = $conn->prepare($query);
$stmt->execute([$employee['id']]);
$attendance_summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent leave requests
$query = "SELECT * FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute([$employee['id']]);
$recent_leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent attendance
$query = "SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC LIMIT 7";
$stmt = $conn->prepare($query);
$stmt->execute([$employee['id']]);
$recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <div class="user-role">Employee</div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['present_days']; ?></div>
                        <div class="stat-label">Present Days (This Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['absent_days']; ?></div>
                        <div class="stat-label">Absent Days (This Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['leave_days']; ?></div>
                        <div class="stat-label">Leave Days (This Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($recent_leaves, function($leave) { return $leave['status'] === 'pending'; })); ?></div>
                        <div class="stat-label">Pending Leave Requests</div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Attendance</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Check In</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_attendance as $attendance): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($attendance['date'])); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $attendance['status'] === 'present' ? 'success' : ($attendance['status'] === 'leave' ? 'info' : 'danger'); ?>">
                                                    <?php echo ucfirst($attendance['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $attendance['check_in_time'] ? date('h:i A', strtotime($attendance['check_in_time'])) : '-'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Leave Requests</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_leaves as $leave): ?>
                                        <tr>
                                            <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                            <td><?php echo $leave['days_requested']; ?> days</td>
                                            <td>
                                                <span class="badge badge-<?php echo $leave['status'] === 'pending' ? 'warning' : ($leave['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($leave['status']); ?>
                                                </span>
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
    </div>
</body>
</html>
