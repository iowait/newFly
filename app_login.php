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

require("./app_php_includes.inc");

$action = script_param("action");

if ($action == "login") {

    $subAddr = script_param("SUB_ADDR");
    $password = script_param("PASSWORD");
    
    if ($msg = processLogin($subAddr, $password, "", $staffImitate)) { // BAd login
        DB_Log('LOGIN_FAIL', "app email($subAddr)/$msg");
        sendJSON(['status' => 'fail', 'msg' => $msg]);   // sendJSON always exits
    } else {
        // generate and store a unique key
        $bytes = random_bytes(20);
        $key = bin2hex($bytes);
        DB_Log('LOGIN_OKAY', "app email($subAddr)");
        $srchVal['emailAddr'] = $subAddr;
        $updateInfo['appLogin'] = $key;
        if ($msg = DB_Update($Conn, 'Members', $srchVal, $updateInfo)) {
            sendJSON(['status' => 'fail', 'msg' => $msg]);
        } else {
            sendJSON(['status' => 'okay', 'appLogin' => $key, 'msg' => '']);
        }
    } 
} // all done!

require("./lib/header.inc");
echo $msg ? $msg : ''; ?>
<p>
    <b>Login below for site access.</b></p>
<p>
     To get a new password use the "Forgot your password" link at the bottom.  Please &nbsp;<a href="mailto:<?=SERVICE_EMAIL?>?subject='New Site'">send a message</a> &nbsp;if you have any suggestions/problems.</p>

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

