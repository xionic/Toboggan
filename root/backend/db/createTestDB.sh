#!/bin/bash

rm old-main.db
mv main.db old-main.db


sqlite3 main.db < 1.template.sql | grep -v wal
sqlite3 main.db < 2.init.sql
sqlite3 main.db < 3.defaultdata.sql
sqlite3 main.db < 4.testingdata.sql

chmod 777 main.db
