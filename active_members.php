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
require("header.inc");
$type = script_param("type");
$orderBy = 'lastName';

// could be they chose a different sort order, limits
$action = script_param("action");  // search or Advanced Search
$order1 = script_param("order1");
$order1Dir = script_param("order1Dir");
$order2 = script_param("order2");
$order2Dir = script_param("order2Dir");
$limit1 = script_param("limit1");
$limit2 = script_param("limit2");
$displaySearch = script_param("displaySearch");
$displayFormat = script_param('displayFormat') ? script_param('displayFormat') : 'normal'; // or flatPart/flatFull

// errorLog('_POST:' . print_r($_POST, true)."order1Dir($order1Dir)");
if (!$order1) { // just default
    $order1 = 'Select';
    $order2 = 'Select';
    $order1Dir = 'ASC';
    $order2Dir = 'ASC';
} else { // they picked something
    if ($order1 != 'Select') {
        $orderBy = "CONCAT({$MemberLayout[$order1]['dbCol']})  $order1Dir";
        
        if ($order2 != 'Select') {
            $orderBy = "$orderBy, CONCAT({$MemberLayout[$order2]['dbCol']}) $order2Dir";
        } # end if - second sort option
    } # end if - first sort otion
} # end if -else sort option select

if ($limit1 != 'Select' || $limit2 != 'Select') {
    $limitBy = "WHERE "; // start the WHERE clause
}

if (!$limit1) {
    $limit1 = 'Select';
} else {
    if ($limit1 != 'Select') {
        $srchInfo['memberType'] = $limit1;
    } # end if - first limit otion
} # end if -else limit option select

if (!$limit2) {
    $limit2 = 'Select';
} else {
    if ($limit2 != 'Select') {
        $srchInfo['pilot'] = $limit2;
    } # end if - first limit otion
} # end if -else limit option select

// if any search is active, ignore
$mesg = '';
$title = '';

if (empty($action)) {
    if ($type == 'Active') {
        $title = "Active Member List";
        $srchInfo['memberType'] = array('!=', 'Inactive');
    } else {
        $title = "Inactive Members Only";
        $srchInfo['memberType'] = 'Inactive';
        $mesg = "<p><i>These people are not regular members, but either are pending deletion 
or have a special relationship with the Club and we'll continue to display their contact info.   
They can still login to the system and are still on the mailling list.</i></p>";
    }
} else {
    $title = "Member Search Results";
} // end if - no search

# add other fields if supplied
$srchInfo['MemberID'] = '';
foreach ($MemberLayout as $label => $params) {
    $dbCol = $params['dbCol'];
    $dbType  = $params['dbType'];
    if (empty($srchInfo[$dbCol])) { // fill in the search based on the type
        $value = script_param($dbCol); // something in the search field 

        if (empty($value)) { // first check, is there anything they are looking for
            $srchInfo[$dbCol] = '';

        } else if ($dbType == 'text' || $dbCol == 'state') {
            if ($value) {
                $srchInfo[$dbCol] = array('LIKE', script_param($dbCol));
            } else {
                $srchInfo[$dbCol] = '';
            }

        } else if ($dbType == 'date') {
            $srchInfo[$dbCol] = $value;

        } else if ($dbType == 'id') { // ZZ Hack - mentor is combo name:id, e.g. Doane:767
            if ($value != 'Select') {
                list($junk, $id) = preg_split( '/:/', $value);
                $srchInfo[$dbCol] = $id;
            } else {
                $srchInfo[$dbCol] = '';
            }

        } else if ($dbType == 'enum' || $dbType == 'set') {
            if (is_array($value)) { // they chose multiple
                $srchInfo[$dbCol] = array_merge(array($dbType), $value);
            } else { // just one
                $srchInfo[$dbCol] = $value;
            }
        }
    } // end if - have value 
} // all db fields

# We always skip MemberID 0 - has special usage, don't change
$srchInfo['MemberID'] = array ('!=', 0);

# get the results
if ($msg = DB_Get($Conn, 'Members', $srchInfo, $results, $orderBy)) {
    echo "ERROR, count query failed ($msg)";
}

$total = count($results);

?>

