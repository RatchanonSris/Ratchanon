<?php
include 'db.php'; // ✅ เชื่อมต่อฐานข้อมูล

// 📅 รับวันที่ที่ผู้ใช้เลือกจาก URL (เช่น ?date=2025-10-10)
$date = isset($_GET['date']) ? $_GET['date'] : null;
$hasDate = !empty($date);

// สร้างตัวแปรสำหรับเก็บข้อมูล
$rows = [];          // ข้อมูลแต่ละบรรทัดจากฐานข้อมูล
$chart_labels = [];  // แกน X ของกราฟ (เวลา)
$chart_data = [];    // แกน Y ของกราฟ (พลังงาน)
$total_kwh = 0;      // พลังงานรวมทั้งหมดในวันนั้น
$bill = ['base'=>0,'ft'=>0,'vat'=>0,'service'=>0,'total'=>0]; // ค่าไฟแต่ละส่วน

// ✅ เมื่อผู้ใช้เลือกวันที่แล้ว
if ($hasDate) {
    // 📊 ดึงข้อมูลจากตาราง energy1 เฉพาะวันที่ที่เลือก
    $sql = "SELECT volt, amp, created_date 
            FROM energy1 
            WHERE DATE(created_date) = ? 
            ORDER BY created_date ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    // 🔄 วนลูปคำนวณพลังงานและเก็บข้อมูลแต่ละรายการ
    while ($row = $result->fetch_assoc()) {

        // 🧮 แปลงค่าที่ได้จากฐานข้อมูลเป็นตัวเลข
        $volt = floatval($row['volt']); // แรงดันไฟฟ้า (โวลต์)
        $amp  = floatval($row['amp']);  // กระแสไฟฟ้า (แอมป์)

        // ⚡ คำนวณกำลังไฟฟ้า (วัตต์)
        // สูตร: P = V × I
        $power = $volt * $amp;

        // ⚡ คำนวณพลังงานไฟฟ้า (กิโลวัตต์ชั่วโมง)
        // สูตร: Energy (kWh) = (V × I) ÷ 1000 ÷ 60
        // ➤ หาร 1000 เพื่อแปลงวัตต์เป็นกิโลวัตต์
        // ➤ หาร 60 เพราะข้อมูลเก็บทุก 1 นาที (1/60 ชั่วโมง)
        $kwh = $power / 1000 / 60;

        // 🔁 รวมค่าพลังงานสะสมทั้งหมด
        $total_kwh += $kwh;

        // 🗂 เก็บข้อมูลไว้แสดงผลภายหลัง (ในตารางและกราฟ)
        $rows[] = [
            'time'  => $row['created_date'],
            'volt'  => $volt,
            'amp'   => $amp,
            'power' => round($power, 2),
            'kwh'   => round($kwh, 4)
        ];
        $chart_labels[] = date("H:i", strtotime($row['created_date']));
        $chart_data[] = round($kwh, 4);
    }
    $stmt->close();

    // 💰 ฟังก์ชันคำนวณค่าไฟฟ้า
    // อ้างอิงอัตราค่าไฟตามโครงสร้างของการไฟฟ้าภูมิภาค (PEA)
    function calculateElectricBill($units) {
        // 🔸 ตรวจสอบปริมาณหน่วยที่ใช้
        if ($units <= 150) {
            // 🔹 กรณีใช้ไฟไม่เกิน 150 หน่วย/เดือน
            $tiers = [
                [15, 2.3488],
                [10, 2.9882],
                [10, 3.2405],
                [65, 3.6237],
                [50, 3.7171]
            ];
            $service = 8.19; // ค่าบริการประจำเดือน
        } else {
            // 🔹 กรณีใช้ไฟมากกว่า 150 หน่วย/เดือน
            $tiers = [
                [150, 3.2484],
                [250, 4.2218],
                [9999, 4.4217]
            ];
            $service = 38.22; // ค่าบริการประจำเดือน
        }

        // 🧮 คำนวณค่าไฟฐาน (Base cost)
        $remaining = $units;
        $base_cost = 0;
        foreach ($tiers as $tier) {
            $use = min($remaining, $tier[0]);
            $base_cost += $use * $tier[1];
            $remaining -= $use;
            if ($remaining <= 0) break;
        }

        // ⚙️ ค่า Ft (Fuel Adjustment Charge)
        // คิดที่ -15.90 สตางค์/หน่วย (แปลงเป็นบาทโดยหาร 100)
        $ft = $units * (-15.90 / 100);

        // 🧾 คำนวณ VAT (7%)
        $vat = ($base_cost + $ft + $service) * 0.07;

        // 💵 รวมค่าไฟทั้งหมด
        $total = $base_cost + $ft + $service + $vat;

        // 🔚 ส่งค่ากลับเป็นอาเรย์
        return [
            'base' => round($base_cost, 2),
            'ft' => round($ft, 2),
            'vat' => round($vat, 2),
            'service' => $service,
            'total' => round($total, 2)
        ];
    }

    // 📦 เรียกใช้ฟังก์ชันคำนวณค่าไฟ โดยส่งค่าพลังงานรวม (kWh)
    $bill = calculateElectricBill($total_kwh);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>⚡ ระบบคำนวณพลังงานไฟฟ้า (PZEM004T)</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">

<style>
/* 🎨 จัดรูปแบบสไตล์ให้เหมือนทุกหน้า */
body {
  font-family:'Prompt',sans-serif;
  margin:0;
  background:#f3f6fa;
  color:#333;
}

/* ✅ Navbar */
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

/* ✅ Header */
header {
  text-align: center;
  padding: 25px 20px;
}
header h1 {
  margin: 0;
  color: #2c3e50;
}
header p {
  color: #555;
  margin-top: 8px;
}

/* ✅ Container */
.container {
  max-width: 950px;
  margin: 25px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Form */
form { text-align:center; margin-bottom:20px; }
input,button {
  padding:8px 14px;
  border-radius:8px;
  border:1px solid #ccc;
  font-size:1em;
}
button {
  background:#2980b9;
  color:white;
  border:none;
  cursor:pointer;
  transition:0.3s;
}
button:hover { background:#1f5f85; }

/* Summary Cards */
.summary {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
  gap:15px;
  margin:20px 0;
}
.card {
  background:#ecf5ff;
  border-radius:15px;
  padding:15px;
  text-align:center;
  box-shadow:0 5px 10px rgba(0,0,0,0.05);
  transition:transform 0.2s;
}
.card:hover { transform:translateY(-5px); }
.card h3 { margin:0; color:#2c3e50; }
.card p { font-weight:bold; font-size:1.3em; color:#2980b9; }

/* Table */
.table-container {
  max-height:300px;
  overflow-y:auto;
  border:1px solid #eee;
  border-radius:10px;
  margin-top:20px;
}
table {
  width:100%;
  border-collapse:collapse;
  font-size:0.95em;
}
th,td {
  padding:10px;
  text-align:center;
  border-bottom:1px solid #eee;
}
th {
  background:#2980b9;
  color:white;
  position:sticky;
  top:0;
}
tr:hover { background:#f8fbff; }

/* Chart */
canvas {
  margin-top:30px;
  background:#fdfdfd;
  border-radius:15px;
  padding:10px;
}
.notice {
  text-align:center;
  color:#888;
  margin-top:40px;
}

/* Hint */
.hint-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  background:#2980b9;
  color:white;
  border:none;
  border-radius:50%;
  width:45px;
  height:45px;
  font-size:22px;
  cursor:pointer;
  box-shadow:0 4px 10px rgba(0,0,0,0.2);
  transition:0.3s;
  z-index:999;
}
.hint-toggle:hover { background:#1f5f85; transform:scale(1.1); }
.hint-box {
  display:none;
  position:fixed;
  top:80px; right:20px;
  width:330px;
  background:white;
  border-left:5px solid #2980b9;
  padding:15px;
  border-radius:12px;
  box-shadow:0 6px 20px rgba(0,0,0,0.2);
}
.hint-box h3 { margin-top:0; color:#2980b9; }
.hint-box code {
  background:#ecf5ff;
  padding:5px 8px;
  border-radius:6px;
  display:block;
  margin:4px 0;
  color:#2c3e50;
  font-size:0.9em;
}
</style>
</head>

<body>

<!-- ✅ เมนูนำทาง -->
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

<!-- 💡 ปุ่ม Hint แสดงสมการ -->
<button class="hint-toggle" id="hintBtn" title="ดูสมการ">💡</button>
<div class="hint-box" id="hintBox">
  <h3>📗 วิธีหาค่าพลังงานไฟฟ้า</h3>
  <code>กำลังไฟฟ้า (W) = แรงดัน (V) × กระแส (A)</code>
  <code>พลังงาน (kWh) = (แรงดัน × กระแส) ÷ 1000 ÷ 60</code>
  <small>※ ใช้เมื่อเก็บข้อมูลทุก 1 นาที</small>
  <hr>
  <h3>📘 การคำนวณค่าไฟฟ้า</h3>
  <code>ค่าไฟรวม = (ค่าไฟฐาน + ค่า Ft + ค่าบริการ) + VAT(7%)</code>
  <small>ค่า Ft = -15.90 สตางค์/หน่วย</small><br>
  <small>ค่าบริการ ≤150 หน่วย = 8.19 บาท / >150 หน่วย = 38.22 บาท</small>
</div>

<header>
  <h1>⚡ ระบบคำนวณพลังงานไฟฟ้า (PZEM004T)</h1>
  <p>เลือกวันที่เพื่อดูข้อมูลพลังงานและคำนวณค่าไฟฟ้าแบบอัตโนมัติ</p>
</header>

<div class="container">
  <form method="GET">
    <label>เลือกวันที่:</label>
    <input type="date" name="date" value="<?= $date ?>" required>
    <button type="submit">ดูข้อมูล</button>
  </form>

  <?php if ($hasDate): ?>
  <!-- 🧾 แสดงผลสรุปพลังงานและค่าไฟ -->
  <div class="summary">
    <div class="card"><h3>วันที่</h3><p><?= $date ?></p></div>
    <div class="card"><h3>รวมพลังงาน</h3><p><?= round($total_kwh,2) ?> kWh</p></div>
    <div class="card"><h3>ค่าไฟรวม</h3><p><?= $bill['total'] ?> บาท</p></div>
    <div class="card"><h3>ฐาน</h3><p><?= $bill['base'] ?> ฿</p></div>
    <div class="card"><h3>Ft</h3><p><?= $bill['ft'] ?> ฿</p></div>
    <div class="card"><h3>VAT</h3><p><?= $bill['vat'] ?> ฿</p></div>
  </div>

  <!-- 📈 กราฟ -->
  <canvas id="costChart" height="280"></canvas>

  <!-- 📋 ตารางข้อมูล -->
  <div class="table-container">
  <table>
    <tr>
      <th>เวลา</th><th>แรงดัน (V)</th><th>กระแส (A)</th><th>กำลังไฟฟ้า (W)</th><th>พลังงาน (kWh)</th>
    </tr>
    <?php if(count($rows)>0): foreach($rows as $r): ?>
      <tr>
        <td><?= date("H:i:s", strtotime($r['time'])) ?></td>
        <td><?= $r['volt'] ?></td>
        <td><?= $r['amp'] ?></td>
        <td><?= $r['power'] ?></td>
        <td><?= $r['kwh'] ?></td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="5">ไม่มีข้อมูลในวันที่เลือก</td></tr>
    <?php endif; ?>
  </table>
  </div>
  <?php else: ?>
    <p class="notice">🔍 กรุณาเลือกวันที่เพื่อดูข้อมูลพลังงานและค่าไฟฟ้า</p>
  <?php endif; ?>
</div>

<!-- 🎛 สคริปต์ Hint -->
<script>
const hintBtn = document.getElementById("hintBtn");
const hintBox = document.getElementById("hintBox");
hintBtn.addEventListener("click", ()=> {
  hintBox.style.display = (hintBox.style.display === "block") ? "none" : "block";
});
</script>

<!-- 📊 สคริปต์สร้างกราฟ -->
<?php if ($hasDate): ?>
<script>
const ctx = document.getElementById('costChart').getContext('2d');
new Chart(ctx,{
  type:'line',
  data:{
    labels: <?= json_encode($chart_labels) ?>,
    datasets:[{
      label:'พลังงาน (kWh)',
      data: <?= json_encode($chart_data) ?>,
      borderColor:'#f39c12',
      backgroundColor:'rgba(243,156,18,0.15)',
      fill:true, tension:0.4, pointRadius:3
    }]
  },
  options:{
    responsive:true,
    plugins:{ legend:{ position:'top' } },
    scales:{
      x:{ title:{display:true,text:'เวลา (ชั่วโมง:นาที)'}, ticks:{ maxTicksLimit:6 }},
      y:{ title:{display:true,text:'พลังงาน (kWh)'}, beginAtZero:true }
    }
  }
});
</script>
<?php endif; ?>

<!-- 🔄 รีเฟรชหน้าอัตโนมัติทุก 30 วินาที -->
<script>
setInterval(() => {
  location.reload();
}, 30000); // 30000 มิลลิวินาที = 30 วินาที
</script>

</body>
</html>
