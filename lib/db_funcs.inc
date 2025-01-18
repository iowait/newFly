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
# $Id: db_funcs.inc,v 1.32 2009/11/16 16:52:14 thebook Exp $
# Common Database Functions
define ("SYSTEM_ERROR","@@@");

$QUERY_COUNT = array('TOTAL' => 0);

// need to handle mysql/pgsql, mysql is the default, if they have not set $isDB, then
// useDB is mysql.  We ONLY use $useDB below
function setDB() {
    global $isDB, $useDB;

    if (!isset($isDB)) {
        $useDB = "mysql";
    } else {
        $useDB = $isDB;
    }
}
# Debug This function sets and checks the debug mask, prints
# the supplied message if set.  The level is set in login.php4
# by an optional param to the screen name;
# INPUTS: mask to check
#   0x1 - DB_Get
#   0x2 - DB_Update
#   0x4 - DB_Delete
#   0x8 - DB_Insert
#  0x10 - Session func activity
#  0x20 - Edit schedule
#  0x40 - keepLogin
#  0x80 - DB_Query
# 0x100 - Print $_POST & $_GET in footer
# 0x200 - 
#  OUTPUT: none
# RETURNS: true if debug mask is a match on, false otherwise
#
function Debug($mask, $function, $msg) {

    global $DebugLevel;

    if ($mask & $DebugLevel) {
        if ($msg && $function) {
            DB_Log('DEBUG', "($function): $msg");
        }
        return(1);
    } else {
        return(0);
    }

} # end fucntion Debug


# DB_Check : checks connection to mySQL server
# parameters : mySQL connection id
# returns : 0 if successful or error message
function DB_Check(&$conn) {
    if (mysql_ping($conn))
        return 0;

    // force new connection
    DB_Close($conn); // don't care about return value

    if ($msg = DB_Open(FAC_DB, FAC_HOST, DB_USER, DB_PASS, $conn))
        return "Could not re-establish DB connection:" . SYSTEM_ERROR . $msg;
    else
        return 0;
}

# DB_Close :  Closes connection to mySQL server
# parameters: mySQL connection id
# returns: 1 if successful, 0 if failed
function DB_Close (&$conn) {

    global $useDB;
    global $queryTime;
    setDB();
    $thisTime = microtime();

    $status = 0;

    if ($useDB == "mysql") {
        if ( mysql_close($conn) ) {
            $status = 1;
        }
    } else {
        if ( pg_close($conn) ) {
            $status = 1;
        }
    }

    $conn = false;

    qCount('CLOSE');

    $queryTime += microtime() - $thisTime;

    return($status);
}


