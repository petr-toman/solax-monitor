#!/bin/bash
# use env values injected from docker-compose .env file
# test if it is number 1 - 86400 (from 1 second to one day)

re='^[0-9]+([.][0-9]+)?$'
if ! [[ $SolaxDataPollInterval =~ $re ]] ; then
   SolaxDataPollInterval = 5
fi

if [ $SolaxDataPollInterval -le 1 ]; then
   SolaxDataPollInterval = 1
fi

if [ $SolaxDataPollInterval -ge 86400  ]; then
   SolaxDataPollInterval = 86400
fi

cd  /var/www/localhost/htdocs/server


while true; do

  php82 poll-service.php
  sleep $SolaxDataPollInterval

done
