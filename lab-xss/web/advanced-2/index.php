<?php
$active = 'advanced-2';
$input  = $_GET['q'] ?? '';

// CSP yang sengaja dikonfigurasi dengan keliru
// unsafe-inline diizinkan, juga whitelisted domain yang bisa di-abuse
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 2: CSP Bypass — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 2: CSP Bypass</span></div>
    <h1>XSS + CSP Bypass <span class="tag r">ADVANCED 2</span></h1>
    <p class="phdr-desc">Content Security Policy (CSP) diaktifkan namun dikonfigurasi secara keliru. Analisis policy yang diterapkan dan temukan celah untuk mengeksekusi JavaScript.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Baca dan pahami CSP header yang dikirim server (periksa DevTools &rarr; Network &rarr; Response Headers)</span></li>
      <li><div class="obj-n">2</div><span>Identifikasi misconfiguration dalam policy yang memungkinkan bypass</span></li>
      <li><div class="obj-n">3</div><span>Eksekusi JavaScript menggunakan teknik bypass yang sesuai dengan policy</span></li>
      <li><div class="obj-n">4</div><span>Pahami mengapa <code class="ic">unsafe-inline</code> dan whitelisted CDN domain berbahaya</span></li>
    </ul>
  </div>

  <!-- CSP Analysis -->
  <div class="box">
    <div class="box-t">Active CSP Header</div>
    <div class="qbox"><div class="ql">Content-Security-Policy</div>default-src <span class="str">'self'</span>;
script-src <span class="str">'self'</span> <span class="kw">'unsafe-inline'</span> <span class="at">https://cdn.jsdelivr.net</span>;
style-src  <span class="str">'self'</span> <span class="kw">'unsafe-inline'</span>;</div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px">
      <div style="background:var(--rbg);border:1px solid var(--rbdr);border-radius:var(--r);padding:12px 14px">
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--red);font-family:var(--mono);margin-bottom:6px">Masalah</div>
        <ul style="font-size:.8rem;color:var(--t2);line-height:1.8;padding-left:14px">
          <li><code class="ic">'unsafe-inline'</code> mengizinkan inline script</li>
          <li>CDN domain bisa di-abuse jika ada file JS yang controllable</li>
        </ul>
      </div>
      <div style="background:var(--gbg);border:1px solid var(--gbdr);border-radius:var(--r);padding:12px 14px">
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--green);font-family:var(--mono);margin-bottom:6px">CSP yang Benar</div>
        <ul style="font-size:.8rem;color:var(--t2);line-height:1.8;padding-left:14px">
          <li>Gunakan <code class="ic">nonce</code> atau <code class="ic">hash</code></li>
          <li>Jangan gunakan <code class="ic">unsafe-inline</code></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Search Input <span style="font-size:.68rem;font-weight:400;color:var(--t3)">(direfleksikan tanpa encoding)</span></div>
    <form method="GET" action="/advanced-2/">
      <div class="frow">
        <div class="fg">
          <label class="fl">Input</label>
          <input class="fi" type="text" name="q"
            value="<?= htmlspecialchars($input, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Masukkan XSS payload yang sesuai dengan CSP...">
        </div>
        <button type="submit" class="btn btn-r">Test</button>
        <?php if($input): ?><a href="/advanced-2/" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>

    <?php if ($input !== ''): ?>
    <div style="margin-top:16px;background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 16px">
      <?= $input /* VULNERABLE: tidak di-encode */ ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- CSP Directives Explained -->
  <div class="box">
    <div class="box-t">CSP Directives Reference</div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Directive</th><th>Nilai</th><th>Dampak</th></tr></thead>
        <tbody>
          <tr><td><code class="ic">default-src</code></td><td style="color:var(--green)">'self'</td><td style="color:var(--t2)">Default untuk semua resource — hanya dari domain sendiri</td></tr>
          <tr><td><code class="ic">script-src</code></td><td style="color:var(--red)">'unsafe-inline'</td><td style="color:var(--red)">Mengizinkan inline &lt;script&gt; — bypass XSS protection</td></tr>
          <tr><td><code class="ic">script-src</code></td><td style="color:var(--orange)">cdn.jsdelivr.net</td><td style="color:var(--t2)">CDN besar — banyak file JS yang bisa di-abuse sebagai gadget</td></tr>
          <tr><td><code class="ic">style-src</code></td><td style="color:var(--orange)">'unsafe-inline'</td><td style="color:var(--t2)">CSS injection memungkinkan beberapa serangan tidak langsung</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Periksa CSP di browser</summary>
      <div class="hint-body">
        Buka Developer Tools (F12) &rarr; tab <strong>Network</strong> &rarr; klik request halaman ini &rarr;
        lihat <strong>Response Headers</strong>. Cari header <code class="ic">Content-Security-Policy</code>
        dan analisis setiap direktif.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; unsafe-inline bypass</summary>
      <div class="hint-body">
        Karena <code class="ic">'unsafe-inline'</code> diizinkan, inline script langsung bisa dieksekusi:<br>
        <code class="ic">&lt;script&gt;alert(document.domain)&lt;/script&gt;</code><br>
        Seharusnya CSP memblokir ini, namun <code class="ic">unsafe-inline</code> menonaktifkan proteksi tersebut.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; CDN JSONP/Gadget abuse</summary>
      <div class="hint-body">
        Domain <code class="ic">cdn.jsdelivr.net</code> diwhitelist. Jika ada file JavaScript di CDN tersebut
        yang dapat dimanipulasi sebagai JSONP endpoint atau mengandung "gadget" yang bisa mengeksekusi
        callback yang kita kontrol, domain whitelist menjadi vektor bypass.<br>
        Contoh: <code class="ic">&lt;script src="https://cdn.jsdelivr.net/...?callback=alert"&gt;&lt;/script&gt;</code>
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; CSP yang benar</summary>
      <div class="hint-body">
        Policy yang aman menggunakan <code class="ic">nonce</code>:<br>
        <code class="ic">script-src 'nonce-RANDOM_VALUE_PER_REQUEST'</code><br>
        Hanya script dengan atribut <code class="ic">nonce</code> yang cocok yang dapat dieksekusi.
        Attacker tidak dapat mengetahui nilai nonce yang di-generate server untuk setiap request.
      </div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
