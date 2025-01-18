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
<?php
$title = "Local Calendar Content";

?>
<div class="clsContent-mid5">

    <form method="POST" action="load_calendar.php">
        <table style="border-collapse: collapse" border="0" bordercolor="#111111" cellpadding="5" cellspacing="0" width="100%">
	    <tr>
                <td colspan='2'>
                    <h3><span style="font-size: 24px;">
                        Load Calendar Content</span></h3>
                    <p>You may load a OPS/TOW/CFIG or Special Event schedule below<b>Be careful!</b>
                        Any changes made take effect immediately. Fields with (*) are required.
                    </p>
		</td>
            </tr>
            <tr>
                <td align="right" width="300"><font color="red"><b>*</b></font>Calendar Type:<br>
                Select the type of calendar you are submitting.</td>
                <td align="left" width="500">
                    <select name="dtype">
                      <option value="OPS" SELECTED>OPS</option>
                      <option value="TOW">TOW</option>
                      <option value="CFIG">CFIG</option>
                      <option value="SPECIAL">For Club Event Calendar</option>
                   </select>
                </td>
            </tr>
            
            <tr>
                <td align="right"><font color="red"><b>*</b></font>Delete current schedule:<br>
                    Be VERY careful here.  If you select "YES" the system will delete any existing data for
                    the calendar type you are submitting.<p>
                    If you are just submitting additional information, choose "NO."  Old data will be retained unless you are loading new info for that date.  In which case it will be replaced.</p>
                    <p><I>Confused?</I> - don't do anything and check with one of the admins.
                </td>
                <td align="left" height="29" width="500" colspan="2">
                  <select name="delete">
                      <option value="Yes">Yes</option>
                      <option value="No" SELECTED>No</option>
                    </select>  
            </tr>
 
            <tr>
                <td valign ="top"  align="right"><font color="red"><b>*</b></font>New Calendar:<br>
                    Copy/paste the data below.  It MUST be submitted in the following
                    format.<p>
                    <UL>
                        <LI> FOR OPS/TOW/CFIG - Each line should be in the form:  month/day/year, lastName<br>
                    like this example:<br>
                            <address>4/28/17, jones</address></LI>
                        <LI> FOR CLUB EVENTS - Each line should be in the form (colon between fields):   month/day/year : title : details<br>
                            <address>2/15/17 : Valentine's Day Dinner :  Not really, just kidding</address></LI>
                    </UL>
                </td>
                <td>
 <textarea name="calendar" rows="50" cols="50"><?=$calendar?></textarea>
                </td>
            </tr>
                   

                    <tr>
                        <td align="right" width="140"></td>
                        <td align="left" width="500" colspan="2">
                            <INPUT TYPE='submit' name='action' value='update'>
                        </td>
                    </tr>
        </table>
        <p>&nbsp;</p>
    </form>
    <script id="source" language="javascript" type="text/javascript">
     $(function() {

         $("#startDate").datepicker({ dateFormat: 'yy-mm-dd'});

     });
    </script>

</div>
