#!/bin/bash 
  clear

# estimate different decimal separator (independent at locale, just test how printf behaves)
decimalseparator=$(echo "$(printf "%1.1f" "3")")
decimalseparator=${decimalseparator:1:1} 

progress_bar() {
  local val=$1
  local max=$2
  local bar_length=20

  if [[ $max -eq 0 ]] ; then 
    lc_progress=0 
  else  
    lc_progress=$((val * bar_length / max))  
  fi 

  local progress_bar=""

  for ((i=0; i<bar_length; i++)); do
    if (( i < lc_progress )); then
      progress_bar+="#"
    else
      progress_bar+="_"
    fi
  done

  echo -n "[$progress_bar]"
}


declare -a inverterModeMap
inverterModeMap[0]="Waiting"
inverterModeMap[1]="Checking"
inverterModeMap[2]="Normal"
inverterModeMap[3]="Off"
inverterModeMap[4]="Permanent Fault"
inverterModeMap[5]="Updating"
inverterModeMap[6]="EPS Check"
inverterModeMap[7]="EPS Mode"
inverterModeMap[8]="Self Test"
inverterModeMap[9]="Idle"
inverterModeMap[10]="Standby"

divLine="------------------------------------------------\r"

totalConsumption=${totalConsumption/./$decimalseparator}
selfSufficiencyRate=${selfSufficiencyRate/./$decimalseparator}  
totalProduction=${totalProduction/./$decimalseparator}
totalProductionInclBatt=${totalProductionInclBatt/./$decimalseparator}
totalGridIn=${totalGridIn/./$decimalseparator}
totalGridOut=${totalGridOut/./$decimalseparator}
totalChargedIn=${totalChargedIn/./$decimalseparator}
totalChargedOut=${totalChargedOut/./$decimalseparator}
batteryCap=${batteryCap/./$decimalseparator}
AZRCurrL1=${AZRCurrL1/./$decimalseparator}
AZRCurrL2=${AZRCurrL2/./$decimalseparator}
AZRCurrL3=${AZRCurrL3/./$decimalseparator}
AZRPowerL1=${AZRPowerL1/./$decimalseparator}
AZRPowerL2=${AZRPowerL2/./$decimalseparator} 
AZRPowerL3=${AZRPowerL3/./$decimalseparator} 

inverterMode=$(echo $inverterMode [${inverterModeMap[$inverterMode]}] )

if  [[ -z $CurrentTimeStamp  ]] ; then
   CurrentTimeStamp="$(date +"%Y-%m-%d %H:%M:%S")" 
fi     

# debugstring # 
PrintData=$( echo "
measurementTime =  $CurrentTimeStamp;
SerNum = $SerNum;
inverterMode = $inverterMode;
totalPower = $totalPower W;
totalPeak = $totalPeak W;
pv1Power = $pv1Power W;
pv2Power = $pv2Power W;
totalProduction = $totalProduction kWh;
batterySoC = $batterySoC %;
batteryTemp = $batteryTemp °C;
batteryCap = $batteryCap kWh;
batteryPower = $batteryPower W;
totalChargedIn = $totalChargedIn kWh;
totalChargedOut = $totalChargedOut kWh;
inverterTemp = $inverterTemp °C;
inverterPower = $inverterPower W;
llph1 = $llph1 W;
llph2 = $llph2 W;
llph3 = $llph3 W;
totalProductionInclBatt = $totalProductionInclBatt kWh;
feedInPower = $feedInPower W;
totalGridIn = $totalGridIn kWh;
totalGridOut = $totalGridOut kWh;
loadHome = $loadHome W;
totalConsumption = $totalConsumption kWh;
selfSufficiencyRate = $selfSufficiencyRate %;
AZRCurrL1 = $AZRCurrL1 mA;
AZRCurrL2 = $AZRCurrL2 mA;
AZRCurrL3 = $AZRCurrL3 mA;
AZRPowerL1  = $AZRPowerL1 W;
AZRPowerL2  = $AZRPowerL2 W;
AZRPowerL3  = $AZRPowerL3 W;
"
 )

  if [[  $Debuglevel = 1  ]]; then
     echo  $PrintData 
  elif [[  $Debuglevel = 2  ]]; then
      mkdir -p log
      echo  $PrintData  >  log/PrintData.txt
  elif [[  $Debuglevel = 3  ]]; then
      mkdir -p log
      echo  $PrintData  >>  log/PrintData.txt     
  fi

