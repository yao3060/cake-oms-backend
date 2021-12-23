#!/usr/bin/env bash
set -aeuo pipefail

sed -i '/networks:/s/^#\ //g' docker-compose.yml
sed -i '/default:/s/^#\ //g' docker-compose.yml
sed -i '/driver:/s/^#\ //g' docker-compose.yml
sed -i '/ipam:/s/^#\ //g' docker-compose.yml
sed -i '/driver:/s/^#\ //g' docker-compose.yml
sed -i '/config:/s/^#\ //g' docker-compose.yml
sed -i '/subnet:/s/^#\ //g' docker-compose.yml
sed -i '/gateway:/s/^#\ //g' docker-compose.yml
