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
require("header2_test.inc");

$action = script_param('action');
$calendarID = script_param('calendarID');
$etype = script_param('etype');
$dTypes = array("OPS", "TOW", "CFIG", "COORD", "HELP");
$notes = script_param('notes');
$title = script_param('title');
$date = script_param('startDate');

if (! $etype) {
    $etype = 'FLYING';
}

//require("debug.inc");
?>
<div  id="divContent">

    <?php
    if (!hasPerms('member')) { # we do stuff
        DisplayMessage( "Update schedule info", "NO PERMISSIONS FOR THIS", 0);
    }


    if ($action == 'delete') {

        if ($calendarID < 1) {
            DisplayMessage("Delete a schedule item ",
                           "No calendar supplied", 1);
        }
        $srchInfo['CalendarID'] = $calendarID;

        // Delete the calendar item, will cascade to Duty
        if ($msg = DB_Delete($Conn, 'Calendar', $srchInfo, 0)) {
            DisplayMessage("Delete a schedule item ",
                           "Not found $calendarID", 1);
        }

        echo " <b>The event has been deleted.</b>";
    
        if ($etype == 'FLYING') {
            echo "<p>Click <a href='flying_sched.php'>here for updated FLYING schedule.</a></p>";
        } else {
            echo "<p>Click <a href='event_sched.php'>here for updated EVENT schedule.</a></p>";
        }


    } else if ($action == 'new') {
        if ($calendarID > 1) {
            DisplayMessage("Create new schedule ",
                           "Calendar event supplied", 1);
        }

        $date  = '';
        $notes = '';

        // prime the results
        $results = array( 0 => array('dtype' => 'OPS',  'tag' => 'none:0'),
                          1 => array('dtype' => 'TOW',  'tag' => 'none:0'),
                          2 => array('dtype' => 'CFIG', 'tag' => 'none:0')
        );

    } else if ($action == 'update' || $action == 'insert') { // most is common


        // got to have a calendarID on update
        if (!($calendarID > 0) && $action == 'update') {
            DisplayMessage("Update schedule ",
                           "No calendar event supplied for $action", 0);
        }

        // got to have have an etype and date for both insert/update
        if (!$date || !$etype) {
            DisplayMessage("Update schedule ",
                           "Not enough info supplied, date missing", 0);
        }

	// if this is an insert into FLYING, no duplicate on date!  
	if ($action == 'insert' && $etype == 'FLYING') {
	   $chkInfo['date'] = $date;
	   $chkInfo['etype'] = $etype;
	   if ($msg = DB_Get($Conn, "Calendar", $chkInfo, $chkResults)) {
	      DisplayMessage("Check for event conflict", $msg);
	   }
	   if (count($chkResults)) {
	      DisplayMessage("Insert your new event", "Sorry, this date ($date) already has a FLYING event on the schedule.  <p>If you wish to make a change, you must go back to the flying schedule and edit the existing event.</p>");
	   }
	} // end if - duplicate FLYING event on insert

        // start a transaction, we have to make to make updates to a couple of
        // tables, it's either all or none
        if ($msg = DB_Transaction($Conn, 'START')) {
            DisplayMessage("Do a calendar update", $msg);
        }

        $srchInfo['CalendarID'] = $calendarID;
        $updateInfo['notes'] = $notes;
        $updateInfo['title'] = $title;
        $updateInfo['date'] = $date;
        $updateInfo['etype'] = $etype;

        // we do an insert or update depending on type
        if ($action == 'insert') {

            if ($msg = DB_Insert($Conn, 'Calendar', $updateInfo, $calendarID)) {
                DisplayMessage("Insert calendar entries",
                               $msg, 1);
            }

        } else {

            if ($msg = DB_Update($Conn, "Calendar", $srchInfo, $updateInfo)) {
                DisplayMessage("Update main calendar entry",
                               $msg, 1);
            }
        }

        if ($etype == 'FLYING') {
            // IF FLYING - now we do the duty assignments
            // clear what we have
            if ($msg = DB_Query($Conn, "DELETE FROM Duty WHERE CalendarID = $calendarID", $junk)) {
                DisplayMessage("Delete duty entries",
                               $msg, 1);
            }

            // insert new values
            $insertInfo['CalendarID'] = $calendarID;
            foreach ($dTypes as $type) {
                $dtype = script_param($type);
                if (!$dtype) {
                    continue;
                }

                foreach ($dtype as $row) {

                    // could get a 'none' here, meaning not used, just skip
                    if ($row == 'none') {
                        continue;
                    }
                    list($junk, $memberID) = explode(':', $row);
                    if (!isset($memberID)) {
                        DisplayMessage("Insert duty entries",
                                       "no memberID ($memberID)", 1);
                    }
                    $insertInfo['MemberID'] = $memberID;
                    $insertInfo['type'] = $type;

                    if ($msg = DB_Insert($Conn, 'Duty', $insertInfo)) {
                        DisplayMessage("Insert duty entries",
                                       $msg, 1);
                    }
                }


            } // end for - all duty types

        }
        
        if ($msg = DB_Transaction($Conn, 'COMMIT')) {
            DisplayMessage("Do a calendar update", $msg);
        }

        echo "<b>The schedule has been updated</b>";
        if ($etype == 'FLYING') {
            echo "<p>Click <a href='flying_sched.php'>here for updated FLYING schedule.</a></p>";
        } else {
            echo "<p>Click <a href='event_sched.php'>here for updated EVENT schedule.</a></p>";
        }


        } // end if-else create/update

        if ($action == 'update' || $action == 'insert' || $action == 'edit') { // load what we have

            $newAction = 'update';
            if ($etype == 'FLYING') {
                if ($msg = getCalendarInfo($calendarID, $etype, $date, $notes, $results)) {
                    DisplayMessage( "Display edit event details", "query failed ($msg)");
                }
            } else {
                $sql = "SELECT DATE(date) AS date, etype, title, calendarID, notes  FROM Calendar c
                  WHERE etype = 'SPECIAL' AND CalendarID = $calendarID";
                
                if ($msg = DB_Query($Conn, $sql, $results)) {
                    DisplayMessage("Display events",  "query failed ($msg)");
                }
                $etype = $results[0]['etype'];
                $date  = $results[0]['date'];
                $title = $results[0]['title'];
                $notes = $results[0]['notes'];
            }



        } else if ($action == 'new') {
            $newAction = 'insert';
        }

        if ($action != 'delete') {
            require 'test_sched_content.php';
        }
    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
