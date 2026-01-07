<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin-dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeopleNest - Human Resource Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6dd669 0%, #129f3c 100%);
            min-height: 100vh;
            color: white;
        }

        .landing-container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .logo-large h1 {
            font-size: 4rem;
        }

        .logo-large p {
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .feature-item {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
        }

        .btn {
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
            border: 2px solid white;
            display: inline-block;
            margin: 10px;
        }

        .btn:hover {
            background: rgba(255,255,255,0.2);
        }

        footer {
            text-align: center;
            padding: 30px;
            opacity: 0.8;
        }
    </style>
</head>

<body>
<div class="landing-container">

    <section class="hero-section">
        <div>
            <div class="logo-large">
                <h1>PeopleNest</h1>
                <p>Human Resource Management System</p>
            </div>

            <h2>Streamline Your HR Operations</h2>
            <p>Manage employees, attendance, leave and recruitment in one platform.</p>

            <div class="feature-grid">
                <div class="feature-item">👥 Employee Management</div>
                <div class="feature-item">📅 Attendance Tracking</div>
                <div class="feature-item">🏖️ Leave Management</div>
                <div class="feature-item">🎯 Recruitment</div>
            </div>

            <a href="login.php" class="btn">Get Started</a>
        </div>
    </section>

</div>

<footer>
    <p>&copy; 2024 PeopleNest. All rights reserved.</p>
</footer>
</body>
</html>
