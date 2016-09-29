<?php
//version551//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

	if (!isset($combinedWithLogin))
	{
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
				($referer != '/sumac_donation2_made.php')
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

	}

/*****************************************************************************
			$html = '';
			$html .= ' ... there are ' . count($_POST) . '.<br /><br />';
			foreach ($_POST as $x => $y)
			{
				$html .= '$_POST[' . $x . ']="' . $y . '"<br />';
			}
			$html .= '<br />' . '... and these are the $_GET variables now';
			$html .= ' ... there are ' . count($_GET) . '.<br /><br />';
			foreach ($_GET as $x => $y)
			{
				$html .= '$_GET[' . $x . ']="' . $y . '"<br />';
			}
			echo $html;
			return;
*******************************************************************************/

	if (isset($_POST['pay']))
	{
//check that we have a contact id for the buyer
		if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_CONTACT_ID . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}

	//checking that certain fields are non-blank should have been done by JS before returning here
		$xml = SUMAC_XML_HEADER;
		$xml .= '<donation id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
				. '" ct="' . sumac_convertXMLSpecialChars($_POST['cardtype'])
				. '" cn="' . sumac_convertXMLSpecialChars($_POST['cardnumber'])
				. '" cs="' . sumac_convertXMLSpecialChars($_POST['cardsecurity'])
				. '" cm="' . sumac_convertXMLSpecialChars($_POST['cardexpmonth'])
				. '" cy="' . sumac_convertXMLSpecialChars($_POST['cardexpyear'])
				. '" cu="' . sumac_convertXMLSpecialChars($_POST['carduser']);
		if (isset($_POST['deductionday'])) $xml .= '" dd="' . sumac_convertXMLSpecialChars($_POST['deductionday']);
		if (isset($_POST['dedicatee'])) $xml .= '" de="' . sumac_convertXMLSpecialChars($_POST['dedicatee']);
		$xml .= '" fc="' . sumac_convertXMLSpecialChars($_POST['frequency'])
				. '" df="' . sumac_convertXMLSpecialChars($_POST['fund'])
				. '" cd="' . sumac_convertXMLSpecialChars($_POST['amountpaid']) . '">';
		$xml .= '</donation>';

	//echo $xml;

include_once 'sumac_xml.php';
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_DONATION,$xml);
		if ($responseDocument == false)
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
		else
		{
			$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
			$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length == 0) $responseMessage = $_SESSION[SUMAC_STR]["DX1"];
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $_SESSION[SUMAC_STR]["DX1"];
			if ($responseStatus == 'good')
			{
				if ($_SESSION[SUMAC_USERPAR_D2NOLOGIN])
				{
					if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS])) unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
					if (isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) unset($_SESSION[SUMAC_SESSION_CONTACT_ID]);
				}
				echo sumac_geth2_exit_package_page('donation2',$responseMessage,'L2',SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_LINK,SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_TEXT);
			}
			else if ($responseStatus == 'bad')
			{
include_once 'sumac_donation2.php';
				if (sumac_donation2('pay',$responseMessage,'sumac_donation2_made.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_geth2_exit_package_page('donation2',$responseMessage,'L2',SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_LINK,SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_TEXT);
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_donation2_made');
	}

?>
