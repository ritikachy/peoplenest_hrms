<?php
require_once 'config/session.php';
requireAdmin(); 
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get the month and year from the URL (sent by the button)
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Set the filename
$filename = "PeopleNest_Attendance_" . $year . "_" . $month . ".csv";

// Set browser headers to force a download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Set CSV Column Headers
fputcsv($output, ['Employee Name', 'Department', 'Date', 'Check In', 'Check Out', 'Total Minutes', 'Status']);

// Fetch all records for that specific month
$query = "SELECT e.first_name, e.last_name, e.department, a.date, a.check_in_time, a.check_out_time, a.status
          FROM attendance a
          JOIN employees e ON a.employee_id = e.id
          WHERE MONTH(a.date) = ? AND YEAR(a.date) = ?
          ORDER BY a.date ASC, e.last_name ASC";

$stmt = $conn->prepare($query);
$stmt->execute([$month, $year]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_minutes = 0;
    
    // Calculate minutes for the CSV
    if ($row['check_in_time'] && $row['check_out_time']) {
        $start = new DateTime($row['check_in_time']);
        $end = new DateTime($row['check_out_time']);
        $diff = $start->diff($end);
        $total_minutes = ($diff->h * 60) + $diff->i;
    }

    // Write row to CSV
    fputcsv($output, [
        $row['first_name'] . ' ' . $row['last_name'],
        $row['department'],
        $row['date'],
        $row['check_in_time'] ?? 'N/A',
        $row['check_out_time'] ?? 'N/A',
        $total_minutes,
        $row['status']
    ]);
}

fclose($output);
exit();
?>