@extends('layouts.customer')

@section('container_class', 'max-w-none w-full')
@section('main_class', 'p-0 !mt-0')

@section('content')
<div class="flex flex-col h-[100dvh] bg-[#f0f2f5] md:bg-white overflow-hidden">
  <!-- Header Tabs -->
  @if($tenant->business_type === 'cafe')
  <div class="flex border-b border-kv-border bg-gray-50 flex-none">
    <a href="{{ route('customer.tables') }}" class="flex-1 py-2.5 md:py-3 text-center border-b-2 border-transparent text-gray-500 font-medium text-[13px] md:text-[14px] flex items-center justify-center gap-2 hover:bg-gray-100 transition">
        <i data-feather="grid" class="w-4 h-4"></i> {{ __('ui.customer.table_map') }}
    </a>
    <a href="#" class="flex-1 py-2.5 md:py-3 text-center border-b-2 border-kv-blue text-kv-blue font-bold text-[13px] md:text-[14px] flex items-center justify-center gap-2 bg-white">
        <i data-feather="file-text" class="w-4 h-4"></i> {{ __('ui.customer.orders') }} @if($table) - {{ $table->name }} @endif
    </a>
  </div>
  @endif

  <div class="flex flex-col md:flex-row flex-1 overflow-hidden relative w-full">
    
    <!-- Trái: Menu -->
    <div class="flex-1 flex flex-col bg-transparent md:bg-white md:border-r border-kv-border overflow-hidden relative w-full">
      <div class="p-2 md:p-3 bg-white flex gap-3 flex-none shadow-sm md:shadow-none border-b border-gray-100">
        <div class="relative flex-1">
          <i data-feather="search" class="w-4 h-4 absolute left-3 top-2.5 md:top-3 text-gray-400"></i>
          <input type="text" id="pos-search" placeholder="{{ __('ui.customer.search_placeholder') }}" class="w-full pl-9 pr-4 py-2 md:py-2.5 text-[14px] border border-gray-200 rounded-lg focus:border-kv-blue focus:ring-1 focus:ring-kv-blue focus:outline-none placeholder-gray-400 transition-all bg-gray-50 focus:bg-white">
        </div>
      </div>

      <div class="bg-white border-b border-gray-100 flex overflow-x-auto no-scrollbar p-1.5 gap-1.5 flex-none">
        <button onclick="filterCat('all', this)" class="cat-tab px-4 py-1.5 rounded-md border text-kv-blue border-kv-blue font-bold text-[13px] whitespace-nowrap bg-blue-50 transition-all focus:outline-none">{{ __('ui.customer.all_categories') }}</button>
        @foreach ($categories as $cat)
          @if ($cat->products->where('is_active', 1)->isEmpty()) @continue @endif
          <button onclick="filterCat({{ $cat->id }}, this)" class="cat-tab px-4 py-1.5 rounded-md border border-transparent text-gray-500 font-medium text-[13px] hover:text-kv-blue whitespace-nowrap transition-all focus:outline-none">{{ $cat->name }}</button>
        @endforeach
      </div>
      
      <div class="flex-1 overflow-y-auto p-2" id="pos-grid">
        <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2">
          @foreach ($categories as $cat)
            @foreach ($cat->products->where('is_active', 1) as $p)
            <div class="prod-item relative bg-white border border-gray-200 rounded-xl p-3 text-center cursor-pointer hover:border-kv-blue transition active:scale-95 select-none flex flex-col justify-center gap-1 h-28 md:h-40 shadow-sm"
                 data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}"
                 onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})">
              <div class="text-[14px] md:text-[16px] font-bold text-gray-800 leading-tight line-clamp-2 px-1">{{ $p->name }}</div>
              <div class="text-[14px] md:text-[16px] font-black text-gray-800 mt-1">{{ number_format($p->price, 0, ',', '.') }}</div>
              
              <div class="absolute bottom-2 right-2 border border-gray-200 text-gray-500 rounded-full w-6 h-6 flex items-center justify-center bg-white hover:bg-gray-50">
                <i data-feather="plus" class="w-3 h-3"></i>
              </div>
            </div>
            @endforeach
          @endforeach
        </div>
      </div>
    </div>

    <!-- Phải: Cart -->
    <div id="cart-section" class="flex-none h-[45vh] md:h-full w-full md:w-[400px] lg:w-[450px] bg-white md:border-l border-kv-border flex flex-col shadow-[0_-4px_15px_-4px_rgba(0,0,0,0.1)] md:shadow-none z-30">
      <div class="px-3 py-2 border-b border-kv-border bg-white flex justify-between items-center text-[14px] flex-none">
        <div class="font-bold text-gray-800 flex items-center gap-2">
            <i data-feather="shopping-cart" class="w-4 h-4 text-gray-500"></i> {{ __('ui.customer.items_ordered') }}
            <span id="sync-badge" class="hidden bg-orange-100 text-orange-600 text-[10px] px-1.5 py-0.5 rounded-full border border-orange-200 animate-pulse">Syncing <span id="sync-count">0</span></span>
        </div>
        <button onclick="clearCart()" class="text-red-500 hover:bg-red-50 p-1.5 rounded"><i data-feather="trash-2" class="w-4 h-4"></i></button>
      </div>

      <div class="flex-1 overflow-y-auto flex flex-col no-scrollbar px-2 bg-white pb-2">
        <div id="old-items-zone">
            @if($activeOrder && $activeOrder->items->count() > 0)
            <div class="px-2 py-1.5 bg-gray-50 border-b border-gray-100 flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase sticky top-0 z-10">
                <i data-feather="clock" class="w-3.5 h-3.5"></i> {{ __('ui.customer.serving_title') }}
            </div>
            @foreach($activeOrder->items as $item)
            <div class="cart-item-row flex items-center justify-between py-2 border-b border-dashed border-gray-200 bg-white" id="old-item-row-{{ $item->id }}">
              <div class="text-[14px] font-bold text-gray-800 w-[40%] leading-tight pr-2">{{ $item->product_name }}</div>
              <div class="flex items-center gap-1.5 w-[35%] justify-center">
                  <div class="flex items-center bg-blue-50/50 border border-blue-100 rounded text-gray-700">
                    <button onclick="updateOldQty({{ $item->id }}, -1)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100"><i data-feather="minus" class="w-3 h-3"></i></button>
                    <span class="w-6 text-center text-[13px] font-bold" id="old-qty-{{ $item->id }}">{{ $item->qty }}</span>
                    <button onclick="updateOldQty({{ $item->id }}, 1)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100"><i data-feather="plus" class="w-3 h-3"></i></button>
                  </div>
                  <button onclick="deleteOldItem({{ $item->id }})" class="w-7 h-7 flex items-center justify-center text-red-400 bg-red-50 rounded hover:bg-red-100 transition">
                    <i data-feather="trash-2" class="w-3.5 h-3.5"></i>
                  </button>
              </div>
              <div class="text-[14px] font-bold text-gray-800 w-[25%] text-right" id="old-sub-{{ $item->id }}" data-value="{{ $item->subtotal }}">{{ number_format($item->subtotal, 0, ',', '.') }}</div>
              <div class="hidden" id="old-price-{{ $item->id }}" data-value="{{ $item->price }}"></div>
            </div>
            @endforeach
            <div class="px-2 py-1.5 bg-gray-50 border-y border-gray-100 flex items-center gap-2 text-[11px] font-bold text-gray-500 uppercase mt-2 sticky top-0 z-10">
                <i data-feather="plus-circle" class="w-3.5 h-3.5"></i> {{ __('ui.customer.new_selection') }}
            </div>
            @endif
        </div>
        
        <div id="cart-new-list" class="flex-1 flex flex-col min-h-[100px]">
            <div class="flex flex-col items-center justify-center p-6 text-gray-400 space-y-2 @if($activeOrder && $activeOrder->items->count() > 0) hidden @endif" id="cart-empty-msg">
              <i data-feather="inbox" class="w-8 h-8 opacity-30"></i>
              <div class="text-[12px]">{{ __('ui.customer.empty_cart') }}...</div>
            </div>
        </div>
      </div>
      
      <!-- Bottom fixed bar internally inside Cart section -->
      <div class="bg-white border-t border-gray-200 p-2.5 md:p-3 flex-none w-full">
        @if($tenant->business_type === 'retail')
        {{-- Removed inline inputs as they are now in a mandatory popup --}}
        @endif

        @if($tenant->business_type === 'cafe')
        <div class="mb-2 hidden md:block">
          @if($table)
            <div class="w-full border border-gray-200 rounded px-2.5 py-2 text-[13px] font-bold text-gray-700 bg-gray-50 flex justify-between items-center">
                <span class="flex items-center gap-2"><i data-feather="map-pin" class="w-3.5 h-3.5 text-gray-400"></i> {{ $table->name }}</span>
                <input type="hidden" id="selected-table" value="{{ $table->id }}">
                <a href="{{ route('customer.tables') }}" class="text-[11px] font-semibold text-kv-blue hover:underline">{{ __('ui.customer.change_table') }}</a>
            </div>
          @else
            <select id="selected-table" class="w-full border border-gray-200 rounded px-2 py-2 text-[13px] outline-none focus:border-kv-blue bg-white shadow-sm">
              <option value="">{{ __('ui.customer.choose_table_placeholder') }}</option>
              @foreach($rooms as $room)
                <optgroup label="{{ $room->name }}">
                  @foreach($room->tables as $tableItem)
                    <option value="{{ $tableItem->id }}">{{ $tableItem->name }} @if($tableItem->status !== 'available') ({{ $tableItem->status }}) @endif</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          @endif
        </div>
        @endif

        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2 w-[40%]">
              <span class="text-[14px] font-bold text-gray-500">{{ __('ui.customer.total') }}:</span>
              <span class="text-[17px] font-black text-gray-800 truncate" id="cart-total-txt">0</span>
          </div>
          
          <div class="flex gap-2 flex-1 justify-end">
            <button onclick="checkout()" id="btn-checkout" class="px-3 bg-white border border-green-500 text-green-600 hover:bg-green-50 py-2.5 rounded-md text-[13px] font-bold transition @if(!$activeOrder) hidden @endif">
              {{ __('ui.customer.checkout') }}
            </button>
            <button onclick="placeOrder()" id="btn-order" class="flex-1 max-w-[160px] bg-[#4caf50] hover:bg-[#3d8c40] text-white font-bold py-2.5 rounded-md text-[15px] transition disabled:opacity-50 shadow-sm flex items-center justify-center gap-1.5">
              {{ __('ui.customer.pay') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="confirm-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
  <div class="bg-white rounded-xl p-6 w-full max-w-sm text-center shadow-2xl">
    <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
      <i data-feather="help-circle" class="w-8 h-8"></i>
    </div>
    <h3 class="text-[18px] font-bold text-gray-800 mb-2" id="confirm-title">{{ __('ui.customer.confirm_delete_title') }}</h3>
    <p class="text-[14px] text-gray-500 mb-6" id="confirm-msg">{{ __('ui.customer.confirm_delete_item') }}</p>
    <div class="flex gap-3">
      <button id="confirm-cancel" class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition text-[14px] uppercase">{{ __('ui.customer.cancel') }}</button>
      <button id="confirm-ok" class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg transition text-[14px] uppercase">{{ __('ui.customer.ok') }}</button>
    </div>
  </div>
</div>

<style>
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .hide-spinners::-webkit-inner-spin-button, .hide-spinners::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
  .hide-spinners { -moz-appearance: textfield; }
</style>

<!-- Customer Info Modal -->
<div id="customer-modal" class="hidden fixed inset-0 z-[10000] bg-black bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-5 animate-in fade-in zoom-in duration-300">
        <h3 class="text-[16px] font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-feather="user" class="w-5 h-5 text-kv-blue"></i>
            {{ __('ui.customer.customer_info_title') }}
        </h3>
        <div class="space-y-4">
            <div>
                <label class="block text-[13px] font-medium text-gray-600 mb-1">{{ __('ui.customer.customer_name') }} <span class="text-red-500">*</span></label>
                <input type="text" id="modal-customer-name" value="{{ $activeOrder ? $activeOrder->customer_name : '' }}" placeholder="{{ __('ui.customer.customer_name') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-[14px] outline-none focus:border-kv-blue shadow-sm">
            </div>
            <div>
                <label class="block text-[13px] font-medium text-gray-600 mb-1">{{ __('ui.customer.customer_phone') }} <span class="text-red-500">*</span></label>
                <input type="tel" id="modal-customer-phone" value="{{ $activeOrder ? $activeOrder->customer_phone : '' }}" placeholder="{{ __('ui.customer.customer_phone') }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-[14px] outline-none focus:border-kv-blue shadow-sm">
            </div>
            <div class="flex gap-2 pt-2">
                <button onclick="closeCustomerModal()" class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-[14px] font-bold transition">
                    {{ __('ui.customer.cancel') }}
                </button>
                <button onclick="submitWithCustomerInfo()" class="flex-1 px-4 py-2 bg-kv-blue hover:bg-kv-blue_hover text-white rounded-md text-[14px] font-bold transition shadow-sm">
                    {{ __('ui.customer.pay') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showCustomerModal() {
    document.getElementById('customer-modal').classList.remove('hidden');
    document.getElementById('modal-customer-name').focus();
}

function closeCustomerModal() {
    document.getElementById('customer-modal').classList.add('hidden');
}

function submitWithCustomerInfo() {
    const name = document.getElementById('modal-customer-name').value.trim();
    const phone = document.getElementById('modal-customer-phone').value.trim();
    if (!name) { showToast("{{ __('ui.customer.please_enter_name') }}", 'error'); return; }
    if (!phone) { showToast("{{ __('ui.customer.please_enter_phone') }}", 'error'); return; }
    placeOrder(true);
}
</script>
<script>
let cart = {};

// --- IndexedDB Setup ---
const DB_NAME = 'POSOfflineDB';
const DB_VERSION = 1;
const STORE_NAME = 'pendingOrders';

function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);
    request.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
      }
    };
    request.onsuccess = (e) => resolve(e.target.result);
    request.onerror = (e) => reject(e.target.error);
  });
}

