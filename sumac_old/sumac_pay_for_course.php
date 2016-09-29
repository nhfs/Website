<?php
//version511//

include_once 'sumac_constants.php';
include_once 'sumac_http.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_execPayForCourse($paymentStatus,$paymentMadePHP,$accountDocument)
{
	if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']) == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_SESSION_SELECTED . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$paymentCards = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_PAYMENT_CARD);
	if (count($paymentCards) < 1)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_PAYMENT_CARD . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$extrasDocument = sumac_reloadExtrasDocument();
	if ($extrasDocument == false) return false;

	$contactDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($contactDocument == false) return false;

	$html = sumac_getHTMLHeadForPayment();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($extrasDocument,'extras');
	$html .= sumac_addParsedXmlIfDebugging($contactDocument,'contactdetails');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'register') . sumac_getSubtitle($_SESSION[SUMAC_STR]["CH9"]);

	$html .= sumac_getSelectedSessionHTML($organisationDocument,$extrasDocument);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','registration');

	$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
	$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	$minimumcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_MINIMUM_CENTS);
	$minimumcents = 0;
	if (($minimumcentsElements->item(0) != null) && ($minimumcentsElements->item(0)->childNodes->item(0) != null))
		$minimumcents = $minimumcentsElements->item(0)->childNodes->item(0)->nodeValue;

	$html .= sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$organisationDocument,$contactDocument,$paymentCards,$totalcents,$minimumcents);

	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','registration');
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if ($paymentStatus != '') $html .= sumac_getJSToRestoreEnteredValues('buy',SUMAC_ID_FORM_BUY);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForPayment()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getCommonCourseCatalogHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}


function sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$organisationDocument,$contactDocument,$paymentCards,$totalcents,$minimumcents)
{
	$html = '<div id="' . SUMAC_ID_DIV_PAYMENT . '" class="sumac_maintable">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_BUY . '" accept-charset="UTF-8" method="post" action="' . $paymentMadePHP . '">' . "\n";

	$deliveryMechanisms = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$informationSources = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if ($totalcents > 0)
	{
		$instructions = $_SESSION[SUMAC_SESSION_PAYCOURSE];
	}
	else if ((count($deliveryMechanisms) > 0) || (count($informationSources) > 0))
	{
		$instructions = $_SESSION[SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_COURSE_ORDER];
	}
	else	//zero cost and no other details needed
	{
		$instructions = $_SESSION[SUMAC_SESSION_INSTRUCTIONS_COURSE_ORDER];
	}
	$html .= '<table align="center"><tr><td class="sumac_instructions">' . $instructions . '</td></tr></table>' . "\n";

	$html .= '<table class="sumac_payment" style="background:' . $_SESSION[SUMAC_SESSION_PPBGCOLOUR] . ';">' . "\n";

	$html .= '<tr><td>' . "\n";
	$html .= sumac_getHTMLTableForPaymentContactDetails($contactDocument);
	$html .= '</td></tr><tr><td>' . "\n";
	$html .= sumac_getHTMLTableOfPaymentStatus($paymentStatus);
	$html .= '</td></tr><tr><td>' . "\n";
	if ($totalcents > 0)
	{
		$html .= sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$totalcents,$minimumcents);
	}
	else if ((count($deliveryMechanisms) > 0) || (count($informationSources) > 0))
	{
		$html .= sumac_getHTMLFormForZeroCostOrderDetails($organisationDocument);
	}
	else	//zero cost and no other details needed
	{
		$html .= sumac_getHTMLFormForZeroCostOrderWithoutDetails($organisationDocument);
	}
	$html .= '</td></tr>' . "\n";

	$html .= '</table>' . "\n";

	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableForPaymentContactDetails($contactDocument)
{
	$email = $_SESSION[SUMAC_SESSION_EMAILADDRESS];
	$nameElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_NAME);
	$name = ($nameElements->item(0)->childNodes->item(0) != null) ? $nameElements->item(0)->childNodes->item(0)->nodeValue : 'no name';
	if (!$_SESSION[SUMAC_SESSION_USE_PASSWORDS])
	{
		$address = '';
		$phone = '';
	}
	else
	{
		$addressElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_ADDRESS);
		$address = ($addressElements->item(0)->childNodes->item(0) != null) ? $addressElements->item(0)->childNodes->item(0)->nodeValue : '';
		$phoneElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_PHONE);
		$phone = ($phoneElements->item(0)->childNodes->item(0) != null) ? $phoneElements->item(0)->childNodes->item(0)->nodeValue : '';
	}

	$html = '<div id="' . SUMAC_ID_DIV_BUYER . '">' . "\n";
	$html .= '<table border="0" rules="none">' . "\n";

	$html .= '<thead><tr><th align="left">' . $name . '</th><th align="right">  ( ' . $email . ' )</th></tr></thead>' . "\n";
	if ($address != '')	$html .= '<tr><td align="left" colspan="2">' . $address . '</td></tr>' . "\n";
	if ($phone != '')	$html .= '<tr><td align="left" colspan="2">' . $_SESSION[SUMAC_STR]["AU2"] . $phone . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableOfPaymentStatus($paymentStatus)
{
	$html = '<table title="Payment error message" border="0" rules="none" width="100%" align="center">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $paymentStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$totalcents,$minimumcents)
{
	$html = '<table>' . "\n";

	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF14"] . '</td><td><select name="cardtype">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($paymentCards,'',-1) . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF15"] . '</td><td><input type="text" autocomplete="off" name="cardnumber" size="25" maxlength="25" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF16"] . '</td><td><input type="password" autocomplete="off" name="cardsecurity" size="4" maxlength="4" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF12"] . '</td><td><select name="cardexpmonth">' . sumac_getHTMLFormOptionsForExpiryMonth() . '</select>'
			. '<select name="cardexpyear">' . sumac_getHTMLFormOptionsForExpiryYear() . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF13"] . '</td><td><input type="text" name="carduser" size="35" maxlength="35" value="" /></td></tr>' . "\n";

	$checkDM = "";
	//$deliveryMechanisms = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	//if (count($deliveryMechanisms) > 0)
	//{
	//	$deliveryMechanismsInputLabel = sumac_getInputLabel($organisationDocument,SUMAC_VALUE_DELIVERY_MECHANISM,$_SESSION[SUMAC_STR]["CF2"]);
	//	$html .= '<tr><td colspan="2">' . $deliveryMechanismsInputLabel . '</td></tr><tr><td colspan="2"><select name="deliverymechanism">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($deliveryMechanisms,true,-1) . '</select></td></tr>' . "\n";
	//	$checkDM = ",'deliverymechanism'";
	//}
	$informationSources = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if (count($informationSources) > 0)
	{
		$informationSourcesInputLabel = sumac_getInputLabel($organisationDocument,SUMAC_VALUE_INFORMATION_SOURCE,$_SESSION[SUMAC_STR]["CF3"]);
		$html .= '<tr><td colspan="2">' . $informationSourcesInputLabel . '</td></tr><tr><td colspan="2"><select name="informationsource">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($informationSources,true,-1) . '</select></td></tr>' . "\n";
	}

	$totalDollars = sumac_centsToPrintableDollars($totalcents);
	$fullPayDetails = sumac_formatMessage($_SESSION[SUMAC_STR]["CI4"],$_SESSION[SUMAC_STR]["CL8"],$totalDollars);
	$html .= '<tr><td colspan="2" align="left"><b>' . $fullPayDetails . '</b></td></tr>' . "\n";
	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buycourse" value="' . $_SESSION[SUMAC_STR]["CL8"] . '"' .
					' onclick="if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\'' . $checkDM . '])) return false; return true;" />' .
					'<input type="hidden" name="fullcentscharge" value="' . $totalcents . '" />' . '</td></tr>' . "\n";

	if ($minimumcents > 0)
	{
		$minimumDollars = sumac_centsToPrintableDollars($minimumcents);
		$minPayDetails = sumac_formatMessage($_SESSION[SUMAC_STR]["CI4"],$_SESSION[SUMAC_STR]["CL9"],$minimumDollars);
		$html .= '<tr><td colspan="2" align="left"><b>' . $minPayDetails . '</b></td></tr>' . "\n";
		$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buycourse" value="' . $_SESSION[SUMAC_STR]["CL9"] . '"' .
						' onclick="if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\'' . $checkDM . '])) return false; return true;" />' .
						'<input type="hidden" name="mincentscharge" value="' . $minimumcents . '" />' . '</td></tr>' . "\n";
	}
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForZeroCostOrderDetails($organisationDocument)
{
	$html = '<table>' . "\n";

	$deliveryMechanisms = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$checkDM = "";
	if (count($deliveryMechanisms) > 0)
	{
		$deliveryMechanismsInputLabel = sumac_getInputLabel($organisationDocument,SUMAC_VALUE_DELIVERY_MECHANISM,$_SESSION[SUMAC_STR]["CF2"]);
		$html .= '<tr><td colspan="2">' . $deliveryMechanismsInputLabel . '</td></tr><tr><td colspan="2"><select name="deliverymechanism">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($deliveryMechanisms,true,-1) . '</select></td></tr>' . "\n";
		$checkDM = "'deliverymechanism'";
	}
	$informationSources = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if (count($informationSources) > 0)
	{
		$informationSourcesInputLabel = sumac_getInputLabel($organisationDocument,SUMAC_VALUE_INFORMATION_SOURCE,$_SESSION[SUMAC_STR]["CF3"]);
		$html .= '<tr><td colspan="2">' . $informationSourcesInputLabel . '</td></tr><tr><td colspan="2"><select name="informationsource">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($informationSources,true,-1) . '</select></td></tr>' . "\n";
	}
	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buy" value="' . $_SESSION[SUMAC_STR]["CL25"] . '"' .
					' onclick="if (sumac_checknamedfields([' . $checkDM . '])) return false; return true;" />' .
					'<input type="hidden" name="cardtype" value="" />' .
					'<input type="hidden" name="cardnumber" value="" />' .
					'<input type="hidden" name="cardsecurity" value="" />' .
					'<input type="hidden" name="cardexpmonth" value="" />' .
					'<input type="hidden" name="cardexpyear" value="" />' .
					'<input type="hidden" name="carduser" value="" />' .
					'<input type="hidden" name="centscharge" value="0" />' .
					'</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForZeroCostOrderWithoutDetails($organisationDocument)
{
	$html = '<table>' . "\n";
	$html .= '<tr><td align="left"><input type="submit" name="buy" value="' . $_SESSION[SUMAC_STR]["CL25"] . '" />' .
					'<input type="hidden" name="cardtype" value="" />' .
					'<input type="hidden" name="cardnumber" value="" />' .
					'<input type="hidden" name="cardsecurity" value="" />' .
					'<input type="hidden" name="cardexpmonth" value="" />' .
					'<input type="hidden" name="cardexpyear" value="" />' .
					'<input type="hidden" name="carduser" value="" />' .
					'<input type="hidden" name="centscharge" value="0" />' .
					'</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getInputLabel($organisationDocument,$context,$defaultLabel)
{
	$inputLabelElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_INPUT_LABEL);
	for ($i = 0; $i < $inputLabelElements->length; $i++)
	{
		$inputLabelElement = $inputLabelElements->item($i);
		if ($inputLabelElement->getAttribute(SUMAC_ATTRIBUTE_CONTEXT) == $context)
		{
			if ($inputLabelElement->childNodes->item(0) != null) return $inputLabelElement->childNodes->item(0)->nodeValue;
		}
	}
	return $defaultLabel;
}

?>
