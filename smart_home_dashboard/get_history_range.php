<?php
include 'db.php';

$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;

if (!$start || !$end) {
  echo json_encode(['error' => 'missing date range']);
  exit;
}

$data = [];

// 🌡️ Sensor data
$sensor_sql = "SELECT timestamp, temperature, humidity, light FROM sensor_data
               WHERE DATE(timestamp) BETWEEN ? AND ?
               ORDER BY timestamp ASC";
$stmt = $conn->prepare($sensor_sql);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$sensor_res = $stmt->get_result();
$data['sensor'] = $sensor_res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ⚡ Energy data
$energy_sql = "SELECT id, volt, amp, watt, energy, created_date FROM energy1 
               WHERE DATE(created_date) BETWEEN ? AND ?
               ORDER BY created_date ASC";
$stmt = $conn->prepare($energy_sql);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$energy_res = $stmt->get_result();
$data['energy'] = $energy_res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($data);
?>