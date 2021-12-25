# Cake OMS Backend

## Configure project name, environment and HTTP Hosts

```
vim .env.default
```

## Start project

```
./start
```

All `docker-compose up` options available in `./start`
Additional Options for `./start`

`--skip-install` to skip install dependencies 

E.g. 

`./start -d` to start in detach mode
`./start -d --force-recreate` to start in detach mode and recreate
`./start --skip-install -d` to start docker as daemon mode and skip modules installation

Start project with no-dev option - simulate to production environment

`./start --no-dev`

Or

`./docker-compose --no-dev up -d --force-recreate --remove-orphans` to start built docker container

## Stop project

```
./stop
```

> Note: Don't run stop unless you want to remove entries docker images and volumes inside your local machine
> Alternative way: can be done is run `./docker-compose down` to remove all docker containers, It will leave docker built images and next time `./docker-compose up -d` can be used to create and start containers again
> See `docker-guide.md` for more docker commands

## Global service containers

You can add global service containers like `prerender`, `imaginary`, `nginx proxy`, ... inside the root docker-compose.yml.

Prerender configuration: https://git.it-consultis.net/docker/prerender#project-configuration
