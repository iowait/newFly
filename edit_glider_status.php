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


                        
# action can be new, insert, edit, update, or delete
$action = script_param('action');

if ($action == 'edit') {
    $action = ''; // just fall thru to bottom
}

$tailNum = script_param('tailNum');
$GliderID = script_param('GliderID');

//require("debug.inc");
?>
<div  id="divContent">

    <?php
    if (!$super) { # we do stuff
        DisplayPage("glider_status_log.php?GliderID=$GliderID&tailNum=$tailNum");
    }

    if ($action == 'delete') {

        if (!$tailNum) {
            DisplayMessage("Delete an aircraft",
                           "No tail number supplied", 1);
        }
        $srchInfo['tailNum'] = $tailNum;

        // Delete the aircraft item
        if ($msg = DB_Delete($Conn, 'GliderStatus', $srchInfo, 0)) {
            DisplayMessage("Delete an aircraft ",
                           "Not found tailNum $tailNum", 1);
        }

        DisplayPage('glider_status.php');
        echo " <b>The aircraft has been deleted.</b>";
    
        echo "<p>Click <a href='aircraft.php'>here for Club Aircraft.</a></p>";
        


    } else if ($action == 'new') {

        foreach ($GliderStatusLayout as $item => $params) {
            $key = $params['dbCol'];
            $$key = '';
        }


    } else if ($action == 'update' || $action == 'insert') { // most is common

        # First store anything we might have gotten in a POST, don't want to lose it on
        # an error
        $missingList = loadSubmitDataCheckRequired($GliderStatusLayout, $super, $updateInfo);

        # below always
        if (!isset($updateInfo['GliderID'])) { 
            $updateInfo['GliderID'] = $GliderID;
        }
        $updateInfo['updatedBy'] = $MemberID;
        
        // We start a transaction here for either an insert or update
        // We need to update two table, GliderStatus and mxLog
        
        if ($msg = DB_Transaction($Conn, 'START')) {
            DisplayMessage("Start a tail number $action", $msg);
        }

        if ($action == 'insert') {
            if (!$missingList) {
                
                if (!isset($updateInfo['GliderID'])) { 
                    $updateInfo['GliderID'] = $GliderID;
                }

                if ($msg = DB_Insert($Conn, 'GliderStatus', $updateInfo, $tailNum)) {
                    DisplayMessage("Insert new tail #", $msg, 1);
                }
               
                if ($msg = DB_Insert($Conn, 'GliderStatusLog', $updateInfo, $tailNum)) {
                    DisplayMessage("Insert new tail #, update log", $msg, 1);
                }

                $tailNum = $updateInfo['tailNum'];
                $action = 'update';
                echo "<b>The new aircraft has been inserted.</b>";
            } else {
                echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to INSERT NEW TAIL NUMBER<br />
    Data missing for required field(s): $missingList</font></b>";
                DB_Log('USER_MSG', "glider_status: Data missing for required field(s): $missingList");
                 $action = 'update';
                $action = 'new';
            }
            
        } else {  # an update

            if (!$missingList) {
                $srchInfo['tailNum'] = $tailNum;
                
                if ($msg = DB_Update($Conn, 'GliderStatus', $srchInfo, $updateInfo)) {
                    DisplayMessage(" $action information on an aircraft",
                                   $msg, 1);
                }
                if ($msg = DB_Insert($Conn, 'GliderStatusLog', $updateInfo)) {
                    DisplayMessage(" $action LOG information on an aircraft",
                                   $msg, 1);
                }
                echo "<b>Your information has been updated</b>";
                $action = 'update';
            } else {
                echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to UPDATE EXISTING aircraft<br />
    Data missing for required field(s): $missingList</font></b>";
                $action = 'update';
            } 
        } // end if-else, update or insert
                       
        if ($msg = DB_Transaction($Conn, 'COMMIT')) {
            DisplayMessage("Finish a tailnumber $action", $msg);
        }
        
        echo "<p>Click <a href='glider_status.php'>here for Fleet Status.</a></p>";
        
    } // end if-else delete, new, update, or insert
    
    if ( !$action || ($action == 'update' && !$missingList)) {  // load the data

        $sql = "SELECT * FROM GliderStatus WHERE tailNum = '$tailNum'";
        
        if ($msg = DB_Query($Conn, $sql, $results)) {
            DisplayMessage("Display glider info",  "query failed ($msg)");
        }

        foreach ($GliderStatusLayout as $item => $params) {
            $key = $params['dbCol'];

            if (!isset($results[0][$key])) { // code error?
                DisplayMessage("Display glider info", "missing value for $item: $key");
            }
            $$key = $results[0][$key];
        }

    }  // end if - not a new
    
    if (!$action) { # then on the next screen they can do an update
        $action = "update";
    }
    
    require 'edit_glider_status_content.php';

    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
