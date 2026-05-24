@extends('layouts.app')

@section('content')

{{-- ══════════════════════════════════════════
     OVERVIEW
══════════════════════════════════════════ --}}
<div class="pg act" id="pg-ov">
<div class="pgwrap">
  {{-- KPI Cards --}}
  <div class="kgrid" id="kpi-grid">
    <div class="kpi"><div class="kacc" style="background:var(--acc)"></div><div class="klbl">Revenue MTD</div><div class="kval" id="kv-rev">—</div><span class="kbdg kb-g">▲ 18.4%</span></div>
    <div class="kpi"><div class="kacc" style="background:var(--ora)"></div><div class="klbl">Tonnage Bought</div><div class="kval" id="kv-ton">—</div><span class="kbdg kb-g">▲ 11.2%</span></div>
    <div class="kpi"><div class="kacc" style="background:var(--blu)"></div><div class="klbl">Net Profit MTD</div><div class="kval" id="kv-prf">—</div><span class="kbdg kb-b">▲ 22.7%</span></div>
    <div class="kpi"><div class="kacc" style="background:var(--gld)"></div><div class="klbl">Active Trips</div><div class="kval" id="kv-atr">—</div><span class="kbdg kb-gd" id="kv-atr-sub">3 offline</span></div>
    <div class="kpi"><div class="kacc" style="background:var(--red)"></div><div class="klbl">Pending Sync</div><div class="kval" id="kv-syn">{{ $syncCount }}</div><span class="kbdg kb-r" id="kv-syn-sub">4 agents</span></div>
  </div>

  <div class="g21">
    <div class="card">
      <div class="ch"><div class="ct">Revenue vs Cost vs Profit — Last 10 Trips</div><span class="cb cb-g">Live</span></div>
      <div class="cwrap"><canvas id="mainChart"></canvas></div>
      <div class="cleg">
        <div class="cli"><div class="cld" style="background:var(--acc)"></div>Revenue</div>
        <div class="cli"><div class="cld" style="background:var(--red)"></div>Cost</div>
        <div class="cli"><div class="cld" style="background:var(--blu)"></div>Profit</div>
      </div>
    </div>
    <div class="card">
      <div class="ch"><div class="ct">Active Routes</div><span class="cb cb-gd" id="routes-badge">— Active</span></div>
      <div class="cbody" style="padding:10px 12px" id="routes-body">
        <div class="ri"><div class="rind act">K</div><div class="rinf"><div class="rinm">Kampala (HQ)</div><div class="risb">Dispatch base · All routes origin</div></div></div>
        <div id="routes-list"></div>
      </div>
    </div>
  </div>

  <div class="g21">
    <div class="card">
      <div class="ch"><div class="ct">Market Prices &amp; Buy Signals</div><span class="cb cb-b">AI Signals</span></div>
      <div class="prow" style="background:var(--sur2);padding:6px 15px">
        <div style="width:8px"></div>
        <div style="flex:1;color:var(--mut);font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm)">Produce</div>
        <div style="width:80px;color:var(--mut);font-size:9px;font-family:var(--fm)">Location</div>
        <div style="width:90px;color:var(--mut);font-size:9px;font-family:var(--fm)">Price/kg</div>
        <div style="width:60px;color:var(--mut);font-size:9px;font-family:var(--fm)">Change</div>
        <div style="width:70px;color:var(--mut);font-size:9px;text-align:right;font-family:var(--fm)">Signal</div>
      </div>
      <div id="prices-list"></div>
    </div>
    <div class="card">
      <div class="ch"><div class="ct">Expenses by Category (MTD)</div><span class="cb cb-gd">May 2026</span></div>
      <div class="cwrap"><canvas id="expDonut" height="160"></canvas></div>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     ACTIVE TRIPS
══════════════════════════════════════════ --}}
<div class="pg" id="pg-tr">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Active</div><div class="msv" id="tr-active">—</div><div class="mss">Field teams</div></div>
    <div class="ms"><div class="msl">Tonnage</div><div class="msv" id="tr-ton">—</div><div class="mss">In-transit</div></div>
    <div class="ms"><div class="msl">Capital Out</div><div class="msv" id="tr-cap">—</div><div class="mss">Advances issued</div></div>
    <div class="ms"><div class="msl">Next Arrival</div><div class="msv" id="tr-arr" style="font-size:13px">—</div><div class="mss">Returning agent</div></div>
  </div>
  <div id="agent-stats-strip" style="display:none;gap:10px;flex-wrap:wrap;margin-bottom:14px"></div>
  <div class="card mb14">
    <div class="ch">
      <div class="ct">All Active Trips</div>
      <div style="display:flex;gap:8px;align-items:center">
        <span class="cb cb-gd" id="tr-badge">— Active</span>
        <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openModal('modal-trip');tripMode('create')"><i class="ti ti-plus" style="font-size:12px"></i> New Trip</button>
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="dtbl">
        <thead><tr><th>Agent</th><th>Region</th><th>Produce</th><th>Tonnage</th><th>Spent (UGX)</th><th>Payment</th><th>Day</th><th>Sync</th><th>Status</th><th></th></tr></thead>
        <tbody id="trips-tbody"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     PRICE TRACKER
══════════════════════════════════════════ --}}
<div class="pg" id="pg-pr">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Best Buy Today</div><div class="msv" id="pr-best" style="color:var(--acc)">—</div><div class="mss" id="pr-best-s">—</div></div>
    <div class="ms"><div class="msl">Avoid Today</div><div class="msv" id="pr-avoid" style="color:var(--red)">—</div><div class="mss" id="pr-avoid-s">—</div></div>
    <div class="ms"><div class="msl">Best Margin</div><div class="msv" id="pr-margin" style="color:var(--blu)">—</div><div class="mss" id="pr-margin-s">—</div></div>
    <div class="ms"><div class="msl">Price Updates</div><div class="msv" id="pr-sync">2h ago</div><div class="mss">Last market sync</div></div>
  </div>
  <div class="g2 mb14" id="pr-charts-grid"></div>
  <div class="card mb14" id="pr-price-table-card">
    <div class="ch">
      <div class="ct">Live Prices</div>
      <span class="cb cb-b">Click row to update</span>
    </div>
    <div id="pr-price-rows"></div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     AI FORECAST
══════════════════════════════════════════ --}}
<div class="pg" id="pg-fc">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Model</div><div class="msv" id="fc-model" style="font-size:13px;color:var(--blu)">—</div><div class="mss">24-month window</div></div>
    <div class="ms"><div class="msl">Confidence</div><div class="msv" id="fc-conf" style="color:var(--acc)">—</div><div class="mss">7-day accuracy</div></div>
    <div class="ms"><div class="msl">Last Trained</div><div class="msv" id="fc-trained">—</div><div class="mss">Daily retraining</div></div>
    <div class="ms"><div class="msl">Data Points</div><div class="msv" id="fc-pts">—</div><div class="mss">Market records</div></div>
  </div>
  <div class="card mb14">
    <div class="ch"><div class="ct">7-Day Price Forecast — All Produce</div><span class="cb cb-b">AI Active</span></div>
    <div class="cwrap"><canvas id="fcChart" height="220"></canvas></div>
    <div class="cleg" id="fc-legend"></div>
  </div>
  <div class="g3" id="fc-cards"></div>
</div>
</div>

{{-- ══════════════════════════════════════════
     ACCOUNTING
══════════════════════════════════════════ --}}
<div class="pg" id="pg-ac">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Revenue MTD</div><div class="msv tg" id="ac-rev">—</div><div class="mss">All trips</div></div>
    <div class="ms"><div class="msl">Total Costs</div><div class="msv" style="color:var(--red)" id="ac-cost">—</div><div class="mss">Produce + ops</div></div>
    <div class="ms"><div class="msl">Net Profit</div><div class="msv" style="color:var(--acc)" id="ac-prf">—</div><div class="mss">26.1% margin</div></div>
    <div class="ms"><div class="msl">Advances Out</div><div class="msv" style="color:var(--gld)" id="ac-adv">—</div><div class="mss">To 6 agents</div></div>
  </div>
  <div class="g2 mb14">
    <div class="card"><div class="ch"><div class="ct">Monthly P&L — Jan–May 2026</div><span class="cb cb-g">2026 YTD</span></div><div class="cwrap"><canvas id="plChart" height="200"></canvas></div></div>
    <div class="card"><div class="ch"><div class="ct">Revenue by Produce (MTD)</div><span class="cb cb-b">May 2026</span></div><div class="cwrap"><canvas id="prodChart" height="200"></canvas></div></div>
  </div>
  <div class="card mb14"><div class="ch"><div class="ct">Profit per Trip — May 2026</div><span class="cb cb-g" id="ac-tp-badge">— Trips</span></div><div class="cwrap"><canvas id="tpChart" height="150"></canvas></div></div>
</div>
</div>

{{-- ══════════════════════════════════════════
     EXPENSES
══════════════════════════════════════════ --}}
<div class="pg" id="pg-ex">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Total Expenses</div><div class="msv" style="color:var(--red)" id="ex-tot">—</div><div class="mss">Ops costs MTD</div></div>
    <div class="ms"><div class="msl">Fuel</div><div class="msv" id="ex-fuel">—</div><div class="mss">44.6% of ops</div></div>
    <div class="ms"><div class="msl">Labour</div><div class="msv" id="ex-lab">—</div><div class="mss">29.5%</div></div>
    <div class="ms"><div class="msl">Other</div><div class="msv" id="ex-oth">—</div><div class="mss">25.9%</div></div>
  </div>
  <div class="g21">
    <div class="card">
      <div class="ch">
        <div class="ct">Expense Breakdown MTD</div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="cb cb-gd">May 2026</span>
          <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openNewExpense()"><i class="ti ti-plus" style="font-size:12px"></i> Add Expense</button>
        </div>
      </div>
      <div id="ex-breakdown"></div>
    </div>
    <div class="card"><div class="ch"><div class="ct">Expense Trend</div></div><div class="cwrap"><canvas id="exTrend" height="200"></canvas></div></div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     TRANSACTION HISTORY
