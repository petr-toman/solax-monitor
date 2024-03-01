#!/bin/bash 
 Debuglevel=2
  CurrentTimeStamp="$(date +"%Z %Y-%m-%d %H:%M:%S")"   
  source $(dirname "$0")/solax.sh      # poll and convert data from solax
  source $(dirname "$0")/azrouter.sh   # poll and convert data from wattrouter
  source $(dirname "$0")/show_data.sh   # poll and convert data from wattrouter
  source $(dirname "$0")/save-db.sh   # poll and convert data from wattrouter

