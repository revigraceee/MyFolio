-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_myfolio
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `db_myfolio`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_myfolio` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `db_myfolio`;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$uaMypjUm03aN.oZUMu.pVukI/2RKxEyYlTUr96AAKnlEk3UqPN.vK','2026-11-05 04:49:17','2026-11-05 04:49:17');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `education`
--

DROP TABLE IF EXISTS `education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `education` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institution` varchar(150) NOT NULL,
  `degree` varchar(100) NOT NULL,
  `major` varchar(120) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `education`
--

LOCK TABLES `education` WRITE;
/*!40000 ALTER TABLE `education` DISABLE KEYS */;
INSERT INTO `education` VALUES (1,'Universitas Anwar Medika','Sarjana Bisnis Digital (S.Bns)','Bisnis Digital','2025-09-22',NULL,1,'bismillah sukses kaya raya',1,'2026-11-05 04:49:17','2026-11-20 08:21:58'),(2,'SMK Yapalis Krian','Sekolah Menengah Kejuruan','Multimedia','2019-07-01','2022-06-01',0,'Suksees',2,'2026-11-05 04:49:17','2026-11-20 08:22:45');
/*!40000 ALTER TABLE `education` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `experience_types`
--

DROP TABLE IF EXISTS `experience_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experience_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `experience_types`
--

