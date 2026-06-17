# 🏥 MediTech SIMRS — Design System

> Panduan desain UI/UX untuk konsistensi antarmuka Sistem Informasi Manajemen Rumah Sakit (SIMRS).
> Terinspirasi dari sistem EMR (Electronic Medical Record) modern yang bersih, profesional, dan mudah digunakan oleh tenaga medis.

---

## 🎨 Filosofi Desain

### Prinsip Utama
1. **Clinical Clarity** — Informasi harus terbaca jelas dalam hitungan detik. Tenaga medis tidak punya waktu untuk mencari-cari.
2. **Calm & Professional** — Gunakan warna netral yang menenangkan. Hindari warna mencolok kecuali untuk status/alert.
3. **Consistent Hierarchy** — Setiap halaman mengikuti pola yang sama: Header → Filter → Content → Footer.
4. **Accessible & Readable** — Font size minimum 13px untuk body text, kontras rasio ≥ 4.5:1.
5. **Responsive & Performant** — Berfungsi baik di desktop (1280px+) dan tablet (768px+).

---

## 🎨 Palet Warna

### Warna Utama (Primary)
| Token                | Value       | Penggunaan                                |
|----------------------|-------------|-------------------------------------------|
| `--primary`          | `#2563EB`   | Tombol utama, link aktif, aksen utama     |
| `--primary-hover`    | `#1D4ED8`   | Hover state tombol utama                  |
| `--primary-light`    | `#EFF6FF`   | Background highlight, selected row        |
| `--primary-border`   | `#BFDBFE`   | Border elemen aktif/selected              |

### Warna Permukaan (Surface)
| Token                | Value       | Penggunaan                                |
|----------------------|-------------|-------------------------------------------|
| `--surface-page`     | `#F8FAFC`   | Background halaman utama (slate-50)       |
| `--surface-card`     | `#FFFFFF`   | Background card, tabel, modal             |
| `--surface-sidebar`  | `#0F172A`   | Background sidebar (slate-900)            |
| `--surface-input`    | `#F8FAFC`   | Background input field default            |

### Warna Teks
| Token                | Value       | Penggunaan                                |
|----------------------|-------------|-------------------------------------------|
| `--text-primary`     | `#0F172A`   | Heading, judul utama (slate-900)          |
| `--text-secondary`   | `#334155`   | Body text, deskripsi (slate-700)          |
| `--text-muted`       | `#64748B`   | Placeholder, label, caption (slate-500)   |
| `--text-disabled`    | `#94A3B8`   | Disabled state (slate-400)                |
| `--text-inverse`     | `#FFFFFF`   | Teks di atas background gelap             |

### Warna Border
| Token                | Value       | Penggunaan                                |
|----------------------|-------------|-------------------------------------------|
| `--border-light`     | `#F1F5F9`   | Divider halus antar row (slate-100)       |
| `--border-default`   | `#E2E8F0`   | Border card, input (slate-200)            |
| `--border-strong`    | `#CBD5E1`   | Border yang perlu lebih terlihat (slate-300)|

### Warna Status / Semantik
| Status     | Background   | Text         | Border       | Penggunaan             |
|------------|-------------|--------------|--------------|------------------------|
| Success    | `#F0FDF4`   | `#166534`    | `#BBF7D0`    | Selesai, Aktif, Valid  |
| Warning    | `#FFFBEB`   | `#92400E`    | `#FDE68A`    | Menunggu, Pending      |
| Danger     | `#FEF2F2`   | `#991B1B`    | `#FECACA`    | Batal, Error, Urgent   |
| Info       | `#EFF6FF`   | `#1E40AF`    | `#BFDBFE`    | Diperiksa, In Progress |
| Neutral    | `#F8FAFC`   | `#475569`    | `#E2E8F0`    | Default, N/A           |

---

## 🔤 Tipografi

### Font Family
```css
font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
```

