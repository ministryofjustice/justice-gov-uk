.PHONY: d-shell

kube := kind
k8s_prt := 8080:80
k8s_nsp := default
k8s_pod := kubectl -n $(k8s_nsp) get pod -l app=justice-gov-uk-local -o jsonpath="{.items[0].metadata.name}"

init: setup run

d-compose: local-stop
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
run: local-stop dory
	docker compose up

# Launch the application, open browser, no stdout
launch: local-stop dory
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

# Starts the application, includes the local-ssh container for migrations.
migrate:
	docker compose --profile local-ssh up

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
	@echo "\n-->  Building local Nginx  <---------------------------|\n"; sleep 3;
	docker image build -t justice-nginx:latest --target nginx .

# FastCGI Process Manager for PHP
# https://www.php.net/manual/en/install.fpm.php
# https://www.plesk.com/blog/various/php-fpm-the-future-of-php-handling/
build-fpm:
	@echo "\n-->  Building local FPM  <---------------------------|\n"; sleep 3;
	docker image build -t justice-fpm:latest --target build-fpm .

build: build-fpm build-nginx
	@if [ ${kube} == 'kind' ]; then kind load docker-image justice-fpm:latest; kind load docker-image justice-nginx:latest; fi
	@echo "\n-->  Done.\n"

deploy: clear
	@echo "\n-->  Local Kubernetes deployment  <---------------------------|\n"
	kubectl apply -f deploy/local

cluster:
	@if [ "${kube}" != 'kind' ]; then echo "\n-->  Please, activate the kind cluster to assist in local app development on Kubernetes"; echo "-->  Amend the variable named kube on line 3 in Makefile to read 'kind' (without quotes)"; echo "-->  ... or, install kind from scratch: https://kind.sigs.k8s.io/docs/user/quick-start/#installation \n"; sleep 8; fi
	@if [ "${kube}" == 'kind' ]; then kind create cluster --config=deploy/config/local/cluster.yml; kubectl apply -f https://projectcontour.io/quickstart/contour.yaml; fi
	@if [ "${kube}" == 'kind' ]; then kubectl patch daemonsets -n projectcontour envoy -p '{"spec":{"template":{"spec":{"nodeSelector":{"ingress-ready":"true"},"tolerations":[{"key":"node-role.kubernetes.io/control-plane","operator":"Equal","effect":"NoSchedule"},{"key":"node-role.kubernetes.io/master","operator":"Equal","effect":"NoSchedule"}]}}}}'; fi

local-kube: local-kube-start clear cluster local-kube-build
	@if [ "${kube}" == 'kind' ]; then echo "\n-->  Verifying..."; echo "-->  Use ctrl + C to exit when ready\n"; kubectl get pods -w; fi

local-kube-start:
	@if [ -n "$(docker ps | grep dory_dnsmasq)" ]; then dory down; fi # lets make sure port 80 is free
	@docker container start kind-control-plane

local-stop:
	@echo "\n-->  Checking if we should stop the kind-control-plane container..."
	@docker container stop kind-control-plane || true >/dev/null 2>&1
	@echo "-->  Done.\n"

local-kube-build: build deploy
	@if [ "${kube}" == 'kind' ]; then echo "\n-->  Verifying..."; echo "-->  Use ctrl + C to exit when ready\n"; kubectl get pods -w; fi

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

