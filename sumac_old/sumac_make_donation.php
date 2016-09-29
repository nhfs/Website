<?php
//version512//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execDonate($donationStatus,$donationMadePHP,$accountDocument)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$paymentCards = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_PAYMENT_CARD);
//there must be at least one way to make payment (and the DTD doesnt enforce it)
	if (count($paymentCards) < 1)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_PAYMENT_CARD . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$funds = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_FUND);
//there may or may not be a choice of funds - usually not - so we proceed in either case
	$deductionDays = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DEDUCTION_DAY);
//there may or may not be a choice of deduction days - usually not - so we proceed in either case

	$contactDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($contactDocument == false) return false;

	$html = sumac_getHTMLHeadForDonation();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($contactDocument,'contactdetails');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'payment') . sumac_getSubtitle();

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= sumac_getHTMLFormForDonation($donationStatus,$donationMadePHP,$organisationDocument,$contactDocument,
											$paymentCards,$funds,$deductionDays);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
//	$html .= sumac_getHTMLBodyForActions('ok',$donationMadePHP,true);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if ($donationStatus != '') $html .= sumac_getJSToRestoreEnteredValues('donate',SUMAC_ID_FORM_DONATE);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForDonation()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements(true);
	$html .= sumac_getDonationHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getDonationHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}

function sumac_getDonationHTMLStyleElements()
{
	return <<<EOCSS

input[type="submit"].sumac_donation_button
{
	border:3px outset;border-radius:7px;-moz-border-radius:7px;
	background:lightgrey;color:black;
	padding:0px 0px;text-align:center;
	max-width:400px; display:block;
}
table.sumac_payment_section {background-color:aliceblue;border:2px solid lightblue;border-radius:5px;}
caption.sumac_payment_section_caption {text-align:left;font-style:italic;}
EOCSS;
}

function sumac_getDonationHTMLScriptFunctions()
{
	return <<<EOJS

	function sumac_set_frequency(fcode)
	{
		document.getElementById("sumac_donation_frequency").value = fcode;
	}

EOJS;
}

