#!/bin/bash
NETWORK=$1
CHECKEXISTED=$(docker network ls| grep $NETWORK | wc -l)

if [ $CHECKEXISTED != 0 ];
then
	docker network rm $NETWORK
else
	echo "no docker network $NETWORK"
fi

