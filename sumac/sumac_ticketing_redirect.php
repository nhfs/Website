<?php
//version567//

//this is 'sumac_ticketing_redirect.php'
//control is passed here by any of the links in the ticketing navbar,
//		'restart' - empty the order basket and go back to event selection
//		'more' - select another event (or could be the same one to change ticket choices)
//		'order' - calculate overall costs and get users contact and payment details
//or by the 'revisit' link in any ticket already in the order basket

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

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
	if (
			($referer != '/sumac_event_chosen.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_payment_made.php') &&
			($referer != '/sumac_ticketing2_ordered.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_ticketing_redirect.php')
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



	//first capture status data for production grouping and details if it is present
	if (isset($_GET['ag'])) $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = $_GET['ag'];
	foreach ($_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] as $prodId => $status)
	{
		$dsname = 'ds' . $prodId;
		if (isset($_GET[$dsname])) $_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING][$prodId] = $_GET[$dsname];
	}

	//then deal with basket if there is one, which there will be after (and only after) seat selection
	if (isset($_GET['basket']))
	{
include_once 'sumac_xml.php';
		$theatreDocument = sumac_reloadOrganisationDocument();
		if ($theatreDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
			return;
		}
		$_SESSION[SUMAC_SESSION_TICKET_BASKET] = sumac_reformatPostedBasket($_GET['basket'],$theatreDocument);
//echo 'GET_BASKET=' . $_GET['basket'] . '<br />';
//echo sumac_countTicketOrdersInBasket() . ' ticket orders for ' . countEventsInBasket() . ' events<br /><br /><br />';

		if (sumac_countTicketOrdersInBasket() > 0)
		{
			$xml = SUMAC_XML_HEADER . sumac_addXMLTicketsFromBasket();
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			//this will access the Sumac server to get the extras data and make sure it is available for display
			//everywhere else in the code can then use reloadExtrasDocument()
			$extrasDocument = sumac_loadExtrasDocument($source,$port,SUMAC_REQUEST_PARAM_TICKETINGEXTRAS,
															SUMAC_REQUEST_KEYWORD_TICKETS,$xml);
			if ($extrasDocument == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
				return;
			}
		}
		else
		{
			sumac_unloadExtrasDocument();
		}
	}

	//then figure out which of the action links was clicked and be on our way
	$function = $_GET['function'];
	if ($function == 'restart')
	{
		if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) unset($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
//@@@ should we discard the login just because the process is restarted? I think not ...
//		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS])) unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
//		$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] = $_SESSION[SUMAC_STR]["AU3"];
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
	}
	else if ($function == 'more')
	{
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
	}
	else if ($function == 'order')
	{
		if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is already logged in
		{
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
			{
include_once 'sumac_pay_for_tickets.php';
				if (sumac_execPayForTickets('','sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING2)
			{
include_once 'sumac_ticketing2_pay.php';
				if (sumac_ticketing2_pay(null,'','') == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else			//not yet logged in
		{
//include_once 'sumac_user_login.php';
//			if (sumac_execLogin('','sumac_identify_user.php',$_SESSION[SUMAC_SESSION_USE_PASSWORDS],true) == false)
include_once 'sumac_login2.php';
			if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],'L/CR','','','sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else if ($function == 'revisit')
	{
		if (isset($_GET['event'])) $_SESSION[SUMAC_SESSION_TICKETING_EVENT] = $_GET['event'];
		$event = $_SESSION[SUMAC_SESSION_TICKETING_EVENT];
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
	else 	//should never happen
	{
		sumac_destroy_session('unknown function ' . $function . ' for sumac_ticketing_redirect');
	}

	return;

function sumac_reformatPostedBasket($postedBasket,$theatreDocument)
{
	//if there was anything in the session basket we gather it into our new basket
	//BUT we will later destroy the session basket ???
	$basket = (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) ? $_SESSION[SUMAC_SESSION_TICKET_BASKET] : array();
	$pieces = explode('=',$postedBasket);
	if ($pieces[0] == $postedBasket) return $basket;

	$eventId = $pieces[0];
	if (isset($basket[$eventId])) unset($basket[$eventId]);
	if ($pieces[1] != '')
	{
		$tickets = array();
	//echo 'EVENT=' . $eventId . '<br />';
		$seats_with_promo = explode('+',$pieces[1]);
		//the first 'seat' is actually a promotion id
		$_SESSION[SUMAC_SESSION_TICKETING_PROMOTIONS][$eventId] = $seats_with_promo[0];
		$seats = array_slice($seats_with_promo,1);
		foreach ($seats as $seat)
		{
			$order = explode(',',$seat);
			if ($order[0] == $seat) continue; //this can happen when the user removes all seats previously ordered for the event
			$ticketInBasket = array();
			$id= ($order[0] != '') ? $order[0] : (sumac_getSellingUnitId($order[1]) . $order[3]);
			$ticketInBasket['label'] = $order[1];
			$ticketInBasket['pricing'] = $order[2];
			$ticketInBasket['letter'] = $order[3];
			$ticketInBasket['requirement'] = sumac_getRequirementText($order[3],$theatreDocument);
			$ticketInBasket['reqid'] = sumac_getRequirementId($order[3],$theatreDocument);
			$ticketInBasket['price'] = $order[4];
			$ticketInBasket['quantity'] = ($order[0] == '') ? $order[5] : 'seat';
			$ticketInBasket['category'] = $order[6];
			$ticketInBasket['sellingUnit'] = ($order[0] == '') ? sumac_getSellingUnitId($order[1]) : '';
			$tickets[$id] = $ticketInBasket;
	//echo 'id=' . $id . ',label=' . $ticketInBasket['label'] . ',pricing=' . $ticketInBasket['pricing'];
	//echo ',letter=' . $ticketInBasket['letter'] . ',requirement=' . $ticketInBasket['requirement'];
	//echo ',price=' . $ticketInBasket['price'] . ',quantity=' . $ticketInBasket['quantity'] . '<br />';
		}
		$basket[$eventId] = $tickets;
	}
	if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) unset($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
	return $basket;
}

function sumac_getSellingUnitId($id)
{
	return substr($id,strlen(SUMAC_CLASS_PREFIX_SECTION));
}

function sumac_getRequirementId($lettercode,$theatreDocument)
{
	$requirementElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_REQUIREMENT);
	for ($i = 0; $i < $requirementElements->length; $i++)
	{
		$requirementElement = $requirementElements->item($i);
		if ($requirementElement->getAttribute(SUMAC_ATTRIBUTE_LETTER_CODE) == $lettercode)
		{
			return $requirementElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		}
	}
	return '';
}

function sumac_getRequirementText($lettercode,$theatreDocument)
{
	if ($lettercode == $_SESSION[SUMAC_STR]["TU2"]) return $_SESSION[SUMAC_STR]["TU1"];
	$requirementElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_REQUIREMENT);
	for ($i = 0; $i < $requirementElements->length; $i++)
	{
		$requirementElement = $requirementElements->item($i);
		if ($requirementElement->getAttribute(SUMAC_ATTRIBUTE_LETTER_CODE) == $lettercode)
		{
			return ($requirementElement->childNodes->item(0) != null) ? $requirementElement->childNodes->item(0)->nodeValue : '';
		}
	}
	return '[' . $lettercode . ']';
}

?>
