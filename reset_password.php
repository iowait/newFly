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
// They don't have to be logged in, just need to confirm their reset value

$action = script_param("action");

// verify a valid reset
$reset = script_param('reset');
$emailAddr = script_param('emailAddr');

if (!$reset || !$emailAddr || strlen($reset) < 10 || strlen($emailAddr) < 7) {
   DisplayMessage("reset", "unavailable");
}

// do we match
$srchInfo['emailAddr'] = $emailAddr;
$srchInfo['reset'] = $reset;

if ($msg = DB_Get($Conn, 'Members', $srchInfo, $results)) {
   DisplayMessage("Find your password reset in the system", $msg);
}

if (count($results) != 1) {
   DisplayMessage("Invalid reset link.$reset $emailAddr", $msg);
}



if ($action === "edit") {

  $pass1 = script_param('pass1');
  $pass2 = script_param('pass2');

  // now check the passwords
  if ($pass1 != $pass2 || !$pass1) {
      DisplayMessage("Update your password", "The two values supplied did not match. <b>Click on the reset link in your email to try again.</b>");
  }

  if ($msg = checkPassword($pass1)) {
      DisplayMessage("Update your password", $msg . " <b>Click on the reset link in your email to try again.</b>");
  }

  $srchInfo['emailAddr'] = $emailAddr;
  $updateInfo['password'] = md5($pass1);
  $updateInfo['reset'] = md5(time());

  if ($msg = DB_Update($Conn, 'Members', $srchInfo, $updateInfo)) {
     DisplayMessage("Update your password in the system", $msg);
  }

  DB_LOG('LOGIN_FORGOT', "Password reset OK for $emailAddr");
  echo "<p><b>Your password has been updated.</b><p>";

}  else {


   include ('reset_password_content.php');
}



include('footer.inc');
?>

