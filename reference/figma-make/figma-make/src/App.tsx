import { FormEvent, ReactNode, useMemo, useRef, useState } from "react";

type IconName =
  | "grid"
  | "leaf"
  | "scan"
  | "bell"
  | "sliders"
  | "users"
  | "search"
  | "cloud"
  | "logout"
  | "chevron"
  | "thermo"
  | "droplet"
  | "sprout"
  | "camera"
  | "alert"
  | "check"
  | "close"
  | "upload"
  | "calendar"
  | "history"
  | "plus"
  | "mail"
  | "lock"
  | "eye";

function Icon({ name, size = 18, className = "" }: { name: IconName; size?: number; className?: string }) {
  const paths: Record<IconName, ReactNode> = {
    grid: <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>,
    leaf: <><path d="M20.8 3.2C13 2.7 6.7 5.4 4.1 10.5c-2 3.8-.6 7.8-.6 7.8s4 .9 7.3-1.2c4.4-2.8 7-7.6 10-13.9Z" /><path d="M3.5 20.5c2.8-5.2 7-8.3 12.6-11.2" /></>,
    scan: <><path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3" /><circle cx="12" cy="12" r="3" /></>,
    bell: <><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" /><path d="M10 21h4" /></>,
    sliders: <><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3" /><path d="M1 14h6M9 8h6M17 16h6" /></>,
    users: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8" /></>,
    search: <><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" /></>,
    cloud: <><path d="M17.5 19H7a5 5 0 1 1 1-9.9A7 7 0 0 1 21 12.5 3.5 3.5 0 0 1 17.5 19Z" /><path d="M10 14h.01M14 14h.01" /></>,
    logout: <><path d="M10 17l5-5-5-5M15 12H3" /><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" /></>,
    chevron: <path d="m9 18 6-6-6-6" />,
    thermo: <><path d="M14 14.8V5a4 4 0 0 0-8 0v9.8a6 6 0 1 0 8 0Z" /><path d="M10 11v6" /></>,
    droplet: <path d="M12 2.5S5 10 5 15a7 7 0 0 0 14 0c0-5-7-12.5-7-12.5Z" />,
    sprout: <><path d="M7 20h10M12 20v-8" /><path d="M12 12C9 12 5 10 5 5c5 0 7 3 7 7ZM12 15c3 0 7-2 7-7-5 0-7 3-7 7Z" /></>,
    camera: <><path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3h5Z" /><circle cx="12" cy="13" r="3.5" /></>,
    alert: <><path d="M10.3 3.5 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.5a2 2 0 0 0-3.4 0Z" /><path d="M12 9v4M12 17h.01" /></>,
    check: <path d="m5 12 4 4L19 6" />,
    close: <><path d="m6 6 12 12M18 6 6 18" /></>,
    upload: <><path d="M12 16V4M7 9l5-5 5 5" /><path d="M5 20h14" /></>,
    calendar: <><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" /></>,
    history: <><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5M12 7v5l3 2" /></>,
    plus: <path d="M12 5v14M5 12h14" />,
    mail: <><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></>,
    lock: <><rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></>,
    eye: <><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" /><circle cx="12" cy="12" r="2.5" /></>,
  };
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" className={className} aria-hidden="true">{paths[name]}</svg>;
}

const tabs = [
  { id: "dashboard", label: "Dashboard", sub: "Overview", icon: "grid" },
  { id: "polybag", label: "Lahan & Polybag", sub: "Data Telemetri", icon: "leaf" },
  { id: "diagnosis", label: "Riwayat Diagnosis AI", sub: "Leaf & Fruit Scans", icon: "scan" },
  { id: "alerts", label: "Early Warning & Alerts", sub: "Peringatan Aktif", icon: "bell" },
  { id: "threshold", label: "Konfigurasi Threshold", sub: "Ambang Batas", icon: "sliders" },
  { id: "users", label: "Manajemen Pengguna", sub: "Akses Tim", icon: "users" },
] as const;

type TabId = (typeof tabs)[number]["id"];

const images = [
  "https://images.unsplash.com/photo-1588165171080-c89acfa5ee83?auto=format&fit=crop&w=900&q=85",
  "https://images.unsplash.com/photo-1646206324367-2fb297ece723?auto=format&fit=crop&w=900&q=85",
  "https://images.unsplash.com/photo-1582757793483-e407049be006?auto=format&fit=crop&w=900&q=85",
  "https://images.unsplash.com/photo-1518994603110-1912b3272afd?auto=format&fit=crop&w=900&q=85",
  "https://images.unsplash.com/photo-1646206319332-8536daa98708?auto=format&fit=crop&w=900&q=85",
  "https://images.unsplash.com/photo-1655288511427-827ced84b453?auto=format&fit=crop&w=900&q=85",
];

