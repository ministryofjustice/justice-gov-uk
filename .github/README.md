<div align="center">

<br>

<img alt="MoJ logo" src="https://moj-logos.s3.eu-west-2.amazonaws.com/moj-uk-logo.png" width="200">

<br>

# Justice GovUK

[![Standards Icon]][Standards Link]
[![License Icon]][License Link]

</div>

## Summary

This code-base is the website for the Ministry of Justice which hosts Civil and Family Procedure Committee Rules content
only.

> Nb. `README.md` is located in `.github/`, the preferred location for a clean repository.

## Architecture

A visual overview of the architectural layout of the development application.

![Container architecture](https://docs.google.com/drawings/d/e/2PACX-1vSI0GFWU3Gw2gmARPqtQ8_hFPOz-9IE5XkM3-Zb5KpX8qfelO2VwErIbRIbeb7_L5vNwcGc7FfeSz38/pub?w=960&h=720)

## Installation for development

The application uses Docker. This repository provides two separate local test environments, namely:

1. Docker Compose
2. Kubernetes

Where `docker compose` provides a pre-production environment to develop features and apply upgrades, Kubernetes allows
us to test and debug our deployments to the Cloud Platform.

### Setup

In a terminal, move to the directory where you want to install the application. You may then run:

```bash
git clone https://github.com/ministryofjustice/justice-gov-uk.git
```

Change directories:

```bash
cd justice-gov-uk
```

Next, depending on the environment you would like to launch, do one of the following.

### 1. Docker Compose

This environment has been set up to develop and improve the application.

#### Requirements

- [Docker](https://www.docker.com/products/docker-desktop/)
- [Dory](https://formulae.brew.sh/formula/dory) (by FreedomBen) _auto-install available_

The following make command will get you up and running.

It creates the environment, starts all services and opens a command prompt on the container that houses our PHP code,
the service is called `php-fpm`:

```bash
make
```

During the `make` process, the Dory proxy will attempt to install. You will be guided though an installation, if needed.

### Services

You will have five services running with different access points. They are:

**Nginx**<br>
http://justice.docker/

**PHP-FPM**<br>

```bash
make bash
```

On first use, the application will need initializing with the following command.

```bash
composer install
```

**Node**<br>
This service watches and compiles our assets, no need to access. The output of this service is available on STDOUT.

**MariaDB**<br>
Internally accessed by PHP-FPM on port 3306

**PHPMyAdmin**<br>
http://justice.docker:9191/ <br>
Login details located in `docker-compose.yml`

> There is no need to install application software on your computer.<br>
> All required software is built within the services and all services are ephemeral.

#### Volumes

There are multiple volume mounts created in this project and shared across the services.
The approach has been taken to speed up and optimise the development experience.

### 2. Kubernetes

This environment is useful to test Kubernetes deployment scripts.

Local setup attempts to get as close to development on Cloud Platform as possible, with a production-first approach.

#### Requirements

- [Docker](https://www.docker.com/products/docker-desktop/)
- [kubectl](https://kubernetes.io/docs/tasks/tools/)
- [Kind](https://kind.sigs.k8s.io/docs/user/quick-start/#installation)
- Hosts file update, you could...
  > `sudo nano /etc/hosts`<br>... on a new line, add: `127.0.0.1	justice.local`

Once the above requirements have been met, we are able to launch our application by executing the following make
command:

```bash
make local-kube
```

The following will take place:

1. If running; the Dory proxy is stopped
2. A Kind cluster is created with configuration from: `deploy/config/local/cluster.yml`
3. The cluster Ingress is configured
4. Nginx and PHP-FPM images are built
5. Images are transferred to the Kind Control Plane
6. Local deployment is applied using `kubectl apply -f deploy/local`
7. Verifies pods using `kubectl get pods -w`

Access the running application here:
**http://justice.local/**

#### Volumes

In the MariaDB YAML file you will notice a persistent volume claim. This will assist you in keeping application data,
preventing you from having to reinstall WordPress every time you stop and start the service.

### Secrets

[Most secrets are managed via GitHub settings](https://github.com/ministryofjustice/justice-gov-uk/settings/secrets/actions)

#### WP Keys & Salts

It is the intention that WordPress keys and salts are auto generated, before the initial GHA
build stage. Lots of testing occurred yet the result wasn't desired; dynamic secrets could not be hidden in
the log outputs. Due to this, secrets are managed in settings.

### Kubernetes - quick command reference

```bash
# Make interaction a little easier; we can create repeatable
# variables, our namespace is the same name as the app, defined
# in ./deploy/development/deployment.tpl
#
# If interacting with a different stack, change the NSP var.
# For example;
# - production, change to 'justice-gov-uk-prod'

# Set some vars, gets the first available pod
NSP="justice-gov-uk-dev"; \
POD=$(kubectl -n $NSP get pod -l app=$NSP -o jsonpath="{.items[0].metadata.name}");

# Local interaction is a little different:
# - local, change NSP to `default` and app to `justice-gov-uk-local`
NSP="default"; \
POD=$(kubectl -n $NSP get pod -l app=justice-gov-uk-local -o jsonpath="{.items[0].metadata.name}");
```

After setting the above variables (via `copy -> paste -> execute`) the following blocks of commands will work
using `copy -> paste -> execute` too.

```bash
# list available pods and their status for the namespace
kubectl get pods -n $NSP

# watch for updates, add the -w flag
kubectl get pods -w -n $NSP

# describe the first available pod
kubectl describe pods -n $NSP

# monitor the system log of the first pod container
kubectl logs -f $POD -n $NSP

# monitor the system log of the fpm container
kubectl logs -f $POD -n $NSP fpm

# open an interactive shell on an active pod
kubectl exec -it $POD -n $NSP -- ash

# open an interactive shell on the FPM container
kubectl exec -it $POD -n $NSP -c fpm -- ash
```

## Testing

### Summary

The test suites for this project use:

[Codeception](https://codeception.com/)

> Codeception collects and shares best practices and solutions for testing PHP web applications.

[wp-browser](https://wpbrowser.wptestkit.dev/)

> The wp-browser library provides a set of Codeception modules and middleware to enable the testing of WordPress sites, plugins and themes.

WP_Mock is used in unit tests to mock WordPress functions and classes.

The suites are intended to include Unit Tests for functions & classes, all the way to Acceptance Tests with automated browsing.

### Spec container

The spec container is used to keep the test environment separate from the application environment. 
Where necessary, packages are installed to the spec container to support the test suites.  
e.g. pdo_mysql

To access a terminal on the spec container, run: `make spec-bash`.

### Container dependencies

The spec container is dependent on the various other containers.

| Test type               | Dependencies                                      |
| ----------------------- | ------------------------------------------------- |
| Unit                    | App files                                         |
| Integration             | App files, MariaDB, Minio, Local CDN              |
| Acceptance & Functional | PHP-FPM, Nginx, MariaDB, Minio, Local CDN, Chrome |

### Installation log
 
The test packages have been installed by running `composer require --dev lucatume/wp-browser` from inside the spec container.

wp-browser was initialised by running `vendor/bin/codecept init wpbrowser` from inside the spec container.

When prompted to use a portable configuration based on PHP built-in server, Chromedriver and SQLite, 
the answer was no.  
This is because these services are already provided by the docker-compose environment.

Changes to the default installation include:

- rename `tests` directory to `spec`.
- move `codeception.yml` from the project root to the spec directory.
- amend file paths within `codeception.yml` accordingly.
- add scripts to the `composer.json` file to run the tests, e.g. running `vendor/bin/codecept` with `-c` to specify the codeception config file.
- [Simple start with Acceptance Testing for WordPress](https://wp-punk.com/simple-start-with-acceptance-testing-for-wordpress/) was followed to add acceptance tests.

> To avoid coupling between tests. We should run each test separately from the default state. In our case, the default state is a default state for a database. So, let’s create a separate acceptance_db database, activate the tested WordPress plugin, install the tested plugin/theme, and export the database to the spec/tests/Support/Data/dump.sql.

```bash
# If this is run from the spec container, the following commands will install WordPress and activate all plugins, and use test_ as the database prefix.
wp core install --url="http://justice.docker" --title="Test" --admin_user="test" --admin_password="test" --admin_email="example@justice.docker" --skip-email
wp plugin activate --all

mysqldump --host="mariadb" --user="mysql" --password="mysql" justice > spec/Support/Data/dump.sql
```

<!-- License badge -->

[License Link]: https://github.com/ministryofjustice/justice-gov-uk/blob/main/LICENSE 'License.'

[License Icon]: https://img.shields.io/github/license/ministryofjustice/justice-gov-uk?style=for-the-badge

<!-- Architecture image -->

[Arch Image]: https://docs.google.com/drawings/d/1BlzbAmZC2lfS3H2wdnNpT229QeZMpNRNYA84mKXAOec/edit?usp=sharing

<!-- MoJ Standards -->

[Standards Link]: https://operations-engineering-reports.cloud-platform.service.justice.gov.uk/public-report/justice-gov-uk 'Repo standards badge.'

[Standards Icon]: https://img.shields.io/endpoint?labelColor=231f20&color=005ea5&style=for-the-badge&url=https%3A%2F%2Foperations-engineering-reports.cloud-platform.service.justice.gov.uk%2Fapi%2Fv1%2Fcompliant_public_repositories%2Fendpoint%2Fjustice-gov-uk&logo=data:image/png;base64,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
