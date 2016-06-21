#!/bin/bash

folder=`dirname ${0}`
if [ "${1}" = "1" ]
then
	ini="${folder}/cserver.ini"
	back_folder=`sed -n '1,1p' ${ini}`

	cd $back_folder
	while [ true ]
	do
		ym=`date +%Y%m`
		php cserver.php >> "/var/log/cserver-${ym}.log"
		sleep 2
	done
else
	"${folder}/cserver.sh" 1 &
fi
exit 0