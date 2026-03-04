<?php $active = 'home'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>XSS Injection Lab — ID-Networkers</title>
<?php include 'includes/shared_css.php'; ?>
<style>
.hero{background:var(--surface);border-bottom:1px solid var(--bd)}
.hero-in{max-width:1160px;margin:0 auto;padding:60px 40px 52px;display:grid;grid-template-columns:1fr 210px;gap:56px;align-items:center;position:relative}
.hero-in::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 50% 100% at 0 50%,rgba(230,57,70,.05),transparent 65%);pointer-events:none}
.hero-eye{display:inline-flex;align-items:center;gap:8px;font-size:.66rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--red);background:var(--rbg);border:1px solid var(--rbdr);padding:4px 12px;border-radius:20px;margin-bottom:20px}
.hero-eye i{width:6px;height:6px;border-radius:50%;background:var(--red);animation:blink 2s infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.hero h1{font-size:2.5rem;font-weight:800;line-height:1.15;letter-spacing:-.025em;color:var(--t1);margin-bottom:16px}
.hero h1 b{color:var(--red)}
.hero-sub{font-size:.9rem;color:var(--t2);max-width:520px;line-height:1.8;margin-bottom:24px}
.hero-note{display:inline-flex;align-items:center;gap:10px;font-size:.72rem;font-family:var(--mono);color:var(--t3);border:1px solid var(--bd);border-radius:var(--r);padding:8px 16px;background:var(--bg)}
.dot-r{width:7px;height:7px;border-radius:50%;background:var(--red);flex-shrink:0;animation:blink 2s infinite}
.hero-stats{display:flex;flex-direction:column;gap:10px}
.stat{background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);padding:16px 20px;text-align:center;transition:border-color .15s}
.stat:hover{border-color:var(--red)}
.stat-n{font-size:2rem;font-weight:800;color:var(--red);font-family:var(--mono);line-height:1}
.stat-l{font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);margin-top:5px}

.main{max-width:1160px;margin:0 auto;padding:44px 40px 72px}
.sec{margin-bottom:44px}
.sec-head{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.sec-head::before{content:'';width:3px;height:16px;background:var(--red);border-radius:2px;flex-shrink:0}
.sec-head h2{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--t2)}

.about{background:var(--card);border:1px solid var(--bd);border-left:3px solid var(--red);border-radius:var(--r2);padding:22px 26px}
.about p{font-size:.88rem;color:var(--t2);line-height:1.85}

/* MODULE GRID */
.mod-section{margin-bottom:28px}
.mod-section-label{font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--t3);margin-bottom:12px;padding-left:2px;font-family:var(--mono)}
.mods{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.mod{display:block;color:inherit;background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);overflow:hidden;transition:transform .15s,border-color .15s,box-shadow .15s}
.mod:hover{transform:translateY(-3px);border-color:var(--bd2);box-shadow:0 14px 40px rgba(0,0,0,.45)}
.mod-line{height:3px}
.mod-line.g{background:var(--green)}.mod-line.o{background:var(--orange)}.mod-line.r{background:var(--red)}.mod-line.p{background:var(--purple)}
.mod-body{padding:20px}
.mod-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.mod-ico{width:36px;height:36px;border-radius:var(--r);display:flex;align-items:center;justify-content:center}
.mod-ico.g{background:var(--gbg);color:var(--green)}
.mod-ico.o{background:var(--obg);color:var(--orange)}
.mod-ico.r{background:var(--rbg);color:var(--red)}
.mod-ico.p{background:var(--pbg);color:var(--purple)}
.mod-ico svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.mod h3{font-size:.92rem;font-weight:700;color:var(--t1);margin-bottom:7px;letter-spacing:-.01em}
.mod-desc{font-size:.81rem;color:var(--t2);line-height:1.65;margin-bottom:13px}
.mod-list{list-style:none;display:flex;flex-direction:column;gap:4px;margin-bottom:18px}
.mod-list li{font-size:.75rem;color:var(--t3);font-family:var(--mono);padding-left:13px;position:relative}
.mod-list li::before{content:'›';position:absolute;left:0;color:var(--red)}
.mod-foot{display:flex;align-items:center;justify-content:space-between;padding-top:13px;border-top:1px solid var(--bd);font-size:.77rem;font-weight:600;color:var(--t3);transition:color .15s}
.mod:hover .mod-foot{color:var(--red)}
.mod-foot svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}

/* XSS TYPE TABLE */
.type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.type-card{background:var(--card);border:1px solid var(--bd);border-radius:var(--r2);padding:18px}
.type-num{font-size:.62rem;font-weight:700;letter-spacing:.1em;font-family:var(--mono);color:var(--red);margin-bottom:8px}
.type-name{font-size:.88rem;font-weight:700;color:var(--t1);margin-bottom:6px}
.type-desc{font-size:.78rem;color:var(--t2);line-height:1.65}

@media(max-width:900px){
  .hero-in,.mods,.type-grid{grid-template-columns:1fr}
  .hero-stats{flex-direction:row}.stat{flex:1}
  .nav,.main,footer{padding-left:20px;padding-right:20px}
  .hero-in{padding:40px 20px}
}
</style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="hero">
  <div class="hero-in">
    <div>
      <div class="hero-eye"><i></i>Vulnerability Research</div>
      <h1>XSS Injection<br><b>Lab Environment</b></h1>
      <p class="hero-sub">Lingkungan praktik Cross-Site Scripting (XSS) terstruktur untuk keperluan edukasi keamanan siber. Enam modul mencakup teknik dari Reflected XSS, Stored XSS, DOM-Based, hingga filter bypass dan session hijacking.</p>
      <div class="hero-note">
        <span class="dot-r"></span>
        FOR EDUCATIONAL USE ONLY &mdash; Gunakan hanya di environment lab terisolasi
      </div>
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Modules</div></div>
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Challenges</div></div>
      <div class="stat"><div class="stat-n">6</div><div class="stat-l">Flags</div></div>
    </div>
  </div>
