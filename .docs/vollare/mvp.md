# MVP POS System — Volare Caffe

> Versi 2.0 — Phase 2: Payment Gateway + Receipt Printing
> Last updated: 2026-04-19

---

## 🎯 Goal

Membangun sistem POS lengkap yang siap digunakan secara nyata:
- Pembayaran digital (QRIS, GoPay, OVO, Dana) via Midtrans
- Cetak struk otomatis via thermal printer (Bluetooth / Network)
- Manajemen shift kasir (opening & closing)
- Retur transaksi sederhana
- Kategori produk & diskon per transaksi
- Tetap multi-tenant & whitelabel-ready

---

## ✅ Phase 1 — Completed (MVP v1)

### Admin Web (Laravel Blade + DaisyUI)
- CRUD produk (nama, harga jual, HPP, gambar, deskripsi)
- List transaksi per owner (filter date)
- Dashboard owner: revenue, profit, transaksi, performa kasir
- Dashboard kasir: statistik pribadi (no profit, no other cashiers)
- Role-aware chart (Chart.js 7-hari terakhir)
- Monitoring pegawai: transaksi, revenue, avg/order per kasir
- Riwayat transaksi kasir (my-history)
- Multi-user: owner + kasir dengan role-based access
- Multi-tenant: GlobalScope `tenant_id` di semua model
- Polling dashboard 5 detik (visibilitychange optimized)
- Image upload via Cloudinary (fallback: local storage)

### System Core
- Laravel Sanctum (token API)
- EnsureTenantScope + EnsureRole middleware
- DaisyUI v5 + Tailwind CSS v4 (custom `volare` theme)
- jQuery CDN — no JS bundler

---

## 🚀 Phase 2 — Current Scope (MVP v2)

### 1. Payment Gateway — Midtrans

**Metode yang diimplementasi:**

| Metode | Tipe |
|---|---|
| Cash | Manual — catat di backend |
| QRIS | Midtrans Core API |
| GoPay | Midtrans Core API |
| OVO | Midtrans Core API |
| Dana | Midtrans Core API |

**Flow pembayaran digital:**
```
Kasir pilih metode
→ POST /api/payments (backend charge ke Midtrans Core API)
→ Backend return QR code / deeplink URL
→ Kasir tampilkan ke customer
→ Customer bayar
→ Midtrans POST /api/payments/webhook
→ Backend update order status = paid
→ Trigger cetak struk otomatis
```

**Implementasi:**
- `midtrans-php` SDK via Composer
- Simpan `midtrans_transaction_id` di tabel `payments`
- Webhook verify signature dengan `MIDTRANS_SERVER_KEY`
- Setiap tenant pakai shared Midtrans credential (Phase 2); per-tenant di Phase 4

---

### 2. Cash Payment

- Input nominal bayar → hitung kembalian otomatis
- Tidak melalui Midtrans — langsung catat di backend
- Kembalian = `cash_paid − net_amount`

---

### 3. Receipt Printing

**Jenis printer yang didukung:**

| Tipe | Koneksi | Protokol |
|---|---|---|
| Thermal 58mm | Bluetooth | ESC/POS |
| Thermal 80mm | USB / Network (LAN/WiFi) | ESC/POS |
| Network printer | TCP/IP port 9100 | ESC/POS |

**Konten struk:**
```
================================
       [TENANT NAMA / LOGO]
================================
Kasir   : Budi Santoso
Tanggal : 19 Apr 2026, 14:32
Tx #    : 00123
--------------------------------
Kopi Susu           x2   50.000
Croissant           x1   22.000
--------------------------------
Subtotal                 72.000
Diskon (10%)             -7.200
--------------------------------
TOTAL                    64.800
================================
Bayar   : GoPay
Status  : LUNAS
================================
  Terima kasih sudah berkunjung!
================================
```

**Implementasi:**
- Mobile (React Native): `react-native-thermal-printer` via Bluetooth
- Network printer: backend generate ESC/POS bytes → kirim ke IP:9100 via TCP
- Web fallback: render HTML struk → `window.print()` dengan `@media print`
- Laravel: `mike42/escpos-php` untuk server-side print trigger

---

### 4. Shift Management

**Opening Shift:**
- Kasir input saldo awal kas
- Sistem catat `shift_id`, `opened_at`, `opening_cash`, `user_id`

**Closing Shift:**
- Sistem tampilkan ringkasan: total tx, total per metode bayar
- Kasir input cash aktual di laci
- Sistem hitung selisih (expected vs actual)
- Owner bisa lihat rekap di web dashboard

---

### 5. Return / Refund

**Rules:**
- Retur hanya dalam 24 jam setelah transaksi (configurable)
- Partial retur (sebagian item) diizinkan
- Refund cash: catat manual
- Refund digital: trigger Midtrans Refund API

**Flow:**
```
Kasir buka riwayat → Pilih order → Klik "Retur"
→ Input alasan + item yang dikembalikan
→ Sistem buat Return Invoice (negatif amount)
→ Refund via metode bayar asal
→ Owner notified
```

---

### 6. Kategori Produk

- Grouping produk untuk navigasi POS
- CRUD kategori di admin web
- Filter produk per kategori di POS grid

---

### 7. Discount per Transaksi

- Input nominal atau persentase di checkout
- `net_amount = subtotal − discount`
- Diskon tercatat di tabel `orders`

---

## 🔌 API Endpoints

### Phase 1 (existing)
```
POST   /api/login
GET    /api/products?tenant=slug
POST   /api/orders
GET    /api/dashboard/summary
GET    /api/transactions
GET    /api/transactions/{id}
```

