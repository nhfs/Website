<?php
//version568//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_login2.php';
include_once 'sumac_payment2.php';
include_once 'sumac_xml.php';

function sumac_donation2($previousRequest,$responseDocument,$responseMessage,$formhandler,$loadedAccountDocument)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$paymentCardElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PAYMENT_CARD);
//there must be at least one way to make payment (and the DTD doesnt enforce it)
	if ($paymentCardElements->length < 1)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_PAYMENT_CARD . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$accountDocument = null;
	if ($loadedAccountDocument != null) $accountDocument = $loadedAccountDocument;
	else
	{
		$savedAccountDocument = sumac_reloadLoginAccountDocument();
		if ($savedAccountDocument !== false) $accountDocument = $savedAccountDocument;
	}

	$html = sumac_donation2_HTML($organisationDocument,$formhandler,$accountDocument,$previousRequest,$responseDocument,$responseMessage);
	echo $html;
	return true;

}

function sumac_donation2_HTML($organisationDocument,$formhandler,$accountDocument,$previousRequest,$responseDocument,$responseMessage)
{
	return sumac_geth2_head('donation2')
			.sumac_donation2_body($organisationDocument,$formhandler,$accountDocument,$previousRequest,$responseDocument,$responseMessage);
}

function sumac_donation2_body($organisationDocument,$formhandler,$accountDocument,$previousRequest,$responseDocument,$responseMessage)
{
	$fixedamount = $_SESSION[SUMAC_USERPAR_D2FIXEDAMT];
	if ($fixedamount != '') $fixedamount = sumac_centsToPrintableDollars($_SESSION[SUMAC_USERPAR_D2FIXEDAMT],false,($fixedamount % 100 != 0),false);
	$retryform = ($previousRequest != '') ? 'all' : null;
	$extrajs = '';
	$extrajs .= sumac_donation2_get_warningjs();
	$fundElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FUND);
	$fundElementArray = sumac_donation2_get_fund_array($fundElements);
	if (count($fundElementArray) >= 1)
	{
		$extrajs .= 'document.getElementById(\'sumac_select_donation2_funds\').options[0].text'
					.' = sumac_get_string_from_id(\'D2\',\'J3\',[\''.count($fundElementArray).'\']);';
	}

	return '<body>'.PHP_EOL
			.(($previousRequest == '') ? sumac_addParsedXmlIfDebugging($organisationDocument,'donation2_organisation') : '')
			.(($previousRequest != '') ? sumac_addParsedXmlIfDebugging($accountDocument,'donation2_accountdetails') : '')
			.(($responseDocument != null) ? sumac_addParsedXmlIfDebugging($responseDocument,'donation2_response') : '')
			.sumac_geth2_user('top','donation2')
			.sumac_donation2_content($organisationDocument,$fundElementArray,$formhandler,$accountDocument,$previousRequest,$responseMessage)
			.sumac_geth2_body_footer('donation2',true,$retryform,$fixedamount,$extrajs);
}

function sumac_donation2_get_warningjs()
{
	$amount = 0;
	if ($_SESSION[SUMAC_USERPAR_D2FIXEDAMT] != '') $amount = $_SESSION[SUMAC_USERPAR_D2FIXEDAMT];
	else if (isset($_POST["amountpaid"])) $amount = $_POST["amountpaid"];
	$warningid = 'PI1';
	if ((substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'M')
		|| ((substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'C')
			&& (isset($_POST["makemonthly"]) && ($_POST["makemonthly"] == 'on'))))
	{
		$warningid = 'PI2';
	}
	return 'document.getElementById(\'sumac_sid_D2PI1\').innerHTML = '
			. 'sumac_get_string_from_id(\'D2\',\''.$warningid.'\','
			. '[sumac_get_string_from_id(\'D2\',\'PL1\'),sumac_centsToPrintableDollars('.$amount.')]);';
}

