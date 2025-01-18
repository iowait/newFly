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
// COMMON UTILITY FUNCTIONS, NOT DEPENDENT ON OTHERS

// add an item to a SET member, e.g. comma sep list, given the name of the variable
// holding the set
function addToSet($name, $value) {
    global $$name;
    if (empty($$name)) {
        $$name = $value;
    } else { // add it, dups are ok in a SET
        $$name .= ",$value";
    }
} // end addToSet

// check that initials match a given first and last name
function checkInitials($first, $last, $initials) {
    $fnChars = preg_split('//', $first);
    $lnChars = preg_split('//', $last);
    $inits = preg_split('//', $initials);

    // initials must be exactly 2 characters, but split function adds null
    // chars at first and last index
    if (count($inits) != 4) {
        return "You must provide exactly two initials (first and last name).";
    }

    // initials must match (case insensitive)
    if (strcasecmp($fnChars[1], $inits[1]) ||
        strcasecmp($lnChars[1], $inits[2])) {
        return "Your initials don't match the name you provided.";
    }
} // end checkInitials


// Check input against defined rules
function CheckInvalidInput ($input, $rule = "USERNAME", $required = true) {

    // Nothing given, nothing required
    if (!($input || $required)) return;

    $types = array('lowercase letters' => 'a-z',
                   'uppercase letters' => 'A-Z',
                   'numbers' => '0-9',
                   'spaces' => '\s',
                   'punctuation marks' => '\,\.\?!',
                   'hyphens' => '\-\_'
    );

    if ($rule == "EMAIL") { // NOTE QUICK RETURN
        if (!preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $input)) {
            return("Invalid format of E-Mail address ($input)");
        }
        return(0);
    }


    $rules = array(
        'USERNAME' => array('lowercase letters', 'uppercase letters', 'numbers'),
        'TEXT' => array('lowercase letters', 'uppercase letters', 'numbers',
                        'spaces', 'punctuation marks'),
        'TAG' => array('lowercase letters', 'uppercase letters', 'numbers', 'hyphens')
    );

    if (!array_key_exists($rule, $rules))
        return SYSTEM_ERROR . "Invalid rule";

    // build regular expression
    $regEx = '/^[';
    foreach ($rules[$rule] as $type)
    $regEx .= $types[$type];
    $regEx .= ']+$/';

    if (!preg_match($regEx, $input)) {
        return "Invalid input entered.  The following types are allowed: " .
               implode(', ', $rules[$rule]) . ".";
    }

    return(0);
} // end checkValidInput


# compareConfigDB: This function compares an array of values, with the corresponding
# enum/set type in the DB
# INPUTS: stringList -- an array of strings
#         tableName  -- the table to check
#         colName    -- the column
# RETURNS: 0 if okay, message otherwise
function compareConfigDB($stringList, $tableName, $colName) {

    global $Conn;

    if (!$stringList || !$tableName || !$colName) {
        return("compareConfigDB, missing req values");
    }

    $sql = "SELECT COLUMN_TYPE FROM information_schema.COLUMNS 
              WHERE TABLE_SCHEMA='".FAC_DB."' AND 
                    TABLE_NAME='$tableName' AND COLUMN_NAME='$colName'";
    
    if ($msg = DB_Query($Conn, $sql, $results)) {
        return("compareConfigDB($stringList, $tableName, $colName) failed ($msg)");
    }

    # response in following form
    # enum('Regular','Special Tow Pilot','Social','Junior','Distant','Emeritur','Other','Inactive','None','Pending Delete')
    # set('L13AC','L13AC_back','ASK21','ASK21_back','Grobe102','SGS_1-26','SGS_2-33','SGS_2-33_Back','Citabria','Pawnee','Ground_launch') 
    $colType = $results[0]['COLUMN_TYPE']; 

    # we strip the enum/set,parens, just want the list of column values, convert to array
    if (!preg_match('/^set|^enum/', $colType)) { # don't support it for now
        return("compareConfigDB: got an unsupported column type for table $tableName, col $colName");
    }
    $colStrings = preg_replace('/^set|^enum|\(|\)|\'/', '', $colType);
    $colVals = explode(',', $colStrings);

    # convert the list they gave us to an array
    $stringVals = Array();
    foreach ($stringList as $val) {
        array_push($stringVals, $val);
    }

    // DB_Log('DEBUG',"colVals: ". print_r($colVals, true) ."\n stringVals: ". print_r($stringVals, true));

    # now we compare the two, each direction
    $diff = array_diff($stringVals, $colVals);
    $msg = '';
    if ($diff) {
        $msg = "compareConfigDB: values missing from DB: ". print_r($diff, true);
    }

    $diff = array_diff($colVals, $stringVals);
    if ($diff) {
        $msg .= "compareConfigDB: extra DB values: ". print_r($diff, true);
    }

    return($msg);

} // end compareConfigDB

