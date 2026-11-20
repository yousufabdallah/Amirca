/*M!999999\- enable the sandbox mode */ 

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
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `subject_type` varchar(100) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_no` varchar(20) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `phone_alt` varchar(30) DEFAULT NULL,
  `civil_id` text DEFAULT NULL,
  `civil_id_hash` varchar(64) DEFAULT NULL,
  `civil_id_last4` varchar(4) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `governorate` varchar(100) DEFAULT NULL,
  `wilayat` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_client_no_unique` (`client_no`),
  UNIQUE KEY `clients_user_id_unique` (`user_id`),
  UNIQUE KEY `clients_civil_id_hash_unique` (`civil_id_hash`),
  KEY `clients_created_by_foreign` (`created_by`),
  KEY `clients_full_name_index` (`full_name`),
  KEY `clients_phone_index` (`phone`),
  KEY `clients_archived_at_index` (`archived_at`),
  CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,3) NOT NULL,
  `expense_date` date NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_vehicle_id_foreign` (`vehicle_id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_category_index` (`category`),
  KEY `expenses_expense_date_index` (`expense_date`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inquiries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_handled` tinyint(1) NOT NULL DEFAULT 0,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `inquiries` WRITE;
/*!40000 ALTER TABLE `inquiries` DISABLE KEYS */;
/*!40000 ALTER TABLE `inquiries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `internal_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internal_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `notable_type` varchar(255) NOT NULL,
  `notable_id` bigint(20) unsigned NOT NULL,
  `body` text NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internal_notes_notable_type_notable_id_index` (`notable_type`,`notable_id`),
  KEY `internal_notes_user_id_foreign` (`user_id`),
  CONSTRAINT `internal_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `internal_notes` WRITE;
/*!40000 ALTER TABLE `internal_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `internal_notes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2026_01_01_000000_create_framework_tables',1),
(2,'2026_01_01_000001_create_business_tables',1),
(3,'2026_11_20_000000_office_stock_and_showroom',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'bi-bell',
  `dedupe_key` varchar(150) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `notifications_user_id_dedupe_key_index` (`user_id`,`dedupe_key`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `receipt_no` varchar(30) NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(14,3) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'OMR',
  `paid_at` date NOT NULL,
  `method` enum('cash','bank_transfer','card','other') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','cancelled') NOT NULL DEFAULT 'active',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_receipt_no_unique` (`receipt_no`),
  KEY `payments_client_id_foreign` (`client_id`),
  KEY `payments_vehicle_id_foreign` (`vehicle_id`),
  KEY `payments_cancelled_by_foreign` (`cancelled_by`),
  KEY `payments_created_by_foreign` (`created_by`),
  KEY `payments_paid_at_index` (`paid_at`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
('about_text','العميد للاستيراد والتصدير مكتب عُماني متخصص في استيراد السيارات من الولايات المتحدة الأمريكية.\nنرافقك في كل خطوة: من اختيار السيارة المناسبة وشرائها من المزادات الموثوقة، مرورًا بالنقل والشحن البحري، وصولًا إلى التخليص الجمركي وتسليمها لك جاهزة.','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('about_title','من نحن','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('address','سلطنة عُمان - مسقط','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('contact_text','لديك استفسار أو ترغب في استيراد سيارة؟ راسلنا وسنتواصل معك في أقرب وقت.','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('currency','OMR','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('date_format','d/m/Y','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('documents_default_visible','1','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('email','info@alameed.om','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('footer_text','شريكك الموثوق في استيراد السيارات من الولايات المتحدة الأمريكية إلى سلطنة عُمان.','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('hero_badge','خبرة موثوقة في استيراد السيارات','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('hero_subtitle','نشتري لك من أكبر المزادات الأمريكية، ونتولى الشحن والتخليص الجمركي، وتتابع كل مرحلة لحظة بلحظة من حسابك الخاص.','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('hero_title','سيارتك من أمريكا إلى باب بيتك في عُمان','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('home_services','شراء السيارات من المزادات | نشتري لك من Copart وIAAI وManheim وغيرها بعد فحص التقارير والصور | bi-hammer\nالنقل الداخلي في أمريكا | ننقل السيارة من ساحة المزاد إلى ميناء الشحن بأمان | bi-truck\nالشحن البحري إلى عُمان | شحن بالحاويات أو RoRo مع شركات شحن معتمدة | bi-water\nالتخليص الجمركي | ننهي جميع الإجراءات الجمركية والأوراق الرسمية نيابة عنك | bi-file-earmark-check\nمتابعة إلكترونية | حساب خاص تتابع فيه سيارتك ودفعاتك وصورها في أي وقت | bi-phone\nاستشارة قبل الشراء | نساعدك في اختيار السيارة المناسبة لميزانيتك | bi-chat-dots','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('home_steps','اطلب سيارتك | أخبرنا بالسيارة التي تريدها وميزانيتك\nالبحث والشراء | نبحث في المزادات ونشتري السيارة بعد موافقتك\nالنقل والشحن | ننقل السيارة إلى الميناء ونشحنها إلى عُمان\nالتخليص والتسليم | نخلّص الإجراءات الجمركية ونسلمك السيارة','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('home_why','شفافية كاملة | تشاهد كل التكاليف والدفعات والمتبقي في حسابك | bi-eye\nمتابعة لحظية | تعرف أين وصلت سيارتك وموعد وصولها المتوقع | bi-broadcast\nخبرة وثقة | تعامل مع مزادات وشركات شحن موثوقة | bi-shield-check\nأسعار منافسة | تكاليف واضحة بدون رسوم مخفية | bi-tags','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('office_name','العميد للاستيراد والتصدير','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('office_tagline','استيراد السيارات من الولايات المتحدة إلى سلطنة عُمان','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('phone','+968 9000 0000','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('receipt_footer','شكرًا لتعاملكم مع العميد للاستيراد والتصدير','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('receipt_prefix','','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('receipt_start','1001','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('receipt_terms','هذا الإيصال صادر إلكترونيًا ولا يحتاج إلى توقيع.','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_1_label','سيارة تم استيرادها','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_1_value','+500','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_2_label','سنوات خبرة','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_2_value','+10','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_3_label','متابعة إلكترونية','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('stat_3_value','100%','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('whatsapp','96890000000','2026-11-20 20:01:30','2026-11-20 20:01:30'),
('working_hours','السبت - الخميس: 9 صباحًا - 9 مساءً','2026-11-20 20:01:30','2026-11-20 20:01:30');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','client') NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'مدير النظام','admin',NULL,'admin@your-domain.com','$2y$12$kGHXFLHCcTgsk/tMVRk/p.Pb4.h4GVq9I5YZdJnKiytPvzEB3jN0C','admin',NULL,1,NULL,NULL,NULL,'2026-11-20 20:01:30','2026-11-20 20:01:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_cost_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_cost_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,3) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_cost_items_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `vehicle_cost_items_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_cost_items` WRITE;
/*!40000 ALTER TABLE `vehicle_cost_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_cost_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `vehicle_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('purchase_invoice','shipping','customs','client','other') NOT NULL DEFAULT 'other',
  `title` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT 0,
  `visible_to_client` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_documents_vehicle_id_foreign` (`vehicle_id`),
  KEY `vehicle_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `vehicle_documents_client_id_foreign` (`client_id`),
  CONSTRAINT `vehicle_documents_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicle_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_documents_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_documents` WRITE;
/*!40000 ALTER TABLE `vehicle_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_images_uploaded_by_foreign` (`uploaded_by`),
  KEY `vehicle_images_vehicle_id_sort_order_index` (`vehicle_id`,`sort_order`),
  CONSTRAINT `vehicle_images_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_images_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_images` WRITE;
/*!40000 ALTER TABLE `vehicle_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `type` enum('status','note','eta','arrival','created') NOT NULL DEFAULT 'status',
  `from_status_id` bigint(20) unsigned DEFAULT NULL,
  `to_status_id` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `visible_to_client` tinyint(1) NOT NULL DEFAULT 1,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `vehicle_status_history_vehicle_id_foreign` (`vehicle_id`),
  KEY `vehicle_status_history_from_status_id_foreign` (`from_status_id`),
  KEY `vehicle_status_history_to_status_id_foreign` (`to_status_id`),
  KEY `vehicle_status_history_user_id_foreign` (`user_id`),
  KEY `vehicle_status_history_created_at_index` (`created_at`),
  CONSTRAINT `vehicle_status_history_from_status_id_foreign` FOREIGN KEY (`from_status_id`) REFERENCES `vehicle_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_status_history_to_status_id_foreign` FOREIGN KEY (`to_status_id`) REFERENCES `vehicle_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_status_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicle_status_history_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_status_history` WRITE;
/*!40000 ALTER TABLE `vehicle_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_status_history` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicle_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#1f3a68',
  `icon` varchar(50) NOT NULL DEFAULT 'bi-circle',
  `stage_group` enum('pre_purchase','purchased','shipping','arrived','delivered') NOT NULL DEFAULT 'purchased',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_statuses_sort_order_index` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicle_statuses` WRITE;
/*!40000 ALTER TABLE `vehicle_statuses` DISABLE KEYS */;
INSERT INTO `vehicle_statuses` VALUES
(1,'تم استلام الطلب','#64748b','bi-inbox','pre_purchase',1,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(2,'البحث عن السيارة','#6366f1','bi-search','pre_purchase',2,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(3,'تم شراء السيارة','#0ea5e9','bi-bag-check','purchased',3,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(4,'في ساحة المزاد','#0891b2','bi-building','purchased',4,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(5,'في النقل الداخلي بأمريكا','#0d9488','bi-truck','purchased',5,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(6,'تم الشحن من أمريكا','#2563eb','bi-box-seam','shipping',6,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(7,'في الطريق إلى سلطنة عُمان','#1d4ed8','bi-water','shipping',7,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(8,'وصلت إلى ميناء عُمان','#c8a24a','bi-geo-alt-fill','arrived',8,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(9,'إجراءات التخليص الجمركي','#d97706','bi-file-earmark-check','arrived',9,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(10,'جاهزة للتسليم','#16a34a','bi-key','arrived',10,1,'2026-11-20 20:01:30','2026-11-20 20:01:30'),
(11,'تم التسليم','#15803d','bi-patch-check-fill','delivered',11,1,'2026-11-20 20:01:30','2026-11-20 20:01:30');
/*!40000 ALTER TABLE `vehicle_statuses` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `file_no` varchar(20) DEFAULT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `status_id` bigint(20) unsigned DEFAULT NULL,
  `make` varchar(80) NOT NULL,
  `model` varchar(80) NOT NULL,
  `trim` varchar(80) DEFAULT NULL,
  `year` smallint(5) unsigned DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `vin` varchar(30) DEFAULT NULL,
  `fuel_type` varchar(30) DEFAULT NULL,
  `engine_size` varchar(30) DEFAULT NULL,
  `transmission` varchar(30) DEFAULT NULL,
  `mileage` int(10) unsigned DEFAULT NULL,
  `mileage_unit` varchar(5) NOT NULL DEFAULT 'mi',
  `condition` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `purchase_location` varchar(255) DEFAULT NULL,
  `auction_name` varchar(100) DEFAULT NULL,
  `auction_url` varchar(500) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(14,3) NOT NULL DEFAULT 0.000,
  `auction_fees` decimal(14,3) NOT NULL DEFAULT 0.000,
  `inland_transport` decimal(14,3) NOT NULL DEFAULT 0.000,
  `cost_currency` varchar(3) NOT NULL DEFAULT 'OMR',
  `purchase_notes` text DEFAULT NULL,
  `departure_port` varchar(100) DEFAULT NULL,
  `arrival_port` varchar(100) DEFAULT NULL,
  `shipping_company` varchar(100) DEFAULT NULL,
  `vessel_name` varchar(100) DEFAULT NULL,
  `container_no` varchar(50) DEFAULT NULL,
  `shipping_date` date DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `arrived_at` date DEFAULT NULL,
  `total_due` decimal(14,3) NOT NULL DEFAULT 0.000,
  `currency` varchar(3) NOT NULL DEFAULT 'OMR',
  `financial_notes` text DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `is_listed` tinyint(1) NOT NULL DEFAULT 1,
  `sale_price` decimal(14,3) DEFAULT NULL,
  `public_notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_file_no_unique` (`file_no`),
  KEY `vehicles_status_id_foreign` (`status_id`),
  KEY `vehicles_created_by_foreign` (`created_by`),
  KEY `vehicles_updated_by_foreign` (`updated_by`),
  KEY `vehicles_make_index` (`make`),
  KEY `vehicles_year_index` (`year`),
  KEY `vehicles_vin_index` (`vin`),
  KEY `vehicles_purchase_date_index` (`purchase_date`),
  KEY `vehicles_container_no_index` (`container_no`),
  KEY `vehicles_eta_index` (`eta`),
  KEY `vehicles_archived_at_index` (`archived_at`),
  KEY `vehicles_client_id_foreign` (`client_id`),
  CONSTRAINT `vehicles_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `vehicles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vehicles_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `vehicle_statuses` (`id`),
  CONSTRAINT `vehicles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

