#!/bin/bash
# use env values injected from docker-compose .env file



source ../../../.env  # just for case to run it locally out of docker with injected env vars


while true; do

  CurrentTimeStamp="$(date +"%Y-%m-%d %H:%M:%S")" 
  source $(dirname "$0")/solax.sh      # poll and convert data from solax
  source $(dirname "$0")/azrouter.sh   # poll and convert data from wattrouter
  source $(dirname "$0")/show_data.sh   # poll and convert data from wattrouter
  source $(dirname "$0")/save-db.sh   # poll and convert data from wattrouter

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