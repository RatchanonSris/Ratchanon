<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📊 กราฟย้อนหลังตามวันที่</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  background: #f4f6f8;
  color: #333;
}

/* ==== Navbar (เหมือนหน้าอื่นทุกประการ) ==== */
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
  transition: color 0.3s;
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

/* ==== Header ==== */
header {
  text-align: center;
  padding: 30px 20px;
}
header h1 {
  margin: 0;
  color: #2c3e50;
}
header p {
  color: #777;
  margin-top: 8px;
}

/* ==== Container (เท่ากับทุกหน้า) ==== */
.container {
  max-width: 950px;
  margin: 30px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

/* ==== Form ==== */
form {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
form input[type="date"] {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #ccc;
  font-size: 1em;
}
form button {
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  background: #2980b9;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}
form button:hover { background: #1f5f85; }

/* ==== Section ==== */
section {
  margin-top: 40px;
  display: none;
  opacity: 0;
  transition: opacity 0.8s ease;
}
section.visible {
  display: block;
  opacity: 1;
}
section h2 {
  color: #2c3e50;
  text-align: center;
  background: #ecf0f1;
  border-left: 5px solid #2980b9;
  padding: 10px;
  border-radius: 10px;
}
canvas {
  margin-top: 25px;
  border-radius: 15px;
  background: #fdfdfd;
  width: 100%;
}
.status {
  text-align: center;
  font-size: 0.9em;
  color: #666;
  margin-top: 10px;
}

/* 📱 Responsive */
@media (max-width: 600px) {
  .container { padding: 15px; }
  canvas { margin-top: 15px; }
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

<header>
  <h1>📊 กราฟย้อนหลังตามวันที่</h1>
  <p>เลือกวันที่เพื่อดูข้อมูลย้อนหลังจากเซนเซอร์และพลังงานไฟฟ้า</p>
</header>

<div class="container">
  <form id="dateForm">
    <input type="date" id="selectedDate" required>
    <button type="submit">ดูกราฟ</button>
  </form>
  <div class="status" id="statusText">กรุณาเลือกวันที่เพื่อเริ่มต้น</div>

  <!-- 🌤️ ข้อมูลสภาพแวดล้อม -->
  <section id="sensorSection">
    <h2>🌤️ ข้อมูลสภาพแวดล้อม</h2>
    <canvas id="tempChart" height="200"></canvas>
    <canvas id="humChart" height="200"></canvas>
    <canvas id="lightChart" height="200"></canvas>
  </section>

  <!-- ⚡ พลังงานไฟฟ้า -->
  <section id="energySection">
    <h2>⚡ พลังงานไฟฟ้า (3 เฟส)</h2>
    <h3 style="text-align:center;color:#e67e22;">Phase 1</h3>
    <canvas id="chartPhase1" height="220"></canvas>
    <h3 style="text-align:center;color:#9b59b6;">Phase 2</h3>
    <canvas id="chartPhase2" height="220"></canvas>
    <h3 style="text-align:center;color:#27ae60;">Phase 3</h3>
    <canvas id="chartPhase3" height="220"></canvas>
  </section>
</div>

<script>
const form = document.getElementById('dateForm');
const statusText = document.getElementById('statusText');
const sensorSection = document.getElementById('sensorSection');
const energySection = document.getElementById('energySection');
let tempChart, humChart, lightChart, chartP1, chartP2, chartP3;

// 🕒 แปลงเวลา
function formatTime(ts) {
  const d = new Date(ts);
  return d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
}

// ⚙️ การตั้งค่ากราฟ
const baseOptions = {
  responsive: true,
  plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } },
  interaction: { mode: 'nearest', axis: 'x', intersect: false },
  scales: {
    x: { title: { display: true, text: 'เวลา' }, ticks: { maxTicksLimit: 6 }, grid: { color: 'rgba(0,0,0,0.05)' } },
    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.08)' } }
  }
};

// 🎯 เมื่อกดดูกราฟ
form.addEventListener('submit', e => {
  e.preventDefault();
  const date = document.getElementById('selectedDate').value;
  if (!date) return;
  localStorage.setItem('lastDate', date);
  loadCharts(date);
});

// 📊 โหลดข้อมูลกราฟ
function loadCharts(date) {
  statusText.textContent = "⏳ กำลังโหลดข้อมูล...";
  fetch(`get_history.php?date=${date}`)
  .then(res => res.json())
  .then(data => {
    if (data.error) return statusText.textContent = "❌ โหลดข้อมูลไม่สำเร็จ";
    if ((!data.sensor?.length) && (!data.energy?.length)) {
      statusText.textContent = "ℹ️ ไม่มีข้อมูลในวันที่เลือก";
      sensorSection.classList.remove('visible');
      energySection.classList.remove('visible');
      return;
    }

    sensorSection.classList.add('visible');
    energySection.classList.add('visible');

    const s = data.sensor || [];
    const labels = s.map(d => formatTime(d.timestamp));
    const temp = s.map(d => +d.temperature);
    const hum = s.map(d => +d.humidity);
    const light = s.map(d => +d.light);

    if (tempChart) tempChart.destroy();
    tempChart = new Chart(document.getElementById('tempChart'), {
      type: 'line',
      data: { labels, datasets: [{ label:'อุณหภูมิ (°C)', data:temp, borderColor:'#e74c3c', fill:false, tension:0.3 }] },
      options: baseOptions
    });

    if (humChart) humChart.destroy();
    humChart = new Chart(document.getElementById('humChart'), {
      type: 'line',
      data: { labels, datasets: [{ label:'ความชื้น (%)', data:hum, borderColor:'#3498db', fill:false, tension:0.3 }] },
      options: baseOptions
    });

    if (lightChart) lightChart.destroy();
    lightChart = new Chart(document.getElementById('lightChart'), {
      type: 'line',
      data: { labels, datasets: [{ label:'แสงสว่าง (Lux)', data:light, borderColor:'#f39c12', fill:false, tension:0.3 }] },
      options: baseOptions
    });

    const e = data.energy || [];
    const drawPhase = (id, canvas, chartRef) => {
      const phase = e.filter(x => x.id == id);
      const lbl = phase.map(x => formatTime(x.created_date));
      const volt = phase.map(x => +x.volt);
      const amp = phase.map(x => +x.amp);
      const watt = phase.map(x => +x.watt);
      const energy = phase.map(x => +x.energy);

      if (chartRef) chartRef.destroy();
      return new Chart(canvas, {
        type: 'line',
        data: {
          labels: lbl,
          datasets: [
            { label:'Volt (V)', data:volt, borderColor:'#f39c12', fill:false },
            { label:'Amp (A)', data:amp, borderColor:'#9b59b6', fill:false },
            { label:'Watt (W)', data:watt, borderColor:'#3498db', fill:false },
            { label:'Energy (kWh)', data:energy, borderColor:'#2ecc71', fill:false }
          ]
        },
        options: baseOptions
      });
    };

    chartP1 = drawPhase(1, document.getElementById('chartPhase1'), chartP1);
    chartP2 = drawPhase(2, document.getElementById('chartPhase2'), chartP2);
    chartP3 = drawPhase(3, document.getElementById('chartPhase3'), chartP3);
    statusText.textContent = "✅ แสดงข้อมูลวันที่ " + date;
  })
  .catch(() => statusText.textContent = "❌ โหลดข้อมูลไม่สำเร็จ");
}

// 📅 โหลดวันที่ล่าสุด
window.addEventListener('load', () => {
  const last = localStorage.getItem('lastDate');
  if (last) {
    document.getElementById('selectedDate').value = last;
    loadCharts(last);
  }
});
</script>
</body>
</html>
