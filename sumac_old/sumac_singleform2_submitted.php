<?php
//version530//

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
				($referer != '/sumac_singleform2_submitted.php')
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

//make sure we have the id of the form
	$formId = "";
	if (isset($_POST['sumac_form']))
	{
		$formId = $_POST['sumac_form'];
	}
	else
	{
		sumac_destroy_session(SUMAC_ERROR_FORM_UPDATE_HAS_NO_FORMID . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

//did the user click OK?
	if (isset($_POST['submitform']))
	{
include_once 'sumac_xml.php';
		$formTemplateElement = sumac_singleform2_get_formtemplate_element($formId);
		if ($formTemplateElement == false)
		{
			sumac_destroy_session($_SESSION[SUMAC_SESSION_REQUEST_ERROR] . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
//check that we have a contact id for the person filling out the form
		if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_CONTACT_ID . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}
		$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
		$formName = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$fformId = $_POST['sumac_filledform'];
		$formVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
		$formVersion = ($formVersion == '') ? 1 : ($formVersion + 1);
		$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
		//all checking of fields should have been done before returning here
		$fieldCount = $formFieldElements->length;
		$xml = SUMAC_XML_HEADER;
		$xml .= '<form fi="' . sumac_convertXMLSpecialChars($fformId) . '" ti="' . sumac_convertXMLSpecialChars($formId) . '"'
				. ' id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID] . '" vn="' . $formVersion . '">';
		for ($i = 0; $i < $fieldCount; $i++)
		{
			$formField = $formFieldElements->item($i);
			$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
			$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
			$fieldValue = isset($_POST[$fieldId]) ? $_POST[$fieldId] : '';
			if ($fieldDataType == 'flag') $fieldValue = isset($_POST[$fieldId]) ? '1' : '0';
			else if (($fieldDataType == 'lookuplist') && ($formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE) == 'checkboxes'))
			{
				$fieldValue = '';
				$fieldValueArray = array_values(array_filter(explode(';',$formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST))));
				for ($j = 0; $j < count($fieldValueArray); $j++)
				{
					$fieldValue .= isset($_POST[$fieldId . '_' .$j]) ? ($_POST[$fieldId . '_' .$j] . ',') : '';
				}
			}
			$xml .= '<field i="' . sumac_convertXMLSpecialChars($fieldId) . '" v="' . sumac_convertXMLSpecialChars($fieldValue) . '" />';
//echo $fieldId . '="' . $fieldValue . '"' . '<br />';
		}
		$xml .= '</form>';
//echo $xml;

include_once 'sumac_xml.php';
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$responseDocument = sumac_postRequestAndLoadResponseDocument($source,$port,SUMAC_REQUEST_PARAM_FORMUPDATE,$xml);

		if ($responseDocument == false)	//this means the update process did not work at all
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
		else
		{
			$responseStatus = $responseDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
			$defaultMessage = ($responseStatus == 'good') ? $_SESSION[SUMAC_STR]["CX1"] : $_SESSION[SUMAC_STR]["CX2"];
			$responseMessageElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length == 0) $responseMessage = $defaultMessage;
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $defaultMessage;
			if ($responseStatus == 'good')
			{
				unset($_SESSION[SUMAC_SESSION_FORM][$formId]);	//in case a response has created this saved copy
				echo sumac_geth2_exit_package_page('singleform2',$responseMessage,'L4','/donation-software',SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_TEXT);
			}
			else if ($responseStatus == 'bad')
			{
				$formTemplateElement = sumac_singleform2_get_formtemplate_element($formId);
				if ($formTemplateElement == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
					return;
				}
include_once 'sumac_singleform2.php';
				if (sumac_singleform2('submitform',$responseMessage,'sumac_singleform2_submitted.php',null,$formTemplateElement) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	//hopeless
			{
				echo sumac_geth2_exit_package_page('singleform2',$responseMessage,'L4','/donation-software',SUMAC_INFO_FOOTER_SUMAC_DONATION2_THANKS_TEXT);
			}
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_singleform2_submitted without variable submitform set');
	}
	return;

function sumac_singleform2_get_formtemplate_element($formId)
{
	if (isset($_SESSION[SUMAC_SESSION_FORM][$formId]))
	{
		$formTemplateDocument = sumac_reloadFormTemplateDocument($formId);
		if ($formTemplateDocument == false) return false;
		$formTemplateElement = ($formTemplateDocument->documentElement->tagName == SUMAC_ELEMENT_FORMTEMPLATE)
								? $formTemplateDocument->documentElement
								: $formTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
	}
	else
	{
		$organisationDocument = sumac_reloadOrganisationDocument();
		if ($organisationDocument == false) return false;
		$formTemplateElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
	}
	return $formTemplateElement;
}

?>
