<?php
$servername = "localhost";
$username = "root";
$password = ""; // ถ้า XAMPP ของคุณไม่มีรหัสผ่าน ให้เว้นว่างไว้
$dbname = "en3"; // ชื่อฐานข้อมูลของคุณ

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