<div  id="divContent">
    <h3><span style="font-size: 24px;"><?=$title?></span></h3>

    <form method="POST" action="active_members.php?type=<?=$type?>&action=search">
        <input type="checkbox" name="displaySearch" id="displaySearch" value="1" <? echo !empty($displaySearch) ? 'checked' : ''?>>Show search options  &nbsp;&nbsp;&nbsp;Click here -> <a href="active_members.php?type=Active">Reset display to defaults.</a>
   <span id="searchInfo" style="display:none">
        <br />
        <b>Order by:</b> <?=displayInput('order1','MENU', $SortMemberOptions, $order1)?>
          <?=displayInput('order1Dir', 'RADIO', array('ASC','DESC'), $order1Dir, 'noTable')?>
        <b>and then:</b> <?=displayInput('order2','MENU', $SortMemberOptions, $order2)?> 
          <?=displayInput('order2Dir', 'RADIO', array('ASC','DESC'), $order2Dir, 'noTable')?>
        <br />
        <b>Also limit to Members</b>: <?=displayInput('limit1','MENU', array_merge($MemberTypes, array('Select')), $limit1)?>
        <b>and/or rating:</b> <?=displayInput('limit2','MENU', array_merge($PilotTypes, array('Select')), $limit2)?>
        <br />
        Display: <div style='display:inline' title="Members with pictures"><input type="radio" name="displayFormat" value="normal" <? echo $displayFormat == 'normal' ? 'checked' : ''?>>Normal </div>
        <div style='display:inline' title="Show data one member/line, some data abbreviated"><input type="radio" name="displayFormat" value="flatPart" <? echo $displayFormat == 'flatPart' ? 'checked' : ''?>>Flat partial</div>
        <div style='display:inline' title="Show complete member data on each line"> <input type="radio" name="displayFormat" value="flatFull" <? echo $displayFormat == 'flatFull' ? 'checked' : ''?>>Flat full</div>  
        <INPUT TYPE="submit" name="orderBy" value="Sort/Search"> 
&nbsp;&nbsp;&nbsp;Click here ->
<a href="edit_member.php?action=search&order1=<?=$order1?>&order1Dir=<?=$order1Dir?>&order2=<?=$order2?>&order2Dir=<?=$order2Dir?>&displayFormat=<?=$displayFormat?>">Advanced Search</a>      </span> 
        
</form>

    
    <?=$mesg?>
    <p>
    <? if ($total) { 
        echo "$total Members displayed.&nbsp;&nbsp;&nbsp;<font color='red'><b>REMEMBER: do not share personal info shown with anyone outside our Club.</b></font> </I>";
    } else {
        echo "Sorry, your search failed to return any matching members";
    } ?>    
