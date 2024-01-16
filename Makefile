.PHONY: d-shell

kube := kind # enables importing our docker images into the kind-control-plane
k8s_prt := 8080:80
k8s_nsp := justice-gov-uk-local
k8s_pod := kubectl -n $(k8s_nsp) get pod -l app=$(k8s_nsp) -o jsonpath="{.items[0].metadata.name}"

init: setup run

d-compose:
	docker compose up -d nginx phpmyadmin
	docker compose run --service-ports --rm --entrypoint=bash php-fpm

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

# Open a bash shell on the running php container
bash:
	docker compose exec php-fpm bash

# Open a bash shell on the running php container
bash-nginx:
	docker compose exec nginx ash

# Run tests
test:
	composer test

# Fix tests
test-fixes:
	composer test-fix


#####
## Mock production, K8S deployment
#####
build-nginx:
	@echo "\n-->  Building local Nginx  <---------------------------|\n"
	docker image build -t justice-nginx:latest --target nginx .

# FastCGI Process Manager for PHP
# https://www.php.net/manual/en/install.fpm.php
# https://www.plesk.com/blog/various/php-fpm-the-future-of-php-handling/
build-fpm:
	@echo "\n-->  Building local FPM  <---------------------------|\n"
	docker image build -t justice-fpm:latest --target build-fpm .

build: build-fpm build-nginx
	@if [ ${kube} == 'kind' ]; then kind load docker-image justice-fpm:latest; kind load docker-image justice-nginx:latest;  fi
	@echo "\n-->  Done.\n"

deploy: clear
	@echo "\n-->  Local Kubernetes deployment  <---------------------------|\n"
	kubectl apply -f deploy/local

cluster:
	@if [ ${kube} == 'kind' ]; then kind create cluster --config=./deploy/config/local/cluster.yml

local-kube: clear cluster build deploy

clear:
	@clear

log-nginx: clear
	@echo "\n-->  NGINX LOGS  <---------------------------|\n"
	@$(k8s_pod) | xargs -t kubectl logs -f -n $(k8s_nsp) -c nginx

log-fpm: clear
	@echo "\n-->  FPM PHP LOGS  <-------------------------|\n"
	@$(k8s_pod) | xargs kubectl logs -f -n $(k8s_nsp) -c fpm

logs-nginx-flash:
	@echo "\n-->  NGINX LOGS  <---------------------------|\n"
	@$(k8s_pod) | xargs kubectl logs -n $(k8s_nsp) -c nginx

logs-fpm-flash:
	@echo "\n-->  FPM PHP LOGS  <-------------------------|\n"
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

