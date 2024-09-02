CURRENT_PATH := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))

DockerCommand := docker compose -p samirify-ftp-hex -f $(CURRENT_PATH)docker/docker-compose.yml

.PHONY: install up down build shell worker unit-test behat-test

install: 
	cp ./app/.env.example ./app/.env
	$(DockerCommand) --env-file $(CURRENT_PATH)app/.env up -d --build
	docker exec -it php-app composer install

up:
	$(DockerCommand) up -d

down:
	$(DockerCommand) down

build:
	$(DockerCommand) build

shell:
	docker exec -it php-app bash

worker:
	docker exec -it php-app php bin/console messenger:consume

rabbitmq:
	docker exec -it rabbitmq bash

unit-test:
	docker exec -it php-app vendor/bin/phpunit

behat-test:
	docker exec -it php-app vendor/bin/behat