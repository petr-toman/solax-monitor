if test -f .env; then
  echo ".env File already exists."
else
  cp sample.env .env  
fi

 
docker-compose down && docker-compose up --build -d

# init: from the scratch:
#docker-compose down -v --remove-orphans && docker-compose up --build -d