async function saveOfflineOrder(orderData) {
  const db = await openDB();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  const store = tx.objectStore(STORE_NAME);
  return new Promise((resolve) => {
    const req = store.add(orderData);
    req.onsuccess = () => {
      updateSyncBadge();
      resolve(true);
    };
  });
}

async function getPendingOrders() {
  const db = await openDB();
  const tx = db.transaction(STORE_NAME, 'readonly');
  const store = tx.objectStore(STORE_NAME);
  return new Promise((resolve) => {
    const req = store.getAll();
    req.onsuccess = () => resolve(req.result);
  });
}

async function deleteOfflineOrder(id) {
  const db = await openDB();
  const tx = db.transaction(STORE_NAME, 'readwrite');
  const store = tx.objectStore(STORE_NAME);
  return new Promise((resolve) => {
    const req = store.delete(id);
    req.onsuccess = () => {
      updateSyncBadge();
      resolve(true);
    };
  });
}

async function updateSyncBadge() {
  const pending = await getPendingOrders();
  const badge = document.getElementById('sync-badge');
  const countSpan = document.getElementById('sync-count');
  if (pending.length > 0) {
    badge.classList.remove('hidden');
    countSpan.textContent = pending.length;
  } else {
    badge.classList.add('hidden');
  }
}

let isSyncing = false;
async function syncOfflineOrders() {
  if (isSyncing || !navigator.onLine) return;
  const pending = await getPendingOrders();
  if (pending.length === 0) return;

  isSyncing = true;
  console.log(`Syncing ${pending.length} offline orders...`);
  
  // Parallel sync for speed
  const syncPromises = pending.map(async (order) => {
    try {
      const res = await fetch('/api/create_order', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          items: order.items,
          note: order.note || '',
          table_id: order.tableId,
          customer_name: order.customerName || '',
          customer_phone: order.customerPhone || ''
        })
      });
      const data = await res.json();
      if (data.ok) {
        await deleteOfflineOrder(order.id);
        return true;
      }
    } catch (e) {
      console.error('Failed to sync order', order.id, e);
    }
    return false;
  });

  const results = await Promise.all(syncPromises);
  const successCount = results.filter(r => r).length;
  
  isSyncing = false;
  updateSyncBadge();
  if (successCount > 0) {
      showToast("{{ __('ui.customer.update_success') }} (Đã đồng bộ " + successCount + " đơn hàng)", 'success');
  }
}