const pots = [
  { id: "POT-01", block: "Blok A", water: 68, n: 42, p: 29, k: 148, status: "Optimal" },
  { id: "POT-02", block: "Blok A", water: 62, n: 38, p: 27, k: 139, status: "Optimal" },
  { id: "POT-03", block: "Blok A", water: 47, n: 28, p: 18, k: 119, status: "Defisiensi" },
  { id: "POT-04", block: "Blok B", water: 71, n: 45, p: 31, k: 152, status: "Optimal" },
  { id: "POT-05", block: "Blok B", water: 39, n: 19, p: 22, k: 126, status: "Defisiensi" },
  { id: "POT-06", block: "Blok B", water: 59, n: 36, p: 25, k: 141, status: "Optimal" },
  { id: "POT-07", block: "Blok C", water: 65, n: 40, p: 28, k: 146, status: "Optimal" },
  { id: "POT-08", block: "Blok C", water: 44, n: 26, p: 17, k: 115, status: "Defisiensi" },
];

const scans = [
  { id: "SCN-938", label: "Bercak Daun", type: "Daun", pot: "POT-05", score: "94.8%", image: images[3], danger: true, time: "10:42 WIB" },
  { id: "SCN-937", label: "Buah Matang", type: "Buah", pot: "POT-02", score: "98.2%", image: images[1], danger: false, time: "10:18 WIB" },
  { id: "SCN-936", label: "Daun Sehat", type: "Daun", pot: "POT-07", score: "97.4%", image: images[2], danger: false, time: "09:54 WIB" },
  { id: "SCN-935", label: "Powdery Mildew", type: "Daun", pot: "POT-03", score: "91.6%", image: images[4], danger: true, time: "09:31 WIB" },
  { id: "SCN-934", label: "Buah Matang", type: "Buah", pot: "POT-01", score: "96.9%", image: images[0], danger: false, time: "09:12 WIB" },
  { id: "SCN-933", label: "Daun Sehat", type: "Daun", pot: "POT-06", score: "95.5%", image: images[5], danger: false, time: "08:47 WIB" },
];

function Button({ children, variant = "primary", icon, onClick, type = "button", className = "", disabled = false }: { children: ReactNode; variant?: "primary" | "secondary" | "danger" | "ghost"; icon?: IconName; onClick?: () => void; type?: "button" | "submit"; className?: string; disabled?: boolean }) {
  return <button type={type} onClick={onClick} disabled={disabled} className={`btn btn-${variant} ${className}`}>{icon && <Icon name={icon} size={16} />}{children}</button>;
}

function Card({ children, className = "" }: { children: ReactNode; className?: string }) {
  return <section className={`card ${className}`}>{children}</section>;
}

function Badge({ children, tone = "green" }: { children: ReactNode; tone?: "green" | "red" | "amber" | "slate" | "blue" }) {
  return <span className={`badge badge-${tone}`}>{children}</span>;
}

function SectionTitle({ title, description, action }: { title: string; description?: string; action?: ReactNode }) {
  return <div className="section-heading"><div><div className="section-title">{title}</div>{description && <div className="section-description">{description}</div>}</div>{action}</div>;
}

function Modal({ open, onClose, title, children, wide = false }: { open: boolean; onClose: () => void; title: string; children: ReactNode; wide?: boolean }) {
  if (!open) return null;
  return <div className="modal-backdrop" onMouseDown={onClose}><div className={`modal ${wide ? "modal-wide" : ""}`} onMouseDown={(e) => e.stopPropagation()} role="dialog" aria-modal="true" aria-label={title}><div className="modal-header"><div><div className="eyebrow">BERRY CARE</div><div className="modal-title">{title}</div></div><button className="icon-button" onClick={onClose} aria-label="Tutup"><Icon name="close" /></button></div>{children}</div></div>;
}

function Logo({ dark = false }: { dark?: boolean }) {
  return <div className="logo-wrap"><div className={`logo-mark ${dark ? "logo-mark-dark" : ""}`}><Icon name="leaf" size={22} /></div><div><div className={`logo-name ${dark ? "text-white" : ""}`}>Berry Care</div><div className={`logo-sub ${dark ? "text-slate-400" : ""}`}>Haifresh Lembang</div></div></div>;
}

