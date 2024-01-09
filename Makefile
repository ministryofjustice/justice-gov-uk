.PHONY: d-shell

k8s_prt := 8080:80
k8s_nsp := justice-gov-uk-local
k8s_pod := kubectl -n $(k8s_nsp) get pod -l app=$(k8s_nsp) -o jsonpath="{.items[0].metadata.name}"

init: setup run

d-compose:
	docker compose up -d nginx phpmyadmin
	docker compose run --service-ports --rm --entrypoint=bash php

d-shell: setup d-compose

setup:
	@chmod +x ./bin/*
	@[ -f "./.env" ] || cp .env.example .env

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
	docker image build -t justice-nginx:latest --target nginx .

build-fpm:
	docker image build -t justice-fpm:latest --target build-fpm .

build: build-fpm build-nginx

minikube:
	@minikube start
	@eval $(minikube docker-env)
	@echo "\n--------------------------------------"
	@echo "Your terminal is connected to minikube"
	@echo "--------------------------------------\n"
	@make build

clear:
	@clear

log-nginx: clear
	@echo "\n-->  NGINX LOGS  <---------------------------\n"
	@$(k8s_pod) | xargs -t kubectl logs -f -n $(k8s_nsp) -c nginx

log-fpm: clear
	@echo "\n-->  FPM PHP LOGS  <-------------------------\n"
	@$(k8s_pod) | xargs kubectl logs -f -n $(k8s_nsp) -c fpm

logs-nginx-flash:
	@echo "\n-->  NGINX LOGS  <---------------------------\n"
	@$(k8s_pod) | xargs kubectl logs -n $(k8s_nsp) -c nginx

logs-fpm-flash:
	@echo "\n-->  FPM PHP LOGS  <-------------------------\n"
	@$(k8s_pod) | xargs kubectl logs -n $(k8s_nsp) -c fpm

logs: clear logs-fpm-flash logs-nginx-flash
	@echo "\n---------------------------------------------\n"

port-forward:
	@$(k8s_pod) | echo $$(cat -)" "$(k8s_prt) | xargs kubectl -n $(k8s_nsp) port-forward

apply:
	kubectl apply -f deploy/local

unapply:
	@$(k8s_pod) | xargs kubectl -n $(k8s_nsp) delete pod

apply-production:
	kubectl apply -f deploy/production

