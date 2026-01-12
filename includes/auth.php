<?php
require_once 'config/database.php';
require_once 'config/session.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Now requires three parameters
    public function login($username, $password, $emp_id) {
        // Query checks BOTH (username/email) AND the specific Employee ID
        $query = "SELECT u.*, e.id as employee_id, e.first_name, e.last_name 
                  FROM users u 
                  LEFT JOIN employees e ON u.emp_id = e.employee_id 
                  WHERE (u.username = ? OR u.email = ?) AND u.emp_id = ?";
        
        $stmt = $this->conn->prepare($query);
        // We pass the $username twice (for the OR check) and $emp_id once
        $stmt->execute([$username, $username, $emp_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['emp_id'] = $user['emp_id']; 
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            
            // Handle cases where Admin has no profile in employees table
            if (!empty($user['first_name'])) {
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            } else {
                $_SESSION['full_name'] = $user['username']; // Fallback for Admin
            }
            
            return true;
        }
        
        return false;
    }
    
    public function register($username, $email, $password, $emp_id, $role = 'employee') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, emp_id, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([$username, $email, $hashedPassword, $emp_id, $role]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>