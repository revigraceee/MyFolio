# Product Requirements Document (PRD) - MyFolio

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Nama Proyek** | MyFolio - Dynamic Portfolio CMS & Showcase Platform |
| **Versi Dokumen** | 1.0 |
| **Tech Stack** | PHP 8.x Native, MySQL / MariaDB (PDO), Bootstrap 5.3, Vanilla JavaScript |
| **Target Pengguna** | Developer, Desainer, Freelancer, Mahasiswa, dan Profesional |

---

## 1. Ringkasan Eksekutif & Latar Belakang Proyek
Bagi seorang profesional teknologi, kreatif, maupun *fresh graduate*, memiliki portofolio digital yang interaktif dan mudah diakses sangat krusial untuk menarik perhatian rekruter atau klien. 

**MyFolio** adalah aplikasi web portofolio pribadi yang dinamis (*Dynamic Portfolio CMS*). Aplikasi ini dilengkapi dengan panel administrasi (*Admin Panel*) terpadu, memungkinkan pemilik situs untuk mengelola profil, daftar keahlian, pameran proyek (*project showcase*), riwayat pengalaman, dan membaca pesan kontak masuk secara *real-time* tanpa perlu mengubah kode sumber HTML secara manual.

---

## 2. Tujuan Produk (Product Objectives)
* Menyediakan halaman profil profesional yang bersih, modern, dan sepenuhnya responsif di semua perangkat seluler.
* Memfasilitasi pengelolaan konten portofolio (CRUD Proyek, Skill, dan Pengalaman) melalui panel admin yang aman.
* Menyediakan fitur interaktif bagi pengunjung untuk memfilter portofolio berdasarkan kategori dan mengirimkan pesan atau tawaran kerja secara langsung.

---

## 3. Peran Pengguna (User Roles)
1. **Visitor / Pengunjung Publik**
   * Melihat halaman utama portofolio (`index.php`).
   * Membaca informasi profil, bio, dan status ketersediaan kerja (*Open to Work*).
   * Memfilter proyek berdasarkan kategori (Web Development, UI/UX Design, dll.).
   * Mengunduh CV dan mengakses tautan eksternal (GitHub, LinkedIn, Live Demo).
   * Mengirim pesan melalui formulir kontak/hire me.
2. **Administrator (Pemilik Portofolio)**
   * Melakukan login aman menggunakan enkripsi *password* (`password_hash` / `password_verify`).
   * Mengelola data profil utama dan tautan sosial media.
   * Menambah, mengedit, atau menghapus data keahlian (*Skills* & persentase level).
   * Menambah, mengedit, atau menghapus proyek portofolio (lengkap dengan *tech stack* dan tautan repo/demo).
   * Menambah, mengedit, atau menghapus riwayat pengalaman kerja dan pendidikan.
   * Membaca dan menghapus pesan masuk dari klien/HRD.

---

## 4. Spesifikasi Fungsional (Functional Requirements)

### 4.1 Modul Halaman Publik (`Public Frontend`)
* **FR-01:** Menampilkan bagian **Hero Section** yang memuat foto profil, headline profesi, bio singkat, status ketersediaan kerja, dan tombol unduh CV.
* **FR-02:** Menampilkan bagian **Skills Showcase** yang dilengkapi dengan indikator progres visual (*Bootstrap Progress Bar*) dan persentase penguasaan.
* **FR-03:** Menampilkan bagian **Projects Showcase** dengan tombol filter kategori dinamis menggunakan JavaScript tanpa memuat ulang halaman (*page reload*).
* **FR-04:** Menampilkan bagian **Timeline Pengalaman & Pendidikan** dalam bentuk tata letak vertikal yang rapi.
* **FR-05:** Menyediakan **Formulir Kontak / Hire Me** yang memvalidasi input dan menyimpan pesan langsung ke dalam tabel database MySQL.

### 4.2 Modul Otentikasi Admin (`Admin Auth`)
* **FR-06:** Halaman login khusus admin (`admin/login.php`) yang memverifikasi kredensial pengguna terhadap database menggunakan fungsi `password_verify()`.
* **FR-07:** Implementasi kontrol sesi PHP (`$_SESSION`) untuk memastikan halaman admin hanya dapat diakses oleh admin yang sudah login.

### 4.3 Modul Panel Admin / CMS (`Admin Dashboard`)
* **FR-08:** **Manajemen Profil:** Form untuk memperbarui nama, headline, deskripsi tentang saya, kontak, dan tautan sosial media.
* **FR-09:** **Manajemen Keahlian (CRUD Skills):** Menambah, mengubah, atau menghapus daftar *skill* beserta kategori dan persentasenya.
* **FR-10:** **Manajemen Proyek (CRUD Projects):** Menambah, mengubah, atau menghapus portofolio proyek (judul, kategori, deskripsi, *tech stack*, link GitHub, link demo, dan status *featured*).
* **FR-11:** **Manajemen Pengalaman (CRUD Experiences):** Menambah, mengubah, atau menghapus riwayat kerja, organisasi, atau pendidikan.
* **FR-12:** **Inbox Pesan Masuk:** Tabel daftar pesan dari pengunjung dengan status baca/belum dibaca serta opsi untuk menghapus pesan.

