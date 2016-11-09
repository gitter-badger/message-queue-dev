#!/bin/bash

set -x
set -e

HOST="localhost"
PORT="15672"
USER="guest"
PASSWD="guest"
VHOST="/"
QUEUE_NAME="formapro.default"
DELAYED_EXCHANGE_NAME="formapro.default.delayed"

while getopts ":q:e:h:P:u:p:v:" OPTION; do
  case $OPTION in
    q)
      QUEUE_NAME=${OPTARG}
      ;;
    e)
      DELAYED_EXCHANGE_NAME=${OPTARG}
      ;;
    h)
      HOST=${OPTARG}
      ;;
    P)
      PORT=${OPTARG}
      ;;
    u)
      USER=${OPTARG}
      ;;
    p)
      PASSWD=${OPTARG}
      ;;
    v)
      VHOST=${OPTARG}
      ;;
  esac
done

function execRabbitmqAdmin()
{
  rabbitmqadmin --host=${HOST} --port=${PORT} --username=${USER} --password=${PASSWD} --vhost=${VHOST} $1
}

execRabbitmqAdmin "declare queue name=${QUEUE_NAME} auto_delete=false durable=true arguments={\"x-max-priority\":4}"
execRabbitmqAdmin "declare exchange name=${DELAYED_EXCHANGE_NAME} type=x-delayed-message auto_delete=false durable=true internal=false arguments={\"x-delayed-type\":\"direct\"}"
execRabbitmqAdmin "declare binding source=${DELAYED_EXCHANGE_NAME} destination=${QUEUE_NAME}"
