#/**
# * TangoMan Kaamelott Videoboard Backend
# *
# * @version  0.1.0
# * @author   "Matthias Morin" <mat@tangoman.io>
# * @license  MIT
# */

.PHONY: help up shell open restart import update export reset build start stop status network install uninstall composer database cache nuke own fixtures tests

#--------------------------------------------------
# Parameters
#--------------------------------------------------

# app environment
env=prod

#--------------------------------------------------
# Colors
#--------------------------------------------------

TITLE     = \033[1;42m
CAPTION   = \033[1;44m
BOLD      = \033[1;34m
LABEL     = \033[1;32m
DANGER    = \033[31m
SUCCESS   = \033[32m
WARNING   = \033[33m
SECONDARY = \033[34m
INFO      = \033[35m
PRIMARY   = \033[36m
DEFAULT   = \033[0m
NL        = \033[0m\n

#--------------------------------------------------
# Symfony
#--------------------------------------------------

# get correct console executable
CONSOLE=$(shell if [ -f ./app/console ]; then echo './app/console'; elif [ -f ./bin/console ]; then echo './bin/console'; fi)
# get correct public folder
PUBLIC=$(shell if [ -d ./web ]; then echo './web'; elif [ -d ./public ]; then echo './public'; else echo './'; fi)
# get current php version
PHP_VERSION=$(shell php -v | grep -oP 'PHP\s\d+\.\d+' | sed s/'PHP '//)
# symfony version
VERSION=$(shell ${CONSOLE} --version)

#--------------------------------------------------
# System
#--------------------------------------------------

# Local operating system (Windows_NT, Darwin, Linux)
ifeq ($(OS),Windows_NT)
	SYSTEM=$(OS)
else
	SYSTEM=$(shell uname -s)
endif

#--------------------------------------------------
# Help
#--------------------------------------------------

## Print this help
help:
	@printf "${TITLE} TangoMan $(shell basename ${CURDIR}) ${NL}\n"

	@printf "${CAPTION} Infos:${NL}"
	@printf "${PRIMARY} %-12s${INFO} %s${NL}" "php"     "${PHP_VERSION}"
	@printf "${PRIMARY} %-12s${INFO} %s${NL}" "symfony" "${VERSION}"
	@printf "${NL}"

	@printf "${CAPTION} Description:${NL}"
	@printf "${WARNING} TangoMan $(shell basename ${CURDIR}) ${NL}\n"

	@printf "${CAPTION} Usage:${NL}"
	@printf "${WARNING} make [command] `awk -F '?' '/^[ \t]+?[a-zA-Z0-9_-]+[ \t]+?\?=/{gsub(/[ \t]+/,"");printf"%s=[%s]\n",$$1,$$1}' ${MAKEFILE_LIST}|sort|uniq|tr '\n' ' '`${NL}\n"

	@printf "${CAPTION} Config:${NL}"
	$(eval CONFIG:=$(shell awk -F '?' '/^[ \t]+?[a-zA-Z0-9_-]+[ \t]+?\?=/{gsub(/[ \t]+/,"");printf"$${PRIMARY}%-12s$${DEFAULT} $${INFO}$${%s}$${NL}\n",$$1,$$1}' ${MAKEFILE_LIST}|sort|uniq))
	@printf " ${CONFIG}\n"

	@printf "${CAPTION} Commands:${NL}"
	@awk '/^### /{printf"\n${BOLD}%s${NL}",substr($$0,5)} \
	/^[a-zA-Z0-9_-]+:/{HELP="";if(match(PREV,/^## /))HELP=substr(PREV, 4); \
		printf " ${LABEL}%-12s${DEFAULT} ${PRIMARY}%s${NL}",substr($$1,0,index($$1,":")),HELP \
	}{PREV=$$0}' ${MAKEFILE_LIST}

##################################################
### Symfony (Docker)
##################################################

## Build, start docker, composer install, create database, import data, and serve
up: network build start install import

## Open a terminal in the php container
shell:
	@printf "${INFO}docker-compose exec php sh${NL}"
	@docker-compose exec php sh

## Open in default browser
open:
	@printf "${INFO}nohup xdg-open `docker inspect $(shell basename ${CURDIR}) --format 'http://{{.NetworkSettings.Networks.tango.IPAddress}}/api/docs' 2>/dev/null` >/dev/null 2>&1${NL}"
	@nohup xdg-open `docker inspect $(shell basename ${CURDIR}) --format 'http://{{.NetworkSettings.Networks.tango.IPAddress}}/api/docs' 2>/dev/null` >/dev/null 2>&1

## Restart app and clear cache
restart: stop start cache

## Drop database, clear cache and re-import data
reset: database import cache

##################################################
### App Import/Export (Docker)
##################################################

## Import data from json/csv
import:
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:import -f people.json --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:import -f people.json --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:import -f episodes.csv --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:import -f episodes.csv --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:import -f clips.csv --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:import -f clips.csv --env=${env}"

## Import and update
update:
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:update -f clips.csv -p name --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:update -f clips.csv -p name --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:update -o -f people.csv -p alternateName --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:update -o -f people.csv -p alternateName --env=${env}"

## Export data to json/csv
export:
	-@printf "${INFO}rm ./assets/exports/*${NL}"
	-@rm ./assets/exports/*

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x clip:csv -g read:clip --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x clip:csv -g read:clip --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x clip:json -g read:clip --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x clip:json -g read:clip --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x episode:csv -g read:episode --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x episode:csv -g read:episode --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x episode:json -g read:episode --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x episode:json -g read:episode --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x person:csv -g read:person --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x person:csv -g read:person --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x person:json -g read:person --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x person:json -g read:person --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x tag:csv -g read:tag --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x tag:csv -g read:tag --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} app:export -x tag:json -g read:tag --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} app:export -x tag:json -g read:tag --env=${env}"

	@printf "${INFO}sudo chown -R `whoami`:`whoami` ./assets/exports${NL}"
	@sudo chown -R `whoami`:`whoami` ./assets/exports

##################################################
### Docker-Compose Container
##################################################

## Build container
build:
	@printf "${INFO}docker-compose build${NL}"
	@docker-compose build

## Start the environment
start:
	@printf "${INFO}docker-compose up --detach --remove-orphans${NL}"
	@docker-compose up --detach --remove-orphans

## Stop containers
stop:
	@printf "${INFO}docker-compose stop${NL}"
	@docker-compose stop

## List containers
status:
	@printf "${INFO}docker-compose ps${NL}"
	@docker-compose ps

##################################################
### Docker Network
##################################################

## Create "tango" network
network:
	@printf "${INFO}docker network create tango${NL}"
	-@docker network create tango

##################################################
### Symfony App (Docker)
##################################################

## Install Symfony application in docker
install:
	@make --no-print-directory composer env=${env}
	@make --no-print-directory database env=${env}
ifeq ($(env),dev)
	@printf "${INFO}make --no-print-directory own env=dev${NL}"
	@make --no-print-directory own env=dev
endif

## Uninstall app completely
uninstall: stop
	@printf "${INFO}sudo rm -f ./.env.local${NL}"
	@sudo rm -f ./.env.local

	@printf "${INFO}sudo rm -f ./composer.lock${NL}"
	@sudo rm -f ./composer.lock

	@printf "${INFO}sudo rm -f ./symfony.lock${NL}"
	@sudo rm -f ./symfony.lock

	@printf "${INFO}sudo rm -f ./var/data.db${NL}"
	@sudo rm -f ./var/data.db

	@printf "${INFO}sudo rm -f ./var/dev.db${NL}"
	@sudo rm -f ./var/dev.db

	@printf "${INFO}sudo rm -f ./var/test.db${NL}"
	@sudo rm -f ./var/test.db

	@printf "${INFO}sudo rm -rf ./public/bundles${NL}"
	@sudo rm -rf ./public/bundles

	@printf "${INFO}sudo rm -rf ./bin/.phpunit${NL}"
	@sudo rm -rf ./bin/.phpunit

	@printf "${INFO}sudo rm -rf ./vendor${NL}"
	@sudo rm -rf ./vendor
	@make --no-print-directory nuke

## Composer install Symfony project
composer:
ifeq ($(env),dev)
	@printf "${INFO}cp .env.dev .env.local${NL}"
	@cp .env.dev .env.local
endif
	@printf "${INFO}docker-compose exec php sh -c \"composer install --no-interaction --optimize-autoloader --prefer-dist --working-dir=/www\"${NL}"
	@docker-compose exec php sh -c "composer install --no-interaction --optimize-autoloader --prefer-dist --working-dir=/www"

## Create database and schema
database:
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} doctrine:database:drop --force --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} doctrine:database:drop --force --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} doctrine:database:create --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} doctrine:database:create --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} doctrine:schema:create --dump-sql --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} doctrine:schema:create --dump-sql --env=${env}"

	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} doctrine:schema:create --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} doctrine:schema:create --env=${env}"

##############################################
### Symfony Cache (Docker)
##############################################

## Clean cache
cache:
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} cache:clear --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} cache:clear --env=${env}"
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} cache:warmup --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} cache:warmup --env=${env}"
ifeq ($(env),dev)
	@make --no-print-directory own
endif

## Force delete cache
nuke:
	@printf "${INFO}sudo rm -rf ./var/cache${NL}"
	@sudo rm -rf ./var/cache
	@printf "${INFO}mkdir ./var/cache${NL}"
	@mkdir ./var/cache

	@printf "${INFO}sudo rm -rf ./var/log${NL}"
	@sudo rm -rf ./var/log
	@printf "${INFO}mkdir ./var/log${NL}"
	@mkdir ./var/log

	@printf "${INFO}sudo rm -rf /var/cache/symfony/$(shell basename ${CURDIR})${NL}"
	@sudo rm -rf /var/cache/symfony/$(shell basename ${CURDIR})

	@printf "${INFO}sudo rm -rf /var/log/symfony/$(shell basename ${CURDIR})${NL}"
	@sudo rm -rf /var/log/symfony/$(shell basename ${CURDIR})

	@printf "${INFO}sudo rm -rf /var/log/nginx/$(shell basename ${CURDIR})${NL}"
	@sudo rm -rf /var/log/nginx/$(shell basename ${CURDIR})

## Own ./var
own:
ifeq ($(env),prod)
	$(eval OWNER=$(shell whoami))
	$(eval GROUP=$(shell whoami))
else
	$(eval OWNER=nobody)
	$(eval GROUP=nogroup)
endif
	@printf "${INFO}sudo chown -R ${OWNER}:${GROUP} ./var${NL}"
	-@sudo chown -R ${OWNER}:${GROUP} ./var
	@printf "${INFO}sudo chmod 777 -R ./var${NL}"
	-@sudo chmod 777 -R ./var

	@printf "${INFO}sudo chown ${OWNER}:${GROUP} ./var/data.db${NL}"
	-@sudo chown ${OWNER}:${GROUP} ./var/data.db
	@printf "${INFO}sudo chmod 664 ./var/data.db${NL}"
	-@sudo chmod 664 ./var/data.db

	@printf "${INFO}sudo chown ${OWNER}:${GROUP} ./var/dev.db${NL}"
	-@sudo chown ${OWNER}:${GROUP} ./var/dev.db
	@printf "${INFO}sudo chmod 664 ./var/dev.db${NL}"
	-@sudo chmod 664 ./var/dev.db

	@printf "${INFO}sudo chown ${OWNER}:${GROUP} ./var/test.db${NL}"
	-@sudo chown ${OWNER}:${GROUP} ./var/test.db
	@printf "${INFO}sudo chmod 664 ./var/test.db${NL}"
	-@sudo chmod 664 ./var/test.db

	@printf "${INFO}sudo chown -R ${OWNER}:${GROUP} /var/cache/symfony/$(shell basename ${CURDIR})${NL}"
	-@sudo chown -R ${OWNER}:${GROUP} /var/cache/symfony/$(shell basename ${CURDIR})
	@printf "${INFO}sudo chmod 777 -R /var/cache/symfony/$(shell basename ${CURDIR})${NL}"
	-@sudo chmod 777 -R /var/cache/symfony/$(shell basename ${CURDIR})

	@printf "${INFO}sudo chown -R ${OWNER}:${GROUP} /var/log/symfony/$(shell basename ${CURDIR})${NL}"
	-@sudo chown -R ${OWNER}:${GROUP} /var/log/symfony/$(shell basename ${CURDIR})
	@printf "${INFO}sudo chmod 777 -R /var/log/symfony/$(shell basename ${CURDIR})${NL}"
	-@sudo chmod 777 -R /var/log/symfony/$(shell basename ${CURDIR})

##############################################
### JWT Docker
##############################################

## Generate JWT key
generate-keys:
	@printf "${INFO}sudo rm -rf ./config/jwt${NL}"
	-@sudo rm -rf ./config/jwt

	@printf "${INFO}mkdir -p ./config/jwt${NL}"
	-@mkdir -p ./config/jwt

	@printf "${INFO}echo \"$(shell grep ^JWT_PASSPHRASE .env | cut -f 2 -d=)\" | openssl genpkey -out ./config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096${NL}"
	@echo "$(shell grep ^JWT_PASSPHRASE .env | cut -f 2 -d=)" | openssl genpkey -out ./config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

	@printf "${INFO}echo \"$(shell grep ^JWT_PASSPHRASE .env | cut -f 2 -d=)\" | openssl pkey -in ./config/jwt/private.pem -passin stdin -out ./config/jwt/public.pem -pubout${NL}"
	@echo "$(shell grep ^JWT_PASSPHRASE .env | cut -f 2 -d=)" | openssl pkey -in ./config/jwt/private.pem -passin stdin -out ./config/jwt/public.pem -pubout

##############################################
### PHPUnit Docker
##############################################

## Load Alice fixtures
fixtures:
	@printf "${INFO}docker-compose exec php sh -c \"${CONSOLE} hautelook:fixtures:load --no-interaction --env=${env}\"${NL}"
	@docker-compose exec php sh -c "${CONSOLE} hautelook:fixtures:load --no-interaction --env=${env}"

## Run tests
tests:
ifeq ($(shell test ! -f ./var/test.db && echo true),true)
	@make --no-print-directory database env=test
	@make --no-print-directory own env=test
endif
	@make --no-print-directory fixtures env=test
	@if [ -x ./bin/phpunit ]; then \
		printf "${INFO}docker-compose exec php sh -c \"php -d memory-limit=-1 ./bin/phpunit --stop-on-failure\"${NL}"; \
		docker-compose exec php sh -c "php -d memory-limit=-1 ./bin/phpunit --stop-on-failure"; \
	elif [ -x ./vendor/bin/phpunit ]; then \
		printf "${INFO}docker-compose exec php sh -c \"bash ./vendor/bin/phpunit --stop-on-failure\"${NL}"; \
		docker-compose exec php sh -c "bash ./vendor/bin/phpunit --stop-on-failure"; \
	elif [ -x ./vendor/bin/simple-phpunit ]; then \
		printf "${INFO}docker-compose exec php sh -c \"php -d memory-limit=-1 ./vendor/bin/simple-phpunit --stop-on-failure\"${NL}"; \
		docker-compose exec php sh -c "php -d memory-limit=-1 ./vendor/bin/simple-phpunit --stop-on-failure"; \
	else \
		printf "${DANGER}error: phpunit executable not found${NL}"; \
		exit 1; \
	fi
