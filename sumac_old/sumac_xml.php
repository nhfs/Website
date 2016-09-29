<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_http.php';
include_once 'sumac_utilities.php';

function sumac_reloadOrganisationDocument()
{
	if (!isset($_SESSION[SUMAC_SESSION_ORGANISATION]))
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_ORGANISATION_DATA . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$organisationDocument = sumac_loadXMLDoc($_SESSION[SUMAC_SESSION_ORGANISATION]);
	if ($organisationDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_ORGANISATION_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (sumac_importXMLElements($organisationDocument,$organisationDocument) == false) return false;
//removeCommentsAndDTDs($organisationDocument);
	if (!$organisationDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_ORGANISATION_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($organisationDocument);
	if (sumac_handleResponseXMLDocument($organisationDocument,SUMAC_ELEMENT_ORGANISATION) == false) return false;
	else if (sumac_isExpectedXMLDocument($organisationDocument,SUMAC_ELEMENT_ORGANISATION) == false) return false;

	$_SESSION[SUMAC_SESSION_ORGANISATION_DOC] = $organisationDocument;
	$_SESSION[SUMAC_SESSION_ORGANISATION_NAME] = $organisationDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	return $organisationDocument;
}

function sumac_loadLoginAccountDocument($source,$port,$xml,$request)
{
	$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . $request;
	if ($request != SUMAC_REQUEST_PARAM_ADDUSER)
	{
		$variableValues .= '&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_CHUNKS .
							'&' . SUMAC_REQUEST_KEYWORD_INCLUDE . '=' . SUMAC_REQUEST_INCLUDE_MEMBERSHIP;
	}
	$variableValues .= $_SESSION[SUMAC_SESSION_WEBSITE_DATA];

	$accountData = sumac_postEncryptedXMLData($source,$port, '?' . $variableValues,$xml,'sumac.pem');
	if ($accountData == false) return false;

	sumac_echoRawXMLIfDebugging($accountData,$request);

	$_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS] = $accountData;

	$accountDocument = sumac_loadXMLDoc($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
	if ($accountDocument == false)
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_NO_ACCOUNT_DETAILS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$accountDocument->validate())
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_CONTACT_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($accountDocument);
	if (sumac_handleResponseXMLDocument($accountDocument,SUMAC_ELEMENT_ACCOUNTDETAILS) == false)
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		return false;
	}
	else if (sumac_isExpectedXMLDocument($accountDocument,SUMAC_ELEMENT_ACCOUNTDETAILS) == false)
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		return false;
	}

//from these account details, username is displayed on every screen and contact-id is used for every request
	$idElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_CONTACT_ID);
	if ($idElements->item(0)->childNodes->item(0) != null) $_SESSION[SUMAC_SESSION_CONTACT_ID] = $idElements->item(0)->childNodes->item(0)->nodeValue;
	else if (isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) unset($_SESSION[SUMAC_SESSION_CONTACT_ID]);
	$nameElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_NAME);
	$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] = ($nameElements->item(0)->childNodes->item(0) != null) ? $nameElements->item(0)->childNodes->item(0)->nodeValue : $_SESSION[SUMAC_STR]["AU3"];
	return $accountDocument;
}

