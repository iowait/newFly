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
$title = "Edit Member Info";
$lastName = preg_replace("/ /",'',$lastName); // need to collapse spaces
$warning = "";
$editImageTxt = "<-- Click on photo to upload/edit.";

if ($super) {
    $warning = "<p><font color='red'><b>WARNING</b> - you are editing this
 member as a superuser. As a superuser you can ONLY update your profile image($action). </font><br /><a href='active_members.php?type=Active'>Return to active membership list</a></p>";

    if ($action == 'new') { # inserting a new user
        $memberID = 0;
        $action = 'insert';   # ACTION change
        $title = "New Member Info";
        // we could be back after a missing field, make sure we have values
        // for some fields
        $detailArray = Array('memberType', 'pilot');
        foreach($detailArray as $t) {
            if (!isset($$t) || stristr($$t, 'none')) {
                $$t = 'None';
            }
        }
    }
    if ($actMemberID && $MemberID != $actMemberID) { // super user NOT editing themselves, ok for pic
        $editImageTxt = "";
    }
        
} // end if- superuser

if (getPhoto($memberID, $photoURL, $path, 'Member')) {
    $photo = PHOTO_DIR."/no_photo.png";
} else {
    $photo = $photoURL;
}
?>

<div class="clsContent-mid5">

    <form id="inputform" method="POST" action="edit_member.php">
        <table style="border-collapse: collapse" border="0" bordercolor="#111111"
               cellpadding="5" cellspacing="0" width="100%">
	    <tr>
	        <td>
                    <? if (!$editImageTxt) { ?>
                        <img align='left' border='2'  alt=" member photo " width="150"  src="<?=$photo?>" class="img-right">
                    <? } else { ?>
                    <a border=2 href="edit_photo.php"><img align='left' border='2'  alt=" member photo " width="150"  src="<?=$photo?>" class="img-right"></a></p>
                    <? }   ?>
		</td>
                <td colspan='2'>
                    <h3><span style="font-size: 24px;"><?=$title?></span></h3>
                    <p>You may update your member info below. Place the mouse over a field label to see additional help text where avaiable.<br /><b>Be careful!</b>  Any changes made take
                        effect immediately. Fields with (*) are required.<br>
                        <?=$editImageTxt?>
                    </p><?=$warning?>
		</td>
                <? 
                $rowCount = 0; 
                $rowIndent = '       ';
                foreach ($MemberLayout as $label => $params) {
                    // define some shortcut values
                    $dbType = $params['dbType'];
                    $dbCol = $params['dbCol'];

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
                    $readOnly = 'readonly';
                  
                    if ($params['access'] == 'MEMBER') {
                        $editPerm = 1;
                    } else if ($super) {
                        $editPerm = 1;
                    }
                    if ($editPerm) {
 	               $readOnly = '';
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
                    if ($valVar == 'mentorID') {  // SPECIAL
                        if ($editPerm) {
                            memberSelect('mentorID', $mentorID ? $mentorID : 'Select', 'CFIG');
                        } else if ($value) { // show the name
                            getMemberInfo($value, $memberInfo);
                            echo $memberInfo['firstName'] . " " . $memberInfo['lastName'];
                        } else {
                            echo "n/a";
                        } 
                    } else if ($dbType == 'date') { //careful on name change, need to match javascript at bottom
                        if ($editPerm) {
                            echo "<input name=$dbCol $size value='$value' $readOnly id='$dbCol'>";
                        } else {
                            echo "$value";
                        }

                    } else if ($dbType == 'textarea') {
                        echo "<textarea name={$params['dbCol']} $readOnly ROWS='{$params['rows']}' COLS='{$params['width']}'>";
                        echo "$value\n</textarea>\n";

                    } else if ($dbType == 'text') {
                        if ($editPerm) {
                            echo "<input name=$dbCol $size value='$value' >";
                        } else {
                            echo "$value";
                        }
                    } else { // limited input values
                        $selVar = $params['select']; // the name of the select options variable
                        $selValue = $$selVar;
                        if (!$value && !empty($params['default'])) {
                            $value = $params['default'];
                        }
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
                    <td align="left" width="500" colspan="2">
                        <INPUT TYPE='hidden' name='actMemberID' value='<?=$actMemberID?>'>
                        <INPUT name='action' type='submit' onClick="return checkSubmit('<?=$action?>','')" value='<?=$action?>'>
                        <? if ($action == 'update' && $super) { ?>
                            <INPUT TYPE='submit' name="action" onClick="return checkSubmit('delete','')" value='delete'>
                        <? } ?>
                    </td>
                </tr>
  



        </table>
        <p>&nbsp;</p>
    </form>

    <script id="source" language="javascript" type="text/javascript">
     $(function() {

         $("#towMed").datepicker({ dateFormat: 'yy-mm-dd'});
         $("#bfr").datepicker({ dateFormat: 'yy-mm-dd'});
     });
     
     </script>


</div>
<?php require 'footer.inc'; ?>
