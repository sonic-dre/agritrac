@php
$eacCurrencies = ['UGX','USD','KES','TZS','RWF','BIF','ETB','SOS','SSP','CDF','EUR','GBP'];
@endphp

{{-- ══════════════════════════════════════════
     TRIP MODAL  (create + edit)
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-trip" onclick="overlayClose(event,'modal-trip')">
  <div class="modal-box">
    <div class="mh">
      <div class="mt" id="modal-trip-title">New Trip</div>
      <button class="mclose" onclick="closeModal('modal-trip')">✕</button>
    </div>
    <div class="mbody">
      <div class="frow" id="trip-create-fields">
        <div class="fgrp" style="grid-column:1/-1">
          <div class="flbl">Agent *</div>
          <select class="finp" id="f-trip-agent">
            <option value="">Select agent…</option>
            @foreach($agents as $a)
            <option value="{{ $a->id }}">{{ $a->name }}</option>
            @endforeach
          </select>
          <div class="ferr" id="err-agent_id"></div>
        </div>
        <div class="fgrp" style="grid-column:1/-1">
          <div class="flbl">Region *</div>
          <input class="finp" type="text" id="f-trip-region" placeholder="e.g. Mbale / Sironko">
          <div class="ferr" id="err-region"></div>
        </div>
        <div class="fgrp" style="grid-column:1/-1">
          <div class="flbl">Produce (select all that apply) *</div>
          <div class="fcheck-grid" id="produce-check-grid">
            @foreach($produceTypes as $p)
            <label class="fcheck"><input type="checkbox" class="trip-produce" value="{{ $p->name }}"> {{ $p->emoji }} {{ $p->name }}</label>
            @endforeach
          </div>
          <div class="ferr" id="err-produce_list"></div>
          {{-- Quick-add produce --}}
          <div class="qa-toggle" onclick="toggleQuickAdd('qa-produce')">➕ Add new produce type</div>
          <div class="qa-panel" id="qa-produce" style="display:none">
            <div class="qa-row">
              <input class="finp" type="text" id="qa-produce-emoji" placeholder="Emoji e.g. 🌽" style="width:72px">
              <input class="finp" type="text" id="qa-produce-name" placeholder="Produce name e.g. Sorghum" style="flex:1">
              <button class="hbtn hb-p" style="white-space:nowrap" onclick="quickAddProduce()">Add</button>
            </div>
            <div class="ferr" id="err-qa-produce"></div>
          </div>
        </div>
        <div class="fgrp">
          <div class="flbl">Start Date *</div>
          <input class="finp" type="date" id="f-trip-date">
          <div class="ferr" id="err-start_date"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Duration (days) *</div>
          <input class="finp" type="number" id="f-trip-days" min="1" max="30" placeholder="e.g. 5">
          <div class="ferr" id="err-total_days"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Pre-negotiated Price (per kg)</div>
          <div class="mny-wrap">
            <input class="finp" type="number" id="f-trip-neg-price" min="1" placeholder="Optional">
            <select class="finp mny-cur" id="f-trip-neg-currency">
              @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="ferr" id="err-negotiated_price_per_kg"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Payment Type *</div>
          <select class="finp" id="f-trip-payment-type" onchange="tripPaymentTypeChange()">
            <option value="advance">Advance (partial payment issued)</option>
            <option value="full">Full Payment (paid in full)</option>
          </select>
        </div>
        <div class="fgrp" style="grid-column:1/-1">
          <div class="flbl" id="f-trip-amount-lbl">Advance Amount *</div>
          <div class="mny-wrap">
            <input class="finp" type="number" id="f-trip-advance" min="0" placeholder="e.g. 15000000">
            <select class="finp mny-cur" id="f-trip-currency">
              @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="ferr" id="err-advance_amount"></div>
        </div>
      </div>

      {{-- Edit-only fields --}}
      <div id="trip-edit-fields" style="display:none">
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Status</div>
            <select class="finp" id="f-trip-status">
              <option value="departing">Departing</option>
              <option value="in_progress">In Progress</option>
              <option value="returning">Returning</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="fgrp">
            <div class="flbl">Sync Status</div>
            <select class="finp" id="f-trip-sync">
              <option value="synced">Synced</option>
              <option value="pending">Pending</option>
              <option value="offline">Offline</option>
            </select>
          </div>
          <div class="fgrp">
            <div class="flbl">Offline Hours</div>
            <input class="finp" type="number" id="f-trip-offline" min="0" placeholder="0">
          </div>
        </div>
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Current Day</div>
            <input class="finp" type="number" id="f-trip-day" min="0" placeholder="0">
          </div>
          <div class="fgrp">
            <div class="flbl">Tonnage (kg)</div>
            <input class="finp" type="number" id="f-trip-tonnage" min="0" step="100" placeholder="0">
          </div>
          <div class="fgrp">
            <div class="flbl">Advance</div>
            <div class="mny-wrap">
              <input class="finp" type="number" id="f-trip-adv-edit" min="0" placeholder="0">
              <select class="finp mny-cur" id="f-trip-edit-currency">
                @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="fgrp">
          <div class="flbl">Amount Spent</div>
          <div class="mny-wrap">
            <input class="finp" type="number" id="f-trip-spent" min="0" placeholder="0">
          </div>
        </div>
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-trip')">Cancel</button>
      <button class="hbtn hb-p" id="trip-submit-btn" onclick="submitTrip()">Create Trip</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     TRANSACTION MODAL
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-txn" onclick="overlayClose(event,'modal-txn')">
  <div class="modal-box">
    <div class="mh">
      <div class="mt">New Transaction</div>
      <button class="mclose" onclick="closeModal('modal-txn')">✕</button>
    </div>
    <div class="mbody">
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Type *</div>
          <select class="finp" id="f-txn-type" onchange="txnTypeChange()">
            <option value="purchase">Purchase</option>
            <option value="expense">Expense</option>
            <option value="advance">Advance</option>
          </select>
          <div class="ferr" id="err-type"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Date *</div>
          <input class="finp" type="date" id="f-txn-date">
          <div class="ferr" id="err-transaction_date"></div>
        </div>
      </div>
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Agent</div>
          <select class="finp" id="f-txn-agent">
            <option value="">HQ / None</option>
            @foreach($agents as $a)
            <option value="{{ $a->id }}">{{ $a->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="fgrp">
          <div class="flbl">Trip</div>
          <select class="finp" id="f-txn-trip">
            <option value="">No trip</option>
          </select>
        </div>
      </div>

      {{-- Purchase fields --}}
      <div id="txn-purchase-fields">
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Produce *</div>
            <select class="finp" id="f-txn-produce">
              <option value="">Select…</option>
              @foreach($produceTypes as $p)
              <option value="{{ $p->id }}">{{ $p->emoji }} {{ $p->name }}</option>
              @endforeach
            </select>
            <div class="ferr" id="err-produce_type_id"></div>
          </div>
          <div class="fgrp">
            <div class="flbl">Location *</div>
            <input class="finp" type="text" id="f-txn-location" placeholder="e.g. Sironko market">
          </div>
        </div>
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Quantity *</div>
            <div class="mny-wrap">
              <input class="finp" type="number" id="f-txn-qty" min="0" step="50" placeholder="0">
              <select class="finp mny-cur" id="f-txn-unit" style="min-width:96px">
                <option value="">kg</option>
                @foreach($units as $u)
                <option value="{{ $u->id }}">{{ $u->symbol }}</option>
                @endforeach
              </select>
            </div>
            <div class="ferr" id="err-quantity_kg"></div>
            {{-- Quick-add unit --}}
            <div class="qa-toggle" onclick="toggleQuickAdd('qa-unit')">➕ Add unit</div>
            <div class="qa-panel" id="qa-unit" style="display:none">
              <div class="qa-row">
                <input class="finp" type="text" id="qa-unit-name" placeholder="Name e.g. 90kg Sack" style="flex:1">
                <input class="finp" type="text" id="qa-unit-symbol" placeholder="Symbol e.g. sack" style="width:90px">
                <input class="finp" type="number" id="qa-unit-base-kg" placeholder="kg equiv." step="0.001" style="width:90px">
                <button class="hbtn hb-p" style="white-space:nowrap" onclick="quickAddUnit()">Add</button>
              </div>
              <div class="ferr" id="err-qa-unit"></div>
            </div>
          </div>
          <div class="fgrp">
            <div class="flbl">Unit Price *</div>
            <div class="mny-wrap">
              <input class="finp" type="number" id="f-txn-price" min="0" placeholder="0">
              <select class="finp mny-cur" id="f-txn-currency">
                @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
              </select>
            </div>
            <div class="ferr" id="err-unit_price"></div>
          </div>
        </div>
      </div>

      {{-- Expense fields --}}
      <div id="txn-expense-fields" style="display:none">
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Category *</div>
            <select class="finp" id="f-txn-category">
              <option value="fuel">⛽ Fuel</option>
              <option value="labour">👷 Labour</option>
              <option value="packaging">📦 Packaging</option>
              <option value="levies">🏛️ Levies</option>
              <option value="maintenance">🔧 Maintenance</option>
              <option value="other">💰 Other</option>
            </select>
          </div>
          <div class="fgrp">
            <div class="flbl">Amount *</div>
            <div class="mny-wrap">
              <input class="finp" type="number" id="f-txn-exp-amount" min="0" placeholder="0">
              <select class="finp mny-cur" id="f-txn-exp-currency">
                @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="fgrp">
          <div class="flbl">Location</div>
          <input class="finp" type="text" id="f-txn-exp-location" placeholder="e.g. Total Mbale">
        </div>
      </div>

      {{-- Advance fields --}}
      <div id="txn-advance-fields" style="display:none">
        <div class="frow">
          <div class="fgrp">
            <div class="flbl">Amount *</div>
            <div class="mny-wrap">
              <input class="finp" type="number" id="f-txn-adv-amount" min="0" placeholder="0">
              <select class="finp mny-cur" id="f-txn-adv-currency">
                @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
              </select>
            </div>
          </div>
          <div class="fgrp">
            <div class="flbl">Location</div>
            <input class="finp" type="text" id="f-txn-adv-location" placeholder="e.g. Kampala HQ">
          </div>
        </div>
      </div>

      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Sync Status</div>
          <select class="finp" id="f-txn-sync">
            <option value="synced">Synced</option>
            <option value="pending">Pending</option>
            <option value="offline">Offline</option>
          </select>
        </div>
        <div class="fgrp">
          <div class="flbl">Notes</div>
          <input class="finp" type="text" id="f-txn-notes" placeholder="Optional…">
        </div>
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-txn')">Cancel</button>
      <button class="hbtn hb-p" onclick="submitTransaction()">Save Transaction</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     EXPENSE MODAL
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-expense" onclick="overlayClose(event,'modal-expense')">
  <div class="modal-box" style="width:420px">
    <div class="mh">
      <div class="mt" id="modal-expense-title">New Expense</div>
      <button class="mclose" onclick="closeModal('modal-expense')">✕</button>
    </div>
    <div class="mbody">
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Category *</div>
          <select class="finp" id="f-exp-category">
            <option value="fuel">⛽ Fuel &amp; Transport</option>
            <option value="labour">👷 Driver &amp; Labour</option>
            <option value="packaging">📦 Packaging / Bags</option>
            <option value="levies">🏛️ Levies &amp; Permits</option>
            <option value="maintenance">🔧 Vehicle Maintenance</option>
            <option value="other">💰 Other</option>
          </select>
          <div class="ferr" id="err-exp-category"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Amount *</div>
          <div class="mny-wrap">
            <input class="finp" type="number" id="f-exp-amount" min="1" placeholder="e.g. 1200000">
            <select class="finp mny-cur" id="f-exp-currency">
              @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="ferr" id="err-exp-amount"></div>
        </div>
      </div>
      <div class="fgrp">
        <div class="flbl">Label *</div>
        <input class="finp" type="text" id="f-exp-label" placeholder="e.g. Fuel & Transport">
        <div class="ferr" id="err-exp-label"></div>
      </div>
      <div class="fgrp">
        <div class="flbl">Sub-label</div>
        <input class="finp" type="text" id="f-exp-sublabel" placeholder="e.g. 14 trips, avg 220L">
      </div>
      <div class="fgrp">
        <div class="flbl">Date *</div>
        <input class="finp" type="date" id="f-exp-date">
        <div class="ferr" id="err-exp-date"></div>
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-expense')">Cancel</button>
      <button class="hbtn hb-p" id="exp-submit-btn" onclick="submitExpense()">Save Expense</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     PRICE UPDATE MODAL
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-price" onclick="overlayClose(event,'modal-price')">
  <div class="modal-box" style="width:420px">
    <div class="mh">
      <div class="mt" id="modal-price-title">Update Price</div>
      <button class="mclose" onclick="closeModal('modal-price')">✕</button>
    </div>
    <div class="mbody">
      <input type="hidden" id="f-price-id">
      <div class="frow">
        <div class="fgrp" style="grid-column:1/-1">
          <div class="flbl">Price per kg *</div>
          <div class="mny-wrap">
            <input class="finp" type="number" id="f-price-val" min="1" placeholder="0">
            <select class="finp mny-cur" id="f-price-currency">
              @foreach($eacCurrencies as $c)<option value="{{ $c }}"{{ $c === 'UGX' ? ' selected' : '' }}>{{ $c }}</option>@endforeach
            </select>
          </div>
          <div class="ferr" id="err-price-val"></div>
        </div>
      </div>
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Change % *</div>
          <input class="finp" type="number" id="f-price-change" step="0.1" placeholder="e.g. 5.2 or -1.8">
        </div>
        <div class="fgrp">
          <div class="flbl">Signal *</div>
          <select class="finp" id="f-price-signal">
            <option value="buy">↑ Buy Now</option>
            <option value="hold">→ Hold</option>
            <option value="sell">↓ Sell Fast</option>
          </select>
        </div>
      </div>
      <div class="fgrp">
        <div class="flbl">Location *</div>
        <input class="finp" type="text" id="f-price-location" placeholder="e.g. Sironko">
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-price')">Cancel</button>
      <button class="hbtn hb-p" onclick="submitPrice()">Update Price</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     USER MODAL  (create + edit)
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-user" onclick="overlayClose(event,'modal-user')">
  <div class="modal-box" style="width:440px">
    <div class="mh">
      <div class="mt" id="modal-user-title">New User</div>
      <button class="mclose" onclick="closeModal('modal-user')">✕</button>
    </div>
    <div class="mbody">
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Full Name *</div>
          <input class="finp" type="text" id="f-user-name" placeholder="e.g. John Doe">
          <div class="ferr" id="err-user-name"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Role *</div>
          <select class="finp" id="f-user-role">
            <option value="viewer">Viewer — Read only</option>
            <option value="manager">Manager — Full CRUD</option>
            <option value="admin">Admin — All access</option>
          </select>
          <div class="ferr" id="err-user-role"></div>
        </div>
      </div>
      <div class="fgrp">
        <div class="flbl">Email Address *</div>
        <input class="finp" type="email" id="f-user-email" placeholder="user@agritrack.ug">
        <div class="ferr" id="err-user-email"></div>
      </div>
      <div class="fgrp">
        <div class="flbl" id="f-user-pw-lbl">Password *</div>
        <input class="finp" type="password" id="f-user-password" placeholder="Min. 8 characters">
        <div class="ferr" id="err-user-password"></div>
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-user')">Cancel</button>
      <button class="hbtn hb-p" id="user-submit-btn" onclick="submitUser()">Create User</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     MOBILE AGENT MODAL  (create + edit)
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-agent" onclick="overlayClose(event,'modal-agent')">
  <div class="modal-box" style="width:480px">
    <div class="mh">
      <div class="mt" id="modal-agent-title">New Agent</div>
      <button class="mclose" onclick="closeModal('modal-agent')">✕</button>
    </div>
    <div class="mbody">
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Full Name *</div>
          <input class="finp" type="text" id="f-agent-name" placeholder="e.g. John Mukasa">
          <div class="ferr" id="err-agent-name"></div>
        </div>
        <div class="fgrp">
          <div class="flbl">Region *</div>
          <input class="finp" type="text" id="f-agent-region" placeholder="e.g. Mbale / Sironko">
          <div class="ferr" id="err-agent-region"></div>
        </div>
      </div>
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Base Location</div>
          <input class="finp" type="text" id="f-agent-base" placeholder="e.g. Mbale Town">
        </div>
        <div class="fgrp">
          <div class="flbl">Phone</div>
          <input class="finp" type="text" id="f-agent-phone" placeholder="e.g. +256 700 000000">
        </div>
      </div>
      <div class="fdivider"></div>
      <div style="font-size:10px;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.8px;font-family:var(--fm);margin-bottom:10px">Mobile App Credentials</div>
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Email (app login)</div>
          <input class="finp" type="email" id="f-agent-email" placeholder="agent@example.com">
          <div class="ferr" id="err-agent-email"></div>
        </div>
        <div class="fgrp">
          <div class="flbl" id="f-agent-pw-lbl">App Password (optional)</div>
          <input class="finp" type="password" id="f-agent-password" placeholder="Min. 8 characters">
          <div class="ferr" id="err-agent-password"></div>
        </div>
      </div>
      <div class="fdivider"></div>
      <div class="frow">
        <div class="fgrp">
          <div class="flbl">Avatar Colour</div>
          <input class="finp" type="text" id="f-agent-color" placeholder="e.g. #3fb950">
        </div>
        <div class="fgrp" style="display:flex;align-items:center;gap:10px;padding-top:18px">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
            <input type="checkbox" id="f-agent-active" checked style="accent-color:var(--acc);width:16px;height:16px">
            <span>Active (can use mobile app)</span>
          </label>
        </div>
      </div>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-agent')">Cancel</button>
      <button class="hbtn hb-p" id="agent-submit-btn" onclick="submitAgent()">Create Agent</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════
     CONFIRM DELETE MODAL
══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-confirm" onclick="overlayClose(event,'modal-confirm')">
  <div class="modal-box" style="width:380px">
    <div class="mh">
      <div class="mt">Confirm Delete</div>
      <button class="mclose" onclick="closeModal('modal-confirm')">✕</button>
    </div>
    <div class="mbody">
      <p style="font-size:13px;color:var(--txt2);line-height:1.6" id="confirm-msg">Are you sure you want to delete this record? This cannot be undone.</p>
    </div>
    <div class="mft">
      <button class="hbtn hb-s" onclick="closeModal('modal-confirm')">Cancel</button>
      <button class="hbtn" style="background:var(--rdim);border:1px solid rgba(248,81,73,.25);color:var(--red)" id="confirm-ok-btn" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>