══════════════════════════════════════════ --}}
<div class="pg" id="pg-hi">
<div class="pgwrap">
  <div class="card mb14">
    <div class="ch">
      <div class="ct">Transaction History — All Trips</div>
      <div style="display:flex;gap:8px;align-items:center">
        <span class="cb cb-b">May 2026</span>
        <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openModal('modal-txn');populateTripSelect()"><i class="ti ti-plus" style="font-size:12px"></i> Add Transaction</button>
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="dtbl">
        <thead><tr><th>#</th><th>Date</th><th>Agent</th><th>Item</th><th>Location</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Type</th><th>Sync</th><th></th></tr></thead>
        <tbody id="history-tbody"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     SYNC MONITOR
══════════════════════════════════════════ --}}
<div class="pg" id="pg-sy">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Pending</div><div class="msv" style="color:var(--gld)" id="sy-pend">—</div><div class="mss" id="sy-pend-s">agents</div></div>
    <div class="ms"><div class="msl">Offline Agents</div><div class="msv" style="color:var(--red)" id="sy-off">—</div><div class="mss" id="sy-off-s">—</div></div>
    <div class="ms"><div class="msl">Last Full Sync</div><div class="msv" id="sy-last">—</div><div class="mss">ago</div></div>
    <div class="ms"><div class="msl">Total Synced</div><div class="msv" style="color:var(--acc)" id="sy-tot">—</div><div class="mss">This trip cycle</div></div>
  </div>
  <div class="g2">
    <div class="card">
      <div class="ch"><div class="ct">Sync Queue</div><span class="cb cb-r" id="sy-badge">— Pending</span></div>
      <div id="sync-queue"></div>
      <div class="cbody" style="border-top:1px solid var(--bdr);padding-top:12px">
        <button class="hbtn hb-p" style="width:100%;justify-content:center" onclick="forceSync()">
          <i class="ti ti-refresh" style="font-size:14px"></i> &nbsp;Force Sync All Agents
        </button>
      </div>
    </div>
    <div class="card"><div class="ch"><div class="ct">Sync Activity Timeline</div></div><div class="cwrap"><canvas id="syChart" height="230"></canvas></div></div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     STOCK
══════════════════════════════════════════ --}}
<div class="pg" id="pg-st">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Total In Transit</div><div class="msv" id="st-trans">—</div><div class="mss">All trucks</div></div>
    <div class="ms"><div class="msl">Kampala Stock</div><div class="msv" id="st-kla">—</div><div class="mss">Awaiting sale</div></div>
    <div class="ms"><div class="msl">Bags Used</div><div class="msv" id="st-bags">—</div><div class="mss">Gunny + polyprop</div></div>
    <div class="ms"><div class="msl">Next Arrival</div><div class="msv" id="st-arr" style="font-size:13px">—</div><div class="mss">Today</div></div>
  </div>
  <div class="card mb14">
    <div class="ch"><div class="ct">Stock Summary — All Trucks + Warehouse</div><span class="cb cb-g">Live</span></div>
    <div style="overflow-x:auto">
      <table class="dtbl">
        <thead><tr><th>Produce</th><th>In Transit (T)</th><th>Kampala Stock (T)</th><th>Total (T)</th><th>Est. Value</th><th>Status</th></tr></thead>
        <tbody id="stock-tbody"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     USER MANAGEMENT
══════════════════════════════════════════ --}}
<div class="pg" id="pg-um">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Total Users</div><div class="msv" id="um-total">—</div><div class="mss">All accounts</div></div>
    <div class="ms"><div class="msl">Admins</div><div class="msv" style="color:var(--acc)" id="um-admins">—</div><div class="mss">Full access</div></div>
    <div class="ms"><div class="msl">Managers</div><div class="msv" style="color:var(--blu)" id="um-managers">—</div><div class="mss">CRUD access</div></div>
    <div class="ms"><div class="msl">Viewers</div><div class="msv" style="color:var(--mut)" id="um-viewers">—</div><div class="mss">Read-only</div></div>
  </div>
  <div class="card mb14">
    <div class="ch">
      <div class="ct">All Users</div>
      <div style="display:flex;gap:8px;align-items:center">
        <span class="cb cb-b" id="um-badge">— Users</span>
        <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openNewUser()"><i class="ti ti-plus" style="font-size:12px"></i> Add User</button>
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="dtbl">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th></th></tr></thead>
        <tbody id="users-tbody"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     PRODUCE & UNITS
══════════════════════════════════════════ --}}
<div class="pg" id="pg-pu">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Produce Types</div><div class="msv" id="pu-prod">—</div><div class="mss">Tracked commodities</div></div>
    <div class="ms"><div class="msl">Buy Signals</div><div class="msv" style="color:var(--acc)" id="pu-buy">—</div><div class="mss">Active buy</div></div>
    <div class="ms"><div class="msl">Sell Signals</div><div class="msv" style="color:var(--red)" id="pu-sell">—</div><div class="mss">Active sell</div></div>
    <div class="ms"><div class="msl">Units</div><div class="msv" style="color:var(--blu)" id="pu-units">—</div><div class="mss">Measurement units</div></div>
  </div>
  <div class="g21">
    <div class="card mb14">
      <div class="ch">
        <div class="ct">Produce Types</div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="cb cb-g" id="pu-prod-badge">— Types</span>
          <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openNewProduce()"><i class="ti ti-plus" style="font-size:12px"></i> Add Produce</button>
        </div>
      </div>
      <div style="overflow-x:auto">
        <table class="dtbl">
          <thead><tr><th>Produce</th><th>Location</th><th>Price/kg</th><th>Change</th><th>Signal</th><th>Txns</th><th></th></tr></thead>
          <tbody id="produce-tbody"></tbody>
        </table>
      </div>
    </div>
    <div class="card mb14">
      <div class="ch">
        <div class="ct">Units of Measure</div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="cb cb-b" id="pu-units-badge">— Units</span>
          <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openNewUnit()"><i class="ti ti-plus" style="font-size:12px"></i> Add Unit</button>
        </div>
      </div>
      <div style="overflow-x:auto">
        <table class="dtbl">
          <thead><tr><th>Name</th><th>Symbol</th><th>kg equiv.</th><th>Txns</th><th></th></tr></thead>
          <tbody id="units-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     MOBILE AGENTS
══════════════════════════════════════════ --}}
<div class="pg" id="pg-ma">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Total Agents</div><div class="msv" id="ma-total">—</div><div class="mss">Field agents</div></div>
    <div class="ms"><div class="msl">Active</div><div class="msv" style="color:var(--acc)" id="ma-active">—</div><div class="mss">Can sync</div></div>
    <div class="ms"><div class="msl">Inactive</div><div class="msv" style="color:var(--red)" id="ma-inactive">—</div><div class="mss">Revoked</div></div>
    <div class="ms"><div class="msl">With Login</div><div class="msv" style="color:var(--blu)" id="ma-login">—</div><div class="mss">Mobile credentials</div></div>
  </div>
  <div class="card mb14">
    <div class="ch">
      <div class="ct">All Field Agents</div>
      <div style="display:flex;gap:8px;align-items:center">
        <span class="cb cb-b" id="ma-badge">— Agents</span>
        <button class="hbtn hb-p" style="padding:4px 11px;font-size:11px" onclick="openNewAgent()"><i class="ti ti-plus" style="font-size:12px"></i> Add Agent</button>
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="dtbl">
        <thead><tr><th>Agent</th><th>Region</th><th>Base</th><th>Phone</th><th>Email</th><th>Login</th><th>Status</th><th>Trips</th><th>Txns</th><th></th></tr></thead>
        <tbody id="agents-tbody"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

{{-- ══════════════════════════════════════════
     FIELD MAP
══════════════════════════════════════════ --}}
<div class="pg" id="pg-mp">
<div class="pgwrap">
  <div class="msr">
    <div class="ms"><div class="msl">Geotagged Txns</div><div class="msv" id="mp-count" style="color:var(--blu)">—</div><div class="mss">Map points</div></div>
    <div class="ms"><div class="msl">Agents on Map</div><div class="msv" id="mp-agents">—</div><div class="mss">Distinct agents</div></div>
  </div>
  <div class="card mb14">
    <div class="ch">
      <div class="ct">Agent Field Locations</div>
      <span class="cb cb-b" id="mp-badge">— Points</span>
    </div>
    <div style="display:flex;border-radius:0 0 12px 12px;overflow:hidden">
      <div id="mp-map" style="flex:1;height:520px;min-width:0"></div>
      <div style="width:200px;border-left:1px solid var(--bdr);overflow-y:auto;max-height:520px;flex-shrink:0">
        <div style="padding:10px 12px;border-bottom:1px solid var(--bdr)">
          <div style="font-size:9px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm)">Legend</div>
        </div>
        <div id="mp-legend-list" style="padding:8px 0"></div>
      </div>
    </div>
  </div>
</div>
</div>

@include('dashboard.modals')

@endsection

