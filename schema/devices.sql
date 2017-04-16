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

-- Dumping structure for table solar.solar_device
CREATE TABLE IF NOT EXISTS `solar_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `devcode` varchar(20) NOT NULL,
  `type` varchar(40) NOT NULL,
  `table` varchar(40) NOT NULL,
  `model` varchar(40) NOT NULL,
  `desc` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

-- Dumping data for table solar.solar_device: ~44 rows (approximately)
/*!40000 ALTER TABLE `solar_device` DISABLE KEYS */;
INSERT INTO `solar_device` (`id`, `project_id`, `devcode`, `type`, `table`, `model`, `desc`) VALUES
	(1, 1, 'mb-031', 'Inverter', 'solar_data_inverter_pvp', 'DataInverterPvp', 'PVP'),
	(2, 1, 'mb-032', 'Inverter', 'solar_data_inverter_pvp', 'DataInverterPvp', 'PVP'),
	(3, 1, 'mb-033', 'Inverter', 'solar_data_inverter_pvp', 'DataInverterPvp', 'PVP'),
	(4, 1, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(5, 2, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(6, 2, 'mb-080', 'Inverter', 'solar_data_inverter_serial', 'DataInverterSerial', 'SERIAL'),
	(7, 2, 'mb-081', 'Inverter', 'solar_data_inverter_serial', 'DataInverterSerial', 'SERIAL'),
	(8, 2, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(9, 1, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(10, 3, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(11, 3, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(12, 3, 'mb-031', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(14, 4, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(15, 4, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(16, 4, 'mb-031', 'Inverter', 'solar_data_inverter_pvp', 'DataInverterPvp', 'PVP'),
	(17, 5, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(18, 5, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(19, 5, 'mb-031', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(21, 6, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(22, 6, 'mb-031', 'Inverter', 'solar_data_inverter_pvp', 'DataInverterPvp', 'PVP'),
	(23, 6, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(24, 7, 'mb-011', 'GenMeter', 'solar_data_genmeter', 'DataGenMeters', ''),
	(25, 7, 'mb-031', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(26, 7, 'mb-032', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(27, 7, 'mb-033', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(28, 7, 'mb-034', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(29, 7, 'mb-035', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(30, 7, 'mb-036', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(31, 7, 'mb-037', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(32, 7, 'mb-038', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(33, 7, 'mb-039', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(34, 7, 'mb-040', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(35, 7, 'mb-041', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(36, 7, 'mb-042', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(37, 7, 'mb-043', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(38, 7, 'mb-044', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(39, 7, 'mb-045', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(40, 7, 'mb-046', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(41, 7, 'mb-047', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(42, 7, 'mb-048', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(43, 7, 'mb-049', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(44, 7, 'mb-050', 'Inverter', 'solar_data_inverter_sma', 'DataInverterSma', 'SMA'),
	(45, 7, 'mb-071', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', ''),
	(46, 7, 'mb-076', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', '');
/*!40000 ALTER TABLE `solar_device` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
