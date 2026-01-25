<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';
require_once 'mailer_logic.php';

$database = new Database();
$conn = $database->getConnection();

$success = null;
$error = null;

// --- 1. CHECK IF WE ARE HIRING A CANDIDATE ---
$hiring_data = null;
if (isset($_GET['hire_id'])) {
    $hire_id = $_GET['hire_id'];
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$hire_id]);
    $hiring_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check for redirect messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'added') $success = "Employee profile created and linked successfully!";
    if ($_GET['msg'] == 'updated') $success = "Employee updated successfully!";
    if ($_GET['msg'] == 'deleted') $success = "Employee deactivated successfully!";
}

// --- 2. AUTO-GENERATE EMPLOYEE ID ---
$idQuery = "SELECT MAX(CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)) as max_id FROM employees WHERE employee_id LIKE 'Emp%'";
$idStmt = $conn->prepare($idQuery);
$idStmt->execute();
$idRow = $idStmt->fetch(PDO::FETCH_ASSOC);
$nextNumber = ($idRow['max_id']) ? $idRow['max_id'] + 1 : 1;
$nextEmpId = 'Emp' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT); 

// --- 3. HANDLE FORM SUBMISSIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                try {
                    $conn->beginTransaction();

                    $firstName = $_POST['first_name'];
                    $lastName = $_POST['last_name'];
                    $userEmail = $_POST['email'];
                    $rawPassword = $_POST['password']; // Save this for the email
                    $cleanUsername = strtolower(trim($firstName . $lastName));
                    $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);
                    
                    // 1. Create User Account
                    $userQuery = "INSERT INTO users (username, email, password, emp_id, role) VALUES (?, ?, ?, ?, 'employee')";
                    $userStmt = $conn->prepare($userQuery);
                    $userStmt->execute([$cleanUsername, $userEmail, $hashedPassword, $_POST['employee_id']]);
                    $user_primary_id = $conn->lastInsertId();
                
                    // 2. Create Employee Profile
                    $empQuery = "INSERT INTO employees (user_id, employee_id, first_name, last_name, email, phone, department, designation, hire_date, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $empStmt = $conn->prepare($empQuery);
                    $empStmt->execute([
                        $user_primary_id, $_POST['employee_id'], $firstName, $lastName, 
                        $userEmail, $_POST['phone'], $_POST['department'], 
                        $_POST['designation'], $_POST['hire_date'], $_POST['salary']
                    ]);
                    $internal_emp_id = $conn->lastInsertId(); 
                
                    // 3. Connect Recruitment Bridge
                    if (isset($_GET['hire_id'])) {
                        $candidate_id = $_GET['hire_id'];
                        $bridgeStmt = $conn->prepare("INSERT INTO candidate_employee (candidate_id, employee_id) VALUES (?, ?)");
                        $bridgeStmt->execute([$candidate_id, $internal_emp_id]);
                
                        $updateCandidate = $conn->prepare("UPDATE candidates SET status = 'hired' WHERE id = ?");
                        $updateCandidate->execute([$candidate_id]);
                    }
                
                    // 4. SEND WELCOME EMAIL
                    $subject = "Welcome to PeopleNest, " . $firstName . "!";
                    $message = "
                        <h2>Welcome to the Team!</h2>
                        <p>Hi " . $firstName . ", we are excited to have you join our " . $_POST['department'] . " department.</p>
                        <p><strong>Your Login Credentials:</strong><br>
                        Username: " . $cleanUsername . "<br>
                        Password: " . $rawPassword . "</p>
                        <p>Please log in to the portal to view your dashboard.</p>
                    ";
                    
                    sendCandidateEmail($userEmail, $firstName . " " . $lastName, $subject, $message);

                    $conn->commit();
                    header("Location: employee-management.php?msg=added");
                    exit();

                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Failed to create employee: " . $e->getMessage();
                }
                break;
            case 'update':
                try {
                    $conn->beginTransaction(); 
                    $newUsername = strtolower(trim($_POST['first_name'] . $_POST['last_name']));
                    
                    $query = "UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, department=?, designation=?, salary=? WHERE employee_id=?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([
                        $_POST['first_name'], $_POST['last_name'], $_POST['email'], 
                        $_POST['phone'], $_POST['department'], $_POST['designation'], 
                        $_POST['salary'], $_POST['employee_id']
                    ]);

                    $userQuery = "UPDATE users SET username=?, email=? WHERE emp_id=?";
                    $userStmt = $conn->prepare($userQuery);
                    $userStmt->execute([$newUsername, $_POST['email'], $_POST['employee_id']]);

                    $conn->commit();
                    header("Location: employee-management.php?msg=updated");
                    exit();
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Update failed: " . $e->getMessage();
                }
                break;
        }
    }
}

