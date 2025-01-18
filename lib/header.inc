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
$scriptName = basename( $_SERVER['SCRIPT_NAME']);

// if we get the keepLogin COOKIE and it matches what was stored, they are good to go!
if (isset($_COOKIE['keepLogin']) && $scriptName != 'account.php') {
    $gotCookie = $_COOKIE['keepLogin'];
    @list($id, $rest) = preg_split('/\-/', $gotCookie);
    if (!is_numeric($id)) { // bad!
        DB_Log("ERROR", "keepLogin: Got fake/bad cookie ($gotCookie)");
        DisplayMessage("Display a page", "Login required first($id/$gotCookie).");
    }

    // get the DB value and what we need to establish a login
    $srchInfo['MemberID'] = $id;
    $srchInfo['keepLogin'] = '';
    $srchInfo['lastName'] = '';
    $srchInfo['emailAddr'] = '';
    
    if ($msg = DB_Get($Conn, "Members", $srchInfo, $results)) { // no match
        DB_Log("WARN", "keepLogin: No match this cookie ($gotCookie), $msg");
        DisplayMessage("Display a site page", "Login required first.");
    }

    unset($srchInfo);
    
    if ($gotCookie == $results[0]['keepLogin']) {
        Debug(0x40, 'keepLogin',  "Good cookie for $id, got $gotCookie");

        // get permissions
        if ($msg = getPerms($id, $perms)) {
            return("Permission check failed: $msg");
        }
        $_SESSION['perms'] = $perms;
        $_SESSION['SUB_NAME'] = preg_replace('/ /', '', $results[0]['lastName']);
        $_SESSION['SUB_ADDR'] = $results[0]['emailAddr'];
        $_SESSION['subID'] = $id;

        if ($msg = updateLastActive($id, "login with cookie")) {
           DB_Log("ERROR", "keepLogin: Update last active failed with $msg");
           DisplayMessage("Display a page", "Login required first.");
        }
      
    } else {
        DB_Log("WARN", "keepLogin: Bad cookie match $id, got $gotCookie / db had ".$results[0]['keepLogin']);
    } // end if-else, bad cookie
} // end if - got COOKIE

if (isset($PageInformation[$scriptName]['protected']) && $PageInformation[$scriptName]['protected'] == 'Yes' &&
    !isset($_SESSION['SUB_ADDR']) ) {
    if (preg_match('/(event_sched.php|flying_sched.php)/', $scriptName)) {
        // no action
    } else {
        DisplayMessage("Display a page", "<a href='login.php'>Login required.</a>");
    }

} else {
    if ($scriptName == 'account.php' && !isset($_COOKIE['keepLogin']) ) { // they just logged in
        if ($_SESSION['keepLogin']) {
            
            // create cookie and store value in db
            $cookie = $_SESSION['subID']."-".md5($_SERVER['REMOTE_ADDR'].time());
            // send cookie - valid for 30 days
            if (!setcookie("keepLogin", $cookie, time()+7776000, "/", "fly.org")) { // FAILED
                DB_Log('ERROR', "keepLogin: Couldn't set cookie to value($cookie)" );
            } else { 
                // store in DB
                $srchInfo['MemberID'] = $_SESSION['subID'];
                $updateInfo['keepLogin'] = $cookie;
                if( $msg = DB_Update($Conn, "Members", $srchInfo, $updateInfo) ) {
                    DB_Log('ERROR', "Couldn't save cookie($cookie) in DB" ); 
                }
                Debug(0x40, 'keepLogin', "Stored $cookie");
            }
            
        } else { // clear any cookie, db value
            
            setcookie("keepLogin", 'bingo-83472974', time()-3600, "/", "fly.org");
            $srchInfo['MemberID'] = $_SESSION['subID'];
            $updateInfo['keepLogin'] = '';
            if( $msg = DB_Update($Conn, "Members", $srchInfo, $updateInfo) ) {
                DB_Log('ERROR', "Couldn't clear cookie($cookie) in DB" ); 
            }
            Debug(0x40, 'keepLogin', "Cleared keepLogin for ". $_SESSION['subID']);

        } // end if -else keepLogin
        unset($srchInfo);
    } // end if - account.php (Control Panel)
}


$super = 0;
if (hasPerms('edit_members') && isset($_SESSION['SUB_ADDR'])) {
    $super = 1;
};

$title = CLUB_NAME;
if (isset($PageInformation[$scriptName]['title'])) {
    $title .= "/".$PageInformation[$scriptName]['title'];
}

