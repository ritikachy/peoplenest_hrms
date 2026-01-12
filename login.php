<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $emp_id = $_POST['emp_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password, $emp_id)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin-dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid credentials provided';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeopleNest - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Add these styles directly here or move them to style.css */
        .login-tabs {
            display: flex;
            background: #f0f2f5;
            border-radius: 8px;
            padding: 5px;
            margin-bottom: 25px;
        }
        .tab-btn {
            flex: 1;
            border: none;
            background: none;
            padding: 12px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
            border-radius: 6px;
        }
        .tab-btn.active {
            background: #fff;
            color: #764ba2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <h1>PeopleNest</h1>
                    <p>Human Resource Management System</p>
                </div>
            </div>
            
            <form class="login-form" method="POST" id="loginForm">
                <div class="login-tabs">
                    <button type="button" class="tab-btn active" onclick="switchLogin('employee', this)">Employee</button>
                    <button type="button" class="tab-btn" onclick="switchLogin('admin', this)">Admin</button>
                </div>

                <h2>Welcome Back</h2>
                <p class="login-subtitle">Sign in to your account</p>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="form-group" id="empIdGroup">
                    <label for="emp_id" id="empLabel">Employee ID</label>
                    <input type="text" id="emp_id" name="emp_id" placeholder="e.g. Emp001" required>
                </div>

                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
                
                
            </form>
        </div>
    </div>

    <script>  
    function switchLogin(role, btn) {
        // 1. Highlight the selected tab
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const empInput = document.getElementById('emp_id');
        const userInput = document.getElementById('username');
        const empLabel = document.getElementById('empLabel');
        const demoText = document.getElementById('demoText');

        if (role === 'admin') {
            // 2. Set Admin values to match your database exactly
            empLabel.innerText = "Admin ID";
            empInput.value = 'EMP001';  // Matches your DB screenshot
            userInput.value = 'admin';   // Matches your DB username
            demoText.innerHTML = "<strong>Admin Demo:</strong> EMP001 / admin / password";
        } else {
            // 3. Reset fields for Employee login
            empLabel.innerText = "Employee ID";
            empInput.value = ''; 
            userInput.value = '';
            // Updated demo text for your working employee
            demoText.innerHTML = "<strong>Employee Demo:</strong> Emp105 / ritikachy002@peoplenest.com / password";
        }
    }
</script>
    
</body>
</html>