function Login({ onLogin }: { onLogin: () => void }) {
  const [showPassword, setShowPassword] = useState(false);
  const [resetMessage, setResetMessage] = useState(false);
  const handleSubmit = (e: FormEvent) => { e.preventDefault(); onLogin(); };
  return <main className="login-page">
    <div className="login-accent" />
    <div className="login-shell">
      <div className="login-brand"><Logo /><div className="login-kicker">Sistem manajemen & monitoring</div></div>
      <Card className="login-card">
        <div className="login-card-top">
          <div className="eyebrow">PORTAL MANAJEMEN KEBUN</div>
          <div className="login-title">Selamat datang kembali</div>
          <p className="login-copy">Masuk untuk memantau kondisi tanaman stroberi Haifresh Lembang.</p>
        </div>
        <form onSubmit={handleSubmit} className="login-form">
          <label className="field-label">Email</label>
          <div className="input-wrap"><Icon name="mail" size={17} /><input className="input input-icon" type="email" defaultValue="admin@haifresh.id" required /></div>
          <label className="field-label">Password</label>
          <div className="input-wrap"><Icon name="lock" size={17} /><input className="input input-icon input-password" type={showPassword ? "text" : "password"} defaultValue="berrycare2026" required /><button type="button" className="input-action" onClick={() => setShowPassword(!showPassword)} aria-label="Tampilkan password"><Icon name="eye" size={17} /></button></div>
          <div className="remember-row"><label className="check-label"><input type="checkbox" defaultChecked /> <span>Ingat saya</span></label><button type="button" className="text-link" onClick={() => setResetMessage(true)}>Lupa password?</button></div>
          {resetMessage && <div className="inline-notice">Tautan pemulihan akan dikirim ke admin@haifresh.id.</div>}
          <Button type="submit" className="btn-full">Masuk ke Dashboard <Icon name="chevron" size={16} /></Button>
        </form>
      </Card>
      <div className="login-footer">Hak Cipta © 2026 Tim Berry Care — TRPL Polsub</div>
    </div>
    <div className="login-side"><img src={images[0]} alt="Stroberi segar tumbuh di kebun Haifresh" /><div className="login-side-overlay"><div className="login-stat"><div className="stat-number">120</div><div className="stat-label">Polybag terhubung</div></div><div className="login-stat"><div className="stat-number">24/7</div><div className="stat-label">Pemantauan kebun</div></div></div></div>
  </main>;
}

function Dashboard({ onGoAlerts, onGoPolybag }: { onGoAlerts: () => void; onGoPolybag: () => void }) {
  const [handled, setHandled] = useState(false);
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Selamat pagi, Pak Dadang</div><div className="page-subtitle">Berikut ringkasan kondisi kebun hari ini, 14 Januari 2026.</div></div><div className="last-sync"><span className="live-dot" /> Data diperbarui 2 menit lalu</div></div>
    <div className="kpi-grid">
      <Kpi icon="thermo" label="Suhu & Kelembapan Udara" value="21.4°C" detail="84% RH" meta="DHT22 • Blok A" />
      <Kpi icon="alert" label="Status Risiko Jamur" value="Waspada Tinggi" detail="RH mendekati 85%" meta="Butuh pemantauan" danger />
      <Kpi icon="sprout" label="Total Polybag Aktif" value="120 Pot" detail="3 blok kebun" meta="98% sensor online" />
      <Kpi icon="camera" label="Total Scan Hari Ini" value="38 Tanaman" detail="5 terindikasi" meta="+12% dari kemarin" />
    </div>
    <div className="dashboard-grid">
      <Card className="chart-card">
        <SectionTitle title="Mikroklimat 24 Jam" description="Perbandingan suhu dan kelembapan area kebun" action={<div className="chart-legend"><span><i className="legend-dot temp" />Suhu °C</span><span><i className="legend-dot humidity" />RH %</span><span><i className="legend-line" />Ambang</span></div>} />
        <ClimateChart />
      </Card>
      <Card className={`warning-card ${handled ? "warning-resolved" : ""}`}>
        <div className="warning-top"><div className="alert-icon"><Icon name={handled ? "check" : "alert"} /></div><Badge tone={handled ? "green" : "red"}>{handled ? "DITANGANI" : "PERINGATAN AKTIF"}</Badge></div>
        <div className="warning-title">{handled ? "Peringatan telah ditangani" : "Kelembapan mendekati ambang kritis"}</div>
        <p className="warning-copy">{handled ? "Catatan penanganan tersimpan. Sistem terus memantau kelembapan area." : "RH di Blok A bertahan pada 84–87% selama 42 menit. Kondisi ini meningkatkan risiko Botrytis."}</p>
        <div className="recommendation"><div className="recommend-label">TINDAKAN DISARANKAN</div><div>Buka ventilasi sisi timur dan tunda penyiraman selama 60 menit.</div></div>
        <div className="warning-actions"><Button variant={handled ? "secondary" : "danger"} icon={handled ? "check" : undefined} onClick={() => setHandled(true)} disabled={handled}>{handled ? "Selesai" : "Tandai Ditangani"}</Button><Button variant="ghost" onClick={onGoAlerts}>Lihat detail <Icon name="chevron" size={14} /></Button></div>
      </Card>
    </div>
    <div className="dashboard-bottom">
      <Card>
        <SectionTitle title="Polybag Terakhir Diuji" description="Pembacaan sensor terbaru" action={<button className="text-link" onClick={onGoPolybag}>Lihat semua</button>} />
        <div className="table-wrap"><table><thead><tr><th>ID Pot</th><th>Blok</th><th>Kadar air</th><th>Hara</th><th>Waktu</th></tr></thead><tbody>{pots.slice(0, 4).map((p, i) => <tr key={p.id}><td className="strong">{p.id}</td><td>{p.block}</td><td>{p.water}%</td><td><Badge tone={p.status === "Optimal" ? "green" : "amber"}>{p.status}</Badge></td><td className="muted">{["10:48", "10:43", "10:37", "10:31"][i]}</td></tr>)}</tbody></table></div>
      </Card>
      <Card>
        <SectionTitle title="Pemindaian Terbaru" description="Hasil diagnosis AI hari ini" />
        <div className="recent-scans">{scans.slice(0, 3).map((s) => <div className="recent-scan" key={s.id}><img src={s.image} alt={s.label} /><div><div className="strong">{s.label}</div><div className="muted small">{s.pot} • {s.score}</div></div><Badge tone={s.danger ? "red" : "green"}>{s.danger ? "Cek" : "Baik"}</Badge></div>)}</div>
      </Card>
    </div>
  </div>;
}

