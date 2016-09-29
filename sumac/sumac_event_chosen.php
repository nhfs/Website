<?php
//version567//

//this is 'sumac_event_chosen.php'
//control is passed here by one of the three 'submit' buttons in the event selection page
//		cancel - exit sumac, return to invoking website
//		order - calculate overall costs and get users contact and payment details
//		choose seats - pick tickets for the selected event
//we could also get here via the 'Back' button from the ticket-picking page

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
		sumac_destroy_session(sumac_formatMessage($_SESSION[SUMAC_STR]["AE7"],$_SESSION[SUMAC_SESSION_THEATRE_NAME]) . $_SESSION[SUMAC_STR]["AE5"]);
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
			($referer != '/raots_start.php') &&
			($referer != '/sumac_ticketing_redirect.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_payment_made.php') &&
			($referer != '/sumac_ticketing2_ordered.php') &&
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_redirect.php')
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

	if (isset($_POST['pickseats']))
	{
		if (isset($_POST['eventchosen']))
		{
			$event = $_POST['eventchosen'];
			$_SESSION[SUMAC_SESSION_TICKETING_EVENT] = $event;
			//capture status data from other posted variables
			if (isset($_POST['ag'])) $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = $_POST['ag'];
			foreach ($_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] as $prodId => $status)
			{
				$dsname = 'ds' . $prodId;
				if (isset($_POST[$dsname])) $_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING][$prodId] = $_POST[$dsname];
			}

			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
			{
include_once 'sumac_pick_tickets.php';
				if (sumac_execPickTicketsForEvent($source,$port,$event) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING2)
			{
include_once 'sumac_ticketing2_seats.php';
				if (sumac_ticketing2_seats($source,$port,$event) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else	//should never happen
		{
			sumac_destroy_session('Unexpected arrival in sumac_event_chosen via pickseats with no eventchosen');
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_event_chosen without variable pickseats');
	}

?>
