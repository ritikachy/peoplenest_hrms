<?php
require_once 'config/session.php';
requireEmployee(); 
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = "";
$error = "";

// 1. Fetch employee data FIRST
$query = "SELECT * FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    die("Employee profile not found. Please contact Admin.");
}

// --- HANDLE LEAVE SUBMISSION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_leave'])) {
    $emp_id = $employee['id']; 
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $today = new DateTime(date('Y-m-d'));
    
    if ($start > $end) {
        $error = "End date cannot be before the start date.";
    } elseif ($start < $today) {
        $error = "You cannot apply for leave in the past.";
    } else {
        $interval = $start->diff($end);
        $days = $interval->days + 1;

        $balance_column = $leave_type . "_balance";
        $available_balance = $employee[$balance_column] ?? 0;

        if ($leave_type !== 'unpaid' && $days > $available_balance) {
            $error = "Insufficient " . ucfirst($leave_type) . " balance. Requested: $days, Available: $available_balance.";
        } else {
            try {
                $conn->beginTransaction();

                $insertQuery = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
                $stmtInsert = $conn->prepare($insertQuery);
                $stmtInsert->execute([$emp_id, $leave_type, $start_date, $end_date, $days, $reason]);

                $adminQuery = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
                $adminResult = $conn->query($adminQuery)->fetch();
                $admin_id = $adminResult['id'] ?? 1;

                $emp_name = $_SESSION['full_name'];
                $notif_msg = "New " . ucfirst($leave_type) . " request from $emp_name ($days Days).";
                
                $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
                $notifStmt->execute([$admin_id, $notif_msg]);

                $conn->commit();
                $success = "Application submitted successfully!";
                
                $stmt->execute([$_SESSION['user_id']]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// 2. Fetch History
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
    <title>Leave Center - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .balance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .balance-card { padding: 15px; border-radius: 10px; color: white; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .bg-sick { background: #8b5cf6; } .bg-casual { background: #3b82f6; } .bg-annual { background: #10b981; } .bg-emergency { background: #f59e0b; }
        .balance-number { font-size: 1.8rem; font-weight: 800; display: block; margin-top: 5px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .btn-logout:hover { background: #fee2e2; color: #b91c1c !important; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; background: #fff; border-bottom: 1px solid #eee;">
                <h2 style="margin:0;"><i class="fa-solid fa-calendar-check"></i> Leave Center</h2>
                
                <div class="user-info" style="display: flex; align-items: center; gap: 20px;">
                    <span style="font-weight: 600; color: #334155;">
                        <i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </span>

                    <a href="logout.php" class="btn-logout" style="text-decoration: none; color: #ef4444; font-size: 0.85rem; display: flex; align-items: center; gap: 5px; padding: 6px 12px; border: 1px solid #fee2e2; border-radius: 6px; transition: 0.3s;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>

            <div class="content-area" style="padding: 30px;">
                <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
                <?php if($error): ?> <div class="alert alert-danger"><?php echo $error; ?></div> <?php endif; ?>

                <div class="balance-grid">
                    <div class="balance-card bg-sick"><small>Sick</small><span class="balance-number"><?php echo $employee['sick_balance']; ?></span></div>
                    <div class="balance-card bg-casual"><small>Casual</small><span class="balance-number"><?php echo $employee['casual_balance']; ?></span></div>
                    <div class="balance-card bg-annual"><small>Annual</small><span class="balance-number"><?php echo $employee['annual_balance']; ?></span></div>
                    <div class="balance-card bg-emergency"><small>Emergency</small><span class="balance-number"><?php echo $employee['emergency_balance']; ?></span></div>
                </div>

                <div class="card">
                    <h3>Apply for Leave</h3>
                    <form method="POST" class="leave-form">
                        <div class="form-row" style="display:flex; gap:20px; margin-bottom:15px;">
                            <div style="flex:1;">
                                <label>Leave Type</label>
                                <select name="leave_type" required class="form-control">
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                </select>
                            </div>
                            <div style="flex:1;"><label>From</label><input type="date" name="start_date" required class="form-control"></div>
                            <div style="flex:1;"><label>To</label><input type="date" name="end_date" required class="form-control"></div>
                        </div>
                        <div class="form-group" style="margin-bottom:15px;">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Explain your reason for leave..."></textarea>
                        </div>
                        <button type="submit" name="apply_leave" class="btn btn-primary">Submit Application</button>
                    </form>
                </div>

                <div class="card" style="margin-top:30px;">
                    <h3>Leave History</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leave_requests as $leave): ?>
                            <tr>
                                <td><strong><?php echo ucfirst($leave['leave_type']); ?></strong></td>
                                <td><small><?php echo date('M d', strtotime($leave['start_date'])); ?> - <?php echo date('M d, Y', strtotime($leave['end_date'])); ?></small></td>
                                <td><?php echo $leave['days_requested']; ?></td>
                                <td><span class="status-badge status-<?php echo $leave['status']; ?>"><?php echo ucfirst($leave['status']); ?></span></td>
                                <td><small><?php echo htmlspecialchars($leave['rejection_reason'] ?: '---'); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>