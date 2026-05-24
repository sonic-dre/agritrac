<!DOCTYPE html>
<html lang="en" id="htmlRoot">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AgriTrack – HQ Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Mono:wght@400;500&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0d1117;--sur:#161b22;--sur2:#1c2333;--sur3:#252d38;
  --bdr:rgba(255,255,255,0.07);--bdr2:rgba(255,255,255,0.13);
  --txt:#e6edf3;--txt2:#adbac7;--mut:#6e7681;
  --acc:#3fb950;--adim:rgba(63,185,80,0.1);--abdr:rgba(63,185,80,0.22);
  --ora:#f0883e;--odim:rgba(240,136,62,0.1);
  --blu:#58a6ff;--bdim:rgba(88,166,255,0.08);--bbdr:rgba(88,166,255,0.2);
  --red:#f85149;--rdim:rgba(248,81,73,0.09);
  --gld:#d29922;--gdim:rgba(210,153,34,0.09);
  --pur:#bc8cff;--pdim:rgba(188,140,255,0.09);
  --sw:220px;--hh:56px;--fb:'Nunito',sans-serif;--fd:'Syne',sans-serif;--fm:'DM Mono',monospace
}
.lm{
  --bg:#f0f2f5;--sur:#fff;--sur2:#f5f6f8;--sur3:#ebedf0;
  --bdr:rgba(0,0,0,0.06);--bdr2:rgba(0,0,0,0.12);
  --txt:#111827;--txt2:#374151;--mut:#6b7280;
  --acc:#15803d;--adim:rgba(21,128,61,0.08);--abdr:rgba(21,128,61,0.2);
  --ora:#c05621;--odim:rgba(192,86,33,0.08);
  --blu:#1d4ed8;--bdim:rgba(29,78,216,0.06);--bbdr:rgba(29,78,216,0.2);
  --red:#b91c1c;--rdim:rgba(185,28,28,0.07);
  --gld:#92400e;--gdim:rgba(146,64,14,0.08);
  --pur:#6d28d9;--pdim:rgba(109,40,217,0.07)
}
html,body{width:100%;height:100%;background:var(--bg);font-family:var(--fb);color:var(--txt);transition:background .3s,color .3s;overflow:hidden}
.app{display:flex;height:100vh}

/* ── SIDEBAR ── */
.sb{width:var(--sw);background:var(--sur);border-right:1px solid var(--bdr2);display:flex;flex-direction:column;flex-shrink:0;transition:width .25s}
.sbb{padding:0 18px;height:var(--hh);display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--bdr)}
.sbico{width:34px;height:34px;border-radius:10px;background:var(--acc);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.sbname{font-family:var(--fd);font-weight:800;font-size:16px;color:var(--txt);letter-spacing:-.3px}
.sbname span{color:var(--acc)}
.sbsub{font-size:9px;color:var(--mut);font-family:var(--fm)}
.sbnav{flex:1;padding:12px 10px;overflow-y:auto;scrollbar-width:none}
.sbnav::-webkit-scrollbar{display:none}
.ngl{font-size:8px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:1.2px;padding:8px 8px 4px;font-family:var(--fm)}
.ni{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;cursor:pointer;margin-bottom:2px;transition:background .15s;color:var(--mut);font-size:13px;font-weight:600}
.ni:hover{background:var(--sur2);color:var(--txt)}
.ni.act{background:var(--adim);color:var(--acc);border:1px solid var(--abdr)}
.niico{font-size:17px;flex-shrink:0}
.nbdg{margin-left:auto;font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px;font-family:var(--fm)}
.nbd-r{background:var(--rdim);color:var(--red)}
.nbd-g{background:var(--adim);color:var(--acc)}
.nbd-b{background:var(--bdim);color:var(--blu)}
.sbft{padding:12px 10px;border-top:1px solid var(--bdr)}
.agrow{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;background:var(--sur2);cursor:pointer}
.agav{width:30px;height:30px;border-radius:50%;background:var(--bdim);border:1px solid var(--bbdr);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--blu);flex-shrink:0}
.agnm{font-size:12px;font-weight:700;color:var(--txt)}
.agrl{font-size:9px;color:var(--mut)}
.agrl-a{color:var(--acc)}.agrl-m{color:var(--blu)}.agrl-v{color:var(--mut)}
.sb-logout{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:7px;padding:6px 10px;border-radius:7px;font-size:11px;font-weight:600;color:var(--mut);cursor:pointer;border:none;background:none;width:100%;font-family:var(--fb);transition:background .15s,color .15s}
.sb-logout:hover{background:var(--rdim);color:var(--red)}

