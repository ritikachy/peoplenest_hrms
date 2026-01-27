<?php
require_once 'config/session.php';
requireAdmin();
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
$message = "";

// 1. ADD NEW JOB (Full logic with all required fields)
if (isset($_POST['add_job'])) {
    $title = trim($_POST['job_title']);
    $experience = trim($_POST['experience_required']);
    $qualifications = trim($_POST['qualifications']);
    $description = trim($_POST['job_description']);

    $stmt = $conn->prepare("INSERT INTO job_postings (position_name, experience_required, qualifications, job_description, status) VALUES (?, ?, ?, ?, 'active')");
    
    if($stmt->execute([$title, $experience, $qualifications, $description])) {
        $message = "Vacancy for $title is now LIVE.";
    } else {
        $message = "Error: Database update failed.";
    }
}

// 2. DELETE/CLOSE JOB
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM job_postings WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: job_postings.php");
    exit();
}

$jobs = $conn->query("SELECT * FROM job_postings ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open Roles - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
        }

        body { background-color: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; }

        /* Header Section */
        .page-header { background: var(--white); padding: 20px 30px; border-bottom: 1px solid #e2e8f0; margin: -30px -30px 30px -30px; }
        .stats-pill { background: #eef2ff; color: var(--primary); padding: 6px 16px; border-radius: 99px; font-weight: 600; font-size: 13px; }

        /* Form Styling */
        .form-card { 
            background: var(--white); padding: 30px; border-radius: 16px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; margin-bottom: 40px; 
        }
        .form-label { font-weight: 600; font-size: 0.875rem; color: var(--text-main); margin-bottom: 8px; display: block; }
        .form-input { 
            width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 10px; 
            transition: all 0.2s; font-size: 14px; outline: none; box-sizing: border-box;
        }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }

        /* Job Grid & Cards */
        .job-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px; }
        .job-card { 
            background: var(--white); border-radius: 16px; border: 1px solid #e2e8f0; 
            transition: transform 0.2s, box-shadow 0.2s; position: relative; overflow: hidden;
        }
        .job-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }
        .job-card-body { padding: 24px; }
        
        .status-badge { 
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            padding: 4px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;
        }
        .badge-active { background: #dcfce7; color: #15803d; }
        
        .job-title { font-size: 18px; font-weight: 700; margin: 15px 0 10px 0; color: #0f172a; }
        
        .info-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; font-size: 14px; color: var(--text-muted); }
        .info-row i { width: 16px; color: #94a3b8; }

        /* Card Footer Actions */
        .card-actions { 
            background: #f1f5f9; padding: 12px 24px; display: flex; 
            justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0;
        }
        .btn-delete { color: #ef4444; font-size: 14px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-delete:hover { color: #b91c1c; }
        
        .publish-btn { 
            background: var(--primary); color: white; border: none; padding: 12px 25px; 
            border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .publish-btn:hover { background: var(--primary-hover); transform: scale(1.02); }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin:0; font-size: 24px;">Open Roles</h2>
                        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Manage your company's open positions and hiring status.</p>
                    </div>
                    <span class="stats-pill">
                        <i class="fa-solid fa-briefcase"></i> &nbsp;<?php echo count($jobs); ?> Active Roles
                    </span>
                </div>
            </div>

            <?php if($message): ?>
                <div style="padding: 16px; background: #f0fdf4; color: #166534; border-radius: 12px; margin-bottom: 25px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <h3 style="margin-top:0; margin-bottom: 20px; font-size: 18px;">Create New Vacancy</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Internal Position Title</label>
                            <input type="text" name="job_title" class="form-input" placeholder="e.g. Senior Backend Developer" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Experience Tier</label>
                            <input type="text" name="experience_required" class="form-input" placeholder="e.g. 4+ Years" required>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <label class="form-label">Required Skills & Qualifications</label>
                        <input type="text" name="qualifications" class="form-input" placeholder="PHP, MySQL, Laravel, AWS..." required>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <label class="form-label">Key Responsibilities & Description</label>
                        <textarea name="job_description" class="form-input" rows="4" placeholder="Describe the day-to-day tasks..." required></textarea>
                    </div>
                    
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" name="add_job" class="publish-btn">
                            <i class="fa-solid fa-paper-plane"></i> &nbsp;Publish Vacancy
                        </button>
                    </div>
                </form>
            </div>

            <div class="job-grid">
                <?php foreach ($jobs as $job): 
                    $created = new DateTime($job['created_at']);
                    $diff = (new DateTime())->diff($created)->days;
                ?>
                <div class="job-card">
                    <div class="job-card-body">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <span class="status-badge badge-active">
                                <i class="fa-solid fa-circle" style="font-size: 8px;"></i> Active
                            </span>
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">
                                <i class="fa-regular fa-clock"></i> <?php echo ($diff == 0) ? "Today" : "$diff d ago"; ?>
                            </span>
                        </div>

                        <h3 class="job-title"><?php echo htmlspecialchars($job['position_name']); ?></h3>
                        
                        <div class="info-row">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span><?php echo htmlspecialchars($job['qualifications']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-chart-line"></i>
                            <span><?php echo htmlspecialchars($job['experience_required']); ?> Experience</span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold;">
                            ID: #00<?php echo $job['id']; ?>
                        </span>
                        <a href="?delete_id=<?php echo $job['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to archive this role?')">
                            <i class="fa-solid fa-trash-can"></i> Archive Role
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>