---

## 5. Rancangan Skema Database MySQL (`db_myfolio`)

Struktur tabel relasional dirancang optimal untuk performa aplikasi berbasis PHP Native:

### 1. Tabel `profile` (Data Pemilik Portofolio)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik profil |
| `full_name` | VARCHAR(100) | Nama lengkap pemilik |
| `headline` | VARCHAR(150) | Judul profesi utama |
| `about_me` | TEXT | Deskripsi panjang tentang diri |
| `email` | VARCHAR(100) | Email kontak publik |
| `phone` | VARCHAR(30) | Nomor telepon / WhatsApp |
| `location` | VARCHAR(100) | Domisili / Lokasi |
| `avatar` | VARCHAR(255) | Nama file foto profil |
| `cv_link` | VARCHAR(255) | Tautan unduh berkas CV |
| `github_url` | VARCHAR(255) | Tautan profil GitHub |
| `linkedin_url` | VARCHAR(255) | Tautan profil LinkedIn |
| `is_available` | TINYINT(1) | Status ketersediaan kerja (1 = Ya, 0 = Tidak) |
| `admin_username`| VARCHAR(50) | Username login admin |
| `admin_password`| VARCHAR(255) | Password terenkripsi admin |

### 2. Tabel `skills` (Keahlian & Teknologi)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik skill |
| `skill_name` | VARCHAR(100) | Nama keterampilan (cth: PHP Native, Bootstrap) |
| `category` | ENUM | Kategori (Frontend, Backend, Database & Tools, Soft Skills) |
| `percentage` | INT | Tingkat penguasaan (1 - 100) |
| `badge_icon` | VARCHAR(50) | Kelas ikon Bootstrap Icons |

### 3. Tabel `projects` (Daftar Proyek Portofolio)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik proyek |
| `title` | VARCHAR(150) | Judul proyek |
| `category` | ENUM | Kategori (Web Development, Mobile App, UI/UX Design, Data & AI) |
| `thumbnail` | VARCHAR(255) | Gambar pratinjau proyek |
| `short_desc` | VARCHAR(255) | Penjelasan ringkas proyek |
| `tech_stack` | VARCHAR(200) | Daftar teknologi (dipisahkan koma) |
| `repo_url` | VARCHAR(255) | Tautan repositori source code |
| `demo_url` | VARCHAR(255) | Tautan live demo aplikasi |
| `is_featured` | TINYINT(1) | Penanda proyek unggulan (1 = Ya, 0 = Tidak) |
| `created_at` | TIMESTAMP | Tanggal data dibuat |

### 4. Tabel `experiences` (Riwayat Pengalaman & Pendidikan)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik pengalaman |
| `type` | ENUM | Jenis (Work, Education, Certification) |
| `title` | VARCHAR(150) | Posisi jabatan / Jurusan studi |
| `organization`| VARCHAR(150) | Nama perusahaan / Institusi pendidikan |
| `period` | VARCHAR(50) | Rentang waktu (cth: 2024 - Sekarang) |
| `description` | TEXT | Deskripsi pencapaian / tanggung jawab |

### 5. Tabel `messages` (Inbox Pesan Masuk)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik pesan |
| `sender_name` | VARCHAR(100) | Nama pengirim pesan |
| `sender_email` | VARCHAR(100) | Email pengirim pesan |
| `subject` | VARCHAR(150) | Subjek pesan |
| `message` | TEXT | Isi pesan |
| `is_read` | TINYINT(1) | Status dibaca (0 = Belum, 1 = Sudah) |
| `sent_at` | TIMESTAMP | Waktu pesan dikirim |

---

## 6. Struktur Direktori Proyek (PHP Native)

Struktur direktori dirancang modular dan mudah dipelihara tanpa menggunakan *framework* eksternal:

```text
myfolio/
├── assets/
│   ├── css/               # Kustomisasi gaya tambahan
│   └── img/               # Direktori penyimpanan gambar profil & thumbnail proyek
├── config/
│   └── database.php       # Konfigurasi koneksi PDO MySQL & fungsi helper (e.g., e())
├── admin/
│   ├── login.php          # Formulir autentikasi admin & proses login
│   ├── dashboard.php      # Panel CMS untuk mengelola Profil, Skill, Proyek, & Pesan
│   └── logout.php         # Penghancur sesi (session destroy)
├── index.php              # Halaman Utama Portofolio Publik & Form Kontak
└── prd.md                 # Dokumen PRD ini
```

