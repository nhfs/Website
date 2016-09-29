<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_http.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execPayWithoutPurchase($paymentStatus,$paymentMadePHP,$accountDocument)
{
	$contactDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($contactDocument == false) return false;

	$financialAccountElement = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_FINANCIALACCOUNT)->item(0);
	if ($financialAccountElement == false)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_FINANCIAL_ACCOUNT . $_SESSION[SUMAC_STR]["AE5"];
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


	$html = sumac_getHTMLHeadForPayment();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($contactDocument,'contactdetails');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'payment') . sumac_getSubtitle($_SESSION[SUMAC_STR]["CH7"]);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','payment');

	$finalcents = $financialAccountElement->getAttribute(SUMAC_ATTRIBUTE_FINAL_CENTS);

	$html .= sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$organisationDocument,$contactDocument,$paymentCards,$finalcents);

	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','payment');
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_COURSES) . sumac_getUserHTML(SUMAC_USER_BOTTOM);

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


function sumac_getHTMLFormForPayment($paymentStatus,$paymentMadePHP,$organisationDocument,$contactDocument,$paymentCards,$finalcents)
{
	$html = '<div id="' . SUMAC_ID_DIV_PAYMENT . '" class="sumac_maintable">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_BUY . '" accept-charset="UTF-8" method="post" action="' . $paymentMadePHP . '">' . "\n";

	$instructions = $_SESSION[SUMAC_SESSION_PAYACCOUNT];
	$html .= '<table align="center"><tr><td class="sumac_instructions">' . $instructions . '</td></tr></table>' . "\n";

	$html .= '<table class="sumac_payment" style="background:' . $_SESSION[SUMAC_SESSION_PPBGCOLOUR] . ';">' . "\n";

	$html .= '<tr><td>' . "\n";
	$html .= sumac_getHTMLTableForPaymentContactDetails($contactDocument);
	$html .= '</td></tr><tr><td>' . "\n";
	$html .= sumac_getHTMLTableOfPaymentStatus($paymentStatus);
	$html .= '</td></tr><tr><td>' . "\n";
	$html .= sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$finalcents);
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
	if (sumac_no_user_password(SUMAC_PACKAGE_COURSES))
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

function sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$finalcents)
{
	$html = '<table>' . "\n";

	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF14"] . '</td><td><select name="cardtype">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($paymentCards,'',-1) . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF15"] . '</td><td><input type="text" autocomplete="off" name="cardnumber" size="25" maxlength="25" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF16"] . '</td><td><input type="password" autocomplete="off" name="cardsecurity" size="4" maxlength="4" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF12"] . '</td><td><select name="cardexpmonth">' . sumac_getHTMLFormOptionsForExpiryMonth() . '</select>'
			. '<select name="cardexpyear">' . sumac_getHTMLFormOptionsForExpiryYear() . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF13"] . '</td><td><input type="text" name="carduser" size="35" maxlength="35" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["CF1"] . '</td><td>' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '<input type="text" name="amountpaid" size="6" maxlength="35" value="" /></td></tr>' . "\n";

	$owingOrCredit = ($finalcents < 0) ? $_SESSION[SUMAC_STR]["CU4"] : $_SESSION[SUMAC_STR]["CU5"];
	$finalDollars = ($finalcents < 0) ? sumac_centsToPrintableDollars(0 - $finalcents) : sumac_centsToPrintableDollars($finalcents);
	$html .= '<tr><td colspan="2"><b>' . $owingOrCredit . $finalDollars . '</b></td></tr>';

	$html .= '<tr><td colspan="2" align="left"><b>' . sumac_formatMessage($_SESSION[SUMAC_STR]["CI5"],$_SESSION[SUMAC_STR]["CL7"]) . '</b></td></tr>' . "\n";

	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="buynothing" value="' . $_SESSION[SUMAC_STR]["CL7"] . '"' .
					' onclick="if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\',\'amountpaid\'])) return false; return true;" />' . '</td></tr>' . "\n";

	$html .= '</table>' . "\n";
	return $html;
}

?>