function sumac_donation2_content($organisationDocument,$fundElementArray,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_sumac_div_hide_mainpage('donation2',sumac_geth2_spantext('donation2','H4'));

	$html .= sumac_geth2_divtag('donation2','mainpage','mainpage');
	$html .= sumac_donation2_instructions();
	$html .= sumac_donation2_HTMLform($organisationDocument,$fundElementArray,$formhandler,$accountDocument,$previousRequest,$responseMessage);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content

	return $html;
}

function sumac_donation2_instructions()
{
	$html = sumac_geth2_divtag('donation2','instructions','instructions');
	$html .= sumac_geth2_spantext('donation2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_donation2_HTMLform($organisationDocument,$fundElementArray,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_formtag('donation2','all','donation',$formhandler);
	$html .= sumac_donation2_amount_section($organisationDocument);
	$html .= sumac_donation2_details_section($organisationDocument,$fundElementArray);
	$html .= sumac_login2_embedded('donation2','D',($_SESSION[SUMAC_USERPAR_D2NOLOGIN]?'C':'CL'),
									$organisationDocument,$accountDocument,$previousRequest,$responseMessage,null,'login');
	$html .= sumac_payment2('donation2','D',$_SESSION[SUMAC_USERPAR_D2FIXEDAMT],(substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'M'),
								$organisationDocument,$accountDocument,$previousRequest,$responseMessage);
	$html .= '</form>'.PHP_EOL;
	return $html;
}

function sumac_donation2_amount_section($organisationDocument)
{
	$html = sumac_geth2_divtag('donation2','amount','titled');
	if ($_SESSION[SUMAC_USERPAR_D2FIXEDAMT] != '')
	{
		$html .= sumac_geth2_sumac_title_with_gobacklink_and_line('donation2','H3','L1');
	}
	else
	{
		$html .= sumac_geth2_sumac_title_with_gobacklink_and_line('donation2','H1','L1');
		$html .= sumac_donation2_amounts_table();
	}
	$monthlyoptional = (substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'C');
	$deductiondays = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_DEDUCTION_DAY);
	if ($monthlyoptional || ((substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'M') && (count($deductiondays) >= 1)))
	{
		$html .= sumac_donation2_monthly_table($deductiondays);
	}
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_donation2_amounts_table()
{
	$amountslist = explode(';',$_SESSION[SUMAC_USERPAR_D2AMOUNTS]);
	$frequency = substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1);
	$html = sumac_geth2_tabletag('donation2','amounts','amounts');
	if ((count($amountslist)<1) || ((count($amountslist)==1) && ($amountslist[0]=='')))
	{
		$html .= sumac_geth2_trtag('donation2','amounts','amounts').sumac_geth2_tdtag('donation2','amount0','donation_amount');
		$attrs = 'data-sumac-reqby="D" type="text" size="8" maxlength="12" value="" oninput="sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
		$html .= sumac_geth2_input_with_label('donation2','amount0','input','onlyamount',$attrs,'F2','before');
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	else
	{
		//$rows = ceiling(count($amountslist)/4);
		$centslist = array();
		for ($i = 0, $j = 0; $i < count($amountslist); $i++)
		{
			if (is_numeric($amountslist[$i])) $centslist[$j++] = $amountslist[$i] * 100; //if valid, convert to cents
		}
		for ($i = 0; $i < count($centslist); $i = $i+4)
		{
			$html .= sumac_geth2_trtag('donation2','amounts'.round($i/4),'amounts');
			$next = min(($i+4),count($centslist));
			for ($j = $i; $j < $next; $j++)
			{
				$html .= sumac_geth2_tdtag('donation2','amount'.($j+1),'donation_amount',1);
				$centsamount = $centslist[$j];
				$dollaramount = sumac_centsToPrintableDollars($centsamount,false,($centsamount % 100 != 0),false);
				$attrs = (($i==0)?'data-sumac-reqby="D" ':'').'type="radio" value="'.$centsamount.'" onclick="sumac_set_not_required(\'sumac_input_donation2_other\',\'P\');sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
				$html .= sumac_geth2_input_with_label('donation2','amount'.($j+1),'input','donationamount',$attrs,$dollaramount,'after',false);
				$html .= '</td>'.PHP_EOL;
			}
			if (count($centslist)<($i+4))
			{
				$html .= sumac_geth2_tdtag('donation2','amount0','donation_amount',3);
				//$attrs = 'data-sumac-reqby="D" type="radio" value=""';
				$attrs = 'type="radio" value="" onclick="sumac_set_required(\'sumac_input_donation2_other\',\'P\');sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
				$html .= sumac_geth2_input_with_label('donation2','amount0','input','donationamount',$attrs,'F1','after',true); //this will have id 'sumac_input_donation2_amount0'
				$attrs = 'type="text" size="8" maxlength="12" value="" onclick="sumac_set_checked(\'sumac_input_donation2_amount0\');sumac_set_required(this.id,\'P\');" oninput="sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
				$html .= sumac_geth2_input('sumac_input_donation2_other','input','otheramount',$attrs);
				$html .= '</td>'.PHP_EOL;
			}
			else
			{
				//two empty TDs to allow the amount field more space if necessary
				$html .= '<td></td><td></td>'.PHP_EOL;
			}
			$html .= '</tr>'.PHP_EOL;
		}
		if (count($centslist)%4==0)
		{
			$html .= sumac_geth2_trtag('donation2','amounts'.round((count($centslist)+1)/4),'amounts');
			$html .= sumac_geth2_tdtag('donation2','amount0','donation_amount',6);
			//$attrs = 'data-sumac-reqby="D" type="radio" value=""';
			$attrs = 'type="radio" value="" onclick="sumac_set_required(\'sumac_input_donation2_other\',\'P\');sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
			$html .= sumac_geth2_input_with_label('donation2','amount0','input','donationamount',$attrs,'F1','after',true); //this will have id 'sumac_input_donation2_amount0'
			$attrs = 'type="text" size="8" maxlength="12" value="" onclick="sumac_set_checked(\'sumac_input_donation2_amount0\');sumac_set_required(this.id,\'P\');" oninput="sumac_donation2_show_amounttopay(\''.$frequency.'\');"';
			$html .= sumac_geth2_input('sumac_input_donation2_other','input','otheramount',$attrs);
			$html .= '</td>'.PHP_EOL;
			$html .= '</tr>'.PHP_EOL;
		}
	}
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_donation2_monthly_table($deductiondays)
{
	$html = sumac_geth2_tabletag('donation2','monthly','monthly_details');
	$html .= sumac_geth2_trtag('donation2','monthly','monthly').PHP_EOL;

	if (substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'C')
	{
		$html .= sumac_geth2_tdtag('donation2','makemonthly','monthly').PHP_EOL;
		$attrs = 'type="checkbox" onclick="sumac_donation2_change_frequency();sumac_donation2_show_amounttopay(\'C\');"';
		$html .= sumac_geth2_input_with_label('donation2','makemonthly','input','makemonthly',$attrs,'F3','after');
		$html .= '</td>'.PHP_EOL;
	}

	$html .= sumac_geth2_tdtag('donation2','frequency','frequency');
	$attrs = 'type="hidden" value="'.((substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'M')?'1':'-1').'"';
	$html .= sumac_geth2_input('sumac_input_donation2_frequency','hidden','frequency',$attrs);
	$html .= '</td>'.PHP_EOL;

	if ((count($deductiondays) >= 1)
		&& ((substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'C') || (substr($_SESSION[SUMAC_USERPAR_D2FREQUENCY],0,1) == 'M')))
	{
		$html .= sumac_geth2_tdtag('donation2','deddays','deddays');
		$html .= sumac_geth2_spantext('donation2','F4');
		$html .= '</td>'.PHP_EOL;
		for ($i = 0; $i < count($deductiondays); $i++)
		{
			$html .= sumac_geth2_tdtag('donation2','dedday','dedday');
			$attrs = 'type="radio" value="'.$i.'"'.(($i==0)?' checked="checked"':'');
			$html .= sumac_geth2_input_with_label('donation2','dedday'.$i,'input','deductionday',$attrs,$deductiondays[$i],'after',false);
			$html .= '</td>'.PHP_EOL;
		}
	}

	$html .= '</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_donation2_details_section($organisationDocument,$fundElementArray)
{
	$sectionclass = 'titled';
	if (count($fundElementArray) < 1) $sectionclass .= ' sumac_optional_dedicatee';
// by adding 'optional_dedicatee' to the class when there is no choice of funds,
//we ensure that the whole section will be hidden if there is also no dedicatee message
	$html = sumac_geth2_divtag('donation2','details',$sectionclass);
	$html .= sumac_geth2_sumac_title_with_line('donation2','H2');
	$html .= sumac_donation2_details_table($organisationDocument,$fundElementArray);
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_donation2_details_table($organisationDocument,$fundElementArray)
{
	$keywordJS = '';
	$html = sumac_geth2_tabletag('donation2','details','data_entry');

	$html .= sumac_geth2_trtag('donation2','funds','details');
	$html .= sumac_geth2_tdtag('donation2','funds','details');
	if (count($fundElementArray) >= 1)
	{
		$oldFundCount = sumac_donation2_old_funds($fundElementArray);
		$options = sumac_donation2_fund_options($fundElementArray,$oldFundCount);
		$attrs = $_SESSION[SUMAC_USERPAR_D2FUNDREQD] ? 'data-sumac-reqby="D"' : '';
		$html .= sumac_geth2_select_with_label('donation2','funds','select','fund',$attrs,$options,'F5','above',true);
		$kwarray = sumac_donation2_fund_keywords($fundElementArray);
		if (count($kwarray) > 0)
		{
			$keywordJS = '<script type="text/javascript">var sumac_fundkws = [';
			for ($i = 0; $i < count($kwarray); $i++) $keywordJS .= "'".$kwarray[$i]."',";
			$keywordJS .= '];</script>'.PHP_EOL;
		}
		if (count($kwarray) > 0)	//@@@ possibly not the best test
		{

			$html .= '</td>'.PHP_EOL;

			$html .= sumac_geth2_tdtag('donation2','filter','filter');
			$attrs = 'type="text" size="15" maxlength="200" autocomplete="off" value="" oninput="sumac_donation2_filter_dropdown(this.value);"';
			$html .= sumac_geth2_input_with_label('donation2','fundfilter','input','',$attrs,'F7','above',true);
			$html .= '</td>'.PHP_EOL;

			$html .= sumac_geth2_tdtag('donation2','kw_matches','kw_matches');
			$html .= '</td>'.PHP_EOL;

			$html .= '</tr>'.PHP_EOL;

			$html .= sumac_geth2_trtag('donation2','funds_hidden',array('details','nodisplay'));
			$html .= sumac_geth2_tdtag('donation2','funds','details',3);
			$html .= sumac_geth2_select('sumac_select_donation2_funds_hidden',array('select','nodisplay'),'','',$options);
		}

		if ($oldFundCount > 0)
		{
			$attrs = 'type="hidden" value="'.$oldFundCount.'"';
			$html .= sumac_geth2_input('sumac_input_donation2_oldfunds','hidden','oldfunds',$attrs);
		}
	}
	else
	{
		$attrs = 'type="hidden" value="-1"';
		$html .= sumac_geth2_input('sumac_input_donation2_fund','hidden','fund',$attrs);
		$attrs = 'type="hidden" value="0"';
		$html .= sumac_geth2_input('sumac_input_donation2_oldfunds','hidden','oldfunds',$attrs);
	}
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag('donation2','dedicatee','details');
	$html .= sumac_geth2_tdtag('donation2','dedicatee','optional_dedicatee');
	$attrs = 'type="text" size="25" maxlength="255" value=""';
	$html .= sumac_geth2_input_with_label('donation2','dedicatee','input','dedicatee',$attrs,'F6','above',true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag('donation2','notes','details');
	$html .= sumac_geth2_tdtag('donation2','notes','optional_notes');
	$attrs = 'type="text" size="25" maxlength="255"';
	$html .= sumac_geth2_textarea_with_label('donation2','notes','textarea','notes',$attrs,'','F8','above',true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag('donation2','alias','details');
	$html .= sumac_geth2_tdtag('donation2','alias','optional_alias');
	$attrs = 'type="text" size="25" maxlength="255" value=""';
	$html .= sumac_geth2_input_with_label('donation2','alias','input','alias',$attrs,'F9','above',true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	$html .= $keywordJS;
	return $html;
}

function sumac_donation2_fund_options($fundElementArray,$oldFundCount)
{
	$fundCount = count($fundElementArray);
	$html = '<option style="font-style:italic;" value="-1"></option>';	// count is inserted by javascript
	for ($i = 0; $i < $fundCount; $i++)
	{
		$fundElement = $fundElementArray[$i];
		$fundId = $fundElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$fundKeys = $fundElement->getAttribute(SUMAC_ATTRIBUTE_KEYWORDS);
		$fundName = $fundId;
		if ($fundElement->childNodes->item(0) != null) $fundName = $fundElement->childNodes->item(0)->nodeValue;
		$optionValue = ($oldFundCount > 0) ? $i : $fundId; //if any funds didn't have an ID then we must use the index
		$html .= '<option value="' . $optionValue. '" title="' . $fundKeys . '">' . $fundName . '</option>';
	}
	return $html;
}

function sumac_donation2_fund_keywords($fundElementArray)
{
	$kwarray = array();
	if ($_SESSION[SUMAC_USERPAR_D2FUNDKEY] != '') return $kwarray;	// suppress fund searching when 'fundkey' has been supplied
	$ukwarray = array();
	$fundCount = count($fundElementArray);
	for ($i = 0; $i < $fundCount; $i++)
	{
		$fundElement = $fundElementArray[$i];
		$fundKeys = $fundElement->getAttribute(SUMAC_ATTRIBUTE_KEYWORDS);
		if (($fundKeys != null) && ($fundKeys != ''))
		{
			$fundkwa = array_values(array_filter(explode(';',$fundKeys)));
			for ($j = 0; $j < count($fundkwa); $j++)
			{
				$kw = trim($fundkwa[$j]);
				$ukw = strtoupper($kw);
				if (in_array($ukw,$ukwarray) == false)
				{
					$ukwarray[] = $ukw;
					$kwarray[] = $kw;
				}
			}
		}
	}
	sort($kwarray);
	return $kwarray;
}

function sumac_donation2_old_funds($fundElementArray)
{
	$oldFundCount = 0;
	$fundCount = count($fundElementArray);
	for ($i = 0; $i < $fundCount; $i++)
	{
		$fundElement = $fundElementArray[$i];
		$fundId = $fundElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		if (($fundId == null) || ($fundId == '')) $oldFundCount++;
	}
	return $oldFundCount;
}

function sumac_donation2_get_fund_array($fundElements)
{
	$fundElementArray = array();
	$fundCount = $fundElements->length;
	$fundKey = $_SESSION[SUMAC_USERPAR_D2FUNDKEY];
	$selectables = explode(',',$fundKey);
	for ($i = 0; $i < $fundCount; $i++)
	{
		$fundElement = $fundElements->item($i);
		if ($fundKey != '')
		{
			$fundKeys = $fundElement->getAttribute(SUMAC_ATTRIBUTE_KEYWORDS);
			$matched = false;
			for ($j = 0; $j < count($selectables); $j++)
			{
				$selectable = trim($selectables[$j]);
				if (stripos($fundKeys,$selectable) !== false)
				{
					$matched = true;
					break;
				}
			}
			if ($matched === false) continue;
		}
		$fundElementArray[] = $fundElement;
	}
	return $fundElementArray;
}

?>