---

## 5. Alur Kerja (User Flow)

### 5.1 Alur Admin — Manajemen Kategori Proyek

```text
Admin Login
    └── Dashboard
         └── Sidebar: "Kategori Proyek" → admin/categories.php
              ├── [Tambah Kategori]
              │    └── Isi nama kategori → Submit → Slug auto-generate → Simpan ke DB → Redirect + Flash "Kategori berhasil ditambahkan"
              ├── [Edit Kategori]
              │    └── Klik tombol Edit → Form pre-filled → Ubah nama → Submit → Update DB → Flash "Kategori berhasil diperbarui"
              └── [Hapus Kategori]
                   ├── Jika kategori MASIH digunakan proyek → Flash Error "Tidak dapat dihapus, masih terhubung ke X proyek"
                   └── Jika kategori TIDAK digunakan → Konfirmasi JS confirm() → DELETE → Flash "Kategori dihapus"
```

### 5.2 Alur Admin — Tambah / Edit Proyek (Dengan Kategori Dinamis)

```text
admin/projects.php → Form Tambah/Edit Proyek
    └── Dropdown <select name="category_id">
         └── Data diambil dari: SELECT id, category_name FROM project_categories ORDER BY category_name
              └── Admin pilih kategori → Submit → Simpan category_id (FK) ke tabel projects
```

### 5.3 Alur Pengunjung — Filter Proyek di Halaman Publik

```text
Pengunjung buka index.php
    └── Tombol filter dihasilkan dari: SELECT id, category_name, slug FROM project_categories
         └── Klik tombol filter [slug] → JavaScript filter card proyek berdasarkan data-category="[slug]"
              └── Hanya proyek dengan kategori yang dipilih yang ditampilkan (tanpa reload halaman)
```

---

## 6. Spesifikasi Antarmuka (UI Specification)

### 6.1 Halaman `admin/categories.php`

**Layout:** Mengikuti template admin standar (sidebar kiri + konten kanan) sesuai `admin_header.php`.

**Komponen Halaman:**

| Komponen | Detail |
| :--- | :--- |
| **Judul Halaman** | "Manajemen Kategori Proyek" + badge jumlah kategori |
| **Tombol Aksi** | `+ Tambah Kategori` (pojok kanan atas, warna olive) → memicu modal Bootstrap |
| **Tabel Daftar** | Kolom: No, Nama Kategori, Slug, Jumlah Proyek Terkait, Aksi |
| **Aksi per Baris** | Tombol `Edit` (ikon pensil, biru) + Tombol `Hapus` (ikon trash, merah) |
| **State Kosong** | Pesan "Belum ada kategori. Tambahkan kategori pertama Anda." |

**Modal Tambah / Edit Kategori:**

| Elemen | Spesifikasi |
| :--- | :--- |
| Judul Modal | "Tambah Kategori Baru" / "Edit Kategori" |
| Input Nama | `<input type="text" name="category_name" required maxlength="100">` |
| Input Slug | *Read-only*, di-generate otomatis dari nama saat admin mengetik (via JavaScript) |
| Tombol Simpan | `Simpan Kategori` (olive) |
| Tombol Batal | `Batal` (secondary/abu-abu) |

### 6.2 Pembaruan `admin/projects.php` (Form Kategori)

Dropdown kategori pada form Tambah/Edit Proyek diubah dari `<select>` statis menjadi dinamis:

```php
// SEBELUM (statis - v1.0):
<option value="Web Development">Web Development</option>

// SESUDAH (dinamis - v2.0):
<?php foreach ($categories as $cat): ?>
    <option value="<?= $cat['id'] ?>" <?= $project['category_id'] == $cat['id'] ? 'selected' : '' ?>>
        <?= e($cat['category_name']) ?>
    </option>
<?php endforeach; ?>
```

### 6.3 Pembaruan `index.php` (Tombol Filter Publik)

```php
// SEBELUM (statis - v1.0):
<button data-filter="web-development">Web Development</button>

// SESUDAH (dinamis - v2.0):
<?php foreach ($categories as $cat): ?>
    <button class="filter-btn" data-filter="<?= e($cat['slug']) ?>">
        <?= e($cat['category_name']) ?>
    </button>
<?php endforeach; ?>
```

---

## 7. Script Migrasi & DDL Database (SQL)

Script SQL berikut dijalankan untuk memigrasikan database dari v1.0 ke v2.0:

