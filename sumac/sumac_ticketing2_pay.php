<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_payment2.php';
include_once 'sumac_xml.php';

function sumac_ticketing2_pay($loadedAccountDocument,$previousRequest,$previousResult)
{
//previousRequest might be either Add Option or Remove Option. Either of these obtains an 'extras' document.
//previousRequest might also have been a Payment attempt that failed.

	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false)
	{
//@@@ needs new error exit
		return false;
	}

	if (sumac_countTicketOrdersInBasket() <= 0)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_TICKETS_IN_BASKET . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

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

	$paymentCards = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PAYMENT_CARD);
	if ($paymentCards->length < 1)
	{
//@@@ needs new error exit
		//$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		//$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_PAYMENT_CARD . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$accountDocument = null;
	if ($loadedAccountDocument != null) $accountDocument = $loadedAccountDocument;
	else
	{
		$savedAccountDocument = sumac_reloadLoginAccountDocument();
		if ($savedAccountDocument !== false) $accountDocument = $savedAccountDocument;
	}
	if ($accountDocument == null)
	{
//@@@ needs new error exit
		return false;
	}

	$extrasDocument = sumac_reloadExtrasDocument();
	if ($extrasDocument == false)
	{
//@@@ needs new error exit
		return false;
	}


	$html = sumac_ticketing2_pay_HTML($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult);
	echo $html;
	return true;
}

function sumac_ticketing2_pay_HTML($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult)
{
	return sumac_geth2_head('ticketing2')
			.sumac_ticketing2_pay_body($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult);
}

function sumac_ticketing2_pay_body($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult)
{
	$retryform = ($previousRequest != '') ? 'all' : null;
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($extrasDocument,'ticketing2_extras')
			.sumac_addParsedXmlIfDebugging($accountDocument,'ticketing2_accountdetails')
			.sumac_geth2_user('top','ticketing2')
			.sumac_ticketing2_pay_content($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult)
			.sumac_geth2_body_footer('ticketing2',true,$retryform,'');
}

function sumac_ticketing2_pay_content($organisationDocument,$accountDocument,$extrasDocument,$previousRequest,$previousResult)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_sumac_div_hide_mainpage('ticketing2',sumac_geth2_spantext('ticketing2','H1'));

	$html .= sumac_geth2_divtag('ticketing2','pay_mainpage','mainpage');

	$html .= sumac_ticketing2_pay_instructions();
	$html .= sumac_ticketing2_title_with_user();
	$html .= sumac_ticketing2_summary_table($organisationDocument,$extrasDocument,true);


//failure of payment will be reported in the payment2 panel
//	if ($previousResult != '')
//	{
//		$html .= sumac_geth2_divtag('ticketing2','previous_result','result').$previousResult.'</div>'.PHP_EOL;
//	}


	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_top_ticketing_navbar",'',true,false);

	$html .= sumac_ticketing2_pay_HTMLform($organisationDocument,$extrasDocument,$accountDocument,$previousRequest,$previousResult);
	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_bottom_ticketing_navbar",'',true,false);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_ticketing2_pay_instructions()
{
	$html = sumac_geth2_divtag('ticketing2','instructions','instructions');
	$html .= sumac_geth2_spantext('ticketing2','I3');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_pay_HTMLform($organisationDocument,$extrasDocument,$accountDocument,$previousRequest,$previousResult)
{
	//$html = sumac_geth2_formtag('ticketing2','all','ticketing','sumac_test_response.php');
	$html = sumac_geth2_formtag('ticketing2','all','ticketing','sumac_ticketing2_ordered.php');
	$totalcents = 0;
	if ($extrasDocument != null)
	{
		$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
		$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	}
	$html .= sumac_ticketing2_pay_delivery_etc($organisationDocument);
	$html .= sumac_payment2('ticketing2','T',$totalcents,false,$organisationDocument,$accountDocument,$previousRequest,$previousResult);
	$html .= '</form>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_pay_delivery_etc($organisationDocument)
{
	$html = sumac_geth2_divtag('ticketing2','delivery','titled');
	$deliveryMechanisms = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DELIVERY_MECHANISM);
	$informationSources = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_INFORMATION_SOURCE);
	if ((count($deliveryMechanisms) > 0) ||	(count($informationSources) > 0) || ($_SESSION[SUMAC_SESSION_INCPAYNOTE]))
	{
		$deliveryTitleId = (count($deliveryMechanisms) > 0) ? 'H6' : 'H7';
		$html .= sumac_geth2_sumac_title_with_line('ticketing2',$deliveryTitleId);
		$html .= sumac_geth2_tabletag('ticketing2','delivery','data_entry');
		if (count($deliveryMechanisms) > 0)
		{
			$html .= sumac_geth2_trtag('ticketing2','deliverymechanism','data_entry').sumac_geth2_tdtag('ticketing2','deliverymechanism','entry_field');
			$options = sumac_getHTMLFormOptionsForValueArrayUsingIndex($deliveryMechanisms,true,-1);
			$deliveryMechanismsInputLabel = sumac_ticketing2_pay_getInputLabel($organisationDocument,SUMAC_VALUE_DELIVERY_MECHANISM,'F1');
			$html .= sumac_geth2_select_with_label('ticketing2','deliverymechanisms','select','deliverymechanism','data-sumac-reqby="T"',$options,$deliveryMechanismsInputLabel,'above',false);
			$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
		}
		if (count($informationSources) > 0)
		{
			$html .= sumac_geth2_trtag('ticketing2','informationsource','data_entry').sumac_geth2_tdtag('ticketing2','informationsource','entry_field');
			$options = sumac_getHTMLFormOptionsForValueArrayUsingIndex($informationSources,true,-1);
			$informationSourcesInputLabel = sumac_ticketing2_pay_getInputLabel($organisationDocument,SUMAC_VALUE_INFORMATION_SOURCE,'F2');
			$html .= sumac_geth2_select_with_label('ticketing2','informationsources','select','informationsource','',$options,$informationSourcesInputLabel,'above',false);
			$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
		}
		if ($_SESSION[SUMAC_SESSION_INCPAYNOTE])
		{
			$html .= sumac_geth2_trtag('ticketing2','payeenote','data_entry').sumac_geth2_tdtag('ticketing2','payeenote','entry_field');
			$attrs = '';
			$fieldLabel = $_SESSION[SUMAC_SESSION_PAYNOTETEXT];
			$html .= sumac_geth2_textarea_with_label('ticketing2','payeenote','payeenote','payeenote',$attrs,'',$fieldLabel,'above',false);
			$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
		}
		$html .= '</table>'.PHP_EOL;
	}
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_pay_getInputLabel($organisationDocument,$context,$defaultLabelId)
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
	return sumac_geth2_spantext('ticketing2',$defaultLabelId);
}

?>