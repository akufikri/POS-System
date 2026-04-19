# MVP POS System — Volare Caffe

## 🎯 Goal
Membangun sistem POS sederhana, cepat, dan mudah digunakan untuk demo ke client dengan:
- UI kasir yang super simple & cepat
- Multi-user (owner & kasir)
- Monitoring penjualan & performa kasir
- Estimasi profit (HPP)
- Siap dikembangkan menjadi SaaS (multi-tenant & whitelabel)

---

## 🚀 Scope MVP

### 1. POS App (Mobile — React Native Expo)

Fitur:
- Login sederhana (email / PIN)
- List produk (image + name + price)
- Add to cart (local state)
- Update qty
- Checkout
- Success notification (instant)

Flow:
1. App load → fetch products by tenant
2. User klik produk → masuk cart (tanpa API)
3. Checkout → kirim order ke backend
4. Cart clear → tampil success

---

### 2. Admin Web (Laravel Blade)

Fitur:
- CRUD produk:
  - nama
  - harga jual
  - cost (HPP)
  - image
  - deskripsi
- List transaksi
- Dashboard:
  - total revenue
  - total cost
  - profit
  - performa kasir

---

### 3. System Core

- Multi-user (owner, kasir)
- Multi-tenant (berbasis tenant_id)
- Whitelabel (branding per tenant)
- Image upload (Cloudinary)
- Near realtime (polling 5 detik)

---

## 🧠 UX Principles

- Kasir harus bisa transaksi tanpa training
- Maksimal 2–3 klik untuk checkout
- Tidak ada menu kompleks di POS
- Fokus ke speed & clarity
- Gunakan visual image produk

---

## 🎨 UI & Branding (Volare Theme)

### Core Colors
- Primary: #edcc94
- Background: #ffffff
- Text: #010101

### Extended Colors
- Primary Light: #f5e3bd
- Primary Dark: #c9a96e
- Surface: #f5f5f5
- Border: #e5e5e5
- Text Secondary: #6b7280

---

### Usage Guidelines

Primary:
- Tombol checkout
- Highlight item aktif

Black:
- Text utama
- Icon

White:
- Background utama
- Card

---

### POS UI Rules

- Grid produk berbasis gambar
- Cart selalu visible
- Checkout button menonjol
- Layout clean & tidak ramai
- Rounded corner (12–16px)

---

## ⚡ Performance Strategy

- Local-first cart (tanpa API saat interaksi)
- API hanya saat checkout
- Preload produk saat app launch
- Gunakan CDN untuk image
- Optimistic UI (no delay feeling)

---

## 🔌 API Minimal

POST   /api/login  
GET    /api/products?tenant=slug  
POST   /api/orders  
GET    /api/dashboard/summary  

---

## 🗄 Database (Core Tables)

tenants  
users  
products  
orders  
order_items  

---

## 🧩 Data Rules

- Semua table menggunakan tenant_id
- Simpan price & cost di order_items (snapshot)
- Tidak ada hardcoded data per brand

---

## 📅 Timeline (7 Hari)

Day 1:
- Setup Laravel + database

Day 2:
- CRUD produk (admin)

Day 3:
- Expo POS UI

Day 4:
- Integrasi API

Day 5:
- Dashboard + multi user

Day 6:
- Testing & bug fix

Day 7:
- Demo preparation

---

## ❌ Out of Scope (MVP)

- Inventory management
- Supplier system
- Advanced analytics
- WebSocket realtime
- Payment gateway
- Multi outlet

---

## ✅ Success Criteria

- Transaksi bisa dilakukan < 10 detik
- UI terasa cepat & tanpa delay
- Data tersimpan dengan benar
- Owner bisa melihat revenue & profit
- System bisa dipakai ulang untuk client lain (whitelabel ready)