function sumac_reloadLoginAccountDocument()
{
	if (!isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_ACCOUNT_DETAILS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$accountDocument = sumac_loadXMLDoc($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
	if ($accountDocument == false)
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_NO_ACCOUNT_DETAILS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$accountDocument->validate())
	{
		unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_CONTACT_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($accountDocument);
	return $accountDocument;
}

function sumac_loadSeatsalesDocument($source,$port,$event)
{
	$variableValues1 = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_SEATSALES . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$variableValues2 = SUMAC_REQUEST_KEYWORD_EVENT . '=' . $event;
	$seatsalesData = sumac_getXMLData($source,$port,'?' . $variableValues1 . '&' . $variableValues2);
	if ($seatsalesData == false) return false;

	sumac_echoRawXMLIfDebugging($seatsalesData,SUMAC_REQUEST_PARAM_SEATSALES);

	$seatsalesDocument = sumac_loadXMLDoc($seatsalesData);
	if ($seatsalesDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_SEATSALES_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$seatsalesDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_SEATSALES_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($seatsalesDocument);
	if (sumac_handleResponseXMLDocument($seatsalesDocument,SUMAC_ELEMENT_SEATSALES) == false) return false;
	else if (sumac_isExpectedXMLDocument($seatsalesDocument,SUMAC_ELEMENT_SEATSALES) == false) return false;

	$_SESSION[SUMAC_SESSION_SEATSALES_DOC] = $seatsalesDocument;
	return $seatsalesDocument;
}

function sumac_loadExtrasDocument($source,$port,$extrasparam,$keyword,$xml)
{
	$orderdata = $keyword . '=' . urlencode($xml);
	$variableValues1 = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . $extrasparam . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$variableValues2 = $orderdata;
	$extrasData = sumac_getXMLData($source,$port,'?' . $variableValues1 . '&' . $variableValues2);
	if ($extrasData == false) return false;

	sumac_echoRawXMLIfDebugging($extrasData,$extrasparam);

	$_SESSION[SUMAC_SESSION_EXTRAS] = $extrasData;

	$extrasDocument = sumac_loadXMLDoc($extrasData);
	if ($extrasDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_EXTRAS_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$extrasDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_EXTRAS_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($extrasDocument);
	if (sumac_handleResponseXMLDocument($extrasDocument,SUMAC_ELEMENT_EXTRAS) == false) return false;
	else if (sumac_isExpectedXMLDocument($extrasDocument,SUMAC_ELEMENT_EXTRAS) == false) return false;

	$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
	$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	return $extrasDocument;
}

function sumac_reloadExtrasDocument()
{
	if (!isset($_SESSION[SUMAC_SESSION_EXTRAS]))
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_EXTRAS_DATA . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$extrasDocument = sumac_loadXMLDoc($_SESSION[SUMAC_SESSION_EXTRAS]);
	if ($extrasDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_EXTRAS_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$extrasDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_EXTRAS_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($extrasDocument);
	return $extrasDocument;
}

function sumac_loadFormTemplateDocument($source,$port,$request,$form)
{
	$variableValues1 = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . $request . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$variableValues2 = SUMAC_REQUEST_KEYWORD_FORM . '=' . $form;
	$variableValues3 = SUMAC_REQUEST_KEYWORD_CONTACTID . '=' . $_SESSION[SUMAC_SESSION_CONTACT_ID];
	$formTemplateData = sumac_getXMLData($source,$port,'?' . $variableValues1 . '&' . $variableValues2 . '&' . $variableValues3);
	if ($formTemplateData == false) return false;

	sumac_echoRawXMLIfDebugging($formTemplateData,$request);

	$formTemplateDocument = sumac_loadXMLDoc($formTemplateData);
	if ($formTemplateDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_FORMTEMPLATE_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$formTemplateDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_FORMTEMPLATE_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($formTemplateDocument);
	if (sumac_handleResponseXMLDocument($formTemplateDocument,SUMAC_ELEMENT_FORMTEMPLATE) == false) return false;
	else if (sumac_isExpectedXMLDocument($formTemplateDocument,SUMAC_ELEMENT_FORMTEMPLATE) == false) return false;

	$formId = $formTemplateDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$_SESSION[SUMAC_SESSION_FORM][$formId] = $formTemplateData;

	return $formTemplateDocument;
}

function sumac_reloadFormTemplateDocument($formId)
{
	if (!isset($_SESSION[SUMAC_SESSION_FORM][$formId]))
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_NO_FORMTEMPLATE_DATA,$formId) . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$formTemplateDocument = sumac_loadXMLDoc($_SESSION[SUMAC_SESSION_FORM][$formId]);
	if ($formTemplateDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_FORMTEMPLATE_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$formTemplateDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_FORMTEMPLATE_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($formTemplateDocument);
	return $formTemplateDocument;
}

function sumac_loadDirectoryEntriesDocument($source,$port,$xml)
{
	$orderdata = SUMAC_REQUEST_KEYWORD_SELECTORS . '=' . urlencode($xml);
	$variableValues1 = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_DIRECTORYENTRIES . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$variableValues2 = $orderdata;
	$directoryEntriesData = sumac_getXMLData($source,$port,'?' . $variableValues1 . '&' . $variableValues2);
	if ($directoryEntriesData == false) return false;

	sumac_echoRawXMLIfDebugging($directoryEntriesData,SUMAC_REQUEST_PARAM_DIRECTORYENTRIES);

	$directoryEntriesDocument = sumac_loadXMLDoc($directoryEntriesData);
	if ($directoryEntriesDocument == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= SUMAC_ERROR_DIRECTORY_ENTRIES_DATA_NOT_XML . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$directoryEntriesDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_DIRECTORY_ENTRIES_NOT_VALID . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($directoryEntriesDocument);
	if (sumac_handleResponseXMLDocument($directoryEntriesDocument,SUMAC_ELEMENT_DIRECTORY_ENTRIES) == false) return false;
	else if (sumac_isExpectedXMLDocument($directoryEntriesDocument,SUMAC_ELEMENT_DIRECTORY_ENTRIES) == false) return false;

	return $directoryEntriesDocument;
}

function sumac_postRequestAndLoadResponseDocument($source,$port,$request,$xml)
{
	$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . $request . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
	$responseData = sumac_postEncryptedXMLData($source,$port, '?' . $variableValues,$xml,'sumac.pem');
	if ($responseData == false) return false;

	sumac_echoRawXMLIfDebugging($responseData,$request);

	$responseDocument = sumac_loadXMLDoc($responseData);
	if ($responseData == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] .= sumac_formatMessage(SUMAC_ERROR_RESPONSE_DATA_NOT_XML,$request) . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	if (!$responseDocument->validate())
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_RESPONSE_NOT_VALID,$request) . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	sumac_decodeDocumentContent($responseDocument);
	if (sumac_isExpectedXMLDocument($responseDocument,SUMAC_ELEMENT_RESPONSE) == false) return false;

	$responseAccountdetailsElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_ACCOUNTDETAILS);
	$responseFormtemplateElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE);
	if ($responseAccountdetailsElements->length == 1) $_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS] = $responseData;
	else if ($responseFormtemplateElements->length == 1)
	{
		$formId = $responseFormtemplateElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$_SESSION[SUMAC_SESSION_FORM][$formId] = $responseData;
	}
	else
	{
		$responseAccountdetailsErrorElements = $responseDocument->getElementsByTagName(SUMAC_ELEMENT_ACCOUNTDETAILSERROR);
		if ($responseAccountdetailsErrorElements->length == 1)
		{
//the refresh of account details failed; we must force the user to login again
			if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS])) unset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]);
			if (isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) unset($_SESSION[SUMAC_SESSION_CONTACT_ID]);
			$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] = $_SESSION[SUMAC_STR]["AU3"];
		}
	}
	return $responseDocument;
}

