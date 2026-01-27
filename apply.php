<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
$message = "";
$message_type = "info"; 

// Check if recruitment is open
$status_query = "SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'";
$status_stmt = $conn->query($status_query);
$current_status = $status_stmt->fetchColumn();

// Fetch active jobs for the dropdown
$job_query = "SELECT DISTINCT position_name FROM job_postings WHERE status = 'active'";
$jobs_stmt = $conn->query($job_query);
$active_jobs = $jobs_stmt->fetchAll(PDO::FETCH_COLUMN);

if ($current_status !== 'open') {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#dc3545;'>Applications are currently paused</h1>
            <p>We aren't currently hiring for new positions. Please check back later!</p>
            <a href='index.php' style='color:#2563eb;'>Return to Homepage</a>
          </div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $position = trim($_POST['position']);
    $experience = $_POST['experience_years'];
    $resume_path = null;

    $email_clean = strtolower(trim($email));
    $position_clean = strtolower(trim($position));

    // Duplicate Check
    $checkQuery = "SELECT id FROM candidates WHERE LOWER(trim(email)) = ? AND LOWER(trim(position)) = ? AND status != 'rejected'";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->execute([$email_clean, $position_clean]);

    if ($stmtCheck->rowCount() > 0) {
        $message = "Error: An active application for '" . htmlspecialchars($position) . "' already exists for this email.";
        $message_type = "error";
    } else {
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
            $upload_dir = 'assets/uploads/resumes/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $file_ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            if ($file_ext !== 'pdf') {
                $message = "Error: Only PDF files are allowed.";
                $message_type = "error";
            } else {
                $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['resume']['name']));
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
                    $resume_path = $target_file;
                    $query = "INSERT INTO candidates (name, email, phone, position, experience_years, resume_path, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                    $stmt = $conn->prepare($query);
                    
                    if ($stmt->execute([$name, $email, $phone, $position, $experience, $resume_path])) {
                        $message = "Success! Your application for " . htmlspecialchars($position) . " has been sent.";
                        $message_type = "success";
                        unset($_POST); // Clear form
                    } else {
                        $message = "Error: Database failed to save application.";
                        $message_type = "error";
                    }
                }
            }
        } else {
            $message = "Error: Please upload your CV in PDF format.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join PeopleNest - Career Portal</title>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .apply-container { max-width: 500px; width: 90%; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background: #2563eb; color: white; border: none; padding: 14px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

<div class="apply-container">
    <h2 style="text-align: center; margin-bottom: 25px;">Career Application</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required value="<?php echo $_POST['name'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" required value="<?php echo $_POST['phone'] ?? ''; ?>">
        </div>

        <div class="form-group">
            <label>Position You Are Applying For</label>
            <select name="position" required>
                <option value="">-- Select an Open Role --</option>
                <?php foreach ($active_jobs as $job): ?>
                    <option value="<?php echo htmlspecialchars($job); ?>" <?php echo (($_POST['position'] ?? '') === $job) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($job); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Years of Experience</label>
            <input type="number" name="experience_years" min="0" required value="<?php echo $_POST['experience_years'] ?? ''; ?>">
        </div>

        <div class="form-group">
            <label>Upload Resume (PDF only)</label>
            <input type="file" name="resume" accept=".pdf" required>
        </div>

        <button type="submit" class="btn-submit">Submit Application</button>
    </form>
</div>

</body>
</html>