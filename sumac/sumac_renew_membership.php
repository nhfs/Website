<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execRenew($renewalStatus,$membershipRenewedPHP,$accountDocument)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$paymentCards = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_PAYMENT_CARD);
	if (count($paymentCards) < 1)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_PAYMENT_CARD . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$contactDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($contactDocument == false) return false;

	$planElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planCount = $planElements->length;
	$html = sumac_getHTMLHeadForRenewal($planCount);

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($contactDocument,'invitationtorenew');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'payment') . sumac_getSubtitle();

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= sumac_getHTMLFormForRenewal($renewalStatus,$membershipRenewedPHP,$organisationDocument,$contactDocument,$paymentCards,$planCount);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
//	$html .= sumac_getHTMLBodyForActions('ok',$membershipRenewedPHP,true);

	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_MEMBERSHIP) . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if ($planCount == 1)
	{
		$planElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
		$planId = $planElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$html .= '<script type="text/javascript">' . "\n";
		$html .= "sumac_select_plan('" . $planId . "',0);" . "\n";
		$html .= '</script>' . "\n";
	}
	if ($renewalStatus != '') $html .= sumac_getJSToRestoreEnteredValues('renew',SUMAC_ID_FORM_MEMBERSHIP);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForRenewal($planCount)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getRenewalHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getRenewalHTMLScriptVariables($planCount);
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getRenewalHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}

function sumac_getRenewalHTMLStyleElements()
{
// the buttons for choosing the renewal plan are like the action buttons
// they all have middling rounding
// the buttons start as black text on light grey background,
// and are very pale with grey text when disabled
// and go to white text on dark grey when the mouse is over them
	$html = 'button.sumac_select' . "\n" .
				'{' . "\n" .
				'	border:3px outset;border-radius:7px;-moz-border-radius:7px;' . "\n" .
				'	width:125px;background:lightgrey;color:black;' . "\n" .
				'}' . "\n" .
				'button.sumac_select:hover' . "\n" .
				'{' . "\n" .
				'	background:slategrey;color:white;' . "\n" .
				'}' . "\n" .
				'button.sumac_select:disabled' . "\n" .
				'{' . "\n" .
				'	background:#E0E0E0;color:grey;' . "\n" .
				'}' . "\n";

// these classes show which renewal plan has been selected
	$html .= 'td.sumac_notselectedtitle' . "\n" .
				'{' . "\n" .
				'	font-weight:normal;text-decoration:underline;' . "\n" .
				'}' . "\n";
	$html .= 'td.sumac_notselected' . "\n" .
				'{' . "\n" .
				'	font-weight:normal;' . "\n" .
				'}' . "\n";
	$html .= 'td.sumac_opt_notselected' . "\n" .
				'{' . "\n" .
				'	text-decoration:line-through;color:grey;/*	font-weight:lighter;*/' . "\n" .
				'}' . "\n";
	$html .= 'td.sumac_selectedtitle' . "\n" .
				'{' . "\n" .
				'	font-weight:bold;text-decoration:underline;' . "\n" .
				'}' . "\n";
	$html .= 'td.sumac_selected' . "\n" .
				'{' . "\n" .
				'	font-weight:bold;' . "\n" .
				'}' . "\n";
	$html .= 'td.sumac_opt_selected' . "\n" .
				'{' . "\n" .
				'	text-decoration:none;color:black;/*	font-weight:normal;*/' . "\n" .
				'}' . "\n";

	return $html;
}

