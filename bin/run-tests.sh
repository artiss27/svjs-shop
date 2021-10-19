#!/usr/bin/env bash
export APP_ENV=test

echo $APP_ENV
#bin/console doctrine:schema:update --dump-sql
bin/console doctrine:database:drop --force
bin/console doctrine:database:create
bin/console doctrine:schema:update --force
bin/console doctrine:fixtures:load -n

php ./vendor/bin/phpunit --testdox --group unit,integration,functional,functional-selenium
#php ./vendor/bin/phpunit --testdox --group functional-selenium