// This function confirms that a potential password is okay to use
// Returns 0 on success, message otherwise
// CURRENTLY: One Cap letter, one small, one number, 8 char min.
$PasswordRule = 'Password must have at least one capital letter, one lower case, a digit, and be at least 8 characters long.';
function checkPassword($pass) {
    global $PasswordRule;

    if (strlen($pass) >= 8 &&
        preg_match('/[a-z]/', $pass) &&
        preg_match('/[0-9]/', $pass) &&
        preg_match('/[A-Z]/', $pass)) {
        return(0);
        
    } else {
        return($PasswordRule);
    }
}



# CreatePassword: This function generates a random password
# INPUTS: none
# OUTPUTS: the new password
# RETURNS: 0 if okay, mesage otherwise
function CreatePassword(&$password) {

    $password = "";
    $vowel_array = array('a','e','i','o','u');
    $consonants_array = array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','y','z');
    srand ((double)microtime()*1000000);
    shuffle ($consonants_array);
    shuffle ($vowel_array);
    for ($i=0; $i<2; $i++) {
        $password .= $consonants_array[$i];
        $password .= $vowel_array[$i];
        $password .= $consonants_array[$i+1];
    }
    return(0);


} // end function CreatePassword


# DisplayMessage: This function displays an informational message to
# the user. This routine is for errors that may occur as part of normal
# processing due to bad user input.
# INPUTS: context - what we were trying to do (user understands), message - what failed
# OUTPUTS: special page
# RETURNS: none
function DisplayMessage($context, $msg, $rollback=0) {
    global $Conn, $subID, $Template, $smarty;


    // do we need to issue a rollback
    if ($rollback) {
        if ($msg2 = DB_Transaction($Conn, 'ROLLBACK')) {
            $msg .= $msg2;
        }
    }

    echo "<br><br><b>"."Problem encountered while attempting to".": </b>".$context."<br>$msg";

    // if the  $msg contains  '@@@', we don't let them see anything after the @@@, it is logged
    // instead -- if staff login, we see it
    // the inclusion of @@@ normally is for things that should NOT be possible and indicate
    // a problem
    $showError = 0;
    // $showError = 1;  echo "ALWAYS SHOWING HIDDEN<br>";

    if ($subID && $subID < 500) {
        $showError = 1;
    }
    // ZZ
    $showError =0;
    $txt = $msg; // what the user will see

    if (strstr($msg, SYSTEM_ERROR)) { // something bad happened

        // if not staff, don't show whole message
        if (!$showError) {
            $txt = preg_replace('/@@@.*$/', "", $msg);
        }

        // mail(ADMIN_EMAIL, "ExtSQL Error","Alert - Serious error:\n$context\n$msg","From:".SERVICE_EMAIL) ;
        DB_Log('USER_MSG', "$context ($msg)");

    } else { // regular error
        DB_Log('USER_MSG', "$context ($msg)");
    }

    if ($showError) {
        echo "<b>"."Details".": </b>".$txt;
    }

    $_SESSION['SubmitError'] = true;

    require('footer.inc');
    exit(0);

} // end DisplayMessage

// put up a new page
// This ONLY works if it is the FIRST html out
function DisplayPage($newPage) {

    header("Location: /".FLY_DIR."/$newPage");
}


// write some output
function errorLog($msg) {
    $theTime = strftime("%c");
    $page = $_SERVER['SCRIPT_NAME'];
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'local script';
    error_log("$theTime ($page): $msg  from: $ip\n", 3, "./debug.log");

}


# This function prepares data for output display, on input we do not
# convert cr/lf into <br> or other HTML codes.  We store as is and then
# convert on output.
# here are the rules,
# we trim any leading/trailing spaces
#  \r\n or \n or \r is converted to a <br>
# more than one blank space is converted to the same number of &nbsp;
# call stripcslashes to take care of anything that might have been added;
function formatOutput($textIn) {

    $textOut = trim($textIn);
    $textOut = ereg_replace("(\r\n|\n|\r)", "<br>", $textOut);
    $textOut = str_replace("  ", '&nbsp;&nbsp;', $textOut);
    $textOut = stripcslashes($textOut);

    return($textOut);
}

# Special function needed to specify read/write mode.
# On input, had problem with ereg_replace("(\r\n|\n|\r)",
# had to escape \r\n|\n\r in order for it to be recogniz ed
function formatOutput2($textIn, $mode) {

    $textOut = trim($textIn);
    $textOut = stripcslashes($textOut);

    if ($mode == "read") {
        $textOut = ereg_replace("(\r\n|\n|\r)", "<br>", $textOut);
    }
    $textOut = str_replace("  ", '&nbsp;&nbsp;', $textOut);

    return($textOut);
}