function sumac_getRenewalHTMLScriptVariables($planCount)
{
	$html = 'var sumac_class_select = "sumac_select";' . "\n";
	$html .= 'var sumac_class_td_notselectedtitle = "sumac_notselectedtitle";' . "\n";
	$html .= 'var sumac_class_td_notselected = "sumac_notselected";' . "\n";
	$html .= 'var sumac_class_td_opt_notselected = "sumac_opt_notselected";' . "\n";
	$html .= 'var sumac_class_td_selectedtitle = "sumac_selectedtitle";' . "\n";
	$html .= 'var sumac_class_td_selected = "sumac_selected";' . "\n";
	$html .= 'var sumac_class_td_opt_selected = "sumac_opt_selected";' . "\n";
	$html .= 'var sumac_class_nodisplay = "sumac_nodisplay";' . "\n";
	$html .= 'var sumac_id_button_select = "' . SUMAC_ID_BUTTON_SELECT . '";' . "\n";
	$html .= 'var sumac_id_button_buy_membership = "' . SUMAC_ID_BUTTON_BUY_MEMBERSHIP . '";' . "\n";
	$html .= 'var sumac_id_td_description = "' . SUMAC_ID_TD_DESCRIPTION . '";' . "\n";
	$html .= 'var sumac_id_td_total = "' . SUMAC_ID_TD_TOTAL . '";' . "\n";
	$html .= 'var sumac_id_td_total_cents = "' . SUMAC_ID_TD_TOTAL_CENTS . '";' . "\n";
	$html .= 'var sumac_id_td_select = "' . SUMAC_ID_TD_SELECT . '";' . "\n";
	$html .= 'var sumac_button_select = "' . $_SESSION[SUMAC_STR]["ML2"] . '";' . "\n";
	$html .= 'var sumac_button_selected = "' . $_SESSION[SUMAC_STR]["ML3"] . '";' . "\n";
	$html .= 'var sumac_id_span_payment = "' . SUMAC_ID_SPAN_PAYMENT . '";' . "\n";
	$html .= 'var sumac_id_post_costofmembership = "' . SUMAC_ID_POST_COSTOFMEMBERSHIP . '";' . "\n";
	$html .= 'var sumac_id_post_membershipplan = "' . SUMAC_ID_POST_MEMBERSHIPPLAN . '";' . "\n";

	$html .= 'var sumac_plan_count = ' . $planCount. ';' . "\n";
	return $html;
}

function sumac_getRenewalHTMLScriptFunctions()
{
	return <<<EOJS

	function sumac_select_plan(plan_id,selection)
	{
		for (var i = 0; i < sumac_plan_count; i++)
		{
			var description_id = sumac_id_td_description + i;
			var total_id = sumac_id_td_total + i;
			var select_id = sumac_id_td_select + i;
			var button_id = sumac_id_button_select + i;

			if (i == selection)
			{
				//show selection on screen by setting the button disabled and changing the button name to 'SELECTED'
				document.getElementById(button_id).disabled = 'disabled';
				document.getElementById(button_id).innerHTML = sumac_button_selected;
				//and change the class from notselected to selected on the description, total, and button replacement
				document.getElementById(description_id).className = sumac_class_td_selectedtitle;
				document.getElementById(total_id).className = sumac_class_td_selected;
				document.getElementById(select_id).className = sumac_class_td_selected;
			}
			else
			{
				//make sure the button is enabled and properly named and that the class of all the text is notselected
				document.getElementById(button_id).removeAttribute('disabled');
				document.getElementById(button_id).innerHTML = sumac_button_select;
				document.getElementById(description_id).className = sumac_class_td_notselectedtitle;
				document.getElementById(total_id).className = sumac_class_td_notselected;
				document.getElementById(select_id).className = sumac_class_td_notselected;
			}
		}
		//make the RENEW button active and set the amount for payment
		document.getElementById(sumac_id_button_buy_membership).removeAttribute('disabled');
		var selected_total_id = sumac_id_td_total + selection;
		document.getElementById(sumac_id_span_payment).innerHTML = document.getElementById(selected_total_id).innerHTML;
		//save the selected plan id and the amount for payment
		document.getElementById(sumac_id_post_membershipplan).value = plan_id;
		var total_cents_id = sumac_id_td_total_cents + selection;
		document.getElementById(sumac_id_post_costofmembership).value = document.getElementById(total_cents_id).innerHTML;
		//check the selected plan for optional extras and set the associated checked input value
		var inputs = document.getElementsByTagName('INPUT');
		for (var i = 0; i < inputs.length; i++)
		{
			var id = inputs[i].id;
			if (id.substr(0,14) == 'sumac_oeinput_')
			{
				var oe_plan_cents_id = id + '_cost' + selection;
				var oe_plan_cents_td = document.getElementById(oe_plan_cents_id);
				if (oe_plan_cents_td != null) inputs[i].value = '1';	// optional extra IS applicable to this plan
				else inputs[i].value = '0';	// optional extra is NOT applicable
			}
		}
	}

	function sumac_calculate_membership_total(e)
	{
// e is the input element for an optional extra
		var oe_cents_id = e.id + '_cents';
		var oe_cents = document.getElementById(oe_cents_id).innerHTML;
		var selectedplan = document.getElementById(sumac_id_post_membershipplan).value;

		for (var i = 0; i < sumac_plan_count; i++)
		{
			var oe_plan_cents_id = e.id + '_cost' + i;
			var oe_plan_cents_td = document.getElementById(oe_plan_cents_id);
			if (oe_plan_cents_td == null)	// optional extra is not applicable to this plan
			{
				//if this is the selected plan, change the associated checked input value
				var button_id = sumac_id_button_select + i;
				if (document.getElementById(button_id).innerHTML == sumac_button_selected) e.value = '0';
				continue;
			}
			var total_id = sumac_id_td_total + i;
			var total_cents_id = sumac_id_td_total_cents + i;
			var total_cents_td = document.getElementById(total_cents_id);
			var total_cents = Number(total_cents_td.innerHTML);
			if (e.checked)
			{
				total_cents = total_cents + Number(oe_cents);
				oe_plan_cents_td.className = sumac_class_td_opt_selected;
			}
			else
			{
				total_cents = total_cents - Number(oe_cents);
				oe_plan_cents_td.className = sumac_class_td_opt_notselected;
			}
			var total_dollars = sumac_centsToPrintableDollars(total_cents);
			total_cents_td.innerHTML = total_cents;
			document.getElementById(total_id).innerHTML = total_dollars;

			//if this is the selected plan, update the amount for payment
			var button_id = sumac_id_button_select + i;
			if (document.getElementById(button_id).innerHTML == sumac_button_selected)
			{
				document.getElementById(sumac_id_post_costofmembership).value = total_cents;
				document.getElementById(sumac_id_span_payment).innerHTML = total_dollars;
				e.value = '1';  // set the associated checked input value in case
			}
		}
	}
EOJS;
}

