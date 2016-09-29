<?php
//version568//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';
include_once 'sumac_xml.php';

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
	if (
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_ticketing_redirect.php') &&
			($referer != '/sumac_course_chosen.php') &&
			($referer != '/sumac_course_unchosen.php') &&
			($referer != '/sumac_courses_redirect.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_payment_made.php')
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


	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
		//was the BUY button was clicked?
		if (isset($_POST['buy']))
		{
			//check that we have a contact id for the buyer
			if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
			{
				sumac_destroy_session(SUMAC_ERROR_NO_BUYER_ID . $_SESSION[SUMAC_STR]["AE5"]);
				return;
			}

			$deliverymechanism = (isset($_POST['deliverymechanism'])) ? $_POST['deliverymechanism'] : '';
			$informationsource = (isset($_POST['informationsource'])) ? $_POST['informationsource'] : '';
			$payeenote = (isset($_POST['payeenote'])) ? $_POST['payeenote'] : '';
		//checking that other fields are non-blank should have been done by JS before returning here
			$xml = SUMAC_XML_HEADER;
			$xml .= '<buy>';
			$xml .= '<payment id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
					. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
					. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
					. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
					. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
					. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
					. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser'])
					. '" cc="' . sumac_convertXMLSpecialChars($_POST['centscharge'])
					. '" dm="' . sumac_convertXMLSpecialChars($deliverymechanism)
					. '" is="' . sumac_convertXMLSpecialChars($informationsource)
					. '" pn="' . sumac_convertXMLSpecialChars($payeenote)
					. '">';
			$xml .= '</payment>';
			$xml .= sumac_addXMLTicketsFromBasket();
			$xml .= '</buy>';

		//echo $xml;

include_once 'sumac_xml.php';
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_PAYMENT,$xml);
			sumac_processBuyResult($responseDocument,'buy');
		}
		else 	//should never happen
		{
			sumac_destroy_session('Unexpected arrival in sumac_payment_made without variable buy for package ' . SUMAC_PACKAGE_TICKETING);
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		// was one of the pay-for-course buttons clicked?
		if (isset($_POST['buycourse']))
		{
			//check that we have a contact id for the buyer
			if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
			{
				sumac_destroy_session(SUMAC_ERROR_NO_BUYER_ID . $_SESSION[SUMAC_STR]["AE5"]);
				return;
			}

			//courses do not require a delivery mechanism
			//$deliverymechanism = (isset($_POST['deliverymechanism'])) ? $_POST['deliverymechanism'] : '';
			$informationsource = (isset($_POST['informationsource'])) ? $_POST['informationsource'] : '';
		//checking that other fields are non-blank should have been done by JS before returning here
			$paymentchoice = ($_POST['buycourse'] == $_SESSION[SUMAC_STR]["CL8"]) ? 'full' : 'min';
			$centscharge = ($paymentchoice == 'full') ? $_POST['fullcentscharge'] : $_POST['mincentscharge'];
			$xml = SUMAC_XML_HEADER;
			$xml .= '<buy>';
			$xml .= '<payment id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
					. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
					. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
					. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
					. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
					. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
					. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser'])
					. '" cl="' . sumac_convertXMLSpecialChars($paymentchoice)
					. '" cc="' . sumac_convertXMLSpecialChars($centscharge)
					. '" is="' . sumac_convertXMLSpecialChars($informationsource) . '">';
			$xml .= '</payment>';
			$xml .= sumac_addXMLregistrationData();
			$xml .= '</buy>';
		//echo $xml;

include_once 'sumac_xml.php';
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_PAYMENT,$xml);
			sumac_processBuyResult($responseDocument,'buycourse');
		}
		else if (isset($_POST['buynothing']))
		{
			//check that we have a contact id for the buyer
			if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
			{
				sumac_destroy_session(SUMAC_ERROR_NO_BUYER_ID . $_SESSION[SUMAC_STR]["AE5"]);
				return;
			}

		//checking that fields are non-blank should have been done by JS before returning here
			$centsPaid = $_POST['amountpaid'] * 100;
			$xml = SUMAC_XML_HEADER;
			$xml .= '<buy>';
			$xml .= '<payment id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
					. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
					. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
					. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
					. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
					. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
					. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser'])
					. '" cc="' . sumac_convertXMLSpecialChars($centsPaid) . '">';
			$xml .= '</payment>';
			$xml .= '</buy>';
		//echo $xml;

include_once 'sumac_xml.php';
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_PAYMENT,$xml);
			sumac_processBuyResult($responseDocument,'buynothing');
		}
		else 	//should never happen
		{
			sumac_destroy_session('Unexpected arrival in sumac_payment_made without variable buycourse or buynothing for package ' . SUMAC_PACKAGE_COURSES);
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('unknown package ' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . ' for sumac_payment_made');
	}

function sumac_processBuyResult($responseDocument,$postedrequest)
{
	if ($responseDocument == false)
	{
		echo sumac_getAbortHTML();
		sumac_destroy_session('');
	}
	else
	{
		$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
		$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
		$defaultMessage = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) ? $_SESSION[SUMAC_STR]["TX4"] : $_SESSION[SUMAC_STR]["CX5"];
		if ($responseMessageElements->length == 0) $responseMessage = $defaultMessage;
		else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
								? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
								: $defaultMessage;
		$responseMessagecodeElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGECODE);
		if ($responseMessagecodeElements->length > 0)
		{
			$userResponseMessage = sumac_getUserMessageFromMessagecode($responseMessagecodeElements->item(0));
			if ($userResponseMessage != null) $responseMessage = $userResponseMessage;
		}
		$organisationDocument = ($postedrequest != 'buynothing') ? sumac_reloadOrganisationDocument() : false;
		$extrasDocument = ($postedrequest != 'buynothing') ? sumac_reloadExtrasDocument() : false;
		$exitpackage = ($postedrequest != 'buynothing');
		if ($responseStatus == 'good')
		{
			$statusMessage = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) ? $_SESSION[SUMAC_STR]["TB2"] : $_SESSION[SUMAC_STR]["CB4"];
			echo sumac_getFunctionCompletedHTML($statusMessage,$responseMessage,$responseDocument,$organisationDocument,$extrasDocument,$exitpackage);
		}
		else if ($responseStatus == 'bad')
		{
			if ($postedrequest == 'buy')
			{
include_once 'sumac_pay_for_tickets.php';
				if (sumac_execPayForTickets($responseMessage,'sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($postedrequest == 'buycourse')
			{
include_once 'sumac_pay_for_course.php';
				if (sumac_execPayForCourse($responseMessage,'sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($postedrequest == 'buynothing')
			{
include_once 'sumac_pay_without_purchase.php';
				if (sumac_execPayWithoutPurchase($responseMessage,'sumac_payment_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else 	//should never happen
			{
				sumac_destroy_session('unknown posted request ' . $postedrequest . ' for sumac_payment_made, sumac_processBuyResult');
			}
		}
		else	//responseStatus is hopeless
		{
			$statusMessage = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) ? $_SESSION[SUMAC_STR]["TB1"] : $_SESSION[SUMAC_STR]["CB3"];
			echo sumac_getFunctionCompletedHTML($statusMessage,$responseMessage,$responseDocument,$organisationDocument,$extrasDocument,$exitpackage);
		}
	}
}

?>