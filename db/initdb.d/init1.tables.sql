
CREATE TABLE IF NOT EXISTS `solax`.`dataset_template` (
  `readTime` datetime NOT NULL COMMENT 'Date and time of data read',
  `SerNum` varchar(32) DEFAULT NULL COMMENT '$SerNum',
  `inverterMode` varchar(32) DEFAULT NULL COMMENT '$inverterMode [${inverterModeMap[$inverterMode]}] ',
  `totalPower` int(11) DEFAULT NULL COMMENT '$totalPower W',
  `totalPeak` int(11) DEFAULT NULL COMMENT '$totalPeak W',
  `pv1Power` int(11) DEFAULT NULL COMMENT '$pv1Power W',
  `pv2Power` int(11) DEFAULT NULL COMMENT '$pv2Power W',
  `totalProduction` decimal(9,2) DEFAULT NULL COMMENT '$totalProduction kWh',
  `batterySoC` int(11) DEFAULT NULL COMMENT '$batterySoC %',
  `batteryTemp` int(11) DEFAULT NULL COMMENT '$batteryTemp °C',
  `batteryCap` decimal(9,2) DEFAULT NULL COMMENT '$batteryCap kWh',
  `batteryPower` int(11) DEFAULT NULL COMMENT '$batteryPower W',
  `totalChargedIn` decimal(9,2) DEFAULT NULL COMMENT '$totalChargedIn kWh ',
  `totalChargedOut` decimal(9,2) DEFAULT NULL COMMENT '$totalChargedOut kWh',
  `inverterTemp` int(11) DEFAULT NULL COMMENT '$inverterTemp °C',
  `inverterPower` int(11) DEFAULT NULL COMMENT '$inverterPower W',
  `llph1` int(11) DEFAULT NULL COMMENT '$llph1 W',
  `llph2` int(11) DEFAULT NULL COMMENT '$llph2 W',
  `llph3` int(11) DEFAULT NULL COMMENT '$llph3 W',
  `totalProductionInclBatt` decimal(9,2) DEFAULT NULL COMMENT '$totalProductionInclBatt kWh',
  `feedInPower` int(11) DEFAULT NULL COMMENT '$feedInPower W',
  `feedInPowerL1` int(11) DEFAULT NULL COMMENT '$feedInPowerL1 W',
  `feedInPowerL2` int(11) DEFAULT NULL COMMENT '$feedInPowerL2 W',
  `feedInPowerL3` int(11) DEFAULT NULL COMMENT '$feedInPowerL3 W',
  `totalGridIn` decimal(9,2) DEFAULT NULL COMMENT '$totalGridIn kWh',
  `totalGridOut` decimal(9,2) DEFAULT NULL COMMENT '$totalGridOut kWh',
  `loadHome` int(11) DEFAULT NULL COMMENT '$loadHome W',
  `totalConsumption` decimal(9,2) DEFAULT NULL COMMENT '$totalConsumption kWh',
  `selfSufficiencyRate` int(11) DEFAULT NULL COMMENT '$selfSufficiencyRate %',
  PRIMARY KEY (`readTime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

TRUNCATE TABLE `solax`.`dataset_template`;  --this is empy table, template

CREATE TABLE IF NOT EXISTS `solax`.`poll_00last_one` LIKE `solax`.`dataset_template`; --just one last enty
CREATE TABLE IF NOT EXISTS `solax`.`poll_current` LIKE `solax`.`dataset_template`;  -- each polled data
CREATE TABLE IF NOT EXISTS `solax`.`poll_1mins_avg` LIKE `solax`.`dataset_template`; --minute averages
CREATE TABLE IF NOT EXISTS `solax`.`poll_5mins_avg` LIKE `solax`.`dataset_template`; --5 minute averages (as solax cloud)
CREATE TABLE IF NOT EXISTS `solax`.`poll_10mins_avg` LIKE `solax`.`dataset_template`; --10 minute averages (easy to get from timestamp)
CREATE TABLE IF NOT EXISTS `solax`.`poll_15mins_avg` LIKE `solax`.`dataset_template`; --15 mins averages -- as grid provider stats
CREATE TABLE IF NOT EXISTS `solax`.`poll_hourly_avg` LIKE `solax`.`dataset_template`; -- hourly averages
CREATE TABLE IF NOT EXISTS `solax`.`poll_daily_avg` LIKE `solax`.`dataset_template`;  -- daily averages
CREATE TABLE IF NOT EXISTS `solax`.`poll_archive` LIKE `solax`.`dataset_template`; --archived data