function sumac_getHTMLFormForRenewal($renewalStatus,$membershipRenewedPHP,$organisationDocument,$contactDocument,$paymentCards,$planCount)
{
	$html = '<div id="' . SUMAC_ID_DIV_MEMBERSHIP . '" class="sumac_maintable">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_MEMBERSHIP . '" accept-charset="UTF-8" method="post" action="' . $membershipRenewedPHP . '">' . "\n";
	$html .= '<input id="' . SUMAC_ID_POST_COSTOFMEMBERSHIP . '" type="hidden" name="costofmembership" value="" />' . "\n";
	$html .= '<input id="' . SUMAC_ID_POST_MEMBERSHIPPLAN . '" type="hidden" name="membershipplan" value="" />' . "\n";

	if ($planCount > 1)
	{
		$html .= '<table align="center"><tr><td class="sumac_instructions">' . $_SESSION[SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP] . '</td></tr></table>' . "\n";
	}
	else if ($planCount > 0)
	{
		$html .= '<table align="center"><tr><td class="sumac_instructions">' . $_SESSION[SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP_ONLYPLAN] . '</td></tr></table>' . "\n";
	}
	else
	{
		$html .= '<table align="center"><tr><td class="sumac_instructions">' . $_SESSION[SUMAC_SESSION_INSTRUCTIONS_CANNOT_OFFER] . '</td></tr></table>' . "\n";
	}

	$html .= '<table class="sumac_payment" style="background:' . $_SESSION[SUMAC_SESSION_PPBGCOLOUR] . ';">' . "\n";

	$html .= '<tr><td>' . "\n";
	$html .= sumac_getHTMLTableForRenewalContactDetails($contactDocument);
	$html .= '</td></tr>' . "\n";

	$html .= '<tr><td>' . "\n";
	$html .= sumac_getHTMLTableOfMembershipPlans($contactDocument,$planCount);
	$html .= '</td></tr>' . "\n";

	if ($planCount > 0)
	{
		$html .= '<tr><td>' . "\n";
		$html .= sumac_getHTMLTableOfRenewalStatus($renewalStatus);
		$html .= '</td></tr>' . "\n";

		$html .= '<tr><td>' . "\n";
		$html .= sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards);
		$html .= '</td></tr>' . "\n";
	}
