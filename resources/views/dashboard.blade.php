@extends('layouts.app')

@section('title','ITani — Dashboard')

@section('top-right')
  <span class="chip" id="mqttStatus"><span class="dot off"></span> Disconnected</span>
@endsection

@section('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>

  <style>
    .sensor-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; }
    .sensor{
      background: linear-gradient(135deg, rgba(2,6,23,.92), rgba(15,23,42,.92));
      border: 1px solid rgba(148,163,184,.25);
      border-radius: 16px;
      padding: 14px 16px;
      box-shadow: 0 14px 30px rgba(0,0,0,.18);
      color:#e5e7eb;
    }
    .sensor.ok{ border-color:#16a34a; }
    .sensor.warn{ border-color:#facc15; }
    .sensor-card.danger{
      border: 2px solid #ef4444;
      box-shadow: 0 0 0 4px rgba(239,68,68,.15);
    }
    .sensor h3{ font-size:.95rem; margin:0 0 6px; opacity:.95; display:flex; gap:.55rem; align-items:center; }
    .sensor .val{ font-size:1.75rem; font-weight:900; line-height:1.05; margin:0; }
    .sensor .unit{ font-size:.9rem; opacity:.8; margin-left:6px; }
    .sensor .lbl{ font-size:.82rem; opacity:.85; margin:6px 0 0; }

    .chart-wrap{ height: 280px; }
    .logbox{ max-height: 290px; overflow:auto; }
    .logitem{ border-bottom: 1px solid var(--border); padding:.7rem 0; }
    .tag{
      border-radius: 999px; font-size:.72rem; font-weight:900;
      padding:.12rem .55rem; border: 1px solid var(--border);
    }
    .tag.manual{ background: rgba(59,130,246,.12); color: #1d4ed8; }
    .tag.otomatis{ background: rgba(34,197,94,.14); color: #166534; }
    .log-meta{
      min-width: 150px;
      text-align: right;
      font-size: .85rem;
      font-variant-numeric: tabular-nums;
      line-height: 1.2;
    }
    .log-date{ opacity: .78; }
    .log-time{ opacity: .95; font-weight: 800; }
    .log-dur{ opacity: .78; margin-top: 2px; }
    .dot.on{ background:#22c55e; box-shadow: 0 0 6px rgba(34,197,94,.75); }
    .dot.off{ background:#dc2626; box-shadow: 0 0 6px rgba(220,38,38,.75); }
    /* Scrollbar khusus Riwayat Penyiraman */
    #logList.logbox{
      scrollbar-width: thin; /* Firefox */
      scrollbar-color: rgba(100,116,139,.55) transparent; /* thumb track */
    }

    /* WebKit: Safari/Chrome/Edge */
    #logList.logbox::-webkit-scrollbar{
      width: 10px;
    }
    #logList.logbox::-webkit-scrollbar-track{
      background: transparent;
    }
    #logList.logbox::-webkit-scrollbar-thumb{
      background: rgba(100,116,139,.55);
      border-radius: 999px;
      border: 2px solid transparent;     /* bikin thumb rapi & gak “lompat” */
      background-clip: padding-box;
    }
    #logList.logbox::-webkit-scrollbar-thumb:hover{
      background: rgba(100,116,139,.75);
      background-clip: padding-box;
    }

    
#logList .logitem{
  display: grid !important;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: start;
}

#logList .logitem .text-end{
  margin-right: 22px;
  min-width: 92px;
  white-space: nowrap;
  text-align: right;
}

</style>
@endsection