function Kpi({ icon, label, value, detail, meta, danger = false }: { icon: IconName; label: string; value: string; detail: string; meta: string; danger?: boolean }) {
  return <Card className="kpi"><div className={`kpi-icon ${danger ? "kpi-danger" : ""}`}><Icon name={icon} /></div><div className="kpi-label">{label}</div><div className={`kpi-value ${danger ? "danger-text" : ""}`}>{value}</div><div className="kpi-foot"><span>{detail}</span><span>{meta}</span></div></Card>;
}

function ClimateChart() {
  return <div className="chart">
    <div className="chart-y"><span>100</span><span>80</span><span>60</span><span>40</span><span>20</span></div>
    <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-label="Grafik suhu dan kelembapan 24 jam">
      {[25, 65, 105, 145, 185].map((y) => <line key={y} x1="0" y1={y} x2="720" y2={y} className="grid-line" />)}
      <line x1="0" y1="53" x2="720" y2="53" className="threshold-line" />
      <path className="area-path" d="M0 73 C70 80 100 54 150 60 S240 42 300 55 S390 43 445 51 S540 33 600 48 S680 40 720 44 L720 220 L0 220 Z" />
      <path className="humidity-path" d="M0 73 C70 80 100 54 150 60 S240 42 300 55 S390 43 445 51 S540 33 600 48 S680 40 720 44" />
      <path className="temp-path" d="M0 171 C80 165 105 154 160 159 S250 134 310 142 S390 123 450 132 S550 145 605 138 S675 151 720 145" />
      <circle cx="720" cy="44" r="5" className="humidity-point" /><circle cx="720" cy="145" r="5" className="temp-point" />
    </svg>
    <div className="chart-x"><span>00:00</span><span>04:00</span><span>08:00</span><span>12:00</span><span>16:00</span><span>20:00</span><span>24:00</span></div>
  </div>;
}

function PolybagView({ initialSearch = "", showToast }: { initialSearch?: string; showToast: (message: string) => void }) {
  const [query, setQuery] = useState(initialSearch);
  const [block, setBlock] = useState("Semua Blok");
  const [selected, setSelected] = useState<(typeof pots)[number] | null>(null);
  const filtered = pots.filter((p) => (block === "Semua Blok" || p.block === block) && p.id.toLowerCase().includes(query.toLowerCase()));
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Lahan & Polybag</div><div className="page-subtitle">Pantau kondisi media tanam dan nutrisi setiap polybag secara real-time.</div></div><Badge tone="green"><span className="live-dot" /> 118 dari 120 sensor online</Badge></div>
    <Card className="toolbar"><div className="search-field"><Icon name="search" /><input value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Cari ID Pot..." /></div><div className="filter-label">Filter lokasi</div>{["Semua Blok", "Blok A", "Blok B", "Blok C"].map((item) => <button key={item} onClick={() => setBlock(item)} className={`filter-chip ${block === item ? "active" : ""}`}>{item}</button>)}</Card>
    <Card>
      <SectionTitle title="Data Telemetri Polybag" description={`${filtered.length} polybag ditampilkan • Pembaruan otomatis setiap 5 menit`} action={<Button variant="secondary" icon="history" onClick={() => showToast("Data telemetri berhasil diekspor")}>Ekspor Data</Button>} />
      <div className="table-wrap"><table className="data-table"><thead><tr><th>ID Pot</th><th>Lokasi</th><th>Kadar Air Tanah</th><th>N <small>mg/kg</small></th><th>P <small>mg/kg</small></th><th>K <small>mg/kg</small></th><th>Status Hara</th><th></th></tr></thead><tbody>{filtered.map((p) => <tr key={p.id}><td><div className="pot-cell"><span className="pot-icon"><Icon name="sprout" size={16} /></span><span className="strong">{p.id}</span></div></td><td>{p.block}</td><td><div className="water-cell"><span className={`water-bar ${p.water < 45 ? "low" : ""}`}><i style={{ width: `${p.water}%` }} /></span><span>{p.water}%</span></div></td><td>{p.n}</td><td>{p.p}</td><td>{p.k}</td><td><Badge tone={p.status === "Optimal" ? "green" : "amber"}>{p.status}</Badge></td><td><Button variant="ghost" icon="history" onClick={() => setSelected(p)}>Lihat Riwayat</Button></td></tr>)}</tbody></table>{filtered.length === 0 && <div className="empty-state">Tidak ada polybag yang cocok dengan pencarian.</div>}</div>
    </Card>
    <Modal open={!!selected} onClose={() => setSelected(null)} title={`Riwayat Telemetri ${selected?.id}`} wide>
      {selected && <div className="modal-content"><div className="modal-meta"><Badge tone={selected.status === "Optimal" ? "green" : "amber"}>{selected.status}</Badge><span>{selected.block}</span><span>Pembaruan terakhir 10:48 WIB</span></div><div className="telemetry-cards"><div><span>Kadar Air</span><strong>{selected.water}%</strong><small>+3% dalam 6 jam</small></div><div><span>Nitrogen</span><strong>{selected.n}</strong><small>mg/kg</small></div><div><span>Fosfor</span><strong>{selected.p}</strong><small>mg/kg</small></div><div><span>Kalium</span><strong>{selected.k}</strong><small>mg/kg</small></div></div><div className="mini-chart-title">Tren kadar air tanah • 7 hari</div><svg className="history-chart" viewBox="0 0 700 180" preserveAspectRatio="none"><path d="M0 110 C70 80 110 98 170 70 S280 75 350 48 S450 90 520 60 S630 42 700 52" /><line x1="0" y1="135" x2="700" y2="135" /></svg><div className="modal-footer"><Button variant="secondary" onClick={() => setSelected(null)}>Tutup</Button><Button onClick={() => showToast(`Laporan ${selected.id} berhasil disimpan`)}>Simpan Laporan</Button></div></div>}
    </Modal>
  </div>;
}

