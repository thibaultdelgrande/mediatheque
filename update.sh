#!/bin/bash
set -e

git pull

composer install --no-dev --optimize-autoloader

php bin/console asset-map:compile

php bin/console doctrine:migrations:migrate