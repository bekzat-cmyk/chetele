/* «Тесты»: tests the admin writes, taken in the app. A tab of the bottom menu.
   List → intro → one question per screen with the answer shown right away → result
   with a review of mistakes. Results of signed-in members are graded and kept by the
   server; guests can take a test without saving it. */
(function(){
  const CSS = `
body #app #quiz-view.active{display:flex;flex-direction:column;gap:22px;padding-bottom:calc(120px + env(safe-area-inset-bottom))}
body #app #quiz-view.focus{padding-bottom:calc(32px + env(safe-area-inset-bottom))}
/* Everything is drawn inside one lasting container: the app replays its entrance on every
   new direct child of a view, which made each repaint flash. The container only slides
   (no fade), so glass behind it never loses its blur mid-animation. */
#quiz-view .qz-root{display:flex;flex-direction:column;gap:22px;min-width:0}
body #app #quiz-view.active>.qz-root{animation:qzSlide .42s cubic-bezier(.2,.8,.2,1) both}
@keyframes qzSlide{from{transform:translateY(12px)}}
body #app.quiz-focus .nav{display:none}
#quiz-view .qz-top{padding:max(12px,calc(56px - env(safe-area-inset-top))) 4px 0;display:flex;flex-direction:column;gap:18px}
#quiz-view .qz-round{width:44px;height:44px;flex:none;border-radius:22px;border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.26);color:#fff;display:grid;place-items:center;padding:0}
#quiz-view .qz-round svg{width:20px;height:20px}
#quiz-view .qz-title{display:flex;flex-direction:column}
#quiz-view .qz-title span{margin-bottom:6px;color:rgba(255,255,255,.78);font-size:14px;font-weight:500}
#quiz-view .qz-title h2{margin:0;color:#fff;font-size:30px;font-weight:600;letter-spacing:-.02em;line-height:1.15}
#quiz-view .qz-title em{font-style:normal;color:rgba(255,255,255,.72);font-size:30px;font-weight:400;letter-spacing:-.02em;line-height:1.15}
#quiz-view .qz-shell{border-radius:32px;border:1px solid rgba(255,255,255,.55);background:rgba(255,255,255,.4);box-shadow:0 20px 50px rgba(16,22,26,.10);display:flex;flex-direction:column}
#quiz-view .qz-shell-bar{min-height:52px;padding:10px 18px;box-sizing:border-box;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
#quiz-view .qz-panel{margin:-1px;padding:22px 20px 20px;border-radius:28px;border:1px solid rgba(255,255,255,.9);background:linear-gradient(180deg,rgba(246,249,248,.95),rgba(233,239,237,.9));box-shadow:0 14px 40px rgba(16,22,26,.10),inset 0 1px 0 #fff;display:flex;flex-direction:column;gap:16px;color:#10161A}
#quiz-view .qz-pill{height:28px;padding:0 12px;border-radius:14px;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;white-space:nowrap;background:rgba(255,255,255,.7);color:#10161A}
#quiz-view .qz-pill.dark{background:#0B0F12;color:#fff}
#quiz-view .qz-pill.dark i{width:6px;height:6px;border-radius:3px;background:#7FD3A8}
#quiz-view .qz-pill.new{background:rgba(78,92,190,.12);color:#3A4596}
#quiz-view .qz-pill.good{background:rgba(47,155,110,.14);color:#1F6B4F}
#quiz-view .qz-pill.bad{background:rgba(190,60,50,.10);color:#A3342A}
#quiz-view .qz-head{display:flex;align-items:center;gap:14px}
#quiz-view .qz-head b{display:block;font-size:22px;font-weight:600;letter-spacing:-.02em;line-height:1.2}
#quiz-view .qz-head small,#quiz-view .qz-muted{color:#5E6A71;font-size:14px;line-height:1.5}
#quiz-view .qz-ball{width:44px;height:44px;flex:none;border-radius:50%;background:rgba(255,255,255,.92);border:1px solid rgba(16,22,26,.06);color:#10161A;display:grid;place-items:center}
#quiz-view .qz-ball svg{width:20px;height:20px}
#quiz-view .qz-ball.big{width:56px;height:56px;background:#0B0F12;color:#fff}
#quiz-view .qz-ball.big svg{width:26px;height:26px}
#quiz-view .qz-ball.ok{background:rgba(47,155,110,.14);color:#1F6B4F}
#quiz-view .qz-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));padding:12px 0;border-radius:20px;background:rgba(255,255,255,.8)}
#quiz-view .qz-stats span{display:flex;flex-direction:column;align-items:center;gap:2px}
#quiz-view .qz-stats span+span{border-left:1px solid rgba(16,22,26,.08)}
#quiz-view .qz-stats b{font-size:20px;font-weight:600;letter-spacing:-.02em}
#quiz-view .qz-stats small{font-size:12px;color:#7A858B}
#quiz-view .qz-cta{height:54px;border:0;border-radius:27px;background:#0B0F12;color:#fff;display:flex;align-items:center;justify-content:center;gap:8px;font-size:16px;font-weight:600;width:100%}
#quiz-view .qz-cta.ghost{background:rgba(255,255,255,.86);border:1px solid rgba(16,22,26,.08);color:#10161A}
#quiz-view .qz-cta.wait{background:rgba(16,22,26,.06);color:#7A858B}
#quiz-view .qz-cta svg{width:18px;height:18px}
#quiz-view .qz-cats{display:flex;gap:8px;overflow-x:auto;margin:0 -16px;padding:0 16px;scrollbar-width:none}
#quiz-view .qz-cats::-webkit-scrollbar{display:none}
#quiz-view .qz-cats button{height:38px;padding:0 16px;flex:none;border-radius:19px;border:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.55);color:#10161A;font-size:14px;font-weight:600}
#quiz-view .qz-cats button[aria-pressed=true]{border-color:#0B0F12;background:#0B0F12;color:#fff}
#quiz-view .qz-list{display:flex;flex-direction:column;gap:10px}
#quiz-view .qz-list>span{padding:0 4px;font-size:14px;font-weight:600;color:#5E6A71}
#quiz-view .qz-row{min-height:76px;padding:12px 14px;border-radius:24px;background:rgba(255,255,255,.86);border:1px solid rgba(255,255,255,.95);box-shadow:0 6px 18px rgba(16,22,26,.05);display:flex;align-items:center;gap:12px;text-align:left;color:#10161A;width:100%}
#quiz-view .qz-row>.qz-copy{flex:1;min-width:0;display:flex;flex-direction:column;gap:3px}
#quiz-view .qz-row>.qz-pill{flex:none}
#quiz-view .qz-row b{font-size:16px;font-weight:600;letter-spacing:-.01em}
#quiz-view .qz-row small{font-size:13px;color:#5E6A71}
#quiz-view .qz-row em{font-style:normal;display:flex;flex-direction:column;align-items:flex-end;gap:2px;font-size:15px;font-weight:600}
#quiz-view .qz-row em small.ok{color:#1F6B4F}
#quiz-view .qz-note{padding:0 12px;text-align:center;font-size:13px;line-height:1.5;color:#7A858B}
#quiz-view .qz-rule{display:flex;align-items:center;gap:12px;padding:12px 0}
#quiz-view .qz-rule+.qz-rule{border-top:1px solid rgba(16,22,26,.06)}
#quiz-view .qz-rule b{display:block;font-size:15px;font-weight:600}
#quiz-view .qz-rule small{font-size:13px;color:#5E6A71;line-height:1.4}
#quiz-view .qz-best{padding:14px 16px;border-radius:22px;background:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.9);display:flex;align-items:center;gap:12px;color:#10161A}
#quiz-view .qz-best b{display:block;font-size:15px;font-weight:600}
#quiz-view .qz-best small{font-size:13px;color:#5E6A71}
#quiz-view .qz-progress{flex:1;display:flex;flex-direction:column;gap:8px;color:#fff;font-size:14px;font-weight:600}
#quiz-view .qz-progress>span{display:flex;justify-content:space-between;gap:8px}
#quiz-view .qz-progress>span em{font-style:normal;color:rgba(255,255,255,.78);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#quiz-view .qz-track{height:8px;border-radius:4px;background:rgba(255,255,255,.3);overflow:hidden}
#quiz-view .qz-track i{display:block;height:100%;border-radius:4px;background:#fff;transition:width .3s ease}
#quiz-view .qz-q{margin:0;font-size:23px;font-weight:600;letter-spacing:-.02em;line-height:1.3}
#quiz-view .qz-options{display:flex;flex-direction:column;gap:10px}
#quiz-view .qz-opt{min-height:56px;padding:10px 14px 10px 10px;border-radius:20px;border:1.5px solid rgba(16,22,26,.08);background:rgba(255,255,255,.9);color:#10161A;display:flex;align-items:center;gap:12px;text-align:left;font-size:16px;font-weight:600;width:100%;transition:background .2s ease,border-color .2s ease,transform .12s ease}
#quiz-view .qz-opt:active{transform:scale(.98)}
#quiz-view .qz-opt i{width:34px;height:34px;flex:none;border-radius:17px;background:rgba(16,22,26,.06);display:grid;place-items:center;font-style:normal;font-size:14px}
#quiz-view .qz-opt span{flex:1}
#quiz-view .qz-opt small{font-size:12px;font-weight:600}
#quiz-view .qz-opt.right{background:rgba(47,155,110,.14);border-color:#2F7F64}
#quiz-view .qz-opt.right i{background:#1F6B4F;color:#fff}
#quiz-view .qz-opt.right small{color:#1F6B4F}
#quiz-view .qz-opt.wrong{background:rgba(190,60,50,.10);border-color:#A3342A}
#quiz-view .qz-opt.wrong i{background:#A3342A;color:#fff}
#quiz-view .qz-opt.wrong small{color:#A3342A}
#quiz-view .qz-opt.picked{border-color:#0B0F12;background:#fff}
#quiz-view .qz-opt.picked i{background:#0B0F12;color:#fff}
#quiz-view .qz-opt.dim{background:rgba(255,255,255,.55);color:#5E6A71}
#quiz-view .qz-feedback{padding:16px 18px;border-radius:24px;background:rgba(255,255,255,.86);border:1px solid rgba(255,255,255,.95);display:flex;flex-direction:column;gap:6px;color:#10161A;animation:qzRise .32s cubic-bezier(.2,.8,.2,1) both}
#quiz-view .qz-feedback .qz-pill{align-self:flex-start;font-size:13px}
#quiz-view .qz-feedback p{margin:0;font-size:14px;line-height:1.5;color:#5E6A71}
#quiz-view .qz-bottom{margin-top:auto;display:flex;flex-direction:column;gap:10px}
#quiz-view .qz-ring{color:#0B0F12;position:relative;width:132px;height:132px;display:grid;place-items:center;align-self:center}
#quiz-view .qz-ring svg{position:absolute;inset:0;transform:rotate(-90deg)}
#quiz-view .qz-ring b{display:block;text-align:center;font-size:40px;font-weight:600;letter-spacing:-.03em;line-height:1}
#quiz-view .qz-ring small{display:block;text-align:center;font-size:14px;color:#7A858B}
#quiz-view .qz-levels{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px}
#quiz-view .qz-levels span{min-width:0;padding:10px 3px;border-radius:16px;background:rgba(255,255,255,.7);color:#5E6A71;display:flex;flex-direction:column;align-items:center;gap:2px;text-align:center}
#quiz-view .qz-levels span.on{background:#0B0F12;color:#fff}
#quiz-view .qz-levels b{max-width:100%;font-size:clamp(10px,3vw,12px);font-weight:600;line-height:1.25;overflow-wrap:anywhere;hyphens:auto}
#quiz-view .qz-levels small{font-size:11px;opacity:.8}
#quiz-view .qz-review{padding:6px 20px 8px;border-radius:28px;background:rgba(255,255,255,.86);border:1px solid rgba(255,255,255,.95);display:flex;flex-direction:column;color:#10161A}
#quiz-view .qz-review>b{padding-top:14px;font-size:17px;font-weight:600}
#quiz-view .qz-mistake{padding:14px 0;display:flex;flex-direction:column;gap:8px}
#quiz-view .qz-mistake+.qz-mistake{border-top:1px solid rgba(16,22,26,.06)}
#quiz-view .qz-mistake>b{font-size:15px;font-weight:600;line-height:1.4}
#quiz-view .qz-mistake span{display:flex;align-items:center;gap:8px;font-size:14px}
#quiz-view .qz-mistake span svg{width:16px;height:16px;flex:none}
#quiz-view .qz-mistake .no{color:#A3342A}
#quiz-view .qz-mistake .yes{color:#1F6B4F}
#quiz-view .qz-mistake p{margin:0;font-size:13px;line-height:1.5;color:#5E6A71}
#quiz-view .qz-empty{padding:28px 20px;border-radius:28px;background:rgba(255,255,255,.8);text-align:center;color:#5E6A71;font-size:15px;line-height:1.5;display:flex;flex-direction:column;align-items:center;gap:12px}
#quiz-view .qz-empty b{color:#10161A;font-size:18px}
#quiz-view .qz-skel{height:76px;border-radius:24px;background:rgba(255,255,255,.55);animation:qzPulse 1.2s ease-in-out infinite}
#quiz-view .qz-rise{animation:qzRise .32s cubic-bezier(.2,.8,.2,1) both}
@keyframes qzRise{from{transform:translateY(10px);opacity:0}to{transform:none;opacity:1}}
@keyframes qzPulse{50%{opacity:.55}}
@media (prefers-reduced-motion:reduce){body #app #quiz-view.active>.qz-root,#quiz-view .qz-rise,#quiz-view .qz-feedback,#quiz-view .qz-skel{animation:none}#quiz-view .qz-track i,#quiz-view .qz-opt{transition:none}}
body.app-dark #app #quiz-view .qz-shell,body.app-dark #app #quiz-view .qz-row,body.app-dark #app #quiz-view .qz-best,body.app-dark #app #quiz-view .qz-review,body.app-dark #app #quiz-view .qz-feedback,body.app-dark #app #quiz-view .qz-empty{border-color:rgba(255,255,255,.08);background:rgba(23,32,30,.72);box-shadow:none;color:var(--ink)}
body.app-dark #app #quiz-view .qz-panel{border-color:rgba(255,255,255,.1);background:rgba(23,32,30,.92);box-shadow:none;color:var(--ink)}
body.app-dark #app #quiz-view .qz-stats,body.app-dark #app #quiz-view .qz-pill,body.app-dark #app #quiz-view .qz-levels span,body.app-dark #app #quiz-view .qz-ball,body.app-dark #app #quiz-view .qz-opt i{background:rgba(255,255,255,.08);color:var(--ink);border-color:rgba(255,255,255,.08)}
body.app-dark #app #quiz-view .qz-opt{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:var(--ink)}
body.app-dark #app #quiz-view .qz-opt.right{background:rgba(47,155,110,.22);border-color:#5FC79A}
body.app-dark #app #quiz-view .qz-opt.wrong{background:rgba(190,60,50,.22);border-color:#F0938A}
body.app-dark #app #quiz-view .qz-opt.picked{border-color:var(--ink)}
body.app-dark #app #quiz-view .qz-opt.dim{color:var(--muted)}
body.app-dark #app #quiz-view .qz-cta{background:#eef5f2;color:#0b1110}
body.app-dark #app #quiz-view .qz-cta.ghost,body.app-dark #app #quiz-view .qz-cta.wait{background:rgba(255,255,255,.08);color:var(--ink);border-color:rgba(255,255,255,.1)}
body.app-dark #app #quiz-view .qz-levels span.on,body.app-dark #app #quiz-view .qz-pill.dark,body.app-dark #app #quiz-view .qz-ball.big{background:#eef5f2;color:#0b1110}
body.app-dark #app #quiz-view .qz-cats button{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.1);color:var(--ink)}
body.app-dark #app #quiz-view .qz-cats button[aria-pressed=true]{background:#eef5f2;color:#0b1110}
body.app-dark #app #quiz-view .qz-muted,body.app-dark #app #quiz-view .qz-head small,body.app-dark #app #quiz-view .qz-row small,body.app-dark #app #quiz-view .qz-rule small,body.app-dark #app #quiz-view .qz-best small,body.app-dark #app #quiz-view .qz-feedback p,body.app-dark #app #quiz-view .qz-mistake p,body.app-dark #app #quiz-view .qz-list>span,body.app-dark #app #quiz-view .qz-empty{color:var(--muted)}
body.app-dark #app #quiz-view .qz-empty b{color:var(--ink)}
body.app-dark #app #quiz-view .qz-ring{color:#eef5f2}
body.app-dark #app #quiz-view .qz-ring circle:first-child{stroke:rgba(255,255,255,.1)}
body.app-dark #app #quiz-view .qz-pill.good,body.app-dark #app #quiz-view .qz-mistake .yes,body.app-dark #app #quiz-view .qz-row em small.ok{color:#7FC8A6}
body.app-dark #app #quiz-view .qz-pill.bad,body.app-dark #app #quiz-view .qz-mistake .no{color:#F0938A}
body.app-dark #app #quiz-view .qz-pill.new{color:#a9b3ff}
/* The app's dark theme makes every <b> light; text on the light tile is set explicitly. */
body.app-dark #app #quiz-view .qz-levels span.on b,body.app-dark #app #quiz-view .qz-levels span.on small{color:#0b1110}
body.app-dark #app #quiz-view .qz-opt.right small{color:#7FC8A6}
body.app-dark #app #quiz-view .qz-opt.wrong small{color:#F0938A}
body.app-dark #app #quiz-view .qz-opt.right i{background:#2F7F64;color:#fff}
body.app-dark #app #quiz-view .qz-opt.wrong i{background:#B8463B;color:#fff}
body.app-dark #app #quiz-view .qz-opt.picked i{background:#eef5f2;color:#0b1110}
.qz-sheet{position:fixed;inset:0;z-index:80;display:flex;align-items:flex-end;justify-content:center;background:rgba(16,22,26,.38);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);animation:qzFade .2s ease both}
.qz-sheet section{width:min(100%,540px);box-sizing:border-box;padding:10px 20px calc(24px + env(safe-area-inset-bottom));border-radius:32px 32px 0 0;background:rgba(246,249,248,.97);box-shadow:0 -20px 50px rgba(16,22,26,.18);display:flex;flex-direction:column;gap:14px;color:#10161A;font-family:Inter,-apple-system,system-ui,sans-serif;animation:qzUp .32s cubic-bezier(.2,.8,.2,1) both}
.qz-sheet section>i{align-self:center;width:40px;height:5px;border-radius:3px;background:rgba(16,22,26,.18)}
.qz-sheet h2{margin:4px 0 0;font-size:22px;font-weight:600;letter-spacing:-.02em;color:inherit}
.qz-sheet p{margin:0;font-size:15px;line-height:1.5;color:#5E6A71}
.qz-sheet button{height:54px;border-radius:27px;border:0;font:inherit;font-size:16px;font-weight:600}
.qz-sheet .stay{background:#0B0F12;color:#fff}
.qz-sheet .leave{background:rgba(190,60,50,.10);color:#A3342A}
body.app-dark .qz-sheet section{background:#17201e;color:#eef5f2}
body.app-dark .qz-sheet h2{color:#eef5f2}
body.app-dark .qz-sheet p{color:#9baea7}
body.app-dark .qz-sheet .stay{background:#eef5f2;color:#0b1110}
body.app-dark .qz-sheet .leave{background:rgba(240,147,138,.14);color:#F0938A}
@keyframes qzFade{from{opacity:0}}
@keyframes qzUp{from{transform:translateY(40px);opacity:0}}
@media (prefers-reduced-motion:reduce){.qz-sheet,.qz-sheet section{animation:none}}
`;
  const P = {
    brain: 'M12 5.5a3 3 0 0 0-5.4-1.8A3.2 3.2 0 0 0 4 7.6a3.2 3.2 0 0 0-.4 5.3A3.6 3.6 0 0 0 7.2 18.4 2.9 2.9 0 0 0 12 19.6zM12 5.5a3 3 0 0 1 5.4-1.8A3.2 3.2 0 0 1 20 7.6a3.2 3.2 0 0 1 .4 5.3 3.6 3.6 0 0 1-3.6 5.5 2.9 2.9 0 0 1-4.8 1.2zM12 5.5v14.1M8.5 9.2c1.4 0 2.4.9 2.4 2.2M15.5 9.2c-1.4 0-2.4.9-2.4 2.2M7.6 14.6c1.3-.2 2.6.4 3.1 1.5M16.4 14.6c-1.3-.2-2.6.4-3.1 1.5',
    back: 'M14.5 6l-6 6 6 6', x: 'M6 6l12 12M18 6L6 18', chev: 'M9.5 6l6 6-6 6', check: 'M5 12.5l4.5 4.5L19 7.5',
    list: 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01', reset: 'M3.5 12a8.5 8.5 0 1 0 2.5-6M3.5 4v4h4',
    medal: 'M6 9a6 6 0 1 0 12 0a6 6 0 1 0 -12 0M8.5 13.8 7 21l5-2.5 5 2.5-1.5-7.2', clock: 'M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0M12 7.5V12l3 2',
    'Намаз': 'M6 3h12v18H6zM9 18h6M12 7.5l2.5 3-2.5 3-2.5-3z', 'Куран': 'M12 6.5C10 5 7.3 4.6 4.5 5.2v9.6c2.8-.6 5.5-.2 7.5 1.3 2-1.5 4.7-1.9 7.5-1.3V5.2C16.7 4.6 14 5 12 6.5zM12 6.5v9.6M6 20.5l12-4M18 20.5l-12-4',
    'Пост': 'M19 14.5A7.5 7.5 0 0 1 9.5 5a7.5 7.5 0 1 0 9.5 9.5z', 'Сира': 'M12 3.5l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8z',
    'Основы': 'M5 4.5A1.5 1.5 0 0 1 6.5 3H19v15H6.5A1.5 1.5 0 0 0 5 19.5zM5 19.5A1.5 1.5 0 0 0 6.5 21H19M10 3v6.5l2-1.5 2 1.5V3'
  };
  const svg = (name, w = 1.7) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${w}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="${P[name] || P.brain}"></path></svg>`;
  const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const plural = (n, one, few, many) => { const d = n % 10, h = n % 100; return d === 1 && h !== 11 ? one : d >= 2 && d <= 4 && (h < 12 || h > 14) ? few : many; };
  const questionsWord = n => `${n} ${plural(n, 'вопрос', 'вопроса', 'вопросов')}`;
  const minutes = n => Math.max(1, Math.round(n * 0.45));
  const LETTERS = ['А', 'Б', 'В', 'Г', 'Д', 'Е'];
  const levelIndex = (right, total) => total < 1 ? 0 : right >= total ? 3 : right / total >= 0.8 ? 2 : right / total >= 0.5 ? 1 : 0;
  const levelRanges = total => { const a = Math.ceil(total * 0.5), b = Math.ceil(total * 0.8); const r = (x, y) => x > y ? '—' : x === y ? String(x) : `${x}–${y}`; return [r(0, a - 1), r(a, b - 1), r(b, total - 1), String(total)]; };
  const shuffle = list => { const a = list.slice(); for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };
  const guest = () => typeof isGuest === 'function' ? isGuest() : !(window.state && state.userId);
  const call = (path, options) => typeof apiFetch === 'function' ? apiFetch(apiUrl(path), options) : fetch(`/api/index.php?path=${encodeURIComponent(path)}`, options);

  const S = { screen: 'list', list: null, failed: false, category: 'Все', quiz: null, order: [], index: 0, picked: -1, answers: [], startedAt: 0, result: null, saving: false };
  let root = null, view = null;

  function setFocus(on) {
    document.querySelector('#app')?.classList.toggle('quiz-focus', on);
    view?.classList.toggle('focus', on);
  }
  /* Cards rise when a screen changes; the header never animates, so its glass stays put. */
  function rise(elements) {
    let i = 0;
    elements.forEach(el => { if (el.matches('header')) return; el.classList.add('qz-rise'); el.style.animationDelay = `${i++ * 40}ms`; });
  }
  function paint(animate) {
    if (!root) return;
    setFocus(S.screen !== 'list');
    root.innerHTML = S.screen === 'list' ? listHtml() : S.screen === 'intro' ? introHtml() : S.screen === 'question' ? questionHtml() : resultHtml();
    if (animate) rise([...root.children]);
  }
  /* Brings the screen up to date without replacing what is already there, so cards that
     are still rising keep rising instead of jumping. */
  function morph(html) {
    const fresh = document.createElement('div'); fresh.innerHTML = html;
    const old = [...root.children], neu = [...fresh.children];
    if (old.length !== neu.length || old.some((el, i) => el.tagName !== neu[i].tagName)) { root.replaceChildren(...neu); return; }
    old.forEach((el, i) => {
      if (el.innerHTML !== neu[i].innerHTML) el.innerHTML = neu[i].innerHTML;
      const rising = el.classList.contains('qz-rise'); el.className = neu[i].className; if (rising) el.classList.add('qz-rise');
    });
  }
  function go(screen) { S.screen = screen; paint(true); window.scrollTo(0, 0); }

  /* ---------- List ---------- */
  /* The list repaints only when it changed, so a refresh in the background is invisible. */
  async function loadList() {
    const before = JSON.stringify([S.list, S.failed]), first = !S.list;
    try { const response = await call('quizzes'); if (!response.ok) throw Error(); S.list = await response.json(); S.failed = false; }
    catch { S.failed = !S.list; }
    if (S.screen === 'list' && JSON.stringify([S.list, S.failed]) !== before) { if (first) paint(true); else morph(listHtml()); }
  }
  function listHtml() {
    const list = S.list || [], done = list.filter(q => q.myBest !== null && q.myBest !== undefined).length;
    const header = `<header class="stats-top"><div><span class="stats-eyebrow">Проверьте знания</span><h2>Тесты</h2><span class="stats-period-label">${S.list ? (list.length ? `${done} из ${list.length} пройдено` : 'скоро') : 'загрузка…'}</span></div><i class="stats-badge">${svg('brain', 1.6)}</i></header>`;
    if (!S.list && !S.failed) return header + '<div class="qz-list"><div class="qz-skel"></div><div class="qz-skel"></div><div class="qz-skel"></div></div>';
    if (S.failed) return header + `<div class="qz-empty"><b>Тесты пока недоступны</b>Проверьте подключение к интернету и попробуйте снова.<button type="button" class="qz-cta ghost" data-qz-retry>Повторить</button></div>`;
    if (!list.length) return header + `<div class="qz-empty"><span class="qz-ball big">${svg('brain')}</span><b>Скоро здесь появятся тесты</b>Администратор сообщества добавит вопросы о намазе, Куране, посте и сире.</div>`;
    const featured = list.find(q => q.featured) || null;
    const cats = ['Все', ...new Set(list.map(q => q.category))];
    if (!cats.includes(S.category)) S.category = 'Все';
    const shown = list.filter(q => S.category === 'Все' || q.category === S.category);
    const row = q => {
      const best = q.myBest !== null && q.myBest !== undefined;
      const right = best ? `<em>${q.myBest} из ${q.questionCount}<small class="ok">пройден</small></em>` : '<span class="qz-pill new">Новый</span>';
      return `<button type="button" class="qz-row" data-qz-open="${q.id}"><span class="qz-ball">${svg(q.category)}</span><span class="qz-copy"><b>${esc(q.title)}</b><small>${esc(q.category)} · ${questionsWord(q.questionCount)} · ~${minutes(q.questionCount)} мин</small></span>${right}</button>`;
    };
    const hero = featured ? `<section class="qz-shell" aria-label="Тест недели"><div class="qz-shell-bar"><span class="qz-pill dark"><i></i>Тест недели</span>${featured.myBest === null ? '<span class="qz-pill">Новый</span>' : ''}</div>
      <article class="qz-panel"><div class="qz-head"><span class="qz-ball big">${svg(featured.category)}</span><span><b>${esc(featured.title)}</b><small>${esc(featured.category)} · ${questionsWord(featured.questionCount)} · ~${minutes(featured.questionCount)} мин</small></span></div>
      ${featured.description ? `<span class="qz-muted">${esc(featured.description)}</span>` : ''}
      ${featured.showStats ? `<div class="qz-stats"><span><b>${featured.people ?? 0}</b><small>прошли</small></span><span><b>${featured.avgPercent === null ? '—' : featured.avgPercent + '%'}</b><small>в среднем</small></span><span><b>${featured.myBest === null ? '—' : `${featured.myBest}/${featured.questionCount}`}</b><small>мой результат</small></span></div>` : ''}
      <button type="button" class="qz-cta" data-qz-open="${featured.id}">${featured.myBest === null ? 'Начать тест' : 'Пройти снова'}${svg('chev')}</button></article></section>` : '';
    return header + hero
      + (cats.length > 2 ? `<div class="qz-cats" role="group" aria-label="Темы">${cats.map(c => `<button type="button" data-qz-cat="${esc(c)}" aria-pressed="${c === S.category}">${esc(c)}</button>`).join('')}</div>` : '')
      + `<section class="qz-list" aria-label="Все тесты"><span>${S.category === 'Все' ? 'Все тесты' : esc(S.category)}</span>${shown.map(row).join('')}</section>`
      + `<p class="qz-note">Пройти тест можно сколько угодно раз — сохраняется лучший результат.${guest() ? ' Чтобы результаты сохранялись, войдите в профиль.' : ''}</p>`;
  }

  /* ---------- Intro ---------- */
  async function openQuiz(id) {
    const brief = (S.list || []).find(q => String(q.id) === String(id));
    S.quiz = { ...(brief || {}), id, loading: true }; go('intro');
    try {
      const response = await call(`quizzes/${id}`); if (!response.ok) throw Error((await response.json().catch(() => ({}))).error || 'Тест недоступен');
      S.quiz = { ...(brief || {}), ...(await response.json()) };
    } catch (error) { S.quiz = { ...(brief || {}), id, error: error.message || 'Тест недоступен' }; }
    if (S.screen === 'intro') morph(introHtml());
  }
  function introHtml() {
    const q = S.quiz || {}, n = q.questions ? q.questions.length : q.questionCount || 0;
    const head = `<header class="qz-top"><button type="button" class="qz-round" data-qz-back aria-label="Назад к тестам">${svg('back')}</button><div class="qz-title"><span>${esc(q.category || 'Тест')} · ~${minutes(n)} мин</span><h2>${esc(q.title || 'Тест')}</h2><em>проверьте себя</em></div></header>`;
    if (q.error) return head + `<div class="qz-empty"><b>${esc(q.error)}</b><button type="button" class="qz-cta ghost" data-qz-back>К тестам</button></div>`;
    const rules = [['list', questionsWord(n), 'по одному на экране'], q.showAnswers === false ? ['check', 'Ответы — в конце', 'верные варианты и пояснения покажем в разборе'] : ['check', 'Ответ виден сразу', 'после выбора — верный вариант и короткое пояснение'], ['reset', 'Можно пройти снова', 'сохраняем лучший результат']];
    const best = q.myBest !== null && q.myBest !== undefined ? `<div class="qz-best"><span class="qz-ball ok">${svg('medal')}</span><span><b>Ваш лучший результат — ${q.myBest} из ${n}</b><small>${esc((q.levels || [])[levelIndex(q.myBest, n)] || '')}</small></span></div>` : '';
    return head + `<article class="qz-panel">${q.description ? `<span class="qz-muted" style="font-size:15px">${esc(q.description)}</span>` : ''}<div>${rules.map(([i, t, s]) => `<div class="qz-rule"><span class="qz-ball">${svg(i)}</span><span><b>${t}</b><small>${s}</small></span></div>`).join('')}</div></article>`
      + best + `<div class="qz-bottom">${q.loading ? '<span class="qz-cta wait">Загружаем вопросы…</span>' : `<button type="button" class="qz-cta" data-qz-start>Начать тест${svg('chev')}</button>`}</div>`;
  }

  /* ---------- Questions ---------- */
  function start() {
    const q = S.quiz; if (!q || !q.questions || !q.questions.length) return;
    const qs = q.shuffleQuestions ? shuffle(q.questions) : q.questions.slice();
    S.order = qs.map(item => { const idx = item.options.map((_, i) => i); return { ...item, view: q.shuffleOptions ? shuffle(idx) : idx }; });
    S.index = 0; S.picked = -1; S.answers = []; S.result = null; S.startedAt = Date.now();
    go('question');
  }
  function questionHtml() {
    const q = S.order[S.index], total = S.order.length, answered = S.picked >= 0, reveal = answered && S.quiz.showAnswers !== false, last = S.index === total - 1;
    const opts = q.view.map((orig, k) => {
      let cls = '', mark = '';
      if (reveal) { if (orig === q.correct) { cls = 'right'; mark = 'Верно'; } else if (orig === S.picked) { cls = 'wrong'; mark = 'Ваш ответ'; } else cls = 'dim'; }
      else if (answered) cls = orig === S.picked ? 'picked' : 'dim';
      const label = esc(q.options[orig]) + (reveal ? (orig === q.correct ? ', верный ответ' : orig === S.picked ? ', ваш ответ, неверно' : '') : '');
      return `<button type="button" class="qz-opt ${cls}" data-qz-pick="${orig}" aria-label="${label}"${answered ? ' aria-disabled="true"' : ''}><i>${LETTERS[k]}</i><span>${esc(q.options[orig])}</span><small>${mark}</small></button>`;
    }).join('');
    const ok = S.picked === q.correct;
    const feedback = reveal ? `<section class="qz-feedback" aria-live="polite"><span class="qz-pill ${ok ? 'good' : 'bad'}">${ok ? 'Верно!' : 'Не совсем'}</span>${q.explanation ? `<p>${esc(q.explanation)}</p>` : ''}</section>` : '';
    const action = !answered ? '<span class="qz-cta wait">Выберите ответ</span>' : last ? `<button type="button" class="qz-cta" data-qz-finish>Посмотреть результат${svg('chev')}</button>` : `<button type="button" class="qz-cta" data-qz-next>Дальше${svg('chev')}</button>`;
    return `<header class="qz-top"><div style="display:flex;align-items:center;gap:12px"><button type="button" class="qz-round" data-qz-quit aria-label="Выйти из теста">${svg('x')}</button>
      <div class="qz-progress"><span><span>Вопрос ${S.index + 1} из ${total}</span><em>${esc(S.quiz.title)}</em></span><span class="qz-track" role="progressbar" aria-label="Прогресс теста" aria-valuemin="0" aria-valuemax="${total}" aria-valuenow="${S.index + 1}"><i style="width:${Math.round((S.index + 1) / total * 100)}%"></i></span></div></div></header>
      <article class="qz-panel"><h1 class="qz-q">${esc(q.text)}</h1><div class="qz-options" role="group" aria-label="Варианты ответа">${opts}</div></article>${feedback}<div class="qz-bottom">${action}</div>`;
  }
  /* Answering changes the options in place and adds the explanation: nothing is redrawn. */
  function pick(choice) {
    if (S.picked >= 0) return;
    S.picked = choice; S.answers[S.index] = { questionId: S.order[S.index].id, choice };
    const fresh = document.createElement('div'); fresh.innerHTML = questionHtml();
    const now = root.querySelectorAll('.qz-opt'), next = fresh.querySelectorAll('.qz-opt');
    now.forEach((button, i) => {
      button.className = next[i].className;
      button.setAttribute('aria-label', next[i].getAttribute('aria-label'));
      button.setAttribute('aria-disabled', 'true');
      button.querySelector('small').textContent = next[i].querySelector('small').textContent;
    });
    const bottom = root.querySelector('.qz-bottom'), feedback = fresh.querySelector('.qz-feedback');
    if (feedback) bottom.before(feedback);
    bottom.replaceWith(fresh.querySelector('.qz-bottom'));
    (root.querySelector('.qz-feedback') || root.querySelector('.qz-bottom'))?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
  /* The next question keeps the header: its progress bar slides on, the cards rise. */
  function next() {
    S.index++; S.picked = -1;
    const head = root.querySelector('.qz-top');
    const fresh = document.createElement('div'); fresh.innerHTML = questionHtml();
    const freshHead = fresh.querySelector('.qz-top');
    if (head && freshHead && head.querySelector('.qz-track')) {
      head.querySelector('.qz-progress > span').innerHTML = freshHead.querySelector('.qz-progress > span').innerHTML;
      const track = head.querySelector('.qz-track');
      track.setAttribute('aria-valuenow', String(S.index + 1));
      track.querySelector('i').style.width = freshHead.querySelector('.qz-track i').style.width;
      [...root.children].forEach(child => { if (child !== head) child.remove(); });
      const cards = [...fresh.children].filter(child => child !== freshHead);
      root.append(...cards); rise(cards);
    } else paint(true);
    window.scrollTo(0, 0);
  }

  /* ---------- Result ---------- */
  async function finish() {
    const total = S.order.length, right = S.order.filter((q, i) => S.answers[i] && S.answers[i].choice === q.correct).length;
    const seconds = Math.round((Date.now() - S.startedAt) / 1000);
    S.result = { right, total, seconds, best: Math.max(right, S.quiz.myBest ?? 0), saved: false, note: '' };
    go('result');
    if (guest()) { S.result.note = 'guest'; morph(resultHtml()); return; }
    S.saving = true;
    try {
      const response = await call(`quizzes/${S.quiz.id}/attempts`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ userId: state.userId, answers: S.answers, durationSec: seconds }) });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw Error(data.error || 'Не удалось сохранить результат');
      Object.assign(S.result, { right: data.correct, total: data.total, best: data.best, saved: true });
      const item = (S.list || []).find(q => q.id === S.quiz.id); if (item) { item.myBest = data.best; item.myTries = (item.myTries || 0) + 1; }
      S.quiz.myBest = data.best;
    } catch (error) { S.result.note = error.message; }
    S.saving = false;
    if (S.screen === 'result') morph(resultHtml());
  }
  function resultHtml() {
    const r = S.result, q = S.quiz, li = levelIndex(r.right, r.total), levels = q.levels || ['Начинающий', 'Знающий', 'Хорошие знания', 'Отлично'], ranges = levelRanges(r.total);
    const C = 2 * Math.PI * 52, share = r.total ? r.right / r.total : 0;
    const mistakes = S.order.map((item, i) => ({ item, a: S.answers[i] })).filter(({ item, a }) => !a || a.choice !== item.correct);
    const time = `${Math.floor(r.seconds / 60)}:${String(r.seconds % 60).padStart(2, '0')}`;
    const lines = ['Не беда — посмотрите разбор и попробуйте снова.', 'Хорошее начало. Разбор ошибок поможет закрепить знания.', 'Вы уверенно знаете тему. Посмотрите разбор — и попробуйте снова.', 'Все ответы верные — ма ша Аллах!'];
    const saveNote = r.note === 'guest' ? `<div class="qz-best"><span class="qz-ball">${svg('medal')}</span><span><b>Результат не сохранён</b><small>Войдите в профиль, чтобы сохранять лучший результат.</small></span></div>`
      : r.note ? `<div class="qz-best"><span class="qz-ball">${svg('x')}</span><span><b>Не удалось сохранить</b><small>${esc(r.note)}</small></span></div>` : '';
    return `<header class="qz-top"><button type="button" class="qz-round" data-qz-back aria-label="К тестам">${svg('back')}</button><div class="qz-title"><span>${esc(q.title)}</span><h2>Тест пройден</h2><em>${r.right === r.total ? 'ма ша Аллах!' : 'ваш результат'}</em></div></header>
      <article class="qz-panel" style="align-items:center;gap:18px">
        <span class="qz-ring" role="img" aria-label="${r.right} правильных ответов из ${r.total}"><svg width="132" height="132" viewBox="0 0 132 132" aria-hidden="true"><circle cx="66" cy="66" r="52" fill="none" stroke="rgba(16,22,26,.08)" stroke-width="12"></circle><circle cx="66" cy="66" r="52" fill="none" stroke="currentColor" stroke-width="12" stroke-linecap="round" stroke-dasharray="${(C * share).toFixed(1)} ${C.toFixed(1)}"></circle></svg><span><b>${r.right}</b><small>из ${r.total}</small></span></span>
        <span style="display:flex;flex-direction:column;align-items:center;gap:6px;text-align:center"><span class="qz-pill ${li >= 2 ? 'good' : li === 1 ? 'new' : 'bad'}">${esc(levels[li])}</span><span class="qz-muted" style="font-size:15px;max-width:290px">${lines[li]}</span></span>
        <div class="qz-stats" style="align-self:stretch"><span><b>${Math.round(share * 100)}%</b><small>правильно</small></span><span><b>${time}</b><small>время</small></span><span><b>${r.best}/${r.total}</b><small>лучший</small></span></div>
        <div class="qz-levels" style="align-self:stretch">${levels.map((name, i) => `<span class="${i === li ? 'on' : ''}"><b>${esc(name)}</b><small>${ranges[i]}</small></span>`).join('')}</div>
      </article>${saveNote}
      ${mistakes.length ? `<section class="qz-review" aria-label="Разбор ошибок"><b>Разбор ошибок · ${mistakes.length}</b>${mistakes.map(({ item, a }) => `<div class="qz-mistake"><b>${esc(item.text)}</b>${a ? `<span class="no">${svg('x', 2)}Ваш ответ: ${esc(item.options[a.choice] ?? '—')}</span>` : ''}<span class="yes">${svg('check', 2)}Верно: ${esc(item.options[item.correct])}</span>${item.explanation ? `<p>${esc(item.explanation)}</p>` : ''}</div>`).join('')}</section>` : ''}
      <div class="qz-bottom"><button type="button" class="qz-cta" data-qz-again>${svg('reset')}Пройти ещё раз</button>${r.note === 'guest' ? '<button type="button" class="qz-cta ghost" data-qz-login>Войти в профиль</button>' : ''}<button type="button" class="qz-cta ghost" data-qz-back>Все тесты</button></div>`;
  }

  /* Leaving mid-test asks in the app's own sheet, not the browser's dialog. */
  function askQuit() {
    if (document.querySelector('.qz-sheet')) return;
    const left = S.order.length - S.answers.filter(Boolean).length;
    const layer = document.createElement('div'); layer.className = 'qz-sheet';
    layer.innerHTML = `<section role="dialog" aria-modal="true" aria-labelledby="qz-quit-title"><i aria-hidden="true"></i><h2 id="qz-quit-title">Выйти из теста?</h2><p>Ответы этой попытки не сохранятся.${left > 0 ? ` До конца ${left === 1 ? 'остался 1 вопрос' : `осталось ${left} ${plural(left, 'вопрос', 'вопроса', 'вопросов')}`}.` : ''}</p><button type="button" class="stay" data-stay>Продолжить тест</button><button type="button" class="leave" data-leave>Выйти</button></section>`;
    const close = () => { layer.remove(); document.removeEventListener('keydown', onKey); };
    const onKey = event => { if (event.key === 'Escape') close(); };
    layer.addEventListener('click', event => {
      if (event.target === layer || event.target.closest('[data-stay]')) close();
      if (event.target.closest('[data-leave]')) { close(); go('list'); loadList(); }
    });
    document.addEventListener('keydown', onKey);
    document.body.append(layer); layer.querySelector('[data-stay]').focus();
  }

  /* ---------- Wiring ---------- */
  document.addEventListener('click', event => {
    if (!root || !root.contains(event.target)) return;
    const t = event.target;
    const open = t.closest('[data-qz-open]'); if (open) { openQuiz(open.dataset.qzOpen); return; }
    const cat = t.closest('[data-qz-cat]'); if (cat) { S.category = cat.dataset.qzCat; morph(listHtml()); return; }
    if (t.closest('[data-qz-retry]')) { S.failed = false; paint(false); loadList(); return; }
    if (t.closest('[data-qz-back]')) { go('list'); loadList(); return; }
    if (t.closest('[data-qz-start]') || t.closest('[data-qz-again]')) { start(); return; }
    if (t.closest('[data-qz-quit]')) { if (S.answers.length) askQuit(); else { go('list'); loadList(); } return; }
    const p = t.closest('[data-qz-pick]'); if (p) { pick(Number(p.dataset.qzPick)); return; }
    if (t.closest('[data-qz-next]')) { next(); return; }
    if (t.closest('[data-qz-finish]')) { finish(); return; }
    if (t.closest('[data-qz-login]') && typeof openAuth === 'function') { openAuth('login'); }
  });

  window.ChetQuizzes = {
    /* Called each time the tab opens: the list refreshes, a test in progress stays. */
    show() {
      view = document.querySelector('#quiz-view');
      if (!view) return;
      root = view.querySelector('.qz-root');
      if (!root) { root = document.createElement('div'); root.className = 'qz-root'; view.append(root); }
      if (!document.querySelector('#qz-style')) { const style = document.createElement('style'); style.id = 'qz-style'; style.textContent = CSS; document.head.appendChild(style); }
      /* Back on the tab: a test in progress continues, anything else returns to the list. */
      if (S.screen === 'intro' || S.screen === 'result') S.screen = 'list';
      if (S.screen === 'list') { paint(true); loadList(); } else paint(false);
    },
    hide() { setFocus(false); document.querySelector('.qz-sheet')?.remove(); }
  };
})();
