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
require("./header.inc");

$action = script_param('action');
$calendarID = script_param('calendarID');
$etype = script_param('etype');
$dTypes = array("OPS", "TOW", "CFIG", "COORD", "HELP");
$pTypes = array("CREW");
$notes = script_param('notes');
$title = script_param('title');
$date = script_param('startDate');
$tailNum = script_param('tailNum');
$startTime = script_param('startTime');
$stopTime = script_param('stopTime');
$crew = script_param('CREW');

if (!strstr($startTime, '-')) { // then we just got a time, add date
    $startTime = "$date $startTime";
}
if (!strstr($stopTime, '-')) {
    $stopTime = "$date $stopTime";
}
    

if (! $etype) {  // this could change later....
    $etype = 'FLYING';
}

//require("debug.inc");
?>
<div  id="divContent">

    <?php
    if (!hasPerms('member')) { # we do stuff
        DisplayMessage( "Update schedule info", "NO PERMISSIONS FOR THIS", 0);
    }

    // always get the aircraft info
    $sql = "SELECT model, tailNum, seats FROM Glider g, GliderStatus s WHERE g.GliderID=s.GliderID";
    if ($msg = DB_Query($Conn, $sql, $planeResults)) {
        DisplayMessage("Display aircraft", "query failed ($msg)");
    }

    // on the aircraft reservation page there is a javascript function, gliderChange(), which shows
    // the correct number of crew positions depending on seats in the glider.  We create
    // a string variable here that contains the tailNum of all two seat aircraft.
    $javaVar = "var twoSeatTailNums = '";
    foreach ($planeResults as $plane) {
        if ($plane['seats'] > 1) {
            $javaVar .= "{$plane['tailNum']},";
        }
    }
    $javaVar .= "';"; // close the string - this is used at the bottom of edit_sched_content.php
    
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
        
        if ($etype == 'FLYING' || $etype == 'PLANE') {
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
        if ($etype == 'FLYING') {
            $results = array( 0 => array('dtype' => 'OPS',  'tag' => 'Select:none:0'),
                              1 => array('dtype' => 'TOW',  'tag' => 'Select:none:0'),
                              2 => array('dtype' => 'CFIG', 'tag' => 'Select:none:0')
            );
        } else if ($etype == 'PLANE') {
            $results = array( 0 => array('dtype' => 'CREW',  'tag' => 'Select:none:0')
            );

            
        }

    } else if ($action == 'update' || $action == 'insert') { 

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

        // PLANE res needs a bit more
        Debug(0x20, 'edit_sched', "tailNum($tailNum), startTime($startTime), stopTime($stopTime)");
        if ($etype == 'PLANE' && (!$tailNum || !$startTime || !$stopTime  || !strpos($startTime, ':') || !strpos($stopTime, ':') || strpos($startTime, " 00:") || strpos($stopTime, " 00:")) ) {
            DisplayMessage("Make an Aircraft Reservation ",
                           "Missing aircraft selection and/or start/stop times $action", 0);
        }

        // Got to have crew
        if (isset($crew[0]) && $crew[0] == 'none:0') {
            DisplayMessage("Make an Aircraft Reservation ",
                           "Need at least one crew member reservation.", 0);
        }
        
	// if this is a new FLYING or PLANE Res event, no duplicate on date/time/aircraft!
        $needsFlying = 0;  // if we get a plane reservation, but no flying event yet on that day
        
	if ($action == 'insert') {
            if ($etype == 'FLYING') {
	        $chkInfo['date'] = $date;
	        $chkInfo['etype'] = $etype;
	        if ($msg = DB_Get($Conn, "Calendar", $chkInfo, $chkResults)) {
	            DisplayMessage("Check for event conflict", $msg);
	        }
	        if (count($chkResults)) {
	            DisplayMessage("Insert your new event", "Sorry, this date ($date) already has a FLYING event on the schedule.  <p>If you wish to make a change, you must go back to the flying schedule and edit the existing event.</p>");
	        }
	    } else if ($etype == 'PLANE') { // date doesn't matter but aircraft and time do
                $startCheck = "$startTime";
                $stopCheck  = "$stopTime";

                $sql = "SELECT * FROM Reservations WHERE tailNum = '$tailNum' AND (
                        (startTime <= '$startCheck' AND stopTime > '$startCheck') OR
                        (startTime <= '$startCheck' AND ( stopTime >= '$stopCheck' OR stopTime > '$startCheck')) OR
                        (startTime < '$stopCheck'  AND stopTime >= '$stopCheck')
                        )";

                //DB_Log("Conflict Check", $sql);
                if ($msg = DB_Query($Conn, $sql, $chkResults)) {
	            DisplayMessage("Check for reservation conflict", $msg);

                }
                if (count($chkResults)) {
                    $conflictStart = $chkResults[0]['startTime'];
                    $conflictStop  = $chkResults[0]['stopTime'];
                    DisplayMessage("Insert your aircraft reservation", "Sorry, this date ($date) shows another reservation for this aircraft from $conflictStart to $conflictStop.<br />  Please choose another time range or aircraft");
                }

                // is there a FLYING day entry yet for this date, if not we need to make one
                // check now, insert in transaction below
                $sql = "SELECT * FROM Calendar WHERE etype='FLYING' AND date='$date'";
                if ($msg = DB_Query($Conn, $sql, $chkResults)) {
	            DisplayMessage("Check for FLYING date", $msg);
                }
                if (!count($chkResults)) {
                    $needsFlying = 1;
                }
            } # end if/else - conflict check
        } // end if - insert of new FLYING or PLANE Reservation

        // start a transaction, we have to make to make updates/inserts to a couple of
        // tables, it's either all or none
        if ($msg = DB_Transaction($Conn, 'START')) {
            DisplayMessage("Do a calendar update", $msg);
        }

        $srchInfo['CalendarID'] = $calendarID;
        $updateInfo['notes'] = $notes;
        $updateInfo['title'] = $title;
        $updateInfo['date'] = $startTime ? $startTime : $date;
        $updateInfo['etype'] = $etype;

        // we do an insert or update depending on type
        if ($action == 'insert') {

            if ($needsFlying) { # insert stub flying and duty entries
                $flyInfo['notes'] = '';
                $flyInfo['title'] = '';
                $flyInfo['date'] = $date;
                $flyInfo['etype'] = 'FLYING';
                
                if ($msg = DB_Insert($Conn, 'Calendar', $flyInfo, $calendarID)) {
                    DisplayMessage("Insert FLYING placehold calendar entries", $msg, 1);
                }

                $dutyInfo['calendarID'] = $calendarID;
                $dutyInfo['memberID']   =  0;
                $dutyInfo['type'] = 'OPS';
    
                if ($msg = DB_Insert($Conn, 'Duty', $dutyInfo, $junk)) {
                    DisplayMessage("Insert FLYING placeholder duty entries", $msg, 1);
                }
            }

            if ($msg = DB_Insert($Conn, 'Calendar', $updateInfo, $calendarID)) {
                DisplayMessage("Insert calendar entries", $msg, 1);
            }

           
        } else { // it's an update

            if ($msg = DB_Update($Conn, "Calendar", $srchInfo, $updateInfo)) {
                DisplayMessage("Update main calendar entry", $msg, 1);
            }
        }

        if ($etype == 'FLYING' || $etype == 'PLANE') {

            // Now we do the duty assignments, get the proper list
            if ($etype == 'FLYING') {
                $theList = $dTypes;
            } else {
                // they needed to pick a plane
                if (!$tailNum) {
                    DisplayMessage("Create/Update Reservation", "No aircraft selected", 1);
                }
                $theList = $pTypes;
                // we also need to check the Reservation table
            }
            
            // clear what we have, but remember any recorded ack or ackCode FIRST, we
            // need to put that back, a bit more complex because a PLANE reservation
            // only has CREW, need to distinguish.
            if ($etype == 'FLYING') {
                $extra = " AND type != 'CREW'";
            } else { // PLANE
                $extra = " AND type = 'CREW'";
            }
            if ($msg = DB_Query($Conn, "SELECT MemberID, type, ack, ackCode FROM Duty 
                               WHERE CalendarID = $calendarID AND MemberID != 0 $extra", $dResults)){
                DisplayMessage("Recall duty entries for FLYING event", $msg, 1);
            }
            
            if ($msg = DB_Query($Conn, "DELETE FROM Duty WHERE CalendarID = $calendarID $extra", $junk)) {
                DisplayMessage("Delete duty entries for $extra", $msg, 1);
            }

            // if the calendar event is associated with a plane res, we get rid of the prior
            if ($etype == 'PLANE') {
                if ($msg = DB_Query($Conn, "DELETE FROM Reservations WHERE CalendarID = $calendarID", $junk)) {
                    DisplayMessage("Delete prior aircraft reservations", $msg, 1);
                }
            }

            // insert new values
            $insertInfo['CalendarID'] = $calendarID;
            foreach ($theList as $type) {
                $theType = script_param($type);
                if (!$theType) {
                    continue;
                }

                foreach ($theType as $row) {

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
                    
                    // look for this member if they had recorded ack data
                    foreach ($dResults as $dResult) {
                        if ($dResult['MemberID'] == $memberID && $dResult['type'] == $type) { #match
                            // restore the info
                            $insertInfo['ack'] = $dResult['ack'];
                            $insertInfo['ackCode'] = $dResult['ackCode'];
                        } // match on user and type
                    } // end for search of existing Duty data
                    
                    if ($msg = DB_Insert($Conn, 'Duty', $insertInfo)) {
                        DisplayMessage("Insert duty entries",
                                       $msg, 1);
                    }
                } // end for - this duty type
            } // end for - all duty types

            // If this is a PLANE reservation, insert in  table
            if ($etype == 'PLANE') { 
                $qInfo['startTime'] = "$startTime";
                $qInfo['stopTime']  = "$stopTime";
                $qInfo['tailNum']  = $tailNum;
                $qInfo['CalendarID'] = $calendarID;
                // DB_LOG("Action is $action", "Got this for reservation " . print_r($qInfo, TRUE));
                if ($msg = DB_Insert($Conn, 'Reservations', $qInfo, $id)) {
                    DisplayMessage("Insert calendar entries",$msg, 1);
                }
            }
            
        } // end if - FLYING/PLANE Reservation event
        
        if ($msg = DB_Transaction($Conn, 'COMMIT')) {
            DisplayMessage("Do a calendar update", $msg);
        }

        echo "<font color='red'>Your changes have been made =====>>>> &nbsp;&nbsp;";
        if ($etype == 'FLYING' || $etype == 'PLANE') {
            echo "<a href='flying_sched.php'>Click here for an updated FLYING schedule.</a>";
        } else {
            echo "<a href='event_sched.php'>Click here for an updated EVENT schedule.</a>";
        }
        echo "</font>";


    } // end if-else create/update

    if ($action == 'update' || $action == 'insert' || $action == 'edit') { // load what we have into results

        $newAction = 'update';
        $reservation = 'bongo';
        if ($etype == 'FLYING' || $etype == 'PLANE' ) {
            if ($msg = getCalendarInfo($calendarID, $etype, $date, $notes, $results, $reservation)) {
                DisplayMessage( "Display edit event details", "query failed ($msg)");
            }
            //DB_Log( "Edit check", "GOT THIS for calendarID($calendarID/$etype) " . print_r($reservation, TRUE));

            if ($etype == 'PLANE') {
                if (isset($reservation[0]['tailNum'])) {
                    $picked = $reservation[0]['tailNum'];
                    $startTime = $reservation[0]['startTime'];
                    $stopTime  = $reservation[0]['stopTime'];
                }
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
            require 'edit_sched_content.php';
        }
    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
