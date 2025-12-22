<?php
require_once 'config/session.php';
requireAdmin();

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $query = "INSERT INTO candidates (name, email, phone, position, experience_years, status, created_by) 
                          VALUES (?, ?, ?, ?, ?, 'pending', ?)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['position'],
                    $_POST['experience_years'],
                    $_SESSION['user_id']
                ]);
                $success = "Candidate added successfully!";
                break;
                
            case 'update':
                $query = "UPDATE candidates SET name=?, email=?, phone=?, position=?, experience_years=?, status=?, interview_date=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['position'],
                    $_POST['experience_years'],
                    $_POST['status'],
                    $_POST['interview_date'] ?: null,
                    $_POST['candidate_id']
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

// Get all candidates
$query = "SELECT * FROM candidates ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Recruitment Management</h1>
                <div class="user-menu">
                    <button class="btn btn-primary" onclick="openModal('addCandidateModal')">Add Candidate</button>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Candidates</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Position</th>
                                        <th>Experience</th>
                                        <th>Status</th>
                                        <th>Interview Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidates as $candidate): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($candidate['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                        <td><?php echo $candidate['experience_years']; ?> years</td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $candidate['status'] === 'pending' ? 'warning' : 
                                                    ($candidate['status'] === 'interview_scheduled' ? 'info' : 
                                                    ($candidate['status'] === 'selected' ? 'success' : 'danger')); 
                                            ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $candidate['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $candidate['interview_date'] ? date('M d, Y h:i A', strtotime($candidate['interview_date'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-secondary btn-sm" onclick="editCandidate(<?php echo htmlspecialchars(json_encode($candidate)); ?>)">Edit</button>
                                            <a href="?delete=<?php echo $candidate['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
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

    <!-- Add Candidate Modal -->
    <div id="addCandidateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Candidate</h3>
                <button class="close" onclick="closeModal('addCandidateModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="experience_years">Years of Experience</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" max="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCandidateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Candidate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Candidate Modal -->
    <div id="editCandidateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Candidate</h3>
                <button class="close" onclick="closeModal('editCandidateModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_candidate_id" name="candidate_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_name">Full Name</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="edit_position">Position</label>
                            <input type="text" id="edit_position" name="position" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_experience">Years of Experience</label>
                            <input type="number" id="edit_experience" name="experience_years" min="0" max="50">
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="interview_scheduled">Interview Scheduled</option>
                                <option value="selected">Selected</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_interview_date">Interview Date & Time</label>
                        <input type="datetime-local" id="edit_interview_date" name="interview_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editCandidateModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Candidate</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
