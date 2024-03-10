if test -f .env; then
  echo ".env File already exists."
else
  cp sample.env .env  
fi

 
docker-compose down && docker-compose up --build -d

# init: atart over from the scratch:
#docker-compose down -v --remove-orphans && docker-compose up --build -d