//NOTE - we do not show the payment stuff when there are no plans

	$html .= '</table>' . "\n";

	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableForRenewalContactDetails($contactDocument)
{
	$email = $_SESSION[SUMAC_SESSION_EMAILADDRESS];
	$nameElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_NAME);
	$name = ($nameElements->item(0)->childNodes->item(0) != null) ? $nameElements->item(0)->childNodes->item(0)->nodeValue : 'no name';
	if (sumac_no_user_password(SUMAC_PACKAGE_MEMBERSHIP))
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

function sumac_getHTMLTableOfMembershipPlans($contactDocument,$planCount)
{
	$currentMembership = null;	//by default this is a new application for membership
	$currentMembershipElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_CURRENT_MEMBERSHIP);
	if ($currentMembershipElements->length > 0)
	{
		if ($currentMembershipElements->item(0)->childNodes->item(0) != null) $currentMembership = $currentMembershipElements->item(0)->childNodes->item(0)->nodeValue;
	}
	$planElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planDetails = sumac_getPlanDetails($planElements);
	$optionalExtraElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_OPTIONAL_EXTRA);
	$optionalExtraCount = $optionalExtraElements->length;
	$optionalExtras = sumac_getOptionalExtras($optionalExtraElements);

	$html = '<div id="' . SUMAC_ID_DIV_PLANS . '">' . "\n";

	$colspan = ($planCount + 1);
	if ($currentMembership != null)
	{
		$html .= '<table align="center" border="0" rules="none">' . "\n";
		$html .= '<tr><td colspan="' . $colspan . '" align="left"><b>' . $_SESSION[SUMAC_STR]["MU1"] . $currentMembership . '</b></td></tr>' . "\n";
		$html .= '</table>' . "\n";
	}

	$html .= '<table align="center" border="0" rules="cols" cellpadding="5">' . "\n";
	$html .= '<tr><td colspan="' . $colspan . '"><hr /></td></tr>' . "\n";
	if ($planCount > 0)
	{
		$html .= '<tr>';		//top line identifying the plans
		$html .= '<td></td>';	//no label
		//As of version 5.6.6 we use the 'name' instead of the 'description' attribute here.
		// - provided that we are talking to the version of Sumac that sends the attributes that way.
		for ($i = 0; $i < $planCount; $i++)
		{
			$planname = sumac_featureIsSupported(SUMAC_FEATURE_NEW_MEMBERSHIP)
						? $planDetails[$i][SUMAC_ATTRIBUTE_NAME]
						: $planDetails[$i][SUMAC_ATTRIBUTE_DESCRIPTION];
			$html .= '<td align="center" class="sumac_notselectedtitle" id="' . SUMAC_ID_TD_DESCRIPTION . $i . '">' .
					 $planname . '</td>';
		}
		$html .= '</tr>' . "\n";

		$html .= '<tr>';		//Fee
		$html .= '<td align="left">' . $_SESSION[SUMAC_STR]["MX1"] . '</td>';
		for ($i = 0; $i < $planCount; $i++)
		{
			$html .= '<td align="right">' . sumac_centsToPrintableDollars($planDetails[$i][SUMAC_ATTRIBUTE_FEE_CENTS]) . '</td>';
		}
		$html .= '</tr>' . "\n";

		$html .= '<tr>';		//Extra charges
		$html .= '<td align="left">' . $_SESSION[SUMAC_STR]["MU2"] . '</td>';
		for ($i = 0; $i < $planCount; $i++)
		{
			$extraReason = $planDetails[$i][SUMAC_ATTRIBUTE_EXTRA_REASON];
			$erText = (($extraReason != null) && ($extraReason != '')) ? ($extraReason . ':&nbsp;&nbsp;') : '';
			$html .= '<td align="right">' . $erText . sumac_centsToPrintableDollars($planDetails[$i][SUMAC_ATTRIBUTE_EXTRA_CENTS]) . '</td>';
		}
		$html .= '</tr>' . "\n";

		for ($j = 0; $j < $optionalExtraCount; $j++)		//Optional extras
		{
			$id = $optionalExtras[$j][SUMAC_ATTRIBUTE_ID];
			$html .= '<tr>';
			$html .= '<td align="left"><input id="sumac_oeinput_' . $id . '" type="checkbox" name="optextra_' . $id . '" value="1" onclick="sumac_calculate_membership_total(this);" />' .
					'<label for="sumac_oeinput_' . $id . '">' . $optionalExtras[$j][SUMAC_ATTRIBUTE_NAME] . '</label></td>';
			$html .= '<td class="sumac_nodisplay" id="sumac_oeinput_' . $id . '_cents">' . $optionalExtras[$j][SUMAC_ATTRIBUTE_COST_CENTS] . '</td>';
			for ($i = 0; $i < $planCount; $i++)
			{
				$planIds = $optionalExtras[$j][SUMAC_ATTRIBUTE_PLANS];
				$applicable = (count($planIds) == 0);
				for ($k = 0; $k < count($planIds); $k++)
				{
					if ($planIds[$k] == $planDetails[$i][SUMAC_ATTRIBUTE_ID]) $applicable = true;
				}
				if ($applicable)
				{
					$html .= '<td align="right" class="sumac_opt_notselected" id="sumac_oeinput_' . $id . '_cost' . $i . '">' .
								sumac_centsToPrintableDollars($optionalExtras[$j][SUMAC_ATTRIBUTE_COST_CENTS]) . '</td>';
				}
				else
				{
					//$natext = $optionalExtras[$j][SUMAC_ATTRIBUTE_NATEXT];
					//$natext = (($natext != null) && ($natext != '')) ? $natext : $_SESSION[SUMAC_SESSION_OPTEXTRA_NATEXT];
					$natext = $_SESSION[SUMAC_SESSION_OPTEXTRA_NATEXT];
					$html .= '<td align="right">' . $natext . '</td>';
				}
			}
			$html .= '</tr>' . "\n";
		}

		$html .= '<tr>';		//Total
		$html .= '<td align="left">' . $_SESSION[SUMAC_STR]["MU3"] . '</td>';
		for ($i = 0; $i < $planCount; $i++)
		{
			$html .= '<td align="right" class="sumac_notselected" id="' . SUMAC_ID_TD_TOTAL . $i . '">' .
						sumac_centsToPrintableDollars($planDetails[$i][SUMAC_ATTRIBUTE_TOTAL_CENTS]) . '</td>' .
						'<td class="sumac_nodisplay" id="' . SUMAC_ID_TD_TOTAL_CENTS . $i . '">' .
						$planDetails[$i][SUMAC_ATTRIBUTE_TOTAL_CENTS] . '</td>';
		}
		$html .= '</tr>' . "\n";

		$html .= '<tr>';		//bottom line - a row of SELECT buttons
		$html .= '<td></td>';	//no label
		for ($i = 0; $i < $planCount; $i++)
		{
			$html .= '<td align="left" class="sumac_notselected" id="' . SUMAC_ID_TD_SELECT . $i . '">' .
					'<button type="button" class="sumac_select"' .
					' onclick="sumac_select_plan(' . "'" . $planDetails[$i][SUMAC_ATTRIBUTE_ID]  . "'," . $i . ')"' .
					' id="' . SUMAC_ID_BUTTON_SELECT . $i . '">' .
					$_SESSION[SUMAC_STR]["ML2"] . '</button></td>';
		}
		$html .= '</tr>' . "\n";
	}

	$remarkElements = $contactDocument->getElementsByTagName(SUMAC_ELEMENT_REMARK);
	if ($remarkElements->length == 0) $remark = '';
	else $remark = ($remarkElements->item(0)->childNodes->item(0) != null) ? $remarkElements->item(0)->childNodes->item(0)->nodeValue : '';
	if ($remark != '')
	{
		$html .= '<tr><td colspan="' . $colspan . '" align="center" class="remark">' . $remark . '</td></tr>' . "\n";
	}

	$html .= '<tr><td colspan="' . $colspan . '"><hr /></td></tr>' . "\n";

	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getPlanDetails($planElements)
{
	$planDetails = array();
	for ($i = 0; $i < $planElements->length; $i++)
	{
		$id = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID);
		//As of version 5.6.6 we use the 'name' instead of the 'description' attribute here.
		//So we get both here and this allows both to be used in the new Membership package.
		$name = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$description = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_DESCRIPTION);
		$feeCents = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_FEE_CENTS);
		$extraCents = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_EXTRA_CENTS);
		$extraReason = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_EXTRA_REASON);
		$totalCents = $planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_TOTAL_CENTS);
		$planDetails[$i] = array(
								SUMAC_ATTRIBUTE_ID => $id,
								SUMAC_ATTRIBUTE_NAME => $name,
								SUMAC_ATTRIBUTE_DESCRIPTION => $description,
								SUMAC_ATTRIBUTE_FEE_CENTS => $feeCents,
								SUMAC_ATTRIBUTE_EXTRA_CENTS => $extraCents,
								SUMAC_ATTRIBUTE_EXTRA_REASON => $extraReason,
								SUMAC_ATTRIBUTE_TOTAL_CENTS => $totalCents
								);
	}
	return $planDetails;
}

