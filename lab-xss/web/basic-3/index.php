<?php $active = 'basic-3'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 3: DOM-Based XSS — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 3: DOM-Based XSS</span></div>
    <h1>DOM-Based XSS <span class="tag g">BASIC 3</span></h1>
    <p class="phdr-desc">Payload diproses sepenuhnya di sisi klien oleh JavaScript. Server tidak pernah melihat payload — kerentanan berada pada cara JavaScript membaca dan menulis ke DOM.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Pahami perbedaan DOM-Based XSS dengan Reflected dan Stored XSS</span></li>
      <li><div class="obj-n">2</div><span>Temukan sumber data (source) yang dibaca JavaScript tanpa validasi</span></li>
      <li><div class="obj-n">3</div><span>Eksekusi <code class="ic">alert(document.domain)</code> melalui manipulasi DOM</span></li>
      <li><div class="obj-n">4</div><span>Pahami mengapa server-side filtering tidak efektif terhadap DOM XSS</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <p class="prose" style="margin-bottom:12px">
      Halaman ini membaca nilai dari <code class="ic">location.hash</code> (fragment identifier setelah <code class="ic">#</code> di URL) dan langsung menuliskannya ke DOM menggunakan <code class="ic">innerHTML</code>:
    </p>
    <div class="qbox"><div class="ql">Vulnerable JavaScript</div><span class="cm">// Source: location.hash (tidak dikirim ke server)</span>
<span class="kw">var</span> name = <span class="val">decodeURIComponent</span>(<span class="val">location.hash</span>.<span class="at">substring</span>(<span class="val">1</span>));

<span class="cm">// Sink: innerHTML (mengeksekusi HTML/JS)</span>
document.<span class="at">getElementById</span>(<span class="str">'greeting'</span>).<span class="kw">innerHTML</span> = <span class="str">"Halo, "</span> + name;</div>
    <p class="prose">Karena <code class="ic">#fragment</code> tidak dikirim ke server, WAF dan server-side filter tidak akan mendeteksi serangan ini.</p>
  </div>

  <div class="box">
    <div class="box-t">Personalized Greeting</div>
    <p style="font-size:.84rem;color:var(--t2);margin-bottom:14px">
      Tambahkan nama kamu di URL menggunakan <code class="ic">#NamaKamu</code> untuk mendapat sapaan personal.
    </p>
    <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:16px 20px;font-size:.96rem;color:var(--t1);min-height:52px" id="greeting">
      Tambahkan nama di URL: <code class="ic">/basic-3/#NamaKamu</code>
    </div>
    <p style="margin-top:10px;font-size:.74rem;color:var(--t3);font-family:var(--mono)">
      URL saat ini: <span id="current-url" style="color:var(--blue)"></span>
    </p>
  </div>

  <!-- Flow diagram -->
  <div class="box">
    <div class="box-t">DOM XSS Flow</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
      <?php
      $steps = [
        ['Source','location.hash','Input yang dibaca JS','var(--blue)'],
        ['Processing','decodeURIComponent()','Payload di-decode','var(--orange)'],
        ['Sink','innerHTML','HTML ditulis ke DOM','var(--red)'],
        ['Execute','Script runs','XSS berhasil dieksekusi','var(--green)'],
      ];
      foreach ($steps as $s): ?>
      <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px;text-align:center">
        <div style="font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:6px"><?= $s[0] ?></div>
        <div style="font-size:.82rem;font-weight:700;color:<?= $s[3] ?>;font-family:var(--mono);margin-bottom:4px"><?= $s[1] ?></div>
        <div style="font-size:.73rem;color:var(--t3)"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Cara kerja fragment</summary>
      <div class="hint-body">
        Fragment (<code class="ic">#</code>) di URL tidak dikirim ke server. Coba buka:<br>
        <code class="ic">/basic-3/#Alice</code> &mdash; sapaan akan berubah menjadi "Halo, Alice"<br>
        Ini membuktikan JavaScript membaca nilai hash secara langsung.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; Inject HTML via hash</summary>
      <div class="hint-body">
        Karena nilai hash dimasukkan ke <code class="ic">innerHTML</code>, HTML tag akan dirender:<br>
        <code class="ic">/basic-3/#&lt;b&gt;Bold Text&lt;/b&gt;</code><br>
        Jika teks tampil bold, sink <code class="ic">innerHTML</code> mengeksekusi HTML kamu.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; XSS payload via hash</summary>
      <div class="hint-body">
        Gunakan <code class="ic">img</code> tag dengan event handler:<br>
        <code class="ic">/basic-3/#&lt;img src=x onerror=alert(document.domain)&gt;</code><br>
        Tag <code class="ic">&lt;script&gt;</code> biasanya tidak berfungsi di dalam <code class="ic">innerHTML</code> — gunakan event handler sebagai gantinya.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; Kenapa server-side filter tidak efektif?</summary>
      <div class="hint-body">
        Buka Network tab di browser DevTools. Perhatikan bahwa fragment <code class="ic">#payload</code>
        tidak muncul di request yang dikirim ke server. Server tidak pernah melihat payload ini,
        sehingga WAF dan server-side sanitasi tidak bisa mencegah DOM XSS.
      </div>
    </details>
  </div>

</div>

<script>
// VULNERABLE: innerHTML digunakan tanpa sanitasi
document.getElementById('current-url').textContent = window.location.href;

function renderGreeting() {
    var hash = decodeURIComponent(location.hash.substring(1));
    if (hash) {
        // SINK: innerHTML — vulnerable
        document.getElementById('greeting').innerHTML = 'Halo, ' + hash + '!';
    }
}

renderGreeting();
window.addEventListener('hashchange', renderGreeting);
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
