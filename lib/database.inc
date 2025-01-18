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
<?
global $Conn;
include_once("config.inc");
include_once("db_funcs.inc");
include_once("common.inc");
//include_once("passwd.inc.php");

global $dbRunning, $Page, $PageInformation;

if (!isset($Conn)) {
    if ( $msg = DB_Open(FAC_DB, FAC_HOST, DB_USER, DB_PASS, $Conn) ) {
        
        $dbRunning = 0;
        echo $msg;
        
        // we'll opt for something more graceful
        //    echo ("<br><b>Our Server is currently undergoing maintanence, please wait a few minutes and try again.</b>");
        //    exit(1);
    } else $dbRunning = 1;
} else $dbRunning = 1;

?>
