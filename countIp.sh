#!/bin/bash
n=$([[ ! -f "/tmp/msgIps/$1" ]] && echo 0 || echo `cat /tmp/msgIps/$1`)
echo $((n+1)) > /tmp/msgIps/$1
echo $((n+1))
