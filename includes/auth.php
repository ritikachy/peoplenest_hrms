<?php
require_once 'config/database.php';
require_once 'config/session.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($username, $password) {
        $query = "SELECT u.*, e.id as employee_id, e.first_name, e.last_name 
                  FROM users u 
                  LEFT JOIN employees e ON u.id = e.user_id 
                  WHERE u.username = ? OR u.email = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            return true;
        }
        
        return false;
    }
    
    public function register($username, $email, $password, $role = 'employee') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        try {
            return $stmt->execute([$username, $email, $hashedPassword, $role]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
