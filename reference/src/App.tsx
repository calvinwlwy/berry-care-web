import { useEffect, useMemo, useState, type ReactNode } from "react";

type Screen = "home" | "scan" | "pots" | "history" | "profile";
type IconName =
  | "home"
  | "camera"
  | "leaf"
  | "history"
  | "user"
  | "bell"
  | "cloud"
  | "droplet"
  | "thermometer"
  | "chevron"
  | "search"
  | "flash"
  | "image"
  | "close"
  | "check"
  | "wifi"
  | "battery"
  | "logout"
  | "alert"
  | "sprout"
  | "trend"
  | "calendar"
  | "location"
  | "lock"
  | "mail";

const photos = {
  leaf:
    "https://images.unsplash.com/photo-1588165171080-c89acfa5ee83?auto=format&fit=crop&w=900&q=85",
  ripe:
    "https://images.unsplash.com/photo-1680180226263-fab5f01a01c2?auto=format&fit=crop&w=900&q=85",
  healthy:
    "https://images.unsplash.com/photo-1582757793483-e407049be006?auto=format&fit=crop&w=900&q=85",
};

function Icon({ name, size = 20, strokeWidth = 2 }: { name: IconName; size?: number; strokeWidth?: number }) {
  const paths: Record<IconName, ReactNode> = {
    home: <><path d="M3 10.5 12 3l9 7.5" /><path d="M5 9.5V21h14V9.5M9 21v-7h6v7" /></>,
    camera: <><path d="M4 7h3l1.5-2h7L17 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" /><circle cx="12" cy="13" r="4" /></>,
    leaf: <><path d="M20.5 3.5C12 3 5 6.5 5 13c0 3.5 2.8 6 6.2 5.5C17 17.7 20 11.5 20.5 3.5Z" /><path d="M3.5 21c2-5.5 6.5-9.5 12.5-12" /></>,
    history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5M12 7v5l3 2" /></>,
    user: <><circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" /></>,
    bell: <><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></>,
    cloud: <path d="M17.5 19H6a4 4 0 0 1-.4-8 6.5 6.5 0 0 1 12.6-1.5A4.8 4.8 0 0 1 17.5 19Z" />,
    droplet: <path d="M12 2S5.5 9 5.5 14.5a6.5 6.5 0 0 0 13 0C18.5 9 12 2 12 2Z" />,
    thermometer: <><path d="M10 14.8V5a2 2 0 1 1 4 0v9.8a4 4 0 1 1-4 0Z" /><path d="M12 8v8" /></>,
    chevron: <path d="m9 18 6-6-6-6" />,
    search: <><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" /></>,
    flash: <path d="m13 2-8 12h7l-1 8 8-12h-7l1-8Z" />,
    image: <><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="9" cy="10" r="2" /><path d="m21 15-5-5L5 20" /></>,
    close: <path d="M18 6 6 18M6 6l12 12" />,
    check: <path d="m5 12 4 4L19 6" />,
    wifi: <><path d="M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0" /><circle cx="12" cy="20" r=".8" fill="currentColor" /></>,
    battery: <><rect x="3" y="7" width="17" height="10" rx="2" /><path d="M22 11v2M6 10h8v4H6z" /></>,
    logout: <><path d="M10 17l5-5-5-5M15 12H3" /><path d="M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" /></>,
    alert: <><path d="M12 3 2.5 20h19L12 3Z" /><path d="M12 9v5M12 17.5v.1" /></>,
    sprout: <><path d="M12 22V10" /><path d="M12 14C6 14 4 10 4 5c5 0 8 2 8 7M12 10c0-5 3-7 8-7 0 5-2 9-8 9" /></>,
    trend: <><path d="M3 19h18M5 16l4-5 4 3 6-8" /><path d="M15 6h4v4" /></>,
    calendar: <><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M7 3v4M17 3v4M3 10h18" /></>,
    location: <><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" /></>,
    lock: <><rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></>,
    mail: <><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></>,
  };
  return (
    <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      {paths[name]}
    </svg>
  );
}

