<?php
//version510//

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
	if (
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_membership_renewed.php')
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


	if (isset($_POST['renew']))
	{
//check that we have a contact id for the buyer
		if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_RENEWER_ID . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
		$optextras = '';
		foreach($_POST as $p => $q) if ((substr($p,0,9) == 'optextra_') && ($q == '1')) $optextras .= substr($p,9) . ' ';
//echo '_POST[' . $p . ']="' . $q . '"<br />';

	//checking that certain fields are non-blank should have been done by JS before returning here
		$xml = SUMAC_XML_HEADER;
		$xml .= '<renewal id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
				. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
				. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
				. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
				. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
				. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
				. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser'])
				. '" rp="' . sumac_convertXMLSpecialChars($_POST['membershipplan'])
				. '" cr="' . sumac_convertXMLSpecialChars($_POST['costofmembership'])
				. '" oe="' . sumac_convertXMLSpecialChars($optextras) . '">';
		$xml .= '</renewal>';
	//echo $xml;

include_once 'sumac_xml.php';
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_RENEWAL,$xml);
		if ($responseDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
		else
		{
			$responseMessage = $_SESSION[SUMAC_STR]["MX2"];
			$contactDocument = sumac_reloadLoginAccountDocument();
			$currentMembershipElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_CURRENT_MEMBERSHIP);
			if ($currentMembershipElements->length == 0) $responseMessage = $_SESSION[SUMAC_STR]["MX3"];
			$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length > 0)
			{
				if ($responseMessageElements->item(0)->childNodes->item(0) != null)
					$responseMessage = $responseMessageElements->item(0)->childNodes->item(0)->nodeValue;
			}
			$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
			if ($responseStatus == 'good')
			{
				echo sumac_getFunctionCompletedHTML($_SESSION[SUMAC_STR]["MB2"],$responseMessage,false,false,true);
			}
			else if ($responseStatus == 'bad')
			{
include_once 'sumac_renew_membership.php';
				if (sumac_execRenew($responseMessage,'sumac_membership_renewed.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_getFunctionCompletedHTML($_SESSION[SUMAC_STR]["MB1"],$responseMessage,false,false,true);
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_membership_renewed');
	}

?>
