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

// This page is the target of an email link, it allows them
// to acknowledge getting a duty notification.  It checks for
// a match on the ackCode with what's in the Duty table.
// No login is required.

// we should get one of the ack Codes, but not both
$ackCode = script_param("ack");
$ackSuper = script_param("ackSuper"); // if we get this, calendarID doesn't matter

$memberID = script_param("id");
$calendarID  = script_param("cal");

if ( (!$ackCode && !$ackSuper) || ($ackCode && $ackSuper) || !$memberID ||
    ($ackCode && !$calendarID) || ( strlen($ackCode)<20 && strlen($ackSuper) < 20 ) ) {
    sleep(5);
    DisplayMessage("Acknowledge Duty", "Unavailable at this time($ackCode/$ackSuper,$memberID,$calendarID).");
}


// do we match
unset($srchInfo);
$srchInfo['ack'] = '';

if ($ackCode) {
    $srchInfo['ackCode'] = $ackCode;
    $srchInfo['CalendarID'] = $calendarID;
} else {
    $srchInfo['ackSuper'] = $ackSuper;
}
$srchInfo['MemberID'] = $memberID;

if ($msg = DB_Get($Conn, 'Duty', $srchInfo, $results)) {
   DisplayMessage("Find your duty in the system", $msg);
}

# we don't like duplicate attempts on a single
if ($ackCode && $results[0]['ack']) { # already set?
    DisplayMessage("Acknowledge duty", 
                   "The acknowledgement has already been processed and should appear on the schedule in green.");
}

if ( ($ackCode && count($results) != 1) || (!count($results) && $ackSuper) ) {
   DisplayMessage("Acknowledge duty", "Bad link or duplicate ACK attempt - $msg");
}

# OK, we found it, set the ack
$updateInfo['ack'] = 1;

if ($msg = DB_Update($Conn, 'Duty', $srchInfo, $updateInfo)) {
    DisplayMessage("Update your acknowledge in the system", $msg);
}

                    ?>
<h2>Thank you!  Your acknowledgment has been recorded.</h2>
You should receive no further email messages.
<p>
    Any questions or recommendations about our acknowledgement sytem?
    Please <a href='mailto:webmaster@fly.org?subject="FLY - Duty"'>
    send email to our webmaster.</a>
</p>
<?

include('footer.inc');
?>

