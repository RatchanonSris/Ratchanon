<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📈 กราฟย้อนหลังรายเดือน</title>
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
  max-width: 1000px;
  margin: 30px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

form {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}
form input, form button {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #ccc;
}
form button {
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
  font-size: 0.95em;
  color: #666;
  margin-top: 10px;
}

/* ตาราง scrollable */
.table-container {
  max-height: 250px;
  overflow-x: auto;
  overflow-y: auto;
  margin-top: 15px;
  border: 1px solid #ddd;
  border-radius: 10px;
}
table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
  font-size: 0.9em;
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
  <h1>📈 กราฟย้อนหลังรายเดือน</h1>
  <p>เลือกเดือนเพื่อดูข้อมูลย้อนหลังทั้งหมดในเดือนนั้น</p>
</header>

<div class="container">
  <form id="monthForm">
    <label>เลือกเดือน:</label>
    <input type="month" id="monthSelect" required>
    <button type="submit">ดูกราฟรายเดือน</button>
  </form>
  <div class="status" id="statusText">กรุณาเลือกเดือนเพื่อเริ่มต้น</div>

  <section id="summarySection">
    <h2>📊 สรุปข้อมูลเดือนที่เลือก</h2>
    <canvas id="summaryChart" height="250"></canvas>

    <h3>📋 ตารางสรุปค่าเฉลี่ย / รวม</h3>
    <div class="table-container">
      <table id="summaryTable">
        <thead>
          <tr>
            <th>หมวด</th>
            <th>ค่าเฉลี่ย / รวม</th>
            <th>หน่วย</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <section id="sensorSection">
    <h2>🌡️ ข้อมูลสภาพแวดล้อม</h2>
    <canvas id="tempChart" height="200"></canvas>
    <canvas id="humChart" height="200"></canvas>
    <canvas id="lightChart" height="200"></canvas>

    <h3>รายละเอียดข้อมูล</h3>
    <div class="table-container">
      <table id="sensorTable">
        <thead>
          <tr><th>เวลา</th><th>อุณหภูมิ (°C)</th><th>ความชื้น (%)</th><th>แสง (Lux)</th></tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <section id="energySection">
    <h2>⚡ พลังงานไฟฟ้า 3 เฟส</h2>
    <canvas id="chartPhase1" height="200"></canvas>
    <canvas id="chartPhase2" height="200"></canvas>
    <canvas id="chartPhase3" height="200"></canvas>

    <h3>รายละเอียดข้อมูล</h3>
    <div class="table-container">
      <table id="energyTable">
        <thead>
          <tr><th>เวลา</th><th>Phase</th><th>Volt</th><th>Amp</th><th>Watt</th><th>Energy</th></tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>
</div>

<script>
const form = document.getElementById('monthForm');
const statusText = document.getElementById('statusText');
const sensorSection = document.getElementById('sensorSection');
const energySection = document.getElementById('energySection');
const summarySection = document.getElementById('summarySection');
let tempChart, humChart, lightChart, chartP1, chartP2, chartP3, summaryChart;

