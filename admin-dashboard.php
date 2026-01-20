<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// --- 1. DATA FETCHING (Top Cards) ---
$stats = [];
$stats['total_employees'] = $conn->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
$stats['pending_leaves'] = $conn->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'")->fetchColumn();
$stats['today_present'] = $conn->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = 'present'")->fetchColumn();
$stats['scheduled_interviews'] = $conn->query("SELECT COUNT(*) FROM candidates WHERE status = 'interview_scheduled'")->fetchColumn();

$recent_leaves = $conn->query("SELECT lr.*, e.first_name, e.last_name, e.employee_id FROM leave_requests lr JOIN employees e ON lr.employee_id = e.id ORDER BY lr.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// --- 2. DATA FETCHING (Charts) ---
$attendance_counts = [];
$attendance_days = [];
for ($i = 4; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT COUNT(*) as count FROM attendance WHERE date = :date AND status = 'present'";
    $stmt = $conn->prepare($query);
    $stmt->execute(['date' => $date]);
    $attendance_counts[] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $attendance_days[] = date('D', strtotime($date)); 
}

$query = "SELECT department, COUNT(*) as count FROM employees WHERE status = 'active' GROUP BY department";
$stmt = $conn->prepare($query);
$stmt->execute();
$dept_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$dept_labels = array_column($dept_results, 'department');
$dept_counts = array_column($dept_results, 'count');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --pn-green: #2d7a32;
            --pn-light-green: #e6fffa;
            --pn-blue: #3182ce;
            --pn-light-blue: #ebf8ff;
            --pn-yellow: #d69e2e;
            --pn-light-yellow: #fffaf0;
            --pn-purple: #805ad5;
            --pn-light-purple: #faf5ff;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        /* STICKY HEADER LAYOUT */
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden; /* Stops double scrollbars */
        }

        .dashboard-layout {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: #f8fafc;
        }

        .sticky-header-part {
            background: #fff;
            z-index: 100;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            padding: 0 25px;
        }

        .scrollable-content {
            flex: 1;
            overflow-y: auto; /* Only this part scrolls */
            padding: 25px;
        }

        /* DASHBOARD COMPONENTS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            border: 1px solid #edf2f7;
        }

        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; margin-right: 15px;
        }

        .stat-number { font-size: 24px; font-weight: 800; color: #1a202c; margin: 0; }
        .stat-label { font-size: 11px; font-weight: 600; color: #718096; text-transform: uppercase; }

        .analytics-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        @media (max-width: 992px) {
            .analytics-container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="sticky-header-part">
                <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px;">
                    <h1 class="page-title" style="margin: 0;">Dashboard Overview</h1>
                    <div class="user-menu" style="display: flex; align-items: center; gap: 15px;">
                        <div class="user-info" style="text-align: right;">
                            <div class="user-name" style="font-weight: bold;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            <div class="user-role" style="font-size: 12px; color: #718096;">Administrator</div>
                        </div>
                        <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--pn-light-green); color: var(--pn-green);">👥</div>
                        <div>
                            <div class="stat-number" style="color: var(--pn-green);"><?php echo $stats['total_employees']; ?></div>
                            <div class="stat-label">Total Staff</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--pn-light-yellow); color: var(--pn-yellow);">⏳</div>
                        <div>
                            <div class="stat-number" style="color: var(--pn-yellow);"><?php echo $stats['pending_leaves']; ?></div>
                            <div class="stat-label">Pending Leaves</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--pn-light-blue); color: var(--pn-blue);">✅</div>
                        <div>
                            <div class="stat-number" style="color: var(--pn-blue);"><?php echo $stats['today_present']; ?></div>
                            <div class="stat-label">Present Today</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: var(--pn-light-purple); color: var(--pn-purple);">📅</div>
                        <div>
                            <div class="stat-number" style="color: var(--pn-purple);"><?php echo $stats['scheduled_interviews']; ?></div>
                            <div class="stat-label">Interviews</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="scrollable-content">
                <div class="analytics-container">
                    <div class="chart-card">
                        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Attendance Trends (Last 5 Days)</h3>
                        <div style="height: 300px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Staff Distribution</h3>
                        <div style="height: 300px;">
                            <canvas id="deptChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card" style="background: #fff; border-radius: 16px; box-shadow: var(--card-shadow); padding: 20px;">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 16px;">Recent Leave Applications</h3>
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid #edf2f7; color: #718096; font-size: 12px; text-transform: uppercase;">
                                <th style="padding-bottom: 10px;">Employee</th>
                                <th style="padding-bottom: 10px;">Leave Type</th>
                                <th style="padding-bottom: 10px;">Status</th>
                                <th style="padding-bottom: 10px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leaves as $leave): ?>
                            <tr style="border-bottom: 1px solid #f7fafc;">
                                <td style="padding: 15px 0;"><strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong></td>
                                <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                <td><span class="badge" style="padding: 5px 10px; border-radius: 20px; font-size: 11px; background: <?php echo $leave['status'] === 'pending' ? '#fffaf0' : '#e6fffa'; ?>; color: <?php echo $leave['status'] === 'pending' ? '#d69e2e' : '#2d7a32'; ?>;"><?php echo ucfirst($leave['status']); ?></span></td>
                                <td>
                                    <?php if ($leave['status'] === 'pending'): ?>
                                        <a href="leave-management.php?action=approve&id=<?php echo $leave['id']; ?>" class="btn btn-success btn-sm" style="font-size: 11px; padding: 5px 10px; background: var(--pn-green); color: white; text-decoration: none; border-radius: 5px;">Approve</a>
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

    <script>
        // Line Chart
        const attCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(attCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($attendance_days); ?>,
                datasets: [{
                    label: 'Present',
                    data: <?php echo json_encode($attendance_counts); ?>,
                    borderColor: '#2d7a32',
                    backgroundColor: 'rgba(45, 122, 50, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#2d7a32'
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } }, 
                scales: { 
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: '#a0aec0' }, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false }, ticks: { color: '#a0aec0' } } 
                } 
            }
        });

        // Donut Chart
        const deptCtx = document.getElementById('deptChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($dept_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($dept_counts); ?>,
                    backgroundColor: ['#2d7a32', '#3182ce', '#d69e2e', '#805ad5', '#e53e3e', '#38b2ac'],
                    hoverOffset: 10
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                cutout: '75%', 
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } } 
            }
        });
    </script>
</body>
</html>