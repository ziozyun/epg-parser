up:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		echo "Помилка: up запускається лише ззовні контейнера."; \
		exit 1; \
	else \
		docker compose up -d; \
	fi

down:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		echo "Помилка: down запускається лише ззовні контейнера."; \
		exit 1; \
	else \
		docker compose down; \
	fi

clean:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		echo "Помилка: clean запускається лише ззовні контейнера."; \
		exit 1; \
	else \
		docker compose down -v; \
	fi

migrate:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		echo "Помилка: міграції запускаються лише ззовні контейнера."; \
		exit 1; \
	else \
		docker compose --profile tools run --rm flyway; \
	fi

ensure-app:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		echo "Помилка: ensure-app запускається лише ззовні контейнера."; \
		exit 1; \
	else \
		if [ "$$(docker compose ps -q app 2>/dev/null | wc -l)" -eq 0 ]; then \
			echo "Стартую app та db..."; \
			docker compose up -d app db; \
		elif [ "$$(docker compose ps --status running -q app 2>/dev/null | wc -l)" -eq 0 ]; then \
			echo "Запускаю app..."; \
			docker compose up -d app; \
		fi; \
	fi

build:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		composer install; \
	else \
		$(MAKE) ensure-app; \
		docker compose exec app composer install; \
	fi

import:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		php bin/parse.php $(ARGS); \
	else \
		$(MAKE) ensure-app; \
		docker compose exec app php bin/parse.php $(ARGS); \
	fi

report:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		php bin/report.php $(ARGS); \
	else \
		$(MAKE) ensure-app; \
		docker compose exec app php bin/report.php $(ARGS); \
	fi

format:
	@if [ -n "$$EPG_IN_CONTAINER" ]; then \
		vendor/bin/php-cs-fixer fix --using-cache=no; \
	else \
		$(MAKE) ensure-app; \
		docker compose exec app vendor/bin/php-cs-fixer fix --using-cache=no; \
	fi
