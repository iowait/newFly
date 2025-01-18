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

$GliderID = script_param('GliderID');

//require("debug.inc");
?>
<div  id="divContent">

    <?php
    if (!$super) { # we do stuff
        DisplayMessage( "Update aircraft info", "NO PERMISSIONS FOR THIS", 0);
    }

    if ($action == 'delete') {

        if (!$GliderID) {
            DisplayMessage("Delete an aircraft",
                           "No ID supplied", 1);
        }
        $srchInfo['GliderID'] = $GliderID;

        // Delete the aircraft item
        if ($msg = DB_Delete($Conn, 'Glider', $srchInfo, 0)) {
            DisplayMessage("Delete an aircraft ",
                           "Not found tailNum $tailNum", 1);
        }

        DisplayPage('aircraft.php');
        echo " <b>The aircraft has been deleted.</b>";
    
        echo "<p>Click <a href='aircraft.php'>here for Club Aircraft.</a></p>";
        


    } else if ($action == 'new') {

        foreach ($GliderLayout as $item => $params) {
            $key = $params['dbCol'];
            $$key = '';
        }


    } else if ($action == 'update' || $action == 'insert') { // most is common

        # First store anything we might have gotten in a POST, don't want to lose it on
        # an error
        $missingList = loadSubmitDataCheckRequired($GliderLayout, $super, $updateInfo);
        
        if ($action == 'insert') {
            if (!$missingList) {
                
                if ($msg = DB_Insert($Conn, 'Glider', $updateInfo, $GliderID)) {
                    DisplayMessage("Insert new aircraft", $msg, 1);
                }
                echo "<b>The new aircraft has been inserted.</b>";
                $action = 'update';
            } else {
                echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to INSERT NEW aircraft<br />
    Data missing for required field(s): $missingList</font></b>";
                DB_Log('USER_MSG', "glider: Data missing for required field(s): $missingList");
                 $action = 'update';
                $action = 'new';
            }
            
        } else {  # an update

            if (!$missingList) {
                $srchInfo['GliderID'] = $GliderID;
                
                if ($msg = DB_Update($Conn, 'Glider', $srchInfo, $updateInfo)) {
                    DisplayMessage(" $action information on an aircraft",
                                   $msg, 0);
                }
                echo "<b>Your information has been updated</b>";
                $action = 'update';
            } else {
                echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to UPDATE EXISTING aircraft<br />
    Data missing for required field(s): $missingList</font></b>";
                $action = 'update';
            } 
        } // end if-else, update or insert
                       
        echo "<p>Click <a href='aircraft.php'>here for Club Aircraft.</a></p>";
        
    } // end if-else delete, new, update, or insert
    
    if ( !$action || ($action == 'update' && !$missingList)) {  // load the data

        $sql = "SELECT * FROM Glider WHERE GliderID = $GliderID";
        
        if ($msg = DB_Query($Conn, $sql, $results)) {
            DisplayMessage("Display glider info",  "query failed ($msg)");
        }

        foreach ($GliderLayout as $item => $params) {
            $key = $params['dbCol'];
            $$key = $results[0][$key];
        }
    
    }  // end if - not a new
    
    if (!$action) { # then on the next screen they can do an update
        $action = "update";
    }
    
    require 'edit_glider_content.php';

    ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
