<?php
//version5122//

//this is 'sumac_courses_redirect.php'
//control is passed here by any of the links in the courses navbar,
//		'personal' - list the financial and educational history of the logged in user
//		'forms' - list the status of the forms for this user
//		'catalog' - show the course catalog

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
	if (!isset($_SESSION[SUMAC_SESSION_SOURCE]))
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
	if (
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_course_chosen.php') &&
			($referer != '/sumac_form_chosen.php') &&
			($referer != '/sumac_form_updated.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_payment_made.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_courses_redirect.php')
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


//@@@
	//first capture status data for course grouping and detail/instructor/registration panel if it is present

	//then see if this is a form-update-cancel operation and a form needs releasing from the session storage
	if (isset($_POST['abandonupdate']))
	{
		//if we have the id of the form, release our old copy; otherwise, press on regardless
		if (isset($_POST['sumac_form'])) unset($_SESSION[SUMAC_SESSION_FORM][$_POST['sumac_form']]);
		//the action to take is always to return to the formslist div of the personal history display
include_once 'sumac_personal_history.php';
		if (sumac_execShowPersonalHistory('formslist','sumac_form_chosen.php',null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	//or perhaps it is a form-view-complete operation
	else if (isset($_POST['viewcomplete']))
	{
		//the action again is always to return to the formslist div of the personal history display
include_once 'sumac_personal_history.php';
		if (sumac_execShowPersonalHistory('formslist','sumac_form_chosen.php',null) == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	//otherwise we need the function to figure out which of the action links was clicked and be on our way
	else if (isset($_GET['function']))
	{
		$function = $_GET['function'];
		if ($function == 'personal')
		{
			$div = $_GET['div'];
			if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
			{
	include_once 'sumac_personal_history.php';
				if (sumac_execShowPersonalHistory($div,'sumac_form_chosen.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else			//not yet logged in
			{
				$_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION] = $function;
				$_SESSION[SUMAC_SESSION_POST_LOGIN_DIV] =  $div;
	include_once 'sumac_user_login.php';
	//note that adding oneself as a new user is not rational if one is asking for personal history
	//but for a forms-list request it might be
				$allownewuser = ($div == 'formslist');
				if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],$allownewuser) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($function == 'payment')
		{
			if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
			{
	include_once 'sumac_pay_without_purchase.php';
				if (sumac_execPayWithoutPurchase('','sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else			//not yet logged in
			{
				$_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION] = $function;
				$_SESSION[SUMAC_SESSION_POST_LOGIN_DIV] =  '';
	include_once 'sumac_user_login.php';
				if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],true) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if (($function == 'catalog') || ($function == 'register'))
		{
	include_once 'sumac_select_course.php';
			if (sumac_execSelectCourse('sumac_course_chosen.php',0) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else 	//should never happen
		{
			sumac_destroy_session('unknown function ' . $function . ' for sumac_courses_redirect');
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_courses_redirect not via abandonupdate and without variable function set');
	}

?>