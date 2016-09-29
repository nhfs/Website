<?php
//version568//

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
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_contact_updated.php')
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

	if (isset($_POST['update']))
	{
//check that we have a contact id for the user
		if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_USER_ID . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}

	//checking that certain fields are non-blank should have been done by JS before returning here
	//except for password ...
		$newpassword = (isset($_POST['newpassword'])) ? $_POST['newpassword'] : '';
		if ($newpassword == '') $newpassword = $_SESSION[SUMAC_SESSION_FORMER_PASSWORD];
		unset($_POST['newpassword']);
//many contact fields can be made optional (via the omitfields parameter)
		$nameprefix =  isset($_POST['nameprefix']) ? $_POST['nameprefix'] : '';
		$fn = (isset($_POST['firstname'])) ? $_POST['firstname'] : '';
		$a1 = (isset($_POST['address1']))  ? $_POST['address1'] : '';
		$a2 = (isset($_POST['address2']))  ? $_POST['address2'] : '';
		$ci = (isset($_POST['city']))  ? $_POST['city'] : '';
		$pr = (isset($_POST['province']))  ? $_POST['province'] : '';
		$pc = (isset($_POST['postcode']))  ? $_POST['postcode'] : '';
		$co = (isset($_POST['country']))  ? $_POST['country'] : '';
		$ph = (isset($_POST['phone']))  ? $_POST['phone'] : '';
		$cp = (isset($_POST['cellphone']))  ? $_POST['cellphone'] : '';
		$si =  isset($_POST['contactsourceid']) ? $_POST['contactsourceid'] : '';
		$commprefs = '';
		foreach ($_POST as $checkbox_name => $pv)
		{
			if ((strlen($checkbox_name) > 5) && (substr($checkbox_name,0,5) == 'cpip_'))
			{
				$id = substr($checkbox_name,5);
				$commprefs .= (($commprefs=='') ? $id : ('|'.$id));
			}
		}

		$xml = SUMAC_XML_HEADER;
		$xml .= '<updateuser>';
		$xml .= '<user id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
				. '" ea="' . sumac_convertXMLSpecialChars($_POST['email'])
				. '" np="' . sumac_convertXMLSpecialChars($nameprefix)
				. '" fn="' . sumac_convertXMLSpecialChars($fn)
				. '" ln="' . sumac_convertXMLSpecialChars($_POST['lastname'])
				. '" a1="' . sumac_convertXMLSpecialChars($a1)
				. '" a2="' . sumac_convertXMLSpecialChars($a2)
				. '" ci="' . sumac_convertXMLSpecialChars($ci)
				. '" pr="' . sumac_convertXMLSpecialChars($pr)
				. '" pc="' . sumac_convertXMLSpecialChars($pc)
				. '" co="' . sumac_convertXMLSpecialChars($co)
				. '" ph="' . sumac_convertXMLSpecialChars($ph)
				. '" cp="' . sumac_convertXMLSpecialChars($cp)
				. '" pw="' . sumac_convertXMLSpecialChars($newpassword)
				. '" si="' . sumac_convertXMLSpecialChars($si)
				. '" pf="' . sumac_convertXMLSpecialChars($commprefs) . '">';
		$xml .= '</user>';
		$xml .= '</updateuser>';

include_once 'sumac_xml.php';
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_UPDATEUSER,$xml);
		if ($responseDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
		else
		{
			$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
			$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length == 0) $responseMessage = $_SESSION[SUMAC_STR]["UX1"];
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $_SESSION[SUMAC_STR]["UX1"];
			$responseMessagecodeElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGECODE);
			if ($responseMessagecodeElements->length > 0)
			{
				$userResponseMessage = sumac_getUserMessageFromMessagecode($responseMessagecodeElements->item(0));
				if ($userResponseMessage != null) $responseMessage = $userResponseMessage;
			}
			if ($responseStatus == 'good')
			{
				//and the username/emailaddress may have been updated
				$_SESSION[SUMAC_SESSION_EMAILADDRESS] = $_POST['email'];
				echo sumac_getFunctionCompletedHTML($_SESSION[SUMAC_STR]["UB2"],$responseMessage,$responseDocument,false,false,true);
			}
			else if ($responseStatus == 'bad')
			{
include_once 'sumac_update_contact_details.php';
				if (sumac_execUpdate($responseMessage,'sumac_contact_updated.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_getFunctionCompletedHTML($_SESSION[SUMAC_STR]["UB1"],$responseMessage,$responseDocument,false,false,true);
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_contact_updated');
	}

?>
