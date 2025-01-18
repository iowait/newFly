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
require("php_includes.inc");
require("header.inc");
//require("debug.inc");

# possible to have no action on first time through.
$action = script_param('action');

// are we overriding and editing a different users
$memberID = $_SESSION['subID'];
$actMemberID = 0;
if ($super) {
    $actMemberID = script_param('actMemberID');
    if ($actMemberID) {
        $memberID = $actMemberID;
    }
} // end if super logged in

# Potentially they have changed items in the config that correspond to
# ENUM/SET values in the DB.  The "System" table will tell us the last
# a DB check was done, should always be after the date on config.inc...
$checkDB = 1;
if ($checkDB) { // compare values
    $checkArray = Array('MemberTypes' => 'memberType', 'CheckoutTypes' => 'checkout', 
                   'PilotTypes' => 'pilot', 'OtherPilotTypes' => 'otherPilot');
    foreach ($checkArray as $key => $value) {
        if ($msg = compareConfigDB($$key, 'Members', $value)) {
            DisplayMessage("Display the edit member page",
                           "Inconsistent values for $key in config.inc versus DB ($msg)", 0);
        }
    }

}


?>
<div  id="divContent">

    <?php
    if (!hasPerms('member')) { # we do stuff
        DisplayMessage( "Update member info", "NO PERMISSIONS FOR THIS", 0);
    }

    if ($action == 'delete') {

        // got to be privileged for this, bunch of sanity checks
        if (!$super) {
            DisplayMessage("Delete a member", "Not a privileged user", 0);
        }

        if (!$actMemberID) {
            DisplayMessage("Delete a member",
                           "No ID supplied", 1);
        }

        if ($actMemberID == $MemberID) {
            DisplayMessage("Deleting a member", "Can't delete yourself", 0);
        }

        $srchInfo['MemberID'] = $actMemberID;

        // Delete the member
        if ($msg = DB_Delete($Conn, 'Members', $srchInfo, 0)) {
            DisplayMessage("Delete a Member ",
                           "Not found MemberID $actMemberID", 0);
        }

        DisplayPage('active_members.php?type=Active');

    } else if ($action == 'update' || $action == 'insert') {

        # First store anything we might have gotten in a POST, don't want to lose it on
        # an error
        $missingList = loadSubmitDataCheckRequired($MemberLayout, $super, $updateInfo);

        if ($action == 'insert') {
            if (!$missingList) {

                #ZZ check for empty mentorID
                if (!isset($updateInfo['mentorID']) || !$updateInfo['mentorID']) {
                    $updateInfo['mentorID'] = 0;
                }
                if (!isset($updateInfo['towMed']) || !$updateInfo['towMed']) {
                    $updateInfo['towMed'] = '0000-00-00';
                }
                if (!isset($updateInfo['bfr']) || !$updateInfo['bfr']) {
                    $updateInfo['bfr'] = '0000-00-00';
                }
		if (!isset($updateInfo['memberSince']) || !$updateInfo['memberSince']) {
                    $updateInfo['memberSince'] = 'n/a';
                }
                
                if ($msg = DB_Insert($Conn, 'Members', $updateInfo, $memberID)) {
                    echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to INSERT NEW user<br /></font>$msg</b>";
                    $action = 'new';
                } else {
                    echo "<b>The new user has been inserted.</b>";
                    $action = 'update';
                    $actMemberID = $memberID;
                }
            } else {
                echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to INSERT NEW user<br />
    Data missing for required field(s): $missingList</font></b>";
                DB_Log('USER_MSG', "new_user: Data missing for required field(s): $missingList");
                 $action = 'new';
            }
            
        } else {  # an update

            if (!$missingList) {
                $srchInfo['MemberID'] = $memberID;
                #ZZ check for empty mentorID
                if (!isset($updateInfo['mentorID']) || !$updateInfo['mentorID']) {
                    $updateInfo['mentorID'] = 0;
                }
                if (!isset($updateInfo['towMed']) || !$updateInfo['towMed']) {
                    $updateInfo['towMed'] = '0000-00-00';
                }
                if (!isset($updateInfo['bfr']) || !$updateInfo['bfr']) {
                    $updateInfo['bfr'] = '0000-00-00';
                }
                if ($msg = DB_Update($Conn, "Members", $srchInfo, $updateInfo)) {
                    DisplayMessage(" $action information on a member",
                                   $msg, 0);
                }
                echo "<b>Your information has been updated</b>";
                $action = 'update';
            } else {
               echo "<br /><br /><b><font color='red'>NOTE --> Problem encountered while attempting to UPDATE EXISTING user<br />
    Data missing for required field(s): $missingList</font></b>";
               DB_Log('USER_MSG', "edit_user: Data missing for required field(s): $missingList");
                 $action = 'update';
            } 
        } // end if-else, update or insert

    } else if ($action == 'new') { // set values to empty
        
        foreach ($MemberLayout as $item => $params) {
            $key = $params['dbCol'];
            $$key = '';
        }

    } else if ($action == 'search') { 
        // no order defined
        $order1 = script_param('order1') ? script_param('order1') : 'Select';
        $order2 = script_param('order2') ? script_param('order2') : 'Select';;
        $order1Dir = script_param('order1Dir') ? script_param('order1Dir') : 'ASC';
        $order2Dir = script_param('order2Dir') ? script_param('order2Dir') : 'ASC';
        $displayFormat = script_param('displayFormat') ? script_param('displayFormat') : 'normal';

    } // end if-else type of action

    if ( !$action || ($action == 'update' && !$missingList)) {  // load the data

        // load variable needed for the page
        if ($msg = getMemberInfo($memberID, $memberInfo)) {
            echo "Can't get your info($msg)";
        }

        foreach ($MemberLayout as $item => $params) {
            $key = $params['dbCol'];
            $$key = $memberInfo[$key];
        }

    }  // end if - not a new, edit, update

    if (!$action) { # then on the next screen they can do an update
        $action = "update";
    }

    if ($action == 'search') {
        require 'member_search_content.php';
    } else { // normal edit process
        require 'edit_member_content.php';
    }
    ?>
</div>
