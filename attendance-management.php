<?php
require_once 'config/session.php';
requireAdmin(); // Security Guard
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// 1. Get Filters
$view_date = $_GET['date'] ?? date('Y-m-d');
$search = $_GET['search'] ?? '';

// 2. The Super Query
$query = "SELECT e.id as emp_id, e.first_name, e.last_name, e.department, 
                 a.id as attendance_id, a.check_in_time, a.check_out_time, a.status
          FROM employees e
          LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
          WHERE e.status = 'active'";

if (!empty($search)) {
    $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$view_date, "%$search%", "%$search%"]);
} else {
    $stmt = $conn->prepare($query);
    $stmt->execute([$view_date]);
}
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Analytics Logic
$missingStmt = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE check_out_time IS NULL AND date < ?");
$missingStmt->execute([date('Y-m-d')]);
$missing_count = $missingStmt->fetchColumn();

$present_count = 0;
$late_count = 0;
$grand_total_minutes = 0;
$chart_labels = [];
$chart_values = [];

foreach ($records as $row) {
    $chart_labels[] = $row['first_name'];
    if ($row['check_in_time']) {
        $present_count++;
        if (strtotime($row['check_in_time']) > strtotime('09:15:00')) {
            $late_count++;
        }
        
        if ($row['check_out_time']) {
            $start = new DateTime($row['check_in_time']);
            $end = new DateTime($row['check_out_time']);
            $diff = $start->diff($end);
            $m = ($diff->h * 60) + $diff->i;
            $grand_total_minutes += $m;
            $chart_values[] = round($m / 60, 1);
        } else {
            $chart_values[] = 0.5; 
        }
    } else {
        $chart_values[] = 0;
    }
}
$absent_count = count($records) - $present_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Control Tower - PeopleNest Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #6c5ce7; --success: #28a745; --danger: #dc3545; --warning: #f39c12; }
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 30px; }

        /* --- NEW HEADER UI --- */
        .page-header {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 20px 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border-left: 6px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        .header-icon {
            width: 54px; height: 54px;
            background: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-size: 24px; margin-right: 20px;
            box-shadow: 0 8px 15px rgba(108, 92, 231, 0.2);
        }
        .header-text { flex-grow: 1; }
        .breadcrumb { font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; color: #a29bfe; margin-bottom: 4px; font-weight: 700; }
        .main-title { margin: 0; font-size: 26px; color: #2d3436; font-weight: 800; }
        .header-status { display: flex; align-items: center; background: #f8f9fa; padding: 8px 15px; border-radius: 20px; border: 1px solid #eee; }
        .live-indicator { width: 8px; height: 8px; background: #00b894; border-radius: 50%; margin-right: 10px; animation: pulse 2s infinite; }
        .status-label { font-size: 12px; font-weight: 600; color: #636e72; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 184, 148, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(0, 184, 148, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 184, 148, 0); }
        }

        /* Analytics Grid */
        .analytics-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 25px; }
        .summary-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .mini-card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #ddd; transition: transform 0.2s; }
        .mini-card:hover { transform: translateY(-3px); }
        .mini-card h3 { margin: 0; font-size: 1.8rem; color: #2d3436; }
        .mini-card p { margin: 5px 0 0; font-size: 0.75rem; font-weight: bold; color: #636e72; text-transform: uppercase; }
        
        .chart-container { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

        /* Table & Filters */
        .card { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { text-align: left; padding: 15px; background: #fbfbfb; color: #636e72; border-bottom: 2px solid #f1f1f1; font-size: 13px; text-transform: uppercase; }
        .table td { padding: 15px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }
        .filter-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .form-control { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1); }

        .text-green { color: var(--success); font-weight: bold; }
        .text-orange { color: var(--warning); font-weight: bold; }
        .text-blue { color: #007bff; font-weight: bold; }
        .text-red { color: var(--danger); font-weight: bold; }

        @media print { .no-print, .admin-sidebar, .filter-section { display: none !important; } }
    </style>
</head>
<body>

<div class="dashboard-container">
    <?php include 'includes/admin-sidebar.php'; ?>

    <div class="main-content">
        
        <div class="page-header">
            <div class="header-icon">
                <i class="fa-solid fa-tower-observation"></i>
            </div>
            <div class="header-text">
                <nav class="breadcrumb">Admin Panel / Attendance</nav>
                <h1 class="main-title">Attendance Control Tower</h1>
            </div>
            <div class="header-status">
                <span class="live-indicator"></span>
                <span class="status-label">Live System Monitor</span>
            </div>
        </div>

        <?php if ($missing_count > 0): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 12px; margin-bottom: 25px; border-left: 5px solid #f39c12; display: flex; align-items: center; gap: 15px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 20px;"></i>
                <span><strong>Action Required:</strong> <?php echo $missing_count; ?> records are missing Check-Out times from previous shifts.</span>
            </div>
        <?php endif; ?>

        <div class="analytics-grid no-print">
            <div class="summary-cards">
                <div class="mini-card" style="border-color: var(--success);">
                    <h3><?php echo $present_count; ?></h3>
                    <p>Present Today</p>
                </div>
                <div class="mini-card" style="border-color: var(--danger);">
                    <h3><?php echo $absent_count; ?></h3>
                    <p>Absent</p>
                </div>
                <div class="mini-card" style="border-color: var(--warning);">
                    <h3><?php echo $late_count; ?></h3>
                    <p>Late Arrivals</p>
                </div>
                <div class="mini-card" style="border-color: var(--primary);">
                    <h3><?php echo floor($grand_total_minutes / 60); ?>h</h3>
                    <p>Total Man-Hours</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="attendanceChart" style="max-height: 220px;"></canvas>
            </div>
        </div>

        <div class="filter-section no-print">
            <form method="GET" style="display:flex; gap:12px;">
                <input type="date" name="date" value="<?php echo $view_date; ?>" class="form-control" onchange="this.form.submit()">
                <input type="text" name="search" placeholder="Search employee..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="min-width: 250px;">
                <button type="submit" class="btn" style="background: var(--primary); color:white; border:none; padding:10px 25px; border-radius:8px; cursor:pointer; font-weight:600;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </form>
            
            <div style="display: flex; gap: 12px;">
                <a href="generate-report.php?month=<?php echo date('m', strtotime($view_date)); ?>&year=<?php echo date('Y', strtotime($view_date)); ?>" class="btn" style="background: var(--success); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-export"></i> Export CSV
                </a>
                <button onclick="window.print()" class="btn" style="background: #636e72; color: white; border:none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Hours Worked</th>
                        <th>Status</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                    <?php 
                        $hours_display = "--";
                        $color_class = "text-red";
                        $is_late = false;

                        if ($row['check_in_time']) {
                            $is_late = strtotime($row['check_in_time']) > strtotime('09:15:00');
                            
                            if ($row['check_out_time']) {
                                $start = new DateTime($row['check_in_time']);
                                $end = new DateTime($row['check_out_time']);
                                $diff = $start->diff($end);
                                $hours_display = $diff->format('%h hrs %i mins');
                                $mins = ($diff->h * 60) + $diff->i;
                                $color_class = ($mins >= 480) ? "text-green" : "text-orange";
                            } else {
                                $color_class = "text-blue";
                                $hours_display = "Working Now";
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #2d3436;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                            <?php if($is_late): ?> <span style="font-size: 10px; background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-weight: bold;">LATE ENTRY</span> <?php endif; ?>
                        </td>
                        <td style="color: #636e72;"><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo $row['check_in_time'] ? date('h:i A', strtotime($row['check_in_time'])) : '---'; ?></td>
                        <td><?php echo $row['check_out_time'] ? date('h:i A', strtotime($row['check_out_time'])) : '---'; ?></td>
                        <td class="<?php echo $color_class; ?>"><?php echo $hours_display; ?></td>
                        <td>
                            <?php if (!$row['check_in_time']): ?>
                                <span class="status-badge" style="background: #ffeaa7; color: #d63031; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">Absent</span>
                            <?php elseif (!$row['check_out_time']): ?>
                                <span class="status-badge" style="background: #e1f5fe; color: #0288d1; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">In Office</span>
                            <?php else: ?>
                                <span class="status-badge" style="background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">Completed</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-print">
                            <?php if ($row['attendance_id']): ?>
                                <a href="edit-attendance.php?id=<?php echo $row['attendance_id']; ?>" style="color: var(--primary); font-weight:bold; text-decoration:none;">
                                    <i class="fa-solid fa-pen-to-square"></i> Fix
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Hours Worked',
                data: <?php echo json_encode($chart_values); ?>,
                backgroundColor: 'rgba(108, 92, 231, 0.7)',
                borderColor: '#6c5ce7',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f1f1' }, ticks: { color: '#b2bec3' } },
                x: { grid: { display: false }, ticks: { color: '#b2bec3' } }
            }
        }
    });
</script>

</body>
</html>