function DiagnosisView({ showToast }: { showToast: (message: string) => void }) {
  const [filter, setFilter] = useState("Semua");
  const [query, setQuery] = useState("");
  const [selected, setSelected] = useState<(typeof scans)[number] | null>(null);
  const [upload, setUpload] = useState(false);
  const [fileName, setFileName] = useState("");
  const shown = scans.filter((s) => (filter === "Semua" || (filter === "Jamur" ? s.danger : s.type === filter)) && (s.label + s.pot).toLowerCase().includes(query.toLowerCase()));
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Riwayat Diagnosis AI</div><div className="page-subtitle">Hasil klasifikasi kesehatan daun dan kematangan buah dari kamera kebun.</div></div><Button icon="upload" onClick={() => setUpload(true)}>Upload Foto Manual</Button></div>
    <Card className="toolbar diagnosis-toolbar"><div className="search-field"><Icon name="search" /><input value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Cari ID pot atau diagnosis..." /></div><div className="date-field"><Icon name="calendar" /><span>14 Jan 2026</span></div></Card>
    <div className="filter-tabs">{[["Semua", "38"], ["Daun", "26"], ["Buah", "12"], ["Jamur", "5"]].map(([item, count]) => <button key={item} onClick={() => setFilter(item)} className={filter === item ? "active" : ""}>{item === "Jamur" ? "Terdeteksi Jamur" : item}<span>{count}</span></button>)}</div>
    <div className="scan-grid">{shown.map((s) => <button className="scan-card" key={s.id} onClick={() => setSelected(s)}><div className="scan-image"><img src={s.image} alt={`Hasil scan ${s.label}`} /><Badge tone={s.danger ? "red" : "green"}>{s.type}</Badge><span className="confidence">{s.score}</span></div><div className="scan-body"><div><div className="scan-label">{s.label}</div><div className="muted small">{s.pot} • {s.time}</div></div><span className="round-arrow"><Icon name="chevron" size={15} /></span></div></button>)}</div>
    <Card>
      <SectionTitle title="Log Pemindaian" description="Semua aktivitas diagnosis hari ini" />
      <div className="table-wrap"><table><thead><tr><th>Scan ID</th><th>Waktu</th><th>Polybag</th><th>Objek</th><th>Hasil Diagnosis</th><th>Kepercayaan</th><th></th></tr></thead><tbody>{scans.map((s) => <tr key={s.id} className="clickable-row" onClick={() => setSelected(s)}><td className="strong">{s.id}</td><td className="muted">{s.time}</td><td>{s.pot}</td><td>{s.type}</td><td><Badge tone={s.danger ? "red" : "green"}>{s.label}</Badge></td><td className="strong">{s.score}</td><td><Icon name="chevron" size={15} /></td></tr>)}</tbody></table></div>
    </Card>
    <Modal open={!!selected} onClose={() => setSelected(null)} title="Detail Hasil Diagnosis" wide>
      {selected && <div className="scan-modal"><div className="scan-modal-image"><img src={selected.image} alt={selected.label} /><div className="scan-box" /></div><div className="scan-modal-info"><div className="modal-meta"><Badge tone={selected.danger ? "red" : "green"}>{selected.label}</Badge><span>{selected.score} confidence</span></div><div className="detail-title">{selected.danger ? "Indikasi penyakit terdeteksi" : "Tanaman dalam kondisi baik"}</div><p>{selected.danger ? `${selected.label} terdeteksi pada permukaan tanaman. Model menemukan pola warna dan tekstur yang konsisten dengan infeksi awal.` : "Tidak ditemukan indikasi penyakit pada area yang dipindai. Pertumbuhan dan warna tanaman berada dalam rentang normal."}</p><div className="sop-box"><div className="recommend-label">SOP PENANGANAN</div><ol><li>Isolasi dan tandai polybag {selected.pot}.</li><li>Pangkas bagian yang terindikasi menggunakan alat steril.</li><li>Aplikasikan fungisida hayati sesuai dosis agronomis.</li><li>Lakukan pemindaian ulang dalam 48 jam.</li></ol></div><div className="modal-footer"><Button variant="secondary" onClick={() => setSelected(null)}>Tutup</Button><Button onClick={() => showToast(`Laporan ${selected.id} berhasil diunduh`)}>Unduh Laporan</Button></div></div></div>}
    </Modal>
    <Modal open={upload} onClose={() => setUpload(false)} title="Upload Foto Manual">
      <div className="modal-content"><label className="upload-zone"><input type="file" accept="image/*" onChange={(e) => setFileName(e.target.files?.[0]?.name || "")} /><Icon name="upload" size={28} /><strong>{fileName || "Pilih foto daun atau buah"}</strong><span>JPG atau PNG, maksimum 10 MB</span></label><label className="field-label">ID Polybag</label><select className="input"><option>POT-01</option>{pots.slice(1).map((p) => <option key={p.id}>{p.id}</option>)}</select><div className="modal-footer"><Button variant="secondary" onClick={() => setUpload(false)}>Batal</Button><Button onClick={() => setUpload(false)} disabled={!fileName}>Mulai Diagnosis</Button></div></div>
    </Modal>
  </div>;
}

