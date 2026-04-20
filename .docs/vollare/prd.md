# Product Requirements Document (PRD)
## Smart POS System — Volare Caffe

> Versi 2.0 — Updated dengan fitur Payment Gateway, Receipt Printing, dan inspirasi ERPNext POS
> Last updated: 2026-04-19

---

## 🧠 Problem Statement

1. POS existing terlalu kompleks dan membingungkan
2. Harga POS terlalu mahal untuk bisnis kecil
3. Owner sulit memonitor performa karyawan
4. Tidak ada visibilitas profit (HPP)
5. Tidak mendukung pembayaran digital (QRIS, e-wallet) yang kini dominan di Indonesia
6. Tidak ada integrasi cetak struk otomatis — kasir harus tulis manual atau skip

---

## 🎯 Product Vision

Membangun POS system yang:
- Simple untuk kasir, powerful untuk owner
- Terjangkau untuk UMKM (≤ Rp150k/bulan)
- Mendukung semua metode pembayaran modern Indonesia
- Cetak struk otomatis via thermal printer
- Bisa digunakan oleh banyak brand (whitelabel / SaaS)

---

## 👤 Target Users

### Owner
- Mengelola produk, harga, kategori
- Melihat laporan penjualan, profit, HPP
- Monitoring performa kasir per shift
- Kelola metode pembayaran aktif
- Rekonsiliasi kas harian (opening/closing shift)

### Kasir
- Input order dengan cepat (≤ 3 klik checkout)
- Pilih metode pembayaran (cash, QRIS, e-wallet, transfer)
- Cetak atau kirim struk ke customer
- Proses retur/refund sederhana
- Lihat riwayat transaksi shift sendiri

### Customer (opsional, fase 2)
- Terima struk digital via WhatsApp / email
- Akumulasi poin loyalty

---

## 🧩 Core Features

### 1. POS (Kasir — Mobile App)

| Fitur | Deskripsi |
|---|---|
| Product grid | Image + nama + harga, searchable |
| Cart management | Add, update qty, remove item |
| Discount per transaksi | Nominal atau persentase |
| Multi-payment per order | Cash + QRIS split, dll |
| QRIS & e-wallet | Midtrans: GoPay, OVO, Dana, ShopeePay, QRIS |
| Cetak struk | Thermal printer 58/80mm via Bluetooth/USB/Network |
| Struk digital | Tampil di layar, bisa screenshot |
| Retur sederhana | Batalkan/refund order yang sudah dibayar |
| Barcode scan | Cari produk via kamera / scanner USB |
| Shift start/end | Kasir input saldo awal, sistem rekap penutupan |

### 2. Admin Web (Owner)

| Fitur | Deskripsi |
|---|---|
| Product CRUD | Nama, harga jual, HPP, gambar, kategori, status aktif |
| Kategori produk | Grouping produk untuk navigasi POS |
| Payment method config | Aktif/nonaktif metode pembayaran per tenant |
| Laporan transaksi | Filter date, kasir, metode bayar |
| Laporan profit | Revenue − HPP, per hari/bulan |
| Monitoring kasir | Transaksi, revenue, avg per order per kasir |
| Shift report | Rekap opening/closing kas per shift |
| Retur management | Lihat & approve retur yang diajukan kasir |
| Dashboard realtime | Stats update ≤ 5 detik |

### 3. System Core

| Fitur | Deskripsi |
|---|---|
| Multi-user | Owner, kasir — role-based access |
| Multi-tenant | Isolasi data per tenant_id |
| Whitelabel | Branding (logo, warna primary) per tenant |
| Near realtime | Polling 5 detik untuk dashboard |
| Audit trail | Setiap transaksi + payment tercatat dengan user & timestamp |

---

## 💳 Payment Gateway — Midtrans

### Provider: Midtrans (Snap + Core API)

**Metode pembayaran yang didukung:**

| Kategori | Metode |
|---|---|
| QR | QRIS (semua e-wallet kompatibel) |
| E-wallet | GoPay, OVO, Dana, ShopeePay, LinkAja |
| Bank Transfer | BCA, BNI, BRI, Mandiri Virtual Account |
| Kartu | Visa, Mastercard (opsional) |
| Cash | Tidak melalui Midtrans — dicatat manual |

**Flow pembayaran digital:**
```
Kasir pilih metode → Backend POST /v2/charge (Midtrans Core API)
→ Midtrans return QR code / payment URL
→ Kasir tampilkan ke customer
→ Customer bayar
→ Midtrans webhook → Backend update order status = paid
→ POS terima konfirmasi → Cetak struk otomatis
```

**Catatan implementasi:**
- Gunakan **Midtrans Core API** (bukan Snap) untuk kontrol penuh di mobile app
- Simpan `midtrans_transaction_id` di tabel `payments`
- Webhook endpoint harus diverifikasi dengan `MIDTRANS_SERVER_KEY` signature
- Setiap tenant punya Midtrans credential sendiri (whitelabel)

---

## 🖨 Receipt Printing

### Jenis Printer yang Didukung

| Tipe | Koneksi | Protokol |
|---|---|---|
| Thermal 58mm | Bluetooth | ESC/POS |
| Thermal 80mm | USB / Network (LAN/WiFi) | ESC/POS |
| Network printer | TCP/IP port 9100 | ESC/POS |

### Konten Struk

