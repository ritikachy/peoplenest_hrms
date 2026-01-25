<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';
require_once 'mailer_logic.php';

$database = new Database();
$conn = $database->getConnection();

// Fetch current hiring status
$status_stmt = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'");
$current_status = $status_stmt->fetchColumn();

// Handle the button click for hiring status
if (isset($_POST['toggle_hiring'])) {
    $new_status = ($current_status === 'open') ? 'closed' : 'open';
    $update = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'recruitment_status'");
    $update->execute([$new_status]);
    header("Location: recruitment.php");
    exit();
}

// 1. HANDLE STATUS FILTERS
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

// --- 2. HANDLE FORM SUBMISSIONS (CORRECTED) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $resume_path = null;
                if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
                    $upload_dir = 'assets/uploads/resumes/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $file_name = time() . '_' . $_FILES['resume']['name'];
                    $target_file = $upload_dir . $file_name;
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
                        $resume_path = $target_file;
                    }
                }
            
                $query = "INSERT INTO candidates (name, email, phone, position, experience_years, resume_path, status, created_by) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['name'], $_POST['email'], $_POST['phone'], 
                    $_POST['position'], $_POST['experience_years'], $resume_path, $_SESSION['user_id']
                ]);
        
                $welcome_msg = "Hello " . $_POST['name'] . ", we have received your application for the " . $_POST['position'] . " position.";
                
                // Log and Send Email
                $log_stmt = $conn->prepare("INSERT INTO communication_logs (recipient_email, candidate_name, subject, message) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$_POST['email'], $_POST['name'], "Application Received", $welcome_msg]);
                sendCandidateEmail($_POST['email'], $_POST['name'], "Application Received - PeopleNest", $welcome_msg);
        
                $success = "Candidate added & Email Sent!";
                break; // Properly end the 'add' case

            case 'update':
                $query = "UPDATE candidates SET name=?, email=?, phone=?, position=?, experience_years=?, status=?, interview_date=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['position'], 
                    $_POST['experience_years'], $_POST['status'], 
                    $_POST['interview_date'] ?: null, $_POST['candidate_id']
                ]);
        
                // Prepare Dynamic Message based on Status
                $candidate_name = $_POST['name'];
                $candidate_email = $_POST['email'];
                $position = $_POST['position'];

                if ($_POST['status'] === 'rejected') {
                    $subject = "Update regarding your application at PeopleNest";
                    $status_msg = "Dear $candidate_name, thank you for applying for the $position position. After careful consideration, we have decided to move forward with other candidates at this time.";
                } elseif ($_POST['status'] === 'interview_scheduled') {
                    $subject = "Interview Scheduled - PeopleNest";
                    $status_msg = "Great news! Your interview for the $position position is set for " . $_POST['interview_date'] . ".";
                } else {
                    $subject = "Application Status Update";
                    $status_msg = "Your application status for $position has been updated to: " . ucwords(str_replace('_', ' ', $_POST['status']));
                }
        
                // 1. LOG TO DATABASE
                $log_stmt = $conn->prepare("INSERT INTO communication_logs (recipient_email, candidate_name, subject, message) VALUES (?, ?, ?, ?)");
                $log_stmt->execute([$candidate_email, $candidate_name, $subject, $status_msg]);
        
                // 2. SEND REAL EMAIL
                sendCandidateEmail($candidate_email, $candidate_name, $subject, $status_msg);
        
                $success = "Status updated & Email Sent!";
                break;
        }
    }
}
// 3. FETCH DATA
$monthFilter = isset($_GET['filter']) && $_GET['filter'] === 'this_month';