const initialAlerts = [
  { id: 1, severity: "Kritis", title: "Kelembapan Udara >85% Mempercepat Spora Botrytis", location: "Blok A • Sensor DHT22-01", time: "12 menit lalu", action: "Buka ventilasi sisi timur dan hentikan penyiraman selama 60 menit.", done: false },
  { id: 2, severity: "Waspada", title: "Polybag POT-05 Kekurangan Nitrogen Akut", location: "Blok B • POT-05", time: "38 menit lalu", action: "Aplikasikan larutan nutrisi N sesuai dosis koreksi dan ukur ulang dalam 6 jam.", done: false },
  { id: 3, severity: "Info", title: "Kadar Air POT-08 Mendekati Batas Minimum", location: "Blok C • POT-08", time: "1 jam lalu", action: "Jadwalkan irigasi pendek 8–10 menit pada siklus berikutnya.", done: false },
];

function AlertsView() {
  const [alerts, setAlerts] = useState(initialAlerts);
  const markDone = (id: number) => setAlerts((all) => all.map((a) => a.id === id ? { ...a, done: true } : a));
  const open = alerts.filter((a) => !a.done).length;
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Early Warning & Alerts</div><div className="page-subtitle">Prioritaskan tindakan lapangan berdasarkan tingkat risiko kebun.</div></div><Badge tone={open ? "red" : "green"}>{open} peringatan belum selesai</Badge></div>
    <div className="alert-summary"><div><span className="summary-count critical">{alerts.filter((a) => a.severity === "Kritis" && !a.done).length}</span><span>Kritis</span></div><div><span className="summary-count warning">{alerts.filter((a) => a.severity === "Waspada" && !a.done).length}</span><span>Waspada</span></div><div><span className="summary-count info">{alerts.filter((a) => a.severity === "Info" && !a.done).length}</span><span>Informasi</span></div></div>
    <div className="alerts-list">{alerts.map((a) => <Card key={a.id} className={`alert-row severity-${a.severity.toLowerCase()} ${a.done ? "is-done" : ""}`}><div className="alert-row-icon"><Icon name={a.done ? "check" : a.severity === "Info" ? "bell" : "alert"} /></div><div className="alert-main"><div className="alert-heading"><Badge tone={a.done ? "green" : a.severity === "Kritis" ? "red" : a.severity === "Waspada" ? "amber" : "blue"}>{a.done ? "Selesai" : a.severity}</Badge><span className="muted small">{a.time}</span></div><div className="alert-title">{a.title}</div><div className="muted">{a.location}</div><div className="action-box"><strong>Tindakan yang direkomendasikan</strong><span>{a.action}</span></div></div><Button variant={a.done ? "secondary" : "primary"} icon="check" onClick={() => markDone(a.id)} disabled={a.done}>{a.done ? "Sudah Selesai" : "Tandai Selesai"}</Button></Card>)}</div>
  </div>;
}

