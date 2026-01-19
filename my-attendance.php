<?php
require_once 'config/session.php';
requireEmployee(); 
require_once 'config/database.php';
date_default_timezone_set('Asia/Kathmandu'); 

$database = new Database();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = "";


// ... the rest of your code ...

// Configuration
$shift_requirement_minutes = 480; // 8 hours
$is_early_leave = false;

// 1. Identify the logged-in employee
$stmt = $conn->prepare("SELECT id, first_name FROM employees WHERE user_id = ?");
$stmt->execute([$user_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
$emp_id = $employee['id'];

// 2. Fetch Month Statistics
$month_start = date('Y-m-01');
$stmt_month = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND date >= ?");
$stmt_month->execute([$emp_id, $month_start]);
$days_worked = $stmt_month->fetchColumn();

// 3. Fetch today's record
$stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
$stmt->execute([$emp_id, $today]);
$today_record = $stmt->fetch(PDO::FETCH_ASSOC);

// --- NEW: Calculate Progress Percentage ---
$progress_percent = 0;
if ($today_record && empty($today_record['check_out_time'])) {
    $start = new DateTime($today_record['check_in_time']);
    $now = new DateTime();
    $diff = $start->diff($now);
    $minutes_worked = ($diff->h * 60) + $diff->i;
    $progress_percent = min(100, round(($minutes_worked / $shift_requirement_minutes) * 100));

    if ($minutes_worked < $shift_requirement_minutes) {
        $is_early_leave = true;
    }
}

// --- NEW: Fetch "Who's In Today" ---
$stmt_others = $conn->prepare("SELECT e.first_name FROM attendance a JOIN employees e ON a.employee_id = e.id WHERE a.date = ? AND a.check_out_time IS NULL AND e.id != ?");
$stmt_others->execute([$today, $emp_id]);
$others_in = $stmt_others->fetchAll(PDO::FETCH_ASSOC);

// 4. Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_time = date('H:i:s');

    if (isset($_POST['btn_checkin'])) {
        $lat = $_POST['latitude'] ?? null;
        $lng = $_POST['longitude'] ?? null;
        $sql = "INSERT INTO attendance (employee_id, date, status, check_in_time, latitude, longitude) VALUES (?, ?, 'present', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$emp_id, $today, $current_time, $lat, $lng]);
        header("Location: my-attendance.php?msg=Shift Started Successfully");
        exit();
    } 
    elseif (isset($_POST['btn_checkout'])) {
        $note = $_POST['early_leave_reason'] ?? '';
        $sql = "UPDATE attendance SET check_out_time = ?, notes = ? WHERE employee_id = ? AND date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$current_time, $note, $emp_id, $today]);
        header("Location: my-attendance.php?msg=Shift Ended Successfully");
        exit();
    }
}

if (isset($_GET['msg'])) { $message = $_GET['msg']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance - PeopleNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --success: #00b894;
            --danger: #d63031;
            --dark: #2d3436;
            --gray: #636e72;
            --light-bg: #f4f7f6;
        }

        body { background-color: var(--light-bg); font-family: 'Segoe UI', Roboto, sans-serif; margin: 0; }
        .main-content { padding: 40px; display: flex; flex-direction: column; align-items: center; min-height: 100vh; }
        
        .portal-header {
            width: 100%;
            max-width: 900px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 40px;
            border-radius: 24px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 15px 35px rgba(108, 92, 231, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .portal-header::after {
            content: "\f0ac"; font-family: "Font Awesome 6 Free"; font-weight: 900;
            position: absolute; right: -20px; bottom: -20px; font-size: 150px; opacity: 0.1;
        }
        .portal-header h1 { margin: 0; font-size: 2.6rem; font-weight: 800; letter-spacing: -1px; }
        .portal-header p { margin: 5px 0 0; opacity: 0.9; font-size: 1.1rem; }

        .stats-container { display: flex; gap: 20px; width: 100%; max-width: 900px; margin-bottom: 30px; }
        .stat-card { 
            flex: 1; background: white; padding: 25px; border-radius: 18px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
            border-left: 5px solid var(--primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.08); }
        .stat-card h4 { margin: 0; color: var(--gray); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 700; }
        .stat-card p { margin: 10px 0 0; font-size: 1.7rem; font-weight: 800; color: var(--dark); }
        .stat-icon { font-size: 2rem; opacity: 0.2; color: var(--dark); }

        .clock-card { 
            width: 100%; max-width: 500px; background: white; padding: 45px; 
            border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.06); text-align: center; 
        }
        #live-timer { font-size: 4.8rem; font-weight: 900; color: var(--dark); margin: 15px 0; font-family: 'Courier New', monospace; }
        
        /* NEW: Progress Bar Styling */
        .progress-container { background: #eee; border-radius: 10px; height: 10px; margin: 20px 0; overflow: hidden; width: 100%; }
        .progress-fill { height: 100%; background: var(--success); transition: width 1s ease-in-out; }

        .btn-clock { 
            width: 100%; padding: 20px; font-size: 1.2rem; border-radius: 15px; border: none; 
            cursor: pointer; font-weight: bold; transition: all 0.3s ease; color: white;
            text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-in { background: var(--success); box-shadow: 0 8px 15px rgba(0, 184, 148, 0.2); }
        .btn-in:hover { background: #00a087; transform: translateY(-2px); }
        .btn-out { background: var(--danger); box-shadow: 0 8px 15px rgba(214, 48, 49, 0.2); }
        .btn-out:hover { background: #c02728; transform: translateY(-2px); }

        .early-box { 
            background: #fff9db; border: 1px solid #fab005; padding: 20px; 
            border-radius: 15px; margin-bottom: 25px; text-align: left; 
        }
        .reason-input { width: 100%; margin-top: 10px; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; box-sizing: border-box; }

        /* NEW: Team Feed Styling */
        .team-feed { width: 100%; max-width: 900px; margin-top: 30px; display: flex; flex-direction: column; align-items: flex-start; }
        .user-dot { height: 10px; width: 10px; background: var(--success); border-radius: 50%; display: inline-block; margin-right: 8px; box-shadow: 0 0 5px var(--success); }

        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.4); backdrop-filter: blur(8px); }
        .modal-content { background:white; margin:5% auto; padding:35px; border-radius:25px; width:90%; max-width:550px; box-shadow:0 25px 50px rgba(0,0,0,0.2); animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

<div class="dashboard-container" style="display: flex;">
    <?php include 'includes/employee-sidebar.php'; ?>

    <div class="main-content" style="flex: 1;">
        
        <div class="portal-header">
            <div>
                <h1>Attendance Portal</h1>
                <p><i class="fa-solid fa-circle-check"></i> Welcome back, <strong><?php echo htmlspecialchars($employee['first_name']); ?>!</strong></p>
            </div>
            <div style="text-align: right; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 25px; z-index: 1;">
                <div style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.8;">Date Today</div>
                <div style="font-size: 1.3rem; font-weight: bold;"><?php echo date('l, M d'); ?></div>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card" style="border-left-color: var(--primary);">
                <div>
                    <h4>Status</h4>
                    <p style="color: var(--primary);">
                        <?php echo ($today_record && $today_record['check_out_time']) ? 'Finished' : ($today_record ? 'Working' : 'Off-Duty'); ?>
                    </p>
                </div>
                <i class="fa-solid fa-user-clock stat-icon"></i>
            </div>
            
            <div class="stat-card" style="border-left-color: var(--success); cursor: pointer;" onclick="openHistory()">
                <div>
                    <h4>Month Count</h4>
                    <p><?php echo $days_worked; ?> Days</p>
                    <small style="color: var(--success); font-weight:bold;">Log History <i class="fa-solid fa-arrow-right"></i></small>
                </div>
                <i class="fa-solid fa-calendar-check stat-icon"></i>
            </div>

            <div class="stat-card" style="border-left-color: #f39c12;">
                <div>
                    <h4>Team Online</h4>
                    <p><?php echo count($others_in); ?> Peers</p>
                </div>
                <i class="fa-solid fa-users stat-icon"></i>
            </div>
        </div>

        <div class="clock-card">
            <div id="live-timer">00:00:00</div>

            <?php if ($today_record && empty($today_record['check_out_time'])): ?>
                <div style="text-align: left; margin-bottom: 20px;">
                    <span style="font-size: 0.8rem; color: var(--gray); font-weight: bold;">SHIFT PROGRESS: <?php echo $progress_percent; ?>%</span>
                    <div class="progress-container">
                        <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div style="background: #e3fcef; color: #00a087; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-weight:bold; border: 1px solid #c3e6cb;">
                    <i class="fa-solid fa-sparkles"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="attendanceForm">
                <input type="hidden" name="latitude" id="lat_input">
                <input type="hidden" name="longitude" id="lng_input">

                <?php if (!$today_record): ?>
                    <button type="submit" name="btn_checkin" class="btn-clock btn-in">
                        <i class="fa-solid fa-play"></i> START SHIFT
                    </button>
                    <p id="gps-status" style="font-size: 0.7rem; color: var(--gray); margin-top: 10px;">Finding location...</p>
                
                <?php elseif ($today_record && empty($today_record['check_out_time'])): ?>
                    <div style="margin-bottom: 25px; padding: 15px; background: #f0f7ff; border-radius: 12px; color: #007bff; font-weight: 600;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Shift started at: <?php echo date('h:i A', strtotime($today_record['check_in_time'])); ?>
                    </div>

                    <?php if ($is_early_leave): ?>
                        <div class="early-box">
                            <strong style="color: #f08c00;"><i class="fa-solid fa-triangle-exclamation"></i> Leaving Early?</strong>
                            <p style="font-size: 0.9rem; margin: 8px 0;">Reason required for departure before 8 hours.</p>
                            <textarea name="early_leave_reason" class="reason-input" rows="3" placeholder="Type reason here..." required></textarea>
                        </div>
                    <?php endif; ?>

                    <button type="submit" name="btn_checkout" class="btn-clock btn-out">
                        <i class="fa-solid fa-stop"></i> END SHIFT
                    </button>
                
                <?php else: ?>
                    <div style="background: #f8f9fa; padding: 30px; border-radius: 20px; border: 2px dashed #e0e0e0;">
                        <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: var(--success); margin-bottom: 10px;"></i>
                        <h3 style="color: var(--success); margin: 0;">Shift Completed!</h3>
                        <p style="margin-top: 12px; color: var(--gray);">Enjoy your evening.</p>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="team-feed">
            <h4 style="margin-bottom: 15px; color: var(--dark); border-bottom: 2px solid #eee; width: 100%; padding-bottom: 10px;">
                <i class="fa-solid fa-users-viewfinder"></i> WHO'S WORKING NOW
            </h4>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <?php if(empty($others_in)): ?>
                    <span style="color: var(--gray); font-style: italic;">You are currently the only one active.</span>
                <?php else: foreach($others_in as $peer): ?>
                    <div style="background: white; padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); font-size: 0.9rem; font-weight: 600;">
                        <span class="user-dot"></span> <?php echo $peer['first_name']; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

    </div>
</div>

<div id="historyModal" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:20px; margin-bottom:20px;">
            <h2 style="margin:0;"><i class="fa-solid fa-history"></i> Recent Logs</h2>
            <button onclick="closeHistory()" style="background:none; border:none; font-size:2.5rem; cursor:pointer; color:#ccc;">&times;</button>
        </div>
        <div style="max-height:400px; overflow-y:auto;">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <tbody id="historyBody">
                    <?php
                    $stmt_history = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date >= ? ORDER BY date DESC LIMIT 10");
                    $stmt_history->execute([$emp_id, $month_start]);
                    while($row = $stmt_history->fetch(PDO::FETCH_ASSOC)):
                        // NEW: Logic for Alerts
                        $is_late = (strtotime($row['check_in_time']) > strtotime('09:15:00'));
                        $time_color = $is_late ? 'var(--warning)' : 'var(--gray)';
                    ?>
                    <tr>
                        <td style="padding:15px; border-bottom:1px solid #f4f7f6;">
                            <strong><?php echo date('M d', strtotime($row['date'])); ?></strong>
                            <?php if($is_late): ?> <small style="display:block; color:orange; font-size:0.6rem;">LATE ARRIVAL</small> <?php endif; ?>
                        </td>
                        <td style="padding:15px; border-bottom:1px solid #f4f7f6; color:<?php echo $time_color; ?>; text-align:right;">
                            <?php echo date('h:i A', strtotime($row['check_in_time'])); ?> — 
                            <?php echo $row['check_out_time'] ? date('h:i A', strtotime($row['check_out_time'])) : '<span style="color:var(--primary)">Active</span>'; ?>
                            
                            <?php if($row['latitude']): ?>
                                <a href="http://google.com/maps?q=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank" style="margin-left: 10px; color: var(--primary);">
                                    <i class="fa-solid fa-location-dot"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <button onclick="closeHistory()" style="width:100%; margin-top:25px; padding:15px; background:var(--light-bg); border:none; border-radius:12px; cursor:pointer; font-weight:bold;">Close</button>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ":" + 
                        now.getMinutes().toString().padStart(2, '0') + ":" + 
                        now.getSeconds().toString().padStart(2, '0');
        document.getElementById('live-timer').textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // NEW: GPS Location Capture
    window.onload = function() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat_input').value = position.coords.latitude;
                document.getElementById('lng_input').value = position.coords.longitude;
                const status = document.getElementById('gps-status');
                if(status) status.innerHTML = "Location Verified <i class='fa-solid fa-check'></i>";
            });
        }
    };

    function openHistory() { document.getElementById('historyModal').style.display = 'block'; }
    function closeHistory() { document.getElementById('historyModal').style.display = 'none'; }
    window.onclick = function(e) { if(e.target == document.getElementById('historyModal')) closeHistory(); }
</script>

</body>
</html>