window.addEventListener('online', syncOfflineOrders);
// Heartbeat sync every 10 seconds to ensure data flows quickly
setInterval(syncOfflineOrders, 10000); 

// --- POS Logic ---

function filterCat(catId, btn) {
  document.querySelectorAll('.cat-tab').forEach(el => {
    el.classList.remove('border-kv-blue', 'text-kv-blue', 'bg-blue-50', 'border');
    el.classList.add('border-transparent', 'text-gray-500');
  });
  btn.classList.add('border-kv-blue', 'text-kv-blue', 'bg-blue-50', 'border');
  btn.classList.remove('border-transparent', 'text-gray-500');
  
  document.querySelectorAll('.prod-item').forEach(el => {
    if (catId === 'all' || el.dataset.cat == catId) el.style.display = 'flex';
    else el.style.display = 'none';
  });
  document.getElementById('pos-search').value = '';
}

document.getElementById('pos-search').addEventListener('input', function(e) {
  const v = e.target.value.toLowerCase().trim();
  document.querySelectorAll('.prod-item').forEach(el => {
    if (el.dataset.name.includes(v)) el.style.display = 'flex';
    else el.style.display = 'none';
  });
});

function addToCart(id, name, price) {
  id = String(id);
  if (!cart[id]) cart[id] = { name, price, qty: 0 };
  cart[id].qty++;
  renderCart();
  if(window.innerWidth < 768) {
      document.getElementById('cart-section').scrollIntoView({ behavior: 'smooth', block: 'end' });
  }
}

function setQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) delete cart[id];
  renderCart();
}

function askConfirm(msg, title = '{{ __('ui.customer.confirm_delete_title') }}') {
  return new Promise((resolve) => {
    const modal = document.getElementById('confirm-modal');
    document.getElementById('confirm-msg').textContent = msg;
    document.getElementById('confirm-title').textContent = title;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    feather.replace();
    
    const cleanup = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.getElementById('confirm-ok').onclick = null;
      document.getElementById('confirm-cancel').onclick = null;
    };

    document.getElementById('confirm-ok').onclick = () => { cleanup(); resolve(true); };
    document.getElementById('confirm-cancel').onclick = () => { cleanup(); resolve(false); };
  });
}

async function clearCart() {
  if(Object.keys(cart).length === 0) return;
  if(!await askConfirm('{{ __('ui.customer.confirm_clear_cart') }}')) return;
  cart = {};
  renderCart();
}

async function removeFromCart(id) {
  if (await askConfirm('{{ __('ui.customer.confirm_delete_selected') }}')) {
    delete cart[id];
    renderCart();
  }
}

function renderCart() {
  const list = document.getElementById('cart-new-list');
  const txtTotal = document.getElementById('cart-total-txt');
  const emptyMsg = document.getElementById('cart-empty-msg');
  
  const keys = Object.keys(cart);
  
  if (!keys.length) {
    if (emptyMsg) emptyMsg.classList.remove('hidden');
    list.innerHTML = ''; 
    txtTotal.textContent = fmtMoney(oldTotalJS);
    // Don't disable order button entirely if offline, we want to allow offline saving
    // document.getElementById('btn-order').disabled = true;
    return;
  }

  if (emptyMsg) emptyMsg.classList.add('hidden');
  document.getElementById('btn-order').disabled = false;
  let total = 0;
  let html = '';
  keys.forEach((id) => {
    const it = cart[id];
    const sub = it.price * it.qty;
    total += sub;
    html += `
      <div class="cart-item-row flex items-center justify-between py-2 border-b border-dashed border-gray-200 bg-white">
        <div class="text-[14px] font-bold text-gray-800 w-[40%] leading-tight pr-2">${it.name}</div>
        <div class="flex items-center gap-1.5 w-[35%] justify-center">
            <div class="flex items-center bg-blue-50/50 border border-blue-100 rounded text-gray-700">
               <button onclick="setQty('${id}', -1)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 transition"><i data-feather="minus" class="w-3 h-3"></i></button>
               <span class="w-6 text-center text-[13px] font-bold">${it.qty}</span>
               <button onclick="setQty('${id}', 1)" class="w-7 h-7 flex items-center justify-center hover:bg-gray-100 transition"><i data-feather="plus" class="w-3 h-3"></i></button>
            </div>
            <button onclick="removeFromCart('${id}')" class="w-7 h-7 flex items-center justify-center text-red-400 bg-red-50 rounded hover:bg-red-100 transition">
              <i data-feather="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        </div>
        <div class="text-[14px] font-bold text-gray-800 w-[25%] text-right">${fmtMoney(sub)}</div>
      </div>
    `;
  });
  list.innerHTML = html;
  feather.replace();
  txtTotal.textContent = fmtMoney(total + oldTotalJS);
}

