<?php
include 'db.php';

// ดึงข้อมูลล่าสุด 1 แถว จาก sensor_data
$sql = "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // ตรวจสอบว่ามีฟิลด์ voltage กับ current หรือไม่ก่อนคำนวณ power
    if (isset($row['voltage']) && isset($row['current'])) {
        $row['power'] = $row['voltage'] * $row['current'];
    } else {
        $row['power'] = null; // เพราะในตารางยังไม่มีฟิลด์นี้
    }

    echo json_encode($row, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([]);
}

$conn->close();
?>
