#!/bin/bash

#
# This script takes a dump of the SoaringTools demo DB, eliminates
# junk, to produce a DB backup suitable for installs and places it 
# in the etc directory.
#

# Run a current dump
echo "Running a current DB dump to backups, some warnings expected"
cd ~
~/fly/bin/doBackup.php fly
cd ~/fly || exit 2
date=`date +%m%d`
backup=fly.db.$date.sql.gz

if [ ! -f backups/$backup ]; then
   echo "ERROR, couldn't find backup $backup";
   exit 1
fi
gunzip backups/$backup || exit 2

# Now we pass all that thru sed and get ride of extraneous lines
backupFileDB=fly.db.$date.sql
backupPathDB=backups/$backupFileDB
installDB=etc/$backupFileDB

# Calendar table, we remove all events

sed -f - $backupPathDB > $installDB <<EOF
# remove Calendar events
/INSERT INTO .Calendar. VALUES/   d

# remove Duty events
/INSERT INTO .Duty. VALUES/       d

# clean the EventLog
/INSERT INTO .EventLog. VALUES/   d

# Leave only a few members
/INSERT INTO .Members. VALUES .*\(Johnville\|Rickville\)/       b
/INSERT INTO .Members. VALUES/    d
# Remove Reservations
/INSERT INTO .Reservations. VALUES/ d

# Remove Sessions
/INSERT INTO .Sessions. VALUES/   d

# Remove hfl data
/INSERT INTO .hfl. VALUES/        d

EOF

# set the symlink to the current
cd etc || exit 2
gzip $backupFileDB || exit 2
rm fly.db.sql.gz || exit 2
ln -s $backupFileDB.gz fly.db.sql.gz || exit 2

echo "New backup created successfully:"
ls -l fly.db.sql.gz

exit 0
