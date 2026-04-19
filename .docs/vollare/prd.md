# Product Requirements Document (PRD)
## Smart POS System — Volare Caffe

---

## 🧠 Problem Statement

1. POS existing terlalu kompleks dan membingungkan
2. Harga POS terlalu mahal untuk bisnis kecil
3. Owner sulit memonitor performa karyawan
4. Tidak ada visibilitas profit (HPP)

---

## 🎯 Product Vision

Membangun POS system yang:
- Simple untuk kasir
- Powerful untuk owner
- Terjangkau untuk UMKM
- Bisa digunakan oleh banyak brand (whitelabel)

---

## 👤 Target Users

### Owner
- Mengelola produk
- Melihat laporan penjualan
- Monitoring performa kasir

### Kasir
- Input order dengan cepat
- Minim kesalahan
- UI sederhana & intuitif

---

## 🧩 Core Features

### POS (Kasir)
- Product grid berbasis gambar
- Cart system
- Checkout cepat
- Instant feedback

---

### Admin (Owner)
- Product management
- Sales report
- Profit report (berbasis HPP)
- Employee performance monitoring

---

### System
- Multi-user
- Multi-tenant ready
- Whitelabel branding
- Near realtime update

---

## 💡 Key Differentiation

- UI sangat simple (anti ribet)
- Berbasis visual (image produk)
- Ada profit visibility (HPP)
- Siap digunakan banyak brand
- Pricing terjangkau

---

## 🎨 Design Principles

- Menggunakan warna brand cafe (warm & clean)
- UI minimal & tidak membingungkan
- Fokus pada kecepatan transaksi
- Image-first interface

Tujuan:
- Mengurangi kebingungan kasir
- Memberikan kesan modern & premium

---

## 🧮 Business Logic

Revenue:
sum(price × qty)

Cost:
sum(cost × qty)

Profit:
revenue - cost

---

## 📊 Key Metrics

- Total transaksi per hari
- Revenue
- Profit
- Transaksi per kasir

---

## 🧱 Technical Architecture

Mobile:
- React Native (Expo)

Backend:
- Laravel API

Web Admin:
- Laravel Blade

Database:
- MySQL

Storage:
- Cloudinary

---

## ⚡ Performance Requirements

- POS interaction < 100ms (local state)
- Checkout < 2 detik
- Dashboard update ≤ 5 detik

---

## 🔐 Security

- Authentication (basic)
- Tenant isolation (tenant_id)
- Role-based access (owner / kasir)

---

## 🧠 Assumptions

- Digunakan oleh cafe kecil–menengah
- Transaksi tidak high-frequency
- Internet relatif stabil
- Owner tidak membutuhkan fitur kompleks

---

## ⚠️ Constraints

- Budget rendah (≤ Rp150k/bulan)
- Timeline cepat (7 hari)
- Fokus ke MVP demo

---

## 📈 Future Roadmap

Phase 2:
- Inventory tracking
- Multi outlet
- Role & permission advanced

Phase 3:
- SaaS onboarding system
- Subscription billing
- Advanced analytics

---

## 🚀 Go-To-Market Strategy

- Target: cafe & UMKM
- Model:
  - Setup murah
  - Subscription bulanan
- Positioning:
  - POS simple & modern
  - Affordable solution

---

## ✅ Success Criteria

- Kasir dapat menggunakan tanpa training
- Owner mendapatkan insight penjualan & profit
- UI terasa cepat & modern
- Sistem dapat digunakan ulang untuk client lain