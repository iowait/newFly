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
require("./php_includes.inc");
require("./lib/header.inc");

$action = script_param('action');
$delete = script_param('delete');
$dtype  = script_param('dtype');
$calendar = script_param('calendar');
$calendarID = '';

if (!$action) {
    $action = 'new'; // first time thru
}

//require("debug.inc");
?>

<div  id="divContent">


    <?php
    if (!hasPerms('admin')) { # we do stuff
        DisplayMessage( "Update member info", "NO PERMISSIONS FOR THIS", 0);
    }



    if ($action == 'update') {

        if ($dtype == 'SPECIAL') {
            $etype = 'SPECIAL';
        } else if (preg_match("/OPS|TOW|CFIG/", $dtype)) {
            $etype = 'FLYING';
        } else {
            DB_Log('ERROR', "Bad event type on calendar update. Got $dtype");
            DisplayMessage("System busy", "can't update");
        }

        // they gave us info, check the data first to make sure it looks good
        $lines = explode("\n", str_replace("\r", "", $calendar));
        $count = 0;

        // start a transaction, we have to make to make updates to a couple of
        // tables, it's either all or none
        if ($msg = DB_Transaction($Conn, 'START')) {
            DisplayMessage("Do a calendar load", $msg);
        }

        foreach ($lines as $line) {

            if (!$line) { // we can take a blank
                continue;
            }

            if ($etype == 'FLYING') {
                list($date, $lastName) = explode(',', $line);
                $title = 'Flying';
                $lastName = trim($lastName);

                if (!$date && !$lastName) { // got nothing, skip it
                    continue;
                }

                if ($date && !$lastName) { // BAD
                    echo "<br>SORRY, Bad format in this line, need two values separated by a comma:<br> $line</br>";
                    $count = 0;
                    break;
                }
                
                // check the name
                if ($msg = getMemberID($lastName, '', $memberID)) {
                    echo "<br>SORRY, Could not find member($msg): $lastName, must match last name as displayed on active members page.</br>";
                    $count = 0;
                    break;
                }

            } else { // SPECIAL
                list($date, $title, $notes) = explode(':', $line);

                if (!$date && (!$title || !$notes)) { // got nothing, skip it
                   continue;
                }
                
            } // end if -else, type of input data.

            // check the date and name, we demand mm/dd/yy
            if (!preg_match("/^(0[1-9]|1[0-2]|[1-9])\/(0[1-9]|[1-2][0-9]|3[0-1]|[0-9])\/([0-9]{2})$/",$date)) {
                echo "<br>SORRY, Bad date used, should by ONLY mm/dd/yy, got: $date</br>";
                $count = 0;
                break;
            }

            // convert the date to mysql, mm/dd/yy -->  yyyy-mm-dd
            list($month,$day,$year) = explode('/', $date);
            $year += 2000;
            $mdate = "$year-$month-$day";

            // Do we need to do an insert into Calendar.
            // first check to see if the date has something that matches
            if ($etype == 'FLYING') {
                $sql = "SELECT CalendarID FROM Calendar WHERE etype='$etype' AND date='$mdate'";
            } else { // SPECIAL
                $sql = "SELECT CalendarID FROM Calendar WHERE etype='$etype' AND date='$mdate' AND title='$title'";
            }

            if ($msg = DB_Query($Conn, $sql, $results)) {
                DisplayMessage("Check calendar",  "query failed ($msg)", 1);
            }

            unset($searchInfo);
            unset($insertInfo);
            unset($calendarID);

            // For FLYING events a duplicate cal is not a problem,we just check duty
            // FOR SPECIAL events we skip a duplicate on date and title

            if (count($results)) { // we have something to add to

                $calendarID = $results[0]['CalendarID'];
                $searchInfo['CalendarID'] = $calendarID;
                echo "<br>Found existing $etype calendar event for $mdate</br>";

                if ($etype == 'SPECIAL') {
                    echo "<br>Found event with same title ($title) for $mdate, skipping.</br>";
                    continue;
                }

                // if the person is already there as duty, just skip
                $searchInfo['type'] = $dtype;
                $searchInfo['memberID'] = $memberID;
                if ($msg = DB_Get($Conn, 'Duty', $searchInfo, $results)) {
                    DisplayMessage("Check existing duty",  "query failed ($msg)", 1);
                }

                if (count($results)) { // we got something
                    echo "<br>Found member $lastName already has duty for $mdate, skipping.</br>";
                    continue;
                }

 
            } else { // first time on this date

                echo "<br>First entry $etype calendar event ($title) for $mdate</br>";
                $insertInfo['CalendarID'] = '';
                $insertInfo['date'] = $mdate;
                $insertInfo['etype'] = $etype;

                if ($etype == "FLYING") {
                    $insertInfo['notes'] = '';
                } else {
                    $insertInfo['title'] = $title;
                    $insertInfo['notes'] = $notes;
                }

                if ($msg = DB_Insert($Conn, 'Calendar', $insertInfo, $calendarID)) {
                    DisplayMessage("Insert calendar entries",
                                   $msg, 1);
                }

                unset($insertInfo['date']);
                unset($insertInfo['etype']);
                
           }  // end if-else, calendar item exists

            // DB_Log('INFO', "Got $dtype for $mdate for $memberID and calendarID of $calendarID");

            // insert duty members if FLYING
            if ($etype == "FLYING") {
                $insertInfo['type'] = $dtype;
                $insertInfo['CalendarID'] = $calendarID;
                $insertInfo['MemberID'] = $memberID;


                if ($msg = DB_Insert($Conn, 'Duty', $insertInfo)) {
                    DisplayMessage("Insert duty entries",
                                   $msg, 1);
                }
                echo "<br>Added member $lastName for $dtype duty on $mdate.</br>";
            }
            $count++; // got something done

        } // end for - all input lines


        if (!$count) { // didn't get anything good
            echo "<p><b>NO UPDATES/CHANGES MADE TO EXISTING DATA</b></p>";
            if ($msg = DB_Transaction($Conn, 'ROLLBACK')) {
                DisplayMessage("Do a calendar load", $msg);
            }


        } else {  // we did it

            // do the COMMIT
            if ($msg = DB_Transaction($Conn, 'COMMIT')) {
                DisplayMessage("Do a calendar load", $msg);
            }

            echo "<p><b>Your info ($count updates) has been loaded successfully!</b></p>";
            if ($etype == 'FLYING') {
                echo "<p>Click <a href='flying_sched.php'>here for updated schedule.</a></p>";
            } else {
                echo "<p>Click <a href='event_sched.php'>here for updated schedule.</a></p>";
            }
            require 'footer.inc';
            exit;
        } // end if-else success

    } // end if - update

    require 'load_calendar_content.php';

    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
