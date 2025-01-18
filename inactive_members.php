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
?>
        <div  id="divContent">

               <table>
                   <tbody>
<?
if ($msg = DB_Query($Conn, "SELECT * FROM Members WHERE memberType = 'Inactive' ORDER BY lastName", $results)) {
        echo "ERROR, query failed ($msg)";
}

foreach ($results as $row) {
  $lastName = preg_replace("/ /",'',$row['lastName']); // need to collapse spaces
  $localPath = preg_replace("/ /",'',"photos/" . $row['firstName'] ."_". $lastName.".jpg");
  if (!file_exists("/pub/comwww$localPath")) {
      $localPath="photos/no_photo.png";
  }
  $photo = "$localPath";

?>

                       <tr>
                           <td>
                           <a href="<?=$photo?>">
                           <img alt="" width="150px" src="<?=$photo?>" class="img-centered" /></a></td>
                           <td>
                              <address><?=$row['firstName']?>  <?=$row['lastName']?></address>
                              <address><?=$row['street']?></address>
                              <address><?=$row['city']?>, <?=$row['state']?> <?=$row['zip']?></address>
                              <address><?=$row['memberType']?> / <?=$row['pilot']?> <br></address>
                           </td>
                           <td>
                              <address>H:<?=$row['phone']?></address>
                              <address>W:<?=$row['phone2']?></address>
                              <address><a href="mailto:<?=$row['emailAddr']?>"><?=$row['emailAddr']?></a></address>
                           </td>
                       </tr>
                       <tr>
                           <td colspan="3"><hr></td>
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

