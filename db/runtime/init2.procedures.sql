
DROP PROCEDURE IF EXISTS solax.get_aggregations;

DELIMITER $$
$$
CREATE PROCEDURE solax.get_aggregations( aggreg_factor INT )
BEGIN
	
SELECT 
   SUBSTR(readTime, 1, aggreg_factor) as aggregator, -- `readTime` datetime NOT NULL COMMENT 'Date and time of data read',
   SerNum, -- varchar(32) DEFAULT NULL COMMENT '$SerNum',
   null as inverterMode, -- `inverterMode` varchar(32) DEFAULT NULL COMMENT '$inverterMode [${inverterModeMap[$inverterMode]}] ',
   AVG( totalPower ) as totalPower, -- `totalPower` int(11) DEFAULT NULL COMMENT '$totalPower W',
   AVG( totalPeak )  as totalPeak,  --  `totalPeak` int(11) DEFAULT NULL COMMENT '$totalPeak W',
   AVG( pv1Power )  as  pv1Power, --  `pv1Power` int(11) DEFAULT NULL COMMENT '$pv1Power W',
   AVG( pv2Power )  as  pv2Power ,  --  `pv2Power` int(11) DEFAULT NULL COMMENT '$pv2Power W',
   AVG( totalProduction )  as  totalProduction ,  -- `totalProduction` decimal(9,2) DEFAULT NULL COMMENT '$totalProduction kWh',
   AVG( batterySoC )  as  batterySoC ,  --  `batterySoC` int(11) DEFAULT NULL COMMENT '$batterySoC %',
   AVG( batteryTemp )  as batteryTemp  ,  -- `batteryTemp` int(11) DEFAULT NULL COMMENT '$batteryTemp °C',
   AVG( batteryCap )  as   batteryCap,  -- `batteryCap` decimal(9,2) DEFAULT NULL COMMENT '$batteryCap kWh',
   AVG( batteryPower )  as  batteryPower ,  -- `batteryPower` int(11) DEFAULT NULL COMMENT '$batteryPower W',
   AVG( totalChargedIn )  as totalChargedIn  ,  -- `totalChargedIn` decimal(9,2) DEFAULT NULL COMMENT '$totalChargedIn kWh ',
   AVG( totalChargedOut )  as totalChargedOut  ,  -- `totalChargedOut` decimal(9,2) DEFAULT NULL COMMENT '$totalChargedOut kWh',
   AVG( inverterTemp )  as  inverterTemp ,  --  `inverterTemp` int(11) DEFAULT NULL COMMENT '$inverterTemp °C',
   AVG( inverterPower )  as  inverterPower ,  --  `inverterPower` int(11) DEFAULT NULL COMMENT '$inverterPower W',
   AVG( llph1 )  as  llph1 ,  --  `llph1` int(11) DEFAULT NULL COMMENT '$llph1 W',
   AVG( llph2 )  as  llph2 ,  --  `llph2` int(11) DEFAULT NULL COMMENT '$llph2 W',
   AVG( llph3 )  as  llph3 ,  --  `llph3` int(11) DEFAULT NULL COMMENT '$llph3 W',
   AVG( totalProductionInclBatt )  as totalProductionInclBatt  ,  --  `totalProductionInclBatt` decimal(9,2) DEFAULT NULL COMMENT '$totalProductionInclBatt kWh',
   AVG( feedInPower )  as  feedInPower ,  --  `feedInPower` int(11) DEFAULT NULL COMMENT '$feedInPower W',
   AVG( feedInPowerL1 )  as feedInPowerL1  ,  --  `feedInPowerL1` int(11) DEFAULT NULL COMMENT '$feedInPowerL1 W',
   AVG( feedInPowerL2 )  as  feedInPowerL2 ,  --  `feedInPowerL2` int(11) DEFAULT NULL COMMENT '$feedInPowerL2 W',
   AVG( feedInPowerL3 )  as feedInPowerL3  ,  --  `feedInPowerL3` int(11) DEFAULT NULL COMMENT '$feedInPowerL3 W',
   AVG( totalGridIn )  as  totalGridIn ,  --  `totalGridIn` decimal(9,2) DEFAULT NULL COMMENT '$totalGridIn kWh',
   AVG( totalGridOut )  as totalGridOut  ,  --  `totalGridOut` decimal(9,2) DEFAULT NULL COMMENT '$totalGridOut kWh',
   AVG( totalGridOut )  as totalGridOut  ,  --  `loadHome` int(11) DEFAULT NULL COMMENT '$loadHome W',
   AVG( totalConsumption )  as totalConsumption  ,  --  `totalConsumption` decimal(9,2) DEFAULT NULL COMMENT '$totalConsumption kWh',
   AVG( selfSufficiencyRate ) as selfSufficiencyRate  --  `selfSufficiencyRate` int(11) DEFAULT NULL COMMENT '$selfSufficiencyRate %',
from poll_current 
group by aggregator, SerNum
ORDER by aggregator DESC ; 	
	
END$$
DELIMITER ;