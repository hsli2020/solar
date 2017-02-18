
CREATE TABLE IF NOT EXISTS `solar_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `devcode` varchar(20) NOT NULL,
  `type` varchar(40) NOT NULL,
  `table` varchar(40) NOT NULL,
  `model` varchar(40) NOT NULL,
  `desc` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

INSERT INTO `solar_device` (`id`, `project_id`, `devcode`, `type`, `table`, `model`, `desc`) VALUES
	(1,  1, 'mb-031', 'Inverter', 'solar_data_inverter_pvp',    'DataInverterPvp',    'PVP'),
	(2,  1, 'mb-032', 'Inverter', 'solar_data_inverter_pvp',    'DataInverterPvp',    'PVP'),
	(3,  1, 'mb-033', 'Inverter', 'solar_data_inverter_pvp',    'DataInverterPvp',    'PVP'),
	(4,  1, 'mb-011', 'GenMeter', 'solar_data_genmeter',        'DataGenMeters',      ''),
	(5,  2, 'mb-071', 'EnvKit',   'solar_data_envkit',          'DataEnvKits',        ''),
	(6,  2, 'mb-080', 'Inverter', 'solar_data_inverter_serial', 'DataInverterSerial', 'SERIAL'),
	(7,  2, 'mb-081', 'Inverter', 'solar_data_inverter_serial', 'DataInverterSerial', 'SERIAL'),
	(8,  2, 'mb-011', 'GenMeter', 'solar_data_genmeter',        'DataGenMeters',      ''),
	(9,  1, 'mb-071', 'EnvKit',   'solar_data_envkit',          'DataEnvKits',        ''),
	(10, 3, 'mb-011', 'GenMeter', 'solar_data_genmeter',        'DataGenMeters',      ''),
	(11, 3, 'mb-071', 'EnvKit',   'solar_data_envkit',          'DataEnvKits',        ''),
	(12, 3, 'mb-031', 'Inverter', 'solar_data_inverter_sma',    'DataInverterSma',    'SMA'),
	(14, 4, 'mb-011', 'GenMeter', 'solar_data_genmeter',        'DataGenMeters',      ''),
	(15, 4, 'mb-071', 'EnvKit',   'solar_data_envkit',          'DataEnvKits',        ''),
	(16, 4, 'mb-031', 'Inverter', 'solar_data_inverter_pvp',    'DataInverterPvp',    'PVP'),
	(17, 5, 'mb-011', 'GenMeter', 'solar_data_genmeter',        'DataGenMeters',      ''),
	(18, 5, 'mb-071', 'EnvKit',   'solar_data_envkit',          'DataEnvKits',        ''),
	(19, 5, 'mb-031', 'Inverter', 'solar_data_inverter_sma',    'DataInverterSma',    'SMA'),
	(20, 5, 'mb-003', 'Inverter', 'solar_data_inverter_sma',    'DataInverterSma',    'SMA');

INSERT INTO `solar_device` (`id`, `project_id`, `devcode`, `type`, `table`, `desc`) VALUES
	(1,  1, 'mb-031', 'Inverter', 'solar_data_inverter_pvp', ''),
	(2,  1, 'mb-032', 'Inverter', 'solar_data_inverter_pvp', ''),
	(3,  1, 'mb-033', 'Inverter', 'solar_data_inverter_pvp', ''),
	(4,  1, 'mb-011', 'GenMeter', 'solar_data_genmeter', ''),
	(5,  2, 'mb-071', 'EnvKit',   'solar_data_envkit', ''),
	(6,  2, 'mb-080', 'Inverter', 'solar_data_inverter_serial', ''),
	(7,  2, 'mb-081', 'Inverter', 'solar_data_inverter_serial', ''),
	(8,  2, 'mb-011', 'GenMeter', 'solar_data_genmeter', ''),
	(9,  1, 'mb-071', 'EnvKit',   'solar_data_envkit', ''),
	(10, 3, 'mb-011', 'GenMeter', 'solar_data_genmeter', ''),
	(11, 3, 'mb-071', 'EnvKit',   'solar_data_envkit', ''),
	(12, 3, 'mb-031', 'Inverter', 'solar_data_inverter_sma', ''),
	(14, 4, 'mb-011', 'GenMeter', 'solar_data_genmeter', ''),
	(15, 4, 'mb-071', 'EnvKit',   'solar_data_envkit', ''),
	(16, 4, 'mb-031', 'Inverter', 'solar_data_inverter_pvp', ''),
	(17, 5, 'mb-011', 'GenMeter', 'solar_data_genmeter', ''),
	(18, 5, 'mb-071', 'EnvKit',   'solar_data_envkit', ''),
	(19, 5, 'mb-031', 'Inverter', 'solar_data_inverter_sma', ''),
	(20, 5, 'mb-003', 'Inverter', 'solar_data_inverter_sma', '');