function sumac_handleResponseXMLDocument($document,$docname)
{
	if ($document->documentElement->tagName == SUMAC_ELEMENT_RESPONSE) //embedded error message
	{
		$responseStatus = $document->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
		if ($responseStatus == 'bad')
		{
			$defaultMessage = sumac_formatMessage($_SESSION[SUMAC_STR]["AE10"],$docname);
			$responseMessageElements = $document->getElementsByTagName(SUMAC_ELEMENT_MESSAGE);
			if ($responseMessageElements->length == 0) $responseMessage = $defaultMessage;
			else $responseMessage = ($responseMessageElements->item(0)->childNodes->item(0) != null)
									? $responseMessageElements->item(0)->childNodes->item(0)->nodeValue
									: $defaultMessage;
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = $responseMessage;
			return false;
		}
	}
	return true;
}

function sumac_isExpectedXMLDocument($document,$docname)
{
	if ($document->documentElement->tagName != $docname)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_XML_NOT_AS_EXPECTED,$docname) . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	return true;
}

function sumac_loadXMLDoc($xmldata)
{
//echo $xmldata . '<br />';
	$doc = new DOMDocument();
	//$result = $doc->loadXML(htmlspecialchars($xmldata));
	$result = $doc->loadXML($xmldata);
	if ($result == false)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = $_SESSION[SUMAC_STR]["AE9"];
		return false;
	}
	return $doc;
}