if ($statusFilter) {
    $query = "SELECT * FROM candidates WHERE status = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$statusFilter]);
} elseif ($monthFilter) {
    $query = "SELECT * FROM candidates 
              WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
              ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
} else {
    $query = "SELECT c.* FROM candidates c 
              LEFT JOIN candidate_employee ce ON c.id = ce.candidate_id 
              WHERE ce.candidate_id IS NULL 
              AND c.status != 'rejected' 
              ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
}
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    /* CRITICAL LAYOUT FIX */
    .dashboard-layout {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }

    .main-content {
        flex: 1;
        padding: 20px;
        background: #f8f9fa;
        min-width: 0;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    /* FIX FOR LONG LOGS: Scrollable area */
    .log-scroll-area {
        max-height: 450px; /* Limits height to ~6-7 rows */
        overflow-y: auto;
        border: 1px solid #eee;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .table th, .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }

    .msg-cell {
        max-width: 300px;
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.4;
    }

    /* Status Badges */
    .badge-pending { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-interview_scheduled { background: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-selected { background: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-rejected { background: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    
    /* Filter Button Colors */
    .filter-group { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn-filter-all { background: #6c757d !important; color: white !important; border: none; }
    .btn-filter-new { background: #ffc107 !important; color: black !important; border: none; }
    .btn-filter-month { background: #17a2b8 !important; color: white !important; border: none; }
    .btn-filter-interviews { background: #007bff !important; color: white !important; border: none; }
    .btn-filter-selected { background: #28a745 !important; color: white !important; border: none; }
    .btn-filter-rejected { background: #dc3545 !important; color: white !important; border: none; }

    .btn-sm { padding: 5px 10px; font-size: 12px; cursor: pointer; border-radius: 4px; text-decoration: none; display: inline-block; }
    
    .filter-group .btn:hover {
        opacity: 0.9;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .modal { 
        display: none; 
        position: fixed; 
        z-index: 2000; 
        left: 0; top: 0; 
        width: 100%; height: 100%; 
        background-color: rgba(0,0,0,0.5); 
    }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar" style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 class="page-title">Recruitment Pipeline</h1>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <form method="POST" style="margin: 0;">
                        <button type="submit" name="toggle_hiring" class="btn <?php echo ($current_status === 'open' ? 'btn-danger' : 'btn-success'); ?> btn-sm">
                            <?php echo ($current_status === 'open' ? 'Stop Applications' : 'Start Applications'); ?>
                        </button>
                    </form>
                    <button class="btn btn-primary btn-sm" onclick="openModal('addCandidateModal')">Add Candidate</button>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>

            <div class="content-area">
                <?php if (isset($success)): ?>
                    <div class="alert" style="padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="filter-group">
                    <a href="recruitment.php" class="btn btn-filter-all btn-sm">All Applicants</a>
                    <a href="recruitment.php?status=pending" class="btn btn-filter-new btn-sm">New</a>
                    <a href="recruitment.php?filter=this_month" class="btn btn-filter-month btn-sm">Applied This Month</a>
                    <a href="recruitment.php?status=interview_scheduled" class="btn btn-filter-interviews btn-sm">Interviews</a>
                    <a href="recruitment.php?status=selected" class="btn btn-filter-selected btn-sm">Selected</a>
                    <a href="recruitment.php?status=rejected" class="btn btn-filter-rejected btn-sm">Talent Bank (Rejected)</a>
                </div>

                <div class="card">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Exp</th>
                                    <th>Status</th>
                                    <th>Interview Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($candidate['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                    <td><?php echo $candidate['experience_years']; ?> Yrs</td>
                                    <td>
                                        <span class="badge-<?php echo $candidate['status']; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $candidate['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $candidate['interview_date'] ? date('M d, h:i A', strtotime($candidate['interview_date'])) : '-'; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <?php if (!empty($candidate['resume_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($candidate['resume_path']); ?>" target="_blank" class="btn btn-info btn-sm" title="View Resume">📄 CV</a>
                                            <?php endif; ?>
                                            <button class="btn btn-secondary btn-sm" onclick='editCandidate(<?php echo json_encode($candidate); ?>)'>Edit</button>
                                            <?php if($candidate['status'] == 'selected'): ?>
                                                <a href="employee-management.php?hire_id=<?php echo $candidate['id']; ?>" class="btn btn-success btn-sm">Hire</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card" style="margin-top: 40px;">
                    <div class="card-header" style="background: #343a40; color: white; padding: 12px; border-radius: 8px 8px 0 0;">
                        <h3 style="margin:0; font-size: 16px;">Outgoing Notifications (Email Logs)</h3>
                    </div>
                    <div class="table-container">
                        <div class="log-scroll-area"> <table class="table" style="font-size: 13px;">
                                <thead style="position: sticky; top: 0; background: #f4f4f4; z-index: 10;">
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Subject</th>
                                        <th>Message Content</th>
                                        <th>Sent At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Increased limit to 20 since we now have a scrollbar
                                    $logs_stmt = $conn->query("SELECT * FROM communication_logs ORDER BY sent_at DESC LIMIT 20");
                                    $logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($logs) > 0):
                                        foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($log['candidate_name']); ?></strong><br>
                                                <small style="color:#666;"><?php echo htmlspecialchars($log['recipient_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['subject']); ?></td>
                                            <td class="msg-cell"><?php echo htmlspecialchars($log['message']); ?></td>
                                            <td><?php echo date('M d, H:i', strtotime($log['sent_at'])); ?></td>
                                            <td><span class="badge-selected" style="background:#28a745;">✔ Delivered</span></td>
                                        </tr>
                                        <?php endforeach; 
                                    else: ?>
                                        <tr><td colspan="5" style="text-align:center;">No notifications sent yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
    </div>

    <div id="addCandidateModal" class="modal">
        <div class="modal-content" style="background:white; margin: 5% auto; padding: 20px; width: 50%; border-radius:8px;">
            <h3>Add New Candidate</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label>Full Name</label><input type="text" name="name" required style="width:100%;"></div>
                    <div><label>Email</label><input type="email" name="email" required style="width:100%;"></div>
                    <div><label>Phone</label><input type="tel" name="phone" style="width:100%;"></div>
                    <div><label>Position</label><input type="text" name="position" required style="width:100%;"></div>
                    <div><label>Experience (Yrs)</label><input type="number" name="experience_years" style="width:100%;"></div>
                    <div><label>CV (PDF)</label><input type="file" name="resume" accept=".pdf"></div>
                </div>
                <div style="margin-top:20px; text-align:right;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCandidateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Candidate</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editCandidateModal" class="modal">
        <div class="modal-content" style="background:white; margin: 5% auto; padding: 20px; width: 50%; border-radius:8px;">
            <h3>Update Candidate</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_candidate_id" name="candidate_id">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label>Name</label><input type="text" id="edit_name" name="name" required style="width:100%;"></div>
                    <div><label>Email</label><input type="email" id="edit_email" name="email" required style="width:100%;"></div>
                    <div><label>Position</label><input type="text" id="edit_position" name="position" style="width:100%;"></div>
                    <div><label>Status</label>
                        <select id="edit_status" name="status" style="width:100%;">
                            <option value="pending">Pending</option>
                            <option value="interview_scheduled">Interview Scheduled</option>
                            <option value="selected">Selected</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top:15px;">
                    <label>Interview Date & Time</label>
                    <input type="datetime-local" id="edit_interview_date" name="interview_date" style="width:100%;">
                </div>
                <input type="hidden" id="edit_phone" name="phone">
                <input type="hidden" id="edit_experience" name="experience_years">
                <div style="margin-top:20px; text-align:right;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editCandidateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = "block"; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = "none"; }

        function editCandidate(data) {
            document.getElementById('edit_candidate_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_position').value = data.position;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_experience').value = data.experience_years;
            
            if(data.interview_date) {
                document.getElementById('edit_interview_date').value = data.interview_date.replace(" ", "T").substring(0, 16);
            }
            openModal('editCandidateModal');
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>