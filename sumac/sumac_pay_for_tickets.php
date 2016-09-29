<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_http.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_execPayForTickets($paymentStatus,$paymentMadePHP,$accountDocument)
{
	if (sumac_countTicketOrdersInBasket() <= 0)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_TICKETS_IN_BASKET . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$theatreDocument = sumac_reloadOrganisationDocument();
	if ($theatreDocument == false) return false;

	if (count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) < 1)
	{
//this cannot normally happen because the DTD insists that:
//	a theatre has at least one production_grouping, and
//	a production_grouping has at least one production, and
//	a production has at least one event
//so this would mean there was no theatre element - which would have caused an error in sumac_select_event
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_EVENTS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$paymentCards = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_PAYMENT_CARD);
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

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'payment') . sumac_getSubtitle();

	$html .= sumac_getBasketAndExtrasHTML($theatreDocument,$extrasDocument,true);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_top_ticketing_navbar",'',true,false);

	$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
	$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';

	$html .= sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$theatreDocument,$contactDocument,$paymentCards,$totalcents);

	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_bottom_ticketing_navbar",'',true,false);
//	$html .= sumac_getHTMLBodyForActions('ok','hide','hide','ok','ok',$paymentMadePHP,true);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_TICKETING) . sumac_getUserHTML(SUMAC_USER_BOTTOM);

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


function sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$theatreDocument,$contactDocument,$paymentCards,$totalcents)
{
	$html = '<div id="' . SUMAC_ID_DIV_PAYMENT . '" class="sumac_maintable">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_BUY . '" accept-charset="UTF-8" method="post" action="' . $paymentMadePHP . '">' . "\n";

	$deliveryMechanisms = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$informationSources = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if ($totalcents > 0)
	{
		$instructions = $_SESSION[SUMAC_SESSION_INSTRUCTIONS_BUY];
	}
	else if ((count($deliveryMechanisms) > 0) || (count($informationSources) > 0))
	{
		$instructions = $_SESSION[SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_ORDER];
	}
	else	//zero cost and no other details needed
	{
		$instructions = $_SESSION[SUMAC_SESSION_INSTRUCTIONS_ORDER];
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
		$html .= sumac_getHTMLFormForPaymentCardDetails($theatreDocument,$paymentCards,$totalcents);
	}
	else if ((count($deliveryMechanisms) > 0) || (count($informationSources) > 0) || ($_SESSION[SUMAC_SESSION_INCPAYNOTE]))
	{
		$html .= sumac_getHTMLFormForZeroCostOrderDetails($theatreDocument);
	}
	else	//zero cost and no other details needed
	{
		$html .= sumac_getHTMLFormForZeroCostOrderWithoutDetails($theatreDocument);
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
	if (sumac_no_user_password(SUMAC_PACKAGE_TICKETING))
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

function sumac_getHTMLFormForPaymentCardDetails($theatreDocument,$paymentCards,$totalcents)
{
	$html = '<table>' . "\n";

	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF14"] . '</td><td><select name="cardtype">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($paymentCards,'',-1) . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF15"] . '</td><td><input type="text" autocomplete="off" name="cardnumber" size="25" maxlength="25" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF16"] . '</td><td><input type="password" autocomplete="off" name="cardsecurity" size="4" maxlength="4" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF12"] . '</td><td><select name="cardexpmonth">' . sumac_getHTMLFormOptionsForExpiryMonth() . '</select>'
			. '<select name="cardexpyear">' . sumac_getHTMLFormOptionsForExpiryYear() . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF13"] . '</td><td><input type="text" name="carduser" size="35" maxlength="35" value="" /></td></tr>' . "\n";

	$deliveryMechanisms = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$checkDM = "";
	if (count($deliveryMechanisms) > 0)
	{
		$deliveryMechanismsInputLabel = sumac_getInputLabel($theatreDocument,SUMAC_VALUE_DELIVERY_MECHANISM,$_SESSION[SUMAC_STR]["TF2"]);
		$html .= '<tr><td colspan="2">' . $deliveryMechanismsInputLabel . '</td></tr><tr><td colspan="2"><select name="deliverymechanism">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($deliveryMechanisms,true,-1) . '</select></td></tr>' . "\n";
		$checkDM = ",'deliverymechanism'";
	}
	$informationSources = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if (count($informationSources) > 0)
	{
		$informationSourcesInputLabel = sumac_getInputLabel($theatreDocument,SUMAC_VALUE_INFORMATION_SOURCE,$_SESSION[SUMAC_STR]["TF3"]);
		$html .= '<tr><td colspan="2">' . $informationSourcesInputLabel . '</td></tr><tr><td colspan="2"><select name="informationsource">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($informationSources,true,-1) . '</select></td></tr>' . "\n";
	}
	if ($_SESSION[SUMAC_SESSION_INCPAYNOTE])
	{
		$html .= '<tr><td colspan="2">' . $_SESSION[SUMAC_SESSION_PAYNOTETEXT] . '</td></tr><tr><td colspan="2"><textarea class="sumac_note_textarea" name="payeenote"></textarea></td></tr>' . "\n";
	}

	$totalDollars = sumac_centsToPrintableDollars($totalcents);
	$payDetails = sumac_formatMessage($_SESSION[SUMAC_STR]["TI15"],$_SESSION[SUMAC_STR]["TL1"],$totalDollars);
	$html .= '<tr><td colspan="2" align="left"><b>' . $payDetails . '</b></td></tr>' . "\n";
	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buy" value="' . $_SESSION[SUMAC_STR]["TL1"] . '"' .
					' onclick="if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\'' . $checkDM . '])) return false; return true;" />' .
					'<input type="hidden" name="centscharge" value="' . $totalcents . '" />' . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForZeroCostOrderDetails($theatreDocument)
{
	$html = '<table>' . "\n";

	$deliveryMechanisms = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$checkDM = "";
	if (count($deliveryMechanisms) > 0)
	{
		$deliveryMechanismsInputLabel = sumac_getInputLabel($theatreDocument,SUMAC_VALUE_DELIVERY_MECHANISM,$_SESSION[SUMAC_STR]["TF2"]);
		$html .= '<tr><td colspan="2">' . $deliveryMechanismsInputLabel . '</td></tr><tr><td colspan="2"><select name="deliverymechanism">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($deliveryMechanisms,true,-1) . '</select></td></tr>' . "\n";
		$checkDM = "'deliverymechanism'";
	}
	$informationSources = sumac_getElementValuesAsArray($theatreDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if (count($informationSources) > 0)
	{
		$informationSourcesInputLabel = sumac_getInputLabel($theatreDocument,SUMAC_VALUE_INFORMATION_SOURCE,$_SESSION[SUMAC_STR]["TF3"]);
		$html .= '<tr><td colspan="2">' . $informationSourcesInputLabel . '</td></tr><tr><td colspan="2"><select name="informationsource">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($informationSources,true,-1) . '</select></td></tr>' . "\n";
	}
	if ($_SESSION[SUMAC_SESSION_INCPAYNOTE])
	{
		$html .= '<tr><td colspan="2">' . $_SESSION[SUMAC_SESSION_PAYNOTETEXT] . '</td></tr><tr><td colspan="2"><textarea class="sumac_note_textarea" name="payeenote"></textarea></td></tr>' . "\n";
	}
	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buy" value="' . $_SESSION[SUMAC_STR]["TL6"] . '"' .
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

function sumac_getHTMLFormForZeroCostOrderWithoutDetails($theatreDocument)
{
	$html = '<table>' . "\n";
	$html .= '<tr><td align="left"><input type="submit" name="buy" value="' . $_SESSION[SUMAC_STR]["TL6"] . '" />' .
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

function sumac_getInputLabel($theatreDocument,$context,$defaultLabel)
{
	$inputLabelElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_INPUT_LABEL);
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
