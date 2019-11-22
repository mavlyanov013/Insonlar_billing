#!/bin/sh
cd /var/www/qoqon/backups/
rm -rf ./qoqon/
OUTPUT="$(ls -t *.tgz | head -1)"
echo "${OUTPUT}"
tar -xzvf "${OUTPUT}" ./
/usr/bin/mongo qoqon --eval "db.dropDatabase();"
/usr/bin/mongorestore --db=qoqon ./qoqon --noIndexRestore
../yii indexer/create-index