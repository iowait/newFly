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
require("IGC.php"); // class file for HFL calcs

// We allow them to submit an IGC file, it's a three step process
// 1 - Entry Check - check file with jsigc to confirm valid
// 2 - Entry Confirm - display hi/far/long, get confirmation
// 3 - Entry Recorded - inserted in DB
//     Entry View - display a prior track recorded in the DB

$action = script_param("action");
$file = script_param('file');

if (empty($action)) {
    $action = 'check';
    $heading = 'IGC file confirmation';
} else if ($action == 'confirm' && empty($file)) {
    DisplayMessage("Process an IGC submission", "Missing data");
}
$msg = '';

if ($action == 'check') { // Step 1

    include('hflCheck_content.php');

} else if ($action == 'confirm') { // do the calcs, show them recent submits
    
    try {  // these defines are in mode.inc, set for a specific Club location
        $msg = getHighFarLong(AIRPORT_LAT, AIRPORT_LON, AIRPORT_ELEV, HFL_TMP_DIR . $file, $high, $far, $long, 
                              $date, $startTime, $endTime, $takeoffHMS, HFL_UNITS);
    } catch (Exception $e) {
        $msg = 'Caught exception: '. $e->getMessage(). "\n";
    }
 
    if ($msg) {
        DisplayMessage("Performing " . HFL . " analysis on $file", $msg);
    }

    include('hflConfirm_content.php');

} else if ($action == 'record') { // store in DB

    include('hflRecord_content.php');

} else if ($action == 'view') { // show them something stored

    include('hflView_content.php');

}


 
include('footer.inc');
?>