function Logo({ compact = false }: { compact?: boolean }) {
  return (
    <div className={`brand-logo ${compact ? "compact" : ""}`} aria-label="Berry Care">
      <svg viewBox="0 0 48 48" aria-hidden="true">
        <path d="M24 17c-7.8-7.1-18.5-1.6-16.2 8.1C10.2 35.5 24 42 24 42s13.8-6.5 16.2-16.9C42.5 15.4 31.8 9.9 24 17Z" fill="#BE123C" />
        <path d="M24 17c-1.8-7.4 2.8-11.1 9.4-11.5-.2 6-3.3 10.6-9.4 11.5Z" fill="#15803D" />
        <path d="M23.5 16.5c-5.1-5.5-10.2-4.1-13.6-.8 5 2.8 9.5 3 13.6.8Z" fill="#22C55E" />
        <g fill="#FFF1F2"><circle cx="17" cy="25" r="1.2" /><circle cx="26" cy="23" r="1.2" /><circle cx="32" cy="28" r="1.2" /><circle cx="21" cy="33" r="1.2" /><circle cx="28" cy="35" r="1.2" /></g>
      </svg>
    </div>
  );
}

const activities = [
  { pot: "Pot 05", text: "Terdeteksi Bercak Daun", score: "94%", time: "10:42 WIB", tone: "danger" },
  { pot: "Pot 12", text: "Buah Matang", score: "98%", time: "10:24 WIB", tone: "good" },
  { pot: "Pot 01", text: "Daun Sehat", score: "96%", time: "09:15 WIB", tone: "good" },
];

const potData = [
  { id: "POT-01", block: "Blok A", soil: 62, condition: "Normal", npk: "45–18–52", status: "Sehat", attention: false, time: "Hari ini, 10:18 WIB" },
  { id: "POT-05", block: "Blok A", soil: 78, condition: "Terlalu Basah", npk: "18–08–20", status: "Defisiensi Hara & Rawan Busuk Akar", attention: true, time: "Hari ini, 10:36 WIB" },
  { id: "POT-12", block: "Blok B", soil: 58, condition: "Normal", npk: "50–22–60", status: "Siap Panen", attention: false, time: "Hari ini, 09:58 WIB" },
];

const historyData = [
  { id: 1, type: "Daun", title: "Daun Bercak", pot: "POT-05", date: "18 Okt 2026, 10:42 WIB", accuracy: "94.8%", photo: photos.leaf, note: "Gejala bercak terlokalisasi. Pangkas daun terdampak dan pantau kelembapan selama 48 jam.", sync: "Tersinkron ke dashboard" },
  { id: 2, type: "Buah", title: "Buah Stroberi Matang", pot: "POT-12", date: "18 Okt 2026, 10:24 WIB", accuracy: "98.1%", photo: photos.ripe, note: "Tingkat kematangan optimal. Direkomendasikan masuk jadwal panen sesi siang.", sync: "Tersinkron ke dashboard" },
  { id: 3, type: "Daun", title: "Daun Stroberi Sehat", pot: "POT-01", date: "18 Okt 2026, 09:15 WIB", accuracy: "96.5%", photo: photos.healthy, note: "Warna dan tekstur daun normal. Lanjutkan jadwal nutrisi dan penyiraman rutin.", sync: "Tersimpan di perangkat" },
];

function Header({ title, eyebrow, right }: { title: string; eyebrow?: string; right?: ReactNode }) {
  return (
    <header className="screen-header">
      <div>
        {eyebrow && <p className="eyebrow">{eyebrow}</p>}
        <h1>{title}</h1>
      </div>
      {right}
    </header>
  );
}

function Modal({ children, onClose, full = false }: { children: ReactNode; onClose: () => void; full?: boolean }) {
  return (
    <div className={`modal-backdrop ${full ? "full" : ""}`} onMouseDown={onClose}>
      <div className={`modal-card ${full ? "modal-full" : ""}`} onMouseDown={(event) => event.stopPropagation()}>
        {children}
      </div>
    </div>
  );
}