function sumac_getOptionalExtras($optionalExtraElements)
{
	$optionalExtras = array();
	for ($i = 0; $i < $optionalExtraElements->length; $i++)
	{
		$id = $optionalExtraElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$name = $optionalExtraElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$costCents = $optionalExtraElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_COST_CENTS);
		//$natext = $optionalExtraElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_NATEXT);
		$plans = $optionalExtraElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_PLANS);
		$planIds = array_values(array_filter(explode(' ',$plans)));
		$optionalExtras[$i] = array(
								SUMAC_ATTRIBUTE_ID => $id,
								SUMAC_ATTRIBUTE_NAME => $name,
								SUMAC_ATTRIBUTE_COST_CENTS => $costCents,
								//SUMAC_ATTRIBUTE_NATEXT => $natext,
								SUMAC_ATTRIBUTE_PLANS => $planIds
								);
	}
	return $optionalExtras;
}

function sumac_getHTMLTableOfRenewalStatus($renewalStatus)
{
	$html = '<table title="Payment error message" border="0" rules="none" align="center">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $renewalStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForPaymentCardDetails($organisationDocument,$paymentCards)
{
	$html = '<table align="center" border="0" rules="none">' . "\n";

	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF14"] . '</td><td><select name="cardtype">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($paymentCards,'',-1) . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF15"] . '</td><td><input type="text" autocomplete="off" name="cardnumber" size="25" maxlength="25" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF16"] . '</td><td><input type="password" autocomplete="off" name="cardsecurity" size="4" maxlength="4" value="" /></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF12"] . '</td><td><select name="cardexpmonth">' . sumac_getHTMLFormOptionsForExpiryMonth() . '</select>'
			. '<select name="cardexpyear">' . sumac_getHTMLFormOptionsForExpiryYear() . '</select></td></tr>' . "\n";
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF13"] . '</td><td><input type="text" name="carduser" size="35" maxlength="35" value="" /></td></tr>' . "\n";

	$dollarsPayable = sumac_centsToPrintableDollars(0);
	$html .= '<tr><td colspan="2" align="left"><b>' . $_SESSION[SUMAC_STR]["MI1"] .
			'<span id="' . SUMAC_ID_SPAN_PAYMENT . '">' . $dollarsPayable . '</span>' .
			$_SESSION[SUMAC_STR]["MI2"] . '</b></td></tr>' . "\n";
	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="renew" value="' . $_SESSION[SUMAC_STR]["ML1"] . '"' .
					' disabled="disabled" id= "' . SUMAC_ID_BUTTON_BUY_MEMBERSHIP . '"' .
					' onclick="if (sumac_checknamedfields([\'cardtype\',\'cardnumber\',\'cardexpmonth\',\'cardexpyear\',\'carduser\'])) return false; return true;" />' . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

?>