async function placeOrder(isConfirmed = false) {
  if (!Object.keys(cart).length) return;

  @if($tenant->business_type === 'retail')
  if (!isConfirmed) {
      document.getElementById('customer-modal').classList.remove('hidden');
      return;
  }
  @endif

  const btn = document.getElementById('btn-order');
  const originalHtml = btn.innerHTML;
  
  const items = Object.entries(cart).map(([id, v]) => ({ id: parseInt(id), qty: v.qty }));
  const tableSelect = document.getElementById('selected-table');
  const tableId = tableSelect ? tableSelect.value : null;

  if (tableSelect && !tableId) {
    showToast("{{ __('ui.customer.select_table_error') }}", 'error');
    return;
  }
  
  const mName = document.getElementById('modal-customer-name');
  const mPhone = document.getElementById('modal-customer-phone');
  const customerName = mName ? mName.value : '';
  const customerPhone = mPhone ? mPhone.value : '';

  // -- OFFLINE HANDLING --
  if (!navigator.onLine) {
    await saveOfflineOrder({ items, tableId, note: '', customerName, customerPhone, timestamp: Date.now() });
    cart = {};
    renderCart();
    if (mName) mName.value = '';
    if (mPhone) mPhone.value = '';
    closeCustomerModal();
    showToast("Đã lưu đơn hàng ngoại tuyến. Hệ thống sẽ tự đồng bộ khi có mạng.", 'success');
    return;
  }

  btn.disabled = true; 
  btn.innerHTML = '<i data-feather="loader" class="w-5 h-5 animate-spin"></i>' + "{{ __('ui.customer.processing_ellipsis') }}"; 
  feather.replace();
  
  try {
    const res = await fetch('/api/create_order', {
      method: 'POST', 
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ 
        items, 
        note: '', 
        table_id: tableId,
        customer_name: customerName,
        customer_phone: customerPhone
      })
    });
    const data = await res.json();
    if (data.ok) {
      cart = {};
      renderCart();
      if (mName) mName.value = '';
      if (mPhone) mPhone.value = '';
      closeCustomerModal();
      showToast("{{ __('ui.customer.order_sent_to_counter') }} - " + data.order_code, 'success');
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    } else {
      showToast(data.msg || "{{ __('ui.customer.ordering_error') }}", 'error');
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    }
  } catch(e) {
    // Fallback save offline if fetch fails even if navigator says online
    await saveOfflineOrder({ items, tableId, note: '', customerName, customerPhone, timestamp: Date.now() });
    cart = {};
    renderCart();
    showToast("Gặp lỗi mạng. Đơn hàng đã được lưu ngoại tuyến để chờ đồng bộ.", 'warning');
    btn.innerHTML = originalHtml;
    btn.disabled = false;
  }
}