/* ── MAIN ── */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.hdr{height:var(--hh);background:var(--sur);border-bottom:1px solid var(--bdr2);display:flex;align-items:center;gap:10px;padding:0 20px;flex-shrink:0}
.hdrtit{font-family:var(--fd);font-size:15px;font-weight:700;color:var(--txt)}
.hdrsp{flex:1}
.hdrdt{font-size:11px;color:var(--mut);font-family:var(--fm)}
.hbtn{display:flex;align-items:center;gap:5px;padding:6px 13px;border-radius:8px;font-family:var(--fb);font-size:12px;font-weight:700;cursor:pointer;border:none;transition:opacity .2s,transform .15s;text-decoration:none}
.hbtn:active{transform:scale(.97)}
.hb-p{background:var(--acc);color:#fff}
.hb-s{background:var(--sur2);border:1px solid var(--bdr2);color:var(--txt)}
.hb-g{background:transparent;border:1px solid var(--bdr2);color:var(--txt)}
.hb-m{background:var(--pdim);border:1px solid rgba(188,140,255,.25);color:var(--pur)}
.syncst{display:flex;align-items:center;gap:6px;padding:5px 11px;border-radius:20px;font-size:10px;font-weight:700;font-family:var(--fm);cursor:pointer;border:none}
.ss-off{background:var(--rdim);color:var(--red);border:1px solid rgba(248,81,73,.2)}
.ss-on{background:var(--adim);color:var(--acc);border:1px solid var(--abdr)}
.sdot{width:6px;height:6px;border-radius:50%}
.sdot-on{background:var(--acc);animation:blink 2s infinite}
.sdot-off{background:var(--red)}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

/* ── PAGES ── */
.pgcont{flex:1;overflow:hidden;position:relative}
.pg{position:absolute;inset:0;overflow-y:auto;scrollbar-width:thin;padding:18px 20px 24px;opacity:0;pointer-events:none;transition:opacity .2s;display:none}
.pg.act{opacity:1;pointer-events:all;display:block}

/* ── KPI GRID ── */
.kgrid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:14px}
.kpi{background:var(--sur);border:1px solid var(--bdr2);border-radius:12px;padding:13px 15px;position:relative;overflow:hidden}
.kacc{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:12px 0 0 12px}
.klbl{font-size:9px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm)}
.kval{font-family:var(--fd);font-size:22px;font-weight:800;color:var(--txt);margin:4px 0 3px;line-height:1}
.kbdg{display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px}
.kb-g{background:var(--adim);color:var(--acc)}
.kb-r{background:var(--rdim);color:var(--red)}
.kb-b{background:var(--bdim);color:var(--blu)}
.kb-gd{background:var(--gdim);color:var(--gld)}

/* ── CARD ── */
.card{background:var(--sur);border:1px solid var(--bdr2);border-radius:12px;overflow:hidden}
.ch{padding:11px 15px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between}
.ct{font-family:var(--fd);font-size:13px;font-weight:700;color:var(--txt)}
.cb{font-size:9px;font-weight:700;padding:2px 8px;border-radius:20px;font-family:var(--fm)}
.cb-g{background:var(--adim);color:var(--acc)}
.cb-r{background:var(--rdim);color:var(--red)}
.cb-b{background:var(--bdim);color:var(--blu)}
.cb-gd{background:var(--gdim);color:var(--gld)}
.cb-p{background:var(--pdim);color:var(--pur)}
.cbody{padding:12px 15px}

