<?php
include 'db.php';

// ดึงข้อมูลทั้งหมดจาก sensor_data
$sql = "SELECT temperature, humidity, light, power, timestamp FROM sensor_data";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $inserted = 0;

    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO history_data (temperature, humidity, light, power, recorded_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("dddis", $row['temperature'], $row['humidity'], $row['light'], $row['power'], $row['timestamp']);
        $stmt->execute();
        $inserted++;
    }

    echo "✅ คัดลอกข้อมูลจาก sensor_data ไปยัง history_data จำนวน $inserted แถวเรียบร้อยแล้ว";
} else {
    echo "⚠️ ไม่มีข้อมูลใน sensor_data";
}

$conn->close();
?>
