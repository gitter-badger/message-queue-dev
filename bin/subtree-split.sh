#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH=`git name-rev --name-only HEAD`

function split()
{
    CURRENT_BRANCH=`git name-rev --name-only HEAD`

    SHA1=`./bin/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:$CURRENT_BRANCH"
}

function remote()
{
    git remote add $1 $2 || true
}

remote fms git@github.com:formapro/fms.git
remote mq git@github.com:formapro/message-queue.git
remote stomp git@github.com:formapro/stomp-transport.git
remote amqp-ext git@github.com:formapro/amqp-ext-transport.git
remote bundle git@github.com:formapro/message-queue-bundle.git
remote job-queue git@github.com:formapro/job-queue.git
remote test git@github.com:formapro/message-queue-test.git

split 'pkg/fms' fms
split 'pkg/mq' mq
split 'pkg/mq-stomp' stomp
split 'pkg/mq-amqp-ext' amqp-ext
split 'pkg/mq-bundle' bundle
split 'pkg/job-queue' job-queue
split 'pkg/test' test