@section('content')
  <div class="mb-3">
    <h3 class="fw-bold mb-1">Dashboard Monitoring Okra Merah</h3>
    <div class="muted">Pantau kondisi tanah, kontrol pompa, dan lihat riwayat penyiraman dalam satu halaman.</div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="fw-bold mb-0">Monitoring Sensor Tanah 7-in-1</h5>
          <small class="muted" id="lastUpdate">Last update: –</small>
        </div>

        <div class="sensor-grid">
          <div class="sensor" id="card-temp">
            <h3><i class="bi bi-thermometer-half"></i> Suhu</h3>
            <p class="val"><span id="tempNumber">--</span> <span class="unit">°C</span></p>
            <p class="lbl" id="tempLabel">Menunggu data...</p>
          </div>

          <div class="sensor" id="card-humi">
            <h3><i class="bi bi-droplet"></i> Kelembapan</h3>
            <p class="val"><span id="humiNumber">--</span> <span class="unit">%</span></p>
            <p class="lbl" id="humiLabel">Menunggu data...</p>
          </div>

          <div class="sensor" id="card-ph">
            <h3><i class="bi bi-activity"></i> pH Tanah</h3>
            <p class="val"><span id="phNumber">--</span></p>
            <p class="lbl" id="phLabel">Menunggu data...</p>
          </div>

          <div class="sensor ok" id="card-n">
            <h3><i class="bi bi-flower2"></i> Nitrogen (N)</h3>
            <p class="val"><span id="nNumber">--</span> <span class="unit">mg/kg</span></p>
            <p class="lbl" id="nLabel">Menunggu data...</p>
          </div>

          <div class="sensor ok" id="card-p">
            <h3><i class="bi bi-flower3"></i> Fosfor (P)</h3>
            <p class="val"><span id="pNumber">--</span> <span class="unit">mg/kg</span></p>
            <p class="lbl" id="pLabel">Menunggu data...</p>
          </div>

          <div class="sensor ok" id="card-k">
            <h3><i class="bi bi-leaf"></i> Kalium (K)</h3>
            <p class="val"><span id="kNumber">--</span> <span class="unit">mg/kg</span></p>
            <p class="lbl" id="kLabel">Menunggu data...</p>
          </div>

          <div class="sensor ok" id="card-ec">
            <h3><i class="bi bi-lightning-charge"></i> EC / Konduktivitas</h3>
            <p class="val"><span id="ecNumber">--</span> <span class="unit">µS/cm</span></p>
            <p class="lbl" id="ecLabel">Menunggu data...</p>
          </div>
        </div>
      </div>

      <div class="cardx">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold">Grafik Perubahan Data Sensor</div>
            <small class="muted">Realtime dari MQTT</small>
          </div>
          <div class="chart-wrap">
            <canvas id="sensorChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="cardx mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold">Kontrol Penyiraman</div>
            <span id="pumpStatus" class="badge text-bg-secondary">Pompa: UNKNOWN</span>
          </div>

          <button class="btn btn-success w-100 fw-bold mb-3" id="btnTogglePump" style="border-radius:14px;padding:.75rem 1rem;">
            Nyalakan Pompa
          </button>

          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-bold" style="font-size:.92rem">Mode Otomatis</div>
              <div class="muted" style="font-size:.85rem">ESP akan mengatur pompa berdasarkan kelembapan tanah.</div>
            </div>
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" id="autoMode" checked>
            </div>
          </div>
        </div>
      </div>

      <div class="cardx">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold">Riwayat Penyiraman</div>
            <button class="btn btn-sm btn-outline-secondary" id="btnRefreshLogs">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
          <div class="logbox" id="logList"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const MQTT_CFG = @json($mqtt);
  const TOPIC = @json($topics);

  // ===== Chart history (server-side, biar tidak reset saat refresh) =====
  const SENSOR_HISTORY = @json($history ?? []);
  const MAX_POINTS = @json($limit ?? 20);

  const mqttStatus = document.getElementById("mqttStatus");
  const pumpStatus = document.getElementById("pumpStatus");
  const btnTogglePump = document.getElementById("btnTogglePump");
  const autoMode = document.getElementById("autoMode");
  const logList = document.getElementById("logList");
  const lastUpdate = document.getElementById("lastUpdate");

  const phNumber = document.getElementById("phNumber");
  const humiNumber = document.getElementById("humiNumber");
  const tempNumber = document.getElementById("tempNumber");
  const nNumber = document.getElementById("nNumber");
  const pNumber = document.getElementById("pNumber");
  const kNumber = document.getElementById("kNumber");
  const ecNumber = document.getElementById("ecNumber");

  const phLabel = document.getElementById("phLabel");
  const humiLabel = document.getElementById("humiLabel");
  const tempLabel = document.getElementById("tempLabel");
  const nLabel = document.getElementById("nLabel");
  const pLabel = document.getElementById("pLabel");
  const kLabel = document.getElementById("kLabel");
  const ecLabel = document.getElementById("ecLabel");

  function setMQTTStatus(connected){
    mqttStatus.innerHTML = `<span class="dot ${connected ? "on":"off"}"></span> ${connected ? "Connected":"Disconnected"}`;
  }
  function setCardStatus(id, kind){
    const el = document.getElementById(id);
    if (!el) return;

    // bersihin semua status yang mungkin
    el.classList.remove('ok','warn','danger');

    // pasang status baru
    if (kind) el.classList.add(kind);
  }

  function toNum(v){ const n = Number(v); return Number.isFinite(n) ? n : null; }

  let mqttClient = null;
  let isAutoMode = true;
  let currentPumpStatus = "UNKNOWN";
  let lastCommandSource = null;
  let lastCommandTime = 0;

  function setPumpStatusText(status){
    currentPumpStatus = status;
    pumpStatus.textContent = `Pompa: ${status}`;
    pumpStatus.className = 'badge ' + (status === "ON" ? 'text-bg-success' : (status === "OFF" ? 'text-bg-danger' : 'text-bg-secondary'));
    btnTogglePump.textContent = (status === "ON") ? "Matikan Pompa" : "Nyalakan Pompa";
  }

  // Chart
  const chartLabels = [];
  const series = { ph:[], humi:[], temp:[], n:[], p:[], k:[], ec:[] };

  let lastChartTs = null; // "YYYY-MM-DD HH:MM:SS"

  function normTs(ts){
    if (!ts) return null;
    ts = String(ts);
    if (ts.length >= 19) {
      const t19 = ts.substring(0, 19);
      return t19.replace('T', ' ');
    }
    return ts;
  }

  function tsToLabel(ts){
    const t = normTs(ts);
    if (!t) return new Date().toLocaleTimeString("id-ID", { hour12:false });
    // ambil HH:MM:SS kalau format lengkap
    if (t.length >= 19) return t.substring(11, 19);
    return t;
  }

  // isi data awal dari database (seperti versi JSP)
  try {
    if (Array.isArray(SENSOR_HISTORY)) {
      for (const item of SENSOR_HISTORY) {
        const ts = normTs(item.time);
        chartLabels.push(tsToLabel(ts));
        series.ph.push(toNum(item.ph));
        series.humi.push(toNum(item.humi));
        series.temp.push(toNum(item.temp));
        series.n.push(toNum(item.n));
        series.p.push(toNum(item.p));
        series.k.push(toNum(item.k));
        series.ec.push(toNum(item.ec));
        if (ts) lastChartTs = ts;
      }
    }
  } catch (e) { /* ignore */ }

  const ctx = document.getElementById("sensorChart").getContext("2d");
  const sensorChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: chartLabels,
      datasets: [
        { label:"pH Tanah", data: series.ph, tension: .3 },
        { label:"Kelembapan (%)", data: series.humi, tension: .3 },
        { label:"Suhu (°C)", data: series.temp, tension: .3 },
        { label:"Nitrogen (N mg/kg)", data: series.n, tension: .3 },
        { label:"Fosfor (P mg/kg)", data: series.p, tension: .3 },
        { label:"Kalium (K mg/kg)", data: series.k, tension: .3 },
        { label:"EC (µS/cm)", data: series.ec, tension: .3 },
      ]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:"bottom" } }, scales:{ y:{ beginAtZero:true } } }
  });

  function addSensorPoint(d){
    const ts = normTs(d.time);
    // kalau ada timestamp dari DB, cegah duplikasi saat refresh / latest()
    if (ts && lastChartTs && ts <= lastChartTs) return;

    chartLabels.push(tsToLabel(ts));
    series.ph.push(toNum(d.ph));
    series.humi.push(toNum(d.humi));
    series.temp.push(toNum(d.temp));
    series.n.push(toNum(d.n));
    series.p.push(toNum(d.p));
    series.k.push(toNum(d.k));
    series.ec.push(toNum(d.ec));

    while (chartLabels.length > MAX_POINTS){
      chartLabels.shift();
      Object.values(series).forEach(arr => arr.shift());
    }

    if (ts) lastChartTs = ts;
    sensorChart.update();
  }

  function updateSensorUI(data){
  const ph   = (typeof data.ph === "number")   ? data.ph   : toNum(data.ph);
  const humi = (typeof data.humi === "number") ? data.humi : toNum(data.humi ?? data.moisture ?? data.soil_moisture);
  const temp = (typeof data.temp === "number") ? data.temp : toNum(data.temp ?? data.temperature ?? data.soilTemp ?? data.soil_temp);

  const n  = toNum(data.n);
  const p  = toNum(data.p);
  const k  = toNum(data.k);
  const ec = toNum(data.ec);

  // ===== Threshold (EC dalam µS/cm) =====
  const TH = {
    temp: { min:24,   max:30,   warnMin:23,   warnMax:31,   okText:"Suhu optimal",           warnText:"Suhu mendekati ambang", dangerText:"Suhu melewati ambang" },
    humi: { min:40,   max:70,   warnMin:39,   warnMax:75,   okText:"Kelembapan ideal",      warnText:"Kelembapan mendekati ambang", dangerText:"Kelembapan melewati ambang" },
    ph:   { min:5.5,  max:7.0,  warnMin:5.4,  warnMax:7.3,  okText:"Kondisi optimal pH",    warnText:"pH mendekati ambang", dangerText:"pH melewati ambang" },

    ec:   { min:180, max:250, warnMin:120,  warnMax:230, okText:"EC ideal",              warnText:"EC mendekati ambang", dangerText:"EC melewati ambang" },
    n:    { min:80,   max:150,  warnMin:70,   warnMax:170,  okText:"Kadar N normal",        warnText:"N mendekati ambang",  dangerText:"N melewati ambang" },
    p:    { min:35,   max:60,   warnMin:30,   warnMax:75,   okText:"Kadar P normal",        warnText:"P mendekati ambang",  dangerText:"P melewati ambang" },
    k:    { min:60,   max:120,  warnMin:55,   warnMax:150,  okText:"Kadar K normal",        warnText:"K mendekati ambang",  dangerText:"K melewati ambang" },
  };

  function evalLevel(key, val){
    if (val === null || Number.isNaN(val)) return { level: 'ok', text: '' };
    const t = TH[key];
    if (!t) return { level: 'ok', text: '' };

    if (val < t.min || val > t.max) return { level: 'danger', text: t.dangerText };
    if (val <= t.warnMin || val >= t.warnMax) return { level: 'warn', text: t.warnText };
    return { level: 'ok', text: t.okText };
  }

  // helper apply
  function apply(key, val, numberEl, labelEl, cardId, fmt){
    if (val === null) return;
    numberEl.textContent = fmt(val);
    const s = evalLevel(key, val);
    if (s.text) labelEl.textContent = s.text;

    // mapping class existing kamu: ok/warn. Kita tambahin 'danger' (buat lebih tegas)
    if (s.level === 'danger') setCardStatus(cardId, 'danger');
    else if (s.level === 'warn') setCardStatus(cardId, 'warn');
    else setCardStatus(cardId, 'ok');
  }

  // === Apply semua card ===
  apply('temp', temp, tempNumber, tempLabel, 'card-temp', v => v.toFixed(1));
  apply('humi', humi, humiNumber, humiLabel, 'card-humi', v => v.toFixed(1));
  apply('ph',   ph,   phNumber,   phLabel,   'card-ph',   v => v.toFixed(1));

  apply('n',  n,  nNumber,  nLabel,  'card-n',  v => v.toFixed(0));
  apply('p',  p,  pNumber,  pLabel,  'card-p',  v => v.toFixed(0));
  apply('k',  k,  kNumber,  kLabel,  'card-k',  v => v.toFixed(0));
  apply('ec', ec, ecNumber, ecLabel, 'card-ec', v => v.toFixed(2));

  // Chart tetap jalan meski ada null
  if (ph !== null && humi !== null && temp !== null){
    addSensorPoint({ ph, humi, temp, n, p, k, ec, time: (data.time ?? data.reading_time ?? null) });
  }

  lastUpdate.textContent = "Last update: " + new Date().toLocaleString("id-ID");
}

  function fmtLogTime(s){
    if (!s) return { date: "-", time: "-" };

    let t = String(s);
    if (t.includes(" ") && !t.includes("T")) t = t.replace(" ", "T");

    const d = new Date(t);
    if (isNaN(d.getTime())) return { date: String(s), time: "" };

    return {
      date: d.toLocaleDateString("id-ID", { day:"2-digit", month:"2-digit", year:"numeric" }),
      time: d.toLocaleTimeString("id-ID", { hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:false }),
    };
  }

  document.getElementById('btnMarkRead')?.addEventListener('click', async () => {
  const res = await fetch('/api/notifications/mark-all-read', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  });

  const j = await res.json();
  console.log('markAllRead:', j);

  // refresh notif & badge
  if (typeof loadNotifications === 'function') {
    loadNotifications();
  }
});

  function renderLogList(logs){
    logList.innerHTML = "";

    if (!logs || logs.length === 0){
      logList.innerHTML = '<div class="muted text-center py-3">Belum ada riwayat penyiraman.</div>';
      return;
    }

    let html = "";
    for (const log of logs){
      const badge = (log.source === "manual") ? "manual" : "otomatis";
      const notes = log.notes ? ` – ${log.notes}` : "";
      const dt = fmtLogTime(log.log_time);

      html += `
        <div class="logitem d-flex justify-content-between gap-3">
          <div>
            <span class="tag ${badge} me-2">${log.source}</span>
            <b>${log.action}</b>${notes}
          </div>
          <div class="log-meta">
            <div class="log-date">${dt.date}</div>
            <div class="log-time">${dt.time}</div>
            ${log.duration_seconds ? `<div class="log-dur">${log.duration_seconds}s</div>` : ``}
          </div>
        </div>
      `;
    }

    logList.innerHTML = html;
  }


  async function loadWateringLogs(){
    try{
      const res = await fetch("/api/watering_logs.php?limit=20");
      const data = await res.json();
      renderLogList(Array.isArray(data) ? data : []);
    }catch(e){
      renderLogList([]);
    }
  }

  async function saveWateringLog(source, action, duration_seconds=null, notes=null){
    try{
      const res = await fetch("/api/watering_logs.php",{
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body: JSON.stringify({ source, action, duration_seconds, notes })
      });
      if (res.ok) loadWateringLogs();
    }catch(e){}
  }

  // MQTT
  function connectMQTT(){
    const clientId = "ITANI_WEB_" + Math.floor(Math.random()*100000);
    mqttClient = new Paho.MQTT.Client(MQTT_CFG.host, Number(MQTT_CFG.port), MQTT_CFG.path, clientId);

    mqttClient.onConnectionLost = () => {
      setMQTTStatus(false);
      setTimeout(connectMQTT, 2000);
    };

    mqttClient.onMessageArrived = (message) => {
      const topic = message.destinationName;
      const payload = message.payloadString;

      if (topic === TOPIC.sensor){
        try{
          let raw = payload;
          if (raw.startsWith('"') && raw.endsWith('"')) { try{ raw = JSON.parse(raw); }catch(e){} }
          const data = (typeof raw === "string") ? JSON.parse(raw) : raw;

          updateSensorUI(data);

          fetch("/api/sensors_insert.php", {
            method:"POST",
            headers:{ "Content-Type":"application/json" },
            body: JSON.stringify(data)
          }).catch(()=>{});
        }catch(e){}
      }

      if (topic === TOPIC.pump_status) {
        const raw = payload.trim().toUpperCase();
        const status = (raw === "ON" || raw === "OFF") ? raw : "UNKNOWN";
        if (status === "UNKNOWN") return;

        const now = Date.now();

        // CASE 1: echo dari klik manual dashboard (<= 3 detik)
        if (lastCommandSource === "manual" && (now - lastCommandTime) <= 3000) {
          setPumpStatusText(status);
          lastCommandSource = null;
          return;
        }

        // CASE 2: mode otomatis ON -> ini perintah otomatis dari ESP
        if (isAutoMode) {
          const prevStatus = currentPumpStatus;
          setPumpStatusText(status);

          // log hanya kalau status berubah
          if (status !== prevStatus) {
            saveWateringLog("otomatis", status, null, "Perintah dari ESP");
          }
          return;
        }

        // CASE 3: auto mode OFF dan bukan echo manual -> ABAIKAN (samakan native)
        console.log("Pump status diabaikan (autoMode OFF & bukan echo manual)");
        return;
      }


      if (topic === TOPIC.auto_mode){
        const raw = payload.trim().toUpperCase();
        const enabled = (raw === "ON");
        isAutoMode = enabled;
        autoMode.checked = enabled;
      }
    };

    const options = {
      timeout: 5,
      useSSL: (Number(MQTT_CFG.port) === 8884),
      onSuccess: () => {
        setMQTTStatus(true);
        mqttClient.subscribe(TOPIC.sensor);
        mqttClient.subscribe(TOPIC.pump_status);
        mqttClient.subscribe(TOPIC.auto_mode);
      },
      onFailure: () => {
        setMQTTStatus(false);
        setTimeout(connectMQTT, 2000);
      }
    };

    if (MQTT_CFG.username){
      options.userName = MQTT_CFG.username;
      options.password = MQTT_CFG.password;
    }

    mqttClient.connect(options);
  }

  function sendPumpCommand(cmd){
    if (!mqttClient || !mqttClient.isConnected()) return;
    const msg = new Paho.MQTT.Message(cmd);
    msg.destinationName = TOPIC.pump_cmd;
    mqttClient.send(msg);
  }

  btnTogglePump.addEventListener("click", () => {
    const isOn = (currentPumpStatus === "ON");
    const nextCmd = isOn ? "OFF" : "ON";

    saveWateringLog("manual", nextCmd, null, "Perintah dari dashboard");
    lastCommandSource = "manual";
    lastCommandTime = Date.now();

    sendPumpCommand(nextCmd);
    setPumpStatusText(nextCmd);
  });

  autoMode.addEventListener("change", () => {
    const aktif = autoMode.checked;
    const payload = aktif ? "ON" : "OFF";
    if (!mqttClient || !mqttClient.isConnected()){
      autoMode.checked = !aktif;
      return;
    }
    const msg = new Paho.MQTT.Message(payload);
    msg.destinationName = TOPIC.auto_mode;
    msg.retained = true;
    mqttClient.send(msg);
    isAutoMode = aktif;
  });

  document.getElementById("btnRefreshLogs").addEventListener("click", loadWateringLogs);

  window.addEventListener("load", async () => {
    try { loadWateringLogs(); } catch(e) {}

    // ambil status pompa terakhir (buat state awal)
    try {
      const r = await fetch("/api/pump_status_latest.php");
      const d = await r.json();
      if (d.exists && (d.action === "ON" || d.action === "OFF")) setPumpStatusText(d.action);
    } catch (e) {}

    // ambil sensor terakhir juga (biar langsung kebaca walau MQTT belum publish)
    try {
      const r2 = await fetch("/api/sensors_latest.php");
      const s = await r2.json();
      if (s.exists) updateSensorUI(s);
    } catch (e) {}

    // PASTI konek MQTT
    try { connectMQTT(); } catch(e) { console.error(e); }
  });

</script>
@endsection
