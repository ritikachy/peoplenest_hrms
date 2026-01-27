<?php
require_once 'config/database.php';
$database = new Database();
$conn = $database->getConnection();

$query = "SELECT * FROM job_postings WHERE status = 'active' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers | PeopleNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #1e293b;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --accent: #e0e7ff;
            --danger: #ef4444;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Inter', -apple-system, sans-serif; 
            color: var(--text-main);
            line-height: 1.6;
            margin: 0;
        }

        /* --- Header Section --- */
        .page-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            margin-bottom: -50px;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin: 0;
            letter-spacing: -1px;
        }

        .page-header p {
            color: #cbd5e1;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 10px auto 0;
        }

        .container { 
            max-width: 1000px; 
            margin: 0 auto 100px; 
            padding: 0 20px; 
            position: relative;
        }

        /* --- Job Card Styling --- */
        .vacancy-card { 
            background: var(--white); 
            padding: 40px; 
            border-radius: 20px; 
            margin-bottom: 30px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }

        .vacancy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
            border-color: var(--accent);
        }

        .vacancy-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 6px; height: 100%;
            background: var(--primary);
            opacity: 0;
            transition: 0.3s;
        }

        .vacancy-card:hover::before { opacity: 1; }

        /* --- Content Layout --- */
        .job-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }

        .job-title h3 { margin: 0; font-size: 24px; color: var(--secondary); font-weight: 700; text-transform: capitalize; }

        .tags-container { margin-top: 10px; display: flex; gap: 10px; }

        .tag {
            font-size: 12px; font-weight: 600; padding: 6px 14px; border-radius: 50px;
            background: var(--bg); color: var(--text-muted); display: flex; align-items: center; gap: 6px;
        }

        .tag i { color: var(--primary); }

        /* --- Requirements Grid --- */
        .requirements-section {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; margin: 30px 0; padding: 20px; background: #fcfdfe;
            border-radius: 12px; border: 1px dashed #e2e8f0;
        }

        .req-item b { display: block; font-size: 11px; text-transform: uppercase; color: var(--primary); letter-spacing: 1px; margin-bottom: 5px; }

        .req-item span { font-weight: 600; color: var(--secondary); }

        /* --- Description --- */
        .description-header { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px; display: block; }

        .description-text { color: #475569; font-size: 15px; line-height: 1.8; }

        /* --- Button --- */
        .btn-apply { 
            background: var(--primary); color: white; padding: 14px 32px; border-radius: 12px; 
            text-decoration: none; font-weight: 600; font-size: 15px; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2); display: inline-block;
        }

        .btn-apply:hover { background: var(--primary-dark); transform: scale(1.02); }

        /* --- Enhanced Empty State --- */
        .empty-state-card {
            text-align: center; padding: 80px 40px; background: white; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px dashed #cbd5e1; margin-top: 40px;
        }

        .empty-icon-circle {
            width: 80px; height: 80px; background: #f1f5f9; color: var(--text-muted);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 25px; font-size: 32px;
        }

        .empty-state-card h2 { color: var(--secondary); margin-bottom: 12px; font-size: 26px; }

        .empty-state-card p { color: var(--text-muted); max-width: 480px; margin: 0 auto 30px; }

        .btn-home {
            display: inline-block; padding: 12px 30px; border: 2px solid #e2e8f0;
            border-radius: 12px; color: var(--text-main); text-decoration: none;
            font-weight: 600; transition: 0.3s;
        }

        .btn-home:hover { background: #f8fafc; border-color: #cbd5e1; }

        @media (max-width: 600px) {
            .job-header { text-align: center; justify-content: center; }
            .btn-apply { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    <header class="page-header">
        <h1>Join the PeopleNest Team</h1>
        <p>Help us build the next generation of HR technology. Your journey starts here.</p>
    </header>

    <div class="container">
        <?php if (count($vacancies) > 0): ?>
            <?php foreach ($vacancies as $job): ?>
                <div class="vacancy-card">
                    <div class="job-header">
                        <div class="job-title">
                            <h3><?php echo htmlspecialchars($job['position_name']); ?></h3>
                            <div class="tags-container">
                                <span class="tag"><i class="fa-regular fa-calendar"></i> Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                <span class="tag"><i class="fa-solid fa-briefcase"></i> Full-Time</span>
                                <span class="tag"><i class="fa-solid fa-location-dot"></i> Remote / On-site</span>
                            </div>
                        </div>
                        <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn-apply">
                            Apply for this Position
                        </a>
                    </div>

                    <div class="requirements-section">
                        <div class="req-item">
                            <b>Experience Required</b>
                            <span><i class="fa-solid fa-clock-rotate-left" style="font-size: 12px; margin-right: 5px; color: #94a3b8;"></i> <?php echo htmlspecialchars($job['experience_required']); ?> Years</span>
                        </div>
                        <div class="req-item">
                            <b>Qualifications</b>
                            <span><i class="fa-solid fa-graduation-cap" style="font-size: 12px; margin-right: 5px; color: #94a3b8;"></i> <?php echo htmlspecialchars($job['qualifications']); ?></span>
                        </div>
                    </div>

                    <div class="description-content">
                        <span class="description-header">Role Overview</span>
                        <div class="description-text">
                            <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-card">
                <div class="empty-icon-circle">
                    <i class="fa-solid fa-briefcase-clock"></i>
                </div>
                <h2>Applications are currently paused</h2>
                <p>We aren't currently hiring for new positions. Our team is currently reviewing existing applications. Please check back later or return to our homepage!</p>
                <a href="index.php" class="btn-home">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i> Return to Homepage
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>