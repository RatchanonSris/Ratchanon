<?php
session_start();
include 'db.php';

// ✅ แสดง error (เพื่อดีบั๊ก - ปิดได้ภายหลัง)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ ระบบล็อกอินจำลอง (เพื่อให้เข้าได้)
if(!isset($_SESSION['feedback_login'])) {
    $_SESSION['feedback_login'] = true;
}

// ✅ เพิ่ม Feedback
if(isset($_POST['submit'])) {
    $name = trim($_POST['user_name']) ?: 'ผู้ใช้งานทั่วไป';
    $message = trim($_POST['message']);
    if($message != '') {
        $stmt = $conn->prepare("INSERT INTO feedback (user_name, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $name, $message);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: feedback.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แบบสำรวจความพึงพอใจ</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Prompt', sans-serif;
    margin: 0;
    background: #f4f6f8;
    color: #333;
}

/* Navbar */
nav {
    background: linear-gradient(90deg, #2980b9, #6dd5fa);
    padding: 15px 30px;
    display: flex;
    gap: 25px;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}
nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    position: relative;
    padding: 5px 0;
}
nav a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    left: 0;
    bottom: -3px;
    background-color: #f1c40f;
    transition: width 0.3s;
}
nav a:hover::after {
    width: 100%;
}
nav a:hover { color: #f1c40f; }

/* Container */
.container {
    max-width: 950px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    animation: fadeIn 0.5s ease-in-out;
}

/* Header */
h1 {
    text-align: center;
    color: #2c3e50;
}
p.desc {
    text-align: center;
    color: #777;
    margin-top: -10px;
    margin-bottom: 20px;
}

/* Form */
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 30px;
}
input, textarea {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 1em;
}
button {
    background: #2980b9;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #1f5f85;
}

/* Feedback cards */
.card {
    background: #ffffff;
    border-radius: 15px;
    padding: 15px 20px;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}
.card:hover {
    transform: translateY(-3px);
}
.card strong {
    color: #2980b9;
}
.card small {
    color: #888;
    font-size: 0.9em;
}
.card p {
    margin-top: 8px;
    color: #333;
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<nav>
    <a href="index.php">Dashboard</a>
    <a href="history.php">กราฟย้อนหลังรายวัน</a>
    <a href="history_range.php">กราฟย้อนหลังกำหนดวัน</a>
    <a href="history_mount.php">กราฟย้อนหลังรายเดือน</a>
    <a href="history_year.php">กราฟย้อนหลังรายปี</a>
    <a href="power.php">คำนวณไฟฟ้า</a>
    <a href="about.php">ติดต่อผู้พัฒนา</a>
    <a href="feedback.php">แบบสำรวจความพึงพอใจ</a>
</nav>
<div class="container">
    <h1>💬 แบบสำรวจความพึงพอใจ</h1>
    <p class="desc">สามารถแสดงความคิดเห็นหรือข้อเสนอแนะเพื่อพัฒนาเว็บไซต์ได้ที่นี่</p>

    <form method="post">
        <input type="text" name="user_name" placeholder="ชื่อ (ไม่บังคับ)">
        <textarea name="message" rows="4" placeholder="พิมพ์คำชี้แนะของคุณที่นี่..." required></textarea>
        <button type="submit" name="submit">ส่งคำชี้แนะ</button>
    </form>

    <h2 style="color:#2c3e50;">📜 ข้อเสนอแนะทั้งหมด</h2>

    <?php
    // ตรวจสอบว่ามีฟิลด์ created_at ในฐานข้อมูลหรือไม่
    $check = $conn->query("SHOW COLUMNS FROM feedback LIKE 'created_at'");
    $hasCreated = $check->num_rows > 0;
    $check->close();

    $sql = $hasCreated
        ? "SELECT id, user_name, message, created_at FROM feedback ORDER BY id DESC"
        : "SELECT id, user_name, message FROM feedback ORDER BY id DESC";

    $result = $conn->query($sql);

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $user = htmlspecialchars($row['user_name']);
            $msg = nl2br(htmlspecialchars($row['message']));
            $time = $hasCreated ? date("d/m/Y H:i", strtotime($row['created_at'])) : "";
            echo "<div class='card'>
                    <strong>{$user}</strong><br>
                    <small>{$time}</small>
                    <p>{$msg}</p>
                  </div>";
        }
    } else {
        echo "<p style='text-align:center;color:#888;'>ยังไม่มีคำชี้แนะในระบบ</p>";
    }
    ?>
</div>

<!-- ✅ สคริปต์รีเฟรชหน้าอัตโนมัติ -->
<script>
// รีเฟรชทุก 30 วินาที (ไม่กระพริบ)
setTimeout(() => {
    location.reload();
}, 30000);
</script>

</body>
</html>
