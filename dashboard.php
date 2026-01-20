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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-navy: #1a202c;
            --primary-green: #2d7a32;
            --bg-gray: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #2d3748;
            --text-muted: #718096;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-dark);
            margin: 0;
        }

        .dashboard-layout { display: flex; min-height: 100vh; }

        /* Main Content Styling */
        .main-content { flex: 1; padding: 30px; }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-title { font-size: 1.25rem; font-weight: 700; color: var(--primary-navy); margin: 0; }

        .user-menu { display: flex; align-items: center; gap: 20px; }
        .user-info { text-align: right; }
        .user-name { font-weight: 600; color: var(--primary-navy); font-size: 0.95rem; }
        .user-role { font-size: 0.8rem; color: var(--primary-green); font-weight: 500; }

        .logout-btn {
            background: #fff5f5;
            color: #c53030;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #feb2b2;
            transition: 0.2s;
        }
        .logout-btn:hover { background: #c53030; color: #fff; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        
        /* Matching the accent borders from your screenshots */
        .stat-card:nth-child(1) { border-left: 4px solid #48bb78; } /* Green */
        .stat-card:nth-child(2) { border-left: 4px solid #f56565; } /* Red */
        .stat-card:nth-child(3) { border-left: 4px solid #4299e1; } /* Blue */
        .stat-card:nth-child(4) { border-left: 4px solid #ed8936; } /* Orange */

        .stat-number { font-size: 1.75rem; font-weight: 700; color: var(--primary-navy); }
        .stat-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-top: 5px; }

        /* Tables Grid */
        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header i { color: var(--primary-green); }
        .card-title { font-size: 1rem; font-weight: 700; color: var(--primary-navy); margin: 0; }

        .table { width: 100%; border-collapse: collapse; }
        .table th {
            text-align: left;
            padding: 12px 20px;
            background: #fcfcfd;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }
        .table td { padding: 12px 20px; font-size: 0.85rem; border-bottom: 1px solid #f1f5f9; }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        .badge-success { background: #f0fdf4; color: #166534; }
        .badge-info { background: #eff6ff; color: #1e40af; }
        .badge-warning { background: #fffbeb; color: #92400e; }
        .badge-danger { background: #fef2f2; color: #991b1b; }

        @media (max-width: 1024px) {
            .data-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Dashboard Overview</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                        <div class="user-role">Employee Portal</div>
                    </div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['present_days']; ?></div>
                        <div class="stat-label">Present (Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['absent_days']; ?></div>
                        <div class="stat-label">Absent (Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $attendance_summary['leave_days']; ?></div>
                        <div class="stat-label">Leave (Month)</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count(array_filter($recent_leaves, function($leave) { return $leave['status'] === 'pending'; })); ?></div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                </div>
                
                <div class="data-grid">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-calendar-check"></i>
                            <h3 class="card-title">Recent Attendance</h3>
                        </div>
                        <div class="card-body">
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
                                        <td style="font-weight: 500;"><?php echo date('M d, Y', strtotime($attendance['date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $attendance['status'] === 'present' ? 'success' : ($attendance['status'] === 'leave' ? 'info' : 'danger'); ?>">
                                                <?php echo ucfirst($attendance['status']); ?>
                                            </span>
                                        </td>
                                        <td style="color: var(--text-muted);"><?php echo $attendance['check_in_time'] ? date('h:i A', strtotime($attendance['check_in_time'])) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock"></i>
                            <h3 class="card-title">Recent Leaves</h3>
                        </div>
                        <div class="card-body">
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
                                        <td style="font-weight: 500;"><?php echo ucfirst($leave['leave_type']); ?></td>
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
</body>
</html>