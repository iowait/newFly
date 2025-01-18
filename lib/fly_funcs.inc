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

// return "staffImitate" if staff logged in as customer
function authorize($subID, $pass, &$staffImitate = 0) {
    global $Conn, $StaffArray;

    $srchInfo['MemberID'] = $subID;

    // take care of blank password
    if (!$pass) {
        return "Invalid password";
    }

    //  $srchInfo['password'] = str_rot13($pass);
    $srchInfo['password'] = md5($pass);

    if( $msg = DB_Get($Conn, "Members", $srchInfo, $results) ) {
        return $msg;
    }

    if( count($results) != 1 ) {

        // try again comparing to a staff password
        if (md5($pass) == STAFF_CHECK) {
            return(0);
        }
        return "Invalid password";
    }
    return(0);

} // end authorize


// function converts an easy human date in form   MMM, DD  YYYY into
// a MySQL date
function convertDate($date) {

    if (@stristr($date, 'pre')) { // present
	return("0000-00-00");
    }

    @list($month, $day, $year) = preg_split('/[\s,\-]+/', $date);
    //errorLog("date($date) -> month($month), day($day), year($year)");

    if (stristr($month, 'jan')) {
	$monthNum = 1;
    } else if (stristr($month, 'feb')) {
	$monthNum = 2;
    } else if (stristr($month, 'mar')) {
	$monthNum = 3;
    } else if (stristr($month, 'apr')) {
	$monthNum = 4;
    } else if (stristr($month, 'may')) {
	$monthNum = 5;
    } else if (stristr($month, 'jun')) {
	$monthNum = 6;
    } else if (stristr($month, 'jul')) {
	$monthNum = 7;
    } else if (stristr($month, 'aug')) {
	$monthNum = 8;
    } else if (stristr($month, 'sep')) {
	$monthNum = 9;
    } else if (stristr($month, 'oct')) {
	$monthNum = 10;
    } else if (stristr($month, 'nov')) {
	$monthNum = 11;
    } else if (stristr($month, 'dec')) {
	$monthNum = 12;
    } else {
	$monthNum = "99";
	$day = "99";
	$year = "9999";
    }

    if ($day < 1 || $day > 31) {
	$day = "99";
    }

    if ($year < 1900 || $year > 2100) { // could be two digit year
        $year =+ 2000;
        if ($year > 2100) {
	    $year = "9999";
        }
    }

    return("$year-$monthNum-$day");
} // end convertDate


