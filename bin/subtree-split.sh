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

remote fms git@gitlab.com:askozienko/FMS.git
remote mq git@gitlab.com:askozienko/MessageQueue.git
remote stomp git@gitlab.com:askozienko/Stomp.git
remote bundle git@gitlab.com:askozienko/MessageQueueBundle.git
remote job-queue git@gitlab.com:askozienko/JobQueue.git
remote test git@gitlab.com:askozienko/MessageQueueTest.git

split 'pkg/fms' fms
split 'pkg/mq' mq
split 'pkg/mq-stomp' stomp
split 'pkg/mq-bundle' bundle
split 'pkg/job-queue' job-queue
split 'pkg/test' test