</p>
<? if ($total && !strstr($displayFormat, 'flat')) { ?>
    <table>
        <tbody>
            <?
            if ($super && $type == 'Active') {  // super users can add new members
            ?>
            
            <tr>
                <td colspan="3">
                    <a href="edit_member.php?action=new"><b>Add new member.</b></a>
                </td>
            </tr>
            <?}
            
            foreach ($results as $row) {

                if (getPhoto($row['MemberID'], $photoURL, $path)) {
                    $photo = PHOTO_DIR."/no_photo.png";
                } else {
                    $photo = $photoURL;
                }
            ?>

            <tr>
                <td>
                    <a href="<?=$photo?>">
                        <img alt="" border='2' width="150px" src="<?=$photo?>" class="img-centered" /></a>
                </td>

                <td>
                    <?
                    if ($super) {
                        
                        if ($msg = getMemberID('', '', $row['emailAddr'], $actMemberID)) {
                            DisplayMessage("Override user", "Can't find data ($msg)");
                        }
                    ?>
                    <a href="edit_member.php?actMemberID=<?=$actMemberID?>">
                        EDIT: <address><?=$row['firstName']?> <?=$row['middleName']?>  <?=$row['lastName']?> <?=$row['suffix']?></address>
                    </a>
                               <? } else {  ?>
                                   <address><?=$row['firstName']?>  <?=$row['lastName']?> <?=$row['suffix']?> </address>
                               <? }  ?>
                               <? if (!empty($row['street'])) { ?>
                                  <address><?=$row['street']?></address>
                               <? } ?>
                               <address><?=$row['city']?>, <?=$row['state']?> <?=$row['zip']?></address>
                               <address><?=$row['memberType']?> Member<br />
                                   Glider rating: <?=$row['pilot']?> <br />
	             <? if (!empty($row['otherPilot'])) { ?>
                                   Other ratings: <?=$row['otherPilot']?> <br /></address>
                     <? } ?>
                               <address>Access: <?=$row['access']?> </address>
                </td>
                <td>
                    <address>Prefer: <?=$row['phone']?></address>
	            <? if (!empty($row['phone2'])) { ?>
                        <address>Alternate: <?=$row['phone2']?></address>
                    <? } ?>
                    <address><a href="mailto:<?=$row['emailAddr']?>"><?=$row['emailAddr']?></a></address>
                    <address>Checked Out: <?=preg_replace('/,/', ', ',$row['checkout'])?> </address>
                    <address>Badges: <?=preg_replace('/,/', ', ',$row['badge'])?> </address>
                    <?  if ($row['mentorID']) {
                        getMemberInfo($row['mentorID'], $memberInfo);
                        $mentorName = $memberInfo['lastName'];
                    ?>
                    <address>Mentor: <?=$mentorName?> </address>
                    <? }  
                    if (!empty($row['memberSince']) && $row['memberSince'] != 'none') { ?>
                       <address>Member Since: <?=$row['memberSince']?> </address> 
                    <? }
                    if (!empty($row['coord'])) { ?> 
                       <address>Coordinator for: <?=preg_replace('/,/', ', ',$row['coord'])?> </address>    
                    <? }
                    if (!strstr($row['towMed'], '0000')) { ?>
                       <address>Tow Medical: <?=$row['towMed']?> </address>    
	            <? } 
                    if (!strstr($row['bfr'], '0000')) { ?>
                       <address>Biennial Flt Review: <?=$row['bfr']?> </address>                             
                    <? } ?>
            </td>
            </tr>
            <tr>
                <td colspan="3">
                <? if (!empty($row['notes']) || !empty($row['about'])) {
                    if (!empty($row['notes'])) { // ZZ function this
                        printMouseOver($params['dbCol'], 'Notes:' . $row['notes'], $params['width'], 'address');
                    }
                    if (!empty($row['about'])) {
                        printMouseOver($params['dbCol'], 'About Me:' . $row['about'], $params['width'], 'address');
                    }
                    
                } ?> 
            <hr></td>
            </tr>
<?
} // end for - all members
?>
        </tbody>
    </table>

<? } else if ($total) { // flat data display (could be flatPartial or flatFull)
    if ($displayFormat == 'flatPart') {
        $extra = "For additional info about a user place mouse over the Last Name.";
    } else {
        $extra = '';
    }
    if ($super) {
        $mesg="<i>$extra Click on the hyperlink shown for Last Name to edit a user.</i>";
    } else {
        $mesg="<i>$extra</i>";
    }
?>
    <?=$mesg?>
    <table border="1">
        <tbody>
            <?
            // we do combine some fields
            if ($displayFormat == 'flatPart') {
                $combined = array('firstName', 'middleName', 'lastName', 'suffix', 'city', 'street', 'state', 'zip', 'phone', 'phone2', 'about');
            } else {
                $combined = array('lastName');
            }
            echo "<tr>\n";
            echo "<th>Last name</th>"; // always first

            foreach ($MemberLayout as $label => $params) { // put the header columns in, lastName first
                if (!in_array($params['dbCol'], $combined)) {
                    echo "<th>$label</th>";
                }
            }
            echo "\n</tr>\n";
            foreach ($results as $row) {

                echo "<tr>\n";
                 
                // display all non empty columns, we always start with lastName and mouseover of
                // combined details
                $details = "{$row['firstName']} {$row['middleName']} {$row['lastName']} {$row['suffix']}&#10{$row['city']},{$row['state']},{$row['zip']} &#10 {$row['phone']}, {$row['phone2']}";
                echo "<td> <div title='$details'>";
                if ($super) { // edit link
                    if ($msg = getMemberID('', '', $row['emailAddr'], $actMemberID)) {
                        DisplayMessage("Override user", "Can't find data ($msg)");
                    }
                    echo "<a href='edit_member.php?actMemberID=$actMemberID'>{$row['lastName']}</a>";
                } else {
                    echo $row['lastName'];
                }
                echo "</div></td>";
                foreach ($MemberLayout as $label => $params) {
                    if (!in_array($params['dbCol'], $combined)) {
                        echo " <td>";
                        // special for mentorID, convert to name
                        if ($params['dbCol'] == 'mentorID') {
                            getMemberInfo($row['mentorID'], $memberInfo);
                            $mentorName = "{$memberInfo['lastName']}, {$memberInfo['firstName']}";
                            echo strstr($mentorName,'none,') ? '' : $mentorName;
                        } else if ($params['dbType'] == 'enum' || $params['dbType'] == 'set') {
                            echo empty($row[$params['dbCol']]) ? '' : preg_replace('/(\w+,\w+,\w+)/', '${1} ', $row[$params['dbCol']]);
                        } else if ($params['dbType'] == 'textarea') {
                            echo empty($row[$params['dbCol']]) ? '' : 
                                 printMouseOver($params['dbCol'], $row[$params['dbCol']], 20, '');
                        } else {
                            echo empty($row[$params['dbCol']]) ? '' : $row[$params['dbCol']];
                        }
                        echo " </td>";
                    }
                }
                echo "</tr>\n";
            
            } // end for each result
            ?>
        </tbody>
    </table>
            


<? } // end if -else display format ?>

</div>
<script type="text/javascript">
       
 $('input[name="displaySearch"]').change(function(e){
     var btn = document.getElementById('displaySearch');
     
     if (btn.checked) {
         document.getElementById('searchInfo').style.display='inline';
     } else {
          document.getElementById('searchInfo').style.display='none';
     }
 });
 
 var btn = document.getElementById('displaySearch');
 if (btn && btn.checked) {
     document.getElementById('searchInfo').style.display='inline';
 }

</script>



<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
