#!/bin/bash

folder=`dirname ${0}`
if [ "${1}" = "1" ]
then
	ini="${folder}/cserver.ini"
	back_folder=`sed -n '1,1p' ${ini}`

    cd $back_folder
    php pserver.php > /var/log/pserver.log
else
	"${folder}/pserver.sh" 1 &
fi
exit 0