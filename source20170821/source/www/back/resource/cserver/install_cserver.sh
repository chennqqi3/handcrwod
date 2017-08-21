#!/bin/bash
folder="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
root_folder="$( cd "${folder}" && cd ../../ && pwd )"
cp -f "${folder}/cserver" /etc/init.d
cp -f "${folder}/cserver.sh" /usr/sbin/cserver.sh
cp -f "${folder}/pserver.sh" /usr/sbin/pserver.sh
echo "${root_folder}" > /usr/sbin/cserver.ini
chkconfig cserver on
service cserver condrestart