```sql
-- ============================================================
-- MIGRASI DATABASE: MyFolio v1.0 → v2.0
-- Dijalankan di phpMyAdmin atau via CLI: mysql -u root db_myfolio < migrate_v2.sql
-- ============================================================

-- LANGKAH 1: Buat tabel master kategori
CREATE TABLE IF NOT EXISTS `project_categories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(100) NOT NULL UNIQUE,
    `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LANGKAH 2: Seed data kategori default
INSERT INTO `project_categories` (`category_name`, `slug`, `sort_order`) VALUES
('Web Development',  'web-development',  1),
('Mobile App',       'mobile-app',       2),
('UI/UX Design',     'ui-ux-design',     3),
('Data & AI',        'data-ai',          4),
('Lainnya',          'lainnya',          5);

-- LANGKAH 3: Tambah kolom category_id ke tabel projects
ALTER TABLE `projects`
    ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `title`;

-- LANGKAH 4: Isi category_id berdasarkan nilai ENUM lama
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'web-development')  WHERE `category` = 'Web Development';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'mobile-app')       WHERE `category` = 'Mobile App';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'ui-ux-design')     WHERE `category` = 'UI/UX Design';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'data-ai')          WHERE `category` = 'Data & AI';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'lainnya')          WHERE `category` IS NULL OR `category` = '';

-- LANGKAH 5: Tambah Foreign Key constraint
ALTER TABLE `projects`
    ADD CONSTRAINT `fk_projects_category`
    FOREIGN KEY (`category_id`) REFERENCES `project_categories`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- LANGKAH 6: Hapus kolom category lama (setelah data termigrasi)
ALTER TABLE `projects` DROP COLUMN `category`;
```

---

## 8. Spesifikasi Non-Fungsional (Non-Functional Requirements)

| ID | Kategori | Ketentuan |
| :--- | :--- | :--- |
| **NFR-01** | Keamanan | Semua input form wajib di-sanitasi menggunakan fungsi `e()` (htmlspecialchars) sebelum ditampilkan ke halaman. |
| **NFR-02** | Keamanan | Semua query database wajib menggunakan PDO *Prepared Statements* untuk mencegah SQL Injection. |
| **NFR-03** | Keamanan | Slug kategori di-*sanitize* server-side: diubah ke huruf kecil, spasi diganti `-`, karakter non-alphanumeric dihapus. |
| **NFR-04** | Performa | Query pengambilan kategori di-*cache* dalam variabel PHP selama satu request (tidak memanggil DB berulang kali per halaman). |
| **NFR-05** | Kompatibilitas | Halaman berjalan baik di PHP 8.0+ dan MariaDB 10.4+ (bundel XAMPP standar). |
| **NFR-06** | UX | Setiap aksi CRUD menampilkan pesan *flash notification* (sukses/error) menggunakan fungsi `set_flash()` dan `get_flash()`. |
| **NFR-07** | Responsivitas | Tabel daftar kategori dapat di-scroll secara horizontal di perangkat mobile (kelas Bootstrap `table-responsive`). |

---

## 9. Kasus Uji (Test Cases)

| ID | Skenario | Langkah | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-01** | Tambah kategori baru | Buka `admin/categories.php` → klik "+ Tambah" → isi "Machine Learning" → Submit | Kategori tersimpan, slug `machine-learning` ter-generate, muncul di tabel, flash sukses | ✅ |
| **TC-02** | Slug auto-generate | Isi nama "Data & AI (2024)" di form | Slug menjadi `data-ai-2024` (simbol dihapus, spasi jadi `-`) | ✅ |
| **TC-03** | Nama kategori duplikat | Tambah kategori dengan nama yang sudah ada | Sistem menampilkan pesan error "Nama kategori sudah digunakan" | ✅ |
| **TC-04** | Edit kategori | Klik Edit pada "Lainnya" → ubah nama → Simpan | Data terupdate di DB, perubahan langsung tampil di halaman publik | ✅ |
| **TC-05** | Hapus kategori tidak terpakai | Hapus kategori "Lainnya" (0 proyek terkait) | Kategori terhapus dari DB, flash "Kategori berhasil dihapus" | ✅ |
| **TC-06** | Hapus kategori terpakai | Hapus kategori "Web Development" (ada proyek terkait) | Sistem menolak penghapusan, tampilkan flash error beserta jumlah proyek terkait | ✅ |
| **TC-07** | Filter publik dinamis | Admin tambah kategori "Blockchain" → buka `index.php` | Tombol filter "Blockchain" muncul otomatis di halaman publik | ✅ |
| **TC-08** | Dropdown proyek dinamis | Buka form tambah proyek di admin | Dropdown kategori memuat semua kategori dari DB, bukan nilai statis | ✅ |
| **TC-09** | Validasi form kosong | Submit form tambah kategori tanpa nama | Form tidak terkirim, pesan validasi HTML5 muncul di field kosong | ✅ |
| **TC-10** | Hak akses halaman | Akses `admin/categories.php` tanpa login | Redirect otomatis ke `admin/login.php` | ✅ |

---

