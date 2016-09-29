<?php
//version567//

//this is 'sumac_start_new_session.php' - which was simply 'sumac_start.php' before v 5.1.0
//this is the front door to all sumac functionality
//control is passed here from the new version of 'sumac_start.php' when there is no active session
//we do not bother checking who our referer is
//we begin by unsetting (deleting) all sumac-related global ($_SESSION) variables

	$sid = session_id();
	if ($sid == "")
	{
		session_name('SUMACSESS');
		session_start();
	}

	sumac_session_clean1();
//that disposes of any pre-existing sumac $_SESSION variables
	session_regenerate_id(false);

include_once 'sumac_constants.php';
	if (isset($forcedrestart)) sumac_load_strings();
include_once 'sumac_utilities.php';
include_once 'sumac_session_utilities.php';

//echo 'source ' . $_SESSION[SUMAC_SESSION_SOURCE] . '<br />';

	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();
	if (isset($_SERVER['HTTP_REFERER'])) $_SESSION[SUMAC_SESSION_RETURN] = $_SERVER['HTTP_REFERER'];
	else $_SESSION[SUMAC_SESSION_RETURN] = 'no-available-return-url';
	$self = $_SERVER['PHP_SELF'];
	$_SESSION[SUMAC_SESSION_FOLDER] = substr($self,0,strrpos($self,'/'));
//echo 'folder ' . $_SESSION[SUMAC_SESSION_FOLDER] . '<br />';

	$_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] = '';
	$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] = '';

//the initial active package can be specified as a parameter - with only one package it is not needed
	$package = (isset($_GET['package'])) ? strtolower($_GET['package']) : '';

//it can also be supplied by a forced restart
	if (($package == '') && isset($forcedrestart)) $package = $forcedrestart[SUMAC_SESSION_ACTIVE_PACKAGE];

include_once 'sumac_set_parameters.php';
	if (sumac_set_parameters($package) === false) return;

	if (isset($forcedrestart))
	{
		$_SESSION[SUMAC_SESSION_RETURN] = $forcedrestart[SUMAC_SESSION_RETURN];
		$_SESSION[SUMAC_USERPAR_SESSEXPIRY] = $forcedrestart[SUMAC_USERPAR_SESSEXPIRY];
	}

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];
include_once 'sumac_http.php';

//the normal entry-point for a package can be overridden using the 'entry' parameter
//at present, the only entry-points that can be specified are
//	'login'
//	'register'
//	'finhistory'
//	'eduhistory'
//	'formslist'
//	'paycourse'

	$entry = (isset($_GET['entry'])) ? strtolower($_GET['entry']) : '';
//it can also be supplied by a forced restart
	if (($entry == '') && isset($forcedrestart)) $entry = $forcedrestart['entry'];
//and if the request is for Courses, in some cases re-interpret the 'entry' setting as 'login' followed by some action
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		if (($entry == 'finhistory') ||
			($entry == 'eduhistory') ||
			($entry == 'formslist'))
		{
			$_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION] = 'personal';
			$_SESSION[SUMAC_SESSION_POST_LOGIN_DIV] =  $entry;
			$entry = SUMAC_FUNCTION_LOGIN;
		}
		else if ($entry == 'paycourse')
		{
			$_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION] = 'payment';
			$entry = SUMAC_FUNCTION_LOGIN;
		}
		// 'register' and 'login' need no interpretation
	}