# getSubID: retrieves the user id from the Subscriber table by looking at the
# SUB_NAME column.  In this system by default the SUB_NAME column is set to the
# same value as the SUB_ADDR.  There can be duplicates of SUB_ADDR, but not of
# SUB_NAME
function getSubID($emailAddress,&$result) {
    global $Conn;

    $result = 0;

    $srchInfo['SUB_ADDR'] = $emailAddress;
    $srchInfo['subID'] = "";

    // check for
    if ( $msg = DB_Get($Conn,"Customer",$srchInfo,$results ) ) {
        return $msg;
    }

    $numResults = count($results);

    if( $numResults > 1 ) {
        return SYSTEM_ERROR."Email address has more then one subID"." $emailAddres";
    }

    if( $numResults < 1 ) {
        return "Email address not found";
    }

    $result = $results[0]['subID'];
}


# function Hide:  used to mask error text we don't' want the user to see
# inserts the SYSTEM_ERROR in front of the text for screening by
# DisplayMessage
function Hide($msg) {

    return(SYSTEM_ERROR."($msg)");

}

#
# GetControl: This function recalls the contents of the control table
# INPUTS: none
# OUTPUT: values in control table
# RETURNS: 0 if okay, otherwise message
function GetControl(&$values) {
    Global $ControlColInfo, $Conn;

    # load the array
    reset($ControlColInfo);
    while (list($col, $elements) = each($ControlColInfo)) {
        $srchInfo[$col] = "";
    } // end while

    if ($msg = DB_Get($Conn, "Control", $srchInfo, $ans)) {
        DB_Log("ERROR",__LINE__." Unable to read control table ($msg)");
        return("Unable to read control table ($msg)");
    }

    if (!($count = count($ans))) {
        DB_Log("ERROR",__LINE__." No control table results");
        return("No control table results");
    }

    if ($count > 1) {
        DB_Log("ERROR",__LINE__." Too many control table results");
        return("Too many control table results");
    }

    $values = $ans[0];
    return(0);

} // end function GetControl

// function to return first name, last initial when given a subID
function GetName ($subID) {

    if ($subID == "") {
        $subID = 0;
    }

    global $Conn;

    $name = "";

    $sql = "SELECT FIRST_NAME, LAST_NAME FROM Customer WHERE subID='$subID'";
    if ($msg = DB_Query($Conn, $sql, $results)) {
        DisplayMessage("Failed to obtain name", $msg);
        exit(1);
    }
    if (count($results) > 0) {
        $name = $results[0]['FIRST_NAME']. " ".$results[0]['LAST_NAME'][0];
    }
    return($name);
}

# is Associative array
# given a variable name, is it a simple index array, or associative pairs
function isAssoc(array $arr)
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

# make a thumbnail
function makeThumb($src, $dest, $desired_width) {

    /* read the source image */
    $source_image = imagecreatefromjpeg($src);
    if (!$source_image) {  # we failed, dummy the thumb
        copy($src, $dest);
        return;
    }
    $width = imagesx($source_image);
    $height = imagesy($source_image);
    
    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_height = floor($height * ($desired_width / $width));
    
    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
    
    /* copy source image at a resized size */
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
    
    /* create the physical thumbnail image to its destination */
    imagejpeg($virtual_image, $dest);
} # end makeThumb

# optionally display extra text within a <div> using a title if
# it exceeds the specified limit, otherwise just output.
# OPtionally supply html tag name to be used for <start> ... </stop>
function printMouseOver($divID, $text, $limit, $tagName = '') {
    if (empty($text) || empty($limit) || empty($divID)) {
        print "<b>ERROR from printMouseOver</b>";
    }
    $len = strlen($text);
    echo $tagName ? "<$tagName>" : '';

    if ($len > $limit) {  # need the div
        echo "<div id='$divID' title='$text'> ". substr($text, 0,$limit-3) . " ...</div>";
    } else {
        echo $text;
    }

    echo $tagName ? "</$tagName>" : '';
 } // end function printMouseOver   

        


function ReplaceFields($inHtml, $mode, $values, $format, &$outHtml) {



    // substitutions are within __ __ bars
    // matches will hold names of what needs to be replaced
    preg_match_all( '/__[a-zA-Z]*__/', $inHtml, $matches);

    $sub=array(); // will hold replacement values in same order

    foreach ($matches[0] as $tag) {

        switch ($tag) {

            case "summary":
            case "name":

                if ($mode == "write") {
                    $sub[] = "<input type=\"text\" maxlength=\"70\" size=\"75\" name=\"$tag\" value=\"".formatOutput2($value[$tag], "write")."\">";
                } else {
                    $sub[] = formatOutput2($values[0][$tag], "read")."<input type=\"hidden\" name=\"$tag\" value=\"".formatOutput2($values[$tag], "read")."\">";
                }
                break;

            default:  // we got nothing
                $sub[] = "(($tag))";
        }

    } // end switch

    // we now have the matches and the new values, replace
    $outHtml = str_replace($matches[0], $sub, $inHtml);

} // end function ReplaceFields