// displays an input box with name $name of the selected $type: MENU, RADIO, CHECK
// using as options an array of $choices, with optional
// default value(s) to be selected $default, $default could be a comma
// separated list (raw data is SET).
function displayInput($name, $type, $choices, $defaults, $noTable = 0) {

    if (!$name || !preg_match('/^(MENU|RADIO|CHECK)$/', $type) || !$choices) {
        return("bad params to displayInput");
    }

    // if they gave us a default(s), it/they better be in the list of choices
    // different check if we have more than one default
    if ($defaults) {
        $defaultsArray = explode(',', $defaults);
        $diffs = array_diff($defaultsArray, $choices);
        if ($defaults && count($diffs)) { // missing values
            if ($defaults != 'Select') { // we'll tolerate that and add it
                echo "<br>the default value ($defaults) is not in the choice list". print_r($choices);
                return;
            } else {
                array_push($choices, 'Select');
            }
        }
    } else {
        $defaultsArray = '';
    }

    if (count($choices) < 2) { // somethings wrong
        return("short list of choices");
    }

    $outputStr = "";

    if ($type == 'MENU') {
        $outputStr .= "<select name='$name'>\n";       
        if (!$defaults) {
            $outputStr .= "  <option value='Select' selected>Select</option>\n";
        }
        
        for ($i = 0; isset($choices[$i]); $i++) {
            $extra = '';           
            if ($choices[$i] == $defaults) {
                $extra = 'selected';
            }
            $outputStr .= "  <option value='${choices[$i]}' $extra>";
            $outputStr .=    $choices[$i];
            $outputStr .= "  </option>\n";
        } // end for - all choices

        $outputStr .= "</select>\n";

    } else if ($type == 'CHECK') {  # put it in a table, 4 to a row.
        $boxName = $name."[]";  // array coming back
        $outputStr="<table>\n";
        
        for ($i = 0; isset($choices[$i]); $i++) {
            $choice = $choices[$i];
            $extra = '';
            
            if (is_array($defaultsArray)) {
                foreach ($defaultsArray as $default) {
                    if ($choice == $default) {
                        $extra = " checked ";
                        break;
                    }
                } // end for - possibly checked items
            } // end if - got a default

            if (! ($i % 4) ) { # limit of 4 in a row
                if ($i) { # afte first row
                    $outputStr .= "  </tr>\n";
                }
                $outputStr .= "  <tr>\n";
            }
            $outputStr .= "<td>  <input type='checkbox' name='$boxName' value='$choice' $extra>";
            $outputStr .=  $choice;
            $outputStr .= "</td>\n";
            
        } // end for - all choices
        $outputStr .= "</table>";
    } else if ($type == 'RADIO') {  # 
        $boxName = $name;  // only ONE value coming back
        if (!$noTable) {
            $outputStr="<table>\n";
        } else {
            $outputStr="";
        }
        
        for ($i = 0; isset($choices[$i]); $i++) {
            $choice = $choices[$i];
            $extra = '';
            
            if (is_array($defaultsArray)) {
                foreach ($defaultsArray as $default) {
                    if ($choice == $default) {
                        $extra = " checked ";
                        break;
                    }
                } // end for - possibly checked items
            } // end if - got a default

            if (!$noTable) {
                if (! ($i % 4) ) { # limit of 4 in a row
                    if ($i) { # afte first row
                        $outputStr .= "  </tr>\n";
                    }
                    $outputStr .= "  <tr>\n";
                }
                $outputStr .= "<td>  <input type='radio' name='$boxName' value='$choice' $extra>";
                $outputStr .=  $choice;
                $outputStr .= "</td>\n";
            } else {
                $outputStr .=  "<input type='radio' name='$boxName' value='$choice' $extra> $choice";
            }
            
        } // end for - all choices
        if (!$noTable) {
            $outputStr .= "</table>";
        }

    }
    echo $outputStr;

} // end displayInput


// converts the input value to a more proper format, checks for 10 digits
// returns error message if number is bad
function convertPhone($phone, &$newPhone) {

    $area = ""; $exchange = ""; $rest = ""; $ext="";

    $charArray = str_split($phone);
    $i = 0; $takeAll = false;
    foreach ($charArray as $char) {
	if ($takeAll || preg_match('/[0-9]/', $char)) {
	    $i++;
	    if ($i <= 3) {
		$area .= $char;
	    } else if ($i <= 6) {
		$exchange .= $char;
	    } else if ($i <=9) {
		$rest .= $char;
	    } else if ($i == 10) {
		$rest .= $char;
		$takeAll = true; // we take everything else as is
	    } else {
		$ext .= $char;
	    }
	} // got a number
    } // end for -- all numbers

    if ($i < 10) { // something missing
	return("Phone number must contain area code.  Make sure you have entered at least 10 digits.");
    }

    $newPhone = "($area) $exchange-$rest $ext";

} // end convert phone

// display HTML for member/glider data where consecutive items are shown
// the Layout give us type data for each field.
// We are passed an array of db field names (fieldList), should match what is supplied in Layout and
// array of actual data ($row), array can also contain "<..> for html to be emitted as is
// if supplied the tagName is an html display tag to be used immediately before/after the data
//  e.g. tagName of address results in <address>data</address>
function displayHTMLforData($Conn, $layout, $fieldList, $row, $formatTag ='') {
    foreach ($fieldList as $item) {
        if (strstr($item, "<")) { // special tag
            echo $item;
        } else {  // potential data
            if (empty($row[$item])) { // nothing to do
                continue;
            }
            
            // check what type it is? To lookup in layout we need the display name
            if (!($label = findDisplayNameForDbCol($layout, $item))) {
                echo "ERROR - missing display data for $item";
                continue;
            }
 
            // display
            $dbType = $layout[$label]['dbType'];
            $access = $layout[$label]['access'];
            if (!$dbType || !$access) { // should always have these
                echo "ERROR - missing display data details for $label";
                continue;
            }

            if ($dbType == 'text' || $dbType == 'enum') {
                if ($formatTag) {
                    echo "<$formatTag>";
                }
                echo $row[$item]." ";
                if ($formatTag) {
                    echo "</$formatTag>";
                }
            }
                    
        }
    } // end for all input
} // end displayHTMLforData

