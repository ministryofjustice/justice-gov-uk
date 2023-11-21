.PHONY: d-shell

init: setup run

d-compose:
	docker compose up -d nginx phpmyadmin
	docker compose run --service-ports --rm --entrypoint=bash php

d-shell: setup d-compose

setup:
	@chmod +x ./bin/*
	@[ -f "./.env" ] || cp .env.example .env
	@echo "http://127.0.0.1:8080/" > public/hot

restart:
	@docker compose down app
	@make d-compose

down:
	docker compose down

node-assets:
	npm install
	npm run watch

nginx:
	docker compose exec --workdir /var/www/html nginx bash

node:
	docker compose exec --workdir /node node bash

# Remove ignored git files – e.g. composer dependencies and built theme assets
# But keep .env file, .idea directory (PhpStorm config), and uploaded media files
clean:
	@if [ -d ".git" ]; then git clean -xdf --exclude ".env" --exclude ".idea" --exclude "public/app/uploads"; fi
	@clear

# Remove all ignored git files (including media files)
deep-clean:
	@if [ -d ".git" ]; then git clean -xdf --exclude ".idea"; fi

# Remove ALL docker images on the system
docker-clean:
	bin/local-docker-clean.sh

# Run the application
run: dory
	docker compose up

# Launch the application, open browser, no stdout
launch: dory
	bin/local-launch.sh

# Start the Dory Proxy
dory:
	@chmod +x ./bin/local-dory-check.sh && ./bin/local-dory-check.sh

# Open a bash shell on the running container
bash:
	docker compose exec php bash

# Run tests
test:
	composer test

# Fix tests
test-fixes:
	composer test-fix


#####
## Production CI mock
#####

build-nginx:
	docker image build -f ops/nginx/Dockerfile -t nginx:latest --target nginx .

build-app:
	docker image build -f ops/app/Dockerfile -t app:latest --target app .

build:
	make build-fpm
	make build-nginx

ks-apply:
	kubectl apply -f resources/ops/kubernetes
