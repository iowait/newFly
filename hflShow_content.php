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

<p><?=HFL?> search results are below. You can refine them by selecting some other search conditions and then resubmit.
</p>
<p><a href="hfl.php">CLick here</a> for submission info.</p>

<? // set our units
$high = 'ft';
$far  = 'nm';
if (count($results)) {
    $units = $results[0]['units'];
    if ($units == 'METRIC') {
        $high = 'm';
        $far  = 'km';
    }
}
?>

<table>

    <table id="resultsTable" width="100%" border="1" cellspacing="0" cellpadding="2">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Pilot</th>
                <th>Glider</th>
                <th>High/<br /><?=$high?></th>
                <th>Far/<br /><?=$far?></th>
                <th>Long/<br />min</th>
                <th>Crew</th>
                <th>Notes</th>
                <th>Entered</th>
            </tr>
        </thead>
        <tbody>
            <?
            if (!count($results)) {
                echo "<tr><td colspan='9'>Sorry, no matching records were found.  You may need to adjust your search criteria below.</td></tr>";
            } else {
                foreach ($results as $row) {
                    echo '<tr>';
                    foreach (array('date', 'pilot', 'glider', 'high', 'far', 'long', 'extraName', 'notes', 'updated') as $field) {
                        echo "<td > $row[$field] </td>";
                    }
                    echo '</tr>';
                }
            }
            ?>
        </tbody>

    </table>
    <p>&nbsp;</p>
    <p><b>Refine search and limit results to:</b><br />
        <i>You can limit by different items or see everything (which is the default).  <br />To see results narrowed by High, Far, or Long you must choose both an Entry Type and Time Period.</i></p>

    <form method='post' action='hflShow.php'>
        <table  width="100%" border="0" cellspacing="0" cellpadding="10">
            <tr style='vertical-align:top'>
                <td>
                    Member:<br /><input type="text" name ="pilot" value="<?=$pilot?>" size="20"><br />
                    <i>Enter all or part of a name starting with the first name</i>
                </td>

                <td>
                    Results limit:<br />
                    <SELECT name="countLimit">
                        <?
                        foreach(array('All', 25,50,75,100) as $type) {
                            $selected = '';
                            if ($type == $countLimit) {
                                $selected = 'SELECTED';
                            }
                            echo "<OPTION $selected>$type</OPTION>\n";
                            } // end for - all values
                        ?>
                    </SELECT>
                </td>

 
                <td>Glider:<br />
                    <SELECT name="glider">
                    <?
                    $gliderArray = array('All');
                    $gliderArray = array_merge($gliderArray, $HFL_GLIDERS);
                    foreach ($gliderArray as $type) { // defined in mode.inc
                        $selected = '';
                        if ($type == $glider) {
                            $selected = 'SELECTED';
                        }
                        echo "<OPTION $selected>$type</OPTION> \n";
                    } // end for - all values
                    ?>
                    </SELECT>
                </td>

                <td>
                    Starting Date:<br /> <input  name="startDate" value="<?=$startDate?>" id="startDate" size="10"><br /><i>Dates must be entered as YYYY-MM-DD</i>
                </td>

                <td>
                    Ending Date:<br /> <input  name="endDate" value="<?=$endDate?>" id="endDate" size="10">
                </td>

               <td>
                    Best by Entry Type:<br />
                    <SELECT name="entryType">
                        <?
                        foreach (array('All','High','Far','Long') as $type) {
                            $selected = '';
                            if ($type == $entryType) {
                                $selected = 'SELECTED';
                            }
                            echo "<OPTION $selected>$type</OPTION>\n";
                        } // end for - all values
                        ?>
                    </SELECT>
                    <p>AND Time Period (<i>both must be selected</i>):</p>
                    <SELECT name='timePeriod'>
                        <?
                        foreach (array('All','Day','Week','Month','Year') as $type) {
                            $selected = '';
                            if ($type == $timePeriod) {
                                $selected = 'SELECTED';
                            }
                            echo "<OPTION $selected>$type</OPTION>\n";
                        } // end for - all values
                        ?>
                    </SELECT>
                </td>
            </tr>
        </table>
        
        <input type='hidden' name='action' value='<?=$action?>'>
        <input type='submit' value='Resubmit query'>
        
    </form>



    <script id="source" language="javascript" type="text/javascript">
        $(function() {
           $("#startDate").datepicker({ dateFormat: 'yy-mm-dd'});
           $("#endDate").datepicker({ dateFormat: 'yy-mm-dd'});
        });
    </script>
        