async function checkout() {
  const tableSelect = document.getElementById('selected-table');
  const tableId = tableSelect ? tableSelect.value : null;
  if (!tableId || !navigator.onLine) return; // Checkout usually needs online to free table
  if (!await askConfirm("{{ __('ui.customer.confirm_checkout_msg') }}", "{{ __('ui.customer.checkout') }}")) return;

  const btn = document.getElementById('btn-checkout');
  btn.disabled = true;
  
  try {
    const res = await fetch('/api/checkout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ table_id: tableId })
    });
    const data = await res.json();
    if (data.ok) {
        showToast('{{ __('ui.customer.checkout_success') }}', 'success');
        setTimeout(() => location.href = '{{ route('customer.tables') }}', 1000);
    } else {
        showToast(data.msg, 'error');
        btn.disabled = false;
    }
  } catch(e) {
    showToast('{{ __('ui.customer.connection_error') }}: ' + e.message, 'error');
    btn.disabled = false;
  }
}

async function deleteOldItem(itemId) {
  if (!await askConfirm('{{ __('ui.customer.confirm_delete_item') }}', '{{ __('ui.customer.confirm_delete_title') }}')) return;
  
  const row = document.getElementById(`old-item-row-${itemId}`);
  const sub = parseInt(document.getElementById(`old-sub-${itemId}`).dataset.value || 0);
  
  if (row) row.remove();
  oldTotalJS -= sub;
  renderCart();

  try {
    const res = await fetch('/api/delete_order_item', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ item_id: itemId })
    });
    const data = await res.json();
    if (data.ok) {
        showToast("{{ __('ui.customer.item_deleted') }}", 'success');
    } else {
        showToast(data.msg, 'error');
        setTimeout(() => location.reload(), 2000);
    }
  } catch(e) {
    showToast('Lỗi kết nối: ' + e.message, 'error');
    setTimeout(() => location.reload(), 2000);
  }
}