## 10. Rencana Implementasi (Implementation Plan)

Urutan pengerjaan fitur v2.0 berdasarkan dependensi:

| Tahap | Tugas | File Terdampak | Prioritas |
| :--- | :--- | :--- | :--- |
| **1** | Jalankan script migrasi SQL di phpMyAdmin | `database.sql` (diperbarui) | 🔴 Tinggi |
| **2** | Buat halaman `admin/categories.php` (CRUD Kategori) | `admin/categories.php` *(baru)* | 🔴 Tinggi |
| **3** | Update `admin/projects.php` → dropdown kategori dinamis | `admin/projects.php` | 🔴 Tinggi |
| **4** | Update `index.php` → tombol filter + card proyek dinamis | `index.php` | 🔴 Tinggi |
| **5** | Tambahkan menu "Kategori Proyek" di sidebar admin | `admin/includes/admin_header.php` | 🟡 Sedang |
| **6** | Update fungsi slug di `includes/functions.php` | `includes/functions.php` | 🟡 Sedang |
| **7** | Update `database.sql` master dengan skema v2.0 | `database.sql` | 🟡 Sedang |
| **8** | Testing semua test cases TC-01 s/d TC-10 | — | 🟢 Rendah |
| **9** | Push ke GitHub dengan tag `v2.0` | — | 🟢 Rendah |

---

## 11. Kriteria Penyelesaian (Definition of Done)

Fitur v2.0 dinyatakan **selesai dan siap rilis** apabila seluruh kriteria berikut terpenuhi:

- [x] Script migrasi SQL berhasil dijalankan tanpa error
- [x] Halaman `admin/categories.php` dapat Tambah, Edit, dan Hapus kategori
- [x] Proteksi penghapusan kategori aktif berjalan dengan benar
- [x] Slug kategori ter-generate otomatis dan unik
- [x] Dropdown kategori pada form proyek memuat data dari DB
- [x] Tombol filter di halaman publik bersifat dinamis (dari DB)
- [x] Semua 10 test cases lulus (✅)
- [x] Tidak ada PHP warning/error di semua halaman yang terpengaruh
- [x] Kode di-push ke GitHub branch `main` dengan pesan commit yang deskriptif

---

*Dokumen ini terakhir diperbarui: November 2026 — MyFolio CMS v2.0*
# Product Requirements Document (PRD) - MyFolio v2.0

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Nama Proyek** | MyFolio - Dynamic Portfolio CMS & Showcase Platform |
| **Versi Dokumen** | 2.0 (Penambahan Modul Manajemen Kategori Proyek) |
| **Tech Stack** | PHP 8.x Native, MySQL / MariaDB (PDO), Bootstrap 5.3, Vanilla JavaScript |
| **Target Pengguna** | Developer, Desainer, Freelancer, Mahasiswa, dan Profesional |

---

## 1. Ringkasan Eksekutif & Pembaruan Versi 2.0
Pada versi sebelumnya, kategori proyek pada tabel `projects` menggunakan tipe data `ENUM` yang bersifat statis dan kaku (misal: hanya terbatas pada Web Development, Mobile App, dll.). 

Pembaruan **Versi 2.0** ini memisahkan kategori menjadi tabel master tersendiri (`project_categories`). Hal ini memungkinkan administrator untuk **menambah, mengubah (edit), dan menghapus kategori proyek secara dinamis** langsung melalui *Admin Dashboard*, sehingga pengelolaan portofolio menjadi lebih fleksibel seiring berkembangnya jenis keahlian atau layanan pengguna.

---

## 2. Pembaruan Skema Database MySQL (`db_myfolio`)

Struktur database diperbarui dengan menambahkan tabel relasional baru untuk kategori:

### 1. Tabel Baru: `project_categories` (Master Kategori Proyek)
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik kategori |
| `category_name` | VARCHAR(100) NOT NULL | Nama kategori (cth: Web App, Machine Learning, UI/UX) |
| `slug` | VARCHAR(100) NOT NULL | Slug URL yang ramah SEO (cth: web-app) |

### 2. Modifikasi Tabel `projects`
Kolom `category` yang sebelumnya bertipe `ENUM` diubah menjadi relasi foreign key ke tabel master kategori:
| Kolom | Tipe Data | Keterangan |
| :--- | :--- | :--- |
| `id` | INT (PK, AI) | ID unik proyek |
| `category_id` | INT (FK) | Relasi ke `project_categories.id` |
| `title` | VARCHAR(150) | Judul proyek |
| `thumbnail` | VARCHAR(255) | Gambar pratinjau proyek |
| `short_desc` | VARCHAR(255) | Penjelasan ringkas proyek |
| `tech_stack` | VARCHAR(200) | Daftar teknologi (dipisahkan koma) |
| `repo_url` | VARCHAR(255) | Tautan repositori source code |
| `demo_url` | VARCHAR(255) | Tautan live demo aplikasi |
| `is_featured` | TINYINT(1) | Penanda proyek unggulan (1 = Ya, 0 = Tidak) |
| `created_at` | TIMESTAMP | Tanggal data dibuat |