/* ── GRIDS ── */
.pgwrap{max-width:1380px;margin:0 auto;width:100%}
.g21{display:grid;grid-template-columns:1fr 300px;gap:12px;margin-bottom:14px;align-items:start}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px;align-items:start}
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:14px;align-items:start}
.mb14{margin-bottom:14px}

/* ── CHART AREAS ── */
.cwrap{padding:10px 15px 12px;position:relative;width:100%}
.cleg{display:flex;gap:14px;padding:8px 15px 10px;border-top:1px solid var(--bdr)}
.cli{display:flex;align-items:center;gap:5px;font-size:10px;color:var(--mut)}
.cld{width:8px;height:8px;border-radius:50%;flex-shrink:0}
canvas{display:block;width:100%!important}

/* ── PRODUCE ROWS (price tracker) ── */
.prow{display:flex;align-items:center;gap:10px;padding:9px 15px;border-bottom:1px solid var(--bdr);transition:background .12s;cursor:pointer}
.prow:last-child{border-bottom:none}
.prow:hover{background:var(--sur2)}
.prdot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.prnm{flex:1;font-size:12px;font-weight:600;color:var(--txt)}
.prloc{font-size:10px;color:var(--mut);width:80px}
.prpr{font-family:var(--fm);font-size:11px;color:var(--txt);width:90px}
.prch{font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;width:60px;text-align:center}
.up{background:var(--adim);color:var(--acc)}
.dn{background:var(--rdim);color:var(--red)}
.prac{font-size:10px;font-weight:700;width:70px;text-align:right}
.buy{color:var(--acc)}.hold{color:var(--mut)}.sell{color:var(--red)}

/* ── ROUTE ITEMS ── */
.ri{display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid var(--bdr);position:relative}
.ri:last-child{border-bottom:none}
.ri:not(:last-child)::before{content:'';position:absolute;left:11px;top:24px;width:1px;height:calc(100% - 4px);background:var(--bdr2)}
.rind{width:22px;height:22px;border-radius:50%;background:var(--sur2);border:2px solid var(--bdr2);display:flex;align-items:center;justify-content:center;font-size:8px;font-weight:700;color:var(--mut);flex-shrink:0;z-index:1}
.rind.act{background:var(--acc);border-color:var(--acc);color:#fff}
.rind.wrn{background:var(--gdim);border-color:var(--gld);color:var(--gld)}
.rinf{flex:1}
.rinm{font-size:12px;font-weight:700;color:var(--txt)}
.risb{font-size:10px;color:var(--mut);margin-top:2px}
.rist{font-size:10px;color:var(--acc);font-weight:600;font-family:var(--fm);margin-top:2px}
.riw{font-size:10px;color:var(--gld);font-weight:600;margin-top:2px}
.rirg{text-align:right}
.riday{font-size:9px;color:var(--mut);font-family:var(--fm)}

/* ── EXPENSE ROWS ── */
.exrow{display:flex;align-items:center;gap:10px;padding:8px 15px;border-bottom:1px solid var(--bdr)}
.exrow:last-child{border-bottom:none}
.exico{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.exlbl{flex:1}
.exnm{font-size:12px;font-weight:600;color:var(--txt)}
.exsb{font-size:10px;color:var(--mut);margin-top:1px}
.exbw{width:80px}
.exbg{height:4px;background:var(--sur3);border-radius:2px;overflow:hidden}
.exbf{height:100%;border-radius:2px}
.expc{font-size:8px;color:var(--mut);margin-top:2px;font-family:var(--fm);text-align:right}
.examt{font-family:var(--fm);font-size:12px;font-weight:600;color:var(--txt);text-align:right}

/* ── SYNC QUEUE ── */
.sqit{display:flex;align-items:center;gap:10px;padding:9px 15px;border-bottom:1px solid var(--bdr)}
.sqit:last-child{border-bottom:none}
.sqdw{width:10px}
.sqd{width:8px;height:8px;border-radius:50%}
.sqd-p{background:var(--gld)}
.sqd-s{background:var(--acc)}
.sqd-f{background:var(--red)}
.sqinf{flex:1}
.sqnm{font-size:12px;font-weight:600;color:var(--txt)}
.sqsb{font-size:10px;color:var(--mut);margin-top:1px}
.sqtm{font-size:9px;color:var(--mut);font-family:var(--fm)}

/* ── MINI STATS ── */
.msr{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,240px));gap:10px;margin-bottom:14px}
.ms{background:var(--sur);border:1px solid var(--bdr2);border-radius:12px;padding:14px 16px;position:relative;overflow:hidden}
.msl{font-size:9px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.9px;font-family:var(--fm)}
.msv{font-family:var(--fd);font-size:22px;font-weight:800;color:var(--txt);margin-top:5px;margin-bottom:3px;line-height:1}
.mss{font-size:10px;color:var(--mut)}

