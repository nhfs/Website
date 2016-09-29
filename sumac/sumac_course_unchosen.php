<?php
//version565//

//this is 'sumac_course_unchosen.php'
//control is passed here to delete a previously selected course from the registration basket

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

	$referer = sumac_get_referer(SUMAC_SESSION_FOLDER);
	if ($referer == false)
	{
		sumac_destroy_session(SUMAC_ERROR_VERIFY_REFERER . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	$firstParamPos = strpos($referer,'?');
	if (!($firstParamPos === false) && ($firstParamPos > 0)) $referer = substr($referer,0,$firstParamPos);
	if (
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_courses_redirect.php') &&
			($referer != '/sumac_payment_made.php') &&
			($referer != '/sumac_course_chosen.php') &&
			($referer != '/sumac_course_unchosen.php')
		)
	{
		sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INVALID_REFERER,$referer) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	if (time() - $_SESSION[SUMAC_SESSION_TIMESTAMP] > $_SESSION[SUMAC_USERPAR_SESSEXPIRY])
	{
		sumac_forced_session_restart();
		return;
	}
	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();

	$selectionCount = 0;
	if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
		$selectionCount = count($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']);
	if ($selectionCount == 0)	//no session to remove
	{
		sumac_destroy_session(SUMAC_ERROR_NO_SESSION_TO_REMOVE . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	$session = isset($_GET['session']) ? $_GET['session'] : '';
	$index = sumac_getSelectedsessionIndex($session);
	if ($index < 0)		//session not in registration basket
	{
		sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INVALID_SESSION_TO_REMOVE,$session) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

// if there is only one selected session then we simply empty the basket,
//		set its total value to zero,
//		and destroy the extras data
//		and go back to the catalog/register view
	if ($selectionCount == 1)
	{
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
include_once 'sumac_xml.php';
		sumac_unloadExtrasDocument();
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
include_once 'sumac_select_course.php';
		if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else
	{
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'][$index]);
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$index]);
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$index]);
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$index]);
// if the selection to be removed is the latest one, it can just be rubbed out
// but for any other selection, since gaps in the array arent allowed, all later entries must be copied down one position
		if ($index < ($selectionCount - 1))
		{
			for ($i = ($index + 1); $i < $selectionCount; $i++)
			{
				$downone = $i - 1;
				$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'][$downone] = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'][$i];
				$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$downone] = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$i];
				$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$downone] = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$i];
				$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$downone] = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$i];
				unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'][$i]);
				unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$i]);
				unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$i]);
				unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$i]);
			}
		}
// now the extras document must be re-obtained from Sumac
include_once 'sumac_xml.php';
		$xml = SUMAC_XML_HEADER . '<extras>' . sumac_addXMLregistrationData() . '</extras>';
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$extrasDocument = sumac_loadExtrasDocument($source,$port,SUMAC_REQUEST_PARAM_REGISTRATIONEXTRAS,
														SUMAC_REQUEST_KEYWORD_COURSESESSION,$xml);
		if ($extrasDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
			return;
		}

//now we follow the same path as when a new course session has been chosen
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
include_once 'sumac_pay_for_course.php';
			if (sumac_execPayForCourse('','sumac_payment_made.php',null) == false)
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
			if (sumac_login2(SUMAC_PACKAGE_COURSES,'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	return;

?>
