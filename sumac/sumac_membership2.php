<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_payment2.php';
include_once 'sumac_xml.php';

function sumac_membership2($loadedAccountDocument,$previousRequest,$previousResult)
{
//previousRequest might be either Add Option or Remove Option. Either of these obtains an 'extras' document.
//previousRequest might also have been a Payment attempt that failed.

	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false)
	{
//@@@ needs new error exit
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

	$planElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planCount = $planElements->length;
//@@@ what if no plans? what if only one plan?

	$html = sumac_membership2_HTML($organisationDocument,$accountDocument,$previousRequest,$previousResult);
	echo $html;
	return true;
}

function sumac_membership2_HTML($organisationDocument,$accountDocument,$previousRequest,$previousResult)
{
	return sumac_geth2_head('membership2')
			.sumac_membership2_body($organisationDocument,$accountDocument,$previousRequest,$previousResult);
}

function sumac_membership2_body($organisationDocument,$accountDocument,$previousRequest,$previousResult)
{
	$retryform = ($previousRequest != '') ? 'all' : null;
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($accountDocument,'membership2_accountdetails')
			.sumac_geth2_user('top','membership2')
			.sumac_membership2_content($organisationDocument,$accountDocument,$previousRequest,$previousResult)
			.sumac_geth2_body_footer('membership2',true,$retryform,'');
}

function sumac_membership2_content($organisationDocument,$accountDocument,$previousRequest,$previousResult)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_sumac_div_hide_mainpage('membership2',sumac_geth2_spantext('membership2','H1'));

	$html .= sumac_geth2_divtag('membership2','mainpage','mainpage');
	$html .= sumac_membership2_instructions();

