<?php
require_once 'config/session.php';
requireAdmin();

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get dashboard statistics
$stats = [];

// Total employees
$query = "SELECT COUNT(*) as count FROM employees WHERE status = 'active'";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['total_employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending leave requests
$query = "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['pending_leaves'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Today's attendance
$query = "SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'present'";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['today_present'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Scheduled interviews
$query = "SELECT COUNT(*) as count FROM candidates WHERE status = 'interview_scheduled'";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats['scheduled_interviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent leave requests
$query = "SELECT lr.*, e.first_name, e.last_name, e.employee_id 
          FROM leave_requests lr 
          JOIN employees e ON lr.employee_id = e.id 
          ORDER BY lr.created_at DESC 
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$recent_leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch all employees for the directory list
$query = "SELECT employee_id, first_name, last_name, email, department, designation 
          FROM employees 
          WHERE status = 'active' 
          ORDER BY employee_id ASC";
$stmt = $conn->prepare($query);
$stmt->execute();
$all_employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['total_employees']; ?></div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['pending_leaves']; ?></div>
                        <div class="stat-label">Pending Leave Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['today_present']; ?></div>
                        <div class="stat-label">Present Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['scheduled_interviews']; ?></div>
                        <div class="stat-label">Scheduled Interviews</div>
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
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_leaves as $leave): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($leave['employee_id']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                        <td><?php echo $leave['start_date'] . ' to ' . $leave['end_date']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $leave['status'] === 'pending' ? 'warning' : ($leave['status'] === 'approved' ? 'success' : 'danger'); ?>">
                                                <?php echo ucfirst($leave['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($leave['created_at'])); ?></td>
                                        <td>
                                            <?php if ($leave['status'] === 'pending'): ?>
                                                <a href="leave-management.php?action=approve&id=<?php echo $leave['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                                <a href="leave-management.php?action=reject&id=<?php echo $leave['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
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
            <div class="card" style="margin-top: 25px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
    <h3 class="card-title" style="margin: 0;">Staff Directory</h3>
    <div class="search-box">
        <input type="text" id="empSearch" placeholder="Search name, ID, or dept..." 
               style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; width: 250px; font-size: 14px;">
    </div>
</div>
    <div class="card-body">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_employees as $emp): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($emp['employee_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($emp['department']); ?></span></td>
                        <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                        <td><?php echo htmlspecialchars($emp['email']); ?></td>
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
document.getElementById('empSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    // Select only the rows in the Staff Directory table
    let rows = document.querySelectorAll('.card:last-child tbody tr');

    rows.forEach(row => {
        let name = row.cells[1].textContent.toLowerCase();
        let id = row.cells[0].textContent.toLowerCase();
        let dept = row.cells[2].textContent.toLowerCase();
        
        // If the search text matches Name, ID, or Dept, show the row
        if (name.includes(filter) || id.includes(filter) || dept.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
