<?php
//version5122//

//this is 'sumac_course_chosen.php'
//control is passed here by the register button on the course selection page

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
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_courses_redirect.php') &&
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_course_chosen.php')
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

	if (isset($_POST['register']))
	{
include_once 'sumac_xml.php';
		sumac_setCourseSelectionsFromPOST();
		$xml = SUMAC_XML_HEADER . sumac_addXMLregistrationData();
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		//this will access the Sumac server to get the extras data and make sure it is available for display
		//everywhere else in the code can then use reloadExtrasDocument()
		$extrasDocument = sumac_loadExtrasDocument($source,$port,SUMAC_REQUEST_PARAM_REGISTRATIONEXTRAS,
														SUMAC_REQUEST_KEYWORD_COURSESESSION,$xml);
		if ($extrasDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
			return;
		}

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
include_once 'sumac_user_login.php';
			if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],true) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_course_chosen: variable register not set');
	}
	return;

function sumac_setCourseSelectionsFromPOST()
{
	if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])) unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
	$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'] = $_POST['session'];
	$costs = array();
	$requirements = array();
	foreach ($_POST as $pn => $pv)
	{
		if ($pn == 'options')
		{
			$options = explode(';',$pv);
			//if ($options[0] == $pv) ... there were no semi-colons ... so?
			//the final semi-colon creates an unwanted null entry in the options array
			//e.g. options=>mex1;rq2;no meat please;mex2;rq2;No special requirements for meals;
			while (((count($options)-1) % 3) > 0) $options[] = '';
			for ($i = 0; $i < (count($options)-1); $i += 3)
			{
				$cid = $options[$i];
				if (isset($requirements[$cid]))
				{
					$requirements[$cid][$options[$i+1]] = $options[$i+2];
				}
				else
				{
					$requirements[$cid] = array($options[$i+1] => $options[$i+2]);
				}
			}
		}
		else if ((strlen($pn) > 5) && (substr($pn,0,5) == 'cost='))
		{
			$parts = explode('=',$pn);
			$costs[$parts[1]] = $pv;
			$prices[$parts[1]] = $parts[2];
		}
		//else - we aren't interested (we already captured 'session')
	}
	$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'] = $costs;
	$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'] = $prices;
	$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'] = $requirements;
}

?>
