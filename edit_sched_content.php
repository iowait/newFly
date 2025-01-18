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
<div class="clsContent-mid5">

    <form id ="inputform" method="POST" action="edit_sched.php">
        <table style="border-collapse: collapse" border="0" bordercolor="#111111" cellpadding="5" cellspacing="0" width="100%">
	    <tr>
                <td colspan='2'>
		    <? if ($etype == 'SPECIAL') { ?>
		        <h3><span style="font-size: 24px;">Edit Event Calendar Info</span></h3>
			Enter the info for the event.  If it covers a range of dates, enter the starting date, and then explain the range in the notes.
			
                    <? } else if ($etype != 'PLANE') { ?>
                        <h3><span style="font-size: 24px;">Edit Calendar Info</span></h3>
                        This screen is for setting up a flying day by assigning OPS/TOW or CGI. <br /><b><font color="blue">If you wish to just schedule an aircraft -> <a href="edit_sched.php?action=new&etype=PLANE"><< Click here. >></a></font></b>
                    <? } else { ?>
                        <h3><span style="font-size: 24px;">Edit/Create Aircraft Reservation</span></h3>
                        This screen is for scheduling an aircraft on a specific date/time. <br /><b><font color="blue">If you wish to just establish a flying day and assign OPS/CFIG/TOW -> <a href="edit_sched.php?action=new&etype=FLYING"><< Click here. >></a></font></b>

                    <? } ?>
                    </p>
		</td>
            </tr>

            <tr>
                <td align="right" width="140"><font color="red"><b>*</b></font>Event Type:</td>
                <td align="left" width="500">
                    <?=$etype?>
                    <input type="hidden" name="etype" value = "<?=$etype?>">
                    <input type="hidden" name="calendarID" value = "<?=$calendarID?>">
                    <?
                    if ($etype == 'SPECIAL') { ?>
                        <br />Title: <input name="title" size="80" value="<?=$title?>">
                    <? }  ?>

                </td>
            </tr>

            <tr>
                <td align="right" width="140"><font color="red"><b>*</b></font>Date:</td>
                <td align="left" height="29" width="500" colspan="2">
		    <? if ($action == 'edit' || $action == 'update') { // read only date ?>
                        &nbsp;&nbsp;<?=$date?> <i>(cannot be changed - if incorrect event must be deleted</i>)
		        <input  name="startDate" type='hidden' value="<?=$date?>" id="startDate">
                    <? } else { ?>
                        <input  name="startDate" value="<?=$date?>" id="startDate" size="20">
		    <? } ?>
                </td>
            </tr>

            <tr>
                <td align="right" width="140">Notes regarding this entry:</td>
                <td align="left" width="500" colspan="2">
                    <textarea  name="notes" cols="45" rows="4" ><?=$notes?></textarea>
                </td>
            </tr>



            <? if ($etype == 'FLYING') { ?>

                <tr>
                    <td align="right" width="140"> Use the selection boxes below to make changes:</td>
                    <td align="left" width="500" colspan="2">

                        <UL>
                            <LI>REMOVE - Choose either 'Remove' at the top of the list, or the 'None' member.</LI>
                            <LI>ADD - It's possible to have multiple people on duty.  You will always see an additional box available where you can choose another member.  If people will share duty, please make an entry in the notes to explain.</LI>
                            <LI>DELETE - ONLY choose this if you wish to remove the entire item from the schedule.</LI>
                        </UL>
                    </td>
                </tr>


                <tr>
                    <td align="right" width="140">Members:</td>
                    <td>
                        <?
                        $lastdtype = 'bongo';
                        foreach ($results as $row) {
                            $dtype = $row['dtype'];
                            if (!$dtype) {
                                continue;
                            }
                            $saw[$dtype] = 1;  // keep track of what we actually got.

                            if ($lastdtype != $dtype)  { // start a new row
                                if ($lastdtype != 'bongo' && $lastName != 'none') { // add one more of the last kind
                                    memberSelect($lastdtype."[]", 0, $lastdtype); // this creates select box of members
                                }
                                $lastdtype = $dtype;
                                echo "<br>$dtype: ";
                            }
                            list($firstName, $lastName, $memberID) = explode(":", $row['tag']);
                            // can have more than one in a type, make an array
                            memberSelect($dtype."[]", $memberID, $dtype); // this creates select box of members
                        } // end for - all results

                        if ($lastName != 'none') {
                            memberSelect($dtype."[]", 0, $dtype); // this creates select box of members
                        }

                        // did we get at least something for each box
                        foreach (array('OPS','TOW','CFIG') as $chk) {
                            if (!isset($saw[$chk])) {
                                echo "<br />$chk: ";
                                memberSelect($chk."[]", 0, $chk); // this creates select box of members
                            }
                        }

                        } // end if - FLYING EVENT
                        ?>

                        

                        

                        <? if ($etype == 'PLANE') {   //  PLANE ######################################?>

                            <tr>
                                <td align="right" width="140"> Use the selection boxes below in order:<br />
                                    1) Pick ONE aircraft,<br />
                                    2) start/stop times,<br />
                                    3) and then crew.
                                </td>
                                <td align="left" width="500" colspan="2">

                                    <UL>
                                        <LI>ADD a crew members - Pick your first crew member and submit the form.  When the screen refreshes you will see an additional box available where you can choose another member.<br /> <i>Remember, more than 2 is a tight fit!</i></LI>
                                        <LI>REMOVE a crew member - Choose 'Remove Member' at the top of the menu list and press 'update'. You may remove and add a crew member in one update.</LI>

                                        <LI>DELETE reservation - ONLY choose the DELETE button if you wish to remove the item from the schedule.</LI>
                                    </UL>
                                </td>
                            </tr>

                            <tr>
                                <td align="right" width="140"><font color="red"><b>*</b><font>Select Aircraft:</td>
                                <td>
                                    <SELECT id="tailNum" name="tailNum">
                                        <?
                                        if (!isset($picked)) {
                                            echo "<OPTION value='0' $default>AIRCRAFT</OPTION>\n";
                                            $picked='xxxxxxx';
                                        }
                                        
                                        foreach ($planeResults as $row) {
                                            $id = $row['tailNum'];
                                            $model = $row['model'];
                                            if ($id == $picked) {
                                                $default = "SELECTED";
                                            } else {
                                                $default = "";
                                            }
                                            echo "<OPTION value='$id' $default>$model($id)</OPTION>\n";
                                        }
                                        ?>
                                     </SELECT>
                                    
                                </td>
                            </tr>

                            <tr>
                                <td align="right"><font color="red"><b>*</b><font>Enter Reservation Times:</td>
                                <td>
                                    <table border="0" cellpadding="10">
                                        <tr>
                                            <td>Start:
                                                <input name="startTime" id="floating_timepicker_start"
                                                       value="<?=$startTime?>" type="text">
                                                <script type="text/javascript">
                                                    $(document).ready(function() {
                                                        $('#floating_timepicker_start').timepicker({
                                                            defaultTime: '12:00',
                                                            showPeriodLabels: false, 
                                                            hours: {
                                                                starts: 7,
                                                                ends: 18
                                                            },
                                                            minutes: {
                                                                starts: 0,
                                                                ends: 45,
                                                                interval: 15,
                                                                manual: []
                                                            },
                                                            minTime: {
                                                                hour: 0,
                                                                minute: 30
                                                            }
                                                        });
                                                    });
                                                </script>
                                                
                                            </td>
                                            <td>End:
                                                <input name="stopTime" id="floating_timepicker_stop"
                                                       value="<?=$stopTime?>" TYPE="text">
                                                <script type="text/javascript">
                                                 $(document).ready(function() {
                                                     $('#floating_timepicker_stop').timepicker({
                                                         defaultTime: '12:00',
                                                         showPeriodLabels: false, 
                                                         hours: {
                                                             starts: 7,
                                                             ends: 18
                                                         },
                                                         minutes: {
                                                             starts: 0,
                                                             ends: 45,
                                                             interval: 15,
                                                             manual: []
                                                         },
                                                         minTime: {
                                                             hour: 0,
                                                             minute: 30
                                                         },
                                                         // onSelect: function(time, inst) {
                                                         // $('#floating_selected_time_stop').html('You selected ' + time);
                                                         //}
                                                     });
                                                 });
                                                </script>
       
                                            </td>
                                        </tr>
                                    </table>
                                                 
                                </td>

                            <tr>
                                <td align="right" width="140"><font color="red"><b>*</b><font>Crew:</td>
                                <td>
                                    <?
                                    $usedNone = 0; // have we put up a none box
                                    $boxCount = 0; // how many select boxes have we displayed
                                    foreach ($results as $row) { // by memberID DESC, none is last
                                        $ptype = $row['dtype'];
                                        if (!$ptype || $ptype != 'CREW') { // shouldn't happen
                                            continue;
                                        }

                                        // if the very first one is none, this is a new reservation
                                        list($firstName, $lastName, $memberID) = explode(":", $row['tag']);
					Debug(0x20, 'edit_sched', 
                                              "Got firstname($firstName), lastName($lastName), memberID($memberID)");

                                        if (!$boxCount && $lastName == 'none') { // FIRST row of new res, create an empty
                                            memberSelect($ptype."[]", 0, $ptype); // this creates select box of members
                                            $boxCount++;
                                            $usedNone = 1;
                                            continue;
                                        }

                                        // first row with a name, or second row with name or none
                                        if ($memberID) {
                                            memberSelect($ptype."[]", $memberID, $ptype); // this creates select box of members
                                            $boxCount++;
                                        } else { // an empty
                                            if (!$usedNone && $boxCount < 2) { // give another empty option
                                                echo "<DIV id = 'moreCrew'>\n";
                                                memberSelect($ptype."[]", 0, $ptype); // this creates select box of members
                                                $boxCount++;
                                                echo "</DIV>\n";
                                            }
                                        }
                                        if ($boxCount >= 2) {
                                            break;
                                        }
                                    } // end for - all results

                                    // did we reach the min
                                    if ($boxCount < 2) {
                                        echo "<DIV id = 'moreCrew'>\n";
                                        memberSelect($ptype."[]", 0, $ptype); // this creates select box of members
                                        echo "</DIV>\n";
                                    }
                                    
                                    
                                    
                                    }
                                    // end if - PLANE reservatio  event
                                    ?>



