<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $date = $_GET['date'];

    $sql1 = "SELECT temperature, humidity, light, timestamp 
             FROM sensor_data 
             WHERE DATE(timestamp) = ?
             ORDER BY timestamp ASC";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("s", $date);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $sensor = [];
    while ($row = $result1->fetch_assoc()) $sensor[] = $row;
    $stmt1->close();

    $sql2 = "SELECT id, volt, amp, watt, energy, created_date 
             FROM energy1 
             WHERE DATE(created_date) = ?
             ORDER BY created_date ASC";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $date);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $energy = [];
    while ($row = $result2->fetch_assoc()) $energy[] = $row;
    $stmt2->close();

    echo json_encode(["sensor" => $sensor, "energy" => $energy], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["error" => "กรุณาเลือกวันที่ก่อนดูกราฟ"], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