### Skala Ukuran
| Elemen               | Size    | Weight     | Tailwind Class                        |
|----------------------|---------|------------|---------------------------------------|
| Page Title (h1)      | 22px    | Bold (700) | `text-[22px] font-bold`               |
| Section Title (h2)   | 16px    | Semibold   | `text-base font-semibold`             |
| Card Title (h3)      | 14px    | Semibold   | `text-sm font-semibold`               |
| Body Text            | 14px    | Regular    | `text-sm`                             |
| Table Body           | 13px    | Regular    | `text-[13px]`                         |
| Table Header         | 11px    | Bold+Upper | `text-[11px] font-bold uppercase`     |
| Label                | 12px    | Semibold   | `text-xs font-semibold`               |
| Caption / Help       | 12px    | Regular    | `text-xs`                             |
| Badge / Tag          | 11px    | Bold       | `text-[11px] font-bold`               |
| Sidebar Modul        | 13px    | Semibold   | `text-[13px] font-semibold`           |
| Sidebar Menu         | 13px    | Medium     | `text-[13px] font-medium`             |
| Sidebar Submenu      | 12px    | Medium     | `text-xs font-medium`                 |

---

## 📐 Spacing & Layout

### Ukuran Grid
| Elemen            | Nilai          |
|-------------------|----------------|
| Sidebar width     | `280px` (w-70) |
| Navbar height     | `64px`         |
| Main padding      | `24px` (p-6)   |
| Card padding      | `20px` (p-5)   |
| Card gap          | `20px` (gap-5) |
| Card border-radius| `12px` (rounded-xl) |

### Spacing antar Elemen
| Konteks                      | Nilai     |
|------------------------------|-----------|
| Antar section (card)         | `24px`    |
| Dalam card: header ke konten | `16px`    |
| Antar form field             | `16px`    |
| Padding tabel cell           | `16px` horizontal, `14px` vertical |

---

## 🧩 Komponen Standar

### 1. Card Container
```html
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Card Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Judul Section</h2>
    </div>
    <!-- Card Content -->
    <div class="p-5">
        ...
    </div>
</div>
```

### 2. Page Header
```html
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Judul Halaman</h1>
    <p class="text-sm text-slate-500 mt-1">Deskripsi singkat halaman ini.</p>
</div>
```

### 3. Status Badge
```html
<!-- Success -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
    Aktif
</span>

<!-- Warning -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
    Menunggu
</span>

<!-- Danger -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
    Batal
</span>

<!-- Info -->
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
    Diperiksa
</span>
```

### 4. Form Input
```html
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Label</label>
    <input type="text" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 
           focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 
           transition-all outline-none text-slate-700 placeholder-slate-400">
</div>
```

### 5. Tombol (Buttons)
```html
<!-- Primary -->
<button class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 
               text-white text-sm font-semibold py-2.5 px-5 rounded-lg shadow-sm 
               transition-all duration-200 active:scale-[0.98]">
    Simpan
</button>

<!-- Secondary -->
<button class="inline-flex items-center justify-center gap-2 bg-white border border-slate-200 
               text-slate-600 hover:bg-slate-50 hover:text-slate-800 text-sm font-semibold 
               py-2.5 px-5 rounded-lg shadow-sm transition-all duration-200">
    Batal
</button>

<!-- Danger -->
<button class="inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 
               text-white text-sm font-semibold py-2.5 px-5 rounded-lg shadow-sm 
               transition-all duration-200">
    Hapus
</button>
```

### 6. Data Table
```html
<table class="w-full text-left">
    <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
            <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Header</th>
        </tr>
    </thead>
    <tbody class="text-[13px] divide-y divide-slate-100">
        <tr class="hover:bg-blue-50/40 transition-colors">
            <td class="px-4 py-3.5 text-slate-700">Data</td>
        </tr>
    </tbody>
</table>
```

### 7. Stat Card (Dashboard)
```html
<div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Label Stat</p>
        <div class="p-2 bg-blue-50 rounded-lg">
            <svg class="w-5 h-5 text-blue-600">...</svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-slate-900">1,234</p>
    <p class="text-xs text-slate-400 mt-1">+12% dari bulan lalu</p>
</div>
```

