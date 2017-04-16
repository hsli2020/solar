-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.13-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table solar.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(80) NOT NULL,
  `role` int(10) NOT NULL,
  `active` char(1) NOT NULL DEFAULT 'Y',
  `createdon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table solar.users: ~5 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `active`, `createdon`, `updatedon`) VALUES
	(1, 'hsli', 'lihsca@gmail.com', '$2y$12$bB9SYPXbqslfYzHmRXVI1emyPtkjB00x2kB/0TbUWNw5TLcz0BpRa', 1, 'Y', '2016-09-24 09:48:37', '2016-09-24 09:48:37'),
	(2, 'wsong', 'wsong365@gmail.com', '$2y$12$GeJSNMQiPu9wtdhhd3W9QuZeNGqDYB9xlB6KnFEnBLtnwpJx5Kpp.', 1, 'Y', '2016-09-24 09:48:37', '2016-09-24 09:48:37'),
	(3, 'jastejada', 'jastejada@greatcirclesolar.ca', '$2y$12$mkeJfIaZwlYLR4P6xVNwh.WhspImA02MXHu0o2RECwbZaIBVfEFUG', 0, 'Y', '2017-02-12 21:19:54', '2017-02-12 21:19:54'),
	(4, 'dmacabales', 'dmacabales@greatcirclesolar.ca', '$2y$12$ftNhewcnfKUuuid2AghqguXFwEo9hJALWvrvcSJRB.vgROb/TpQTy', 0, 'Y', '2017-02-16 21:41:19', '2017-02-16 21:41:19'),
	(5, 'om_rescoenergy', '', '$2y$12$Y3B6bmZGc0lQRmxLTTVETOpV1d3ipcdgAZkA3eOa4KnjpqjFe.E66', 0, 'Y', '2017-04-02 15:05:00', '2017-04-02 15:05:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