// display HTML for flying day duties, i.e. OPS/TOW/CFIG
function displayHTMLforDuty($Conn, $calendarID, $MemberID, $readOnly, $dtype) {

    if ($msg = getCalendarInfo($calendarID, $etype, $notUsed, $notes, $results2, $res)) {
        DisplayMessage( "Display event details", "query failed ($msg)");
    }
    
    foreach ($results2 as $row2) {
        list($firstName, $lastName, $memberID) = explode(':', $row2['tag']);
        $ack = '';

        // adding FULL name handling..
        if ($msg = getMemberInfo($memberID, $memberInfo)) {
            DisplayMessage("Display event member details", "no member info($msg)");
        }
        $middleName = $memberInfo['middleName'];
        $suffix = $memberInfo['suffix'];

        $dateDiff = $row2['dateDiff']; # days from duty date to now, neg in past
        if ($dateDiff >= -2 && $dateDiff < ACK_DAY_LIMIT && $row2['ackCode']) { # special action when close to duty date
            if ($row2['ack']) {
                $ack = "<font color='green'>(ACK)</font>";
            }
            # if the person logged in is the same as the person with pending duty, but
            # but no ACK, allow them to ACK.
            if ($memberID == $MemberID && !$ack) {
                # let them ack
                $ack = " (<a href='flying_sched.php?action=ACK&calendarID=$calendarID&dtype=$dtype'>
                                     <font color='red'>Click ACK</font></a>)";
            } else if (!$ack) { # no match
                $ack = "<font color='red'>(ACK)</font>";
            } ; # end if - else,  ack duty
        } # end if - close to date
        
        if ($lastName == 'none' || ($dtype && $row2['dtype'] != $dtype)) {
            continue;
        }
	if (isset($_SESSION['subID']) && $memberID == $_SESSION['subID']) {
	    $lastName = "<b><font color='blue'>$lastName</font></b>";
	}

        $email = !empty($_SESSION['subID']) ? $row2['emailAddr'] : 'login required for email';
        
        $extra = '';
                ?>
        <address><?=$row2['dtype']?>: <a href="mailto:<?=$email?>"><?=$firstName." ".$middleName." ".$lastName." ".$suffix?></a>
        <?=$ack?>  <?=$extra?></address>
        <?
    } # end for each duty assignment for this event 

} // end displayHTMLforDuty