---

## 3. Spesifikasi Fungsional Fitur Baru (Functional Requirements)

### 3.1 Modul Manajemen Kategori di Admin Panel (`Admin CMS`)
* **FR-01 (Tambah Kategori):** Administrator dapat memasukkan nama kategori baru melalui form modal atau halaman khusus di `admin/categories.php`. Sistem otomatis menghasilkan string *slug* (huruf kecil tanpa spasi/simbol khusus).
* **FR-02 (Ubah Kategori):** Administrator dapat mengubah nama kategori yang sudah ada. Perubahan ini akan langsung berdampak pada pelabelan proyek terkait di halaman publik.
* **FR-03 (Hapus Kategori):** Administrator dapat menghapus kategori. Sistem harus memvalidasi atau mencegah penghapusan jika kategori tersebut masih terikat dengan proyek aktif (atau mengatur relasi `ON DELETE SET NULL` / `RESTRICT`).
* **FR-04 (Dinamisasi Form Proyek):** Pada form Tambah/Edit Proyek, pilihan dropdown kategori proyek kini ditarik secara dinamis dari data tabel `project_categories`, bukan lagi nilai teks statis.

### 3.2 Modul Halaman Publik (`Public Frontend`)
* **FR-05 (Filter Kategori Dinamis):** Tombol filter kategori di bagian proyek (`index.php`) kini dihasilkan secara otomatis dari data di tabel `project_categories`, sehingga setiap kategori baru yang ditambahkan admin akan langsung muncul sebagai tombol filter di halaman depan.

---

## 4. Struktur Direktori Proyek Diperbarui

```text
myfolio/
├── assets/
│   ├── css/
│   └── img/
├── config/
│   └── database.php       # Koneksi PDO & fungsi helper
├── admin/
│   ├── login.php          # Autentikasi Admin
│   ├── dashboard.php      # Ringkasan & Manajemen Proyek / Profil
│   ├── categories.php     # NEW: Halaman CRUD Kategori Proyek
│   └── logout.php
├── index.php              # Landing Page Publik dengan Filter Kategori Dinamis
└── prd.md                 # Dokumen PRD ini
```

---

## 5. Alur Kerja (User Flow)

### 5.1 Alur Admin — Manajemen Kategori Proyek

```text
Admin Login
    └── Dashboard
         └── Sidebar: "Kategori Proyek" → admin/categories.php
              ├── [Tambah Kategori]
              │    └── Isi nama kategori → Submit → Slug auto-generate → Simpan ke DB → Redirect + Flash "Kategori berhasil ditambahkan"
              ├── [Edit Kategori]
              │    └── Klik tombol Edit → Form pre-filled → Ubah nama → Submit → Update DB → Flash "Kategori berhasil diperbarui"
              └── [Hapus Kategori]
                   ├── Jika kategori MASIH digunakan proyek → Flash Error "Tidak dapat dihapus, masih terhubung ke X proyek"
                   └── Jika kategori TIDAK digunakan → Konfirmasi JS confirm() → DELETE → Flash "Kategori dihapus"
```

### 5.2 Alur Admin — Tambah / Edit Proyek (Dengan Kategori Dinamis)

```text
admin/projects.php → Form Tambah/Edit Proyek
    └── Dropdown <select name="category_id">
         └── Data diambil dari: SELECT id, category_name FROM project_categories ORDER BY category_name
              └── Admin pilih kategori → Submit → Simpan category_id (FK) ke tabel projects
```

### 5.3 Alur Pengunjung — Filter Proyek di Halaman Publik

```text
Pengunjung buka index.php
    └── Tombol filter dihasilkan dari: SELECT id, category_name, slug FROM project_categories
         └── Klik tombol filter [slug] → JavaScript filter card proyek berdasarkan data-category="[slug]"
              └── Hanya proyek dengan kategori yang dipilih yang ditampilkan (tanpa reload halaman)
```

---

## 6. Spesifikasi Antarmuka (UI Specification)

### 6.1 Halaman `admin/categories.php`

**Layout:** Mengikuti template admin standar (sidebar kiri + konten kanan) sesuai `admin_header.php`.

**Komponen Halaman:**

| Komponen | Detail |
| :--- | :--- |
| **Judul Halaman** | "Manajemen Kategori Proyek" + badge jumlah kategori |
| **Tombol Aksi** | `+ Tambah Kategori` (pojok kanan atas, warna olive) → memicu modal Bootstrap |
| **Tabel Daftar** | Kolom: No, Nama Kategori, Slug, Jumlah Proyek Terkait, Aksi |
| **Aksi per Baris** | Tombol `Edit` (ikon pensil, biru) + Tombol `Hapus` (ikon trash, merah) |
| **State Kosong** | Pesan "Belum ada kategori. Tambahkan kategori pertama Anda." |

