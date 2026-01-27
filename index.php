<?php
require_once 'includes/auth.php';

// Redirect if already logged in based on role
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin-dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

// --- LOGIC: Fetch dynamic counts from Database ---
try {
    // Check recruitment status
    $status_query = "SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'";
    $status_stmt = $conn->query($status_query);
    $recruitment_open = ($status_stmt->fetchColumn() === 'open');

    // Stats
    $emp_count = $conn->query("SELECT COUNT(*) FROM employees WHERE status = 'active'")->fetchColumn();
    $pos_count = $conn->query("SELECT COUNT(*) FROM job_postings WHERE status = 'active'")->fetchColumn();
    $dept_count = $conn->query("SELECT COUNT(DISTINCT department) FROM employees")->fetchColumn();
} catch (PDOException $e) {
    // Fallback if tables don't exist yet
    $emp_count = 0; $pos_count = 0; $dept_count = 0; $recruitment_open = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeopleNest | Intelligently Simple HR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pn-navy: #1a202c;       /* Dashboard Sidebar */
            --pn-green: #2d7a32;      /* Brand Green */
            --pn-light-bg: #f8fafc;   /* Dashboard Content BG */
            --pn-border: #edf2f7;     /* Dashboard Borders */
            --text-main: #2d3748;
            --text-muted: #718096;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: white; color: var(--pn-navy); line-height: 1.6; }

        /* Navigation */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 8%; background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 1000;
            border-bottom: 1px solid var(--pn-border);
        }
        .logo { font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--pn-navy); }
        .logo-box { background: var(--pn-navy); color: var(--pn-green); padding: 5px 8px; border-radius: 6px; }
        
        .nav-links a { text-decoration: none; color: var(--pn-navy); font-weight: 600; margin: 0 15px; font-size: 0.9rem; transition: var(--transition); }
        .nav-links a:hover { color: var(--pn-green); }

        .btn-login { border: 2px solid var(--pn-navy); padding: 8px 20px; border-radius: 8px; text-decoration: none; color: var(--pn-navy); font-weight: 700; font-size: 0.85rem; transition: var(--transition); }
        .btn-login:hover { background: var(--pn-navy); color: white; }

        /* Hero */
        .hero { padding: 100px 8% 80px; text-align: center; background: radial-gradient(circle at top right, #f1f5f9, #ffffff); }
        .badge { background: white; border: 1px solid var(--pn-border); color: var(--pn-green); padding: 6px 16px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 20px; display: inline-block; }
        .hero h1 { font-size: 4rem; font-weight: 800; letter-spacing: -2px; line-height: 1.1; margin-bottom: 25px; }
        .hero p { color: var(--text-muted); font-size: 1.2rem; max-width: 700px; margin: 0 auto 40px; }

        .btn-primary { background: var(--pn-navy); color: white; padding: 15px 35px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-block; transition: var(--transition); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(26, 32, 44, 0.2); }

        /* Stats Section */
        .stats-grid { 
            display: grid; grid-template-columns: repeat(5, 1fr); 
            background: white; margin: -50px 8% 0; padding: 40px;
            border-radius: 20px; border: 1px solid var(--pn-border);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            position: relative; z-index: 10;
        }
        .stat-card { text-align: center; border-right: 1px solid var(--pn-border); }
        .stat-card:last-child { border-right: none; }
        .stat-num { display: block; font-size: 2.2rem; font-weight: 800; color: var(--pn-navy); }
        .stat-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; }

        /* Features */
        .features { padding: 100px 8%; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .f-card { padding: 40px; border-radius: 20px; border: 1px solid var(--pn-border); transition: var(--transition); }
        .f-card:hover { border-color: var(--pn-green); transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
        .f-card i { font-size: 2rem; color: var(--pn-green); margin-bottom: 20px; display: block; }
        .f-card h3 { font-size: 1.3rem; margin-bottom: 10px; }
        .f-card p { font-size: 0.9rem; color: var(--text-muted); }

        /* Professional Footer */
        footer { background: var(--pn-navy); color: #cbd5e0; padding: 80px 8% 30px; border-top: 5px solid var(--pn-green); }
        .footer-main { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1.5fr; gap: 40px; margin-bottom: 50px; }
        .footer-head { color: white; font-weight: 700; margin-bottom: 20px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: #cbd5e0; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .footer-links a:hover { color: var(--pn-green); padding-left: 5px; }

        .newsletter input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; width: 100%; margin-bottom: 10px; }
        .newsletter button { background: var(--pn-green); color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; }

        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.05); padding-top: 30px; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; }
    /* Add this inside your <style> tag */
@media (max-width: 992px) {
    .stats-grid { 
        grid-template-columns: repeat(3, 1fr); 
        margin: -50px 4% 0; 
    }
    .hero h1 { font-size: 3rem; }
}

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .features { grid-template-columns: 1fr; }
    .footer-main { grid-template-columns: 1fr 1fr; }
    .nav-links { display: none; } /* Consider a hamburger menu later */
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .stat-card { border-right: none; border-bottom: 1px solid var(--pn-border); padding-bottom: 15px; }
    .stat-card:last-child { border-bottom: none; }
}
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">
        <div class="logo-box"><i class="fas fa-cubes"></i></div>
        People<span style="color: var(--pn-green)">Nest</span>
    </a>
    <div class="nav-links">
        <a href="#features">Platform</a>
        <a href="apply.php">Careers</a>
        <a href="login.php" class="btn-login">Staff Login</a>
    </div>
</nav>

<section class="hero">
    <div class="badge">
        <i class="fas fa-shield-check"></i> <?php echo $recruitment_open ? "Now Hiring: $pos_count Active Roles" : "Trusted by $emp_count Professionals"; ?>
    </div>
    <h1>The Effortlessly Simple <br> <span style="color: var(--pn-green)">HR Platform</span></h1>
    <p>We’ve combined powerful performance insights with a clean interface to help you manage your team without the stress.</p>
    <div style="display: flex; justify-content: center; gap: 15px;">
        <a href="careers.php" class="btn-primary">Explore Careers <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></a>
        <a href="login.php" class="btn-login" style="padding: 14px 30px;">staff login</a>
    </div>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <span class="stat-num"><?php echo $emp_count; ?></span>
        <span class="stat-lbl">Active Staff</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?php echo $pos_count; ?></span>
        <span class="stat-lbl">Open Positions</span>
    </div>
    <div class="stat-card">
        <span class="stat-num" style="color: var(--pn-green)">99.9%</span>
        <span class="stat-lbl">Reliability</span>
    </div>
    <div class="stat-card">
        <span class="stat-num">24/7</span>
        <span class="stat-lbl">Access</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?php echo $dept_count; ?></span>
        <span class="stat-lbl">Departments</span>
    </div>
</section>

<section class="features" id="features">
    <div class="f-card">
        <i class="fas fa-id-badge"></i>
        <h3>Unified Profiles</h3>
        <p>A single source of truth for all employee data, from documents to performance history.</p>
    </div>
    <div class="f-card">
        <i class="fas fa-calendar-alt"></i>
        <h3>Smart Attendance</h3>
        <p>Automated clock-ins and leave management that syncs directly with your payroll logic.</p>
    </div>
    <div class="f-card">
        <i class="fas fa-chart-line"></i>
        <h3>Growth Analytics</h3>
        <p>Visual reports that help you understand turnover, diversity, and departmental growth.</p>
    </div>
</section>

<footer>
    <div class="footer-main">
        <div class="footer-col">
            <div class="logo" style="color: white; margin-bottom: 20px;">
                <div class="logo-box" style="background: var(--pn-green); color: var(--pn-navy);"><i class="fas fa-cubes"></i></div>
                PeopleNest
            </div>
            <p style="font-size: 0.85rem; line-height: 1.6;">Empowering organizations with intelligent HR tools to build better workplace cultures.</p>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <a href="#"><i class="fab fa-linkedin" style="font-size: 1.2rem;"></i></a>
                <a href="#"><i class="fab fa-twitter" style="font-size: 1.2rem;"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h4 class="footer-head">Platform</h4>
            <ul class="footer-links">
                <li><a href="login.php">Admin Login</a></li>
                <li><a href="login.php">Employee Portal</a></li>
                <li><a href="#">Self-Service</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4 class="footer-head">Company</h4>
            <ul class="footer-links">
                <li><a href="apply.php">About Us</a></li>
                <li><a href="apply.php">Careers</a></li>
                <li><a href="#">Privacy</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4 class="footer-head">Support</h4>
            <ul class="footer-links">
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Security</a></li>
                <li><span style="color: var(--pn-green); font-size: 0.8rem;"><i class="fas fa-circle" style="font-size: 0.5rem;"></i> System Live</span></li>
            </ul>
        </div>

        <div class="footer-col newsletter">
            <h4 class="footer-head">Stay Updated</h4>
            <input type="email" placeholder="Work email address">
            <button>Subscribe</button>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 PeopleNest HRMS. Crafted for Excellence.</p>
        <div style="display: flex; gap: 20px; opacity: 0.5;">
            <i class="fab fa-aws" title="Hosted on AWS"></i>
            <i class="fas fa-shield-alt" title="SSL Secured"></i>
            <i class="fab fa-stripe" title="Secure Payments"></i>
        </div>
    </div>
</footer>

</body>
</html>