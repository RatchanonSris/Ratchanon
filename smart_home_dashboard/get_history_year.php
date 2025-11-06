<?php
include 'db.php';

$year = $_GET['year'] ?? null;

if (!$year || !preg_match('/^\d{4}$/', $year)) {
    echo json_encode(['error' => 'Invalid year']);
    exit;
}

$start = "$year-01-01";
$end = "$year-12-31";

$data = [];

// 🌡️ Sensor data
$sensor_sql = "SELECT timestamp, temperature, humidity, light 
               FROM sensor_data
               WHERE DATE(timestamp) BETWEEN ? AND ?
               ORDER BY timestamp ASC";
$stmt = $conn->prepare($sensor_sql);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$res = $stmt->get_result();
$data['sensor'] = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ⚡ Energy data
$energy_sql = "SELECT id, volt, amp, watt, energy, created_date 
               FROM energy1
               WHERE DATE(created_date) BETWEEN ? AND ?
               ORDER BY created_date ASC";
$stmt = $conn->prepare($energy_sql);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$res = $stmt->get_result();
$data['energy'] = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($data);
?>
