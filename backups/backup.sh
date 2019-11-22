#!/bin/sh
cd /var/www/qoqon/backups/

OUTPUT="$(ls -t mon*5\-*\-*\_*_*.bak.tgz | head -1)"
if [ -f $OUTPUT ]; then
     FILE_BACK="rtt_$(echo $OUTPUT | cut -c7-29)tgz"
     mv "${OUTPUT}"  "${FILE_BACK}"
fi

OUTPUT="$(ls -t mon*0\-*\-*\_*_*.bak.tgz | head -1)"
if [ -f $OUTPUT ]; then
     FILE_BACK="rtt_$(echo $OUTPUT | cut -c7-29)tgz"
     mv "${OUTPUT}"  "${FILE_BACK}"
fi

THE_DATE=$(date +"%d-%m-%Y_%H_%M")
/usr/bin/mongodump --db=qoqon --out=./
tar -czvf mongo_qoqon_${THE_DATE}.bak.tgz ./qoqon/
rm -rf ./qoqon
find /var/www/qoqon/backups/mongo_*.bak.* -mtime +15 -exec rm {} \;