@section('scripts')
<script>
// ─── Bootstrap data available in JS ──────────────────────────────────────────
const AGENTS       = @json($agents);
const PRODUCES     = @json($produceTypes);
const UNITS        = @json($units);
const CSRF         = document.querySelector('meta[name=csrf-token]').content;

// ─── Modal helpers ────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function overlayClose(e, id) { if (e.target.id === id) closeModal(id); }

function clearErrors() {
  document.querySelectorAll('.ferr').forEach(el => el.textContent = '');
}
function showErrors(errors) {
  Object.entries(errors || {}).forEach(([k, msgs]) => {
    const el = document.getElementById('err-' + k);
    if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
  });
}

// ─── API helpers ──────────────────────────────────────────────────────────────
async function api(url, method = 'GET', body = null) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
  };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(url, opts);
  return r.json();
}

function exportPage() {
  const page = document.querySelector('.ni.act')?.id?.replace('sn-', '') || 'ov';
  window.location.href = '/export/' + page;
}

// ─── TRIPS CRUD ───────────────────────────────────────────────────────────────
let tripEditId = null;

function tripMode(mode, data = {}) {
  tripEditId = mode === 'edit' ? data.id : null;
  const isEdit = mode === 'edit';
  document.getElementById('modal-trip-title').textContent  = isEdit ? 'Edit Trip' : 'New Trip';
  document.getElementById('trip-submit-btn').textContent   = isEdit ? 'Save Changes' : 'Create Trip';
  document.getElementById('trip-create-fields').style.display = isEdit ? 'none' : '';
  document.getElementById('trip-edit-fields').style.display   = isEdit ? '' : 'none';
  if (isEdit) {
    document.getElementById('f-trip-status').value   = data.status   || 'in_progress';
    document.getElementById('f-trip-sync').value     = data.sync     || 'synced';
    document.getElementById('f-trip-offline').value  = data.offline_h || 0;
    document.getElementById('f-trip-day').value      = (data.day || '0').split('/')[0];
    document.getElementById('f-trip-tonnage').value  = data.tonnage_raw || 0;
    document.getElementById('f-trip-spent').value    = data.spent_raw  || 0;
    document.getElementById('f-trip-adv-edit').value = data.advance_raw || 0;
  } else {
    document.getElementById('f-trip-date').value         = new Date().toISOString().split('T')[0];
    document.getElementById('f-trip-days').value         = '';
    document.getElementById('f-trip-advance').value      = '';
    document.getElementById('f-trip-agent').value        = '';
    document.getElementById('f-trip-region').value       = '';
    document.getElementById('f-trip-neg-price').value    = '';
    document.getElementById('f-trip-payment-type').value = 'advance';
    document.getElementById('f-trip-amount-lbl').textContent = 'Advance Amount *';
    document.getElementById('f-trip-currency').value     = 'UGX';
    document.getElementById('f-trip-neg-currency').value = 'UGX';
    document.querySelectorAll('.trip-produce').forEach(c => c.checked = false);
    document.getElementById('qa-produce').style.display = 'none';
  }
  clearErrors();
}

function tripPaymentTypeChange() {
  const t = document.getElementById('f-trip-payment-type').value;
  document.getElementById('f-trip-amount-lbl').textContent =
    t === 'full' ? 'Full Payment Amount *' : 'Advance Amount *';
  document.getElementById('f-trip-advance').placeholder =
    t === 'full' ? 'Total amount paid' : 'e.g. 15000000';
}

async function submitTrip() {
  clearErrors();
  let body, url, method;

  if (tripEditId) {
    url    = '/trips/' + tripEditId;
    method = 'PUT';
    body   = {
      status:           document.getElementById('f-trip-status').value,
      sync_status:      document.getElementById('f-trip-sync').value,
      offline_hours:    +document.getElementById('f-trip-offline').value || 0,
      current_day:      +document.getElementById('f-trip-day').value     || 0,
      tonnage_kg:       +document.getElementById('f-trip-tonnage').value || 0,
      amount_spent:     +document.getElementById('f-trip-spent').value   || 0,
      advance_amount:   +document.getElementById('f-trip-adv-edit').value || 0,
    };
  } else {
    const produce = [...document.querySelectorAll('.trip-produce:checked')].map(c => c.value);
    const negPrice = +document.getElementById('f-trip-neg-price').value || null;
    url    = '/trips';
    method = 'POST';
    body   = {
      agent_id:                document.getElementById('f-trip-agent').value,
      region:                  document.getElementById('f-trip-region').value,
      produce_list:            produce,
      start_date:              document.getElementById('f-trip-date').value,
      total_days:              +document.getElementById('f-trip-days').value,
      advance_amount:          +document.getElementById('f-trip-advance').value,
      payment_type:            document.getElementById('f-trip-payment-type').value,
      negotiated_price_per_kg: negPrice,
      currency:                document.getElementById('f-trip-currency').value || 'UGX',
    };
  }

  const res = await api(url, method, body);
  if (res.success) {
    closeModal('modal-trip');
    pageCache = {};
    loadPage('tr');
    refreshBadges();
    showToast(tripEditId ? 'Trip updated!' : 'Trip created!');
  } else if (res.errors) {
    showErrors(res.errors);
  }
}

let deleteTarget = null;

function askDelete(url, label) {
  deleteTarget = url;
  document.getElementById('confirm-msg').textContent = `Delete "${label}"? This cannot be undone.`;
  openModal('modal-confirm');
}

async function confirmDelete() {
  if (!deleteTarget) return;
  const res = await api(deleteTarget, 'DELETE');
  closeModal('modal-confirm');
  deleteTarget = null;
  if (res.success) {
    pageCache = {};
    const cur = document.querySelector('.ni.act')?.id?.replace('sn-', '') || 'ov';
    loadPage(cur);
    refreshBadges();
    showToast('Deleted successfully.');
  }
}

// ─── TRANSACTIONS CRUD ────────────────────────────────────────────────────────
function txnTypeChange() {
  const t = document.getElementById('f-txn-type').value;
  document.getElementById('txn-purchase-fields').style.display = t === 'purchase' ? '' : 'none';
  document.getElementById('txn-expense-fields').style.display  = t === 'expense'  ? '' : 'none';
  document.getElementById('txn-advance-fields').style.display  = t === 'advance'  ? '' : 'none';
}

async function populateTripSelect() {
  const sel = document.getElementById('f-txn-trip');
  sel.innerHTML = '<option value="">No trip</option>';
  try {
    const d = await api('/api/dashboard/tr');
    (d.table || []).forEach(t => {
      sel.innerHTML += `<option value="${t.id}">${t.agent} — ${t.region}</option>`;
    });
  } catch(e) {}
  document.getElementById('f-txn-date').value = new Date().toISOString().split('T')[0];
  clearErrors();
  txnTypeChange();
}

async function submitTransaction() {
  clearErrors();
  const type = document.getElementById('f-txn-type').value;
  const body = {
    agent_id:         document.getElementById('f-txn-agent').value   || null,
    trip_id:          document.getElementById('f-txn-trip').value     || null,
    type,
    transaction_date: document.getElementById('f-txn-date').value,
    sync_status:      document.getElementById('f-txn-sync').value,
    notes:            document.getElementById('f-txn-notes').value    || null,
  };

  if (type === 'purchase') {
    body.produce_type_id = document.getElementById('f-txn-produce').value  || null;
    body.location        = document.getElementById('f-txn-location').value || null;
    body.quantity_kg     = +document.getElementById('f-txn-qty').value;
    body.unit_id         = document.getElementById('f-txn-unit').value     || null;
    body.unit_price      = +document.getElementById('f-txn-price').value;
    body.currency        = document.getElementById('f-txn-currency').value || 'UGX';
  } else if (type === 'expense') {
    body.category       = document.getElementById('f-txn-category').value;
    body.location       = document.getElementById('f-txn-exp-location').value || null;
    body.expense_amount = +document.getElementById('f-txn-exp-amount').value;
    body.currency       = document.getElementById('f-txn-exp-currency').value || 'UGX';
  } else {
    body.location       = document.getElementById('f-txn-adv-location').value || null;
    body.advance_amount = +document.getElementById('f-txn-adv-amount').value;
    body.currency       = document.getElementById('f-txn-adv-currency').value || 'UGX';
  }

  const res = await api('/transactions', 'POST', body);
  if (res.success) {
    closeModal('modal-txn');
    pageCache['hi'] = null;
    loadPage('hi');
    showToast('Transaction saved!');
  } else if (res.errors) {
    showErrors(res.errors);
  }
}

// ─── EXPENSES CRUD ────────────────────────────────────────────────────────────
let expenseEditId = null;

function openNewExpense() {
  expenseEditId = null;
  document.getElementById('modal-expense-title').textContent = 'New Expense';
  document.getElementById('exp-submit-btn').textContent      = 'Save Expense';
  document.getElementById('f-exp-category').value  = 'fuel';
  document.getElementById('f-exp-label').value     = '';
  document.getElementById('f-exp-sublabel').value  = '';
  document.getElementById('f-exp-amount').value    = '';
  document.getElementById('f-exp-date').value      = new Date().toISOString().split('T')[0];
  clearErrors();
  openModal('modal-expense');
}

function openEditExpense(id, data) {
  expenseEditId = id;
  document.getElementById('modal-expense-title').textContent = 'Edit Expense';
  document.getElementById('exp-submit-btn').textContent      = 'Save Changes';
  document.getElementById('f-exp-category').value  = data.category;
  document.getElementById('f-exp-label').value     = data.label;
  document.getElementById('f-exp-sublabel').value  = data.sub_label || '';
  document.getElementById('f-exp-amount').value    = data.amount_raw;
  document.getElementById('f-exp-currency').value  = data.currency || 'UGX';
  document.getElementById('f-exp-date').value      = data.date || new Date().toISOString().split('T')[0];
  clearErrors();
  openModal('modal-expense');
}