```
================================
       [TENANT LOGO / NAMA]
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

### Implementasi

- **Mobile (React Native):** `@tillpos/react-native-thermal-printer` atau `react-native-esc-pos-printer`
- **Web (fallback):** Render HTML struk → `window.print()` dengan CSS `@media print`
- **Network printer:** Backend generate ESC/POS bytes → kirim ke IP:9100 via TCP
- **Laravel package:** `mike42/escpos-php` untuk server-side print trigger

---

## 🔄 Return & Refund

### Flow Retur
```
Kasir buka riwayat → Pilih order → Klik "Retur"
→ Input alasan + item yang dikembalikan
→ Sistem buat Return Invoice (negatif amount)
→ Refund via metode bayar asal (jika digital → Midtrans Refund API)
→ Stok dikembalikan (jika ada inventory tracking)
→ Owner notified
```

**Rules:**
- Retur hanya bisa dilakukan dalam 24 jam setelah transaksi (configurable)
- Partial retur (sebagian item) diizinkan
- Refund cash: dicatat manual, tidak otomatis
- Refund digital: trigger Midtrans Refund API

---

## ⏱ Shift Management

### Opening Shift
- Kasir input saldo awal kas (uang tunai di laci)
- Sistem catat `shift_opening_id`, timestamp, user

### Closing Shift
- Sistem tampilkan ringkasan: total transaksi, total per metode bayar
- Kasir input cash aktual di laci
- Sistem hitung selisih (expected vs actual)
- Owner bisa lihat rekap di web dashboard

### Database
```
shifts: id, tenant_id, user_id, opened_at, closed_at,
        opening_cash, closing_cash, expected_cash, notes
```

---

## 🧮 Business Logic

```
Revenue         = sum(unit_price × qty) per order
Cost            = sum(unit_cost × qty) per order
Gross Profit    = Revenue − Cost
Net per Order   = Revenue − Discount

Payment         = sum(amount per payment method) = Net per Order
Change          = Payment Cash − Net per Order (jika ada kembalian)

Shift Cash      = Opening Cash + Total Cash Payments − Total Cash Refunds
Cash Difference = Shift Cash Expected − Actual Count
```

---

## 📊 Key Metrics

| Metric | Akses |
|---|---|
| Total transaksi per hari / bulan | Owner |
| Revenue & Profit (HPP) | Owner |
| Transaksi per kasir | Owner |
| Breakdown per metode bayar | Owner |
| Rekap shift (expected vs actual cash) | Owner |
| Transaksi & revenue pribadi hari ini | Kasir |

---

## 🧱 Technical Architecture

```
Mobile POS (React Native Expo)
  ↓ API calls
Backend (Laravel 13 + MySQL)
  ↓ Webhook
Midtrans Payment Gateway
  ↓
ESC/POS Thermal Printer (Bluetooth / Network)

Web Admin (Laravel Blade + DaisyUI + jQuery)
  ↓ REST API
Backend (shared)

Storage: Cloudinary (product images)
```

**Key packages:**
- `laravel-midtrans/midtrans-php` — payment gateway
- `mike42/escpos-php` — thermal printer (server-side)
- `react-native-thermal-printer` — thermal printer (mobile)
- `react-native-camera` — barcode scanning

---

## ⚡ Performance Requirements

| Operasi | Target |
|---|---|
| POS interaction (cart) | < 100ms (local state) |
| Checkout API response | < 2 detik |
| Payment QR generation | < 3 detik |
| Dashboard update | ≤ 5 detik |
| Struk print (Bluetooth) | < 5 detik |
| Webhook processing | < 1 detik |

---

## 🔐 Security

| Area | Implementasi |
|---|---|
| Authentication | Laravel Sanctum (token API) |
| Tenant isolation | `tenant_id` global scope semua model |
| Role-based access | owner / cashier middleware |
| Payment signature | Midtrans `MIDTRANS_SERVER_KEY` verification |
| Webhook validation | IP whitelist + signature hash |
| PII | Tidak simpan nomor kartu, hanya transaction ID |

---

## 🧠 Assumptions

- Digunakan oleh cafe kecil–menengah Indonesia
- Koneksi internet relatif stabil (Midtrans butuh koneksi)
- Kasir menggunakan smartphone Android/iOS
- Printer thermal tersedia di meja kasir
- Owner tidak butuh fitur akuntansi kompleks

---

## ⚠️ Constraints

- Budget operasional ≤ Rp150k/bulan (hosting + Midtrans fee diluar)
- Midtrans fee: 0.7%–2% per transaksi (tergantung metode)
- Timeline MVP v2: +14 hari dari MVP v1
- Fokus pada UX kasir yang zero-friction

---

## 📈 Roadmap

### Phase 1 — MVP v1 ✅ (done)
- Web admin: product CRUD, dashboard, transaksi, monitoring kasir
- Mobile: login, POS grid, cart, checkout
- Multi-user, multi-tenant

### Phase 2 — Payment & Print (current)
- Midtrans payment gateway (QRIS, GoPay, OVO, Dana)
- Cash payment dengan kembalian otomatis
- Cetak struk thermal (Bluetooth + Network)
- Struk digital (tampil di layar)
- Shift management (opening/closing)
- Retur transaksi sederhana
- Kategori produk
- Discount per transaksi

### Phase 3 — Advanced Operations
- Inventory tracking (stok produk)
- Barcode scanner support
- Customer management + loyalty points
- Multi outlet (satu tenant, banyak cabang)
- Advanced analytics (tren harian, produk terlaris)
- Notifikasi WhatsApp / email struk digital

### Phase 4 — SaaS
- Self-service onboarding
- Subscription billing
- Midtrans credential per tenant (whitelabel payment)
- Custom domain per tenant
- API publik untuk integrasi third-party

---

## ✅ Success Criteria

- Kasir dapat transaksi + bayar + cetak struk ≤ 30 detik
- QRIS / e-wallet terkonfirmasi otomatis tanpa input manual kasir
- Owner mendapatkan insight revenue, profit, dan rekap shift
- Struk tercetak akurat dan rapi setiap transaksi
- Sistem berjalan stabil untuk 10+ kasir bersamaan per tenant
