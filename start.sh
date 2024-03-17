
if test -f .env; then
  echo ".env File already exists."

else
  read -p "IP adresa invertoru/WiFi donglu: " SolaxUrl
  read -p "Heslo invertoru/WiFi Pocket: " SolaxPasswd
  read -p "IP adresa AZ Routeru: " AZRouterPowerUrl
  read -p "Interval obnovy data(polling sec.): " SolaxDataPollInterval

  cp sample.env .env 

    sed -i .bak "s/^SolaxUrl.*/SolaxUrl=http:\/\/$SolaxUrl/g" .env
    sed -i .bak "s/^SolaxPasswd.*/SolaxPasswd=$SolaxPasswd/g" .env
    sed -i .bak "s/^AZRouterPowerUrl.*/AZRouterPowerUrl=http:\/\/$AZRouterPowerUrl\/api\/v1\/power/g" .env
    sed -i .bak "s/^AZRouterDevicesURL.*/AZRouterDevicesURL=http:\/\/$AZRouterPowerUrl\/api\/v1\/devices/g" .env
    sed -i .bak "s/^SolaxDataPollInterval.*/SolaxDataPollInterval=$SolaxDataPollInterval/g" .env

  rm .env.bak 

fi

 docker-compose down && docker-compose up --build -d

# init: atart over from the scratch:
#rm .env.bak 
#rm .env 
#docker-compose down -v --remove-orphans && docker-compose up --build -d