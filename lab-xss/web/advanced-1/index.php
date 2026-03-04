<?php
$active  = 'advanced-1';
$input   = $_GET['q'] ?? '';
$level   = max(1, min(3, (int)($_GET['level'] ?? 1)));
$filtered = $input;
$blocked  = false;

function filterInput($s, $level) {
    if ($level >= 1) {
        // Level 1: blokir <script> saja
        $s = preg_replace('/<script[\s\S]*?>[\s\S]*?<\/script>/i', '', $s);
    }
    if ($level >= 2) {
        // Level 2: blokir tag script + kata kunci on*
        $s = preg_replace('/<script[\s\S]*?>/i', '', $s);
        $s = preg_replace('/\bon\w+\s*=/i', '', $s);
    }
    if ($level >= 3) {
        // Level 3: strip semua tag HTML
        $s = strip_tags($s);
    }
    return $s;
}

if ($input !== '') {
    $filtered = filterInput($input, $level);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Advanced 1: Filter Bypass — IDN Lab</title>
<?php include '../includes/shared_css.php'; ?>
</head>
<body>
<?php include '../includes/nav.php'; ?>

<div class="phdr">
  <div class="phdr-in">
    <div class="bc"><a href="/">Dashboard</a><span class="bc-sep">/</span><span>Advanced 1: Filter Bypass</span></div>
    <h1>XSS + Filter Bypass <span class="tag o">ADVANCED 1</span></h1>
    <p class="phdr-desc">Server memfilter tag dan keyword berbahaya menggunakan blacklist. Pelajari teknik encoding, obfuscation, dan penggunaan alternatif untuk melewati filter.</p>
  </div>
</div>

<div class="wrap">

  <div class="box">
    <div class="box-t">Objectives</div>
    <ul class="obj-list">
      <li><div class="obj-n">1</div><span>Bypass filter Level 1 yang memblokir tag <code class="ic">&lt;script&gt;</code></span></li>
      <li><div class="obj-n">2</div><span>Bypass filter Level 2 yang juga memblokir atribut <code class="ic">on*</code> seperti <code class="ic">onerror</code></span></li>
      <li><div class="obj-n">3</div><span>Bypass filter Level 3 yang menggunakan <code class="ic">strip_tags()</code></span></li>
      <li><div class="obj-n">4</div><span>Pahami kelemahan pendekatan blacklist dibanding whitelist</span></li>
    </ul>
  </div>

  <!-- Level Tabs -->
  <div class="box">
    <div class="box-t">Filter Level</div>
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
      <a href="/advanced-1/?level=1<?= $input?'&q='.urlencode($input):'' ?>"
         style="padding:8px 18px;border-radius:var(--r);font-size:.8rem;font-weight:600;border:1px solid;text-decoration:none;transition:all .15s;<?= $level==1?'background:var(--rbg);border-color:var(--rbdr);color:var(--red)':'background:var(--el);border-color:var(--bd);color:var(--t2)' ?>">
        Level 1 &mdash; Block &lt;script&gt;
      </a>
      <a href="/advanced-1/?level=2<?= $input?'&q='.urlencode($input):'' ?>"
         style="padding:8px 18px;border-radius:var(--r);font-size:.8rem;font-weight:600;border:1px solid;text-decoration:none;transition:all .15s;<?= $level==2?'background:var(--rbg);border-color:var(--rbdr);color:var(--red)':'background:var(--el);border-color:var(--bd);color:var(--t2)' ?>">
        Level 2 &mdash; Block on* events
      </a>
      <a href="/advanced-1/?level=3<?= $input?'&q='.urlencode($input):'' ?>"
         style="padding:8px 18px;border-radius:var(--r);font-size:.8rem;font-weight:600;border:1px solid;text-decoration:none;transition:all .15s;<?= $level==3?'background:var(--rbg);border-color:var(--rbdr);color:var(--red)':'background:var(--el);border-color:var(--bd);color:var(--t2)' ?>">
        Level 3 &mdash; strip_tags()
      </a>
    </div>
    <?php if($level==1): ?>
    <div class="alert a-info">Filter aktif: Tag <code class="ic">&lt;script&gt;...&lt;/script&gt;</code> dihapus dari input. Tag HTML lainnya masih diizinkan.</div>
    <?php elseif($level==2): ?>
    <div class="alert a-warn">Filter aktif: Tag <code class="ic">&lt;script&gt;</code> dan semua atribut event <code class="ic">on*</code> dihapus dari input.</div>
    <?php else: ?>
    <div class="alert a-err">Filter aktif: Fungsi PHP <code class="ic">strip_tags()</code> menghapus semua tag HTML. Namun ada kasus tepi yang bisa dieksploitasi.</div>
    <?php endif; ?>
  </div>

  <div class="box">
    <div class="box-t">Search Input</div>
    <form method="GET" action="/advanced-1/">
      <input type="hidden" name="level" value="<?= $level ?>">
      <div class="frow">
        <div class="fg">
          <label class="fl">Input</label>
          <input class="fi" type="text" name="q"
            value="<?= htmlspecialchars($input, ENT_QUOTES, 'UTF-8') ?>"
            placeholder="Masukkan XSS payload...">
        </div>
        <button type="submit" class="btn btn-r">Test</button>
        <?php if($input): ?><a href="/advanced-1/?level=<?= $level ?>" class="btn btn-g">Reset</a><?php endif; ?>
      </div>
    </form>
  </div>

  <?php if ($input !== ''): ?>
  <div class="box">
    <div class="box-t">Filter Process</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:6px">Input Asli</div>
        <div class="qbox" style="margin-bottom:0;color:var(--red)"><?= htmlspecialchars($input, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div>
        <div style="font-size:.64rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);font-family:var(--mono);margin-bottom:6px">Setelah Filter L<?= $level ?></div>
        <div class="qbox" style="margin-bottom:0;color:var(--green)"><?= htmlspecialchars($filtered, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
  </div>

  <div class="box">
    <div class="box-t">Rendered Output <span style="font-size:.68rem;font-weight:400;color:var(--t3)">(setelah filter)</span></div>
    <!-- Output SETELAH filter — masih vulnerable jika bypass berhasil -->
    <div style="background:var(--el);border:1px solid var(--bd);border-radius:var(--r);padding:14px 16px;min-height:48px"><?= $filtered ?></div>
  </div>
  <?php endif; ?>

  <div class="box">
    <div class="box-t">Bypass Techniques</div>
    <div class="qbox"><div class="ql">Teknik yang dapat dicoba</div><span class="cm">-- Level 1: &lt;script&gt; diblokir, tapi tag lain tidak</span>
<span class="str">&lt;img src=x onerror=alert(1)&gt;</span>
<span class="str">&lt;svg onload=alert(1)&gt;</span>
<span class="str">&lt;body onload=alert(1)&gt;</span>

<span class="cm">-- Level 2: on* diblokir, coba encoding</span>
<span class="str">&lt;img src=x oNeRrOr=alert(1)&gt;</span>       <span class="cm">-- case variation</span>
<span class="str">&lt;a href="javascript:alert(1)"&gt;klik&lt;/a&gt;</span> <span class="cm">-- javascript: URI</span>

<span class="cm">-- Level 3: strip_tags aktif, coba karakter khusus</span>
<span class="str">&lt;&lt;script&gt;alert(1)&lt;/script&gt;</span>        <span class="cm">-- double bracket</span>
<span class="str">&lt;scr&lt;script&gt;ipt&gt;alert(1)&lt;/scr&lt;/script&gt;ipt&gt;</span></div>
  </div>

  <div class="box">
    <div class="box-t">Hints</div>

    <details class="hint">
      <summary>Hint 1 &mdash; Level 1: Alternatif tag script</summary>
      <div class="hint-body">
        Filter hanya memblokir <code class="ic">&lt;script&gt;</code>. Banyak tag HTML lain yang mendukung event handler JavaScript:<br>
        <code class="ic">&lt;img src=x onerror=alert(1)&gt;</code><br>
        <code class="ic">&lt;svg onload=alert(1)&gt;</code>
      </div>
    </details>

    <details class="hint">
      <summary>Hint 2 &mdash; Level 2: Case variation</summary>
      <div class="hint-body">
        Regex filter sering case-sensitive atau tidak menangani semua variasi:<br>
        <code class="ic">&lt;img src=x OnErRoR=alert(1)&gt;</code><br>
        Atau gunakan <code class="ic">javascript:</code> URI scheme:<br>
        <code class="ic">&lt;a href="javascript:alert(1)"&gt;Klik saya&lt;/a&gt;</code>
      </div>
    </details>

    <details class="hint">
      <summary>Hint 3 &mdash; Level 3: strip_tags edge case</summary>
      <div class="hint-body">
        <code class="ic">strip_tags()</code> memiliki edge case dengan nested atau malformed tags:<br>
        <code class="ic">&lt;scr&lt;script&gt;ipt&gt;alert(1)&lt;/scr&lt;/script&gt;ipt&gt;</code><br>
        Atau coba tag yang tidak lengkap untuk membingungkan parser.
      </div>
    </details>

    <details class="hint">
      <summary>Hint 4 &mdash; Kelemahan blacklist</summary>
      <div class="hint-body">
        Pendekatan blacklist selalu inferior karena browser mendukung ratusan tag dan event handler.
        Solusi yang benar adalah <strong style="color:var(--t1)">whitelist encoding</strong> — gunakan
        <code class="ic">htmlspecialchars()</code> yang mengubah semua karakter khusus menjadi HTML entities,
        bukan mencoba memblokir karakter tertentu.
      </div>
    </details>
  </div>

</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
