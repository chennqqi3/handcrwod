#!/bin/bash

folder=`dirname ${0}`

cp -f "${folder}/handcrowd" /etc/init.d
cp -f "${folder}/batch.ini" /usr/sbin/handcrowd.ini
cp -f "${folder}/handcrowd.sh" /usr/sbin/handcrowd.sh

chkconfig handcrowd on

service handcrowd condrestart