// Handle deactivation
if (isset($_GET['delete'])) {
    $query = "UPDATE employees SET status = 'inactive' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['delete']]);
    header("Location: employee-management.php?msg=deleted");
    exit();
}

// --- 4. FETCH EMPLOYEES ---
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT e.*, ce.candidate_id 
          FROM employees e 
          LEFT JOIN candidate_employee ce ON e.id = ce.employee_id 
          WHERE e.status = 'active'";

if (!empty($searchTerm)) {
    $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.employee_id LIKE ?)";
    $query .= " ORDER BY e.created_at DESC";
    $stmt = $conn->prepare($query);
    $wildcard = "%$searchTerm%";
    $stmt->execute([$wildcard, $wildcard, $wildcard, $wildcard]);
} else {
    $query .= " ORDER BY e.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
}
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Employee Management</h1>
                <div class="user-menu">
                    <button class="btn btn-primary" onclick="openModal('addEmployeeModal')">Add Employee</button>
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success" style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="search-container" style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <form method="GET" action="employee-management.php" style="display: flex; gap: 10px;">
                        <input type="text" name="search" placeholder="Search by name, email, or ID..." 
                               value="<?php echo htmlspecialchars($searchTerm); ?>"
                               style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <?php if(!empty($searchTerm)): ?>
                            <a href="employee-management.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; padding: 0 15px;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>
                                    <?php if (!empty($employee['candidate_id'])): ?>
                                        <span class="badge" style="background: #e1f5fe; color: #01579b; font-size: 10px; margin-left: 5px;">HIRED</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($employee['department']); ?></span></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm" onclick='editEmployee(<?php echo json_encode($employee); ?>)'>Edit</button>
                                    <a href="?delete=<?php echo $employee['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this employee?')">Deactivate</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Employee</h3>
                <button class="close" onclick="closeModal('addEmployeeModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" value="<?php echo $nextEmpId; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?php echo $hiring_data ? explode(' ', $hiring_data['name'])[0] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?php 
                                if($hiring_data) {
                                    $parts = explode(' ', $hiring_data['name']);
                                    echo isset($parts[1]) ? $parts[1] : '';
                                } ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo $hiring_data ? $hiring_data['email'] : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Initial Login Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?php echo $hiring_data ? $hiring_data['phone'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" required>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" name="designation" value="<?php echo $hiring_data ? $hiring_data['position'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Hire Date</label>
                            <input type="date" name="hire_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Monthly Salary</label>
                        <input type="number" name="salary" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Profile</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Employee Details</h3>
                <button class="close" onclick="closeModal('editEmployeeModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="employee_id" id="edit_employee_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" id="edit_phone">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" id="edit_department" required>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Designation</label>
                            <input type="text" name="designation" id="edit_designation" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Salary</label>
                        <input type="number" name="salary" id="edit_salary" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        window.onload = function() {
            <?php if ($hiring_data): ?>
                openModal('addEmployeeModal');
            <?php endif; ?>
        };

        function editEmployee(emp) {
            document.getElementById('edit_employee_id').value = emp.employee_id;
            document.getElementById('edit_first_name').value = emp.first_name;
            document.getElementById('edit_last_name').value = emp.last_name;
            document.getElementById('edit_email').value = emp.email;
            document.getElementById('edit_phone').value = emp.phone;
            document.getElementById('edit_department').value = emp.department;
            document.getElementById('edit_designation').value = emp.designation;
            document.getElementById('edit_salary').value = emp.salary;
            openModal('editEmployeeModal');
        }
    </script>
</body>
</html>