//failure of payment will be reported in the payment2 panel
//	if ($previousResult != '')
//	{
//		$html .= sumac_geth2_divtag('membership2','previous_result','result').$previousResult.'</div>'.PHP_EOL;
//	}

	$currentMembership = null;	//by default this is a new application for membership
	$mainHeadingId = 'H2';
	$currentMembershipElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_CURRENT_MEMBERSHIP);
	if ($currentMembershipElements->length > 0)
	{
		if ($currentMembershipElements->item(0)->childNodes->item(0) != null) $currentMembership = $currentMembershipElements->item(0)->childNodes->item(0)->nodeValue;
		$mainHeadingId = 'H3';
	}
	$html .= sumac_geth2_sumac_title_with_gobacklink_and_line('membership2',$mainHeadingId,'L1',true);
	$html .= sumac_geth2_sumac_subtitle('membership2','H4','_name',true,
				array(sumac_getElementValue($accountDocument,SUMAC_ELEMENT_FIRSTNAME),
						sumac_getElementValue($accountDocument,SUMAC_ELEMENT_LASTNAME)));
	if ($currentMembership != null)
		$html .= sumac_geth2_sumac_subtitle('membership2',$currentMembership,'_status',false);
	else
		$html .= sumac_geth2_sumac_subtitle('membership2','H5',1);
	$html .= sumac_membership2_HTMLform($organisationDocument,$accountDocument,$previousRequest,$previousResult);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_membership2_instructions()
{
	$html = sumac_geth2_divtag('membership2','instructions','instructions');
	$html .= sumac_geth2_spantext('membership2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_membership2_HTMLform($organisationDocument,$accountDocument,$previousRequest,$previousResult)
{
	$html = sumac_geth2_formtag('membership2','all','membership','sumac_membership2_submit.php');
	//$html = sumac_geth2_formtag('membership2','all','membership','sumac_test_response.php');
	$extrasDocument = sumac_reloadExtrasDocument();
	$selectedPlanId = sumac_membership2_get_selectedplanid($accountDocument,$previousRequest);
	$html .= sumac_membership2_outer_table($accountDocument,$extrasDocument,$selectedPlanId);
	$amountToPay = sumac_membership2_get_amounttopay($accountDocument,$extrasDocument,$selectedPlanId);
	$html .= sumac_payment2('membership2','M',$amountToPay,false,$organisationDocument,$accountDocument,$previousRequest,$previousResult);
	$html .= '</form>'.PHP_EOL;
	return $html;
}

function sumac_membership2_outer_table($accountDocument,$extrasDocument,$selectedPlanId)
{
/*
outer table (no lines, no borders):
	two columns (tables with borders and horizontal lines):
		column 1 = membership-plans table:
		followed by plan-options table:
		[there are multiple plan options tables, one for each plan, only one being visible at a time]

		column 2 = cost summary table
	extra row with hidden variables for add/remove option
*/
	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the stringids are not duplicated

	//$selectedPlanElement = $planElements->getElementById($selectedPlanId);

	$planElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planCount = $planElements->length;
	$optionElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_OPTIONAL_EXTRA);
	$optionCount = $optionElements->length;

	$html = sumac_geth2_tabletag('membership2','outer','outer');
	$html .= sumac_geth2_trtag('membership2','plans','plans').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','plans','plans').PHP_EOL;
	$html .= sumac_membership2_plan_table($planElements,$selectedPlanId);
	$html .= '</td>'.PHP_EOL;
	$tableCount = $planCount + 1;

	for ($i = 0; $i < $planCount; $i++)
	{
		$planElement = $planElements->item($i);
		$planId = $planElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$selected = ($planId == $selectedPlanId);
		$optionsArray = array();
		for ($j = 0; $j < $optionCount; $j++)
		{
			$optionElement = $optionElements->item($j);
			$plans = $optionElement->getAttribute(SUMAC_ATTRIBUTE_PLANS);
			$planIds = array_values(array_filter(explode(' ',$plans)));
			if (count($planIds) == 0)
			{
				$optionsArray[] = $optionElement;	//if no plan ids specified, option applies to all
			}
			else
			{
				for ($k = 0; $k < count($planIds); $k++)
				{
					if ($planIds[$k] == $planId) { $optionsArray[] = $optionElement; break; }
				}
			}
		}
		$cls = $selected ? 'summary' : array('summary','nodisplay');
		$html .= sumac_geth2_tdtag('membership2','summary_'.$planId,$cls,1,' rowspan="'.$tableCount.'"').PHP_EOL;
		$html .= sumac_membership2_summary_table($optionsArray,$planElement,$selected,$extrasDocument);
		$html .= '</td>'.PHP_EOL;
	}

	$html .= '</tr>'.PHP_EOL;

	for ($i = 0; $i < $planCount; $i++)
	{
		$planElement = $planElements->item($i);
		$planId = $planElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$planName = $planElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$selected = ($planId == $selectedPlanId);
		$optionsArray = array();
		for ($j = 0; $j < $optionCount; $j++)
		{
			$optionElement = $optionElements->item($j);
			$plans = $optionElement->getAttribute(SUMAC_ATTRIBUTE_PLANS);
			$planIds = array_values(array_filter(explode(' ',$plans)));
			if (count($planIds) == 0)
			{
				$optionsArray[] = $optionElement;	//if no plan ids specified, option applies to all
			}
			else
			{
				for ($k = 0; $k < count($planIds); $k++)
				{
					if ($planIds[$k] == $planId) { $optionsArray[] = $optionElement; break; }
				}
			}
		}

		$cls = $selected ? 'options' : array('options','nodisplay');
		$html .= sumac_geth2_trtag('membership2','options_'.$planId,$cls);
		$html .= sumac_geth2_tdtag('membership2','options_'.$planId,'options');
		$html .= sumac_membership2_options_table($optionCount,$optionsArray,$planId,$planName,$selected);
		$html .= '</td>'.PHP_EOL;
		$html .= '</tr>'.PHP_EOL;
	}

	$html .= sumac_geth2_trtag('membership2','options_changes','hidden').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','options_changes','hidden');
	$value = '';
	$selOptCount = isset($_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS]) ? count($_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS]) : 0;
	for ($i=0; $i<$selOptCount; $i++) $value .= (($i==0)?'':' ').$_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS][$i];
	$attrs = 'type="hidden" value="'.$value.'"';
	$html .= sumac_geth2_input('sumac_input_membership2_addedoptions','hidden','sumac_options',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);
	return $html;
}