/* ── TABLE ── */
.dtbl{width:100%;border-collapse:collapse;font-size:12px}
.dtbl th{text-align:left;padding:8px 12px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.8px;border-bottom:1px solid var(--bdr2);font-family:var(--fm);font-size:9px;background:var(--sur2)}
.dtbl td{padding:9px 12px;border-bottom:1px solid var(--bdr);color:var(--txt)}
.dtbl tr:hover td{background:var(--sur2)}
.dtbl tr:last-child td{border-bottom:none}
.tm{font-family:var(--fm)}
.tg{color:var(--acc)}.tr{color:var(--red)}
.spill{display:inline-block;font-size:9px;font-weight:700;padding:2px 8px;border-radius:20px;font-family:var(--fm)}
.sp-sy{background:var(--adim);color:var(--acc)}
.sp-pe{background:var(--gdim);color:var(--gld)}
.sp-of{background:var(--rdim);color:var(--red)}

/* ── FORECAST CARDS ── */
.fcard{background:var(--sur);border:1px solid var(--bdr2);border-radius:12px;padding:14px}
.fclbl{font-size:10px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm);margin-bottom:6px}
.fcval{font-family:var(--fd);font-size:26px;font-weight:800;margin-bottom:6px}
.fctxt{font-size:11px;color:var(--mut);line-height:1.5}

/* ── LOADING SKELETON ── */
.skel{background:var(--sur2);border-radius:6px;animation:sk 1.5s infinite}
@keyframes sk{0%,100%{opacity:.4}50%{opacity:.8}}