### Phase 2 (new)
```
POST   /api/payments                  — charge ke Midtrans
POST   /api/payments/webhook          — Midtrans webhook callback
GET    /api/payments/{orderId}/status — cek status pembayaran

GET    /api/shifts/current            — shift aktif kasir
POST   /api/shifts/open               — buka shift
POST   /api/shifts/close              — tutup shift
GET    /api/shifts                    — list shift (owner)

POST   /api/orders/{id}/return        — ajukan retur
GET    /api/returns                   — list retur (owner)

GET    /api/categories                — list kategori
POST   /api/categories                — create kategori (owner)
PUT    /api/categories/{id}           — update kategori (owner)
DELETE /api/categories/{id}           — delete kategori (owner)
```

---

## 🗄 Database Tables

### Phase 1 (existing)
```
tenants       — id, name, slug, logo_url, primary_color
users         — id, tenant_id, name, email, role, password
products      — id, tenant_id, name, description, price, cost, image_url, is_active
orders        — id, tenant_id, user_id, total_amount, total_cost, notes
order_items   — id, order_id, product_id, product_name, unit_price, unit_cost, quantity, subtotal
```

### Phase 2 (new)
```
categories    — id, tenant_id, name, sort_order
               (add: products.category_id FK)

payments      — id, order_id, method, amount, status,
                midtrans_transaction_id, midtrans_response, paid_at

shifts        — id, tenant_id, user_id, opened_at, closed_at,
                opening_cash, closing_cash, expected_cash, notes

returns       — id, tenant_id, order_id, user_id, reason,
                status (pending/approved/rejected), refund_amount,
                midtrans_refund_id, created_at

return_items  — id, return_id, order_item_id, quantity, amount
```

**Order table additions:**
```
orders: + discount_type (nominal/percent), discount_value,
          net_amount, payment_status (pending/paid/returned),
          shift_id
```

---

## 🧠 Business Logic

```
Subtotal     = sum(unit_price × qty)
Net Amount   = Subtotal − Discount
Total Cost   = sum(unit_cost × qty)
Gross Profit = Net Amount − Total Cost

Payment      = sum(amount per payment method) = Net Amount
Change       = Cash Paid − Net Amount (cash only)

Shift Cash Expected = Opening Cash + Total Cash Payments − Total Cash Refunds
Cash Difference     = Expected − Actual Count
```

---

## 🎨 UI & Branding (Volare Theme)

### Core Colors (oklch)
- Primary: `oklch(84% 0.09 75)` → #edcc94
- Background: `oklch(100% 0 0)` → #ffffff
- Text: `oklch(10% 0 0)` → #010101

### Extended
- Primary Light: #f5e3bd
- Primary Dark: #c9a96e
- Surface: #f5f5f5
- Border: oklch(91% 0 0)
- Text Secondary: #6b7280

### Component Rules
- `.btn { border-width: 1px }` — DaisyUI v5 override
- `.input/.textarea/.select { border: 1px solid oklch(91% 0 0) }` — DaisyUI v5 input fix
- Pagination: DaisyUI `join` + `btn` — no dark: Tailwind classes
- Rounded corner: 12–16px (rounded-2xl)
- Max page width: `max-w-6xl mx-auto`

---

## ⚡ Performance Targets

| Operasi | Target |
|---|---|
| Cart interaction | < 100ms (local state) |
| Checkout API | < 2 detik |
| Payment QR generation | < 3 detik |
| Dashboard update | ≤ 5 detik (polling) |
| Receipt print (Bluetooth) | < 5 detik |
| Webhook processing | < 1 detik |

---

## 🔐 Security

- Laravel Sanctum — token API
- `tenant_id` GlobalScope — isolasi data antar tenant
- `EnsureRole` middleware — owner/cashier separation
- Midtrans webhook: signature hash verification + IP whitelist
- Tidak simpan nomor kartu — hanya `midtrans_transaction_id`

---

## 📅 Timeline Phase 2 (14 Hari)

**Week 1 — Backend & Payment:**
- Day 1–2: Database migrations (payments, shifts, returns, categories) + models
- Day 3–4: Midtrans integration (charge + webhook + status check)
- Day 5: Cash payment + kembalian otomatis
- Day 6: Shift management (open/close API + web UI)
- Day 7: Return/refund flow + Midtrans refund API

**Week 2 — Frontend & Polish:**
- Day 8–9: React Native payment flow (method selection + QR display)
- Day 10: Receipt printing (Bluetooth mobile + network backend)
- Day 11: Kategori produk (admin CRUD + POS filter)
- Day 12: Discount per transaksi (POS UI + backend logic)
- Day 13: Testing end-to-end (payment → webhook → print)
- Day 14: Bug fix & demo preparation

---

## ❌ Out of Scope (Phase 2)

- Inventory / stok tracking
- Barcode scanner
- Customer loyalty / poin
- Multi outlet (satu tenant, banyak cabang)
- Advanced analytics
- WebSocket realtime (masih polling)
- Per-tenant Midtrans credential (Phase 4)
- Self-service onboarding / subscription billing

---

## ✅ Success Criteria

- Kasir dapat transaksi + bayar + cetak struk ≤ 30 detik
- QRIS / GoPay terkonfirmasi otomatis via webhook tanpa input manual
- Owner mendapatkan rekap shift expected vs actual cash
- Struk tercetak akurat dan rapi setiap transaksi
- Retur dapat diproses dalam < 5 klik
- System stabil untuk 10+ kasir bersamaan per tenant
