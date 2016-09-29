<?php
//version510//

//this is 'sumac_leave.php'
//this is the exit door that ends the sumac session and releases all variables
//control is passed here by the "Leave" link in the standard navigation bar

include_once 'sumac_constants.php';
include_once 'sumac_session_utilities.php';

	$sid = session_id();
	if ($sid == "")
	{
		session_name(SUMAC_SESSION_NAME);
		session_start();
	}

	if (isset($_GET['homephp'])) $goto = $_GET['homephp'];
	else $goto = $_SESSION[SUMAC_SESSION_RETURN];

	sumac_destroy_session('');

	echo sumac_getLeaveSumacHTML($goto);

	exit();
?>