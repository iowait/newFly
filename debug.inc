<?
  echo "<font size='-2'><Table border=1><TR>";
  echo "<TD><PRE><font size='-2'><B>DEBUG INFO</B><BR>SESSION: "; print_r($_SESSION);  echo "</font></PRE></TD>";
  echo "<TD><PRE><font size='-2'>POST_VARS: "; if (isset($_POST)) print_r($_POST); echo "</font></PRE></TD>"; 
   echo "<TD><PRE><font size='-2'>GET_VARS: "; if (isset($HTTP_GET_VARS)) print_r($HTTP_GET_VARS); echo "</font></PRE></TD>";
   echo "</TR></TABLE></font>";
?>

