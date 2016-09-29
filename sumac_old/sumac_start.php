<?php
//version510//

//this is 'sumac_start.php' - which was briefly 'sumac_resume_or_start.php' before v 5.1.0
//this is the only door to all sumac functionality
//control is passed here by a link in the organisation website
//we could also get here via the 'Back' button from the next page
//we do not bother checking who our referer is
//if one critical sumac $_SESSION variable is set, we assume all the rest will be - so we "resume"
//if that critical global variable is NOT set,
// OR if the "resume" parameter is set to false,
// we assume none of them are - so we "start"

	$sid = session_id();
	if ($sid == "")
	{
		session_name('SUMACSESS');
		session_start();
	}
	$resume = (isset($_GET['resume'])) ? substr(strtolower($_GET['resume']),0,1) : '';
	if (!isset($_SESSION['sumac_source']) || ($resume == 'f'))
	{
//in all other PHP files we use this test to make sure we have not been accidentally or wrongly invoked
//here it is our way of deciding between performing a "start" or a "resume" operation
		require 'sumac_start_new_session.php';
		return;
	}
	else
	{
include_once 'sumac_constants.php';
//reset the folder we are running from in case it has changed - but leave everything else
		$_SESSION[SUMAC_SESSION_RETURN] = $_SERVER['HTTP_REFERER'];
		$self = $_SERVER['PHP_SELF'];
		$_SESSION[SUMAC_SESSION_FOLDER] = substr($self,0,strrpos($self,'/'));
//use our standard redirection code to branch to the resumption point
		$sumac_resume = true; //to bypass referer chceks
		require 'sumac_redirect.php';
		return;
	}

?>