function Login({ onLogin }: { onLogin: () => void }) {
  const [remember, setRemember] = useState(true);
  return (
    <main className="login-shell">
      <div className="login-decor">
        <span>HAIFRESH • LEMBANG</span>
        <strong>Field operations</strong>
      </div>
      <section className="login-card">
        <div className="login-brand">
          <Logo />
          <h1>Berry Care</h1>
          <p>Field App</p>
        </div>
        <div className="login-intro">
          <h2>Selamat datang kembali</h2>
          <p>Sistem Operasional Lapangan<br />Haifresh Lembang</p>
        </div>
        <label className="field">
          <span>ID Operator / Email</span>
          <div className="input-wrap"><Icon name="mail" size={18} /><input defaultValue="asep@haifresh.id" /></div>
        </label>
        <label className="field">
          <span>PIN / Password</span>
          <div className="input-wrap"><Icon name="lock" size={18} /><input type="password" defaultValue="123456" /></div>
        </label>
        <button className="remember" onClick={() => setRemember(!remember)} aria-pressed={remember}>
          <span className={`checkbox ${remember ? "checked" : ""}`}>{remember && <Icon name="check" size={14} strokeWidth={3} />}</span>
          Ingat perangkat ini di kebun
        </button>
        <button className="primary-button login-button" onClick={onLogin}>
          Masuk Tugas Kebun <Icon name="chevron" size={18} />
        </button>
        <div className="secure-note"><span className="status-dot" /> Akses aman untuk operator terdaftar</div>
      </section>
      <footer>Aplikasi Khusus Karyawan Kebun Haifresh Lembang © 2026</footer>
    </main>
  );
}

function Home({ navigate, openAlerts }: { navigate: (screen: Screen) => void; openAlerts: () => void }) {
  return (
    <div className="screen home-screen">
      <div className="home-top">
        <button className="profile-shortcut" onClick={() => navigate("profile")}>
          <span className="avatar">AS</span>
          <span><small>HALO,</small><strong>Kang Asep</strong><em>Operator Kebun • Blok A</em></span>
        </button>
        <button className="icon-button notification" onClick={openAlerts} aria-label="Buka notifikasi">
          <Icon name="bell" size={21} /><span />
        </button>
      </div>

      <div className="weather-strip"><Icon name="cloud" size={18} /><strong>Lembang 21°C</strong><span /> RH 84% <span /> Lembap</div>

      <button className="alert-banner" onClick={openAlerts}>
        <span className="alert-icon"><Icon name="alert" size={20} /></span>
        <span><strong>Waspada Jamur Botrytis!</strong><small>Kelembapan udara 84% rentan memicu pembusukan buah. Periksa ventilasi naungan.</small></span>
        <Icon name="chevron" size={18} />
      </button>

      <button className="scan-cta" onClick={() => navigate("scan")}>
        <span className="scan-cta-icon"><Icon name="camera" size={24} /></span>
        <span><small>DIAGNOSIS AI</small><strong>Mulai Pemindaian Tanaman</strong></span>
        <Icon name="chevron" size={20} />
      </button>

      <section>
        <div className="section-heading"><div><p>RINGKASAN HARI INI</p><h2>Kondisi kebun</h2></div><span>Live</span></div>
        <div className="metric-grid">
          <article className="metric-card">
            <div className="metric-top"><span className="metric-icon temp"><Icon name="thermometer" size={19} /></span><em>DHT22</em></div>
            <small>Suhu udara</small><strong>21.4<span>°C</span></strong>
            <div className="metric-foot"><Icon name="droplet" size={14} /> Kelembapan <b>84%</b></div>
          </article>
          <article className="metric-card">
            <div className="metric-top"><span className="metric-icon soil"><Icon name="sprout" size={19} /></span><em>3 POT</em></div>
            <small>Rata-rata air tanah</small><strong>62<span>%</span></strong>
            <div className="metric-foot good"><span className="status-dot" /> Kondisi ideal</div>
          </article>
        </div>
      </section>

      <section className="activity-section">
        <div className="section-heading"><div><p>AKTIVITAS TERBARU</p><h2>Pemindaian hari ini</h2></div><button onClick={() => navigate("history")}>Lihat semua</button></div>
        <div className="activity-list">
          {activities.map((activity) => (
            <button className="activity-row" key={activity.pot} onClick={() => navigate("history")}>
              <span className={`activity-mark ${activity.tone}`}><Icon name={activity.tone === "danger" ? "alert" : "leaf"} size={17} /></span>
              <span className="activity-copy"><strong>{activity.pot} <i>•</i> {activity.text}</strong><small>{activity.time}</small></span>
              <span className={`score ${activity.tone}`}>{activity.score}</span>
            </button>
          ))}
        </div>
      </section>
    </div>
  );
}

