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
// Login is a bit special, on action login, if they got the name/password
// correct we send them to their account page.
// Need to do all that before calling the standard header.

require("./php_includes.inc");

$dbRunning = 1;

@ session_start();

// if no db
if (!$dbRunning) {
  DisplayPage("/maintenance.php");
}

$action = script_param("action");
$isError = "";

$goodLogin = false;

if ($action == "login") {

    $subAddr = script_param("SUB_ADDR");
    $password = script_param("PASSWORD");
    $keepLogin = script_param("keepLogin");
    $_SESSION['keepLogin'] = $keepLogin; 
    
    // check name/password
    // ProcessLogin will set the globals $subID and $subName

    $msg = "";
    
    if ($msg = processLogin($subAddr, $password, "", $staffImitate)) { // BAd login
        DB_Log('LOGIN_FAIL', "email($subAddr)/$msg");
        $isError = $msg;
        $msg = "<p><font color='red'><b>Sorry</b>: You could not be authenticated: $msg. Please try again or use the forgot password link below.  A password reset link will be sent to your email address.</font></p>";

    } else { 
        DB_Log('LOGIN_OKAY', "email($subAddr)");
	DisplayPage("account.php");
        echo "<p>Login Okay: <a href='account.php'>click here for your Control Panel.</a><br>  You may also always use the Control Panel link at the top right of each page.($keepLogin)</p>";
        include('footer.inc');
        exit(0);
    } 
} // all done!

require("./lib/header.inc");
echo $msg ? $msg : ''; ?>
<p>
    <b>Login below for site access.</b></p>
<p>
     Please <a href="mailto:<?=SERVICE_EMAIL?>?subject='New Site'">send a message</a> &nbsp;if you need a trial login or have any suggestions/problems.</p>

    <form method='post' action='login.php'>

      <table cellpadding = '10'>
        <tr>
          <td style="width:200px;">
            Email address:
          </td>

          <td>
            <input type='text' name='SUB_ADDR' size='35'>
          </td>

        </tr>

        <tr>
          <td>
            Password:
          </td>

          <td>
            <input type='password' name='PASSWORD' size='20'>
          </td>
        </tr>
 <? if (0) { ?>
        <tr>
          <td>
              <p>Check this box to remain logged in until you use the Logout link.<br /><br />

                  <i>Use only on your personal computer. You will remain logged in
              even after exiting your browser or rebooting your computer.</i>
          </td>

          <td>
            <input type='checkbox' name='keepLogin'> Store auto login access on this browser.
          </td>
        </tr>
<? } ?>
        <tr>
          <td colspan='2'>
            <input type='hidden' name='action' value='login'>
           <input type='submit' value='Login'>
          </td>
        </tr>
      </table>
      <p>
          <b>Forgot your password</b>, <a href="forgot.php">click here.</a>
      </p>

    </form>


<?

// do we show the debug field
$addr = $_SERVER['REMOTE_ADDR'];
$debugMsg = "";

if (strstr($addr, "66.66.85.20") || strstr($addr, "192.168.0.27")) { // office machine
  $debugMsg = "<br>Detected login from office location ($addr), debug option shown.<br>";
 }

//include('debug.inc');

include('footer.inc');
?>

