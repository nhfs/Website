<?php
//version5631//

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
			($referer != '/sumac_course_unchosen.php') &&
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
		$xml = SUMAC_XML_HEADER . '<extras>' . sumac_addXMLregistrationData() . '</extras>';
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
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_course_chosen: variable register not set');
	}
	return;

function sumac_setCourseSelectionsFromPOST()
{
//client may have specified only one registration per payment or Sumac Backend may not support more than one
//in which case remove any earlier selection before adding this one
	if ($_SESSION[SUMAC_USERPAR_SINGLEREG] || (!sumac_featureIsSupported(SUMAC_FEATURE_COURSES_MULTISESSIONPAYMENT)))
	{
		if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])) unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
	}
	if (!isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]))
	{
		//define four arrays: each element in the arrays corresponds to one course selection
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'] = array();
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'] = array();
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'] = array();
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'] = array();
	}
	//have we already got this session in our registration basket?
	$sessionid = $_POST['session'];
	$selectedsessionIndex = sumac_getSelectedsessionIndex($sessionid);
	if ($selectedsessionIndex < 0)
	{
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'][] = $sessionid;
	}
	else
	{
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$selectedsessionIndex]);
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$selectedsessionIndex]);
		unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$selectedsessionIndex]);
	}
	$costs = array();
	$prices = array();
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
	if ($selectedsessionIndex < 0)
	{
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][] = $costs;
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][] = $prices;
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][] = $requirements;
	}
	else
	{
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'][$selectedsessionIndex] = $costs;
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'][$selectedsessionIndex] = $prices;
		$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'][$selectedsessionIndex] = $requirements;
	}
}

?>
