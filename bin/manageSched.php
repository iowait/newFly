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
#
# manageSched takes care of duty notifications, has several different modes

# manageSched     :  with no args checks for anyone with duty in the next 6
# days who has not ack'd and sends them a reminder.
# manageSched -A  :  Alert scan to see if anyone still has not ack'd, and sends
#                    an email to the DutyContact alerting them
# manageSched -S  :  Send a message to all members regarding duty for the weekend,
#                    note missing acks.
<?php
require("php_includes.inc");
$TEST_MODE = 1;  # only for testing
$DAY_LIMIT = ACK_DAY_LIMIT;  # how far we look ahead

function sendMemberMsg($emailAddr, $name, $details, $contactEmail) {

    global $TEST_MODE, $DAY_LIMIT;
    $body = "Dear $name,

This message is to alert you of duty you have upcoming in the 
next $DAY_LIMIT days.

If you WILL BE present please just click on the following link(s) and the system
will record you as acknowledged.  You'll receive no further messages.

$details

If you can't make it, please arrange a swap and update the schedule at our site:

". NON_SECURE_URL . FLY_DIR ."/login.php

Thanks for your cooperation and help!
All the Members of FLY Soaring Club

";
    if ($TEST_MODE) {
        sleep(5);
        $emailAddr = SERVICE_EMAIL;
    } else {
        sleep(30);
    }
    
    if ($status = sendEmail($emailAddr, SERVICE_EMAIL,
                            "FLY - Scheduled Duty", $body)) {
        echo "ERROR on send to $emailAddr, $status\n";
    }
    DB_Log('INFO', "Duty scheduled email sent to: $emailAddr");
} // end function sendMemberMsg

# get our mode
if (!isset($argv[1])) {
    $mode = 'normal';
} else if ($argv[1] == '-A') {
    $mode = 'alert';
} else if ($argv[1] == '-S') {
    $mode = 'send';
} else {
    echo "Bad option: $argv[1]\n";
    exit(1);
}

echo "Mode is $mode, TEST_MODE is $TEST_MODE\n";
sleep(10);

DB_Log('INFO', "Starting manageSched.php in mode is $mode, TEST_MODE is $TEST_MODE");



# SPECIAL - we want BOARD members to get a message and do the ack cycle, but need
# to put them in the Duty table.  We do this automatically $DAY_LIMIT days prior to the meeting
$sql = "SELECT C.CalendarID FROM Calendar C WHERE TO_DAYS(C.date) - TO_DAYS(now()) < $DAY_LIMIT AND 
          TO_DAYS(C.date) - TO_DAYS(now()) >= 0  AND C.title LIKE 'board meeting%' AND 
          C.etype='SPECIAL' ORDER BY C.date LIMIT 1";
if ($msg = DB_Query($Conn, $sql, $results)) {
    DisplayMessage("Check for BOARD meeting",  "query failed ($msg)");
}

$numB = count($results);
echo "\n\nGot $numB results for BOARD meetings in next $DAY_LIMIT days.\n";

