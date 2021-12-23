#!/usr/bin/env bash
set -aeuo pipefail

IMAGES=$@

docker login --username=$ALIYUN_USERNAME $ALIYUN_CONTAINER_REGISTRY --password=$DOCKER_PASSWORD

for image in ${IMAGES[*]}
do
	docker pull ${CONTAINER_REGISTRY}/${ENVIRONMENT}_$image:${VERSION}
	docker tag ${CONTAINER_REGISTRY}/${ENVIRONMENT}_$image:${VERSION} $ALIYUN_CONTAINER_REGISTRY/itconsultis/${PROJECT}_${ENVIRONMENT}_$image:latest
	docker push $ALIYUN_CONTAINER_REGISTRY/itconsultis/${PROJECT}_${ENVIRONMENT}_$image:latest
	docker tag $ALIYUN_CONTAINER_REGISTRY/itconsultis/${PROJECT}_${ENVIRONMENT}_$image:latest $ALIYUN_CONTAINER_REGISTRY/itconsultis/${PROJECT}_${ENVIRONMENT}_$image:${VERSION}
	docker push $ALIYUN_CONTAINER_REGISTRY/itconsultis/${PROJECT}_${ENVIRONMENT}_$image:${VERSION}
done
