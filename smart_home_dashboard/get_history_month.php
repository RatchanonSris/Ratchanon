<?php
include 'db.php';

header('Content-Type: application/json; charset=utf-8');

$month = $_GET['month'] ?? null;

if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
    echo json_encode(['error' => 'Invalid month format']);
    exit;
}

// แปลงเดือนเป็นวันเริ่มต้นและวันสิ้นสุด
list($year, $mon) = explode('-', $month);
$start = "$year-$mon-01";
$end = date("Y-m-t", strtotime($start)); // วันสุดท้ายของเดือน

$data = [];

try {
    // 🌡️ Sensor data
    $sensor_sql = "SELECT timestamp, temperature, humidity, light 
                   FROM sensor_data
                   WHERE DATE(timestamp) BETWEEN ? AND ?
                   ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sensor_sql);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $sensor_res = $stmt->get_result();
    $data['sensor'] = $sensor_res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ⚡ Energy data
    $energy_sql = "SELECT id, volt, amp, watt, energy, created_date 
                   FROM energy1 
                   WHERE DATE(created_date) BETWEEN ? AND ?
                   ORDER BY created_date ASC";
    $stmt = $conn->prepare($energy_sql);
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $energy_res = $stmt->get_result();
    $data['energy'] = $energy_res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ตรวจสอบว่ามีข้อมูลหรือไม่
    if(empty($data['sensor']) && empty($data['energy'])) {
        echo json_encode(['error' => 'No data found']);
        exit;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
