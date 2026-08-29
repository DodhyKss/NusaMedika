# Aturan Commit — NusaMedika (MediTechV2)

Panduan penulisan commit message untuk project SIMRS ini. Ikuti aturan ini agar riwayat git rapi, konsisten, dan mudah ditelusuri.

## Bahasa

- **Semua commit message ditulis dalam Bahasa Indonesia** (mengikuti konvensi seluruh kode & UI di project ini).
- Hindari campur bahasa (Inggris + Indonesia) kecuali istilah teknis yang memang tidak ada padanannya (mis. `fix`, `refactor`, nama class/foreign key).

## Format pesan

Gunakan **Conventional Commits**:

```
<type>(<scope>): <ringkasan>
```

- `<type>` — jenis perubahan (lihat daftar di bawah)
- `<scope>` — bagian yang diubah (opsional, tapi disarankan). Contoh scope: `pasien`, `jadwal-dokter`, `sidebar`, `emr`, `migration`, `route`, `seed`.
- `<ringkasan>` — kalimat pendek, verdikatif, tidak berakhiran titik.
- Baris subjek maksimal **±72 karakter**.

Contoh:

```
feat(pasien): tambah pencarian pasien via Select2
fix(jadwal-dokter): cegah duplikat jadwal di poli & hari sama
refactor(route): otomatisasi route dari tabel sub_menu
chore(migration): buat 39 migration bersih dari tabel aktif
```

## Tipe (type)

| Type      | Kapan dipakai                                                          |
|-----------|------------------------------------------------------------------------|
| `feat`    | Fitur baru                                                             |
| `fix`     | Perbaikan bug                                                          |
| `refactor`| Ubah struktur kode tanpa ubah perilaku                                 |
| `style`   | Format/rapikan kode (mis. hasil `pint`), tidak ubah logika              |
| `test`    | Tambah/ubah test                                                       |
| `docs`    | Perubahan dokumentasi (README, AGENTS.md, md)                          |
| `chore`   | Tugas rutin: dependency, config, migration, seed, dll.                 |
| `perf`    | Optimasi performa                                                      |
| `build`   | Perubahan build/dependency (composer.json, package.json)               |

> `style` berbeda dari `refactor`: `style` **tidak** mengubah logika sama sekali (hanya formatting, contoh hasil `php vendor/bin/pint`). `refactor` mengubah struktur tapi perilaku tetap.

## Ringkasan (subjek)

- Mulai dengan **kata kerja** bentuk dasar (imperatif): `tambah`, `hapus`, `perbaiki`, `ubah`, `pindahkan`, `perbarui`, `ganti`, `bersihkan`.
- Fokus **apa & mengapa**, bukan oke-oke-an.
- Jangan akhiri dengan titik (`.`).

**Contoh benar:**
- `feat: tambah modul Administrasi Manajemen User`
- `fix: atur ulang sequence setelah seeder mengisi ID eksplisit`

**Contoh salah:**
- `perbaiki bug.` ✗ (tidak jelas bug apa, ada titik)
- `update file` ✗ (terlalu umum)
- `fix: fixing the bug.` ✗ (campur bahasa, bukan imperatif)

## Kapan membuat commit

Jangan campur banyak hal dalam satu commit. Satu commit = satu tujuan logis.

Contoh pemisahan yang baik:

```bash
# 1. rapikan dulu kode lama (style)
git add app/Models/User.php
git commit -m "style(model): rapikan format User via pint"

# 2. fitur baru terpisah
git add app/Http/Controllers/Registrasi/Pasien/
git commit -m "feat(pasien): tambah form pendaftaran pasien baru"
```

## Sebelum commit — wajib

1. **Jalankan analisis statis/code style dulu:**
   ```bash
   docker compose exec -T app php vendor/bin/pint --test
   ```
   Jika ada yang belum rapi, jalankan `php vendor/bin/pint` lalu commit sebagai `style:` (atau gabung jika masih dalam perubahan yang sama).

2. **Periksa apa yang akan di-commit** — jangan sampai ada file tidak diinginkan:
   ```bash
   git status
   git diff
   ```

3. **Jangan commit file rahasia** — `.env` **wajib** di-ignore (mengandung password DB). Jangan pernah `git add .env`.

4. **Uji perubahan** bila relevan (`docker compose exec -T app php artisan test`).

## Langkah commit (ringkas)

```bash
git add <file/kirim>
git commit -m "feat(pasien): tambah kolom no_mr otomatis"
git push
```

## Contoh pesan commit nyata

```bash
feat(nasabah): tambah master nasabah dengan instalasi json
fix(wilayah): cascade soft-delete provinsi ke kelurahan
refactor(routing): hapus SubMenuRouter, pakai ServiceProvider
style: format ulang seluruh model dengan pint
test(select-option): tambah unit test SelectOption
chore(seed): buat KelasRuangSeeder untuk data kelas awal
build: tambah script composer lint & format
```

## TypeScript/JS dll.

Commit yang menyentuh `resources/js` mengikuti aturan yang sama (type + scope), mis.:

```
feat(js): tambah initWilayahCascade untuk form pasien
fix(select): ganti pencarian pasien ke Select2 statis
```

## Catatan tambahan

- Hindari `commit -am` / gabung banyak file tidak relevan.
- Bila perlu konteks lebih (mis. kenapa solusinya begini), bisa ditambah **body** setelah subjek (baris kosong lalu isi detail), format Indonesia juga.
- Konsistenlah; riwayat yang rapi memudahkan `git bisect`, `git log`, dan review PR.
