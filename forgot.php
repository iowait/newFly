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
// This page allows them to receive their password

$action = script_param("action");

if ($action === "edit") {

    $emailAddr = script_param('emailAddr');
    // let's save some hassle and make sure the email is good.
    if (getMemberID('', '', $emailAddr, $id)) {
        echo "<p><b>SORRY</b>, the email address you supplied ($emailAddr) does not match a registered member.<br/>  To get this corrected please &nbsp;<a href=\"mailto:".SERVICE_EMAIL."?subject='New Site Login'\">send a message</a> &nbsp; with your name and proper email address.</p>";
        DB_Log('LOGIN_FORGOT', "no reset match for ($emailAddr)");
        
    } else {
    
        $srchInfo['emailAddr'] = $emailAddr;
        $reset = md5(time());
        $updateInfo['reset'] = $reset;
        
        //errorLog("search: ". print_r($srchInfo, true));
        if ($msg = DB_Update($Conn, "Members", $srchInfo, $updateInfo)) {
            DisplayMessage("Reset your password", "search failed($msg)");
            DB_Log('LOGIN_RESET', "no reset match for ($emailAddr)");
        }

        DB_LOG('LOGIN_FORGOT', "Sent password reset to: $emailAddr");
        
        if ($msg = SendEmail( $emailAddr, SERVICE_EMAIL, "Login Information for FLY",
                              "To reset your login password for fly.org\nclick on the following link:\n\n" .
NON_SECURE_URL . FLY_DIR . "/reset_password.php?emailAddr=$emailAddr&reset=$reset"
            
        )) {
            DisplayMessage("Send your password", "Unable to send to: $emailAddr");    exit(1);
        }
        echo "<p>A reset link has been sent to: $emailAddr.<p>";
        
    } // end if -else, match on email
    
} else { // end if - edit action
    include('forgot_content.php');
}

include('footer.inc');
?>