function Scan({ onResult, setToast }: { onResult: () => void; setToast: (value: string) => void }) {
  const [mode, setMode] = useState<"leaf" | "fruit">("leaf");
  const [flash, setFlash] = useState(false);
  const [scanning, setScanning] = useState(false);
  const capture = (gallery = false) => {
    setScanning(true);
    if (gallery) setToast("Foto galeri dipilih • Memproses gambar");
    window.setTimeout(() => {
      setScanning(false);
      onResult();
    }, 1000);
  };
  return (
    <div className="scan-screen">
      <img src={mode === "leaf" ? photos.leaf : photos.ripe} alt="Tanaman stroberi dalam viewfinder" className="camera-bg" />
      <div className="camera-shade" />
      <div className="scan-top">
        <div className="mode-switch">
          <button className={mode === "leaf" ? "active" : ""} onClick={() => setMode("leaf")}><Icon name="leaf" size={15} /> Scan Daun</button>
          <button className={mode === "fruit" ? "active" : ""} onClick={() => setMode("fruit")}><Icon name="sprout" size={15} /> Scan Buah</button>
        </div>
        <label className="pot-select"><span><Icon name="location" size={15} /> ID Polybag</span><select defaultValue="POT-05"><option>POT-01</option><option>POT-05</option><option>POT-12</option></select></label>
      </div>

      <div className={`viewfinder ${scanning ? "scanning" : ""}`}>
        <span className="corner tl" /><span className="corner tr" /><span className="corner bl" /><span className="corner br" />
        {scanning && <><div className="scan-line" /><div className="analyzing"><span className="loader" />Menganalisis tanaman...</div></>}
      </div>
      <p className="scan-hint">{mode === "leaf" ? "Posisikan satu helai daun di dalam bingkai" : "Posisikan buah dengan pencahayaan yang cukup"}</p>

      <div className="camera-controls">
        <button className={`round-control ${flash ? "active" : ""}`} onClick={() => setFlash(!flash)} aria-label="Lampu kilat"><Icon name="flash" size={22} /></button>
        <button className="shutter" onClick={() => capture()} disabled={scanning} aria-label="Ambil gambar"><span /></button>
        <button className="round-control" onClick={() => capture(true)} disabled={scanning} aria-label="Pilih dari galeri"><Icon name="image" size={22} /></button>
      </div>
    </div>
  );
}

function Pots({ openTrend }: { openTrend: (id: string) => void }) {
  const [filter, setFilter] = useState("Semua Pot");
  const [search, setSearch] = useState("");
  const [expanded, setExpanded] = useState<string | null>(null);
  const filtered = potData.filter((pot) => {
    const filterMatch = filter === "Semua Pot" || (filter === "Perlu Perhatian" ? pot.attention : pot.block === filter);
    return filterMatch && pot.id.toLowerCase().includes(search.trim().toLowerCase());
  });
  return (
    <div className="screen pots-screen">
      <Header title="Status Polybag" eyebrow="TELEMETRI IOT" right={<div className="live-badge"><span /> LIVE</div>} />
      <label className="search-box"><Icon name="search" size={19} /><input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Ketik nomor pot (misal: 05)..." /></label>
      <div className="chips">
        {["Semua Pot", "Blok A", "Blok B", "Perlu Perhatian"].map((item) => <button key={item} className={filter === item ? "active" : ""} onClick={() => setFilter(item)}>{item}</button>)}
      </div>
      <p className="result-label">{filtered.length} polybag ditemukan</p>
      <div className="pot-list">
        {filtered.map((pot) => {
          const isOpen = expanded === pot.id;
          return (
            <article className={`pot-card ${pot.attention ? "attention" : ""} ${isOpen ? "expanded" : ""}`} key={pot.id}>
              <button className="pot-main" onClick={() => setExpanded(isOpen ? null : pot.id)}>
                <span className="pot-id"><small>{pot.block}</small><strong>{pot.id}</strong></span>
                <span className={`status-badge ${pot.attention ? "danger" : "good"}`}>{pot.status}</span>
                <span className={`expand-chevron ${isOpen ? "open" : ""}`}><Icon name="chevron" size={19} /></span>
              </button>
              <div className="pot-metrics">
                <div><span className="metric-icon soil"><Icon name="droplet" size={18} /></span><p>Air tanah<strong>{pot.soil}% <em className={pot.attention ? "danger-text" : ""}>{pot.condition}</em></strong></p></div>
                <div><span className="metric-icon npk">N</span><p>NPK tanah<strong>{pot.npk} <em>mg/kg</em></strong></p></div>
              </div>
              {isOpen && (
                <div className="pot-details">
                  <div><span><Icon name="history" size={16} /> Pembacaan probe terakhir</span><strong>{pot.time}</strong></div>
                  <button onClick={() => openTrend(pot.id)}><Icon name="trend" size={17} /> Lihat Tren 7 Hari</button>
                </div>
              )}
            </article>
          );
        })}
        {filtered.length === 0 && <div className="empty-state"><Icon name="search" size={28} /><strong>Pot tidak ditemukan</strong><p>Coba nomor atau filter lainnya.</p></div>}
      </div>
    </div>
  );
}