/* ── MODALS ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:200;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .2s;backdrop-filter:blur(2px)}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal-box{background:var(--sur);border:1px solid var(--bdr2);border-radius:14px;width:500px;max-width:calc(100vw - 32px);max-height:88vh;overflow-y:auto;transform:translateY(18px);transition:transform .2s;scrollbar-width:thin}
.modal-overlay.open .modal-box{transform:translateY(0)}
.mh{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--bdr);position:sticky;top:0;background:var(--sur);z-index:1}
.mt{font-family:var(--fd);font-size:14px;font-weight:700;color:var(--txt)}
.mclose{background:none;border:none;color:var(--mut);font-size:18px;cursor:pointer;line-height:1;padding:2px 6px;border-radius:6px;transition:background .15s}
.mclose:hover{background:var(--sur2);color:var(--txt)}
.mbody{padding:16px 18px}
.mft{padding:12px 18px;border-top:1px solid var(--bdr);display:flex;gap:8px;justify-content:flex-end;position:sticky;bottom:0;background:var(--sur)}
.fgrp{margin-bottom:13px}
.flbl{font-size:10px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm);margin-bottom:5px}
.finp{width:100%;padding:8px 11px;background:var(--sur2);border:1px solid var(--bdr2);border-radius:8px;color:var(--txt);font-family:var(--fb);font-size:13px;outline:none;transition:border-color .15s;appearance:none}
.finp:focus{border-color:var(--acc)}
.finp option{background:var(--sur2)}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.frow3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.ferr{font-size:10px;color:var(--red);margin-top:3px;min-height:14px;font-family:var(--fm)}
.fcheck-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:10px;background:var(--sur2);border:1px solid var(--bdr2);border-radius:8px}
.fcheck{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt);cursor:pointer;padding:4px 6px;border-radius:6px;transition:background .12s}
.fcheck:hover{background:var(--sur3)}
.fcheck input[type=checkbox]{accent-color:var(--acc);width:14px;height:14px;cursor:pointer}
.fdivider{height:1px;background:var(--bdr);margin:14px 0}
.fact-btns{display:flex;gap:5px}
.abtn{display:inline-flex;align-items:center;gap:4px;padding:4px 9px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;border:none;font-family:var(--fb);transition:opacity .15s}
.abtn:hover{opacity:.8}
.abtn-e{background:var(--bdim);color:var(--blu)}
.abtn-d{background:var(--rdim);color:var(--red)}
.abtn-u{background:var(--adim);color:var(--acc)}
/* ── MONEY + CURRENCY INLINE ── */
.mny-wrap{display:flex;gap:6px;align-items:stretch}
.mny-wrap .finp:not(.mny-cur){flex:1}
.mny-cur{width:80px!important;flex-shrink:0;padding-left:8px!important;padding-right:6px!important;font-family:var(--fm)!important;font-size:11px!important;font-weight:700!important;color:var(--acc)!important;background:var(--sur3)!important;border-color:var(--bdr2)!important;cursor:pointer}
.mny-cur:focus{border-color:var(--acc)!important}
/* ── QUICK-ADD ── */
.qa-toggle{font-size:10px;font-weight:700;color:var(--acc);cursor:pointer;margin-top:7px;padding:3px 0;display:inline-block;opacity:.75;transition:opacity .15s;font-family:var(--fm)}
.qa-toggle:hover{opacity:1}
.qa-panel{background:var(--sur2);border:1px solid var(--bdr2);border-radius:8px;padding:10px;margin-top:6px}
.qa-row{display:flex;gap:6px;align-items:center}
.qa-row .finp{margin-bottom:0}
/* ── TOAST ── */
.toast{position:fixed;bottom:20px;right:20px;background:var(--sur);border:1px solid var(--bdr2);border-radius:10px;padding:10px 18px;font-size:13px;font-weight:600;color:var(--txt);opacity:0;pointer-events:none;transition:opacity .3s,transform .3s;transform:translateY(10px);z-index:999}
.toast.show{opacity:1;transform:translateY(0)}