function sumac_getHTMLFormForDonation($donationStatus,$donationMadePHP,$organisationDocument,$contactDocument,$paymentCards,
								$funds,$deductionDays)
{
	$html = '<div id="' . SUMAC_ID_DIV_DONATION . '" class="sumac_maintable">' . "\n";

	$html .= '<form id="' . SUMAC_ID_FORM_DONATE . '" accept-charset="UTF-8" method="post" action="' . $donationMadePHP . '">' . "\n";

	$monthly = (substr($_SESSION[SUMAC_SESSION_FREQUENCY],0,1) == 'M');
	$combined = (substr($_SESSION[SUMAC_SESSION_FREQUENCY],0,1) == 'C');
	$onceoff = (substr($_SESSION[SUMAC_SESSION_FREQUENCY],0,1) == 'O');
	if (!$combined && !$monthly && !$onceoff)
	{
		if (count($deductionDays) > 0) $combined = true;	//default is 'once' if there are no deduction days
	}

	$instructionsWithInsert = sumac_formatMessage($_SESSION[SUMAC_SESSION_INSTRUCTIONS_DONATE],$_SESSION[SUMAC_SESSION_PAY_BUTTON]);
	if ($monthly && $_SESSION[SUMAC_SESSION_SEPARATE_MONTHLY_INSTRUCTIONS])
		$instructionsWithInsert = sumac_formatMessage($_SESSION[SUMAC_STR]["DI11"],$_SESSION[SUMAC_STR]["DL2"]);
	else if ($combined)
		$instructionsWithInsert = sumac_formatMessage($_SESSION[SUMAC_STR]["DI12"],$_SESSION[SUMAC_SESSION_PAY_BUTTON],$_SESSION[SUMAC_STR]["DL2"]);

	$html .= '<table align="center"><tr><td class="sumac_instructions">' . $instructionsWithInsert . '</td></tr></table>' . "\n";

	$html .= '<table class="sumac_payment" style="background:' . $_SESSION[SUMAC_SESSION_PPBGCOLOUR] . ';">' . "\n";

	$html .= '<tr><td>' . "\n";
	$html .= sumac_getHTMLTableForDonationContactDetails($contactDocument);
	$html .= '</td></tr><tr><td>' . "\n";
	$html .= sumac_getHTMLTableOfDonationStatus($donationStatus);
	$html .= '</td></tr><tr><td>' . "\n";
	$html .= sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$funds,$deductionDays,$combined,$monthly);
	$html .= '</td></tr>' . "\n";

	$html .= '</table>' . "\n";

	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableForDonationContactDetails($contactDocument)
{
	$email = $_SESSION[SUMAC_SESSION_EMAILADDRESS];;
	$nameElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_NAME);
	$name = ($nameElements->item(0)->childNodes->item(0) != null) ? $nameElements->item(0)->childNodes->item(0)->nodeValue : '';
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
	$html .= '<table align="left" border="0" rules="none">' . "\n";

	$html .= '<thead><tr><th align="left">' . $name . '</th><th align="right">  ( ' . $email . ' )</th></tr></thead>' . "\n";
	if ($address != '')	$html .= '<tr><td align="left" colspan="2">' . $address . '</td></tr>' . "\n";
	if ($phone != '')	$html .= '<tr><td align="left" colspan="2">' . $_SESSION[SUMAC_STR]["AU2"] . $phone . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableOfDonationStatus($donationStatus)
{
	$html = '<table title="Payment error message" border="0" rules="none" width="100%">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $donationStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards,$funds,$deductionDays,$combined,$monthly)
{
	$html = '<table align="left" border="0" rules="none">' . "\n";
	$html .= '<tr><td colspan="2"><input id="sumac_donation_frequency" type="hidden" name="frequency" value="1" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF14"] . '</td><td><select name="cardtype">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($paymentCards,'',-1) . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF15"] . '</td><td><input type="text" autocomplete="off" name="cardnumber" size="25" maxlength="25" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF16"] . '</td><td><input type="password" autocomplete="off" name="cardsecurity" size="4" maxlength="4" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF12"] . '</td><td><select name="cardexpmonth">' . sumac_getHTMLFormOptionsForExpiryMonth() . '</select>'
			. '<select name="cardexpyear">' . sumac_getHTMLFormOptionsForExpiryYear() . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF13"] . '</td><td><input type="text" name="carduser" size="35" maxlength="35" value="" /></td></tr>' . "\n";
	if (count($funds) > 0)
	{
		$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["DU1"] . '</td><td><select name="fund">' . sumac_getHTMLFormOptionsForValueArrayUsingIndex($funds,true,-1) . '</select></td></tr>' . "\n";
	}
	//else if (count($funds) == 1)
	//{
	//	$html .= '<tr><td colspan="2" align="left">' . SUMAC_TEXT_DONATE_WILL_BE_APPLIED_TO . $funds[0] . '</td></tr>' . "\n";
	//}
	$fixedamount = 0;
	if ($_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] == '')
	{
		$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["DF3"] . '</td><td>' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '<input type="text" name="amountdonated" size="6" maxlength="35" value="" /></td></tr>' . "\n";
	}
	else
	{
		$html .= '<tr><td colspan="2"><input type="hidden" name="amountdonated" size="6" maxlength="35" value="' . number_format(($_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] / 100),2) . '" /></td></tr>' . "\n";
		$fixedamount = sumac_centsToPrintableDollars($_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY]);
	}
	if ($_SESSION[SUMAC_SESSION_DONATION_COMMEM_LINES] == '1')
	{
		$html .= '<tr><td>' . $_SESSION[SUMAC_SESSION_DONATION_COMMEM_QUERY1] . '</td><td><input type="text" name="dedicatee" size="35" maxlength="200" value="" /></td></tr>' . "\n";
	}
	else if ($_SESSION[SUMAC_SESSION_DONATION_COMMEM_LINES] == '2')
	{
		$html .= '<tr><td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		. $_SESSION[SUMAC_SESSION_DONATION_COMMEM_QUERY1] . '</td></tr><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
		. $_SESSION[SUMAC_SESSION_DONATION_COMMEM_QUERY2] . '</td><td><input type="text" name="dedicatee" size="35" maxlength="200" value="" /></td></tr>' . "\n";
	}
// else lines value must be zero, so omit this query

