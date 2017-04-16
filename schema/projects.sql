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

-- Dumping structure for table solar.solar_project
CREATE TABLE IF NOT EXISTS `solar_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `ftpdir` varchar(200) NOT NULL,
  `desc` varchar(100) NOT NULL,
  `DC_Nameplate_Capacity` double(12,2) NOT NULL,
  `AC_Nameplate_Capacity` double(12,2) NOT NULL,
  `IE_Insolation` double(12,2) NOT NULL,
  `FIT_Rate` double(12,3) NOT NULL,
  `Module_Power_Coefficient` float NOT NULL DEFAULT '-0.43',
  `Inverter_Efficiency` float NOT NULL DEFAULT '0.98',
  `Transformer_Loss` float NOT NULL DEFAULT '0.015',
  `Other_Loss` float NOT NULL DEFAULT '0.02',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- Dumping data for table solar.solar_project: ~5 rows (approximately)
/*!40000 ALTER TABLE `solar_project` DISABLE KEYS */;
INSERT INTO `solar_project` (`id`, `name`, `ftpdir`, `desc`, `DC_Nameplate_Capacity`, `AC_Nameplate_Capacity`, `IE_Insolation`, `FIT_Rate`, `Module_Power_Coefficient`, `Inverter_Efficiency`, `Transformer_Loss`, `Other_Loss`, `active`) VALUES
	(1, '125 Bermondsey', 'C:\\GCS-FTP-ROOT\\125Bermondsey_001EC6053434', '', 653.00, 450.00, 1162.00, 0.635, -0.43, 0.98, 0.015, 0.02, 1),
	(2, '1935 Drew', 'C:\\GCS-FTP-ROOT\\1935Drew_001EC6053835', '', 162.70, 150.00, 1139.00, 0.713, -0.43, 0.98, 0.015, 0.02, 1),
	(3, '1755 Brimley', 'C:\\GCS-FTP-ROOT\\1755Brimley_001EC6053C3F', '', 602.91, 500.00, 1189.00, 0.635, -0.43, 0.98, 0.015, 0.02, 1),
	(4, '200 Bullock', 'C:\\GCS-FTP-ROOT\\200Bullock_001EC6053C3A', '', 306.24, 250.00, 1180.00, 0.713, -0.43, 0.98, 0.015, 0.02, 1),
	(5, '51 Gerry Fitzgerald', 'C:\\GCS-FTP-ROOT\\51GerryFitzgerald_001EC60540BF', '', 611.30, 500.00, 1156.00, 0.635, -0.43, 0.98, 0.015, 0.02, 1),
	(6, '171 Guelph', 'C:\\GCS-FTP-ROOT\\171GUELPH_001EC6053E35', '', 408.87, 375.00, 1156.00, 0.635, -0.43, 0.98, 0.015, 0.02, 1),
	(7, 'Norfolk', 'C:\\GCS-FTP-ROOT\\Norfolk_001EC60537E5', '', 12089.00, 10000.00, 0.00, 0.000, -0.43, 0.98, 0.015, 0.02, 1);
/*!40000 ALTER TABLE `solar_project` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
