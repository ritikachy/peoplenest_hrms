<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get all users with role 'employee'
$userQuery = "SELECT * FROM users WHERE role = 'employee'";
$userStmt = $conn->prepare($userQuery);
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    // Check if this user already has an employee record
    $checkQuery = "SELECT * FROM employees WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$user['id']]);
    $employee = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        // Create a new employee record
        $insertQuery = "INSERT INTO employees 
            (user_id, employee_id, first_name, last_name, email, phone, department, designation, hire_date, salary, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        // Generate unique employee ID (e.g., Emp + user_id)
        $employee_id = 'Emp' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
        $first_name = explode('@', $user['username'])[0]; // simple first name from email
        $last_name = ''; // leave empty if unknown
        $email = $user['email'];
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->execute([
            $user['id'],
            $employee_id,
            ucfirst($first_name),
            $last_name,
            $email,
            '',
            'IT',         // default department
            'Employee',   // default designation
            date('Y-m-d'),// hire date as today
            0             // default salary
        ]);

        echo "Created employee record for: {$email} <br>";
    } else {
        echo "Employee already exists for: {$user['email']} <br>";
    }
}
?>