// This function takes a parameter name and checks both GET
// and POST arrays to find the parameter value.
function script_param ($name) {

    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    unset ($val);
    if (isset ($_GET[$name])) {
        $val = $_GET[$name];
        $val = stripcslashes($val);

    } else if (isset ($_POST[$name])) {
        $val = $_POST[$name];

    } else if (isset ($HTTP_GET_VARS[$name])) {
        $val = $HTTP_GET_VARS[$name];
        $val = stripcslashes($val);

    } else if (isset ($HTTP_POST_VARS[$name])) {
        $val = $HTTP_POST_VARS[$name];
    }

    // clean the data, array or single value
    if (@is_array($val)) {

        // scan each value
        foreach ($val as $index => $value) {
            $value = @trim($value);
            $value = htmlspecialchars($value, ENT_QUOTES);
            $val[$index] = $value;
        }
        return($val);
    } else {

        $value = @trim($val);
        $value = htmlspecialchars($value, ENT_QUOTES);
        
        // return @$val rather than $val to prevent "undefined value"
        // messages in case $val is unset and warnings are enabled
        return ($value);
    }
}


/**
 * sendEmail sends a "real" e-mail based on the paramaters..
 *
 * @param to the e-mail address the mail is being sent to.
 *
 * @param from the address the message is from
 *
 * @param subject the subject of the sent message
 *
 * @param body the message body
 *
 * @return Error messages.
 */
function SendEmail( $to, $from, $subject, $body) {
    if (!mail( $to, $subject,$body,"From:".$from, "-f ".SERVICE_EMAIL) ) {
        DB_Log("ERROR",__LINE__." Failure to send email notification to $to");
        return( "Failed to send email notification to"." $to");
    }
}

// got this from php.net on fpassthru function
function send_file($path, $fileType) {

    if ($fileType != "text" && $fileType != "source" && $fileType != "binary") {
        return("Invalid file type");
    }

    if (!is_file($path) || connection_status()!=0)
        return(1);

    $downLoadRate = 200; // KBytes/sec

    $name=basename($path);
    $fileSize = filesize($path);

    ini_set('zlib.output_compression', 'Off');

    //filenames in IE containing dots will screw up the
    //filename unless we add this

    if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
        $name = preg_replace('/\./', '%2e', $name, substr_count($name, '.') - 1);


    header("Cache-Control: ");
    header("Pragma: ");

    if ($fileType == "binary" || $fileType == "source") {
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: binary\n");
    } else {
        header("Content-Type: text/plain");
        header("Content-Transfer-Encoding: 8bit\n");
    }

    header("Content-Length: $fileSize" );
    header('Content-Disposition: attachment; filename="'.$name.'"');

    // we try to determine if we have sent the whole file
    $readSize = 1024 * $downLoadRate * 1.3; // we adjust the download rate by 30% to get the actual rate.

    $numReads = round($fileSize/$readSize);

    if($file = fopen($path, 'rb')){
        $count = 1;

        while( (!feof($file)) && (connection_status()==0) ){

            print(fread($file, $readSize));
            flush();
            sleep(1);
            $count++;
        }
        fclose($file);
    }

    if (connection_status() == 0 && !connection_aborted() && $count >= $numReads) {
        return(0);
    } else {
        return(1);
    }

}  // end function - send_file

/* function sendJSON - supports the mobile app
   INPUT: $data - associative array of key->value pairs
   no return, but logs a failure
 */
function sendJSON($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    exit(0);

} // end sendJSON

/*
   function subAddrExists -- check to see if existing customer
   INPUT: $subAddr
   OUTPUT: NONE, return 0 if found match, 1 otherwise
 */
function subAddrExists($subAddr) {

    global $Conn;

    $query = "SELECT SUB_ADDR FROM Customer WHERE SUB_ADDR = '".$subAddr."'";

    if ($msg = DB_Query($Conn,$query,$results)) {
        print $msg;
    } else {
        if(!count($results)){
            return(1);
        } else {
            return(0);
        }
    }

}

function subNameExists($subName) {

    global $Conn;

    $query = "SELECT SUB_NAME FROM Customer WHERE SUB_NAME = '".$subName."'";

    if ($msg = DB_Query($Conn,$query,$results)) {
        print $msg;
    } else {
        if(!count($results)){
            return(1);
        } else {
            return(0);
        }
    }

}


?>