**Modal Tambah / Edit Kategori:**

| Elemen | Spesifikasi |
| :--- | :--- |
| Judul Modal | "Tambah Kategori Baru" / "Edit Kategori" |
| Input Nama | `<input type="text" name="category_name" required maxlength="100">` |
| Input Slug | *Read-only*, di-generate otomatis dari nama saat admin mengetik (via JavaScript) |
| Tombol Simpan | `Simpan Kategori` (olive) |
| Tombol Batal | `Batal` (secondary/abu-abu) |

### 6.2 Pembaruan `admin/projects.php` (Form Kategori)

Dropdown kategori pada form Tambah/Edit Proyek diubah dari `<select>` statis menjadi dinamis:

```php
// SEBELUM (statis - v1.0):
<option value="Web Development">Web Development</option>

// SESUDAH (dinamis - v2.0):
<?php foreach ($categories as $cat): ?>
    <option value="<?= $cat['id'] ?>" <?= $project['category_id'] == $cat['id'] ? 'selected' : '' ?>>
        <?= e($cat['category_name']) ?>
    </option>
<?php endforeach; ?>
```

### 6.3 Pembaruan `index.php` (Tombol Filter Publik)

```php
// SEBELUM (statis - v1.0):
<button data-filter="web-development">Web Development</button>

// SESUDAH (dinamis - v2.0):
<?php foreach ($categories as $cat): ?>
    <button class="filter-btn" data-filter="<?= e($cat['slug']) ?>">
        <?= e($cat['category_name']) ?>
    </button>
<?php endforeach; ?>
```

---

## 7. Script Migrasi & DDL Database (SQL)

Script SQL berikut dijalankan untuk memigrasikan database dari v1.0 ke v2.0:

```sql
-- ============================================================
-- MIGRASI DATABASE: MyFolio v1.0 → v2.0
-- Dijalankan di phpMyAdmin atau via CLI: mysql -u root db_myfolio < migrate_v2.sql
-- ============================================================

-- LANGKAH 1: Buat tabel master kategori
CREATE TABLE IF NOT EXISTS `project_categories` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(100) NOT NULL,
    `slug`          VARCHAR(100) NOT NULL UNIQUE,
    `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LANGKAH 2: Seed data kategori default
INSERT INTO `project_categories` (`category_name`, `slug`, `sort_order`) VALUES
('Web Development',  'web-development',  1),
('Mobile App',       'mobile-app',       2),
('UI/UX Design',     'ui-ux-design',     3),
('Data & AI',        'data-ai',          4),
('Lainnya',          'lainnya',          5);

-- LANGKAH 3: Tambah kolom category_id ke tabel projects
ALTER TABLE `projects`
    ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `title`;

-- LANGKAH 4: Isi category_id berdasarkan nilai ENUM lama
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'web-development')  WHERE `category` = 'Web Development';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'mobile-app')       WHERE `category` = 'Mobile App';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'ui-ux-design')     WHERE `category` = 'UI/UX Design';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'data-ai')          WHERE `category` = 'Data & AI';
UPDATE `projects` SET `category_id` = (SELECT id FROM `project_categories` WHERE slug = 'lainnya')          WHERE `category` IS NULL OR `category` = '';

-- LANGKAH 5: Tambah Foreign Key constraint
ALTER TABLE `projects`
    ADD CONSTRAINT `fk_projects_category`
    FOREIGN KEY (`category_id`) REFERENCES `project_categories`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- LANGKAH 6: Hapus kolom category lama (setelah data termigrasi)
