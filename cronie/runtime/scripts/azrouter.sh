#!/bin/bash

AZRouterresponse=$(curl -s $AZRouterPowerUrl )

  AZRData=$(echo "$AZRouterresponse" | jq -r  '[.input.current[0].value, 
                                     .input.current[1].value, 
                                     .input.current[2].value, 
                                     .input.power[0].value, 
                                     .input.power[1].value, 
                                     .input.power[2].value,
                                     .output.energy[0].value, 
                                     .output.energy[1].value, 
                                     .output.energy[2].value
                                     ]| @tsv')
                            
read AZRCurrL1 AZRCurrL2 AZRCurrL3 AZRPowerL1 AZRPowerL2 AZRPowerL3 AZRTotalEnergyL1 AZRTotalEnergyL2 AZRTotalEnergyL3  <<< "$AZRData"

  if [[  $Debuglevel = 1  ]]; then
     echo  $AZRouterresponse 
  elif [[  $Debuglevel = 2  ]]; then
      mkdir -p log
      echo  $AZRouterresponse  >  log/AZResponse.json
  elif [[  $Debuglevel = 3  ]]; then
      mkdir -p log
      echo  $AZRouterresponse  >>  log/AZResponses.json     
  fi
  