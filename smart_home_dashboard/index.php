<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>🏠 Smart Home Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  background: #f3f6fa;
  color: #333;
}

/* ==== Navbar ==== */
nav {
  background: linear-gradient(90deg, #2980b9, #6dd5fa);
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 0 0 15px 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}
.nav-links {
  display: flex;
  gap: 25px;
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
.user-info { color: white; font-weight: 500; font-size: 0.9em; }

/* ==== Header ==== */
header {
  text-align: center;
  padding: 25px 20px;
}
h1 { color: #2c3e50; margin-bottom: 5px; }
#clock { font-size: 1.1em; color: #555; }

/* ==== Layout Container ==== */
.container {
  max-width: 1100px;
  margin: 20px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* ==== Section Title ==== */
.section-title {
  font-weight: 700;
  color: #2c3e50;
  font-size: 1.2em;
  margin: 20px 0 15px;
}

/* ==== Grid Layout ==== */
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 15px;
}

/* ==== Card ==== */
.card {
  background: linear-gradient(145deg, #ffffff, #eef3f9);
  border-radius: 15px;
  padding: 18px 15px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
  position: relative;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}
.card.alert {
  background: linear-gradient(145deg, #ffe6e6, #ffdcdc);
  box-shadow: 0 0 12px rgba(255, 0, 0, 0.25);
}
.value {
  font-size: 2em;
  font-weight: bold;
  color: #1f5f85;
}
.unit { font-size: 0.85em; color: #555; }
.status-symbol { margin-top: 6px; font-size: 1.1em; }
.status-text { font-size: 0.85em; color: #555; margin-top: 3px; }

/* 📱 Responsive */
@media (max-width: 600px) {
  .container { padding: 15px; }
  .grid { gap: 10px; }
  .card { padding: 15px 10px; }
  nav { flex-direction: column; gap: 10px; }
  .nav-links { flex-wrap: wrap; justify-content: center; }
}
</style>
</head>

<body>
<nav>
  <div class="nav-links">
    <a href="index.php">Dashboard</a>
    <a href="history.php">กราฟย้อนหลังรายวัน</a>
    <a href="history_range.php">กราฟย้อนหลังกำหนดวัน</a>
    <a href="history_mount.php">กราฟย้อนหลังรายเดือน</a>
    <a href="history_year.php">กราฟย้อนหลังรายปี</a>
    <a href="power.php">คำนวณไฟฟ้า</a>
    <a href="about.php">ติดต่อผู้พัฒนา</a>
    <a href="feedback.php">แบบสำรวจความพึงพอใจ</a>
  </div>
  <div class="user-info">⚙️ Smart Home System</div>
</nav>

<header>
  <h1>🏠 Smart Home Dashboard</h1>
  <p>ติดตามสถานะอุณหภูมิ ความชื้น แสงสว่าง และพลังงานไฟฟ้าแบบเรียลไทม์</p>
  <div id="clock">⏳ กำลังโหลดเวลา...</div>
</header>

<div class="container">

  <!-- 🌤️ สภาพแวดล้อม -->
  <div class="section">
    <div class="section-title">🌤️ ข้อมูลสภาพแวดล้อม</div>
    <div class="grid">
      <div class="card" id="card-temp">
        <h2>อุณหภูมิ</h2>
        <div class="value" id="temperature">--</div>
        <span class="unit">°C</span>
        <div class="status-symbol" id="temp-icon">--</div>
        <div class="status-text" id="temp-text">--</div>
      </div>

      <div class="card" id="card-hum">
        <h2>ความชื้น</h2>
        <div class="value" id="humidity">--</div>
        <span class="unit">%</span>
        <div class="status-symbol" id="hum-icon">--</div>
        <div class="status-text" id="hum-text">--</div>
      </div>

      <div class="card" id="card-light">
        <h2>แสงสว่าง</h2>
        <div class="value" id="light">--</div>
        <span class="unit">Lux</span>
        <div class="status-symbol" id="light-icon">--</div>
        <div class="status-text" id="light-text">--</div>
      </div>
    </div>
  </div>

  <!-- ⚡ พลังงานไฟฟ้า -->
  <div class="section">
    <div class="section-title">⚡ ข้อมูลพลังงานไฟฟ้า</div>
    <div class="grid">
      <div class="card" id="card-volt">
        <h2>แรงดันไฟฟ้า</h2>
        <div class="value" id="volt">--</div>
        <span class="unit">V</span>
        <div class="status-symbol" id="volt-icon">--</div>
        <div class="status-text" id="volt-text">--</div>
      </div>

      <div class="card" id="card-amp">
        <h2>กระแสไฟฟ้า</h2>
        <div class="value" id="amp">--</div>
        <span class="unit">A</span>
        <div class="status-symbol" id="amp-icon">--</div>
        <div class="status-text" id="amp-text">--</div>
      </div>

      <div class="card" id="card-watt">
        <h2>กำลังไฟฟ้า</h2>
        <div class="value" id="watt">--</div>
        <span class="unit">W</span>
        <div class="status-symbol" id="watt-icon">--</div>
        <div class="status-text" id="watt-text">--</div>
      </div>

      <div class="card" id="card-energy">
        <h2>พลังงานสะสม</h2>
        <div class="value" id="energy">--</div>
        <span class="unit">kWh</span>
        <div class="status-symbol" id="energy-icon">--</div>
        <div class="status-text" id="energy-text">--</div>
      </div>
    </div>
  </div>
</div>

<script>
// 🕒 อัปเดตเวลา
function updateClock() {
  const now = new Date();
  document.getElementById('clock').textContent = "🕒 " + now.toLocaleTimeString('th-TH');
}
setInterval(updateClock, 1000);
updateClock();

// 📊 ดึงข้อมูลจาก PHP
function updateSensorData() {
  fetch('get_latest.php')
  .then(res => res.json())
  .then(data => {
    updateValue('temperature', data.temperature);
    updateValue('humidity', data.humidity);
    updateValue('light', data.light);
    updateStatus(data.temperature, data.humidity, data.light);
  });
}

function updateEnergyData() {
  fetch('get_energy.php')
  .then(res => res.json())
  .then(data => {
    updateValue('volt', data.volt);
    updateValue('amp', data.amp);
    updateValue('watt', data.watt);
    updateValue('energy', data.energy);
    updatePowerStatus(data.volt, data.amp, data.watt, data.energy);
  });
}

// 🎨 อัปเดตค่าแบบ Smooth
function updateValue(id, value) {
  const el = document.getElementById(id);
  el.textContent = value;
}

// ✅ สถานะสิ่งแวดล้อม
function updateStatus(temp, hum, light) {
  let card;

  // อุณหภูมิ
  card = document.getElementById('card-temp');
  if (temp > 35) { card.classList.add("alert"); setStatus('temp', "🔴", "ร้อนมาก"); }
  else if (temp < 15) { card.classList.remove("alert"); setStatus('temp', "🟡", "เย็น"); }
  else { card.classList.remove("alert"); setStatus('temp', "🟢", "ปกติ"); }

  // ความชื้น
  card = document.getElementById('card-hum');
  if (hum < 30) { card.classList.add("alert"); setStatus('hum', "⚠️", "แห้ง"); }
  else if (hum > 80) { card.classList.add("alert"); setStatus('hum', "💧", "ชื้นมาก"); }
  else { card.classList.remove("alert"); setStatus('hum', "🟢", "ปกติ"); }

  // แสง
  card = document.getElementById('card-light');
  if (light > 3000) { card.classList.add("alert"); setStatus('light', "🌙", "มืด"); }
  else if (light < 50) { card.classList.remove("alert"); setStatus('light', "☀️", "สว่าง"); }
  else { card.classList.remove("alert"); setStatus('light', "💡", "ปกติ"); }
}

// ⚡ สถานะพลังงาน
function updatePowerStatus(volt, amp, watt, energy) {
  const voltCard = document.getElementById('card-volt');
  if (volt > 250 || volt < 210) voltCard.classList.add('alert');
  else voltCard.classList.remove('alert');
  setStatus('volt', (volt > 250) ? "⚠️" : (volt < 210 ? "⚡" : "🟢"), (volt > 250) ? "แรงดันสูง" : (volt < 210 ? "แรงดันต่ำ" : "ปกติ"));

  const ampCard = document.getElementById('card-amp');
  ampCard.classList.toggle('alert', amp > 20);
  setStatus('amp', (amp > 20) ? "🔥" : (amp < 0.1 ? "⚫" : "🟢"), (amp > 20) ? "กระแสสูง" : (amp < 0.1 ? "ไม่มีโหลด" : "ปกติ"));

  const wattCard = document.getElementById('card-watt');
  wattCard.classList.toggle('alert', watt > 3000);
  setStatus('watt', (watt > 3000) ? "⚡" : "✅", (watt > 3000) ? "ใช้ไฟสูง" : "ปกติ");

  const energyCard = document.getElementById('card-energy');
  setStatus('energy', "🔋", (energy > 5000) ? "ใช้พลังงานมาก" : "ปกติ");
}

// 🔧 ช่วยตั้งค่า Icon + ข้อความ
function setStatus(prefix, icon, text) {
  document.getElementById(prefix + '-icon').textContent = icon;
  document.getElementById(prefix + '-text').textContent = text;
}

// 🔄 อัปเดตทุก 10 วินาที
updateSensorData();
updateEnergyData();
setInterval(() => {
  updateSensorData();
  updateEnergyData();
}, 10000);
</script>

</body>
</html>
