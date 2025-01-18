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
$title = "Advanced Member Search";


?>

<div class="clsContent-mid5">

    <form id="inputform" method="POST" action="active_members.php">
        <table style="border-collapse: collapse" border="0" bordercolor="#111111"
               cellpadding="5" cellspacing="0" width="100%">
	    <tr>
                <td colspan='3'>
                    <h3><span style="font-size: 24px;"><?=$title?></span></h3>
                    <p>You can search on any number of options below.  If you don't make a selection or enter any data for a field, that is ignored in the search. The more you select, the more
                        detailed the search. </p><p>If the field is a text box a wild card search will be done using the text you enter, <br />
e.g. Last Name: smith  -->  would return members: "Smith", "Blacksmith", "Smithers"</p>
                    <p>If the field offers multiple check boxes like 'Other Ratings' and you select more than one, the member must have all the ratings selected.</p>
                    <i>NOTE: After reviewing your search results use the browser 'BACK' button to return to this form and retain your selections (you'll have about 5 minutes).  If you wish to reset choices, just choose the "Advanced Search" link on the results page.</i>
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

                    if (preg_match('/^(notes|about|memberSince)$/', $dbCol)) {
                        continue;
                    }

                    $help = empty($params['help']) ? '' : $params['help'];

                     if ($help) {
                        $label = "<div style='display:inline;' title='$help'>$label</div>";
                     }


                    if ($params['newRow'] || $dbCol == 'pilot') {
                        if ($rowCount++) { // had previous row
                            echo "\n$rowIndent   </td>\n$rowIndent </tr>\n";
                        } 
                        echo "\n$rowIndent <tr>\n";
                        echo "$rowIndent  <td align='right'><b>$label:</b></td>\n$rowIndent  <td align='left'>";
                    } else {
                        echo "&nbsp;&nbsp; <b>$label:</b>   ";
                    }

                    // setup the correct input field
                    $valVar = $dbCol;  // the name of the current value variable
                    $value='';

                    if ($valVar == 'mentorID') {  // SPECIAL
                        memberSelect('mentorID', 'Select', 'CFIG');
                    } else if ($dbType == 'date') { //careful, need to match javascript at bottom
                        echo "<input name=$dbCol $size value='$value'  id='$dbCol'>";
                    } else if ($dbType == 'text' || $dbCol == 'state') {
                        echo "<input name=$dbCol $size value='$value' >";
                    } else { // limited input values
                        $selVar = $params['select']; // the name of the select options variable
                        $selValue = $$selVar;
                        displayInput($valVar, 'CHECK', $$selVar, $value);
                    } // end if-else field name/type
                    
                } // end for all display items

                echo "\n$rowIndent   </td>\n$rowIndent </tr>\n"; // finish the last row.
                ?>

 
                <tr>
                    <td  colspan="3" style="text-align: center">
                        <b>Order by:</b> <?=displayInput('order1','MENU', $SortMemberOptions, $order1)?>
                        <?=displayInput('order1Dir', 'RADIO', array('ASC','DESC'), $order1Dir, 'noTable')?>
                        <b>and then:</b> <?=displayInput('order2','MENU', $SortMemberOptions, $order2)?> 
                        <?=displayInput('order2Dir', 'RADIO', array('ASC','DESC'), $order2Dir, 'noTable')?>
                        <br />
                        Display: <div style='display:inline' title="Members with pictures"><input type="radio" name="displayFormat" value="normal" <? echo $displayFormat == 'normal' ? 'checked' : ''?>>Normal </div>
                        <div style='display:inline' title="Show data one member/line, some data abbreviated"><input type="radio" name="displayFormat" value="flatPart" <? echo $displayFormat == 'flatPart' ? 'checked' : ''?>>Flat partial</div>
                        <div style='display:inline' title="Show complete member data on each line"> <input type="radio" name="displayFormat" value="flatFull" <? echo $displayFormat == 'flatFull' ? 'checked' : ''?>>Flat full</div>  
                        <p>
                        <INPUT name='action' type='submit'  value='Advanced Search'>&nbsp;&nbsp;&nbsp;&nbsp;
                        <INPUT type="reset" value="Reset all search fields.">
                        </p>
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