/* ── LEAFLET TOOLTIP ── */
.agri-tooltip{padding:10px 12px!important;border-radius:10px!important;border:1px solid rgba(0,0,0,0.08)!important;box-shadow:0 6px 20px rgba(0,0,0,0.13)!important;max-width:260px!important;pointer-events:none}
.agri-tooltip::before{display:none!important}
/* ── RESPONSIVE ── */
@media(max-width:1100px){.g21{grid-template-columns:1fr}.kgrid{grid-template-columns:repeat(3,1fr)}.g3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:800px){.sb{width:56px}.sbname,.sbsub,.ni span,.nbdg,.agnm,.agrl{display:none}.sbb{justify-content:center;padding:0}.ni{justify-content:center;padding:10px}.agrow{justify-content:center}}
</style>
</head>
<body>
<div class="app">

  {{-- SIDEBAR --}}
  <aside class="sb">
    <div class="sbb">
      <div class="sbico">🌿</div>
      <div>
        <div class="sbname">Agri<span>Track</span></div>
        <div class="sbsub">HQ DASHBOARD</div>
      </div>
    </div>
    <nav class="sbnav">
      <div class="ngl">Overview</div>
      <div class="ni act" id="sn-ov" onclick="gp('ov')"><i class="ti ti-layout-dashboard niico"></i><span>Overview</span></div>
      <div class="ni" id="sn-tr" onclick="gp('tr')"><i class="ti ti-truck niico"></i><span>Active Trips</span><span class="nbdg nbd-g" id="badge-tr">6</span></div>
      <div class="ngl">Market</div>
      <div class="ni" id="sn-pr" onclick="gp('pr')"><i class="ti ti-trending-up niico"></i><span>Price Tracker</span></div>
      <div class="ni" id="sn-fc" onclick="gp('fc')"><i class="ti ti-sparkles niico"></i><span>AI Forecast</span><span class="nbdg nbd-b">Live</span></div>
      <div class="ngl">Finance</div>
      <div class="ni" id="sn-ac" onclick="gp('ac')"><i class="ti ti-report-money niico"></i><span>Accounting</span></div>
      <div class="ni" id="sn-ex" onclick="gp('ex')"><i class="ti ti-cash niico"></i><span>Expenses</span></div>
      <div class="ni" id="sn-hi" onclick="gp('hi')"><i class="ti ti-history niico"></i><span>History</span></div>
      <div class="ngl">Catalogue</div>
      <div class="ni" id="sn-pu" onclick="gp('pu')"><i class="ti ti-plant niico"></i><span>Produce & Units</span></div>
      <div class="ngl">Operations</div>
      <div class="ni" id="sn-sy" onclick="gp('sy')"><i class="ti ti-refresh niico"></i><span>Sync Monitor</span><span class="nbdg nbd-r" id="badge-sy">{{ $syncCount }}</span></div>
      <div class="ni" id="sn-st" onclick="gp('st')"><i class="ti ti-package niico"></i><span>Stock</span></div>
      <div class="ni" id="sn-mp" onclick="gp('mp')"><i class="ti ti-map-pin niico"></i><span>Field Map</span></div>
      @if(auth()->user()->isManager())
      <div class="ngl">Admin</div>
      <div class="ni" id="sn-ma" onclick="gp('ma')"><i class="ti ti-device-mobile niico"></i><span>Mobile Agents</span></div>
      @if(auth()->user()->isAdmin())
      <div class="ni" id="sn-um" onclick="gp('um')"><i class="ti ti-users niico"></i><span>User Management</span></div>
      @endif
      @endif
    </nav>
    <div class="sbft">
      @php $u = auth()->user(); $rCls = match($u->role){ 'admin'=>'agrl-a','manager'=>'agrl-m',default=>'agrl-v' }; @endphp
      <div class="agrow">
        <div class="agav" style="background:var(--adim);border-color:var(--abdr);color:var(--acc)">
          {{ strtoupper(substr($u->name,0,1)) }}{{ strtoupper(substr(strstr($u->name,' '),1,1)) }}
        </div>
        <div>
          <div class="agnm">{{ $u->name }}</div>
          <div class="agrl {{ $rCls }}">{{ $u->roleLabel() }}</div>
        </div>
      </div>
      <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="sb-logout"><i class="ti ti-logout" style="font-size:13px"></i> <span>Sign Out</span></button>
      </form>
    </div>
  </aside>

  {{-- MAIN --}}
  <div class="main">
    <header class="hdr">
      <div class="hdrtit" id="ptit">Overview</div>
      <div class="hdrsp"></div>
      <div class="hdrdt" id="hdrdt"></div>
      <button class="syncst ss-on" id="syncel" onclick="toggleConn()">
        <div class="sdot sdot-on" id="sdot"></div>
        <span id="slbl">{{ $syncCount }} PENDING</span>
      </button>
      <button class="hbtn hb-s" onclick="exportPage()"><i class="ti ti-download" style="font-size:13px"></i> Export</button>
      <button class="hbtn hb-p" onclick="openModal('modal-trip');tripMode('create')"><i class="ti ti-plus" style="font-size:13px"></i> New Trip</button>
      <button class="hbtn hb-g" onclick="toggleTheme()"><i class="ti ti-sun" id="thico" style="font-size:14px"></i></button>
    </header>

    <div class="pgcont">
      @yield('content')
    </div>
  </div>

