<?php
require_once 'db_config.php';
header('Content-Type: application/json');

$dateFilter = $_GET['date'] ?? '';
$doctorFilter = $_GET['doctor'] ?? '';

if(!$dateFilter || !$doctorFilter) {
    echo json_encode(['status'=>'error', 'message'=>'Date and Doctor are required']);
    exit;
}

$mysqli = getDBConnection();

$stmt = $mysqli->prepare("SELECT date FROM patients WHERE DATE(date) = ? AND doctor = ? AND status != 'served'");
$stmt->bind_param("ss", $dateFilter, $doctorFilter);
$stmt->execute();
$result = $stmt->get_result();

$bookedSlots = [];
while($row = $result->fetch_assoc()) {
    // Format is "YYYY-MM-DD HH:mm:00", we want just "HH:mm"
    $timePart = substr($row['date'], 11, 5);
    $bookedSlots[] = $timePart;
}

echo json_encode(['status'=>'success', 'booked'=>$bookedSlots]);
$stmt->close();
$mysqli->close();
?>
