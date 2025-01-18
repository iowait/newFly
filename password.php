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

// nothing to do
// This page allows them to change their password

$action = script_param("action");
$pass1  = script_param("pass1");
$pass2  = script_param("pass2");
$msg = '';

if ($action === "edit") {
    
    if ($pass1 != $pass2 || !$pass1) {
        $msg = 'Sorry, the two values supplied did not match.';
    }
    
    if (!$msg) { // proceed
        $msg = checkPassword($pass1); 
    
        if (!$msg) { // proceed
            $srchInfo['emailAddr'] = $_SESSION['SUB_ADDR'];
            $updateInfo['password'] = md5($pass1);
            
            $msg = DB_Update($Conn, 'Members', $srchInfo, $updateInfo);
        }
    } // end if - no errors

    if (!$msg) {
        $msg = '<p><b>Your password has been updated successfully!</b></p>';
        echo $msg;
        include('footer.inc');
        exit;
    } else {
        echo "<font color='red'>$msg</font><p>";
    }
    
}     
include('password_content.php');


include('footer.inc');
?>