</div>

<div class="main">

  <div class="sec">
    <div class="sec-head"><h2>Tentang Lab</h2></div>
    <div class="about">
      <p>Lab ini dirancang untuk memahami kerentanan Cross-Site Scripting (XSS) dari sisi teknis secara mendalam. Setiap modul merepresentasikan skenario nyata yang ditemukan pada aplikasi web modern — mulai dari refleksi input tanpa sanitasi, penyimpanan payload di database, manipulasi DOM pada sisi klien, hingga teknik lanjutan seperti bypass filter dan eksfiltrasi session cookie.</p>
    </div>
  </div>

  <div class="sec">
    <div class="sec-head"><h2>Tipe XSS</h2></div>
    <div class="type-grid">
      <div class="type-card">
        <div class="type-num">TYPE 01</div>
        <div class="type-name">Reflected XSS</div>
        <div class="type-desc">Payload tidak disimpan — langsung direfleksikan ke respons HTTP. Korban perlu mengklik link berbahaya yang mengandung payload di URL.</div>
      </div>
      <div class="type-card">
        <div class="type-num">TYPE 02</div>
        <div class="type-name">Stored XSS</div>
        <div class="type-desc">Payload disimpan di database dan dieksekusi setiap kali halaman dibuka. Lebih berbahaya karena tidak memerlukan interaksi langsung dari attacker.</div>
      </div>
      <div class="type-card">
        <div class="type-num">TYPE 03</div>
        <div class="type-name">DOM-Based XSS</div>
        <div class="type-desc">Payload dieksekusi melalui manipulasi DOM di sisi klien. Server tidak terlibat — kerentanan berada sepenuhnya di JavaScript browser.</div>
      </div>
    </div>
  </div>

  <div class="sec">
    <div class="sec-head"><h2>Lab Modules</h2></div>

    <div class="mod-section">
      <div class="mod-section-label">Basic Series</div>
      <div class="mods">

        <a href="/basic-1/" class="mod">
          <div class="mod-line g"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico g">
                <svg viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              </div>
              <span class="tag g">BASIC 1</span>
            </div>
            <h3>Reflected XSS</h3>
            <p class="mod-desc">Input dari URL langsung ditampilkan ke halaman tanpa encoding. Payload dieksekusi saat link diklik.</p>
            <ul class="mod-list">
              <li>Search field tanpa sanitasi</li>
              <li>Reflected ke HTML response</li>
              <li>Alert box sebagai proof-of-concept</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/basic-2/" class="mod">
          <div class="mod-line o"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico o">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </div>
              <span class="tag o">BASIC 2</span>
            </div>
            <h3>Stored XSS</h3>
            <p class="mod-desc">Payload disimpan di database melalui form komentar dan dieksekusi setiap kali halaman dibuka oleh user manapun.</p>
            <ul class="mod-list">
              <li>Form komentar tanpa filtering</li>
              <li>Persistent payload di database</li>
              <li>Eksekusi otomatis saat page load</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/basic-3/" class="mod">
          <div class="mod-line g"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico g">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
              </div>
              <span class="tag g">BASIC 3</span>
            </div>
            <h3>DOM-Based XSS</h3>
            <p class="mod-desc">Payload diproses oleh JavaScript di browser menggunakan <code class="ic">innerHTML</code>. Server tidak melihat payload sama sekali.</p>
            <ul class="mod-list">
              <li>Fragment identifier (#hash) injection</li>
              <li>innerHTML sink vulnerable</li>
              <li>Client-side only execution</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

      </div>
    </div>

    <div class="mod-section">
      <div class="mod-section-label">Advanced Series</div>
      <div class="mods">

        <a href="/advanced-1/" class="mod">
          <div class="mod-line o"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico o">
                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </div>
              <span class="tag o">ADV 1</span>
            </div>
            <h3>XSS + Filter Bypass</h3>
            <p class="mod-desc">Server memfilter tag dan keyword tertentu. Pelajari teknik encoding dan obfuscation untuk melewati filter berbasis blacklist.</p>
            <ul class="mod-list">
              <li>Blacklist filter evasion</li>
              <li>Case & encoding bypass</li>
              <li>Alternative event handlers</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/advanced-2/" class="mod">
          <div class="mod-line r"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico r">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              </div>
              <span class="tag r">ADV 2</span>
            </div>
            <h3>XSS + CSP Bypass</h3>
            <p class="mod-desc">Content Security Policy (CSP) diaktifkan namun dikonfigurasi dengan keliru. Temukan celah dalam policy untuk mengeksekusi script.</p>
            <ul class="mod-list">
              <li>CSP header analysis</li>
              <li>unsafe-inline & nonce bypass</li>
              <li>Whitelisted domain abuse</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

        <a href="/advanced-3/" class="mod">
          <div class="mod-line p"></div>
          <div class="mod-body">
            <div class="mod-top">
              <div class="mod-ico p">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <span class="tag p">ADV 3</span>
            </div>
            <h3>XSS + Session Hijacking</h3>
            <p class="mod-desc">Cookie <code class="ic">HttpOnly</code> tidak diset. Gunakan XSS untuk mencuri session token dan impersonasi akun admin.</p>
            <ul class="mod-list">
              <li>document.cookie exfiltration</li>
              <li>Session token theft</li>
              <li>Account takeover simulation</li>
            </ul>
            <div class="mod-foot"><span>Mulai Modul</span><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div>
          </div>
        </a>

      </div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
