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
<? require("./php_includes.inc");
require("./lib/header.inc");
$action = script_param('action');

if (!$super) { # shouldn't be here
    DisplayMessage("Upload minutes", "Sorry only privileged users can upload");
}
?>

<div  id="divContent">
    
    <? if (!$action) { // first time thru ?>
        <p>
            <font size="+1" color="red"><b>FIRST TIME HERE - READ THIS!!!</b></font></p><br />
            This page allows you to upload JUST board minutes to the website.  The minutes are stored in a specific directory on the web server and the script that displays the page with the minutes examines that directory. EVERYTHING depends on a proper file name for the minutes.  It also depends on minutes being present for each month.  If for some reason minutes were skipped for a month, you MUST still upload a PDF file with the proper name.  For the contents just explain why they are missing.</p>
        <p>
            Click the 'browse' button below to find the minutes on your computer.  The file name MUST be of the following form: FLY_MINUTES_month_year.pdf<br />The month name and year name must be complete.  First letter of month must be capitalized, e.g. Minutes for Feb 2018 would be named:  FLY_MINUTES_February_2018.pdf
            <p>  <b>BE CAREFUL</b> --> Then press Submit.</p>
        </p>
        <form action="edit_minutes.php" method="post" enctype="multipart/form-data" >

            <input type="file" name="minFile" />
            <input type="hidden" name="action" value="submit">
            <input type="submit" name="uploadButton" class="upbtn" value="Submit" />
        </form>
    <? } else if ($action == 'submit') { // they uploaded 
        
        $minutesName   = $_FILES['minFile']['name'] ;
        $minutesPath   = $_FILES['minFile']['tmp_name'];
        $minutesStatus = $_FILES['minFile']['error'];
        $minutesSize   = $_FILES['minFile']['size'];
        $minutesType   = $_FILES['minFile']['type'];
            
        $ext         = pathinfo($minutesName, PATHINFO_EXTENSION);
        $tmpPath     = ROOT_DIR . "/". MINUTES_DIR . "/tmp/$minutesName";
        $minutesURL    = MINUTES_DIR . "/tmp/$minutesName";

        if (!$minutesName) { # nothing?
            $minutesStatus = "No minutes file found. Please select a file first using the browse button.";
            
        } else if ($minutesSize > 2000000) {
            $minutesStatus = "Minutes too large at $minutesSize bytes";
            
        } else if (!strstr($minutesType, 'pdf')) {
            $minutesStatus = "Does not appear to be an image. Got type of $minutesType";
            
        } else { # now we check the filename format, split it on _   FLY_Minutes_month_year.pdf
            $nameParts = preg_split("/_/", $minutesName);
            if ($nameParts[0] != 'FLY' || $nameParts[1] != 'Minutes') {  # check for problem at start
                $minutesStatus = "File name must be in the form of:  FLY_Minutes_month_year.pdf";
                
            } else { # check for problem with month
                $found = 0;
                foreach ($MonthNames as $month) {
                    if ($nameParts[2] === $month) {
                        $found = 1;
                        break;
                    }
                } # end for - all months
                if (!$found) {
                    $minutesStatus = "Got $nameParts[2] for the month name, must be complete, start with CAP";
                    
                } else { # check for problem with year
                    $found = 0;
                    foreach ($YearNames as $year) {
                        if (strstr($nameParts[3], "$year.pdf")) {
                            $found = 1;
                            break;
                        }
                    } # end for - all years
                    if (!$found) {
                        $minutesStatus = "Year must be complete, 4 digits";
                    }
                }
            } # end if-else check file naming
        } # end if-else check formating
        
        if ($minutesStatus) {
            if (file_exists($minutesPath)) {
                unlink($minutesPath);
            }
            DisplayMessage("Upload minutes", "Got an error on file: $minutesName ($minutesStatus), you can try
again, make sure the minutes are in PDF format and with the proper name.");
        }
        
        if (move_uploaded_file($minutesPath, $tmpPath)) {
            $result=$tmpPath;
        } else {
            $result=0;
        }
        
        $_SESSION['tmpPath'] = $tmpPath;


    ?>
    
    <p>The minutes were uploaded succesfully. <a target="_blank" href="<?=$minutesURL?>">Click here to view</a>.  If they look okay, press Confirm.</p><p>&nbsp</p>

        <form action="edit_minutes.php" method="post" >
            <input type="hidden" name="action" value="confirm">
            <input type="submit" name="uploadButton" class="upbtn" value="Confirm" />
        </form>

        <? } else if ($action == 'confirm') {  # Save them

            # create the file name
            $newMinutes = ROOT_DIR . MINUTES_DIR . "/" . basename($_SESSION['tmpPath']);

            # let's make sure the file is there
            if (!file_exists($_SESSION['tmpPath'])) {
                 DisplayMessage("Store minutes", "No file");
            }

            if (!copy($_SESSION['tmpPath'], $newMinutes)) {
                DisplayMessage("Copy minutes", "copy failed");
            }
            DB_Log('INFO', "Copied minutes " . $_SESSION['tmpPath']." TO ". $newMinutes);
            unlink($_SESSION['tmpPath']);

            echo "<p><b>The minutes have been updated.</b>
Use the link below to check the minutes page and make sure they appear.</p>
<p><a target='_blank' href='/MembersOnly/BoardMeetingMinutes.php'>Click to see minutes page.</a>";
        } ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
