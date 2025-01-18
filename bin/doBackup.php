#!/usr/bin/php
<?  /* Copyright (C) 1995-2020  John Murtari
This file is part of FLY flight management software
FLY is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

FLY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with FLY.  If not, see <https://www.gnu.org/licenses/>. */ ?>


<?php

# we need to get a dir name to know where we are installed
if ($argc != 2) {
  echo "ERROR, need default dir for install\n";
  exit(1);
}

$installDir = $argv[1];

if (!chdir($installDir)) {
    echo "ERROR, could not find install directory for FLY software: $installDir";
    exit(1);
}

if (!file_exists("php_includes.inc")) {  // not in the right place
    echo "ERROR, $installDir does not appear to be the FLY install directory";
    eixt(1);
}


require("php_includes.inc");

// set some variable from CONSTANTS
$user = DB_USER;
$pass = DB_PASS;
$db   = FAC_DB;
$flyDir = FLY_DIR;
$host = FAC_HOST;

$date = trim(`date +%m%d`);
echo "Creating web/db backups for FLY on $date\n";

# save web files for fly
system("tar -czf backups/$flyDir.web.$date.tar.gz --exclude '*backups*' --exclude *.mp4 --exclude *.avi --exclude *.mkv --exclude *.sav ../$flyDir");

# save DB for fly
$user = DB_USER;
$pass = DB_PASS;
$db   = FAC_DB;
# --opt: same as  --add-drop-table --add-locks --create-options --disable-keys --extended-insert --lock-tables --quick --set-charset.
system("mysqldump -u $user -h $host -p$pass --opt --no-create-db  --skip-extended-insert $db | gzip > backups/$flyDir.db.$date.sql.gz");

system("chmod go-rwx backups/$flyDir.web.$date.tar.gz backups/$flyDir.db.$date.sql.gz");
?>
