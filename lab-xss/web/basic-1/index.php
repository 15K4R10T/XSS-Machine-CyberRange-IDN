<?php
$active = 'basic-1';
$query  = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 1: Reflected XSS — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 1: Reflected XSS</span></div>
    <h1>Reflected XSS <span class="tag g">BASIC 1</span></h1>
    <p class="phdr-desc">Input dari parameter URL langsung ditampilkan ke halaman tanpa HTML encoding. Server merefleksikan apapun yang dikirim pengguna ke dalam respons HTML.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Konfirmasi kerentanan dengan menyisipkan karakter <code class="ic">&lt;</code> atau <code class="ic">"</code> pada field pencarian</span></li>
      <li><div class="obj-n">2</div><span>Eksekusi <code class="ic">alert(1)</code> sebagai proof-of-concept XSS dasar</span></li>
      <li><div class="obj-n">3</div><span>Baca nilai <code class="ic">document.cookie</code> dan tampilkan via alert box</span></li>
      <li><div class="obj-n">4</div><span>Buat URL berbahaya yang jika diklik akan mengeksekusi script pada browser korban</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <p class="prose">
      Aplikasi ini mengambil nilai parameter <code class="ic">?q=</code> dari URL dan langsung menyisipkannya ke dalam HTML respons menggunakan PHP:
    </p>
    <div class="qbox" style="margin-top:12px"><div class="ql">Vulnerable PHP Code</div><span class="kw">echo</span> <span class="str">"Hasil pencarian untuk: "</span> . <span class="val">$_GET</span>[<span class="str">'q'</span>];</div>
    <p class="prose">Tidak ada <code class="ic">htmlspecialchars()</code> atau encoding lainnya — input langsung masuk ke halaman.</p>
  </div>

  <!-- Search Form -->
  <div class="box">
    <div class="box-t">Product Search</div>
    <form method="GET" action="/basic-1/">
      <div class="frow">
        <div class="fg">
          <label class="fl">Kata Kunci Pencarian</label>
          <input class="fi" type="text" name="q"
            value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="cari produk... atau masukkan XSS payload">
        </div>
        <button type="submit" class="btn btn-r">Cari</button>
        <?php if($query): ?><a href="/basic-1/" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($query !== ''): ?>
  <div class="box">
    <div class="box-t">Search Result</div>
    <p style="font-size:.86rem;color:var(--t2);margin-bottom:8px">Menampilkan hasil untuk:</p>
    <!-- VULNERABLE: output tidak di-escape -->
    <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:12px 16px;font-size:.88rem;color:var(--t1)">
      <?= $query ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Konfirmasi kerentanan</summary>
      <div class="hint-body">
        Masukkan karakter HTML: <code class="ic">&lt;b&gt;test&lt;/b&gt;</code><br>
        Jika teks muncul sebagai <strong>bold</strong>, berarti HTML dirender &mdash; input tidak di-escape.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; Basic alert payload</summary>
      <div class="hint-body">
        Payload paling dasar untuk membuktikan XSS:<br>
        <code class="ic">&lt;script&gt;alert(1)&lt;/script&gt;</code><br>
        Atau gunakan event handler tanpa tag script:<br>
        <code class="ic">&lt;img src=x onerror=alert(1)&gt;</code>
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; Baca cookie</summary>
      <div class="hint-body">
        Ganti isi alert dengan <code class="ic">document.cookie</code>:<br>
        <code class="ic">&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code><br>
        Atau tampilkan menggunakan <code class="ic">prompt</code> agar bisa di-copy:<br>
        <code class="ic">&lt;script&gt;prompt(0,document.cookie)&lt;/script&gt;</code>
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; Buat URL berbahaya</summary>
      <div class="hint-body">
        URL yang mengandung payload XSS:<br>
        <code class="ic">http://[IP]:8082/basic-1/?q=&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code><br>
        URL ini dapat dikirim ke korban melalui email, pesan, atau media sosial.
      </div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
