COMPOSE=docker compose
PHP=$(COMPOSE) exec php
COMPOSER=$(PHP) composer
ARTISAN=$(PHP) php artisan
NODE=$(COMPOSE) run node

up:
	@${COMPOSE} up -d

down:
	@${COMPOSE} down

migrate:
	@${ARTISAN} migrate

install:
	@${COMPOSER} install
	@${NODE} npm install
	@${NODE} npm run build

