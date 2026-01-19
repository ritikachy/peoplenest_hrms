<?php
require_once 'config/session.php';
requireAdmin(); // Security: Only Admins can modify records
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: attendance-management.php");
    exit();
}

// 1. Handle the Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = $_POST['check_in_time'];
    $out = $_POST['check_out_time'];
    $note = $_POST['admin_note'];

    // Update the record
    $sql = "UPDATE attendance SET check_in_time = ?, check_out_time = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$in, $out, $note, $id]);
    
    // Redirect back to management page with success flag
    header("Location: attendance-management.php?success=1");
    exit();
}

// 2. Fetch current record to populate the form
$stmt = $conn->prepare("SELECT a.*, e.first_name, e.last_name FROM attendance a 
                        JOIN employees e ON a.employee_id = e.id WHERE a.id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die("Record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Attendance Record</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .edit-container { max-width: 500px; margin: 60px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #444; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
        textarea.form-control { height: 100px; resize: none; }
        .btn-save { background: #007bff; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; width: 100%; font-size: 1.1rem; }
        .btn-save:hover { background: #0056b3; }
        .cancel-link { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2 style="margin-top:0;">✏️ Fix Attendance</h2>
    <p style="color: #666;">Editing record for: <strong><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></strong></p>
    <p style="font-size: 0.9rem; color: #888;">Date: <?php echo date('M d, Y', strtotime($record['date'])); ?></p>

    <form method="POST">
        <div class="form-group">
            <label>Arrival Time (Check-In)</label>
            <input type="time" name="check_in_time" value="<?php echo $record['check_in_time']; ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Departure Time (Check-Out)</label>
            <input type="time" name="check_out_time" value="<?php echo $record['check_out_time']; ?>" class="form-control">
        </div>

        <div class="form-group">
            <label>Admin Remarks / Reason</label>
            <textarea name="admin_note" class="form-control" placeholder="Explain why this record was modified..."><?php echo htmlspecialchars($record['notes'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn-save">Save Changes</button>
        <a href="attendance-management.php" class="cancel-link">Nevermind, go back</a>
    </form>
</div>

</body>
</html>