</div>
<div class="toast" id="toastEl"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let dark = true, online = true;
const pages  = ['ov','tr','pr','fc','ac','ex','hi','sy','st','pu','mp','ma','um'];
const titles = {ov:'Overview',tr:'Active Trips',pr:'Price Tracker',fc:'AI Forecast',ac:'Accounting',ex:'Expenses',hi:'Transaction History',sy:'Sync Monitor',st:'Stock',pu:'Produce & Units',mp:'Field Map',ma:'Mobile Agents',um:'User Management'};
const charts = {};
let pageCache = {};

function gp(n) {
  pages.forEach(p => {
    document.getElementById('pg-'+p)?.classList.remove('act');
    document.getElementById('sn-'+p)?.classList.remove('act');
  });
  document.getElementById('pg-'+n)?.classList.add('act');
  document.getElementById('sn-'+n)?.classList.add('act');
  document.getElementById('ptit').textContent = titles[n] || n;
  loadPage(n);
}

async function loadPage(n) {
  if (pageCache[n]) { renderCharts(n, pageCache[n]); return; }
  try {
    const res  = await fetch('/api/dashboard/' + n);
    const data = await res.json();
    pageCache[n] = data;
    renderPage(n, data);
  } catch(e) { console.warn('API error:', e); }
}

function toggleTheme() {
  dark = !dark;
  document.getElementById('htmlRoot').classList.toggle('lm', !dark);
  document.getElementById('thico').className = dark ? 'ti ti-sun' : 'ti ti-moon';
  Object.keys(charts).forEach(k => { charts[k]?.destroy(); delete charts[k]; });
  pageCache = {};
  loadPage(document.querySelector('.ni.act')?.id?.replace('sn-','') || 'ov');
}

function toggleConn() {
  online = !online;
  const el  = document.getElementById('syncel');
  const dot = document.getElementById('sdot');
  const lbl = document.getElementById('slbl');
  if (online) {
    el.className = 'syncst ss-on'; dot.className = 'sdot sdot-on';
    lbl.textContent = document.getElementById('badge-sy').textContent + ' PENDING';
    showToast('Connected — syncing...');
  } else {
    el.className = 'syncst ss-off'; dot.className = 'sdot sdot-off';
    lbl.textContent = 'OFFLINE'; showToast('Disconnected');
  }
}

let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toastEl');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

function gc(v) { return getComputedStyle(document.documentElement).getPropertyValue(v).trim(); }
function gridC() { return dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'; }
function txtC()  { return dark ? '#6e7681' : '#6b7280'; }

function mk(id, cfg) {
  charts[id]?.destroy();
  const el = document.getElementById(id);
  if (!el) return null;
  charts[id] = new Chart(el.getContext('2d'), cfg);
  return charts[id];
}

function lineOpts(yFmt) {
  return {
    responsive:true, maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{
      x:{ticks:{color:txtC(),font:{size:9,family:'DM Mono'},maxRotation:0,autoSkip:true,maxTicksLimit:8},grid:{color:gridC()}},
      y:{ticks:{color:txtC(),font:{size:9,family:'DM Mono'},callback:yFmt||undefined},grid:{color:gridC()},border:{display:false}}
    }
  };
}

function setDate() {
  const d = new Date();
  const dy = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  const mn = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  document.getElementById('hdrdt').textContent = dy[d.getDay()] + ', ' + d.getDate() + ' ' + mn[d.getMonth()] + ' ' + d.getFullYear() + ' · EAT';
}
setDate();

// Bootstrap first page
setTimeout(() => loadPage('ov'), 150);
</script>
@yield('scripts')
</body>
</html>
