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
include_once("common.inc");
include_once("database.inc");

$queryTime = 0;

// get page name right away
include_once("config.inc");

global $PageInformation;

$tmp =  explode("/",$_SERVER['PHP_SELF']);
$Page = $tmp[(count($tmp)-1)];

//if ($PageInformation[$Page]['db'])
  session_set_save_handler("sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_gc");

// Function to see whether or not form data is saved
function isFormData() {

  global $Page;

  if (!isset($_SESSION['form'])) return false;

  // are we on the same form?
  if (isset($_SESSION['formRef']) &&
      $_SESSION['formRef'] == $Page)
    return true;

  return false;
}

// Function to clear session var holding param data
function clearFormData() {
  unset($_SESSION['form']);
  unset($_SESSION['formTS']);
  unset($_SESSION['formRef']);
}

// Function to merge form data into an array passed from db
function mergeFormData(&$result = array()) {
  if (!isset($_SESSION['form']))
    return "Form data does not exist.".SYSTEM_ERROR."Make sure you call isFormData() " .
      "before calling mergeFormData()";

  // allow for a direct associative array, or an array of arrays
  $zeroIndex = (isset($result[0]) && is_array($result[0]));

  // overwrite any existing fields/add new fields from saved form data
  foreach ($_SESSION['form'] as $field => $value) {
    if ($zeroIndex) $result[0][$field] = $value;
    else $result[$field] = $value;
  }
}

// Function to save form data into SESSION var
function saveFormData() {
  // clear any existing data
  clearFormData();

  $_SESSION['form'] = array();

  // get params
  foreach ($_REQUEST as $name => $value)
    // but use script_param() to clean them
    $_SESSION['form'][$name] = script_param($name);

  // timestamp the data
  $_SESSION['formTS'] = time();

  // remember which form we're on
  $tmp =  split("/", preg_replace("/(.*)\?.*/", '${1}', $_SERVER['HTTP_REFERER']));
  $_SESSION['formRef'] = $tmp[(count($tmp)-1)];
}

function sess_open($sess_path, $sess_name) {
    Debug(0x10, 'sess_open', "sess_path($sess_path), sess_name($sess_name)");
    return true;
}

function sess_close() {
    Debug(0x10, 'sess_close', "session_id:".session_id());
    return true;
}

function sess_read($sess_id) {

  global $Conn;

  Debug(0x10, 'sess_read', "session_id($sess_id)");
 $search = array('SessionID' => $sess_id,
                  'Data' => '');
  if ($msg = DB_Get($Conn, "Sessions", $search, $results)) {
    DB_Log('ERROR', "Unable to READ session data ($msg)");
    return '';
  }

  $CurrentTime = time();

  if (!count($results)) {
    $insert = array('SessionID' => $sess_id,
                    'DateTouched' => $CurrentTime);
    if ($msg = DB_Insert($Conn, "Sessions", $insert)) {
    DB_Log('ERROR', "Unable to INSERT session data ($msg)");
      return '';
    }
  } else {
    extract($results[0], EXTR_PREFIX_ALL, 'sess');
    
    $search = array('SessionID' => $sess_id);
    $update = array('DateTouched' => $CurrentTime);
    if ($msg = DB_Update($Conn, "Sessions", $search, $update))
      DB_Log('ERROR', "Unable to UPDATE session data ($msg)");
    // we're escaping in sess_Write, so strip slashes here
    return stripslashes($sess_Data);
  }
}

function sess_write($sess_id, $sess_Data) {

  global $Conn;

  if (!$Conn) return false;
    
  $CurrentTime = time();
  
  // not using script_param, so be sure to escape vals
  $sess_Data = mysql_real_escape_string($sess_Data);

  $sql = "UPDATE Sessions SET Data = '$sess_Data', DateTouched = $CurrentTime WHERE SessionID = '$sess_id'";
  if ($msg = DB_Query($Conn, $sql, $results)) {
    DB_Log('ERROR', "Unable to WRITE session data ($msg)");
    return false;
  }

  return true;
}

function sess_destroy($sess_id) {

    global $Conn;
  Debug(0x10, 'sess_destroy', "session_id($sess_id)");

    $search = array('SessionID' => $sess_id);
    if ($msg = DB_Delete($Conn, "Sessions", $search))
     DB_Log('ERROR', "Unable to DELETE session data ($msg)");

    return true;
}

function sess_gc($sess_maxlifetime) {

    global $Conn;

    $CurrentTime = time();

    $sql = "DELETE FROM Sessions WHERE (DateTouched + $sess_maxlifetime) < $CurrentTime;";
    if ($msg = DB_Query($Conn, $sql, $results)) {
     DB_Log('ERROR', "Unable to PURGE OLD session data ($msg)");
      return false;
    }

    return true;
}

?>