// display HTML for aircraft reservation given the Calendar ID
function displayHTMLforPlane($Conn, $calID, $MemberID, $readOnly) {

    $sql = "SELECT model, r.tailNum, CONCAT(DATE_FORMAT(startTime, '%H:%i'), '-', DATE_FORMAT(stopTime, '%H:%i')) as time, firstName, lastName, m.memberID, ack, ackCode, s.status, c.date, DATEDIFF(date,now()) as dateDiff, c.notes
                                  FROM Calendar c, Duty d, Glider g, GliderStatus s, Members m, Reservations r 
                                       WHERE c.CalendarID = $calID AND 
                                       r.tailNum = s.tailNum AND g.gliderID = s.gliderID AND c.CalendarID = d.CalendarID AND c.CalendarID = r.CalendarID AND c.CalendarID = d.CalendarID AND d.MemberID = m.MemberID AND m.memberID != 0";

    if ($msg = DB_Query($Conn, $sql, $resResults)) {
        DisplayMessage("Getting crew data", $msg, 0);
    }
 
    $foundTwo = 0;  # multiple crew members
    foreach ($resResults as $res) {
        
        // adding FULL name handling..
        if ($msg = getMemberInfo($res['memberID'], $memberInfo)) {
            DisplayMessage("Display fying res member details", "no member info($msg)");
        }
        $middleName = $memberInfo['middleName'];
        $suffix = $memberInfo['suffix'];

        $theName = $res['firstName'] ." ". $middleName . " ". $res['lastName'] . " " . $suffix;
        $theNameID = $res['memberID'];

        
        $rNotes = !empty($_SESSION['subID']) ? $res['notes'] : 'login required to see notes';
        $dateDiff = $res['dateDiff'];
        $ack = '';
	if (isset($_SESSION['subID']) && $theNameID == $_SESSION['subID']) {
	    $theName = "<b><font color='blue'>$theName</font></b>";
	}
         # do we mark the name for an ACK
        if ($dateDiff >= -2 && $dateDiff < ACK_DAY_LIMIT && $res['ackCode']) { # special action when close to duty date
            if ($res['ack']) {
                $ack = "<font color='green'>(ACK)</font>";
            }
            # if the person logged in is the same as the person with pending duty, but
            # but no ACK, allow them to ACK.
            if ($res['memberID'] == $MemberID && !$ack) {
                # let them ack
                $ack = " (<a title='Click to acknowledge duty' href='flying_sched.php?action=ACK&calendarID=$calID&dtype=CREW'>
                                     <font color='red'>Click ACK</font></a>)";
            } else if (!$ack) { # no match
               $ack = "<font color='red'>(ACK)</font>";
            } ; # end if - else,  ack duty
        } # end if - close to date

        # highlight the name if the same person logged in
        $theName = $theName . $ack ;

        # if we got a glider status of red/orange, tag it.
        $dotHTML = '';
        if ($res['status'] != 'GREEN') {
            $color = $res['status'];
            $img = "images/$color"."_dot.jpg";
            $dotHTML = "<img src='$img' width=12>";
        }
            
        
        if (!$foundTwo) { # do the first one we've seen.
            if (!$readOnly) {
                echo "<a href='edit_sched.php?calendarID=$calID&action=edit'>";
            }
            $shortTailNum = substr($res['tailNum'], -3);
            echo "<span title='({$res['tailNum']})$rNotes'>" . $dotHTML . $res['time'] . ":" . $res['model'] . "($shortTailNum)</span>/ ";
            if (!$readOnly) {
                echo "</a>:";
            }
            echo " $theName";
        }
        if ($foundTwo) { # we just got a partner
            echo ", $theName";
        }
        $foundTwo++;
    } # end for more
    echo "<br />";
} // displayHTMLforPlane

// Find the display field name give the dbCol into a Layout structure in config.inc
// Returns the name, or null if not found
function findDisplayNameForDbCol($layoutArray, $dbCol) {
    if (!$layoutArray || !$dbCol) {
         return("ERROR - findDisplayNameForDbCol called with missing data");
    }

    foreach ($layoutArray as $displayName => $params) {
        if ($params['dbCol'] == $dbCol) { // found it
            return($displayName);
        }
    } // end for - all items
        
    return '';
}
 

