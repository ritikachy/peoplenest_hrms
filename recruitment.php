<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
// Fetch current status
$status_stmt = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'");
$current_status = $status_stmt->fetchColumn();

// Handle the button click
if (isset($_POST['toggle_hiring'])) {
    $new_status = ($current_status === 'open') ? 'closed' : 'open';
    $update = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'recruitment_status'");
    $update->execute([$new_status]);
    header("Location: recruitment.php"); // Refresh page
    exit();
}

// --- 1. HANDLE STATUS FILTERS (SRS: Talent Bank Logic) ---
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

// --- 2. HANDLE FORM SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $resume_path = null;
                // Handle File Upload
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
                $success = "Candidate added successfully!";
                break;
            case 'update':
                $query = "UPDATE candidates SET name=?, email=?, phone=?, position=?, experience_years=?, status=?, interview_date=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['name'], $_POST['email'], $_POST['phone'], $_POST['position'], 
                    $_POST['experience_years'], $_POST['status'], 
                    $_POST['interview_date'] ?: null, $_POST['candidate_id']
                ]);
                $success = "Candidate updated successfully!";
                break;
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $query = "DELETE FROM candidates WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['delete']]);
    $success = "Candidate deleted successfully!";
}

// --- 3. FETCH DATA (Updated for Monthly Filter & Bridge Check) ---
$monthFilter = isset($_GET['filter']) && $_GET['filter'] === 'this_month';

