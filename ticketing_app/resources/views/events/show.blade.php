<x-layouts.app>
  <section class="max-w-7xl mx-auto py-12 px-6">
    <nav class="mb-6">
      <div class="breadcrumbs">
        <ul>
          <li><a href="{{ route('home') }}" class="link link-neutral">Beranda</a></li>
          <li><a href="#" class="link link-neutral">Event</a></li>
          <li>{{ $event->judul }}</li>
        </ul>
      </div>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left / Main area -->
      <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
          <figure>
            <img src="{{ $event->gambar
      ? asset('storage/' . $event->gambar)
      : 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp'
  }}" alt="{{ $event->judul }}" class="w-full h-96 object-cover" />
          </figure>
          <div class="card-body">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h1 class="text-3xl font-extrabold">{{ $event->judul }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                  {{ \Carbon\Carbon::parse($event->tanggal_waktu)->locale('id')->translatedFormat('d F Y, H:i') }} • 📍
                  {{ $event->lokasi }}
                </p>

                <div class="mt-3 flex gap-2 items-center">
                  <span class="badge badge-primary">{{ $event->kategori?->nama ?? 'Tanpa Kategori' }}</span>
                  <span class="badge">{{ $event->user?->name ?? 'Penyelenggara' }}</span>
                </div>
              </div>

            </div>

            <p class="mt-4 text-gray-700 leading-relaxed">{{ $event->deskripsi }}</p>

            <div class="divider"></div>

            <h3 class="text-xl font-bold">Pilih Tiket</h3>

            <div class="mt-4 space-y-4">
              @forelse($event->tikets as $tiket)
              <div class="card card-side shadow-sm p-4 items-center">
                <div class="flex-1">
                  <h4 class="font-bold">{{ $tiket->tipe }}</h4>
                  <p class="text-sm text-gray-500">Stok: <span id="stock-{{ $tiket->id }}">{{ $tiket->stok }}</span></p>
                  <p class="text-sm mt-2">{{ $tiket->keterangan ?? '' }}</p>
                </div>

                <div class="w-44 text-right">
                  <div class="text-lg font-bold">
                    {{ $tiket->harga ? 'Rp ' . number_format($tiket->harga, 0, ',', '.') : 'Gratis' }}
                  </div>

                  <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline" data-action="dec" data-id="{{ $tiket->id }}"
                      aria-label="Kurangi satu">−</button>
                    <input id="qty-{{ $tiket->id }}" type="number" min="0" max="{{ $tiket->stok }}" value="0"
                      class="input input-bordered w-16 text-center" data-id="{{ $tiket->id }}" />
                    <button type="button" class="btn btn-sm btn-outline" data-action="inc" data-id="{{ $tiket->id }}"
                      aria-label="Tambah satu">+</button>
                  </div>

                  <div class="text-sm text-gray-500 mt-2">Subtotal: <span id="subtotal-{{ $tiket->id }}">Rp 0</span>
                  </div>
                </div>
              </div>
              @empty
              <div class="alert alert-info">Tiket belum tersedia untuk acara ini.</div>
              @endforelse
            </div>

          </div>
        </div>
      </div>

      <!-- Right / Summary -->
      <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-4 bg-base-100 shadow">
          <h4 class="font-bold text-lg">Ringkasan Pembelian</h4>

          <div class="mt-4">
            <div class="flex justify-between text-sm text-gray-500"><span>Item</span><span id="summaryItems">0</span>
            </div>
            <div class="flex justify-between text-xl font-bold mt-1"><span>Total</span><span id="summaryTotal">Rp
                0</span></div>
          </div>

          <div class="divider"></div>

          <div id="selectedList" class="space-y-2 text-sm text-gray-700">
            <p class="text-gray-500">Belum ada tiket dipilih</p>
          </div>

          <div class="divider"></div>

          @if($event->paymentTypes->isNotEmpty())
          <div class="form-control w-full mb-4">
              <label class="label">
                  <span class="label-text font-bold">Metode Pembayaran</span>
              </label>
              <select id="paymentTypeSelect" class="select select-bordered w-full" required>
                  <option disabled selected value="">Pilih Metode Pembayaran</option>
                  @foreach($event->paymentTypes as $payment)
                      <option value="{{ $payment->id }}">{{ $payment->name }}</option>
                  @endforeach
              </select>
          </div>
          @else
              <p class="text-sm text-warning mb-4">Tidak ada metode pembayaran tersedia.</p>
          @endif

          @auth
            <button id="checkoutButton" class="btn btn-primary !bg-blue-900 text-white btn-block mt-2" onclick="openCheckout()" disabled>Checkout</button>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block mt-6 text-white">Login untuk Checkout</a>
          @endauth

        </div>
      </aside>
    </div>

    <!-- Checkout Modal -->
    <dialog id="checkout_modal" class="modal">
      <form method="dialog" class="modal-box">
        <h3 class="font-bold text-lg">Konfirmasi Pembelian</h3>
        <div class="mt-4 space-y-2 text-sm">
          <div id="modalItems">
            <p class="text-gray-500">Belum ada item.</p>
          </div>

          <div class="divider"></div>
          
          <div class="flex justify-between items-center mb-2">
            <span class="font-bold">Metode Pembayaran</span>
            <span id="modalPaymentType">-</span>
          </div>

          <div class="flex justify-between items-center">
            <span class="font-bold">Total</span>
            <span class="font-bold text-lg" id="modalTotal">Rp 0</span>
          </div>
        </div>

        <div class="modal-action">
          <button class="btn">Tutup</button>
          <button type="button" class="btn btn-primary px-4 !bg-blue-900 text-white" id="confirmCheckout">Konfirmasi</button>
        </div>
      </form>
    </dialog>
        </div>

        <div class="modal-action">
          <button class="btn">Tutup</button>
          <button type="button" class="btn btn-primary px-4 !bg-blue-900 text-white" id="confirmCheckout">Konfirmasi</button>
        </div>
      </form>
    </dialog>

  </section>

  @php
    $ticketsData = $event->tikets
      ->keyBy('id')
      ->map(function ($t) {
        return [
          'id' => $t->id,
          'price' => $t->harga ?? 0,
          'stock' => $t->stok,
          'tipe' => $t->tipe,
        ];
      })
      ->toArray();
  @endphp

  <script type="application/json" id="ticketsData">{!! json_encode($ticketsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
  <script type="application/json" id="checkoutConfig">{!! json_encode([
    'eventId' => $event->id,
    'ordersStoreUrl' => route('orders.store'),
    'ordersIndexUrl' => route('orders.index'),
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

  <script>
    (function () {
      // Helper to format Indonesian currency
      const formatRupiah = (value) => {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
      }

      const ticketsDataEl = document.getElementById('ticketsData');
      const tickets = ticketsDataEl ? JSON.parse(ticketsDataEl.textContent || '{}') : {};
      const checkoutConfigEl = document.getElementById('checkoutConfig');
      const checkoutConfig = checkoutConfigEl ? JSON.parse(checkoutConfigEl.textContent || '{}') : {};

    const summaryItemsEl = document.getElementById('summaryItems');
    const summaryTotalEl = document.getElementById('summaryTotal');
    const selectedListEl = document.getElementById('selectedList');
    const checkoutButton = document.getElementById('checkoutButton');

    function updateSummary() {
      let totalQty = 0;
      let totalPrice = 0;
      let selectedHtml = '';

      Object.values(tickets).forEach(t => {
        const qtyInput = document.getElementById('qty-' + t.id);
        if (!qtyInput) return;
        const qty = Number(qtyInput.value || 0);
        if (qty > 0) {
          totalQty += qty;
          totalPrice += qty * t.price;
          selectedHtml += `<div class="flex justify-between"><span>${t.tipe} x ${qty}</span><span>${formatRupiah(qty * t.price)}</span></div>`;
        }
      });

      summaryItemsEl.textContent = totalQty;
      summaryTotalEl.textContent = formatRupiah(totalPrice);
      selectedListEl.innerHTML = selectedHtml || '<p class="text-gray-500">Belum ada tiket dipilih</p>';
      checkoutButton.disabled = totalQty === 0;
    }

    // Wire up plus/minus buttons and manual input
    document.querySelectorAll('[data-action="inc"]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        const input = document.getElementById('qty-' + id)
        const info = tickets[id];
        if (!input || !info) return;
        let val = Number(input.value || 0);
        if (val < info.stock) val++;
        input.value = val;
        updateTicketSubtotal(id);
        updateSummary();
      });
    });

    document.querySelectorAll('[data-action="dec"]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        const input = document.getElementById('qty-' + id);
        if (!input) return;
        let val = Number(input.value || 0);
        if (val > 0) val--;
        input.value = val;
        updateTicketSubtotal(id);
        updateSummary();
      });
    });

    document.querySelectorAll('input[id^="qty-"]').forEach(input => {
      input.addEventListener('change', (e) => {
        const el = e.currentTarget;
        const id = el.dataset.id;
        const info = tickets[id];
        let val = Number(el.value || 0);
        if (val < 0) val = 0;
        if (val > info.stock) val = info.stock;
        el.value = val;
        updateTicketSubtotal(id);
        updateSummary();
      });
    });

    function updateTicketSubtotal(id) {
      const t = tickets[id];
      const qty = Number(document.getElementById('qty-' + id).value || 0);
      const subtotalEl = document.getElementById('subtotal-' + id);
      if (subtotalEl) subtotalEl.textContent = formatRupiah(qty * t.price);
    }

    // Checkout modal
    window.openCheckout = function () {
      const paymentSelect = document.getElementById('paymentTypeSelect');
      
      // Validate payment type first
      if (paymentSelect && !paymentSelect.value) {
          alert('Silakan pilih metode pembayaran terlebih dahulu');
          paymentSelect.focus();
          return;
      }

      const modal = document.getElementById('checkout_modal');
      // populate modal items
      const modalItems = document.getElementById('modalItems');
      const modalTotal = document.getElementById('modalTotal');
      const modalPaymentType = document.getElementById('modalPaymentType');

      let itemsHtml = '';
      let total = 0;
      Object.values(tickets).forEach(t => {
        const qty = Number(document.getElementById('qty-' + t.id).value || 0);
        if (qty > 0) {
          itemsHtml += `<div class="flex justify-between"><span>${t.tipe} x ${qty}</span><span>${formatRupiah(qty * t.price)}</span></div>`;
          total += qty * t.price;
        }
      });

      modalItems.innerHTML = itemsHtml || '<p class="text-gray-500">Belum ada item.</p>';
      modalTotal.textContent = formatRupiah(total);
      
      // Update payment type display in modal
      if (paymentSelect && modalPaymentType) {
          const selectedText = paymentSelect.options[paymentSelect.selectedIndex].text;
          modalPaymentType.textContent = selectedText;
      }

      const btn = document.getElementById('confirmCheckout');
      btn.removeAttribute('disabled');
      btn.textContent = 'Konfirmasi';

      if (typeof modal.showModal === 'function') {
        modal.showModal();
      } else {
        // fallback for older browsers
        modal.classList.add('modal-open');
      }
    }

    document.getElementById('confirmCheckout').addEventListener('click', async () => {
      const btn = document.getElementById('confirmCheckout');
      const paymentSelect = document.getElementById('paymentTypeSelect');
      
      btn.setAttribute('disabled', 'disabled');
      btn.textContent = 'Memproses...';

      // gather items
      const items = [];
      Object.values(tickets).forEach(t => {
        const qty = Number(document.getElementById('qty-' + t.id).value || 0);
        if (qty > 0) items.push({ tiket_id: t.id, jumlah: qty });
      });

      if (items.length === 0) {
        alert('Tidak ada tiket dipilih');
        btn.removeAttribute('disabled');
        btn.textContent = 'Konfirmasi';
        return;
      }

      try {
        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.getAttribute('content') : '';

        const headers = {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        };
        if (csrfToken) {
          headers['X-CSRF-TOKEN'] = csrfToken;
        }

        const body = { 
            event_id: checkoutConfig.eventId, 
            items,
            payment_type_id: paymentSelect ? paymentSelect.value : null
        };

        const res = await fetch(checkoutConfig.ordersStoreUrl, {
          method: 'POST',
          headers,
          body: JSON.stringify(body)
        });

        if (!res.ok) {
          const text = await res.text();
          throw new Error(text || 'Gagal membuat pesanan');
        }

        const data = await res.json();
        // redirect to orders list
        window.location.href = data.redirect || checkoutConfig.ordersIndexUrl;
      } catch (err) {
        console.log(err);
        alert('Terjadi kesalahan saat memproses pesanan: ' + err.message);
        btn.removeAttribute('disabled');
        btn.textContent = 'Konfirmasi';
      }
    });

    // init
    updateSummary();
    }) ();
  </script>
</x-layouts.app>