---

## 🧭 Navigasi (Sidebar)

### Hierarki Menu
```
Sidebar
├── Header (Logo + Nama Aplikasi)
├── Search Bar
├── Dashboard Link (selalu di atas)
├── Modul Section Label
│   ├── Modul Item (collapsible)
│   │   ├── Menu Item (collapsible)
│   │   │   ├── Sub Menu Link
│   │   │   └── Sub Menu Link
│   │   └── Menu Item
│   └── Modul Item
└── Footer info (optional)
```

### Aturan Sidebar
- Background: `slate-900` (dark)
- Teks level Modul: `text-[13px] font-semibold text-slate-300`
- Teks level Menu: `text-[13px] font-medium text-slate-400`  
- Teks level SubMenu: `text-xs font-medium text-slate-400`
- Hover state: background berubah halus, teks menjadi lebih terang
- Active/Open state: menggunakan aksen warna `blue-500` atau `indigo-500`
- Padding clickable area: Minimum `py-2.5 px-4` untuk kemudahan klik
- Jangan gunakan warna terlalu terang di sidebar gelap — hindari putih solid untuk elemen non-header

---

## 📋 Layout Halaman

### Struktur Standar
```
┌──────────────────────────────────────────────────────┐
│ Navbar (h-16, sticky top, bg-white, border-b)        │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌─ Page Header ──────────────────────────────────┐  │
│  │ Judul Halaman                                  │  │
│  │ Deskripsi singkat                              │  │
│  └────────────────────────────────────────────────┘  │
│                                                      │
│  ┌─ Filter Card (optional) ───────────────────────┐  │
│  │ form fields in grid                            │  │
│  └────────────────────────────────────────────────┘  │
│                                                      │
│  ┌─ Content Card ─────────────────────────────────┐  │
│  │ Table / Form / Content                         │  │
│  │ ──────────────────────────────────────────────  │  │
│  │ Pagination                                     │  │
│  └────────────────────────────────────────────────┘  │
│                                                      │
├──────────────────────────────────────────────────────┤
│ Footer (border-t, copyright)                         │
└──────────────────────────────────────────────────────┘
```

---

## ⚡ Loading & Transisi

### Loading Screen
- Tampilkan saat halaman pertama dimuat
- Gunakan animasi spinner sederhana + teks "Memuat..."
- Auto-hide setelah `window.load` atau fallback 5 detik
- Transition: fade out 400ms

### Transisi Umum
| Elemen        | Properti        | Durasi   |
|---------------|-----------------|----------|
| Button hover  | background      | `200ms`  |
| Card hover    | shadow          | `200ms`  |
| Sidebar open  | width           | `300ms`  |
| Input focus   | ring, border    | `200ms`  |
| Row hover     | background      | `150ms`  |

---

## ✅ Checklist Sebelum Deploy

- [ ] Semua card menggunakan `rounded-xl border border-slate-200 shadow-sm`
- [ ] Semua tabel menggunakan header `bg-slate-50` dengan teks uppercase
- [ ] Semua badge status menggunakan pola warna semantik yang konsisten
- [ ] Semua input menggunakan `rounded-lg border-slate-200 focus:ring-blue-500`
- [ ] Sidebar clickable area minimal `py-2.5`
- [ ] Font Inter ter-load dengan benar
- [ ] Loading screen berfungsi dan auto-hide
- [ ] Responsif di layar 768px ke atas

---

## 📝 Catatan Pengembangan

- Gunakan **Tailwind CSS v4** (via `@tailwindcss/vite`)
- Font utama: **Inter** (Google Fonts)
- Icon: Inline SVG (Heroicons style) — jangan mix dengan FontAwesome
- Hindari class inline yang terlalu panjang — pertimbangkan `@apply` untuk pattern yang sering berulang
- Setiap halaman baru **harus** mengacu ke dokumen ini untuk konsistensi