if (isset($PageInformation[$scriptName]['cache-control'])) {
    $action = script_param('action');
    if ($action == 'search') { // keep the values
        header($PageInformation[$scriptName]['cache-control']);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html  xml:lang="en-US" lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta content="text/javascript" http-equiv="Content-Script-Type" />
        <meta content="text/css" http-equiv="Content-Style-Type" />
        <title>
	    <?=$title?>
        </title>
         
        <link rel="stylesheet" href="/<?=FLY_DIR?>/css/jquery-ui.min.css" />
        <script src="/<?=FLY_DIR?>/js/jquery.js" type="text/javascript"></script>
        <script src="/<?=FLY_DIR?>/js/jquery-ui.min.js" type="text/javascript"></script>
        <link href="/<?=FLY_DIR?>/css/fly.css" type="text/css" rel="stylesheet"/>
        
        <!-- BELOW JUST FOR TIMEPICKER https://fgelinas.com/code/timepicker/ -->
        <link rel="stylesheet" href="/<?=FLY_DIR?>/js/timepicker/include/ui-1.10.0/ui-lightness/jquery-ui-1.10.0.custom.min.css" type="text/css" />
        <link rel="stylesheet" href="/<?=FLY_DIR?>/js/timepicker/jquery.ui.timepicker.css?v=0.3.3" type="text/css" />
        <script type="text/javascript" src="/<?=FLY_DIR?>/js/timepicker/jquery.ui.timepicker.js?v=0.3.3"></script>
        
    </head>
    <body id="Body">
        
        <script type="text/javascript">
         function checkSubmit(label, more) {
             var extra = '';
             if (label.toUpperCase() == "DELETE") {
                 extra = '\nA DELETE action will permanently remove the item.\n\n\n -->> Your activity is logged, please be absolutely sure of this action!!! <<--';
             }
             if (!confirm('Pending Action is: ' + label + '\n\nAre you sure you wish to proceed with this action?\n' +
                          extra + '\n' + more)) {
                 // event.preventDefault();
                 return(false);
             }
             return(true);
         }

        </script>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="2" style='text-align:center'>
		    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                     <tr>
                       <td style='width:25%'>
		         <a href="/" border='1'><img src="/<?=FLY_DIR?>/images/ask21_grass.jpg" height='150'></a>
		       </td>
		       
		       <td style='width:50%'>		
                       <h1><?=$title?></h1> <p> 
		           <a href='<?=MAIN_SITE?>'>Main Site</a>&nbsp;&nbsp;
                           <a href='/<?=FLY_DIR?>/aircraft.php'>Aircraft</a>&nbsp;&nbsp; 
                           <a href='/<?=FLY_DIR?>/event_sched.php'>Event Schedule</a>&nbsp;&nbsp; 
 	                <? if (SHOW_FLY_SCHEDULE) { ?>
                           <a href='/<?=FLY_DIR?>/flying_sched.php'>Flying Schedule</a>&nbsp;&nbsp;
			<? } ?>
                           <a href='/<?=FLY_DIR?>/about.php'>About</a>
               
		       </td>

                            <? if (!isset($_SESSION['subID'])) {
                                $memberID = '';
                                $MemberID = '';
                            ?>
		                <td style='width:25%;background:white'>
                                    <a id="dnn_dnnLOGIN_loginLink" title="Login" class="reg_login" rel="nofollow" href="/<?=FLY_DIR?>/login.php">Login</a>
                            <? } else {
                                $name = isset($_SESSION['SUB_ADDR']) ? $_SESSION['SUB_ADDR'] : ' ';
                                # mean to replace with MemberID as global usage
                                $memberID = $_SESSION['subID'];
                                $MemberID = $memberID;
                                $subName = $_SESSION['SUB_NAME'];
                            ?>
		                <td style='width:25%;background-color:lightgray'>
                               <b><?=$name?></b><br /> <a href="/<?=FLY_DIR?>/account.php">Control Panel</a><br /><br /> <a id="dnn_dnnLOGIN_loginLink" title="Login" class="reg_login" rel="nofollow" href="/<?=FLY_DIR?>/logout.php">Logout</a> &nbsp; &nbsp;
                            <? } ?>
			    </p>
		       </td>
                     </tr>
                   </table>
                </td>
            </tr>
            <tr>

                <td style='width:20%; vertical-align:top'>
                    <!-- LEFT CUSTOM NAVIGATION -->
		    <br /><br /><br /><br /><br /><br /><br /><br /><br />
                    <b>Convenience Links</b>
                    <br />
		    <UL>
		      <LI><a href='http://www.faa.gov/regulations_policies/handbooks_manuals/aircraft/glider_handbook/'>FAA Glider Flying Handbook</a></LI>
		      <LI><a href='http://www.soaringsafety.org/school/towpilot/tpctoc.htm'>Tow Pilot Course</a></LI>
                </td>


                <td valign="top">
                    <? // MAIN DATA AREA FOLLOWS - closed by footer.inc ?>