LOCK TABLES `experience_types` WRITE;
/*!40000 ALTER TABLE `experience_types` DISABLE KEYS */;
INSERT INTO `experience_types` VALUES (1,'Work',1,'2026-11-21 04:20:35'),(2,'Organization',2,'2026-11-21 04:20:35'),(3,'Committee',3,'2026-11-21 04:20:35'),(4,'Competition',4,'2026-11-21 04:20:35'),(5,'Volunteer',5,'2026-11-21 04:20:35'),(6,'Internship',6,'2026-11-21 04:32:56');
/*!40000 ALTER TABLE `experience_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `experiences`
--

DROP TABLE IF EXISTS `experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `organization` varchar(150) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text NOT NULL,
  `achievements` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `experiences`
--

LOCK TABLES `experiences` WRITE;
/*!40000 ALTER TABLE `experiences` DISABLE KEYS */;
INSERT INTO `experiences` VALUES (1,'Work','Admin Color Mixing','Auto 2000 Body dan Cat Kenjeran','2022-09-15','2024-12-31',0,'Bertanggung jawab mengolah data masuk, Input data, Follow up data, Stok Opname dan Stock Keeper','',1,'2026-11-05 04:49:17','2026-11-21 04:14:54'),(2,'Organization','Wakil Ketua 2','HIMA Fakultas Teknologi dan Bisnis','2025-10-01',NULL,1,'Memipin Hima dan membantu Ketua dan Wakil ketua 1, membuat projek Hima, berkolaborasi dengan Ormawa lain','1. Seminar kolaborasi\r\n2. Webinar kolaborasi\r\n3. PKKMB Fakultas\r\n4. Sister School\r\n5. Tech to School',2,'2026-11-05 04:49:17','2026-11-21 05:13:56'),(3,'Internship','Social media & Content Intern','Digital Marketing Universitas Anwar Medika','2026-09-21',NULL,1,'Mengelola sosial media, Live Streaming, Content Creator','',3,'2026-11-05 04:49:17','2026-11-21 04:37:04'),(4,'Competition','Presenter','Institut Asia Malang','2026-11-18','2026-11-19',0,'Mengikuti lomba conference internasional membuat Jurnal Artikel dengan tema \" Digital Branding Based on Local Wisdom to Enhance the Competitivness of UMKM Product \" Penelitian ini mengkaji pengaruh strategi digital branding berbasis kearifan lokal dan kapabilitas pemasaran digital terhadap daya saing UMKM.','Nice Try',4,'2026-11-05 04:49:17','2026-11-21 05:12:19'),(5,'Work','Media','KPPTI','2026-11-11','2026-11-13',0,'Ikut serta dalam kegiatan KPPTI yang berlokasi di Unair sebagai media yang meliput selama kegiatan berlangsung dan mengupload di sosial media, berita internal atau eksternal.','',5,'2026-11-05 04:49:17','2026-11-21 05:11:38');
/*!40000 ALTER TABLE `experiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_name` varchar(100) NOT NULL,
  `sender_email` varchar(120) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,'Rizky Pratama','rizky@fintechstar.id','Peluang Magang Product & Business Strategy','Halo Nadia, kami melihat portofolio business plan EcoBite Anda dan sangat terkesan dengan ketajaman unit economics yang Anda rancang. Kami ingin mengajak Anda berbincang mengenai peluang internship posisi Business Analyst di tim kami.',1,'2026-11-05 04:49:17'),(2,'Dewi Anggraini','dewi.anggraini@umkm-kreatif.com','Konsultasi Go-Digital Produk Olahan Pangan','Selamat siang Nadia, kami pengelola komunitas UMKM kuliner di Bandung ingin mengundang Anda sebagai pemateri sesi sharing session Digital Marketing untuk UMKM.',1,'2026-11-04 04:49:17');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `headline` varchar(180) NOT NULL,
  `about_me` text NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
INSERT INTO `profile` VALUES (1,'Revigrace Agnes Andyka Firenza','Mahasiswa Bisnis Digital | Turning Ideas Into Meaningful Digital Experiences','Saya adalah seorang mahasiswa Bisnis Digital yang memiliki antusiasme tinggi pada pengembangan model bisnis terukur, strategi pemasaran digital, riset pasar berbasis data, dan perancangan pengalaman produk digital (UI/UX). Memiliki pengalaman memimpin tim dalam kompetisi business plan nasional serta merancang strategi go-to-market untuk UMKM lokal.','revigrace.works@gmail.com','0882-3147-7818','Sidoarjo, Indonesia','1793855095_1ab4aff2.jpeg','#','2026-11-05 05:05:39');
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_categories`
--

DROP TABLE IF EXISTS `project_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_categories`
--

LOCK TABLES `project_categories` WRITE;
/*!40000 ALTER TABLE `project_categories` DISABLE KEYS */;
INSERT INTO `project_categories` VALUES (1,'Web Development','web-development',1,'2026-11-19 08:58:33'),(2,'Mobile App','mobile-app',2,'2026-11-19 08:58:33'),(3,'UI/UX Design','ui-ux-design',3,'2026-11-19 08:58:33'),(4,'Data & AI','data-ai',4,'2026-11-19 08:58:33'),(5,'Lainnya','lainnya',5,'2026-11-19 08:58:33'),(6,'Business Planning','business-planning',10,'2026-11-19 08:58:33'),(7,'Digital Marketing','digital-marketing',11,'2026-11-19 08:58:33'),(9,'Research & Community Projects','research-community-projects',13,'2026-11-19 08:58:33'),(10,'Website & Application','website-application',14,'2026-11-19 08:58:33');
/*!40000 ALTER TABLE `project_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_images`
--

DROP TABLE IF EXISTS `project_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  CONSTRAINT `fk_project_images_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_images`
--

LOCK TABLES `project_images` WRITE;
/*!40000 ALTER TABLE `project_images` DISABLE KEYS */;
INSERT INTO `project_images` VALUES (1,2,'1795229629_2657a7bc.jpg',1,'2026-11-21 02:35:31'),(3,4,'1795233025_63cd238b.jpg',1,'2026-11-21 03:50:25'),(4,4,'1795233025_77a21d2e.jpg',2,'2026-11-21 03:50:25'),(5,4,'1795233025_b89e60f0.jpg',3,'2026-11-21 03:50:25');
/*!40000 ALTER TABLE `project_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `slug` varchar(180) NOT NULL,
  `category` varchar(80) NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `role` varchar(120) DEFAULT NULL,
  `process` text DEFAULT NULL,
  `result` text DEFAULT NULL,
  `tools` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `repository_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'published',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_projects_category` (`category_id`),
  CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Back to School',7,'back-to-school','Digital Marketing','Flayer Back to School untuk sosial media Universitas Anwar Medika','','Magang','','','Photoshop','1795233038_6d97baac.jpg','https://pitch.example.com/ecobite','',0,'published',1,'2026-11-05 04:49:17','2026-11-21 03:50:38'),(2,'Foto kita blurr',7,'foto-kita-blurr','Digital Marketing','Branding sosial media UAM CAMPUS','','Magang','','','Photoshop','1795229629_be401e2d.jpg','https://instagram.com/kopikenangannusantara','',0,'published',2,'2026-11-05 04:49:17','2026-11-21 02:53:49'),(4,'User Interface bertema \"Gejalaku\"',3,'user-interface-bertema-gejalaku','UI/UX Design','\"Gejalaku\" adalah mobile App yang berfungsi sebagai tempat para pengguna menyimpan riwayat sakit guna melihat penyakit yang dirasakan.','','Mahasiswa','','','Figma','1795233025_53e9e496.jpg','https://www.figma.com/design/TnfCjbJacaEYiqzDsi8bog/PROJEK-UI-UX-GEJALAKU?node-id=0-1&t=8g7hFWsRU3aG6MkJ-1','',0,'published',4,'2026-11-05 04:49:17','2026-11-21 03:50:25');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skill_categories`
--

DROP TABLE IF EXISTS `skill_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skill_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skill_categories`
--

LOCK TABLES `skill_categories` WRITE;
/*!40000 ALTER TABLE `skill_categories` DISABLE KEYS */;
INSERT INTO `skill_categories` VALUES (1,'Administration & Data',1,'2026-11-19 09:29:51'),(2,'Business & Entrepreneurship',2,'2026-11-19 09:29:51'),(3,'Communication',3,'2026-11-19 09:29:51'),(4,'Digital Tools',4,'2026-11-19 09:29:51'),(5,'Personal / Soft Skills',5,'2026-11-19 09:29:51'),(6,'UI/UX',6,'2026-11-21 03:55:43'),(7,'Digital Marketing',7,'2026-11-21 03:56:09');
/*!40000 ALTER TABLE `skill_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(100) NOT NULL,
  `category` varchar(80) NOT NULL,
  `proficiency` varchar(30) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'Google Analytics 4 & Data Studio','Digital Tools','Advanced','Pelacakan konversi e-commerce, user journey mapping, dan pembuatan custom dashboard.',1,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(2,'Meta Ads & TikTok Ads Manager','Digital Tools','Advanced','Perencanaan funnel kampanye iklan berbayar, audience targeting, dan A/B testing materi kreatif.',2,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(3,'Figma & FigJam (UI/UX)','Digital Tools','Intermediate','Wireframing, interactive prototyping, dan usability testing produk digital.',3,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(4,'Business Model Canvas & Lean Canvas','Business & Entrepreneurship','Advanced','Pemetaan proposisi nilai, analisis struktur biaya, aliran pendapatan, dan strategi kemitraan.',4,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(5,'Financial Modeling & Unit Economics','Business & Entrepreneurship','Intermediate','Proyeksi arus kas, perhitungan CAC & LTV, BEP analysis, dan sensitivitas bisnis.',5,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(7,'Microsoft Excel & Google Sheets','Administration & Data','Advanced','VLOOKUP/XLOOKUP, Pivot Table,dan visualisasi data bisnis.',7,'2026-11-05 04:49:17','2026-11-21 03:56:42'),(8,'Public Speaking','Communication','Advanced','Penyampaian presentasi ide bisnis  dan promosi yang persuasif di hadapan audiens luas.',8,'2026-11-05 04:49:17','2026-11-21 03:57:30'),(9,'Copywriting & Content Strategy','Communication','Intermediate','Penulisan naskah iklan, optimasi headline, dan storytelling brand emosional.',9,'2026-11-05 04:49:17','2026-11-05 04:49:17'),(12,'Canva','Digital Tools','Intermediate','Membuat desain Flayer, Undangan, Banner, Logo, Poster Dst',9,'2026-11-21 03:59:00','2026-11-21 03:59:00');
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
INSERT INTO `social_links` VALUES (1,'Instagram','https://www.instagram.com/rvgracee?dmpr=dWt2aGtqbWl2ZXhz',1,1,'2026-11-05 04:49:17','2026-11-20 08:25:03');
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25 15:07:40
