<?php 
require_once 'config/session.php';
requireEmployee(); // Ensures only employees can access this page

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// 1. Get current employee data including all 5 leave balances
$query = "SELECT * FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

$success = "";
$error = "";

// Handle leave application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveType = $_POST['leave_type']; // 'sick', 'casual', 'annual', 'maternity', 'emergency'
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    
    // 2. Calculate requested days
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $daysRequested = $start->diff($end)->days + 1;
    
    // 3. THE BALANCE GUARD: Check if they have enough days left in their DB column
    $balanceColumn = $leaveType . "_balance"; // Matches 'sick_balance', 'casual_balance', etc.
    $availableBalance = $employee[$balanceColumn];

    if ($daysRequested > $availableBalance) {
        $error = "You only have $availableBalance days of $leaveType leave left. You cannot request $daysRequested days.";
    } else {
        try {
            $conn->beginTransaction();

            // 4. Insert the request into leave_requests table
            $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status) 
                      VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->execute([$employee['id'], $leaveType, $startDate, $endDate, $daysRequested, $reason]);

            // 5. Create notification for the Admin (User ID 1)
            $admin_id = 1; 
            $emp_name = $_SESSION['full_name'];
            $notif_msg = "New " . ucfirst($leaveType) . " leave request from $emp_name ($daysRequested days).";
            
            $notifQuery = "INSERT INTO notifications (user_id, message, is_read, created_at) 
                           VALUES (?, ?, 0, NOW())";
            $notifStmt = $conn->prepare($notifQuery);
            $notifStmt->execute([$admin_id, $notif_msg]);

            $conn->commit();
            $success = "Application sent! Your $leaveType balance is currently $availableBalance days.";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .balance-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 20px; }
        .b-card { background: #fff; padding: 10px; border-radius: 8px; border-left: 4px solid #2563eb; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; }
        .b-card small { color: #666; display: block; }
        .b-card strong { font-size: 1.2rem; color: #333; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Apply for Leave</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success" style="padding:15px; background:#dcfce7; color:#166534; border-radius:5px; margin-bottom:20px;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error" style="padding:15px; background:#fee2e2; color:#991b1b; border-radius:5px; margin-bottom:20px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="balance-cards">
                    <div class="b-card"><small>Sick</small><strong><?php echo $employee['sick_balance']; ?></strong></div>
                    <div class="b-card"><small>Casual</small><strong><?php echo $employee['casual_balance']; ?></strong></div>
                    <div class="b-card"><small>Annual</small><strong><?php echo $employee['annual_balance']; ?></strong></div>
                    <div class="b-card"><small>Emergency</small><strong><?php echo $employee['emergency_balance']; ?></strong></div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Leave Type</label>
                                <select name="leave_type" required style="width:100%; padding:10px; margin-bottom:15px;">
                                    <option value="sick">Sick Leave</option>
                                    <option value="casual">Casual Leave</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="maternity">Maternity Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                </select>
                            </div>
                            
                            <div style="display:flex; gap:20px; margin-bottom:15px;">
                                <div style="flex:1;">
                                    <label>Start Date</label>
                                    <input type="date" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:10px;">
                                </div>
                                <div style="flex:1;">
                                    <label>End Date</label>
                                    <input type="date" id="end_date" name="end_date" required min="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:10px;">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Reason</label>
                                <textarea name="reason" required style="width:100%; min-height:80px; padding:10px;"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top:15px; padding:12px 25px; cursor:pointer;">Submit Application</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent picking an end date before the start date
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>