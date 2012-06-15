#!/bin/bash
#########################################################
# script to perform initialisation/installation tasks	#
#########################################################

echo -e "This script should be run by the apache user\n"

DBFile="db/main.db"
templateSQL="db/template.sql"
initSQL="db/init.sql"
sqlitePath="/usr/bin/sqlite3"


if [[ ! -w "$(dirname $DBFile)" ]]; then
	echo "db directory is not writable or script was not run from the directory containing the 'db' directory"
	exit
fi
if [[ -e "$DBFile" ]]; then
	echo "db file at $DBFile already exists. This script is intended to initialise a database where none exists"
	exit
fi
if [[ ! -x "$sqlitePath" ]]; then
	echo "sqlite3 not found"
	exit
fi

$sqlitePath $DBFile < $templateSQL
$sqlitePath $DBFile < $initSQL

echo "install complete"
