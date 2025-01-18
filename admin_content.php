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

<p>You search results are below. Newest activity at the top.</p>
<p>  You can refine them by selecting a specific
Member name, IP address, or by changing the number of results shown and then resubmit.
</p>

<table>

    <table id="resultsTable" width="100%" border="1" cellspacing="0" cellpadding="2">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Type</th>
                <th>Name</th>
                <th>IP Addr</th>
                <th>Page</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?
            foreach ($results as $row) {
                echo '<tr>';
                foreach (array('time', 'type', 'name', 'ipAddr', 'url', 'details') as $field) {
                    echo "<td > $row[$field] </td>";
                }
                echo '</tr>';
            }
            ?>
        </tbody>

    </table>
    <p><b>Repeat search and limit results to:</b></p>

    <form method='post' action='admin.php'>
        <table  width="100%" border="0" cellspacing="0" cellpadding="10">
            <tr>
                <td>
                    Member: <?=MemberSelect('nameDesignator', $memberID, 0)?>
                </td>
                <td>
                    Results limit:
                    <SELECT name="countLimit">
                        <?
                    foreach(array(25,50,100,200,500) as $limit) {
                        $selected = '';
                        if ($countLimit == $limit) {
                            $selected = 'SELECTED';
                        }
                        echo "<OPTION $selected>$limit</OPTION>\n";
                        } // end for - all values
                        ?>
                    </SELECT>
                </td>
                <td>
                IP address:
                    <INPUT TYPE="text" NAME="ipAddr" SIZE="16" VALUE="<?=$ipAddr?>">
                </td>
            </tr>
        </table>
        
        <input type='hidden' name='action' value='<?=$action?>'>
        <input type='submit' value='Resubmit query'>
        
    </form>



