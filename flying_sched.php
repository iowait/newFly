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

$dtype = script_param('dtype');

$action = script_param('action');  # this is only set if coming from an ACK link
$calendarID = script_param('calendarID'); # only set with the above
$limit = script_param('limit');

if ($action == 'ACK') { # set the field
    if (!$calendarID) {
        DisplayMessage('Acknowledge Duty', "Invalid calendar event ($calendarID)");
    }
    $srchInfo['MemberID'] = $MemberID;
    $srchInfo['CalendarID'] = $calendarID;
    $updateInfo['ack'] = 1;
    if ($msg = DB_Update($Conn, 'Duty', $srchInfo, $updateInfo)) {
        DisplayMessage('Update Duty', "Failed for $msg");
    }
    $dType = "";
} # end if - ACK submission on form

$title = "Flying Schedule";
$dmsg = ' - All Teams';
if ($dtype) {
    $dmsg = " - Showing only $dtype";
}
if ($limit) {
    $dmsg = "";
    $title = "Individual Duty Schedule";
}

// this page is special, along with event_sched,  the header2.inc login check allows it through,
// but we remove the edit links
$readOnly = 0;
$msg = "<i>NOTE: Click on a date to edit details or change assignments. Mouse over an aircraft reservation to see notes entered for that flight.</i>";
$limitExtra = ''; // are we looking for a specific member

if (!isset($_SESSION['SUB_ADDR'])) {
    $readOnly = 1;
    $msg = "<b>#### Login required to see member/flight details or make schedule changes. ###</b><br />";
} else {
    if ($limit) { // limit to person logged in
        $limitExtra = "AND MemberID = " . $_SESSION['subID'];
    }
}

