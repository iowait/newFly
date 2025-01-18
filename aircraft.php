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

// Show info on Gliders, limit some into to members
?>
        <div  id="divContent">
            <h3><span style="font-size: 24px;">Club Aircraft</span></h3>
            We're proud of our Club owned fleet of tow planes, trainers and solo aircraft. 
            We try to give our members every opportunity to fly!
            <? if ($super) { ?>
                <p><b><a href="edit_glider.php?action=new">Insert new aircraft.</a></b>&nbsp; &nbsp; &nbsp;
                    <b><a href="glider_status.php">Aircraft Status Report</a></b></p>
            <? } else if ($MemberID) { ?>
                <p><b><a href="glider_status.php">Aircraft Status Report</a></b></p>
            <? } else { ?>
                <p>Login required to see more detailed info on each aircraft.</p>
            <?}  ?> 
 
            <table cellpadding="5">
                <tbody>

<?

$sql = "SELECT * FROM Glider ORDER BY type, model";
if ($msg = DB_Query($Conn, $sql, $results)) {
    DisplayMessage("Display aircraft",  "query failed ($msg)");
}

foreach ($results as $row) {
    $pohURL = $row['pohURL'];
    if (strlen($pohURL) > 10) {
        $handbook="<a href='$pohURL'>Click here</a>";
    } else {
        $handbook="not available";
    }
?>                           
    <tr>

        <td colspan="2">
            <a border="2" href="photos/<?=$row['photo']?>">
                <img border="2" width="350" src="photos/<?=$row['photo']?>">
            </a>
        </td>
        <td colspan="2">
            <p style="font-size:16px;">
            <b><?=$row['mfg']?><br /><?=$row['model']?></b>
            </p>
            <p>
                Description: <i><?=nl2br($row['descrip'])?></i>
            </p>
            <p>
                Operating Handbook: <?=$handbook?>
            </p>
            <? if ($super) {
                echo "<p><b><a href='edit_glider_status.php?action=new&GliderID=".$row['GliderID']."'>Insert new tail # for this model.</a></b>&nbsp; &nbsp; &nbsp;</p>";
                echo "<p><b><a href='edit_glider.php?action=edit&GliderID=".$row['GliderID']."'>Edit information for this model.</a></b>&nbsp; &nbsp; &nbsp;</p>";
            } 
            ?>
        </td>
    </tr>

    <? if ($row['type'] != 'Tow') { ?>

   <tr>
       <td>
           Never exceed: <?=$row['vne']?>
       </td>
       <td>
           Rough air: <?=$row['vrough']?>
       </td>
       <td>
           Final: <?=$row['vfinal']?>
       </td>
       <td>
           Best glide: <?=$row['vglide']?>
       </td>
 
   </tr>

   <tr>
      <td>
           Min sink: <?=$row['vsink']?>
       </td>
       <td>
           Stall:  <?=$row['vstall']?>
       </td>
       <td>
           Glide ratio: <?=$row['glideRatio']?>
       </td>
       <td>
           Max G: <?=$row['maxG']?>
       </td>
   </tr>

   <? } # end if not Tow plane ?> 


       <tr>
           <td colspan="2"><hr/>
           </td>
       </tr>
<?
}
?>
                </tbody>
            </table>
        </div>

        <!--   ------------------ Content ends here ---------------------------------- -->
<?php
  require 'footer.inc';
?>

