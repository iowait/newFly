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
include_once("./php_includes.inc");
include_once("header.inc");

// This page is the initial access to Hi-Far-Long tracking
$action = script_param('action');
$file = script_param('file');
$igcData = script_param('igcData');

if (empty($action)) { // just started
    $action = 'check';
    $checkColor = 'red';
    $checkStatus = '';
} else if ($action == 'confirm' && !empty($file)) {
    $checkColor = 'green';
    $checkStatus = 'Complete';
    $confirmColor = 'red';
    $confirmStatus = '';
} else if ($action == 'record') {
    $checkColor = 'green';
    $checkStatus = 'Complete';
    $confirmColor = 'green';
    $confirmStatus = 'Complete';
    $recordColor  = 'green';
    $recordStatus = 'Complete';
}
    
?>

<h2> <?=HFL?> Competition</h2>

<p>Many are familiar with <a href="https://www.OnLineContest.Org/">OLC(On Line Contest Tracking)</a> but may not consider
    their Club flying worthy of International note! <i><b>But on any given day -- you can be the best out there!  Proudly stake your claim!</b></i>&nbsp;&nbsp; <a href="hflShow.php">Click here</a> to see/search prior submissions.</p>

<p><b><?=HFL?></b> is meant to encourage local flying and make achieving
personal bests a bit more fun for Club members. You upload
an IGC file created by XCSoar running on your cell phone (or other device) and it
determines if you're a TOP THREE in either <b>High</b>(Highest GPS altitude), 
<b>Far</b>(Farthest distance from the field), or <b>Long</b>(Minutes in the air) 
for that day, week, month, or year.</p>

<p><i>Right now doing final testing. Feel free to load any old file you may have (remember, takeoff and landing must be from the same field). There's a lot of complexity in analyzing a .igc file and different devices can have slightly different output.  Anything you can submit helps improve the program.  A copy is kept of every file, <b><a href="mailto:webmaster@SoaringTools.org?Subject='HFL issue'">please email</a> with any issues</b> (be sure to include the filename you uploaded) or recommendations. Thanks!</i></p>

<p><font color="<?=$checkColor?>"><b>Step 1 - Check your file data (<?=$checkStatus?>)</b> - This allows you to upload your data and confirm it looks correct. <br /><a href="hflSubmit.php">Click here</a> for our submission screen. Don't know how to create a .igc file, <a href="https://www.soaringtools.org/index.php/xcsoar-basic-setup-and-usage/"> click here?</a></font></p>

<? if ($action == 'confirm') { // store the file
    $filePath = HFL_TMP_DIR . $file;
    if (!file_put_contents($filePath, $igcData)) {
        DisplayMessage("Trying to store your file data", "Unable to save");
    }

} ?>

<? if ($action == 'confirm' || $action == 'record') { ?>
    <p><font color="<?=$confirmColor?>"><b>Step 2 - Confirm your results (<?=$confirmStatus?>)</b> - We'll now calculate <?=HFL?> results using: <?=$file?><br /><b>Note:</b> - this installation is configured for submissions for: <?=AIRPORT_NAME?>.  Takeoff/landing must be from that field.<br /><a href="hflSubmit.php?action=confirm&file=<?=$file?>">Click here</a> to continue.</font></p>
    
<? } ?>

<? if ($action == 'record') { // store the data, we have to strip off units for some fields
    list($takeoffTime, $junk) = explode(' ', script_param('takeoffHMS'));
    $insertInfo['date'] = script_param('date') . ' ' . $takeoffTime;
    $insertInfo['pilot']  = $_SESSION['SUB_NAME'];
    $insertInfo['glider'] = script_param('glider');
    $insertInfo['extraName'] = script_param('extraName');
    $insertInfo['notes'] = script_param('notes');
    $tmpFile = script_param('fileName');
    $storedFile = "$MemberID-$tmpFile";
    $insertInfo['fileName']  = $storedFile;  // we tag the file with the memberID when stored below
    $insertInfo['units'] = HFL_UNITS;
    $insertInfo['lat']   = AIRPORT_LAT;
    $insertInfo['lon']   = AIRPORT_LON;
    $insertInfo['elev']  = AIRPORT_ELEV;
    $insertInfo['timezone'] = HFL_TIMEZONE;

    // next three we strip off the units
    list($insertInfo['high'], $junk)  = explode(' ', script_param('high'));
    list($insertInfo['far'], $junk)   = explode(' ', script_param('far'));
    list($insertInfo['`long`'], $junk)  = explode(' ', script_param('long'));

    // store it
    // Move the file to long term storage
    if (! is_dir(HFL_DIR)) { // create it, used by webserver
        if (!mkdir(HFL_DIR)) {
            DisplayMessage("Saving your IGC file", "Could not create storage location. Setup may be incomplete.");
        }
    }

    if (!rename(HFL_TMP_DIR.$tmpFile, HFL_DIR.$storedFile)) {
        DisplayMessage("Saving your IGC file", "Could not move $tmpFile to storage location.");
    }
    
            
    if ($msg = DB_Insert($Conn, 'hfl', $insertInfo)) {
        $recordStatus='Failed';
        $recordColor='red';
        // remove the file we moved to storage
        unlink(HFL_DIR.$storedFile);
    } else {
        $msg = 'Your data has been stored. <a href="hflShow.php">Click here</a> to see yours and other submissions. ';
    }

    // most common error is same person submitting twice: Duplicate entry '24-2020-09-25-XCS-AAA-02.igc' for key 'theEntry'
   
    echo "<p><font color='$recordColor'><b>Step 3 - Record your results ($recordStatus)</b> - $msg</font></p>";
    
} // end if - record
include('footer.inc');
?>

