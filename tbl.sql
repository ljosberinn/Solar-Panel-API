CREATE TABLE IF NOT EXISTS `data` (
  `ts` int(10) NOT NULL,
  `production` float NOT NULL,
  `purchased` float NOT NULL,
  `feedin` float NOT NULL,
  `consumption` float NOT NULL,
  `selfconsumption` float NOT NULL,
  UNIQUE KEY `ts` (`ts`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;
