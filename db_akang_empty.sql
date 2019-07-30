-- --------------------------------------------------------
-- Host:                         192.168.146.248
-- Server version:               10.2.21-MariaDB-log - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for sapi
CREATE DATABASE IF NOT EXISTS `sapi` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `sapi`;

-- Dumping structure for table sapi.absensi_pemeriksa
CREATE TABLE IF NOT EXISTS `absensi_pemeriksa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `tgl_absen` date NOT NULL,
  `status` set('PIB','CN_PIBK','CARNET') NOT NULL,
  `wkt_absen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_tgl_absen` (`user_id`,`tgl_absen`),
  KEY `tgl_absen` (`tgl_absen`),
  KEY `status` (`status`),
  KEY `wkt_absen` (`wkt_absen`),
  CONSTRAINT `FK_absensi_pemeriksa_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42884 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for event sapi.auto_absen_pulang
DELIMITER //
CREATE DEFINER=`root`@`localhost` EVENT `auto_absen_pulang` ON SCHEDULE EVERY 1 DAY STARTS '2018-03-07 03:15:01' ON COMPLETION PRESERVE ENABLE COMMENT 'otomatis absen pulang pemeriksa' DO BEGIN
	-- insert active pemeriksa into status table
	INSERT INTO 
		status_pemeriksa (
			user_id,
			status,
			lokasi
		)
	SELECT
		a.id,
		'BUSY',
		'BCSH'
	FROM
		user a
	WHERE
		a.role = 'PEMERIKSA'
		AND a.active = 'Y';
END//
DELIMITER ;

-- Dumping structure for event sapi.auto_assign_role
DELIMITER //
CREATE DEFINER=`root`@`localhost` EVENT `auto_assign_role` ON SCHEDULE EVERY 1 DAY STARTS '2018-03-07 07:00:31' ON COMPLETION PRESERVE ENABLE COMMENT 'otomatis menset role pemeriksa aktif pada hari itu' DO BEGIN
	INSERT IGNORE INTO
		absensi_pemeriksa(
			user_id,
			tgl_absen,
			status
		)
	SELECT
		id,
		DATE(NOW()),
		'CN_PIBK'
	FROM
		user a
	WHERE
		a.role = 'PEMERIKSA'
		AND a.active = 'Y';
END//
DELIMITER ;

-- Dumping structure for event sapi.auto_purge_overtime
DELIMITER //
CREATE DEFINER=`data_miner`@`%` EVENT `auto_purge_overtime` ON SCHEDULE EVERY 1 DAY STARTS '2019-04-22 16:18:25' ON COMPLETION PRESERVE ENABLE COMMENT 'automatis nge-overtime dokumen yang lewat dari 24 jam tidak dapa' DO BEGIN
	-- masukan status baru untuk dokumen yang masih ON_PROCESS
	-- dengan umur > 24 jam sejak diunggah via AKANG
	INSERT INTO
		status_dok(dok_id, status, catatan, user_id)
	(
	SELECT
		proc_list.dok_id, 'OVERTIME', 'Barang tidak dapat disediakan lewat waktu 24 jam',2
	FROM
		batch_detail a
		INNER JOIN
		(
		SELECT
			a.dok_id
		FROM
			status_dok a
			INNER JOIN
			(
			SELECT
				a.dok_id, MAX(a.time) latest_time
			FROM
				status_dok a
			GROUP BY
				a.dok_id
			) lt
			ON
				a.dok_id = lt.dok_id
				AND a.time = lt.latest_time
		WHERE
			-- hanya untuk dokumen yang status terakhirnya ON_PROCESS
			a.`status` = 'ON_PROCESS'
		) proc_list
		ON
			a.id = proc_list.dok_id
		LEFT JOIN
			batch_header b
		ON
			a.batch_id = b.id
	WHERE
		-- batas paling lama adalah 24 jam (EDIT DI SINI UNTUK UBAH WAKTU)
		TIMEDIFF(NOW(),b.time_uploaded) > '24:00:00'
	);
END//
DELIMITER ;

