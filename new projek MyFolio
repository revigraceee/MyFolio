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