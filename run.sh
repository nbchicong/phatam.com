#!/bin/bash
dt=`date +%d_%m_%Y`;
/bin/php /opt/phatammp3/protected/yiic processmp3 loadMP3 >> /var/log/phatammp3/processmp3_${dt}.log 2>&1 &
chmod -R 777 /home/phatam/domains/phatam.com/public_html/upload2015/mp3/giang/*