# if they just choose team scheds, we only go back a week
if ($dtype && $dtype !='CREW') {
    $sql = "SELECT DISTINCT(DATE(date)) AS sorter, DATE_FORMAT(date, '%m/%d') as date, DAYNAME(date) as day,  MONTHNAME(date) as month, etype, d.calendarID, c.notes  
       FROM Calendar c, Duty d
       WHERE etype = 'FLYING' AND YEAR(date) = YEAR(now()) $limitExtra AND c.CalendarID = d.CalendarID
            ORDER BY sorter";
} else {
   //    $msg .= "<i> Not showing past weeks. To see entire schedule click on a specific team under Schedules.</i>";
    $sql = "SELECT DISTINCT(date) AS sorter, DATE_FORMAT(date, '%m/%d') as date, DAYNAME(date) as day,  MONTHNAME(date) as month, etype, d.calendarID, c.notes  
       FROM Calendar c, Duty d
       WHERE (etype = 'FLYING' OR etype = 'PLANE') $limitExtra AND c.CalendarID = d.CalendarID
             AND TO_DAYS(date) + 1 >= TO_DAYS(NOW()) ORDER BY sorter, etype";
}
?>
<div  id="divContent">
    <h3><span style="font-size: 24px;"><?=$title?> <?=$dmsg?></span></h3>

    <? if ($limit) { ?>
        <p>NOTE: Just showing your scheduled duty assignments.
        </p>

    <? } else { ?>
    <p>The system schedules field duty and aircraft reservations. Members receive automatic notification of weekend duty or as a crew member. The <font color='green'>green (ACK)</font> indicates they've ACKnowledged pending duty.  Reminders to ACK are emailed on Tuesday/Wednesday at 10 AM.   They can ACKnowledge by a link in the email or on the web site if they have logged in, clicking on the <font color='red'>red (ACK)</font>.</p>
    A <img src='images/RED_dot.jpg' width=12>red/<img src='images/ORANGE_dot.jpg' width=12>orange dot preceding a glider reservations indicates a MX issue. Check the <a href="aircraft.php">Aircraft page</a> for details. A summary message to all members is sent at 4 PM on Thursday. </p>
    <? } ?>

    <p><?=$msg?></p>
    <table>
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center">
                    <?
                    if (!$readOnly) { ?>
                        <b><font color="blue"><<</font><a title="Setup a new scheduled flying day where you plan on assigning OPS, TOW, or CFIG" href="edit_sched.php?action=new&type=FLYING"> Insert new flying day/duty. </a><font color="blue">>></font></b>
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b><font color="blue"><<</font><a title="Reserve an aircraft for a flight on a specific date/time." href="edit_sched.php?action=new&etype=PLANE"> Make a new aircraft reservation. </a><font color="blue">>></font></font></b>
                    <? } else {
                      echo "&nbsp;";
                    }?>
                    <br />&nbsp;
                </td>
            </tr>
            <?
            if ($msg = DB_Query($Conn, $sql, $results)) {
                DisplayMessage("Display events",  "query failed ($msg)");
            }

            $lastDate = 'bongo';
            $lastMonth = 'bongo';
            $entryCount = 0;
            $spacer = 'grey';
            $spaceRow = "<td colspan='5' bgcolor='$spacer'><span style='font-size: 1px;'>&nbsp;</span></td>";

            for ($i = 0; $i < count($results); $i++) {
                
                $row = $results[$i];
                $etype = $row['etype'];
                $rdate = $row['date'];
                
                //DB_Log("Results $i", print_r($row, TRUE));
                
                $month = $row['month'];  // only show first month and then a change

                if ($lastMonth != $month) {
                    $lastMonth = $month;
                    $displayMonth = "<td bgcolor='grey' align='center' colspan='5'><span style='font-size: 18px;'>$month</span></td>";
                    $entryCount = 0;  // new row of entries
                } else {
                    $displayMonth = '';
                }

                if ($lastDate == $row['date']) {
                    #continue;
                }
                $lastDate = $row['date'];
                $notes='';
                if ($row['notes']) {
                    $notes = "NOTE: ".$row['notes'];
                }
                $calendarID = $row['calendarID'];
                $date = $rdate . " - " . $row['day'];

                if ($displayMonth) { ?>
                <tr><?=$spaceRow?></tr>
                <tr><?=$displayMonth?></tr>
            <? }
            
            if (!$entryCount || (!($entryCount %2))) {
                echo "\n<tr>$spaceRow</tr>\n";
                echo "<tr>\n";
            }

            ?>

                <td width="180">
                    <? if ($readOnly || $etype == 'PLANE') { ?>
                        <address><?=$date?></address>
                    <? } else { ?>

                        <address><a href='edit_sched.php?calendarID=<?=$calendarID?>&action=edit'>
                            EDIT: <?=$date?></a></address>
                    <? } ?>
                    <p><? if ($etype!='PLANE') {
                        echo nl2br($notes);
                       }
                       ?>
                    </p>
                </td>

                <td width="270">
                    <?
                    $res = "";
                    // special check here, it is possible to reserve a plane on a day
                    // without a normal flyings schedule entry (weekday)
                    if ($etype == 'FLYING') {
                        displayHTMLforDuty($Conn, $calendarID, $MemberID, $readOnly, $dtype);
                    } else if ($etype == 'PLANE') {
                        displayHTMLforPlane($Conn, $calendarID, $MemberID, $readOnly);
                    } else {
                        displayMessage("Show schedule", "Unexpected etype($etype)", 0);
                    }
              
                    
                    # is there a plan reservation next for the SAME day?
                    $foundRes = 0;
                    while (isset($results[$i+1]['etype']) && $results[$i+1]['etype'] == 'PLANE'
                         && $results[$i+1]['date'] == $rdate) { # we post the info
                        $i++; // skip it in the next loop since we're processing here
                        $calID = $results[$i]['calendarID'];
                        if (!$foundRes++) {
                            echo "<hr />\n";
                        }
                        displayHTMLforPlane($Conn, $calID, $MemberID, $readOnly);
                    } // end while
                       
               ?>
                </td>
                <td width='2' bgcolor='<?=$spacer?>'>
                </td>
                
             <?
             $entryCount++;  # how many entries have we completed, used for row breaks
             if (!($entryCount % 2)) {
                 echo "\n   </tr>\n";
             }
             } # end for - all flying events
             ?>
             
         <tr>
             <td colspan="5" style="text-align: center" >
                 <? if (!$readOnly) { ?>
		     <br /><br />
                     <b><a title="Setup a new scheduled flying day where you plan on assigning OPS, TOW, or CFIG" href="edit_sched.php?action=new&type=FLYING"><< Insert new flying day/duty. >></a></b>
                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                     <b><a title="Reserve an aircraft for a flight on a specific date/time." href="edit_sched.php?action=new&etype=PLANE"><< Make a new aircraft reservation. >></a></b>
                     <? } else {
                     echo "&nbsp;";
                    }?>
             </td>
         </tr>
         
        </tbody>
    </table>

</div>

<!-- Content ends here -->
<?php
require 'footer.inc';
?>