// Get info on an event
function getCalendarInfo($calendarID, &$etype, &$date, &$notes, &$results, &$reservation) {
    global $Conn;

    if (!($calendarID > 0)) {
        return("No event id supplied");
    }

    $sql = "SELECT CONCAT_WS(':', m.firstName, m.lastName, m.MemberID) as tag, c.etype as etype, c.title as title,
                c.notes as notes, DATE(c.date) AS date, d.type AS dtype, d.ack, d.ackCode,
                c.etype AS etype, m.emailAddr AS emailAddr,  DATEDIFF(date,now()) AS dateDiff 
            FROM Calendar c, Members m, Duty d
            WHERE m.MemberID = d.MemberID AND c.CalendarID = d.CalendarID AND c.CalendarID = $calendarID
            ORDER BY dtype, m.MemberID DESC";

    if ($msg = DB_Query($Conn, $sql, $results)) {
        return "event query failed ($msg)";
    }

    if (!count($results)) {
        return "No calendar events found for $calendarID";
    }

    $etype = $results[0]['etype'];
    $date  = $results[0]['date'];
    $notes = $results[0]['notes'];
    $reservation = "";
    
    // if the etype is PLANE res, get some more info
    if ($etype == "PLANE") {
        $sql = "SELECT tailNum, startTime, stopTime FROM Reservations WHERE CalendarID = $calendarID ORDER BY startTime";
        
        if ($msg = DB_Query($Conn, $sql, $reservation)) {
            return "Reservation query failed ($msg)";
        }
        
        if (!count($reservation)) {
            return "No aircraft reservation data for $calendarID";
        }
        //DB_Log("Res check", "etype($etype) for calID($calendarID), got ". print_r($reservation, TRUE));
    }
    return(0);
}

// return all the members in a club
function getClubMembers($clubID, &$memberList) {
    Global $Conn;

    if (!$clubID) {
	return("No club selected");
    }

    $memberList = Array();
    $sql = "SELECT @count:=@count+1 as 'count', MemberID, name, expireDate
	             FROM (SELECT M.MemberID, expireDate, CONCAT(firstName,' ',middleInitial,' ',lastName) as name
	             FROM Member M, ClubMember CM WHERE M.MemberID = CM.MemberID AND CM.ClubID = $clubID
	                ORDER BY M.lastName ASC)x,  (SELECT @count:=0) r  ";

    if ($msg = DB_Query($Conn, $sql, $results)) {
	return("Unable to get club members for clubID($clubID)($msg)");
    }

    $memberList = $results;
    return(0);

} // end getClubMembers

// getPhoto: given a glider or member id, return the URL for their photo
// and the full path
function getPhoto($ID, &$photoURL, &$filePath, $type='Member') {

    global $Conn;

    if (!$ID) {
       return("No ID supplied for $type");
    };
        
    $srchInfo['photo'] = '';
    if ($type == 'Member') {
       $field = 'MemberID';
       $table = 'Members';
    } else {
       $field = 'GliderID';
       $table = 'Glider';
    }
        
    $srchInfo[$field] = $ID;

    if ($msg = DB_Get($Conn, $table, $srchInfo, $results)) {
	return("Sorry, no photo");
    }
    $photoURL = PHOTO_DIR . "/". $results[0]['photo'];
    $filePath = ROOT_DIR . "/$photoURL";

    return(0);

} // end getPhoto


// This finds the matching member id from the info given
// name and/or email address
// the firstName is optional, used if present        
// returns msg on error
function getMemberID($lastName, $firstName, $emailAddr, &$memberID) {

    global $Conn, $Output;
    $ownerID = 0;

    // they have to give us at least one
    if (!$emailAddr && !$lastName) {
	return("missing email/lastName");
    }

    $srchInfo['lastName'] = $lastName;
    $srchInfo['firstName'] = $firstName;    
    $srchInfo['emailAddr'] = $emailAddr;
    $srchInfo['MemberID'] ="";

    if ($msg = DB_Get($Conn, "Members", $srchInfo, $results)) {
	$Output .= "\nERROR(findOwnerID error ($msg)\n";
	return("ERROR" .  __FUNCTION__ . " : $msg");
    }

    if (count($results) == 1) {
	$memberID = $results[0]['MemberID'];
	return(0);
    }

    return("too many/few match $lastName $firstName,$emailAddr");

} // end getMemberID


// return info on the specified member
function getMemberInfo($memberID, &$memberInfo) {
    Global $Conn;

    $memberInfo = Array();

    $srchInfo['MemberID'] = $memberID;
    if ($msg = DB_Query($Conn, "SELECT * FROM Members WHERE MemberID=$memberID", $results)) {
        return("ERROR" .  __FUNCTION__ . " : $msg");
    }

    if (( $num = count($results)) != 1) {
        return("Bad member count ($num) for ($memberID)!");
    }
    // unset the password
    unset($results[0]['password']);
    $memberInfo = $results[0];
    return(0);

} // end getMemberInfo

