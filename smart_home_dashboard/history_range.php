<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📈 กราฟย้อนหลังตามช่วงวันที่</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  background: #f4f6f8;
  color: #333;
}
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

header {
  text-align: center;
  padding: 30px 20px;
}
header h1 { margin: 0; color: #2c3e50; }
header p { color: #777; margin-top: 8px; }

.container {
  max-width: 950px;
  margin: 30px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}
form {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 20px;
}
form input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #ccc;
}
form button {
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  background: #2980b9;
  color: white;
  font-weight: bold;
  cursor: pointer;
}
form button:hover { background: #1f5f85; }

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

/* 💠 ตาราง */
.table-container {
  max-height: 250px;
  overflow-y: auto;
  overflow-x: auto;
  margin-top: 15px;
  border: 1px solid #ddd;
  border-radius: 10px;
}
table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9em;
  min-width: 600px;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
  white-space: nowrap;
}
th {
  background: #2980b9;
  color: white;
  position: sticky;
  top: 0;
}
tr:nth-child(even) { background: #f9f9f9; }
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
  <h1>📈 กราฟย้อนหลังตามช่วงวันที่</h1>
  <p>เลือกวันที่เริ่มต้นและสิ้นสุดเพื่อดูข้อมูลย้อนหลังแบบหลายวัน</p>
</header>

<div class="container">
  <form id="rangeForm">
    <label>จากวันที่:</label>
    <input type="date" id="startDate" required>
    <label>ถึงวันที่:</label>
    <input type="date" id="endDate" required>
    <button type="submit">ดูกราฟช่วงวันที่</button>
  </form>
  <div class="status" id="statusText">กรุณาเลือกช่วงวันที่เพื่อเริ่มต้น</div>

  <!-- 📊 กราฟสรุป -->
  <section id="summarySection">
    <h2>📊 สรุปข้อมูล (Summary)</h2>
    <canvas id="summaryChart" height="250"></canvas>
  </section>

  <!-- 🌡️ ข้อมูลสภาพแวดล้อม -->
  <section id="sensorSection">
    <h2>🌡️ ข้อมูลสภาพแวดล้อม (ช่วงวันที่)</h2>
    <canvas id="tempChart" height="200"></canvas>
    <canvas id="humChart" height="200"></canvas>
    <canvas id="lightChart" height="200"></canvas>
    <div class="table-container" id="sensorTable"></div>
  </section>

  <!-- ⚡ พลังงานไฟฟ้า -->
  <section id="energySection">
    <h2>⚡ พลังงานไฟฟ้า (3 เฟส)</h2>
    <h3 style="text-align:center;color:#e67e22;">Phase 1</h3>
    <canvas id="chartPhase1" height="220"></canvas>
    <div class="table-container" id="tableP1"></div>

    <h3 style="text-align:center;color:#9b59b6;">Phase 2</h3>
    <canvas id="chartPhase2" height="220"></canvas>
    <div class="table-container" id="tableP2"></div>

    <h3 style="text-align:center;color:#27ae60;">Phase 3</h3>
    <canvas id="chartPhase3" height="220"></canvas>
    <div class="table-container" id="tableP3"></div>
  </section>
</div>

<script>
const form = document.getElementById('rangeForm');
const statusText = document.getElementById('statusText');
const sensorSection = document.getElementById('sensorSection');
const energySection = document.getElementById('energySection');
const summarySection = document.getElementById('summarySection');
let tempChart, humChart, lightChart, chartP1, chartP2, chartP3, summaryChart;

// 🕒 แปลงเวลาให้อ่านง่าย
function formatTime(ts) {
  const d = new Date(ts);
  return d.toLocaleString('th-TH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// ⚙️ การตั้งค่ากราฟ
const baseOptions = {
  responsive: true,
  plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } },
  interaction: { mode: 'nearest', axis: 'x', intersect: false },
  scales: {
    x: { title: { display: true, text: 'วันที่-เวลา' }, ticks: { maxTicksLimit: 6 } },
    y: { beginAtZero: true }
  }
};

// 📊 สร้างตาราง
function createTable(headers, rows) {
  if (!rows.length) return '<p style="text-align:center;color:#777;">ไม่มีข้อมูล</p>';
  let html = '<table><tr>' + headers.map(h=>`<th>${h}</th>`).join('') + '</tr>';
  rows.forEach(r => {
    html += '<tr>' + r.map(v => `<td>${v}</td>`).join('') + '</tr>';
  });
  return html + '</table>';
}

// 🎯 วาดกราฟสรุป
function drawSummary(sensors, energy) {
  const avgTemp = sensors.length ? (sensors.reduce((a,b)=>a+b.temperature,0)/sensors.length).toFixed(2) : 0;
  const avgHum = sensors.length ? (sensors.reduce((a,b)=>a+b.humidity,0)/sensors.length).toFixed(2) : 0;
  const avgLight = sensors.length ? (sensors.reduce((a,b)=>a+b.light,0)/sensors.length).toFixed(2) : 0;
  const totalEnergy = [1,2,3].map(phaseId => {
    const ph = energy.filter(e => e.id==phaseId);
    return ph.reduce((a,b)=>a+b.energy,0).toFixed(2);
  });

  if (summaryChart) summaryChart.destroy();
  summaryChart = new Chart(document.getElementById('summaryChart'), {
    type: 'bar',
    data: {
      labels: ['อุณหภูมิ (°C)', 'ความชื้น (%)', 'แสง (Lux)', 'Phase 1 (kWh)', 'Phase 2 (kWh)', 'Phase 3 (kWh)'],
      datasets: [{
        label: 'สรุปค่าเฉลี่ย / รวม',
        data: [avgTemp, avgHum, avgLight, ...totalEnergy],
        backgroundColor: ['#e74c3c','#3498db','#f39c12','#f39c12','#9b59b6','#27ae60']
      }]
    },
    options: {
      responsive:true,
      plugins: { legend: { display:false }, tooltip:{ mode:'index', intersect:false } },
      scales: { y: { beginAtZero:true } }
    }
  });
}

// 🎯 เมื่อกดปุ่มดูกราฟ
form.addEventListener('submit', e => {
  e.preventDefault();
  const start = document.getElementById('startDate').value;
  const end = document.getElementById('endDate').value;
  if (!start || !end) return;
  statusText.textContent = "⏳ กำลังโหลดข้อมูล...";
  fetch(`get_history_range.php?start=${start}&end=${end}`)
    .then(res => res.json())
    .then(data => {
      if (data.error) return statusText.textContent = "❌ โหลดข้อมูลไม่สำเร็จ";
      if ((!data.sensor?.length) && (!data.energy?.length)) {
        statusText.textContent = "ℹ️ ไม่มีข้อมูลในช่วงวันที่เลือก";
        sensorSection.classList.remove('visible');
        energySection.classList.remove('visible');
        summarySection.classList.remove('visible');
        return;
      }

      sensorSection.classList.add('visible');
      energySection.classList.add('visible');
      summarySection.classList.add('visible');

      // 🌡️ สภาพแวดล้อม
      const s = data.sensor || [];
      const labels = s.map(d => formatTime(d.timestamp));
      const temp = s.map(d => +d.temperature);
      const hum = s.map(d => +d.humidity);
      const light = s.map(d => +d.light);

      if (tempChart) tempChart.destroy();
      tempChart = new Chart(document.getElementById('tempChart'), {
        type: 'line',
        data: { labels, datasets: [{ label:'อุณหภูมิ (°C)', data:temp, borderColor:'#e74c3c', fill:false }] },
        options: baseOptions
      });

      if (humChart) humChart.destroy();
      humChart = new Chart(document.getElementById('humChart'), {
        type: 'line',
        data: { labels, datasets: [{ label:'ความชื้น (%)', data:hum, borderColor:'#3498db', fill:false }] },
        options: baseOptions
      });

      if (lightChart) lightChart.destroy();
      lightChart = new Chart(document.getElementById('lightChart'), {
        type: 'line',
        data: { labels, datasets: [{ label:'แสงสว่าง (Lux)', data:light, borderColor:'#f39c12', fill:false }] },
        options: baseOptions
      });

      const sensorRows = s.map(d => [formatTime(d.timestamp), d.temperature, d.humidity, d.light]);
      document.getElementById('sensorTable').innerHTML = createTable(['เวลา', 'อุณหภูมิ (°C)', 'ความชื้น (%)', 'แสง (Lux)'], sensorRows);

      // ⚡ พลังงานไฟฟ้า
      const e = data.energy || [];
      const drawPhase = (id, canvas, chartRef, divId) => {
        const phase = e.filter(x => x.id == id);
        const lbl = phase.map(x => formatTime(x.created_date));
        const volt = phase.map(x => +x.volt);
        const amp = phase.map(x => +x.amp);
        const watt = phase.map(x => +x.watt);
        const energy = phase.map(x => +x.energy);

        if (chartRef) chartRef.destroy();
        const chart = new Chart(canvas, {
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

        const rows = phase.map(x => [formatTime(x.created_date), x.volt, x.amp, x.watt, x.energy]);
        document.getElementById(divId).innerHTML = createTable(['เวลา', 'Volt (V)', 'Amp (A)', 'Watt (W)', 'Energy (kWh)'], rows);
        return chart;
      };

      chartP1 = drawPhase(1, document.getElementById('chartPhase1'), chartP1, 'tableP1');
      chartP2 = drawPhase(2, document.getElementById('chartPhase2'), chartP2, 'tableP2');
      chartP3 = drawPhase(3, document.getElementById('chartPhase3'), chartP3, 'tableP3');

      // วาดกราฟสรุป
      drawSummary(s, e);

      statusText.textContent = `✅ แสดงข้อมูลระหว่าง ${start} ถึง ${end}`;
    })
    .catch(() => statusText.textContent = "❌ โหลดข้อมูลไม่สำเร็จ");
});
</script>
</body>
</html>