function ThresholdView({ showToast }: { showToast: (message: string) => void }) {
  const [values, setValues] = useState({ humidity: 85, water: 45, n: 30, p: 20, k: 120 });
  const change = (key: keyof typeof values, value: number) => setValues({ ...values, [key]: value });
  const submit = (e: FormEvent) => { e.preventDefault(); showToast("Pengaturan berhasil diperbarui"); };
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Konfigurasi Threshold</div><div className="page-subtitle">Atur ambang batas sistem peringatan otomatis untuk kondisi kebun.</div></div><Badge tone="slate">Terakhir diubah 12 Jan 2026</Badge></div>
    <form onSubmit={submit}>
      <div className="settings-layout">
        <div className="settings-main">
          <Card>
            <SectionTitle title="Ambang Lingkungan" description="Batas pemicu peringatan berdasarkan pembacaan sensor mikroklimat." />
            <SettingRow icon="droplet" title="Kelembapan Udara Pemicu Jamur" description="Peringatan aktif ketika RH melebihi batas selama 15 menit." value={values.humidity} unit="%" onChange={(v) => change("humidity", v)} />
            <SettingRow icon="sprout" title="Minimum Kadar Air Tanah Polybag" description="Peringatan irigasi ketika media tanam berada di bawah batas." value={values.water} unit="%" onChange={(v) => change("water", v)} />
          </Card>
          <Card>
            <SectionTitle title="Target Hara Minimum" description="Nilai minimum unsur makro untuk status nutrisi optimal." />
            <SettingRow icon="leaf" title="Nitrogen (N)" description="Mendukung pertumbuhan vegetatif dan pembentukan klorofil." value={values.n} unit="mg/kg" onChange={(v) => change("n", v)} />
            <SettingRow icon="leaf" title="Fosfor (P)" description="Mendukung perkembangan akar serta pembentukan bunga." value={values.p} unit="mg/kg" onChange={(v) => change("p", v)} />
            <SettingRow icon="leaf" title="Kalium (K)" description="Mendukung kualitas buah dan ketahanan tanaman." value={values.k} unit="mg/kg" onChange={(v) => change("k", v)} />
          </Card>
        </div>
        <aside className="settings-side"><Card><div className="settings-note-icon"><Icon name="sliders" /></div><div className="detail-title">Tentang ambang batas</div><p className="muted">Perubahan konfigurasi berlaku untuk semua blok dan akan digunakan oleh sistem peringatan pada siklus pembacaan berikutnya.</p><div className="settings-tip"><strong>Rekomendasi Agronomis</strong><span>Pastikan perubahan nilai telah dikonsultasikan dengan agronomis kebun.</span></div></Card><Button type="submit" className="btn-full">Simpan Konfigurasi</Button></aside>
      </div>
    </form>
  </div>;
}

function SettingRow({ icon, title, description, value, unit, onChange }: { icon: IconName; title: string; description: string; value: number; unit: string; onChange: (v: number) => void }) {
  return <div className="setting-row"><div className="setting-icon"><Icon name={icon} /></div><div className="setting-copy"><div className="strong">{title}</div><div className="muted small">{description}</div></div><div className="number-input"><input type="number" min="0" value={value} onChange={(e) => onChange(Number(e.target.value))} /><span>{unit}</span></div></div>;
}

const initialUsers = [
  { name: "Dadang Suhendar", initials: "DS", email: "dadang@haifresh.id", role: "Owner", last: "Hari ini, 10:52" },
  { name: "Dr. Rina Wulandari", initials: "RW", email: "rina@haifresh.id", role: "Agronomis", last: "Hari ini, 09:41" },
  { name: "Asep Mulyana", initials: "AM", email: "asep@haifresh.id", role: "Petani Lapangan", last: "Kemarin, 16:28" },
  { name: "Yudi Hermawan", initials: "YH", email: "yudi@haifresh.id", role: "Petani Lapangan", last: "12 Jan 2026" },
];

