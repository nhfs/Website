<?php
//version530//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

function sumac_payment2($pkg,$buttoncode,$fixedPaymentAmount,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
{
	return sumac_payment2_section($pkg,$buttoncode,$organisationDocument)
		.sumac_payment2_pay_section($pkg,$buttoncode,$fixedPaymentAmount,$organisationDocument,$accountDocument,$previousRequest,$responseMessage);
}

function sumac_payment2_section($pkg,$buttoncode,$organisationDocument)
{
	$html = sumac_geth2_divtag($pkg,'payment','titled');
	$html .= sumac_geth2_sumac_title_with_line($pkg,'PH1');
	$html .= sumac_payment2_table($pkg,$buttoncode,$organisationDocument);
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_payment2_table($pkg,$buttoncode,$organisationDocument)
{
	$html = sumac_geth2_tabletag($pkg,'payment','data_entry');
	$cols = 2;
	$reqby = ' data-sumac-reqby="'.$buttoncode.'"';
	$paymentCards = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_PAYMENT_CARD);
	$html .= sumac_geth2_trtag($pkg,'cardtype','data_entry').sumac_geth2_tdtag($pkg,'cardtype','entry_field',$cols);
	$html .= sumac_payment2_cardtype_table($pkg,$organisationDocument,$reqby);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'carddetail','carddetails');
	$html .= sumac_geth2_tdtag($pkg,'cardnumber','entry_field');
	$attrs = 'type="text" autocomplete="off" size="25" maxlength="25" value=""'.$reqby;
	$html .= sumac_geth2_input_with_label($pkg,'cardnumber','input','cardnumber',$attrs,'PF1','above',true);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag($pkg,'expiration','entry_table');
	$table = sumac_payment2_expiration_table($pkg,$reqby);
	$html .= sumac_geth2_element_with_label($pkg,'expiration',$table,'table','PF2','above',true);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'cardsecurity','data_entry').sumac_geth2_tdtag($pkg,'cardsecurity','entry_field',$cols);
	$attrs = 'type="password" autocomplete="off" size="4" maxlength="4" value=""'.$reqby;
	$html .= sumac_geth2_input_with_label($pkg,'cardsecurity','input','cardsecurity',$attrs,'PF5','above',true);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'carduser','data_entry').sumac_geth2_tdtag($pkg,'carduser','entry_field',$cols);
	$attrs = 'type="text" size="35" maxlength="35" value=""'.$reqby;
	$html .= sumac_geth2_input_with_label($pkg,'carduser','input','carduser',$attrs,'PF6','above',true);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_payment2_cardtype_table($pkg,$organisationDocument,$reqby)
{
	$html = sumac_geth2_tabletag($pkg,'cardtype','data_select_radio');
	$paymentCards = sumac_getElementValuesAsArray($organisationDocument,SUMAC_ELEMENT_PAYMENT_CARD);

	$html .= sumac_geth2_trtag($pkg,'cardtype','cardtypes');
	for ($i = 0; $i < count($paymentCards); $i++)
	{
		$html .= sumac_geth2_tdtag($pkg,'cardtype'.$i,'cardtype');
		$cardname = $paymentCards[$i];
		$attrs = 'type="radio" value="'.$cardname.'"'.(($i==0)?$reqby:'');
		$html .= sumac_geth2_input_with_label($pkg,'cardtype'.$i,'input','cardtype',$attrs,$cardname,'after',false);
		$html .= '</td>'.PHP_EOL;
	}
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_payment2_expiration_table($pkg,$reqby)
{
	$html = sumac_geth2_tabletag($pkg,'expiration','data_selections');
	$html .= sumac_geth2_trtag($pkg,'expiration','expiration_dates');

	$html .= sumac_geth2_tdtag($pkg,'cardexpmonth','cardexpmonth');
	$selectid = 'sumac_select_'.$pkg.'_cardexpmonth';
	$html .= sumac_geth2_select($selectid,'select','cardexpmonth',$reqby,sumac_getHTMLFormOptionsForExpiryMonth(sumac_geth2_textid_for_span($pkg,'PF3')));
	$html .= '</td>'.PHP_EOL;

	$html .= sumac_geth2_tdtag($pkg,'cardexpyear','cardexpyear');
	$selectid = 'sumac_select_'.$pkg.'_cardexpyear';
	$html .= sumac_geth2_select($selectid,'select','cardexpyear',$reqby,sumac_getHTMLFormOptionsForExpiryYear(sumac_geth2_textid_for_span($pkg,'PF4')));
	$html .= '</td>'.PHP_EOL;

	$html .= '</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_payment2_pay_section($pkg,$buttoncode,$fixedPaymentAmount,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_divtag($pkg,'pay','untitled');

	$html .= sumac_payment2_pay_status_table($pkg,$accountDocument,$previousRequest,$responseMessage);

	$html .= sumac_geth2_tabletag($pkg,'pay_button','pay_button');
	$html .= sumac_geth2_trtag($pkg,'pay_button','pay_button');

	$html .= sumac_geth2_tdtag($pkg,'paybutton','pay_button titlebutton');
	$attrs = 'type="submit" value="" onclick="sumac_'.$pkg.'_set_amountpaid(); if (sumac_check_for_missing_fields(\''.$buttoncode.'\',\''.sumac_geth2_textid_for_span($pkg,(($accountDocument == null)?'PE1':'PE2')).'\')) return false;"';
	$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input',(($fixedPaymentAmount == '')?'PL1':'PL2')),'submit','pay',$attrs);
	$html .= '</td>'.PHP_EOL;

	$html .= sumac_geth2_tdtag($pkg,'amountpaid','amountpaid');
	$attrs = 'type="hidden" value="'.$fixedPaymentAmount.'"';	//will be set by JS if not fixed
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_amountpaid','hidden','amountpaid',$attrs);
	$html .= '</td>'.PHP_EOL;

	$html .= '</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;

	return $html;
}

function sumac_payment2_pay_status_table($pkg,$accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag($pkg,'pay_status','pay_status');

	if ($previousRequest == 'pay')
	{
		$html .= sumac_geth2_trtag($pkg,'pay_status','pay_status').sumac_geth2_tdtag($pkg,'pay_status','status',1,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag($pkg,'payment_entry_status','pay_status').sumac_geth2_tdtag($pkg,'payment_entry_status','nodisplay');
	$html .= sumac_geth2_spantext($pkg,(($accountDocument == null)?'PE1':'PE2'));
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

?>