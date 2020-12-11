<?php
require_once('functions.php');
//create session if one doesn't exist
getSession();
//empty session
$_SESSION = array();
//destroy session
session_destroy();
//close session
session_write_close();
//redirect to previous page
if (isset($_SERVER['HTTP_REFERER']))
{
	//check against admin pages for redirect
	$toRedirect = validate($_SERVER['HTTP_REFERER'], 'generic');
	redirect($toRedirect, adminPage($toRedirect));
}
else if (isset($_REQUEST['page']))
{
	//check against admin pages for redirect
	$toRedirect = validate($_REQUEST['page'], 'generic');
	redirect($toRedirect, adminPage($toRedirect));
}
else
{
	/*  if you came to logout.php specifically and not through any type of redirect
		clicking logout or being automatically logged out won't cause this to occur */
	redirect('index.php');
}
?>