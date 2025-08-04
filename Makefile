.DEFAULT_GOAL := help

CONTAINER_NAME = bb-coaster-app
NETWORK = coaster-network


.PHONY: network
network: ## Create network
	@[ $(shell docker network ls --filter name="^$(NETWORK)$$" --format="{{.ID}}") ] || ( echo "Creating external network ..."; docker network create $(NETWORK) )

.PHONY: start
start: network ## Docker compose up
	@docker-compose up -d

.PHONY: stop
stop: ## Docker compose stop
	@docker-compose stop

.PHONY: restart
restart: stop start ## Restart docker compose

.PHONY: down
down: ## Docker compose down
	@docker-compose down -v

.PHONY: init
init: | start composer-install cache ## Init project

.PHONY: exec
exec: ## Shell into container
	@docker exec -it --user www-data $(CONTAINER_NAME) bash

.PHONY: composer-install
composer-install: ## Composer install
	@docker exec -it --user www-data $(CONTAINER_NAME) composer install

.PHONY: composer-update
composer-update: ## Composer update
	@docker exec -it --user www-data $(CONTAINER_NAME) composer update

.PHONY: cache
cache: ## Clears cache
	@docker exec -it --user www-data $(CONTAINER_NAME) /var/www/html/bin/console cache:clear


.PHONY: help
help: ## Alphabetically ordered help
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-20s\033[0m %s\n", $$1, $$2}'
