#!/bin/sh

chmod 777  /shared/
mkdir -p /shared/mariadb

which mysqld
/usr/sbin/mysqld --verbose --help | grep -A 1 "Default options" >> /shared/mariadb/mysqld.options

