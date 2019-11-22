#!/bin/sh
cd /var/www/insonlar/backups/
rm -rf ./insonlar/
OUTPUT="$(ls -t *.tgz | head -1)"
echo "${OUTPUT}"
tar -xzvf "${OUTPUT}" ./
/usr/bin/mongo inson --eval "db.dropDatabase();"
/usr/bin/mongorestore --db=inson ./inson --noIndexRestore
../yii indexer/create-index