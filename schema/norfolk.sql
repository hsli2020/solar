INSERT INTO solar_project (
	`id`,	
	`name`,
	`ftpdir`,
	`desc`,
	`DC_Nameplate_Capacity`,
	`AC_Nameplate_Capacity`,
	`active`)
VALUES (
    7,
    'Norfolk',
    'C:\\GCS-FTP-ROOT\\Norfolk_001EC60537E5',
    '',
    12089, 
    10000,
    1);

-- 011     GEN_METER
-- 031~050 SMA Inverters
-- 071 076 ENV_Kit

INSERT INTO `solar_device` (`id`, `project_id`, `devcode`, `type`, `table`, `model`, `desc`) VALUES 
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
    (46, 7, 'mb-076', 'EnvKit', 'solar_data_envkit', 'DataEnvKits', '')
;

INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 1, 558318,   65 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 2, 765281,   82 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 3, 1293758,  130 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 4, 1467029,  148 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 5, 1753889,  180 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 6, 1855926,  194 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 7, 1773650,  189 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 8, 1611537,  171 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 9, 1347744,  139 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 10, 1038866, 102 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 11, 598571,  61 )
INSERT INTO monthly_budget (project_id, year, month, Budget, IE_POA_Insolation) VALUES (7, 2017, 12, 488257,  56 )

