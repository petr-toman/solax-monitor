solax inverter monitoring
cronie: pulls data from invertor via local api, creates AVG aggregations
db: storage for pulled data, inlc 15 mins, hour daily averages
web: presents data to browser


shared enviroment values in .env file
config of inverter /IP, req.nr, password, max. power figures/ in config.env (or by dialog in first run?)


Pocket Wifi 2.0 REST API
Important: You need to send a HTTP POST request!
Firmeware Version: 2.033.20 (Download page: http://de.solaxpower.com/downloads/)

URLs:
http://5.8.8.8/?optType=ReadRealTimeData
http://5.8.8.8/?optType=ReadSetData
http://5.8.8.8/?optType=BatteryMinEnergy&BatteryMinEnergyValue={{{payload}}}&
http://5.8.8.8/?optType=SolarChargerUseMode&SolarChargerUseModeValue={{{payload}}}&
http://5.8.8.8/?optType=Password&PasswordValue={{{password}}}

http://5.8.8.8/?optType=ActivePowerLimit&PasswordValue={{{password}}}&ActivePowerLimitValue={{{payload}}}
