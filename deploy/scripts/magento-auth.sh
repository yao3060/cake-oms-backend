#!/usr/bin/env bash

if [ -d ./src-magento/src ]; then
    echo "{\"http-basic\":{\"repo.magento.com\":{\"username\":\"${MAGENTO_USERNAME}\",\"password\":\"${MAGENTO_PASSWORD}\"}}}" > src-magento/src/auth.json
fi

