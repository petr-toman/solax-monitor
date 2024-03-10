#!/bin/bash
# use env values injected from docker-compose .env file

while true; do

  CurrentTimeStamp="$(date +"%Y-%m-%d %H:%M:%S")" 


# wait SolaxDataPollInterval seconds and repeat:
  symbols="/-\|"
  for ((w=0; w<$SolaxDataPollInterval; w++)); do
    for ((i=0; i<${#symbols}; i++)); do
      echo -n "                " "${symbols:$i:1}" " " "$(echo $SolaxDataPollInterval - $w  | bc )" " " "${symbols:$i:1}"  "                    " 
      sleep 0.25
      echo -ne "\r" 
    done
  done
done