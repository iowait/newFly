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

// this page is special, along with flying_sched,  the header.inc login check allows it through,
// but we remove the edit links
$readOnly = 0;
$msg = "Click on a date to edit event details.";
if (!isset($_SESSION['SUB_ADDR'])) {
    $readOnly = 1;
    $msg = "Login required to make schedule changes.";
} 

$etype = 'SPECIAL';
$sql = "SELECT DATE(date) AS date, DAYNAME(date) as day,  MONTHNAME(date) as month, etype, title, calendarID, notes  FROM Calendar c
          WHERE etype = '$etype' AND TO_DAYS(date) + 30 > TO_DAYS(NOW()) ORDER BY date";
?>
        <div  id="divContent">
            <h3><span style="font-size: 24px;">SPECIAL Event Calendar</span></h3>
            <p><?=$msg?></p>
            <input type='checkbox' name="boardMode" id="boardMode" CHECKED > Display <?=CLUB_NAME?> Board Meetings
               <table>
                   <tbody>
<? if (!$readOnly) { ?>
    <tr>
        <td colspan="3">
            <b><a href="edit_sched.php?action=new&etype=<?=$etype?>">Insert new event.</a></b>
        </td>
    </tr>
<? } ?>
    

<?
if ($msg = DB_Query($Conn, $sql, $results)) {
    DisplayMessage("Display events",  "query failed ($msg)");
}

$lastMonth = 'bongo';

foreach ($results as $row) {

    $calendarID = $row['calendarID'];
    $title = $row['title'];
    $notes = $row['notes'];
    $date  = $row['date'] . " / " . $row['day'];

    $month = $row['month'];  // only show first month and then a change
    
    if ($lastMonth != $month) {
        $lastMonth = $month;
        $displayMonth = "<span style='font-size: 18px;'>$month</span>";
    } else {
        $displayMonth = '';
    }

    if ($displayMonth) {
        $extraRow = "<tr><td colspan='3'><span style='font-size: 18px;'>&nbsp;</span></td></tr>\n";
    } else {
        $extraRow = '';
    }

    // we setup a special class for Board Meeting events, make them easy to show/hide
    $boardClass = '';
    if (stristr($title, 'board')) {
        $boardClass = "class='normal'";
    }

?>
    <?=$extraRow?>
                           
    <tr>
        <td colspan="2"><hr/>
        </td>
        <td>
            &nbsp;&nbsp;<?=$displayMonth?>
        </td>
    </tr>
    
   <tr <?=$boardClass?>>
        <td width="250">
	    <b><?=$title?></b>
            <? if ($readOnly) { ?>
                <address> <?=$date?></address>
            <? } else { ?>
                <address>EDIT: <a href='edit_sched.php?calendarID=<?=$calendarID?>&action=edit&etype=<?=$etype?>'>
                    <?=$date?></a></address>
            <? } ?>
            
        </td>
        <td>
	    &nbsp; &nbsp;
        </td>
        <td>
            <?=nl2br($notes)?>
        </td>
    </tr>

<?
}
?>

            
                   </tbody>
               </table>
               <script type="text/javascript">

                $(function(){
                    $('input[name="boardMode"]').change(function(e){
                        
                        var btn = document.getElementById('boardMode');
                        
                        if (btn.checked) {
                            $('tr.normal').show();
                        } else {
                            $('tr.normal').hide();
                        }
                    });
                });

               </script>
        </div>

        <!--   ------------------ Content ends here ---------------------------------- -->
<?php
  require 'footer.inc';
?>

