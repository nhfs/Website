<?php
//version510//

//this is 'sumac_form_updated.php'

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
			($referer != '/sumac_form_chosen.php') &&
			($referer != '/sumac_form_updated.php')
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

//check that we have a contact id for the form owner
	if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
	{
		sumac_destroy_session(SUMAC_ERROR_NO_OWNER_ID . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

//make sure we have the id of the form
	$formId = "";
	if (isset($_POST['sumac_form']))
	{
		$formId = $_POST['sumac_form'];
	}
	else
	{
		echo sumac_getFormClosingHTML(SUMAC_ERROR_FORM_UPDATE_HAS_NO_FORMID,SUMAC_TEXT_UNKNOWN_FORM);
		return;
	}

//did the user click OK?
	if (isset($_POST['updateform']))
	{
include_once 'sumac_xml.php';
		$formTemplateDocument = sumac_reloadFormTemplateDocument($formId);
		if ($formTemplateDocument == false)
		{
			echo sumac_getFormClosingHTML($_SESSION[SUMAC_SESSION_REQUEST_ERROR],SUMAC_TEXT_UNKNOWN_FORM);
			return;
		}
		$formName = $formTemplateDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$fformId = $_POST['sumac_filledform'];
		$filledFormElement = $formTemplateDocument->getElementById($fformId);
		$formVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
		$formVersion = ($formVersion == '') ? 1 : ($formVersion + 1);

//echo 'Contact Id = "' . $_SESSION[SUMAC_SESSION_CONTACT_ID] . '"' . '<br />';
//echo 'Form Template Id = "' . $formId . '"' . '<br />';
//echo 'Filled Form Id = "' . $fformId . '"' . '<br />';

		$formFieldElements = $formTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
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
			echo sumac_getFormClosingHTML($_SESSION[SUMAC_SESSION_REQUEST_ERROR],sumac_formatMessage($_SESSION[SUMAC_STR]["AE8"],$formName,$fformId,$formId),$formName);
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
				unset($_SESSION[SUMAC_SESSION_FORM][$formId]);
				echo sumac_getFormClosingHTML($responseMessage,$formName,false);
			}
			else if ($responseStatus == 'bad')
			{
				$formTemplateDocument = sumac_reloadFormTemplateDocument($formId);
				if ($formTemplateDocument == false)
				{
					echo sumac_getFormClosingHTML($_SESSION[SUMAC_SESSION_REQUEST_ERROR],$formName);
					return;
				}
				$filledFormElement = $formTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
				if ($filledFormElement == null)
				{
					echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FILLEDFORM,$formName);
					return;
				}

include_once 'sumac_manage_forms.php';
				if (sumac_execViewOrUpdateForm($responseMessage,'sumac_form_updated.php',$formTemplateDocument) == false)
				{
					echo sumac_getFormClosingHTML($_SESSION[SUMAC_SESSION_REQUEST_ERROR],$formName);
				}
			}
			else	//hopeless
			{
				echo sumac_getFormClosingHTML($responseMessage,$formName);
			}
		}
	}
//or did the user click Cancel?
	else if (isset($_POST['abandonupdate']))
	{
		unset($_SESSION[SUMAC_SESSION_FORM][$formId]);
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
							' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
		echo '<html><body><p>' . SUMAC_INFO_NO_NEED_TO_WAIT . '</p>' . "\n";
		echo '<script type="text/javascript">window.close();</script>' . "\n";
		echo '</body></html>' . "\n";
		exit();
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_form_updated without variable sumac_form set');
	}
	return;

?>