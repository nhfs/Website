<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';
include_once 'sumac_geth2.php';
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
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_ticketing2_ordered.php')
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


	//was the BUY button was clicked?
	if (isset($_POST['pay']))
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
				. '" cc="' . sumac_convertXMLSpecialChars($_POST['amountpaid'])
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
		if ($responseDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
		else
		{
			$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
			$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length == 0) $responseMessage = $_SESSION[SUMAC_STR]["TX4"];
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $_SESSION[SUMAC_STR]["TX4"];
			$responseMessagecodeElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGECODE);
			if ($responseMessagecodeElements->length > 0)
			{
				$userResponseMessage = sumac_getUserMessageFromMessagecode($responseMessagecodeElements->item(0));
				if ($userResponseMessage != null) $responseMessage = $userResponseMessage;
			}
			if ($responseStatus == 'good')
			{
				$organisationDocument = sumac_reloadOrganisationDocument();
				if ($organisationDocument == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
				$extrasDocument = sumac_reloadExtrasDocument();
				if ($extrasDocument == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
				if (sumac_countTicketOrdersInBasket() > 0)
				{
					$summaryHTML = sumac_ticketing2_title_with_user()
									.sumac_ticketing2_summary_table($organisationDocument,$extrasDocument,false);
					echo sumac_geth2_exit_package_page('ticketing2',$responseDocument,$responseMessage,'L2','L3','',$summaryHTML);
				}
			}
			else if ($responseStatus == 'bad')
			{
include_once 'sumac_ticketing2_pay.php';
				if (sumac_ticketing2_pay(null,'pay',$responseMessage) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_geth2_exit_package_page('ticketing2',$responseDocument,$responseMessage,'L2','L3','');
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_ticketing2_ordered without variable pay for package ' . SUMAC_PACKAGE_TICKETING);
	}

?>