-- Database Schema & Seed Data v2.0 - MyFolio (Digital Business Student)
CREATE DATABASE IF NOT EXISTS `db_myfolio` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_myfolio`;

-- Drop old tables if necessary to align schema
DROP TABLE IF EXISTS `social_links`;
DROP TABLE IF EXISTS `education`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `projects`;
DROP TABLE IF EXISTS `project_categories`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `skill_categories`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `profile`;
DROP TABLE IF EXISTS `admins`;

-- 1. Table admins
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table profile
CREATE TABLE `profile` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `headline` VARCHAR(180) NOT NULL,
  `about_me` TEXT NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `location` VARCHAR(120) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `cv_path` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table project_categories
CREATE TABLE `project_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Table projects
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `slug` VARCHAR(180) UNIQUE NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `role` VARCHAR(120) DEFAULT NULL,
  `process` TEXT DEFAULT NULL,
  `result` TEXT DEFAULT NULL,
  `tools` TEXT DEFAULT NULL,
  `thumbnail` VARCHAR(255) DEFAULT NULL,
  `project_url` VARCHAR(255) DEFAULT NULL,
  `repository_url` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('draft', 'published') DEFAULT 'published',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table skill_categories
CREATE TABLE `skill_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Table skills
CREATE TABLE `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `proficiency` VARCHAR(30) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table experiences
CREATE TABLE `experiences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `organization` VARCHAR(150) NOT NULL,
  `period_start` DATE DEFAULT NULL,
  `period_end` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `description` TEXT NOT NULL,
  `achievements` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table education
CREATE TABLE `education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `institution` VARCHAR(150) NOT NULL,
  `degree` VARCHAR(100) NOT NULL,
  `major` VARCHAR(120) NOT NULL,
  `period_start` DATE DEFAULT NULL,
  `period_end` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table skills
CREATE TABLE `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `proficiency` VARCHAR(30) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table messages
CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_email` VARCHAR(120) NOT NULL,
  `subject` VARCHAR(150) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table social_links
CREATE TABLE `social_links` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `platform` VARCHAR(50) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA

-- Admin (Default: admin / admin123)
INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`)
VALUES (1, 'admin', '$2y$10$uaMypjUm03aN.oZUMu.pVukI/2RKxEyYlTUr96AAKnlEk3UqPN.vK', NOW());

-- Profile: Mahasiswa Bisnis Digital
INSERT INTO `profile` (`id`, `full_name`, `headline`, `about_me`, `email`, `phone`, `location`, `avatar`, `cv_path`)
VALUES (
  1,
  'Nadia Saraswati',
  'Mahasiswa Bisnis Digital | Turning Ideas Into Meaningful Digital Experiences',
  'Saya adalah seorang mahasiswa Bisnis Digital yang memiliki antusiasme tinggi pada pengembangan model bisnis terukur, strategi pemasaran digital, riset pasar berbasis data, dan perancangan pengalaman produk digital (UI/UX). Memiliki pengalaman memimpin tim dalam kompetisi business plan nasional serta merancang strategi go-to-market untuk UMKM lokal.',
  'nadia.saraswati@student.ac.id',
  '+62 812-9876-5432',
  'Jakarta, Indonesia',
  'avatar-default.png',
  '#'
);

-- Project Categories Master Data
INSERT INTO `project_categories` (`id`, `category_name`, `slug`, `sort_order`)
VALUES
(1, 'Web Development', 'web-development', 1),
(2, 'Mobile App', 'mobile-app', 2),
(3, 'UI/UX Design', 'ui-ux-design', 3),
(4, 'Data & AI', 'data-ai', 4),
(5, 'Lainnya', 'lainnya', 5),
(6, 'Business Planning', 'business-planning', 6),
(7, 'Digital Marketing', 'digital-marketing', 7),
(8, 'Research & Community Projects', 'research-community-projects', 8),
(9, 'Website & Application', 'website-application', 9);

-- Projects
INSERT INTO `projects` (`title`, `category_id`, `slug`, `category`, `short_description`, `description`, `role`, `process`, `result`, `tools`, `thumbnail`, `project_url`, `repository_url`, `is_featured`, `status`, `sort_order`)
VALUES
(
  'EcoBite - Smart Food Waste Marketplace Business Plan',
  6,
  'ecobite-smart-food-waste-marketplace',
  'Business Planning',
  'Rencana bisnis platform marketplace B2B dan B2C untuk mengoptimalkan surplus makanan dari hotel dan resto dengan model penetapan harga dinamis.',
  'EcoBite adalah inisiatif bisnis berkelanjutan yang menghubungkan industri F&B yang memiliki stok makanan berlebih layak konsumsi dengan konsumen dan pelaku UMKM olahan pangan secara diskon.',
  'Lead Business Strategist & Financial Modeler',
  'Melakukan riset primer terhadap 30 restoran, validasi problem-solution fit, penyusunan Financial Projection 3 tahun, serta pembuatan pitch deck kompetisi.',
  'Meraih Juara 2 pada National Business Plan Competition 2024 dan validasi LOI dari 3 mitra kuliner lokal.',
  'Notion, Microsoft Excel, Canva, Google Forms',
  'project-1.jpg',
  'https://pitch.example.com/ecobite',
  NULL,
  1,
  'published',
  1
),
(
  'Campaign Go-Digital: Optimalisasi Omzet UMKM Kopi Kenangan Nusantara',
  7,
  'campaign-go-digital-umkm-kopi',
  'Digital Marketing',
  'Strategi pemasaran digital terintegrasi melalui Meta Ads, optimasi TikTok Shop, dan kolaborasi micro-influencer lokal.',
  'Kampanye pemasaran digital selama 3 bulan untuk membantu brand kopi lokal meningkatkan brand recall dan konversi penjualan langsung di channel digital.',
  'Digital Campaign Strategist',
  'Audit channel eksisting, riset persona pembeli, produksi konten edukasi storytelling, A/B testing ad copy di Meta Ads, dan evaluasi ROAS.',
  'Peningkatan engagement rate Instagram sebesar 185% dan kenaikan total omzet bulanan sebesar 42% selama periode kampanye.',
  'Meta Ads Manager, TikTok Creative Center, CapCut, Google Analytics',
  'project-2.jpg',
  'https://instagram.com/kopikenangannusantara',
  NULL,
  1,
  'published',
  2
),
(
  'AgriHub - Redesain UI/UX Platform Lelang Hasil Tani Digital',
  3,
  'agrihub-redesain-ui-ux-platform-lelang-tani',
  'UI/UX Design',
  'Perancangan antarmuka aplikasi mobile yang intuitif bagi kelompok tani untuk mengikuti lelang hasil panen langsung ke distributor kota.',
  'Proyek perancangan produk digital yang berfokus pada kemudahan aksesibilitas bagi petani dengan keterbatasan literasi teknologi tinggi.',
  'UI/UX Researcher & Product Designer',
  'User interview 15 petani, pembuatan User Persona, Customer Journey Map, Wireframing Lo-Fi hingga Hi-Fi Prototype interaktif dan Usability Testing SUS Score.',
  'Skor SUS (System Usability Scale) meningkat dari 54 (kategori kurang) menjadi 82 (kategori Excellent) pada fase pengujian akhir.',
  'Figma, FigJam, Maze, Whimsical',
  'project-3.jpg',
  'https://figma.com/proto/agrihub-preview',
  NULL,
  1,
  'published',
  3
),
(
  'Riset Valuasi Pasar & Perilaku Konsumen Gen-Z pada Quick Commerce',
  8,
  'riset-pasar-quick-commerce-gen-z',
  'Research & Community Projects',
  'Laporan riset pasar komprehensif mengenai frekuensi pembelian impulsif dan loyalitas pelanggan aplikasi Quick Commerce di area Jabodetabek.',
  'Penelitian akademik kuantitatif dan kualitatif untuk memetakan faktor penentu kepuasan pelanggan terhadap kecepatan pengiriman barang kebutuhan harian.',
  'Lead Academic Researcher',
  'Penyebaran kuesioner kepada 320 responden Gen-Z, pengolahan data regresi linier berganda menggunakan SPSS, dan penyusunan policy recommendation.',
  'Dipresentasikan pada Seminar Nasional Manajemen & Bisnis Digital 2024 dan diterbitkan dalam prosiding universitas.',
  'SPSS, Google Sheets, Mendeley, Tableau',
  'project-4.jpg',
  'https://researchgate.net/publication/quick-commerce-genz',
  NULL,
  0,
  'published',
  4
),
(
  'KaryaKita - Portal Portofolio Kreatif Mahasiswa Vokasi',
  9,
  'karyakita-portal-portofolio-mahasiswa',
  'Website & Application',
  'Prototipe web showcase karya tugas akhir mahasiswa desain dan multimedia yang terhubung langsung dengan talent recruiter industri kreatif.',
  'Pengembangan platform web interaktif untuk menjembatani mahasiswa tingkat akhir dengan industri agensi periklanan dan software house.',
  'Product Manager & Frontend Collaborator',
  'Product backlog prioritization, perancangan skema relasional, dan koordinasi sprint tim developer menggunakan metodologi Scrum.',
  'Berhasil mengumpulkan 120 karya proyek mahasiswa pada pameran expo tahunan kampus.',
  'Trello, PHP Native, Bootstrap 5, MySQL',
  'project-5.jpg',
  'https://karyakita.example.com',
  'https://github.com/example/karyakita',
  0,
  'published',
  5
);

-- Experiences
INSERT INTO `experiences` (`type`, `title`, `organization`, `period_start`, `period_end`, `is_current`, `description`, `achievements`, `sort_order`)
VALUES
(
  'Work',
  'Digital Marketing & Growth Intern',
  'PT Inovasi Niaga Digital',
  '2024-01-15',
  NULL,
  1,
  'Bertanggung jawab merancang kalender konten media sosial, menganalisis performa kata kunci SEO on-page, dan mengeksekusi kampanye newsletter mingguan.',
  'Meningkatkan open rate email marketing dari 18% ke 29.4% serta mengelola budget iklan digital bulanan dengan ROAS rata-rata 3.8x.',
  1
),
(
  'Organization',
  'Ketua Departemen Kewirausahaan & Kemitraan',
  'BEM Fakultas Ekonomi & Bisnis',
  '2023-02-01',
  '2024-01-01',
  0,
  'Memimpin divisi berisi 14 anggota dalam program inkubasi bisnis rintisan mahasiswa dan menjalin kerjasama sponsorship dengan 8 perusahaan swasta.',
  'Menyelenggarakan Business Pitch Day kampus dengan total pendanaan bibit senilai Rp 50.000.000 untuk 5 startup mahasiswa terpilih.',
  2
),
(
  'Committee',
  'Project Officer - Digital Business Summit 2023',
  'Himpunan Mahasiswa Bisnis Digital',
  '2023-06-01',
  '2023-11-30',
  0,
  'Mengkoordinasikan keseluruhan operasional konferensi nasional yang dihadiri oleh lebih dari 500 peserta umum dan 12 pembicara industri teknologi.',
  'Mencapai 100% target penjualan tiket dan meraih kepuasan peserta (CSAT) 4.8/5.0.',
  3
),
(
  'Competition',
  'Juara 2 - National Business Plan Competition 2024',
  'Universitas Brawijaya Entrepreneurship Week',
  '2024-05-01',
  '2024-05-20',
  0,
  'Menyusun proposal inovasi bisnis sirkular ekonomi EcoBite bersaing dengan 150+ tim dari perguruan tinggi se-Indonesia.',
  'Meraih penghargaan Best Pitching Performance dari dewan juri modal ventura.',
  4
),
(
  'Volunteer',
  'Mentor Relawan Literasi Digital UMKM Desa',
  'Gerakan Digitalisasi UMKM Nusantara',
  '2023-08-01',
  '2023-09-30',
  0,
  'Memberikan pelatihan praktis foto produk katalog, pencatatan keuangan digital berbasis aplikasi, dan onboarding marketplace kepada 25 pengrajin lokal.',
  '18 dari 25 UMKM berhasil memiliki toko online aktif dan menerima pesanan digital pertama mereka.',
  5
);

-- Education
INSERT INTO `education` (`institution`, `degree`, `major`, `period_start`, `period_end`, `is_current`, `description`, `sort_order`)
VALUES
(
  'Universitas Pembangunan Nasional Veteran',
  'Sarjana Bisnis Digital (S.Bns)',
  'Bisnis Digital & Teknologi Informasi',
  '2022-08-01',
  NULL,
  1,
  'IPK Sementara: 3.86/4.00. Fokus konsentrasi pada Digital Venture Creation, E-Commerce Strategy, dan Data Analytics for Business. Aktif dalam riset pasar dan kompetisi kewirausahaan.',
  1
),
(
  'SMA Negeri 1 Jakarta',
  'Sekolah Menengah Atas',
  'Matematika & Ilmu Pengetahuan Alam (MIPA)',
  '2019-07-01',
  '2022-06-01',
  0,
  'Lulus dengan predikat nilai akademik sangat memuaskan. Menjabat sebagai Koordinator Mading Sekolah dan Juara Lomba Debat Ekonomi Pelajar.',
  2
);

-- Skill Categories Master Data
INSERT INTO `skill_categories` (`id`, `category_name`, `sort_order`)
VALUES
(1, 'Digital Tools', 1),
(2, 'Business & Entrepreneurship', 2),
(3, 'Administration & Data', 3),
(4, 'Communication', 4),
(5, 'Personal / Soft Skills', 5);

-- Skills (Menggunakan label proficiency terukur: Beginner, Intermediate, Advanced)
INSERT INTO `skills` (`skill_name`, `category`, `proficiency`, `description`, `sort_order`)
VALUES
('Google Analytics 4 & Data Studio', 'Digital Tools', 'Advanced', 'Pelacakan konversi e-commerce, user journey mapping, dan pembuatan custom dashboard.', 1),
('Meta Ads & TikTok Ads Manager', 'Digital Tools', 'Advanced', 'Perencanaan funnel kampanye iklan berbayar, audience targeting, dan A/B testing materi kreatif.', 2),
('Figma & FigJam (UI/UX)', 'Digital Tools', 'Intermediate', 'Wireframing, interactive prototyping, dan usability testing produk digital.', 3),
('Business Model Canvas & Lean Canvas', 'Business & Entrepreneurship', 'Advanced', 'Pemetaan proposisi nilai, analisis struktur biaya, aliran pendapatan, dan strategi kemitraan.', 4),
('Financial Modeling & Unit Economics', 'Business & Entrepreneurship', 'Intermediate', 'Proyeksi arus kas, perhitungan CAC & LTV, BEP analysis, dan sensitivitas bisnis.', 5),
('Riset Pasar & Competitor Benchmarking', 'Administration & Data', 'Advanced', 'Riset primer kuantitatif, analisis SWOT komparatif, dan tren pertumbuhan industri.', 6),
('Microsoft Excel & Google Sheets', 'Administration & Data', 'Advanced', 'VLOOKUP/XLOOKUP, Pivot Table, scenario manager, dan visualisasi data bisnis.', 7),
('Public Speaking & Pitching', 'Communication', 'Advanced', 'Penyampaian presentasi ide bisnis yang persuasif di hadapan investor dan audiens luas.', 8),
('Copywriting & Content Strategy', 'Communication', 'Intermediate', 'Penulisan naskah iklan, optimasi headline, dan storytelling brand emosional.', 9),
('Agile Framework & Scrum Teamwork', 'Personal / Soft Skills', 'Intermediate', 'Kolaborasi sprint, task tracking berbasis Trello/Jira, dan fleksibilitas adaptasi ide.', 10);

-- Messages
INSERT INTO `messages` (`sender_name`, `sender_email`, `subject`, `message`, `is_read`, `sent_at`)
VALUES
(
  'Rizky Pratama',
  'rizky@fintechstar.id',
  'Peluang Magang Product & Business Strategy',
  'Halo Nadia, kami melihat portofolio business plan EcoBite Anda dan sangat terkesan dengan ketajaman unit economics yang Anda rancang. Kami ingin mengajak Anda berbincang mengenai peluang internship posisi Business Analyst di tim kami.',
  0,
  NOW()
),
(
  'Dewi Anggraini',
  'dewi.anggraini@umkm-kreatif.com',
  'Konsultasi Go-Digital Produk Olahan Pangan',
  'Selamat siang Nadia, kami pengelola komunitas UMKM kuliner di Bandung ingin mengundang Anda sebagai pemateri sesi sharing session Digital Marketing untuk UMKM.',
  1,
  DATE_SUB(NOW(), INTERVAL 1 DAY)
);

-- Social Links
INSERT INTO `social_links` (`platform`, `url`, `is_active`, `sort_order`)
VALUES
('LinkedIn', 'https://linkedin.com/in/nadia-saraswati', 1, 1),
('Instagram', 'https://instagram.com/nadiasaraswati', 1, 2),
('GitHub', 'https://github.com/nadiasaraswati', 1, 3);
