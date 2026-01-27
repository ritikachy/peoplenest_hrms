<?php 
require_once 'config/session.php';
requireEmployee(); 

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// 1. Get current employee data
$query = "SELECT * FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveType = $_POST['leave_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $daysRequested = $start->diff($end)->days + 1;
    
    $balanceColumn = $leaveType . "_balance"; 
    $availableBalance = $employee[$balanceColumn];

    if ($daysRequested > $availableBalance) {
        $error = "Insufficient Balance: You only have $availableBalance days of $leaveType leave left.";
    } else {
        try {
            $conn->beginTransaction();

            $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, days_requested, reason, status) 
                      VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($query);
            $stmt->execute([$employee['id'], $leaveType, $startDate, $endDate, $daysRequested, $reason]);

            // Notification for Admin
            $admin_id = 1; 
            $emp_name = $_SESSION['full_name'];
            $notif_msg = "New " . ucfirst($leaveType) . " leave request from $emp_name ($daysRequested days).";
            
            $notifQuery = "INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())";
            $notifStmt = $conn->prepare($notifQuery);
            $notifStmt->execute([$admin_id, $notif_msg]);

            $conn->commit();
            $success = "Application submitted successfully! Your request is now pending admin approval.";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Leave | PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .content-area { max-width: 900px; margin: 0 auto; padding: 20px; }
        
        /* Balance Cards UI */
        .balance-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); 
            gap: 15px; 
            margin-bottom: 30px; 
        }
        
        .leave-stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.2s;
        }
        
        .leave-stat-card:hover { transform: translateY(-3px); border-color: var(--primary-blue); }
        
        .icon-box {
            width: 45px; height: 45px;
            border-radius: 10px;
            background: #eff6ff;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-info small { color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; }
        .stat-info h3 { margin: 0; color: var(--text-main); font-size: 1.4rem; }

        /* Form Styling */
        .application-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .card-header { background: #fcfcfd; padding: 20px; border-bottom: 1px solid var(--border-color); }
        .card-header h2 { margin: 0; font-size: 1.1rem; color: var(--text-main); }
        
        .card-body { padding: 30px; }

        .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-main); font-size: 0.9rem; }
        
        .input-style {
            width: 100%; padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            background: #fff;
        }
        
        .input-style:focus { outline: none; border-color: var(--primary-blue); ring: 2px solid #bfdbfe; }

        .btn-submit {
            background: var(--primary-blue);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: background 0.2s;
        }
        
        .btn-submit:hover { background: #1d4ed8; }

        .alert { border-radius: 8px; padding: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/employee-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title"><i class="fa-solid fa-calendar-plus"></i> Apply for Leave</h1>
                <div class="user-menu">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert" style="background:#f0fdf4; color:#166534; border: 1px solid #bbf7d0;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert" style="background:#fef2f2; color:#991b1b; border: 1px solid #fecaca;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="balance-grid">
                    <div class="leave-stat-card">
                        <div class="icon-box"><i class="fa-solid fa-hand-holding-medical"></i></div>
                        <div class="stat-info"><small>Sick</small><h3><?php echo $employee['sick_balance']; ?></h3></div>
                    </div>
                    <div class="leave-stat-card">
                        <div class="icon-box"><i class="fa-solid fa-umbrella-beach"></i></div>
                        <div class="stat-info"><small>Casual</small><h3><?php echo $employee['casual_balance']; ?></h3></div>
                    </div>
                    <div class="leave-stat-card">
                        <div class="icon-box"><i class="fa-solid fa-calendar-check"></i></div>
                        <div class="stat-info"><small>Annual</small><h3><?php echo $employee['annual_balance']; ?></h3></div>
                    </div>
                    <div class="leave-stat-card">
                        <div class="icon-box"><i class="fa-solid fa-kit-medical"></i></div>
                        <div class="stat-info"><small>Emergency</small><h3><?php echo $employee['emergency_balance']; ?></h3></div>
                    </div>
                </div>
                
                <div class="application-card">
                    <div class="card-header">
                        <h2>Leave Application Form</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div style="margin-bottom: 20px;">
                                <label class="form-label">Type of Leave</label>
                                <select name="leave_type" class="input-style" required>
                                    <option value="" disabled selected>Select category...</option>
                                    <option value="sick">Sick Leave (Medical)</option>
                                    <option value="casual">Casual Leave (Short-term)</option>
                                    <option value="annual">Annual Leave (Vacation)</option>
                                    <option value="maternity">Maternity Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                </select>
                            </div>
                            
                            <div style="display:flex; gap:20px; margin-bottom:20px;">
                                <div style="flex:1;">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" id="start_date" name="start_date" class="input-style" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div style="flex:1;">
                                    <label class="form-label">End Date</label>
                                    <input type="date" id="end_date" name="end_date" class="input-style" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 25px;">
                                <label class="form-label">Reason for Absence</label>
                                <textarea name="reason" class="input-style" placeholder="Please provide a brief explanation..." required style="min-height:100px; resize: vertical;"></textarea>
                            </div>
                            
                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i> Submit Leave Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Automatic date validation
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        startDate.addEventListener('change', function() {
            endDate.min = this.value;
            if (endDate.value && endDate.value < this.value) {
                endDate.value = this.value;
            }
        });
    </script>
</body>
</html>