function History({ openDetail }: { openDetail: (id: number) => void }) {
  const [filter, setFilter] = useState("Semua");
  const filtered = historyData.filter((item) => filter === "Semua" || item.type === filter);
  return (
    <div className="screen history-screen">
      <Header title="Riwayat Diagnosis" eyebrow="CATATAN LAPANGAN" right={<button className="icon-button"><Icon name="calendar" size={20} /></button>} />
      <div className="history-filters">
        {["Semua", "Daun", "Buah"].map((item) => <button key={item} onClick={() => setFilter(item)} className={filter === item ? "active" : ""}>{item} <span>{item === "Semua" ? 38 : item === "Daun" ? 26 : 12}</span></button>)}
      </div>
      <div className="date-divider"><span>HARI INI • 18 OKTOBER 2026</span><i /></div>
      <div className="history-list">
        {filtered.map((item) => (
          <button className="history-card" key={item.id} onClick={() => openDetail(item.id)}>
            <img src={item.photo} alt={item.title} />
            <span className="history-copy"><small>{item.type} • {item.pot}</small><strong>{item.title}</strong><em><Icon name="history" size={13} /> {item.date}</em><b>Akurasi AI {item.accuracy}</b></span>
            <span className="history-arrow"><Icon name="chevron" size={18} /></span>
          </button>
        ))}
      </div>
      <div className="sync-foot"><Icon name="check" size={15} /> Terakhir sinkron hari ini, 10:45 WIB</div>
    </div>
  );
}

function Profile({ onLogout, setToast }: { onLogout: () => void; setToast: (value: string) => void }) {
  const [offline, setOffline] = useState(true);
  return (
    <div className="screen profile-screen">
      <Header title="Profil Operator" eyebrow="AKUN & PERANGKAT" />
      <section className="profile-hero">
        <div className="profile-avatar">AS<span /></div>
        <div><h2>Kang Asep</h2><p>asep@haifresh.id</p><span className="role-badge"><Icon name="sprout" size={14} /> Operator Kebun Blok A</span></div>
      </section>
      <section className="profile-section">
        <h3>Informasi Penugasan</h3>
        <div className="info-card">
          <div><span className="info-icon"><Icon name="location" size={19} /></span><p><small>AREA PENUGASAN</small><strong>Blok A & Blok B</strong><em>Kebun Stroberi Utama</em></p></div>
          <div><span className="info-icon"><Icon name="history" size={19} /></span><p><small>SHIFT KERJA</small><strong>Pagi</strong><em>07:00 – 15:00 WIB</em></p></div>
        </div>
      </section>
      <section className="profile-section">
        <h3>Perangkat Lapangan</h3>
        <div className="device-card">
          <button onClick={() => setToast("ESP32 terhubung dengan sinyal kuat")}>
            <span className="device-icon"><Icon name="wifi" size={21} /></span><p><strong>Tester Portabel ESP32</strong><small><i className="online-dot" /> Terhubung • Wi-Fi Hotspot</small></p><Icon name="chevron" size={18} />
          </button>
          <button onClick={() => setToast("Probe siap digunakan • Baterai 85%")}>
            <span className="device-icon"><Icon name="battery" size={21} /></span><p><strong>Probe NPK & DHT22</strong><small><i className="online-dot" /> Siap Pakai • Baterai 85%</small></p><Icon name="chevron" size={18} />
          </button>
        </div>
      </section>
      <section className="profile-section">
        <h3>Pengaturan Aplikasi</h3>
        <button className="setting-row" onClick={() => setOffline(!offline)}>
          <span className="setting-icon"><Icon name="cloud" size={20} /></span><p><strong>Mode Offline</strong><small>Simpan data lokal jika sinyal kebun lemah</small></p>
          <span className={`toggle ${offline ? "on" : ""}`}><i /></span>
        </button>
      </section>
      <button className="logout-button" onClick={onLogout}><Icon name="logout" size={19} /> Keluar / Logout</button>
      <p className="app-version">Berry Care Field App • Versi 2.4.0</p>
    </div>
  );
}

