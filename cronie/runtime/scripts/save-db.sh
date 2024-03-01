#!/bin/bash

if  [[ -z $CurrentTimeStamp  ]] ; then
   CurrentTimeStamp="$(date +"%Y-%m-%d %H:%M:%S")" 
fi   


insert_query=$(
echo "INSERT INTO solax.poll_current (
     readTime,        
     SerNum, 
     inverterMode ,
     totalPower ,
     totalPeak ,
     pv1Power ,
     pv2Power ,
     totalProduction,
     batterySoC ,
     batteryTemp ,
     batteryCap ,
     batteryPower, 
     totalChargedIn, 
     totalChargedOut, 
     inverterTemp ,
     inverterPower ,
     llph1 ,
     llph2 ,
     llph3 ,
     totalProductionInclBatt ,
     feedInPower ,
     feedInPowerL1,
     feedInPowerL2,
     feedInPowerL3,
     totalGridIn ,
     totalGridOut ,
     loadHome ,
     totalConsumption, 
     selfSufficiencyRate
    ) 
 VALUES (
     '$CurrentTimeStamp',
     '$SerNum',
     '$inverterMode',
     '$totalPower',
     '$totalPeak',
     '$pv1Power',
     '$pv2Power',
     '$totalProduction',
     '$batterySoC',
     '$batteryTemp',
     '$batteryCap',
     '$batteryPower',
     '$totalChargedIn',
     '$totalChargedOut',
     '$inverterTemp',
     '$inverterPower',
     '$llph1',
     '$llph2',
     '$llph3',
     '$totalProductionInclBatt',
     '$feedInPower',
     '$AZRPowerL1',
     '$AZRPowerL2',
     '$AZRPowerL3',
     '$totalGridIn',
     '$totalGridOut',
     '$loadHome',
     '$totalConsumption',
     '$selfSufficiencyRate'
 );
 "
)

mysql -hsolax-monitor-mariadb-1 -u$MYSQL_USER -p$MYSQL_PASSWORD -e"$insert_query"