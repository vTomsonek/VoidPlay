<?php
require __DIR__ . '/auth.php';

$key      = current_key($ACCESS_KEYS);
$base     = strtok($_SERVER['REQUEST_URI'], '?');
$submitted = isset($_GET['key']);

// Wylogowanie
if (isset($_GET['logout'])) {
    setcookie('player_key', '', time() - 3600, '/');
    header('Location: ' . $base);
    exit;
}

// Ustal widok: gate (idle/error) | success | library
if ($submitted && $key !== null) {
    // poprawny klucz – zapamiętaj i pokaż ekran "Otwieram portal…"
    setcookie('player_key', $key, [
        'expires'  => time() + 60 * 60 * 24 * 30,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $view = 'success';
} elseif ($key !== null) {
    $view = 'library';
} else {
    $view = 'gate';
}
$isError = ($submitted && $key === null); // podano klucz, ale błędny

// znak Orbita (logo) – akcent #a855f7
function orbit_mark(int $size): string {
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 100 100" class="vp-mark" aria-hidden="true">
  <defs>
    <linearGradient id="vpog" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#ffffff"/><stop offset="100%" stop-color="#a855f7"/>
    </linearGradient>
    <filter id="vpogl" x="-60%" y="-60%" width="220%" height="220%">
      <feGaussianBlur stdDeviation="2.4" result="b"/>
      <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
    </filter>
  </defs>
  <ellipse cx="50" cy="50" rx="42" ry="42" fill="none" stroke="#a855f7" stroke-width="1" opacity=".3" transform="rotate(-24 50 50)"/>
  <ellipse cx="50" cy="50" rx="42" ry="16" fill="none" stroke="#a855f7" stroke-width="1.2" opacity=".55" transform="rotate(-24 50 50)"/>
  <path d="M40 34 L40 66 L70 50 Z" fill="url(#vpog)" filter="url(#vpogl)"/>
  <g class="vp-spin" style="transform-origin:50px 50px"><g transform="rotate(-24 50 50)">
    <circle cx="92" cy="50" r="3.4" fill="#fff" filter="url(#vpogl)"/>
  </g></g>
</svg>';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= $view === 'library' ? 'VoidPlay — Biblioteka' : 'VoidPlay — Klucz dostępu' ?></title>
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<meta name="theme-color" content="#050609">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
<?php if ($view === 'library'): ?>
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css">
<?php endif; ?>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  :root{
    --accent:#a855f7;
    --ink:#f4f5fb;
    --muted:#8a8fa3;
    --bg:#050609;
  }
  html,body{height:100%}
  body{
    background:var(--bg);color:var(--ink);
    font-family:'Space Grotesk',system-ui,sans-serif;
    -webkit-font-smoothing:antialiased;
  }
  body.view-gate,body.view-success{overflow:hidden}
  a{color:inherit}

  /* tła kosmiczne */
  .vp-stars{position:fixed;inset:0;width:100%;height:100%;z-index:0}
  .vp-voidglow{
    position:fixed;z-index:1;width:70vmax;height:70vmax;
    left:50%;top:50%;transform:translate(-50%,-54%);
    background:
      radial-gradient(circle at 50% 50%, color-mix(in oklab, var(--accent) 22%, transparent) 0%, transparent 38%),
      radial-gradient(circle at 50% 50%, #000 0%, #000 14%, transparent 30%);
    filter:blur(8px);pointer-events:none;
    animation:vp-breathe 9s ease-in-out infinite;
  }
  @keyframes vp-breathe{0%,100%{opacity:.75}50%{opacity:1}}
  .vp-vignette{
    position:fixed;inset:0;z-index:1;pointer-events:none;
    background:radial-gradient(120% 90% at 50% 42%, transparent 40%, rgba(0,0,0,.65) 100%);
  }

  /* gate layout */
  .vp-root{position:relative;z-index:2;min-height:100vh;width:100%;display:grid;place-items:center;isolation:isolate;padding:20px}

  /* karta */
  .vp-card{
    position:relative;z-index:2;width:min(440px,90vw);
    padding:44px 42px 36px;border-radius:22px;
    background:linear-gradient(180deg, rgba(20,22,32,.72), rgba(10,11,17,.78));
    border:1px solid rgba(255,255,255,.09);
    box-shadow:0 1px 0 rgba(255,255,255,.06) inset,0 40px 90px -30px rgba(0,0,0,.9),0 0 0 1px rgba(0,0,0,.4);
    backdrop-filter:blur(18px) saturate(120%);-webkit-backdrop-filter:blur(18px) saturate(120%);
    opacity:1;transition:transform .5s cubic-bezier(.4,0,.2,1),opacity .5s;
  }
  @media (prefers-reduced-motion:no-preference){
    .vp-card{animation:vp-rise .7s cubic-bezier(.2,.7,.2,1) backwards}
  }
  .vp-card.is-leaving{transform:translateY(-6px) scale(.98)}
  @keyframes vp-rise{from{transform:translateY(22px) scale(.97)}to{transform:none}}

  /* logo */
  .vp-lockup{display:flex;align-items:center;gap:14px;margin-bottom:30px}
  .vp-mark{display:block;filter:drop-shadow(0 6px 18px color-mix(in oklab, var(--accent) 35%, transparent))}
  .vp-wordmark{font-family:'Sora',sans-serif;font-weight:600;font-size:22px;letter-spacing:.14em;color:rgba(255,255,255,.78)}
  .vp-wordmark b{color:#fff;font-weight:800}

  .vp-title{font-family:'Sora',sans-serif;font-weight:800;font-size:27px;line-height:1.12;letter-spacing:-.01em;margin-bottom:9px;text-wrap:balance}
  .vp-sub{color:var(--muted);font-size:14.5px;line-height:1.55;margin-bottom:24px}

  /* form */
  .vp-form{display:flex;flex-direction:column;gap:12px}
  .vp-field{position:relative;display:flex;align-items:center;background:rgba(8,9,14,.85);border:1px solid rgba(255,255,255,.12);border-radius:13px;transition:border-color .2s,box-shadow .2s,background .2s}
  .vp-field:focus-within{border-color:color-mix(in oklab, var(--accent) 80%, white);box-shadow:0 0 0 4px color-mix(in oklab, var(--accent) 22%, transparent);background:rgba(12,13,20,.95)}
  .vp-field.is-error{border-color:#ff6b6b;box-shadow:0 0 0 4px rgba(255,107,107,.16);animation:vp-shake .42s cubic-bezier(.36,.07,.19,.97)}
  @keyframes vp-shake{10%,90%{transform:translateX(-1px)}20%,80%{transform:translateX(2px)}30%,50%,70%{transform:translateX(-4px)}40%,60%{transform:translateX(4px)}}
  .vp-field-glyph{display:flex;padding-left:15px;color:var(--muted)}
  .vp-field:focus-within .vp-field-glyph{color:var(--accent)}
  .vp-input{flex:1;background:transparent;border:0;outline:none;color:var(--ink);font-family:inherit;font-size:15.5px;letter-spacing:.02em;padding:15px 12px}
  .vp-input::placeholder{color:#5b6072}
  .vp-eye{display:flex;align-items:center;justify-content:center;background:transparent;border:0;cursor:pointer;color:var(--muted);padding:0 14px;height:100%;transition:color .15s}
  .vp-eye:hover{color:var(--ink)}

  .vp-error-msg{font-size:12.5px;color:#ff8585;height:0;overflow:hidden;opacity:0;transition:opacity .2s,height .2s;padding-left:4px}
  .vp-error-msg.show{height:18px;opacity:1}

  /* button (light variant) */
  .vp-btn{position:relative;margin-top:2px;display:flex;align-items:center;justify-content:center;gap:9px;font-family:inherit;font-weight:700;font-size:15.5px;letter-spacing:.01em;border:0;border-radius:13px;padding:15px 20px;cursor:pointer;overflow:hidden;transition:transform .14s,box-shadow .25s,filter .2s}
  .vp-btn--light{color:#0b0c12;background:linear-gradient(180deg,#ffffff,#e9eaf2);box-shadow:0 10px 24px -10px rgba(0,0,0,.7)}
  .vp-btn:hover{transform:translateY(-1px);filter:brightness(1.05)}
  .vp-btn:active{transform:translateY(0) scale(.99)}
  .vp-btn-arrow{transition:transform .2s;opacity:.85}
  .vp-btn:hover .vp-btn-arrow{transform:translateX(3px)}
  .vp-btn.is-loading{pointer-events:none}
  .vp-btn.is-loading .vp-btn-label,.vp-btn.is-loading .vp-btn-arrow{opacity:0}
  .vp-spinner{position:absolute;width:19px;height:19px;border-radius:50%;border:2.4px solid rgba(8,7,13,.35);border-top-color:#08070d;opacity:0;animation:vp-spin .7s linear infinite}
  .vp-btn.is-loading .vp-spinner{opacity:1}
  @keyframes vp-spin{to{transform:rotate(360deg)}}

  .vp-foot{margin-top:22px;color:#565b6c;font-size:12.5px;line-height:1.6}

  /* success */
  .vp-success{display:flex;flex-direction:column;align-items:flex-start;padding:6px 0 10px}
  .vp-success-ring{width:46px;height:46px;border-radius:50%;margin-bottom:20px;border:3px solid color-mix(in oklab, var(--accent) 30%, transparent);border-top-color:var(--accent);animation:vp-spin .8s linear infinite}

  /* logo anim */
  .vp-spin{animation:vp-rot 6s linear infinite}
  @keyframes vp-rot{to{transform:rotate(360deg)}}
  @media (prefers-reduced-motion:reduce){.vp-voidglow,.vp-spin{animation:none}}

  @media (max-width:480px){.vp-card{padding:34px 26px 30px}.vp-title{font-size:24px}}

  /* ---------- Biblioteka ---------- */
  header.vp-header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid rgba(255,255,255,.08);position:sticky;top:0;background:rgba(5,6,9,.7);backdrop-filter:blur(10px);z-index:5}
  header.vp-header .hb{display:flex;align-items:center;gap:11px}
  .logout{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:var(--muted);text-decoration:none;padding:9px 15px;border:1px solid rgba(255,255,255,.12);border-radius:11px;background:rgba(20,22,32,.6);transition:color .15s,border-color .15s,background .15s,transform .14s}
  .logout svg{width:16px;height:16px}
  .logout:hover{color:var(--ink);border-color:color-mix(in oklab, var(--accent) 60%, white);background:rgba(12,13,20,.9)}
  .logout:active{transform:scale(.97)}
  .wrap{position:relative;z-index:2;max-width:1100px;margin:0 auto;padding:24px}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
  .card{background:rgba(20,22,32,.72);border:1px solid rgba(255,255,255,.09);border-radius:14px;overflow:hidden;cursor:pointer;transition:transform .12s,border-color .12s;backdrop-filter:blur(6px)}
  .card:hover{transform:translateY(-3px);border-color:color-mix(in oklab, var(--accent) 70%, white)}
  .thumb{position:relative;aspect-ratio:16/9;background:#000;display:flex;align-items:center;justify-content:center}
  .thumb video{width:100%;height:100%;object-fit:cover}
  .play{position:absolute;width:52px;height:52px;border-radius:50%;background:color-mix(in oklab, var(--accent) 92%, black);display:flex;align-items:center;justify-content:center}
  .play svg{width:22px;height:22px;fill:#fff;margin-left:3px}
  .meta{padding:12px 14px}
  .meta .t{font-size:14px;font-weight:600;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
  .meta .s{font-size:12px;color:var(--muted);margin-top:6px;display:flex;align-items:center;flex-wrap:wrap;gap:6px 8px}
  .meta .s .sep{opacity:.5}
  .meta .s .date,.views{display:inline-flex;align-items:center;gap:5px}
  .meta .s .date svg,.views svg{width:14px;height:14px;opacity:.85}
  .empty{color:var(--muted);text-align:center;padding:60px 0}
  .modal{position:fixed;inset:0;background:rgba(0,0,0,.9);display:none;align-items:center;justify-content:center;z-index:50;padding:24px}
  .modal.open{display:flex}
  .modal-inner{width:min(1000px,100%)}
  .modal-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .modal-top h3{margin:0;font-size:16px;font-weight:600}
  .close{background:none;border:0;color:var(--muted);font-size:26px;cursor:pointer;line-height:1}
  .close:hover{color:#fff}
</style>
</head>
<body class="view-<?= $view ?>">

<canvas id="space" class="vp-stars"></canvas>

<?php if ($view === 'gate' || $view === 'success'): ?>
  <div class="vp-voidglow" aria-hidden="true"></div>
  <div class="vp-vignette" aria-hidden="true"></div>

  <div class="vp-root">
    <main class="vp-card <?= $view === 'success' ? 'is-leaving' : '' ?>">
      <div class="vp-lockup">
        <?= orbit_mark(56) ?>
        <span class="vp-wordmark"><b>VOID</b>PLAY</span>
      </div>

      <?php if ($view === 'success'): ?>
        <div class="vp-success">
          <div class="vp-success-ring"></div>
          <h1 class="vp-title">Otwieram portal…</h1>
          <p class="vp-sub">Klucz zaakceptowany. Przenoszę Cię do biblioteki.</p>
        </div>
        <script>setTimeout(function(){location.href=<?= json_encode($base) ?>;},950);</script>
        <noscript><meta http-equiv="refresh" content="1;url=<?= htmlspecialchars($base) ?>"></noscript>
      <?php else: ?>
        <h1 class="vp-title">Wpisz klucz dostępu</h1>
        <p class="vp-sub">Ta platforma jest prywatna. Podaj klucz, aby zobaczyć filmy.</p>

        <form class="vp-form" method="get" novalidate>
          <div class="vp-field <?= $isError ? 'is-error' : '' ?>" id="vpField">
            <span class="vp-field-glyph" aria-hidden="true">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M7 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M11 10h10v4M17 10v4"/>
              </svg>
            </span>
            <input id="vpInput" type="password" name="key" class="vp-input" placeholder="Klucz dostępu"
                   value="<?= htmlspecialchars($_GET['key'] ?? '') ?>" autofocus autocomplete="off" spellcheck="false">
            <button type="button" class="vp-eye" id="vpEye" aria-label="Pokaż klucz">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-8 10-8 10 8 10 8-3 8-10 8-10-8-10-8Z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>

          <div class="vp-error-msg <?= $isError ? 'show' : '' ?>" id="vpErr">
            Klucz jest zbyt krótki lub nieprawidłowy.
          </div>

          <button type="submit" class="vp-btn vp-btn--light" id="vpBtn">
            <span class="vp-btn-label">Wejdź</span>
            <span class="vp-spinner" aria-hidden="true"></span>
            <svg class="vp-btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </button>
        </form>

        <p class="vp-foot">
          Dostęp tylko dla osób posiadających klucz.<br>
          Nie udostępniaj swojego linku innym.
        </p>
      <?php endif; ?>
    </main>
  </div>

<?php else: /* ===== library ===== */ ?>
  <?php $videos = list_videos($MEDIA_DIR, $ALLOWED_EXT); $views = read_views(); ?>
  <header class="vp-header">
    <div class="hb"><?= orbit_mark(28) ?><span class="vp-wordmark" style="font-size:17px"><b>VOID</b>PLAY</span></div>
    <a class="logout" href="?logout=1">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
      Wyloguj
    </a>
  </header>

  <div class="wrap">
    <?php if (empty($videos)): ?>
      <div class="empty">Brak filmów. Wrzuć pliki .mp4 do folderu <b>media</b>.</div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($videos as $v):
          $src = 'stream.php?file=' . rawurlencode($v['file']) . '&key=' . rawurlencode($key);
          $vc  = (int) ($views[$v['file']] ?? 0);
        ?>
          <div class="card" data-src="<?= htmlspecialchars($src) ?>" data-title="<?= htmlspecialchars($v['name']) ?>"
               data-file="<?= htmlspecialchars($v['file']) ?>">
            <div class="thumb">
              <video src="<?= htmlspecialchars($src) ?>#t=2" preload="metadata" muted></video>
              <div class="play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
            </div>
            <div class="meta">
              <div class="t"><?= htmlspecialchars($v['name']) ?></div>
              <div class="s">
                <span class="date" title="Data dodania">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                  <?= human_date((int) $v['mtime']) ?>
                </span>
                <span class="sep">·</span>
                <span><?= human_size($v['size']) ?></span>
                <span class="sep">·</span>
                <span class="views" title="Liczba odtworzeń">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-8 10-8 10 8 10 8-3 8-10 8-10-8-10-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <span class="vc" data-file="<?= htmlspecialchars($v['file']) ?>"><?= fmt_count($vc) ?></span>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="modal" id="modal">
    <div class="modal-inner">
      <div class="modal-top">
        <h3 id="modalTitle"></h3>
        <button class="close" id="closeBtn">&times;</button>
      </div>
      <video id="player" playsinline controls></video>
    </div>
  </div>

  <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
  <script>
    const player = new Plyr('#player', {settings:['quality','speed'],speed:{selected:1,options:[0.5,0.75,1,1.25,1.5,2]}});
    const modal=document.getElementById('modal'),titleEl=document.getElementById('modalTitle'),videoEl=document.getElementById('player');
    const baseTitle=document.title;
    function registerView(card){
      const file=card.dataset.file; if(!file) return;
      fetch('view.php?file='+encodeURIComponent(file)+'&key='+encodeURIComponent(<?= json_encode($key) ?>))
        .then(r=>r.ok?r.json():null)
        .then(d=>{ if(d&&typeof d.count==='number'){ const el=card.querySelector('.vc'); if(el) el.textContent=new Intl.NumberFormat('pl-PL').format(d.count); } })
        .catch(()=>{});
    }
    document.querySelectorAll('.card').forEach(card=>card.addEventListener('click',()=>{videoEl.src=card.dataset.src;titleEl.textContent=card.dataset.title;document.title=card.dataset.title+' — VoidPlay';modal.classList.add('open');player.play();registerView(card);}));
    function closeModal(){player.pause();modal.classList.remove('open');videoEl.src='';document.title=baseTitle;}
    document.getElementById('closeBtn').addEventListener('click',closeModal);
    modal.addEventListener('click',e=>{if(e.target===modal)closeModal();});
    document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
  </script>
<?php endif; ?>

<!-- Starfield -->
<script>
(function(){
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const motion = !reduce;
  const accent = (getComputedStyle(document.documentElement).getPropertyValue('--accent') || '#a855f7').trim();
  const canvas = document.getElementById('space');
  if(!canvas) return;
  const ctx = canvas.getContext('2d');
  let w,h,dpr,stars=[],t=0,raf;
  function resize(){
    dpr = Math.min(window.devicePixelRatio||1,2);
    w = canvas.clientWidth; h = canvas.clientHeight;
    canvas.width = w*dpr; canvas.height = h*dpr;
    ctx.setTransform(dpr,0,0,dpr,0,0);
    const count = Math.round((w*h)/5200);
    stars = Array.from({length:count},()=>({
      x:Math.random()*w, y:Math.random()*h,
      r:Math.random()*1.3+0.2,
      base:Math.random()*0.5+0.25,
      tw:Math.random()*0.5+0.2,
      ph:Math.random()*Math.PI*2,
      drift:Math.random()*0.04+0.01
    }));
  }
  function draw(){
    ctx.clearRect(0,0,w,h);
    for(const s of stars){
      const a = s.base + Math.sin(t*s.tw + s.ph)*0.22;
      ctx.globalAlpha = Math.max(0,Math.min(1,a));
      ctx.fillStyle = s.r>1.05 ? accent : '#ffffff';
      ctx.beginPath(); ctx.arc(s.x,s.y,s.r,0,Math.PI*2); ctx.fill();
      if(motion){ s.y += s.drift; if(s.y>h+2){ s.y=-2; s.x=Math.random()*w; } }
    }
    ctx.globalAlpha = 1; t += 0.016;
    raf = requestAnimationFrame(draw);
  }
  resize(); draw();
  addEventListener('resize', resize);
})();
</script>

<?php if ($view === 'gate'): ?>
<script>
(function(){
  const form=document.querySelector('.vp-form'),field=document.getElementById('vpField'),
        input=document.getElementById('vpInput'),err=document.getElementById('vpErr'),
        btn=document.getElementById('vpBtn'),eye=document.getElementById('vpEye');

  const EYE='<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-8 10-8 10 8 10 8-3 8-10 8-10-8-10-8Z"/><circle cx="12" cy="12" r="3"/></svg>';
  const EYEOFF='<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19M6.6 6.6A18.5 18.5 0 0 0 2 12s3 8 10 8a9.1 9.1 0 0 0 5.4-1.6"/><path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/><line x1="2" y1="2" x2="22" y2="22"/></svg>';

  eye.addEventListener('click',function(){
    const toText = input.type==='password';
    input.type = toText?'text':'password';
    eye.setAttribute('aria-label', toText?'Ukryj klucz':'Pokaż klucz');
    eye.innerHTML = toText?EYEOFF:EYE;
    input.focus();
  });

  input.addEventListener('input',function(){
    if(field.classList.contains('is-error')){ field.classList.remove('is-error'); err.classList.remove('show'); }
  });

  form.addEventListener('submit',function(e){
    if(input.value.trim().length < 4){
      e.preventDefault();
      field.classList.remove('is-error'); void field.offsetWidth; field.classList.add('is-error');
      err.classList.add('show'); input.focus();
      return;
    }
    btn.classList.add('is-loading'); // walidacja po stronie serwera po nawigacji
  });
})();
</script>
<?php endif; ?>

</body>
</html>