function UsersView({ showToast }: { showToast: (message: string) => void }) {
  const [users, setUsers] = useState(initialUsers);
  const [adding, setAdding] = useState(false);
  const [query, setQuery] = useState("");
  const [form, setForm] = useState({ name: "", email: "", role: "Petani Lapangan" });
  const visibleUsers = users.filter((u) => (u.name + u.email + u.role).toLowerCase().includes(query.toLowerCase()));
  const submit = (e: FormEvent) => { e.preventDefault(); const initials = form.name.split(" ").map((p) => p[0]).join("").slice(0, 2).toUpperCase(); setUsers([...users, { ...form, initials, last: "Belum pernah" }]); setAdding(false); setForm({ name: "", email: "", role: "Petani Lapangan" }); showToast("Pengguna baru berhasil ditambahkan"); };
  return <div className="view-stack">
    <div className="welcome"><div><div className="page-title">Manajemen Pengguna</div><div className="page-subtitle">Kelola akses tim dan peran pengguna dalam sistem Berry Care.</div></div><Button icon="plus" onClick={() => setAdding(true)}>Tambah Pengguna</Button></div>
    <Card>
      <SectionTitle title="Daftar Pengguna" description={`${users.length} pengguna terdaftar`} action={<div className="search-field small-search"><Icon name="search" /><input value={query} onChange={(e) => setQuery(e.target.value)} placeholder="Cari pengguna..." /></div>} />
      <div className="table-wrap"><table className="users-table"><thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Aktivitas Terakhir</th><th></th></tr></thead><tbody>{visibleUsers.map((u) => <tr key={u.email}><td><div className="user-cell"><span className="avatar avatar-light">{u.initials}</span><span className="strong">{u.name}</span></div></td><td>{u.email}</td><td><Badge tone={u.role === "Owner" ? "blue" : u.role === "Agronomis" ? "green" : "slate"}>{u.role}</Badge></td><td><span className="active-status"><i />Aktif</span></td><td className="muted">{u.last}</td><td><button className="icon-button" onClick={() => showToast(`Menu pengaturan ${u.name} dibuka`)} aria-label={`Pengaturan ${u.name}`}>•••</button></td></tr>)}</tbody></table></div>
    </Card>
    <Modal open={adding} onClose={() => setAdding(false)} title="Tambah Pengguna">
      <form onSubmit={submit} className="modal-content"><label className="field-label">Nama Lengkap</label><input className="input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} placeholder="Masukkan nama lengkap" required /><label className="field-label">Email</label><input className="input" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} placeholder="nama@haifresh.id" required /><label className="field-label">Peran</label><select className="input" value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}><option>Owner</option><option>Agronomis</option><option>Petani Lapangan</option></select><div className="modal-footer"><Button variant="secondary" onClick={() => setAdding(false)}>Batal</Button><Button type="submit">Tambah Pengguna</Button></div></form>
    </Modal>
  </div>;
}

function AppShell({ onLogout }: { onLogout: () => void }) {
  const [activeTab, setActiveTab] = useState<TabId>("dashboard");
  const [quickSearch, setQuickSearch] = useState("");
  const [toast, setToast] = useState("");
  const toastTimer = useRef<number | null>(null);
  const current = tabs.find((t) => t.id === activeTab)!;
  const showToast = (message: string) => { setToast(message); if (toastTimer.current) window.clearTimeout(toastTimer.current); toastTimer.current = window.setTimeout(() => setToast(""), 2800); };
  const quickSubmit = (e: FormEvent) => { e.preventDefault(); if (quickSearch.trim()) setActiveTab("polybag"); };
  const content = useMemo(() => {
    if (activeTab === "dashboard") return <Dashboard onGoAlerts={() => setActiveTab("alerts")} onGoPolybag={() => setActiveTab("polybag")} />;
    if (activeTab === "polybag") return <PolybagView initialSearch={quickSearch} showToast={showToast} />;
    if (activeTab === "diagnosis") return <DiagnosisView showToast={showToast} />;
    if (activeTab === "alerts") return <AlertsView />;
    if (activeTab === "threshold") return <ThresholdView showToast={showToast} />;
    return <UsersView showToast={showToast} />;
  }, [activeTab, quickSearch]);
  return <div className="app-shell">
    <aside className="sidebar">
      <div className="sidebar-header"><Logo dark /></div>
      <div className="nav-label">MENU UTAMA</div>
      <nav className="sidebar-nav">{tabs.map((tab) => <button key={tab.id} onClick={() => setActiveTab(tab.id)} className={activeTab === tab.id ? "active" : ""}><span className="nav-icon"><Icon name={tab.icon as IconName} /></span><span className="nav-copy"><strong>{tab.label}</strong><small>{tab.sub}</small></span>{tab.id === "alerts" && <span className="nav-badge">2</span>}</button>)}</nav>
      <div className="sidebar-status"><div className="status-line"><span className="live-dot" /> Sistem beroperasi normal</div><div className="muted-sidebar">Sinkronisasi terakhir 10:54 WIB</div></div>
      <div className="user-panel"><div className="avatar">PD</div><div className="user-details"><strong>Pak Dadang</strong><span>Manager Kebun</span></div><button className="logout-button" onClick={onLogout} aria-label="Keluar"><Icon name="logout" /></button></div>
    </aside>
    <main className="main-area">
      <header className="topbar"><div className="breadcrumb"><span>Berry Care</span><Icon name="chevron" size={14} /><strong>{current.label}</strong></div><div className="topbar-actions"><div className="weather"><Icon name="cloud" size={17} /><span><strong>Lembang: 21°C</strong><i />84% RH<i />Lembap Basah</span></div><form className="quick-search" onSubmit={quickSubmit}><Icon name="search" size={17} /><input value={quickSearch} onChange={(e) => setQuickSearch(e.target.value)} placeholder="Cari Pot ID..." /><kbd>↵</kbd></form></div></header>
      <div className="content">{content}</div>
    </main>
    {toast && <div className="toast"><Icon name="check" /><span>{toast}</span></div>}
  </div>;
}

export default function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(true);
  return isAuthenticated ? <AppShell onLogout={() => setIsAuthenticated(false)} /> : <Login onLogin={() => setIsAuthenticated(true)} />;
}