# # # # # # # # # # # # # # # # # # # # # # # 

  if [[ -z $SerNum  ]] ; then
     colorDimmed="\e[2m"
     colorDefault=$colorDimmed
     colorPositive=$colorDimmed
     colorNegative=$colorDimmed   
     printf "\e[0m \e[31mConnection error: $url \e[0m \n"
     printf "$colorDimmed" 
  else
     colorDimmed="\e[2m"
     colorDefault="\e[0m"
     colorPositive="\e[36m"
     colorNegative="\e[31m"
     printf "$colorDefault"
  fi

  echo -e "$divLine"
  dt=$(date) 
  echo $SerNum "      "  $dt
  echo -e "$divLine"
  echo ""
  echo -ne "$divLine"
  echo -e "\033[20C PANELY "
  echo "        celkem: $(printf "%5d" "$totalPower") W   $(progress_bar $totalPower $totalPeak)"
  echo "      string 1: $(printf "%5d" "$pv1Power") W   $(progress_bar $pv1Power $SolaxString1Peak)"
  echo "      string 2: $(printf "%5d" "$pv2Power") W   $(progress_bar $pv2Power $SolaxString1Peak)"
  echo "dnes výroba DC: $(printf "%5.1f" "$totalProduction") kWh"
  echo ""
  echo -ne "$divLine"
  echo -e "\033[20C BATERIE "
  echo "                          $(printf "%3d" "$batterySoC") %        $(printf "%5d" "$batteryTemp") °C"
  echo "        nabití: $(printf "%5.1f" "$batteryCap") kWh $(progress_bar $batterySoC 100)"
  if ((batteryPower >= 0)); then
    printf "      nabíjení: $colorPositive$(printf "%5d" "$batteryPower") W$colorDefault\n"
  else
    printf "      vybíjení: $colorNegative$(printf "%5d" "$batteryPower") W$colorDefault\n"
  fi
  echo "   dnes nabito: $(printf "%5.1f" "$totalChargedIn") kWh"
  echo "        vybito: $(printf "%5.1f" "$totalChargedOut") kWh"
  echo ""
  echo -ne "$divLine"
  echo -e "\033[20C STŘÍDAČ [$inverterMode] "
  echo "                                       $(printf "%5d" "$inverterTemp") °C"
  echo "         výkon: $(printf "%5d" "$inverterPower") W   $(progress_bar $inverterPower $SolaxmaxPower)"
  echo "            L1: $(printf "%5d" "$llph1") W"
  echo "            L2: $(printf "%5d" "$llph2") W"
  echo "            L3: $(printf "%5d" "$llph3") W"
  echo "dnes výroba AC: $(printf "%5.1f" "$totalProductionInclBatt") kWh"
  echo ""
  echo -ne "$divLine"
  echo -e "\033[20C DISTRIBUČNÍ SÍŤ "
  if ((feedInPower < 0)); then
    printf "         odběr: $colorNegative$(printf "%5d" "$feedInPower") W$colorDefault\n"
  else
    printf "       dodávka: $colorPositive$(printf "%5d" "$feedInPower") W$colorDefault\n"
  fi
  if (( AZRPowerL1 >= 0 )); then
    printf "    L1 dodávka: \e[36m$(printf "%5d" $AZRPowerL1 ) W\e[0m\n"
  else
    printf "      L1 odběr: \e[31m$(printf "%5d" $AZRPowerL1 ) W\e[0m\n"
  fi
  if (( AZRPowerL2 >= 0)); then
    printf "    L2 dodávka: \e[36m$(printf "%5d" $AZRPowerL2 ) W\e[0m\n"
  else
    printf "      L2 odběr: \e[31m$(printf "%5d" $AZRPowerL2 ) W\e[0m\n"
  fi
  if (( AZRPowerL3 >= 0)); then
    printf "    L3 dodávka: \e[36m$(printf "%5d" $AZRPowerL3 ) W\e[0m\n"
  else
    printf "      L3 odběr: \e[31m$(printf "%5d" $AZRPowerL3 ) W\e[0m\n"
  fi
  AZRPowerTotal=$(($AZRPowerL1 + $AZRPowerL2 + $AZRPowerL3))
   printf "      L1+L2+L3: \e[31m$(printf "%5d" $AZRPowerTotal ) W\e[0m\n"

  echo " dnes odebráno: $(printf "%5.2f" "$totalGridIn") kWh"
  echo "        dodáno: $(printf "%5.2f" "$totalGridOut") kWh"
  echo ""
  echo -ne "$divLine"
  echo -e "\033[20C DŮM "
  echo "aktuální odběr: $(printf "%5d" "$loadHome") W   $(progress_bar $loadHome $SolaxHouseMaxLoad)"
  echo " dnes spotřeba: $(printf "%5.1f" "$totalConsumption") kWh"
  echo "  soběstačnost:   $(printf "%3d" "$selfSufficiencyRate") %   $(progress_bar $selfSufficiencyRate 100)"
  echo ""