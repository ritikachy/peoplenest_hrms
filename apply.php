<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resume_path = null;

    // --- 1. HANDLE CV UPLOAD ---
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
        $upload_dir = 'assets/uploads/resumes/';
        
        // Create the folder if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Create a unique filename: timestamp + original name
        $file_name = time() . '_' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $file_name;

        // Move the file from temporary storage to your assets folder
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            $resume_path = $target_file;
        }
    }

    // --- 2. UPDATED INSERT QUERY ---
    $query = "INSERT INTO candidates (name, email, phone, position, experience_years, resume_path, status) 
              VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([
        $_POST['name'], 
        $_POST['email'], 
        $_POST['phone'], 
        $_POST['position'], 
        $_POST['experience_years'],
        $resume_path // Saving the path to the database
    ])) {
        $message = "Application submitted successfully! CV Uploaded.";
    } else {
        $message = "Something went wrong. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join Our Team - PeopleNest</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .apply-container { max-width: 600px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body style="background: #f4f7f6;">
    <div class="apply-container">
        <h2>Career Opportunities</h2>
        <p>Fill out the form below to apply for a position.</p>
        
        <?php if ($message): ?>
            <div class="alert alert-info" style="padding:10px; background:#e1f5fe; color:#01579b; margin-bottom:15px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="john@example.com">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required placeholder="+123456789">
            </div>
            <div class="form-group">
                <label>Target Position</label>
                <input type="text" name="position" required placeholder="PHP Developer">
            </div>
            <div class="form-group">
                <label>Years of Experience</label>
                <input type="number" name="experience_years" min="0" required>
            </div>

            <div class="form-group">
                <label>Upload CV (PDF only)</label>
                <input type="file" name="resume" accept=".pdf" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Application</button>
        </form>
    </div>
</body>
</html>