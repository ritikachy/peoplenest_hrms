<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
$message = "";
$message_type = "info"; 
// Check if recruitment is open before allowing any logic to run
$status_query = "SELECT setting_value FROM site_settings WHERE setting_key = 'recruitment_status'";
$status_stmt = $conn->query($status_query);
$current_status = $status_stmt->fetchColumn();

if ($current_status !== 'open') {
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#dc3545;'>Applications are currently paused</h1>
            <p>We aren't currently hiring for new positions. Please check back later!</p>
            <a href='index.php' style='color:#2563eb;'>Return to Homepage</a>
          </div>";
    exit(); // This stops the rest of the form from showing!
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $position = trim($_POST['position']);
    $experience = $_POST['experience_years'];
    $resume_path = null;

   // 1. DUPLICATE CHECK (Improved Case & Space Insensitivity)
   $email_clean = strtolower(trim($email));
   $position_clean = strtolower(trim($position));

   // We check for any application that matches the email and position exactly, 
   // ignoring case sensitivity and hidden spaces.
   $checkQuery = "SELECT id FROM candidates WHERE LOWER(trim(email)) = ? AND LOWER(trim(position)) = ? AND status != 'rejected'";
   $stmtCheck = $conn->prepare($checkQuery);
   $stmtCheck->execute([$email_clean, $position_clean]);

   if ($stmtCheck->rowCount() > 0) {
       $message = "Error: An active application for '" . htmlspecialchars($position) . "' already exists for this email.";
       $message_type = "error";
   } else {
       // ... Only proceed to Section 2 (File Upload) if no duplicate is found ...
        // 2. FILE UPLOAD HANDLING
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

                    // 3. DATABASE INSERTION
                    $query = "INSERT INTO candidates (name, email, phone, position, experience_years, resume_path, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                    $stmt = $conn->prepare($query);
                    
                    if ($stmt->execute([$name, $email, $phone, $position, $experience, $resume_path])) {
                        // Success! Clear POST to prevent resubmission on refresh
                        $message = "Success! Your application for " . htmlspecialchars($position) . " has been sent.";
                        $message_type = "success";
                        $_POST = array(); 
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .apply-container { max-width: 500px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background: #2563eb; color: white; border: none; padding: 14px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: #1d4ed8; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
    </style>
</head>
<body>

<div class="apply-container">
    <h2 style="margin-top:0;">Apply for a Position</h2>
    
    <?php if ($message): ?>
        <div class="alert" style="
            background: <?php echo ($message_type == 'error') ? '#fee2e2' : '#dcfce7'; ?>; 
            color: <?php echo ($message_type == 'error') ? '#991b1b' : '#166534'; ?>;
            border: 1px solid <?php echo ($message_type == 'error') ? '#fecaca' : '#bbf7d0'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" required>
        </div>

        <div class="form-group">
            <label>Position You Are Applying For</label>
            <input type="text" name="position" placeholder="e.g. Accountant" required>
        </div>

        <div class="form-group">
            <label>Years of Experience</label>
            <input type="number" name="experience_years" min="0" required>
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