ALTER TABLE `projects` DROP COLUMN `category`;
```

---

## 8. Spesifikasi Non-Fungsional (Non-Functional Requirements)

| ID | Kategori | Ketentuan |
| :--- | :--- | :--- |
| **NFR-01** | Keamanan | Semua input form wajib di-sanitasi menggunakan fungsi `e()` (htmlspecialchars) sebelum ditampilkan ke halaman. |
| **NFR-02** | Keamanan | Semua query database wajib menggunakan PDO *Prepared Statements* untuk mencegah SQL Injection. |
| **NFR-03** | Keamanan | Slug kategori di-*sanitize* server-side: diubah ke huruf kecil, spasi diganti `-`, karakter non-alphanumeric dihapus. |
| **NFR-04** | Performa | Query pengambilan kategori di-*cache* dalam variabel PHP selama satu request (tidak memanggil DB berulang kali per halaman). |
| **NFR-05** | Kompatibilitas | Halaman berjalan baik di PHP 8.0+ dan MariaDB 10.4+ (bundel XAMPP standar). |
| **NFR-06** | UX | Setiap aksi CRUD menampilkan pesan *flash notification* (sukses/error) menggunakan fungsi `set_flash()` dan `get_flash()`. |
| **NFR-07** | Responsivitas | Tabel daftar kategori dapat di-scroll secara horizontal di perangkat mobile (kelas Bootstrap `table-responsive`). |

---

## 9. Kasus Uji (Test Cases)

| ID | Skenario | Langkah | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :--- | :--- |
| **TC-01** | Tambah kategori baru | Buka `admin/categories.php` → klik "+ Tambah" → isi "Machine Learning" → Submit | Kategori tersimpan, slug `machine-learning` ter-generate, muncul di tabel, flash sukses | ✅ |
| **TC-02** | Slug auto-generate | Isi nama "Data & AI (2024)" di form | Slug menjadi `data-ai-2024` (simbol dihapus, spasi jadi `-`) | ✅ |
| **TC-03** | Nama kategori duplikat | Tambah kategori dengan nama yang sudah ada | Sistem menampilkan pesan error "Nama kategori sudah digunakan" | ✅ |
| **TC-04** | Edit kategori | Klik Edit pada "Lainnya" → ubah nama → Simpan | Data terupdate di DB, perubahan langsung tampil di halaman publik | ✅ |
| **TC-05** | Hapus kategori tidak terpakai | Hapus kategori "Lainnya" (0 proyek terkait) | Kategori terhapus dari DB, flash "Kategori berhasil dihapus" | ✅ |
| **TC-06** | Hapus kategori terpakai | Hapus kategori "Web Development" (ada proyek terkait) | Sistem menolak penghapusan, tampilkan flash error beserta jumlah proyek terkait | ✅ |
| **TC-07** | Filter publik dinamis | Admin tambah kategori "Blockchain" → buka `index.php` | Tombol filter "Blockchain" muncul otomatis di halaman publik | ✅ |
| **TC-08** | Dropdown proyek dinamis | Buka form tambah proyek di admin | Dropdown kategori memuat semua kategori dari DB, bukan nilai statis | ✅ |
| **TC-09** | Validasi form kosong | Submit form tambah kategori tanpa nama | Form tidak terkirim, pesan validasi HTML5 muncul di field kosong | ✅ |
| **TC-10** | Hak akses halaman | Akses `admin/categories.php` tanpa login | Redirect otomatis ke `admin/login.php` | ✅ |

---

## 10. Rencana Implementasi (Implementation Plan)

Urutan pengerjaan fitur v2.0 berdasarkan dependensi:

| Tahap | Tugas | File Terdampak | Prioritas |
| :--- | :--- | :--- | :--- |
| **1** | Jalankan script migrasi SQL di phpMyAdmin | `database.sql` (diperbarui) | 🔴 Tinggi |
| **2** | Buat halaman `admin/categories.php` (CRUD Kategori) | `admin/categories.php` *(baru)* | 🔴 Tinggi |
| **3** | Update `admin/projects.php` → dropdown kategori dinamis | `admin/projects.php` | 🔴 Tinggi |
| **4** | Update `index.php` → tombol filter + card proyek dinamis | `index.php` | 🔴 Tinggi |
| **5** | Tambahkan menu "Kategori Proyek" di sidebar admin | `admin/includes/admin_header.php` | 🟡 Sedang |
| **6** | Update fungsi slug di `includes/functions.php` | `includes/functions.php` | 🟡 Sedang |
| **7** | Update `database.sql` master dengan skema v2.0 | `database.sql` | 🟡 Sedang |
| **8** | Testing semua test cases TC-01 s/d TC-10 | — | 🟢 Rendah |
| **9** | Push ke GitHub dengan tag `v2.0` | — | 🟢 Rendah |

---

## 11. Kriteria Penyelesaian (Definition of Done)

Fitur v2.0 dinyatakan **selesai dan siap rilis** apabila seluruh kriteria berikut terpenuhi:

- [x] Script migrasi SQL berhasil dijalankan tanpa error
- [x] Halaman `admin/categories.php` dapat Tambah, Edit, dan Hapus kategori
- [x] Proteksi penghapusan kategori aktif berjalan dengan benar
- [x] Slug kategori ter-generate otomatis dan unik
- [x] Dropdown kategori pada form proyek memuat data dari DB
- [x] Tombol filter di halaman publik bersifat dinamis (dari DB)
- [x] Semua 10 test cases lulus (✅)
- [x] Tidak ada PHP warning/error di semua halaman yang terpengaruh
- [x] Kode di-push ke GitHub branch `main` dengan pesan commit yang deskriptif

---

*Dokumen ini terakhir diperbarui: November 2026 — MyFolio CMS v2.0*