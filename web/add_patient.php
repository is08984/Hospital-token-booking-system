<?php
require_once 'db_config.php';
header('Content-Type: application/json');

$mysqli = getDBConnection();

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$date = $_POST['date'] ?? '';
$doctor = $_POST['doctor'] ?? 'Dr. Rajesh Kumar';

if(!$name || !$email || !$phone || !$date || !$doctor){
    echo json_encode(['status'=>'error','message'=>'Missing fields']);
    exit;
}

// Convert datetime (Fallback if it's not strictly formatted over fetch)
if($date) {
    // If it's a T string like 2026-04-04T12:30
    $date = str_replace('T', ' ', $date);
    if(strlen($date) == 16) {
        $date .= ':00';
    }
}

// Regular bookings 
try {
    // Validate if the slot is taken!
    $checkStmt = $mysqli->prepare("SELECT id FROM patients WHERE DATE(date) = DATE(?) AND TIME(date) = TIME(?) AND doctor = ? AND status != 'served'");
    $checkStmt->bind_param("sss", $date, $date, $doctor);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    if($res->num_rows > 0) {
        throw new Exception('Time slot already booked for this doctor.');
    }
    $checkStmt->close();

    $result = $mysqli->query("SELECT MAX(queue_position) as max_pos FROM patients WHERE status='waiting'");
    $row = $result->fetch_assoc();
    $queuePos = ($row['max_pos'] ?? 0) + 1;

    $stmt = $mysqli->prepare("INSERT INTO patients (name,email,phone,date,doctor,priority,queue_position) VALUES (?,?,?,?,?,?,?)");
    if(!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
    }
    
    $priority = 'normal';
    $stmt->bind_param("ssssssi",$name,$email,$phone,$date,$doctor,$priority,$queuePos);
    
    if($stmt->execute()){
        echo json_encode(['status'=>'success']);
    } else {
        throw new Exception('Insert failed: ' . $stmt->error);
    }
    $stmt->close();
} catch(Exception $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}

$mysqli->close();
?>
