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
include_once("./lib/sess_funcs.inc");
@ session_start();

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = preg_replace('/(\/.*\/).*$/', '${1}', $uri);
session_set_cookie_params(0, $path);

// This page logs the user out of the system
// we end the session, destroy all their info
setcookie(session_name(), '', time()-42000, $path);
setcookie("keepLogin", 'bongo', time()-3600, $path, "fly.org");
@session_destroy();

header("Location: login.php");




include ("footer.inc"); // code to end content table, and bottom table
?>

