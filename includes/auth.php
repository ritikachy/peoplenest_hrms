<?php
require_once 'config/database.php';
require_once 'config/session.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Updated login function: Only requires Employee ID and Password.
     * This follows the teacher's suggestion to remove the redundant username field.
     */
    public function login($emp_id, $password) {
        // Updated Query: Removed checks for username and email. 
        // Only filters by the unique emp_id.
        $query = "SELECT u.*, e.id as employee_id, e.first_name, e.last_name 
                  FROM users u 
                  LEFT JOIN employees e ON u.emp_id = e.employee_id 
                  WHERE u.emp_id = ?";
        
        $stmt = $this->conn->prepare($query);
        // Execute only with the Employee ID
        $stmt->execute([$emp_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Validate password against the hash in the database
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables for the application
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['emp_id'] = $user['emp_id']; 
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            
            // Set a display name for the dashboard
            if (!empty($user['first_name'])) {
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            } else {
                // Fallback: If no name exists (like for Admin), use the ID
                $_SESSION['full_name'] = $user['emp_id']; 
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Updated Register function: Removed the username requirement.
     */
    public function register($email, $password, $emp_id, $role = 'employee') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Removed 'username' from the columns and values
        $query = "INSERT INTO users (email, password, emp_id, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([$email, $hashedPassword, $emp_id, $role]);
        } catch (PDOException $e) {
            // In case of duplicate email or emp_id
            return false;
        }
    }
}
?>