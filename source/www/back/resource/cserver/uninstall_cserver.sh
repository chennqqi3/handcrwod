#!/bin/bash
service cserver stop
chkconfig cserver off
rm -rf /etc/init.d/cserver
rm -rf /usr/sbin/cserver.sh