function sumac_loadXMLDocFromFile($xmlfile)
{
	$data = null;
	if (isset($_SESSION['sumac_' . $xmlfile]))
	{
		$data = $_SESSION['sumac_' . $xmlfile];
	}
	else
	{
		$realPath = realpath($xmlfile);
		if ($realPath == false)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_XML_NO_FILE,$xmlfile);
			return false;
		}
		$data = file_get_contents($realPath);
		if ($data == false)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_XML_FILE_LOAD_FAILED,$xmlfile);
			return false;
		}
		$_SESSION['sumac_' . $xmlfile] = $data;
	}
	$doc = sumac_loadXMLDoc($data);
	return $doc;
}

function sumac_removeCommentsAndDTDs($parent)
{
	$childNodes = $parent->childNodes;
	if ($childNodes)
	{
		for ($i = ($childNodes->length - 1); $i >= 0; $i--)
		{
			if ($childNodes->item($i) instanceof DOMDocumentType) $parent->removeChild($childNodes->item($i));
			else if ($childNodes->item($i) instanceof DOMComment) $parent->removeChild($childNodes->item($i));
			else if ($childNodes->item($i) instanceof DOMNode) sumac_removeCommentsAndDTDs($childNodes->item($i));
		}
	}
}

function sumac_importXMLElements($parent,$document)
{
	$childNodes = $parent->childNodes;
	if ($childNodes)
	{
		for ($i = ($childNodes->length - 1); $i >= 0; $i--)
		{
			if ($childNodes->item($i)->nodeType != XML_ELEMENT_NODE) continue;//we only want the elements ...
			$xmldoc = $childNodes->item($i)->getAttribute('xmldoc');//... with the xmldoc attribute
			if ($xmldoc)
			{
//echo 'xmldoc ' . $xmldoc . '<br />';
				$childDoc = sumac_loadXMLDocFromFile($xmldoc);
				if ($childDoc == false) return false;
				$newChild = $document->importNode($childDoc->documentElement,true);
				$parent->replaceChild($newChild,$childNodes->item($i));
				if ($newChild instanceof DOMNode) if (sumac_importXMLElements($newChild,$document) == false) return false;
			}
			else if ($childNodes->item($i) instanceof DOMNode) if (sumac_importXMLElements($childNodes->item($i),$document) == false) return false;
		}
	}
	return true;
}

function sumac_listXMLElementsAndAttributes($parent,$indent)
{
	$childNodes = $parent->childNodes;
	$newindent = $indent + 16;
	if ($childNodes)
	{
		for ($i = 0; $i < $childNodes->length; $i++)
		{
			if ($childNodes->item($i)->nodeType != XML_ELEMENT_NODE) continue;//first we only want the elements
			echo '<p style="{text-indent:' . $newindent . 'px;}">' . 'ELEMENT: ' . $childNodes->item($i)->tagName;
			$text = ($childNodes->item($i)->childNodes->item(0) != null) ? $childNodes->item($i)->childNodes->item(0)->nodeValue : '';
			if (strlen(rtrim($text)) > 0) echo ' ...TEXT: ' . $text;
			if ($childNodes->item($i)->hasAttributes()) sumac_listXMLAttributes($childNodes->item($i)->attributes);
			echo '</p>';
			sumac_listXMLElementsAndAttributes($childNodes->item($i),$newindent);
		}
	}
}

function sumac_listXMLAttributes($attributes)
{
	if ($attributes)
	{
		echo ' ... ATTRS: ';
		foreach ($attributes as $index=>$attr)
		{
			echo $attr->name . '="' . $attr->value . '" ';
			if ($attr->isId()) echo '[ID] ';
		}
	}
}

