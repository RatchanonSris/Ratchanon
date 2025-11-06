<?php include 'db.php'; ?> 
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>📈 กราฟย้อนหลังรายปี</title>

<!-- โหลด Chart.js สำหรับสร้างกราฟ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- โหลดฟอนต์ภาษาไทย -->
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap" rel="stylesheet">

<style>
/* ==================== ส่วนตกแต่งหน้าเว็บ ==================== */
body {
  font-family: 'Prompt', sans-serif;
  margin: 0;
  background: #f4f6f8;
  color: #333;
}

/* แถบเมนูด้านบน */
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

/* สไตล์ลิงก์เมนู */
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

/* ส่วนหัว */
header {
  text-align: center;
  padding: 30px 20px;
}
header h1 { margin: 0; color: #2c3e50; }
header p { color: #777; margin-top: 8px; }

/* กล่องเนื้อหา */
.container {
  max-width: 1000px;
  margin: 30px auto;
  padding: 25px;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

/* ฟอร์มเลือกปี */
form {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}
form select, form button {
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

/* ส่วนแสดงข้อมูล */
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

/* ตาราง scroll ได้ */
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

<!-- ==================== เมนูหลัก ==================== -->
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

<!-- ==================== ส่วนหัวหน้า ==================== -->
<header>
  <h1>📈 กราฟย้อนหลังรายปี</h1>
  <p>เลือกปีเพื่อดูข้อมูลย้อนหลังทั้งหมดของปีนั้น</p>
</header>

<div class="container">
  <!-- ==================== ฟอร์มเลือกปี ==================== -->
  <form id="yearForm">
    <label>เลือกปี:</label>
    <select id="yearSelect" required>
      <option value="">-- เลือกปี --</option>
      <script>
        // สร้างรายการปี 5 ปีย้อนหลัง
        const yearNow = new Date().getFullYear();
        for(let y=yearNow; y>=yearNow-5; y--){
          document.write(`<option value="${y}">${y}</option>`);
        }
      </script>
    </select>
    <button type="submit">ดูกราฟรายปี</button>
  </form>

  <div class="status" id="statusText">กรุณาเลือกปีเพื่อเริ่มต้น</div>

  <!-- ==================== ตารางสรุปค่าเฉลี่ย ==================== -->
  <section id="summarySection">
    <h2>📊 สรุปค่าเฉลี่ยของปีที่เลือก</h2>
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

  <!-- ==================== กราฟข้อมูลสภาพแวดล้อม ==================== -->
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

  <!-- ==================== กราฟพลังงานไฟฟ้า 3 เฟส ==================== -->
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
// ==================== ตัวแปรพื้นฐาน ====================
const form=document.getElementById('yearForm');
const statusText=document.getElementById('statusText');
const sensorSection=document.getElementById('sensorSection');
const energySection=document.getElementById('energySection');
const summarySection=document.getElementById('summarySection');
let tempChart,humChart,lightChart,chartP1,chartP2,chartP3;

// ฟังก์ชันแปลงเวลาสำหรับแสดงในกราฟ
function formatTime(ts){
  const d=new Date(ts);
  return d.toLocaleString('th-TH',{month:'short',day:'numeric'});
}

// กำหนดค่าพื้นฐานให้กราฟ
const baseOptions={
  responsive:true,
  plugins:{legend:{position:'top'},tooltip:{mode:'index',intersect:false}},
  interaction:{mode:'nearest',axis:'x',intersect:false},
  scales:{x:{title:{display:true,text:'เดือน-วัน'},ticks:{maxTicksLimit:6}},y:{beginAtZero:true}}
};

// ==================== เติมข้อมูลลงในตารางเซ็นเซอร์ ====================
function populateSensorTable(sensors){
  const tbody=document.querySelector('#sensorTable tbody');
  tbody.innerHTML='';
  sensors.forEach(d=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`<td>${formatTime(d.timestamp)}</td><td>${d.temperature}</td><td>${d.humidity}</td><td>${d.light}</td>`;
    tbody.appendChild(tr);
  });
}

// ==================== เติมข้อมูลลงในตารางพลังงาน ====================
function populateEnergyTable(energy){
  const tbody=document.querySelector('#energyTable tbody');
  tbody.innerHTML='';
  energy.forEach(d=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`<td>${formatTime(d.created_date)}</td><td>${d.id}</td><td>${d.volt}</td><td>${d.amp}</td><td>${d.watt}</td><td>${d.energy}</td>`;
    tbody.appendChild(tr);
  });
}

// ==================== ✅ ฟังก์ชันคำนวณและแสดง “ค่าเฉลี่ย” ====================
function drawSummary(sensors, energy) {
  const tbody = document.querySelector('#summaryTable tbody');
  tbody.innerHTML = '';

  // ---- คำนวณค่าเฉลี่ยอุณหภูมิ ความชื้น และแสง ----
  const avgTemp = sensors.length ? (sensors.reduce((a, b) => a + b.temperature, 0) / sensors.length).toFixed(2) : 0;
  const avgHum = sensors.length ? (sensors.reduce((a, b) => a + b.humidity, 0) / sensors.length).toFixed(2) : 0;
  const avgLight = sensors.length ? (sensors.reduce((a, b) => a + b.light, 0) / sensors.length).toFixed(2) : 0;

  // ---- รวมค่าพลังงานไฟฟ้าในแต่ละเฟส ----
  const totalEnergy = [1, 2, 3].map(p => {
    const ph = energy.filter(e => e.id == p);
    return ph.reduce((a, b) => a + b.energy, 0).toFixed(2);
  });

  // ---- รวมพลังงานทั้งหมดของทุกเฟส ----
  const totalAll = totalEnergy.reduce((a, b) => a + parseFloat(b), 0).toFixed(2);

  // ---- สร้างข้อมูลสรุปสำหรับแสดงในตาราง ----
  const rows = [
    { name: 'อุณหภูมิ', value: avgTemp, unit: '°C' },
    { name: 'ความชื้น', value: avgHum, unit: '%' },
    { name: 'แสง', value: avgLight, unit: 'Lux' },
    { name: 'พลังงาน Phase 1', value: totalEnergy[0], unit: 'kWh' },
    { name: 'พลังงาน Phase 2', value: totalEnergy[1], unit: 'kWh' },
    { name: 'พลังงาน Phase 3', value: totalEnergy[2], unit: 'kWh' },
    { name: 'รวมพลังงานทั้งหมด', value: totalAll, unit: 'kWh' }
  ];

  // ---- แสดงผลในตาราง ----
  rows.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${r.name}</td><td>${r.value}</td><td>${r.unit}</td>`;
    tbody.appendChild(tr);
  });
}

// ==================== เมื่อกดปุ่ม “ดูกราฟรายปี” ====================
form.addEventListener('submit',e=>{
  e.preventDefault();
  const year=document.getElementById('yearSelect').value;
  if(!year)return;
  statusText.textContent="⏳ กำลังโหลดข้อมูล...";

  // ดึงข้อมูลจาก PHP ผ่าน API
  fetch(`get_history_year.php?year=${year}`)
  .then(res=>res.json())
  .then(data=>{
    if(data.error) return statusText.textContent="❌ โหลดข้อมูลไม่สำเร็จ";
    if(!data.sensor?.length && !data.energy?.length){
      statusText.textContent="ℹ️ ไม่มีข้อมูลในปีที่เลือก";
      sensorSection.classList.remove('visible');
      energySection.classList.remove('visible');
      summarySection.classList.remove('visible');
      return;
    }

    // แสดงส่วนต่าง ๆ
    sensorSection.classList.add('visible');
    energySection.classList.add('visible');
    summarySection.classList.add('visible');

    const s=data.sensor||[];
    const e=data.energy||[];
    const labels=s.map(d=>formatTime(d.timestamp));
    const temp=s.map(d=>+d.temperature);
    const hum=s.map(d=>+d.humidity);
    const light=s.map(d=>+d.light);

    // ✅ สร้างกราฟอุณหภูมิ
    if(tempChart)tempChart.destroy();
    tempChart=new Chart(document.getElementById('tempChart'),{
      type:'line',
      data:{labels,datasets:[{label:'อุณหภูมิ',data:temp,borderColor:'#e74c3c',fill:false}]},
      options:baseOptions
    });

    // ✅ สร้างกราฟความชื้น
    if(humChart)humChart.destroy();
    humChart=new Chart(document.getElementById('humChart'),{
      type:'line',
      data:{labels,datasets:[{label:'ความชื้น',data:hum,borderColor:'#3498db',fill:false}]},
      options:baseOptions
    });

    // ✅ สร้างกราฟแสง
    if(lightChart)lightChart.destroy();
    lightChart=new Chart(document.getElementById('lightChart'),{
      type:'line',
      data:{labels,datasets:[{label:'แสง',data:light,borderColor:'#f39c12',fill:false}]},
      options:baseOptions
    });

    populateSensorTable(s);

    // ✅ สร้างกราฟไฟฟ้าแต่ละเฟส
    const drawPhase=(id,canvas,ref)=>{
      const ph=e.filter(x=>x.id==id);
      const lbl=ph.map(x=>formatTime(x.created_date));
      const volt=ph.map(x=>+x.volt);
      const amp=ph.map(x=>+x.amp);
      const watt=ph.map(x=>+x.watt);
      const energy=ph.map(x=>+x.energy);
      if(ref)ref.destroy();
      return new Chart(canvas,{
        type:'line',
        data:{labels:lbl,datasets:[
          {label:'Volt',data:volt,borderColor:'#f39c12',fill:false},
          {label:'Amp',data:amp,borderColor:'#9b59b6',fill:false},
          {label:'Watt',data:watt,borderColor:'#3498db',fill:false},
          {label:'Energy',data:energy,borderColor:'#2ecc71',fill:false}
        ]},
        options:baseOptions
      });
    };
    chartP1=drawPhase(1,document.getElementById('chartPhase1'),chartP1);
    chartP2=drawPhase(2,document.getElementById('chartPhase2'),chartP2);
    chartP3=drawPhase(3,document.getElementById('chartPhase3'),chartP3);

    populateEnergyTable(e);

    // ✅ เรียกฟังก์ชันสรุปค่าเฉลี่ยทั้งหมด
    drawSummary(s,e);

    statusText.textContent=`✅ แสดงข้อมูลปี ${year}`;
  })
  .catch(()=>statusText.textContent="❌ โหลดข้อมูลไม่สำเร็จ");
});
</script>
</body>
</html>
