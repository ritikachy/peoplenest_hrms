<?php
require_once 'config/session.php';
requireAdmin();

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $date = $_POST['attendance_date'];
    $attendanceData = $_POST['attendance'];

    foreach ($attendanceData as $employeeId => $status) {
        if (!empty($status)) {
            // Check if attendance already exists
            $checkQuery = "SELECT id FROM attendance WHERE employee_id = ? AND date = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->execute([$employeeId, $date]);

            if ($checkStmt->fetch()) {
                // Update existing attendance
                $updateQuery = "UPDATE attendance SET status = ?, created_by = ? WHERE employee_id = ? AND date = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->execute([$status, $_SESSION['user_id'], $employeeId, $date]);
            } else {
                // Insert new attendance
                $insertQuery = "INSERT INTO attendance (employee_id, date, status, created_by) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute([$employeeId, $date, $status, $_SESSION['user_id']]);
            }
        }
    }
    $success = "Attendance marked successfully!";
}

// Get selected date (default to today)
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get all active employees
$query = "SELECT * FROM employees WHERE status = 'active' ORDER BY first_name, last_name";
$stmt = $conn->prepare($query);
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get attendance for selected date
$attendanceQuery = "SELECT employee_id, status FROM attendance WHERE date = ?";
$attendanceStmt = $conn->prepare($attendanceQuery);
$attendanceStmt->execute([$selectedDate]);
$attendanceData = [];
while ($row = $attendanceStmt->fetch(PDO::FETCH_ASSOC)) {
    $attendanceData[$row['employee_id']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title">Attendance Management</h1>
                <div class="user-menu">
                    <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
                </div>
            </div>

            <div class="content-area">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mark Attendance</h3>
                        <div>
                            <input type="date" id="dateSelector" value="<?php echo $selectedDate; ?>" onchange="changeDate()">
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="attendance_date" value="<?php echo $selectedDate; ?>">
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($employee['department']); ?></td>
                                            <td>
                                                <select name="attendance[<?php echo $employee['id']; ?>]" class="form-control">
                                                    <option value="">Select Status</option>
                                                    <option value="present" <?php echo (isset($attendanceData[$employee['id']]) && $attendanceData[$employee['id']] === 'present') ? 'selected' : ''; ?>>Present</option>
                                                    <option value="absent" <?php echo (isset($attendanceData[$employee['id']]) && $attendanceData[$employee['id']] === 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                    <option value="leave" <?php echo (isset($attendanceData[$employee['id']]) && $attendanceData[$employee['id']] === 'leave') ? 'selected' : ''; ?>>Leave</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-20">
                                <button type="submit" name="mark_attendance" class="btn btn-primary">Save Attendance</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function changeDate() {
            const date = document.getElementById('dateSelector').value;
            window.location.href = '?date=' + date;
        }
    </script>
</body>
</html>