<?
if( session_id() == '' )
{
    # don't mix sessions, will stay within the request URI
    # e.g.  REQUEST_URI = "/fly/login.php", strip to "/fly/"
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $path = preg_replace('/(\/.*\/).*$/', '${1}', $uri);
    session_set_cookie_params(0, $path);
    session_start();
}

// Debug stuff here, only to ADMIN users
if (!empty($_SESSION['perms']) && strstr($_SESSION['perms'], 'ADMIN')) {
    if (empty($_SESSION['DebugLevel'])) { // take a new value
        if (!empty($_GET['DebugLevel'])) {
            $_SESSION['DebugLevel'] = intval($_GET['DebugLevel'], 16);
            $DebugLevel = $_SESSION['DebugLevel'];
        } else {
            $_SESSION['DebugLevel'] = 0;
        }
    } else { // store a changed value of non zero
        if (!empty($_GET['DebugLevel'])) {
            $_SESSION['DebugLevel'] = intval($_GET['DebugLevel'], 16);
        }
        $DebugLevel = $_SESSION['DebugLevel'];
    }
}

// Reporting E_NOTICE can be good too (to report uninitialized
// variables or catch variable name misspellings ...)
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_include_path(".:./lib:../lib");
include_once("database.inc");
include_once("fly_funcs.inc");
include_once("hfl.inc");

?>