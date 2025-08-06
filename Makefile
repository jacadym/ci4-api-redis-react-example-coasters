.DEFAULT_GOAL := help

CONTAINER_NAME_DEV = bb-coaster-app-dev
CONTAINER_NAME_PROD = bb-coaster-app-prod
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

.PHONY: exec-dev
exec-dev: ## Shell into development container
	@docker exec -it --user www-data $(CONTAINER_NAME_DEV) bash

.PHONY: root-dev
root-dev: ## Shell into development container as root
	@docker exec -it $(CONTAINER_NAME_DEV) bash

.PHONY: exec-prod
exec-prod: ## Shell into production container
	@docker exec -it --user www-data $(CONTAINER_NAME_PROD) bash

.PHONY: composer-install-dev
composer-install-dev: ## Composer install in development
	@docker exec -it --user www-data $(CONTAINER_NAME_DEV) composer install

.PHONY: composer-install-prod
composer-install-prod: ## Composer install in production
	@docker exec -it --user www-data $(CONTAINER_NAME_PROD) composer install --no-dev

.PHONY: composer-install
composer-install: composer-install-dev composer-install-prod ## Composer install

.PHONY: composer-update-dev
composer-update-dev: ## Composer update in development
	@docker exec -it --user www-data $(CONTAINER_NAME_DEV) composer update

.PHONY: composer-update-prod
composer-update-prod: ## Composer update in production
	@docker exec -it --user www-data $(CONTAINER_NAME_PROD) composer update --no-dev

.PHONY: cache-dev
cache-dev: ## Clears cache in development
	@docker exec -it --user www-data $(CONTAINER_NAME_DEV) php spark cache:clear

.PHONY: cache-prod
cache-prod: ## Clears cache in production
	@docker exec -it --user www-data $(CONTAINER_NAME_PROD) php spark cache:clear

.PHONY: cache
cache: | cache-dev cache-prod ## Clears cache

.PHONY: help
help: ## Alphabetically ordered help
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-22s\033[0m %s\n", $$1, $$2}'