function sumac_membership2_plan_table($planElements,$selectedPlanId)
{
/*
membership-plan table (borders and horizontal lines)
	top row spanning all columns = title 'Membership Options'
	two rows for each plan
*/
	$html = sumac_geth2_tabletag('membership2','plans','plans');
	$html .= sumac_geth2_trtag('membership2','plans_title','plans_title').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','plans_title','plans_title',3);
	$html .= sumac_geth2_sumac_subtitle('membership2','H6',1,true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$planCount = $planElements->length;
	for ($i = 0; $i < $planCount; $i++)
	{
		$html .= sumac_membership2_plan_rows($selectedPlanId,$planElements->item($i));
	}
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_membership2_plan_rows($selectedPlanId,$planElement)
{
/*
two rows:(1) radio button (spanning both rows), plan name, plan cost
		(2) plan description spanning last two columns

*/
/*
<!ELEMENT plan EMPTY>
	id ID #REQUIRED
	name CDATA #IMPLIED
	description CDATA #IMPLIED
	fee_cents CDATA #REQUIRED
	[extra_cents CDATA "0"]
	[extra_name CDATA ""]
	[extra_reason CDATA "none"]
	[total_cents CDATA #REQUIRED]
*/
	$id = $planElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$name = $planElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$description = $planElement->getAttribute(SUMAC_ATTRIBUTE_DESCRIPTION);
	$feeCents = $planElement->getAttribute(SUMAC_ATTRIBUTE_FEE_CENTS);
	if ($name == '')
	{
		$name = $description;
		$description = '';
	}

	$html = sumac_geth2_trtag('membership2','plan_'.$id,'plan').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','radio_'.$id,'radio',1,' rowspan="2"').PHP_EOL;
	$attrs = 'type="radio" onchange="sumac_membership2_select_new_plan(\''.$id.'\');" value="'.$id.'"'.(($id==$selectedPlanId)?' checked="checked"':'');
	$html .= sumac_geth2_input('sumac_input_membership2_plan','plan','sumac_plan',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','plan_'.$id,'plan');
	$html .= $name;
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','cost_'.$id,'cost');
	$html .= sumac_centsToPrintableDollars($feeCents);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;
	$html .= sumac_geth2_trtag('membership2','desc_'.$id,'desc').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','desc_'.$id,'desc',2);
	$html .= $description;
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;
	return $html;
}

function sumac_membership2_summary_table($optionsArray,$planElement,$selected,$extrasDocument)
{
/*
cost summary table
	top row spanning all columns = title 'Summary'
	one row for selected plan: name, cost
	one row for each plan EXTRA cost: name, cost
	one row for total
*/
/*
<!ELEMENT plan EMPTY>
	extra_cents CDATA "0"
	extra_name CDATA ""

	total_cents CDATA #REQUIRED
*/
/*
<!ELEMENT extra_cents (#PCDATA)>
	name CDATA #REQUIRED

<!ELEMENT total_cents (#PCDATA)>
*/
	$planId = $planElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$html = sumac_geth2_tabletag('membership2','summary_'.$planId,'summary');
	$html .= sumac_geth2_trtag('membership2','summary_title_'.$planId,'summary_title').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','summary_title_'.$planId,'summary_title',2);
	$html .= sumac_geth2_sumac_subtitle('membership2','H8',1,true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;
	$html .= sumac_geth2_trtag('membership2','summary_plan_'.$planId,'summary_plan').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','summary_plan_'.$planId,'summary_plan');
	$html .= $planElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','summary_cost_'.$planId,'summary_cost');
	$html .= sumac_centsToPrintableDollars($planElement->getAttribute(SUMAC_ATTRIBUTE_FEE_CENTS));
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	if ($selected && isset($_SESSION[SUMAC_SESSION_EXTRAS]))
	{
		$extraElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_EXTRA_CENTS);
		$extraCount = $extraElements->length;
		for ($i = 0; $i < $extraCount; $i++)
		{
			$extraElement = $extraElements->item($i);
			$extracents = ($extraElement->childNodes->item(0) != null) ? $extraElement->childNodes->item(0)->nodeValue : '0';
			$html .= sumac_geth2_trtag('membership2','extra_'.$i.'_'.$planId,'extra').PHP_EOL;
			$html .= sumac_geth2_tdtag('membership2','extraname_'.$i.'_'.$planId,'extraname');
			$html .= $extraElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$html .= '</td>'.PHP_EOL;
			$html .= sumac_geth2_tdtag('membership2','extracost_'.$i.'_'.$planId,'extracost');
			$html .= sumac_centsToPrintableDollars($extracents);
			$html .= '</td>'.PHP_EOL;
			$html .= '</tr>'.PHP_EOL;
		}
	}
	else
	//if we have not yet been sent an 'extras' document,
	//display the fee-related extra from the selected plan element - if it has one
	{
		if ($planElement->getAttribute(SUMAC_ATTRIBUTE_EXTRA_CENTS) != '0')
		{
			$html .= sumac_geth2_trtag('membership2','extra_basic_'.$planId,'extra').PHP_EOL;
			$html .= sumac_geth2_tdtag('membership2','extraname_basic_'.$planId,'extraname');
			$html .= $planElement->getAttribute(SUMAC_ATTRIBUTE_EXTRA_NAME);
			$html .= '</td>'.PHP_EOL;
			$html .= sumac_geth2_tdtag('membership2','extracost_basic_'.$planId,'extracost');
			$html .= sumac_centsToPrintableDollars($planElement->getAttribute(SUMAC_ATTRIBUTE_EXTRA_CENTS));
			$html .= '</td>'.PHP_EOL;
			$html .= '</tr>'.PHP_EOL;
		}
	}

	$totalcents = $planElement->getAttribute(SUMAC_ATTRIBUTE_TOTAL_CENTS);
	if ($selected && isset($_SESSION[SUMAC_SESSION_EXTRAS]))
	{
		$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
		$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
		unset($_POST["amountpaid"]);	//prevent previous amount from coming back again
	}
	$html .= sumac_geth2_trtag('membership2','total_'.$planId,'total').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','total_'.$planId,'total');
	$html .= sumac_geth2_sumac_subtitle('membership2','H9','_total',true);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','totalcost_'.$planId,'totalcost');
	$html .= sumac_centsToPrintableDollars($totalcents);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','totalcents_'.$planId,'nodisplay');
	$html .= $totalcents;
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_membership2_options_table($optionCount,$optionsArray,$planId,$planName,$selected)
{
/*
plan-options table:
[there are multiple plan options tables, one for each plan, only one being visible at a time]
[if there are no options at all, these tables will be invisible]
	top row spanning all columns = title $plan.' Options'
	one row for each option: option name, option cost, Add button and Remove button (only one visible)
*/
	$cls = ($optionCount > 0) ? 'options' : array('options','nodisplay');
	$html = sumac_geth2_tabletag('membership2','options_'.$planId,$cls);
	$html .= sumac_geth2_trtag('membership2','options_title_'.$planId,'options_title').PHP_EOL;
	$html .= sumac_geth2_tdtag('membership2','options_title_'.$planId,'options_title',4);
	$html .= sumac_geth2_sumac_subtitle('membership2','H7',1,true,$planName);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$optCount = count($optionsArray);
	$selOptCount = ($selected && isset($_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS]))
									? count($_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS]) : 0;
	for ($i = 0; $i < $optCount; $i++)
	{
		$optionElement = $optionsArray[$i];
		$optId = $optionElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$optSelected = false;
		if ($selected)
		{
			for ($j = 0; $j < $selOptCount; $j++)
			{
				if ($optId == $_SESSION[SUMAC_SESSION_MEMBERSHIP_OPTIONS][$j]) { $optSelected = true; break; }
			}
		}
		$html .= sumac_geth2_trtag('membership2','option_'.$planId.'_'.$optId,'option').PHP_EOL;
		$html .= sumac_geth2_tdtag('membership2','option_'.$planId.'_'.$optId,'option');
		$html .= $optionElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$html .= '</td>'.PHP_EOL;
		$html .= sumac_geth2_tdtag('membership2','optcost_'.$planId.'_'.$optId,'optcost');
		$html .= sumac_centsToPrintableDollars($optionElement->getAttribute(SUMAC_ATTRIBUTE_COST_CENTS));
		$html .= '</td>'.PHP_EOL;
		$optaddcls = $optSelected ? array('optadd','nodisplay') : 'optadd';
		$html .= sumac_geth2_tdtag('membership2','optadd_'.$planId.'_'.$optId,$optaddcls);
		$onclick = 'sumac_membership2_add_option(\''.$planId.'\',\''.$optId.'\');'; //set related option hidden variables; submit form
		$html .= sumac_geth2_sumac_button('membership2','optadd_'.$planId.'_'.$optId,array('button','optadd'),$onclick,'L3');
		$html .= '</td>'.PHP_EOL;
		$optremovecls = $optSelected ? 'optremove' : array('optremove','nodisplay');
		$html .= sumac_geth2_tdtag('membership2','optremove_'.$planId.'_'.$optId,$optremovecls);
		$onclick = 'sumac_membership2_remove_option(\''.$planId.'\',\''.$optId.'\');'; //set related option hidden variables; submit form
		$html .= sumac_geth2_sumac_button('membership2','optremove_'.$planId.'_'.$optId,array('button','optremove'),$onclick,'L4');
		$html .= '</td>'.PHP_EOL;
		$html .= '</tr>'.PHP_EOL;
	}

	if ($optCount < 1)
	{
		$html .= sumac_geth2_trtag('membership2','option_'.$planId.'_none','option').PHP_EOL;
		$html .= sumac_geth2_tdtag('membership2','option_'.$planId.'_none','option',3);
		$html .= sumac_geth2_spantext('membership2','H10');
		$html .= '</td>'.PHP_EOL;
		$html .= '</td>'.PHP_EOL;
	}

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_membership2_get_selectedplanid($accountDocument,$previousRequest)
{
	$planElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planCount = $planElements->length;
	$selectedPlanId = isset($_SESSION[SUMAC_SESSION_MEMBERSHIP_PLAN]) ? $_SESSION[SUMAC_SESSION_MEMBERSHIP_PLAN] : '';
	if ($previousRequest == '')	//if this is the opening display of this page
	{
		$currentPlanElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_CURRENT_PLAN);
		if (($currentPlanElements->length > 0) && ($currentPlanElements->item(0)->childNodes->item(0) != null))
		{
			$currentPlanId = $currentPlanElements->item(0)->childNodes->item(0)->nodeValue;
			//and make sure the current plan is still offered
			for ($i = 0; $i < $planCount; $i++)
			{
				if ($planElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID) == $currentPlanId)
				{
					$selectedPlanId = $currentPlanId;
					break;
				}
			}
		}
	}
	if ($selectedPlanId == '')	//if there is no plan either current or selected yet, select the first plan
	{
		$selectedPlanId = $planElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_ID);
	}
	return $selectedPlanId;
}

function sumac_membership2_get_amounttopay($accountDocument,$extrasDocument,$selectedPlanId)
{
	$totalcents = 0;
	$planElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_PLAN);
	$planCount = $planElements->length;
	if (isset($_SESSION[SUMAC_SESSION_EXTRAS]))
	{
		$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
		$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	}
	else
	{
		for ($i = 0; $i < $planCount; $i++)
		{
			$planElement = $planElements->item($i);
			$planId = $planElement->getAttribute(SUMAC_ATTRIBUTE_ID);
			if ($planId == $selectedPlanId)
			{
				$totalcents = $planElement->getAttribute(SUMAC_ATTRIBUTE_TOTAL_CENTS);
				break;
			}
		}
	}
	return $totalcents;
}

?>