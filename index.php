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

// Connect to database to check recruitment status
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

$status_query = "SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'";
$status_stmt = $conn->query($status_query);
$recruitment_open = ($status_stmt->fetchColumn() === 'open');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeopleNest | The Intelligently Simple HR Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #2d7a32;
            --light-green: #e8f5e9;
            --text-dark: #1a2e1c;
            --text-gray: #556b58;
            --white: #ffffff;
            --disabled-gray: #9ca3af;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Hero Background Decorations */
        .bg-shape {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 80%;
            background: radial-gradient(circle at top right, #e0f2f1 0%, transparent 70%);
            z-index: -1;
        }

        /* Navigation */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            background: rgba(255, 255, 255, 0.9);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-green);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .nav-links a:hover { color: var(--primary-green); }

        .nav-btns {
            display: flex;
            gap: 15px;
        }

        /* Buttons */
        .btn-outline {
            padding: 10px 25px;
            border: 2px solid var(--primary-green);
            color: var(--primary-green);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-filled {
            padding: 10px 25px;
            background: var(--primary-green);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            border: none;
        }

        .btn-filled:hover:not(.disabled) { background: #235d27; transform: translateY(-2px); }
        
        .btn-filled.disabled {
            background: var(--disabled-gray);
            cursor: not-allowed;
        }

        /* Hero Section */
        .hero {
            padding: 80px 8% 40px;
            text-align: center;
        }

        .trust-badge {
            background: var(--light-green);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            color: var(--primary-green);
            font-weight: 600;
            display: inline-block;
            margin-bottom: 25px;
        }
        
        .trust-badge.closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .hero h1 {
            font-size: 3.5rem;
            color: var(--primary-green);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        /* Feature Grid */
        .feature-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            padding: 40px 10%;
            margin-bottom: 60px;
        }

        .feature-card {
            text-align: center;
            padding: 20px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: var(--primary-green);
            border: 2px solid #c8e6c9;
            transition: 0.3s;
        }

        .feature-card:hover .icon-circle {
            transform: scale(1.1);
            background: var(--primary-green);
            color: white;
        }

        .feature-card h3 {
            font-size: 1rem;
            color: var(--text-dark);
        }

        /* Footer */
        footer {
            background: #2c332e;
            color: #bdc3bc;
            text-align: center;
            padding: 40px;
            font-size: 0.9rem;
        }
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>
    
    <?php if (isset($_GET['applied']) && $_GET['applied'] == 'success'): ?>
        <div style="background: var(--light-green); color: var(--primary-green); padding: 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #c8e6c9; position: relative; z-index: 1001;">
            <i class="fas fa-check-circle"></i> Application received! Our HR team will review your CV shortly.
            <a href="index.php" style="margin-left: 15px; color: var(--primary-green); text-decoration: none; font-size: 0.8rem; opacity: 0.7;">[Dismiss]</a>
        </div>
    <?php endif; ?>

    <div class="bg-shape"></div>

    <nav>
        <div class="logo">
            <i class="fas fa-leaf"></i> PeopleNest
        </div>
        <div class="nav-links">
            <a href="#features">Our Platform</a>
            <a href="apply.php">Careers</a> 
            <a href="#">About Us</a>
        </div>
        <div class="nav-btns">
            <a href="login.php" class="btn-outline">Staff Login</a>
            <?php if ($recruitment_open): ?>
                <a href="apply.php" class="btn-filled">Apply Now</a>
            <?php else: ?>
                <span class="btn-filled disabled">Hiring Paused</span>
            <?php endif; ?>
        </div>
    </nav>

<section class="hero">
    <div class="trust-badge <?php echo !$recruitment_open ? 'closed' : ''; ?>">
        <?php echo $recruitment_open ? 'We are currently hiring for 5+ positions!' : 'Recruitment is currently paused'; ?>
    </div>
    <h1>The Intelligently Simple <br> HR Platform</h1>
    <p>Manage people, not paperwork. Join the team that's building the future of the modern workforce.</p>
    
    <div style="display: flex; justify-content: center; gap: 15px;">
        <?php if ($recruitment_open): ?>
            <a href="apply.php" class="btn-filled" style="padding: 15px 40px; font-size: 1.1rem;">Explore Open Roles</a>
        <?php else: ?>
            <button class="btn-filled disabled" style="padding: 15px 40px; font-size: 1.1rem;">No Current Vacancies</button>
        <?php endif; ?>
        <a href="login.php" class="btn-outline" style="padding: 15px 40px; font-size: 1.1rem; border-radius: 50px;">Staff Portal</a>
    </div>
</section>

<div id="features" class="feature-container">
    <div class="feature-card">
        <div class="icon-circle"><i class="fas fa-users-cog"></i></div>
        <h3>Employee Management</h3>
    </div>
    <div class="feature-card">
        <div class="icon-circle"><i class="fas fa-calendar-check"></i></div>
        <h3>Time & Attendance</h3>
    </div>
    <div class="feature-card">
        <div class="icon-circle"><i class="fas fa-umbrella-beach"></i></div>
        <h3>Leave Management</h3>
    </div>
    <div class="feature-card">
        <div class="icon-circle"><i class="fas fa-chart-line"></i></div>
        <h3>Performance</h3>
    </div>
</div>

<section style="background: var(--light-green); padding: 80px 8%; text-align: center; margin: 40px 0;">
    <h2 style="font-size: 2.5rem; color: var(--primary-green); margin-bottom: 20px;">Ready to grow with us?</h2>
    <p style="color: var(--text-gray); font-size: 1.1rem; max-width: 600px; margin: 0 auto 30px;">
        Whether you are looking for your next career move or a better way to manage your team, PeopleNest is here to help.
    </p>
    <?php if ($recruitment_open): ?>
        <a href="apply.php" class="btn-filled" style="padding: 15px 40px; display: inline-block;">View All Openings</a>
    <?php else: ?>
        <span class="btn-filled disabled" style="padding: 15px 40px; display: inline-block;">Hiring Closed</span>
    <?php endif; ?>
</section>

    <footer>
        <p>&copy; 2026 PeopleNest HRMS. All rights reserved.</p>
    </footer>
</body>
</html>