-- Dumping structure for table sapi.batch_detail
CREATE TABLE IF NOT EXISTS `batch_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `jenis_dok` enum('CN_PIBK','PIB','CARNET') NOT NULL,
  `no_dok` varchar(32) NOT NULL,
  `tgl_dok` date NOT NULL,
  `importir` varchar(256) NOT NULL,
  `jml_item` int(10) unsigned NOT NULL,
  `berat_kg` decimal(10,4) unsigned NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `photo_id` int(10) unsigned DEFAULT NULL,
  `tahun_dok` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_dok_no_dok_tahun_dok` (`no_dok`,`tahun_dok`,`jenis_dok`),
  KEY `tgl_dok` (`tgl_dok`),
  KEY `photo_id` (`photo_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `FK_batch_detail_batch_header` FOREIGN KEY (`batch_id`) REFERENCES `batch_header` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_batch_detail_pkb_photo` FOREIGN KEY (`photo_id`) REFERENCES `pkb_photo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=507740 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.batch_header
CREATE TABLE IF NOT EXISTS `batch_header` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_uploaded` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uploader_id` int(10) unsigned NOT NULL,
  `gudang` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time_uploaded` (`time_uploaded`),
  KEY `uploader_id` (`uploader_id`),
  KEY `gudang2` (`gudang`),
  FULLTEXT KEY `gudang` (`gudang`),
  CONSTRAINT `FK_batch_header_grup_gudang` FOREIGN KEY (`gudang`) REFERENCES `grup_gudang` (`gudang`),
  CONSTRAINT `FK_batch_header_user` FOREIGN KEY (`uploader_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48999 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.grup_gudang
CREATE TABLE IF NOT EXISTS `grup_gudang` (
  `gudang` varchar(16) NOT NULL,
  `grup` varchar(16) NOT NULL,
  PRIMARY KEY (`gudang`,`grup`),
  KEY `grup` (`grup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Data exporting was unselected.

-- Dumping structure for table sapi.notifikasi
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `targetid` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `read` tinyint(4) NOT NULL DEFAULT 0,
  `readtime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `FK_notifikasi_user` (`targetid`),
  CONSTRAINT `FK_notifikasi_user` FOREIGN KEY (`targetid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table sapi.pkb_photo
CREATE TABLE IF NOT EXISTS `pkb_photo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(256) NOT NULL,
  `real_filename` varchar(256) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.status_dok
CREATE TABLE IF NOT EXISTS `status_dok` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dok_id` int(10) unsigned NOT NULL,
  `status` enum('ON_PROCESS','FINISHED','CANCELED','OVERTIME','INCONSISTENT') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dok_id` (`dok_id`),
  KEY `status` (`status`),
  KEY `time` (`time`),
  KEY `FK_status_dok_user` (`user_id`),
  CONSTRAINT `FK_status_dok_batch_detail` FOREIGN KEY (`dok_id`) REFERENCES `batch_detail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_status_dok_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=673751 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.status_pemeriksa
CREATE TABLE IF NOT EXISTS `status_pemeriksa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `status` enum('AVAILABLE','BUSY') NOT NULL,
  `lokasi` varchar(16) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `time` (`time`),
  KEY `FK_status_pemeriksa_grup_gudang` (`lokasi`),
  CONSTRAINT `FK_status_pemeriksa_grup_gudang` FOREIGN KEY (`lokasi`) REFERENCES `grup_gudang` (`gudang`) ON UPDATE CASCADE,
  CONSTRAINT `FK_status_pemeriksa_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23759 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `fullname` varchar(256) NOT NULL,
  `password` varchar(32) NOT NULL,
  `role` set('PJT','PPJK','ADMIN_PABEAN','SUPERUSER','PEMERIKSA','CARNET_HANDLER') NOT NULL,
  `active` enum('Y','N') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.user_gudang_pair
CREATE TABLE IF NOT EXISTS `user_gudang_pair` (
  `user_id` int(10) unsigned NOT NULL,
  `gudang` varchar(16) NOT NULL,
  UNIQUE KEY `userid_gudang` (`user_id`,`gudang`),
  KEY `FK_user_gudang_pair_grup_gudang` (`gudang`),
  CONSTRAINT `FK_user_gudang_pair_grup_gudang` FOREIGN KEY (`gudang`) REFERENCES `grup_gudang` (`gudang`),
  CONSTRAINT `FK_user_gudang_pair_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data exporting was unselected.

-- Dumping structure for table sapi.user_session
CREATE TABLE IF NOT EXISTS `user_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `ip_address` int(10) unsigned NOT NULL,
  `time_started` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expire` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63576 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for trigger sapi.batch_detail_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `batch_detail_after_insert` AFTER INSERT ON `batch_detail` FOR EACH ROW BEGIN
	INSERT INTO
		status_dok(
			`dok_id`, 
			`status`, 
			`user_id`
		)
	VALUES (
		NEW.id,
		'ON_PROCESS',
		2
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
