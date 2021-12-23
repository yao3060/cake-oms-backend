#!/usr/bin/env bash
set -aeuo pipefail

PARALLEL=
if docker-compose build --help | grep -q 'parallel'; then
   PARALLEL="--parallel"
fi

docker-compose -f ./docker-compose.yml $(find ./src-* -maxdepth 1 -name 'docker-compose.*' -exec echo '-f {} ' \;) build ${PARALLEL}
docker-compose -f ./docker-compose.yml $(find ./src-* -maxdepth 1 -name 'docker-compose.*' -exec echo '-f {} ' \;) push