function formatTime(ts){
  const d = new Date(ts);
  return d.toLocaleString('th-TH', { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}

const baseOptions = {
  responsive:true,
  plugins:{ legend:{ position:'top'}, tooltip:{ mode:'index', intersect:false } },
  interaction:{ mode:'nearest', axis:'x', intersect:false },
  scales:{ x:{ title:{ display:true, text:'วันที่-เวลา' }, ticks:{ maxTicksLimit:6 } }, y:{ beginAtZero:true } }
};

function drawSummary(sensors, energy){
  // 🔸 คำนวณค่าเฉลี่ย
  const avgTemp = sensors.length ? (sensors.reduce((a,b)=>a+b.temperature,0)/sensors.length).toFixed(2):0;
  const avgHum = sensors.length ? (sensors.reduce((a,b)=>a+b.humidity,0)/sensors.length).toFixed(2):0;
  const avgLight = sensors.length ? (sensors.reduce((a,b)=>a+b.light,0)/sensors.length).toFixed(2):0;
  const totalEnergy = [1,2,3].map(phaseId=>{
    const ph = energy.filter(e=>e.id==phaseId);
    return ph.reduce((a,b)=>a+b.energy,0).toFixed(2);
  });

  // 🔹 วาดกราฟสรุป
  if(summaryChart) summaryChart.destroy();
  summaryChart = new Chart(document.getElementById('summaryChart'),{
    type:'bar',
    data:{
      labels:['อุณหภูมิ (°C)','ความชื้น (%)','แสง (Lux)','Phase 1','Phase 2','Phase 3'],
      datasets:[{
        label:'สรุปค่าเฉลี่ย/รวม',
        data:[avgTemp,avgHum,avgLight,...totalEnergy],
        backgroundColor:['#e74c3c','#3498db','#f39c12','#9b59b6','#2ecc71','#1abc9c']
      }]
    },
    options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
  });

  // 🔹 เพิ่มข้อมูลในตารางสรุป
  const tbody = document.querySelector('#summaryTable tbody');
  tbody.innerHTML = '';
  const rows = [
    { name:'อุณหภูมิ', value:avgTemp, unit:'°C' },
    { name:'ความชื้น', value:avgHum, unit:'%' },
    { name:'แสง', value:avgLight, unit:'Lux' },
    { name:'พลังงาน Phase 1', value:totalEnergy[0], unit:'kWh' },
    { name:'พลังงาน Phase 2', value:totalEnergy[1], unit:'kWh' },
    { name:'พลังงาน Phase 3', value:totalEnergy[2], unit:'kWh' }
  ];
  rows.forEach(r=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${r.name}</td><td>${r.value}</td><td>${r.unit}</td>`;
    tbody.appendChild(tr);
  });
}

function populateSensorTable(sensors){
  const tbody = document.querySelector('#sensorTable tbody');
  tbody.innerHTML = '';
  sensors.forEach(d=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${formatTime(d.timestamp)}</td><td>${d.temperature}</td><td>${d.humidity}</td><td>${d.light}</td>`;
    tbody.appendChild(tr);
  });
}

function populateEnergyTable(energy){
  const tbody = document.querySelector('#energyTable tbody');
  tbody.innerHTML = '';
  energy.forEach(d=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${formatTime(d.created_date)}</td><td>${d.id}</td><td>${d.volt}</td><td>${d.amp}</td><td>${d.watt}</td><td>${d.energy}</td>`;
    tbody.appendChild(tr);
  });
}

form.addEventListener('submit', e=>{
  e.preventDefault();
  const month = document.getElementById('monthSelect').value;
  if(!month) return;
  statusText.textContent="⏳ กำลังโหลดข้อมูล...";

  fetch(`get_history_month.php?month=${month}`)
  .then(res=>res.json())
  .then(data=>{
    if(data.error) return statusText.textContent="❌ โหลดข้อมูลไม่สำเร็จ";
    if(!data.sensor?.length && !data.energy?.length){
      statusText.textContent="ℹ️ ไม่มีข้อมูลในเดือนที่เลือก";
      sensorSection.classList.remove('visible');
      energySection.classList.remove('visible');
      summarySection.classList.remove('visible');
      return;
    }

    sensorSection.classList.add('visible');
    energySection.classList.add('visible');
    summarySection.classList.add('visible');

    // Sensor charts
    const s = data.sensor||[];
    const labels = s.map(d=>formatTime(d.timestamp));
    const temp = s.map(d=>+d.temperature);
    const hum = s.map(d=>+d.humidity);
    const light = s.map(d=>+d.light);

    if(tempChart) tempChart.destroy();
    tempChart = new Chart(document.getElementById('tempChart'), {type:'line', data:{labels, datasets:[{label:'อุณหภูมิ',data:temp,borderColor:'#e74c3c',fill:false}]}, options:baseOptions});
    if(humChart) humChart.destroy();
    humChart = new Chart(document.getElementById('humChart'), {type:'line', data:{labels, datasets:[{label:'ความชื้น',data:hum,borderColor:'#3498db',fill:false}]}, options:baseOptions});
    if(lightChart) lightChart.destroy();
    lightChart = new Chart(document.getElementById('lightChart'), {type:'line', data:{labels, datasets:[{label:'แสง',data:light,borderColor:'#f39c12',fill:false}]}, options:baseOptions});
    populateSensorTable(s);

    // Energy charts
    const e = data.energy||[];
    const drawPhase=(id, canvas, chartRef)=>{
      const phase=e.filter(x=>x.id==id);
      const lbl=phase.map(x=>formatTime(x.created_date));
      const volt=phase.map(x=>+x.volt);
      const amp=phase.map(x=>+x.amp);
      const watt=phase.map(x=>+x.watt);
      const energy=phase.map(x=>+x.energy);
      if(chartRef) chartRef.destroy();
      return new Chart(canvas,{type:'line', data:{labels:lbl, datasets:[
        {label:'Volt', data:volt, borderColor:'#f39c12', fill:false},
        {label:'Amp', data:amp, borderColor:'#9b59b6', fill:false},
        {label:'Watt', data:watt, borderColor:'#3498db', fill:false},
        {label:'Energy', data:energy, borderColor:'#2ecc71', fill:false}
      ]}, options:baseOptions});
    };
    chartP1 = drawPhase(1, document.getElementById('chartPhase1'), chartP1);
    chartP2 = drawPhase(2, document.getElementById('chartPhase2'), chartP2);
    chartP3 = drawPhase(3, document.getElementById('chartPhase3'), chartP3);
    populateEnergyTable(e);

    drawSummary(s,e);
    statusText.textContent=`✅ แสดงข้อมูลเดือน ${month}`;
  })
  .catch(()=>statusText.textContent="❌ โหลดข้อมูลไม่สำเร็จ");
});
</script>
</body>
</html>
