<?php
//version567//

//this is 'sumac_redirect.php'
//control is passed here by a link in the control navbar
//we could also get here via the 'Back' button from the next page
//or we could have come here by way of 'sumac_start.php'

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';

	$sid = session_id();
	if ($sid == "")
	{
		session_name(SUMAC_SESSION_NAME);
		session_start();
	}

	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'displayerrors') !== false)
	{
		$new_level = error_reporting(-1);
		ini_set("display_errors",1);
	}

include_once 'sumac_session_utilities.php';
	if (!isset($_SESSION[SUMAC_SESSION_SOURCE])) //or we could check 'port'
	{
		sumac_destroy_session(sumac_formatMessage($_SESSION[SUMAC_STR]["AE7"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
	if (($usingHTTPS == false) && (strtolower(substr($_SESSION[SUMAC_SESSION_ALLOWHTTP],0,1)) != 't'))
	{
		sumac_destroy_session(SUMAC_ERROR_REQUIRES_SSL . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	if (!isset($sumac_resume) || !$sumac_resume)	//this is a variable only set in 'sumac_start.php'
	{
		$referer = sumac_get_referer(SUMAC_SESSION_FOLDER);
		if ($referer == false)
		{
			sumac_destroy_session(SUMAC_ERROR_VERIFY_REFERER . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
		$firstParamPos = strpos($referer,'?');
		if (!($firstParamPos === false) && ($firstParamPos > 0)) $referer = substr($referer,0,$firstParamPos);
		if (
				($referer != '/sumac_start.php') &&
				($referer != '/sumac_ticketing_redirect.php') &&
				($referer != '/sumac_courses_redirect.php') &&
				($referer != '/sumac_identify_user.php') &&
				($referer != '/sumac_payment_made.php') &&
				($referer != '/sumac_ticketing2_ordered.php') &&
				($referer != '/sumac_event_chosen.php') &&
				($referer != '/sumac_donation_made.php') &&
				($referer != '/sumac_membership_renewed.php') &&
				($referer != '/sumac_contact_updated.php') &&
				($referer != '/sumac_course_chosen.php') &&
				($referer != '/sumac_course_unchosen.php') &&
				($referer != '/sumac_form_chosen.php') &&
				($referer != '/sumac_directory_chosen.php') &&
				($referer != '/raocu_start.php') &&
				($referer != '/raods_start.php') &&
				($referer != '/raomr_start.php') &&
				($referer != '/raots_start.php') &&
				($referer != '/sumac_start_new_session.php') &&
				($referer != '/sumac_redirect.php')
			)
		{
			sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INVALID_REFERER,$referer) . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
	}

	$package = (isset($_GET['package'])) ? strtolower($_GET['package']) : '';
	$entry = (isset($_GET['entry'])) ? strtolower($_GET['entry']) : '';

//@@@ this may be a rather poor idea
	if (isset($sumac_resume) && ($sumac_resume == true))	//this is a variable only set in 'sumac_start.php'
	{
//take any parameter overrides in the href
include_once 'sumac_set_parameters.php';
		if (sumac_set_parameters($package,true) === false) return; //the session has already been destroyed

//if the request is for a selected course-session we must refetch the organisation record so as to translate the session data
//if the request is for a single form we must refetch the organisation record so as to get the form template
		$special = '';
		if (($entry == SUMAC_FUNCTION_REGISTER) && (isset($_GET['coursename']) || isset($_GET['coursedate']))) $special = SUMAC_FUNCTION_REGISTER;
		else if (($package == SUMAC_PACKAGE_SINGLEFORM2) && isset($_GET['formname'])) $special = SUMAC_PACKAGE_SINGLEFORM2;
		if ($special != '')
		{
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
include_once 'sumac_http.php';
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

			if ($special == SUMAC_FUNCTION_REGISTER)
			{
				if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])) unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
				$coursename = (isset($_GET['coursename'])) ? $_GET['coursename'] : '';
				$coursedate = (isset($_GET['coursedate'])) ? $_GET['coursedate'] : '';
				$include .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_SELECTEDSESSION .
							'&' . SUMAC_REQUEST_KEYWORD_COURSESESSIONNAME . '=' . urlencode($coursename) .
							'&' . SUMAC_REQUEST_KEYWORD_COURSESESSIONDATE . '=' . urlencode($coursedate);
			}
			else if ($special == SUMAC_PACKAGE_SINGLEFORM2)
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
		}
	}
	if (isset($sumac_resume)) unset($sumac_resume);	//its usefulness is over

//are we changing the active package?
	if ($package != '')
	{
		$packagelist = array(SUMAC_PACKAGE_DONATION, SUMAC_PACKAGE_TICKETING, SUMAC_PACKAGE_COURSES,
							SUMAC_PACKAGE_DIRECTORIES, SUMAC_PACKAGE_MEMBERSHIP, SUMAC_PACKAGE_CONTACT_UPDATE,
							SUMAC_PACKAGE_DONATION2, SUMAC_PACKAGE_SINGLEFORM2, SUMAC_PACKAGE_SIGNUP2,
							SUMAC_PACKAGE_GRANTREVIEW2G,SUMAC_PACKAGE_GRANTREVIEW2R,
							SUMAC_PACKAGE_MEMBERSHIP2,SUMAC_PACKAGE_TICKETING2);
		if (!in_array($package,$packagelist))
		{
			sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INITIAL_PACKAGE_NOT_VALID,$package) . $_SESSION[SUMAC_STR]["AE5"]);
			return false;
		}
		$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] = $package;

	}

//and if the request is for Courses, in some cases re-interpret the 'entry' setting as 'login' followed by some action
	if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		&& (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]) == false))	//the user is not yet logged in
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