async function submitExpense() {
  clearErrors();
  const body = {
    category:     document.getElementById('f-exp-category').value,
    label:        document.getElementById('f-exp-label').value,
    sub_label:    document.getElementById('f-exp-sublabel').value || null,
    amount:       +document.getElementById('f-exp-amount').value,
    currency:     document.getElementById('f-exp-currency').value || 'UGX',
    expense_date: document.getElementById('f-exp-date').value,
  };

  const url    = expenseEditId ? '/expenses/' + expenseEditId : '/expenses';
  const method = expenseEditId ? 'PUT' : 'POST';
  const res    = await api(url, method, body);

  if (res.success) {
    closeModal('modal-expense');
    pageCache['ex'] = null;
    loadPage('ex');
    showToast(expenseEditId ? 'Expense updated!' : 'Expense added!');
  } else if (res.errors) {
    const mapped = {};
    if (res.errors.category) mapped['exp-category'] = res.errors.category;
    if (res.errors.label)    mapped['exp-label']    = res.errors.label;
    if (res.errors.amount)   mapped['exp-amount']   = res.errors.amount;
    if (res.errors.expense_date) mapped['exp-date'] = res.errors.expense_date;
    showErrors(mapped);
  }
}

// ─── PRICES CRUD ─────────────────────────────────────────────────────────────
function openUpdatePrice(id, name, price, change, signal, location, currency) {
  document.getElementById('modal-price-title').textContent = 'Update — ' + name;
  document.getElementById('f-price-id').value        = id;
  document.getElementById('f-price-val').value       = price;
  document.getElementById('f-price-change').value    = change;
  document.getElementById('f-price-signal').value    = signal;
  document.getElementById('f-price-location').value  = location;
  document.getElementById('f-price-currency').value  = currency || 'UGX';
  clearErrors();
  openModal('modal-price');
}

async function submitPrice() {
  clearErrors();
  const id   = document.getElementById('f-price-id').value;
  const body = {
    current_price:    +document.getElementById('f-price-val').value,
    currency:         document.getElementById('f-price-currency').value || 'UGX',
    change_percent:   +document.getElementById('f-price-change').value,
    signal:           document.getElementById('f-price-signal').value,
    primary_location: document.getElementById('f-price-location').value,
  };
  const res = await api('/prices/' + id, 'PUT', body);
  if (res.success) {
    closeModal('modal-price');
    pageCache['pr'] = null;
    pageCache['ov'] = null;
    loadPage('pr');
    showToast('Price updated!');
  } else if (res.errors) {
    showErrors({ 'price-val': res.errors.current_price });
  }
}

// ─── USER MANAGEMENT ──────────────────────────────────────────────────────────
let userEditId = null;

function openNewUser() {
  userEditId = null;
  document.getElementById('modal-user-title').textContent  = 'New User';
  document.getElementById('user-submit-btn').textContent   = 'Create User';
  document.getElementById('f-user-pw-lbl').textContent     = 'Password *';
  document.getElementById('f-user-name').value    = '';
  document.getElementById('f-user-email').value   = '';
  document.getElementById('f-user-role').value    = 'viewer';
  document.getElementById('f-user-password').value= '';
  clearErrors();
  openModal('modal-user');
}

function openEditUser(id, data) {
  userEditId = id;
  document.getElementById('modal-user-title').textContent  = 'Edit User';
  document.getElementById('user-submit-btn').textContent   = 'Save Changes';
  document.getElementById('f-user-pw-lbl').textContent     = 'New Password (leave blank to keep current)';
  document.getElementById('f-user-name').value    = data.name;
  document.getElementById('f-user-email').value   = data.email;
  document.getElementById('f-user-role').value    = data.role;
  document.getElementById('f-user-password').value= '';
  clearErrors();
  openModal('modal-user');
}

async function submitUser() {
  clearErrors();
  const body = {
    name:     document.getElementById('f-user-name').value,
    email:    document.getElementById('f-user-email').value,
    role:     document.getElementById('f-user-role').value,
    password: document.getElementById('f-user-password').value || null,
  };

  const url    = userEditId ? '/users/' + userEditId : '/users';
  const method = userEditId ? 'PUT' : 'POST';
  const res    = await api(url, method, body);

  if (res.success) {
    closeModal('modal-user');
    pageCache['um'] = null;
    loadPage('um');
    showToast(userEditId ? 'User updated!' : 'User created!');
  } else if (res.errors) {
    const mapped = {};
    if (res.errors.name)     mapped['user-name']     = res.errors.name;
    if (res.errors.email)    mapped['user-email']    = res.errors.email;
    if (res.errors.role)     mapped['user-role']     = res.errors.role;
    if (res.errors.password) mapped['user-password'] = res.errors.password;
    showErrors(mapped);
  }
}

// ─── QUICK-ADD HELPERS ────────────────────────────────────────────────────────
function toggleQuickAdd(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'none' ? '' : 'none';
}

async function quickAddProduce() {
  const name  = document.getElementById('qa-produce-name').value.trim();
  const emoji = document.getElementById('qa-produce-emoji').value.trim() || '🌿';
  const errEl = document.getElementById('err-qa-produce');
  if (!name) { errEl.textContent = 'Name is required.'; return; }
  errEl.textContent = '';

  const res = await api('/produce-types', 'POST', { name, emoji });
  if (res.success && res.produce) {
    // Add checkbox to the grid
    const grid = document.getElementById('produce-check-grid');
    const lbl  = document.createElement('label');
    lbl.className = 'fcheck';
    lbl.innerHTML = `<input type="checkbox" class="trip-produce" value="${res.produce.name}" checked> ${res.produce.emoji} ${res.produce.name}`;
    grid.appendChild(lbl);

    // Add to transaction produce select
    const sel = document.getElementById('f-txn-produce');
    const opt = document.createElement('option');
    opt.value = res.produce.id;
    opt.textContent = `${res.produce.emoji} ${res.produce.name}`;
    sel.appendChild(opt);

    document.getElementById('qa-produce-name').value  = '';
    document.getElementById('qa-produce-emoji').value = '';
    document.getElementById('qa-produce').style.display = 'none';
    showToast(`"${res.produce.name}" added!`);
  } else {
    errEl.textContent = res.message || 'Failed to add produce.';
  }
}

async function quickAddUnit() {
  const name   = document.getElementById('qa-unit-name').value.trim();
  const symbol = document.getElementById('qa-unit-symbol').value.trim();
  const baseKg = +document.getElementById('qa-unit-base-kg').value || null;
  const errEl  = document.getElementById('err-qa-unit');
  if (!name || !symbol) { errEl.textContent = 'Name and symbol are required.'; return; }
  errEl.textContent = '';

  const res = await api('/units', 'POST', { name, symbol, base_kg: baseKg });
  if (res.success && res.unit) {
    const sel = document.getElementById('f-txn-unit');
    const opt = document.createElement('option');
    opt.value = res.unit.id;
    opt.textContent = res.unit.symbol;
    opt.selected = true;
    sel.appendChild(opt);

    document.getElementById('qa-unit-name').value    = '';
    document.getElementById('qa-unit-symbol').value  = '';
    document.getElementById('qa-unit-base-kg').value = '';
    document.getElementById('qa-unit').style.display = 'none';
    showToast(`Unit "${res.unit.symbol}" added!`);
  } else {
    errEl.textContent = res.message || 'Failed to add unit.';
  }
}

// ─── SYNC ─────────────────────────────────────────────────────────────────────
async function forceSync() {
  const res = await api('/sync/force', 'POST');
  if (res.success) {
    pageCache['sy'] = null;
    pageCache['ov'] = null;
    loadPage('sy');
    refreshBadges();
    showToast('All agents synced — ' + res.synced + ' records updated.');
  }
}

// ─── Refresh sidebar badges ───────────────────────────────────────────────────
async function refreshBadges() {
  try {
    const d = await api('/api/dashboard/ov');
    const syncBadge = document.getElementById('badge-sy');
    const slbl      = document.getElementById('slbl');
    if (syncBadge) syncBadge.textContent = d.kpis?.pending_sync ?? '';
    if (slbl && online) slbl.textContent = (d.kpis?.pending_sync ?? '') + ' PENDING';
    const tripBadge = document.getElementById('badge-tr');
    if (tripBadge) tripBadge.textContent = d.kpis?.active_trips ?? '';
  } catch(e) {}
}
</script>
<script>
// ─── Page renderers ───────────────────────────────────────────────────────────
function renderPage(n, d) {
  if (n === 'ov')   renderOverview(d);
  if (n === 'tr')   renderTrips(d);
  if (n === 'pr')   renderPrices(d);
  if (n === 'fc')   renderForecast(d);
  if (n === 'ac')   renderAccounting(d);
  if (n === 'ex')   renderExpenses(d);
  if (n === 'hi')   renderHistory(d);
  if (n === 'sy')   renderSync(d);
  if (n === 'st')   renderStock(d);
  if (n === 'pu')   renderProduceUnits(d);
  if (n === 'ma')   renderMobileAgents(d);
  if (n === 'um')   renderUsers(d);
  if (n === 'mp')   renderMap(d);
}