<? if ($NoUseDutySched && $etype == 'FLYING') {
} else { ?> 
                                    <tr>
                                        <td align="right" width="140"></td>
                                        <td align="left" width="500" colspan="2">
                                            <INPUT TYPE='submit' name="action" onClick="return checkSubmit('<?=$newAction?>','')" value='<?=$newAction?>'>
                                            <? if ($newAction != 'insert') { ?>
                                                <INPUT TYPE='submit' name='action' onClick="return checkSubmit('DELETE','')" value='delete'>
                                            <? } ?>

                                        </td>
                                    </tr>
<? } ?>
        </table>
        <p>&nbsp;</p>
    </form>
    <script id="source" language="javascript" type="text/javascript">
     $(function() {
         $("#startDate").datepicker({ dateFormat: 'yy-mm-dd'});
         gliderChange();
     });

     $('select[name="tailNum"]').change(function(e) {
                                   gliderChange();
     });

     // did they just select an aircraft
     function gliderChange() {
         if (document.getElementById('tailNum') == null) {
             return;
         }
         var tailNum = document.getElementById('tailNum').value;
         var twoSeat = 0;
         // what tailNums have two seats, create in edit_sched.
         // looks like:  var twoSeatTailNums = 'N345,N657,';
         <?=$javaVar?>  

         if (twoSeatTailNums.indexOf(tailNum) > -1) {
             twoSeat = 1;
         }
         if (! twoSeat) { // hide any extra crew boxes
             document.getElementById('moreCrew').style.display='none';
         } else {
             document.getElementById('moreCrew').style.display='inline';
         }
     }

    </script>
                                                
</div>