let oldTotalJS = {{ $activeOrder ? $activeOrder->total : 0 }};

async function updateOldQty(itemId, change) {
  const qtyEl = document.getElementById(`old-qty-${itemId}`);
  const subEl = document.getElementById(`old-sub-${itemId}`);
  const price = parseInt(document.getElementById(`old-price-${itemId}`).dataset.value);
  
  let currentQty = parseInt(qtyEl.textContent);
  let newQty = currentQty + change;
  
  if (newQty < 1) {
    if(await askConfirm("{{ __('ui.customer.confirm_delete_from_table') }}", "{{ __('ui.customer.confirm_delete_title') }}")) await deleteOldItem(itemId);
    return;
  }
  
  const diff = change * price;
  oldTotalJS += diff;
  qtyEl.textContent = newQty;
  subEl.textContent = fmtMoney(newQty * price);
  renderCart(); 
  
  try {
    const res = await fetch('/api/update_order_item_qty', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify({ item_id: itemId, change: change })
    });
    const data = await res.json();
    if (!data.ok) {
        showToast(data.msg, 'error');
        setTimeout(() => location.reload(), 2000);
    }
  } catch(e) {
    showToast('Lỗi kết nối: ' + e.message, 'error');
    setTimeout(() => location.reload(), 2000);
  }
}

// Initialize
renderCart();
updateSyncBadge();
setTimeout(syncOfflineOrders, 2000); // Initial sync check
</script>
@endpush