function renderCharts(n, d) {
  // Re-render charts only (data already in DOM)
  if (n === 'ov')   { mkMainChart(d.mainChart); mkExpDonut(d.expDonut); }
  if (n === 'pr')   renderPriceCharts(d.charts);
  if (n === 'fc')   mkFcChart(d.chart);
  if (n === 'ac')   { mkPlChart(d.plChart); mkProdChart(d.prodChart); mkTpChart(d.tpChart); }
  if (n === 'ex')   mkExTrend(d.trendChart);
  if (n === 'sy')   mkSyChart(d.chart);
}

// ─── OVERVIEW ────────────────────────────────────────────────────────────────
function renderOverview(d) {
  // KPIs
  document.getElementById('kv-rev').textContent = d.kpis?.revenue_mtd    || '342.8M';
  document.getElementById('kv-ton').textContent = d.kpis?.tonnage_bought  || '184.2T';
  document.getElementById('kv-prf').textContent = d.kpis?.net_profit      || '89.4M';
  document.getElementById('kv-atr').textContent = d.kpis?.active_trips    || '6';
  document.getElementById('kv-syn').textContent = d.kpis?.pending_sync    || '7';

  // Routes
  const list = document.getElementById('routes-list');
  document.getElementById('routes-badge').textContent = (d.routes?.length || 0) + ' Active';
  list.innerHTML = (d.routes || []).map(r => {
    const isWarn = r.sync === 'offline' && r.offline_h >= 3;
    const cls = isWarn ? 'wrn' : (r.tonnage !== '0.0T' ? 'act' : '');
    return `<div class="ri">
      <div class="rind ${cls}">${r.initials}</div>
      <div class="rinf">
        <div class="rinm">${r.region}</div>
        <div class="risb">${r.produce} · ${r.agent}</div>
        ${r.tonnage !== '0.0T' ? `<div class="${isWarn ? 'riw' : 'rist'}">
          ${isWarn ? '⚠ ' + r.unsynced + ' unsynced · offline ' + r.offline_h + 'h' : 'Day ' + r.day + ' · ' + r.tonnage + ' · UGX ' + r.spent + ' spent'}
        </div>` : '<div class="risb" style="color:var(--mut)">Departing today</div>'}
      </div>
      ${r.day > 0 ? `<div class="rirg"><div class="riday">Day ${r.day}/${r.total_days}</div></div>` : ''}
    </div>`;
  }).join('');

  // Prices list
  const pl = document.getElementById('prices-list');
  pl.innerHTML = (d.prices || []).map(p => {
    const up = p.change >= 0;
    return `<div class="prow">
      <div class="prdot" style="background:${p.dot_color}"></div>
      <div class="prnm">${p.emoji} ${p.name}</div>
      <div class="prloc">${p.location}</div>
      <div class="prpr">${p.currency||'UGX'} ${p.price}/kg</div>
      <div class="prch ${up ? 'up' : 'dn'}">${up ? '▲' : '▼'} ${Math.abs(p.change)}%</div>
      <div class="prac ${p.signal_class}">${p.signal_label}</div>
    </div>`;
  }).join('');

  mkMainChart(d.mainChart);
  mkExpDonut(d.expDonut);
}

