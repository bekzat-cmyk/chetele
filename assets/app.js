    const APP_VERSION = '2.13';
    const appLocale = () => window.cheteleLocale || 'ru-RU';
    /* Header title: weekday and date of the day that is open, e.g. «Среда, 21 октября». */
    window.cheteleHeaderTitle = () => { if(typeof entryDateKey==='undefined') return ''; const text=new Intl.DateTimeFormat(appLocale(),{weekday:'long',day:'numeric',month:'short'}).format(new Date(`${entryDateKey}T12:00:00`)).replace(/\.$/,''); return text.charAt(0).toUpperCase()+text.slice(1); };
    const NAFIL = [
      { id:'asr', label:'Аср' }, { id:'kuptan', label:'Иша' },
      { id:'avvabin', label:'Аввабин' }, { id:'duha', label:'Духа' }
    ];
    const TASKS = [
      { id:'quran', title:'Куран', sub:'Сколько страниц прочитали?', type:'number', suffix:'стр.' },
      { id:'jauchan', title:'Жавшан', sub:'Сколько страниц прочитали?', type:'number', suffix:'стр.' },
      { id:'kitap', title:'Книги', sub:'Сколько страниц прочитали?', type:'number', suffix:'стр.' },
      { id:'nafil', title:'Нафил намаз', sub:'Отмечайте каждый совершённый намаз', type:'nafil' },
      { id:'tahajjud', title:'Тахаджуд', sub:'Ночной намаз', type:'yesno' },
      { id:'fast', title:'Пост', sub:'Добровольный пост', type:'yesno' },
      { id:'salawat', title:'Салават', sub:'Количество прочитанных', type:'number', suffix:'раз' },
      { id:'tasbihat', title:'Зикр', sub:'Количество прочитанных', type:'number', suffix:'раз' }
    ];
    const QAZA = ['Багымдат','Пешим','Аср','Шам','Куптан','Витр'];
    /* Saved qaza keys stay as they are; the screen shows the common Russian prayer names. */
    const QAZA_LABEL = {'Багымдат':'Фаджр','Пешим':'Зухр','Аср':'Аср','Шам':'Магриб','Куптан':'Иша','Витр':'Витр'};
    /* Lucide icons (ISC licence). Rendered as stroke SVG in the current text colour. */
    const ICON_PATHS = {
      /* Soft glass icons drawn for the design canvas: 24 grid, 1.6 stroke. */
      softQuran:'<path d="M12 6.5C10 5 7.3 4.6 4.5 5.2v9.6c2.8-.6 5.5-.2 7.5 1.3 2-1.5 4.7-1.9 7.5-1.3V5.2C16.7 4.6 14 5 12 6.5zM12 6.5v9.6M6 20.5l12-4M18 20.5l-12-4"/>',
      softJauchan:'<path d="M8 4h9a3 3 0 0 1 3 3v1h-3M8 4a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3M8 4a3 3 0 0 0-3 3v1h3M8 20h8a3 3 0 0 0 3-3v-1h-5M11 10h4M11 13.5h3"/>',
      softKitap:'<path d="M5 4.5A1.5 1.5 0 0 1 6.5 3H19v15H6.5A1.5 1.5 0 0 0 5 19.5zM5 19.5A1.5 1.5 0 0 0 6.5 21H19M10 3v6.5l2-1.5 2 1.5V3"/>',
      softFast:'<path d="M13.9 3.1a5.27 5.27 0 1 0 3.41 9.3 4.34 4.34 0 0 1-3.41-9.3z"/><path d="M3.5 17.5h17M7.5 20.5h9"/>',
      softTahajjud:'<path d="M19 14.5A7.5 7.5 0 0 1 9.5 5a7.5 7.5 0 1 0 9.5 9.5zM16.5 3.5l.5 1.3 1.3.5-1.3.5-.5 1.3-.5-1.3-1.3-.5 1.3-.5z"/>',
      softNafil:'<path d="M6 3h12v18H6zM9 18h6M12 7.5l2.5 3-2.5 3-2.5-3z"/>',
      softSalawat:'<path d="M6.5 9a5.5 5.5 0 1 0 11.0 0a5.5 5.5 0 1 0 -11.0 0M12 14.5v2M10.5 18a1.5 1.5 0 1 0 3.0 0a1.5 1.5 0 1 0 -3.0 0M11 19.5l-1 2M13 19.5l1 2"/>',
      softZikr:'<path d="M8 3h8a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3zM8.5 7h7v4h-7zM10 16a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>',
      softQaza:'<path d="M3.5 12a8.5 8.5 0 1 0 2.5-6M3.5 4v4h4M12 8v4l3 2"/>',
      softMedal:'<path d="M6 9a6 6 0 1 0 12 0a6 6 0 1 0 -12 0M8.5 13.8 7 21l5-2.5 5 2.5-1.5-7.2"/>',
      softChevron:'<path d="M9.5 6l6 6-6 6"/>',
      softFlame:'<path d="M12 3c1 3.5 5 5.5 5 10a5 5 0 0 1-10 0c0-2.5 1.5-4 2.5-5 .3 1.6 1 2.6 2 3 0-3 .5-5.5.5-8z"/>',
      softStats:'<path d="M4 20h16M7 16v-5M12 16V6M17 16v-8"/>',
      softBell:'<path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0"/>',
      softSettings:'<path d="M4 7h10M18 7h2M4 17h4M12 17h8M14 5v4M8 15v4"/>',
      softInfo:'<path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0M12 11v5M12 8v.01"/>',
      softPencil:'<path d="M16.5 3.5l4 4L8 20H4v-4zM14 6l4 4"/>',
      softExit:'<path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4M10 16l4-4-4-4M14 12H4"/>',
      softMoon:'<path d="M19 14.5A7.5 7.5 0 0 1 9.5 5a7.5 7.5 0 1 0 9.5 9.5z"/>',
      softSend:'<path d="M4 12l16-8-6 16-2.5-6.5z"/>',
      softPin:'<path d="M12 21s6.5-5.6 6.5-11a6.5 6.5 0 0 0-13 0c0 5.4 6.5 11 6.5 11zM9.7 10a2.3 2.3 0 1 0 4.6 0a2.3 2.3 0 1 0 -4.6 0"/>',
      softLocate:'<path d="M12 3v3M12 18v3M3 12h3M18 12h3M7 12a5 5 0 1 0 10 0a5 5 0 1 0 -10 0"/>',
      softDeeds:'<path d="M4 6.5l1.5 1.5L8 5.5M4 12.5 5.5 14 8 11.5M4 18.5 5.5 20 8 17.5M11 7h9M11 13h9M11 19h9"/>',
      softShield:'<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/>',
      softEvents:'<path d="M5 21V4M5 4h11l-2 3.5L16 11H5"/>',
      softSpark:'<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/>',
      softWifiOff:'<path d="M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0M12 19.5h.01M3 3l18 18"/>',
      softList:'<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
      softStar:'<path d="M12 3.5l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8z"/>',
      softBack:'<path d="M14.5 6l-6 6 6 6"/>',
      softMinus:'<path d="M5 12h14"/>',
      softReset:'<path d="M3.5 12a8.5 8.5 0 1 0 2.5-6M3.5 4v4h4"/>',
      softClock:'<path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0M12 7.5V12l3 2"/>',
      softRating:'<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 6h2.5v1.5A3 3 0 0 1 17 10.5M7 6H4.5v1.5A3 3 0 0 0 7 10.5"/>',
      bookOpen:'<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
      scroll:'<path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/>',
      book:'<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/>',
      sparkles:'<path d="M9.94 15.5A2 2 0 0 0 8.5 14.06l-6.13-1.58a.5.5 0 0 1 0-.96L8.5 9.94A2 2 0 0 0 9.94 8.5l1.58-6.13a.5.5 0 0 1 .96 0L14.06 8.5A2 2 0 0 0 15.5 9.94l6.13 1.58a.5.5 0 0 1 0 .96L15.5 14.06a2 2 0 0 0-1.44 1.44l-1.58 6.13a.5.5 0 0 1-.96 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/>',
      repeat:'<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
      moonStar:'<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9"/><path d="M20 3v4"/><path d="M22 5h-4"/>',
      moon:'<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
      check:'<path d="M20 6 9 17l-5-5"/>',
      circleCheck:'<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
      chevronLeft:'<path d="m15 18-6-6 6-6"/>',
      chevronRight:'<path d="m9 18 6-6-6-6"/>',
      rotateCcw:'<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
      star:'<path d="M11.53 2.3a.53.53 0 0 1 .95 0l2.31 4.68a2.12 2.12 0 0 0 1.6 1.16l5.16.76a.53.53 0 0 1 .3.9l-3.74 3.64a2.12 2.12 0 0 0-.61 1.88l.88 5.14a.53.53 0 0 1-.77.56l-4.62-2.43a2.12 2.12 0 0 0-1.97 0L6.4 21.01a.53.53 0 0 1-.77-.56l.88-5.13a2.12 2.12 0 0 0-.61-1.88L2.16 9.8a.53.53 0 0 1 .29-.9l5.17-.76a2.12 2.12 0 0 0 1.6-1.16z"/>',
      calendarCheck:'<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
      target:'<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
      calendarDays:'<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/>',
      flame:'<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
      sunrise:'<path d="M12 2v8"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m19.07 10.93-1.41 1.41"/><path d="M22 22H2"/><path d="m8 6 4-4 4 4"/><path d="M16 18a4 4 0 0 0-8 0"/>',
      sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
      cloudSun:'<path d="M12 2v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="M20 12h2"/><path d="m19.07 4.93-1.41 1.41"/><path d="M15.95 12.65a4 4 0 0 0-5.93-4.13"/><path d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z"/>',
      sunset:'<path d="M12 10V2"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m19.07 10.93-1.41 1.41"/><path d="M22 22H2"/><path d="m16 6-4 4-4-4"/><path d="M16 18a4 4 0 0 0-8 0"/>'
    };
    const icon = name => `<svg class="lucide" viewBox="0 0 24 24" aria-hidden="true">${ICON_PATHS[name]||''}</svg>`;
    const storeKey = 'niet-app-data-v1';
    const cacheVersionKey = 'niet-cache-version';
    const cacheVersion = '2026-09-14-preserve-account-1';
    const defaultReminderSettings = () => ({ slots:[{id:'start',label:'Начать путь',time:'12:00',enabled:true},{id:'continue',label:'Продолжить',time:'16:30',enabled:true},{id:'finish',label:'Подвести итог',time:'20:00',enabled:true}], quiet:{enabled:false,start:'22:00',end:'08:00'}, prayer:{enabled:false,before:0,prayers:{fajr:true,dhuhr:true,asr:true,maghrib:true,isha:true}} });
    const blankState = () => ({ name:'', login:'', tasks:{}, qaza:{}, history:{}, drafts:{}, activeDate:'', celebratedDays:{}, minimumCelebratedDays:{}, seenAchievements:{}, achievementBaselineReady:false, score:0, streak:0, bestStreak:0, streakLastDate:'', lastDate:'', reminderSettings:defaultReminderSettings() });
    function markClientVersion(){
      try { localStorage.setItem(cacheVersionKey,cacheVersion); }
      catch { /* Браузер может запрещать локальное хранилище. */ }
    }
    function loadState(){
      try { return { ...blankState(), ...JSON.parse(localStorage.getItem(storeKey) || sessionStorage.getItem(storeKey) || '{}') }; }
      catch { return blankState(); }
    }
    markClientVersion();
    let state = loadState();
    let registrationPassword='';
    let registrationError='';
    /* The place prayer times are counted for (chosen under «Время намазов», kept on this phone),
       sent with the reminder settings so the server notifies at the same minutes. */
    function chosenPrayerPlace(){
      const P=window.ChetPrayer, fallback={lat:42.8746,lng:74.5698,city:'Бишкек'};
      try { const saved=JSON.parse(localStorage.getItem('chetele-prayer-place')||'null');
        if(saved?.id==='geo'&&Number.isFinite(saved.lat)&&Number.isFinite(saved.lng)) return {lat:saved.lat,lng:saved.lng,city:saved.name||'Моё место'};
        const city=P?.CITIES.find(item=>item.id===saved?.id)||P?.CITIES[0]; return city?{lat:city.lat,lng:city.lng,city:city.name}:fallback;
      } catch { return fallback; }
    }
    function reminderSettings(){
      const defaults=defaultReminderSettings(), saved=state.reminderSettings||{}, slots=Array.isArray(saved.slots)?saved.slots:[];
      return {slots:defaults.slots.map(slot=>{ const value=slots.find(item=>item?.id===slot.id)||{}; return {...slot,time:/^([01]\d|2[0-3]):[0-5]\d$/.test(value.time)?value.time:slot.time,enabled:value.enabled!==false}; }),quiet:{enabled:!!saved.quiet?.enabled,start:/^([01]\d|2[0-3]):[0-5]\d$/.test(saved.quiet?.start)?saved.quiet.start:defaults.quiet.start,end:/^([01]\d|2[0-3]):[0-5]\d$/.test(saved.quiet?.end)?saved.quiet.end:defaults.quiet.end},prayer:{enabled:saved.prayer?.enabled===true,before:[0,5,10,15].includes(saved.prayer?.before)?saved.prayer.before:0,prayers:Object.fromEntries(Object.keys(defaults.prayer.prayers).map(id=>[id,saved.prayer?.prayers?.[id]!==false])),...chosenPrayerPlace()}};
    }
    state.reminderSettings=reminderSettings();
    const apiUrl = path => `/api/index.php?path=${encodeURIComponent(path)}`;
    /* Adds the session token to every API call. A 401 on a request that carried
       the current token means the session is gone: the user signs in again. */
    async function apiFetch(url, options={}){
      const token=state.token;
      const response=await fetch(url,{...options,headers:{...(options.headers||{}),...(token?{'X-Auth-Token':token}:{})}});
      if(response.status===401&&token&&token===state.token) requireRelogin();
      if(!(response.headers.get('content-type')||'').includes('json')) isHostingStub(response).then(stub=>{ if(stub) showMaintenance(); });
      return response;
    }
    /* Segmented controls and switches that are drawn again after a tap start where they were
       and slide to the new place, the way the fasting choice does. */
    function controlKey(el){ return `${el.closest('[id]')?.id||''}|${el.dataset.setting||el.dataset.settingToggle||el.getAttribute('aria-label')||''}`; }
    const segIndex=seg=>[...seg.querySelectorAll(':scope>button')].findIndex(button=>button.classList.contains('active'));
    function controlStates(){
      const states=new Map();
      document.querySelectorAll('.glass-seg').forEach(seg=>states.set(`seg:${controlKey(seg)}`,segIndex(seg)));
      document.querySelectorAll('.st-switch input').forEach(input=>states.set(`sw:${controlKey(input)}`,input.checked));
      document.querySelectorAll('.prayer-tahajjud .pill').forEach(pill=>states.set(`pill:${controlKey(pill)}`,pill.querySelector('.active')?.dataset.choice||'no'));
      return states;
    }
    /* What each control showed after the last drawing: a tap has already flipped a checkbox
       by the time the screen is drawn again, so the live state cannot tell where it was. */
    let shownControls=new Map();
    function slideControls(){
      const before=shownControls, started=[];
      document.querySelectorAll('.glass-seg').forEach(seg=>{
        const was=before.get(`seg:${controlKey(seg)}`), now=segIndex(seg);
        if(was==null||was<0||now<0||was===now) return;
        seg.style.setProperty('--seg-from',`calc(${was} * (100% + 4px))`); seg.classList.add('seg-from'); started.push(()=>seg.classList.remove('seg-from'));
      });
      document.querySelectorAll('.st-switch input').forEach(input=>{
        const was=before.get(`sw:${controlKey(input)}`);
        if(was==null||was===input.checked) return;
        const box=input.closest('.st-switch'); box.classList.add(was?'from-on':'from-off'); started.push(()=>box.classList.remove('from-on','from-off'));
      });
      shownControls=controlStates();
      document.querySelectorAll('.prayer-tahajjud .pill').forEach(pill=>{
        const was=before.get(`pill:${controlKey(pill)}`), now=pill.querySelector('.active')?.dataset.choice||'no';
        if(was==null||was===now) return;
        pill.classList.add(`from-${was}`); started.push(()=>pill.classList.remove('from-yes','from-no'));
      });
      if(!started.length) return;
      document.body.offsetWidth;
      requestAnimationFrame(()=>requestAnimationFrame(()=>started.forEach(run=>run())));
    }
    /* When the hosting switches the site off, its own HTML page answers instead of the API.
       The app then shows its own maintenance screen and checks every 30 s until the server is
       back, then reloads. Marks keep being saved on the phone meanwhile. */
    const maintenance={shown:false,dismissed:false,timer:0};
    async function isHostingStub(response){
      const type=response.headers.get('content-type')||'';
      if(type.includes('json')||!type.includes('html')) return false;
      if([502,503,504].includes(response.status)) return true;
      try { return /ISPsystem|Аккаунт отключ|account (is )?(suspended|disabled|blocked)/i.test(await response.clone().text()); } catch { return false; }
    }
    async function probeServer(){
      try {
        const response=await fetch(`${apiUrl('runtime')}&nc=${Date.now()}`,{cache:'no-store'});
        if(await isHostingStub(response)) return 'down';
        return (response.headers.get('content-type')||'').includes('json')?'up':'unknown';
      } catch { return 'offline'; }
    }
    function showMaintenance(){
      if(maintenance.shown||maintenance.dismissed) return;
      maintenance.shown=true;
      const layer=document.createElement('div'); layer.className='maint'; layer.id='maintenance-screen';
      layer.innerHTML=`<section role="alertdialog" aria-modal="true" aria-labelledby="maint-title" aria-describedby="maint-text">
        <div class="maint-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
        <h1 id="maint-title">Технические работы</h1>
        <p id="maint-text">Сервер Chetele сейчас недоступен. Мы уже занимаемся этим — скоро всё заработает.</p>
        <div class="maint-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span>Отметки сохраняются на телефоне и отправятся, когда сервер вернётся.</span></div>
        <button type="button" class="maint-retry" data-maint-retry>Проверить снова</button>
        <button type="button" class="maint-close" data-maint-close>Продолжить без сервера</button>
        <small role="status" data-maint-status>Проверяем автоматически каждые 30 секунд</small>
      </section>`;
      document.body.appendChild(layer);
      const status=layer.querySelector('[data-maint-status]'), retry=layer.querySelector('[data-maint-retry]');
      const check=async manual=>{
        if(manual){ retry.disabled=true; status.textContent='Проверяем…'; }
        const result=await probeServer();
        if(result==='up'){ location.reload(); return; }
        if(manual){ retry.disabled=false; status.textContent=result==='offline'?'Нет интернета на телефоне':'Сервер пока недоступен'; }
      };
      retry.addEventListener('click',()=>check(true));
      layer.querySelector('[data-maint-close]').addEventListener('click',()=>{ maintenance.dismissed=true; maintenance.shown=false; clearInterval(maintenance.timer); layer.remove(); });
      maintenance.timer=setInterval(()=>check(false),30000);
      setTimeout(()=>retry.focus(),50);
    }
    /* The service worker leaves a flag when it opened the saved app instead of the hosting's page. */
    if('caches' in window) caches.match('/__maintenance').then(flag=>{ if(flag) probeServer().then(result=>{ if(result==='down') showMaintenance(); }); }).catch(()=>{});
    const localDateKey = () => { const d=new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; };
    let todayKey = localDateKey();
    let entryDateKey = state.activeDate || todayKey;
    if(entryDateKey!==todayKey){
      state.drafts={...(state.drafts||{}),[entryDateKey]:{tasks:state.tasks||{},qaza:state.qaza||{},completed:ChetDeeds.countDeeds(state.tasks||{},state.qaza||{}),max:ChetDeeds.DAILY_MAX}};
      const todayDraft=state.drafts[todayKey];
      state.tasks=todayDraft?.tasks||{}; state.qaza=todayDraft?.qaza||{};
      entryDateKey=todayKey; state.activeDate=todayKey;
    }
    let syncTimer;
    /* Rewards already shown (minimum, all deeds, badges) are kept per account apart from the
       account data, so signing out and back in does not show them again. */
    const celebrationsKey='chetele-celebrations';
    function rememberCelebrations(){
      if(!state.userId) return;
      try {
        const all=JSON.parse(localStorage.getItem(celebrationsKey)||'{}'), recent=days=>Object.fromEntries(Object.entries(days||{}).sort().slice(-60));
        all[state.userId]={celebratedDays:recent(state.celebratedDays),minimumCelebratedDays:recent(state.minimumCelebratedDays),seenAchievements:state.seenAchievements||{}};
        localStorage.setItem(celebrationsKey,JSON.stringify(all));
      } catch {}
    }
    function restoreCelebrations(){
      try {
        const saved=JSON.parse(localStorage.getItem(celebrationsKey)||'{}')[state.userId]; if(!saved) return;
        state.celebratedDays={...(saved.celebratedDays||{}),...(state.celebratedDays||{})};
        state.minimumCelebratedDays={...(saved.minimumCelebratedDays||{}),...(state.minimumCelebratedDays||{})};
        state.seenAchievements={...(saved.seenAchievements||{}),...(state.seenAchievements||{})};
      } catch {}
    }
    /* Badges the account already has count as seen once its days are known. */
    function settleAchievementBaseline(){
      if(state.achievementBaselineReady) return;
      state.seenAchievements={...(state.seenAchievements||{}),...Object.fromEntries(achievementData().filter(badge=>badge.earned).map(badge=>[badge.title,true]))};
      state.achievementBaselineReady=true;
    }
    function save(){ rememberCelebrations(); try { const value=JSON.stringify(state); if(state.rememberMe!==false){ localStorage.setItem(storeKey,value); sessionStorage.removeItem(storeKey); } else { sessionStorage.setItem(storeKey,value); localStorage.removeItem(storeKey); } } catch { /* Работает и в режиме без сохранения данных. */ } queueSync(); }
    function resetIfNewDay(){
      todayKey=localDateKey();
      if(state.lastDate && state.lastDate !== todayKey){
        stashOpenDay();
        const todayDraft=state.drafts?.[todayKey];
        entryDateKey=todayKey; state.activeDate=todayKey; state.tasks=copyEntry(todayDraft?.tasks); state.qaza=copyEntry(todayDraft?.qaza);
      }
      state.lastDate=todayKey; refreshStreak(); save();
    }
    function previousDateKey(){ const date=new Date(); date.setDate(date.getDate()-1); return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`; }
    function nafilValues(){
      const value=state.tasks.nafil;
      if(value && typeof value==='object') return value;
      return value ? Object.fromEntries(NAFIL.map(item=>[item.id,true])) : {};
    }
    function nafilCompleted(){ return NAFIL.filter(item=>nafilValues()[item.id]).length; }
    function dailyMax(){ return ChetDeeds.DAILY_MAX; }
    function recordDay(date=entryDateKey){
      const count=completed();
      if(!count && !state.history?.[date]) return;
      state.history={...(state.history||{}),[date]:{completed:count,max:ChetDeeds.maxFor(date),tasks:JSON.parse(JSON.stringify(state.tasks||{})),qaza:{...(state.qaza||{})}}};
      const keys=Object.keys(state.history).sort();
      if(keys.length>60) keys.slice(0,keys.length-60).forEach(key=>delete state.history[key]);
    }
    function dateBefore(key){ const [year,month,day]=key.split('-').map(Number),date=new Date(year,month-1,day); date.setDate(date.getDate()-1); return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`; }
    /* Number of deeds in a saved day, counted the same way as completed() for today. */
    function entryCompletedCount(entry){
      if(!entry) return 0;
      if(!entry.tasks) return Number(entry.completed)||0;
      return ChetDeeds.countDeeds(entry.tasks,entry.qaza);
    }
    function entryHasProgress(entry){
      if(Number(entry?.completed)>0) return true;
      const taskValues=Object.values(entry?.tasks||{}).some(value=>value&&typeof value==='object'?Object.values(value).some(Boolean):value===true||value==='yes'||Number(value)>0);
      return taskValues||Object.values(entry?.qaza||{}).some(value=>Number(value)>0);
    }
    function refreshStreak(){
      const history={...(state.history||{})};
      Object.entries(state.drafts||{}).forEach(([date,draft])=>{ if(draft?.tasks) history[date]={...(history[date]||{}),...draft}; });
      (state.remoteEntries||[]).forEach(entry=>{ if(entry?.date) history[entry.date]={...(history[entry.date]||{}),...entry}; });
      if(entryDateKey===todayKey) history[todayKey]={...(history[todayKey]||{}),completed:completed(),tasks:copyEntry(state.tasks),qaza:copyEntry(state.qaza)};
      let cursor=entryHasProgress(history[todayKey])?todayKey:dateBefore(todayKey),count=0;
      while(entryHasProgress(history[cursor])){ count++; cursor=dateBefore(cursor); }
      state.streak=count;
      state.streakLastDate=count?(entryHasProgress(history[todayKey])?todayKey:dateBefore(todayKey)):'';
      let best=0,run=0,previous='';
      Object.keys(history).sort().forEach(key=>{ if(!entryHasProgress(history[key])){ run=0; previous=key; return; } run=previous&&dateBefore(key)===previous?run+1:1; best=Math.max(best,run); previous=key; });
      state.bestStreak=Math.max(Number(state.bestStreak||0),best,count);
    }
    let midnightTimer;
    function scheduleMidnightReset(){
      clearTimeout(midnightTimer);
      const now=new Date(), next=new Date(now); next.setHours(24,0,1,0);
      midnightTimer=setTimeout(()=>{ resetIfNewDay(); render(); scheduleMidnightReset(); },next-now);
    }
    function queueSync(){
      if(!state.userId) return;
      clearTimeout(syncTimer);
      const autosave=document.querySelector('.autosave'); autosave?.classList.remove('is-offline'); autosave?.classList.add('is-saving'); setSaveStatus('Сохраняем…');
      syncTimer=setTimeout(syncCurrentEntry, 700);
    }
    function setSaveStatus(text){ const status=document.querySelector('#save-status'); if(status) status.textContent=text; }
    async function syncCurrentEntry(){
      if(!state.userId) return false;
      /* The server keeps the typed part of salawat and zikr apart from the counter. The
         counter goes first, or the server would take the new total as counted and keep 0. */
      await settleZikr(entryDateKey);
      try {
        const response=await apiFetch(apiUrl(`users/${state.userId}/entries/${entryDateKey}`), {
          method:'PUT', headers:{'Content-Type':'application/json'}, keepalive:true,
          body:JSON.stringify({tasks:state.tasks,qaza:state.qaza,zikrSync:2})
        });
        if(response.status===404){ delete state.userId; save(); ensureRemoteUser(); return null; }
        if(!response.ok) throw new Error('Sync failed');
        document.querySelector('.autosave')?.classList.remove('is-saving'); setSaveStatus('Сохранено в кабинете'); paintDayStates(); refreshVisibleLeaderboards(1500); return true;
      } catch { const autosave=document.querySelector('.autosave'); autosave?.classList.remove('is-saving'); autosave?.classList.add('is-offline'); paintDayStates(); setSaveStatus('Сохранено на телефоне — отправим, когда появится связь'); renderBoard(); return false; }
    }
    async function ensureRemoteUser(){
      if(!state.name) return false;
      if(state.userId){
        refreshVisibleLeaderboards();
        /* On a Home Screen launch, restore the server copy before a local save
           can overwrite today's entry in a separate browser context. */
        fetchPersonalEntries(true).then(restored=>{ if(!restored) queueSync(); });
        return true;
      }
      try {
        if(!registrationPassword) throw new Error('Для создания кабинета снова введите пароль.');
        const response=await apiFetch(apiUrl('users'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({displayName:state.name,login:state.login,password:registrationPassword})});
        if(!response.ok){ const data=await response.json().catch(()=>({})); throw new Error(data.error||'Не удалось создать кабинет.'); }
        const user=await response.json();
        state.userId=user.id; state.login=user.login; state.token=user.token; registrationPassword=''; save(); refreshVisibleLeaderboards(); fetchPersonalEntries(); loadPushStatus(); scheduleZikrFlush(0); uploadGuestDays();
        return true;
      } catch (error) { registrationError=error.message||'Не удалось создать кабинет.'; renderBoard(); return false; }
    }
    /* Leaderboard: week and all time are loaded separately, each with its own status,
       and only for what is on screen. Saving a day refreshes the visible rating once,
       a moment later, instead of fetching both periods after every save. */
    /* A board is a period and what it counts: points, or one deed (salawat, quran, …). */
    const boardStatus={}, boardLoading={};
    const boardKey=(period,metric='points')=>`${period}:${metric}`;
    // Points boards keep their old places in state; boards for one deed live in state.metricBoards.
    function boardRows(period,metric='points'){
      if(metric!=='points') return state.metricBoards?.[boardKey(period,metric)];
      return period==='week'?state.weekLeaderboard:period==='month'?state.monthLeaderboard:state.remoteLeaderboard;
    }
    function loadLeaderboard(period,metric='points'){
      const key=boardKey(period,metric);
      if(boardLoading[key]) return boardLoading[key];
      if(boardStatus[key]!=='online') boardStatus[key]='loading';
      renderBoard();
      boardLoading[key]=(async()=>{
        try {
          const response=await apiFetch(`${apiUrl('leaderboard')}&limit=50${period==='all'?'':`&period=${period}`}${metric==='points'?'':`&metric=${metric}`}`);
          if(!response.ok) throw new Error('leaderboard_failed');
          const rows=await response.json();
          if(!Array.isArray(rows)) throw new Error('leaderboard_failed');
          if(metric!=='points') state.metricBoards={...state.metricBoards,[key]:rows};
          else if(period==='week') state.weekLeaderboard=rows; else if(period==='month') state.monthLeaderboard=rows; else state.remoteLeaderboard=rows;
          state.ratingTotal={...state.ratingTotal,[key]:Number(response.headers.get('X-Total-Count'))||0};
          boardStatus[key]='online';
        } catch { boardStatus[key]='offline'; }
        finally { delete boardLoading[key]; renderBoard(); if(key==='all:points') renderProfile(); }
      })();
      return boardLoading[key];
    }
    function visibleBoards(){
      const view=document.querySelector('.view.active')?.id;
      return view==='rating-view'?[[ratingPeriod(),ratingMetric()]]:view==='profile-view'?[['all','points']]:[];
    }
    let boardRefreshTimer=null;
    function refreshVisibleLeaderboards(delay=0){
      clearTimeout(boardRefreshTimer);
      boardRefreshTimer=setTimeout(()=>visibleBoards().forEach(([period,metric])=>loadLeaderboard(period,metric)),delay);
    }
    async function fetchPersonalEntries(restoreCurrent=false){
      if(!state.userId) return;
      try {
        const response=await apiFetch(apiUrl(`users/${state.userId}/entries`));
        if(!response.ok) throw new Error('Entries failed');
        const entries=await response.json();
        if(Array.isArray(entries)){
          let restored=false;
          state.remoteEntries=entries; state.statsOnline=true; settleAchievementBaseline();
          if(restoreCurrent){
            let current=entries.find(entry=>entry.date===todayKey);
            const waiting={...(state.zikrPending?.[todayKey]||{})};
            if(current&&(Object.keys(waiting).length||zikrFlushing)){ const merged=ChetZikrSync.adoptServer(current.tasks||{},current.tasks?.zikr,waiting); current.tasks={...(current.tasks||{}),zikr:merged.zikr,salawat:merged.salawat?String(merged.salawat):'',tasbihat:merged.tasbihat?String(merged.tasbihat):''}; }
            if(current&&zikrFlushing){ restored=true; current=null; }
            if(current){ const done=entryCompletedCount(current); if(done>=3) state.minimumCelebratedDays={...(state.minimumCelebratedDays||{}),[todayKey]:true}; if(done>=dailyMax()) state.celebratedDays={...(state.celebratedDays||{}),[todayKey]:true}; state.tasks={...(current.tasks||{})}; state.qaza={...(current.qaza||{})}; state.activeDate=todayKey; state.lastDate=todayKey; state.history={...(state.history||{}),[todayKey]:{tasks:{...(current.tasks||{})},qaza:{...(current.qaza||{})},completed:0,max:dailyMax()}}; save(); render(); restored=true; }
          }
          refreshStreak(); save(); renderCalendar(); renderAchievementsTeaser(); renderStats(); renderProfile();
          return restored;
        }
      } catch { state.statsOnline=false; renderStats(); }
      return false;
    }
    function isDone(task){ const v=state.tasks[task.id]; return task.type==='number' ? Number(v)>0 : task.type==='yesno' ? v==='yes' : task.type==='nafil' ? nafilCompleted()===NAFIL.length : !!v; }
    function completed(){ return ChetDeeds.countDeeds(state.tasks,state.qaza); }
    /* Day states from the canvas: a nudge when nothing is marked yet today, the streak
       that is waiting for today, and a banner while the server cannot be reached. */
    function paintDayStates(){
      const empty=entryDateKey===todayKey&&completed()===0, streak=Number(state.streak||0);
      document.querySelectorAll('#today-view [data-icon]').forEach(slot=>{ if(!slot.firstChild) slot.innerHTML=icon(slot.dataset.icon); });
      const card=document.querySelector('#empty-day'); if(card) card.hidden=!empty;
      const reminder=document.querySelector('#empty-streak'); if(reminder){ reminder.hidden=!empty||!streak; document.querySelector('#empty-streak-title').textContent=`Серия ${streak} ${streak%10===1&&streak%100!==11?'день':[2,3,4].includes(streak%10)&&![12,13,14].includes(streak%100)?'дня':'дней'}`; }
      const banner=document.querySelector('#offline-banner'); if(banner) banner.hidden=navigator.onLine&&!document.querySelector('.autosave')?.classList.contains('is-offline');
    }
    window.addEventListener('online',()=>paintDayStates()); window.addEventListener('offline',()=>paintDayStates());
    document.addEventListener('click',event=>{
      const quick=event.target.closest('[data-empty-focus]'); if(!quick) return;
      const field=document.querySelector(`#task-list [data-id="${quick.dataset.emptyFocus}"]`); if(!field) return;
      field.scrollIntoView({behavior:motionOff()?'auto':'smooth',block:'center'}); setTimeout(()=>field.focus({preventScroll:true}),motionOff()?0:400);
    });
    /* Voluntary fast: one question, suhur and iftar from the prayer times, and the next Sunnah day. */
    function fastTimes(date){
      const P=window.ChetPrayer; if(!P) return null;
      let where=P.CITIES[0];
      try { const saved=JSON.parse(localStorage.getItem('chetele-prayer-place')||'null'); where=saved?.id==='geo'&&Number.isFinite(saved.lat)?{lat:saved.lat,lng:saved.lng}:(P.CITIES.find(city=>city.id===saved?.id)||where); } catch {}
      const day=P.timesFor(date,where), pick=id=>day.find(item=>item.id===id)?.time;
      return {suhur:pick('fajr'),iftar:pick('maghrib')};
    }
    let shownFast;
    function fastGroup(){
      const answer=state.tasks.fast||'', from=shownFast===undefined?answer:shownFast, changed=from!==answer&&!motionOff(); shownFast=answer;
      const date=new Date(`${entryDateKey}T12:00:00`), isToday=entryDateKey===todayKey;
      const clock=time=>time?`${String(time.getHours()).padStart(2,'0')}:${String(time.getMinutes()).padStart(2,'0')}`:'—';
      const times=fastTimes(date), hint=window.ChetFasting?.hint(date,appLocale());
      let note='';
      if(answer==='yes'){
        const left=isToday&&times?.iftar?Math.round((times.iftar-new Date())/60000):0;
        note=`<p class="fast-note${changed?' entering':''}"><i>${icon('check')}</i>${left>0?`Пусть Аллах примет ваш пост. Ифтар через ${left>=60?`${Math.floor(left/60)} ч `:''}${left%60} мин`:'Пусть Аллах примет ваш пост.'}</p>`;
      }
      const group=document.createElement('section'); group.className='prayer-group fast-group';
      group.innerHTML=`<div class="group-head"><h3>Пост</h3><span>добровольный</span></div><div class="prayer-body fast-body"><div class="fast-top"><span class="task-icon${answer==='yes'?' on':''}${changed?' pop':''}">${icon('softFast')}</span><div class="task-copy"><div class="task-title">${isToday?'Вы сегодня постились?':'Вы постились в этот день?'}</div><div class="task-sub">${times?`Сухур до ${clock(times.suhur)} · ифтар в ${clock(times.iftar)}`:'Добровольный пост'}</div></div></div><div class="fast-choice" role="radiogroup" aria-label="Пост"><span class="fast-thumb" data-pos="${from||''}" aria-hidden="true"></span><button type="button" role="radio" data-choice="no" data-id="fast" aria-checked="${answer==='no'}" class="${answer==='no'?'active':''}">Не постился</button><button type="button" role="radio" data-choice="yes" data-id="fast" aria-checked="${answer==='yes'}" class="${answer==='yes'?'active':''}">Да, пощусь</button></div>${note}${hint?`<p class="fast-hint${hint.forbidden?' warn':''}"><i>${icon('softStar')}</i><span>${escapeHtml(hint.text)}${hint.next?` Ближайший: <b>${escapeHtml(hint.next)}</b>`:''}</span></p>`:''}</div>`;
      /* The thumb starts where the last answer was and slides to the new one. */
      if(changed) requestAnimationFrame(()=>requestAnimationFrame(()=>{ const thumb=group.querySelector('.fast-thumb'); if(thumb) thumb.dataset.pos=answer||''; }));
      else group.querySelector('.fast-thumb').dataset.pos=answer||'';
      return group;
    }
    function render(){
      paintDayStates();
      if(typeof paintHadithRead==='function') paintHadithRead();
      document.querySelector('#welcome').textContent = state.name?`Ассаламу алейкум, ${state.name}`:'Ассаламу алейкум';
      paintGuest();
      renderProfile(); renderAchievementsTeaser();
      if(!state.achievementBaselineReady&&(!state.userId||Array.isArray(state.remoteEntries))){ settleAchievementBaseline(); save(); }
      const activeDate=new Date(`${entryDateKey}T12:00:00`), isToday=entryDateKey===todayKey;
      document.querySelector('#tasks-heading').textContent=isToday?'Сегодняшние дела':'Дела за выбранный день';
      document.querySelector('#top-day').textContent=appLocale()==='ky-KG'?(isToday?'Бүгүн':'Тандалган күн'):(isToday?'Сегодня':'Выбранный день');
      const initial=String(state.name||'').trim().charAt(0).toUpperCase(), avatar=document.querySelector('#avatar');
      if(initial&&state.userId){ avatar.textContent=initial; avatar.classList.add('has-initial'); avatar.setAttribute('aria-label','Открыть профиль'); }
      else if(avatar.classList.contains('has-initial')||!avatar.firstElementChild){ avatar.innerHTML=GUEST_ICON; avatar.classList.remove('has-initial'); avatar.setAttribute('aria-label','Профиль гостя'); }
      document.querySelector('#today-date').textContent = new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long'}).format(activeDate);
      document.querySelector('#tasks-heading').closest('.section-head').classList.toggle('is-today',isToday);
      const pastBanner=document.querySelector('#past-day-banner'); if(pastBanner){ pastBanner.hidden=isToday; if(!isToday){ pastBanner.querySelector('.past-day-icon').innerHTML=icon('calendarDays'); document.querySelector('#past-day-title').textContent=`Открыт ${new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long'}).format(activeDate)}`; } }
      const readingIcons={quran:'softQuran',jauchan:'softJauchan',kitap:'softKitap'};
      const list=document.querySelector('#task-list'); list.innerHTML='';
      TASKS.forEach(task=>{ 
        const readingIds=['quran','jauchan','kitap'];
        if(task.id==='quran'){
          const group=document.createElement('section'); group.className='reading-group';
          group.innerHTML=`<div class="group-head"><h3>Чтение</h3><span>страниц за сегодня</span></div><div class="reading-list">${TASKS.filter(item=>readingIds.includes(item.id)).map(item=>`<div class="reading-row ${Number(state.tasks[item.id])>0?'filled':''}"><span class="task-icon icon-${item.id}">${icon(readingIcons[item.id])}</span><div class="task-copy"><div class="task-title">${item.title}</div><div class="task-sub">${Number(state.tasks[item.id])>0?'Отмечено':'Ещё не начато'}</div></div><div class="stepper"><button type="button" class="step-btn" data-step="-1" data-step-for="${item.id}" aria-label="Убавить: ${item.title}">−</button><input aria-label="${item.title}" class="field" type="number" min="0" inputmode="numeric" enterkeyhint="next" data-id="${item.id}" value="${state.tasks[item.id]||''}" placeholder="0"><button type="button" class="step-btn step-plus" data-step="1" data-step-for="${item.id}" aria-label="Прибавить: ${item.title}">+</button></div></div>`).join('')}</div>`;
          list.append(group); return;
        }
        if(readingIds.includes(task.id)) return;
        if(task.type==='nafil'){
          const values=nafilValues(), count=nafilCompleted(), tahajjud=TASKS.find(item=>item.id==='tahajjud');
          const group=document.createElement('section'); group.className='prayer-group';
          group.innerHTML=`<div class="group-head prayer-heading"><h3>Намаз</h3><span class="nafil-count">${appLocale()==='ky-KG'?`напил ${count}/${NAFIL.length}`:`нафиль ${count} из ${NAFIL.length}`}</span></div><div class="prayer-body"><div class="prayer-tahajjud"><span class="task-icon icon-moon">${icon('softTahajjud')}</span><div class="task-copy"><div class="task-title">${tahajjud.title}</div><div class="task-sub">${tahajjud.sub}</div></div><div class="pill"><button data-choice="yes" data-id="tahajjud" class="${state.tasks.tahajjud==='yes'?'active':''}">Да</button><button data-choice="no" data-id="tahajjud" class="${state.tasks.tahajjud==='no'?'active':''}">Нет</button></div></div><div class="prayer-list">${NAFIL.map(item=>`<button class="prayer-option ${values[item.id]?'done':''}" data-nafil="${item.id}" aria-pressed="${!!values[item.id]}"><span class="mini-check ${values[item.id]?'done':''}"></span><span class="nafil-copy"><b>${item.label}</b><small>нафиль</small></span><span class="nafil-mark">${icon('softNafil')}</span></button>`).join('')}</div><section class="prayer-qaza"><div class="qaza-head"><span class="task-icon">${icon('softQaza')}</span><div class="task-copy"><h4>Пропущенные намазы</h4><div class="task-sub">Нажмите, чтобы прибавить</div></div><button type="button" class="qaza-reset" data-qaza-reset>Сбросить</button></div><div class="qaza-grid" id="qaza-grid"></div><p class="qaza-hint">Нажмите — добавить, удерживайте — убрать</p></section></div>`;
          list.append(group); return;
        }
        if(task.id==='tahajjud') return;
        if(task.id==='fast'){ list.append(fastGroup()); return; }
        const dhikrIds=['salawat','tasbihat'];
        if(task.id==='salawat'){
          const group=document.createElement('section'); group.className='dhikr-group';
          group.innerHTML=`<div class="group-head"><h3>Зикр и салават</h3><button type="button" class="group-link" data-view="zikr-view">Счётчик</button></div><div class="dhikr-list">${TASKS.filter(item=>dhikrIds.includes(item.id)).map(item=>`<div class="dhikr-row ${Number(state.tasks[item.id])>0?'filled':''}"><div class="task-copy"><div class="task-title">${item.title}</div><span class="task-icon">${icon(item.id==='salawat'?'softSalawat':'softZikr')}</span></div><input aria-label="${item.title}" class="field" type="number" min="0" inputmode="numeric" enterkeyhint="next" data-id="${item.id}" value="${state.tasks[item.id]||''}" placeholder="0"><span class="dhikr-unit">раз сегодня</span><div class="dhikr-quick"><button type="button" data-step="33" data-step-for="${item.id}">+33</button><button type="button" data-step="100" data-step-for="${item.id}">+100</button></div></div>`).join('')}</div>`;
          list.append(group); return;
        }
        if(dhikrIds.includes(task.id)) return;
        const done=isDone(task); const row=document.createElement('div'); row.className=`task ${done?'done':''}`;
        let control='';
        if(task.type==='number') { control=`<input aria-label="${task.title}" class="field" type="number" min="0" inputmode="numeric" enterkeyhint="next" data-id="${task.id}" value="${state.tasks[task.id]||''}" placeholder="0"><span class="date">${task.suffix}</span>`; row.innerHTML=`<div class="task-copy"><div class="task-title">${task.title}</div><div class="task-sub">${task.sub}</div></div>${control}`; }
        else if(task.type==='yesno') { control=`<div class="pill"><button data-choice="yes" data-id="${task.id}" class="${state.tasks[task.id]==='yes'?'active':''}">Да</button><button data-choice="no" data-id="${task.id}" class="${state.tasks[task.id]==='no'?'active':''}">Нет</button></div>`; row.innerHTML=`<div class="task-copy"><div class="task-title">${task.title}</div><div class="task-sub">${task.sub}</div></div>${control}`; }
        else if(task.type==='nafil') { const values=nafilValues(), count=nafilCompleted(); row.classList.add('nafil-task'); row.innerHTML=`<div class="nafil-top"><div class="task-copy"><div class="task-title">${task.title}</div><div class="task-sub">${task.sub}</div></div><span class="nafil-count">${count} из ${NAFIL.length}</span></div><div class="nafil-grid">${NAFIL.map(item=>`<button class="nafil-option ${values[item.id]?'done':''}" data-nafil="${item.id}" aria-pressed="${!!values[item.id]}"><span class="mini-check"></span>${item.label}</button>`).join('')}</div>`; }
        else { control=`<button aria-label="Отметить ${task.title}" class="check ${done?'done':''}" data-check="${task.id}"></button>`; row.innerHTML=`<div class="task-copy"><div class="task-title">${task.title}</div><div class="task-sub">${task.sub}</div></div>${control}`; }
        list.append(row);
      });
      const grid=document.querySelector('#qaza-grid'); grid.innerHTML=QAZA.map(n=>{ const value=Number(state.qaza[n])||0; return `<button type="button" class="qaza-tap ${value>0?'filled':''}" data-qaza-inc="${n}" aria-label="Пропущенный ${QAZA_LABEL[n]}: ${value}. Нажмите, чтобы добавить, удерживайте, чтобы убрать"><b>${value}</b><span>${QAZA_LABEL[n]}</span></button>`; }).join('');
      renderCalendar();
      renderHadith();
      renderBoard();
      renderStats();
      renderZikr();
      window.cheteleTranslateUi?.();
      slideControls();
    }
    /* A new hadith every day from assets/hadiths.js; the list repeats only after it ends. */
    function hadithOfTheDay(){ return window.ChetHadiths.hadithForDate(new Date()); }
    function renderHadith(){
      const hadith=hadithOfTheDay();
      document.querySelector('#hadith-text').textContent=hadith.text;
      document.querySelector('#hadith-source').textContent=hadith.source;
    }
    function amount(value){ const number=Number(value); return Number.isFinite(number)&&number>0?Math.floor(number):0; }
    function emptyTotals(){ return {quran:0,jauchan:0,kitap:0,nafil:0,nafilBy:Object.fromEntries(NAFIL.map(item=>[item.id,0])),tahajjud:0,fast:0,salawat:0,tasbihat:0,qaza:Object.fromEntries(QAZA.map(name=>[name,0])),zikrBy:{},days:0}; }
    function addToTotals(totals, entry){
      const tasks=entry?.tasks&&typeof entry.tasks==='object'?entry.tasks:{}, qaza=entry?.qaza&&typeof entry.qaza==='object'?entry.qaza:{};
      totals.quran+=amount(tasks.quran); totals.jauchan+=amount(tasks.jauchan); totals.kitap+=amount(tasks.kitap); totals.salawat+=amount(tasks.salawat); totals.tasbihat+=amount(tasks.tasbihat);
      totals.tahajjud+=tasks.tahajjud==='yes'?1:0;
      totals.fast+=tasks.fast==='yes'?1:0;
      const nafil=tasks.nafil&&typeof tasks.nafil==='object'?tasks.nafil:(tasks.nafil?Object.fromEntries(NAFIL.map(item=>[item.id,true])):{});
      NAFIL.forEach(item=>{ if(nafil[item.id]){ totals.nafil++; totals.nafilBy[item.id]++; } });
      QAZA.forEach(name=>{ totals.qaza[name]+=amount(qaza[name]); });
      Object.entries(tasks.zikr&&typeof tasks.zikr==='object'?tasks.zikr:{}).forEach(([id,value])=>{ totals.zikrBy[id]=(totals.zikrBy[id]||0)+amount(value); });
      if(Object.values(tasks).some(value=>typeof value==='object'?Object.values(value).some(Boolean):amount(value)>0||value==='yes')||Object.values(qaza).some(value=>amount(value)>0)) totals.days++;
    }
    let statsPeriod='week';
    const statsAnimations=new Map();
    function animateStatNumber(element, target, formatter){
      const previous=statsAnimations.get(element); if(previous) cancelAnimationFrame(previous);
      if(window.matchMedia('(prefers-reduced-motion: reduce)').matches){ element.textContent=formatter.format(target); return; }
      const duration=720, started=performance.now();
      function frame(now){
        const progress=Math.min((now-started)/duration,1), eased=1-Math.pow(1-progress,3);
        element.textContent=formatter.format(Math.round(target*eased));
        if(progress<1) statsAnimations.set(element,requestAnimationFrame(frame)); else statsAnimations.delete(element);
      }
      statsAnimations.set(element,requestAnimationFrame(frame));
    }
    function personalEntries(){
      const entries=Array.isArray(state.remoteEntries)?state.remoteEntries.map(entry=>({date:entry.date,tasks:entry.tasks,qaza:entry.qaza})):Object.entries(state.history||{}).map(([date,entry])=>({date,tasks:entry.tasks,qaza:entry.qaza}));
      let start='';
      if(statsPeriod==='week'){ const date=new Date(`${todayKey}T12:00:00`); date.setDate(date.getDate()-6); start=`${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`; }
      if(statsPeriod==='month') start=`${todayKey.slice(0,8)}01`;
      return [...entries.filter(entry=>entry.date!==todayKey&&(!start||entry.date>=start)),{date:todayKey,tasks:state.tasks,qaza:state.qaza}];
    }
    /* Zikr section. Counting writes into the open day: salawat adds to tasks.salawat,
       every other zikr to tasks.tasbihat, and tasks.zikr keeps the per-zikr breakdown,
       so the counter feeds deeds, points, rating, events and stats. */
    const ZIKR = [
      {id:'subhanallah', title:'Субханаллах', arabic:'سُبْحَانَ ٱللَّٰهِ', translit:'Субханаллах.', meaning:'Пречист Аллах.', goal:33},
      {id:'alhamdulillah', title:'Альхамдулиллях', arabic:'ٱلْحَمْدُ لِلَّٰهِ', translit:'Аль-хамду лилляхи.', meaning:'Вся хвала принадлежит Аллаху.', goal:33},
      {id:'allahuakbar', title:'Аллаху акбар', arabic:'ٱللَّٰهُ أَكْبَرُ', translit:'Аллаху акбар.', meaning:'Аллах велик.', goal:33},
      {id:'tahlil', title:'Ля иляха илляллах', arabic:'لَا إِلَٰهَ إِلَّا ٱللَّٰهُ', translit:'Ля иляха илляллах.', meaning:'Нет божества, кроме Аллаха.', goal:100},
      {id:'istighfar', title:'Астагфируллах', arabic:'أَسْتَغْفِرُ ٱللَّٰهَ', translit:'Астагфируллах.', meaning:'Прошу прощения у Аллаха.', goal:100},
      {id:'salawat', title:'Салават', arabic:'ٱللَّٰهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ وَعَلَىٰ آلِ مُحَمَّدٍ', translit:'Аллахумма салли ‘аля Мухаммадин ва ‘аля али Мухаммад.', meaning:'О Аллах, благослови Мухаммада и семью Мухаммада.', goal:100},
      {id:'subhanallahi_wa_bihamdihi', title:'Субханаллахи ва бихамдихи', arabic:'سُبْحَانَ ٱللَّٰهِ وَبِحَمْدِهِ', translit:'Субханаллахи ва бихамдихи.', meaning:'Пречист Аллах и хвала Ему.', goal:100},
      {id:'subhanallahil_azim', title:'Субханаллахиль-‘азым', arabic:'سُبْحَانَ ٱللَّٰهِ ٱلْعَظِيمِ', translit:'Субханаллахиль-‘азым.', meaning:'Пречист Аллах Великий.', goal:33},
      {id:'hawqala', title:'Ля хауля ва ля куввата илля биллях', arabic:'لَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِٱللَّٰهِ', translit:'Ля хауля ва ля куввата илля биллях.', meaning:'Нет мощи и силы ни у кого, кроме Аллаха.', goal:33},
      {id:'hasbunallah', title:'Хасбуналлаху ва ни‘маль-вакиль', arabic:'حَسْبُنَا ٱللَّٰهُ وَنِعْمَ ٱلْوَكِيلُ', translit:'Хасбуналлаху ва ни‘маль-вакиль.', meaning:'Достаточно нам Аллаха, и как прекрасен Он как Покровитель.', goal:33}
    ];
    /* Morning and evening adhkar. Each one counts like any zikr (into tasks.tasbihat), under
       its own id per set, so the morning and the evening progress stay apart. */
    const ADHKAR_SETS = {
      morning:{title:'Утренние азкары', short:'Утро', when:'после фаджра до восхода', icon:'sun'},
      evening:{title:'Вечерние азкары', short:'Вечер', when:'после асра до магриба', icon:'moon'}
    };
    const ADHKAR_COMMON = {
      kursi:{title:'Аят аль-Курси', long:true, goal:1, arabic:'ٱللَّهُ لَا إِلَٰهَ إِلَّا هُوَ ٱلْحَيُّ ٱلْقَيُّومُ ۚ لَا تَأْخُذُهُۥ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُۥ مَا فِي ٱلسَّمَٰوَٰتِ وَمَا فِي ٱلْأَرْضِ ۗ مَن ذَا ٱلَّذِي يَشْفَعُ عِندَهُۥٓ إِلَّا بِإِذْنِهِۦ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِۦٓ إِلَّا بِمَا شَآءَ ۚ وَسِعَ كُرْسِيُّهُ ٱلسَّمَٰوَٰتِ وَٱلْأَرْضَ ۖ وَلَا يَـُٔودُهُۥ حِفْظُهُمَا ۚ وَهُوَ ٱلْعَلِيُّ ٱلْعَظِيمُ', translit:'Аллаху ля иляха илля хуваль-хайюль-каййум…', meaning:'Аллах — нет божества, кроме Него, Живого, Поддерживающего жизнь. Сура аль-Бакара, 255.'},
      qul:{title:'Три последние суры', long:true, goal:3, arabic:'قُلْ هُوَ ٱللَّهُ أَحَدٌ ۝ ٱللَّهُ ٱلصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُن لَّهُۥ كُفُوًا أَحَدٌ', translit:'Куль хуваллаху ахад…', meaning:'Прочитайте суры аль-Ихлас, аль-Фалак и ан-Нас — три раза.'},
      sayyid:{title:'Сайид аль-истигфар', long:true, goal:1, arabic:'ٱللَّهُمَّ أَنْتَ رَبِّي لَا إِلَٰهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَىٰ عَهْدِكَ وَوَعْدِكَ مَا ٱسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي فَٱغْفِرْ لِي، فَإِنَّهُ لَا يَغْفِرُ ٱلذُّنُوبَ إِلَّا أَنْتَ', translit:'Аллахумма анта рабби, ля иляха илля анта…', meaning:'О Аллах, Ты мой Господь, нет божества, кроме Тебя. Прости меня, ведь никто не прощает грехи, кроме Тебя.'},
      bismillah:{title:'Бисмилляхи-ллязи ля ядурру', goal:3, arabic:'بِسْمِ ٱللَّهِ ٱلَّذِي لَا يَضُرُّ مَعَ ٱسْمِهِ شَيْءٌ فِي ٱلْأَرْضِ وَلَا فِي ٱلسَّمَاءِ وَهُوَ ٱلسَّمِيعُ ٱلْعَلِيمُ', translit:'Бисмилляхи-ллязи ля ядурру ма‘а-смихи шай’ун фи-ль-арды ва ля фи-с-сама’, ва хува-с-сами‘у-ль-‘алим.', meaning:'С именем Аллаха, с именем Которого ничто не причинит вреда ни на земле, ни на небе.'},
      raditu:{title:'Радыту билляхи раббан', goal:3, arabic:'رَضِيتُ بِٱللَّهِ رَبًّا، وَبِٱلْإِسْلَامِ دِينًا، وَبِمُحَمَّدٍ ﷺ نَبِيًّا', translit:'Радыту билляхи раббан, ва биль-ислами динан, ва би-Мухаммадин ﷺ набийя.', meaning:'Я доволен Аллахом как Господом, исламом как религией и Мухаммадом ﷺ как пророком.'},
      hasbi:{title:'Хасбияллаху ля иляха илля хува', goal:7, arabic:'حَسْبِيَ ٱللَّهُ لَا إِلَٰهَ إِلَّا هُوَ عَلَيْهِ تَوَكَّلْتُ وَهُوَ رَبُّ ٱلْعَرْشِ ٱلْعَظِيمِ', translit:'Хасбияллаху ля иляха илля хува, ‘аляйхи таваккальту ва хува раббу-ль-‘арши-ль-‘азым.', meaning:'Достаточно мне Аллаха, нет божества, кроме Него. На Него я уповаю, и Он — Господь великого Трона.'},
      tasbih:{title:'Субханаллахи ва бихамдихи', goal:100, arabic:'سُبْحَانَ ٱللَّهِ وَبِحَمْدِهِ', translit:'Субханаллахи ва бихамдихи.', meaning:'Пречист Аллах и хвала Ему.'}
    };
    const ADHKAR = {
      morning:[
        ['kursi'],['qul'],
        ['asbahna',{title:'Асбахна ва асбаха-ль-мульку лиллях', long:true, goal:1, arabic:'أَصْبَحْنَا وَأَصْبَحَ ٱلْمُلْكُ لِلَّهِ، وَٱلْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا ٱللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ ٱلْمُلْكُ وَلَهُ ٱلْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ', translit:'Асбахна ва асбаха-ль-мульку лиллях, валь-хамду лиллях…', meaning:'Мы встретили утро, и встретило утро царство Аллаха. Хвала Аллаху, нет божества, кроме Него.'}],
        ['bika',{title:'Аллахумма бика асбахна', goal:1, arabic:'ٱللَّهُمَّ بِكَ أَصْبَحْنَا، وَبِكَ أَمْسَيْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ ٱلنُّشُورُ', translit:'Аллахумма бика асбахна, ва бика амсайна, ва бика нахйа, ва бика намуту, ва иляйка-н-нушур.', meaning:'О Аллах, благодаря Тебе мы дожили до утра и до вечера, живём и умираем, и к Тебе — воскрешение.'}],
        ['sayyid'],['bismillah'],['raditu'],['hasbi'],['tasbih']
      ],
      evening:[
        ['kursi'],['qul'],
        ['amsayna',{title:'Амсайна ва амса-ль-мульку лиллях', long:true, goal:1, arabic:'أَمْسَيْنَا وَأَمْسَى ٱلْمُلْكُ لِلَّهِ، وَٱلْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا ٱللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ ٱلْمُلْكُ وَلَهُ ٱلْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ', translit:'Амсайна ва амса-ль-мульку лиллях, валь-хамду лиллях…', meaning:'Мы встретили вечер, и встретило вечер царство Аллаха. Хвала Аллаху, нет божества, кроме Него.'}],
        ['bika',{title:'Аллахумма бика амсайна', goal:1, arabic:'ٱللَّهُمَّ بِكَ أَمْسَيْنَا، وَبِكَ أَصْبَحْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ ٱلْمَصِيرُ', translit:'Аллахумма бика амсайна, ва бика асбахна, ва бика нахйа, ва бика намуту, ва иляйка-ль-масыр.', meaning:'О Аллах, благодаря Тебе мы дожили до вечера и до утра, живём и умираем, и к Тебе — возвращение.'}],
        ['sayyid'],['bismillah'],
        ['audhu',{title:'А‘узу би-калимати-ллях', goal:3, arabic:'أَعُوذُ بِكَلِمَاتِ ٱللَّهِ ٱلتَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ', translit:'А‘узу би-калимати-лляхи-т-таммати мин шарри ма халак.', meaning:'Прибегаю к совершенным словам Аллаха от зла того, что Он сотворил.'}],
        ['raditu'],['hasbi'],['tasbih']
      ]
    };
    Object.entries(ADHKAR).forEach(([set,list])=>list.forEach(([key,own])=>ZIKR.push({...(own||ADHKAR_COMMON[key]),id:`${set==='morning'?'am':'pm'}_${key}`,set})));
    const ZIKR_GENERAL=ZIKR.filter(item=>!item.set);
    const zikrSet=set=>ZIKR.filter(item=>item.set===set);
    const ZIKR_DEFAULT_FAVORITES=['subhanallah','alhamdulillah','allahuakbar','salawat'];
    let zikrScreen='hub', zikrCurrent=ZIKR[0].id, zikrFilter='all', zikrCelebrate=false;
    /* Salawat typed or added on the main screen is the salawat counter itself: both
       always show one number, and the difference goes to the server like taps do. */
    function matchSalawatCounter(){
      if(!ChetZikrSync.matchSalawat) return;
      const matched=ChetZikrSync.matchSalawat(state.tasks.zikr,state.tasks.salawat); if(!matched.delta) return;
      state.tasks={...state.tasks,zikr:matched.zikr};
      state.zikrPending=ChetZikrSync.addPending(state.zikrPending,entryDateKey,'salawat',matched.delta);
      save(); scheduleZikrFlush();
    }
    /* The counter's arrows walk through one set: the adhkar of that time, or the general list. */
    function zikrSequence(item){ return item?.set?zikrSet(item.set):ZIKR_GENERAL; }
    function zikrToday(id){ return Number(state.tasks?.zikr?.[id])||0; }
    function zikrAllTime(id){
      const seen=new Set([entryDateKey]); let sum=zikrToday(id);
      (state.remoteEntries||[]).forEach(entry=>{ if(!entry?.date||seen.has(entry.date)) return; seen.add(entry.date); sum+=Number(entry.tasks?.zikr?.[id])||0; });
      Object.entries(state.history||{}).forEach(([date,entry])=>{ if(seen.has(date)) return; seen.add(date); sum+=Number(entry?.tasks?.zikr?.[id])||0; });
      return sum;
    }
    function zikrAdd(id,delta){
      const item=ZIKR.find(zikr=>zikr.id===id); if(!item) return;
      if(id==='salawat') matchSalawatCounter();
      const before=completed(), current=zikrToday(id), next=Math.min(99999,Math.max(0,current+delta)), change=next-current; if(!change) return;
      const field=id==='salawat'?'salawat':'tasbihat', total=Math.max(0,(Number(state.tasks[field])||0)+change);
      const breakdown={...(state.tasks.zikr&&typeof state.tasks.zikr==='object'?state.tasks.zikr:{}),[id]:next}; if(!next) delete breakdown[id];
      state.tasks={...state.tasks,zikr:breakdown,[field]:total?String(total):''};
      state.zikrPending=ChetZikrSync.addPending(state.zikrPending,entryDateKey,id,change); scheduleZikrFlush();
      if(change>0) state.zikrLast={id,at:Date.now()};
      if(change>0&&next%item.goal===0){ zikrCelebrate=true; navigator.vibrate?.([20,40,20]); } else navigator.vibrate?.(8);
      zikrRoll=change>0?'up':'down';
      scoreUpdate(before);
      zikrRoll='';
    }
    /* Waiting taps go to the server, the only writer of per-zikr counts, so two
       phones counting on the same day add up instead of overwriting each other. */
    let zikrRoll='';
    let zikrFlushTimer=null, zikrFlushing=false, zikrFlushRun=null;
    function scheduleZikrFlush(delay=500){ clearTimeout(zikrFlushTimer); zikrFlushTimer=setTimeout(flushZikr,delay); }
    /* Sends every waiting tap for the day before anything else about that day, and
       waits for a send already on its way. */
    async function settleZikr(date){
      clearTimeout(zikrFlushTimer);
      for(let round=0; round<4 && (zikrFlushing||state.zikrPending?.[date]); round++){
        if(zikrFlushing) await zikrFlushRun; else await flushZikr(date);
        if(state.zikrPending?.[date]&&!navigator.onLine) break;
      }
    }
    function flushZikr(only){ if(zikrFlushing) return zikrFlushRun; zikrFlushRun=flushZikrOnce(only); return zikrFlushRun; }
    async function flushZikrOnce(only){
      if(zikrFlushing||!state.userId||!state.token) return;
      const date=typeof only==='string'&&state.zikrPending?.[only]?only:Object.keys(state.zikrPending||{})[0]; if(!date) return;
      const deltas=state.zikrPending[date];
      state.zikrPending=Object.entries(deltas).reduce((pending,[id,delta])=>ChetZikrSync.addPending(pending,date,id,-delta),state.zikrPending);
      zikrFlushing=true;
      let sent=false;
      try {
        const response=await apiFetch(apiUrl(`users/${state.userId}/entries/${date}/zikr`),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({deltas})});
        if(!response.ok) throw new Error('zikr_sync_failed');
        const entry=await response.json(); sent=true;
        if(date===entryDateKey){
          const merged=ChetZikrSync.adoptServer(state.tasks,entry?.tasks?.zikr,(state.zikrPending||{})[date]);
          state.tasks={...state.tasks,zikr:merged.zikr,salawat:merged.salawat?String(merged.salawat):'',tasbihat:merged.tasbihat?String(merged.tasbihat):''};
          render();
        }
      } catch {
        state.zikrPending=Object.entries(deltas).reduce((pending,[id,delta])=>ChetZikrSync.addPending(pending,date,id,delta),state.zikrPending);
      } finally {
        zikrFlushing=false; save();
        if(sent&&Object.keys(state.zikrPending||{}).length) scheduleZikrFlush(0);
      }
    }
    window.addEventListener('online',()=>scheduleZikrFlush(0));
    function renderZikr(){
      const root=document.querySelector('#zikr-view'); if(!root) return;
      root.classList.toggle('is-counter',zikrScreen==='counter');
      const currentItem=ZIKR.find(zikr=>zikr.id===zikrCurrent);
      const themeSet=zikrScreen==='list'&&ADHKAR_SETS[zikrFilter]?zikrFilter:zikrScreen==='counter'&&currentItem?.set||'';
      if(themeSet) root.dataset.set=themeSet; else delete root.dataset.set;
      matchSalawatCounter();
      const number=new Intl.NumberFormat(appLocale());
      const favorites=new Set(state.zikrFavorites||ZIKR_DEFAULT_FAVORITES);
      const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
      const zikrs=n=>`${number.format(n)} ${plural(n,'зикр','зикра','зикров')}`;
      const percent=(value,goal)=>Math.min(100,Math.round(value/Math.max(1,goal)*100));
      const round=(attrs,iconName,label)=>`<button type="button" class="zg-round" ${attrs} aria-label="${label}">${icon(iconName)}</button>`;
      const head=(eyebrow,title,sub,side)=>`<header class="stats-top zg-top">${side.startsWith('<button')?side:''}<div><span class="stats-eyebrow">${eyebrow}</span><h2>${title}</h2><span class="stats-period-label">${sub}</span></div>${side.startsWith('<button')?'':side}</header>`;
      if(zikrScreen!=='counter') root.dataset.key='';
      if(zikrScreen==='list'){
        const titles={all:'Все зикры',starred:'Избранное',open:'Осталось'}, setInfo=ADHKAR_SETS[zikrFilter];
        const items=setInfo?zikrSet(zikrFilter):zikrFilter==='starred'?ZIKR.filter(item=>favorites.has(item.id)):ZIKR_GENERAL.filter(item=>zikrFilter==='all'||zikrToday(item.id)<item.goal);
        const rows=items.map(item=>{ const today=zikrToday(item.id), done=today>=item.goal, starred=favorites.has(item.id);
          return `<li class="zg-row"><button type="button" class="zg-row-main" data-zikr-open="${item.id}"><span class="zg-row-copy"><b>${item.title}</b><small>${done?'цель на сегодня выполнена':item.goal<33?`${item.goal} ${plural(item.goal,'раз','раза','раз')} · ${item.meaning}`:item.meaning}</small></span><span class="zg-row-progress"><span class="zg-track" aria-hidden="true"><i style="width:${percent(today,item.goal)}%"></i></span><em class="${done?'done':''}">${number.format(today)} / ${item.goal}</em></span></button><button type="button" class="zg-star ${starred?'on':''}" data-zikr-star="${item.id}" aria-pressed="${starred}" aria-label="Избранное: ${item.title}">${icon('softStar')}</button></li>`; }).join('');
        if(setInfo){
          const done=items.filter(item=>zikrToday(item.id)>=item.goal).length;
          root.innerHTML=`${head('Азкары',setInfo.title,`${done} из ${items.length} прочитано`,round('data-zikr-go="hub"','softBack','Назад'))}<section class="zg-set-banner"><i class="zg-set-icon">${icon(setInfo.icon)}</i><span><b>${done===items.length?'Все азкары прочитаны':`Осталось ${items.length-done}`}</b><small>Время: ${setInfo.when}</small></span><span class="zg-track" aria-hidden="true"><i style="width:${percent(done,items.length)}%"></i></span></section><ol class="zg-list">${rows}</ol>`;
          return;
        }
        root.innerHTML=`${head('Зикр',titles[zikrFilter],zikrs(items.length),round('data-zikr-go="hub"','softBack','Назад'))}<div class="stats-seg glass-seg" role="radiogroup" aria-label="Фильтр зикров">${Object.entries({all:'Все',starred:'Избранные',open:'Осталось'}).map(([id,label])=>`<button type="button" role="radio" aria-checked="${zikrFilter===id}" class="${zikrFilter===id?'active':''}" data-zikr-filter="${id}">${label}</button>`).join('')}</div>${rows?`<ol class="zg-list">${rows}</ol>`:'<p class="zg-empty">Здесь пока пусто.</p>'}`; slideControls();
        return;
      }
      if(zikrScreen==='counter'){
        const item=currentItem||ZIKR[0], sequence=zikrSequence(item), index=sequence.indexOf(item), today=zikrToday(item.id);
        /* The bar counts rounds of the goal (33): 33 fills it, 34 starts round two at 1 / 33. */
        const inRound=today?((today-1)%item.goal)+1:0, lap=today?Math.floor((today-1)/item.goal)+1:1;
        const hint=today<item.goal?`ещё ${item.goal-today} до цели`:inRound===item.goal?(lap===1?'цель выполнена':`${lap}-й круг пройден`):`${lap}-й круг · ещё ${item.goal-inRound}`;
        const roundPercent=Math.round(inRound/item.goal*100), roundLabel=`${number.format(inRound)} / ${item.goal}`;
        /* A tap only changes numbers: patch them in place so the screen does not rebuild and flash. */
        const key=`counter|${item.id}|${zikrCelebrate}|${entryDateKey}`;
        if(root.dataset.key===key&&root.querySelector('.zg-count')){
          rollDigits(root.querySelector('.zg-count'),number.format(today),zikrRoll);
          root.querySelector('.zg-hint').textContent=hint;
          const bar=root.querySelector('.zg-goal i'), was=parseFloat(bar.style.width)||0;
          if(roundPercent<was&&lap>1&&inRound===1){ bar.style.transition='none'; bar.style.width=`${roundPercent}%`; void bar.offsetWidth; bar.style.transition=''; }
          else bar.style.width=`${roundPercent}%`;
          root.querySelector('.zg-goal b').textContent=roundLabel;
          root.querySelector('.zg-total b').textContent=number.format(zikrAllTime(item.id));
          root.querySelector('.zg-tap').setAttribute('aria-label',`Отметить: ${today+1}`);
          return;
        }
        root.dataset.key=key;
        const dayNote=entryDateKey===todayKey?'':`<p class="zg-day-note">Счёт идёт в открытый день — ${formatDayLabel(entryDateKey)}</p>`;
        const goal=`<div class="zg-goal"><span class="zg-track" aria-hidden="true"><i style="width:${zikrCelebrate?100:roundPercent}%"></i></span><b>${roundLabel}</b></div>`;
        const tap=zikrCelebrate
          ?`<button type="button" class="zg-tap celebrate" data-zikr-tap aria-label="Цель выполнена, продолжить счёт"><span class="zg-done-icon">${icon('check')}</span><b class="zg-done-title">Цель выполнена</b><span class="zg-hint">Нажмите, чтобы продолжить счёт</span></button>`
          :`<button type="button" class="zg-tap" data-zikr-tap aria-label="Отметить: ${today+1}"><span class="zg-count">${number.format(today)}</span><span class="zg-hint">${hint}</span><small>Нажмите в любом месте карточки</small></button>`;
        root.innerHTML=`<header class="zg-bar">${round('data-zikr-go="list"','softBack','К списку')}<span class="zg-pos">${item.set?`${ADHKAR_SETS[item.set].short} · `:''}${index+1} из ${sequence.length}</span><span class="zg-arrows">${round('data-zikr-step="-1"','softBack','Предыдущий зикр')}${round('data-zikr-step="1"','softChevron','Следующий зикр')}</span></header>${dayNote}<section class="zg-text${item.long?' zg-long':''}"><span class="zg-label">${item.title}${item.goal<33?` · ${item.goal} ${plural(item.goal,'раз','раза','раз')}`:''}</span><p class="zg-arabic" lang="ar" dir="rtl">${item.arabic}</p><b>${item.translit}</b><p>${item.meaning}</p></section><section class="zg-counter">${goal}${tap}</section><div class="zg-foot"><button type="button" class="zg-circle" data-zikr-minus aria-label="Убрать одно">${icon('softMinus')}</button><span class="zg-total"><small>всего</small><b>${number.format(zikrAllTime(item.id))}</b></span><button type="button" class="zg-circle" data-zikr-reset aria-label="Сбросить счёт за день">${icon('softReset')}</button></div>`;
        return;
      }
      /* Today's total is the same salawat + zikr as in the deeds of the day, typed numbers included. */
      const daySalawat=Number(state.tasks.salawat)||0, dayZikr=Number(state.tasks.tasbihat)||0, todayTotal=daySalawat+dayZikr;
      const typed=Math.max(0,todayTotal-ZIKR.reduce((sum,item)=>sum+zikrToday(item.id),0)), last=state.zikrLast&&ZIKR.find(item=>item.id===state.zikrLast.id);
      const event=(state.events||[]).find(item=>item.mode!=='personal_path'&&['salawat','dhikr'].includes(item.metric));
      const eventPercent=event?percent(Number(event.totalProgress||0),Number(event.goal||0)):0;
      const open=ZIKR_GENERAL.filter(item=>zikrToday(item.id)<item.goal).length;
      const hour=new Date().getHours(), nowSet=entryDateKey!==todayKey?'':hour>=3&&hour<12?'morning':hour>=15&&hour<23?'evening':'';
      const adhkar=Object.entries(ADHKAR_SETS).map(([id,info])=>{ const list=zikrSet(id), done=list.filter(item=>zikrToday(item.id)>=item.goal).length;
        return `<button type="button" class="zg-adhkar zg-adhkar-${id}" data-zikr-list="${id}"><span class="zg-adhkar-top"><i class="zg-set-icon">${icon(info.icon)}</i>${done===list.length?`<em>${icon('check')}готово</em>`:nowSet===id?'<em>сейчас</em>':''}</span><span class="zg-adhkar-copy"><b>${info.title}</b><small>${info.when}</small></span><span class="zg-adhkar-progress"><span class="zg-track" aria-hidden="true"><i style="width:${percent(done,list.length)}%"></i></span><strong>${done}/${list.length}</strong></span></button>`; }).join('');
      const dayLabel=entryDateKey===todayKey?'Сегодня':formatDayLabel(entryDateKey);
      const times=`${number.format(todayTotal)} ${plural(todayTotal,'раз','раза','раз')}`;
      root.innerHTML=`${head('Поминание Аллаха','Зикр',`${times} ${entryDateKey===todayKey?'сегодня':'за день'}`,`<i class="stats-badge" aria-hidden="true">${icon('softSalawat')}</i>`)}<section class="zg-hero" aria-label="${dayLabel}"><div class="zg-hero-bar"><span>${dayLabel}</span><em>как в делах дня</em></div><article class="zg-hero-card"><div class="zg-sum"><b>${number.format(todayTotal)}</b><span>${plural(todayTotal,'раз','раза','раз')}</span></div><div class="zg-split"><span><small>Салават</small><b>${number.format(daySalawat)}</b></span><span><small>Зикр</small><b>${number.format(dayZikr)}</b></span></div>${typed?`<p class="zg-typed">из них ${number.format(typed)} введено на главном экране</p>`:''}${last?`<button type="button" class="zg-continue" data-zikr-open="${last.id}"><i>${icon('softSalawat')}</i>Продолжить: ${last.title}</button>`:''}</article></section><div class="zg-adhkar-grid" aria-label="Утренние и вечерние азкары">${adhkar}</div><div class="zg-entries"><button type="button" class="zg-entry" data-zikr-list="all"><i class="zg-circle-icon">${icon('softList')}</i><span><b>Все зикры</b><small>${zikrs(ZIKR_GENERAL.length)}</small></span></button><button type="button" class="zg-entry" data-zikr-list="starred"><i class="zg-circle-icon zg-gold">${icon('softStar')}</i><span><b>Избранное</b><small>${zikrs(favorites.size)}</small></span></button></div>${event?`<button type="button" class="zg-event" data-view="events-view"><span><small>Общий зикр</small><b>${number.format(Number(event.totalProgress||0))}</b><em>${escapeHtml(event.title)} · цель ${number.format(Number(event.goal||0))}</em></span><span class="zg-ring" aria-label="${eventPercent} процентов"><svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="27"/><circle class="zg-ring-value" cx="32" cy="32" r="27" stroke-dasharray="${Math.round(eventPercent*1.696)} 170"/></svg><b>${eventPercent}%</b></span></button>`:''}<button type="button" class="zg-entry zg-wide" data-zikr-list="open"><i class="zg-circle-icon">${icon('softClock')}</i><span><b>Осталось сегодня</b><small>${open?`${zikrs(open)} ещё не дошли до цели`:'все цели на сегодня выполнены'}</small></span></button>`;
    }
    document.addEventListener('click',event=>{
      const target=event.target;
      const go=target.closest('[data-zikr-go]'); if(go){ zikrScreen=go.dataset.zikrGo; zikrCelebrate=false; renderZikr(); window.scrollTo(0,0); riseIn(document.querySelector('#zikr-view')); return; }
      const list=target.closest('[data-zikr-list]'); if(list){ zikrFilter=list.dataset.zikrList; zikrScreen='list'; renderZikr(); window.scrollTo(0,0); riseIn(document.querySelector('#zikr-view')); return; }
      const filter=target.closest('[data-zikr-filter]'); if(filter){ zikrFilter=filter.dataset.zikrFilter; renderZikr(); return; }
      const open=target.closest('[data-zikr-open]'); if(open){ zikrCurrent=open.dataset.zikrOpen; const opened=ZIKR.find(item=>item.id===zikrCurrent); if(opened?.set) zikrFilter=opened.set; else if(ADHKAR_SETS[zikrFilter]) zikrFilter='all'; zikrScreen='counter'; zikrCelebrate=false; renderZikr(); window.scrollTo(0,0); riseIn(document.querySelector('#zikr-view')); return; }
      const star=target.closest('[data-zikr-star]'); if(star){ const set=new Set(state.zikrFavorites||ZIKR_DEFAULT_FAVORITES), id=star.dataset.zikrStar; if(set.has(id)) set.delete(id); else set.add(id); state.zikrFavorites=[...set]; save(); renderZikr(); return; }
      const step=target.closest('[data-zikr-step]'); if(step){ const sequence=zikrSequence(ZIKR.find(item=>item.id===zikrCurrent)||ZIKR[0]), index=(sequence.findIndex(item=>item.id===zikrCurrent)+Number(step.dataset.zikrStep)+sequence.length)%sequence.length; zikrCurrent=sequence[index].id; zikrCelebrate=false; renderZikr(); return; }
      if(target.closest('[data-zikr-tap]')){ if(zikrCelebrate){ zikrCelebrate=false; renderZikr(); return; } zikrAdd(zikrCurrent,1); return; }
      if(target.closest('[data-zikr-minus]')){ zikrCelebrate=false; zikrAdd(zikrCurrent,-1); return; }
      if(target.closest('[data-zikr-reset]')){ if(!zikrToday(zikrCurrent)) return; const id=zikrCurrent; askSheet({title:'Сбросить счёт?',text:`Счёт этого зикра за сегодня (${zikrToday(id)}) обнулится. Общий итог «всего» уменьшится на столько же.`,ok:'Сбросить',cancel:'Оставить'}).then(yes=>{ if(!yes||zikrCurrent!==id) return; zikrCelebrate=false; zikrAdd(id,-zikrToday(id)); }); return; }
    });
    /* Stats by day: what one deed came to on a date, the series behind a tile's detail screen
       and the sheet for one day of the calendar. The open day is read live from state. */
    const STATS_METRICS={
      quran:{task:'quran',ru:'Куран',ky:'Куран',icon:'softQuran',unit:(n,ky,pl)=>ky?'бет':pl(n,'страница','страницы','страниц'),short:ky=>ky?'бет':'стр.'},
      salawat:{task:'salawat',ru:'Салават',ky:'Салабат',icon:'softSalawat',unit:(n,ky,pl)=>ky?'жолу':pl(n,'раз','раза','раз'),short:ky=>ky?'жолу':'раз'},
      tahajjud:{task:'tahajjud',ru:'Тахаджуд',ky:'Тахажжуд',icon:'softTahajjud',binary:true,unit:(n,ky,pl)=>ky?'түн':pl(n,'ночь','ночи','ночей'),short:()=>''},
      zikr:{task:'tasbihat',ru:'Зикр',ky:'Зикир',icon:'softZikr',unit:(n,ky,pl)=>ky?'жолу':pl(n,'раз','раза','раз'),short:ky=>ky?'жолу':'раз'}
    };
    const statsPlural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
    const statsKeyOf=date=>`${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
    const statsShift=(key,offset)=>{ const date=new Date(`${key}T12:00:00`); date.setDate(date.getDate()+offset); return statsKeyOf(date); };
    // Totals read the same entries as the tiles (personalEntries): the server's, else this phone's history.
    function statsEntry(key){
      if(key===todayKey) return {tasks:state.tasks,qaza:state.qaza};
      return Array.isArray(state.remoteEntries)?state.remoteEntries.find(entry=>entry?.date===key)||null:state.history?.[key]||null;
    }
    // A single day in the sheet shows what that day holds now, the open day included.
    function statsDayEntry(key){
      if(key===entryDateKey) return {tasks:state.tasks,qaza:state.qaza};
      return (state.remoteEntries||[]).find(entry=>entry?.date===key)||state.drafts?.[key]||state.history?.[key]||null;
    }
    function statsValue(key,metric){ const tasks=statsEntry(key)?.tasks||{}, spec=STATS_METRICS[metric]; return spec.binary?(tasks.tahajjud==='yes'?1:0):amount(tasks[spec.task]); }
    /* One deed over a period: a bar per day (week, month) or per month (all time), and the
       same stretch just before it for the comparison (none for all time). */
    function statsSeries(metric,period){
      const locale=appLocale(), cap=text=>text.charAt(0).toUpperCase()+text.slice(1), fmt=(key,options)=>new Intl.DateTimeFormat(locale,options).format(new Date(`${key}T12:00:00`));
      if(period==='week'){
        const keys=Array.from({length:7},(_,i)=>statsShift(todayKey,i-6));
        return {keys,values:keys.map(key=>statsValue(key,metric)),labels:keys.map(key=>[cap(fmt(key,{weekday:'short'}).replace('.','')),cap(fmt(key,{weekday:'long',day:'numeric',month:'long'}))]),prev:keys.reduce((sum,key)=>sum+statsValue(statsShift(key,-7),metric),0)};
      }
      if(period==='month'){
        const day=Number(todayKey.slice(8)), keys=Array.from({length:day},(_,i)=>`${todayKey.slice(0,8)}${String(i+1).padStart(2,'0')}`);
        const first=new Date(`${todayKey.slice(0,8)}01T12:00:00`); first.setMonth(first.getMonth()-1);
        const prevLength=new Date(first.getFullYear(),first.getMonth()+1,0).getDate(), prevMonth=statsKeyOf(first).slice(0,8);
        const prev=Array.from({length:Math.min(day,prevLength)},(_,i)=>statsValue(`${prevMonth}${String(i+1).padStart(2,'0')}`,metric)).reduce((a,b)=>a+b,0);
        return {keys,values:keys.map(key=>statsValue(key,metric)),labels:keys.map(key=>[String(Number(key.slice(8))),cap(fmt(key,{weekday:'long',day:'numeric',month:'long'}))]),prev,prevName:fmt(statsKeyOf(first),{month:'long'})};
      }
      const dates=new Set([todayKey,...(state.remoteEntries||[]).map(entry=>entry?.date).filter(Boolean),...Object.keys(state.history||{}),...Object.keys(state.drafts||{})].filter(key=>key<=todayKey));
      const byMonth={}; dates.forEach(key=>{ byMonth[key.slice(0,7)]=(byMonth[key.slice(0,7)]||0)+statsValue(key,metric); });
      const firstKey=[...dates].sort()[0], start=new Date(`${firstKey.slice(0,8)}01T12:00:00`), now=new Date(`${todayKey}T12:00:00`);
      const months=Math.min(12,(now.getFullYear()-start.getFullYear())*12+now.getMonth()-start.getMonth()+1);
      const monthKeys=Array.from({length:months},(_,i)=>{ const date=new Date(`${todayKey.slice(0,8)}01T12:00:00`); date.setMonth(date.getMonth()-(months-1-i)); return statsKeyOf(date); });
      return {keys:monthKeys,values:monthKeys.map(key=>byMonth[key.slice(0,7)]||0),labels:monthKeys.map(key=>[cap(fmt(key,{month:'short'}).replace('.','')),fmt(key,{month:'long',year:'numeric'})]),prev:null};
    }
    /* Personal goals: pages or times a day for Quran, salawat and zikr, nights a week for
       tahajjud. Kept on this phone with the rest of the settings. */
    const STATS_GOALS={
      quran:{presets:[2,5,10,20],max:604,per:'day',ask:ky=>ky?'Күнүнө канча бет?':'Сколько страниц в день?',unit:(n,ky)=>ky?'бет':statsPlural(n,'страница','страницы','страниц')},
      salawat:{presets:[100,300,500,1000],max:100000,per:'day',ask:ky=>ky?'Күнүнө канча жолу?':'Сколько раз в день?',unit:(n,ky)=>ky?'жолу':statsPlural(n,'раз','раза','раз')},
      tahajjud:{presets:[1,3,5,7],max:7,per:'week',ask:ky=>ky?'Аптасына канча түн?':'Сколько ночей в неделю?',unit:(n,ky)=>ky?'түн':statsPlural(n,'ночь','ночи','ночей')},
      zikr:{presets:[100,300,500,1000],max:100000,per:'day',ask:ky=>ky?'Күнүнө канча жолу?':'Сколько раз в день?',unit:(n,ky)=>ky?'жолу':statsPlural(n,'раз','раза','раз')}
    };
    const statsGoal=metric=>{ const value=Number(state.statsGoals?.[metric]); return Number.isFinite(value)&&value>0?value:0; };
    // Today's share of the goal (this week's for tahajjud), from 0 to 1.
    function statsGoalShare(metric){
      const goal=statsGoal(metric); if(!goal) return 0;
      const done=STATS_GOALS[metric].per==='week'?Array.from({length:7},(_,i)=>statsValue(statsShift(todayKey,i-6),metric)).reduce((a,b)=>a+b,0):statsValue(todayKey,metric);
      return Math.min(1,done/goal);
    }
    function openGoalSheet(metric){
      const ky=appLocale()==='ky-KG', spec=STATS_GOALS[metric], current=statsGoal(metric), name=ky?STATS_METRICS[metric].ky:STATS_METRICS[metric].ru;
      document.querySelector('.app-sheet.goal-sheet')?.remove();
      const layer=document.createElement('div'); layer.className='app-sheet goal-sheet';
      const value=current||spec.presets[1];
      layer.innerHTML=`<section role="dialog" aria-modal="true" aria-labelledby="goal-sheet-title"><i aria-hidden="true"></i><h2 id="goal-sheet-title">${ky?'Максат':'Цель'}: ${name}</h2><p>${spec.ask(ky)}</p><div class="gs-presets" role="radiogroup" aria-label="${ky?'Даяр варианттар':'Готовые варианты'}">${spec.presets.map(n=>`<button type="button" role="radio" aria-checked="${n===value}" class="${n===value?'active':''}" data-goal-pick="${n}">${new Intl.NumberFormat(appLocale()).format(n)}</button>`).join('')}</div><label class="gs-field"><span>${ky?'Же өзүңүз жазыңыз':'Или своё число'}</span><input type="number" inputmode="numeric" min="1" max="${spec.max}" value="${value}" data-goal-input><small>${spec.unit(value,ky)} ${spec.per==='week'?(ky?'аптасына':'в неделю'):(ky?'күнүнө':'в день')}</small></label><button type="button" class="stay" data-goal-save>${ky?'Сактоо':'Сохранить'}</button>${current?`<button type="button" class="ds-close" data-goal-clear>${ky?'Максатты алып салуу':'Убрать цель'}</button>`:''}<button type="button" class="ds-close" data-goal-cancel>${ky?'Жокко чыгаруу':'Отмена'}</button></section>`;
      const input=layer.querySelector('[data-goal-input]'), unitLabel=layer.querySelector('.gs-field small');
      const sync=()=>{ const n=Math.max(1,Math.min(spec.max,Math.round(Number(input.value)||0))); unitLabel.textContent=`${spec.unit(n,ky)} ${spec.per==='week'?(ky?'аптасына':'в неделю'):(ky?'күнүнө':'в день')}`; layer.querySelectorAll('[data-goal-pick]').forEach(b=>{ const on=Number(b.dataset.goalPick)===n; b.classList.toggle('active',on); b.setAttribute('aria-checked',String(on)); }); };
      const close=()=>{ layer.remove(); document.removeEventListener('keydown',onKey); };
      const onKey=event=>{ if(event.key==='Escape') close(); };
      const store=n=>{ state.statsGoals={...(state.statsGoals||{}),[metric]:n}; if(!n) delete state.statsGoals[metric]; save(); close(); renderStats(); renderStatsDetail(); };
      input.addEventListener('input',sync);
      layer.addEventListener('click',event=>{
        if(event.target===layer||event.target.closest('[data-goal-cancel]')){ close(); return; }
        const pick=event.target.closest('[data-goal-pick]'); if(pick){ input.value=pick.dataset.goalPick; sync(); return; }
        if(event.target.closest('[data-goal-clear]')){ store(0); return; }
        if(event.target.closest('[data-goal-save]')){ const n=Math.round(Number(input.value)||0); if(n<1||n>spec.max){ input.focus(); return; } store(n); }
      });
      document.addEventListener('keydown',onKey);
      document.body.append(layer); layer.querySelector('[data-goal-save]').focus();
    }
    // The goal card on a tile's detail screen.
    function statsGoalHtml(metric,series,period){
      const ky=appLocale()==='ky-KG', number=new Intl.NumberFormat(appLocale()), spec=STATS_GOALS[metric], goal=statsGoal(metric), name=ky?STATS_METRICS[metric].ky:STATS_METRICS[metric].ru;
      if(!goal) return `<section class="stats-card sd-goal is-empty"><div class="stats-line"><i class="stats-circle">${icon('softStar')}</i><span><b>${ky?'Максат коюңуз':'Поставьте цель'}</b><small>${ky?`Мисалы, ${number.format(spec.presets[1])} ${spec.unit(spec.presets[1],ky)} ${spec.per==='week'?'аптасына':'күнүнө'}`:`Например, ${number.format(spec.presets[1])} ${spec.unit(spec.presets[1],ky)} ${spec.per==='week'?'в неделю':'в день'}`}</small></span></div><button type="button" class="sd-goal-btn" data-goal-open="${metric}">${ky?'Максат коюу':'Поставить цель'}</button></section>`;
      const weekly=spec.per==='week';
      const done=weekly?Array.from({length:7},(_,i)=>statsValue(statsShift(todayKey,i-6),metric)).reduce((a,b)=>a+b,0):statsValue(todayKey,metric);
      const met=!weekly&&period!=='all'?series.values.filter(v=>v>=goal).length:0;
      const share=Math.min(1,done/goal);
      return `<section class="stats-card sd-goal"><div class="sd-goal-head"><span><small>${ky?'Максат':'Цель'}</small><b>${number.format(goal)} ${spec.unit(goal,ky)} ${weekly?(ky?'аптасына':'в неделю'):(ky?'күнүнө':'в день')}</b></span><button type="button" class="sd-goal-btn ghost" data-goal-open="${metric}">${ky?'Өзгөртүү':'Изменить'}</button></div><div class="sd-goal-bar"><span class="stats-track" aria-hidden="true"><i style="width:${Math.round(share*100)}%"></i></span><span>${weekly?(ky?'Ушул 7 күндө':'За 7 дней'):(ky?'Бүгүн':'Сегодня')} <b>${number.format(done)} ${ky?'/':'из'} ${number.format(goal)}</b>${share>=1?` · ${ky?'аткарылды!':'выполнено!'}`:''}</span></div>${!weekly&&period!=='all'?`<p class="sd-verdict">${ky?`Максат ${series.values.length} күндүн ${met} күнүндө аткарылды.`:`Цель выполнена в ${met} из ${series.values.length} ${statsPlural(series.values.length,'дня','дней','дней')}.`}</p>`:''}</section>`;
    }
    /* The month's results as a picture for stories (1080×1920): drawn on a canvas, then
       shared through the phone's share sheet or saved. In the first week of a month it sums
       up the month before; later, the month so far. */
    function statsShareMonth(){
      const today=new Date(`${todayKey}T12:00:00`);
      if(today.getDate()<=7){ const prev=new Date(today.getFullYear(),today.getMonth()-1,1,12); return {key:statsKeyOf(prev).slice(0,7),done:true}; }
      return {key:todayKey.slice(0,7),done:false};
    }
    function statsMonthSummary(){
      const {key,done}=statsShareMonth(), entries=new Map(achievementEntries().map(entry=>[entry.date,entry]));
      const [y,m]=key.split('-').map(Number), last=done?new Date(y,m,0).getDate():Number(todayKey.slice(8));
      const sum={deeds:0,days:0,quran:0,salawat:0,zikr:0,tahajjud:0,fast:0,streak:0}; let run=0;
      for(let d=1;d<=last;d++){
        const date=`${key}-${String(d).padStart(2,'0')}`, entry=entries.get(date), tasks=entry?.tasks||{}, count=entry?Math.min(ChetDeeds.countDeeds(tasks,entry.qaza),ChetDeeds.maxFor(date)):0;
        sum.deeds+=count; if(count){ sum.days++; run++; sum.streak=Math.max(sum.streak,run); } else run=0;
        sum.quran+=amount(tasks.quran); sum.salawat+=amount(tasks.salawat); sum.zikr+=amount(tasks.tasbihat);
        if(tasks.tahajjud==='yes') sum.tahajjud++; if(tasks.fast==='yes') sum.fast++;
      }
      const month=new Intl.DateTimeFormat(appLocale(),{month:'long'}).format(new Date(y,m-1,1));
      return {...sum,key,done,last,month,year:y};
    }
    function statsShareHtml(){
      const ky=appLocale()==='ky-KG', s=statsMonthSummary(), genitive=s.month.replace(/ь$/,'я').replace(/й$/,'я').replace(/т$/,'та');
      return `<section class="stats-card ss-card"><div class="stats-line"><i class="stats-circle">${icon('softSend')}</i><span><b>${ky?`${s.month} айынын жыйынтыгы`:`Итоги ${genitive}`}</b><small>${ky?'Сторис үчүн сүрөт':'Картинка для сторис'}${s.done?'':(ky?' · азырынча':' · пока что')}</small></span></div><button type="button" class="sd-goal-btn" data-share-month>${ky?'Бөлүшүү':'Поделиться'}</button></section>`;
    }
    async function drawMonthCard(){
      const ky=appLocale()==='ky-KG', s=statsMonthSummary(), number=new Intl.NumberFormat(appLocale()), pl=statsPlural;
      const canvas=document.createElement('canvas'); canvas.width=1080; canvas.height=1920; const c=canvas.getContext('2d');
      const font=getComputedStyle(document.body).fontFamily||'sans-serif';
      try { await document.fonts?.ready; } catch {}
      const bg=c.createLinearGradient(0,0,0,1920); bg.addColorStop(0,'#3F6F66'); bg.addColorStop(.35,'#6F9C92'); bg.addColorStop(.7,'#B9D2CA'); bg.addColorStop(1,'#ECF0EF'); c.fillStyle=bg; c.fillRect(0,0,1080,1920);
      const glow=c.createRadialGradient(900,180,10,900,180,520); glow.addColorStop(0,'rgba(255,255,255,.28)'); glow.addColorStop(1,'rgba(255,255,255,0)'); c.fillStyle=glow; c.fillRect(0,0,1080,1920);
      const round=(x,y,w,h,r,fill)=>{ c.beginPath(); c.roundRect?c.roundRect(x,y,w,h,r):c.rect(x,y,w,h); c.fillStyle=fill; c.fill(); };
      const text=(t,x,y,size,weight,color,align='left')=>{ c.font=`${weight} ${size}px ${font}`; c.fillStyle=color; c.textAlign=align; c.fillText(t,x,y); };
      const genitive=s.month.replace(/ь$/,'я').replace(/й$/,'я').replace(/т$/,'та');
      text(ky?`${s.year}-жыл`:`${s.year}`,90,200,40,'500','rgba(255,255,255,.8)');
      text(ky?`${s.month.charAt(0).toUpperCase()+s.month.slice(1)} айы`:`Итоги ${genitive}`,90,300,92,'700','#FFFFFF');
      if(state.name) text(state.name,90,380,46,'500','rgba(255,255,255,.85)');
      round(60,460,960,420,64,'rgba(255,255,255,.94)');
      text(number.format(s.deeds),110,690,200,'700','#10161A');
      text(ky?'иш белгиленди':pl(s.deeds,'дело отмечено','дела отмечено','дел отмечено'),110,780,48,'500','#3B474D');
      text(ky?`${s.last} күндүн ${s.days} күнүндө`:`за ${s.days} ${pl(s.days,'день','дня','дней')} из ${s.last}`,110,840,40,'400','#5E6A71');
      const cells=[[ky?'Куран':'Куран',`${number.format(s.quran)}`,ky?'бет':pl(s.quran,'страница','страницы','страниц')],[ky?'Салабат':'Салават',number.format(s.salawat),ky?'жолу':pl(s.salawat,'раз','раза','раз')],[ky?'Зикир':'Зикр',number.format(s.zikr),ky?'жолу':pl(s.zikr,'раз','раза','раз')],[ky?'Тахажжуд':'Тахаджуд',String(s.tahajjud),ky?'түн':pl(s.tahajjud,'ночь','ночи','ночей')],[ky?'Орозо':'Пост',String(s.fast),ky?'күн':pl(s.fast,'день','дня','дней')],[ky?'Эң узун катар':'Лучшая серия',String(s.streak),ky?'күн':pl(s.streak,'день','дня','дней')]];
      cells.forEach(([label,value,unit],i)=>{ const x=60+(i%2)*490, y=920+Math.floor(i/2)*250; round(x,y,470,226,48,'rgba(255,255,255,.78)'); text(label,x+44,y+70,38,'500','#5E6A71'); text(value,x+44,y+164,84,'700','#10161A'); c.font=`700 84px ${font}`; const w=c.measureText(value).width; text(unit,x+44+w+16,y+164,36,'500','#5E6A71'); });
      try { const logo=await new Promise((resolve,reject)=>{ const img=new Image(); img.onload=()=>resolve(img); img.onerror=reject; img.src='img/logo.png'; }); // The logo file has wide transparent margins: draw only the mark itself.
        const probe=document.createElement('canvas'); probe.width=logo.width; probe.height=logo.height; const pc=probe.getContext('2d'); pc.drawImage(logo,0,0);
        const px=pc.getImageData(0,0,logo.width,logo.height).data; let x0=logo.width,y0=logo.height,x1=0,y1=0;
        for(let y=0;y<logo.height;y+=2) for(let x=0;x<logo.width;x+=2) if(px[(y*logo.width+x)*4+3]>24){ if(x<x0)x0=x; if(x>x1)x1=x; if(y<y0)y0=y; if(y>y1)y1=y; }
        if(x1>x0&&y1>y0){ const sw=x1-x0, sh=y1-y0, h=170, w=sw/sh*h; c.drawImage(logo,x0,y0,sw,sh,(1080-w)/2,1672,w,h); } } catch {}
      text(ky?'chetele.ru · күчтүү такыбаага жол':'chetele.ru · путь к сильному таква',540,1890,34,'500','#3B474D','center');
      return await new Promise(resolve=>canvas.toBlob(resolve,'image/png'));
    }
    async function openMonthShare(){
      const ky=appLocale()==='ky-KG', s=statsMonthSummary();
      const blob=await drawMonthCard(); if(!blob) return;
      const url=URL.createObjectURL(blob), name=`chetele-${s.key}.png`, file=new File([blob],name,{type:'image/png'});
      document.querySelector('.app-sheet.share-sheet')?.remove();
      const layer=document.createElement('div'); layer.className='app-sheet share-sheet';
      const canShare=!!(navigator.canShare&&navigator.canShare({files:[file]}));
      layer.innerHTML=`<section role="dialog" aria-modal="true" aria-labelledby="share-sheet-title"><i aria-hidden="true"></i><h2 id="share-sheet-title">${ky?'Айдын жыйынтыгы':'Итоги месяца'}</h2><img class="sh-preview" src="${url}" alt="${ky?'Айдын жыйынтыгы сүрөтү':'Картинка с итогами месяца'}">${canShare?`<button type="button" class="stay" data-share-go>${ky?'Бөлүшүү':'Поделиться'}</button>`:''}<a class="${canShare?'ds-close':'stay'} sh-save" href="${url}" download="${name}">${ky?'Сүрөттү сактоо':'Сохранить картинку'}</a><button type="button" class="ds-close" data-share-close>${ky?'Жабуу':'Закрыть'}</button></section>`;
      const close=()=>{ layer.remove(); URL.revokeObjectURL(url); document.removeEventListener('keydown',onKey); };
      const onKey=event=>{ if(event.key==='Escape') close(); };
      layer.addEventListener('click',async event=>{
        if(event.target===layer||event.target.closest('[data-share-close]')){ close(); return; }
        if(event.target.closest('[data-share-go]')){ try { await navigator.share({files:[file],title:ky?'Айдын жыйынтыгы':'Итоги месяца'}); } catch { /* the user closed the share sheet */ } }
      });
      document.addEventListener('keydown',onKey);
      document.body.append(layer); layer.querySelector('[data-share-go],.sh-save')?.focus();
    }
    let statsDetail={metric:'quran',period:'week',picked:-1};
    function renderStatsDetail(animate=false){
      const root=document.querySelector('#stats-detail-view'); if(!root) return;
      const locale=appLocale(), ky=locale==='ky-KG', number=new Intl.NumberFormat(locale), pl=statsPlural;
      const {metric,period}=statsDetail, spec=STATS_METRICS[metric], series=statsSeries(metric,period), values=series.values, last=values.length-1;
      const picked=statsDetail.picked>=0&&statsDetail.picked<=last?statsDetail.picked:last;
      const goal=STATS_GOALS[metric].per==='day'&&period!=='all'?statsGoal(metric):0, total=values.reduce((a,b)=>a+b,0), max=Math.max(1,...values,goal), active=values.filter(Boolean).length, avg=values.length?total/values.length:0;
      const binary=spec.binary&&period!=='all', unit=n=>spec.unit(n,ky,pl), short=spec.short(ky);
      const valueText=v=>binary?(v?(ky?'болду':'был'):(ky?'болгон жок':'не было')):`${number.format(v)} ${short||unit(v)}`;
      const name=ky?spec.ky:spec.ru, periodName=period==='week'?(ky?'апта':'неделя'):period==='month'?(ky?'ай':'месяц'):(ky?'бардык убакыт':'всё время');
      const headline=binary?`${active} ${unit(active)}`:`${number.format(total)} ${unit(total)}`;
      const height=v=>v?Math.max(10,Math.round(v/max*138)):4, wide=values.length>10;
      const bars=values.map((v,i)=>{ const on=i===picked, today=i===last&&period!=='all';
        const label=period==='month'&&i%5!==0&&!today&&!on?'':series.labels[i][0];
        return `<button type="button" class="sd-bar${on?' on':''}${v&&v===Math.max(...values)?' is-best':''}${v?'':' zero'}" data-detail-pick="${i}" aria-pressed="${on}" aria-label="${escapeHtml(series.labels[i][1])}: ${valueText(v)}"><i style="height:${height(v)}px${animate?`;animation-delay:${Math.round(i*(wide?18:45))}ms`:''}"${animate?' class="grow"':''}></i><small>${label}</small></button>`; }).join('');
      const best=values.indexOf(Math.max(...values)), prev=series.prev, change=prev==null?null:total-prev;
      const prevWord=period==='week'?(ky?'мурдагы аптага':'к прошлой'):(ky?'мурдагы айга':`к ${(series.prevName||'').replace(/[ьй]$/,m=>'ю').replace(/([^ю])$/,'$1у')}`);
      const same=period==='week'?(ky?'мурдагы аптадай':'как на прошлой'):(ky?'мурдагы айдай':'как в прошлом месяце');
      const delta=prev!=null&&change===0&&total?same:prev==null?(ky?`${values.length} ай`:`${values.length} ${pl(values.length,'месяц','месяца','месяцев')}`):binary?`${change>=0?'+':'−'}${Math.abs(change)} ${prevWord}`:prev?`${change>=0?'+':'−'}${Math.round(Math.abs(change)/prev*100)}% ${prevWord}`:total?(ky?'жаңы':'впервые'):(ky?'азырынча жок':'пока нет');
      const tone=prev==null||(!prev&&!total)||change===0?'':change>0?' up':' down';
      let streak=0; for(let i=last;i>=0&&values[i]>0;i--) streak++;
      const fact=(label,value,sub)=>`<div class="sd-fact"><small>${label}</small><b>${value}</b><span>${sub}</span></div>`;
      const facts=binary
        ?fact(ky?'Түндөр':'Ночей',`${active} ${ky?'/':'из'} ${values.length}`,period==='week'?(ky?'аптада':'за неделю'):(ky?'айда':'за месяц'))+fact(ky?'Катары менен':'Подряд',String(streak),unit(streak))+fact(ky?'Калтырылды':'Пропущено',String(values.length-active),unit(values.length-active))
        :fact(ky?'Орточо':'В среднем',number.format(Math.round(avg)),`${short||unit(Math.round(avg))} ${period==='all'?(ky?'айына':'в месяц'):(ky?'күнүнө':'в день')}`)+fact(period==='all'?(ky?'Мыкты ай':'Лучший месяц'):(ky?'Мыкты күн':'Лучший день'),number.format(values[best]||0),values[best]?escapeHtml(series.labels[best][1].replace(/^[^,]+,\s*/,'')):'—')+fact(period==='all'?(ky?'Айлар':'Месяцев'):(ky?'Белгиленген күн':'Дней с отметкой'),String(active),`${ky?'/':'из'} ${values.length}`);
      const row=(label,value,share,strong)=>`<div class="sd-compare-row${strong?' strong':''}"><span>${label}</span><span class="stats-track" aria-hidden="true"><i style="width:${Math.round(share*100)}%"></i></span><b>${value}</b></div>`;
      let compare='';
      if(prev==null){ const now=values[last], top=Math.max(...values);
        compare=row(ky?'Ушул ай':'Этот месяц',number.format(now),top?now/top:0,true)+row(ky?'Мыкты ай':'Лучший',number.format(top),top?1:0,false)+`<p class="sd-verdict">${top&&now<top?(ky?`Рекордго чейин ${number.format(top-now)} калды.`:`До рекорда осталось ${number.format(top-now)}.`):(ky?'Бул айыңыз — эң мыкты!':'Этот месяц — ваш лучший!')}</p>`;
      } else { const big=Math.max(total,prev,1);
        compare=row(period==='week'?(ky?'Ушул апта':'Эта неделя'):(ky?'Ушул ай':'Этот месяц'),binary?String(total):number.format(total),total/big,true)+row(period==='week'?(ky?'Мурдагы':'Прошлая'):(ky?'Мурдагы ай':'Прошлый'),binary?String(prev):number.format(prev),prev/big,false)+`<p class="sd-verdict">${!total&&!prev?(ky?'Бул мезгилде азырынча белги жок.':'За этот период пока нет отметок.'):change>=0?(ky?'Мурдагыдан көп — ушундай уланта бериңиз!':'Больше, чем в прошлый раз, — так держать!'):(ky?`Мурдагыга жетүү үчүн ${number.format(-change)} калды.`:`Чуть меньше, чем в прошлый раз: осталось ${number.format(-change)}, чтобы догнать.`)}</p>`;
      }
      const goalLine=goal?`<span class="sd-avg sd-goal-line" style="bottom:${24+Math.round(goal/max*138)}px"><em>${ky?'максат':'цель'} ${number.format(goal)}</em></span>`:'';
      const avgLine=binary||!total?'':`<span class="sd-avg" style="bottom:${24+Math.round(avg/max*138)}px"><em>${ky?'орточо':'в среднем'} ${number.format(Math.round(avg))}</em></span>`;
      const chips=Object.entries(STATS_METRICS).map(([id,item])=>`<button type="button" role="radio" aria-checked="${id===metric}" class="${id===metric?'active':''}" data-detail-metric="${id}">${ky?item.ky:item.ru}</button>`).join('');
      const seg=[['week',ky?'Апта':'Неделя'],['month',ky?'Ай':'Месяц'],['all',ky?'Бардык убакыт':'Всё время']].map(([id,label])=>`<button type="button" role="radio" data-detail-period="${id}" aria-checked="${id===period}" class="${id===period?'active':''}">${label}</button>`).join('');
      const pickedLabel=`${escapeHtml(series.labels[picked][1])}${picked===last&&period!=='all'?(ky?', бүгүн':', сегодня'):''}`;
      root.innerHTML=`<header class="stats-top"><div><button type="button" class="stats-back" data-view="stats-view" aria-label="${ky?'Статистикага кайтуу':'Назад к статистике'}">${icon('softBack')}</button><span class="stats-eyebrow">${ky?'Статистика':'Статистика'} · ${periodName}</span><h2>${name}</h2><span class="stats-period-label">${headline}</span></div><i class="stats-badge" aria-hidden="true">${icon(spec.icon)}</i></header>
        <div class="rt-metrics" role="radiogroup" aria-label="${ky?'Эмнени көрсөтүү':'Что показать'}">${chips}</div>
        <div class="stats-seg glass-seg" role="radiogroup" aria-label="${ky?'Мезгил':'Период подробностей'}">${seg}</div>
        <article class="stats-chart-card sd-card" aria-label="${name}"><div class="sd-head"><div class="sd-picked"><small>${pickedLabel}</small><b>${valueText(values[picked]||0)}</b></div><em class="sd-delta${tone}">${delta}</em></div><div class="sd-chart">${avgLine}${goalLine}<div class="sd-bars${wide?' wide':''}" style="grid-template-columns:repeat(${values.length},minmax(0,1fr))">${bars}</div></div><p class="sd-tip">${period==='all'?(ky?'Айды көрүү үчүн тилкени басыңыз':'Нажмите на столбик, чтобы увидеть месяц'):(ky?'Күндү көрүү үчүн тилкени басыңыз':'Нажмите на столбик, чтобы увидеть день')}</p></article>
        <div class="sd-facts">${facts}</div>
        ${statsGoalHtml(metric,series,period)}
        <section class="stats-card sd-compare" aria-label="${ky?'Салыштыруу':'Сравнение'}"><div class="stats-section-sub">${ky?'Салыштыруу':'Сравнение'}</div>${compare}</section>`;
      root.querySelectorAll('[data-icon]').forEach(slot=>{ if(!slot.firstChild) slot.innerHTML=icon(slot.dataset.icon); });
      slideControls();
    }
    /* One day of the calendar in a sheet: what was done, what was not, arrows to the days
       around it and a way to open the day to fill it in. */
    function statsDayHtml(key){
      const locale=appLocale(), ky=locale==='ky-KG', number=new Intl.NumberFormat(locale), pl=statsPlural;
      const entry=statsDayEntry(key), tasks=entry?.tasks||{}, qaza=entry?.qaza||{}, max=ChetDeeds.maxFor?ChetDeeds.maxFor(key):dailyMax();
      const count=Math.min(ChetDeeds.countDeeds(tasks,qaza),max), cap=text=>text.charAt(0).toUpperCase()+text.slice(1);
      const title=cap(new Intl.DateTimeFormat(locale,{weekday:'long',day:'numeric',month:'long'}).format(new Date(`${key}T12:00:00`)));
      const ago=Math.round((new Date(`${todayKey}T12:00:00`)-new Date(`${key}T12:00:00`))/86400000);
      const sub=ago===0?(ky?'бүгүн':'сегодня'):ago===1?(ky?'кечээ':'вчера'):ky?`${ago} күн мурун`:`${ago} ${pl(ago,'день','дня','дней')} назад`;
      const done=[], miss=[];
      const pages=ky?'бет':'стр.', times=n=>ky?'жолу':pl(n,'раз','раза','раз');
      [['quran','Куран',v=>`${number.format(v)} ${pages}`],['jauchan','Жавшан',v=>`${number.format(v)} ${pages}`],['kitap',ky?'Китеп':'Книги',v=>`${number.format(v)} ${pages}`]].forEach(([id,name,fmt])=>{ const v=amount(tasks[id]); if(v) done.push([name,fmt(v)]); else miss.push(name); });
      const nafil=tasks.nafil&&typeof tasks.nafil==='object'?tasks.nafil:(tasks.nafil?Object.fromEntries(NAFIL.map(item=>[item.id,true])):{}), nafilDone=NAFIL.filter(item=>nafil[item.id]);
      if(nafilDone.length) done.push([ky?'Напил':'Нафиль',nafilDone.length===NAFIL.length?(ky?'бардыгы':'все 4'):nafilDone.map(item=>item.label).join(', ')]);
      NAFIL.filter(item=>!nafil[item.id]).forEach(item=>miss.push(`${ky?'Напил':'Нафиль'} ${item.label}`));
      if(tasks.tahajjud==='yes') done.push([ky?'Тахажжуд':'Тахаджуд',ky?'болду':'был']); else miss.push(ky?'Тахажжуд':'Тахаджуд');
      if(!(ChetDeeds.FAST_FROM&&key<ChetDeeds.FAST_FROM)){ if(tasks.fast==='yes') done.push([ky?'Орозо':'Пост',ky?'кармалды':'держали']); else miss.push(ky?'Орозо':'Пост'); }
      [['salawat',ky?'Салабат':'Салават'],['tasbihat',ky?'Зикир':'Зикр']].forEach(([id,name])=>{ const v=amount(tasks[id]); if(v) done.push([name,`${number.format(v)} ${times(v)}`]); else miss.push(name); });
      const qazaCount=Object.values(qaza).reduce((sum,value)=>sum+amount(value),0);
      if(qazaCount) done.push([ky?'Каза намаз':'Каза-намазы',`${qazaCount} ${ky?'толукталды':'восполнено'}`]); else miss.push(ky?'Каза намаз':'Каза-намазы');
      const level=!count?(ky?'бош':'пусто'):count>=max?(ky?'бардыгы':'все дела'):count/max>=2/3?(ky?'дээрлик бардыгы':'почти все'):count/max>=1/3?(ky?'жарымы':'половина'):(ky?'аз':'мало');
      const older=statsShift(key,-1), newer=statsShift(key,1), canNewer=newer<=todayKey, canOlder=ago<365;
      const action=!count?(ky?'Артынан белгилөө':'Отметить задним числом'):key===todayKey?(ky?'Бүгүнкүнү толуктоо':'Дополнить сегодня'):(ky?'Күндү ачып толуктоо':'Открыть день и дополнить');
      return `<section role="dialog" aria-modal="true" aria-labelledby="day-sheet-title"><i aria-hidden="true"></i>
        <div class="ds-head"><button type="button" class="ds-arrow" data-day-go="${older}" aria-label="${ky?'Мурунку күн':'Предыдущий день'}"${canOlder?'':' disabled'}>${icon('softBack')}</button><div aria-live="polite"><h2 id="day-sheet-title">${title}</h2><small>${sub}</small></div><button type="button" class="ds-arrow" data-day-go="${newer}" aria-label="${ky?'Кийинки күн':'Следующий день'}"${canNewer?'':' disabled'}>${icon('softChevron')}</button></div>
        <div class="ds-sum"><div><b>${count}</b><span>${ky?`/ ${max} иш`:`из ${max} ${pl(max,'дела','дел','дел')}`}</span><em class="${count?'on':''}">${level}</em></div><span class="stats-track" aria-hidden="true"><i style="width:${Math.round(count/max*100)}%"></i></span></div>
        ${count?`<div class="ds-group"><small>${ky?'Аткарылды':'Сделано'}</small><ul class="ds-done">${done.map(([name,value])=>`<li><i aria-hidden="true">${icon('check')}</i><b>${name}</b><span>${value}</span></li>`).join('')}</ul></div>${miss.length?`<div class="ds-group"><small>${ky?'Белгиленген жок':'Не отмечено'}</small><div class="ds-miss">${miss.map(name=>`<span>${name}</span>`).join('')}</div></div>`:''}`:`<div class="ds-empty"><b>${ky?'Бул күнү эч нерсе белгиленген жок':'В этот день ничего не отмечено'}</b><span>${ky?'Аткарып, бирок белгилөөнү унутуп калсаңыз — күндү ачып, артынан белгилеңиз.':'Если что-то делали, но забыли отметить, — откройте день и отметьте задним числом.'}</span></div>`}
        <button type="button" class="stay" data-day-open="${key}">${action}</button><button type="button" class="ds-close" data-day-close>${ky?'Жабуу':'Закрыть'}</button></section>`;
    }
    function openStatsDay(key){
      document.querySelector('.app-sheet.day-sheet')?.remove();
      const layer=document.createElement('div'); layer.className='app-sheet day-sheet'; layer.innerHTML=statsDayHtml(key);
      const close=()=>{ layer.remove(); document.removeEventListener('keydown',onKey); };
      const onKey=event=>{ if(event.key==='Escape') close(); };
      layer.addEventListener('click',async event=>{
        if(event.target===layer||event.target.closest('[data-day-close]')){ close(); return; }
        const go=event.target.closest('[data-day-go]'); if(go&&!go.disabled){ layer.innerHTML=statsDayHtml(go.dataset.dayGo); layer.querySelector('.ds-arrow:not([disabled])')?.focus(); window.cheteleTranslateUi?.(); return; }
        const open=event.target.closest('[data-day-open]'); if(open){ close(); calendarSelectedDate=open.dataset.dayOpen; await openSelectedDay(); showView('today-view'); }
      });
      document.addEventListener('keydown',onKey);
      document.body.append(layer); layer.querySelector('.ds-close').focus();
    }
    document.addEventListener('click',event=>{
      const target=event.target;
      const tile=target.closest('[data-stats-detail]'); if(tile){ statsDetail={metric:tile.dataset.statsDetail,period:statsPeriod,picked:-1}; showView('stats-detail-view'); return; }
      const metric=target.closest('[data-detail-metric]'); if(metric){ if(metric.dataset.detailMetric!==statsDetail.metric){ statsDetail={...statsDetail,metric:metric.dataset.detailMetric,picked:-1}; renderStatsDetail(true); } return; }
      const period=target.closest('[data-detail-period]'); if(period){ if(period.dataset.detailPeriod!==statsDetail.period){ statsDetail={...statsDetail,period:period.dataset.detailPeriod,picked:-1}; renderStatsDetail(true); } return; }
      const pick=target.closest('[data-detail-pick]'); if(pick){ statsDetail={...statsDetail,picked:Number(pick.dataset.detailPick)}; renderStatsDetail(); return; }
      const day=target.closest('[data-stats-day]'); if(day){ openStatsDay(day.dataset.statsDay); return; }
      const goalOpen=target.closest('[data-goal-open]'); if(goalOpen){ openGoalSheet(goalOpen.dataset.goalOpen); return; }
      if(target.closest('[data-share-month]')){ const button=target.closest('[data-share-month]'); button.disabled=true; openMonthShare().finally(()=>{ button.disabled=false; }); return; }
    });
    /* Insights: a few short sentences drawn from the user's own days — the week compared
       with the weeks before, the nafil most often missed, the strongest day of the week, a
       streak waiting for today. At most three, the most useful first. */
    function statsInsightsHtml(){
      const ky=appLocale()==='ky-KG', number=new Intl.NumberFormat(appLocale()), pl=statsPlural;
      const entries=new Map(achievementEntries().map(entry=>[entry.date,entry])), count=key=>{ const entry=entries.get(key); return entry?Math.min(ChetDeeds.countDeeds(entry.tasks,entry.qaza),ChetDeeds.maxFor(key)):0; };
      const days=n=>ky?'күн':pl(n,'день','дня','дней'), deeds=n=>ky?'иш':pl(n,'дело','дела','дел');
      const items=[];
      const last28=Array.from({length:28},(_,i)=>statsShift(todayKey,i-27)), active=last28.filter(key=>count(key)>0);
      const streak=Number(state.streak||0), hour=new Date().getHours();
      if(!count(todayKey)&&streak>0&&hour>=12) items.push(['softFlame','warn',ky?`Бүгүн азырынча эч нерсе белгиленген жок. Бир иш — жана ${streak} күндүк катар уланат.`:`Сегодня ещё ничего не отмечено. Одно дело — и серия «${streak} ${days(streak)} подряд» продолжится.`]);
      // This week against the seven before it, each the seven days ending on the same weekday.
      const weekSum=offset=>Array.from({length:7},(_,i)=>count(statsShift(todayKey,i-6-offset*7))).reduce((a,b)=>a+b,0);
      const now=weekSum(0), before=Array.from({length:7},(_,i)=>weekSum(i+1)), seen=before.filter(Boolean).length;
      if(now&&seen>=3&&now>Math.max(...before)) items.push(['softSpark','good',ky?`Бул аптаңыз — акыркы эки айдагы эң мыктысы: ${number.format(now)} иш.`:`Эта неделя — ваша лучшая за два месяца: ${number.format(now)} ${deeds(now)}.`]);
      else if(now&&seen>=3){ const best=Math.max(...before); if(best-now>0&&best-now<=8) items.push(['softSpark','',ky?`Мыкты аптаңызга чейин ${best-now} иш калды.`:`До вашей лучшей недели осталось ${best-now} ${deeds(best-now)}.`]); }
      // The nafil prayer done least often over four weeks.
      if(active.length>=7){
        const by=NAFIL.map(item=>[item,active.filter(key=>{ const nafil=entries.get(key)?.tasks?.nafil; return nafil===true||(nafil&&typeof nafil==='object'&&nafil[item.id]); }).length]).sort((a,b)=>a[1]-b[1]);
        const [weak,times]=by[0], strong=by[by.length-1][1];
        if(strong-times>=3&&times<active.length/2) items.push(['softNafil','',ky?`Көбүнчө ${weak.label} напилин калтырасыз — ${active.length} күндүн ${times} күнүндө гана.`:`Чаще всего пропускаете нафиль ${weak.label} — лишь ${times} из ${active.length} ${pl(active.length,'дня','дней','дней')}.`]);
      }
      // The strongest day of the week over eight weeks.
      const last56=Array.from({length:56},(_,i)=>statsShift(todayKey,i-56)).filter(key=>count(key)>0);
      if(last56.length>=14){
        const sums=Array(7).fill(0), seenDays=Array(7).fill(0);
        last56.forEach(key=>{ const d=new Date(`${key}T12:00:00`).getDay(); sums[d]+=count(key); seenDays[d]++; });
        const avg=sums.map((sum,d)=>seenDays[d]>=2?sum/seenDays[d]:0), overall=sums.reduce((a,b)=>a+b,0)/last56.length, bestDay=avg.indexOf(Math.max(...avg));
        if(avg[bestDay]>=overall*1.15){ const name=new Intl.DateTimeFormat(appLocale(),{weekday:'long'}).format(new Date(2026,10,1+((bestDay+7)%7))); const plural=ky?name:({'понедельник':'понедельникам','вторник':'вторникам','среда':'средам','четверг':'четвергам','пятница':'пятницам','суббота':'субботам','воскресенье':'воскресеньям'})[name]||name; items.push(['softStats','',ky?`${name} күндөрү эң көп иш жасайсыз — орточо ${Math.round(avg[bestDay])} иш.`:`По ${plural} вы делаете больше всего — в среднем ${Math.round(avg[bestDay])} ${deeds(Math.round(avg[bestDay]))}.`]); }
      }
      if(!items.length) items.push(['softInfo','',ky?'Бир нече күн иштерди белгилеп туруңуз — бул жерде адаттарыңыз боюнча кеңештер пайда болот.':'Отмечайте дела несколько дней — здесь появятся подсказки по вашим привычкам.']);
      return `<section class="stats-card si-card" aria-label="${ky?'Кеңештер':'Подсказки'}"><div class="stats-section-sub">${ky?'Кеңештер':'Подсказки'}</div>${items.slice(0,3).map(([iconName,tone,text])=>`<p class="si-item${tone?` is-${tone}`:''}"><i class="stats-circle" aria-hidden="true">${icon(iconName)}</i><span>${text}</span></p>`).join('')}</section>`;
    }
    /* Personal records over all time, each with the day or week it was set. */
    function statsRecordsHtml(){
      const ky=appLocale()==='ky-KG', locale=appLocale(), number=new Intl.NumberFormat(locale), pl=statsPlural;
      const list=achievementEntries(), day=key=>new Intl.DateTimeFormat(locale,{day:'numeric',month:'long'}).format(new Date(`${key}T12:00:00`));
      let streak=0, longest=0, tahajjud=0, longestTahajjud=0, previous='';
      const best={deeds:[0,''],salawat:[0,''],quran:[0,''],zikr:[0,'']}, weeks={};
      list.forEach(entry=>{
        const consecutive=previous===statsShift(entry.date,-1), count=Math.min(ChetDeeds.countDeeds(entry.tasks,entry.qaza),ChetDeeds.maxFor(entry.date));
        streak=count>0?(consecutive?streak+1:1):0; longest=Math.max(longest,streak);
        tahajjud=entry.tasks?.tahajjud==='yes'?(consecutive?tahajjud+1:1):0; longestTahajjud=Math.max(longestTahajjud,tahajjud);
        [['deeds',count],['salawat',amount(entry.tasks?.salawat)],['quran',amount(entry.tasks?.quran)],['zikr',amount(entry.tasks?.tasbihat)]].forEach(([id,value])=>{ if(value>best[id][0]) best[id]=[value,entry.date]; });
        const monday=statsShift(entry.date,-((new Date(`${entry.date}T12:00:00`).getDay()+6)%7)); weeks[monday]=(weeks[monday]||0)+count;
        if(count>0||entry.date!==todayKey) previous=entry.date;
      });
      longest=Math.max(longest,Number(state.bestStreak||0));
      const [weekStart,weekSum]=Object.entries(weeks).sort((a,b)=>b[1]-a[1])[0]||['',0];
      const weekLabel=weekStart?(()=>{ const range=new Intl.DateTimeFormat(locale,{day:'numeric',month:'long'}), from=new Date(`${weekStart}T12:00:00`), to=new Date(`${statsShift(weekStart,6)}T12:00:00`); return (range.formatRange?range.formatRange(from,to):`${range.format(from)} – ${range.format(to)}`).replace(/\./g,''); })():'—';
      const card=(iconName,label,value,sub,fresh)=>`<div class="sr-card"><span class="sr-top"><small>${label}</small><i aria-hidden="true">${icon(iconName)}</i></span><b>${value}</b><span class="sr-sub">${sub}${fresh?`<em>${ky?'бүгүн':'сегодня'}</em>`:''}</span></div>`;
      const at=([value,key],unit)=>value?[`${number.format(value)}${unit?` <small>${unit}</small>`:''}`,day(key),key===todayKey]:['—',ky?'азырынча жок':'пока нет',false];
      const deedsAt=at(best.deeds,''), salawatAt=at(best.salawat,''), quranAt=at(best.quran,ky?'бет':'стр.');
      return `<section class="sr-section" aria-label="${ky?'Жеке рекорддор':'Личные рекорды'}"><div class="stats-section-head"><h3>${ky?'Жеке рекорддор':'Личные рекорды'}</h3><span>${ky?'бардык убакыт':'за всё время'}</span></div><div class="sr-grid">`
        +card('softFlame',ky?'Эң узун катар':'Лучшая серия',`${longest} <small>${ky?'күн':pl(longest,'день','дня','дней')}</small>`,ky?'катары менен':'подряд',false)
        +card('softTahajjud',ky?'Тахажжуд катары':'Тахаджуд подряд',`${longestTahajjud} <small>${ky?'түн':pl(longestTahajjud,'ночь','ночи','ночей')}</small>`,ky?'катары менен':'подряд',false)
        +card('softDeeds',ky?'Бир күндө иштер':'Дел за день',deedsAt[0],deedsAt[1],deedsAt[2])
        +card('softStats',ky?'Мыкты апта':'Лучшая неделя',`${number.format(weekSum)} <small>${ky?'иш':pl(weekSum,'дело','дела','дел')}</small>`,weekLabel,weekStart&&statsShift(weekStart,6)>=todayKey&&weekStart<=todayKey)
        +card('softSalawat',ky?'Салабат бир күндө':'Салават за день',salawatAt[0],salawatAt[1],salawatAt[2])
        +card('softQuran',ky?'Куран бир күндө':'Куран за день',quranAt[0],quranAt[1],quranAt[2])
        +`</div></section>`;
    }
    function renderStats(animate=false){
      const totals=emptyTotals(); personalEntries().forEach(entry=>addToTotals(totals,entry));
      const week=document.querySelector('#stats-week'), content=document.querySelector('#stats-content'), note=document.querySelector('#stats-note'), periodLabel=document.querySelector('#stats-period-label');
      if(!week||!content||!note||!periodLabel) return;
      document.querySelectorAll('#stats-view .stats-seg [data-period]').forEach(button=>{ const on=button.dataset.period===statsPeriod; button.classList.toggle('active',on); button.setAttribute('aria-checked',String(on)); });
      document.querySelectorAll('#stats-view [data-icon]').forEach(slot=>{ if(!slot.firstChild) slot.innerHTML=icon(slot.dataset.icon); });
      const locale=appLocale(), number=new Intl.NumberFormat(locale), ky=locale==='ky-KG';
      const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
      const days=n=>ky?'күн':plural(n,'день','дня','дней'), times=n=>ky?'жолу':plural(n,'раз','раза','раз');
      const pad=n=>String(n).padStart(2,'0');
      const keyOf=date=>`${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`;
      const dateOf=key=>new Date(`${key}T12:00:00`);
      const shift=(key,offset)=>{ const date=dateOf(key); date.setDate(date.getDate()+offset); return keyOf(date); };
      const savedEntry=key=>(state.remoteEntries||[]).find(entry=>entry?.date===key)||state.drafts?.[key]||state.history?.[key];
      const dayCount=key=>Math.min(key===entryDateKey?completed():entryCompletedCount(savedEntry(key)),dailyMax());
      const capital=text=>text.charAt(0).toUpperCase()+text.slice(1);
      const format=(date,options)=>new Intl.DateTimeFormat(locale,options).format(date);
      const signed=n=>n>0?`+${number.format(n)}`:`−${number.format(-n)}`;
      const max=dailyMax(), today=dateOf(todayKey), todayNumber=today.getDate();
      const keys=new Set([todayKey,...(state.remoteEntries||[]).map(entry=>entry?.date).filter(Boolean),...Object.keys(state.history||{}),...Object.keys(state.drafts||{})]);
      const firstKey=[...keys].filter(key=>key<=todayKey).sort()[0]||todayKey;
      const spanDays=Math.round((today-dateOf(firstKey))/86400000)+1;
      const periodDays=statsPeriod==='week'?7:statsPeriod==='month'?todayNumber:spanDays;

      /* Chart: days of the week, weeks of the month, months for all time. */
      let bars=[], title='', delta='', label='';
      if(statsPeriod==='week'){
        const start=shift(todayKey,-6), from=dateOf(start), same=from.getMonth()===today.getMonth();
        bars=Array.from({length:7},(_,index)=>{ const key=shift(start,index); return {label:capital(format(dateOf(key),{weekday:'short'}).replace('.','')),value:dayCount(key),max}; });
        const previous=Array.from({length:7},(_,index)=>dayCount(shift(start,index-7))).reduce((sum,value)=>sum+value,0);
        const diff=bars.reduce((sum,bar)=>sum+bar.value,0)-previous;
        title=ky?'Аптадагы иштер':'Дел за неделю';
        delta=diff?`${signed(diff)} ${ky?'мурдагы аптага':'к прошлой'}`:(ky?'мурдагы аптадай':'как на прошлой');
        const range=new Intl.DateTimeFormat(locale,{day:'numeric',month:same?'long':'short'});
        label=(range.formatRange?range.formatRange(from,today):`${range.format(from)} – ${range.format(today)}`).replace(/\./g,'');
      } else if(statsPeriod==='month'){
        const month=todayKey.slice(0,8);
        bars=Array.from({length:Math.ceil(todayNumber/7)},(_,index)=>{ let value=0; for(let day=index*7+1;day<=Math.min(todayNumber,index*7+7);day++) value+=dayCount(`${month}${pad(day)}`); return {label:ky?`${index+1}-апта`:`${index+1} н`,value,max:max*7}; });
        const first=dateOf(`${month}01`); first.setMonth(first.getMonth()-1);
        const previousMonth=keyOf(first).slice(0,8), previousLength=new Date(first.getFullYear(),first.getMonth()+1,0).getDate();
        const previous=Array.from({length:Math.min(todayNumber,previousLength)},(_,index)=>dayCount(`${previousMonth}${pad(index+1)}`)).reduce((sum,value)=>sum+value,0);
        const diff=bars.reduce((sum,bar)=>sum+bar.value,0)-previous;
        // «к сентябрю», «к маю»: the dative of the previous month.
        const name=format(first,{month:'long'}), dative=/[ьй]$/.test(name)?name.slice(0,-1)+'ю':name+'у';
        title=ky?'Айдагы иштер':'Дел за месяц';
        delta=diff?`${signed(diff)} ${ky?'мурдагы айга':`к ${dative}`}`:(ky?'мурдагы айдай':'как в прошлом месяце');
        label=capital(format(today,{month:'long'}));
      } else {
        const byMonth={}; keys.forEach(key=>{ byMonth[key.slice(0,7)]=(byMonth[key.slice(0,7)]||0)+dayCount(key); });
        const start=dateOf(`${firstKey.slice(0,8)}01`), months=(today.getFullYear()-start.getFullYear())*12+today.getMonth()-start.getMonth()+1;
        bars=Array.from({length:Math.min(months,8)},(_,index)=>{ const date=dateOf(`${todayKey.slice(0,8)}01`); date.setMonth(date.getMonth()-(Math.min(months,8)-1-index)); const length=new Date(date.getFullYear(),date.getMonth()+1,0).getDate(); return {label:capital(format(date,{month:'short'}).replace('.','')),value:byMonth[keyOf(date).slice(0,7)]||0,max:max*length}; });
        title=ky?'Бардык иштер':'Дел за всё время';
        delta=ky?`${months} ай`:`${months} ${plural(months,'месяц','месяца','месяцев')}`;
        label=ky?`${start.getFullYear()}-жылдан бери`:`С ${format(start,{day:'numeric',month:'long'}).replace(/^\d+\s*/,'')} ${start.getFullYear()}`;
      }
      const done=statsPeriod==='all'?[...keys].reduce((sum,key)=>sum+dayCount(key),0):bars.reduce((sum,bar)=>sum+bar.value,0);
      const possible=periodDays*max, top=Math.max(...bars.map(bar=>bar.value)), wide=bars.length>7;
      periodLabel.textContent=label;
      const weekHtml=`<section class="stats-chart" aria-label="${title}"><div class="stats-chart-bar"><span>${title}</span><em>${delta}</em></div><article class="stats-chart-card"><div class="stats-sum"><b>${number.format(done)}</b><span>${ky?`/ ${number.format(possible)} иш`:`из ${number.format(possible)} ${plural(possible,'дела','дел','дел')}`}</span><strong>${possible?Math.round(done/possible*100):0}%</strong></div><div class="stats-bars${wide?' is-wide':''}${animate?' grow':''}" role="img" aria-label="${bars.map(bar=>`${bar.label}: ${bar.value}`).join(', ')}" style="grid-template-columns:repeat(${bars.length},minmax(0,1fr))">${bars.map((bar,index)=>`<div class="stats-bar${index===bars.length-1?' is-now':bar.value===top&&top?' is-peak':''}"><span>${number.format(bar.value)}</span><i style="height:${Math.max(8,Math.round(bar.value/Math.max(1,bar.max)*104))}px;--i:${index}"></i><small>${bar.label}</small></div>`).join('')}</div></article></section>`;
      // The chart is rewritten only when its numbers change; a quiet refresh never replays the bar growth.
      const weekKey=weekHtml.replace(' grow','');
      if(animate||week.statsKey!==weekKey){ week.statsKey=weekKey; week.innerHTML=animate?weekHtml:weekKey; }

      /* Tiles: totals for the period. */
      [['quran',totals.quran,n=>ky?'бет':plural(n,'страница','страницы','страниц')],['salawat',totals.salawat,times],['tahajjud',totals.tahajjud,n=>ky?'түн':plural(n,'ночь','ночи','ночей')],['zikr',totals.tasbihat,times]].forEach(([id,value,unit])=>{
        const element=document.querySelector(`#stats-${id}`); if(!element) return;
        // A repeat render with the same total leaves a running count-up alone.
        if(animate){ element.statTarget=value; animateStatNumber(element,value,number); }
        else if(element.statTarget!==value||!statsAnimations.has(element)){ const running=statsAnimations.get(element); if(running) cancelAnimationFrame(running); statsAnimations.delete(element); element.statTarget=value; element.textContent=number.format(value); }
        document.querySelector(`#stats-${id}-unit`).textContent=unit(value);
        // The chip on the tile: change against the period before, or nights out of days for tahajjud.
        const trend=document.querySelector(`#stats-${id}-trend`), series=statsSeries(id,statsPeriod), now=series.values.reduce((a,b)=>a+b,0);
        const text=id==='tahajjud'&&statsPeriod!=='all'?`${series.values.filter(Boolean).length} ${ky?'/':'из'} ${series.values.length}`:series.prev?`${now>=series.prev?'+':'−'}${Math.round(Math.abs(now-series.prev)/series.prev*100)}%`:'';
        if(trend) trend.innerHTML=`${text}${icon('softChevron')}`;
        const ring=document.querySelector(`[data-stats-detail="${id}"] .stats-tile-top i`); if(ring){ const goal=statsGoal(id); ring.classList.toggle('has-goal',!!goal); ring.style.setProperty('--goal',goal?String(Math.round(statsGoalShare(id)*100)):'0'); ring.classList.toggle('goal-done',!!goal&&statsGoalShare(id)>=1); }
        document.querySelector(`[data-stats-detail="${id}"]`)?.setAttribute('aria-label',`${({quran:'Куран',salawat:ky?'Салабат':'Салават',tahajjud:ky?'Тахажжуд':'Тахаджуд',zikr:ky?'Зикир':'Зикр'})[id]}: ${number.format(value)} ${unit(value)}${text?`, ${text}`:''}. ${ky?'Толугураак':'Подробнее'}`);
      });
      if(document.querySelector('#stats-detail-view')?.classList.contains('active')) renderStatsDetail();

      /* Prayer, streak and the other deeds. */
      const nafilRow=item=>{ const value=totals.nafilBy[item.id], name=ky&&item.id==='kuptan'?'Куптан':item.label; return `<div class="stats-nafil" aria-label="${ky?'Напил':'Нафиль'} ${name}: ${value} ${ky?'/':'из'} ${periodDays} ${days(periodDays)}"><span class="stats-nafil-name"><b>${name}</b><small>${ky?'напил':'нафиль'}</small></span><span class="stats-track" aria-hidden="true"><i style="width:${Math.min(100,Math.round(value/periodDays*100))}%"></i></span><b class="stats-nafil-value">${number.format(value)}/${number.format(periodDays)}</b></div>`; };
      const line=(iconName,name,sub,value)=>`<div class="stats-line"><i class="stats-circle">${icon(iconName)}</i><span><b>${name}</b><small>${sub}</small></span><strong>${value}</strong></div>`;
      const qaza=Object.values(totals.qaza).reduce((sum,value)=>sum+value,0);
      const streak=Number(state.streak||0), best=Math.max(Number(state.bestStreak||0),streak);
      // Four whole weeks from Monday; the days after today are drawn empty and cannot be opened.
      const gridStart=shift(todayKey,-((today.getDay()+6)%7)-21);
      const heat=Array.from({length:28},(_,index)=>{ const key=shift(gridStart,index), dayMax=ChetDeeds.maxFor?ChetDeeds.maxFor(key):max, count=key>todayKey?0:dayCount(key), share=count/dayMax; return {key,count,dayMax,future:key>todayKey,level:share<=0?0:share<1/3?1:share<2/3?2:3}; });
      const marked=heat.filter(day=>day.level).length;
      const weekdays=Array.from({length:7},(_,index)=>capital(format(dateOf(shift(gridStart,index)),{weekday:'short'}).replace('.','')).slice(0,2));
      const heatCell=day=>{ const num=Number(day.key.slice(8));
        if(day.future) return `<span class="heat-future" aria-hidden="true">${num}</span>`;
        return `<button type="button" class="heat-${day.level}${day.key===todayKey?' is-today':''}" data-stats-day="${day.key}" aria-label="${format(dateOf(day.key),{day:'numeric',month:'long'})}${day.key===todayKey?(ky?', бүгүн':', сегодня'):''}: ${day.count} ${ky?'/':'из'} ${day.dayMax}">${num}</button>`; };
      const pages=ky?'бет':'стр.';
      const contentHtml=`${statsInsightsHtml()}<section class="stats-prayer"><div class="stats-section-head"><h3>Намаз</h3><span>${ky?`${periodDays} күндөгү напил`:`нафиль за ${number.format(periodDays)} ${days(periodDays)}`}</span></div><div class="stats-card">${NAFIL.map(nafilRow).join('')}<hr>${line('softQaza',ky?'Каза намаздар':'Пропущенные намазы',ky?'мезгил ичинде толукталды':'восполнено за период',number.format(qaza))}</div></section><section class="stats-fast"><div class="stats-section-head"><h3>${ky?'Орозо':'Пост'}</h3><span>${ky?'ыктыярдуу':'добровольный'}</span></div><div class="stats-card">${line('softFast',ky?'Орозо кармаган күндөр':'Дней в посту',ky?`${periodDays} күндөн`:`из ${number.format(periodDays)} ${days(periodDays)} периода`,number.format(totals.fast))}<div class="stats-track stats-fast-track" aria-hidden="true"><i style="width:${periodDays?Math.min(100,Math.round(totals.fast/periodDays*100)):0}%"></i></div></div></section><section class="stats-card stats-streak" aria-label="${ky?'Катар':'Серия'}"><div class="stats-line"><i class="stats-circle is-flame">${icon('softFlame')}</i><span><b>${streak?(ky?`${streak} күн катары менен`:`${streak} ${days(streak)} подряд`):(ky?'Катарды бүгүн баштаңыз':'Начните серию сегодня')}</b><small>${ky?`Эң узун катар — ${best} күн`:`Лучшая серия — ${best} ${days(best)}`}</small></span></div><div class="stats-weekdays" aria-hidden="true">${weekdays.map(day=>`<span>${day}</span>`).join('')}</div><div class="stats-heat" role="group" aria-label="${ky?`Акыркы төрт апта: ${marked} күн белгиленди`:`Последние четыре недели: отмечено ${marked} ${days(marked)}`}">${heat.map(heatCell).join('')}</div><div class="stats-heat-foot"><span class="stats-legend">${ky?'Аз':'Меньше'}<span class="heat-0"></span><span class="heat-1"></span><span class="heat-2"></span><span class="heat-3"></span>${ky?'Көп':'Больше'}</span><span class="stats-heat-hint">${ky?'күндү басыңыз':'нажмите на день'}</span></div></section>${statsRecordsHtml()}${statsShareHtml()}<section class="stats-card"><div class="stats-section-sub">${ky?'Башка иштер':'Другие дела'}</div>${line('softJauchan',ky?'Жавшан':'Жавшан',ky?'мезгил ичинде':'за период',`${number.format(totals.jauchan)} ${pages}`)}${line('softKitap',ky?'Китеп':'Книги',ky?'мезгил ичинде':'за период',`${number.format(totals.kitap)} ${pages}`)}</section>`;
      // Rebuilding unchanged cards would replay their entrance, so only real changes are written.
      if(animate||content.statsHtml!==contentHtml){ content.statsHtml=contentHtml; content.innerHTML=contentHtml; }
      note.hidden=state.statsOnline!==false;
      note.textContent=ky?'Ушул түзмөктө сакталган маалымат көрсөтүлдү. Бардык тарыхты көрүү үчүн интернетке кошулуңуз.':'Показаны данные, сохранённые на этом устройстве. Подключитесь к интернету, чтобы увидеть всю историю.';
    }
    /* A question in the app's own bottom sheet instead of the browser's confirm(). Resolves true or false. */
    function askSheet({title,text='',ok='Да',cancel='Отмена',danger=true}){
      return new Promise(resolve=>{
        const layer=document.createElement('div'); layer.className='app-sheet';
        layer.innerHTML=`<section role="dialog" aria-modal="true" aria-labelledby="app-sheet-title"><i aria-hidden="true"></i><h2 id="app-sheet-title">${escapeHtml(title)}</h2>${text?`<p>${escapeHtml(text)}</p>`:''}<button type="button" class="${danger?'leave':'stay'}" data-sheet-ok>${escapeHtml(ok)}</button><button type="button" class="${danger?'stay':'leave'}" data-sheet-no>${escapeHtml(cancel)}</button></section>`;
        const done=value=>{ layer.remove(); document.removeEventListener('keydown',onKey); resolve(value); };
        const onKey=event=>{ if(event.key==='Escape') done(false); };
        layer.addEventListener('click',event=>{ if(event.target===layer||event.target.closest('[data-sheet-no]')) done(false); else if(event.target.closest('[data-sheet-ok]')) done(true); });
        document.addEventListener('keydown',onKey);
        document.body.append(layer); layer.querySelector('[data-sheet-no]').focus();
      });
    }
    function escapeHtml(value){ return String(value).replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char])); }
    function achievementEntries(){
      const entries=new Map();
      Object.entries(state.history||{}).forEach(([date,entry])=>entries.set(date,{date,tasks:entry.tasks||{},qaza:entry.qaza||{}}));
      if(Array.isArray(state.remoteEntries)) state.remoteEntries.forEach(entry=>entries.set(entry.date,{date:entry.date,tasks:entry.tasks||{},qaza:entry.qaza||{}}));
      entries.set(todayKey,{date:todayKey,tasks:state.tasks||{},qaza:state.qaza||{}});
      return [...entries.values()].sort((a,b)=>a.date.localeCompare(b.date));
    }
    function achievementProgress(entry){
      const tasks=entry?.tasks&&typeof entry.tasks==='object'?entry.tasks:{}, qaza=entry?.qaza&&typeof entry.qaza==='object'?entry.qaza:{};
      const nafil=tasks.nafil&&typeof tasks.nafil==='object'?tasks.nafil:(tasks.nafil?Object.fromEntries(NAFIL.map(item=>[item.id,true])):{});
      const basics=['quran','jauchan','kitap','tahajjud','fast','salawat','tasbihat'].reduce((count,id)=>count+(id==='tahajjud'||id==='fast'?tasks[id]==='yes':amount(tasks[id])>0),0);
      return basics+NAFIL.filter(item=>nafil[item.id]).length+(Object.values(qaza).some(value=>amount(value)>0)?1:0);
    }
    function achievementData(){
      const entries=achievementEntries(), totals=emptyTotals(); entries.forEach(entry=>addToTotals(totals,entry));
      let streak=0, longest=0, fullRun=0, longestFull=0, tahajjudRun=0, longestTahajjud=0, previous='';
      entries.forEach(entry=>{
        const date=new Date(`${entry.date}T12:00:00`), yesterday=new Date(date); yesterday.setDate(date.getDate()-1); const expected=`${yesterday.getFullYear()}-${String(yesterday.getMonth()+1).padStart(2,'0')}-${String(yesterday.getDate()).padStart(2,'0')}`;
        const consecutive=previous===expected, progress=achievementProgress(entry), active=progress>0, full=progress>=ChetDeeds.maxFor(entry.date);
        streak=active?(consecutive?streak+1:1):0; longest=Math.max(longest,streak);
        fullRun=full?(consecutive?fullRun+1:1):0; longestFull=Math.max(longestFull,fullRun);
        tahajjudRun=entry?.tasks?.tahajjud==='yes'?(consecutive?tahajjudRun+1:1):0; longestTahajjud=Math.max(longestTahajjud,tahajjudRun);
        previous=entry.date;
      });
      const list=[
        {title:'Ваш ритм', value:longest, goal:3, rule:'Отмечать дела 3 дня подряд', detail:longest>=3?`${longest} дня подряд`:'3 дня подряд', earned:longest>=3, icon:'<svg viewBox="0 0 24 24"><path d="M12 3c1.8 3 5.7 4.9 5.7 9.2A5.7 5.7 0 0 1 6.3 12.2C6.3 8.5 9 6.1 12 3Z"/></svg>'},
        {title:'Полная неделя', value:longestFull, goal:7, rule:'Все дела дня 7 дней подряд', detail:longestFull>=7?'7 полных дней':'7 полных дней подряд', earned:longestFull>=7, icon:'<svg viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="15" rx="3"/><path d="M8 3v4m8-4v4m-8 7 2.5 2.5L16 11"/></svg>'},
        {title:'Путь Корана', value:totals.quran, goal:604, rule:'Прочитать 604 страницы', detail:totals.quran>=604?`${totals.quran} страниц прочитано`:'604 страницы прочитано', earned:totals.quran>=604, icon:'<svg viewBox="0 0 24 24"><path d="M4 5.5c2.7-.8 5.1-.4 8 1.4v12c-2.9-1.8-5.3-2.2-8-1.4zM20 5.5c-2.7-.8-5.1-.4-8 1.4v12c2.9-1.8 5.3-2.2 8-1.4zM12 7v12"/></svg>'},
        {title:'Тахажуд', value:longestTahajjud, goal:40, rule:'Тахаджуд 40 ночей подряд', detail:longestTahajjud>=40?`${longestTahajjud} дней подряд`:'40 дней подряд', earned:longestTahajjud>=40, icon:'<svg viewBox="0 0 24 24"><path d="M19 15.5A7.5 7.5 0 0 1 8.5 5 7.5 7.5 0 1 0 19 15.5Z"/><path d="m17 4 .4 1.1L18.5 5.5l-1.1.4L17 7l-.4-1.1-1.1-.4 1.1-.4z"/></svg>'},
        {title:'Салават', value:totals.salawat, goal:100000, rule:'100 000 салаватов', detail:totals.salawat>=100000?`${new Intl.NumberFormat(appLocale()).format(totals.salawat)} раз`:'100 000 салаватов', earned:totals.salawat>=100000, icon:'<svg viewBox="0 0 24 24"><circle cx="7" cy="7" r="2"/><circle cx="17" cy="7" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="m8.5 8.5 2 2m3 0 2-2m-7 7 2-2m3 0 2 2"/></svg>'},
        {title:'Постоянство', value:totals.days, goal:21, rule:'21 день в пути', detail:totals.days>=21?`${totals.days} дней в пути`:'21 день в пути', earned:totals.days>=21, icon:'<svg viewBox="0 0 24 24"><path d="M6 12h12M12 6v12"/><circle cx="12" cy="12" r="8"/></svg>'}
      ];
      const lucideByTitle={'Ваш ритм':'flame','Полная неделя':'calendarCheck','Путь Корана':'bookOpen','Тахажуд':'moonStar','Салават':'sparkles','Постоянство':'target'};
      return list.map(badge=>lucideByTitle[badge.title]?{...badge,icon:icon(lucideByTitle[badge.title])}:badge);
    }
    function renderAchievementsTeaser(){
      const target=document.querySelector('#achievement-teaser-text'); if(!target) return;
      const next=achievementData().find(badge=>!badge.earned);
      const title=document.querySelector('#achievement-teaser-title'); if(title) title.textContent=next?next.title:(appLocale()==='ky-KG'?'Бардык жетишкендиктер':'Все достижения');
      const number=new Intl.NumberFormat(appLocale()), value=next?Math.min(Number(next.value)||0,next.goal):1, goal=next?next.goal:1;
      target.textContent=next?`${number.format(value)} из ${number.format(goal)}`:'все открыты';
      const fill=document.querySelector('#achievement-teaser-fill'); if(fill) fill.style.width=`${Math.round(value/goal*100)}%`;
      target.closest('.achievement-teaser')?.setAttribute('aria-label',next?`Ближайшее достижение: ${next.title}, ${value} из ${goal}`:'Все достижения открыты');
    }
    /* Profile in the soft glass style: the card with name and numbers, then everything
       else as menu rows. Rows reuse the existing actions through data-profile-menu. */
    function renderSoftProfile(){
      const root=document.querySelector('#soft-profile'); if(!root) return;
      const number=new Intl.NumberFormat(appLocale());
      const people=Array.isArray(state.remoteLeaderboard)?state.remoteLeaderboard:[];
      const me=people.find(person=>person.me);
      const keys=[...(state.remoteEntries||[]).map(entry=>entry?.date),...Object.keys(state.history||{})].filter(Boolean).sort();
      const since=keys[0]?`в пути с ${new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long',year:'numeric'}).format(new Date(`${keys[0]}T12:00:00`)).replace(/^\d+\s*/,'').replace(/\s*г\.?$/,'')}`:'путь только начинается';
      const badges=achievementData(), earned=badges.filter(badge=>badge.earned).length, next=badges.find(badge=>!badge.earned);
      const slots=(state.reminderSettings?.slots||[]).filter(slot=>slot.enabled!==false).map(slot=>slot.time);
      let prayerPlace='Бишкек';
      try { const saved=JSON.parse(localStorage.getItem('chetele-prayer-place')||'null'); prayerPlace=saved?.id==='geo'?(saved.name||'Моё место'):(window.ChetPrayer?.CITIES.find(city=>city.id===saved?.id)||window.ChetPrayer?.CITIES[0])?.name||prayerPlace; } catch {}
      const row=(attrs,iconName,title,sub,value='',extra='')=>`<li><button type="button" class="sp-row ${extra}" ${attrs}><i class="sp-icon">${icon(iconName)}</i><span class="sp-copy"><b>${title}</b><small>${sub}</small></span>${value?`<em>${value}</em>`:''}${extra.includes('danger')?'':`<span class="sp-arrow">${icon('softChevron')}</span>`}</button></li>`;
      root.innerHTML=`<header class="sp-top"><button type="button" class="zg-round" data-view="today-view" aria-label="Вернуться к делам">${icon('softBack')}</button><h2>Профиль</h2><span aria-hidden="true"></span></header>
<section class="sp-card" aria-label="Вы"><div class="sp-me"><span class="sp-avatar">${escapeHtml(Array.from(String(state.name||'').trim())[0]||'·')}</span><span><b>${escapeHtml(state.name||'—')}</b><small>${state.login?`@${escapeHtml(state.login)} · `:''}${since}</small></span></div><div class="sp-stats"><button type="button" data-view="rating-view"><b>${number.format(Number(me?.points??state.score??0))}</b><small>очков</small></button><button type="button" data-view="rating-view"><b>${me?`#${me.place}`:'—'}</b><small>в рейтинге</small></button><button type="button" data-view="stats-view"><b>${number.format(Number(state.bestStreak||state.streak||0))}</b><small>лучшая серия</small></button></div></section>
<ol class="sp-list" aria-label="Меню профиля">${row('data-profile-menu="achievements"','softMedal','Достижения',next?`Следующее: ${next.title}`:'Все получены',`${earned} из ${badges.length}`,'gold')}${row('data-profile-menu="reminders"','softBell','Напоминания',slots.length?slots.join(' · '):'Время не выбрано',state.pushEnabled?'Вкл.':'Выкл.')}${row('data-profile-menu="place"','softClock','Время намазов',`${escapeHtml(prayerPlace)} · ханафитский мазхаб`)}${row('data-profile-menu="settings"','softSettings','Настройки','Тема, язык, крупный текст')}${row('data-profile-menu="about"','softInfo','О приложении','Как считаются дела и очки')}</ol>
<ol class="sp-list" aria-label="Аккаунт">${row('data-profile-menu="rename"','softPencil','Изменить имя','Так вас видят в рейтинге')}${row('data-profile-menu="logout"','softExit','Выйти из аккаунта','Отметки сохранены в кабинете','','danger')}</ol>
<p class="sp-version">Версия приложения ${APP_VERSION}</p>`;
    }
    /* Profile screens from the canvas: achievements, reminders, prayer place, about and
       rename open in one view with a back button to the profile. */
    let profileScreen='achievements', profileScreenNote='', personCard=null;
    /* A participant's card from the rating: points, place, streaks and badges, never their deeds. */
    async function openPersonCard(key){
      personCard={key,status:'loading'}; openProfileScreen('person');
      try {
        const response=await apiFetch(apiUrl(`leaderboard/profile/${key}`)); if(!response.ok) throw new Error('person_failed');
        const data=await response.json(); if(personCard?.key!==key) return; personCard={key,status:'online',data};
      } catch { if(personCard?.key===key) personCard={key,status:'offline'}; }
      if(profileScreen==='person') renderProfileScreen();
    }
    document.addEventListener('keydown',event=>{ if((event.key==='Enter'||event.key===' ')&&event.target.matches?.('[data-person]')){ event.preventDefault(); openPersonCard(event.target.dataset.person); } });
    function openProfileScreen(kind){ profileScreen=kind; profileScreenNote=''; renderProfileScreen(); showView('profile-sub-view'); window.scrollTo(0,0); }
    function renderProfileScreen(){
      const root=document.querySelector('#profile-sub-view'); if(!root) return;
      root.classList.toggle('ps-about',profileScreen==='about');
      const number=new Intl.NumberFormat(appLocale());
      const head=(title,eyebrow='Профиль',back='profile-view')=>`<header class="stats-top zg-top"><button type="button" class="zg-round" data-view="${back}" aria-label="${back==='rating-view'?'Назад в рейтинг':'Назад в профиль'}">${icon('softBack')}</button><div><span class="stats-eyebrow">${eyebrow}</span><h2>${title}</h2></div></header>`;
      const toggle=(attrs,on,label)=>`<span class="st-switch"><input type="checkbox" ${attrs} ${on?'checked':''} aria-label="${label}"><i></i></span>`;
      if(profileScreen==='person'){
        const card=personCard||{}, data=card.data;
        if(card.status!=='online'||!data){ root.innerHTML=`${head(card.status==='offline'?'Не удалось загрузить':'Загрузка…','Рейтинг','rating-view')}<section class="st-card ps-person-empty"><p>${card.status==='offline'?'Проверьте подключение и откройте карточку снова.':'Получаем данные участника…'}</p></section>`; return; }
        const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
        const days=n=>plural(n,'день','дня','дней'), points=n=>plural(n,'очко','очка','очков');
        const since=data.since?new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long',year:'numeric'}).format(new Date(`${data.since}T12:00:00`)).replace(/^\d+\s*/,'').replace(/\s*г\.?$/,''):'';
        const icons=Object.fromEntries(achievementData().map(badge=>[badge.title,badge.icon]));
        const earned=(data.badges||[]).filter(badge=>badge.earned).length;
        const stat=(value,label)=>`<span><b>${value}</b><small>${label}</small></span>`;
        root.innerHTML=`${head(escapeHtml(data.name)+(data.me?' · вы':''),'Рейтинг','rating-view')}<section class="sp-card ps-person"><div class="sp-me"><span class="sp-avatar">${escapeHtml(Array.from(String(data.name||'').trim())[0]||'·')}</span><span><b>${escapeHtml(data.name)}</b><small>${since?`в пути с ${since}`:'путь только начинается'}</small></span></div><div class="sp-stats ps-person-stats">${stat(number.format(data.points?.all||0),points(data.points?.all||0))}${stat(`#${data.place}`,'в рейтинге')}${stat(number.format(data.streak?.best||0),'лучшая серия')}</div></section><section class="st-card ps-person-grid">${stat(number.format(data.points?.week||0),'очков за неделю')}${stat(number.format(data.points?.month||0),'очков за месяц')}${stat(`${number.format(data.streak?.current||0)} ${days(data.streak?.current||0)}`,'подряд сейчас')}${stat(`${number.format(data.streak?.days||0)} ${days(data.streak?.days||0)}`,'в пути')}</section><section class="st-group"><h3>Достижения · ${earned} из ${(data.badges||[]).length}</h3><div class="ps-badges">${(data.badges||[]).map(badge=>`<span class="ps-badge ${badge.earned?'earned':''}"><i>${icons[badge.title]||''}</i><b>${escapeHtml(badge.title)}</b></span>`).join('')}</div></section><p class="ps-note">Личные отметки участника не показываются — только очки, серии и достижения.</p>`;
        return;
      }
      if(profileScreen==='achievements'){
        const badges=achievementData(), earned=badges.filter(badge=>badge.earned).length;
        root.innerHTML=head('Достижения',`Профиль · ${earned} из ${badges.length} получено`)+softAchievementsHtml();
        return;
      }
      if(profileScreen==='reminders'){
        const settings=reminderSettings(), on=!!state.pushEnabled;
        const titles={start:['Дела дня','Начать путь'],continue:['Продолжим путь','Мягко напомнить'],finish:['Итог дня','Подвести итог']};
        const count=settings.slots.filter(slot=>slot.enabled).length;
        /* Prayer notifications: one switch, then each of the five prayers with today's time,
           and whether to notify at the time or a few minutes ahead. */
        const pr=settings.prayer, P=window.ChetPrayer, two=n=>String(n).padStart(2,'0');
        const today=P?Object.fromEntries(P.timesFor(new Date(),{lat:pr.lat,lng:pr.lng}).map(item=>[item.id,`${two(item.time.getHours())}:${two(item.time.getMinutes())}`])):{};
        const prayerNames={fajr:'Фаджр',dhuhr:'Зухр',asr:'Аср',maghrib:'Магриб',isha:'Иша'}, live=on&&pr.enabled;
        const beforeSeg=[[0,'Вовремя'],[5,'За 5 мин'],[10,'За 10'],[15,'За 15']].map(([value,label])=>`<button type="button" role="radio" data-ps-prayer-before="${value}" aria-checked="${pr.before===value}" class="${pr.before===value?'active':''}" ${live?'':'disabled'}>${label}</button>`).join('');
        const prayerGroup=`<section class="st-group ps-prayer ${on?'':'is-off'}"><h3>Время намаза</h3><section class="st-card ps-quiet"><div class="ps-quiet-top"><i class="sp-icon">${icon('softClock')}</i><span class="ps-copy"><b>О времени намаза</b><small>${pr.enabled?`По городу ${escapeHtml(pr.city)} · ${Object.values(pr.prayers).filter(Boolean).length} из 5`:'Уведомление, когда наступает намаз'}</small></span>${toggle(`data-ps-prayer ${on?'':'disabled'}`,pr.enabled,'Уведомления о времени намаза')}</div><div class="ps-prayer-more ${pr.enabled?'':'is-off'}"><div class="glass-seg st-seg" role="radiogroup" aria-label="Когда уведомлять о намазе" style="--seg-n:4">${beforeSeg}</div><ol class="st-list">${Object.keys(prayerNames).map(id=>`<li><div class="st-row ps-slot"><span class="ps-time ps-prayer-at">${today[id]||'—'}</span><span><b>${prayerNames[id]}</b><small>${pr.before?`сигнал в ${today[id]?(()=>{ const [h,m]=today[id].split(':').map(Number), t=h*60+m-pr.before; return `${two(Math.floor((t+1440)%1440/60))}:${two((t+1440)%60)}`; })():'—'}`:'в начале времени'}</small></span>${toggle(`data-ps-prayer-id="${id}" ${live?'':'disabled'}`,pr.prayers[id],prayerNames[id])}</div></li>`).join('')}</ol></div></section></section>`;
        root.innerHTML=`${head('Напоминания')}<section class="st-card ps-master"><i class="sp-icon ps-bell">${icon('softBell')}</i><span class="ps-copy"><b>Напоминания</b><small>${on?`${count} в день · заголовки по времени дня`:'Выключены'}</small></span>${toggle('data-ps-push',on,'Напоминания')}</section><section class="st-group ${on?'':'is-off'}"><h3>Когда напомнить</h3><ol class="st-list">${settings.slots.map(slot=>`<li><div class="st-row ps-slot"><input class="ps-time" type="time" value="${slot.time}" data-ps-slot-time="${slot.id}" aria-label="Время: ${titles[slot.id][0]}" ${on?'':'disabled'}><span><b>${titles[slot.id][0]}</b><small>${titles[slot.id][1]}</small></span>${toggle(`data-ps-slot="${slot.id}" ${on?'':'disabled'}`,slot.enabled,titles[slot.id][0])}</div></li>`).join('')}</ol></section>${prayerGroup}<section class="st-card ps-quiet ${on?'':'is-off'}"><div class="ps-quiet-top"><i class="sp-icon">${icon('softMoon')}</i><span class="ps-copy"><b>Тихие часы</b><small>Ночью напоминания не приходят</small></span>${toggle(`data-ps-quiet ${on?'':'disabled'}`,settings.quiet.enabled,'Тихие часы')}</div><div class="ps-quiet-times ${settings.quiet.enabled?'':'is-off'}"><label><small>с</small><input class="ps-time" type="time" value="${settings.quiet.start}" data-ps-quiet-time="start" ${on&&settings.quiet.enabled?'':'disabled'}></label><label><small>до</small><input class="ps-time" type="time" value="${settings.quiet.end}" data-ps-quiet-time="end" ${on&&settings.quiet.enabled?'':'disabled'}></label></div></section><button type="button" class="ps-primary" data-ps-test ${on?'':'disabled'}>${icon('softSend')}Отправить тестовое</button><p class="ps-note" role="status">${profileScreenNote||'На iPhone напоминания приходят, когда приложение добавлено на экран «Домой».'}</p>`;
        slideControls();
        return;
      }
      if(profileScreen==='place'){
        const P=window.ChetPrayer; let saved=null; try { saved=JSON.parse(localStorage.getItem('chetele-prayer-place')||'null'); } catch {}
        const current=saved?.id==='geo'?(saved.name||'Моё место'):(P?.CITIES.find(city=>city.id===saved?.id)||P?.CITIES[0])?.name||'Бишкек';
        const selected=saved?.id||'bishkek';
        root.innerHTML=`${head('Время намазов')}<section class="st-card"><div class="ps-quiet-top"><i class="sp-icon">${icon('softPin')}</i><span class="ps-copy"><small>Ваш город</small><b class="ps-city">${escapeHtml(current)}</b></span></div><button type="button" class="ps-secondary" data-ps-geo>${icon('softLocate')}Определить по геопозиции</button><small class="ps-hint">${profileScreenNote||'Геопозиция остаётся только на телефоне'}</small></section><section class="st-group"><h3>Города</h3><ol class="st-list ps-cities">${(P?.CITIES||[]).map(city=>`<li><button type="button" class="ps-city-row ${city.id===selected?'on':''}" data-ps-city="${city.id}" aria-pressed="${city.id===selected}">${city.name}<span>${icon('check')}</span></button></li>`).join('')}</ol></section><p class="ps-note">Расчёт: ${P?.METHOD_LABEL||''}. Сверяйтесь с вашей мечетью.</p>`;
        return;
      }
      if(profileScreen==='about'){
        const facts=[['softDeeds','Дела дня','12 дел: Куран, Жавшан, книги, четыре нафиль-намаза, тахаджуд, добровольный пост, салават, зикр и пропущенные намазы.'],['softRating','Очки и рейтинг','Каждое отмеченное дело — 10 очков. Рейтинг бывает за неделю, месяц и всё время.'],['softClock','Время намазов','Считается на телефоне по вашему городу и работает без интернета.'],['softShield','Данные','Отметки хранятся в вашем кабинете. В рейтинге видно только имя и очки.']];
        root.innerHTML=`${head('О приложении')}<section class="st-card ps-app"><img src="img/logo.png" alt="" width="64" height="64"><span class="ps-copy"><b>Chetele</b><small>Путь к сильному таква · версия ${APP_VERSION}</small></span></section>${facts.map(([iconName,title,text])=>`<article class="st-card ps-fact"><i class="sp-icon">${icon(iconName)}</i><span class="ps-copy"><b>${title}</b><p>${text}</p></span></article>`).join('')}<section class="st-card ps-author" aria-label="Об авторе"><div class="ps-author-row"><span class="ps-author-av" aria-hidden="true">БЖ</span><span class="ps-copy"><small>Автор приложения</small><b>Женишов Бекзат</b><small>Telegram · @bekzat_de</small></span></div><a class="ps-author-link" href="https://t.me/bekzat_de" target="_blank" rel="noopener noreferrer">${icon('softSend')}Написать в Telegram</a></section>`;
        return;
      }
      if(profileScreen==='rename'){
        const people=Array.isArray(state.remoteLeaderboard)?state.remoteLeaderboard:[], me=people.find(person=>person.me);
        root.innerHTML=`${head('Изменить имя')}<form class="st-card ps-rename" data-ps-rename novalidate><label for="ps-name">Имя</label><input id="ps-name" class="ps-input" maxlength="24" autocomplete="name" value="${escapeHtml(state.name||'')}" aria-describedby="ps-name-hint ps-name-error"><small id="ps-name-hint">Так вас видят в рейтинге и в событиях. От 2 до 24 символов.</small><p id="ps-name-error" class="ps-error" role="alert">${escapeHtml(profileScreenNote)}</p></form><section class="st-card ps-preview"><span class="ps-preview-avatar">${escapeHtml(Array.from(String(state.name||'').trim())[0]||'·')}</span><span class="ps-copy"><b><span data-ps-preview>${escapeHtml(state.name||'—')}</span> · вы</b><small>так будет в рейтинге</small></span><strong>${number.format(Number(me?.points??state.score??0))}</strong></section><button type="button" class="ps-primary" data-ps-save>Сохранить</button>`;
      }
    }
    async function savePushSlots(update){
      const next=reminderSettings(); update(next); state.reminderSettings=next; save(); renderReminderTimes();
      try { await savePushSettings(); profileScreenNote='Сохранено.'; } catch { profileScreenNote='Сохранено на телефоне, но не отправлено на сервер.'; }
      renderProfileScreen(); renderProfile();
    }
    document.addEventListener('change',async event=>{
      const target=event.target; if(!target.closest('#profile-sub-view')) return;
      if(target.matches('[data-ps-push]')){
        target.disabled=true;
        try {
          if(target.checked){ if(!('Notification' in window)||!('PushManager' in window)) throw new Error('push_unsupported'); const permission=await Notification.requestPermission(); if(permission!=='granted') throw new Error('permission_denied'); const result=await subscribeToPush(); state.pushEnabled=true; save(); setReminderUi(); profileScreenNote=result?.testError?'Подписка включена. Сервер пока не смог отправить тест.':'Напоминания включены.'; }
          else { await disablePush(); profileScreenNote='Напоминания выключены.'; }
        } catch(error){ profileScreenNote=String(error.message||'')==='permission_denied'?'Разрешите уведомления в настройках устройства.':'Не удалось изменить настройку. Проверьте подключение.'; }
        renderProfileScreen(); renderProfile(); return;
      }
      if(target.matches('[data-ps-slot]')) return savePushSlots(next=>{ next.slots.find(slot=>slot.id===target.dataset.psSlot).enabled=target.checked; });
      if(target.matches('[data-ps-slot-time]')&&target.value) return savePushSlots(next=>{ next.slots.find(slot=>slot.id===target.dataset.psSlotTime).time=target.value; });
      if(target.matches('[data-ps-quiet]')) return savePushSlots(next=>{ next.quiet.enabled=target.checked; });
      if(target.matches('[data-ps-prayer]')) return savePushSlots(next=>{ next.prayer.enabled=target.checked; });
      if(target.matches('[data-ps-prayer-id]')) return savePushSlots(next=>{ next.prayer.prayers[target.dataset.psPrayerId]=target.checked; });
      if(target.matches('[data-ps-quiet-time]')&&target.value) return savePushSlots(next=>{ next.quiet[target.dataset.psQuietTime]=target.value; });
    });
    document.addEventListener('input',event=>{ if(event.target.id==='ps-name'){ const preview=document.querySelector('[data-ps-preview]'); if(preview) preview.textContent=event.target.value.trim()||'—'; } });
    document.addEventListener('submit',event=>{ if(event.target.matches('[data-ps-rename]')){ event.preventDefault(); document.querySelector('[data-ps-save]')?.click(); } });
    document.addEventListener('click',async event=>{
      const target=event.target; if(!target.closest('#profile-sub-view')) return;
      const prayerBefore=target.closest('[data-ps-prayer-before]'); if(prayerBefore){ if(!prayerBefore.disabled&&!prayerBefore.classList.contains('active')) savePushSlots(next=>{ next.prayer.before=Number(prayerBefore.dataset.psPrayerBefore); }); return; }
      if(target.closest('[data-ps-test]')){
        const button=target.closest('[data-ps-test]'); button.disabled=true; profileScreenNote='Отправляем пример…'; document.querySelector('#profile-sub-view .ps-note').textContent=profileScreenNote;
        try { const response=await apiFetch(apiUrl('push/test'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,login:state.login})}); if(!response.ok) throw new Error(); profileScreenNote='Тест отправлен ✦'; }
        catch { profileScreenNote='Не удалось отправить пример. Проверьте подключение.'; }
        renderProfileScreen(); return;
      }
      const city=target.closest('[data-ps-city]');
      if(city){ try { localStorage.setItem('chetele-prayer-place',JSON.stringify({id:city.dataset.psCity})); } catch {} profileScreenNote=''; window.chetelePlaceChanged?.(); window.chetelePaintPrayer?.(); renderProfileScreen(); renderSoftProfile(); return; }
      if(target.closest('[data-ps-geo]')){
        const P=window.ChetPrayer;
        if(!navigator.geolocation||!P){ profileScreenNote='Это устройство не умеет определять место. Выберите город из списка.'; renderProfileScreen(); return; }
        document.querySelector('#profile-sub-view .ps-hint').textContent='Определяем…';
        navigator.geolocation.getCurrentPosition(position=>{
          // The nearest listed city names the place; coordinates are rounded to about a kilometre.
          const lat=Math.round(position.coords.latitude*100)/100, lng=Math.round(position.coords.longitude*100)/100;
          const distance=item=>Math.hypot(item.lat-lat,(item.lng-lng)*Math.cos(lat*Math.PI/180));
          const nearest=P.CITIES.reduce((best,item)=>distance(item)<distance(best)?item:best);
          try { localStorage.setItem('chetele-prayer-place',JSON.stringify({id:'geo',lat,lng,name:distance(nearest)<0.35?nearest.name:'Моё место'})); } catch {} window.chetelePlaceChanged?.();
          profileScreenNote=''; window.chetelePaintPrayer?.(); renderProfileScreen(); renderSoftProfile();
        },()=>{ profileScreenNote='Не удалось определить место. Выберите город из списка.'; renderProfileScreen(); },{timeout:15000});
        return;
      }
      if(target.closest('[data-ps-save]')){
        const button=target.closest('[data-ps-save]'), input=document.querySelector('#ps-name'); button.disabled=true; button.textContent='Сохраняем…';
        const error=await saveName(input.value);
        // On an error the typed name stays in the field.
        if(error){ document.querySelector('#ps-name-error').textContent=error; button.disabled=false; button.textContent='Сохранить'; input.focus(); return; }
        profileScreenNote=''; renderProfile(); showView('profile-view'); window.scrollTo(0,0);
      }
    });
    /* Achievements sheet: the summary ring and a card per badge with its progress. */
    function softAchievementsHtml(){
      const number=new Intl.NumberFormat(appLocale()), badges=achievementData(), earned=badges.filter(badge=>badge.earned).length;
      const next=badges.filter(badge=>!badge.earned).sort((a,b)=>b.value/b.goal-a.value/a.goal)[0];
      const ordered=[...badges.filter(badge=>badge.earned),...badges.filter(badge=>!badge.earned)];
      return `<section class="sp-ach-sum"><span class="zg-ring"><svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="27"/><circle class="zg-ring-value" cx="32" cy="32" r="27" stroke-dasharray="${Math.round(earned/badges.length*169.6)} 170"/></svg><b>${earned}/${badges.length}</b></span><span><b>${next?`Ближе всего: ${next.title}`:'Все достижения получены'}</b><small>${next?`${number.format(Math.min(next.value,next.goal))} из ${number.format(next.goal)}`:'Масха Аллах!'}</small></span></section><div class="sp-ach-list">${ordered.map(badge=>`<article class="sp-ach ${badge.earned?'earned':''}"><div><i>${badge.icon}</i><span><b>${escapeHtml(badge.title)}</b><small>${escapeHtml(badge.rule)}</small></span>${badge.earned?'<em>получено</em>':''}</div><span class="sp-ach-progress"><span class="zg-track" aria-hidden="true"><i style="width:${Math.min(100,Math.round(badge.value/badge.goal*100))}%"></i></span><b>${badge.earned?'выполнено':`${number.format(badge.value)} / ${number.format(badge.goal)}`}</b></span></article>`).join('')}</div>`;
    }
    function renderProfile(){
      const avatar=document.querySelector('#profile-avatar'), name=document.querySelector('#profile-name'), score=document.querySelector('#profile-score'), rank=document.querySelector('#profile-rank');
      if(!avatar||!name||!score||!rank) return;
      const people=Array.isArray(state.remoteLeaderboard)?state.remoteLeaderboard:[];
      const position=people.findIndex(person=>person.me), me=position>=0?people[position]:null;
      name.textContent=state.name||'—'; score.textContent=Number(me?.points??state.score??0); rank.textContent=me?`#${me.place||position+1}`:'—';
      avatar.textContent=Array.from(String(state.name||'').trim())[0]||'·';
      const bestLabel=document.querySelector('#profile-best'); if(bestLabel) bestLabel.textContent=Number(state.bestStreak||state.streak||0);
      const badgesRoot=document.querySelector('#profile-badges');
      if(badgesRoot){ const list=achievementData(), earnedCount=list.filter(badge=>badge.earned).length; const ordered=[...list.filter(badge=>badge.earned),...list.filter(badge=>!badge.earned)]; badgesRoot.innerHTML=`<div class="profile-badges-head"><h2>Достижения</h2><span>${earnedCount} из ${list.length}</span></div><div class="profile-badges-grid">${ordered.map(badge=>`<article class="profile-badge ${badge.earned?'earned':''}" title="${escapeHtml(badge.detail)}"><i>${badge.icon}</i><b>${escapeHtml(badge.title)}</b></article>`).join('')}</div>`; }
      const reminderHint=document.querySelector('[data-profile-menu="reminders"] .profile-menu-value'); if(reminderHint) reminderHint.textContent=state.pushEnabled?'Включены':'Выключены';
      const versionLine=document.querySelector('#profile-version'); if(versionLine) versionLine.textContent=`Версия приложения ${APP_VERSION}`;
      let version=document.querySelector('#app-version');
      if(!version){
        version=document.createElement('p'); version.id='app-version'; version.className='app-version';
        document.querySelector('#profile-view .reminder-card')?.insertAdjacentElement('afterend',version);
      }
      version.textContent=`Версия приложения ${APP_VERSION}`;
      let logout=document.querySelector('#logout-button');
      if(!logout){
        logout=document.createElement('button'); logout.id='logout-button'; logout.className='logout-button'; logout.type='button'; logout.textContent='Выйти из аккаунта';
        version.insertAdjacentElement('afterend',logout);
      }
      let achievements=document.querySelector('#profile-achievements');
      if(!achievements){
        achievements=document.createElement('section'); achievements.id='profile-achievements'; achievements.className='achievements';
        document.querySelector('#profile-view .profile-card')?.insertAdjacentElement('afterend',achievements);
      }
      const badges=achievementData(), earned=badges.filter(badge=>badge.earned).length;
      achievements.innerHTML=`<div class="achievements-head"><h2>Достижения</h2><span>${earned} из ${badges.length}</span></div><p class="achievements-help">Выполняйте условия в карточках, чтобы открыть достижения.</p><div class="achievements-grid">${badges.map(badge=>`<article class="achievement ${badge.earned?'earned':''}"><i>${badge.icon}</i><strong>${badge.title}</strong><small>${badge.detail}</small></article>`).join('')}</div>`;
      renderSoftProfile();
    }
    let boardShownPeriod=null;
    function ratingPeriod(){ return ['month','all'].includes(state.ratingPeriod)?state.ratingPeriod:'week'; }
    /* What the board counts: points, or one deed with its own unit. */
    const RATING_METRICS={points:{label:'Очки',ky:'Упай',unit:['очко','очка','очков'],short:'очк.'},salawat:{label:'Салават',ky:'Салават',unit:['раз','раза','раз'],short:'раз'},zikr:{label:'Зикр',ky:'Зикир',unit:['раз','раза','раз'],short:'раз'},quran:{label:'Куран',ky:'Куран',unit:['страница','страницы','страниц'],short:'стр.'},tahajjud:{label:'Тахаджуд',ky:'Тахажжуд',unit:['ночь','ночи','ночей'],short:'ноч.'},fast:{label:'Пост',ky:'Орозо',unit:['день','дня','дней'],short:'дн.'},nafil:{label:'Нафиль',ky:'Напил',unit:['намаз','намаза','намазов'],short:'нам.'}};
    function ratingMetric(){ return RATING_METRICS[state.ratingMetric]?state.ratingMetric:'points'; }
    function renderBoard(){
      const period=ratingPeriod(), metric=ratingMetric(), key=boardKey(period,metric), source=boardRows(period,metric), status=boardStatus[key], spec=RATING_METRICS[metric];
      document.querySelectorAll('[data-rating-period]').forEach(button=>{ const on=button.dataset.ratingPeriod===period; button.classList.toggle('active',on); button.setAttribute('aria-checked',String(on)); });
      document.querySelectorAll('#rating-view [data-icon]').forEach(slot=>{ if(!slot.firstChild) slot.innerHTML=icon(slot.dataset.icon); });
      const locale=appLocale(), ky=locale==='ky-KG', number=new Intl.NumberFormat(locale);
      const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
      const points=n=>metric==='points'&&ky?'упай':plural(n,...spec.unit), short=metric==='points'&&ky?'уп.':spec.short;
      const chips=document.querySelector('#rating-metrics');
      if(chips) chips.innerHTML=Object.entries(RATING_METRICS).map(([id,item])=>`<button type="button" role="radio" aria-checked="${id===metric}" class="${id===metric?'active':''}" data-rating-metric="${id}">${ky?item.ky:item.label}</button>`).join('');
      const people=Array.isArray(source) ? source.map((person,index)=>({name:person.name,points:Number(person.points)||0,me:person.me===true,place:Number(person.place)||index+1,move:Number(person.move)||0,key:/^[0-9a-f]{16}$/.test(person.key||'')?person.key:''})) : [];
      const open=p=>p.key?` data-person="${p.key}" role="button" tabindex="0"`:'';
      const leaderboard=document.querySelector('#leaderboard'), hint=document.querySelector('#rating-hint'), label=document.querySelector('#rating-period-label');
      const initial=name=>escapeHtml(Array.from(String(name).trim())[0]||'·');
      const today=new Date(`${todayKey}T12:00:00`), from=new Date(today); from.setDate(from.getDate()-6);
      if(period==='week'){ const range=new Intl.DateTimeFormat(locale,{day:'numeric',month:from.getMonth()===today.getMonth()?'long':'short'}); label.textContent=(range.formatRange?range.formatRange(from,today):`${range.format(from)} – ${range.format(today)}`).replace(/\./g,''); }
      else if(period==='month'){ const month=new Intl.DateTimeFormat(locale,{month:'long'}).format(today); label.textContent=month.charAt(0).toUpperCase()+month.slice(1); }
      else label.textContent=ky?'Бардык убакыт':'За всё время';
      const moveText=move=>{ const n=Math.abs(move); return !move?(ky?'өзгөргөн жок':'без изменений'):`${move>0?'↑':'↓'} ${n} ${ky?'орун':plural(n,'позиция','позиции','позиций')}`; };
      const moveClass=move=>move>0?' up':move<0?' down':'';
      const crown='<svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.5 8.5 8 12l4-6.5 4 6.5 4.5-3.5L19 18H5zM5 21h14"/></svg>';
      if(status==='offline' && !people.length){
        leaderboard.innerHTML=`<div class="rt-empty"><b>${ky?'Рейтинг жеткиликсиз':'Рейтинг недоступен'}</b><span>${ky?'Упайларыңыз жоголбойт — интернет пайда болгондо рейтинг жаңыланат.':'Ваши очки не потеряются — рейтинг обновится, когда появится интернет.'}</span></div>`;
      } else if(!Array.isArray(source)){
        const slot=height=>`<div class="rt-slot"><span class="rt-avatar sk-light"></span><span class="sk-line sk-light"></span><div class="rt-step sk-light" style="height:${height}px"></div></div>`;
        leaderboard.innerHTML=`<section class="rt-podium" aria-busy="true">${slot(110)}${slot(150)}${slot(90)}</section><ol class="rt-list">${[120,90,140].map(width=>`<li class="rt-row"><span class="sk-line" style="width:22px"></span><span class="rt-row-avatar sk"></span><span class="sk-line" style="width:${width}px"></span></li>`).join('')}</ol>`;
      } else if(!people.length){
        leaderboard.innerHTML=`<div class="rt-empty"><b>${ky?'Рейтинг азырынча бош':'Рейтинг пока пуст'}</b><span>${ky?'Биринчи ишти белгилеңиз — ушул жерде пайда болосуз.':'Отметьте первое дело — и вы появитесь здесь.'}</span></div>`;
      } else {
        const top=people.filter(person=>person.place<=3).sort((a,b)=>a.place-b.place), max=Math.max(1,top[0]?.points||0);
        const podiumSlot=p=>{ const first=p.place===1; return `<div class="rt-slot${first?' first':''}"${open(p)} aria-label="${p.place} ${ky?'орун':'место'}: ${escapeHtml(p.name)}, ${number.format(p.points)} ${points(p.points)}"><span class="rt-crown">${first?crown:''}</span><span class="rt-avatar">${initial(p.name)}</span><span class="rt-name"><b>${escapeHtml(p.name)}${p.me?(ky?' · сиз':' · вы'):''}</b><small>${number.format(p.points)} ${short}</small></span><div class="rt-step" style="height:${Math.round(60+70*p.points/max)+(first?20:0)}px"><span>${p.place}</span></div></div>`; };
        const podium=`<section class="rt-podium" aria-label="${ky?'Алдыңкы үчөө':'Тройка лидеров'}">${[top[1],top[0],top[2]].filter(Boolean).map(podiumSlot).join('')}</section>`;
        // With shared places the one to catch is the nearest person with more.
        const me=people.find(person=>person.me), above=me?[...people].reverse().find(person=>person.points>me.points):null;
        let meCard=isGuest()?`<section class="rt-me guest-seat" aria-label="Ваше место"><div class="rt-me-top"><span class="rt-me-place">#?</span><span class="rt-me-copy"><b>Здесь будет ваше место</b><small>Каждое дело — 10 очков. Гостя в рейтинге не видно.</small></span><span class="rt-me-points"><b>0</b><small>очков</small></span></div><div class="guest-cta-actions"><button type="button" class="guest-primary" data-auth="signup">Занять место</button><button type="button" class="guest-secondary" data-auth="login">Войти</button></div></section>`:'';
        if(me){
          const gap=above?(metric==='points'?Math.max(10,above.points-me.points+10):above.points-me.points+1):0;
          const goal=me.place===1?(ky?'Сиз биринчи орундасыз — ушундай уланта бериңиз!':'Вы на первом месте — так держать!'):!me.points&&period==='week'?(ky?'Рейтингге кирүү үчүн ишти белгилеңиз':'Отметьте дело, чтобы попасть в рейтинг недели'):above?`${ky?`${above.place}-орунга чейин`:`До ${above.place} места`} — ${number.format(gap)} ${points(gap)}`:'';
          const pct=me.place===1?100:above&&above.points?Math.min(100,Math.round(me.points/above.points*100)):0;
          meCard=`<section class="rt-me" aria-label="${ky?'Сиздин ордуңуз':'Ваше место'}"><div class="rt-me-top"><span class="rt-me-place">#${me.place}</span><span class="rt-me-copy"><b>${escapeHtml(me.name)} · ${ky?'сиз':'вы'}</b><small>${moveText(me.move)}${period==='week'?(ky?' аптада':' за неделю'):period==='month'?(ky?' айда':' за месяц'):''}</small></span><span class="rt-me-points"><b>${number.format(me.points)}</b><small>${points(me.points)}</small></span></div><div class="rt-me-goal"><span class="rt-track" aria-hidden="true"><i style="width:${pct}%"></i></span><span class="rt-me-foot"><span>${goal}</span>${above&&me.place>1&&metric==='points'?`<b>≈ ${number.format(Math.ceil(gap/10))} ${ky?'иш':plural(Math.ceil(gap/10),'дело','дела','дел')}</b>`:''}</span></div></section>`;
        }
        const rest=people.filter(person=>person.place>3);
        const total=Number(state.ratingTotal?.[key]??state.ratingTotal?.[period])||0;
        const list=rest.length?`<section class="rt-all"><div class="stats-section-head"><h3>${ky?'Бардык катышуучулар':'Все участники'}</h3>${total?`<span>${number.format(total)} ${ky?'адам':plural(total,'человек','человека','человек')}</span>`:''}</div><ol class="rt-list">${rest.map(p=>`<li class="rt-row${p.me?' me':''}" data-name="${escapeHtml(p.name)}"${open(p)} aria-label="${p.place} ${ky?'орун':'место'}: ${escapeHtml(p.name)}, ${number.format(p.points)} ${points(p.points)}, ${moveText(p.move)}"><span class="rt-row-place">${p.place}</span><span class="rt-row-avatar">${initial(p.name)}</span><span class="rt-row-copy"><b>${escapeHtml(p.name)}${p.me?(ky?' · сиз':' · вы'):''}</b><small class="${moveClass(p.move).trim()}">${moveText(p.move)}</small></span><span class="rt-row-points">${number.format(p.points)}</span></li>`).join('')}</ol></section>`:'';
        // Rows that changed place slide to it instead of jumping (same period only).
        const before=boardShownPeriod===key?new Map([...leaderboard.querySelectorAll('.rt-row[data-name]')].map(row=>[row.dataset.name,row.getBoundingClientRect().top])):new Map();
        const html=podium+meCard+list;
        // The same data again (a refresh right after opening) leaves the board, and its animation, alone.
        if(!(html===leaderboard.shownHtml&&leaderboard.firstElementChild===leaderboard.shownNode)){
          const shownPodium=leaderboard.querySelector('.rt-podium:not([aria-busy])'), podiumFrom=boardShownPeriod===key&&shownPodium?new Map([...shownPodium.querySelectorAll('.rt-step')].map(step=>[Number(step.textContent.trim()),step.offsetHeight])):null;
          leaderboard.innerHTML=html; leaderboard.shownHtml=html; leaderboard.shownNode=leaderboard.firstElementChild;
          const since=performance.now()-podiumIntroAt;
          if(podiumFrom&&since<1700) growPodium(null,since); else growPodium(podiumFrom);
        }
        if(before.size&&!motionOff()) leaderboard.querySelectorAll('.rt-row[data-name]').forEach(row=>{ const top=before.get(row.dataset.name); const shift=top===undefined?0:top-row.getBoundingClientRect().top; if(Math.abs(shift)>1) row.animate([{transform:`translateY(${shift}px)`},{transform:'translateY(0)'}],{duration:360,easing:'cubic-bezier(.2,.8,.2,1)'}); });
      }
      boardShownPeriod=key;
      if(status==='online'||(status==='loading'&&people.length)) hint.textContent=metric!=='points'?(ky?'Күндүн иштериндеги белгилер боюнча эсептелет. Катышуучуну басып, анын карточкасын ачыңыз.':'Считается по отметкам в делах дня. Нажмите на участника, чтобы открыть его карточку.'):(ky?'Упайлар белгиленген иштер үчүн эсептелет. Катышуучуну басып, анын карточкасын ачыңыз.':'Очки считаются за отмеченные дела. Нажмите на участника, чтобы открыть его карточку.');
      else if(status==='offline') hint.textContent=people.length?(ky?'Сервер менен байланыш жок. Интернет пайда болгондо рейтинг жаңыланат.':'Нет связи с сервером. Рейтинг обновится, когда появится интернет.'):'';
      else hint.textContent=ky?'Рейтинг жүктөлүүдө…':'Загрузка рейтинга…';
    }
    document.addEventListener('click',event=>{
      const chip=event.target.closest('[data-rating-metric]');
      if(chip){ if(chip.dataset.ratingMetric!==ratingMetric()){ state.ratingMetric=chip.dataset.ratingMetric; save(); renderBoard(); loadLeaderboard(ratingPeriod(),ratingMetric()); } return; }
      const person=event.target.closest('[data-person]');
      if(person){ openPersonCard(person.dataset.person); return; }
      const button=event.target.closest('[data-rating-period]'); if(!button||button.dataset.ratingPeriod===ratingPeriod()) return;
      state.ratingPeriod=button.dataset.ratingPeriod; save(); loadLeaderboard(ratingPeriod(),ratingMetric());
    });
    /* Events page in the soft glass style: the first running common goal as a hero card
       with progress, my numbers and leaders; personal paths and the rest as glass cards;
       upcoming events under «Скоро». Joining keeps data-event-join for the handlers below. */
    function eventsEmptyCard(title,text,action){
      return `<section class="evs-card evs-empty"><i class="evs-icon">${icon('softEvents')}</i><h3>${title}</h3><p>${text}</p>${action}</section>`;
    }
    function renderEvents(events=[]){
      const root=document.querySelector('#events-list'); if(!root) return;
      root.removeAttribute('aria-busy');
      document.querySelectorAll('#events-view [data-icon]').forEach(slot=>{ if(!slot.firstChild) slot.innerHTML=icon(slot.dataset.icon); });
      const number=new Intl.NumberFormat(appLocale()), units={salawat:'салаватов',dhikr:'зикров',quran_pages:'стр.',book_pages:'стр.',tahajjud_count:'ночей'}, unitOf=event=>event.metric==='custom'?(event.custom_unit||'раз'):(units[event.metric]||'раз');
      const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
      const shortDate=key=>new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long'}).format(new Date(`${key}T12:00:00`));
      const dayDiff=(from,to)=>Math.round((new Date(`${to}T12:00:00`)-new Date(`${from}T12:00:00`))/86400000);
      const metricIcon={salawat:'softSalawat',dhikr:'softZikr',quran_pages:'softQuran',book_pages:'softKitap',tahajjud_count:'softTahajjud',custom:'softStar'};
      const live=events.filter(event=>event.starts_on<=todayKey), soon=events.filter(event=>event.starts_on>todayKey);
      const sub=document.querySelector('#events-sub');
      if(sub) sub.textContent=live.length?`${live.length} ${plural(live.length,'идёт','идут','идут')} сейчас`:soon.length?'Скоро начнутся':'Скоро новое событие';
      if(!events.length){ root.innerHTML=eventsEmptyCard('Скоро новое событие','Сейчас нет активных событий. Мы сообщим, когда начнётся новое.','<button type="button" class="ps-primary" data-view="today-view">Вернуться к делам</button>')+'<section class="evs-card evs-about"><h3>Что такое события</h3><p><b>Общая цель</b> — вся община вместе, например 20 000 салаватов за месяц. <b>Личный путь</b> — своя цель в своём темпе, например 40 ночей тахаджуда.</p></section><p class="evs-note">Отметки засчитываются в событие с момента участия.</p>'; return; }
      const pills=event=>{
        const personal=event.mode==='personal_path', upcoming=event.starts_on>todayKey, left=dayDiff(todayKey,event.ends_on);
        const status=upcoming?`<span class="evs-pill soon">С ${shortDate(event.starts_on)}</span>`:`<span class="evs-pill live"><i></i>${left<=0?'Последний день':`Идёт · ещё ${left} ${plural(left,'день','дня','дней')}`}</span>`;
        return `<div class="evs-pills">${status}<span class="evs-pill">${personal?'Личный путь':'Общая цель'}</span>${event.paused?'<span class="evs-pill paused">На паузе</span>':''}${event.pending?'<span class="evs-pill pending">Ждёт подтверждения</span>':event.rejected?'<span class="evs-pill rejected">Заявка отклонена</span>':event.joined?'<span class="evs-pill joined">Вы участвуете</span>':''}</div>`;
      };
      const join=event=>event.joined||event.paused?'':isGuest()?`<button type="button" class="ps-primary evs-join" data-auth="signup">${guestSvg('lock')}Участвовать</button>`:`<button type="button" class="ps-primary evs-join" data-event-join="${event.id}" data-event-mode="${event.mode==='personal_path'?'personal_path':'common_goal'}" data-event-metric="${escapeHtml(event.metric||'')}" data-event-unit="${escapeHtml(unitOf(event))}" data-event-step="${Number(event.custom_step)||1}" data-event-title="${escapeHtml(event.title)}" data-event-days="${dayDiff(event.starts_on,event.ends_on)+1}">Участвовать</button>`;
      const contribute=event=>event.canContribute?`<button type="button" class="ps-primary evs-join evs-contribute" data-event-contribute="${event.id}">${icon('softStar')}Добавить вклад${event.pending?' · ждёт подтверждения':''}</button>`:'';
      const copy=(event,big)=>`<div class="evs-copy">${big?'':`<i class="evs-icon">${icon(metricIcon[event.metric]||'softEvents')}</i>`}<span><h3>${escapeHtml(event.title)}</h3><p>${escapeHtml(event.description||(event.mode==='personal_path'?'Личный путь в своём темпе.':'Общая цель сообщества.'))}</p></span></div>`;
      const progress=(value,target,unit)=>{ const percent=Math.min(100,Math.round(value/Math.max(1,target)*100)); return `<div class="evs-progress"><div><b>${number.format(value)}</b><span>из ${number.format(target)} ${unit}</span><strong>${percent}%</strong></div><span class="zg-track" aria-hidden="true"><i style="width:${percent}%"></i></span></div>`; };
      const participants=n=>`${number.format(n)} ${plural(n,'участник','участника','участников')}`;
      const hero=live.find(event=>event.mode!=='personal_path');
      const heroCard=event=>{
        const unit=unitOf(event), stat=(value,label)=>`<span><b>${value}</b><small>${label}</small></span>`;
        const leaders=Array.isArray(event.leaders)&&event.leaders.length?`<div class="evs-leaders"><h4>Лидеры</h4>${event.leaders.map((leader,index)=>`<div class="evs-leader"><span class="evs-place">${index+1}</span><span class="rt-row-avatar">${escapeHtml(Array.from(String(leader.name||'').trim())[0]||'·')}</span><b>${escapeHtml(leader.name)}</b><strong>${number.format(leader.progress)}</strong></div>`).join('')}</div>`:'';
        return `<section class="evs-hero" aria-label="${escapeHtml(event.title)}"><div class="evs-hero-bar">${pills(event)}</div><article class="evs-hero-card">${copy(event,true)}${progress(Number(event.totalProgress||0),Number(event.goal||0),unit)}<div class="evs-stats">${stat(event.joined?number.format(event.myProgress||0):'—','мой вклад')}${stat(event.joined&&event.myRank?`#${event.myRank}`:'—','моё место')}${stat(number.format(event.participants||0),plural(Number(event.participants||0),'участник','участника','участников'))}</div>${leaders}${join(event)}${contribute(event)}</article></section>`;
      };
      const card=event=>{
        const personal=event.mode==='personal_path', upcoming=event.starts_on>todayKey, left=Math.max(0,dayDiff(todayKey,event.ends_on)), unit=unitOf(event);
        let body='';
        if(personal&&event.joined) body=`${progress(Number(event.myProgress||0),Number(event.personalTarget||0),unit)}<p class="evs-meta">${upcoming?`Старт ${shortDate(event.starts_on)}`:`Осталось ${left} ${plural(left,'день','дня','дней')}`} · ${participants(Number(event.participants||0))}</p>`;
        else if(!personal&&!upcoming) body=progress(Number(event.totalProgress||0),Number(event.goal||0),unit);
        return `<article class="evs-card">${pills(event)}${copy(event,false)}${body}${contribute(event)}${event.joined?'':`<div class="evs-foot"><span>${participants(Number(event.participants||0))}</span>${join(event)}</div>`}</article>`;
      };
      const others=live.filter(event=>event!==hero);
      root.innerHTML=`${isGuest()?guestCta('Смотреть можно, участвовать — с профилем','Ваши салаваты и дела будут засчитываться в события автоматически.'):''}${hero?heroCard(hero):''}${others.map(card).join('')}${soon.length?`<section class="evs-soon"><h3 class="evs-heading">Скоро</h3>${soon.map(card).join('')}</section>`:''}<p class="evs-note">Отметки засчитываются в событие с момента участия.${events.some(event=>event.metric==='custom')?' В событиях со своим счётом вклад добавляется кнопкой в карточке.':''}</p>`;
    }
    async function fetchEvents(){
      const root=document.querySelector('#events-list'); if(!root) return;
      try { const response=await apiFetch(apiUrl('events')); if(!response.ok) throw new Error('events_failed'); const events=await response.json(); state.events=Array.isArray(events)?events:[]; renderEvents(state.events); }
      catch { if(Array.isArray(state.events)&&state.events.length) return; root.removeAttribute('aria-busy'); root.innerHTML=eventsEmptyCard('События пока недоступны','Проверьте подключение к интернету и попробуйте снова.','<button type="button" class="ev-button" data-events-retry>Повторить</button>'); }
    }
    document.addEventListener('click',event=>{ if(event.target.closest('[data-events-retry]')) fetchEvents(); });
    let personalGoalButton=null;
    async function joinEvent(button,personalTarget=null){ const payload={userId:state.userId,login:state.login}; if(personalTarget!==null) payload.personalTarget=personalTarget; button.disabled=true; button.textContent='Присоединяем…'; try { const response=await apiFetch(apiUrl(`events/${button.dataset.eventJoin}/join`),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}); if(!response.ok) throw new Error(); await fetchEvents(); } catch { button.disabled=false; button.textContent='Не удалось вступить'; } }
    /* Personal goal sheet: a stepper and three ready-made goals that fit the event.
       Nights are limited by the length of the event; counts get round numbers. */
    const goalPlural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
    const GOAL_KINDS={
      tahajjud_count:{unit:n=>goalPlural(n,'ночь','ночи','ночей'),step:1},
      quran_pages:{unit:n=>goalPlural(n,'страница','страницы','страниц'),step:10,presets:[30,100,604]},
      book_pages:{unit:n=>goalPlural(n,'страница','страницы','страниц'),step:10,presets:[100,300,1000]},
      salawat:{unit:n=>goalPlural(n,'салават','салавата','салаватов'),step:100,presets:[1000,5000,10000]},
      dhikr:{unit:n=>goalPlural(n,'зикр','зикра','зикров'),step:100,presets:[1000,5000,10000]}
    };
    let goalKind=null, goalLabels=[], goalMode='join', contributeEventId=null;
    /* The same sheet adds an amount to a custom event: number, quick steps, and the last
       entries with a way to remove a mistaken one. */
    function setupContribute(eventId){
      const event=(state.events||[]).find(item=>String(item.id)===String(eventId)); if(!event) return false;
      const step=Math.max(1,Number(event.custom_step)||1), unit=event.custom_unit||'раз';
      goalMode='contribute'; contributeEventId=event.id;
      goalKind={unit:()=>unit,step,presets:[step,step*5,step*10],max:99999}; goalLabels=[`+${step}`,`+${step*5}`,`+${step*10}`].map(()=>unit);
      document.querySelector('#personal-goal-title').textContent='Добавить вклад';
      document.querySelector('#personal-goal-event').textContent=`${event.title} · у вас ${Number(event.myProgress||0).toLocaleString('ru-RU')} ${unit}`;
      document.querySelector('#personal-goal-note').textContent=event.pending?'Вклад сохранится и засчитается после подтверждения участия':'Вклад засчитывается в событие сразу';
      document.querySelector('#personal-goal-input').value=step;
      paintContributions(event); paintPersonalGoal(); return true;
    }
    function paintContributions(event){
      const box=document.querySelector('#personal-goal-entries'), entries=event?.myEntries||[], unit=event?.custom_unit||'раз';
      box.hidden=!entries.length;
      box.innerHTML=entries.length?`<h3>Ваши записи</h3>${entries.map(entry=>`<div class="pg-entry"><span><b>${Number(entry.amount).toLocaleString('ru-RU')} ${escapeHtml(unit)}</b><small>${new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long'}).format(new Date(`${String(entry.date).slice(0,10)}T12:00:00`))}</small></span><button type="button" data-contrib-delete="${entry.id}" aria-label="Удалить запись"><svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M10 7V4h4v3M7 7l1 13h8l1-13"/></svg></button></div>`).join('')}`:'';
    }
    async function deleteContribution(button){
      button.disabled=true;
      try { const response=await apiFetch(apiUrl(`events/${contributeEventId}/contributions/${button.dataset.contribDelete}`),{method:'DELETE'}); if(!response.ok) throw new Error(); await fetchEvents(); setupContribute(contributeEventId); }
      catch { button.disabled=false; document.querySelector('#personal-goal-error').textContent='Не удалось удалить запись.'; }
    }
    function resetGoalSheet(){
      goalMode='join'; contributeEventId=null;
      document.querySelector('#personal-goal-title').textContent='Ваша цель';
      document.querySelector('#personal-goal-note').textContent='Цель можно изменить позже в карточке события';
      paintContributions(null);
    }
    function setupPersonalGoal(button){
      resetGoalSheet();
      const metric=button.dataset.eventMetric, days=Math.max(1,Number(button.dataset.eventDays)||30);
      const customStep=Math.max(1,Number(button.dataset.eventStep)||1), customUnit=button.dataset.eventUnit||'раз';
      const kind=metric==='custom'?{unit:()=>customUnit,step:customStep,presets:[customStep*10,customStep*50,customStep*100]}:GOAL_KINDS[metric]||{unit:n=>goalPlural(n,'раз','раза','раз'),step:1,presets:[10,50,100]};
      const presets=kind.presets||[...new Set([Math.max(1,Math.round(days/3)),Math.max(1,Math.round(days*2/3)),days])];
      goalKind={...kind,presets,max:metric==='tahajjud_count'?days:999999};
      document.querySelector('#personal-goal-event').textContent=`${button.dataset.eventTitle||''} · событие длится ${days} ${goalPlural(days,'день','дня','дней')}`;
      goalLabels=metric==='tahajjud_count'?['для начала','через ночь','каждую ночь']:['для начала','хороший темп','большая цель'];
      document.querySelector('#personal-goal-input').value=presets[Math.min(1,presets.length-1)];
      paintPersonalGoal();
    }
    function paintPersonalGoal(){
      if(!goalKind) return;
      const value=Number(document.querySelector('#personal-goal-input').value)||0, format=n=>n.toLocaleString('ru-RU');
      document.querySelector('#personal-goal-unit').textContent=goalKind.unit(value);
      document.querySelector('#personal-goal-presets').innerHTML=goalKind.presets.map((n,index)=>`<button type="button" class="${n===value?'active':''}" data-goal-preset="${n}" aria-pressed="${n===value}"><b>${format(n)}</b><small>${goalLabels[index]||goalKind.unit(n)}</small></button>`).join('');
      const verb=goalMode==='contribute'?'Добавить':'Участвовать';
      document.querySelector('#personal-goal-save').textContent=value?`${verb} · ${format(value)} ${goalKind.unit(value)}`:verb;
    }
    document.querySelector('#personal-goal-input').addEventListener('input',paintPersonalGoal);
    document.querySelector('#personal-goal-modal').addEventListener('click',event=>{
      const input=document.querySelector('#personal-goal-input');
      if(event.target.id==='personal-goal-modal'||event.target.closest('[data-goal-close]')){ closePersonalGoalModal(); return; }
      const step=event.target.closest('[data-goal-step]');
      if(step&&goalKind){ const next=(Number(input.value)||0)+Number(step.dataset.goalStep)*goalKind.step; input.value=Math.min(goalKind.max,Math.max(1,next)); paintPersonalGoal(); return; }
      const preset=event.target.closest('[data-goal-preset]');
      if(preset){ input.value=preset.dataset.goalPreset; paintPersonalGoal(); return; }
      const remove=event.target.closest('[data-contrib-delete]'); if(remove) deleteContribution(remove);
    });
    function closePersonalGoalModal(){ closeLayer(document.querySelector('#personal-goal-modal')); personalGoalButton=null; }
    document.addEventListener('click',event=>{ const button=event.target.closest('[data-event-contribute]'); if(!button||!state.userId) return; if(!setupContribute(button.dataset.eventContribute)) return; document.querySelector('#personal-goal-error').textContent=''; openLayer(document.querySelector('#personal-goal-modal')); requestAnimationFrame(()=>document.querySelector('#personal-goal-input').focus({preventScroll:true})); });
    document.addEventListener('click',event=>{ const button=event.target.closest('[data-event-join]'); if(!button||!state.userId||!state.login) return; if(button.dataset.eventMode!=='personal_path'){ joinEvent(button); return; } personalGoalButton=button; setupPersonalGoal(button); document.querySelector('#personal-goal-error').textContent=''; openLayer(document.querySelector('#personal-goal-modal')); requestAnimationFrame(()=>document.querySelector('.personal-goal-save').focus({preventScroll:true})); });
    document.querySelector('#personal-goal-cancel').addEventListener('click',closePersonalGoalModal);
    document.querySelector('#personal-goal-form').addEventListener('submit',async event=>{ event.preventDefault(); const target=Number(document.querySelector('#personal-goal-input').value); if(!Number.isInteger(target)||target<1){ document.querySelector('#personal-goal-error').textContent=goalMode==='contribute'?'Введите целое число больше нуля.':'Укажите цель больше нуля.'; return; }
      if(goalMode==='contribute'){
        const save=document.querySelector('#personal-goal-save'); save.disabled=true;
        try { const response=await apiFetch(apiUrl(`events/${contributeEventId}/contributions`),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,amount:target})}); if(!response.ok){ const data=await response.json().catch(()=>({})); throw new Error(data.error||'Не удалось сохранить вклад.'); } closePersonalGoalModal(); await fetchEvents(); }
        catch(error){ document.querySelector('#personal-goal-error').textContent=error.message; }
        finally { save.disabled=false; }
        return;
      } const button=personalGoalButton; closePersonalGoalModal(); if(button) joinEvent(button,target); });
    function renderCalendar(){
      const calendar=document.querySelector('#week-calendar'), formatter=new Intl.DateTimeFormat(appLocale(),{weekday:'narrow'}), now=new Date(), monday=(now.getDay()+6)%7;
      const flameIcon=icon('flame');
      calendar.innerHTML=Array.from({length:7},(_,index)=>{ const date=new Date(now); date.setDate(now.getDate()-monday+index); const key=`${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`; const entry=entryForCalendarDate(key), ratio=entry ? Math.min(entry.completed/entry.max,1) : 0, current=key===todayKey, complete=ratio===1; return `<div class="calendar-day ${current?'current':''} ${complete?'complete':''} ${ratio>0?'has-progress':''}"><span>${formatter.format(date).replace('.','')}</span><b>${date.getDate()}</b><i class="day-dot" aria-hidden="true"></i></div>`; }).join('');
      const todayEntry=entryDateKey===todayKey?{completed:completed(),tasks:state.tasks,qaza:state.qaza}:((state.remoteEntries||[]).find(entry=>entry?.date===todayKey)||state.history?.[todayKey]);
      const streak=Number(state.streak||0),best=Number(state.bestStreak||streak),streakRing=document.querySelector('#streak-ring-big'),ky=appLocale()==='ky-KG',todayActive=entryHasProgress(todayEntry);
      const milestones=[3,7,14,30,40,100],nextMilestone=milestones.find(value=>value>streak),ringTarget=nextMilestone||Math.max(streak,1);
      /* The ring shows today's deeds; the streak lives in the pill below the text. */
      const max=dailyMax(), isPastDay=entryDateKey!==todayKey, doneToday=Math.min(isPastDay?completed():entryCompletedCount(todayEntry),max);
      const plural=n=>{ const d=n%10,h=n%100; return d===1&&h!==11?'день':d>=2&&d<=4&&(h<12||h>14)?'дня':'дней'; };
      document.querySelector('#streak-detail').textContent=doneToday;
      document.querySelector('#streak-unit').textContent=ky?`/ ${max}`:`из ${max}`;
      document.querySelector('#streak-title').textContent=ky?(doneToday>=max?'Бардык иштер аткарылды':doneToday>=6?'Жакшы күн, улантыңыз':doneToday>=3?'Күндүн минимуму бар':doneToday>0?'Жол башталды':'Бир иштен баштаңыз'):(doneToday>=max?'Все дела дня выполнены':doneToday>=6?'Хороший день, продолжайте':doneToday>=3?'Минимум дня есть':doneToday>0?'Путь начат':'Начните с одного дела');
      document.querySelector('#streak-detail-text').textContent=ky?(doneToday?`Бүгүн рейтингке +${doneToday*10} упай.`:'Ар бир иш — рейтингке +10 упай.'):(doneToday?`${isPastDay?'В этот день':'Сегодня'} +${doneToday*10} очков в рейтинг.`:'Каждое дело — плюс 10 очков в рейтинг.');
      document.querySelector('#streak-best').textContent=streak;
      document.querySelector('#streak-best-label').textContent=ky?'күн катары менен':`${plural(streak)} подряд`;
      document.querySelector('#streak-next').textContent=`${ky?'Рекорд':'Рекорд'}: ${best}`;
      streakRing.style.setProperty('--streak-fill',`${doneToday/max*360}deg`);
      streakRing.setAttribute('aria-label',ky?'Бүгүнкү иштер':'Дела за сегодня');
      streakRing.setAttribute('aria-valuenow',String(doneToday)); streakRing.setAttribute('aria-valuemax',String(max)); streakRing.setAttribute('aria-valuetext',ky?`${doneToday} / ${max}`:`${doneToday} из ${max}`);
      /* The glass hero above the prayer card and the streak beside «Эта неделя». */
      const heroDone=document.querySelector('#day-hero-done');
      if(heroDone){ heroDone.textContent=ky?`Күндүн иштери · ${doneToday} / ${max}`:`Дела дня · ${doneToday} из ${max}`; document.querySelector('#day-hero-percent').textContent=`${Math.round(doneToday/max*100)}%`; document.querySelector('#day-hero-ring').style.strokeDasharray=`${(44*doneToday/max).toFixed(1)} 44`; }
      const weekStreak=document.querySelector('#week-streak'); if(weekStreak) weekStreak.innerHTML=`${flameIcon}${ky?`${streak} күн катары менен`:`${streak} ${plural(streak)} подряд`}`;
    }
    let calendarCursor=null, calendarSelectedDate='';
    function entryForCalendarDate(key){
      if(key===entryDateKey) return {completed:completed(),max:ChetDeeds.maxFor(key)};
      /* The count comes from the day's marks, not a stored number that can be stale, and
         a day saved on the server (another phone, a new login) shows too. */
      const local=state.drafts?.[key]||state.history?.[key], remote=(state.remoteEntries||[]).find(entry=>entry?.date===key);
      if(!local&&!remote) return undefined;
      return {...(remote||{}),...(local||{}),completed:Math.max(entryCompletedCount(local),entryCompletedCount(remote)),max:ChetDeeds.maxFor(key)};
    }
    function formatDayLabel(key){ return new Intl.DateTimeFormat(appLocale(),{day:'numeric',month:'long'}).format(new Date(`${key}T12:00:00`)); }
    function renderMonthCalendar(){
      const legend=document.querySelector('#lg-full-label'); if(legend) legend.textContent=`все ${dailyMax()} дел`;
      const now=new Date(), cursor=calendarCursor||new Date(now.getFullYear(),now.getMonth(),1), year=cursor.getFullYear(), month=cursor.getMonth(), first=new Date(year,month,1), days=new Date(year,month+1,0).getDate(), offset=(first.getDay()+6)%7;
      const monthTitle=new Intl.DateTimeFormat(appLocale(),{month:'long',year:'numeric'}).format(cursor).replace(/\s*г\.?$/,'');
      document.querySelector('#month-title').textContent=monthTitle.charAt(0).toUpperCase()+monthTitle.slice(1);
      const currentMonth=year===now.getFullYear()&&month===now.getMonth(); document.querySelector('#month-next').disabled=currentMonth;
      const items=[];
      for(let i=0;i<offset;i++) items.push('<span class="month-day empty"></span>');
      let activeDays=0, fullDays=0;
      for(let day=1;day<=days;day++){
        const date=new Date(year,month,day), key=`${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
        const entry=entryForCalendarDate(key), ratio=entry?Math.min(entry.completed/entry.max,1):0, isActive=entry && entry.completed>0, isToday=key===todayKey, isFuture=date>now, level=ratio===1?'full':ratio>=.45?'mid':'';
        if(isActive) activeDays++; if(level==='full') fullDays++;
        items.push(`<button class="month-day ${isActive?'active':''} ${level} ${isToday?'today':''} ${isFuture?'future':''} ${calendarSelectedDate===key?'selected':''}" data-month-date="${key}" ${isFuture?'disabled':''} aria-label="${formatDayLabel(key)}">${day}</button>`);
      }
      document.querySelector('#month-grid').innerHTML=items.join('');
      const selected=calendarSelectedDate&&entryForCalendarDate(calendarSelectedDate);
      const dayWord=n=>{ const d=n%10,h=n%100; return d===1&&h!==11?'день':d>=2&&d<=4&&(h<12||h>14)?'дня':'дней'; };
      document.querySelector('#month-subtitle').textContent=activeDays?`${activeDays} ${dayWord(activeDays)} с делами · ${fullDays} ${fullDays===1?'полный':'полных'}`:'Дел пока не отмечено';
      // The chosen day's result with a bar, as on the canvas.
      const summary=document.querySelector('#month-summary');
      summary.classList.toggle('has-day',!!calendarSelectedDate);
      summary.innerHTML=calendarSelectedDate?`<span class="month-summary-top"><b>${escapeHtml(formatDayLabel(calendarSelectedDate))}</b><span><strong>${selected?selected.completed:0}</strong> из ${selected?selected.max:dailyMax()} дел</span></span><span class="zg-track" aria-hidden="true"><i style="width:${selected?Math.round(Math.min(1,selected.completed/selected.max)*100):0}%"></i></span>`:'Нажмите на дату, чтобы увидеть итог дня.';
      const openButton=document.querySelector('#open-selected-day');
      openButton.hidden=!calendarSelectedDate;
      if(calendarSelectedDate) openButton.textContent=calendarSelectedDate===todayKey?'Вернуться к сегодняшним делам':`Открыть дела за ${formatDayLabel(calendarSelectedDate)}`;
    }
    /* Reward screens. Several can be earned by one tap (the whole day and a badge),
       so they wait in a queue and open one after another. */
    const rewardQueue=[]; let rewardOpen=null, rewardReturnFocus=null;
    function rewardDays(n){ const d=n%10,h=n%100; return d===1&&h!==11?'день':d>=2&&d<=4&&(h<12||h>14)?'дня':'дней'; }
    function fillDoneReward(){
      const max=dailyMax(), streak=Number(state.streak||0), people=Array.isArray(state.remoteLeaderboard)?state.remoteLeaderboard:[], me=people.find(person=>person.me);
      document.querySelector('#reward-done-title').textContent=`Все ${max} дел выполнены`;
      document.querySelector('#reward-done-text').textContent='Машаллах! Сегодняшний путь пройден полностью. Пусть Аллах примет ваши дела.';
      document.querySelector('#reward-ring-label').textContent=`${max}/${max}`;
      document.querySelector('#reward-points').textContent=`+${max*10}`;
      document.querySelector('#reward-streak').textContent=String(streak);
      document.querySelector('#reward-streak-label').textContent=`${rewardDays(streak)} подряд`;
      document.querySelector('#reward-rank').textContent=me?`#${me.place||people.indexOf(me)+1}`:`${Number(state.score||0)}`;
      document.querySelector('#reward-rank-label').textContent=me?'в рейтинге':'очков всего';
      const confetti=document.querySelector('#celebration .reward-confetti');
      if(!confetti.childElementCount){ const colors=['#FFD66B','#FFFFFF','#9FDDBB','#F59E8B','#8FB8FF']; confetti.innerHTML=Array.from({length:24},(_,i)=>`<i style="left:${(i*41)%97}%;background:${colors[i%colors.length]};animation-delay:${((i*.37)%3).toFixed(2)}s;animation-duration:${(2.6+(i%5)*.3).toFixed(1)}s"></i>`).join(''); }
    }
    function fillBadgeReward(badge){
      document.querySelector('#reward-badge-icon').innerHTML=badge.icon||icon('star');
      document.querySelector('#reward-badge-title').textContent=badge.title;
      document.querySelector('#reward-badge-text').textContent=`${badge.detail}. Пусть Аллах примет ваши старания.`;
    }
    const rewardScreens={done:'#celebration',minimum:'#minimum-celebration',badge:'#achievement-celebration'};
    function openNextReward(){
      if(rewardOpen||!rewardQueue.length) return;
      const {kind,badge}=rewardQueue.shift(), element=document.querySelector(rewardScreens[kind]);
      if(kind==='done') fillDoneReward(); if(kind==='badge') fillBadgeReward(badge);
      rewardReturnFocus=document.activeElement; rewardOpen=element; openLayer(element);
      navigator.vibrate?.(kind==='minimum'?[12,30,12]:[20,40,20,40,30]);
      requestAnimationFrame(()=>element.querySelector('.reward-primary')?.focus({preventScroll:true}));
    }
    function showReward(kind,badge=null){ rewardQueue.push({kind,badge}); openNextReward(); }
    function closeReward(){
      if(!rewardOpen) return;
      closeLayer(rewardOpen); rewardOpen=null;
      if(rewardQueue.length) setTimeout(openNextReward,260); else rewardReturnFocus?.focus?.({preventScroll:true});
    }
    document.addEventListener('click',event=>{
      if(!event.target.closest('.reward')) return;
      if(event.target.closest('[data-reward-badges]')){ rewardQueue.length=0; closeReward(); openProfileScreen('achievements'); return; }
      if(event.target.closest('[data-reward-close]')) closeReward();
    });
    document.addEventListener('keydown',event=>{
      if(event.key!=='Escape') return;
      if(rewardOpen){ closeReward(); return; }
      if(!document.querySelector('#exit-confirmation').hidden){ document.querySelector('#exit-cancel').click(); return; }
      if(!document.querySelector('#personal-goal-modal').hidden){ closePersonalGoalModal(); return; }
      [...document.querySelectorAll('.calendar-sheet:not([hidden]) .calendar-sheet-backdrop')].pop()?.click();
    });
    function scoreUpdate(before){ const after=completed(); if(after>before) state.score=(state.score||0)+(after-before)*10; if(after<before) state.score=Math.max(0,(state.score||0)-(before-after)*10); recordDay(); refreshStreak(); const completedToday=entryDateKey===todayKey&&after===dailyMax()&&before<dailyMax(), minimumReached=entryDateKey===todayKey&&after>=3&&before<3&&!state.minimumCelebratedDays?.[todayKey], shouldCelebrate=completedToday&&!state.celebratedDays?.[todayKey], unlocked=state.achievementBaselineReady?achievementData().find(badge=>badge.earned&&!state.seenAchievements?.[badge.title]):null; if(shouldCelebrate) state.celebratedDays={...(state.celebratedDays||{}),[todayKey]:true}; if(minimumReached) state.minimumCelebratedDays={...(state.minimumCelebratedDays||{}),[todayKey]:true}; if(unlocked) state.seenAchievements={...(state.seenAchievements||{}),[unlocked.title]:true}; save(); render(); requestAnimationFrame(()=>{ if(shouldCelebrate) showReward('done'); else if(minimumReached) showReward('minimum'); if(unlocked) showReward('badge',unlocked); }); }
    /* The podium grows in: the steps rise out of the floor one after another (3rd, 2nd, 1st),
       then the avatars and names appear. Only transform and opacity move, which the phone's
       GPU handles without laying the page out again every frame. A redraw that lands in the
       middle (fresh data from the server) carries on from the same moment via `elapsed`.
       On a refresh of the same board, `from` (place → previous height) eases changed steps. */
    let podiumIntroAt=-1e9;
    function growPodium(from,elapsed=0){
      const podium=document.querySelector('#leaderboard .rt-podium:not([aria-busy])');
      if(!podium||motionOff()||!document.querySelector('#rating-view')?.classList.contains('active')) return;
      if(!from&&!elapsed) podiumIntroAt=performance.now();
      const order={3:0,2:1,1:2}, ease='cubic-bezier(.2,.8,.2,1)';
      podium.querySelectorAll('.rt-slot').forEach(slot=>{
        const step=slot.querySelector('.rt-step'); if(!step) return;
        const place=Number(step.textContent.trim())||0, height=step.offsetHeight;
        if(from){ const start=from.get(place)??height; if(Math.abs(start-height)>=1) step.animate([{height:`${start}px`},{height:`${height}px`}],{duration:500,easing:ease}); return; }
        const parts=[...slot.querySelectorAll('.rt-crown,.rt-avatar,.rt-name')];
        [step,...parts].forEach(el=>el.getAnimations().forEach(a=>a.cancel()));
        const delay=120+(order[place]??0)*160;
        const moves=[step.animate([{transform:'translateY(101%)'},{transform:'translateY(0)'}],{duration:850,delay,easing:ease,fill:'backwards'}),
          ...parts.map((el,i)=>el.animate([{opacity:0,transform:'translateY(10px) scale(.96)'},{opacity:1,transform:'none'}],{duration:450,delay:delay+420+i*60,easing:ease,fill:'backwards'}))];
        if(elapsed) moves.forEach(move=>{ move.currentTime=elapsed; });
      });
    }
    function showView(viewId){
      // A new section starts at the top, so its cards rise where they can be seen.
      if(!document.querySelector('#'+viewId)?.classList.contains('active')) window.scrollTo(0,0);
      document.querySelectorAll('.view,.nav button').forEach(element=>element.classList.remove('active'));
      document.querySelector('#'+viewId)?.classList.add('active');
      // Screens that repaint on every tap animate their cards only right after opening.
      const opened=document.querySelector('#'+viewId); opened?.classList.add('entering'); clearTimeout(opened?.enteringTimer); if(opened) opened.enteringTimer=setTimeout(()=>opened.classList.remove('entering'),600);
      document.querySelector(`.nav [data-view="${viewId}"]`)?.classList.add('active');
      document.querySelector('#app').classList.toggle('profile-mode',viewId==='profile-view');
      document.querySelector('#app').classList.toggle('in-profile',['profile-view','profile-sub-view','settings-view'].includes(viewId));
      document.querySelector('#app').classList.toggle('on-today',viewId==='today-view');
      document.querySelector('#app').classList.toggle('on-stats',['stats-view','stats-detail-view'].includes(viewId));
      if(viewId==='stats-detail-view') renderStatsDetail(true);
      document.querySelector('#app').classList.toggle('on-glass',['quiz-view','stats-view','stats-detail-view','rating-view','zikr-view','profile-view','settings-view','profile-sub-view','events-view'].includes(viewId));
      if(viewId==='rating-view'){ loadLeaderboard(ratingPeriod(),ratingMetric()); growPodium(null); }
      if(viewId==='profile-view'&&!isGuest()) loadLeaderboard('all');
      if(viewId==='events-view') fetchEvents();
      if(viewId==='zikr-view'){ renderZikr(); riseIn(document.querySelector('#zikr-view')); if(!state.events) fetchEvents(); }
      if(viewId==='stats-view'){ renderStats(true); fetchPersonalEntries(); }
      if(viewId==='quiz-view') window.ChetQuizzes?.show(); else window.ChetQuizzes?.hide();
    }
    document.addEventListener('click',event=>{ const option=event.target.closest('.stats-seg [data-period]'); if(!option||option.dataset.period===statsPeriod) return; statsPeriod=option.dataset.period; renderStats(true); });
    document.addEventListener('click', e=>{ const before=completed(); const check=e.target.dataset.check, choice=e.target.dataset.choice, nafil=e.target.closest('[data-nafil]')?.dataset.nafil; if(nafil){ const values=nafilValues(); state.tasks.nafil={...values,[nafil]:!values[nafil]}; scoreUpdate(before); if(!values[nafil]) document.querySelector(`[data-nafil="${nafil}"]`)?.classList.add('just-checked'); return; } if(check){state.tasks[check]=!state.tasks[check]; scoreUpdate(before);} if(choice){state.tasks[e.target.dataset.id]=choice; scoreUpdate(before);} const view=e.target.closest('[data-view]'); if(view) showView(view.dataset.view); });
    const fieldTimers=new WeakMap(), fieldBaselines=new WeakMap();
    function storeFieldValue(field){
      if(field.dataset.id) state.tasks[field.dataset.id]=field.value;
      if(field.dataset.qaza) state.qaza[field.dataset.qaza]=field.value;
      /* Lowering salawat or zikr below what the counter counted lowers the counted zikrs
         too, so the zikr section agrees and the next sync does not bring the number back. */
      if(field.dataset.id==='salawat') return matchSalawatCounter();
      // The check guards against an older zikr-sync.js still held by the browser cache.
      if(field.dataset.id==='tasbihat'&&ChetZikrSync.trimToTotal){
        const trimmed=ChetZikrSync.trimToTotal(state.tasks.zikr,field.dataset.id,field.value);
        if(!Object.keys(trimmed.deltas).length) return;
        state.tasks={...state.tasks,zikr:trimmed.zikr};
        state.zikrPending=Object.entries(trimmed.deltas).reduce((pending,[id,delta])=>ChetZikrSync.addPending(pending,entryDateKey,id,delta),state.zikrPending);
        scheduleZikrFlush();
      }
    }
    function focusFollowingField(field){
      const fields=[...document.querySelectorAll('[data-id], [data-qaza]')];
      const key=field.dataset.id ? `id:${field.dataset.id}` : `qaza:${field.dataset.qaza}`;
      const index=fields.findIndex(item=>(item.dataset.id?`id:${item.dataset.id}`:`qaza:${item.dataset.qaza}`)===key);
      const next=fields[index+1];
      if(next){ next.focus(); next.select?.(); }
    }
    function commitField(field, moveNext=false){
      const timer=fieldTimers.get(field); if(timer) clearTimeout(timer);
      const before=fieldBaselines.has(field) ? fieldBaselines.get(field) : completed();
      storeFieldValue(field); fieldBaselines.delete(field); fieldTimers.delete(field);
      scoreUpdate(before);
      if(moveNext) setTimeout(()=>focusFollowingField(field),40);
    }
    document.addEventListener('input',e=>{ const field=e.target; if(!field.dataset.id && !field.dataset.qaza) return; if(!fieldBaselines.has(field)) fieldBaselines.set(field,completed()); storeFieldValue(field); const oldTimer=fieldTimers.get(field); if(oldTimer) clearTimeout(oldTimer); fieldTimers.set(field,setTimeout(()=>commitField(field,true),700)); });
    document.addEventListener('change',e=>{ const field=e.target; if(field.dataset.id || field.dataset.qaza) commitField(field,false); });
    document.addEventListener('click',e=>{ if(!e.target.closest('[data-back-today]')) return; calendarSelectedDate=todayKey; openSelectedDay(); });
    /* Qaza: tap adds one prayer, a long press removes one, «Сбросить» clears the day's qaza. */
    let qazaHoldTimer=null, qazaHeld=false;
    document.addEventListener('pointerdown',e=>{
      const tile=e.target.closest('[data-qaza-inc]'); if(!tile) return;
      qazaHeld=false; clearTimeout(qazaHoldTimer);
      qazaHoldTimer=setTimeout(()=>{ const name=tile.dataset.qazaInc, value=Number(state.qaza[name])||0; if(!value) return; qazaHeld=true; const before=completed(); state.qaza={...state.qaza,[name]:value>1?String(value-1):''}; navigator.vibrate?.(25); scoreUpdate(before); rollNumber(`[data-qaza-inc="${name}"] b`,'down'); },550);
    });
    ['pointerup','pointercancel','pointerleave'].forEach(type=>document.addEventListener(type,()=>clearTimeout(qazaHoldTimer)));
    document.addEventListener('contextmenu',e=>{ if(e.target.closest('[data-qaza-inc]')) e.preventDefault(); });
    document.addEventListener('click',e=>{
      const add=e.target.closest('[data-qaza-inc]'), reset=e.target.closest('[data-qaza-reset]'); if(!add&&!reset) return;
      if(add&&qazaHeld){ qazaHeld=false; return; }
      if(add) navigator.vibrate?.(10);
      const before=completed();
      state.qaza=add?{...state.qaza,[add.dataset.qazaInc]:String((Number(state.qaza[add.dataset.qazaInc])||0)+1)}:Object.fromEntries(QAZA.map(name=>[name,'']));
      scoreUpdate(before);
      if(add) rollNumber(`[data-qaza-inc="${add.dataset.qazaInc}"] b`,'up');
    });
    /* Counters roll: the new number comes up after +1 and down after −1. */
    /* Writes a number digit by digit; only the digits that changed roll in, like an odometer
       (34 → 35 rolls the 5, 39 → 40 rolls both). Digits are matched from the right. */
    function rollDigits(element, text, direction){
      if(!element) return;
      const before=[...(element.dataset.value??element.textContent)], after=[...text], shift=after.length-before.length;
      element.dataset.value=text;
      /* A changed digit keeps the old one in the same window: the old slides out as the new slides in. */
      element.innerHTML=after.map((char,index)=>{ const old=before[index-shift], changed=direction&&!motionOff()&&old!==char; return changed?`<span class="rd rd-${direction}"><span class="rd-new">${char}</span>${old!==undefined?`<span class="rd-old" aria-hidden="true">${old}</span>`:''}</span>`:`<span class="rd">${char}</span>`; }).join('');
      element.setAttribute('aria-label',text);
    }
    function rollNumber(selector, direction){
      const element=document.querySelector(selector); if(!element||motionOff()) return;
      element.classList.remove('roll-up','roll-down'); void element.offsetWidth; element.classList.add(`roll-${direction}`);
      element.addEventListener('animationend',()=>element.classList.remove(`roll-${direction}`),{once:true});
    }
    function motionOff(){ return matchMedia('(prefers-reduced-motion: reduce)').matches||document.body.classList.contains('a11y-reduce'); }
    /* Zikr repaints on every tap, so its cards rise once by script when a screen opens
       instead of by CSS, which would replay on every repaint. */
    function riseIn(container){
      if(!container?.animate||motionOff()) return;
      [...container.children].forEach((child,index)=>child.animate([{opacity:0,transform:'translateY(12px)'},{opacity:1,transform:'none'}],{duration:240,delay:Math.min(index,5)*60,easing:'cubic-bezier(.2,.8,.2,1)',fill:'backwards'}));
    }
    /* Dialogs and rewards fade out before they hide, the way sheets slide down. */
    function openLayer(element){ clearTimeout(element.closing); element.classList.remove('is-closing'); element.hidden=false; }
    function closeLayer(element){
      if(element.hidden) return;
      const hide=()=>{ element.classList.remove('is-closing'); element.hidden=true; };
      if(motionOff()) return hide();
      clearTimeout(element.closing); element.classList.add('is-closing'); element.closing=setTimeout(hide,220);
    }
    /* −/+ and quick-add buttons change the neighbouring field and save it like typed input. */
    document.addEventListener('click',e=>{
      const button=e.target.closest('[data-step]'); if(!button) return;
      const field=document.querySelector(`[data-id="${button.dataset.stepFor}"]`); if(!field) return;
      if(!fieldBaselines.has(field)) fieldBaselines.set(field,completed());
      navigator.vibrate?.(10);
      const value=Math.min(99999,Math.max(0,(Number(field.value)||0)+Number(button.dataset.step)));
      field.value=value?String(value):'';
      commitField(field,false);
    });
    document.addEventListener('keydown',e=>{ const field=e.target; if(e.key==='Enter' && (field.dataset.id || field.dataset.qaza)){ e.preventDefault(); commitField(field,true); } });
    function openSheet(id){ const sheet=document.querySelector('#'+id); clearTimeout(sheet.closing); sheet.classList.remove('is-closing'); sheet.hidden=false; document.body.classList.add('sheet-open'); }
    /* The sheet slides back down before it hides. */
    function closeSheet(id){
      const sheet=document.querySelector('#'+id); if(sheet.hidden) return;
      const hide=()=>{ sheet.classList.remove('is-closing'); sheet.hidden=true; if(!document.querySelector('.calendar-sheet:not([hidden])')) document.body.classList.remove('sheet-open'); };
      if(motionOff()) return hide();
      clearTimeout(sheet.closing); sheet.classList.add('is-closing'); sheet.closing=setTimeout(hide,240);
    }
    document.querySelector('#calendar-open').addEventListener('click',()=>{ const now=new Date(); calendarCursor=new Date(now.getFullYear(),now.getMonth(),1); calendarSelectedDate=todayKey; renderMonthCalendar(); openSheet('calendar-sheet'); });
    document.addEventListener('click',e=>{ if(e.target.closest('[data-close-calendar]')) closeSheet('calendar-sheet'); });
    const calendarPanel=document.querySelector('#calendar-sheet .calendar-sheet-panel');
    document.querySelector('#month-prev').addEventListener('click',()=>{ calendarCursor.setMonth(calendarCursor.getMonth()-1); calendarSelectedDate=''; renderMonthCalendar(); });
    document.querySelector('#month-next').addEventListener('click',()=>{ const now=new Date(); if(calendarCursor.getFullYear()===now.getFullYear()&&calendarCursor.getMonth()===now.getMonth()) return; calendarCursor.setMonth(calendarCursor.getMonth()+1); calendarSelectedDate=''; renderMonthCalendar(); });
    document.querySelector('#month-grid').addEventListener('click',event=>{ const day=event.target.closest('[data-month-date]'); if(!day||day.disabled) return; calendarSelectedDate=day.dataset.monthDate; renderMonthCalendar(); });
    function copyEntry(value){ return JSON.parse(JSON.stringify(value||{})); }
    function stashOpenDay(){
      state.drafts={...(state.drafts||{}),[entryDateKey]:{completed:completed(),max:dailyMax(),tasks:copyEntry(state.tasks),qaza:copyEntry(state.qaza)}};
      if(entryDateKey!==todayKey) recordDay(entryDateKey);
    }
    async function openSelectedDay(){
      const date=calendarSelectedDate;
      if(!date) return;
      stashOpenDay();
      let entry=state.drafts?.[date];
      if((!entry || !entry.tasks) && state.userId){
        try {
          const response=await apiFetch(apiUrl(`users/${state.userId}/entries/${date}`));
          if(response.ok) entry=await response.json();
        } catch { /* Если сети нет, откроем локально сохранённый день. */ }
      }
      entryDateKey=date;
      state.tasks=copyEntry(entry?.tasks); state.qaza=copyEntry(entry?.qaza);
      state.activeDate=date;
      save(); render(); closeSheet('calendar-sheet');
      setSaveStatus(date===todayKey?'Вы вернулись к сегодняшним делам.':`Открыт ${formatDayLabel(date)}. Дополните дела и сохраните прогресс.`);
    }
    document.querySelector('#open-selected-day').addEventListener('click',openSelectedDay);
    const calendarGrabber=calendarPanel.querySelector('.sheet-grabber'); let calendarDrag=null;
    function finishCalendarDrag(close){
      if(!calendarDrag) return; const pointerId=calendarDrag.pointerId; clearTimeout(calendarDrag.hold); calendarDrag=null; calendarGrabber.releasePointerCapture?.(pointerId);
      calendarPanel.style.transition='transform .2s cubic-bezier(.2,.8,.2,1)';
      if(close){ calendarPanel.style.transform='translateY(110%)'; setTimeout(()=>{ closeSheet('calendar-sheet'); calendarPanel.style.transform=''; calendarPanel.style.transition=''; },205); }
      else { calendarPanel.style.transform=''; setTimeout(()=>{ calendarPanel.style.transition=''; },205); }
    }
    calendarGrabber.addEventListener('pointerdown',event=>{ calendarDrag={startY:event.clientY,delta:0,pointerId:event.pointerId,hold:setTimeout(()=>finishCalendarDrag(true),850)}; calendarGrabber.setPointerCapture?.(event.pointerId); });
    calendarGrabber.addEventListener('pointermove',event=>{ if(!calendarDrag) return; calendarDrag.delta=Math.max(0,event.clientY-calendarDrag.startY); if(calendarDrag.delta>4) clearTimeout(calendarDrag.hold); calendarPanel.style.transition='none'; calendarPanel.style.transform=`translateY(${calendarDrag.delta}px)`; });
    calendarGrabber.addEventListener('pointerup',()=>finishCalendarDrag((calendarDrag?.delta||0)>76));
    calendarGrabber.addEventListener('pointercancel',()=>finishCalendarDrag(false));
    /* The card is one tap target; once read today it folds into a single line. */
    document.querySelector('.hadith-card').addEventListener('click',event=>{ if(event.target.closest('#hadith-more')) return; document.querySelector('#hadith-more').click(); });
    function paintHadithRead(){ document.querySelector('.hadith-card')?.classList.toggle('is-read',state.hadithReadOn===todayKey); }
    document.querySelector('#hadith-more').addEventListener('click',()=>{ if(state.hadithReadOn!==todayKey){ state.hadithReadOn=todayKey; save(); setTimeout(paintHadithRead,300); } });
    document.querySelector('#hadith-more').addEventListener('click',()=>{ const hadith=hadithOfTheDay(); document.querySelector('#hadith-full-text').textContent=hadith.text; document.querySelector('#hadith-full-source').textContent=hadith.source; document.querySelector('#hadith-explanation').textContent=hadith.explanation; document.querySelector('#hadith-practice').textContent=hadith.practice; const day=new Intl.DateTimeFormat(appLocale(),{weekday:'long',day:'numeric',month:'long'}).format(new Date()); document.querySelector('#hadith-sheet-date').textContent=day.charAt(0).toUpperCase()+day.slice(1); openSheet('hadith-sheet'); });
    document.addEventListener('click',e=>{ if(e.target.closest('[data-close-hadith]')) closeSheet('hadith-sheet'); });
    /* The full welcome (3 s with the animation) plays on the very first launch only. Later
       launches just flash the logo while the app gets ready. */
    const preloaderStartedAt=Date.now(), firstLaunch=(()=>{ try { if(localStorage.getItem('chetele-launched')) return false; localStorage.setItem('chetele-launched','1'); } catch {} return true; })();
    if(!firstLaunch) document.querySelector('#preloader-lottie')?.remove();
    /* The welcome animation is a 50 KB muted video (it used to be a 920 KB Lottie file plus a
       300 KB player). It only plays on the first launch. */
    if(firstLaunch){ const container=document.querySelector('#preloader-lottie'); if(container){ const video=document.createElement('video'); Object.assign(video,{src:'assets/preloader.mp4',muted:true,loop:true,autoplay:true,playsInline:true,preload:'auto'}); video.setAttribute('playsinline',''); video.setAttribute('muted',''); container.appendChild(video); video.play?.().catch(()=>{}); } }
    function dismissPreloader(){ const preloader=document.querySelector('#app-preloader'); if(!preloader||preloader.classList.contains('is-hidden')) return; const wait=Math.max(0,(firstLaunch?3000:500)-(Date.now()-preloaderStartedAt)); setTimeout(()=>{ requestAnimationFrame(()=>preloader.classList.add('is-hidden')); setTimeout(()=>{ preloader.hidden=true; },450); },wait); }
    /* Notifications open a screen: ?open=deeds shows today's deeds, other names open that section. */
    function openTarget(target){
      /* Prayer times and the qibla live in the prayer script, which loads after this one:
         a link opened at launch waits for it. */
      if(target==='prayer'||target==='qibla'){
        const run=()=>{ showView('today-view'); (target==='qibla'?window.cheteleOpenQibla:window.cheteleOpenSchedule)?.(); };
        if(window.cheteleOpenQibla) run(); else document.addEventListener('DOMContentLoaded',run,{once:true});
        return;
      }
      const views={deeds:'today-view',zikr:'zikr-view',events:'events-view',stats:'stats-view',rating:'rating-view',quiz:'quiz-view'}, view=views[target];
      if(!view||document.querySelector('#app').hidden) return;
      if(target==='deeds'&&entryDateKey!==todayKey) document.querySelector('[data-back-today]')?.click();
      showView(view); window.scrollTo(0,0);
      if(target==='deeds') setTimeout(()=>document.querySelector('#tasks-heading')?.scrollIntoView({behavior:motionOff()?'auto':'smooth',block:'start'}),350);
    }
    navigator.serviceWorker?.addEventListener('message',event=>{ if(event.data?.type==='open') openTarget(event.data.target); if(event.data?.type==='maintenance') probeServer().then(result=>{ if(result==='down') showMaintenance(); }); });
    function showCabinet(){
      const app=document.querySelector('#app');
      app.hidden=false; app.style.display='block';
      resetIfNewDay(); render(); dismissPreloader();
    }
    let swipeStartX=null, swipeStartY=null;
    document.querySelector('#app').addEventListener('touchstart',event=>{ if(event.target.closest('input,button')) return; const touch=event.touches[0]; swipeStartX=touch.clientX; swipeStartY=touch.clientY; },{passive:true});
    document.querySelector('#app').addEventListener('touchend',event=>{ if(swipeStartX===null) return; const touch=event.changedTouches[0], xDelta=touch.clientX-swipeStartX, yDelta=touch.clientY-swipeStartY; swipeStartX=null; if(Math.abs(xDelta)<55 || Math.abs(xDelta)<Math.abs(yDelta)) return; const todayOpen=document.querySelector('#today-view').classList.contains('active'); if(xDelta<0 && todayOpen) showView('rating-view'); if(xDelta>0 && !todayOpen) showView('today-view'); },{passive:true});
    function clearAuthError(form){ form.querySelectorAll('.name-input').forEach(field=>{field.classList.remove('invalid'); field.removeAttribute('aria-invalid');}); const error=form.querySelector('.auth-error'); if(error) error.textContent=''; }
    function showAuthError(form,field,message){ clearAuthError(form); field.classList.add('invalid'); field.setAttribute('aria-invalid','true'); const error=form.querySelector('.auth-error'); if(error) error.textContent=message; field.focus(); }
    document.querySelectorAll('.password-toggle').forEach(button=>button.addEventListener('click',()=>{ const input=document.querySelector(`#${button.dataset.passwordToggle}`), visible=input.type==='text'; input.type=visible?'password':'text'; button.setAttribute('aria-pressed',String(!visible)); button.setAttribute('aria-label',visible?'Показать пароль':'Скрыть пароль'); }));
    document.querySelectorAll('#name-form .name-input,#login-form .name-input').forEach(field=>field.addEventListener('input',()=>clearAuthError(field.closest('form'))));
    async function startApp(){
      const input=document.querySelector('#name-input'), passwordInput=document.querySelector('#register-password-input'), button=document.querySelector('#start-button');
      const name=input.value.trim().replace(/\s+/g,' '), login=name.toLocaleLowerCase(), password=passwordInput.value;
      const form=document.querySelector('#name-form'); clearAuthError(form);
      if(!name){ showAuthError(form,input,'Введите имя, чтобы создать профиль.'); return; }
      if(!password){ showAuthError(form,passwordInput,'Придумайте пароль, чтобы защитить профиль.'); return; }
      const before={name:state.name,login:state.login,rememberMe:state.rememberMe};
      state.name=name; state.login=login; registrationPassword=password; state.rememberMe=document.querySelector('#register-remember').checked;
      button.disabled=true;
      const created=await ensureRemoteUser();
      button.disabled=false;
      if(created){ save(); finishAuth(); return; }
      Object.assign(state,before); registrationPassword='';
      showAuthError(form,input,registrationError||'Не удалось создать профиль. Проверьте подключение.');
    }
    /* Guest mode. Everyone starts on the main screen; marks stay on this phone until a
       profile is created. Stats, rating, events and the profile show a gate instead. */
    const GUEST_ICON='<svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 8a4 4 0 1 0 8 0a4 4 0 1 0 -8 0M4.5 20.5c1.2-3.6 4.1-5.5 7.5-5.5s6.3 1.9 7.5 5.5"/></svg>';
    const GUEST_CHECK='<svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    /* A guest sees each section as it will look: rating and events are the real ones,
       stats and the profile are empty pages. A card on each asks for a profile. */
    const GUEST_ICONS={lock:'M6 11h12v9H6zM8.5 11V8a3.5 3.5 0 0 1 7 0v3',phone:'M7 2.5h10a1.5 1.5 0 0 1 1.5 1.5v16a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 20V4A1.5 1.5 0 0 1 7 2.5zM11 18.5h2',star:'M12 3.5l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8z',qaza:'M3.5 12a8.5 8.5 0 1 0 2.5-6M3.5 4v4h4M12 8v4l3 2',medal:'M6 9a6 6 0 1 0 12 0a6 6 0 1 0 -12 0M8.5 13.8 7 21l5-2.5 5 2.5-1.5-7.2',bell:'M6 16V11a6 6 0 0 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0',clock:'M3 12a9 9 0 1 0 18 0a9 9 0 1 0 -18 0M12 7.5V12l3 2',settings:'M4 7h10M18 7h2M4 17h4M12 17h8M14 5v4M8 15v4',chevron:'m9 18 6-6-6-6',back:'m15 18-6-6 6-6'};
    function isGuest(){ return !state.userId; }
    const guestSvg=name=>`<svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="${GUEST_ICONS[name]}"/></svg>`;
    function guestCta(title,text,iconName='lock',primary='Создать профиль'){
      return `<section class="guest-cta" aria-label="${title}"><div class="guest-cta-row"><i>${guestSvg(iconName)}</i><span><b>${title}</b><small>${text}</small></span></div><div class="guest-cta-actions"><button type="button" class="guest-primary" data-auth="signup">${primary}</button><button type="button" class="guest-secondary" data-auth="login">Войти</button></div></section>`;
    }
    function guestStatsPage(){
      const days=['Пн','Вт','Ср','Чт','Пт','Сб','Вс'], today=(new Date().getDay()+6)%7;
      const tile=(label,iconName,value,unit,cls='')=>`<div class="gs-tile"><span class="gs-tile-top"><span>${label}</span><i class="${cls}">${iconName.startsWith('soft')?icon(iconName):guestSvg(iconName)}</i></span><b>${value}</b><small>${unit}</small></div>`;
      const nafil=[['Аср','нафил'],['Иша','нафил'],['Аввабин','нафил'],['Духа','нафил'],['Тахаджуд','ночной']].map(([title,kind])=>`<div class="gs-bar-row"><span><b>${title}</b><small>${kind}</small></span><i></i><em>0</em></div>`).join('');
      return guestCta('Здесь будет ваша статистика','Пока пусто: отметки гостя не попадают в итоги. С профилем сюда сразу перейдут дела с главного.')
        +`<section class="gs-hero" aria-label="Дела за неделю"><div class="gs-hero-bar"><span>Дела за неделю</span><em>пока пусто</em></div><article class="gs-hero-card"><div class="gs-total"><b>0</b><span>из ${dailyMax()*7} дел</span><em>0%</em></div><div class="gs-chart" role="img" aria-label="График пока пуст">${days.map((day,index)=>`<div class="${index===today?'today':''}"><small>0</small><i></i><span>${day}</span></div>`).join('')}</div></article></section>`
        +`<div class="gs-tiles">${tile('Серия','softFlame','—','дней подряд','warm')}${tile('Лучший день','star','—','дел за день')}${tile('Салават','softSalawat','0','раз за неделю')}${tile('Куран','softQuran','0','страниц')}</div>`
        +`<section class="gs-group"><div class="gs-group-head"><h3>Намаз</h3><span>0 из 35</span></div><div class="gs-card">${nafil}<hr><div class="gs-qaza"><i>${guestSvg('qaza')}</i><span><b>Пропущенные намазы</b><small>восполнено за период</small></span><em>0</em></div></div></section>`
        +`<section class="gs-card gs-streak" aria-label="Серия"><div class="gs-qaza"><i class="warm">${icon('softFlame')}</i><span><b>Серия начнётся с первого дня</b><small>Каждый день с делом закрасит клетку</small></span></div><div class="gs-grid" role="img" aria-label="Последние 28 дней пока пусты">${'<span></span>'.repeat(28)}</div></section>`;
    }
    function guestProfilePage(){
      const row=(title,sub,iconName,attrs,locked=false)=>`<li><button type="button" class="gp-row${locked?' locked':''}" ${attrs}><i>${guestSvg(iconName)}</i><span><b>${title}</b><small>${sub}</small></span>${locked?`<em>${guestSvg('lock')}с профилем</em>`:guestSvg('chevron')}</button></li>`;
      let place='Бишкек'; try { const saved=JSON.parse(localStorage.getItem('chetele-prayer-place')||'null'); place=saved?.id==='geo'?(saved.name||'Моё место'):(window.ChetPrayer?.CITIES.find(city=>city.id===saved?.id)||window.ChetPrayer?.CITIES[0])?.name||place; } catch {}
      return `<header class="gp-top"><button type="button" data-view="today-view" aria-label="Вернуться к делам">${guestSvg('back')}</button><span>Профиль</span><i aria-hidden="true"></i></header>`
        +`<section class="gs-hero gp-hero" aria-label="Гость"><div class="gp-me"><span class="gp-avatar">${GUEST_ICON}</span><span><b>Гость</b><small>Отметки только на этом телефоне</small></span></div><article class="gs-hero-card gp-stats"><span><b class="muted">0</b><small>очков</small></span><span><b class="muted">—</b><small>в рейтинге</small></span><span><b id="gp-today">${entryDateKey===todayKey?completed():0}</b><small>сегодня дел</small></span></article></section>`
        +guestCta('Сохраните свой путь','Профиль сохранит отметки, откроет статистику, рейтинг и события и вход с любого телефона.','phone')
        +`<ol class="gs-card gp-menu" aria-label="Меню профиля">${row('Достижения','Серии и награды','medal','data-auth="signup"',true)}${row('Напоминания','Утро, день и вечер','bell','data-auth="signup"',true)}${row('Время намазов',`${escapeHtml(place)} · ханафитский мазхаб`,'clock','data-profile-menu="place"')}${row('Настройки','Тема, язык, крупный текст','settings','data-profile-menu="settings"')}</ol>`;
    }
    function renderGuestGates(){
      if(!isGuest()) return;
      [['stats-view',guestStatsPage],['profile-view',guestProfilePage]].forEach(([viewId,build])=>{
        const view=document.querySelector('#'+viewId); if(!view) return;
        let element=view.querySelector(':scope>.guest-gate-wrap');
        if(!element){ element=document.createElement('div'); element.className='guest-gate-wrap'; const header=view.querySelector(':scope>.stats-top'); if(header) header.after(element); else view.prepend(element); }
        element.innerHTML=build();
      });
    }
    function paintGuest(){
      const guest=isGuest();
      if(guest) renderGuestGates();
      document.querySelector('#app').classList.toggle('is-guest',guest);
      const banner=document.querySelector('#guest-banner'); if(banner) banner.hidden=!guest;
      if(guest) setSaveStatus('Сохранено только на этом телефоне');
    }
    /* Days with marks that registration carries into the new profile. */
    function guestDays(){
      const past=Object.entries(state.history||{}).filter(([date,entry])=>date!==todayKey&&entryHasProgress(entry)).map(([date])=>date);
      return {past,today:entryDateKey===todayKey?completed():0};
    }
    function guestCarryText(){
      const {past,today}=guestDays(), days=past.length+(today?1:0);
      const plural=(n,one,few,many)=>{ const d=n%10,h=n%100; return d===1&&h!==11?one:d>=2&&d<=4&&(h<12||h>14)?few:many; };
      if(days>1) return `Отметки за ${days} ${plural(days,'день','дня','дней')} перейдут в профиль`;
      if(today) return `${today} ${plural(today,'сегодняшняя отметка перейдёт','сегодняшние отметки перейдут','сегодняшних отметок перейдут')} в профиль`;
      return '';
    }
    let authReturnView='';
    function setAuthMode(mode){
      const signup=mode==='signup';
      document.querySelector('#name-form').hidden=!signup; document.querySelector('#login-form').hidden=signup;
      document.querySelector('#auth-title').textContent=signup?'Создайте профиль':'С возвращением';
      document.querySelector('#auth-sub').textContent=signup?'Займёт полминуты. После этого откроются статистика, рейтинг и события.':'Войдите, чтобы вернуть свои отметки, серии и место в рейтинге.';
      const carry=signup?guestCarryText():'';
      document.querySelector('#auth-carry').hidden=!carry; document.querySelector('#auth-carry-text').textContent=carry;
    }
    function openAuth(mode,{login='',message=''}={}){
      authReturnView=document.querySelector('#app .view.active')?.id||'today-view';
      setAuthMode(mode); openSheet('auth-sheet');
      const form=document.querySelector(mode==='signup'?'#name-form':'#login-form');
      clearAuthError(form);
      if(mode==='login'&&login) document.querySelector('#login-input').value=login;
      if(message) showAuthError(form,login?document.querySelector('#password-input'):document.querySelector('#login-input'),message);
    }
    function finishAuth(){
      closeSheet('auth-sheet');
      document.querySelector('#name-form').reset(); document.querySelector('#login-form').reset();
      render();
      const view=authReturnView; authReturnView='';
      if(view&&view!=='today-view'&&document.querySelector('#'+view)?.classList.contains('active')){ document.querySelector('#'+view).classList.remove('active'); showView(view); }
    }
    /* After registration the guest's earlier days go up too; today goes with the usual sync. */
    async function uploadGuestDays(){
      const {past}=guestDays(); if(!past.length) return;
      for(const date of past){
        const entry=state.history[date];
        try { await apiFetch(apiUrl(`users/${state.userId}/entries/${date}`),{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({tasks:entry.tasks||{},qaza:entry.qaza||{},zikrSync:2})}); } catch {}
      }
      fetchPersonalEntries();
    }
    document.addEventListener('click',event=>{
      const trigger=event.target.closest('[data-auth]'); if(trigger){ event.preventDefault(); openAuth(trigger.dataset.auth); return; }
      if(event.target.closest('[data-close-auth]')) closeSheet('auth-sheet');
    });
    renderGuestGates();
    document.querySelector('#name-form').addEventListener('submit',e=>{ e.preventDefault(); startApp(); });
    document.querySelector('#start-button').addEventListener('click',e=>{ e.preventDefault(); startApp(); });
    document.querySelector('#avatar').addEventListener('click',()=>showView('profile-view'));
    document.querySelector('#open-stats').addEventListener('click',()=>showView('stats-view'));
    document.querySelector('#profile-close').addEventListener('click',()=>showView('today-view'));
    function openExitConfirmation(){ openLayer(document.querySelector('#exit-confirmation')); }
    function closeExitConfirmation(){ closeLayer(document.querySelector('#exit-confirmation')); }
    async function logout(){
      clearTimeout(syncTimer);
      if(state.userId && await syncCurrentEntry()===false){ alert('Не удалось сохранить прогресс. Проверьте подключение и попробуйте выйти ещё раз.'); return; }
      if(state.token) apiFetch(apiUrl('auth/session'),{method:'DELETE'}).catch(()=>{});
      clearSession();
    }
    function clearSession(){
      clearTimeout(syncTimer);
      try { localStorage.removeItem(storeKey); sessionStorage.removeItem(storeKey); } catch {}
      document.querySelector('.autosave')?.classList.remove('is-saving','is-offline');
      state=blankState(); entryDateKey=localDateKey();
      document.querySelector('#name-form').reset(); document.querySelector('#login-form').reset();
      showView('today-view'); showCabinet();
    }
    /* Shown when the server no longer accepts the session (or the account was
       created before sessions existed). Today's marks are already on the server. */
    function requireRelogin(message='Сессия истекла. Войдите снова.'){
      const login=state.login||'';
      clearSession();
      openAuth('login',{login,message});
    }
    document.addEventListener('click',event=>{ if(event.target.closest('#logout-button')) openExitConfirmation(); });
    document.querySelector('#exit-cancel').addEventListener('click',closeExitConfirmation);
    document.querySelector('#exit-approve').addEventListener('click',async()=>{ closeExitConfirmation(); await logout(); });
    /* The whole row under the calendar opens the achievements; a guest gets the profile page. */
    document.querySelector('.at-strip').addEventListener('click',event=>{ if(event.target.closest('#open-achievements')) return; document.querySelector('#open-achievements').click(); });
    document.querySelector('.at-strip').addEventListener('keydown',event=>{ if(event.key==='Enter'||event.key===' '){ event.preventDefault(); document.querySelector('#open-achievements').click(); } });
    document.querySelector('#open-achievements').addEventListener('click',event=>{ event.stopImmediatePropagation(); if(isGuest()){ showView('profile-view'); return; } openProfileScreen('achievements'); });
    document.querySelector('#open-achievements').addEventListener('click',()=>{ showView('profile-view'); setTimeout(()=>document.querySelector('#profile-achievements')?.scrollIntoView({behavior:'smooth',block:'start'}),40); });
    document.querySelector('#show-login').addEventListener('click',()=>{ setAuthMode('login'); document.querySelector('#login-input').focus(); });
    document.querySelector('#show-register').addEventListener('click',()=>{ setAuthMode('signup'); document.querySelector('#name-input').focus(); });
    document.querySelector('#login-form').addEventListener('submit',async e=>{
      e.preventDefault(); const form=document.querySelector('#login-form'), loginInput=document.querySelector('#login-input'), passwordInput=document.querySelector('#password-input'), login=loginInput.value.trim().replace(/\s+/g,' ').toLocaleLowerCase(), password=passwordInput.value; clearAuthError(form);
      if(!login){ showAuthError(form,loginInput,'Введите имя, указанное при регистрации.'); return; }
      if(!password){ showAuthError(form,passwordInput,'Введите пароль.'); return; }
      try {
        const response=await apiFetch(apiUrl('auth/login'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({login,password})});
        if(!response.ok){ const data=await response.json().catch(()=>({})); throw new Error(data.error||'Неверное имя или пароль.'); }
        const user=await response.json();
        const guestToday=!state.userId&&entryDateKey===todayKey&&entryHasProgress({tasks:state.tasks,qaza:state.qaza})?{tasks:copyEntry(state.tasks),qaza:copyEntry(state.qaza),zikr:state.zikrPending?.[todayKey]}:null;
        state={...blankState(),name:user.displayName,login:user.login,userId:user.id,token:user.token,rememberMe:document.querySelector('#login-remember').checked};
        restoreCelebrations();
        /* Nothing is sent until the profile's own day is known, so an empty phone cannot overwrite it. */
        showCabinet(); save(); clearTimeout(syncTimer); finishAuth(); refreshVisibleLeaderboards(); loadPushStatus();
        fetchPersonalEntries(true).then(restored=>{
          document.querySelector('.autosave')?.classList.remove('is-saving');
          if(restored||!guestToday||!state.statsOnline) return;
          state.tasks=guestToday.tasks; state.qaza=guestToday.qaza; if(guestToday.zikr) state.zikrPending={[todayKey]:guestToday.zikr};
          save(); render(); scheduleZikrFlush(0);
        });
      } catch (error) { showAuthError(form,passwordInput,error.message||'Не удалось войти. Проверьте подключение.'); }
    });
    function renameCount(){ document.querySelector('#rename-count').textContent=`${document.querySelector('#rename-input').value.length} / 24`; }
    document.querySelector('#rename-input').addEventListener('input',renameCount);
    function openRenameSheet(){ const input=document.querySelector('#rename-input'); input.value=state.name||''; renameCount(); document.querySelector('#rename-error').textContent=''; openSheet('rename-sheet'); }
    document.querySelector('#profile-edit').addEventListener('click',openRenameSheet);
    document.addEventListener('click',event=>{ if(event.target.closest('[data-close-rename]')) closeSheet('rename-sheet'); });
    /* Checks and saves a new name; returns the error text, or an empty string when saved. */
    async function saveName(value){
      const name=String(value||'').trim().replace(/\s+/g,' ');
      if(name.length<2) return 'Введите имя минимум из 2 символов.';
      if(name.length>24) return 'Имя не должно быть длиннее 24 символов.';
      if(!/^[\p{L}\p{M}][\p{L}\p{M}\s'-]*$/u.test(name)) return 'Используйте только буквы, пробел, дефис или апостроф.';
      try {
        if(state.userId&&state.login){ const response=await apiFetch(apiUrl(`users/${state.userId}`),{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({displayName:name,login:state.login})}); if(!response.ok) throw new Error('rename_failed'); }
        state.name=name; save(); render(); return '';
      } catch { return 'Не удалось сохранить имя. Проверьте подключение и попробуйте снова.'; }
    }
    document.querySelector('#rename-form').addEventListener('submit',async event=>{
      event.preventDefault(); const input=document.querySelector('#rename-input'), error=document.querySelector('#rename-error'), button=document.querySelector('#rename-save');
      button.disabled=true; button.textContent='Сохраняем…';
      const message=await saveName(input.value);
      error.textContent=message; button.disabled=false; button.textContent='Сохранить';
      if(message) input.focus(); else closeSheet('rename-sheet');
    });
    function pushKeyBytes(value){
      const padded=value.replace(/-/g,'+').replace(/_/g,'/')+'='.repeat((4-value.length%4)%4);
      const binary=atob(padded); return Uint8Array.from(binary,char=>char.charCodeAt(0));
    }
    let serverVapidKey='';
    async function getVapidPublicKey(){
      if(serverVapidKey) return serverVapidKey;
      const response=await apiFetch(apiUrl('push/public-key'));
      if(!response.ok) throw new Error('push_server_unavailable');
      const data=await response.json();
      if(!data.publicKey) throw new Error('push_key_missing');
      serverVapidKey=data.publicKey; return serverVapidKey;
    }
    function renderReminderTimes(){
      const settings=reminderSettings(), cards=[...document.querySelectorAll('.reminder-time')];
      cards.forEach((card,index)=>{ const slot=settings.slots[index]; if(!slot) return; card.querySelector('span').textContent=slot.time; card.querySelector('small').textContent=slot.enabled?slot.label:'Выключено'; card.style.opacity=slot.enabled?'1':'.45'; });
    }
    function ensureReminderSettingsSheet(){
      let sheet=document.querySelector('#reminder-settings-sheet'); if(sheet) return sheet;
      sheet=document.createElement('div'); sheet.id='reminder-settings-sheet'; sheet.className='calendar-sheet'; sheet.hidden=true;
      sheet.innerHTML='<button class="calendar-sheet-backdrop" data-close-reminder-settings aria-label="Закрыть"></button><section class="calendar-sheet-panel" aria-modal="true" role="dialog"><div class="sheet-grabber"></div><div class="sheet-header"><h2>Напоминания</h2><button class="sheet-close" data-close-reminder-settings aria-label="Закрыть"><svg class="lucide" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button></div><form id="reminder-settings-form" class="reminder-settings-form"></form></section>';
      document.body.append(sheet);
      document.addEventListener('click',event=>{ if(event.target.closest('[data-close-reminder-settings]')) closeSheet('reminder-settings-sheet'); });
      return sheet;
    }
    function openReminderSettings(){
      ensureReminderSettingsSheet(); const settings=reminderSettings(), form=document.querySelector('#reminder-settings-form');
      form.innerHTML=`${settings.slots.map(slot=>`<div class="reminder-setting-row"><div><b>${slot.label}</b><small>Отдельный сигнал</small></div><div class="reminder-setting-controls"><label><input type="checkbox" data-slot-enabled="${slot.id}" ${slot.enabled?'checked':''}> Вкл.</label><input class="reminder-time-input" type="text" inputmode="numeric" maxlength="5" data-slot-time="${slot.id}" value="${slot.time}" aria-label="Время: ${slot.label}"></div></div>`).join('')}<div class="reminder-setting-row reminder-quiet-row"><div><b>Не беспокоить</b><small>В это время сигналы не приходят</small><div class="quiet-times"><input class="reminder-time-input" id="quiet-start" type="text" inputmode="numeric" maxlength="5" value="${settings.quiet.start}"><span>до</span><input class="reminder-time-input" id="quiet-end" type="text" inputmode="numeric" maxlength="5" value="${settings.quiet.end}"></div></div><label><input id="quiet-enabled" type="checkbox" ${settings.quiet.enabled?'checked':''}> Вкл.</label></div><button class="continue" type="submit">Сохранить настройки</button>`;
      form.onsubmit=async event=>{ event.preventDefault(); const validTime=value=>/^([01]\d|2[0-3]):[0-5]\d$/.test(value); const next=reminderSettings(); next.slots.forEach(slot=>{ slot.enabled=!!form.querySelector(`[data-slot-enabled="${slot.id}"]`)?.checked; slot.time=form.querySelector(`[data-slot-time="${slot.id}"]`)?.value.trim()||slot.time; }); next.quiet={enabled:!!form.querySelector('#quiet-enabled').checked,start:form.querySelector('#quiet-start').value.trim()||'22:00',end:form.querySelector('#quiet-end').value.trim()||'08:00'}; if(!next.slots.every(slot=>validTime(slot.time))||!validTime(next.quiet.start)||!validTime(next.quiet.end)){ alert('Введите время в формате чч:мм, например 08:30.'); return; } state.reminderSettings=next; save(); renderReminderTimes(); try { await savePushSettings(); closeSheet('reminder-settings-sheet'); } catch { alert('Настройки сохранены на устройстве, но не отправлены на сервер. Проверьте подключение.'); } };
      openSheet('reminder-settings-sheet');
    }
    async function savePushSettings(){
      if(!state.pushEnabled||!state.userId||!state.login||!('serviceWorker' in navigator)) return;
      state.reminderSettings=reminderSettings(); save();
      const registration=await navigator.serviceWorker.ready, subscription=await registration.pushManager.getSubscription(); if(!subscription) return;
      await apiFetch(apiUrl('push/subscriptions'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,login:state.login,subscription:subscription.toJSON(),timezone:Intl.DateTimeFormat().resolvedOptions().timeZone||'Asia/Bishkek',settings:state.reminderSettings})});
    }
    /* A new city moves prayer notifications to its times: the server is told right away. */
    window.chetelePlaceChanged=()=>{ if(state.pushEnabled&&reminderSettings().prayer.enabled) savePushSettings().catch(()=>{}); };
    function setReminderUi(){
      const button=document.querySelector('#reminder-button'), status=document.querySelector('#reminder-state'), detail=document.querySelector('#reminder-detail');
      let settingsButton=document.querySelector('#reminder-settings-button'); if(!settingsButton){ settingsButton=document.createElement('button'); settingsButton.id='reminder-settings-button'; settingsButton.className='reminder-settings-button'; settingsButton.type='button'; settingsButton.textContent='Настроить'; settingsButton.addEventListener('click',openReminderSettings); button.before(settingsButton); }
      const enabled=!!state.pushEnabled;
      button.classList.toggle('active',enabled); button.setAttribute('aria-pressed',String(enabled)); button.setAttribute('aria-label',enabled?'Отключить напоминания':'Включить напоминания');
      status.textContent=enabled?'Напоминания бережно ведут вас весь день':'Три бережных сигнала в течение дня';
      const active=reminderSettings().slots.filter(slot=>slot.enabled).map(slot=>slot.time).join(', ');
      detail.textContent=enabled?`Включено: ${active||'нет активных сигналов'}. Нажмите «Настроить», чтобы изменить.`:'Включите — и настройте удобные сигналы.';
      renderReminderTimes();
    }
    async function loadPushStatus(){
      if(!state.userId || !state.login){ setReminderUi(); return; }
      try {
        const response=await apiFetch(`${apiUrl('push/status')}&userId=${encodeURIComponent(state.userId)}&login=${encodeURIComponent(state.login)}`);
        if(!response.ok) throw new Error('status_unavailable');
        const data=await response.json(); state.pushEnabled=!!data.enabled; if(data.settings) state.reminderSettings=data.settings; save(); setReminderUi();
      } catch { setReminderUi(); }
    }
    async function subscribeToPush(){
      if(!('serviceWorker' in navigator) || !('PushManager' in window)) throw new Error('push_unsupported');
      if(!state.userId && !await ensureRemoteUser()) throw new Error('account_unavailable');
      const publicKey=await getVapidPublicKey();
      const registration=await navigator.serviceWorker.ready;
      let subscription=await registration.pushManager.getSubscription();
      if(!subscription) subscription=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:pushKeyBytes(publicKey)});
      const saveResponse=await apiFetch(apiUrl('push/subscriptions'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,login:state.login,subscription:subscription.toJSON(),timezone:Intl.DateTimeFormat().resolvedOptions().timeZone||'Asia/Bishkek',settings:state.reminderSettings})});
      if(!saveResponse.ok) throw new Error('subscription_save_failed');
      const testResponse=await apiFetch(apiUrl('push/test'),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,login:state.login})});
      if(!testResponse.ok){ const details=await testResponse.json().catch(()=>({})); return {testError:`${details.reason||'test_failed'}:${details.status||testResponse.status}`}; }
      return {testError:''};
    }
    async function disablePush(){
      const registration=await navigator.serviceWorker.ready;
      const subscription=await registration.pushManager.getSubscription();
      const response=await apiFetch(apiUrl('push/subscriptions'),{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({userId:state.userId,login:state.login,endpoint:subscription?.endpoint||''})});
      if(!response.ok) throw new Error('disable_failed');
      state.pushEnabled=false; save(); setReminderUi();
    }
    document.querySelector('#reminder-button').addEventListener('click',async()=>{
      const stateText=document.querySelector('#reminder-state'), detail=document.querySelector('#reminder-detail'), button=document.querySelector('#reminder-button');
      if(isGuest()){ openAuth('signup'); return; }
      if(!('Notification' in window) || !('PushManager' in window)){ stateText.textContent='Добавьте приложение на главный экран и откройте его оттуда, чтобы включить уведомления.'; return; }
      button.disabled=true;
      if(state.pushEnabled){
        detail.textContent='Отключаем напоминания…';
        try { await disablePush(); }
        catch { detail.textContent='Не удалось отключить напоминания. Попробуйте ещё раз.'; }
        finally { button.disabled=false; }
        return;
      }
      const permission=await Notification.requestPermission();
      if(permission!=='granted'){ stateText.textContent='Разрешение на уведомления не получено в настройках устройства.'; button.disabled=false; return; }
      stateText.textContent='Подключаем ваш ритм…'; detail.textContent='Сейчас отправим тестовое уведомление.';
      try {
        const result=await subscribeToPush();
        state.pushEnabled=true; save(); setReminderUi();
        if(result?.testError){
          stateText.textContent='Подписка включена, но сервер пока не может отправить уведомление.';
          detail.textContent=String(result.testError).startsWith('ecdh_missing')?'На сервере используется PHP 7.2. Для уведомлений переключите сайт на PHP 8.1 или новее.':'Подписка сохранена, но тест уведомления не был доставлен.';
        } else detail.textContent='Готово. Тестовое уведомление уже отправлено.';
      } catch (error) {
        const code=String(error.message||'');
        const message={push_unsupported:'На этом устройстве Web Push недоступен.',account_unavailable:'Не удалось создать кабинет. Проверьте подключение.',push_server_unavailable:'Сервер Web Push пока недоступен.',push_key_missing:'На сервере нет VAPID-ключа.',subscription_save_failed:'Не удалось сохранить подписку.'}[code] || (code.startsWith('curl_missing') ? 'На хостинге выключено PHP-расширение cURL.' : code.startsWith('openssl_missing') ? 'На хостинге выключено PHP-расширение OpenSSL.' : code.startsWith('ecdh_missing') ? 'Текущая версия PHP не поддерживает шифрование Web Push: включите PHP 8.1.' : code.startsWith('push_gateway_rejected') ? `Сервис уведомлений отклонил запрос (HTTP ${code.split(':')[1]}).` : code.startsWith('server_push_error') ? 'Сервер не смог зашифровать Web Push. Включите PHP 8.1, OpenSSL и cURL.' : 'Подписка сохранена, но серверный тест не отправлен.');
        stateText.textContent=message; detail.textContent='Проверьте разрешение на уведомления и подключение к серверу.';
      } finally { button.disabled=false; }
    });
    if(state.userId && !state.token) requireRelogin('Мы усилили защиту аккаунтов. Пожалуйста, войдите снова.');
    else {
      showCabinet();
      const opened=new URLSearchParams(location.search).get('open');
      if(opened){ history.replaceState(null,'',location.pathname); openTarget(opened); }
      /* A brand-new guest sees the short introduction once; anyone with a profile or marks skips it. */
      if(window.ChetOnboarding&&!window.ChetOnboarding.seen()){ if(!opened&&isGuest()&&!Object.keys(state.history||{}).length&&!completed()) window.ChetOnboarding.show({onLogin:()=>openAuth('login')}); else window.ChetOnboarding.done(); }
      if(state.userId){
        refreshVisibleLeaderboards();
        fetchPersonalEntries(true).then(restored=>{ if(!restored){ resetIfNewDay(); queueSync(); } });
        loadPushStatus();
      } else setReminderUi();
    }
    window.addEventListener('online',()=>{ if(state.userId){ fetchPersonalEntries(true); refreshVisibleLeaderboards(); fetchEvents(); } });
    document.addEventListener('visibilitychange',()=>{ if(!state.userId) return; if(document.visibilityState==='hidden') syncCurrentEntry(); else { fetchPersonalEntries(true); refreshVisibleLeaderboards(); fetchEvents(); } });
    scheduleMidnightReset();
    scheduleZikrFlush(1500);
    if('serviceWorker' in navigator) window.addEventListener('load',()=>navigator.serviceWorker.register('/sw.js').then(()=>getVapidPublicKey().catch(()=>{})).catch(()=>{}));
    /* A Home Screen app can stay open for days with the old page in memory. Returning to it
       checks for a new version, and once the new service worker takes over the page reloads
       itself, so a deploy reaches the phone without closing the app by hand. */
    if('serviceWorker' in navigator){
      const hadController=!!navigator.serviceWorker.controller; let reloading=false;
      navigator.serviceWorker.addEventListener('controllerchange',async()=>{
        if(!hadController||reloading) return;
        /* A page that already came from the network is the new version: nothing to reload. */
        const build=document.querySelector('meta[name="chetele-build"]')?.content;
        try { if(build&&(await caches.keys()).includes(build)) return; } catch {}
        if(reloading) return; reloading=true;
        const cover=document.querySelector('#app-preloader'); if(cover){ cover.hidden=false; cover.classList.remove('is-hidden'); }
        /* The day is sent before the reload (keepalive lets it finish even if the page goes first);
           a weak network gets at most 6 s, so the splash never hangs. */
        const synced=state.userId?syncCurrentEntry().catch(()=>{}):null;
        await Promise.race([synced,new Promise(resolve=>setTimeout(resolve,6000))]);
        location.reload();
      });
      document.addEventListener('visibilitychange',()=>{ if(document.visibilityState==='visible') navigator.serviceWorker.getRegistration().then(registration=>registration?.update()).catch(()=>{}); });
    }
  
