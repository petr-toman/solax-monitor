#!/bin/sh

 #Exit on non defined variables and on non zero exit codes
set -eu

TZ="${TZ:-UTC}"

echo 'Updating configurations'

chmod 777  /shared/
mkdir -p /shared/cronie

echo 'Starting Cronie'
mkdir -p /shared/cronie

# crond -f 
crond

#start endless polling loop:
/scripts/start-polling.sh

