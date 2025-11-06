<?php
include 'db.php';
header('Content-Type: application/json');

// ดึงข้อมูลล่าสุดจากตาราง energy1
$sql = "SELECT volt, amp, watt, energy FROM energy1 ORDER BY created_date DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "volt" => $row['volt'],
        "amp" => $row['amp'],
        "watt" => $row['watt'],
        "energy" => $row['energy']
    ]);
} else {
    echo json_encode([
        "volt" => "--",
        "amp" => "--",
        "watt" => "--",
        "energy" => "--"
    ]);
}
?>
