<?php
//version567//

//this is 'sumac_membership2_submit.php'
//control is passed here by the add and remove buttons in the membership2 screen

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
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
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_membership2_submit.php')
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

	$paymentxml = '';
	if (isset($_POST['pay']))	//the user wants to buy this membership
	{
		//check that we have a contact id for the buyer
		if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_BUYER_ID . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
		$paymentxml = '<payment id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
						. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
						. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
						. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
						. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
						. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
						. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser'])
						. '" cc="' . sumac_convertXMLSpecialChars($_POST['amountpaid'])
						. '"></payment>';
	}
	$planId = $_POST['sumac_plan'];
	$_SESSION[SUMAC_SESSION_MEMBERSHIP_PLAN] = $planId;
	unset($_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS]);
	$optIds = $_POST['sumac_options'];
	if ($optIds == '')
	{
		sumac_unloadExtrasDocument();
	}
	else
	{
		$optIdArray = array_values(array_filter(explode(' ',$optIds)));
		$optCount = count($optIdArray);
		for ($i=0; $i<$optCount; $i++) $_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS][$i] = $optIdArray[$i];
	}
	$membershipxml = '<membership mp="' . sumac_convertXMLSpecialChars($planId)
							. '" oe="' . sumac_convertXMLSpecialChars($optIds)
							. '"></membership>';

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];

	if (isset($_POST['pay']))
	{
		$xml = SUMAC_XML_HEADER.'<buy>'.$paymentxml.$membershipxml.'</buy>';
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
			if ($responseMessageElements->length == 0) $responseMessage = $_SESSION[SUMAC_STR]["M2X1"];
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $_SESSION[SUMAC_STR]["M2X1"];
			$responseMessagecodeElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGECODE);
			if ($responseMessagecodeElements->length > 0)
			{
				$userResponseMessage = sumac_getUserMessageFromMessagecode($responseMessagecodeElements->item(0));
				if ($userResponseMessage != null) $responseMessage = $userResponseMessage;
			}
			if ($responseStatus == 'good')
			{
				echo sumac_geth2_exit_package_page('membership2',$responseDocument,$responseMessage,'L2','L5','');
			}
			else if ($responseStatus == 'bad')
			{
include_once 'sumac_membership2.php';
				if (sumac_membership2(null,'pay',$responseMessage) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_geth2_exit_package_page('membership2',$responseDocument,$responseMessage,'L2','L5','');
			}
		}
	}
	else	//just adding/removing a membership option - so get the extras again and go back to membership page
	{
		$extrasDocument = sumac_loadExtrasDocument($source,$port,SUMAC_REQUEST_PARAM_MEMBERSHIPEXTRAS,
											SUMAC_REQUEST_KEYWORD_MEMBERSHIP,SUMAC_XML_HEADER.$membershipxml);
		if ($extrasDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
			return;
		}
include_once 'sumac_membership2.php';
		if (sumac_membership2(null,'extras','') == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}

?>