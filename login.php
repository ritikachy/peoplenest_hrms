<?php
require_once 'includes/auth.php';

// Redirect if already logged in based on role
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
    
    // Core logic: Attempt login through your existing Auth class
    if ($auth->login($username, $password, $emp_id)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin-dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid credentials provided. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeopleNest | Secure Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pn-navy: #1a202c;       /* Matches Dashboard Sidebar */
            --pn-green: #2d7a32;      /* Brand Green */
            --pn-bg: #f8fafc;         /* Light Gray BG */
            --pn-border: #edf2f7;
            --text-main: #2d3748;
            --text-muted: #718096;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: var(--pn-navy); /* Dark background makes the white card pop */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Branding */
        .brand-header { text-align: center; margin-bottom: 30px; }
        .logo { 
            font-size: 1.8rem; font-weight: 800; color: var(--pn-navy); 
            display: flex; align-items: center; justify-content: center; gap: 10px; 
            text-decoration: none; margin-bottom: 10px;
        }
        .logo-box { background: var(--pn-navy); color: var(--pn-green); padding: 5px 8px; border-radius: 8px; }

        /* Role Switcher (Matching Dashboard Tabs) */
        .login-tabs {
            display: flex;
            background: var(--pn-bg);
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 30px;
            border: 1px solid var(--pn-border);
        }
        .tab-btn {
            flex: 1; border: none; background: none; padding: 12px;
            cursor: pointer; font-weight: 700; color: var(--text-muted);
            transition: 0.3s; border-radius: 10px; font-size: 0.9rem;
        }
        .tab-btn.active {
            background: white; color: var(--pn-navy);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Form Elements */
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { 
            display: block; font-size: 0.85rem; font-weight: 700; 
            color: var(--pn-navy); margin-bottom: 8px; 
        }
        .form-group input {
            width: 100%; padding: 12px 16px; border: 1.5px solid var(--pn-border);
            border-radius: 10px; background: var(--pn-bg); font-size: 0.95rem;
            outline: none; transition: 0.3s;
        }
        .form-group input:focus {
            border-color: var(--pn-green); background: white;
            box-shadow: 0 0 0 4px rgba(45, 122, 50, 0.1);
        }

        .alert-error {
            background: #fff5f5; color: #c53030; padding: 12px;
            border-radius: 10px; border-left: 4px solid #c53030;
            font-size: 0.85rem; margin-bottom: 20px; font-weight: 600;
        }

        .btn-signin {
            width: 100%; padding: 14px; background: var(--pn-green);
            color: white; border: none; border-radius: 10px;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            transition: 0.3s; margin-top: 10px;
        }
        .btn-signin:hover { background: #235e27; transform: translateY(-2px); }

        .demo-hint {
            margin-top: 25px; padding: 15px; background: #f0fdf4;
            border: 1px dashed var(--pn-green); border-radius: 10px;
            font-size: 0.8rem; color: #166534;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <div class="logo">
            <div class="logo-box"><i class="fas fa-cubes"></i></div>
            People<span style="color: var(--pn-green)">Nest</span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Access your HR workspace</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
        <div class="login-tabs">
            <button type="button" class="tab-btn active" onclick="switchLogin('employee', this)">Employee</button>
            <button type="button" class="tab-btn" onclick="switchLogin('admin', this)">Administrator</button>
        </div>

        <div class="form-group">
            <label id="empLabel">Employee ID</label>
            <input type="text" id="emp_id" name="emp_id" placeholder="e.g. Emp105" required>
        </div>

        <div class="form-group">
            <label>Username or Email</label>
            <input type="text" id="username" name="username" placeholder="name@peoplenest.com" required>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="btn-signin">Sign In to Dashboard</button>
    </form>

    <div class="demo-hint" id="demoHint">
        <strong>Employee Access:</strong><br>
        Emp105 / ritikachy002@peoplenest.com
    </div>

    <p style="margin-top: 25px; font-size: 0.8rem; color: var(--text-muted); text-align: center;">
        Forgot credentials? <a href="#" style="color: var(--pn-green); text-decoration: none; font-weight: 600;">Contact HR Support</a>
    </p>
</div>

<script>
function switchLogin(role, btn) {
    // UI: Toggle Active Class
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const empLabel = document.getElementById('empLabel');
    const empInput = document.getElementById('emp_id');
    const userInput = document.getElementById('username');
    const demoHint = document.getElementById('demoHint');

    if (role === 'admin') {
        empLabel.innerText = "Admin ID";
        empInput.value = 'EMP001'; // Fills demo data for your admin
        userInput.value = 'admin';
        demoHint.innerHTML = "<strong>Administrator Access:</strong><br>EMP001 / admin / password";
        demoHint.style.background = "#eff6ff";
        demoHint.style.borderColor = "#3b82f6";
        demoHint.style.color = "#1e40af";
    } else {
        empLabel.innerText = "Employee ID";
        empInput.value = ''; 
        userInput.value = '';
        demoHint.innerHTML = "<strong>Employee Access:</strong><br>Emp105 / ritikachy002@peoplenest.com";
        demoHint.style.background = "#f0fdf4";
        demoHint.style.borderColor = "var(--pn-green)";
        demoHint.style.color = "#166534";
    }
}
</script>

</body>
</html>