if ($numB) { # we add them to Duty table if not present
    $boardCalendarID = $results[0]['CalendarID'];
    echo "Board CalendarID ($boardCalendarID)\n";
    $sql = "SELECT C.CalendarID, C.notes, DATE_FORMAT(C.date,'%M %d')as date, DAYNAME(C.date) AS day, 
                       D.MemberID, D.type, D.ack, M.lastName, M.firstName, M.emailAddr ,M.phone
                   FROM Calendar C, Duty D, Members M 
                  WHERE C.CalendarID = $boardCalendarID 
                       AND TO_DAYS(C.date) - TO_DAYS(now()) < $DAY_LIMIT AND TO_DAYS(C.date) - TO_DAYS(now()) >= 0 
                       AND C.CalendarID = D.CalendarID AND D.type = 'BOARD' AND C.etype='SPECIAL' 
                       AND D.MemberID = M.MemberID AND D.ack = 0 AND M.lastName !='none' ORDER BY C.date";
    
    if ($msg = DB_Query($Conn, $sql, $results)) {
        DisplayMessage("Check for BOARD meeting members in Duty table",  "query failed ($msg)");
    }
    
    $num = count($results);
    if (!$num) { # add them
        # get all the BOARD members
        $sql = "SELECT MemberID FROM Members WHERE FIND_IN_SET ('BOARD', access)";
        if ($msg = DB_Query($Conn, $sql, $results)) {
            DisplayMessage("Query for Board members ",  "query failed ($msg)");
        }
        
        $num = count($results);
        echo "\n\nGot $num results for BOARD members, will add to Duty Table for CalendarID $boardCalendarID\n";
        $insertInfo['CalendarID'] = $boardCalendarID;
        $insertInfo['type'] = 'BOARD';
       
        foreach($results as $row) { # add them
            $insertInfo['MemberID'] = $row['MemberID'];
            if ($msg = DB_Insert($Conn, 'Duty', $insertInfo)) {
                DisplayMessage("Insert duty entries for BOARD",
                               $msg, 1);
            }
        } # end for - all BOARD
    } # end if - missing Duty entries
} # end if - upcoming BOARD meeting

if ($numB) {
    $dTypes = array('OPS', 'TOW', 'CFIG', 'CREW', 'BOARD');
} else {
    $dTypes = array('OPS', 'TOW', 'CFIG', 'CREW');
}