//but if we have timed out,	we should start a new session after all
	if (time() - $_SESSION[SUMAC_SESSION_TIMESTAMP] > $_SESSION[SUMAC_USERPAR_SESSEXPIRY])
	{
		sumac_forced_session_restart();
		return;
	}
	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();

	//@@@ ??? capture status data
	if ($entry == SUMAC_FUNCTION_LOGOUT)
	{

		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS])) unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		if (isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) unset($_SESSION[SUMAC_SESSION_CONTACT_ID]);
		$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] = $_SESSION[SUMAC_STR]["AU3"];

		if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) unset($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;

//for some packages logging out implies a need to log back in again
		if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
			|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP)
			|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP2)
			|| ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE))
		{
			$entry = SUMAC_FUNCTION_LOGIN;
		}
	}
	if ($entry == SUMAC_FUNCTION_LOGIN)
	{
//most packages allow new users, but contact update does not
//		$allownewuser = true;	//by default we allow them
//		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE) $allownewuser = false;
//include_once 'sumac_user_login.php';
//		if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],$allownewuser) == false)
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
		{
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_CONTACT_UPDATE,'L','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else
		{
include_once 'sumac_login2.php';
			if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($entry == SUMAC_FUNCTION_LOGOUT)
	{
//only necessary to deal with packages for which logging out does not imply a need to log back in again
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
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
		else 	//should never happen
		{
			sumac_destroy_session('unknown active package ' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . ' for sumac_redirect, function=' . SUMAC_FUNCTION_LOGOUT);
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_make_donation.php';
			if (sumac_execDonate('','sumac_donation_made.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
//include_once 'sumac_user_login.php';
//			if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],true) == false)
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_DONATION,'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION2)
	{
include_once 'sumac_donation2.php';
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
			if (sumac_donation2('',null,'','sumac_donation2_made.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else
		{
			if (sumac_donation2('',null,'','sumac_identify_user.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SINGLEFORM2)
	{
include_once 'sumac_singleform2.php';
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
			if (sumac_singleform2('','',null,null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else
		{
			if (sumac_singleform2('','',null,null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SIGNUP2)
	{
include_once 'sumac_login2.php';
		if (sumac_login2('signup2','C','','','sumac_identify_user.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_grantreview2_grantee.php';
			if (sumac_grantreview2_grantee(null,'') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_GRANTREVIEW2G,'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2R)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_grantreview2_reviewer.php';
			if (sumac_grantreview2_reviewer(null,'') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_GRANTREVIEW2R,'L','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_renew_membership.php';
			if (sumac_execRenew('','sumac_membership_renewed.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
//include_once 'sumac_user_login.php';
//			if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],true) == false)
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_MEMBERSHIP,'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP2)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_membership2.php';
			if (sumac_membership2(null,'','') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_MEMBERSHIP2,'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_update_contact_details.php';
			if (sumac_execUpdate('','sumac_contact_updated.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else			//not yet logged in
		{
//include_once 'sumac_user_login.php';
//			if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],false) == false)
include_once 'sumac_login2.php';
			if (sumac_login2(SUMAC_PACKAGE_CONTACT_UPDATE,'L','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}

	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
//always allow user to pick another event, whether or not they are logged in, whether or not they have tickets in their basket already
include_once 'sumac_select_event.php';
		if (sumac_execSelectEvent('sumac_event_chosen.php') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING2)
	{
//always allow user to pick another event, whether or not they are logged in, whether or not they have tickets in their basket already
include_once 'sumac_ticketing2.php';
		if (sumac_ticketing2() == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		if (($entry == '') || ($entry == 'register')) //function is course catalog
		{
			if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])
				&& ($_SESSION[SUMAC_SESSION_TOTAL_CENTS] > 0))
//??? should we use a different test to see if a course has been selected for registration
			{
include_once 'sumac_pay_for_course.php';
				if (sumac_execPayForCourse('','sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else
			{
include_once 'sumac_select_course.php';
				if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else //function is personal history (including forms) or payment
		{
			if (($entry == 'formslist') || ($entry == 'finhistory') || ($entry == 'eduhistory')) //personal
			{
include_once 'sumac_personal_history.php';
				if (sumac_execShowPersonalHistory($entry,'sumac_form_chosen.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($entry == 'paycourse')	//payment
			{
include_once 'sumac_pay_without_purchase.php';
				if (sumac_execPayWithoutPurchase('','sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else 	//should never happen
			{
				sumac_destroy_session('unknown course entry ' . $entry . ' for sumac_redirect');
			}
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
	else 	//should never happen
	{
		sumac_destroy_session('unknown package ' . $package . ' for sumac_redirect');
	}

	return;

?>