if ($statusFilter) {
    // 1. Show specific status (Pending, Selected, etc.)
    $query = "SELECT * FROM candidates WHERE status = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$statusFilter]);
} elseif ($monthFilter) {
    // 2. TEACHER'S REQUEST: Show everyone who applied this month
    $query = "SELECT * FROM candidates 
              WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
              ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
} else {
    // 3. DEFAULT VIEW: Hide hired (via bridge) and hide rejected
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
        .filter-group { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .badge-pending { background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-interview_scheduled { background: #17a2b8; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-selected { background: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-rejected { background: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        /* Ensure the modal actually shows up when triggered */
.modal { 
    display: none; 
    position: fixed; 
    z-index: 1000; 
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
        <div class="top-bar">
    <h1 class="page-title">Recruitment Pipeline</h1>
    <div class="user-menu" style="display: flex; gap: 10px; align-items: center;">
        <form method="POST" style="margin: 0;">
            <button type="submit" name="toggle_hiring" class="btn <?php echo ($current_status === 'open' ? 'btn-danger' : 'btn-success'); ?> btn-sm" style="padding: 8px 15px;">
                <?php echo ($current_status === 'open' ? 'Stop Applications' : 'Start Applications'); ?>
            </button>
        </form>

        <button class="btn btn-primary" onclick="openModal('addCandidateModal')">Add Candidate</button>
        <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
    </div>
</div>
            
            <div class="content-area">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success" style="padding:15px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:20px;"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="filter-group">
                    <a href="recruitment.php" class="btn btn-secondary btn-sm">All Applicants</a>
                    <a href="recruitment.php?status=pending" class="btn btn-secondary btn-sm">New</a>
                    <a href="recruitment.php?filter=this_month" class="btn btn-info btn-sm" style="background-color: #17a2b8; border: none;">
                     Applied This Month
                    </a>
                    <a href="recruitment.php?status=interview_scheduled" class="btn btn-secondary btn-sm">Interviews</a>
                    <a href="recruitment.php?status=selected" class="btn btn-secondary btn-sm">Selected</a>
                    <a href="recruitment.php?status=rejected" class="btn btn-danger btn-sm">Talent Bank (Rejected)</a>
                </div>

                <div class="card">
                    <div class="card-body">
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
        <div style="display: flex; gap: 8px; align-items: center;">
    <?php if (!empty($candidate['resume_path'])): ?>
        <a href="<?php echo htmlspecialchars($candidate['resume_path']); ?>" 
           target="_blank" 
           class="btn btn-info btn-sm" 
           style="background-color: #17a2b8; color: white; text-decoration: none; padding: 4px 8px; border-radius: 3px;"
           title="View Resume">
           📄 CV
        </a>
    <?php else: ?>
        <span style="font-size: 10px; color: #999; width: 45px; text-align: center;">No CV</span>
    <?php endif; ?>

    <button class="btn btn-secondary btn-sm" onclick='editCandidate(<?php echo json_encode($candidate); ?>)'>Edit</button>
    
    <?php if($candidate['status'] == 'selected'): ?>
        <a href="employee-management.php?hire_id=<?php echo $candidate['id']; ?>" 
           class="btn btn-success btn-sm" 
           style="background-color: #28a745; color: white; text-decoration: none; padding: 4px 8px; border-radius: 3px;">
           Hire Now
        </a>
    <?php endif; ?>
</div>
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
    <form method="POST" style="margin-bottom: 20px;">
    <span>Hiring is currently: <strong><?php echo strtoupper($current_status); ?></strong></span>
    <button type="submit" name="toggle_hiring" class="btn <?php echo ($current_status === 'open' ? 'btn-danger' : 'btn-success'); ?> btn-sm">
        <?php echo ($current_status === 'open' ? 'Stop Applications' : 'Start Applications'); ?>
    </button>
</form>
    
    <div id="addCandidateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Candidate</h3>
                <button class="close" onclick="closeModal('addCandidateModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group"><label>Full Name</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Phone</label><input type="tel" name="phone"></div>
                        <div class="form-group"><label>Position</label><input type="text" name="position" required></div>
                    </div>
                    <div class="form-group"><label>Years of Experience</label><input type="number" name="experience_years" min="0"></div>
                    
                    <div class="form-group">
                        <label>Upload CV (PDF)</label>
                        <input type="file" name="resume" accept=".pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Candidate</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editCandidateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Candidate / Update Status</h3>
                <button class="close" onclick="closeModal('editCandidateModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_candidate_id" name="candidate_id">
                    
                    <div class="form-row">
                        <div class="form-group"><label>Full Name</label><input type="text" id="edit_name" name="name" required></div>
                        <div class="form-group"><label>Email</label><input type="email" id="edit_email" name="email" required></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Position</label><input type="text" id="edit_position" name="position" required></div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="edit_status" name="status">
                                <option value="pending">Pending</option>
                                <option value="interview_scheduled">Interview Scheduled</option>
                                <option value="selected">Selected</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Interview Date & Time</label>
                        <input type="datetime-local" id="edit_interview_date" name="interview_date">
                    </div>

                    <div class="form-group" id="cv_preview_container" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; display: none;">
                        <label style="display:block; margin-bottom:5px;">Attached Resume:</label>
                        <a id="edit_cv_link" href="#" target="_blank" class="btn btn-info btn-sm" style="background:#17a2b8; color:white; text-decoration:none;">📄 View PDF Resume</a>
                    </div>

                    <input type="hidden" id="edit_phone" name="phone">
                    <input type="hidden" id="edit_experience" name="experience_years">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
    // Essential functions to make the buttons work
    function openModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    function editCandidate(data) {
        document.getElementById('edit_candidate_id').value = data.id;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_position').value = data.position;
        document.getElementById('edit_status').value = data.status;
        document.getElementById('edit_phone').value = data.phone;
        document.getElementById('edit_experience').value = data.experience_years;
        
        const cvContainer = document.getElementById('cv_preview_container');
        const cvLink = document.getElementById('edit_cv_link');
        
        if (data.resume_path && data.resume_path !== "") {
            cvContainer.style.display = 'block';
            cvLink.href = data.resume_path;
        } else {
            cvContainer.style.display = 'none';
        }
        
        if(data.interview_date) {
            document.getElementById('edit_interview_date').value = data.interview_date.replace(" ", "T").substring(0, 16);
        } else {
            document.getElementById('edit_interview_date').value = "";
        }
        
        // Use the function defined above
        openModal('editCandidateModal');
    }

    // Close modal if user clicks outside of the box
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = "none";
        }
    }
</script>
</body>
</html>