# Store all the glider model/id info - couldn't do it in the SQL query
if ($msg = DB_Query($Conn, "SELECT g.gliderID, model, tailNum FROM Glider g, GliderStatus s WHERE 
                     g.GliderID = s.GliderID ORDER BY tailNum", $results)) {
    DisplayMessage("Check gliders", $msg);
}

// load an assoc array
foreach ($results as $row) {
    $gliders[$row['tailNum']] = $row['model'];
}

# track transitions between duty type
# NOTE - break after duty type normal, only one query done.
#  if sending to all, first query
#  collects/formats message info
foreach ($dTypes as $duty) {

    $body = "";  # start of an email message
    $lastDate = 'bongo';
    
    if ($TEST_MODE) {
        $contactEmail = SERVICE_EMAIL;
    } else if ($mode == 'send') {
        $contactEmail = SERVICE_EMAIL;  # mailing list
    } else {
        $contactEmail = $DutyContact[$duty]; // the RI for this duty type, top of script
    }
    
    if ($mode == 'normal') { # just to people with duty - this query gets ALL duty types
        $sql = "SELECT C.CalendarID, C.notes, DATE_FORMAT(C.date,'%M %d')as date, DAYNAME(C.date) AS day,  D.MemberID, D.type, D.ack, M.lastName, M.firstName, M.emailAddr ,M.phone, M.mentorID,
                DATE_FORMAT(startTime,'%H:%i') as startTime, DATE_FORMAT(stopTime, '%H:%i') as stopTime, tailNum
                 FROM  Duty D, Members M, Calendar C 
                       LEFT JOIN Reservations R ON  R.calendarID = C.CalendarID
                 WHERE TO_DAYS(C.date) - TO_DAYS(now()) < $DAY_LIMIT AND TO_DAYS(C.date) - TO_DAYS(now()) >= 0 AND C.CalendarID = D.CalendarID AND D.MemberID = M.MemberID AND D.ack = 0 AND M.lastName !='none' ORDER BY M.MemberID, date";
        
    } else if ($mode == 'alert') {  # message to RIs about missing ACKs

        $sql = "SELECT C.CalendarID, C.notes, DATE_FORMAT(C.date,'%M %d')as date, DAYNAME(C.date) AS day,  D.MemberID, D.type, D.ack, M.lastName, M.firstName, M.emailAddr ,M.phone, M.mentorID,
                DATE_FORMAT(startTime,'%H:%i') as startTime, DATE_FORMAT(stopTime, '%H:%i') as stopTime, tailNum
                 FROM  Duty D, Members M, Calendar C 
                       LEFT JOIN Reservations R ON  R.calendarID = C.CalendarID
                 WHERE TO_DAYS(C.date) - TO_DAYS(now()) < $DAY_LIMIT AND TO_DAYS(C.date) - TO_DAYS(now()) >= 0 AND C.CalendarID = D.CalendarID AND D.MemberID = M.MemberID AND D.type = '$duty' AND D.ack = 0 AND M.lastName !='none' ORDER BY M.MemberID, date";

    } else if ($mode == 'send') { # notify everyone with assigned duty

         $sql = "SELECT C.CalendarID, C.notes, DATE_FORMAT(C.date,'%M %d')as date, DAYNAME(C.date) AS day,  D.MemberID, D.type, D.ack, M.lastName, M.firstName, M.emailAddr ,M.phone, M.mentorID,
                DATE_FORMAT(startTime,'%H:%i') as startTime, DATE_FORMAT(stopTime, '%H:%i') as stopTime, tailNum
                 FROM  Duty D, Members M, Calendar C 
                       LEFT JOIN Reservations R ON  R.calendarID = C.CalendarID
                 WHERE TO_DAYS(C.date) - TO_DAYS(now()) < $DAY_LIMIT AND TO_DAYS(C.date) - TO_DAYS(now()) >= 0 AND C.CalendarID = D.CalendarID AND D.MemberID = M.MemberID AND M.lastName !='none' ORDER BY date, type ASC, M.MemberID";
    }
    
    if ($msg = DB_Query($Conn, $sql, $results)) {
        DisplayMessage("Display events",  "query failed ($msg)");
    }
    
    $num = count($results);
    if ($mode != 'normal') {
        echo "\n\nGot $num results for $duty\n";
    } else {
        echo "\n\nGot $num results for member duty assignments\n";
    }

    $priorEmail      = ''; // these used for normal mode
    $priorName       = '';
    $priorMemberID   = 0;
    $priorCalendarID = 0;
    $priorAckSuperString = '';
    $priorAckSuper   = 0;
    $count           = 0;  // multiple duty for one person
    $firstLoop       = 1;  // pretty bad!

    // put a marker at the end
    $results[$num]['type'] = 'END';
    
    foreach($results as $row) { # let them know

        // are we done processing normal alerts
        if ($row['type'] == 'END') { # did we do any ?
            if (!$priorEmail) { // nothing was found
                break;
            } else if ($mode == 'normal') { # send the last member email
                if (!$ackSuperString) {
                    $ackSuperString = "\nNOTE: YOU MAY ACKNOWLEDGE ALL DUTY IN THIS MESSAGE\n -->" . NON_SECURE_URL . FLY_DIR . "/ack_duty.php?ackSuper=$ackSuper&id=$memberID\n\n";
                }
                sendMemberMsg($priorEmail, $priorName, $details . ($count>1 ? $ackSuperString : ''), $contactEmail);
                break;
            }
        } // end if - no more results
  
        $emailAddr = $row['emailAddr'];
        $lastName  = $row['lastName'];
        $firstName = $row['firstName'];
        $date      = $row['date'];
        $day       = $row['day'];
        $type      = $row['type'];
        $memberID  = $row['MemberID'];
        $calendarID= $row['CalendarID'];
        $phone     = $row['phone'];
        $ack       = $row['ack'];
        $notes     = $row['notes'];
        $startTime = $row['startTime'];
        $stopTime  = $row['stopTime'];
        $tailNum   = $row['tailNum'];
        $mentorID  = $row['mentorID'];

        echo "$date - $type - $notes - $lastName - $emailAddr\n";

        $notesMsg = "No notes were entered for this event.";
        if ($notes) {
            $notesMsg = "The following notes were entered for this on the calendar:
$notes";
        }
        
        if ($mode == 'normal') { # we set their ack codes and send them one consolidated email

            if ($type == 'CREW') { // must be a plane here
                $notes .= " - {$gliders[$tailNum]} ($tailNum) : $startTime-$stopTime ";
            }
        
            $ackCode = md5(time() + rand()); // on a per duty item basis
            $ackString = "$date/$day -->" . NON_SECURE_URL . FLY_DIR . "/ack_duty.php?ack=$ackCode&id=$memberID&cal=$calendarID  
    [ $type - $notes ]\n\n";
            
            if ($firstLoop) { # just getting started, init strings
                $details = $ackString;
                $priorEmail = $emailAddr;
                $priorName = "$firstName $lastName";
                $priorMemberID = $memberID;
                $ackSuper =  md5(time() + rand()); // on a per member basis
                $priorAckSuper = $ackSuper;
                // below only gets used if we never see another person.
                $ackSuperString = "\nNOTE: YOU MAY ACKNOWLEDGE ALL DUTY IN THIS MESSAGE\n --> ". NON_SECURE_URL . FLY_DIR . "/ack_duty.php?ackSuper=$priorAckSuper&id=$priorMemberID\n\n";
                $count = 1;
            }
            
            if ($priorEmail != $emailAddr) { // change in email
                $ackSuper =  md5(time() + rand()); // on a per member basis                
            }
            
            $updateInfo['ackCode'] = $ackCode;
            $updateInfo['ackSuper'] = $ackSuper;
            
            $srchInfo['MemberID'] = $memberID;
            $srchInfo['CalendarID'] = $calendarID;
            $srchInfo['type'] = $type;
            
            if ($msg = DB_Update($Conn, 'Duty', $srchInfo, $updateInfo)) {
                DB_Log('ERROR', "Load ack code for $memberID / $calendarID in the system $msg");
                continue;
            }
            
            if ($priorEmail != $emailAddr) { # finished one, starting a new person, the last person never gets here.

                $ackSuperString = "\nNOTE: YOU MAY ACKNOWLEDGE ALL DUTY IN THIS MESSAGE\n --> "  . NON_SECURE_URL . FLY_DIR .  "/ack_duty.php?ackSuper=$priorAckSuper&id=$priorMemberID\n\n";
                # send out the email
                sendMemberMsg($priorEmail, "$priorName", $details . ($count>1 ? $ackSuperString:''), $contactEmail);
                $ackSuperString = '';
                $count = 1;
                $details = $ackString;
                $priorEmail = $emailAddr;
                $priorMemberID = $memberID;
                $priorName = "$firstName $lastName";
                $priorAckSuper = $ackSuper;
               
            } else if (!$firstLoop) { # continue, just adding on to existing
                
                $details .= $ackString;
                $count++;
                
            }; // end if - else, start of new one, or end of current, or continue
            
            $firstLoop = 0;
          
        } else if ($mode == 'alert') {  # we collect the info

            // SPECIAL:  if the person has a mentor, an immediate alert is sent to them.
            // If the person has no mentor, an Instructor, the alert is sent to CREW coord.
            
            if ($type == 'CREW' && $mentorID) {
                if ($msg = getMemberInfo($mentorID, $mentorInfo)) {
                    echo "ERROR - can't get mentor info for mentorID: $mentorID\n";
                } else { // send message
                    $mentorEmail = $TEST_MODE ? $contactEmail : $mentorInfo['emailAddr'];
                    echo "Sending mentor alert message to $mentorEmail for $lastName\n";
                    if ($status = sendEmail($mentorEmail, SERVICE_EMAIL, 'No CREW ACK from your student '. "$firstName $lastName", 
                                      "$notesMsg\n\nNo acknowledgment yet from $lastName for $duty on $date.\n".
                                      "       $startTime - $stopTime, {$gliders[$tailNum]} ($tailNum)\n".
                                            " Two requests were sent to: $emailAddr.  Their primary contact phone is: $phone\n\n")) {
                        echo "ERROR on send to $contactEmail for CREW mentor alert";
                    }
                }
            } else { // not a CREW with mentor

                $body .= "$notesMsg\n\nNo acknowledgment yet from $lastName for $duty on $date.\n";
                if ($type == 'CREW') { // add the glider info/times
                    $body.= "       $startTime - $stopTime,  {$gliders[$tailNum]} ($tailNum)\n" ;
                }
                $body .=" Two requests were sent to: $emailAddr.  Their primary contact phone is: $phone\n\n";
            }

        } else if ($mode == 'send') { # put together message to members

            if ($date != $lastDate) {
                $body .= "\nDuty assignments for $date:\n$notesMsg\n\n";
                $lastDate = $date;
            }
            
            if ($ack) {
                $ack = "ACK recv'd";
            } else {
                $ack = "No duty ACK recv'd yet.";
            }
            $body .= "   $type - $firstName $lastName ($emailAddr) - $ack\n";
            if ($type == 'CREW') { // add the glider info/times
                $body.= "       $startTime - $stopTime, {$gliders[$tailNum]} ($tailNum)\n" ;
            }
        }

    } // end for - duty alerts for day

    // if we were running in normal mode we're done, every duty has been processed
    if ($mode == 'normal') {
        break;
    }

    // If we are doing alerts to the duty RI, send it ( if necessary)
    if ($mode == 'alert') {

        if (!$body) { # clean!
            echo "No duty alert for $duty necessary.\n";
            $subject = "FLY - A-OKAY, no Duty Alerts for $duty this weekend.";
            $body = "$duty coordinator,

Happy News!  All members have acknowledge $duty for this weekend.
No action is necessary.\n";
            
        } else { # late
            $subject = "FLY - Duty Alerts for $duty this weekend";
            $body = "$duty coordinator,

The following members have not acknowledged duty for the upcoming
weekend.  Details follow:

". $body ."

NOTE: This message was generated by our system to alert members of
weekend duty. Your feedback and recommendations are welcome, send to:
" . SERVICE_EMAIL;
            
        } // end if-else alert message

        echo "Sending $duty Alert message to $contactEmail: $subject\n";
        if ($status = sendEmail($contactEmail, SERVICE_EMAIL,
                                $subject, $body)) {
            echo "ERROR on Duty alert email to $contactEmail, $status\n";
        }
        DB_Log('INFO', "$duty Alert email sent to: $contactEmail");
        
    } else if ($mode == 'send') {
        echo "Sending duty message to all members mailist";
        $body = "Members,

    Below is the current status of duty assignments for this weekend.

NOTE: We'd like the members with duty as OPS, TOW, or CFIG to be at
the field no later than 0900.  Our goal is to start flying by 0930.

Those who have acknowledged a duty reminder thru our website have been
marked:

". $body . "

Weekend Weather Forecast: You may find the following two sources useful 
for Dansville weather.  

   WUNDERGROUND: If you scroll down below the 10 day forecast, we
   can recommend the human created 'Scientific Forecaster Discussion':
   https://www.wunderground.com/q/zmw:14437.1.99999

   NATIONAL WEATHER SERVICE: https://forecast.weather.gov/MapClick.php?lat=42.56063000000006&lon=-77.6933899


NOTE: This message was generated by our system to help automate
weekend duty notification. Your feedback and recommendations are welcome,
send to:". SERVICE_EMAIL. "
" ;
 
        if ($status = sendEmail(MAIL_LIST_EMAIL, SERVICE_EMAIL,
                                "Club flying duty assignments", $body)) {
            echo "ERROR on club members email to $contactEmail, $status\n";
        }
        DB_Log('INFO', "Members email sent to: ". MAIL_LIST_EMAIL);
        
        break;  # exit early
    } // end if else - mode of alert/send
    
} // end for - all duty types

?>

