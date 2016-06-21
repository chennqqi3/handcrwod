#!/bin/bash

service handcrowd stop

chkconfig handcrowd off

rm -rf /etc/init.d/handcrowd
rm -rf /usr/sbin/handcrowd.ini
rm -rf /usr/sbin/handcrowd.sh
