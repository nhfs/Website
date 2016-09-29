<?php
//version5101//

//this is 'raocu_start.php'
//control is passed here by a link in the organisation website
//we could also get here via the 'Back' button from the update page

//it always displays the login page

	$sid = session_id();
	if ($sid == "")
	{
		session_name('SUMACSESS');
		session_start();
	}

	sumac_session_clean1();
	session_regenerate_id(false);

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_session_utilities.php';

	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();
	$_SESSION[SUMAC_SESSION_RETURN] = $_SERVER['HTTP_REFERER'];
	$self = $_SERVER['PHP_SELF'];
	$_SESSION[SUMAC_SESSION_FOLDER] = substr($self,0,strrpos($self,'/'));
//echo 'folder ' . $_SESSION[SUMAC_SESSION_FOLDER] . '<br />';

include_once 'sumac_set_parameters.php';
	$_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] = SUMAC_PACKAGE_CONTACT_UPDATE;
	if (sumac_set_parameters(SUMAC_PACKAGE_CONTACT_UPDATE) === false) return;
	sumac_use_single_package_parameter_defaults();

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];
include_once 'sumac_http.php';

	$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_ORGANISATION . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$organisationData = sumac_getXMLData($source,$port, '?' . $variableValues);

	$_SESSION[SUMAC_SESSION_ORGANISATION] = $organisationData;
	if ($organisationData == false)
	{
		echo sumac_getAbortHTML();
		sumac_destroy_session('');
		return;
	}

	sumac_echoRawXMLIfDebugging($organisationData,SUMAC_REQUEST_PARAM_ORGANISATION);

include_once 'sumac_user_login.php';
	if ($_SESSION[SUMAC_SESSION_USE_PASSWORDS])
	{
		if (sumac_execLogin('','sumac_identify_user.php',true,false) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else
	{
		if (sumac_execLogin('','sumac_identify_user.php',false,false) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	return;

function sumac_session_clean1($prefix='sumac_')
{
	$prefixlen = strlen($prefix);
	$v = array();
	foreach($_SESSION as $x => $y)
		if(substr($x,0,$prefixlen) == $prefix) $v[] = $x;

	foreach($v as $x) unset($_SESSION[$x]);
	return;
}

?>
