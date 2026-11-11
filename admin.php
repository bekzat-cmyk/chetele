<?php
declare(strict_types=1);

/* The admin cabinet is served through PHP so it can send its own security headers:
   nginx hands static .html files out directly, past .htaccess. A fresh nonce per request
   lets only this page's own script run. */
$nonce = base64_encode(random_bytes(18));
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; script-src 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'; object-src 'none'");
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cross-Origin-Opener-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header('X-Robots-Tag: noindex, nofollow');
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Chetele · Админ-кабинет</title>
<!-- With a sign-in kept in this tab, a reload opens the cabinet straight away instead of
     flashing the sign-in form while the saved login is checked. -->
<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">try { if (sessionStorage.getItem('chetele-admin')) document.documentElement.classList.add('restoring'); } catch {}</script>
<link rel="icon" href="assets/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#10161A;--muted:#5E6A71;--soft:#3E4A50;--black:#0B0F12;--line:rgba(16,22,26,.08);
  --glass:rgba(255,255,255,.66);--glass-line:rgba(255,255,255,.9);
  --green:#1F6B4F;--green-bg:rgba(47,155,110,.14);--amber:#8A4B12;--amber-bg:rgba(224,134,43,.16);
  --indigo:#3A4596;--indigo-bg:rgba(78,92,190,.12);--red:#A3342A;--red-bg:rgba(190,60,50,.07);
  --shadow:0 10px 30px rgba(16,22,26,.06);
}
*{box-sizing:border-box}
[hidden]{display:none!important}
html.restoring #login{display:none!important}
html{-webkit-text-size-adjust:100%}
body{margin:0;min-height:100vh;font-family:Inter,-apple-system,system-ui,sans-serif;color:var(--ink);background:linear-gradient(180deg,#4F7F76 0,#6F9C92 160px,#9DBFB5 300px,#D3E3DE 440px,#ECF0EF 600px,#F2F3F4 100%) fixed;background-color:#F2F3F4}
body::before{content:"";position:fixed;inset:0;pointer-events:none;z-index:0;background:radial-gradient(circle at 26% 40%,rgba(70,110,140,.22),transparent 34%),radial-gradient(circle at 92% 0%,rgba(255,255,255,.3),transparent 30%),radial-gradient(circle at 80% 105%,rgba(255,200,110,.18),transparent 36%)}
button,input,select,textarea{font:inherit;color:inherit}
button{cursor:pointer}
a{color:inherit}
svg.i{width:20px;height:20px;flex:none;fill:none;stroke:currentColor;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
:focus-visible{outline:2px solid var(--black);outline-offset:2px}

/* Shared pieces */
.glass{background:var(--glass);border:1px solid var(--glass-line);-webkit-backdrop-filter:blur(24px);backdrop-filter:blur(24px);box-shadow:var(--shadow)}
.card{padding:20px;border-radius:30px;display:flex;flex-direction:column;gap:16px;min-width:0}
.card-head{display:flex;align-items:center;justify-content:space-between;gap:12px}
.card-head h2{margin:0;font-size:18px;font-weight:600;letter-spacing:-.01em}
.circle{flex:none;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.92);border:1px solid rgba(16,22,26,.06);box-shadow:0 2px 8px rgba(16,22,26,.06);display:grid;place-items:center;color:var(--ink)}
.circle svg.i{width:18px;height:18px}
.btn{height:48px;padding:0 20px;border-radius:24px;border:1px solid var(--line);background:rgba(255,255,255,.92);color:var(--ink);font-size:15px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;white-space:nowrap;transition:transform .12s ease,background .2s ease}
.btn:active{transform:scale(.96)}
.btn.black{border:0;background:var(--black);color:#fff}
.btn.small{height:36px;padding:0 14px;font-size:13px;border-radius:18px}
.btn.icon{width:36px;padding:0}
.btn.danger{border-color:rgba(190,60,50,.25);background:var(--red-bg);color:var(--red)}
.btn:disabled{opacity:.55;cursor:default}
.btn-glass{width:48px;height:48px;border-radius:24px;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.22);-webkit-backdrop-filter:blur(20px);backdrop-filter:blur(20px);color:#fff;display:grid;place-items:center;text-decoration:none}
.bar{display:block;height:6px;border-radius:6px;background:rgba(16,22,26,.09);overflow:hidden}
.bar>i{display:block;height:100%;min-width:6px;border-radius:6px;background:var(--ink);transition:width .5s cubic-bezier(.22,1,.36,1)}
.pill{height:28px;padding:0 12px;border-radius:14px;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.pill::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.pill.active{background:var(--green-bg);color:var(--green)}
.pill.published{background:var(--indigo-bg);color:var(--indigo)}
.pill.draft,.pill.finished{background:rgba(16,22,26,.07);color:var(--soft)}
.chip{height:26px;padding:0 10px;border-radius:13px;background:var(--green-bg);color:var(--green);font-size:12px;font-weight:600;display:inline-flex;align-items:center;white-space:nowrap}
.seg{display:inline-flex;flex-wrap:wrap;padding:4px;border-radius:22px;background:rgba(16,22,26,.06);gap:2px}
.seg button{height:34px;padding:0 14px;border:0;border-radius:17px;background:transparent;color:var(--muted);font-size:13px;font-weight:600}
.seg button[aria-pressed=true]{background:#fff;color:var(--ink);box-shadow:0 2px 8px rgba(16,22,26,.08)}
.search{height:42px;width:260px;max-width:100%;padding:0 14px;border-radius:21px;background:rgba(255,255,255,.92);border:1px solid var(--line);display:flex;align-items:center;gap:8px;color:var(--muted)}
.search input{flex:1;min-width:0;border:0;background:transparent;font-size:14px;outline:none;color:var(--ink)}
.select{height:42px;padding:0 36px 0 14px;border-radius:21px;border:1px solid var(--line);background:rgba(255,255,255,.92) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2310161A' stroke-width='1.8' stroke-linecap='round'%3E%3Cpath d='M6 9.5l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 12px center/16px;font-size:14px;font-weight:600;appearance:none;-webkit-appearance:none}
.empty{padding:36px 20px;text-align:center;color:var(--muted);font-size:14px;line-height:1.5}
.error{margin:0;color:var(--red);font-size:13px;min-height:18px}
.muted{color:var(--muted)}

/* Login */
.login{position:relative;z-index:1;min-height:100vh;display:grid;place-items:center;padding:24px}
.login form{width:min(420px,100%);padding:28px;border-radius:32px;display:flex;flex-direction:column;gap:16px}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:44px;height:44px;border-radius:16px;background:var(--black);color:#fff;display:grid;place-items:center}
.brand-mark svg.i{width:24px;height:24px;stroke-width:1.7}
.brand b{display:block;font-size:17px;font-weight:600}
.brand small{font-size:12px;color:var(--muted)}
.login h1{margin:6px 0 0;font-size:30px;font-weight:600;letter-spacing:-.02em}
.login p{margin:0;color:var(--muted);font-size:15px;line-height:1.45}
.field{display:flex;flex-direction:column;gap:8px}
.field>span{font-size:13px;font-weight:600;color:var(--soft)}
.field small{font-size:12px;color:var(--muted)}
.input{height:48px;width:100%;padding:0 16px;border-radius:18px;border:1px solid rgba(16,22,26,.1);background:#fff;font-size:15px;outline:none}
.input:focus{border-color:var(--black);box-shadow:0 0 0 4px rgba(11,15,18,.08)}
textarea.input{height:104px;padding:14px 16px;resize:vertical;line-height:1.45}

/* Shell */
.shell{position:relative;z-index:1;display:flex;min-height:100vh}
.side{position:sticky;top:16px;align-self:flex-start;width:264px;height:calc(100vh - 32px);flex:none;margin:16px 0 16px 16px;padding:22px 14px 16px;border-radius:32px;background:linear-gradient(180deg,rgba(14,28,25,.94),rgba(11,15,18,.96));box-shadow:0 24px 60px rgba(11,15,18,.28);display:flex;flex-direction:column;gap:22px;color:#fff}
.side .brand{padding:0 6px}
.side .brand-mark{background:#fff;color:var(--black)}
.side .brand small{color:rgba(255,255,255,.6)}
.nav{display:flex;flex-direction:column;gap:4px}
.nav a{height:46px;padding:0 12px 0 14px;border-radius:23px;display:flex;align-items:center;gap:12px;text-decoration:none;font-size:15px;font-weight:500;color:rgba(255,255,255,.82);transition:background .2s ease,color .2s ease}
.nav a:hover{background:rgba(255,255,255,.08)}
.nav a[aria-current=page]{background:#fff;color:var(--black);font-weight:600}
.nav em{margin-left:auto;min-width:22px;height:22px;padding:0 7px;border-radius:11px;background:rgba(255,255,255,.14);font-size:12px;font-style:normal;font-weight:600;display:grid;place-items:center}
.nav a[aria-current=page] em{background:rgba(16,22,26,.08)}
.nav em:empty{display:none}
.side-status{margin-top:auto;padding:14px;border-radius:24px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.1);display:flex;flex-direction:column;gap:10px;font-size:13px}
.side-status span{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.75)}
.side-status span::before{content:"";width:8px;height:8px;border-radius:50%;background:#5ED19B;box-shadow:0 0 0 4px rgba(94,209,155,.18)}
.side-status a{color:#fff;text-decoration:none;display:flex;align-items:center;gap:6px}
.side-status a svg.i{width:14px;height:14px}
.me{display:flex;align-items:center;gap:10px;padding:0 6px}
.me-avatar{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.14);display:grid;place-items:center;font-size:15px;font-weight:600;text-transform:uppercase}
.me div{flex:1;min-width:0}
.me b{display:block;font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis}
.me small{font-size:12px;color:rgba(255,255,255,.6)}
.me button{width:38px;height:38px;border:0;border-radius:50%;background:rgba(255,255,255,.1);color:#fff;display:grid;place-items:center}
.me button svg.i{width:18px;height:18px}
.main{flex:1;min-width:0;padding:16px 28px 40px;display:flex;flex-direction:column;gap:20px}
.top{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;padding:18px 4px 0;color:#fff}
.top-copy{display:flex;flex-direction:column;gap:4px;min-width:0}
.top-copy span{font-size:14px;font-weight:500;color:rgba(255,255,255,.78)}
.top h1{margin:0;font-size:34px;font-weight:600;letter-spacing:-.02em;line-height:1.1}
.top p{margin:0;font-size:15px;color:rgba(255,255,255,.85)}
.top-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.top-back{display:flex;align-items:center;gap:14px}
/* The page slides in, and each glass card fades on its own: fading the whole page would
   switch the cards' blur off until the animation ends, which reads as a flash. */
.view{display:flex;flex-direction:column;gap:20px;animation:slide .45s cubic-bezier(.22,1,.36,1) both}
.view .glass,.view .top{animation:fade .35s ease both}
.view.still,.view.still .glass,.view.still .top{animation:none}
@keyframes slide{from{transform:translateY(10px)}}
.view.still{animation:none}
@keyframes rise{from{opacity:0;transform:translateY(10px)}}
.tabbar{display:none}

/* Overview */
.kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.kpi{padding:18px;border-radius:28px;display:flex;flex-direction:column;gap:14px}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.kpi b{display:block;font-size:30px;font-weight:600;letter-spacing:-.02em}
.kpi small{font-size:13px;color:var(--muted)}
.ov-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:16px;align-items:start}
.ov-grid2{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.45fr);gap:16px;align-items:start}
.chart-total{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap}
.chart-total b{font-size:30px;font-weight:600;letter-spacing:-.02em}
.chart{height:200px;display:flex;align-items:flex-end;gap:6px;padding-top:8px;border-bottom:1px solid var(--line)}
.chart span{flex:1;min-width:0;border-radius:6px 6px 3px 3px;background:rgba(16,22,26,.16);transition:height .6s cubic-bezier(.22,1,.36,1)}
.chart span:last-child{background:var(--black)}
.chart span:hover{background:rgba(16,22,26,.4)}
/* In a group report the chart fills the height of the card beside it. */
.ov-grid.stretch{align-items:stretch}
.ov-grid.stretch .chart{flex:1;height:auto;min-height:200px}
.chart.dense{gap:3px}.chart.dense span{border-radius:3px 3px 1px 1px}
.chart.tight{gap:1px}.chart.tight span{border-radius:2px 2px 0 0}
.chart-axis{display:flex;justify-content:space-between;font-size:12px;color:var(--muted)}
.ev-mini{display:flex;align-items:center;gap:12px;text-decoration:none}
.ev-mini-copy{flex:1;min-width:0;display:flex;flex-direction:column;gap:6px}
.ev-mini-copy span{display:flex;justify-content:space-between;gap:8px;font-size:14px}
.ev-mini-copy b{font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ev-mini-copy small{font-size:12px;color:var(--muted)}
.link{font-size:14px;font-weight:500;color:var(--muted);text-decoration:none;display:inline-flex;align-items:center;gap:2px;border:0;background:none;padding:0}
.link svg.i{width:16px;height:16px}
.person{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(16,22,26,.06)}
.person:last-child{border-bottom:0}
.avatar{flex:none;width:36px;height:36px;border-radius:50%;background:#fff;border:1px solid rgba(16,22,26,.06);display:grid;place-items:center;font-size:14px;font-weight:600;text-transform:uppercase}
.person div{flex:1;min-width:0;display:flex;flex-direction:column}
.person b{font-size:14px;font-weight:600}
.person small{font-size:12px;color:var(--muted)}
.todo{display:flex;align-items:center;gap:12px;padding:12px;border-radius:20px;background:rgba(16,22,26,.04)}
.todo.amber{background:rgba(255,214,140,.22)}.todo.amber .circle{color:#C7782A}
.todo.indigo{background:rgba(78,92,190,.08)}.todo.indigo .circle{color:var(--indigo)}
.todo div{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.todo b{font-size:14px;font-weight:600}
.todo small{font-size:12px;color:var(--muted)}
.all-good{display:flex;align-items:center;gap:12px;padding:14px;border-radius:20px;background:var(--green-bg);color:var(--green);font-size:14px;font-weight:500}

/* Tables with a side card */
.split{display:flex;gap:16px;align-items:flex-start}
.split>.card:first-child{flex:1}
.detail{width:360px;flex:none;position:sticky;top:16px}
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.toolbar>span{display:flex;gap:8px;flex-wrap:wrap}
table{width:100%;border-collapse:collapse}
th{padding:0 12px 10px;text-align:left;font-size:12px;font-weight:600;color:var(--muted);letter-spacing:.02em}
td{padding:14px 12px;border-bottom:1px solid rgba(16,22,26,.06);font-size:14px;vertical-align:middle}
tbody tr{cursor:pointer;transition:background .15s ease}
tbody tr:hover{background:rgba(255,255,255,.45)}
tbody tr[aria-selected=true]{background:rgba(255,255,255,.8)}
tbody tr:last-child td{border-bottom:0}
.cell-main{display:flex;align-items:center;gap:12px;min-width:0}
.cell-main div{display:flex;flex-direction:column;gap:2px;min-width:0}
.cell-main b{font-size:15px;font-weight:600}
.cell-main small{font-size:12px;color:var(--muted)}
.prog{display:flex;flex-direction:column;gap:6px;min-width:110px}
.prog b{font-size:13px;font-weight:600}
.right{text-align:right}
.menu-wrap{position:relative;display:inline-block}
.menu{position:absolute;right:0;top:42px;z-index:20;min-width:180px;padding:6px;border-radius:20px;background:#fff;border:1px solid var(--line);box-shadow:0 18px 40px rgba(16,22,26,.16);display:flex;flex-direction:column;gap:2px}
.menu button{height:40px;padding:0 12px;border:0;border-radius:14px;background:transparent;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px;text-align:left}
.menu button:hover{background:rgba(16,22,26,.05)}
.menu button.danger{color:var(--red)}
.menu svg.i{width:17px;height:17px}
.d-title{display:flex;flex-direction:column;gap:4px}
.d-title b{font-size:22px;font-weight:600;letter-spacing:-.02em}
.d-title small{font-size:14px;color:var(--muted)}
.d-dark{padding:16px;border-radius:24px;background:var(--black);color:#fff;display:flex;flex-direction:column;gap:10px}
.d-dark span{display:flex;justify-content:space-between;align-items:baseline;gap:8px}
.d-dark b{font-size:30px;font-weight:600;letter-spacing:-.02em}
.d-dark small{font-size:13px;color:rgba(255,255,255,.72);line-height:1.4}
.d-dark .bar{height:8px;background:rgba(255,255,255,.16)}
.d-dark .bar>i{background:#fff}
.d-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.d-stats.three{grid-template-columns:repeat(3,minmax(0,1fr))}
.d-stats span{padding:12px;border-radius:20px;background:rgba(255,255,255,.82);display:flex;flex-direction:column;min-width:0}
.d-stats b{font-size:20px;font-weight:600;overflow:hidden;text-overflow:ellipsis}
.d-stats small{font-size:12px;color:var(--muted)}
.d-rows span{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid rgba(16,22,26,.06);font-size:14px}
.d-rows span:last-child{border-bottom:0}
.d-rows em{font-style:normal;color:var(--muted)}
.d-actions{display:flex;gap:8px}
.d-actions .btn{flex:1;height:46px;font-size:14px}
.d-close{display:flex;align-items:center;justify-content:space-between}
.detail-empty{display:flex;flex-direction:column;align-items:center;gap:10px;padding:30px 10px;text-align:center;color:var(--muted);font-size:14px;line-height:1.45}

/* Editor */
.editor{display:flex;gap:24px;align-items:flex-start}
.editor-form{flex:1;min-width:0;display:flex;flex-direction:column;gap:16px}
.choice-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.choice{position:relative;padding:16px;border-radius:22px;border:1px solid var(--line);background:rgba(255,255,255,.7);display:flex;flex-direction:column;gap:4px;cursor:pointer;transition:border-color .2s ease,background .2s ease}
.choice input{position:absolute;opacity:0;pointer-events:none}
.choice b{font-size:15px;font-weight:600;display:flex;justify-content:space-between;align-items:center;gap:8px}
.choice b::after{content:"";width:20px;height:20px;flex:none;border-radius:50%;border:2px solid rgba(16,22,26,.2);transition:border .2s ease}
.choice small{font-size:13px;line-height:1.4;color:var(--muted)}
.choice:has(input:checked){border:2px solid var(--black);background:#fff;padding:15px}
.choice:has(input:checked) b::after{border:6px solid var(--black)}
.choice:has(input:focus-visible){outline:2px solid var(--black);outline-offset:2px}
.metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
.units{display:flex;gap:6px;flex-wrap:wrap}
.units button{height:32px;padding:0 12px;border-radius:16px;border:1px solid var(--line);background:rgba(255,255,255,.8);font-size:13px;font-weight:600}
.units button[aria-pressed=true]{background:var(--black);color:#fff;border-color:var(--black)}
.metric{position:relative;padding:14px 8px;border-radius:20px;border:1px solid var(--line);background:rgba(255,255,255,.7);display:flex;flex-direction:column;align-items:center;gap:8px;font-size:13px;font-weight:600;text-align:center;cursor:pointer}
.metric input{position:absolute;opacity:0;pointer-events:none}
.metric i{width:38px;height:38px;border-radius:50%;background:rgba(16,22,26,.06);display:grid;place-items:center}
.metric i svg.i{width:18px;height:18px}
.metric:has(input:checked){border:2px solid var(--black);background:#fff;padding:13px 7px}
.metric:has(input:checked) i{background:var(--black);color:#fff}
.metric:has(input:focus-visible){outline:2px solid var(--black);outline-offset:2px}
.grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.toggle{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid rgba(16,22,26,.06);cursor:pointer}
.toggle:last-child{border-bottom:0}
.toggle div{flex:1;display:flex;flex-direction:column;gap:2px}
.toggle b{font-size:14px;font-weight:600}
.toggle small{font-size:12px;color:var(--muted)}
.toggle input{position:absolute;opacity:0;pointer-events:none}
.switch{width:48px;height:28px;flex:none;border-radius:14px;background:rgba(16,22,26,.14);position:relative;transition:background .2s ease}
.switch::after{content:"";position:absolute;top:3px;left:3px;width:22px;height:22px;border-radius:50%;background:#fff;box-shadow:0 2px 6px rgba(16,22,26,.2);transition:transform .25s cubic-bezier(.22,1,.36,1)}
.toggle input:checked+.switch{background:var(--black)}
.toggle input:checked+.switch::after{transform:translateX(20px)}
.toggle input:focus-visible+.switch{outline:2px solid var(--black);outline-offset:2px}
.options{display:flex;gap:8px;flex-wrap:wrap}
.option{flex:1;min-width:100px;position:relative;height:44px;border-radius:22px;border:1px solid var(--line);background:rgba(255,255,255,.7);display:grid;place-items:center;font-size:14px;font-weight:600;cursor:pointer}
.option input{position:absolute;opacity:0;pointer-events:none}
.option:has(input:checked){border:2px solid var(--black);background:#fff}
.option:has(input:focus-visible){outline:2px solid var(--black);outline-offset:2px}
.hint{padding:12px 14px;border-radius:18px;background:var(--green-bg);color:var(--green);font-size:13px;line-height:1.45;display:flex;gap:10px}
.hint.warn{background:var(--amber-bg);color:var(--amber)}
.preview{width:300px;flex:none;position:sticky;top:16px;display:flex;flex-direction:column;gap:12px;align-items:center}
.preview>span{font-size:13px;font-weight:600;color:var(--soft);display:flex;align-items:center;gap:6px}
.phone{width:300px;height:560px;padding:16px 12px;border-radius:40px;background:linear-gradient(180deg,#4F7F76 0,#9DBFB5 200px,#ECF0EF 400px,#F2F3F4 100%);border:8px solid var(--black);box-shadow:0 30px 60px rgba(11,15,18,.25);display:flex;flex-direction:column;gap:12px;overflow:hidden}
.phone h3{margin:0;padding:18px 6px 0;color:#fff;font-size:22px;font-weight:600;letter-spacing:-.02em}
.p-card{border-radius:26px;background:rgba(255,255,255,.35);border:1px solid rgba(255,255,255,.55);display:flex;flex-direction:column}
.p-head{height:40px;padding:0 14px;display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:500;color:#1D2A30}
.p-body{margin:-1px;padding:14px;border-radius:26px;background:linear-gradient(180deg,rgba(246,249,248,.96),rgba(233,239,237,.92));border:1px solid rgba(255,255,255,.9);display:flex;flex-direction:column;gap:10px}
.p-body>span{display:flex;align-items:center;gap:10px}
.p-body>span b{font-size:15px;font-weight:600;overflow-wrap:anywhere}
.p-body>small{font-size:12px;line-height:1.4;color:var(--muted);overflow-wrap:anywhere}
.p-foot{display:flex;justify-content:space-between;font-size:12px}
.p-btn{height:40px;border-radius:20px;background:var(--black);color:#fff;font-size:14px;font-weight:600;display:grid;place-items:center}
.form-foot{display:flex;align-items:center;gap:10px;flex-wrap:wrap}

/* Settings */
.settings{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;align-items:start}
.settings-side{display:flex;flex-direction:column;gap:16px}
.info{display:flex;align-items:center;gap:12px;padding:12px;border-radius:20px;background:rgba(255,255,255,.8)}
.info div{flex:1;display:flex;flex-direction:column;gap:2px}
.info b{font-size:14px;font-weight:600}
.info small{font-size:12px;color:var(--muted);line-height:1.4}


/* Groups */
.groups{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.group-card{padding:20px;border-radius:30px;display:flex;flex-direction:column;gap:14px;text-decoration:none;transition:transform .2s ease,box-shadow .2s ease}
.group-card:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(16,22,26,.1)}
.group-card h2{margin:0;font-size:19px;font-weight:600;letter-spacing:-.01em}
.group-card p{margin:2px 0 0;font-size:13px;color:var(--muted);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.stack{display:flex;align-items:center}
.stack .avatar{width:34px;height:34px;font-size:13px;margin-left:-8px;box-shadow:0 0 0 2px #F2F3F4}
.stack .avatar:first-child{margin-left:0}
.stack .avatar.more{background:var(--black);color:#fff;border-color:var(--black);font-size:12px}
.group-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
.group-stats span{padding:10px 12px;border-radius:18px;background:rgba(255,255,255,.8);display:flex;flex-direction:column;gap:6px}
.group-stats b{font-size:18px;font-weight:600}
.group-stats small{font-size:12px;color:var(--muted)}
.group-new{border:2px dashed rgba(16,22,26,.16);background:rgba(255,255,255,.35);align-items:center;justify-content:center;text-align:center;color:var(--soft);min-height:220px}
.picker{display:flex;flex-direction:column;max-height:520px;overflow:auto;margin:0 -8px;padding:0 8px}
.pick{display:flex;align-items:center;gap:12px;padding:10px 4px;border-bottom:1px solid rgba(16,22,26,.06);cursor:pointer}
.pick:last-child{border-bottom:0}
.pick div{flex:1;min-width:0;display:flex;flex-direction:column}
.pick b{font-size:14px;font-weight:600}
.pick small{font-size:12px;color:var(--muted)}
.pick input{position:absolute;opacity:0;pointer-events:none}
.tick{width:24px;height:24px;flex:none;border-radius:8px;border:2px solid rgba(16,22,26,.2);display:grid;place-items:center;color:transparent;transition:background .15s ease,border-color .15s ease}
.tick svg.i{width:15px;height:15px;stroke-width:2.6}
.pick input:checked~.tick{background:var(--black);border-color:var(--black);color:#fff}
.pick input:focus-visible~.tick{outline:2px solid var(--black);outline-offset:2px}
.chips{display:flex;flex-wrap:wrap;gap:6px}
.chip-x{height:32px;padding:0 6px 0 12px;border-radius:16px;border:0;background:#fff;box-shadow:0 2px 8px rgba(16,22,26,.06);font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px}
.chip-x svg.i{width:14px;height:14px;color:var(--muted)}
.totals{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}
.totals span{padding:14px;border-radius:22px;background:rgba(255,255,255,.82);display:flex;flex-direction:column;gap:8px}
.totals i{width:34px;height:34px;border-radius:50%;background:rgba(16,22,26,.06);display:grid;place-items:center}
.totals i svg.i{width:17px;height:17px}
.totals b{font-size:22px;font-weight:600;letter-spacing:-.02em}
.totals small{font-size:12px;color:var(--muted)}
.days-cell{display:flex;flex-direction:column;gap:6px;min-width:90px}
.days-cell b{font-size:13px;font-weight:600}
.num{font-variant-numeric:tabular-nums}
.quiet{color:var(--muted)}
.flag{height:24px;padding:0 9px;border-radius:12px;background:var(--amber-bg);color:var(--amber);font-size:12px;font-weight:600;display:inline-flex;align-items:center;white-space:nowrap}
@media (max-width:1240px){.groups{grid-template-columns:repeat(2,minmax(0,1fr))}.totals{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:860px){.groups{grid-template-columns:minmax(0,1fr)}.totals{grid-template-columns:repeat(2,minmax(0,1fr))}}

/* Pause, moderation, deeds, search and notifications */
.pill.paused{background:var(--amber-bg);color:var(--amber)}
.ov-grid3{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:stretch}
.deed{display:flex;flex-direction:column;gap:6px;padding:8px 0}
.deed span{display:flex;justify-content:space-between;gap:8px;font-size:14px}
.deed b{font-weight:600}
.deed small{color:var(--muted);font-size:13px}
.deed .bar{height:8px}
.mod-group{display:flex;flex-direction:column;gap:4px}
.mod-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.mod-head .cell-main b{font-size:17px}
.mod-row{display:flex;align-items:center;gap:12px;padding:12px 4px;border-bottom:1px solid rgba(16,22,26,.06)}
.mod-row:last-child{border-bottom:0}
.mod-row>div{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.mod-row b{font-size:14px;font-weight:600}
.mod-row small{font-size:12px;color:var(--muted)}
.mod-actions{display:flex;gap:6px;flex:none}
.btn.ok{border-color:rgba(31,107,79,.25);background:var(--green-bg);color:var(--green)}
.btn-glass{position:relative}
.btn-glass .dot{position:absolute;top:-3px;right:-3px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#E0862B;color:#fff;font-size:11px;font-weight:700;display:grid;place-items:center;box-shadow:0 0 0 2px rgba(255,255,255,.5)}
.panel{position:fixed;z-index:45;top:84px;right:28px;width:min(400px,calc(100vw - 32px));max-height:calc(100vh - 120px);overflow:auto;padding:16px;border-radius:28px;background:#F6F9F8;box-shadow:0 30px 70px rgba(11,15,18,.28);display:flex;flex-direction:column;gap:10px;animation:rise .25s cubic-bezier(.22,1,.36,1) both}
.panel h2{margin:4px 4px 2px;font-size:18px;font-weight:600}
.panel .todo{text-decoration:none}
.search-layer{place-items:start center;padding-top:12vh}
.search-box{width:min(560px,100%);padding:14px;border-radius:30px;background:#F6F9F8;box-shadow:0 30px 70px rgba(11,15,18,.3);display:flex;flex-direction:column;gap:8px;animation:rise .25s cubic-bezier(.22,1,.36,1) both}
.search-box .search{width:100%;height:52px;border-radius:26px}
.search-box .search input{font-size:16px}
.results{display:flex;flex-direction:column;max-height:56vh;overflow:auto}
.results h3{margin:10px 8px 4px;font-size:12px;font-weight:600;color:var(--muted);letter-spacing:.02em}
.result{display:flex;align-items:center;gap:12px;padding:10px 8px;border-radius:18px;text-decoration:none;border:0;background:none;text-align:left;width:100%}
.result:hover,.result:focus-visible{background:rgba(16,22,26,.05)}
.result div{flex:1;min-width:0;display:flex;flex-direction:column}
.result b{font-size:14px;font-weight:600}
.result small{font-size:12px;color:var(--muted)}
.secret{display:flex;align-items:center;gap:8px;padding:8px 8px 8px 16px;border-radius:20px;background:#fff;border:1px solid var(--line)}
.secret code{flex:1;font:600 20px/1.2 ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.06em}
@media (max-width:860px){.ov-grid3{grid-template-columns:minmax(0,1fr)}}
.preview.quiz-side{width:360px;gap:16px;align-items:stretch}
@media (max-width:1240px){.editor.quiz-editor{flex-direction:column}.preview.quiz-side{width:100%}}
@media (max-width:480px){.hide-xs{display:none}}
/* Six sections fit a phone only as icons. */
@media (max-width:440px){nav.tabbar{gap:2px!important}nav.tabbar a{min-width:44px!important;height:44px!important}nav.tabbar a[aria-current=page]{padding:0;width:44px}nav.tabbar a[aria-current=page] span{display:none}}
@media (max-width:860px){.panel{top:auto;bottom:96px;right:16px}}

/* Dialog and toast */
.layer{position:fixed;inset:0;z-index:50;display:grid;place-items:center;padding:20px;background:rgba(11,15,18,.38);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);animation:fade .2s ease both}
@keyframes fade{from{opacity:0}}
.dialog{width:min(420px,100%);padding:24px;border-radius:30px;background:#F6F9F8;box-shadow:0 30px 70px rgba(11,15,18,.3);display:flex;flex-direction:column;gap:12px;animation:rise .3s cubic-bezier(.22,1,.36,1) both}
.dialog .circle{width:48px;height:48px;color:var(--red)}
.dialog h2{margin:4px 0 0;font-size:22px;font-weight:600;letter-spacing:-.02em}
.dialog p{margin:0;color:var(--muted);font-size:15px;line-height:1.45}
.dialog-actions{display:flex;gap:8px;margin-top:8px}
.dialog-actions .btn{flex:1}
.toast{position:fixed;left:50%;bottom:28px;z-index:60;transform:translateX(-50%);height:48px;padding:0 20px;border-radius:24px;background:var(--black);color:#fff;font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;box-shadow:0 16px 40px rgba(11,15,18,.3);animation:rise .3s cubic-bezier(.22,1,.36,1) both}

/* Live quiz console */
.lv-h{margin:0;font-size:18px;font-weight:600;letter-spacing:-.01em}
.lv-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.lv-actions .btn{white-space:nowrap}
.lv-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(16,22,26,.06)}
.lv-row:last-child{border-bottom:0}
.lv-row div{display:flex;flex-direction:column;gap:2px;min-width:0}
.lv-row b{font-size:14px;font-weight:600}.lv-row small{font-size:12px;color:var(--muted)}
.lv-steps{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px;font-size:14px;line-height:1.5;color:var(--soft)}
.lv-steps b{color:var(--ink);font-weight:600}
.lv-stages{display:flex;gap:8px;flex-wrap:wrap}
.lv-stages span{height:34px;padding:0 14px;border-radius:17px;background:rgba(255,255,255,.7);color:var(--muted);display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600}
.lv-stages span.done{color:var(--green)}.lv-stages span.now{background:var(--black);color:#fff}
.lv-stages svg.i{width:15px;height:15px}
.lv-q{display:flex;align-items:center;gap:22px}
.lv-q h2{margin:6px 0 0;font-size:26px;font-weight:600;letter-spacing:-.02em;line-height:1.25}
.lv-ring{position:relative;width:112px;height:112px;flex:none;display:grid;place-items:center}
.lv-ring svg{position:absolute;inset:0;transform:rotate(-90deg)}
.lv-ring circle{fill:none;stroke:rgba(16,22,26,.08);stroke-width:10}
.lv-ring circle+circle{stroke:#2F8F68;stroke-linecap:round}
.lv-ring>span{display:flex;flex-direction:column;align-items:center;text-align:center}
.lv-ring b{font-size:34px;font-weight:600;line-height:1}.lv-ring small{font-size:11px;color:var(--muted);max-width:80px}
.lv-dist{display:flex;flex-direction:column;gap:8px}
.lv-opt{display:flex;align-items:center;gap:14px;padding:12px 14px;border-radius:20px;background:rgba(255,255,255,.75);border:1.5px solid rgba(16,22,26,.06)}
.lv-opt i{width:34px;height:34px;flex:none;border-radius:17px;background:rgba(16,22,26,.06);display:grid;place-items:center;font-style:normal;font-size:14px;font-weight:600}
.lv-opt b{width:28%;min-width:0;font-size:16px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lv-opt em{flex:1;height:12px;border-radius:6px;background:rgba(16,22,26,.07);overflow:hidden}
.lv-opt em u{display:block;height:100%;min-width:12px;border-radius:6px;background:rgba(16,22,26,.22);transition:width .4s ease}
.lv-opt strong{width:40px;text-align:right;font-size:16px;font-weight:600}
.lv-opt small{width:52px;font-size:12px;font-weight:600;color:var(--green)}
.lv-opt.right{background:var(--green-bg);border-color:#2F8F68}
.lv-opt.right i{background:var(--green);color:#fff}.lv-opt.right em u{background:#2F8F68}
.lv-lobby{display:flex;flex-direction:column;align-items:center;gap:4px;padding:18px 0 6px;text-align:center}
.lv-lobby b{font-size:48px;font-weight:600;letter-spacing:-.03em;line-height:1}
.lv-lobby span{font-size:15px;color:var(--soft)}.lv-lobby small{font-size:13px;color:var(--muted);max-width:420px}
.lv-names{display:flex;flex-wrap:wrap;gap:8px;justify-content:center}
.lv-names span{height:36px;padding:0 14px 0 4px;border-radius:18px;background:rgba(255,255,255,.9);border:1px solid var(--line);display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600}
.lv-names i,.lv-lb i{width:28px;height:28px;flex:none;border-radius:50%;background:var(--green-bg);color:var(--green);display:grid;place-items:center;font-style:normal;font-size:12px;font-weight:600}
.lv-dark{padding:18px 20px;border-radius:30px;background:var(--black);color:#fff;display:flex;flex-direction:column;align-items:flex-start;gap:10px}
.lv-dark>b{font-size:22px;font-weight:600;letter-spacing:-.01em}
.lv-dark>small{font-size:13px;line-height:1.5;color:rgba(255,255,255,.72)}
.lv-dark .btn{background:rgba(255,255,255,.14);border:0;color:#fff}
.lv-pill{height:28px;padding:0 12px 0 10px;border-radius:14px;background:#C8402F;color:#fff;font-size:12px;font-weight:600;letter-spacing:.04em;display:inline-flex;align-items:center;gap:6px}
.lv-pill i,.lv-dot{width:7px;height:7px;border-radius:50%;background:#fff;animation:lvBlink 1.4s ease-in-out infinite}
.lv-dot{background:#C8402F;margin-right:2px}
@keyframes lvBlink{50%{opacity:.35}}
.lv-board{display:flex;flex-direction:column;max-height:520px;overflow:auto}
.lv-lb{display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid rgba(16,22,26,.06)}
.lv-lb:last-child{border-bottom:0}
.lv-lb>b{width:22px;text-align:center;font-size:14px;font-weight:600;color:var(--muted)}
.lv-lb>b.top{color:var(--green)}
.lv-lb>span{flex:1;min-width:0;font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lv-lb>em{width:8px;height:8px;border-radius:50%;background:rgba(16,22,26,.18)}.lv-lb>em.yes{background:#2F8F68}
.lv-lb>strong{min-width:56px;text-align:right;font-size:14px;font-weight:600}
.lv-podium{display:flex;align-items:flex-end;gap:14px;max-width:560px;width:100%;align-self:center;padding-top:10px}
.lv-slot{flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;gap:6px}
.lv-slot i{width:52px;height:52px;border-radius:50%;background:var(--green-bg);color:var(--green);display:grid;place-items:center;font-style:normal;font-size:20px;font-weight:600}
.lv-slot.first i{width:64px;height:64px;background:var(--green);color:#fff;font-size:24px}
.lv-slot b{max-width:100%;font-size:15px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lv-slot small{font-size:12px;color:var(--muted)}
.lv-slot span{align-self:stretch;box-sizing:border-box;padding-top:12px;border-radius:18px 18px 6px 6px;border:1px solid rgba(47,155,110,.25);background:linear-gradient(180deg,rgba(47,155,110,.32),rgba(47,155,110,.12));color:var(--green);display:grid;place-items:start center;font-size:28px;font-weight:600}
.lv-slot.first span{border-color:var(--green);background:linear-gradient(180deg,#2F8F68,#1F6B4F);color:#fff}
@media (max-width:860px){.lv-q{flex-direction:column;align-items:flex-start;gap:12px}.lv-q h2{font-size:22px}.lv-opt b{width:34%}.lv-opt small{display:none}}
@media (prefers-reduced-motion:reduce){.lv-pill i,.lv-dot{animation:none}.lv-opt em u{transition:none}}

/* Narrower screens */
@media (max-width:1240px){
  .kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
  .ov-grid,.ov-grid2,.settings{grid-template-columns:minmax(0,1fr)}
  .split{flex-direction:column}
  .split>.card:first-child{width:100%}
  .detail{width:100%;position:static}
  .editor{flex-direction:column-reverse;align-items:stretch}
  .preview{position:static;width:100%}
}
@media (max-width:860px){
  .side{display:none}
  .main{padding:12px 16px calc(110px + env(safe-area-inset-bottom))}
  .top{flex-direction:column;align-items:flex-start;padding-top:calc(env(safe-area-inset-top) + 12px)}
  .top h1{font-size:30px}
  .top-actions{justify-content:flex-start}
  .tabbar{display:flex;position:fixed;left:50%;bottom:calc(16px + env(safe-area-inset-bottom));transform:translateX(-50%);z-index:30;padding:6px;border-radius:30px;background:rgba(255,255,255,.78);border:1px solid rgba(255,255,255,.95);-webkit-backdrop-filter:blur(30px) saturate(180%);backdrop-filter:blur(30px) saturate(180%);box-shadow:0 12px 34px rgba(16,22,26,.16);gap:4px}
  .tabbar a{min-width:48px;height:48px;padding:0;border-radius:24px;color:#4A555B;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;font-size:15px;font-weight:600}
  .tabbar a span{display:none}
  .tabbar a[aria-current=page]{padding:0 18px 0 14px;background:var(--black);color:#fff}
  .tabbar a[aria-current=page] span{display:inline}
  .kpi b{font-size:24px}
  .metrics{grid-template-columns:repeat(2,minmax(0,1fr))}
  .grid3{grid-template-columns:minmax(0,1fr)}
  .choice-row{grid-template-columns:minmax(0,1fr)}
  .search{width:100%}
  .toolbar>span{width:100%}
  table,thead,tbody,tr,td{display:block}
  thead{display:none}
  tbody tr{padding:12px 4px;border-bottom:1px solid rgba(16,22,26,.06);display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center}
  tbody tr:last-child{border-bottom:0}
  td{padding:0;border:0}
  td.wide{grid-column:1 / -1}
  td.hide-sm{display:none}
  .card{padding:16px;border-radius:26px}
}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
</head>
<body>
<section id="login" class="login">
  <form id="login-form" class="glass" novalidate>
    <div class="brand"><span class="brand-mark" data-icon="moon"></span><div><b>Chetele</b><small>Админ-кабинет</small></div></div>
    <h1>Ассаламу алейкум</h1>
    <p>Войдите, чтобы управлять событиями и участниками сообщества.</p>
    <label class="field"><span>Логин</span><input id="login-name" class="input" autocomplete="username" required></label>
    <label class="field"><span>Пароль</span><input id="login-pass" class="input" type="password" autocomplete="current-password" required></label>
    <p id="login-error" class="error" role="alert"></p>
    <button class="btn black" type="submit">Войти в кабинет</button>
  </form>
</section>

<div id="shell" class="shell" hidden>
  <aside class="side">
    <div class="brand"><span class="brand-mark" data-icon="moon"></span><div><b>Chetele</b><small>Админ-кабинет</small></div></div>
    <nav class="nav" aria-label="Разделы">
      <a href="#overview" data-route="overview"><span data-icon="home"></span>Обзор<em></em></a>
      <a href="#events" data-route="events"><span data-icon="flag"></span>События<em id="nav-events"></em></a>
      <a href="#users" data-route="users"><span data-icon="users"></span>Участники<em id="nav-users"></em></a>
      <a href="#groups" data-route="groups"><span data-icon="group"></span>Группы<em id="nav-groups"></em></a>
      <a href="#moderation" data-route="moderation"><span data-icon="shield"></span>Модерация<em id="nav-moderation"></em></a>
      <a href="#quizzes" data-route="quizzes"><span data-icon="brain"></span>Тесты<em id="nav-quizzes"></em></a>
      <a href="#settings" data-route="settings"><span data-icon="sliders"></span>Настройки<em></em></a>
    </nav>
    <div class="side-status"><span id="app-version">Сайт работает</span><a href="/" target="_blank" rel="noopener">Открыть приложение<i data-icon="arrow"></i></a></div>
    <div class="me"><span class="me-avatar" id="me-avatar">A</span><div><b id="me-name">admin</b><small>Администратор</small></div><button type="button" id="logout" aria-label="Выйти" data-icon="logout"></button></div>
  </aside>
  <main class="main" id="main"></main>
  <nav class="tabbar" aria-label="Разделы">
    <a href="#overview" data-route="overview" aria-label="Обзор"><i data-icon="home"></i><span>Обзор</span></a>
    <a href="#events" data-route="events" aria-label="События"><i data-icon="flag"></i><span>События</span></a>
    <a href="#users" data-route="users" aria-label="Участники"><i data-icon="users"></i><span>Участники</span></a>
    <a href="#groups" data-route="groups" aria-label="Группы"><i data-icon="group"></i><span>Группы</span></a>
    <a href="#moderation" data-route="moderation" aria-label="Модерация"><i data-icon="shield"></i><span>Модерация</span></a>
    <a href="#quizzes" data-route="quizzes" aria-label="Тесты"><i data-icon="brain"></i><span>Тесты</span></a>
    <a href="#settings" data-route="settings" aria-label="Настройки"><i data-icon="sliders"></i><span>Настройки</span></a>
  </nav>
</div>

<script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES) ?>">
(() => {
  /* ---------- Basics ---------- */
  const PATHS = {
    moon:'M19 14.5A7.5 7.5 0 0 1 9.5 5a7.5 7.5 0 1 0 9.5 9.5zM16.5 3.5l.5 1.3 1.3.5-1.3.5-.5 1.3-.5-1.3-1.3-.5 1.3-.5z',
    home:'M4 11l8-7 8 7v9H4zM10 20v-6h4v6', flag:'M5 21V4M5 4h11l-2 3.5L16 11H5',
    users:'M9 8a3.5 3.5 0 1 0 0 .01M3 20c.8-3.5 3.3-5.5 6-5.5s5.2 2 6 5.5M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5c1.6.6 2.6 2.4 3 5.5',
    sliders:'M4 7h10M18 7h2M4 17h4M12 17h8M14 5v4M8 15v4', plus:'M12 5v14M5 12h14', search:'M4 11a7 7 0 1 0 14 0a7 7 0 1 0 -14 0M20 20l-4-4',
    chev:'M9.5 6l6 6-6 6', back:'M14.5 6l-6 6 6 6', more:'M6 12h.01M12 12h.01M18 12h.01', check:'M5 12.5l4.5 4.5L19 7.5',
    clock:'M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0M12 7.5V12l3 2', pen:'M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4',
    trash:'M5 7h14M10 7V4h4v3M7 7l1 13h8l1-13', logout:'M14 4h5v16h-5M10 8l-4 4 4 4M6 12h10', x:'M6 6l12 12M18 6L6 18',
    arrow:'M7 17L17 7M9 7h8v8', lock:'M6 11h12v9H6zM8.5 11V8a3.5 3.5 0 0 1 7 0v3', phone:'M8 3h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM11 18h2',
    rating:'M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 6h2.5v1.5A3 3 0 0 1 17 10.5M7 6H4.5v1.5A3 3 0 0 0 7 10.5',
    copy:'M8 8h11v12H8zM5 16V4h11', group:'M8 7.5a3 3 0 1 0 0 .01M16 7.5a3 3 0 1 0 0 .01M2.5 19c.6-3 2.8-4.5 5.5-4.5s4.9 1.5 5.5 4.5M10.5 19c.6-3 2.8-4.5 5.5-4.5s4.9 1.5 5.5 4.5',
    brain:'M12 5.5a3 3 0 0 0-5.4-1.8A3.2 3.2 0 0 0 4 7.6a3.2 3.2 0 0 0-.4 5.3A3.6 3.6 0 0 0 7.2 18.4 2.9 2.9 0 0 0 12 19.6zM12 5.5a3 3 0 0 1 5.4-1.8A3.2 3.2 0 0 1 20 7.6a3.2 3.2 0 0 1 .4 5.3 3.6 3.6 0 0 1-3.6 5.5 2.9 2.9 0 0 1-4.8 1.2zM12 5.5v14.1M8.5 9.2c1.4 0 2.4.9 2.4 2.2M15.5 9.2c-1.4 0-2.4.9-2.4 2.2M7.6 14.6c1.3-.2 2.6.4 3.1 1.5M16.4 14.6c-1.3-.2-2.6.4-3.1 1.5', up:'M6 14.5l6-6 6 6', down:'M6 9.5l6 6 6-6', eyeoff:'M3 3l18 18M10.6 6.1A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-2.6 3.3M6.6 6.6C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.6 0 3-.4 4.3-1M9.9 9.9a3 3 0 0 0 4.2 4.2',
    book:'M5 5a2 2 0 0 1 2-2h12v15H7a2 2 0 0 0-2 2zM5 20a2 2 0 0 0 2 2h12v-4M9 7h6', star:'M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.8l-5.2 2.8 1-5.8-4.3-4.1 5.9-.9z',
    shield:'M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6zM9 12l2 2 4-4', bell:'M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0', pause:'M9 6v12M15 6v12', play:'M8 5.5v13l10-6.5z', send:'M4 12l16-8-6 16-2.5-6.5zM11.5 13.5L20 4', key:'M14.5 9.5a4 4 0 1 0-3.4 4L5 19.5V21h3v-2h2v-2h2l1.1-1.1a4 4 0 0 0 1.4-6.4zM16 8h.01',
    download:'M12 4v11M7 10l5 5 5-5M5 20h14', flame:'M12 3c1 3.5 5 5.5 5 10a5 5 0 0 1-10 0c0-2.5 1.5-4 2.5-5 .3 1.6 1 2.6 2 3 0-3 .5-5.5.5-8z', alert:'M12 4l9 16H3zM12 10v4M12 17.5h.01', refresh:'M20 11a8 8 0 1 0-2.3 5.7M20 5v6h-6',
    salawat:'M6.5 9a5.5 5.5 0 1 0 11.0 0a5.5 5.5 0 1 0 -11.0 0M12 14.5v2M10.5 18a1.5 1.5 0 1 0 3.0 0a1.5 1.5 0 1 0 -3.0 0M11 19.5l-1 2M13 19.5l1 2',
    zikr:'M8 3h8a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3zM8.5 7h7v4h-7zM10 16a2 2 0 1 0 4 0a2 2 0 1 0 -4 0',
    quran:'M12 6.5C10 5 7.3 4.6 4.5 5.2v9.6c2.8-.6 5.5-.2 7.5 1.3 2-1.5 4.7-1.9 7.5-1.3V5.2C16.7 4.6 14 5 12 6.5zM12 6.5v9.6M6 20.5l12-4M18 20.5l-12-4'
  };
  const icon = name => `<svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path d="${PATHS[name]||''}"/></svg>`;
  const paintIcons = root => root.querySelectorAll('[data-icon]').forEach(el => { el.innerHTML = icon(el.dataset.icon); });
  const $ = s => document.querySelector(s);
  const esc = v => String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
  const num = v => (Number(v) || 0).toLocaleString('ru-RU');
  const plural = (n, one, few, many) => { const d = n % 10, h = n % 100; return d === 1 && h !== 11 ? one : d >= 2 && d <= 4 && (h < 12 || h > 14) ? few : many; };
  /* The route goes in ?path=; anything after its own ? stays a separate query parameter. */
  const api = p => { const [path, query] = p.split('?'); return '/api/index.php?path=' + encodeURIComponent(path) + (query ? '&' + query : ''); };
  let cred = null, signingIn = false;
  async function req(path, options = {}) {
    const response = await fetch(api(path), { ...options, headers: { ...(options.headers || {}), 'X-Admin-Login': cred?.login || '', 'X-Admin-Password': cred?.password || '' } });
    /* A saved sign-in the server no longer accepts (the password was changed) leads back to the form. */
    if (response.status === 403 && cred && !signingIn) { signedOut('Данные входа больше не подходят — войдите снова.'); throw Error('Нужно войти снова'); }
    if (!response.ok) throw Error((await response.json().catch(() => ({}))).error || 'Ошибка сервера');
    return response.json();
  }
  const send = (path, method, body) => req(path, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });

  /* Dates are local calendar days, as the app counts them. */
  const dayKey = (d = new Date()) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const parseDay = key => new Date(String(key).slice(0, 10) + 'T12:00');
  const shortDate = key => new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'short' }).format(parseDay(key));
  const daysBetween = (a, b) => Math.round((parseDay(b) - parseDay(a)) / 864e5);
  function relDay(stamp) {
    if (!stamp) return '';
    const key = String(stamp).slice(0, 10), diff = daysBetween(key, dayKey());
    if (diff === 0) return 'сегодня'; if (diff === 1) return 'вчера';
    return shortDate(key);
  }

  const METRICS = {
    salawat: { label: 'Салават', icon: 'salawat', unit: 'раз' },
    dhikr: { label: 'Зикр', icon: 'zikr', unit: 'раз' },
    quran_pages: { label: 'Куран, стр.', icon: 'quran', unit: 'стр.' },
    book_pages: { label: 'Книги, стр.', icon: 'book', unit: 'стр.' },
    tahajjud_count: { label: 'Тахаджуд', icon: 'moon', unit: 'ночей' },
    custom: { label: 'Своё', icon: 'star', unit: '' }
  };
  const metricOf = e => METRICS[e.metric] || METRICS.salawat;
  /* A custom event counts in the unit the admin typed; the rest come from the app's deeds. */
  const unitOf = e => e.metric === 'custom' ? (e.custom_unit || 'раз') : metricOf(e).unit;
  const MODES = { common_goal: 'Общая цель', personal_path: 'Личный путь' };
  function stateOf(e) {
    if (e.status === 'paused') return e.ends_on < dayKey() ? 'finished' : 'paused';
    if (e.status !== 'published') return e.status || 'draft';
    const today = dayKey();
    return e.ends_on < today ? 'finished' : e.starts_on > today ? 'published' : 'active';
  }
  const STATE_LABEL = { active: 'Идёт', published: 'Скоро', draft: 'Черновик', finished: 'Завершено', paused: 'Пауза' };
  const progressOf = e => Number(e.total_progress) || 0;
  const pctOf = e => Math.min(100, Math.round(progressOf(e) / Math.max(1, Number(e.goal)) * 100));
  const period = e => `${shortDate(e.starts_on)} — ${shortDate(e.ends_on)}`;

  function toast(text) {
    document.querySelector('.toast')?.remove();
    const el = document.createElement('div'); el.className = 'toast'; el.setAttribute('role', 'status');
    el.innerHTML = icon('check') + esc(text); document.body.append(el);
    setTimeout(() => el.remove(), 2400);
  }
  function confirmDialog({ title, text, action = 'Удалить', danger = true, iconName = 'trash' }) {
    return new Promise(resolve => {
      const layer = document.createElement('div'); layer.className = 'layer';
      layer.innerHTML = `<section class="dialog" role="dialog" aria-modal="true" aria-labelledby="dlg-title"><span class="circle" style="${danger ? '' : 'color:var(--ink)'}">${icon(iconName)}</span><h2 id="dlg-title">${esc(title)}</h2><p>${esc(text)}</p><div class="dialog-actions"><button type="button" class="btn" data-no>Отмена</button><button type="button" class="btn ${danger ? 'danger' : 'black'}" data-yes>${esc(action)}</button></div></section>`;
      const done = value => { layer.remove(); document.removeEventListener('keydown', onKey); resolve(value); };
      const onKey = event => { if (event.key === 'Escape') done(false); };
      layer.addEventListener('click', event => { if (event.target === layer || event.target.closest('[data-no]')) done(false); if (event.target.closest('[data-yes]')) done(true); });
      document.addEventListener('keydown', onKey);
      document.body.append(layer); layer.querySelector('[data-yes]').focus();
    });
  }

  /* ---------- Data ---------- */
  const data = { events: null, users: null, overview: null, settings: null, groups: null, moderation: null, quizzes: null };
  async function loadModeration(force) { if (force || !data.moderation) data.moderation = await req('admin/moderation'); $('#nav-moderation').textContent = data.moderation.length || ''; paintBell(); return data.moderation; }
  async function loadGroups(force) { if (force || !data.groups) data.groups = await req('admin/groups'); $('#nav-groups').textContent = data.groups.length || ''; return data.groups; }
  async function loadEvents(force) { if (force || !data.events) data.events = await req('admin/events'); paintCounts(); return data.events; }
  async function loadUsers(force) { if (force || !data.users) data.users = await req('admin/users'); paintCounts(); return data.users; }
  function paintCounts() {
    if (data.events) $('#nav-events').textContent = data.events.filter(e => stateOf(e) === 'active').length || '';
    if (data.users) $('#nav-users').textContent = data.users.length || '';
  }

  /* ---------- Layout helpers ---------- */
  const main = $('#main');
  const top = (kicker, title, sub, actions = '') => `<header class="top"><div class="top-copy"><span>${kicker}</span><h1>${title}</h1><p>${sub}</p></div><div class="top-actions">${actions}</div></header>`;
  const createBtn = `<a class="btn black" href="#events/new">${icon('plus')}Создать событие</a>`;
  const refreshBtn = `<button type="button" class="btn-glass" data-refresh aria-label="Обновить данные">${icon('refresh')}</button>`;
  /* Search and notifications sit in every section's header. */
  const tools = () => `<button type="button" class="btn-glass" data-search aria-label="Поиск" title="Поиск (/)">${icon('search')}</button><button type="button" class="btn-glass" data-bell aria-label="Уведомления" aria-haspopup="dialog">${icon('bell')}<span class="dot" id="bell-dot" hidden></span></button>`;
  /* The page rises in only when the section changes, not on every repaint. */
  let shownView = '';
  /* After a loading placeholder the real page replaces it in place, without a second entrance. */
  function mount(html, key) { main.innerHTML = `<div class="view${key === shownView || shownView === 'loading' ? ' still' : ''}">${html}</div>`; shownView = key; paintBell(); }
  function loading(title) { mount(top('Chetele', title, 'Загружаем…') + '<section class="glass card"><div class="empty">Загружаем данные…</div></section>', 'loading'); }
  function failed(title, error) { mount(top('Chetele', title, 'Не удалось загрузить') + `<section class="glass card"><div class="empty">${esc(error.message)}<br><br><button type="button" class="btn small" data-refresh>Повторить</button></div></section>`, 'failed'); }

  /* ---------- Overview ---------- */
  async function showOverview() {
    /* Repeat visits and «Обновить» keep the current page until fresh numbers arrive. */
    if (!data.overview) loading('Обзор');
    let ov, events;
    try { [ov, events] = await Promise.all([req('admin/overview'), loadEvents(true), loadModeration(true)]); data.overview = ov; }
    catch (error) { failed('Обзор', error); return; }
    const today = dayKey(), byDay = Object.fromEntries((ov.activity || []).map(r => [String(r.date).slice(0, 10), Number(r.entries) || 0]));
    const days = Array.from({ length: 30 }, (_, i) => { const d = new Date(); d.setDate(d.getDate() - 29 + i); const key = dayKey(d); return { key, n: byDay[key] || 0 }; });
    const total = days.reduce((s, d) => s + d.n, 0), max = Math.max(1, ...days.map(d => d.n));
    const lastWeek = days.slice(-7).reduce((s, d) => s + d.n, 0), prevWeek = days.slice(-14, -7).reduce((s, d) => s + d.n, 0);
    const weekDelta = prevWeek ? Math.round((lastWeek - prevWeek) / prevWeek * 100) : null;
    const users = Number(ov.users) || 0, todayEntries = Number(ov.todayEntries) || 0;
    const active = events.filter(e => stateOf(e) === 'active');
    const people = active.reduce((s, e) => s + (Number(e.participant_count) || 0), 0);
    const kpi = (name, value, label, chip) => `<article class="glass kpi"><div class="kpi-top"><span class="circle">${icon(name)}</span>${chip ? `<span class="chip">${chip}</span>` : ''}</div><div><b>${value}</b><small>${label}</small></div></article>`;

    const now = [...active, ...events.filter(e => stateOf(e) === 'published')].slice(0, 4);
    const nowHtml = now.length ? now.map(e => `<a class="ev-mini" href="#events/${e.id}"><span class="circle">${icon(metricOf(e).icon)}</span><span class="ev-mini-copy"><span><b>${esc(e.title)}</b><b>${stateOf(e) === 'active' ? pctOf(e) + '%' : ''}</b></span><i class="bar"><i style="width:${pctOf(e)}%"></i></i><small>${stateOf(e) === 'active' ? 'до ' + shortDate(e.ends_on) : 'старт ' + shortDate(e.starts_on)} · ${num(e.participant_count)} ${plural(Number(e.participant_count) || 0, 'участник', 'участника', 'участников')}</small></span></a>`).join('') : `<div class="empty">Сейчас событий нет.<br><a class="link" href="#events/new">Создать событие${icon('chev')}</a></div>`;

    const recent = (ov.recentUsers || []).map(u => `<div class="person"><span class="avatar">${esc((u.display_name || '?').slice(0, 1))}</span><div><b>${esc(u.display_name)}</b><small>зарегистрировался</small></div><small class="muted">${relDay(u.created_at)}</small></div>`).join('') || '<div class="empty">Пока никого.</div>';


    const DEEDS = [['salawat', 'Салават'], ['tasbihat', 'Тасбихат'], ['quran', 'Куран'], ['nafil', 'Нафиль-намазы'], ['jauchan', 'Джаушан'], ['kitap', 'Чтение книги'], ['tahajjud', 'Тахаджуд'], ['qaza', 'Каза-намазы'], ['fast', 'Пост']];
    const deedsToday = ov.deedsToday || {}, deedRows = DEEDS.map(([k, l]) => [l, Number(deedsToday[k]) || 0]).filter(([, n]) => n > 0).sort((a, b) => b[1] - a[1]).slice(0, 6);
    const deedHtml = deedRows.length ? deedRows.map(([l, n]) => `<div class="deed"><span><b>${l}</b><small>${num(n)} ${plural(n, 'человек', 'человека', 'человек')}</small></span><i class="bar"><i style="width:${Math.round(n / Math.max(1, todayEntries) * 100)}%"></i></i></div>`).join('') : '<div class="empty">Сегодня пока никто не отметил дела.</div>';
    const weekday = new Intl.DateTimeFormat('ru-RU', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date());
    mount(top(`Ассаламу алейкум, ${esc(cred.login)}`, 'Обзор', weekday[0].toUpperCase() + weekday.slice(1), tools() + refreshBtn + createBtn) + `
      <div class="kpis">
        ${kpi('users', num(users), 'участников всего', ov.newUsersPeriod ? `+${num(ov.newUsersPeriod)} за 30 дней` : '')}
        ${kpi('check', num(todayEntries), 'отметили дела сегодня', users ? `${Math.round(todayEntries / users * 100)}% от всех` : '')}
        ${kpi('flag', num(active.length), plural(active.length, 'событие идёт', 'события идут', 'событий идут'), people ? `${num(people)} ${plural(people, 'участник', 'участника', 'участников')}` : '')}
        ${kpi('rating', num(ov.todayPoints), 'очков за сегодня', '')}
      </div>
      <div class="ov-grid">
        <section class="glass card">
          <div class="card-head"><h2>Активность за 30 дней</h2></div>
          <div class="chart-total"><b>${num(total)}</b><span class="muted">${plural(total, 'отметка', 'отметки', 'отметок')} · в среднем ${num(Math.round(total / 30))} в день</span>${weekDelta !== null ? `<span class="chip" style="${weekDelta < 0 ? 'background:var(--amber-bg);color:var(--amber)' : ''}">${weekDelta >= 0 ? '+' : ''}${weekDelta}% за неделю</span>` : ''}</div>
          <div class="chart" role="img" aria-label="Отметки по дням за 30 дней">${days.map(d => `<span style="height:${Math.max(2, d.n / max * 100)}%" title="${shortDate(d.key)}: ${d.n}"></span>`).join('')}</div>
          <div class="chart-axis"><span>${shortDate(days[0].key)}</span><span>${shortDate(days[10].key)}</span><span>${shortDate(days[20].key)}</span><span>Сегодня · ${num(days[29].n)}</span></div>
        </section>
        <section class="glass card"><div class="card-head"><h2>События сейчас</h2><a class="link" href="#events">Все${icon('chev')}</a></div>${nowHtml}</section>
      </div>
      <div class="ov-grid3">
        <section class="glass card"><div class="card-head"><h2>Новые участники</h2><a class="link" href="#users">Все ${num(users)}${icon('chev')}</a></div><div>${recent}</div></section>
        <section class="glass card"><div class="card-head"><h2>Что отмечают чаще</h2><span class="chip">сегодня</span></div><div>${deedHtml}</div></section>
      </div>`, 'overview');
  }

  /* ---------- Events ---------- */
  const ev = { tab: 'all', query: '', mode: 'all', selected: null };
  const TABS = [['all', 'Все'], ['active', 'Идут'], ['published', 'Скоро'], ['draft', 'Черновики'], ['finished', 'Завершены']];
  async function showEvents(selectedId) {
    if (selectedId !== undefined) ev.selected = selectedId;
    if (!data.events) loading('События');
    try { await loadEvents(!data.events); } catch (error) { failed('События', error); return; }
    paintEvents();
  }
  function paintEvents() {
    const events = data.events, counts = Object.fromEntries(TABS.map(([k]) => [k, k === 'all' ? events.length : events.filter(e => stateOf(e) === k).length]));
    const q = ev.query.toLowerCase();
    const list = events.filter(e => (ev.tab === 'all' || stateOf(e) === ev.tab) && (ev.mode === 'all' || e.mode === ev.mode) && (!q || (e.title + ' ' + (e.description || '')).toLowerCase().includes(q)));
    const selected = events.find(e => String(e.id) === String(ev.selected)) || null;
    const running = counts.active;
    const rows = list.length ? list.map(e => { const st = stateOf(e); return `<tr data-event="${e.id}" aria-selected="${selected && selected.id === e.id}">
        <td class="wide"><span class="cell-main"><span class="circle">${icon(metricOf(e).icon)}</span><div><b>${esc(e.title)}</b><small>${MODES[e.mode] || 'Общая цель'} · ${period(e)}</small></div></span></td>
        <td class="hide-sm"><b>${num(e.participant_count)}</b></td>
        <td>${st === 'draft' ? '<small class="muted">после публикации</small>' : `<span class="prog"><b>${pctOf(e)}%</b><i class="bar"><i style="width:${pctOf(e)}%"></i></i></span>`}</td>
        <td><span class="pill ${st}">${STATE_LABEL[st] || st}</span></td>
        <td class="right hide-sm"><span class="menu-wrap"><button type="button" class="btn small icon" data-menu="${e.id}" aria-label="Действия с событием" aria-haspopup="menu">${icon('more')}</button></span></td>
      </tr>`; }).join('') : `<tr><td colspan="5" class="wide"><div class="empty">${events.length ? 'По этому фильтру событий нет.' : 'Событий пока нет. Создайте первое — оно сразу появится в приложении.'}</div></td></tr>`;
    mount(top('Управление', 'События', `${num(events.length)} ${plural(events.length, 'событие', 'события', 'событий')} · ${running} ${plural(running, 'идёт', 'идут', 'идут')} сейчас`, tools() + refreshBtn + createBtn) + `
      <div class="split">
        <section class="glass card">
          <div class="toolbar"><div class="seg" role="group" aria-label="Статус">${TABS.map(([k, l]) => `<button type="button" data-tab="${k}" aria-pressed="${ev.tab === k}">${l} · ${counts[k]}</button>`).join('')}</div>
            <span><label class="search">${icon('search')}<input id="ev-search" type="search" placeholder="Найти событие" aria-label="Найти событие" value="${esc(ev.query)}"></label>
            <select id="ev-mode" class="select" aria-label="Режим"><option value="all">Все режимы</option><option value="common_goal">Общая цель</option><option value="personal_path">Личный путь</option></select></span></div>
          <table><thead><tr><th>Событие</th><th>Участники</th><th>Прогресс</th><th>Статус</th><th></th></tr></thead><tbody>${rows}</tbody></table>
        </section>
        <aside class="glass card detail" aria-label="Карточка события">${selected ? eventDetail(selected) : `<div class="detail-empty"><span class="circle">${icon('flag')}</span>Выберите событие в списке, чтобы увидеть прогресс и темп.</div>`}</aside>
      </div>`, 'events');
    $('#ev-mode').value = ev.mode;
  }
  function eventDetail(e) {
    const st = stateOf(e), m = metricOf(e), goal = Number(e.goal) || 0, done = progressOf(e), today = dayKey();
    const length = daysBetween(e.starts_on, e.ends_on) + 1, left = Math.max(0, daysBetween(today, e.ends_on) + 1), passed = Math.min(length, Math.max(0, daysBetween(e.starts_on, today) + 1));
    let pace = '';
    if (st === 'active' && e.mode !== 'personal_path') {
      const need = Math.max(0, Math.ceil((goal - done) / Math.max(1, left))), have = Math.round(done / Math.max(1, passed));
      pace = done >= goal ? 'Цель уже достигнута, ма ша Аллах' : `Чтобы успеть, нужно ~${num(need)} в день — сейчас ${num(have)}`;
    } else if (st === 'published') pace = `Начнётся через ${daysBetween(today, e.starts_on)} ${plural(daysBetween(today, e.starts_on), 'день', 'дня', 'дней')}`;
    else if (st === 'finished') pace = done >= goal ? 'Цель выполнена' : 'Событие завершилось';
    else if (st === 'paused') pace = 'На паузе: в приложении видно, но вступить нельзя';
    else pace = 'Черновик: участники увидят событие после публикации';
    const when = st === 'active' ? `осталось ${left} ${plural(left, 'день', 'дня', 'дней')}` : st === 'published' ? 'ещё не началось' : st === 'finished' ? 'завершено' : st === 'paused' ? 'на паузе' : 'черновик';
    const pending = Number(e.pending_count) || 0;
    return `<div class="d-close"><span class="pill ${st}">${STATE_LABEL[st]}</span><button type="button" class="btn small icon" data-close-detail aria-label="Закрыть карточку">${icon('x')}</button></div>
      <div class="d-title"><b>${esc(e.title)}</b><small>${MODES[e.mode] || ''} · ${period(e)} · ${when}</small></div>
      <div class="d-dark"><span><b>${num(done)}</b><small>из ${num(goal)} ${esc(unitOf(e))}</small></span><i class="bar"><i style="width:${pctOf(e)}%"></i></i><small>${pace}</small></div>
      <div class="d-stats"><span><b>${num(e.participant_count)}</b><small>${plural(Number(e.participant_count) || 0, 'участник', 'участника', 'участников')}</small></span><span><b>${pctOf(e)}%</b><small>цели</small></span></div>
      <div class="d-rows"><span><em>Считаем</em>${e.metric === 'custom' ? `своё · ${esc(unitOf(e))}` : m.label}</span>${e.metric === 'custom' ? `<span><em>Записей участников</em>${num(e.contribution_count)}</span>` : ''}<span><em>Рейтинг</em>${Number(e.leaderboard_enabled) === 0 ? 'выключен' : 'включён'}</span><span><em>Имена в рейтинге</em>${({ names: 'полностью', initials: 'инициалы', anonymous: 'анонимно' })[e.results_visibility] || 'полностью'}</span><span><em>Ручная проверка</em>${Number(e.moderation) ? (pending ? `<a class="link" href="#moderation">${num(pending)} ${plural(pending, 'заявка ждёт', 'заявки ждут', 'заявок ждут')}${icon('chev')}</a>` : 'включена') : 'выключена'}</span><span><em>Напомнить за день до конца</em>${Number(e.remind_before_end) === 0 ? 'нет' : 'да'}</span>${e.description ? `<span><em>Описание</em><span style="padding:0;border:0;text-align:right;max-width:200px">${esc(e.description)}</span></span>` : ''}</div>
      <div class="d-actions"><a class="btn black" href="#events/${e.id}/edit">${icon('pen')}Изменить</a>${st === 'active' || st === 'published' ? `<button type="button" class="btn" data-pause="${e.id}">${icon('pause')}Пауза</button>` : st === 'paused' ? `<button type="button" class="btn" data-resume="${e.id}">${icon('play')}Продолжить</button>` : ''}<button type="button" class="btn danger icon" style="flex:none;width:46px" data-delete-event="${e.id}" aria-label="Удалить событие">${icon('trash')}</button></div>
      ${st === 'active' ? `<button type="button" class="btn" data-remind="${e.id}">${icon('send')}Напомнить участникам</button>` : ''}`;
  }
  function openMenu(button) {
    document.querySelector('.menu')?.remove();
    const id = button.dataset.menu, e = data.events.find(x => String(x.id) === id); if (!e) return;
    const menu = document.createElement('div'); menu.className = 'menu'; menu.setAttribute('role', 'menu');
    const st = stateOf(e);
    menu.innerHTML = `<button type="button" role="menuitem" data-edit="${id}">${icon('pen')}Изменить</button><button type="button" role="menuitem" data-copy="${id}">${icon('copy')}Дублировать</button>${st === 'active' || st === 'published' ? `<button type="button" role="menuitem" data-pause="${id}">${icon('pause')}Пауза</button>` : st === 'paused' ? `<button type="button" role="menuitem" data-resume="${id}">${icon('play')}Продолжить</button>` : ''}${st === 'active' ? `<button type="button" role="menuitem" data-remind="${id}">${icon('send')}Напомнить</button>` : ''}<button type="button" role="menuitem" class="danger" data-delete-event="${id}">${icon('trash')}Удалить</button>`;
    button.parentElement.append(menu); menu.querySelector('button').focus();
  }
  async function deleteEvent(id) {
    const e = data.events.find(x => String(x.id) === String(id)); if (!e) return;
    if (!await confirmDialog({ title: 'Удалить событие?', text: `«${e.title}» исчезнет из приложения вместе с прогрессом участников. Это нельзя отменить.` })) return;
    try { await req(`admin/events/${id}`, { method: 'DELETE' }); ev.selected = null; await loadEvents(true); paintEvents(); toast('Событие удалено'); }
    catch (error) { toast(error.message); }
  }

  async function setPaused(id, paused) {
    document.querySelector('.menu')?.remove();
    const e = data.events.find(x => String(x.id) === String(id)); if (!e) return;
    if (paused && !await confirmDialog({ title: 'Поставить на паузу?', text: `«${e.title}» останется в приложении с пометкой «На паузе», но вступить в него будет нельзя. Отметки участников продолжат считаться.`, action: 'Пауза', danger: false, iconName: 'pause' })) return;
    try { await send(`admin/events/${id}/pause`, 'POST', { paused }); await loadEvents(true); if (location.hash.startsWith('#events')) paintEvents(); toast(paused ? 'Событие на паузе' : 'Событие снова идёт'); }
    catch (error) { toast(error.message); }
  }
  async function remind(id) {
    document.querySelector('.menu')?.remove();
    const e = (data.events || []).find(x => String(x.id) === String(id)); if (!e) return;
    if (!await confirmDialog({ title: 'Напомнить участникам?', text: `Участники «${e.title}» с включёнными уведомлениями получат push. Отправить можно раз в час.`, action: 'Отправить', danger: false, iconName: 'send' })) return;
    try { const r = await send(`admin/events/${id}/remind`, 'POST', {}); toast(r.targets ? `Отправлено: ${r.sent} из ${r.targets}` : 'Ни у кого из участников не включены уведомления'); }
    catch (error) { toast(error.message); }
  }

  /* ---------- Editor ---------- */
  async function showEditor(id, copyFrom) {
    loading(id ? 'Изменить событие' : 'Новое событие');
    let settings, item = null;
    try {
      [settings] = await Promise.all([data.settings || req('admin/settings'), loadEvents(!data.events)]);
      data.settings = settings;
      if (id || copyFrom) { item = data.events.find(e => String(e.id) === String(id || copyFrom)); if (!item) throw Error('Событие не найдено.'); }
    } catch (error) { failed('Событие', error); return; }
    const start = new Date(), end = new Date(); end.setDate(end.getDate() + 13);
    const v = item ? { ...item, title: copyFrom ? item.title + ' (копия)' : item.title } : { title: '', description: '', mode: 'common_goal', metric: 'salawat', goal: 100000, starts_on: dayKey(start), ends_on: dayKey(end), leaderboard_enabled: settings.defaultLeaderboard ? 1 : 0, results_visibility: settings.defaultVisibility || 'names', moderation: settings.defaultModeration ? 1 : 0, remind_before_end: 1, custom_unit: '', custom_step: 1, status: 'draft' };
    if (copyFrom) { v.starts_on = dayKey(start); v.ends_on = dayKey(end); }
    const editing = Boolean(id), published = editing && (item.status === 'published' || item.status === 'paused');
    const metricCard = (key, m) => `<label class="metric"><input type="radio" name="metric" value="${key}" ${v.metric === key ? 'checked' : ''}><i>${icon(m.icon)}</i>${m.label}</label>`;
    const optionCard = (val, label) => `<label class="option"><input type="radio" name="visibility" value="${val}" ${(v.results_visibility || 'names') === val ? 'checked' : ''}>${label}</label>`;
    mount(`<header class="top"><div class="top-back"><a class="btn-glass" href="#events${editing ? '/' + id : ''}" aria-label="Назад к событиям">${icon('back')}</a><div class="top-copy"><span>События${editing ? ' · ' + (published ? 'опубликовано' : 'черновик') : ''}</span><h1>${editing ? 'Изменить событие' : 'Новое событие'}</h1></div></div>
      <div class="top-actions">${published ? '' : '<button type="submit" form="event-form" class="btn" data-status="draft">Сохранить черновик</button>'}<button type="submit" form="event-form" class="btn black" data-status="published">${published ? 'Сохранить' : 'Опубликовать'}</button></div></header>
      <div class="editor">
        <form id="event-form" class="editor-form" novalidate>
          <section class="glass card"><div class="card-head"><h2>Основное</h2></div>
            <label class="field"><span>Название</span><input class="input" name="title" maxlength="120" required value="${esc(v.title)}" placeholder="Например, Салават всей общиной"><small>Видно в приложении в карточке события</small></label>
            <label class="field"><span>Описание</span><textarea class="input" name="description" maxlength="800" placeholder="Расскажите участникам, к чему вы идёте вместе">${esc(v.description)}</textarea></label>
            <div class="field"><span>Режим</span><div class="choice-row">
              <label class="choice"><input type="radio" name="mode" value="common_goal" ${v.mode !== 'personal_path' ? 'checked' : ''}><b>Общая цель</b><small>Все идут к одной цели, прогресс суммируется</small></label>
              <label class="choice"><input type="radio" name="mode" value="personal_path" ${v.mode === 'personal_path' ? 'checked' : ''}><b>Личный путь</b><small>Каждый выбирает свою цель и идёт сам</small></label>
            </div></div>
          </section>
          <section class="glass card"><div class="card-head"><h2>Цель и сроки</h2></div>
            <div class="field"><span>Что считаем</span><div class="metrics">${Object.entries(METRICS).map(([k, m]) => metricCard(k, m)).join('')}</div><small id="metric-note"></small></div>
            <div id="custom-fields" class="grid3" style="grid-template-columns:minmax(0,2fr) minmax(0,1fr)" hidden>
              <label class="field"><span>В чём считаем</span><input class="input" name="customUnit" maxlength="16" value="${esc(v.custom_unit || '')}" placeholder="км, стр., раз, шт."><span class="units">${['раз', 'км', 'стр.', 'шт.', 'мин', 'ч'].map(u => `<button type="button" data-unit="${u}">${u}</button>`).join('')}</span></label>
              <label class="field"><span>Шаг кнопок</span><input class="input" name="customStep" type="number" min="1" max="10000" step="1" value="${Number(v.custom_step) || 1}"><small>Быстрые кнопки: +шаг, +5 шагов, +10 шагов</small></label>
            </div>
            <div class="grid3">
              <label class="field"><span>Цель</span><input class="input" name="goal" type="number" min="1" step="1" required value="${Number(v.goal) || 1}"><small id="goal-unit"></small></label>
              <label class="field"><span>Начало</span><input class="input" name="startsOn" type="date" required value="${esc(String(v.starts_on).slice(0, 10))}"></label>
              <label class="field"><span>Окончание</span><input class="input" name="endsOn" type="date" required value="${esc(String(v.ends_on).slice(0, 10))}"><small id="length"></small></label>
            </div>
            <div id="goal-hint" class="hint" hidden></div>
          </section>
          <section class="glass card"><div class="card-head"><h2>Рейтинг</h2></div>
            <label class="toggle"><div><b>Рейтинг внутри события</b><small>Участники видят места и очки</small></div><input type="checkbox" name="leaderboard" ${Number(v.leaderboard_enabled) === 0 ? '' : 'checked'}><span class="switch" aria-hidden="true"></span></label>
            <div class="field"><span>Как показывать участников</span><div class="options">${optionCard('names', 'Имя')}${optionCard('initials', 'Инициалы')}${optionCard('anonymous', 'Анонимно')}</div></div>
          </section>
          <section class="glass card"><div class="card-head"><h2>Участники и напоминания</h2></div>
            <div><label class="toggle"><div><b>Ручная проверка</b><small>Новые участники ждут вашего одобрения в «Модерации»; до него их отметки не засчитываются</small></div><input type="checkbox" name="moderation" ${Number(v.moderation) ? 'checked' : ''}><span class="switch" aria-hidden="true"></span></label>
            <label class="toggle"><div><b>Напомнить за день до конца</b><small>Push участникам накануне последнего дня, в 18:00</small></div><input type="checkbox" name="remind" ${Number(v.remind_before_end) === 0 ? '' : 'checked'}><span class="switch" aria-hidden="true"></span></label></div>
          </section>
          <p id="form-error" class="error" role="alert"></p>
        </form>
        <div class="preview" aria-hidden="true"><span>${icon('phone')}Так увидят в приложении</span><div class="phone"><h3>События</h3><div class="p-card"><div class="p-head"><span id="pv-mode"></span><span id="pv-left"></span></div><div class="p-body"><span><span class="circle" id="pv-icon"></span><b id="pv-title"></b></span><small id="pv-desc"></small><i class="bar"><i style="width:${editing ? pctOf(item) : 0}%"></i></i><div class="p-foot"><b id="pv-goal"></b><span class="muted">${editing ? num(item.participant_count) : 0} участников</span></div><span class="p-btn">Участвовать</span></div></div></div></div>
      </div>`, 'editor');
    const form = $('#event-form');
    function syncPreview() {
      const f = new FormData(form), custom = f.get('metric') === 'custom', base = METRICS[f.get('metric')] || METRICS.salawat, m = { ...base, unit: custom ? (f.get('customUnit') || '').trim() || 'раз' : base.unit }, goal = Number(f.get('goal')) || 0, mode = f.get('mode');
      $('#custom-fields').hidden = !custom;
      $('#metric-note').textContent = custom ? 'Не связано с делами приложения: участники сами добавляют свой вклад в карточке события' : f.get('metric') === 'book_pages' ? 'Считается автоматически из «Книги» в делах дня' : 'Считается автоматически из дел дня';
      form.querySelectorAll('[data-unit]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.unit === (f.get('customUnit') || '').trim())));
      const s = f.get('startsOn'), e = f.get('endsOn'), length = s && e ? daysBetween(s, e) + 1 : 0;
      $('#pv-title').textContent = f.get('title').trim() || 'Название события';
      $('#pv-desc').textContent = f.get('description').trim() || 'Описание события';
      $('#pv-mode').textContent = MODES[mode];
      $('#pv-left').textContent = length > 0 ? `${length} ${plural(length, 'день', 'дня', 'дней')}` : '';
      $('#pv-icon').innerHTML = icon(m.icon);
      $('#pv-goal').textContent = mode === 'personal_path' ? 'Своя цель у каждого' : `${editing ? num(progressOf(item)) : 0} из ${num(goal)}`;
      $('#goal-unit').textContent = mode === 'personal_path' ? `${m.unit} — подсказка участникам` : `${m.unit} на всех`;
      $('#length').textContent = length > 0 ? `${length} ${plural(length, 'день', 'дня', 'дней')}` : 'позже начала';
      const hint = $('#goal-hint');
      if (mode !== 'personal_path' && length > 0 && goal > 0) { hint.hidden = false; hint.className = 'hint'; hint.innerHTML = `${icon('check')}<span>Это ~${num(Math.ceil(goal / length))} ${m.unit} в день на всё сообщество.</span>`; }
      else if (length <= 0 && s && e) { hint.hidden = false; hint.className = 'hint warn'; hint.innerHTML = `${icon('alert')}<span>Дата окончания должна быть не раньше начала.</span>`; }
      else hint.hidden = true;
    }
    form.addEventListener('input', syncPreview); form.addEventListener('change', syncPreview); syncPreview();
    form.addEventListener('click', event => { const u = event.target.closest('[data-unit]'); if (!u) return; form.customUnit.value = u.dataset.unit; syncPreview(); });
    form.addEventListener('submit', async event => {
      event.preventDefault();
      const f = new FormData(form), button = event.submitter, chosen = button?.dataset.status || 'published', status = chosen === 'published' && editing && item.status === 'paused' ? 'paused' : chosen;
      const body = { title: f.get('title').trim(), description: f.get('description').trim(), goal: Math.round(Number(f.get('goal'))), startsOn: f.get('startsOn'), endsOn: f.get('endsOn'), metric: f.get('metric'), mode: f.get('mode'), status, leaderboardEnabled: f.get('leaderboard') === 'on', resultsVisibility: f.get('visibility'), moderation: f.get('moderation') === 'on', remindBeforeEnd: f.get('remind') === 'on', customUnit: (f.get('customUnit') || '').trim(), customStep: Math.round(Number(f.get('customStep')) || 1) };
      const error = !body.title ? 'Введите название события.' : body.metric === 'custom' && !body.customUnit ? 'Укажите, в чём считаем: например «км» или «раз».' : !(body.goal >= 1) ? 'Цель должна быть больше нуля.' : !body.startsOn || !body.endsOn || body.endsOn < body.startsOn ? 'Проверьте даты: окончание не раньше начала.' : '';
      $('#form-error').textContent = error; if (error) return;
      document.querySelectorAll('[form=event-form]').forEach(b => { b.disabled = true; });
      try {
        const saved = editing ? await send(`admin/events/${id}`, 'PUT', body) : await send('admin/events', 'POST', body);
        await loadEvents(true);
        toast(status === 'draft' ? 'Черновик сохранён' : editing ? 'Изменения сохранены' : 'Событие опубликовано'); data.moderation = null;
        location.hash = `#events/${editing ? id : saved.id}`;
      } catch (err) { $('#form-error').textContent = err.message; document.querySelectorAll('[form=event-form]').forEach(b => { b.disabled = false; }); }
    });
  }

  /* ---------- Users ---------- */
  const us = { query: '', filter: 'all', selected: null };
  async function showUsers() {
    if (!data.users) loading('Участники');
    try { await Promise.all([loadUsers(!data.users), loadEvents(!data.events)]); } catch (error) { failed('Участники', error); return; }
    paintUsers();
  }
  function paintUsers() {
    const users = data.users, month = new Date(); month.setDate(month.getDate() - 29); const monthKey = dayKey(month);
    const fresh = users.filter(u => String(u.created_at).slice(0, 10) >= monthKey);
    const q = us.query.toLowerCase();
    const base = us.filter === 'new' ? fresh : us.filter === 'events' ? users.filter(u => Number(u.event_count) > 0) : users;
    const list = base.filter(u => !q || `${u.display_name} ${u.login}`.toLowerCase().includes(q));
    const shown = list.slice(0, 200);
    const selected = users.find(u => u.id === us.selected) || null;
    const rows = shown.length ? shown.map(u => `<tr data-user="${esc(u.id)}" aria-selected="${selected && selected.id === u.id}">
        <td class="wide"><span class="cell-main"><span class="avatar">${esc((u.display_name || '?').slice(0, 1))}</span><div><b>${esc(u.display_name)}</b><small>@${esc(u.login || '—')}</small></div></span></td>
        <td class="hide-sm">${relDay(u.created_at)}</td>
        <td class="hide-sm">${u.last_active ? relDay(u.last_active) : '<span class="muted">ещё не отмечал</span>'}</td>
        <td><b>${num(u.points)}</b> <small class="muted">очков</small></td>
        <td class="hide-sm">${num(u.event_count)}</td>
      </tr>`).join('') : `<tr><td colspan="5" class="wide"><div class="empty">${users.length ? 'Никого не нашли.' : 'Участников пока нет.'}</div></td></tr>`;
    mount(top('Сообщество', 'Участники', `${num(users.length)} ${plural(users.length, 'человек', 'человека', 'человек')} · ${num(fresh.length)} новых за 30 дней`, tools() + refreshBtn + `<button type="button" class="btn" data-users-csv>${icon('download')}Выгрузить CSV</button>`) + `
      <div class="split">
        <section class="glass card">
          <div class="toolbar"><div class="seg" role="group" aria-label="Фильтр">${[['all', `Все · ${num(users.length)}`], ['new', `Новые · ${num(fresh.length)}`], ['events', `В событиях · ${num(users.filter(u => Number(u.event_count) > 0).length)}`]].map(([k, l]) => `<button type="button" data-ufilter="${k}" aria-pressed="${us.filter === k}">${l}</button>`).join('')}</div>
            <span><label class="search">${icon('search')}<input id="us-search" type="search" placeholder="Имя или логин" aria-label="Найти участника" value="${esc(us.query)}"></label></span></div>
          <table><thead><tr><th>Участник</th><th>С нами с</th><th>Последняя отметка</th><th>Очки</th><th>События</th></tr></thead><tbody>${rows}</tbody></table>
          ${list.length > shown.length ? `<small class="muted">Показаны первые ${shown.length} из ${num(list.length)} — уточните поиск</small>` : ''}
        </section>
        <aside class="glass card detail" aria-label="Карточка участника">${selected ? userDetail(selected) : `<div class="detail-empty"><span class="circle">${icon('users')}</span>Выберите участника, чтобы увидеть его карточку.</div>`}</aside>
      </div>`, 'users');
  }
  function userDetail(u) {
    const rank = [...data.users].sort((a, b) => (Number(b.points) || 0) - (Number(a.points) || 0)).findIndex(x => x.id === u.id) + 1;
    const joined = new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }).format(parseDay(u.created_at));
    return `<div class="d-close"><span class="cell-main"><span class="avatar" style="width:52px;height:52px;font-size:20px">${esc((u.display_name || '?').slice(0, 1))}</span><div><b style="font-size:20px;letter-spacing:-.02em">${esc(u.display_name)}</b><small>@${esc(u.login || '—')}</small></div></span><button type="button" class="btn small icon" data-close-user aria-label="Закрыть карточку">${icon('x')}</button></div>
      <div class="d-stats three"><span><b>#${rank}</b><small>по очкам</small></span><span><b>${num(u.points)}</b><small>очков</small></span><span><b>${num(u.event_count)}</b><small>${plural(Number(u.event_count) || 0, 'событие', 'события', 'событий')}</small></span></div>
      <div class="d-rows"><span><em>С нами с</em>${joined}</span><span><em>Логин</em>@${esc(u.login || '—')}</span><span><em>Последняя отметка</em>${u.last_active ? relDay(u.last_active) : 'ещё не было'}</span><span><em>Дней с отметками</em>${num(u.active_days)}</span><span><em>Уведомления</em>${Number(u.push_devices) ? `включены · ${num(u.push_devices)} ${plural(Number(u.push_devices), 'устройство', 'устройства', 'устройств')}` : 'выключены'}</span></div>
      <div style="display:flex;flex-direction:column;gap:8px"><button type="button" class="btn" data-reset-pass="${esc(u.id)}">${icon('lock')}Сбросить пароль</button>
      <button type="button" class="btn danger" data-delete-user="${esc(u.id)}">${icon('trash')}Удалить аккаунт</button></div>`;
  }
  async function deleteUser(id) {
    const u = data.users.find(x => x.id === id); if (!u) return;
    if (!await confirmDialog({ title: 'Удалить аккаунт?', text: `Будут удалены аккаунт «${u.display_name}», все его отметки, участие в событиях и напоминания. Это нельзя отменить.` })) return;
    try { await req(`admin/users/${id}`, { method: 'DELETE' }); us.selected = null; await loadUsers(true); data.events = data.groups = null; paintUsers(); toast('Аккаунт удалён'); }
    catch (error) { toast(error.message); }
  }

  /* A temporary password is shown once; the admin passes it on and the member changes nothing else. */
  async function resetPassword(id) {
    const u = data.users.find(x => x.id === id); if (!u) return;
    if (!await confirmDialog({ title: 'Сбросить пароль?', text: `Для «${u.display_name}» создадим временный пароль. Старый перестанет работать, а все устройства выйдут из аккаунта.`, action: 'Сбросить', danger: false, iconName: 'lock' })) return;
    let result; try { result = await send(`admin/users/${id}/password`, 'POST', {}); } catch (error) { toast(error.message); return; }
    const layer = document.createElement('div'); layer.className = 'layer';
    layer.innerHTML = `<section class="dialog" role="dialog" aria-modal="true" aria-labelledby="pw-title"><span class="circle" style="color:var(--green)">${icon('check')}</span><h2 id="pw-title">Новый пароль готов</h2><p>Передайте его участнику лично. Вход: логин <b>@${esc(u.login)}</b> и этот пароль. Больше мы его не покажем.</p><div class="secret"><code>${esc(result.password)}</code><button type="button" class="btn small" data-copy-pass>${icon('copy')}Копировать</button></div><div class="dialog-actions"><button type="button" class="btn black" data-no>Готово</button></div></section>`;
    const close = () => { layer.remove(); document.removeEventListener('keydown', onKey); };
    const onKey = event => { if (event.key === 'Escape') close(); };
    layer.addEventListener('click', async event => {
      if (event.target.closest('[data-no]')) close();
      if (event.target.closest('[data-copy-pass]')) { try { await navigator.clipboard.writeText(result.password); toast('Пароль скопирован'); } catch { toast('Скопируйте вручную'); } }
    });
    document.addEventListener('keydown', onKey); document.body.append(layer); layer.querySelector('[data-copy-pass]').focus();
  }
  function usersCsv() {
    const cell = v => { const s = String(v ?? ''); return /[";\n]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s; };
    const rows = [['Имя', 'Логин', 'Регистрация', 'Последняя отметка', 'Дней с отметками', 'Очки', 'События', 'Уведомления'], ...data.users.map(u => [u.display_name, u.login, String(u.created_at).slice(0, 10), u.last_active ? String(u.last_active).slice(0, 10) : '', Number(u.active_days) || 0, Number(u.points) || 0, Number(u.event_count) || 0, Number(u.push_devices) ? 'да' : 'нет'])];
    const blob = new Blob(['\ufeff' + rows.map(r => r.map(cell).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `chetele-участники-${dayKey()}.csv`; a.click(); setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  }

  /* ---------- Groups ---------- */
  const initials = name => esc((name || '?').slice(0, 1));
  async function showGroups() {
    if (!data.groups) loading('Группы');
    let groups;
    try { groups = await loadGroups(!data.groups); } catch (error) { failed('Группы', error); return; }
    const card = g => {
      const n = Number(g.member_count) || 0, active = Number(g.active_week) || 0, rest = n - (g.preview || []).length;
      const avatars = (g.preview || []).map(name => `<span class="avatar" title="${esc(name)}">${initials(name)}</span>`).join('') + (rest > 0 ? `<span class="avatar more">+${rest}</span>` : '');
      return `<a class="glass group-card" href="#groups/${g.id}">
        <div><h2>${esc(g.name)}</h2>${g.description ? `<p>${esc(g.description)}</p>` : ''}</div>
        <div class="stack" aria-label="${n} ${plural(n, 'участник', 'участника', 'участников')}">${avatars || '<small class="muted">Пока никого</small>'}</div>
        <div class="group-stats">
          <span><small>активны за неделю</small><b>${active} из ${n}</b><i class="bar"><i style="width:${n ? Math.round(active / n * 100) : 0}%"></i></i></span>
          <span><small>дел за неделю</small><b>${num(g.deeds_week)}</b><small>${n ? `≈ ${(Number(g.deeds_week) / n / 7).toLocaleString('ru-RU', { maximumFractionDigits: 1 })} в день на человека` : '—'}</small></span>
        </div>
      </a>`;
    };
    mount(top('Сообщество', 'Группы', groups.length ? `${groups.length} ${plural(groups.length, 'группа', 'группы', 'групп')} · отчёты по каждой` : 'Объедините участников и смотрите отчёт по ним', tools() + refreshBtn + `<a class="btn black" href="#groups/new">${icon('plus')}Создать группу</a>`) + `
      <div class="groups">${groups.map(card).join('')}<a class="glass group-card group-new" href="#groups/new"><span class="circle">${icon('plus')}</span><b>Новая группа</b><small class="muted">Семья, класс, кружок при мечети — кого угодно</small></a></div>`, 'groups');
  }

  async function showGroupEditor(id) {
    loading(id ? 'Изменить группу' : 'Новая группа');
    let users, report = null;
    try {
      [users] = await Promise.all([loadUsers(!data.users)]);
      if (id) report = await req(`admin/groups/${id}/report?days=7`);
    } catch (error) { failed('Группа', error); return; }
    const picked = new Set(report ? report.members.map(m => m.id) : []);
    let query = '';
    const g = report ? report.group : { name: '', description: '' };
    mount(`<header class="top"><div class="top-back"><a class="btn-glass" href="#groups${id ? '/' + id : ''}" aria-label="Назад">${icon('back')}</a><div class="top-copy"><span>Группы</span><h1>${id ? 'Изменить группу' : 'Новая группа'}</h1></div></div>
      <div class="top-actions">${id ? `<button type="button" class="btn danger" data-delete-group="${id}">${icon('trash')}Удалить</button>` : ''}<button type="submit" form="group-form" class="btn black">${id ? 'Сохранить' : 'Создать группу'}</button></div></header>
      <form id="group-form" class="split" novalidate>
        <section class="glass card" style="flex:1">
          <div class="card-head"><h2>Участники</h2><b id="picked-count"></b></div>
          <div class="chips" id="picked"></div>
          <label class="search" style="width:100%">${icon('search')}<input id="pick-search" type="search" placeholder="Найти по имени или логину" aria-label="Найти участника"></label>
          <div class="picker" id="picker" role="group" aria-label="Все участники"></div>
        </section>
        <aside class="glass card detail">
          <div class="card-head"><h2>Группа</h2></div>
          <label class="field"><span>Название</span><input class="input" name="name" maxlength="80" required value="${esc(g.name)}" placeholder="Например, Семья или 9 «Б» класс"></label>
          <label class="field"><span>Описание</span><textarea class="input" name="description" maxlength="300" placeholder="Кто в группе и зачем — видно только вам">${esc(g.description)}</textarea></label>
          <div class="info"><span class="circle">${icon('lock')}</span><div><b>Видно только в админке</b><small>Участники не узнают, что они в группе</small></div></div>
          <p id="group-error" class="error" role="alert"></p>
        </aside>
      </form>`, 'group-editor');
    const byId = new Map(users.map(u => [u.id, u]));
    function paintPicked() {
      $('#picked-count').textContent = `${picked.size} ${plural(picked.size, 'выбран', 'выбрано', 'выбрано')}`;
      $('#picked').innerHTML = [...picked].map(uid => { const u = byId.get(uid); return u ? `<button type="button" class="chip-x" data-unpick="${esc(uid)}" aria-label="Убрать ${esc(u.display_name)}">${esc(u.display_name)}${icon('x')}</button>` : ''; }).join('') || '<small class="muted">Отметьте участников в списке ниже</small>';
    }
    function paintPicker() {
      const q = query.toLowerCase();
      const list = users.filter(u => !q || `${u.display_name} ${u.login}`.toLowerCase().includes(q));
      list.sort((a, b) => (picked.has(b.id) - picked.has(a.id)) || String(a.display_name).localeCompare(String(b.display_name), 'ru'));
      $('#picker').innerHTML = list.slice(0, 300).map(u => `<label class="pick"><span class="avatar">${initials(u.display_name)}</span><div><b>${esc(u.display_name)}</b><small>@${esc(u.login || '—')} · ${num(u.points)} очков</small></div><input type="checkbox" value="${esc(u.id)}" ${picked.has(u.id) ? 'checked' : ''}><span class="tick">${icon('check')}</span></label>`).join('') || '<div class="empty">Никого не нашли.</div>';
    }
    paintPicked(); paintPicker();
    const form = $('#group-form');
    form.addEventListener('change', event => { const box = event.target; if (box.type !== 'checkbox') return; if (box.checked) picked.add(box.value); else picked.delete(box.value); paintPicked(); });
    form.addEventListener('click', event => { const chip = event.target.closest('[data-unpick]'); if (!chip) return; picked.delete(chip.dataset.unpick); paintPicked(); paintPicker(); });
    $('#pick-search').addEventListener('input', event => { query = event.target.value; paintPicker(); });
    form.addEventListener('submit', async event => {
      event.preventDefault();
      const f = new FormData(form), body = { name: f.get('name').trim(), description: f.get('description').trim(), members: [...picked] };
      if (!body.name) { $('#group-error').textContent = 'Введите название группы.'; return; }
      const button = document.querySelector('[form=group-form]'); button.disabled = true;
      try {
        const saved = id ? await send(`admin/groups/${id}`, 'PUT', body) : await send('admin/groups', 'POST', body);
        data.groups = null; toast(id ? 'Группа сохранена' : 'Группа создана');
        location.hash = `#groups/${id || saved.id}`;
      } catch (error) { $('#group-error').textContent = error.message; button.disabled = false; }
    });
  }

  const reportState = { days: 7 };
  async function showGroupReport(id) {
    loading('Отчёт группы');
    let r;
    try { r = await req(`admin/groups/${id}/report?days=${reportState.days}`); } catch (error) { failed('Отчёт группы', error); return; }
    const members = r.members, n = members.length, days = r.days;
    const sum = key => members.reduce((s, m) => s + (Number(m[key]) || 0), 0);
    const active = members.filter(m => Number(m.active_days) > 0).length;
    const deeds = sum('deeds'), perDay = n ? deeds / n / days : 0;
    const byDay = Object.fromEntries(r.daily.map(d => [String(d.date).slice(0, 10), d]));
    const series = Array.from({ length: days }, (_, i) => { const d = parseDay(r.from); d.setDate(d.getDate() + i); const key = dayKey(d); return { key, active: Number(byDay[key]?.active) || 0, deeds: Number(byDay[key]?.deeds) || 0 }; });
    const today = dayKey();
    const quiet = members.filter(m => !m.last_active || daysBetween(String(m.last_active).slice(0, 10), today) >= 3);
    const kpi = (name, value, label, chip) => `<article class="glass kpi"><div class="kpi-top"><span class="circle">${icon(name)}</span>${chip ? `<span class="chip">${chip}</span>` : ''}</div><div><b>${value}</b><small>${label}</small></div></article>`;
    const total = (name, value, label) => `<span><i>${icon(name)}</i><b class="num">${num(value)}</b><small>${label}</small></span>`;
    const last = m => !m.last_active ? '<span class="flag">ни разу</span>' : daysBetween(String(m.last_active).slice(0, 10), today) >= 3 ? `<span class="flag">${relDay(m.last_active)}</span>` : relDay(m.last_active);
    const rows = members.map(m => `<tr>
        <td class="wide"><span class="cell-main"><span class="avatar">${initials(m.display_name)}</span><div><b>${esc(m.display_name)}</b><small>@${esc(m.login || '—')}</small></div></span></td>
        <td><span class="days-cell"><b>${Number(m.active_days)} из ${days}</b><i class="bar"><i style="width:${Math.round(Number(m.active_days) / days * 100)}%"></i></i></span></td>
        <td class="num"><b>${num(m.deeds)}</b></td>
        <td class="num hide-sm">${num(m.salawat)}</td><td class="num hide-sm">${num(m.tasbihat)}</td><td class="num hide-sm">${num(m.quran)}</td><td class="num hide-sm">${num(m.tahajjud)}</td><td class="num hide-sm">${num(m.fast)}</td>
        <td class="hide-sm">${last(m)}</td>
      </tr>`).join('') || `<tr><td colspan="9" class="wide"><div class="empty">В группе пока никого. <a class="link" href="#groups/${id}/edit">Добавить участников${icon('chev')}</a></div></td></tr>`;
    const max = Math.max(1, n);
    const periodLabel = `${shortDate(r.from)} — ${shortDate(r.to)}`;
    mount(`<header class="top"><div class="top-back"><a class="btn-glass" href="#groups" aria-label="К группам">${icon('back')}</a><div class="top-copy"><span>Группы · отчёт за ${periodLabel}</span><h1>${esc(r.group.name)}</h1><p>${n} ${plural(n, 'участник', 'участника', 'участников')}${r.group.description ? ' · ' + esc(r.group.description) : ''}</p></div></div>
      <div class="top-actions"><div class="seg" role="group" aria-label="Период" style="background:rgba(255,255,255,.3)">${[7, 30, 90].map(d => `<button type="button" data-days="${d}" aria-pressed="${days === d}">${d} дней</button>`).join('')}</div><button type="button" class="btn" data-csv>${icon('download')}CSV</button><a class="btn black" href="#groups/${id}/edit">${icon('pen')}Изменить</a></div></header>
      <div class="kpis">
        ${kpi('group', num(n), plural(n, 'участник', 'участника', 'участников'), '')}
        ${kpi('check', `${active} из ${n}`, 'отмечали дела за период', n ? `${Math.round(active / n * 100)}%` : '')}
        ${kpi('flag', perDay.toLocaleString('ru-RU', { maximumFractionDigits: 1 }), 'дел в день на человека', '')}
        ${kpi('rating', num(sum('points')), 'очков за период', '')}
      </div>
      <div class="ov-grid stretch">
        <section class="glass card">
          <div class="card-head"><h2>Сколько человек отмечали дела</h2><small class="muted">из ${n} по дням</small></div>
          <div class="chart${days > 30 ? ' tight' : days > 7 ? ' dense' : ''}" role="img" aria-label="Активные участники по дням">${series.map(d => `<span style="height:${Math.max(2, d.active / max * 100)}%" title="${shortDate(d.key)}: ${d.active} из ${n}, дел ${d.deeds}"></span>`).join('')}</div>
          <div class="chart-axis"><span>${shortDate(r.from)}</span><span>Сегодня · ${series[series.length - 1].active} из ${n}</span></div>
        </section>
        <section class="glass card"><div class="card-head"><h2>Давно не отмечали</h2>${quiet.length ? `<span class="chip" style="background:var(--amber-bg);color:var(--amber)">${quiet.length}</span>` : ''}</div>
          ${quiet.length ? quiet.slice(0, 6).map(m => `<div class="person"><span class="avatar">${initials(m.display_name)}</span><div><b>${esc(m.display_name)}</b><small>${m.last_active ? 'последний раз ' + relDay(m.last_active) : 'ещё ни разу не отмечал(а)'}</small></div></div>`).join('') + (quiet.length > 6 ? `<small class="muted">и ещё ${quiet.length - 6}</small>` : '') : `<div class="all-good">${icon('check')}Все отмечали дела за последние 3 дня.</div>`}
        </section>
      </div>
      <section class="glass card"><div class="card-head"><h2>Итого по группе</h2><small class="muted">${periodLabel}</small></div>
        <div class="totals">${total('salawat', sum('salawat'), 'салават')}${total('zikr', sum('tasbihat'), 'зикр')}${total('quran', sum('quran'), 'стр. Курана')}${total('moon', sum('tahajjud'), 'ночей тахаджуда')}${total('flame', sum('fast'), 'дней поста')}</div>
      </section>
      <section class="glass card"><div class="card-head"><h2>Участники</h2><small class="muted">по числу дел</small></div>
        <table><thead><tr><th>Участник</th><th>Дни с делами</th><th>Дела</th><th>Салават</th><th>Зикр</th><th>Куран</th><th>Тахаджуд</th><th>Пост</th><th>Был(а)</th></tr></thead><tbody>${rows}</tbody></table>
      </section>`, 'group-report-' + id);
    main.querySelectorAll('[data-days]').forEach(b => b.addEventListener('click', () => { reportState.days = Number(b.dataset.days); shownView = ''; showGroupReport(id); }));
    main.querySelector('[data-csv]').addEventListener('click', () => {
      const head = ['Участник', 'Логин', 'Дни с делами', 'Дела', 'Очки', 'Салават', 'Зикр', 'Куран, стр.', 'Тахаджуд', 'Пост', 'Последний раз'];
      const cell = v => `"${String(v ?? '').replace(/"/g, '""')}"`;
      const lines = [head, ...members.map(m => [m.display_name, m.login, `${m.active_days} из ${days}`, m.deeds, m.points, m.salawat, m.tasbihat, m.quran, m.tahajjud, m.fast, m.last_active || ''])].map(row => row.map(cell).join(';'));
      const blob = new Blob(['﻿' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
      const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `${r.group.name} — ${r.from}—${r.to}.csv`; a.click(); setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    });
  }
  async function deleteGroup(id) {
    const g = (data.groups || []).find(x => String(x.id) === String(id));
    if (!await confirmDialog({ title: 'Удалить группу?', text: `Группа${g ? ' «' + g.name + '»' : ''} исчезнет из админки. Участники и их отметки останутся.` })) return;
    try { await req(`admin/groups/${id}`, { method: 'DELETE' }); data.groups = null; toast('Группа удалена'); location.hash = '#groups'; }
    catch (error) { toast(error.message); }
  }

  /* ---------- Moderation ---------- */
  async function showModeration() {
    if (!data.moderation) loading('Модерация');
    try { await Promise.all([loadModeration(true), loadEvents(!data.events)]); } catch (error) { failed('Модерация', error); return; }
    paintModeration();
  }
  function paintModeration() {
    const list = data.moderation, byEvent = new Map();
    list.forEach(r => { if (!byEvent.has(r.event_id)) byEvent.set(r.event_id, []); byEvent.get(r.event_id).push(r); });
    const moderated = (data.events || []).filter(e => Number(e.moderation) && stateOf(e) !== 'finished');
    const groups = [...byEvent.entries()].map(([eventId, rows]) => {
      const first = rows[0], m = METRICS[first.metric] || METRICS.salawat;
      return `<section class="glass card mod-group"><div class="mod-head"><span class="cell-main"><span class="circle">${icon(m.icon)}</span><div><b>${esc(first.title)}</b><small>${MODES[first.mode] || ''} · ${shortDate(first.starts_on)} — ${shortDate(first.ends_on)} · ${num(rows.length)} ${plural(rows.length, 'заявка', 'заявки', 'заявок')}</small></div></span>
        <span class="mod-actions">${rows.length > 1 ? `<button type="button" class="btn small ok" data-approve-all="${eventId}">${icon('check')}Одобрить всех</button>` : ''}</span></div>
        ${rows.map(r => `<div class="mod-row"><span class="avatar">${esc((r.display_name || '?').slice(0, 1))}</span><div><b>${esc(r.display_name)}</b><small>@${esc(r.login)} · заявка ${relDay(r.joined_at)} · уже ${num(r.progress)} ${esc(r.metric === 'custom' ? r.custom_unit || 'раз' : m.unit)}${r.personal_target ? ` · цель ${num(r.personal_target)}` : ''}</small></div>
          <span class="mod-actions"><button type="button" class="btn small ok" data-approve="${eventId}|${esc(r.user_id)}">${icon('check')}<span class="hide-xs">Одобрить</span></button><button type="button" class="btn small danger icon" data-reject="${eventId}|${esc(r.user_id)}" aria-label="Отклонить заявку ${esc(r.display_name)}">${icon('x')}</button></span></div>`).join('')}
      </section>`;
    }).join('');
    const empty = `<section class="glass card"><div class="all-good">${icon('check')}Все заявки проверены.</div><p class="muted" style="margin:0;font-size:14px;line-height:1.5">${moderated.length ? `Ручная проверка включена в ${moderated.length} ${plural(moderated.length, 'событии', 'событиях', 'событиях')}: ${moderated.map(e => `«${esc(e.title)}»`).join(', ')}. Новые участники появятся здесь.` : 'Ручную проверку можно включить в настройках события — тогда новые участники будут ждать одобрения здесь, а их отметки начнут засчитываться после него.'}</p></section>`;
    mount(top('Управление', 'Модерация', list.length ? `${num(list.length)} ${plural(list.length, 'заявка ждёт', 'заявки ждут', 'заявок ждут')} проверки` : 'Новых заявок нет', tools() + refreshBtn) + (groups || empty) +
      `<p class="muted" style="margin:0 4px;font-size:13px;color:rgba(16,22,26,.6)">После одобрения отметки участника засчитываются с момента заявки. Отклонённый участник видит «Заявка отклонена» и не попадает в рейтинг.</p>`, 'moderation');
  }
  async function decide(decision, items) {
    if (decision === 'reject' && !await confirmDialog({ title: items.length > 1 ? 'Отклонить заявки?' : 'Отклонить заявку?', text: 'Участник увидит «Заявка отклонена», его отметки не попадут в событие.', action: 'Отклонить', iconName: 'x' })) return;
    try { await send('admin/moderation', 'POST', { decision, items }); await Promise.all([loadModeration(true), loadEvents(true)]); paintModeration(); toast(decision === 'approve' ? (items.length > 1 ? `Одобрено: ${items.length}` : 'Участник одобрен') : 'Заявка отклонена'); }
    catch (error) { toast(error.message); }
  }

  /* ---------- Tests ---------- */
  const QZ_CATS = ['Намаз', 'Куран', 'Пост', 'Сира', 'Основы', 'Другое'];
  const QZ_LEVELS = ['Начинающий', 'Знающий', 'Хорошие знания', 'Отлично'];
  const QZ_STATUS = { published: ['Опубликован', 'active'], draft: ['Черновик', 'draft'], hidden: ['Скрыт', 'paused'] };
  const qzPill = s => `<span class="pill ${(QZ_STATUS[s] || QZ_STATUS.draft)[1]}">${(QZ_STATUS[s] || QZ_STATUS.draft)[0]}</span>`;
  const qzWord = n => `${n} ${plural(n, 'вопрос', 'вопроса', 'вопросов')}`;
  const QZ_TIMES = [10, 15, 20, 30, 45, 60, 90, 120];
  const timeMode = () => !qe.questionTimeSec ? '' : QZ_TIMES.includes(qe.questionTimeSec) && !qe.customTime ? String(qe.questionTimeSec) : 'custom';
  const qzTime = q => q.questionTimeSec ? ` · ${q.questionTimeSec} сек на ответ` : '';
  const qz = { tab: 'all', selected: null, details: {} };
  async function loadQuizzes(force) { if (force || !data.quizzes) data.quizzes = await req('admin/quizzes'); $('#nav-quizzes').textContent = data.quizzes.quizzes.filter(q => q.status === 'published').length || ''; return data.quizzes; }
  async function showQuizzes(selectedId) {
    if (selectedId !== undefined) qz.selected = selectedId;
    if (!data.quizzes) loading('Тесты');
    try { await loadQuizzes(!data.quizzes); } catch (error) { failed('Тесты', error); return; }
    paintQuizzes();
    if (qz.selected && !qz.details[qz.selected]) loadQuizDetail(qz.selected);
    /* A running live quiz shows on the button in the header. */
    req('admin/live').then(body => { const was = lvActive(lv.data); lv.data = body.live; lv.recent = body.recent || []; if (was !== lvActive(lv.data) && location.hash.startsWith('#quizzes') && shownView === 'quizzes') paintQuizzes(); }).catch(() => {});
  }
  async function loadQuizDetail(id) {
    try { qz.details[id] = await req(`admin/quizzes/${id}`); } catch (error) { qz.details[id] = { error: error.message }; }
    if (location.hash.startsWith('#quizzes') && String(qz.selected) === String(id)) paintQuizzes();
  }
  function paintQuizzes() {
    const { quizzes, summary } = data.quizzes, counts = { all: quizzes.length, published: 0, draft: 0, hidden: 0 };
    quizzes.forEach(q => { counts[q.status] = (counts[q.status] || 0) + 1; });
    const list = quizzes.filter(q => qz.tab === 'all' || q.status === qz.tab);
    const selected = quizzes.find(q => String(q.id) === String(qz.selected)) || null;
    const kpi = (name, value, label, chip) => `<article class="glass kpi"><div class="kpi-top"><span class="circle">${icon(name)}</span>${chip ? `<span class="chip">${chip}</span>` : ''}</div><div><b>${value}</b><small>${label}</small></div></article>`;
    const rows = list.length ? list.map(q => `<tr data-quiz="${q.id}" aria-selected="${selected && selected.id === q.id}">
        <td class="wide"><span class="cell-main"><span class="circle">${icon('brain')}</span><div><b>${esc(q.title)}${q.featured ? ' <span class="chip" style="height:22px;font-size:11px;margin-left:4px">тест недели</span>' : ''}</b><small>${esc(q.category)} · ${qzWord(q.questionCount)}${qzTime(q)}</small></div></span></td>
        <td class="hide-sm"><b>${num(q.attempts)}</b></td>
        <td>${q.attempts ? `<span class="prog"><b>${q.avg}%</b><i class="bar"><i style="width:${q.avg}%"></i></i></span>` : '<small class="muted">пока нет</small>'}</td>
        <td>${qzPill(q.status)}</td></tr>`).join('') : `<tr><td colspan="4" class="wide"><div class="empty">${quizzes.length ? 'В этой вкладке тестов нет.' : 'Тестов пока нет. Создайте первый — участники увидят его во вкладке «Тесты».'}</div></td></tr>`;
    const published = counts.published || 0;
    mount(top('Знания', 'Тесты', `${num(quizzes.length)} ${plural(quizzes.length, 'тест', 'теста', 'тестов')} · ${num(summary.attempts30)} ${plural(summary.attempts30, 'прохождение', 'прохождения', 'прохождений')} за 30 дней`, tools() + refreshBtn + `<a class="btn" href="#quizzes/live">${lvActive(lv.data) ? '<span class="lv-dot"></span>Идёт живой квиз' : `${icon('users')}Живой квиз`}</a><a class="btn black" href="#quizzes/new">${icon('plus')}Создать тест</a>`) + `
      <div class="kpis">
        ${kpi('brain', num(published), plural(published, 'тест опубликован', 'теста опубликовано', 'тестов опубликовано'), counts.draft ? `+${counts.draft} ${plural(counts.draft, 'черновик', 'черновика', 'черновиков')}` : '')}
        ${kpi('check', num(summary.attempts30), 'прохождений за 30 дней', summary.people30 ? `${num(summary.people30)} ${plural(summary.people30, 'человек', 'человека', 'человек')}` : '')}
        ${kpi('rating', summary.avg === null ? '—' : summary.avg + '%', 'средний результат', '')}
        ${kpi('users', num(summary.peopleAll), 'прошли хотя бы один', summary.users ? `${Math.round(summary.peopleAll / summary.users * 100)}% участников` : '')}
      </div>
      <div class="split">
        <section class="glass card">
          <div class="toolbar"><div class="seg" role="group" aria-label="Статус">${[['all', 'Все'], ['published', 'Опубликованы'], ['draft', 'Черновики'], ['hidden', 'Скрытые']].map(([k, l]) => `<button type="button" data-qz-tab="${k}" aria-pressed="${qz.tab === k}">${l} · ${counts[k] || 0}</button>`).join('')}</div></div>
          <table><thead><tr><th>Тест</th><th>Прохождений</th><th>Средний результат</th><th>Статус</th></tr></thead><tbody>${rows}</tbody></table>
        </section>
        <aside class="glass card detail" aria-label="Карточка теста">${selected ? quizDetail(selected) : `<div class="detail-empty"><span class="circle">${icon('brain')}</span>Выберите тест, чтобы увидеть, как его проходят и где ошибаются.</div>`}</aside>
      </div>`, 'quizzes');
  }
  function quizDetail(q) {
    const d = qz.details[q.id], st = d && !d.error ? d.stats : null;
    let body = '<div class="empty" style="padding:20px">Загружаем статистику…</div>';
    if (d && d.error) body = `<div class="empty" style="padding:20px">${esc(d.error)}</div>`;
    else if (st && !st.attempts) body = '<div class="empty" style="padding:20px">Тест ещё никто не прошёл.</div>';
    else if (st) {
      const total = d.questions.length, values = Array.from({ length: total + 1 }, (_, i) => Number(st.distribution[i]) || 0), max = Math.max(1, ...values);
      const mins = st.medianSec === null ? '—' : `${Math.floor(st.medianSec / 60)}:${String(st.medianSec % 60).padStart(2, '0')}`;
      body = `<div class="d-dark"><span><b>${st.avg}%</b><small>средний результат</small></span><small>Сколько правильных ответов набрали участники</small>
          <span aria-hidden="true" style="height:90px;align-items:flex-end;gap:4px">${values.map((v, i) => `<span style="flex:1;height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:4px" title="${i} из ${total}: ${v}"><i style="display:block;width:100%;height:${Math.max(3, v / max * 100)}%;border-radius:4px 4px 1px 1px;background:${i >= Math.ceil(total * 0.8) ? '#fff' : 'rgba(255,255,255,.28)'}"></i><small style="font-size:10px">${i}</small></span>`).join('')}</span></div>
        <div class="d-stats three"><span><b>${num(st.attempts)}</b><small>прохождений</small></span><span><b>${num(st.perfect)}</b><small>на ${total} из ${total}</small></span><span><b>${mins}</b><small>в среднем</small></span></div>
        ${st.hardest.some(h => h.wrongPercent > 0) ? `<div><b style="font-size:15px;font-weight:600">Чаще всего ошибаются</b>${st.hardest.filter(h => h.wrongPercent > 0).slice(0, 3).map(h => `<div style="display:flex;flex-direction:column;gap:6px;padding:10px 0;border-bottom:1px solid rgba(16,22,26,.06)"><span style="display:flex;justify-content:space-between;gap:10px;font-size:14px"><span>${esc(h.text)}</span><b style="font-weight:600;color:var(--amber);white-space:nowrap">${h.wrongPercent}%</b></span><i class="bar"><i style="width:${h.wrongPercent}%;background:#C7782A"></i></i></div>`).join('')}</div>` : ''}`;
    }
    const toggle = q.status === 'published' ? `<button type="button" class="btn" data-qz-status="hidden" data-id="${q.id}">${icon('eyeoff')}Скрыть</button>` : `<button type="button" class="btn" data-qz-status="published" data-id="${q.id}">${icon('check')}Опубликовать</button>`;
    return `<div class="d-close">${qzPill(q.status)}<button type="button" class="btn small icon" data-qz-close aria-label="Закрыть карточку">${icon('x')}</button></div>
      <div class="d-title"><b>${esc(q.title)}</b><small>${esc(q.category)} · ${qzWord(q.questionCount)}${qzTime(q)}${q.featured ? ' · тест недели' : ''}</small></div>
      ${body}
      ${q.status === 'published' && q.questionCount ? `<button type="button" class="btn ok" data-lv-launch="${q.id}">${icon('users')}Провести вживую</button>` : ''}<div class="d-actions"><a class="btn black" href="#quizzes/${q.id}/edit">${icon('pen')}Изменить</a>${toggle}<button type="button" class="btn danger icon" style="flex:none;width:46px" data-qz-delete="${q.id}" aria-label="Удалить тест">${icon('trash')}</button></div>`;
  }
  async function setQuizStatus(id, status) {
    try { await send(`admin/quizzes/${id}/status`, 'POST', { status }); await loadQuizzes(true); delete qz.details[id]; paintQuizzes(); loadQuizDetail(id); toast(status === 'published' ? 'Тест опубликован' : 'Тест скрыт из приложения'); }
    catch (error) { toast(error.message); }
  }
  async function deleteQuiz(id) {
    const q = data.quizzes.quizzes.find(x => String(x.id) === String(id)); if (!q) return;
    if (!await confirmDialog({ title: 'Удалить тест?', text: `«${q.title}» исчезнет из приложения вместе с результатами участников. Это нельзя отменить.` })) return;
    try { await req(`admin/quizzes/${id}`, { method: 'DELETE' }); qz.selected = null; await loadQuizzes(true); history.replaceState(null, '', '#quizzes'); paintQuizzes(); toast('Тест удалён'); }
    catch (error) { toast(error.message); }
  }

  /* Editor: text fields update the draft as you type; adding, removing, moving and
     choosing the right answer repaint the questions. */
  let qe = null;
  const blankQuestion = () => ({ id: null, text: '', options: ['', '', ''], correct: -1, explanation: '', open: true });
  async function showQuizEditor(id) {
    loading(id ? 'Изменить тест' : 'Новый тест');
    try {
      if (id) { const q = await req(`admin/quizzes/${id}`); qe = { ...q, questions: q.questions.map(x => ({ ...x, options: x.options.slice(), open: false })) }; if (qe.questions[0]) qe.questions[0].open = true; }
      else qe = { id: null, title: '', description: '', category: 'Основы', status: 'draft', featured: false, showAnswers: true, shuffleQuestions: true, shuffleOptions: false, showStats: true, timeLimitMin: null, levels: QZ_LEVELS.slice(), questionTimeSec: null, questions: [blankQuestion()] };
    } catch (error) { failed('Тест', error); return; }
    paintQuizEditor();
  }
  function questionCard(q, i) {
    const n = qe.questions.length, filled = q.options.filter(o => o.trim()).length;
    const head = `<div style="display:flex;align-items:center;gap:10px"><span style="width:30px;height:30px;flex:none;border-radius:15px;background:${q.open ? 'var(--black)' : 'rgba(16,22,26,.06)'};color:${q.open ? '#fff' : 'var(--ink)'};display:grid;place-items:center;font-size:13px;font-weight:600">${i + 1}</span>
      <button type="button" data-qe-toggle="${i}" style="flex:1;min-width:0;border:0;background:none;padding:0;text-align:left;font-size:15px;font-weight:${q.open ? 600 : 500};overflow:hidden;text-overflow:ellipsis;white-space:nowrap" aria-expanded="${q.open}">${q.text.trim() ? esc(q.text) : `Вопрос ${i + 1}`}</button>
      ${q.open ? '' : `<small class="muted" style="white-space:nowrap">${filled} ${plural(filled, 'вариант', 'варианта', 'вариантов')}${q.correct < 0 ? ' · <b style="color:var(--amber)">нет верного</b>' : ''}</small>`}
      <button type="button" class="btn small icon" data-qe-move="${i}|-1" aria-label="Выше" ${i === 0 ? 'disabled' : ''}>${icon('up')}</button>
      <button type="button" class="btn small icon" data-qe-move="${i}|1" aria-label="Ниже" ${i === n - 1 ? 'disabled' : ''}>${icon('down')}</button>
      ${q.open ? `<button type="button" class="btn small icon" data-qe-dup="${i}" aria-label="Дублировать вопрос">${icon('copy')}</button><button type="button" class="btn small icon danger" data-qe-del="${i}" aria-label="Удалить вопрос">${icon('trash')}</button>` : ''}</div>`;
    if (!q.open) return `<div style="padding:12px 14px;border-radius:22px;background:rgba(255,255,255,.8);border:1px solid rgba(16,22,26,.06)">${head}</div>`;
    const opts = q.options.map((o, k) => `<div style="display:flex;align-items:center;gap:10px">
        <label style="display:flex;align-items:center;cursor:pointer"><input type="radio" name="qe-right-${i}" data-qe-right="${i}|${k}" ${q.correct === k ? 'checked' : ''} aria-label="Вариант ${'АБВГДЕ'[k]} — верный ответ" style="width:20px;height:20px;accent-color:#1F6B4F;margin:0"></label>
        <input class="input" data-qe-opt="${i}|${k}" maxlength="200" value="${esc(o)}" placeholder="Вариант ${'АБВГДЕ'[k]}" aria-label="Вариант ${'АБВГДЕ'[k]}" style="flex:1;${q.correct === k ? 'border-color:#2F7F64;background:rgba(47,155,110,.08)' : ''}">
        ${q.correct === k ? '<span class="chip">Верный</span>' : ''}
        <button type="button" class="btn small icon" data-qe-optdel="${i}|${k}" aria-label="Удалить вариант" ${q.options.length <= 2 ? 'disabled' : ''}>${icon('x')}</button></div>`).join('');
    return `<div style="padding:18px;border-radius:24px;background:#fff;border:1.5px solid var(--black);display:flex;flex-direction:column;gap:14px">${head}
      <label class="field"><span>Текст вопроса</span><textarea class="input" data-qe-text="${i}" maxlength="600" placeholder="Например: сколько обязательных намазов в сутки?" style="height:72px">${esc(q.text)}</textarea></label>
      <div class="field"><span>Варианты ответа · отметьте верный кружком</span>${opts}${q.options.length < 6 ? `<button type="button" class="btn small" style="align-self:flex-start" data-qe-optadd="${i}">${icon('plus')}Добавить вариант</button>` : ''}</div>
      <label class="field"><span>Пояснение после ответа</span><textarea class="input" data-qe-expl="${i}" maxlength="800" placeholder="Коротко: почему этот ответ верный" style="height:64px">${esc(q.explanation)}</textarea><small>Участник увидит его сразу после ответа и в разборе ошибок</small></label></div>`;
  }
  function paintQuizEditor() {
    const editing = Boolean(qe.id), published = qe.status === 'published', n = qe.questions.length;
    const a = Math.ceil(n * 0.5), b = Math.ceil(n * 0.8), r = (x, y) => x > y ? '—' : x === y ? String(x) : `${x}–${y}`;
    const ranges = [r(0, a - 1), r(a, b - 1), r(b, n - 1), String(n)];
    const tog = (key, label, text) => `<label class="toggle"><div><b>${label}</b><small>${text}</small></div><input type="checkbox" data-qe-flag="${key}" ${qe[key] ? 'checked' : ''}><span class="switch" aria-hidden="true"></span></label>`;
    mount(`<header class="top"><div class="top-back"><a class="btn-glass" href="#quizzes${editing ? '/' + qe.id : ''}" aria-label="Назад к тестам">${icon('back')}</a><div class="top-copy"><span>Тесты${editing ? ' · ' + (QZ_STATUS[qe.status] || QZ_STATUS.draft)[0].toLowerCase() : ''}</span><h1>${editing ? 'Изменить тест' : 'Новый тест'}</h1></div></div>
      <div class="top-actions">${published ? '' : '<button type="button" class="btn" data-qe-save="draft">Сохранить черновик</button>'}<button type="button" class="btn black" data-qe-save="published">${published ? 'Сохранить' : 'Опубликовать'}</button></div></header>
      <div class="editor quiz-editor">
        <div class="editor-form">
          <section class="glass card"><div class="card-head"><h2>Основное</h2></div>
            <label class="field"><span>Название</span><input class="input" data-qe-field="title" maxlength="120" value="${esc(qe.title)}" placeholder="Например, Основы намаза"></label>
            <label class="field"><span>Описание</span><textarea class="input" data-qe-field="description" maxlength="600" placeholder="О чём тест и кому он полезен">${esc(qe.description)}</textarea></label>
            <div class="grid3" style="grid-template-columns:repeat(2,minmax(0,1fr))"><label class="field"><span>Тема</span><select class="select input" data-qe-field="category" style="height:48px;border-radius:18px">${QZ_CATS.map(c => `<option ${c === qe.category ? 'selected' : ''}>${c}</option>`).join('')}</select></label>
            <label class="toggle" style="border:0;align-self:end"><div><b>Тест недели</b><small>Большая карточка вверху вкладки</small></div><input type="checkbox" data-qe-flag="featured" ${qe.featured ? 'checked' : ''}><span class="switch" aria-hidden="true"></span></label></div>
            <div class="grid3" style="grid-template-columns:repeat(2,minmax(0,1fr))"><label class="field"><span>Время на ответ</span><select class="select input" data-qe-timemode style="height:48px;border-radius:18px">${[['', 'Без ограничения'], ...QZ_TIMES.map(s => [String(s), `${s} секунд`]), ['custom', 'Своё значение…']].map(([v, l]) => `<option value="${v}" ${timeMode() === v ? 'selected' : ''}>${l}</option>`).join('')}</select><small>${qe.questionTimeSec ? 'Не успел — ответ не засчитывается' : 'Отвечать можно сколько угодно'}</small></label>
            ${timeMode() === 'custom' ? `<label class="field"><span>Секунд на вопрос</span><input class="input" type="number" min="5" max="600" step="1" data-qe-time value="${qe.questionTimeSec || ''}"><small>От 5 до 600 секунд</small></label>` : ''}</div>
          </section>
          <section class="glass card"><div class="card-head"><h2>Вопросы · ${n}</h2><small class="muted">стрелками меняйте порядок</small></div>
            <div style="display:flex;flex-direction:column;gap:10px">${qe.questions.map(questionCard).join('')}</div>
            <button type="button" data-qe-add style="height:56px;border-radius:22px;border:2px dashed rgba(16,22,26,.16);background:rgba(255,255,255,.35);color:var(--soft);font-size:15px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px">${icon('plus')}Добавить вопрос</button>
          </section>
          <p id="qe-error" class="error" role="alert"></p>
        </div>
        <div class="preview quiz-side">
          <section class="glass card"><div class="card-head"><h2>Правила</h2></div><div>${tog('showAnswers', 'Показывать ответ сразу', 'Верный вариант и пояснение после каждого вопроса')}${tog('shuffleQuestions', 'Перемешивать вопросы', 'У каждого участника свой порядок')}${tog('shuffleOptions', 'Перемешивать варианты', 'Варианты ответа меняются местами')}${tog('showStats', 'Показывать статистику', 'Участники видят, сколько человек прошли и средний результат')}</div></section>
          <section class="glass card"><div class="card-head"><h2>Уровни по результату</h2></div><small class="muted" style="margin-top:-8px">Сколько правильных ответов — такой уровень увидит участник</small>
            ${qe.levels.map((l, i) => `<div style="display:flex;align-items:center;gap:10px"><span style="width:64px;height:40px;flex:none;border-radius:14px;background:rgba(16,22,26,.05);display:grid;place-items:center;font-size:14px;font-weight:600">${ranges[i]}</span><input class="input" data-qe-level="${i}" maxlength="40" value="${esc(l)}" aria-label="Название уровня ${i + 1}" style="height:40px"></div>`).join('')}</section>
          <div class="info" style="background:rgba(78,92,190,.08)"><span class="circle" style="color:var(--indigo)">${icon('alert')}</span><div><b>Проверьте перед публикацией</b><small>У каждого вопроса один верный ответ. Тексты по фикху лучше показать знающему человеку.</small></div></div>
        </div>
      </div>`, 'quiz-editor');
  }
  function quizErrors() {
    if (!qe.title.trim()) return 'Введите название теста.';
    if (qe.questionTimeSec && (qe.questionTimeSec < 5 || qe.questionTimeSec > 600)) return 'Время на ответ — от 5 до 600 секунд.';
    for (const [i, q] of qe.questions.entries()) {
      if (!q.text.trim()) return `Вопрос ${i + 1}: введите текст.`;
      if (q.options.filter(o => o.trim()).length < 2) return `Вопрос ${i + 1}: нужно хотя бы два варианта ответа.`;
      if (q.correct < 0 || !q.options[q.correct]?.trim()) return `Вопрос ${i + 1}: отметьте верный ответ.`;
    }
    return '';
  }
  async function saveQuiz(status, button) {
    const error = status === 'published' && !qe.questions.length ? 'Добавьте хотя бы один вопрос.' : quizErrors();
    $('#qe-error').textContent = error; if (error) return;
    /* Empty options are dropped; the right answer keeps pointing at the same text. */
    const questions = qe.questions.map(q => { const kept = q.options.map((o, k) => [o.trim(), k]).filter(([o]) => o); return { id: q.id, text: q.text.trim(), options: kept.map(([o]) => o), correct: kept.findIndex(([, k]) => k === q.correct), explanation: q.explanation.trim() }; });
    const body = { title: qe.title.trim(), description: qe.description.trim(), category: qe.category, status, featured: qe.featured, showAnswers: qe.showAnswers, shuffleQuestions: qe.shuffleQuestions, shuffleOptions: qe.shuffleOptions, showStats: qe.showStats, timeLimitMin: null, questionTimeSec: qe.questionTimeSec || null, levels: qe.levels, questions };
    button.disabled = true;
    try {
      const saved = qe.id ? await send(`admin/quizzes/${qe.id}`, 'PUT', body) : await send('admin/quizzes', 'POST', body);
      const id = qe.id || saved.id; delete qz.details[id]; data.quizzes = null;
      toast(status === 'draft' ? 'Черновик сохранён' : qe.status === 'published' ? 'Изменения сохранены' : 'Тест опубликован');
      location.hash = `#quizzes/${id}`;
    } catch (err) { $('#qe-error').textContent = err.message; button.disabled = false; }
  }
  main.addEventListener('input', event => {
    if (!qe || shownView !== 'quiz-editor') return;
    const t = event.target, d = t.dataset;
    if (d.qeField) qe[d.qeField] = t.value;
    else if (d.qeText !== undefined) qe.questions[+d.qeText].text = t.value;
    else if (d.qeExpl !== undefined) qe.questions[+d.qeExpl].explanation = t.value;
    else if (d.qeOpt) { const [i, k] = d.qeOpt.split('|').map(Number); qe.questions[i].options[k] = t.value; }
    else if (d.qeLevel !== undefined) qe.levels[+d.qeLevel] = t.value;
    else if (d.qeTime !== undefined) qe.questionTimeSec = Math.round(Number(t.value)) || null;
  });
  main.addEventListener('change', event => {
    if (!qe || shownView !== 'quiz-editor') return;
    const t = event.target, d = t.dataset;
    if (d.qeFlag) { qe[d.qeFlag] = t.checked; return; }
    if (d.qeField === 'category') qe.category = t.value;
    if (d.qeTimemode !== undefined) { qe.customTime = t.value === 'custom'; qe.questionTimeSec = t.value === 'custom' ? (qe.questionTimeSec || 25) : t.value ? Number(t.value) : null; paintQuizEditor(); return; }
    if (d.qeRight) { const [i, k] = d.qeRight.split('|').map(Number); qe.questions[i].correct = k; paintQuizEditor(); }
  });
  main.addEventListener('click', event => {
    const t = event.target;
    const row = t.closest('tr[data-quiz]'); if (row) { qz.selected = row.dataset.quiz; history.replaceState(null, '', `#quizzes/${row.dataset.quiz}`); paintQuizzes(); if (!qz.details[qz.selected]) loadQuizDetail(qz.selected); if (innerWidth <= 1240) $('.detail')?.scrollIntoView({ behavior: 'smooth' }); return; }
    const tab = t.closest('[data-qz-tab]'); if (tab) { qz.tab = tab.dataset.qzTab; paintQuizzes(); return; }
    if (t.closest('[data-qz-close]')) { qz.selected = null; history.replaceState(null, '', '#quizzes'); paintQuizzes(); return; }
    const st = t.closest('[data-qz-status]'); if (st) { setQuizStatus(st.dataset.id, st.dataset.qzStatus); return; }
    const del = t.closest('[data-qz-delete]'); if (del) { deleteQuiz(del.dataset.qzDelete); return; }
    if (!qe || shownView !== 'quiz-editor') return;
    const save = t.closest('[data-qe-save]'); if (save) { saveQuiz(save.dataset.qeSave, save); return; }
    if (t.closest('[data-qe-add]')) { qe.questions.forEach(q => { q.open = false; }); qe.questions.push(blankQuestion()); paintQuizEditor(); requestAnimationFrame(() => { const last = main.querySelectorAll('[data-qe-text]'); last[last.length - 1]?.focus(); }); return; }
    const tg = t.closest('[data-qe-toggle]'); if (tg) { const i = +tg.dataset.qeToggle, open = !qe.questions[i].open; qe.questions.forEach(q => { q.open = false; }); qe.questions[i].open = open; paintQuizEditor(); return; }
    const mv = t.closest('[data-qe-move]'); if (mv) { const [i, dir] = mv.dataset.qeMove.split('|').map(Number), j = i + dir; if (j < 0 || j >= qe.questions.length) return; [qe.questions[i], qe.questions[j]] = [qe.questions[j], qe.questions[i]]; paintQuizEditor(); return; }
    const dup = t.closest('[data-qe-dup]'); if (dup) { const i = +dup.dataset.qeDup, q = qe.questions[i]; qe.questions.forEach(x => { x.open = false; }); qe.questions.splice(i + 1, 0, { ...q, id: null, options: q.options.slice(), open: true }); paintQuizEditor(); return; }
    const rm = t.closest('[data-qe-del]'); if (rm) { const i = +rm.dataset.qeDel; qe.questions.splice(i, 1); if (qe.questions[i]) qe.questions[i].open = true; else if (qe.questions[i - 1]) qe.questions[i - 1].open = true; paintQuizEditor(); return; }
    const oa = t.closest('[data-qe-optadd]'); if (oa) { qe.questions[+oa.dataset.qeOptadd].options.push(''); paintQuizEditor(); return; }
    const od = t.closest('[data-qe-optdel]'); if (od) { const [i, k] = od.dataset.qeOptdel.split('|').map(Number), q = qe.questions[i]; q.options.splice(k, 1); if (q.correct === k) q.correct = -1; else if (q.correct > k) q.correct--; paintQuizEditor(); return; }
  });

  /* ---------- Settings ---------- */
  async function showSettings() {
    if (!data.settings) loading('Настройки');
    let s;
    try { s = await req('admin/settings'); data.settings = s; } catch (error) { failed('Настройки', error); return; }
    const zones = [['Asia/Bishkek', 'Бишкек · UTC+6'], ['Asia/Almaty', 'Алматы · UTC+5'], ['Asia/Tashkent', 'Ташкент · UTC+5'], ['Europe/Moscow', 'Москва · UTC+3']];
    if (!zones.some(([z]) => z === s.timezone)) zones.unshift([s.timezone, s.timezone]);
    const opt = (val, label) => `<label class="option"><input type="radio" name="visibility" value="${val}" ${s.defaultVisibility === val ? 'checked' : ''}>${label}</label>`;
    const sys = s.system || {}, cron = sys.cron, cronState = cronStatus(cron);
    mount(top('Кабинет', 'Настройки', 'Правила новых событий и доступ', tools()) + `
      <div class="settings">
        <form id="settings-form" class="glass card"><div class="card-head"><h2>Новые события по умолчанию</h2></div>
          <label class="field"><span>Часовой пояс</span><select class="select input" name="timezone" style="height:48px;border-radius:18px">${zones.map(([z, l]) => `<option value="${esc(z)}" ${z === s.timezone ? 'selected' : ''}>${esc(l)}</option>`).join('')}</select><small>Даты начала и окончания событий считаются по нему</small></label>
          <div class="field"><span>Как показывать участников в рейтинге</span><div class="options">${opt('names', 'Имя')}${opt('initials', 'Инициалы')}${opt('anonymous', 'Анонимно')}</div></div>
          <div><label class="toggle"><div><b>Рейтинг внутри события</b><small>Включён в новых событиях</small></div><input type="checkbox" name="leaderboard" ${s.defaultLeaderboard ? 'checked' : ''}><span class="switch" aria-hidden="true"></span></label>
          <label class="toggle"><div><b>Ручная проверка участников</b><small>Включена в новых событиях: заявки ждут одобрения в «Модерации»</small></div><input type="checkbox" name="moderation" ${s.defaultModeration ? 'checked' : ''}><span class="switch" aria-hidden="true"></span></label></div>
          <div class="form-foot"><button type="submit" class="btn black">Сохранить</button><p id="settings-error" class="error" role="alert"></p></div>
        </form>
        <div class="settings-side">
          <section class="glass card"><div class="card-head"><h2>Доступ</h2></div>
            <div class="info"><span class="circle">${icon('lock')}</span><div><b>Логин и пароль</b><small>Задаются в api/config.php на сервере. В браузере пароль хранится только до закрытия вкладки.</small></div></div>
            <button type="button" class="btn danger" data-logout>${icon('logout')}Выйти из кабинета</button>
          </section>
          <section class="glass card"><div class="card-head"><h2>Приложение</h2></div>
            <div class="d-rows"><span><em>Версия</em><b id="sw-version">—</b></span><span><em>Push-подписок</em><b>${num(sys.pushSubscriptions)}${sys.pushUsers ? ` <small class="muted" style="font-weight:500">· ${num(sys.pushUsers)} ${plural(Number(sys.pushUsers), 'человек', 'человека', 'человек')}</small>` : ''}</b></span><span><em>Напоминания</em>каждую минуту · cron</span><span><em>Последний запуск cron</em><b style="${cronState.bad ? 'color:var(--red)' : ''}">${cronState.text}</b></span>${cron && cron.error ? `<span><em>Ошибка cron</em><small style="max-width:200px;text-align:right;color:var(--red)">${esc(cron.error)}</small></span>` : ''}<span><em>Адрес</em><a href="/" target="_blank" rel="noopener">chetele.ru</a></span></div>
          </section>
          <section class="glass card"><div class="card-head"><h2>Защита входа</h2></div>
            <div class="info"><span class="circle">${icon('shield')}</span><div><b>После 5 неверных паролей — пауза 15 минут</b><small>Блокируется только адрес, с которого подбирают пароль. Ваш вход с другого адреса продолжает работать.</small></div></div>
            <div class="d-rows"><span><em>Неудачных входов за сутки</em><b style="${Number(sys.failedLogins24h) >= 3 ? 'color:var(--amber)' : ''}">${num(sys.failedLogins24h)}</b></span>${Number(sys.failedLogins24h) ? `<span><em>С разных адресов</em>${num(sys.failedIps24h)}</span>` : ''}<span><em>Последняя неудачная</em>${sys.lastFailedAt ? relDay(sys.lastFailedAt) + ', ' + String(sys.lastFailedAt).slice(11, 16) : 'не было'}</span></div>
          </section>
        </div>
      </div>`, 'settings');
    paintVersion();
    $('#settings-form').addEventListener('submit', async event => {
      event.preventDefault();
      const f = new FormData(event.target), button = event.submitter || event.target.querySelector('[type=submit]'); button.disabled = true;
      try { await send('admin/settings', 'PUT', { timezone: f.get('timezone'), defaultVisibility: f.get('visibility'), defaultLeaderboard: f.get('leaderboard') === 'on', defaultModeration: f.get('moderation') === 'on' }); data.settings = null; toast('Настройки сохранены'); }
      catch (error) { $('#settings-error').textContent = error.message; }
      finally { button.disabled = false; }
    });
  }
  /* The cron runs every minute; a report older than 10 minutes means it stopped. */
  function cronStatus(cron) {
    if (!cron || !cron.at) return { text: 'нет данных', bad: true };
    const at = new Date(cron.at), minutes = Math.round((Date.now() - at) / 6e4);
    const time = new Intl.DateTimeFormat('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }).format(at);
    return { text: minutes < 2 ? `только что · ${time}` : minutes < 60 ? `${minutes} мин назад` : time, bad: minutes > 10 || Boolean(cron.error), minutes };
  }
  /* The app's version is its service-worker cache name. */
  let version = '';
  async function paintVersion() {
    if (!version) { try { const text = await (await fetch('/sw.js', { cache: 'no-store' })).text(); version = (text.match(/CACHE_NAME\s*=\s*'([^']+)'/) || [])[1] || ''; } catch {} }
    if (!version) return;
    const short = version.replace(/^chetele-static-/, '');
    $('#app-version').textContent = `Сайт работает · ${short}`;
    const el = $('#sw-version'); if (el) el.textContent = version;
  }

  /* ---------- Notifications ---------- */
  /* What needs the admin: waiting participants, events about to end, a stopped cron. */
  function alerts() {
    const list = [], today = dayKey();
    const waiting = data.moderation || [];
    if (waiting.length) list.push(['amber', 'shield', `${num(waiting.length)} ${plural(waiting.length, 'заявка ждёт', 'заявки ждут', 'заявок ждут')} проверки`, 'Ручная модерация', '#moderation']);
    (data.events || []).filter(e => stateOf(e) === 'active' && daysBetween(today, e.ends_on) <= 3).forEach(e => { const left = daysBetween(today, e.ends_on); list.push(['indigo', 'clock', `«${esc(e.title)}» ${left ? `кончится через ${left} ${plural(left, 'день', 'дня', 'дней')}` : 'кончается сегодня'}`, e.mode === 'personal_path' ? 'Личный путь' : `Цель выполнена на ${pctOf(e)}%`, `#events/${e.id}`]); });
    (data.events || []).filter(e => stateOf(e) === 'active' && !(Number(e.participant_count) > 0)).forEach(e => list.push(['amber', 'users', `В «${esc(e.title)}» пока нет участников`, 'Событие уже идёт — расскажите о нём сообществу', `#events/${e.id}`]));
    (data.events || []).filter(e => stateOf(e) === 'draft').forEach(e => list.push(['', 'pen', `Черновик «${esc(e.title)}»`, 'Ещё не опубликован', `#events/${e.id}/edit`]));
    const sys = data.settings?.system;
    /* Wrong admin passwords: after 5 in a row an address is locked for 15 minutes. */
    if (sys && Number(sys.failedLogins24h) >= 3) list.push(['amber', 'lock', `${num(sys.failedLogins24h)} ${plural(Number(sys.failedLogins24h), 'неудачная попытка', 'неудачные попытки', 'неудачных попыток')} входа за сутки`, `С ${num(sys.failedIps24h)} ${plural(Number(sys.failedIps24h), 'адреса', 'адресов', 'адресов')}. Если это не вы — проверьте, что пароль длинный и случайный`, '#settings']);
    const cron = data.settings?.system?.cron;
    if (data.settings?.system && cronStatus(cron).bad) list.push(['', 'alert', 'Напоминания не отправляются', cron?.error ? esc(cron.error) : `Cron давно не запускался: ${cronStatus(cron).text}`, '#settings']);
    return list;
  }
  function paintBell() {
    const n = alerts().length;
    document.querySelectorAll('#bell-dot').forEach(dot => { dot.hidden = !n; dot.textContent = n > 9 ? '9+' : n; });
  }
  async function openBell() {
    if (document.querySelector('.panel')) { closePanel(); return; }
    const panel = document.createElement('section'); panel.className = 'panel'; panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-label', 'Уведомления');
    panel.innerHTML = '<h2>Уведомления</h2><div class="empty" style="padding:20px">Проверяем…</div>';
    document.body.append(panel);
    try { const [, , s] = await Promise.all([loadModeration(true), loadEvents(!data.events), req('admin/settings')]); data.settings = s; } catch (error) { panel.innerHTML = `<h2>Уведомления</h2><div class="empty">${esc(error.message)}</div>`; return; }
    if (!panel.isConnected) return;
    const list = alerts(); paintBell();
    panel.innerHTML = '<h2>Уведомления</h2>' + (list.length ? list.map(([tone, ic, title, text, href]) => `<a class="todo ${tone}" href="${href}"><span class="circle">${icon(ic)}</span><div><b>${title}</b><small>${text}</small></div>${icon('chev')}</a>`).join('') : `<div class="all-good">${icon('check')}Ничего нового — всё под контролем.</div>`);
  }
  const closePanel = () => document.querySelector('.panel')?.remove();

  /* ---------- Search ---------- */
  async function openSearch() {
    if (document.querySelector('.search-layer')) return;
    closePanel();
    const layer = document.createElement('div'); layer.className = 'layer search-layer';
    layer.innerHTML = `<section class="search-box" role="dialog" aria-modal="true" aria-label="Поиск"><label class="search">${icon('search')}<input type="search" placeholder="Участник, событие или группа" aria-label="Что найти" autocomplete="off"></label><div class="results" role="listbox"><div class="empty" style="padding:18px">Загружаем…</div></div></section>`;
    const close = () => { layer.remove(); document.removeEventListener('keydown', onKey); };
    const onKey = event => { if (event.key === 'Escape') close(); };
    layer.addEventListener('click', event => {
      if (event.target === layer) { close(); return; }
      const hit = event.target.closest('[data-go]'); if (!hit) return;
      const [kind, id] = hit.dataset.go.split(':');
      close();
      if (kind === 'user') { us.selected = id; us.query = ''; us.filter = 'all'; if (location.hash === '#users') paintUsers(); else location.hash = '#users'; }
      else if (kind === 'event') { ev.tab = 'all'; ev.query = ''; location.hash = `#events/${id}`; }
      else location.hash = `#groups/${id}`;
    });
    document.addEventListener('keydown', onKey);
    document.body.append(layer);
    const input = layer.querySelector('input'), results = layer.querySelector('.results'); input.focus();
    try { await Promise.all([loadUsers(!data.users), loadEvents(!data.events), loadGroups(!data.groups)]); } catch (error) { results.innerHTML = `<div class="empty">${esc(error.message)}</div>`; return; }
    const paint = () => {
      const q = input.value.trim().toLowerCase();
      if (!q) { results.innerHTML = `<div class="empty" style="padding:18px">${num(data.users.length)} ${plural(data.users.length, 'участник', 'участника', 'участников')}, ${num(data.events.length)} ${plural(data.events.length, 'событие', 'события', 'событий')}, ${num(data.groups.length)} ${plural(data.groups.length, 'группа', 'группы', 'групп')}</div>`; return; }
      const users = data.users.filter(u => `${u.display_name} ${u.login}`.toLowerCase().includes(q)).slice(0, 6);
      const events = data.events.filter(e => `${e.title} ${e.description || ''}`.toLowerCase().includes(q)).slice(0, 5);
      const groups = data.groups.filter(g => `${g.name} ${g.description || ''}`.toLowerCase().includes(q)).slice(0, 5);
      const row = (go, lead, title, sub) => `<button type="button" class="result" data-go="${go}">${lead}<div><b>${title}</b><small>${sub}</small></div>${icon('chev')}</button>`;
      results.innerHTML = (users.length ? '<h3>Участники</h3>' + users.map(u => row(`user:${esc(u.id)}`, `<span class="avatar">${esc((u.display_name || '?').slice(0, 1))}</span>`, esc(u.display_name), `@${esc(u.login)} · ${num(u.points)} очков`)).join('') : '')
        + (events.length ? '<h3>События</h3>' + events.map(e => row(`event:${e.id}`, `<span class="circle">${icon(metricOf(e).icon)}</span>`, esc(e.title), `${STATE_LABEL[stateOf(e)]} · ${period(e)}`)).join('') : '')
        + (groups.length ? '<h3>Группы</h3>' + groups.map(g => row(`group:${g.id}`, `<span class="circle">${icon('group')}</span>`, esc(g.name), `${num(g.member_count)} ${plural(Number(g.member_count) || 0, 'участник', 'участника', 'участников')}`)).join('') : '')
        || '<div class="empty" style="padding:18px">Ничего не нашли.</div>';
    };
    input.addEventListener('input', paint); paint();
    input.addEventListener('keydown', event => { if (event.key === 'Enter') results.querySelector('[data-go]')?.click(); });
  }

  /* ---------- Live quiz: the host's console ---------- */
  /* The admin opens a lobby for a published test, starts it, shows each answer and opens
     the next question. The page asks the server every 1.5 s while a quiz runs; only the
     parts that changed are rewritten, so nothing flickers. */
  const lv = { data: null, recent: [], key: '', parts: {}, deadline: 0, poll: null, tick: null, busy: false, fresh: false, form: { quizId: '', timeSec: 20, push: true } };
  const LV_TIMES = [10, 15, 20, 30, 45, 60];
  const LV_LETTERS = ['А', 'Б', 'В', 'Г', 'Д', 'Е'];
  const lvActive = d => d && ['lobby', 'question', 'reveal'].includes(d.status);
  async function showLive() {
    if (!lv.data && shownView !== 'live') loading('Живой квиз');
    try { await Promise.all([loadQuizzes(!data.quizzes), lvFetch()]); } catch (error) { failed('Живой квиз', error); return; }
    lv.key = ''; paintLive(); lvSchedule();
  }
  async function lvFetch() {
    const body = await req('admin/live');
    if (lvActive(body.live) || (lv.data && lv.data.id === body.live?.id)) lv.fresh = false;
    lv.data = body.live; lv.recent = body.recent || [];
    if (lv.data && lv.data.status === 'question') { const deadline = Date.now() + (lv.data.remainingMs || 0); if (Math.abs(deadline - lv.deadline) > 1200 || lv.key !== lvKey()) lv.deadline = deadline; }
    paintLiveNav();
  }
  function paintLiveNav() { const el = $('#nav-live-dot'); if (el) el.hidden = !lvActive(lv.data); }
  const lvKey = () => lv.fresh || !lv.data ? 'setup' : `${lv.data.id}:${lv.data.status}:${lv.data.index}`;
  function lvSchedule() {
    clearTimeout(lv.poll); lv.poll = null;
    if (!location.hash.startsWith('#quizzes/live') || !lvActive(lv.data) || lv.fresh) return;
    lv.poll = setTimeout(async () => { try { await lvFetch(); if (location.hash.startsWith('#quizzes/live')) paintLive(); } catch {} lvSchedule(); }, 1500);
  }
  async function lvAction(action) {
    if (lv.busy || !lv.data) return;
    if (action === 'cancel' || action === 'finish') {
      const ok = await confirmDialog(action === 'cancel' ? { title: 'Отменить квиз?', text: 'Участники увидят, что квиз отменён. Итоги подводиться не будут.', action: 'Отменить квиз' } : { title: 'Завершить квиз сейчас?', text: 'Оставшиеся вопросы не будут заданы. Участники сразу увидят пьедестал по набранным очкам.', action: 'Завершить', danger: false, iconName: 'rating' });
      if (!ok) return;
    }
    lv.busy = true; paintLive();
    try {
      const body = await send(`admin/live/${lv.data.id}/${action}`, 'POST', {});
      if (action === 'push') toast(body.sent ? `Push отправлен: ${num(body.sent)}` : 'Нет подписчиков на уведомления');
      else { lv.data = body.live; if (lv.data.status === 'question') lv.deadline = Date.now() + (lv.data.remainingMs || 0); }
    } catch (error) { toast(error.message); }
    lv.busy = false; paintLiveNav(); paintLive(); lvSchedule();
  }
  async function lvCreate() {
    if (lv.busy) return;
    const quizId = Number(lv.form.quizId); if (!quizId) { toast('Выберите тест'); return; }
    lv.busy = true; paintLive();
    try {
      const body = await send('admin/live', 'POST', { quizId, timeSec: lv.form.timeSec, push: lv.form.push });
      toast(body.push ? `Лобби открыто · push: ${num(body.push.sent)}` : 'Лобби открыто');
      lv.fresh = false; await lvFetch();
    } catch (error) { toast(error.message); }
    lv.busy = false; paintLive(); lvSchedule();
  }
  /* Rewrites only the parts whose markup changed; a new step redraws the page. */
  function paintLive() {
    if (!location.hash.startsWith('#quizzes/live')) return;
    const parts = liveParts(), key = lvKey();
    if (key !== lv.key || shownView !== 'live') {
      lv.key = key; lv.parts = parts;
      mount(parts.top + `<div data-lv="body">${parts.body}</div>`, 'live');
    } else if (parts.body !== lv.parts.body) {
      const fresh = document.createElement('div'); fresh.innerHTML = parts.body;
      const now = main.querySelector('[data-lv="body"]');
      fresh.querySelectorAll('[data-lv-part]').forEach(el => { const old = now?.querySelector(`[data-lv-part="${el.dataset.lvPart}"]`); if (old && old.innerHTML !== el.innerHTML) old.innerHTML = el.innerHTML; });
      lv.parts = parts;
    }
    lvTick();
  }
  /* The countdown ring moves on its own between requests. */
  function lvTick() {
    clearInterval(lv.tick); lv.tick = null;
    const d = lv.data; if (!d || d.status !== 'question' || lv.fresh) return;
    const C = 2 * Math.PI * 46;
    const update = () => {
      const ring = main.querySelector('[data-lv-ring]'); if (!ring) { clearInterval(lv.tick); lv.tick = null; return; }
      const ms = Math.max(0, lv.deadline - Date.now()), share = ms / (d.timeSec * 1000);
      ring.querySelector('b').textContent = Math.ceil(ms / 1000);
      ring.querySelector('circle + circle').setAttribute('stroke-dasharray', `${(C * share).toFixed(1)} ${C.toFixed(1)}`);
      if (ms <= 0) { clearInterval(lv.tick); lv.tick = null; setTimeout(() => lvFetch().then(paintLive).catch(() => {}), 1800); }
    };
    update(); lv.tick = setInterval(update, 250);
  }
  function liveParts() {
    const d = lv.data, back = `<a class="btn" href="#quizzes">${icon('back')}К тестам</a>`;
    const published = (data.quizzes?.quizzes || []).filter(q => q.status === 'published' && q.questionCount > 0);
    if (lv.fresh || !d) {
      if (!lv.form.quizId && published.length) lv.form.quizId = String((published.find(q => q.featured) || published[0]).id);
      const chosen = published.find(q => String(q.id) === String(lv.form.quizId));
      const recent = lv.recent.length ? lv.recent.map(r => `<div class="lv-row"><span class="circle">${icon('rating')}</span><div><b>${esc(r.title)}</b><small>${relDay(r.finishedAt)} · ${num(r.players)} ${plural(r.players, 'участник', 'участника', 'участников')}${r.winner ? ` · победитель: ${esc(r.winner)}` : ''}</small></div></div>`).join('') : '<div class="empty" style="padding:20px">Живых квизов ещё не было.</div>';
      return {
        top: top('Тесты · живой режим', 'Живой квиз', 'Все участники проходят тест одновременно', tools() + back),
        body: `<div class="split"><section class="glass card" style="flex:1">
            <h2 class="lv-h">Новый живой квиз</h2>
            ${published.length ? `<label class="field"><span>Тест</span><select data-lv-quiz>${published.map(q => `<option value="${q.id}"${String(q.id) === String(lv.form.quizId) ? ' selected' : ''}>${esc(q.title)} · ${qzWord(q.questionCount)}</option>`).join('')}</select><small>Вопросы идут в порядке теста, все видят один и тот же вопрос.</small></label>
            <div class="field"><span>Время на ответ</span><div class="options">${LV_TIMES.map(t => `<label class="option"><input type="radio" name="lv-time" value="${t}" data-lv-time${t === lv.form.timeSec ? ' checked' : ''}>${t} сек</label>`).join('')}</div><small>Верный ответ даёт от 500 до 1000 очков — чем быстрее, тем больше. Неверный или запоздавший — 0.</small></div>
            <label class="toggle"><div><b>Push всем участникам</b><small>«Живой квиз начинается» — уведомление получат все, кто их включил</small></div><input type="checkbox" data-lv-push${lv.form.push ? ' checked' : ''}><span class="switch" aria-hidden="true"></span></label>
            <div class="lv-actions"><button type="button" class="btn black" data-lv-create${lv.busy ? ' disabled' : ''}>${icon('users')}Открыть лобби</button>${chosen ? `<small class="muted">${qzWord(chosen.questionCount)} · около ${Math.max(1, Math.round(chosen.questionCount * (lv.form.timeSec + 8) / 60))} мин</small>` : ''}</div>`
            : '<div class="empty">Нет опубликованных тестов с вопросами. Опубликуйте тест, чтобы провести его вживую.</div>'}
          </section>
          <aside class="glass card detail"><h2 class="lv-h">Как это работает</h2>
            <ol class="lv-steps"><li><b>Откройте лобби.</b> Во вкладке «Тесты» у всех появится карточка LIVE — участники подключаются одним нажатием.</li><li><b>Начните квиз,</b> когда все соберутся. Вопрос появляется у всех одновременно, с таймером.</li><li><b>Когда время выйдет,</b> ответ откроется сам. Можно показать его и раньше.</li><li><b>Откройте следующий вопрос.</b> После последнего — пьедестал с тремя победителями.</li></ol>
            <h2 class="lv-h" style="margin-top:6px">Прошедшие</h2>${recent}
          </aside></div>`
      };
    }

    const statusText = { lobby: 'лобби — ждём участников', question: `вопрос ${d.index + 1} из ${d.total}`, reveal: `ответ на вопрос ${d.index + 1} из ${d.total}`, finished: 'завершён', cancelled: 'отменён' }[d.status];
    const kpi = (name, value, label, chip) => `<article class="glass kpi"><div class="kpi-top"><span class="circle">${icon(name)}</span>${chip ? `<span class="chip">${chip}</span>` : ''}</div><div><b>${value}</b><small>${label}</small></div></article>`;
    const r = d.reveal || { counts: [], right: 0, correct: -1 };
    const kpis = `<div class="kpis" data-lv-part="kpis">${kpi('users', num(d.players), plural(d.players, 'участник в игре', 'участника в игре', 'участников в игре'))}${kpi('check', d.status === 'question' || d.status === 'reveal' ? `${num(d.answered || 0)} из ${num(d.players)}` : '—', 'ответили на вопрос')}${kpi('rating', d.status === 'question' || d.status === 'reveal' ? `${num(r.right)}` : '—', 'верных ответов', d.answered ? `${Math.round(r.right / d.answered * 100)}%` : '')}${kpi('brain', `${Math.max(0, d.index + (d.status === 'reveal' || d.status === 'finished' ? 1 : 0))} из ${d.total}`, 'вопросов пройдено')}</div>`;
    const stages = [['lobby', 'Лобби'], ['play', d.status === 'question' || d.status === 'reveal' ? `Вопрос ${d.index + 1} из ${d.total}` : 'Вопросы'], ['end', 'Итоги']];
    const stageOf = { lobby: 0, question: 1, reveal: 1, finished: 2, cancelled: 2 }[d.status];
    const stageBar = `<div class="lv-stages">${stages.map(([, label], i) => `<span class="${i < stageOf ? 'done' : i === stageOf ? 'now' : ''}">${i < stageOf ? icon('check') : ''}${label}</span>`).join('')}</div>`;
    const dis = lv.busy ? ' disabled' : '';
    let console = '';
    if (d.status === 'lobby') {
      console = `${stageBar}<div class="lv-lobby" data-lv-part="lobby"><b>${num(d.players)}</b><span>${plural(d.players, 'участник подключился', 'участника подключились', 'участников подключились')}</span><small>Участники видят карточку LIVE во вкладке «Тесты». Начните, когда все соберутся.</small></div>
        <div class="lv-names" data-lv-part="names">${(d.leaders || []).map(l => `<span><i>${esc(l.name.charAt(0).toUpperCase())}</i>${esc(l.name)}</span>`).join('') || '<small class="muted">Пока никого — отправьте push или напомните участникам.</small>'}</div>
        <div class="lv-actions" data-lv-part="actions"><button type="button" class="btn black" data-lv-do="start"${dis}${d.players ? '' : ' disabled'}>${icon('play')}Начать квиз</button><span style="flex:1"></span><button type="button" class="btn danger" data-lv-do="cancel"${dis}>${icon('x')}Отменить</button></div>`;
    } else if (d.status === 'question' || d.status === 'reveal') {
      const q = d.questionFull || { text: '', options: [], correct: -1, explanation: '' }, total = Math.max(1, d.answered || 0), C = 2 * Math.PI * 46;
      const share = d.status === 'question' ? Math.max(0, lv.deadline - Date.now()) / (d.timeSec * 1000) : 0;
      const dist = q.options.map((text, k) => `<div class="lv-opt${k === q.correct ? ' right' : ''}"><i>${LV_LETTERS[k]}</i><b>${esc(text)}</b><em><u style="width:${Math.round((r.counts[k] || 0) / total * 100)}%"></u></em><strong>${num(r.counts[k] || 0)}</strong><small>${k === q.correct ? 'верный' : ''}</small></div>`).join('');
      const last = d.index + 1 >= d.total;
      const actions = d.status === 'question'
        ? `<button type="button" class="btn black" data-lv-do="reveal"${dis}>${icon('check')}Показать ответ</button>`
        : `<button type="button" class="btn black" data-lv-do="next"${dis}>${last ? `${icon('rating')}Подвести итоги` : `Следующий вопрос${icon('chev')}`}</button>`;
      console = `${stageBar}<div class="lv-q">
          <span class="lv-ring" data-lv-ring role="timer" aria-label="Осталось времени"><svg width="112" height="112" viewBox="0 0 112 112" aria-hidden="true"><circle cx="56" cy="56" r="46"></circle><circle cx="56" cy="56" r="46" stroke-dasharray="${(C * share).toFixed(1)} ${C.toFixed(1)}"></circle></svg><span><b>${d.status === 'question' ? Math.ceil(Math.max(0, lv.deadline - Date.now()) / 1000) : 0}</b><small>${d.status === 'question' ? 'секунд' : 'ответ открыт'}</small></span></span>
          <div><small class="muted">Вопрос ${d.index + 1} · ${d.status === 'question' ? 'идёт ответ' : 'участники видят верный ответ'}</small><h2>${esc(q.text)}</h2></div></div>
        <div class="lv-dist" data-lv-part="dist">${dist}</div>
        ${d.status === 'reveal' && q.explanation ? `<small class="muted">${esc(q.explanation)}</small>` : ''}
        <div class="lv-actions" data-lv-part="actions">${actions}<span style="flex:1"></span><button type="button" class="btn danger" data-lv-do="finish"${dis}>${icon('x')}Завершить квиз</button></div>`;
    } else {
      const leaders = d.leaders || [];
      const slot = (l, place) => l ? `<div class="lv-slot${place === 1 ? ' first' : ''}"><i>${esc(l.name.charAt(0).toUpperCase())}</i><b>${esc(l.name)}</b><small>${num(l.score)} · ${l.correct} из ${d.total}</small><span style="height:${place === 1 ? 120 : place === 2 ? 88 : 66}px">${place}</span></div>` : '<div class="lv-slot"></div>';
      console = `${stageBar}${d.status === 'cancelled' ? '<div class="empty">Квиз отменён — итоги не подводились.</div>' : leaders.length ? `<div class="lv-podium">${slot(leaders[1], 2)}${slot(leaders[0], 1)}${slot(leaders[2], 3)}</div>` : '<div class="empty">Никто не участвовал.</div>'}
        <div class="lv-actions"><button type="button" class="btn black" data-lv-new>${icon('plus')}Новый живой квиз</button></div>`;
    }
    const board = (d.leaders || []).map((l, i) => `<div class="lv-lb"><b class="${i < 3 && d.status !== 'lobby' ? 'top' : ''}">${l.place}</b><i>${esc(l.name.charAt(0).toUpperCase())}</i><span>${esc(l.name)}</span>${d.status === 'question' ? `<em class="${l.answered ? 'yes' : ''}" title="${l.answered ? 'ответил' : 'ещё думает'}"></em>` : ''}<strong>${num(l.score)}</strong></div>`).join('') || '<div class="empty" style="padding:16px">Пока никого.</div>';
    const pill = lvActive(d) ? '<span class="lv-pill"><i></i>LIVE</span>' : `<span class="chip">${d.status === 'finished' ? 'Завершён' : 'Отменён'}</span>`;
    return {
      top: top('Тесты · живой режим', `Живой квиз: ${esc(d.title)}`, `${qzWord(d.total)} · ${d.timeSec} сек на ответ · ${statusText}`, tools() + back),
      body: `${kpis}<div class="split"><section class="glass card" style="flex:1">${console}</section>
        <aside class="detail" style="display:flex;flex-direction:column;gap:16px">
          <section class="lv-dark" data-lv-part="dark">${pill}<b>${statusText}</b><small>Участники видят квиз во вкладке «Тесты» и подключаются одним нажатием${lvActive(d) ? ', даже после начала.' : '.'}</small>${lvActive(d) ? `<button type="button" class="btn small" data-lv-do="push"${dis}>${icon('bell')}Push всем: «Квиз идёт»</button>` : ''}</section>
          <section class="glass card"><div class="toolbar"><b style="font-size:17px;font-weight:600">Таблица</b><small class="muted" data-lv-part="count">${num(d.players)} · ${d.status === 'question' ? '● ответил' : 'по очкам'}</small></div><div class="lv-board" data-lv-part="board">${board}</div></section>
        </aside></div>`
    };
  }

  /* ---------- Routing ---------- */
  function route() {
    if (!cred) return;
    document.querySelector('.menu')?.remove();
    const [section = 'overview', id, action] = location.hash.replace(/^#/, '').split('/');
    const current = ['overview', 'events', 'users', 'groups', 'moderation', 'quizzes', 'settings'].includes(section) ? section : 'overview';
    closePanel();
    document.querySelectorAll('[data-route]').forEach(a => { if (a.dataset.route === current) a.setAttribute('aria-current', 'page'); else a.removeAttribute('aria-current'); });
    window.scrollTo(0, 0);
    if (current === 'events') {
      if (id === 'new') showEditor(null, null);
      else if (id && action === 'edit') showEditor(id);
      else if (id && action === 'copy') showEditor(null, id);
      else showEvents(id || null);
    } else if (current === 'users') showUsers();
    else if (current === 'groups') {
      if (id === 'new') showGroupEditor(null);
      else if (id && action === 'edit') showGroupEditor(id);
      else if (id) showGroupReport(id);
      else showGroups();
    }
    else if (current === 'moderation') showModeration();
    else if (current === 'quizzes') {
      if (id === 'live') showLive();
      else if (id === 'new') showQuizEditor(null);
      else if (id && action === 'edit') showQuizEditor(id);
      else showQuizzes(id || null);
    }
    else if (current === 'settings') showSettings();
    else showOverview();
  }
  window.addEventListener('hashchange', route);

  main.addEventListener('click', event => {
    const t = event.target;
    const lvDo = t.closest('[data-lv-do]'); if (lvDo) { lvAction(lvDo.dataset.lvDo); return; }
    if (t.closest('[data-lv-create]')) { lvCreate(); return; }
    if (t.closest('[data-lv-new]')) { lv.fresh = true; lv.key = ''; clearTimeout(lv.poll); clearInterval(lv.tick); paintLive(); req('admin/live').then(body => { lv.recent = body.recent || []; lv.key = ''; paintLive(); }).catch(() => {}); return; }
    const lvLaunch = t.closest('[data-lv-launch]'); if (lvLaunch) { lv.form.quizId = lvLaunch.dataset.lvLaunch; if (!lvActive(lv.data)) lv.fresh = true; location.hash = '#quizzes/live'; return; }
    if (t.closest('[data-refresh]')) { data.events = data.users = data.groups = data.moderation = data.quizzes = null; qz.details = {}; route(); return; }
    if (t.closest('[data-search]')) { openSearch(); return; }
    if (t.closest('[data-bell]')) { event.stopPropagation(); openBell(); return; }
    const pause = t.closest('[data-pause]'); if (pause) { setPaused(pause.dataset.pause, true); return; }
    const resume = t.closest('[data-resume]'); if (resume) { setPaused(resume.dataset.resume, false); return; }
    const rem = t.closest('[data-remind]'); if (rem) { remind(rem.dataset.remind); return; }
    const ok = t.closest('[data-approve]'); if (ok) { const [eventId, userId] = ok.dataset.approve.split('|'); decide('approve', [{ eventId: Number(eventId), userId }]); return; }
    const no = t.closest('[data-reject]'); if (no) { const [eventId, userId] = no.dataset.reject.split('|'); decide('reject', [{ eventId: Number(eventId), userId }]); return; }
    const all = t.closest('[data-approve-all]'); if (all) { decide('approve', data.moderation.filter(r => String(r.event_id) === all.dataset.approveAll).map(r => ({ eventId: Number(r.event_id), userId: r.user_id }))); return; }
    if (t.closest('[data-users-csv]')) { usersCsv(); return; }
    const reset = t.closest('[data-reset-pass]'); if (reset) { resetPassword(reset.dataset.resetPass); return; }
    if (t.closest('[data-logout]')) { logout(); return; }
    const menuBtn = t.closest('[data-menu]'); if (menuBtn) { event.stopPropagation(); openMenu(menuBtn); return; }
    const edit = t.closest('[data-edit]'); if (edit) { location.hash = `#events/${edit.dataset.edit}/edit`; return; }
    const copy = t.closest('[data-copy]'); if (copy) { location.hash = `#events/${copy.dataset.copy}/copy`; return; }
    const delEv = t.closest('[data-delete-event]'); if (delEv) { document.querySelector('.menu')?.remove(); deleteEvent(delEv.dataset.deleteEvent); return; }
    const tab = t.closest('[data-tab]'); if (tab) { ev.tab = tab.dataset.tab; paintEvents(); return; }
    if (t.closest('[data-close-detail]')) { history.replaceState(null, '', '#events'); ev.selected = null; paintEvents(); return; }
    const row = t.closest('tr[data-event]'); if (row && !t.closest('.menu-wrap')) { history.replaceState(null, '', `#events/${row.dataset.event}`); ev.selected = row.dataset.event; paintEvents(); if (innerWidth <= 1240) $('.detail')?.scrollIntoView({ behavior: 'smooth' }); return; }
    const uf = t.closest('[data-ufilter]'); if (uf) { us.filter = uf.dataset.ufilter; paintUsers(); return; }
    if (t.closest('[data-close-user]')) { us.selected = null; paintUsers(); return; }
    const delUser = t.closest('[data-delete-user]'); if (delUser) { deleteUser(delUser.dataset.deleteUser); return; }
    const delGroup = t.closest('[data-delete-group]'); if (delGroup) { deleteGroup(delGroup.dataset.deleteGroup); return; }
    const urow = t.closest('tr[data-user]'); if (urow) { us.selected = urow.dataset.user; paintUsers(); if (innerWidth <= 1240) $('.detail')?.scrollIntoView({ behavior: 'smooth' }); }
  });
  /* Search keeps focus while the list repaints. */
  main.addEventListener('input', event => {
    if (event.target.id === 'ev-search') { ev.query = event.target.value; paintEvents(); const s = $('#ev-search'); s.focus(); s.setSelectionRange(s.value.length, s.value.length); }
    if (event.target.id === 'us-search') { us.query = event.target.value; paintUsers(); const s = $('#us-search'); s.focus(); s.setSelectionRange(s.value.length, s.value.length); }
  });
  /* The live quiz setup keeps its choices across repaints. */
  main.addEventListener('change', event => {
    const t = event.target;
    if (t.matches('[data-lv-quiz]')) { lv.form.quizId = t.value; lv.key = ''; paintLive(); }
    if (t.matches('[data-lv-time]')) { lv.form.timeSec = Number(t.value); lv.key = ''; paintLive(); }
    if (t.matches('[data-lv-push]')) lv.form.push = t.checked;
  });
  main.addEventListener('change', event => { if (event.target.id === 'ev-mode') { ev.mode = event.target.value; paintEvents(); } });
  document.addEventListener('click', event => {
    if (!event.target.closest('.menu') && !event.target.closest('[data-menu]')) document.querySelector('.menu')?.remove();
    if (!event.target.closest('.panel') && !event.target.closest('[data-bell]')) closePanel();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') { document.querySelector('.menu')?.remove(); closePanel(); }
    /* «/» opens search when no field has focus. */
    if (event.key === '/' && cred && !$('#shell').hidden && !event.target.closest('input,textarea,select') && !document.querySelector('.layer')) { event.preventDefault(); openSearch(); }
  });

  /* ---------- Sign in and out ---------- */
  function enter() {
    document.documentElement.classList.remove('restoring');
    $('#login').hidden = true; $('#shell').hidden = false;
    $('#me-name').textContent = cred.login; $('#me-avatar').textContent = (cred.login || 'a').slice(0, 1);
    paintVersion(); route();
    loadModeration(true).catch(() => {});
    if (data.settings) paintBell(); else req('admin/settings').then(s => { data.settings = s; paintBell(); }).catch(() => {});
  }
  async function logout() {
    if (!await confirmDialog({ title: 'Выйти из кабинета?', text: 'Для входа снова понадобятся логин и пароль администратора.', action: 'Выйти', danger: false, iconName: 'logout' })) return;
    cred = null; sessionStorage.removeItem('chetele-admin');
    Object.keys(data).forEach(k => { data[k] = null; });
    $('#shell').hidden = true; $('#login').hidden = false; $('#login-pass').value = ''; $('#login-pass').focus();
  }
  function signedOut(message) {
    cred = null; try { sessionStorage.removeItem('chetele-admin'); } catch {}
    Object.keys(data).forEach(k => { data[k] = null; });
    document.querySelectorAll('.layer,.panel').forEach(el => el.remove());
    document.documentElement.classList.remove('restoring');
    $('#shell').hidden = true; $('#login').hidden = false; $('#login-pass').value = '';
    $('#login-error').textContent = message || ''; $('#login-name').focus();
  }
  $('#logout').addEventListener('click', logout);
  $('#login-form').addEventListener('submit', async event => {
    event.preventDefault();
    const button = event.submitter || event.target.querySelector('[type=submit]'); button.disabled = true; $('#login-error').textContent = '';
    cred = { login: $('#login-name').value.trim(), password: $('#login-pass').value };
    signingIn = true;
    try { await loadEvents(true); sessionStorage.setItem('chetele-admin', JSON.stringify(cred)); enter(); }
    catch (error) { cred = null; $('#login-error').textContent = error.message; }
    finally { button.disabled = false; signingIn = false; }
  });

  paintIcons(document);
  try { cred = JSON.parse(sessionStorage.getItem('chetele-admin') || 'null'); } catch { cred = null; }
  /* The cabinet opens at once with its own loading state; the first request checks the saved login. */
  if (cred && cred.login && cred.password) {
    /* One request checks the saved login first, so a stale password counts as one failed attempt, not several. */
    document.documentElement.classList.remove('restoring');
    $('#login').hidden = true; $('#shell').hidden = false;
    $('#me-name').textContent = cred.login; $('#me-avatar').textContent = (cred.login || 'a').slice(0, 1);
    loading('Кабинет');
    req('admin/settings').then(s => { data.settings = s; enter(); }).catch(error => { if (cred) failed('Кабинет', error); });
  } else signedOut('');
})();
</script>
</body>
</html>
