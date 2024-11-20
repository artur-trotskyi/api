docker stop $(docker ps -q)
docker compose up -d
docker compose exec php-fpm bash

./vendor/bin/pint


TODO: need to update auth api-doc
