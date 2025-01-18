    <h2>Step 2/4 - <?=HFL?> results confirmation</h2>
    <p>This allows you to see the results of <?=HFL?> analysis on your ICG file: <?=$file?>
        </p>
    <form method=post action=hfl.php>
        <input type=hidden name=action value=record>
        <input type=hidden name=fileName   value="<?=$file?>">
        <table border="0">
            <tr>
                <td>Date:</td>
                <td><?=$date?><input type="hidden" name="date" value="<?=$date?>"></td>
            </tr>

            <tr>
                <td>Pilot:</td>
                <td><?=$_SESSION['SUB_NAME']?></td>
            </tr>

            <tr>
                <td>Glider:</td>
                <td><SELECT name="glider">
                    <?
                    foreach ($HFL_GLIDERS as $type) { // defined in mode.inc
                        $selected = '';
                        if ($type == $glider) {
                            $selected = 'SELECTED';
                        }
                        echo "<OPTION $selected>$type</OPTION> \n";
                    } // end for - all values
                    ?>
                </SELECT> -- Select the correct glider.
                </td>
            </tr>

             <tr>
                <td>Takeoff Time:</td>
                <td><?=$takeoffHMS?><input type="hidden" name="takeoffHMS" value="<?=$takeoffHMS?>"></td>
            </tr>
            
            <tr>
                <td>High:</td>
                <td><?=$high?><input type="hidden" name="high" value="<?=$high?>"> -- Highest altitude (MSL) recorded.</td>
            </tr>

            <tr>
                <td>Far:</td>
                <td><?=$far?><input type="hidden" name="far" value="<?=$far?>"> -- Greatest distance from takeoff.</td>
            </tr>

            <tr>
                <td>Long:</td>
                <td><?=$long?><input type="hidden" name="long" value="<?=$long?>"> -- Duration of the flight.</td>
            </tr>
        </table>
        <p>
            <font color="red"><b>NOTE:</b>Please make sure all data above is correct. No further edits are possible after you Submit.</font> 
            <br />You can also enter some optional additional info below to be recorded with your submission.
        </p>
        <p>
            If there was another crew member on board during the flight and you wish to record their name, please enter it here.<br />
<input type="text" size="40" name="extraName">
        </p>
        <p>
            Flight notes:  Record any other info you want stored with your submission<br />
<textarea cols="60" name="notes">


</textarea>
        </p>
                
        <input type="submit" value="Record Flight">
    </form>