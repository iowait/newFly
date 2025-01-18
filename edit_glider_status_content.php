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
$GliderID = script_param('GliderID');
$tailNum = script_param('tailNum');
$title = "Edit Status Tail # $tailNum";
$warning = "";

if ($super) {
    if ($action == 'new') { # inserting a new glider
        $GliderID = 0;
        $action = 'insert';
        $title = "New Tail # Info";
        // we could be back after a missing field, make sure we have values
        // for some fields
        $detailArray = Array('type');
        foreach($detailArray as $t) {
            if (!isset($$t) || stristr($$t, 'none')) {
                $$t = 'None';
            }
        }
    }
} // end if- superuser

$GliderID = script_param('GliderID');

if (getPhoto($GliderID, $photoURL, $path, 'Glider')) {
    $photo = PHOTO_DIR."/glider_0.jpg";
} else {
    $photo = $photoURL;
}
?>

<div class="clsContent-mid5">

    <form id ="inputform" method="POST" action="edit_glider_status.php">
        <table style="border-collapse: collapse" border="0" bordercolor="#111111" cellpadding="5" cellspacing="0" width="100%">
	    <tr>
 	        <td>
                    <img align='left' border='2'  alt=" Glider photo " width="250"  src="<?=$photo?>" class="img-right"></p>
		</td>
               
                <td>
                    <h3><span style="font-size: 24px;"><?=$title?></span></h3>
                    <p>You may edit aircraft info below.</p><p> <br /><font color="red"> <b>Be careful!</b> Other pilots are relying on this info! Do not delete/change prior MX Notes unless you know they NO LONGER APPLY.  Just add more recent notes at the top.  Any changes made take effect immediately.</font></p><p> Fields with (*) are required.
                        <? if ($action != 'new') { ?>
                            <p><a href="glider_status_log.php?GliderID=<?=$GliderID?>&tailNum=<?=$tailNum?>">Click here</a> for status log of this aircraft.</p>
                            <p><a href="glider_status.php">Back to</a> the complete status report.</p>
                            <? } ?>
                    </p>
		</td>
            </tr>

            <? 
            $rowCount = 0; 
            $rowIndent = '       ';
            foreach ($GliderStatusLayout as $label => $params) {
                // define some shortcut values
                $dbType = $params['dbType'];
  	        $dbCol  = $params['dbCol'];
                
                if ($dbType == 'text') {
                    $size = "size = '" . $params['width'] . "',";
                } else {
                    $size = '';
                }

	        $help = empty($params['help']) ? '' : $params['help'];
     
	        if ($help) {
		   $label = "<div style='display:inline;' title='$help'>$label</div>";
                }
                
                if ($params['req']) {
                    $required = "<font color='red'><b>*</b></font>";
                } else {
                    $required = '';
                }
                
                $editPerm = 0;  // can the user edit this field or read only
                if ($params['access'] == 'SUPER' && $super) {
                    $editPerm = 1;
                }

                if ($params['newRow']) {
                    if ($rowCount++) { // had previous row
                        echo "\n$rowIndent   </td>\n$rowIndent </tr>\n";
                    } 
                    echo "\n$rowIndent <tr>\n";
                    echo "$rowIndent  <td align='right'><b>$required $label:</b></td>\n$rowIndent  <td align='left'>";
                } else {
                    echo "&nbsp;&nbsp; <b>$required $label:</b>   ";
                }
                
                // setup the correct input field
                $valVar = $dbCol;  // the name of the current value variable
                $value = $$valVar;
                $readOnly = 'readonly';
		if ($editPerm) {
	           $readOnly = '';
                }
                
                if ($valVar == 'tailNum' && $value) { // we have a tailNum, can't edit
                    $readOnly = 'readonly';
                }

                if ($valVar == 'mentorID') {  // SPECIAL
                    memberSelect('mentorID', $mentorID, 'CFIG');
                } else if ($dbType == 'textarea') {
                    echo "<textarea name=$dbCol ROWS='8' COLS='" . $params['width'] . "' $readOnly>";
                    echo "$value\n</textarea>\n";
                } else if ($dbType == 'date') { //careful on name change, need to match javascript at bottom
                    echo "<input name=$dbCol $size value='$value' $readOnly id='$dbCol'>";
                } else if ($dbType == 'text') {
                    echo "<input name=$dbCol $size value='$value' $readOnly>";
                } else { // limited input values
                    $selVar = $params['select']; // the name of the select options variable
                    $selValue = $$selVar;
                    if ($editPerm) {
                        displayInput($valVar, $dbType == 'enum' ? 'MENU' : 'CHECK', $$selVar, $value);
                    } else {
                        echo "$value";
                    }
                } // end if-else field name/type
                
            } // end for all display items
            
            echo "\n$rowIndent   </td>\n$rowIndent </tr>\n"; // finish the last row.
            ?>



            <tr>
                <td align="right" width="140"></td>
                <td align="left" width="800" colspan="2">
                    <INPUT TYPE='hidden' name='GliderID' value='<?=$GliderID?>'>
	            <? if ($super) { ?>
                       <INPUT TYPE='submit' name="action" onClick="return checkSubmit('<?=$action?>','')" value='<?=$action?>'>
                       <? if ($action == 'update') { ?>
                        <INPUT TYPE='submit' name="action" onClick="return checkSubmit('delete','')" value='delete'>
                       <? } ?>
                    <? } ?>
                </td>
            </tr>
        </table>
        <p>&nbsp;</p>
    </form>
    
    <script id="source" language="javascript" type="text/javascript">
     $(function() {

         $("#annualExpire").datepicker({ dateFormat: 'yy-mm-dd'});
         $("#regExpire").datepicker({ dateFormat: 'yy-mm-dd'});
     });
     
   </script>

</div>