// This function checks the permissions of the specified user
// It returns the permission string
function getPerms($memberID, &$perms) {
    Global $Conn;

    if (!$memberID) {
	return("NO member supplied");
    }

    $perms = 'NONE';

    $srchInfo['MemberID'] = $memberID;
    $srchInfo['access'] = '';

    if ($msg = DB_Get($Conn, "Members", $srchInfo, $results)) {
	return("Permissions check: ". $msg);
    }

    $perms = $results[0]['access'];

    return(0);

} // end getPerms


// This function return true/false if the user has the desired permission
// Translations below to what is in access column
// 'member' - ALL, OWN_DATA
// 'sysadmin' - ALL
function hasPerms($desired) {
    Global $Conn;

    if (!isset($_SESSION['perms'])) {
        return(0);
    }
    
    $perms = $_SESSION['perms'];

    switch ($desired) {

	case 'member':
	    if (strstr($perms, 'ADMIN') || strstr($perms, 'MEMBER')) {
		return(1);
	    }
	    break;

	case 'admin':
            if (strstr($perms, 'ADMIN')) {
                return(1);
            }
            break;

        case 'edit_members':
            if (preg_match('/ADMIN|BOARD|CFIG|SCHEDULES|TOW/', $perms)) {
		return(1);
	    }
	    break;
            
	default:
   	    return(0);
    } // end switch


} // end hasPerms


// this function expects a Layout structure from lib/config.inc and is
// invoked after a form submission, confirm we have all the data required
// and returns a loaded array by reference.
// the return value of the function is missing required data
function loadSubmitDataCheckRequired($layoutArray, $isSuperUser, &$submitData) {
        # First store anything we might have gotten in a POST, don't want to lose it on
        # an error
        $missingList = '';  # did we get all the mandatory fields, we'll confirm below
        foreach ($layoutArray as $item => $params) {
            $field = $params['dbCol'];
            global $$field;
            $req = $params['req'];
            $privNeeded = $params['access'];
            
            $$field = script_param($field);
            $fieldVal = script_param($field);  // could be an array for a SET

            // if it's a field only a SUPER user can edit, and we're not, skip it
            if ($privNeeded == 'SUPER' && !$isSuperUser) {
                continue;
            }

            // is it a required field?
            if ((!$fieldVal || (!is_array($fieldVal) && (stristr($fieldVal, 'none') || stristr($fieldVal, 'Select'))   ) ) && 
                ( ($privNeeded == 'MEMBER' && $req) || $isSuperUser && $req)) {
                $missingList .= " $item, ";
            }
            
            if ($field == 'mentorID' && $fieldVal) { // format like  'Murtari:20' strip name
                list($junk, $fieldVal) = preg_split('/:/', $fieldVal);
            }
            
            if (is_array($fieldVal)) {
                # echo "<br>$field is ARRAY: ".print_r($fieldVal)."</br>";
                $fieldVal = implode(',', $fieldVal);
                $$field = $fieldVal;
            }

            if ($field == 'memberSince' && ($fieldVal < 1000 || $fieldVal == 'none')) {
                $fieldVal = '';
                $$field = $fieldVal;
            }

            // ZZ temp fix for type on Glider
            if (($field == 'type' || $field == 'seats') && $fieldVal == 'none') {
                $fieldVal = '';
                $$field = $fieldVal;
            }
            $submitData[$field] = $fieldVal;
        }
        return($missingList);
} // end loadParamsCheckRequired
        
