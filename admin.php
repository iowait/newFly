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
$warn = "";
if (!hasPerms('admin')) {
    DisplayMessage('Show admin info', 'No privileges, activity logged.');
}

// always required
$action = script_param('action');
if (empty($action) || !preg_match('/^(log|login|messages|photo)$/', $action)) {
    DisplayMessage('Show admin info', 'No privs, activity logged');
}

// optional items and defaults
$extra = '';

$memberID = '';
$nameDesignator = script_param('nameDesignator'); // name and id, looks like  'smith:55'
if (!empty($nameDesignator)) {
    list($junk, $memberID) = explode(':', $nameDesignator);
    if ($memberID) {
        $extra .= " AND subID = $memberID";
    }
} // got a specific member

$ipAddr = script_param('ipAddr');
if (!empty($ipAddr)) {
    $extra .= " AND ipAddr = '$ipAddr'";
}

$countLimit = script_param('countLimit'); // limit on result rows
if (empty($countLimit)) {
    $countLimit = 50;
}
$extra .= " ORDER BY time DESC LIMIT $countLimit";

// set the SQL query
$sql = 'SELECT time,type,details,subID,ipAddr,url,CONCAT(firstName," ",lastName) AS name FROM EventLog e, Members m WHERE e.subID = m.MemberID ';
switch ($action) {
    case 'login':
        $sql .= " AND type LIKE 'LOGIN%'";
        break;

    case 'log':
        break;

    case 'messages':
        $sql .= " AND type LIKE 'USER_MSG%' OR type LIKE 'ERROR%'";
        break;

    case 'photo':
        $sql .= " AND type LIKE 'PHOTO%'";
        break;

    default:
        DisplayMessage('Show admin info', 'No user privs, activity logged');
        break;

} // end switch

// add the extra options
$sql .= " $extra";
errorLog("SQL is $sql");
if ($msg = DB_Query($Conn, $sql, $results)) {
    DisplayMessage('Admin results', $msg ($sql),0);
}



    ?>
    


    <div  id="divContent">

        <?php
        require 'admin_content.php';
        ?>
    </div>

    <!--   ------------------ Content ends here ---------------------------------- -->
    <?php
    require 'footer.inc';
    ?>