//new layout for support for combined one-time and monthly panel, v512
//*******monthly payment section
	$monthlyhtml = '<tr><td colspan="2"><table id="sumac_monthly_payment_section" class="sumac_payment_section">' . "\n";
	if ($combined)
	{
		$monthlyhtml .= '<caption id="sumac_payment_monthly_caption" class="sumac_payment_section_caption">' . $_SESSION[SUMAC_STR]["DI9"] .  '</caption>' . "\n";
	}
	if (count($deductionDays) >= 1)
	{
		$selectedDay = (count($deductionDays) > 1) ? $_SESSION[SUMAC_STR]["DF2"] : $deductionDays[0];
		$monthlyhtml .= '<tr><td align="left">' . sumac_formatMessage($_SESSION[SUMAC_STR]["DU2"],$selectedDay) .  '</td></tr>' . "\n";
	}
	if (count($deductionDays) > 1)
	{
		$monthlyhtml .= '<tr><td>' . $_SESSION[SUMAC_STR]["DU3"] . '&nbsp;';
		for ($i = 0; $i < count($deductionDays); $i++)
		{
			$checkedIfFirst = ($i == 0) ? ' checked="checked"' : '';
			$monthlyhtml .= '<input type="radio" name="deductionday" value="' . $i . '"' . $checkedIfFirst . '/> ' . $deductionDays[$i] . '&nbsp;&nbsp;';
		}
		$monthlyhtml .= '</td></tr>' . "\n";
	}
	else
	{
		$ddvalue = (count($deductionDays) == 1) ? '0' : '-1';
		$monthlyhtml .= '<tr><td><input type="hidden" name="deductionday" value="' . $ddvalue . '" /></td></tr>' . "\n";
	}
	$monthlyhtml .= '<tr><td align="left"><b>' . $_SESSION[SUMAC_STR]["DI2"] .  '</b></td></tr>' . "\n";
	$buttonlabel = ($combined || $_SESSION[SUMAC_SESSION_SEPARATE_MONTHLY_INSTRUCTIONS]) ? $_SESSION[SUMAC_STR]["DL2"] : $_SESSION[SUMAC_SESSION_PAY_BUTTON];
	if ($_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] == '')
	{
		$monthlyhtml .= '<tr><td align="left"><b>' . sumac_formatMessage($_SESSION[SUMAC_STR]["DI1"],$buttonlabel) .  '</b></td></tr>' . "\n";
	}
	else
	{
		$monthlyhtml .= '<tr><td align="left"><b>' . sumac_formatMessage($_SESSION[SUMAC_STR]["DI4"],$buttonlabel,$fixedamount) .  '</b></td></tr>' . "\n";
	}
	$monthlyhtml .= '<tr><td align="left"><input class="sumac_donation_button" type="submit" name="donate" value="' . $buttonlabel . '"' .
				' onclick="sumac_set_frequency(1);if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\',\'amountdonated\'])) return false; return true;" />' . '</td></tr>' . "\n";
	$monthlyhtml .= '</table></td></tr>' . "\n";

//*******one-time payment section
	$onetimehtml = '<tr><td colspan="2"><table id="sumac_onetime_payment_section" class="sumac_payment_section">' . "\n";
	if ($combined)
	{
		$onetimehtml .= '<caption id="sumac_payment_onetime_caption" class="sumac_payment_section_caption">' . $_SESSION[SUMAC_STR]["DI10"] .  '</caption>' . "\n";
	}
	if ($_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] == '')
	{
		$onetimehtml .= '<tr><td align="left"><b>' . sumac_formatMessage($_SESSION[SUMAC_STR]["DI5"],$_SESSION[SUMAC_SESSION_PAY_BUTTON]) . '</b></td></tr>' . "\n";
	}
	else
	{
		$onetimehtml .= '<tr><td align="left"><b>' . sumac_formatMessage($_SESSION[SUMAC_STR]["DI3"],$_SESSION[SUMAC_SESSION_PAY_BUTTON],$fixedamount) . '</b></td></tr>' . "\n";
	}
	$onetimehtml .= '<tr><td align="left"><input class="sumac_donation_button" type="submit" name="donate" value="' . $_SESSION[SUMAC_SESSION_PAY_BUTTON] . '"' .
				' onclick="sumac_set_frequency(-1); if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\',\'amountdonated\'])) return false; return true;" />' . '</td></tr>' . "\n";
	$onetimehtml .= '</table></td></tr>' . "\n";

	if ($combined === false)
	{
		if ($monthly) $html .= $monthlyhtml;
		else 		$html .= $onetimehtml;
	}
	else
	{
		if (substr($_SESSION[SUMAC_SESSION_COMBINED_DONATION_PREF],0,1) == 'M')
				$html .= $monthlyhtml . $onetimehtml;
		else 	$html .= $onetimehtml . $monthlyhtml;
	}

	$html .= '</table>' . "\n";
//set 'fund' input for cases where there are no choices
	if (count($funds) < 1) $html .= '<input type="hidden" name="fund" value="-1" />' . "\n";
	return $html;
}

?>