function mkMainChart(c) {
  mk('mainChart', {
    type:'bar',
    data:{
      labels: c.labels,
      datasets:[
        {label:'Revenue', data:c.revenue, backgroundColor:'rgba(63,185,80,0.55)', borderRadius:4, borderSkipped:false},
        {label:'Cost',    data:c.cost,    backgroundColor:'rgba(248,81,73,0.45)',  borderRadius:4, borderSkipped:false},
        {type:'line',label:'Profit',data:c.profit,borderColor:'#58a6ff',borderWidth:2,pointRadius:3,pointBackgroundColor:'#58a6ff',tension:0.35,fill:false}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:true, aspectRatio:2.8,
      plugins:{legend:{display:false},tooltip:{mode:'index',intersect:false}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'},callback:v=>v+'M'},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

function mkExpDonut(c) {
  mk('expDonut', {
    type:'doughnut',
    data:{labels:c.labels, datasets:[{data:c.data, backgroundColor:c.colors, borderWidth:0, hoverOffset:4}]},
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10,padding:10}}}
    }
  });
}

// ─── TRIPS ───────────────────────────────────────────────────────────────────
function renderTrips(d) {
  const s = d.stats || {};
  document.getElementById('tr-active').textContent = s.active || '—';
  document.getElementById('tr-ton').textContent    = (s.tonnage || '—') + ' T';
  document.getElementById('tr-cap').textContent    = (s.capital_out || '—') + 'M';
  document.getElementById('tr-arr').textContent    = s.next_arrival || '—';
  document.getElementById('tr-badge').textContent  = (s.active || '—') + ' Active';

  // Per-agent metrics strip
  const strip = document.getElementById('agent-stats-strip');
  if (strip && d.agent_stats?.length) {
    strip.innerHTML = d.agent_stats.map(a => `
      <div style="background:var(--sur);border:1px solid var(--bdr2);border-left:3px solid ${a.color};border-radius:10px;padding:10px 14px;min-width:160px">
        <div style="display:flex;align-items:center;gap:7px;margin-bottom:6px">
          <div style="width:22px;height:22px;border-radius:50%;background:${hexToRgba(a.color,0.15)};border:1px solid ${hexToRgba(a.color,0.4)};display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:${a.color};flex-shrink:0">${a.name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase()}</div>
          <div style="font-size:12px;font-weight:700;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:110px">${a.name}</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px">
          <div><div style="font-size:8px;color:var(--mut);font-family:var(--fm)">TRIPS</div><div style="font-size:13px;font-weight:700;color:var(--txt)">${a.trips}</div></div>
          <div><div style="font-size:8px;color:var(--mut);font-family:var(--fm)">TONNES</div><div style="font-size:13px;font-weight:700;color:var(--acc)">${a.tonnage}</div></div>
          <div><div style="font-size:8px;color:var(--mut);font-family:var(--fm)">SPENT</div><div style="font-size:11px;font-weight:700;color:var(--red)">${a.spent}</div></div>
        </div>
      </div>
    `).join('');
    strip.style.display = 'flex';
  } else if (strip) {
    strip.style.display = 'none';
  }

  const tbody = document.getElementById('trips-tbody');
  tbody.innerHTML = (d.table || []).map((t, i) => {
    const stCls = t.status === 'completed' ? 'sp-sy' : 'sp-pe';
    const pmtBadge = t.payment_type === 'full'
      ? `<span class="spill sp-sy" style="margin-left:4px">Paid Full</span>`
      : `<span class="spill sp-pe" style="margin-left:4px">Advance</span>`;
    const cur = t.currency || 'UGX';
    const negPriceTip = t.neg_price_fmt ? `<div style="font-size:9px;color:var(--mut);font-family:var(--fm);margin-top:2px">Neg. ${cur} ${t.neg_price_fmt}</div>` : '';
    return `<tr>
      <td>${t.agent}</td>
      <td>${t.region}</td>
      <td>${t.produce}</td>
      <td class="tm">${t.tonnage} T</td>
      <td class="tm tr">${+t.tonnage > 0 ? '-' + t.spent : '—'}</td>
      <td>
        <div style="font-family:var(--fm)">${cur} ${t.advance_fmt}</div>
        ${pmtBadge}${negPriceTip}
      </td>
      <td>${t.day}</td>
      <td><span class="spill ${t.sync_badge}">${t.sync_label}</span></td>
      <td><span class="spill ${stCls}">${t.status_label}</span></td>
      <td><div class="fact-btns">
        <button class="abtn abtn-e" onclick='tripMode("edit",${JSON.stringify(t)});openModal("modal-trip")'><i class="ti ti-pencil"></i></button>
        <button class="abtn abtn-d" onclick="askDelete('/trips/${t.id}','${t.agent} — ${t.region}')"><i class="ti ti-trash"></i></button>
      </div></td>
    </tr>`;
  }).join('');
}

// ─── PRICES ──────────────────────────────────────────────────────────────────
function renderPrices(d) {
  const s = d.stats || {};
  document.getElementById('pr-best').textContent   = s.best_buy    || '—';
  document.getElementById('pr-avoid').textContent  = s.avoid       || '—';
  document.getElementById('pr-margin').textContent = s.best_margin || '—';
  document.getElementById('pr-sync').textContent   = s.last_sync   || '—';

  // Build chart canvases
  const grid = document.getElementById('pr-charts-grid');
  const slugs = Object.keys(d.charts || {});
  grid.innerHTML = slugs.map(slug => {
    const c = d.charts[slug];
    const ch = c.change >= 0 ? 'cb-g' : 'cb-r';
    return `<div class="card">
      <div class="ch">
        <div class="ct">${c.emoji} ${c.name} — 24 Month History</div>
        <span class="cb ${ch}">UGX ${c.price}/kg</span>
      </div>
      <div class="cwrap"><canvas id="hc-${slug}" height="160"></canvas></div>
    </div>`;
  }).join('');

  // Price update rows
  const prRows = document.getElementById('pr-price-rows');
  if (prRows) {
    prRows.innerHTML = (d.prices || []).map(p => {
      const up = p.change >= 0;
      return `<div class="prow" style="justify-content:space-between">
        <div class="prdot" style="background:${p.dot_color}"></div>
        <div class="prnm">${p.emoji} ${p.name}</div>
        <div class="prloc">${p.location}</div>
        <div class="prpr" style="font-family:var(--fm)">${p.currency||'UGX'} ${p.price}/kg</div>
        <div class="prch ${up?'up':'dn'}">${up?'▲':'▼'} ${Math.abs(p.change)}%</div>
        <div class="prac ${p.signal_class}">${p.signal_label}</div>
        <button class="abtn abtn-u" onclick="openUpdatePrice(${p.id},'${p.name}',${p.price_raw},${p.change},'${p.signal}','${p.location}','${p.currency||'UGX'}')"><i class="ti ti-pencil"></i> Update</button>
      </div>`;
    }).join('');
  }

  renderPriceCharts(d.charts);
}

function renderPriceCharts(charts) {
  const lo = lineOpts();
  Object.entries(charts || {}).forEach(([slug, c]) => {
    mk('hc-' + slug, {
      type:'line',
      data:{
        labels: c.labels,
        datasets:[{
          data: c.data, borderColor: c.color, borderWidth:2,
          pointRadius:0, tension:0.4, fill:true,
          backgroundColor: hexToRgba(c.color, 0.08)
        }]
      },
      options: lo
    });
  });
}

// ─── FORECAST ────────────────────────────────────────────────────────────────
function renderForecast(d) {
  document.getElementById('fc-model').textContent   = d.stats?.model        || '—';
  document.getElementById('fc-conf').textContent    = d.stats?.confidence   || '—';
  document.getElementById('fc-trained').textContent = d.stats?.last_trained || '—';
  document.getElementById('fc-pts').textContent     = d.stats?.data_points  || '—';

  // Legend
  document.getElementById('fc-legend').innerHTML = (d.chart?.datasets || []).map(ds =>
    `<div class="cli"><div class="cld" style="background:${ds.color}"></div>${ds.label}</div>`
  ).join('');

  // Cards
  document.getElementById('fc-cards').innerHTML = (d.cards || []).map(c => `
    <div class="fcard">
      <div class="fclbl">${c.emoji} ${c.name}</div>
      <div class="fcval" style="color:${c.color}">${c.trend}</div>
      <div class="fctxt">${c.text} <strong style="color:${c.signal_color}">${c.signal}</strong></div>
    </div>
  `).join('');

  mkFcChart(d.chart);
}

function mkFcChart(c) {
  mk('fcChart', {
    type:'line',
    data:{
      labels: c.labels,
      datasets: (c.datasets || []).map(ds => ({
        label:ds.label, data:ds.data, borderColor:ds.color,
        borderWidth:2, tension:0.4, pointRadius:3, fill:false
      }))
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10,padding:10}}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'}},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

// ─── ACCOUNTING ──────────────────────────────────────────────────────────────
function renderAccounting(d) {
  document.getElementById('ac-rev').textContent  = d.stats?.revenue  || '—';
  document.getElementById('ac-cost').textContent = d.stats?.costs    || '—';
  document.getElementById('ac-prf').textContent  = d.stats?.profit   || '—';
  document.getElementById('ac-adv').textContent  = d.stats?.advances || '—';
  document.getElementById('ac-tp-badge').textContent = (d.tpChart?.labels?.length || '—') + ' Trips';

  mkPlChart(d.plChart);
  mkProdChart(d.prodChart);
  mkTpChart(d.tpChart);
}

function mkPlChart(c) {
  mk('plChart', {
    type:'bar',
    data:{
      labels: c.labels,
      datasets:[
        {label:'Revenue',data:c.revenue,backgroundColor:'rgba(63,185,80,.6)',borderRadius:4,borderSkipped:false},
        {label:'Cost',   data:c.cost,   backgroundColor:'rgba(248,81,73,.5)', borderRadius:4,borderSkipped:false},
        {type:'line',label:'Profit',data:c.profit,borderColor:'#58a6ff',borderWidth:2,pointRadius:4,pointBackgroundColor:'#58a6ff',tension:0.3,fill:false}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10,padding:8}}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:10}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'},callback:v=>v+'M'},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

function mkProdChart(c) {
  mk('prodChart', {
    type:'doughnut',
    data:{labels:c.labels, datasets:[{data:c.data, backgroundColor:c.colors, borderWidth:0}]},
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10,padding:10}}}
    }
  });
}

function mkTpChart(c) {
  const data = c.data || [];
  mk('tpChart', {
    type:'bar',
    data:{
      labels: c.labels,
      datasets:[{
        label:'Profit (M)', data:data,
        backgroundColor: data.map(p => p > 10 ? 'rgba(63,185,80,.7)' : p > 7 ? 'rgba(88,166,255,.6)' : 'rgba(240,136,62,.6)'),
        borderRadius:4, borderSkipped:false
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{display:false}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:9,family:'DM Mono'}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:9,family:'DM Mono'},callback:v=>v+'M'},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

// ─── EXPENSES ────────────────────────────────────────────────────────────────
function renderExpenses(d) {
  document.getElementById('ex-tot').textContent  = d.stats?.total  || '—';
  document.getElementById('ex-fuel').textContent = d.stats?.fuel   || '—';
  document.getElementById('ex-lab').textContent  = d.stats?.labour || '—';
  document.getElementById('ex-oth').textContent  = d.stats?.other  || '—';

  const bk = document.getElementById('ex-breakdown');
  bk.innerHTML = (d.breakdown || []).map(e => `
    <div class="exrow">
      <div class="exico" style="background:${hexToRgba(e.bar_color, 0.12)}">${e.icon}</div>
      <div class="exlbl">
        <div class="exnm">${e.label}</div>
        <div class="exsb">${e.sub_label}</div>
      </div>
      <div class="exbw">
        <div class="exbg"><div class="exbf" style="width:${e.bar_width}%;background:${e.bar_color}"></div></div>
        <div class="expc">${e.percentage}%</div>
      </div>
      <div class="examt"><span style="font-size:9px;color:var(--mut)">${e.currency||'UGX'}</span> ${e.amount}</div>
      <div class="fact-btns" style="margin-left:8px">
        <button class="abtn abtn-e" onclick="openEditExpense(${e.id},${JSON.stringify(e).replace(/"/g,'&quot;')})"><i class="ti ti-pencil"></i></button>
        <button class="abtn abtn-d" onclick="askDelete('/expenses/${e.id}','${e.label}')"><i class="ti ti-trash"></i></button>
      </div>
    </div>
  `).join('');

  mkExTrend(d.trendChart);
}

function mkExTrend(c) {
  mk('exTrend', {
    type:'line',
    data:{
      labels: c.labels,
      datasets:[
        {label:'Fuel',   data:c.fuel,   borderColor:'#f0883e',borderWidth:2,tension:0.4,fill:false,pointRadius:2},
        {label:'Labour', data:c.labour, borderColor:'#58a6ff',borderWidth:2,tension:0.4,fill:false,pointRadius:2}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10,padding:8}}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:9}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:9,family:'DM Mono'},callback:v=>v+'M'},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

// ─── HISTORY ─────────────────────────────────────────────────────────────────
function renderHistory(d) {
  const tbody = document.getElementById('history-tbody');
  tbody.innerHTML = (d.transactions || []).map(t => `
    <tr>
      <td class="tm">${t.seq}</td>
      <td>${t.date}</td>
      <td>${t.agent}</td>
      <td>${t.item_emoji ? t.item_emoji + ' ' : ''}${t.item}</td>
      <td>${t.location}</td>
      <td class="tm">${t.quantity}</td>
      <td class="tm">${t.unit_price}</td>
      <td class="tm ${t.is_positive ? 'tg' : 'tr'}">${t.is_positive ? '+' : '-'}${t.total} <span style="font-size:9px;opacity:.6">${t.currency||'UGX'}</span></td>
      <td>${t.type}</td>
      <td><span class="spill ${t.sync_badge}">${t.sync_label}</span></td>
      <td><button class="abtn abtn-d" onclick="askDelete('/transactions/${t.id}','${t.item} — ${t.date}')"><i class="ti ti-trash"></i></button></td>
    </tr>
  `).join('');
}

// ─── SYNC ────────────────────────────────────────────────────────────────────
function renderSync(d) {
  const s = d.stats || {};
  document.getElementById('sy-pend').textContent  = s.pending       || '—';
  document.getElementById('sy-off').textContent   = s.offline       || '—';
  document.getElementById('sy-last').textContent  = s.last_sync     || '—';
  document.getElementById('sy-tot').textContent   = s.total_synced  || '—';
  document.getElementById('sy-badge').textContent = (s.pending || '—') + ' Pending';

  const queue = document.getElementById('sync-queue');
  queue.innerHTML = (d.queue || []).map(q => `
    <div class="sqit" style="opacity:${q.opacity}">
      <div class="sqdw"><div class="sqd ${q.dot_class}"></div></div>
      <div class="sqinf">
        <div class="sqnm">${q.agent} — ${q.region}</div>
        <div class="sqsb">${q.description}</div>
      </div>
      <div class="sqtm">${q.time_label}</div>
    </div>
  `).join('');

  mkSyChart(d.chart);
}

function mkSyChart(c) {
  mk('syChart', {
    type:'bar',
    data:{
      labels: c.labels,
      datasets:[
        {label:'Synced', data:c.synced, backgroundColor:'rgba(63,185,80,.6)', borderRadius:3},
        {label:'Failed', data:c.failed, backgroundColor:'rgba(248,81,73,.6)', borderRadius:3}
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{color:txtC(),font:{size:10,family:'DM Mono'},boxWidth:10}}},
      scales:{
        x:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'}},grid:{color:gridC()}},
        y:{ticks:{color:txtC(),font:{size:10,family:'DM Mono'}},grid:{color:gridC()},border:{display:false}}
      }
    }
  });
}

// ─── STOCK ───────────────────────────────────────────────────────────────────
function renderStock(d) {
  document.getElementById('st-trans').textContent = d.stats?.in_transit    || '—';
  document.getElementById('st-kla').textContent   = d.stats?.kampala_stock || '—';
  document.getElementById('st-bags').textContent  = d.stats?.bags_used     || '—';
  document.getElementById('st-arr').textContent   = d.stats?.next_arrival  || '—';

  const tbody = document.getElementById('stock-tbody');
  tbody.innerHTML = (d.table || []).map(row => `
    <tr>
      <td>${row.emoji} ${row.name}</td>
      <td class="tm">${row.in_transit}</td>
      <td class="tm">${row.kampala}</td>
      <td class="tm">${row.total}</td>
      <td class="tm ${row.value_positive ? 'tg' : 'tr'}">${row.est_value}</td>
      <td><span class="spill ${row.badge}">${row.status}</span></td>
    </tr>
  `).join('');
}

// ─── USERS ───────────────────────────────────────────────────────────────────
function renderUsers(d) {
  const s = d.stats || {};
  document.getElementById('um-total').textContent    = s.total    || '—';
  document.getElementById('um-admins').textContent   = s.admins   || '—';
  document.getElementById('um-managers').textContent = s.managers || '—';
  document.getElementById('um-viewers').textContent  = s.viewers  || '—';
  document.getElementById('um-badge').textContent    = (s.total || '—') + ' Users';

  const roleBadge = r => {
    if (r === 'admin')   return '<span class="spill sp-sy">Admin</span>';
    if (r === 'manager') return '<span class="spill sp-pe" style="background:var(--bdim);color:var(--blu)">Manager</span>';
    return '<span class="spill" style="background:var(--sur2);color:var(--mut)">Viewer</span>';
  };

  const tbody = document.getElementById('users-tbody');
  tbody.innerHTML = (d.users || []).map(u => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:28px;height:28px;border-radius:50%;background:var(--adim);border:1px solid var(--abdr);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--acc);flex-shrink:0">${u.initials}</div>
          <span>${u.name}${u.is_self ? ' <span style="font-size:9px;color:var(--mut);font-family:var(--fm)">(you)</span>' : ''}</span>
        </div>
      </td>
      <td class="tm" style="color:var(--txt2)">${u.email}</td>
      <td>${roleBadge(u.role)}</td>
      <td class="tm" style="color:var(--mut)">${u.created}</td>
      <td><div class="fact-btns">
        <button class="abtn abtn-e" onclick='openEditUser(${u.id},${JSON.stringify(u).replace(/"/g,"&quot;")})'><i class="ti ti-pencil"></i></button>
        ${!u.is_self ? `<button class="abtn abtn-d" onclick="askDelete('/users/${u.id}','${u.name}')"><i class="ti ti-trash"></i></button>` : ''}
      </div></td>
    </tr>
  `).join('');
}

// ─── PRODUCE & UNITS ─────────────────────────────────────────────────────────
function renderProduceUnits(d) {
  const s = d.stats || {};
  document.getElementById('pu-prod').textContent        = s.produce_count || '—';
  document.getElementById('pu-buy').textContent         = s.buy_signals   || '—';
  document.getElementById('pu-sell').textContent        = s.sell_signals  || '—';
  document.getElementById('pu-units').textContent       = s.unit_count    || '—';
  document.getElementById('pu-prod-badge').textContent  = (s.produce_count || '—') + ' Types';
  document.getElementById('pu-units-badge').textContent = (s.unit_count    || '—') + ' Units';

  const signalBadge = sig => {
    if (sig === 'buy')  return '<span class="spill sp-sy">Buy</span>';
    if (sig === 'sell') return '<span class="spill sp-of">Sell</span>';
    return '<span class="spill" style="background:var(--sur2);color:var(--mut)">Hold</span>';
  };

  document.getElementById('produce-tbody').innerHTML = (d.produces || []).map(p => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:28px;height:28px;border-radius:8px;background:${hexToRgba(p.accent_color||'#6b7280',.15)};display:flex;align-items:center;justify-content:center;font-size:15px">${p.emoji}</div>
          <div>
            <div style="font-weight:600">${p.name}</div>
            <div style="font-size:9px;color:var(--mut);font-family:var(--fm)">${p.slug}</div>
          </div>
        </div>
      </td>
      <td style="color:var(--txt2)">${p.primary_location || '—'}</td>
      <td class="tm">${p.price_fmt}/kg</td>
      <td class="tm ${p.change_percent >= 0 ? 'tg' : 'tr'}">${p.change_percent >= 0 ? '▲' : '▼'} ${Math.abs(p.change_percent)}%</td>
      <td>${signalBadge(p.signal)}</td>
      <td class="tm" style="color:var(--mut)">${p.txn_count}</td>
      <td><div class="fact-btns">
        <button class="abtn abtn-e" onclick='openEditProduce(${p.id},${JSON.stringify(p).replace(/"/g,"&quot;")})'><i class="ti ti-pencil"></i></button>
        <button class="abtn abtn-d" onclick="askDelete('/produce-types/${p.id}','${p.name}')"><i class="ti ti-trash"></i></button>
      </div></td>
    </tr>
  `).join('');

  document.getElementById('units-tbody').innerHTML = (d.units || []).map(u => `
    <tr>
      <td style="font-weight:600">${u.name}</td>
      <td class="tm" style="color:var(--acc)">${u.symbol}</td>
      <td class="tm" style="color:var(--mut)">${u.base_kg ? u.base_kg + ' kg' : '—'}</td>
      <td class="tm" style="color:var(--mut)">${u.txn_count}</td>
      <td><div class="fact-btns">
        <button class="abtn abtn-e" onclick='openEditUnit(${u.id},${JSON.stringify(u).replace(/"/g,"&quot;")})'><i class="ti ti-pencil"></i></button>
        <button class="abtn abtn-d" onclick="askDelete('/units/${u.id}','${u.name}')"><i class="ti ti-trash"></i></button>
      </div></td>
    </tr>
  `).join('');
}

let produceEditId = null;

function openNewProduce() {
  produceEditId = null;
  document.getElementById('modal-produce-title').textContent = 'New Produce Type';
  document.getElementById('produce-submit-btn').textContent  = 'Add Produce';
  document.getElementById('f-prod-emoji').value    = '';
  document.getElementById('f-prod-name').value     = '';
  document.getElementById('f-prod-price').value    = '';
  document.getElementById('f-prod-change').value   = '0';
  document.getElementById('f-prod-signal').value   = 'hold';
  document.getElementById('f-prod-location').value = '';
  document.getElementById('f-prod-color').value    = '#6b7280';
  clearErrors();
  openModal('modal-produce');
}

function openEditProduce(id, data) {
  produceEditId = id;
  document.getElementById('modal-produce-title').textContent = 'Edit — ' + data.name;
  document.getElementById('produce-submit-btn').textContent  = 'Save Changes';
  document.getElementById('f-prod-emoji').value    = data.emoji            || '';
  document.getElementById('f-prod-name').value     = data.name;
  document.getElementById('f-prod-price').value    = data.current_price    || 0;
  document.getElementById('f-prod-change').value   = data.change_percent   || 0;
  document.getElementById('f-prod-signal').value   = data.signal           || 'hold';
  document.getElementById('f-prod-location').value = data.primary_location || '';
  document.getElementById('f-prod-color').value    = data.accent_color     || '#6b7280';
  clearErrors();
  openModal('modal-produce');
}

async function submitProduce() {
  clearErrors();
  const body = {
    name:             document.getElementById('f-prod-name').value,
    emoji:            document.getElementById('f-prod-emoji').value    || null,
    current_price:    +document.getElementById('f-prod-price').value   || 0,
    change_percent:   +document.getElementById('f-prod-change').value  || 0,
    signal:           document.getElementById('f-prod-signal').value,
    primary_location: document.getElementById('f-prod-location').value || null,
    accent_color:     document.getElementById('f-prod-color').value    || null,
  };

  const url    = produceEditId ? '/produce-types/' + produceEditId : '/produce-types';
  const method = produceEditId ? 'PUT' : 'POST';
  const res    = await api(url, method, body);

  if (res.success) {
    closeModal('modal-produce');
    pageCache['pu'] = null;
    loadPage('pu');
    showToast(produceEditId ? 'Produce type updated!' : 'Produce type added!');
  } else if (res.errors) {
    showErrors({'prod-name': res.errors.name});
  }
}

let unitEditId = null;

function openNewUnit() {
  unitEditId = null;
  document.getElementById('modal-unit-title').textContent = 'New Unit';
  document.getElementById('unit-submit-btn').textContent  = 'Add Unit';
  document.getElementById('f-unit-name').value    = '';
  document.getElementById('f-unit-symbol').value  = '';
  document.getElementById('f-unit-base-kg').value = '';
  clearErrors();
  openModal('modal-unit');
}

function openEditUnit(id, data) {
  unitEditId = id;
  document.getElementById('modal-unit-title').textContent = 'Edit — ' + data.name;
  document.getElementById('unit-submit-btn').textContent  = 'Save Changes';
  document.getElementById('f-unit-name').value    = data.name;
  document.getElementById('f-unit-symbol').value  = data.symbol;
  document.getElementById('f-unit-base-kg').value = data.base_kg || '';
  clearErrors();
  openModal('modal-unit');
}

async function submitUnit() {
  clearErrors();
  const body = {
    name:    document.getElementById('f-unit-name').value,
    symbol:  document.getElementById('f-unit-symbol').value,
    base_kg: +document.getElementById('f-unit-base-kg').value || null,
  };

  const url    = unitEditId ? '/units/' + unitEditId : '/units';
  const method = unitEditId ? 'PUT' : 'POST';
  const res    = await api(url, method, body);

  if (res.success) {
    closeModal('modal-unit');
    pageCache['pu'] = null;
    loadPage('pu');
    showToast(unitEditId ? 'Unit updated!' : 'Unit added!');
  } else if (res.errors) {
    const mapped = {};
    if (res.errors.name)   mapped['unit-name']   = res.errors.name;
    if (res.errors.symbol) mapped['unit-symbol']  = res.errors.symbol;
    showErrors(mapped);
  }
}

// ─── MOBILE AGENTS ───────────────────────────────────────────────────────────
function renderMobileAgents(d) {
  const s = d.stats || {};
  document.getElementById('ma-total').textContent   = s.total      || '—';
  document.getElementById('ma-active').textContent  = s.active     || '—';
  document.getElementById('ma-inactive').textContent= s.inactive   || '—';
  document.getElementById('ma-login').textContent   = s.with_login || '—';
  document.getElementById('ma-badge').textContent   = (s.total || '—') + ' Agents';

  const statusBadge = a =>
    a.is_active
      ? '<span class="spill sp-sy">Active</span>'
      : '<span class="spill sp-of">Inactive</span>';

  const loginBadge = a =>
    a.has_login
      ? '<span class="spill" style="background:var(--bdim);color:var(--blu)">App Login</span>'
      : '<span class="spill" style="background:var(--sur2);color:var(--mut)">No Login</span>';

  const tbody = document.getElementById('agents-tbody');
  tbody.innerHTML = (d.agents || []).map(a => {
    const av = a.avatar_color
      ? `background:${hexToRgba(a.avatar_color,0.15)};border-color:${hexToRgba(a.avatar_color,0.4)};color:${a.avatar_color}`
      : 'background:var(--bdim);border-color:var(--bbdr);color:var(--blu)';
    return `<tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;${av}">${a.initials}</div>
          <span>${a.name}</span>
        </div>
      </td>
      <td style="color:var(--txt2)">${a.region}</td>
      <td class="tm" style="color:var(--mut)">${a.base_location}</td>
      <td class="tm" style="color:var(--txt2)">${a.phone}</td>
      <td class="tm" style="color:var(--txt2)">${a.email || '—'}</td>
      <td>${loginBadge(a)}</td>
      <td>${statusBadge(a)}</td>
      <td class="tm" style="color:var(--mut)">${a.trips_count}</td>
      <td class="tm" style="color:var(--mut)">${a.txn_count}</td>
      <td><div class="fact-btns">
        <button class="abtn abtn-e" onclick='openEditAgent(${a.id},${JSON.stringify(a).replace(/"/g,"&quot;")})'><i class="ti ti-pencil"></i></button>
        <button class="abtn ${a.is_active ? 'abtn-d' : 'abtn-u'}" onclick="toggleAgentStatus(${a.id},'${a.name.replace(/'/g,"\\'")}',${a.is_active})">
          <i class="ti ${a.is_active ? 'ti-player-pause' : 'ti-player-play'}"></i>
        </button>
        <button class="abtn abtn-d" onclick="askDelete('/agents/${a.id}','${a.name.replace(/'/g,"\\'")}')"><i class="ti ti-trash"></i></button>
      </div></td>
    </tr>`;
  }).join('');
}

let agentEditId = null;

function openNewAgent() {
  agentEditId = null;
  document.getElementById('modal-agent-title').textContent = 'New Agent';
  document.getElementById('agent-submit-btn').textContent  = 'Create Agent';
  document.getElementById('f-agent-pw-lbl').textContent    = 'App Password (optional)';
  document.getElementById('f-agent-name').value         = '';
  document.getElementById('f-agent-region').value       = '';
  document.getElementById('f-agent-base').value         = '';
  document.getElementById('f-agent-phone').value        = '';
  document.getElementById('f-agent-email').value        = '';
  document.getElementById('f-agent-password').value     = '';
  document.getElementById('f-agent-color').value        = '';
  document.getElementById('f-agent-active').checked     = true;
  clearErrors();
  openModal('modal-agent');
}

function openEditAgent(id, data) {
  agentEditId = id;
  document.getElementById('modal-agent-title').textContent = 'Edit — ' + data.name;
  document.getElementById('agent-submit-btn').textContent  = 'Save Changes';
  document.getElementById('f-agent-pw-lbl').textContent    = 'New App Password (leave blank to keep)';
  document.getElementById('f-agent-name').value         = data.name;
  document.getElementById('f-agent-region').value       = data.region;
  document.getElementById('f-agent-base').value         = data.base_location !== '—' ? data.base_location : '';
  document.getElementById('f-agent-phone').value        = data.phone !== '—' ? data.phone : '';
  document.getElementById('f-agent-email').value        = data.email || '';
  document.getElementById('f-agent-password').value     = '';
  document.getElementById('f-agent-color').value        = data.avatar_color || '';
  document.getElementById('f-agent-active').checked     = data.is_active;
  clearErrors();
  openModal('modal-agent');
}

async function submitAgent() {
  clearErrors();
  const body = {
    name:          document.getElementById('f-agent-name').value,
    region:        document.getElementById('f-agent-region').value,
    base_location: document.getElementById('f-agent-base').value  || null,
    phone:         document.getElementById('f-agent-phone').value || null,
    email:         document.getElementById('f-agent-email').value || null,
    password:      document.getElementById('f-agent-password').value || null,
    avatar_color:  document.getElementById('f-agent-color').value || null,
    is_active:     document.getElementById('f-agent-active').checked,
  };

  const url    = agentEditId ? '/agents/' + agentEditId : '/agents';
  const method = agentEditId ? 'PUT' : 'POST';
  const res    = await api(url, method, body);

  if (res.success) {
    closeModal('modal-agent');
    pageCache['ma'] = null;
    loadPage('ma');
    showToast(agentEditId ? 'Agent updated!' : 'Agent created!');
  } else if (res.errors) {
    const mapped = {};
    if (res.errors.name)     mapped['agent-name']     = res.errors.name;
    if (res.errors.region)   mapped['agent-region']   = res.errors.region;
    if (res.errors.email)    mapped['agent-email']    = res.errors.email;
    if (res.errors.password) mapped['agent-password'] = res.errors.password;
    showErrors(mapped);
  }
}

async function toggleAgentStatus(id, name, currentlyActive) {
  const action = currentlyActive ? 'deactivate' : 'activate';
  if (currentlyActive && !confirm(`Deactivate "${name}"? This will revoke all mobile sessions.`)) return;
  const res = await api('/agents/' + id + '/toggle', 'PATCH');
  if (res.success) {
    pageCache['ma'] = null;
    loadPage('ma');
    showToast(`${name} ${res.is_active ? 'activated' : 'deactivated'}.`);
  }
}

// ─── FIELD MAP ───────────────────────────────────────────────────────────────
let mapInstance = null;
let mapMarkers  = [];

function renderMap(d) {
  document.getElementById('mp-count').textContent  = d.count  ?? '—';
  document.getElementById('mp-agents').textContent = (d.agents ?? []).length || '—';
  document.getElementById('mp-badge').textContent  = (d.count ?? '—') + ' Points';

  document.getElementById('mp-legend-list').innerHTML = (d.agents || []).map(a => `
    <div style="display:flex;align-items:center;gap:8px;padding:7px 12px;border-bottom:1px solid var(--bdr)">
      <div style="width:10px;height:10px;border-radius:50%;background:${a.color};flex-shrink:0"></div>
      <span style="font-size:12px;font-weight:600;color:var(--txt)">${a.name}</span>
    </div>
  `).join('');

  setTimeout(() => {
    if (!mapInstance) {
      mapInstance = L.map('mp-map').setView([1.3733, 32.2903], 7);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
      }).addTo(mapInstance);
    } else {
      mapMarkers.forEach(m => m.remove());
      mapMarkers = [];
    }

    (d.points || []).forEach(p => {
      const m = L.circleMarker([p.lat, p.lng], {
        radius: 7, fillColor: p.agent_color, color: '#fff',
        weight: 1.5, opacity: 1, fillOpacity: 0.85,
      }).addTo(mapInstance);

      m.bindPopup(`
        <div style="min-width:155px;font-family:sans-serif">
          <div style="font-weight:700;font-size:13px;margin-bottom:3px">${p.emoji} ${p.produce}</div>
          <div style="font-size:11px;color:#888;margin-bottom:4px">${p.agent_name}</div>
          ${p.qty_kg    ? `<div style="font-size:12px">${Number(p.qty_kg).toLocaleString()} kg</div>` : ''}
          ${p.amount    ? `<div style="font-size:12px;font-weight:600">${p.currency} ${Number(Math.abs(p.amount)).toLocaleString()}</div>` : ''}
          ${p.location  ? `<div style="font-size:11px;color:#999;margin-top:2px">${p.location}</div>` : ''}
          ${p.moisture  ? `<div style="font-size:11px;color:#777">Moisture: ${p.moisture}%</div>` : ''}
          <div style="font-size:10px;color:#aaa;margin-top:4px">${p.date}</div>
        </div>
      `);
      mapMarkers.push(m);
    });

    if (mapMarkers.length > 0) {
      try { mapInstance.fitBounds(L.latLngBounds(mapMarkers.map(m => m.getLatLng())), {padding:[40,40]}); } catch(e) {}
    }
    mapInstance.invalidateSize();
  }, 120);
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function hexToRgba(hex, alpha) {
  const r = parseInt(hex.slice(1,3),16);
  const g = parseInt(hex.slice(3,5),16);
  const b = parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${alpha})`;
}
</script>
@endsection