function sumac_getXMLElementsAndAttributesAsHTML($parent,$indent)
{
	$html = '';
	$childNodes = $parent->childNodes;
	$newindent = $indent + 1;
	if ($childNodes)
	{
		for ($i = 0; $i < $childNodes->length; $i++)
		{
			if ($childNodes->item($i)->nodeType != XML_ELEMENT_NODE) continue;//first we only want the elements
			for ($j = 0; $j < $indent; $j++) $html .= '...';
			$html .= $childNodes->item($i)->tagName;
			$text = ($childNodes->item($i)->childNodes->item(0) != null) ? $childNodes->item($i)->childNodes->item(0)->nodeValue : '';
			if (strlen(rtrim($text)) > 0) $html .=  ' "' . $text .'"';
			if ($childNodes->item($i)->hasAttributes()) $html .= sumac_getXMLAttributesAsHTML($childNodes->item($i)->attributes);
			$html .= '<br />';
			$html .= sumac_getXMLElementsAndAttributesAsHTML($childNodes->item($i),$newindent);
		}
	}
	return $html;
}

function sumac_getXMLAttributesAsHTML($attributes)
{
	$html = '';
	if ($attributes)
	{
		$html .= ' (';
		foreach ($attributes as $index=>$attr)
		{
			$html .= $attr->name . ($attr->isId() ? '[I]' : '') . '="' . $attr->value . '" ';
		}
		$html .= ')';
	}
	return $html;
}

function sumac_decodeDocumentContent($document)
{
//first decode any attributes of the top-level element
	$documentContent = "";
	$topNode = $document->documentElement;
	$nodeAtts = $topNode->attributes;
	foreach ($nodeAtts as $att)
	{
		$decoded = urldecode($att->nodeValue);
		$decoded = iconv('UTF-8','ISO-8859-1//TRANSLIT',$decoded);
		if ($att->nodeValue != $decoded)
		{
//echo 'top element attr: ' . $att->nodeValue . ' decoded to ' . $decoded. '<br />';
			$decoded = str_replace('&','&amp;',$decoded);
			$att->nodeValue = $decoded;
			$documentContent .= $decoded . '<br />';
		}
	}
//then decode any text and any attributes for the lower levels
	$documentContent .= sumac_decodeNodeChildrenContent($topNode);

//echo '<br />Decoded document: ' . $documentContent . '<br />';
}

function sumac_decodeNodeChildrenContent($node)
{
	$decodedNodeContent = "";
	$nodeList = $node->childNodes;
	for ($i = 0; $i < $nodeList->length; $i++)
	{
		$childNode = $nodeList->item($i);
		$childNodeName = $childNode->nodeName;
		$childNodeValue = $childNode->nodeValue;

		if ($childNode->nodeType == XML_TEXT_NODE)
		{
			$decoded = urldecode($childNodeValue);
			$decoded = iconv('UTF-8','ISO-8859-1//TRANSLIT',$decoded);
			if ($childNodeValue != $decoded)
			{
//echo 'text node: ' . $childNode->nodeValue . ' decoded to ' . $decoded. '<br />';
				$decoded = str_replace('&','&amp;',$decoded);
				$childNode->nodeValue = $decoded;
				$decodedNodeContent .=  $decoded . '<br />';
			}
		}
		else if ($childNode->nodeType == XML_ELEMENT_NODE)
		{
			$childAtts = $childNode->attributes;
			foreach ($childAtts as $att)
			{
				$decoded = urldecode($att->nodeValue);
				$decoded = iconv('UTF-8','ISO-8859-1//TRANSLIT',$decoded);
				if ($att->nodeValue != $decoded)
				{
//echo 'lower element attr: ' . $att->nodeValue . ' decoded to ' . $decoded. '<br />';
					$decoded = str_replace('&','&amp;',$decoded);
					$att->nodeValue = $decoded;
					$decodedNodeContent .= $decoded . '<br />';
				}
			}
			$decodedNodeContent .= sumac_decodeNodeChildrenContent($childNode);
		}
	}
	return $decodedNodeContent;
}
?>
