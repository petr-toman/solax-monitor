#!/bin/bash

unsignedToSigned() {
  local value=$1
  if ((value > 32767)); then
    value=$((value - 65536))
  fi
  echo "$value"
}


  SolaxResponse=$(curl -m $SolaxDataPollInterval -s -d  "optType=ReadRealTimeData&pwd=$SolaxPasswd" -X POST $SolaxUrl )

  data=$(echo "$SolaxResponse" | jq -r '[.sn,   .Data[14], .Data[15], .Data[82] / 10,  .Data[70] / 10,         .Data[34],  (.Data[93] * 65536 + .Data[92]) / 100, (.Data[91] * 65536 + .Data[90]) / 100, .Data[47], .Data[41],   .Data[79] / 10, .Data[78] / 10, .Data[103], .Data[106] / 10, .Data[105],  .Data[54],   .Data[9],     .Data[19],  .Data[6], .Data[7],  .Data[8]]  | @tsv')
                                read SerNum pv1Power   pv2Power  totalProduction  totalProductionInclBatt feedInPower totalGridIn                            totalGridOut                            loadHome      batteryPower totalChargedIn  totalChargedOut batterySoC   batteryCap       batteryTemp inverterTemp inverterPower inverterMode llph1 llph2 llph3 <<< "$data"

  if [[  $Debuglevel = 1  ]]; then
     echo  $SolaxResponse 
  elif [[  $Debuglevel = 2  ]]; then
      mkdir -p log
      echo  $SolaxResponse  >  log/SolaxResponse.json
  elif [[  $Debuglevel = 3  ]]; then
      mkdir -p log
      echo  $SolaxResponse  >>  log/SolaxResponses.json     
  fi
  

  totalConsumption=$(echo "$totalGridIn + $totalProductionInclBatt - $totalGridOut" | bc)
  #tady by bylo možné dělení nulou!
  if [[  $totalConsumption = 0  ]]; then
    selfSufficiencyRate=0
   else 
    selfSufficiencyRate=$(echo "($totalProductionInclBatt - $totalGridOut) * 100 / $totalConsumption" | bc)
  fi


  totalPower=$((pv1Power + pv2Power))
  totalPeak=$((SolaxString1Peak + SolaxString2Peak))
  
  feedInPower=$(unsignedToSigned "$feedInPower")
  batteryPower=$(unsignedToSigned "$batteryPower")
  loadHome=$(unsignedToSigned "$loadHome")
  inverterPower=$(unsignedToSigned "$inverterPower")