// this function outputs the HTML for a select box of all members
// if the defaultMemberID is 'Select' -- show that as selected
function memberSelect($selectName, $defaultMemberID, $screen) {
    global $Conn;

    $extra = '';
    if ($screen) {
        if ($screen == 'CFIG') {
            $extra = "AND access LIKE '%CFIG%'";
        } else if ($screen == 'TOW') {
            $extra = "AND access LIKE '%TOW%'";
        }
    }
    $sql = "SELECT CONCAT_WS(':', lastName, MemberID) as tag, firstName, middleName, suffix FROM Members WHERE memberType != 'inactive' $extra ORDER BY lastName";

    if ($msg = DB_Query($Conn, $sql, $results)) {
        return($msg);
    }

    echo "<SELECT name = '$selectName'>\n";
    
    if ($defaultMemberID == 'Select') {
       echo  "<OPTION value='' SELECTED> Select </OPTION>\n";
    } else {    
        echo "<OPTION value='none:0'> Remove Member</OPTION>\n";
    }

    foreach ($results as $row) {
        list($lastName, $memberID) = explode(":", $row['tag']);
        if ($memberID == $defaultMemberID) {
            $selected = "SELECTED";
        } else {
            $selected = "";
        }
        $firstName = $row['firstName'];
        $middleName = $row['middleName'] ? " ${row['middleName']}" : '';
        $suffix = $row['suffix'] ? " ${row['suffix']}" : '';
        $name = "$lastName$suffix, $firstName$middleName";
        echo "<OPTION value=${row['tag']} $selected>$name</OPTION>\n";
    } // end for - all vals
    echo "</SELECT>\n";

} // end memberSelect

// Return "staffImitate" if staff logging in as customer
function processLogin($emailAddress, $pass, $control, &$staffImitate = 0) {
    global $sessionSubID, $Conn, $UserProfileInfo, $subID, $subName;

    // clear everything
    $sessionSubID = 0; // do we need this?
    $subID = 0;
    $subName = "";

    if ( isset($_SESSION['SUB_NAME']) || isset($_SESSION['subID']) ) {
         unset($_SESSION['SUB_NAME']);
         unset($_SESSION['subID']);
    }

    if ($msg = getMemberID("", "", $emailAddress, $sessionSubID)) {
  	return "No match on the email address you entered ($emailAddress) - $msg";
    }

    $subID = $sessionSubID;

    if( $msg = authorize($subID,$pass,$staffImitate)) {
  	return "Password incorrect".$msg;
    }

    if( $msg = getMemberInfo($subID, $UserProfileInfo) ) {
  	return $msg;
    }


    if  ($UserProfileInfo['memberType'] == "None" || $UserProfileInfo['memberType'] == 'Pending Delete' ) {
  	return ("Your login is temporarily disabled.  This E-Mail address is no longer
              a valid login.  To contact us you will need to go to our sign up page and
              register under a different E-Mail address.");

    } else {

  	// get permissions
        if ($msg = getPerms($subID, $perms)) {
  	    return("Permission check failed: $msg");
  	}

  	$_SESSION['perms'] = $perms;

  	// preferred way to use session vars
        $mi = '';
        if (!empty($UserProfileInfo['middleName'])) {
            $mi = $UserProfileInfo['middleName'] . ' ';
        }
  	$_SESSION['SUB_NAME'] = $UserProfileInfo['firstName'] . " $mi" .
                                preg_replace('/ /', '', $UserProfileInfo['lastName']) .
                                ' ' . $UserProfileInfo['suffix'];
  	$_SESSION['SUB_ADDR'] = $UserProfileInfo['emailAddr'];
  	$_SESSION['subID'] = $subID;

  	if ($msg = updateLastActive($subID, "login")) {
  	    return $msg;
  	}

    }

} # end processLogin


// This function trims a string of leading/trailing spaces
// and removes and more than one consecutive internal space.
function stripBlanks($string) {

    trim($string); // remove leading/trailing
    $string = preg_replace("/ +/", " ",$string);
    return($string);
}

function updateLastActive($subID) {
    global $Conn;

    $srchInfo['MemberID'] = $subID;

    $theDate = strftime("%Y-%m-%d %H:%M:%S", time());

    $updateInfo['lastActive'] = $theDate;
    $updateInfo['lastIP'] = $_SERVER['REMOTE_ADDR'];

    if( $msg = DB_Update($Conn, "Members", $srchInfo, $updateInfo) ) {
        return $msg;
    }
}


?>