function BottomNav({ active, navigate }: { active: Screen; navigate: (screen: Screen) => void }) {
  const tabs: { id: Screen; label: string; icon: IconName }[] = [
    { id: "home", label: "Beranda", icon: "home" },
    { id: "scan", label: "Scan AI", icon: "camera" },
    { id: "pots", label: "Status Pot", icon: "leaf" },
    { id: "history", label: "Riwayat", icon: "history" },
    { id: "profile", label: "Profil", icon: "user" },
  ];
  return (
    <nav className="bottom-nav">
      {tabs.map((tab) => (
        <button key={tab.id} className={`${active === tab.id ? "active" : ""} ${tab.id === "scan" ? "scan-tab" : ""}`} onClick={() => navigate(tab.id)}>
          <span className="nav-icon"><Icon name={tab.icon} size={21} /></span><small>{tab.label}</small><i />
        </button>
      ))}
    </nav>
  );
}

export default function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(true);
  const [activeScreen, setActiveScreen] = useState<Screen>("home");
  const [modal, setModal] = useState<"alerts" | "result" | "trend" | null>(null);
  const [trendPot, setTrendPot] = useState("");
  const [historyId, setHistoryId] = useState<number | null>(null);
  const [zoomed, setZoomed] = useState(false);
  const [toast, setToast] = useState("");
  const selectedHistory = useMemo(() => historyData.find((item) => item.id === historyId), [historyId]);

  useEffect(() => {
    if (!toast) return;
    const timeout = window.setTimeout(() => setToast(""), 2800);
    return () => window.clearTimeout(timeout);
  }, [toast]);

  const login = () => {
    setIsLoggedIn(true);
    setActiveScreen("home");
  };
  const logout = () => {
    setIsLoggedIn(false);
    setActiveScreen("home");
  };
  const openTrend = (id: string) => {
    setTrendPot(id);
    setModal("trend");
  };
  const saveScan = () => {
    setModal(null);
    setToast("Data tersimpan ke Laravel & Web Dashboard");
  };

  if (!isLoggedIn) return <div className="stage"><div className="phone-frame"><Login onLogin={login} /></div></div>;

  return (
    <div className="stage">
      <div className="phone-frame">
        <div className={`status-bar ${activeScreen === "scan" ? "over-camera" : ""}`}><span>09:41</span><div><i className="signal" /><Icon name="wifi" size={14} /><span className="battery-mini" /></div></div>
        <main className={`app-content ${activeScreen === "scan" ? "camera-content" : ""}`}>
          {activeScreen === "home" && <Home navigate={setActiveScreen} openAlerts={() => setModal("alerts")} />}
          {activeScreen === "scan" && <Scan onResult={() => setModal("result")} setToast={setToast} />}
          {activeScreen === "pots" && <Pots openTrend={openTrend} />}
          {activeScreen === "history" && <History openDetail={setHistoryId} />}
          {activeScreen === "profile" && <Profile onLogout={logout} setToast={setToast} />}
        </main>
        <BottomNav active={activeScreen} navigate={setActiveScreen} />

        {modal === "alerts" && (
          <Modal onClose={() => setModal(null)}>
            <div className="modal-handle" />
            <div className="modal-heading"><div className="modal-symbol danger"><Icon name="alert" size={22} /></div><div><small>PERINGATAN AKTIF</small><h2>Mikroklimat Kebun</h2></div><button onClick={() => setModal(null)}><Icon name="close" size={20} /></button></div>
            <div className="alert-detail">
              <div><span className="pulse-dot" /><strong>Risiko Botrytis meningkat</strong><em>Baru saja</em></div>
              <p>Kelembapan udara bertahan di atas 80% selama 2 jam. Area naungan Blok A memerlukan sirkulasi udara tambahan.</p>
              <ul><li>Buka ventilasi sisi timur naungan</li><li>Periksa buah yang menyentuh media tanam</li></ul>
            </div>
            <button className="primary-button" onClick={() => { setModal(null); setActiveScreen("pots"); }}>Periksa Status Pot</button>
          </Modal>
        )}

        {modal === "result" && (
          <Modal onClose={() => setModal(null)}>
            <div className="modal-handle" />
            <div className="modal-heading"><div className="modal-symbol"><Icon name="sprout" size={22} /></div><div><small>HASIL DIAGNOSIS AI</small><h2>Analisis selesai</h2></div><button onClick={() => setModal(null)}><Icon name="close" size={20} /></button></div>
            <div className="diagnosis-photo"><img src={photos.leaf} alt="Daun stroberi terdiagnosis" /><span className="bounding-box"><i>Bercak daun 94.8%</i></span></div>
            <div className="diagnosis-title"><div><span className="danger-badge">PERLU TINDAKAN</span><h3>Bercak Daun <em>(Leaf Spot)</em></h3></div><strong>94.8%<small>AKURASI</small></strong></div>
            <div className="location-row"><Icon name="location" size={17} /><span>Lokasi</span><strong>Blok A • POT-05</strong></div>
            <div className="sop-box"><span><Icon name="check" size={17} /></span><p><small>REKOMENDASI SOP</small>Pangkas helai daun yang terinfeksi jamur dan semprotkan bio-fungisida pada sore hari.</p></div>
            <button className="primary-button" onClick={saveScan}><Icon name="check" size={18} /> Simpan & Kirim ke Server</button>
          </Modal>
        )}

        {modal === "trend" && (
          <Modal onClose={() => setModal(null)}>
            <div className="modal-handle" />
            <div className="modal-heading"><div className="modal-symbol"><Icon name="trend" size={22} /></div><div><small>KELEMBAPAN TANAH</small><h2>Tren {trendPot}</h2></div><button onClick={() => setModal(null)}><Icon name="close" size={20} /></button></div>
            <div className="chart">
              <div className="chart-y"><span>80%</span><span>60%</span><span>40%</span></div>
              <svg viewBox="0 0 300 120" preserveAspectRatio="none">
                <path className="chart-grid" d="M0 15H300M0 60H300M0 105H300" />
                <path className="chart-area" d="M0 88 C35 80 42 72 72 76 S115 50 145 57 S183 32 218 40 S257 22 300 26 L300 120 L0 120Z" />
                <path className="chart-line" d="M0 88 C35 80 42 72 72 76 S115 50 145 57 S183 32 218 40 S257 22 300 26" />
              </svg>
              <div className="chart-x"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
            </div>
            <div className="trend-summary"><span><small>RATA-RATA</small><strong>66%</strong></span><span><small>TERTINGGI</small><strong className="danger-text">78%</strong></span><span><small>STATUS</small><strong>Naik</strong></span></div>
            <button className="secondary-button" onClick={() => setModal(null)}>Tutup Ringkasan</button>
          </Modal>
        )}

        {selectedHistory && (
          <Modal full onClose={() => { setHistoryId(null); setZoomed(false); }}>
            <div className="detail-topbar"><button onClick={() => { setHistoryId(null); setZoomed(false); }}><Icon name="chevron" size={22} /></button><strong>Detail Diagnosis</strong><span /></div>
            <button className={`detail-image ${zoomed ? "zoomed" : ""}`} onClick={() => setZoomed(!zoomed)}>
              <img src={selectedHistory.photo} alt={selectedHistory.title} /><span>{zoomed ? "Ketuk untuk perkecil" : "Ketuk untuk perbesar"}</span>
            </button>
            <div className="detail-body">
              <div className="detail-kicker">{selectedHistory.type} • {selectedHistory.pot}</div>
              <h2>{selectedHistory.title}</h2>
              <p className="detail-date"><Icon name="calendar" size={15} /> {selectedHistory.date}</p>
              <div className="confidence-box"><span><small>AKURASI MODEL</small><strong>{selectedHistory.accuracy}</strong></span><div><i style={{ width: selectedHistory.accuracy }} /></div></div>
              <div className="note-box"><small>CATATAN AGRONOMIS</small><p>{selectedHistory.note}</p><footer><span className="mini-avatar">RN</span><span><strong>Rina Nurhayati</strong><em>Agronomis Haifresh</em></span></footer></div>
              <div className="sync-status"><span className="modal-symbol"><Icon name="cloud" size={19} /></span><p><small>STATUS SINKRONISASI</small><strong>{selectedHistory.sync}</strong></p><Icon name="check" size={18} /></div>
            </div>
          </Modal>
        )}
        {toast && <div className="toast"><span><Icon name="check" size={16} /></span>{toast}</div>}
      </div>
    </div>
  );
}
