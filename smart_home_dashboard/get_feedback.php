<?php
include 'db.php';

header('Content-Type: application/json; charset=utf-8');

// ตรวจสอบว่ามีการส่ง date มาหรือไม่ (เช่น ?date=2025-10-05)
if (isset($_GET['date'])) {
    $date = $_GET['date'];

    $sql = "SELECT id, temperature, humidity, light, timestamp 
            FROM sensor_data 
            WHERE DATE(timestamp) = ? 
            ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // ถ้าไม่ระบุวันที่ ให้ดึงข้อมูลทั้งหมด
    $sql = "SELECT id, temperature, humidity, light, timestamp 
            FROM sensor_data 
            ORDER BY timestamp DESC";
    $result = $conn->query($sql);
}

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);

$conn->close();
?>
