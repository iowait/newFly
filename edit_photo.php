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
$table = script_param('table');   // a Members or Glider table/photo
if (! $table) {
    $table = 'Members';
}
$GliderID = script_param('GliderID');
$PHOTO_LIMIT=3000000;  // 3 meg
?>

<div  id="divContent">
    
    <? if (!$action) { // first time thru ?>
        <p> There are always new people joining the Club.  <i>Associating a face with a name can be difficult!</i>  Do everyone a favor and post a good photo of yourself.
        </p>
        <p>
            Click the 'browse' button below to find a photo on your computer.  Best format to upload is JPEG images, e.g files ending in .jpg or .jpeg.  Then press Submit.
        </p>
        <form action="edit_photo.php" method="post" enctype="multipart/form-data" >

            <input type="file" name="imgfile" />
            <input type="hidden" name="action"   value="submit">
            <input type="hidden" name="table"     value="<?=$table?>">
            <input type="hidden" name="GliderID" value="<?=$GliderID?>">
            <input type="submit" name="uploadButton" class="upbtn" value="Submit" />
        </form>
    <? } else if ($action == 'submit') { // they uploaded 
        
        $photoName   = $_FILES['imgfile']['name'] ;
        $photoPath   = $_FILES['imgfile']['tmp_name'];
        $photoStatus = $_FILES['imgfile']['error'];
        $photoSize   = $_FILES['imgfile']['size'];
        $photoType   = $_FILES['imgfile']['type'];
        DB_Log('PHOTO', print_r($_FILES['imgfile'], true));
            
        $ext         = pathinfo($photoName, PATHINFO_EXTENSION);
        $tmpName     = "${subName}_${memberID}.$ext";
        $tmpPath     = ROOT_DIR . "/". PHOTO_DIR . "/tmp/$tmpName";
        $photoURL    = PHOTO_DIR . "/tmp/$tmpName";
        

        if (empty($photoPath)) {
            $photoStatus = "You must first browse and select a photo from your PC";
        } else if ($photoSize > $PHOTO_LIMIT) {
            $photoStatus = "Image too large at $photoSize bytes";
        } else if (!strstr($photoType, 'image')) {
            $photoStatus = "Does not appear to be an image.";
        }

        # let's get the dimensions, we'll resize later
        if (!$photoStatus) { // keep going
            list($width_o, $height_o) = getimagesize($photoPath);
            if (!$photoStatus && ($width_o < 100 || $height_o < 100)) {
                $photoStatus = "Image appears to small, less than 100 pixels in size.";
            }
        }

        if ($photoStatus) {
            if (!empty($photoPath)) {
                unlink($photoPath);
            }
            DisplayMessage("Upload a photo", "Got an error: $photoStatus. <br />You can try
again, make sure the photo is an image and not larger than $PHOTO_LIMIT bytes in size.");
        }

        if (move_uploaded_file($photoPath, $tmpPath)) {
            $result=$tmpPath;
        } else {
            $result=0;
        }
        @ unlink($photoPath);  # just to be sure it's gone

        # limits
        $height = 900;
        $width  = 900;

        if ($width_o > $width || $height_o > $height) {
            
            $ratio = $width_o/$height_o;
            
            if ($width/$height > $ratio) { // it's over in width
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }
            // Resample
            $image_p = imagecreatetruecolor($width, $height);
            $image = imagecreatefromjpeg($tmpPath);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_o, $height_o);
            imagejpeg($image_p, $tmpPath);
        }
        $_SESSION['tmpPath'] = $tmpPath;


    ?>
    
        <p>Your photo was uploaded succesfully.  It is displayed below as a
preview of how it will appear in your profile.  If it looks okay, press Confirm.</p><p>&nbsp</p>
        <img width='150' src='<?=$photoURL?>'>

        <form action="edit_photo.php" method="post" >
            <input type="hidden" name="table"     value="<?=$table?>">
            <input type="hidden" name="GliderID" value="<?=$GliderID?>">
            <input type="hidden" name="action" value="confirm">
            <input type="submit" name="uploadButton" class="upbtn" value="Confirm" />
        </form>

        <? } else if ($action == 'confirm') {  # Change it
            
            # let's make sure the file is there
            if (!file_exists($_SESSION['tmpPath'])) {
                 DisplayMessage("Replace your $type photo", "No file");
            }

            # THE BELOW DEPENDS ON IF WE HAVE A MEMBERS OR GLIDER table update
            if ($table == 'Members') {
                $fileName  = basename($_SESSION['tmpPath']);
                $srchField = 'memberID';
                $srchVal   = $memberID;
            } else {
                $fileName  = "glider_$GliderID.jpg";
                $srchField = 'GliderID';
                $srchVal   = $GliderID;
            }

            # create the file name
            $newPhoto = ROOT_DIR . PHOTO_DIR . "/$fileName";

            DB_Log('PHOTO', "Copying photo ". $_SESSION['tmpPath']." TO ". $newPhoto);
            if (!copy($_SESSION['tmpPath'], $newPhoto)) {
                DisplayMessage("Replace your photo", "copy failed");
            }
            unlink($_SESSION['tmpPath']);

            # update the DB with the new name
            $srchInfo[$srchField] = $srchVal;

            $updateInfo['photo'] = $fileName;

            if ($msg = DB_Update($Conn, $table, $srchInfo, $updateInfo)) {
                DisplayMessage("Replace  photo", "Data update failed ($msg)");
            }

            if ($table == 'Members') {
                echo "<p><b>The photo has been replaced </b>with the new image.
Make sure to refresh the page to see your new photo.</p>
<p><a href='edit_member.php'>Back to your profile info.</a>";
            } else {
                echo "<p><b>The photo has been replaced </b>with the new image.
Make sure to refresh the page to see the new impage.</p>
<p><a href='edit_glider.php?action=edit&GliderID=$GliderID'>Back to Glider info.</a>";
            }
        } // end if-else
        ?>
</div>

<!--   ------------------ Content ends here ---------------------------------- -->
<?php
require 'footer.inc';
?>