# Create DBs - Pass an array of db names to be created
function DB_CreateDBs($dbArray, $conn = 0){
    Global $Conn; // only used if they didn't specify.
    global $useDB;
    setDB();

    if (!$conn) {
        $conn = $Conn;
    }

    $query = "";
    $results = "";

    if (empty($dbArray)) {
        return(SYSTEM_ERROR."Failed to specify dbs");
    }

    if (!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    $status = 0;

    foreach($dbArray as $db){
        if ($useDB == "mysql") {
            $query = "CREATE database IF NOT EXISTS $db";
        } else {
            if (DB_Exists($conn, $db)) {
                continue;
            }
            $query = "CREATE database $db";
        }

        if ($msg = DB_Query ($conn, $query, $results)){
            $status .= SYSTEM_ERROR."Failed to create $db ($msg)";
        }
    }

    return($status);
} // end function DB_CreateDBs



# Create Tables - create a table in all DBs
# Must pass an array of db names in which to create the table and the query used to create the table. If no table type is
# specified the default table type will be used.
# example query: "CREATE TABLE IF NOT EXISTS customer ( name varchar(50) NOT NULL default 'john doe', address varchar(50)"
#                 ." NOT NULL default '123 easy st', zip int(11) NOT NULL default 12345)"
# PostgreSQL made for complications, no such thing as USE or IF EXISTS, need Connection string
# to force connect to proper db.
#
function DB_CreateTables($dbArray, $tableName, $query, $tableType, $connString = 0, $conn = 0){
    Global $Conn;  // only used if they didn't specify.
    global $useDB;
    setDB();

    $tempConn = 0;

    if (!$conn) {
        $conn = $Conn;
    }

    if (empty($dbArray) || empty($query) || empty($tableName) ) {
        return("Failed to specifiy dbs, table, or query");
    }

    if (!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    if (!empty($tableType) && $useDB == "mysql") {
        $query = $query." ENGINE=$tableType";
    }

    $status = 0;
    foreach($dbArray as $db){

        // switch the db
        if ($useDB == "mysql") {
            $query1 = "USE $db";
            if ($msg = DB_Query ($conn, $query1, $results)){
                $status .= "Failed to use $db ($msg)";
                return($status);
            }
        } else {

            if ($tempConn) { // close any existing.
                DB_Close($tempConn);
                $tempConn = 0;
            }

            // open brand new connection to proper db
            if ( $msg = DB_Open($db, $connString,'thebook','',$tempConn) ) {
                echo "Create-tables, can't connect to sql test server with ($connString), db ($db), useDB ($useDB), reason ($msg)";
                exit(1);
            }
        }

        // create the table
        if ($useDB == "mysql") {
            $query2 = "CREATE TABLE IF NOT EXISTS $tableName $query";
        } else {
            if (DB_Exists($tempConn, $db, $tableName)) {
                // $status .= " Exists ($db/$tableName) in CreateTable, skipping";
                continue;
            }
            $query2 = "CREATE TABLE $tableName $query";
        }

        if ($tempConn) { // we are doing pgsql
            $conn = $tempConn;
        }

        if ($msg = DB_Query ($conn, $query2, $results)){

            // before returning a bad status, if we are doing pgsql, see if the table is there.
            if (!DB_Exists($tempConn, $db, $tableName)) {
                $status .= "Failed to create table for $db ($msg) ($query2)";
            }
        }

        if ($tempConn) {
            DB_close($tempConn);
        }
    } // end for - all dbs

    return ($status);
} // end function DB_CreateTables


# Create User(s) for Each DB
# Create users for each db, the size of userArray and dbArray must be the same if more than 1
# If userArray has only one entry, it will apply to all in dbArray
# Permissions are given for the specified host.

function DB_CreateUsers($userArray, $dbArray, $host = "%", $password, $conn){
    Global $Conn; // only used if they didn't specify.
    global $useDB;

    if (!$conn) {
        $conn = $Conn;
    }

    if (empty($userArray) || empty($dbArray)) {
        return("Failed to specifys dbs or users");
    }

    if (!is_array($userArray)){
        $userArray = array($userArray);
    }
    if (!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    if ((count($userArray) != count($dbArray)) && (count($userArray != 1))){
        return("The user array and the db array must be of equal length or the user array must = 1");
    }

    // the dbArray is always larger or same size as userArray, special condition if 1 user
    if (count($userArray) == 1) {
        $oneUser = 1;
    } else {
        $oneUser = 0;
    }

    $status = 0;
    for ($iMax = count($dbArray), $i = 0; $i < $iMax; $i++) {

        $results = "";
        $db = $dbArray[$i];
        $user = $oneUser ? $userArray[0] : $userArray[$i];
        $query = "GRANT ALL ON $db.* TO '".$user."'@'".$host."' IDENTIFIED BY '".$password."'";

        if ($msg = DB_Query ($conn, $query, $results)){
            $status .= "Failed to create $user ($msg)";
        }
    }

    return($status);

} // end DB_CreateUsers


# DB_Delete : delete a record from a mySQL table
# parameters : $conn, $table, $searchfield
# returns : status : 0 if successful, or errormessage
#
# check to make sure there is only 1 match in table, do not want to delelte
# multiple. (maybe DB_Delete_All)
function DB_Delete ($conn, $table, $searchvals, $deleteAll = 0) {

    global $useDB;
    global $queryTime;
    setDB();

    # first check get the results for searchvals - should return only 1 match
    //    addslashes($searchvals);
    $order_by = "";
    $status= DB_Get($conn,$table,$searchvals,$check,$order_by);
    if ( ! $status) {
        $numresults = count($check);
        if ($numresults == 1 || ($numresults > 1 && $deleteAll)) {

            $fieldstr = "";
            # build the query string, have 1 strings to build, selectstring,
            $search_keycount = count($searchvals);

            foreach ($searchvals as $key => $val) {
                
                # this string will be comma seperated if we are passed multiple fields
                $val = trim($val);  # strip leading/trailing blanks
                if ( $val) {
                    $fieldstr =  $fieldstr . $key . "=\"$val\"" . " and ";
                }
            }

            # we have to chop off last 5 chars off the string.  PHP chop only eliminates
            # whitespace, we have to get rid of ", "

            $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -5);
            $sql = "delete from $table where $fieldstr";
            $thisTime = microtime(true);

            if ($useDB == "mysql") {
                $result = mysqli_query($conn, $sql);
                if ($status = mysqli_error($conn)) { // return right now
                    return(SYSTEM_ERROR.$status);
                }

            } else {
                $sql = str_replace("\"", "'", $sql);
                $result = pg_query($conn, $sql);
                if (!$result) {
                    $status = pg_last_error();
                    return(SYSTEM_ERROR.$status.$sql);
                }
            }

            $queryTime += microtime(true) - $thisTime;

            qCount("DELETE");

            if ($result == 0) {
                $status = mysqli_error($conn);
            } else {
                $status = 0;
            }
        } elseif ($numresults == 0)  { // didn't find any, thats okay.
            if( $table == "MsgHdr" ) {
                $fieldstr = "";
                # build the query string, have 1 strings to build, selectstring,
                $search_keycount = count($searchvals);

                foreach ($searchvals as $key => $val) {
                    # this string will be comma seperated if we are passed multiple fields
                    $val = trim($val);  # strip leading/trailing blanks
                    if ( $val) {
                        $fieldstr =  $fieldstr . $key . "=\"$val\"" . " and ";
                    }
                }

                # we have to chop off last 5 chars off the string.  PHP chop only eliminates
                # whitespace, we have to get rid of ", "

                $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -5);
                $sql = "delete from $table where $fieldstr";
            }
            $status = 0;
        } elseif ($numresults > 1)  {
            $status = "mySQL Query returned multiple matching records on $table";
        }
    }
    Debug(4, 'DB_Delete', $sql);
    return ($status);
} # end function DB_Delete


# Drop DBs - Pass an array of db names to be dropped
function DB_DropDBs($dbArray, $conn = 0){
    Global $Conn; // only used if they didn't specify.
    global $useDB;
    setDB();

    if (!$conn) {
        $conn = $Conn;
    }

    $query = "";
    $results = "";

    if(!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    $status = 0;

    foreach($dbArray as $db){

        if ($useDB == "mysql") {
            $query = "DROP DATABASE IF EXISTS $db";
        } else { // for pgsql - we can't drop if it is the current db, temp switch
            if (!DB_Exists($conn, $db)) {
                continue;
            }
            $query = "DROP DATABASE $db";
        }

        if ($msg = DB_Query ($conn, $query, $results)) {
            $status .= SYSTEM_ERROR."Failed to drop database $db ($msg)";  // we will keep trying to delete others
        }
    }
    return($status);
} // end function DB_DropDBs


# DropTables - Completely remove tables from all DBs
# If single db or table name is passed convert it to an array
# Example Query: $query = "DROP TABLE IF EXISTS $table";

function DB_DropTables($dbArray, $tableName, $connString = 0, $conn = 0){
    Global $Conn; // only used if they didn't specify.
    global $useDB;
    setDB();

    $tempConn = 0;

    if (!$conn) {
        $conn = $Conn;
    }

    if (!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    if (!is_array($tableName)){
        $tableName = array($tableName);
    }

    $status = 0;
    foreach($dbArray as $db){

        if ($useDB == "mysql") {

            $query1 = "USE $db";
            if ($msg = DB_Query ($conn, $query1, $results)){
                $status .= "Failed to use $db ($msg)";
            }
        } else {

            if ($tempConn) { // close any existing.
                DB_Close($tempConn);
                $tempConn = 0;
            }

            // open brand new connection to proper db
            if ( $msg = DB_Open($db, $connString,'thebook','',$tempConn) ) {
                echo "Create-tables, can't connect to sql test server with ($connString), db ($db), useDB ($useDB), reason ($msg)";
                exit(1);
            }

        }

        if ($tempConn) {
            $conn = $tempConn;
        }

        foreach($tableName as $table){
            $results = "";

            if ($useDB == "mysql") {
                $query = "DROP TABLE IF EXISTS $table";
            } else { // for pgsql - we can't drop if it is the current db, temp switch

                if (!DB_Exists($conn, $db, $tableName)) { // not there, skip it
                    //$status .= "Does NOT Exist ($db/$tableName) in DropTables, skipping ";
                    continue;
                }
                $query = "DROP TABLE $table";
            }

            if ($msg = DB_Query ($conn, $query, $results)){
                $status .= "Failed to drop $table for $db ($msg)";
            }
        } // end for - all tables

        if ($tempConn) {
            DB_close($tempConn);
        }

    } // end for - all dbs

    return ($status);
} // end function DB_DropTables


# Drop User(s) for Each DB
# If userArray has only one entry, it will be converted to an array
# MySQL versions prior to 5.0.2 require the separate revoke command to remove priviliges prior to
# to using drop user, Version 5.0.2 and later can do it all with just drop user.
# Drop user does not work in mysql versions prior to 4.1.1

function DB_DropUsers($userArray, $dbArray, $host, $conn){
    Global $Conn;  // only used if they didn't specify.
    global $useDB;

    if (!$conn) {
        $conn = $Conn;
    }

    if (empty($userArray) || empty($dbArray)) {
        return("Failed to specifys dbs or users");
    }

    if (!is_array($userArray)){
        $userArray = array($userArray);
    }

    if (!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    if ((count($userArray) != count($dbArray)) && (count($userArray != 1))){
        return("The user array and the db array must be of equal length or the user array must = 1");
    }

    // the dbArray is always larger or same size as userArray, special condition if 1 user
    if (count($userArray) == 1) {
        $oneUser = 1;
    } else {
        $oneUser = 0;
    }

    $status = 0;
    for ($iMax = count($dbArray), $i = 0; $i < $iMax; $i++) {

        $results = "";
        $db = $dbArray[$i];
        $user = $oneUser ? $userArray[0] : $userArray[$i];

        $query = "REVOKE ALL ON $db.* FROM '".$user."'@'".$host."' ";

        if ($msg = DB_Query ($conn, $query, $results)){
            $status .= "Failed to revoke $user ($msg)";
        }

        $query = "DROP USER '".$user."'@'".$host."'";

        if ($msg = DB_Query ($conn, $query, $results)){
            // changed return statement to "status ="  because of check in DB_Query
            $status .= "Failed to drop $user ($msg)";
        }
    }

    return($status);
} // end function DB_DropUsers




# DB_Error : Formats a readable error message
# parameters: high level message
function DB_Error ($msg, $conn = 0) {
    global $useDB;

    $detail = mysqli_error($conn);
    echo "<p>Sorry, an error occured: $msg<br>";
    echo "Details:$detail</p>";
}


# DB_Exists:  Given a single DB name, check to see if exists.
#  If given Db and table Name, check to see if table exists.
# RETURN 1 if exists, 0 otherwise
#  NOT a lot of error checking here.
#  USED JUST FOR PGSQL
function DB_Exists ($conn, $dbName, $tableName = "") {

    global $useDB;
    setDB();

    $results = array();

    if ($useDB == "mysql") { // called in error
        echo "DB_Exists error 1";
        return(0);
    }

    if (! $dbName) {
        echo "DB_Exists error 2";
        return(0);
    }

    if (! $tableName) {
        $sql = "SELECT datname FROM pg_catalog.pg_database WHERE datname = '$dbName'";
    } else { // table is always with respect to the current db
        $sql = "SELECT tablename FROM pg_tables WHERE tablename = '$tableName' AND current_database() = '$dbName'";
    }

    if ($msg = DB_Query($conn, $sql, $results)) {
        echo "DB_Exists error 3 ($msg)";
        return(0);
    }

    if (count($results)) {
        return(1);
    } else {
        return(0);
    }

} // end DB_Exists.

#DB_Get: searches mySQL table $table for matches  - assumes we want exact matches
# parameters:
# $conn = mysql connection id
# $table = mysql table to query
# $search_vals = assoc array, keys are the sql fields
#    if values are presend for a key, the query is generated based on these matched
# results = array of assoc arrays returned to the function call
# order_by = optional - if we want the results sorted
#
# returns : 0 on success, or error message
# results =array of mySQL result rows (each as an assoc array)
#
function DB_Get ($conn, $table,$searchvals,&$results,$order_by = "", $limitCount = "") {

    global $queryTime;
    global $useDB;
    setDB();

    $results = array();
    $result = "";
    $selectstr = "";
    //    foreach($searchvals as $member) {
    //   addslashes($member);
    //}
    # build the query string, have 2 strings to build, selectstring, and then the
    # values to match
    $keycount = count($searchvals);
    $fieldstr = "";
    foreach ($searchvals as $key => $val) {
        $selectstr = $selectstr . $key . ", ";
        if ( $val ) {


            // We now look out for an array in $val, if it is that means we are getting either a range type search
            // or a multi select, e.g.  age between 10 and 18, or ethic in ('black','white','red')
            // The first element in the array will be the type, i.e. INT, or ENUM -- which will tell us how to construct,
            // the rest of the elements will be the values to search for.
            // We now support LIKE as the first array element, followed by one value
            if (is_array($val)) {
                if (strstr($val[0], "INT")) { // some type of numeric range
                    $fieldstr .= $key . " between $val[1] and $val[2] and ";

                } else if (stristr($val[0], "ENUM") || stristr($val[0], "SET")) { // enumerated value
                    // create list
                    $num = count($val);
                    $list = "";
                    if (stristr($val[0], 'ENUM')) {
                        for ($i = 1; $i < $num; $i++) {
                            $list .= "'".mysqli_real_escape_string($conn,$val[$i])."'";
                            if ($i +1 < $num) {
                                $list .= ",";
                            } // end if - next to last
                        } // end for - all members
                        $fieldstr .= $key . " in ( $list ) and ";
                    } else { // it's a SET, string them together with LIKE
                        for ($i = 1; $i < $num; $i++) {
                            $member = mysqli_real_escape_string($conn,$val[$i]);
                            $fieldstr .= "$key LIKE '%$member%' and ";
                        }
                    }
                        
                } else if (strstr($val[0], "<") || strstr($val[0], ">") || strstr($val[0],"!=")) {
                    $fieldstr .= $key. " $val[0] '$val[1]' and ";

                } else if (!strcasecmp($val[0], "LIKE")) { // do a wildcard LIKE comparison
                    $fieldstr .= $key. " LIKE '%". mysqli_real_escape_string($conn,$val[1]) ."%' and ";

                } else {

                    return(SYSTEM_ERROR."Unexpected search type: $val[0] for key ($key)");
                }

            } else { // the old way
      	        $val = trim($val);  # strip leading/trailing blanks
      	        $fieldstr =  $fieldstr . $key . "=\"" . mysqli_real_escape_string($conn,$val). "\"" . " and ";
            }
        }
    }

    # we have to chop off last 2 chars off the string.  PHP chop only eliminates
    # whitespace, we have to get rid of ", "

    $selectstr = substr ($selectstr,0,strlen($selectstr) -2);
    $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -5);

    # check the fieldstr .  if null, this is a generic query, dont need to include
    # the where
    if ($fieldstr) {
        $sql = "select " . $selectstr ." from $table where $fieldstr";
    } else {
        $sql = "select " . $selectstr ." from $table ";
    }

    if ($order_by) {
        $sql = $sql . " order by $order_by";
    }

    if ($limitCount) {
        $sql = $sql . " limit $limitCount";
    }

    #$sql = mysqli_real_escape_string($conn,$sql);
    Debug(1, "DB_get", "query is: $sql");

    $thisTime = microtime(true);


    if ($useDB == "mysql") {
        $result = mysqli_query($conn, $sql);
        if ($status = mysqli_error($conn)) { // return right now
            return(SYSTEM_ERROR.$status);
        }
        $numRows = mysqli_num_rows($result);

    } else {
        $sql = str_replace("\"", "'", $sql);
        $result = pg_query($conn, $sql);
        if (!$result) {
            $status = pg_last_error();
            return(SYSTEM_ERROR.$status.$sql);
        }
        $numRows = pg_num_rows($result);
    }
    //  $result = mysqli_query($conn, $sql);

    $queryTime += microtime(true) - $thisTime;

    // Increment query count
    qCount('SELECT');

    # call mysql_fetch_assoc, which returns an associative array
    # based on the fields (which will be the same as the incoming keys
    # then just build a new array of these arrays

    if ($result) {
        Debug(1, "DB_get", "num results is: $numRows");

        if ($useDB == "mysql") {
            $array = mysqli_fetch_assoc($result);
        } else {
            $array = pg_fetch_assoc($result);
        }

        if ($array) {
            $count = 0;
            while($array) {
                $results[] = $array;
                $count ++;

                if ($useDB == "mysql") {
                    $array = mysqli_fetch_assoc($result);
                } else {
                    $array = pg_fetch_assoc($result);
                }

            }
        } else {
            unset($results); // set results to NULL
        }
        $status = 0;

    } else {

        if ($useDB == "mysql") {
            $status = mysqli_error($conn);
        } else {
            $status = pg_last_error();
        }
        $status = SYSTEM_ERROR.$status;
    }
    // Debug(1, "DB_get", "return status is: $status");
    return ($status);

} # end function get


#DB_Insert: Adds a record to the mySQL table
# parameters: $conn, $table, $newvals
# returns: 0 if successful, errormessage  if fails
function DB_Insert ($conn, $table, $newvals, &$insertID = 0) {

    global $queryTime;
    global $useDB;
    setDB();

    // errorLog("INSERT: ".print_r($newvals, true));

    //    addslashes($newvals);
    $insertID = 0;
    # build the query string, have 2 strings to build,
    # selectstring - field names
    # fieldstr - matching pairs, key = values
    $selectstr = "";
    $fieldstr = "";
    foreach ($newvals as $key => $val) {

        $selectstr = $selectstr . $key . ", ";

        if (is_array($val)) { // some special processing, else the old way

            if (strstr($val[0], "ENUM") || strstr($val[0], "SET")) { // enumerated value

                // create list
                array_shift($val);
                $list = implode(",", $val);
                $fieldstr .= "\"$list\"". ", ";

            } else {

                return(SYSTEM_ERROR."Unexpected field type ($val[0]) for key ($key)");

            } // end if - else proper spec for array

        } else {

            $val = trim($val);  // strip leading/trailing blanks
            if ($val == "") { // empty value
                $val = "";
            }

            $val = mysqli_real_escape_string($conn,$val);

            $fieldstr .= "\"$val\"" . ", ";
        } // end if-else, input array


    }

    # we have to chop off last 2 chars off the string.  PHP chop only eliminates
    # whitespace, we have to get rid of ", "

    $selectstr = substr ($selectstr,0,strlen($selectstr) -2);
    $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -2);

    $sql = "insert into $table ($selectstr) values ($fieldstr)";
    if ($table != 'EventLog') {
        DB_Log("INSERT", $sql);
    }
    
    // Increment count
    qCount('INSERT');

    $thisTime = microtime(true);

    if ($useDB == "mysql") {  // old block

        $result = mysqli_query($conn, $sql);
        $queryTime += microtime(true) - $thisTime;

        // any warnings?
        if (mysqli_warning_count($conn)) {
            if ($resultW = mysqli_query($conn, "SHOW WARNINGS")) {
                $rowW = mysqli_fetch_row($resultW);
                $msgW = sprintf("%s (%d): %s\n", $rowW[0], $rowW[1], $rowW[2]);
                mysqli_free_result($resultW);
            }          
            errorLog("ERROR Got MySQL warnings ($msgW) on insert: $sql");
        }
        //result returns 1 here on success,
        if ($result && mysqli_affected_rows($conn) > 0) {
            $status= 0;
        }   else {
            $status = mysqli_error($conn);
            $status = SYSTEM_ERROR.$status;

            // it is possible to fail because of a mysql error OR we still want an error return if no rows were
            // inserted.
            if (!$status && (mysqli_affected_rows($conn) < 1)) {
                $status = "No rows inserted";
            }
        }
        $insertID = mysqli_insert_id($conn);

    } else {

        $sql = str_replace("\"", "'", $sql);
        $result = pg_query($conn, $sql);
        $queryTime += microtime(true) - $thisTime;

        //result returns 1 here on success,
        if ($result && pg_affected_rows($result) > 0) {
            $status= 0;
        }   else {
            $status = pg_last_error();
            $status = SYSTEM_ERROR.$status;

            // it is possible to fail because of a mysql error OR we still want an error return if no rows were
            // inserted.
            if (!$status && (pg_affected_rows($conn) < 1)) {
                $status = "No rows inserted";
            }
        }
        $insertID = $result; // ZZ - should be an oid/ insert id????

    } // end if-else pgsql or mysql

    //Debug(8, "DB_Insert", "insert was $sql -- result($result), status($status), ($insertID)");
    //echo "<br>insert was $sql -- result($result), status($status), ($insertID)<br>";
    return ($status);

} # end function insert

#
# DB_Log: Provides a simple logging function to the server for debug
# purposes.  Just does straight inserts,s houldnt be left on all
# the time.
# The EventLog table has the following columns:
#  time: timestamp field
#  type: 10 char field (req)
#  details: 80 char field (reg)
#  subID: 20 char field
#  IPaddr: 15 char field
function DB_Log ($type, $details) {

    global $Conn;

    if (!$type || !$details) {
        return(SYSTEM_ERROR."Failed for no type or details supplied");
    }


    $vals['type'] = $type;
    $vals['details'] = $details;
    $memberID = isset($_SESSION['subID']) ? $_SESSION['subID'] : 0;
    $vals['subID'] = $memberID;
    $vals['ipAddr'] = getenv("REMOTE_ADDR");
    $vals['url'] = getenv("REQUEST_URI");

    errorLog("DB_LOG: memberID:$memberID - $type - $details");

    if ($msg = DB_Insert($Conn, "EventLog", $vals)) {
        return(SYSTEM_ERROR."Failed to log ($msg)");
    }
    return(0);
} # end function DB_Log


# DB_Open: Opens connection to mySQL server
# parameters: $db, $host, $user, $passwd, $conn
# returns: $status : 0 if success, errormessage if fails
#
# db = database name
# host = hostname mysql server is running on (host:port)
# user = username for mysql
# passwd = mysql passwd
# conn = mysql socket connection id
#
function DB_Open ($db,$host,$user,$passwd,&$conn) {

    global $useDB;
    global $queryTime;
    setDB();

    // suppress warning if connection doesn't happen
    $thisTime = microtime(true);
    $status = "";

    if ($useDB == "mysql") {
        // fourth parameter is new_link--ie, if $conn has already been set,
        // we are calling because connection timed out and we need a new resource
        // otherwise, if not set yet, it can use an already established resource.
        $conn = @mysqli_connect($host,$user,$passwd, ($conn ? true : false));

        $queryTime += microtime(true) - $thisTime;

        qCount('CONNECT');

        if ($conn) {
            if (! mysqli_select_db($conn, $db)) {
                $status = mysqli_error($conn);
            } else {
                $status= 0;
            }

        } else { # connection failed
            $status = "MySQL - can't connect to $db on $host";
        }

    } else { // pgsql

        // the host value can have a :portNum
        if (strstr($host, ":")) {
            list($justHost, $justPort) = split(':', $host);
            $conn = pg_connect("host=$justHost port=$justPort dbname=$db user=$user");
        } else {
            $conn = pg_connect("host=$host dbname=$db user=$user");
        }

        if (!$conn) {
            $msg = pg_last_error();
            $status = "PgSQL - can't connect to $db on $host ($msg)";
        }


    } // end if-else mysql or pgsql

    return ($status) ;
} # end function DB_Open

# DB_Query: Does a simple query of the database
# returns: 0 on success, msg otherwise
# Conn - connection
# query - the query
# results - where to put results
#
function DB_Query ($conn, $query, &$results) {

    global $useDB;
    global $queryTime;
    setDB();

    Debug(0x80, "DB_Query", "Query is $query");

    $results = array(); // clear it out

    $thisTime = microtime(true);
    // remove any \n
    $query = preg_replace('/\n/', ' ', $query);

    //$query = mysqli_real_escape_string($conn,$query);

    // mysql_query will return a resource on SHOW, SELECT..., just TRUE for others on success
    if ($useDB == "mysql") {
        $result = mysqli_query($conn, $query);
        if ($status = mysqli_error($conn)) { // return right now
            return(SYSTEM_ERROR.$status);
        }

    } else {
        $query = str_replace("\"", "'", $query);
        $result = pg_query($conn, $query);
        if (!$result) {
            $status = pg_last_error();
            return(SYSTEM_ERROR.$status.$query);
        }
        $numRows = pg_num_rows($result);
    }

    $queryTime = microtime(true) - $thisTime;

    // Increment query count with first word of query

    qCount(strtoupper(preg_replace('/^(\w+)\s.*/s', "$1", $query)));


    // if we are doing a select or show, we expect data rows to come back
    if (!strncasecmp($query, "select", 6) || !strncasecmp($query, "show", 4)
        || !strncasecmp($query, "describe", 8)) {


        if ($result) {

            if ($useDB == "mysql") {
                $array = mysqli_fetch_assoc($result);
                $numRows = mysqli_num_rows($result);
            } else {
                $array = pg_fetch_array($result);
            }

            Debug(0x80, "DB_Query", "num results is: $numRows");

            if ($array) {
                $count = 0;
                while($array) {
                    $results[$count] = $array;
                    $count ++;

                    if ($useDB == "mysql") {
                        $array = mysqli_fetch_assoc($result);
                    } else {
                        $array = pg_fetch_array($result);
                    }
                }

            } else {
                unset($results); // set results to NULL
                return(0);
            }
        } else {
            return(SYSTEM_ERROR."unexpected case 1");
        }


    } else { // an update, delete, drop...., may expect affected rows on some

        if (!$result) {
            return(SYSTEM_ERROR."unexpected case 2");
        }

        return(0);

    }

} // end function DQ_Query

#DB_Insert: Adds a record to the mySQL table
# parameters: $conn, $table, $newvals
# returns: 0 if successful, errormessage  if fails
function DB_Replace($conn, $table, $newvals, &$insertID = 0) {

    global $queryTime;
    global $useDB;
    setDB();

    //    addslashes($newvals);
    $insertID = 0;
    # build the query string, have 2 strings to build,
    # selectstring - field names
    # fieldstr - matching pairs, key = values
    $selectstr = "";
    $fieldstr = "";
    foreach ($newvals as $key => $val) {

        $selectstr = $selectstr . $key . ", ";

        if (is_array($val)) { // some special processing, else the old way

            if (strstr($val[0], "ENUM") || strstr($val[0], "SET")) { // enumerated value

                // create list
                array_shift($val);
                $list = implode(",", $val);
                $fieldstr .= "\"$list\"". ", ";

            } else {

                return(SYSTEM_ERROR."Unexpected field type ($val[0]) for key ($key)");

            } // end if - else proper spec for array

        } else {

            $val = trim($val);  // strip leading/trailing blanks
            if ($val == "") { // empty value
                $val = "";
            }

            $val = mysqli_real_escape_string($conn,$val);

            $fieldstr .= "\"$val\"" . ", ";
        } // end if-else, input array


    }

    # we have to chop off last 2 chars off the string.  PHP chop only eliminates
    # whitespace, we have to get rid of ", "

    $selectstr = substr ($selectstr,0,strlen($selectstr) -2);
    $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -2);

    $sql = "replace into $table ($selectstr) values ($fieldstr)";

    // Increment count
    qCount('INSERT');

    $thisTime = microtime(true);

    if ($useDB == "mysql") {  // old block

        $result = mysqli_query($conn, $sql);
        $queryTime += microtime(true) - $thisTime;

        //result returns 1 here on success,
        if ($result && mysqli_affected_rows($conn) > 0) {
            $status= 0;
        }   else {
            $status = mysqli_error($conn);
            $status = SYSTEM_ERROR.$status;

            // it is possible to fail because of a mysql error OR we still want an error return if no rows were
            // inserted.
            if (!$status && (mysqli_affected_rows($conn) < 1)) {
                $status = "No rows inserted";
            }
        }
        $insertID = mysqli_insert_id($conn);

    } else {

        $sql = str_replace("\"", "'", $sql);
        $result = pg_query($conn, $sql);
        $queryTime += microtime(true) - $thisTime;

        //result returns 1 here on success,
        if ($result && pg_affected_rows($result) > 0) {
            $status= 0;
        }   else {
            $status = pg_last_error();
            $status = SYSTEM_ERROR.$status;

            // it is possible to fail because of a mysql error OR we still want an error return if no rows were
            // inserted.
            if (!$status && (pg_affected_rows($conn) < 1)) {
                $status = "No rows inserted";
            }
        }
        $insertID = $result; // ZZ - should be an oid/ insert id????

    } // end if-else pgsql or mysql

    Debug(8, "DB_Insert", "insert was $sql -- result($result), status($status), ($insertID)");

    return ($status);

} # end function replace



// function DB_Transaction - control transaction status
// Accepts command of "START", "COMMIT", or "ROLLBACK"
//   if a transaction is already active and we get another
//   START it will/should log a warning, give an error
// if it works it returns(0), msg otherwise
$CurrentTransaction = 0;

function DB_Transaction($conn, $command) {

    global $useDB;
    global $CurrentTransaction;

    switch ($command) {

        case 'START':

            if ($msg = DB_Query($conn, "START TRANSACTION", $results)) {
                return("DB_Transaction: can't START transaction ($msg)");
            }

            if ($CurrentTransaction == 1) {
                echo "<br><b>DB_Transaction: in progress and got START, committed</b>";
            }

            $CurrentTransaction = 1;

            break;


        case 'COMMIT':

            if ($msg = DB_Query($conn, "COMMIT", $results)) {
                return("DB_Transaction: can't COMMIT transaction ($msg)");
            }

            if (! $CurrentTransaction) {
                echo "<br><b>DB_Transaction: NOT in progress and got COMMIT</b>";
            }
            $CurrentTransaction = 0;
            break;


        case 'ROLLBACK':

            if ($msg = DB_Query($conn, "ROLLBACK", $results)) {
                return("DB_Transaction: can't ROLLBACK transaction ($msg)");
            }

            if (! $CurrentTransaction) {
                echo "<br><b>DB_Transaction: NOT in progress and got ROLLBACK</b>";
            }
            $CurrentTransaction = 0;
            break;


        default:

            return("DB_Transaction: undefined command");

    } // end switch

    return(0);

} // end DB_Transaction


# Truncate All Tables - Remove all rows from one table in all DBs
# Pass in an array of db names and array of table names to truncate
# If single db or table name is passed convert it to an array
# Example Query: "TRUNCATE $db.$table";
function DB_TruncateTables($dbArray, $tableName, $conn = 0){
    Global $Conn;  // only used if they didn't specify.

    if (!$conn) {
        $conn = $Conn;
    }

    if(!is_array($dbArray)){
        $dbArray = array($dbArray);
    }

    if(!is_array($tableName)){
        $tableName = array($tableName);
    }

    $results = "";

    foreach($dbArray as $db){
        $query1 = "USE $db";
        if($msg = DB_Query ($conn, $query1, $results)){
            $status = "Failed to use $db ($msg)";
        } else {
            $status = 0;
        }

        $results = "";
        foreach($tableName as $table){
            $results = "";

            if($msg = DB_Query ($conn, $query, $results)){
                $status = "Failed to truncate $table for $db ($msg)";
            }else {
                $status = 0;
            }
        }

    }
    return ($status);
}  // end function DB_TruncateTables


# DB_Update: updates mySQL table.  finds row matching $searchvals, updates with $newvals
# parameters: $conn, $table, $searchvals, $newvals
# returns: 0 if successful, 1 if failed, -1 if searchvals not found in table
#
# check first to make sure there is 1 match, do not want to update multiple
function DB_Update ($conn, $table,$searchvals,$newvals) {

    global $queryTime;
    global $useDB;
    setDB();

    // errorLog("UPDATE: ".print_r($newvals, true));

    // we remove the old sanity check
    $order_by ="";

    // build the query string, have 2 strings to build, selectstring, and then the
    // values to match

    $fieldstr = "";
    $newstr = "";
    $search_keycount = count($searchvals);
    $new_keycount= count($newvals);

    foreach ($searchvals as $key => $val) {
        // this string will be comma seperated if we are passed multiple fields
        $val = trim($val);  // strip leading/trailing blanks
        $fieldstr =  $fieldstr . $key . "=\"$val\"" . " and ";
    } //end while

    foreach ($newvals as $key => $val) {
        if (is_array($val)) {
            if (strstr($val[0], "ENUM") || strstr($val[0], "SET")) { // enumerated value
                // create list
                array_shift($val);
                $list = implode(",", $val);
                $newstr .= $key . "=\"$list\"". ", ";

            } else {

                DB_Log("ERROR",__LINE__." Unexpected field type ($val[0]) for key ($key)");
                return(SYSTEM_ERROR."Unexpected field type ($val[0]) for key ($key)");

            }
        } else { // old way

            $val = mysqli_real_escape_string($conn,$val);

            $val = trim($val);  // strip leading/trailing blanks
            if ($val =="") {
                // edited to allow blanks to be inserted.
                $val = "";
            }
            $newstr =  $newstr . $key . "=\"$val\"" . ", ";
        } //end if-else for array

    } // end while

    // we have to chop off last 2 chars off the string.  PHP chop only eliminates
    // whitespace, we have to get rid of ", "

    $newstr = substr ($newstr,0,strlen($newstr) -2);
    $fieldstr = substr ($fieldstr,0,strlen($fieldstr) -4);

    $sql = "update $table set $newstr where $fieldstr";
    DB_Log("UPDATE", $sql);
    Debug(2, "DB_Update", "query is:  $sql");

    $thisTime = microtime(true);
    $status = 0;

    if ($useDB == "mysql") {
        $result = mysqli_query($conn, $sql);
        if ($status = mysqli_error($conn)) { // return right now
            return(SYSTEM_ERROR.$status);
        }

        if ($result ==1) {
            $status = 0;
        } else {
            $status = mysqli_error($conn);
        }

    } else {
        $sql = str_replace("\"", "'", $sql);
        $result = pg_query($conn, $sql);
        if (!$result) {
            $status = pg_last_error();
            return(SYSTEM_ERROR.$status.$sql);
        }
    }
    //  $result = mysqli_query($conn, $sql);

    $queryTime += microtime(true) - $thisTime;

    // Increment query count
    qCount('UPDATE');


    return ($status);

} // end function DB_Update


/* Function qCount()
 *
 * Increments a global query counter.
 * Argument : Uppercase string of query type, ie qCount('SELECT');
 */
function qCount($type) {

    global $QUERY_COUNT;
    if (isset($QUERY_COUNT[$type])) $QUERY_COUNT[$type]++;
    else $QUERY_COUNT[$type] = 1;

    $QUERY_COUNT['TOTAL']++;
}

?>
