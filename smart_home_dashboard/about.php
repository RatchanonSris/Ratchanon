<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>About | Smart Home Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">
<style>
:root {
  --blue: #2980b9;
  --light-blue: #6dd5fa;
  --gray-bg: #f0f4f8;
  --dark: #2c3e50;
}

body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  background: var(--gray-bg);
  color: var(--dark);
  line-height: 1.6;
}

/* 🔹 Navbar */
nav {
  background: linear-gradient(90deg, var(--blue), var(--light-blue));
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
  transition: all 0.3s ease;
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
nav a:hover::after { width: 100%; }
nav a:hover { color: #f1c40f; }

/* 🔹 Header */
header {
  text-align: center;
  padding: 60px 20px 40px;
  background: linear-gradient(135deg, #dff1ff, #ffffff);
}
header h1 {
  margin: 0;
  color: var(--dark);
  font-size: 2.2em;
}
header p {
  color: #555;
  font-size: 1.1em;
  margin-top: 10px;
}

/* 🔹 Container */
.container {
  max-width: 850px;
  margin: -30px auto 40px;
  background: #fff;
  border-radius: 25px;
  padding: 40px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
  animation: fadeIn 0.8s ease;
}

/* 🔹 About Section */
.about {
  text-align: center;
}
.about h2 {
  color: var(--blue);
  margin-bottom: 10px;
}
.about p {
  color: #555;
  font-size: 1.05em;
}

/* 🔹 Contact Section */
.contact {
  margin-top: 40px;
  padding-top: 25px;
  border-top: 2px solid #eee;
  text-align: center;
}
.contact h3 {
  color: var(--blue);
  font-size: 1.3em;
}
.contact p {
  margin: 10px 0;
  font-size: 1.05em;
}
.contact a {
  color: var(--blue);
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s;
}
.contact a:hover {
  color: #1f5f85;
  text-decoration: underline;
}

/* 🔹 Footer */
footer {
  text-align: center;
  padding: 15px;
  background: linear-gradient(90deg, var(--blue), var(--light-blue));
  color: white;
  border-radius: 15px 15px 0 0;
  font-size: 0.9em;
}

/* 🔹 Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>

<!-- 🔹 Navbar -->
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

<!-- 🔹 Header -->
<header>
  <h1>เกี่ยวกับระบบ Smart Home Dashboard</h1>
  <p>ติดตามข้อมูลสิ่งแวดล้อมและพลังงานไฟฟ้าแบบเรียลไทม์ เพื่อการจัดการพลังงานอย่างมีประสิทธิภาพ</p>
</header>

<!-- 🔹 Content -->
<div class="container">
  <section class="about">
    <h2>💡 วัตถุประสงค์ของระบบ</h2>
    <p>
      ระบบ Smart Home Dashboard ถูกพัฒนาขึ้นเพื่อใช้ในการตรวจสอบค่าต่าง ๆ ภายในบ้าน เช่น
      อุณหภูมิ ความชื้น ความเข้มแสง และพลังงานไฟฟ้า โดยแสดงผลผ่านกราฟและข้อมูลเรียลไทม์
      ช่วยให้ผู้ใช้งานสามารถจัดการการใช้พลังงานได้อย่างมีประสิทธิภาพและประหยัดมากขึ้น
    </p>
  </section>

  <section class="contact">
    <h3>📞 ติดต่อผู้พัฒนา</h3>
    <p><strong>ชื่อผู้พัฒนา:</strong> รัชชานนท์ ศรีสุวรรณ์</p>
    <p><strong>Email:</strong> <a href="mailto:za5531050057@gmail.com">za5531050057@gmail.com</a></p>
    <p><strong>โทรศัพท์:</strong> <a href="tel:0923712176">092-371-2176</a></p>
  </section>
</div>

<!-- 🔹 Footer -->
<footer>
  © <?= date('Y') ?> Smart Home Dashboard | พัฒนาโดย รัชชานนท์ ศรีสุวรรณ์
</footer>

</body>
</html>
