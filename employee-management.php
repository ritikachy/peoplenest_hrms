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
                // 1️⃣ Create login user
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
                $userQuery = "INSERT INTO users (username, email, password, role)
                              VALUES (?, ?, ?, 'employee')";
                $userStmt = $conn->prepare($userQuery);
                $userStmt->execute([
                    $_POST['email'],   // username
                    $_POST['email'],
                    $hashedPassword
                ]);
            
                $user_id = $conn->lastInsertId();
            
                // 2️⃣ Create employee profile
                $empQuery = "INSERT INTO employees 
                    (user_id, employee_id, first_name, last_name, email, phone, department, designation, hire_date, salary)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
                $empStmt = $conn->prepare($empQuery);
                $empStmt->execute([
                    $user_id,
                    $_POST['employee_id'],
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['department'],
                    $_POST['designation'],
                    $_POST['hire_date'],
                    $_POST['salary']
                ]);
            
                $success = "Employee added & login created successfully!";
                break;
            
            case 'update':
                $query = "UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, department=?, designation=?, salary=? WHERE id=?";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['department'],
                    $_POST['designation'],
                    $_POST['salary'],
                    $_POST['employee_id']
                ]);
                $success = "Employee updated successfully!";
                break;
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $query = "UPDATE employees SET status = 'inactive' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_GET['delete']]);
    $success = "Employee deactivated successfully!";
}

// Get all employees
$query = "SELECT * FROM employees WHERE status = 'active' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Employees</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Hire Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-secondary btn-sm" onclick="editEmployee(<?php echo htmlspecialchars(json_encode($employee)); ?>)">Edit</button>
                                            <a href="?delete=<?php echo $employee['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
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

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Employee</h3>
                <button class="close" onclick="closeModal('addEmployeeModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="employee_id">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required>
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
                            <label for="department">Department</label>
                            <select id="department" name="department" required>
                                <option value="">Select Department</option>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="designation">Designation</label>
                            <input type="text" id="designation" name="designation" required>
                        </div>
                        <div class="form-group">
                            <label for="hire_date">Hire Date</label>
                            <input type="date" id="hire_date" name="hire_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="number" id="salary" name="salary" step="0.01">
                    </div>
                </div>
                    <div class="form-group">
                        <label for="password">Login Password</label>
                        <input type="password" id="password" name="password" required>
                   </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addEmployeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Employee</h3>
                <button class="close" onclick="closeModal('editEmployeeModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="edit_employee_id" name="employee_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_first_name">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_phone">Phone</label>
                            <input type="tel" id="edit_phone" name="phone">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_department">Department</label>
                            <select id="edit_department" name="department" required>
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_designation">Designation</label>
                            <input type="text" id="edit_designation" name="designation" required>
                        </div>
                    </div>
                        <div class="form-group">
                            <label for="password">Login Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                     
                    <div class="form-group">
                        <label for="edit_salary">Salary</label>
                        <input type="number" id="edit_salary" name="salary" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editEmployeeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
