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
$tailNum = script_param('tailNum');
$GliderID = script_param('GliderID');

$msg='';
if (!$super) {
    $msg="<b><font color='red'>NOTE: You don't have access to edit aircraft status info, only history info is available.</font></b>";
}

// Show status info on Gliders limited to members
?>
        <div  id="divContent">
            <h3><span style="font-size: 24px;">Aircraft Status Log for Tail # <?=$tailNum?></span></h3>
            <p><?=$msg?></p><p>Complete log history follows.
            <a href="edit_glider_status.php?GliderID=<?=$GliderID?>&tailNum=<?=$tailNum?>">Return to edit this glider</a>
            &nbsp;&nbsp;&nbsp;
            <a href="glider_status.php">Return to the aircraft status page</a>
           </p>

            <table cellspacing="0" cellpadding="3" border="0">
                <tbody>
                    
                    <tr>
                        <th>Model</th>
                        <th>Tail #</th>
                        <th>Registration<br />Expires</th>
                        <th>Annual<br />Expires</th>
                        <th>Notes</th>
                        <th>Status As Of</th>
                        <th>By</th>
                    </tr>

<?

$sql = "SELECT * FROM Glider g, GliderStatusLog l, Members m WHERE g.GliderID = l.GliderID  AND l.updatedBy = m.MemberID AND l.tailNum='$tailNum' ORDER BY l.updated DESC";
if ($msg = DB_Query($Conn, $sql, $results)) {
    DisplayMessage("Display aircraft status",  "query failed ($msg)");
}

$lastModel = '';
$colArray = array('model', 'tailNum', 'regExpire', 'annualExpire', 'mxNotes', 'updated');

foreach ($results as $row) {
    $model = $row['model'];
    if ($model != $lastModel) {
        $lastModel = $model;
    }
    $status = $row['status']; 
    $bgStyle = ''; // nothing by default
    if ($status == 'RED') {
        $bgStyle = 'style="background-color: #ff3300"';
    } else if ($status == 'ORANGE') {
        $bgStyle = 'style="background-color: #ff9900"';
    }

    echo "<tr $bgStyle>\n";
    foreach ($colArray as $col) {
        $value = $row[$col];
        if ($col == 'model') {
            $value = "<b>$value</b>";
        } else if ($col == 'tailNum') {
            $value = "<a href=edit_glider_status.php?tailNum=$value&GliderID=$row[GliderID]>$value</a>";
        }
       echo "<td>$value</td>\n";
    }

    // add the updateBy name
    echo "<td>" . $row['lastName'] . "<br />&nbsp;&nbsp;" . $row['firstName'] . "</td>\n";
    echo "</tr>\n";
    echo "<tr><td colspan=7><hr /></td></tr>\n";

} // end for all aircraft

?>
                </tbody>
            </table>
            </table>
        </div>

        <!--   ------------------ Content ends here ---------------------------------- -->
<?php
  require 'footer.inc';
?>