//now request theatre in organisation? or courses? or ...
	$include = '';
	if (!$_SESSION[SUMAC_SESSION_EXCLUDE_THEATRE])	//include theatre unless explicitly excluded
	{
		if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) unset($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
		$_SESSION[SUMAC_SESSION_THEATRE_NAME] = 'main';
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_THEATRE;
	}
	if (!$_SESSION[SUMAC_SESSION_EXCLUDE_COURSECATALOG])	//include course catalog unless explicitly excluded
	{
		if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])) unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_COURSECATALOG;
	}
	if (!$_SESSION[SUMAC_SESSION_EXCLUDE_DIRECTORIES])	//include directories unless explicitly excluded
	{
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_DIRECTORIES;
	}
	if (!$_SESSION[SUMAC_SESSION_EXCLUDE_GRANTFORMS])	//include grant application forms unless explicitly excluded
	{
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_GRANTFORMS;
	}
	if ($entry == SUMAC_FUNCTION_REGISTER)
	{
		$coursename = (isset($_GET['coursename'])) ? $_GET['coursename'] : '';
		$coursedate = (isset($_GET['coursedate'])) ? $_GET['coursedate'] : '';
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_SELECTEDSESSION .
					'&' . SUMAC_REQUEST_KEYWORD_COURSESESSIONNAME . '=' . urlencode($coursename) .
					'&' . SUMAC_REQUEST_KEYWORD_COURSESESSIONDATE . '=' . urlencode($coursedate);
	}
	if ($package == SUMAC_PACKAGE_SINGLEFORM2)
	{
		$formname = (isset($_GET['formname'])) ? $_GET['formname'] : '';
		$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_SINGLEFORM .
					'&' . SUMAC_REQUEST_KEYWORD_FORMNAME . '=' . urlencode($formname);
	}

	$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_ORGANISATION .
							$include . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];

	$organisationData = sumac_getXMLData($source,$port, '?' . $variableValues);
	$_SESSION[SUMAC_SESSION_ORGANISATION] = $organisationData;
	if ($organisationData == false)
	{
		echo sumac_getAbortHTML();
		sumac_destroy_session('');
		return;
	}

	sumac_echoRawXMLIfDebugging($organisationData,SUMAC_REQUEST_PARAM_ORGANISATION);

//most packages allow new users, but contact update does not, nor does grant review (reviewer)
//	$allownewuser = true;	//by default we allow them
//	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE) $allownewuser = false;

	$message = (isset($forcedrestart)) ? $forcedrestart['message'] : '';
	$request = (isset($forcedrestart)) ? 'forcedrestart' : '';

	if (($entry == SUMAC_FUNCTION_LOGIN)
		|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
		|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP)
		|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP2)
		|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G))
	{
//include_once 'sumac_user_login.php';
//		if (sumac_execLogin($message,'sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],$allownewuser) == false)
include_once 'sumac_login2.php';
		if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],'L/CR',$request,$message,'sumac_identify_user.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
			|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2R))
	{
include_once 'sumac_login2.php';
		if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],'L',$request,$message,'sumac_identify_user.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($entry == SUMAC_FUNCTION_REGISTER)
	{
include_once 'sumac_select_course.php';
		$_SESSION[SUMAC_SESSION_COURSES_NO_CATALOG] = true;
		if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
include_once 'sumac_select_event.php';
		if (sumac_execSelectEvent('sumac_event_chosen.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING2)
	{
include_once 'sumac_ticketing2.php';
		if (sumac_ticketing2() == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
include_once 'sumac_select_course.php';
		$_SESSION[SUMAC_SESSION_COURSES_NO_CATALOG] = false;
		if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DIRECTORIES)
	{
include_once 'sumac_select_directory.php';
		if (sumac_execSelectDirectory('sumac_directory_chosen.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION2)
	{
include_once 'sumac_donation2.php';
		if (sumac_donation2($request,null,$message,'sumac_identify_user.php',null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SINGLEFORM2)
	{
include_once 'sumac_singleform2.php';
		if (sumac_singleform2($request,$message,null,null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SIGNUP2)
	{
include_once 'sumac_login2.php';
		if (sumac_login2('signup2','C',$request,$message,'sumac_identify_user.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}

	else 	//should never happen
	{
		sumac_destroy_session('unknown entry ' . $entry . ' or package ' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . ' for sumac_start_new_session');
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