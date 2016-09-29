<?php
//version530//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

function sumac_login2($pkg,$buttoncode,$onlynewuser,$accountDocument,$previousRequest,$responseMessage,$goBackLink)
{
	if ($accountDocument == null)
	{
		return sumac_login2_personal_section($pkg,$buttoncode,$onlynewuser,$previousRequest,$responseMessage,$goBackLink);
	}
	else
	{
		return sumac_login2_known_person_section($pkg,$accountDocument,$goBackLink);
	}
}

function sumac_login2_personal_section($pkg,$buttoncode,$onlynewuser,$previousRequest,$responseMessage,$goBackLink)
{
	$html = sumac_geth2_divtag($pkg,'personal','titled');
	if ($goBackLink == null) $html .= sumac_geth2_sumac_title_with_line($pkg,'NH1');
	else $html .= sumac_geth2_sumac_title_with_gobacklink_and_line($pkg,'NH1',$goBackLink);
	$html .= sumac_login2_personal_table($pkg,$buttoncode,$onlynewuser,$previousRequest,$responseMessage);
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_login2_personal_table($pkg,$buttoncode,$onlynewuser,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag($pkg,'personal','personal_info');

	$html .= sumac_geth2_trtag($pkg,'personal','personal');

	$html .= sumac_geth2_tdtag($pkg,'name_and_address','data_table');

	if ($onlynewuser === false)
	{
		$html .= sumac_geth2_sumac_subtitle($pkg,'NH2',1);
		$html .= sumac_geth2_sumac_subtitle($pkg,'NH3',2);
	}

	$html .= sumac_login2_name_and_address_table($pkg,$buttoncode,$onlynewuser);
	$html .= '</td>'.PHP_EOL;

	if ($onlynewuser === false)
	{
		$html .= sumac_geth2_tdtag($pkg,'login','data_table');
		$html .= sumac_geth2_sumac_subtitle($pkg,'NH4',1);
		$html .= sumac_geth2_sumac_subtitle($pkg,'NH5',2);
		$html .= sumac_login2_login_table($pkg,$previousRequest,$responseMessage);
		$html .= '</td>'.PHP_EOL;
	}

	$html .= '</tr>'.PHP_EOL;

	if ($previousRequest == 'adduser')
	{
		$html .= sumac_geth2_trtag($pkg,'adduser_status','adduser_status').sumac_geth2_tdtag($pkg,'adduser_status','status',2,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_login2_name_and_address_table($pkg,$buttoncode,$onlynewuser)
{
	$html = sumac_geth2_tabletag($pkg,'name_and_address','data_entry');

	$cols = 1;
	$ph = ' placeholder="'.SUMAC_FIELD_OPTIONAL.'"';
	$reqby = ' data-sumac-reqby="'.$buttoncode.'"';
	$html .= sumac_geth2_trtag($pkg,'names','data_entry');
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_FIRSTNAME) === false)
	{
		$html .= sumac_geth2_tdtag($pkg,'firstname','entry_field');
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_FIRSTNAME) === false);
		$attrs = 'type="text" size="20" maxlength="20" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'firstname','input','firstname',$attrs,'NF1','above',true);
		$html .= '</td>'.PHP_EOL;
		$cols = 2;
	}
	$html .= sumac_geth2_tdtag($pkg,'lastname','entry_field');
	$attrs = 'type="text" size="30" maxlength="30" value=""'.$reqby;
	$html .= sumac_geth2_input_with_label($pkg,'lastname','input','lastname',$attrs,'NF2','above',true);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_ADDRESS) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'address','data_entry').sumac_geth2_tdtag($pkg,'address','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_ADDRESS) === false);
		$attrs = 'type="text" size="30" maxlength="35" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'address1','input','address1',$attrs,'NF3','above',true);
		$attrs = 'type="text" size="30" maxlength="35" value=""';
		$html .= '<br />'.sumac_geth2_input('sumac_input_'.$pkg.'_address2','input','address2',$attrs);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_CITY) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'city','data_entry').sumac_geth2_tdtag($pkg,'city','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_CITY) === false);
		$attrs = 'type="text" size="30" maxlength="35" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'city','input','city',$attrs,'NF4','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_PROVINCE) === false)
	{

		$html .= sumac_geth2_trtag($pkg,'province','data_entry').sumac_geth2_tdtag($pkg,'province','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_PROVINCE) === false);

		$dropdownoptions = '';
		$provinceOrStateDropdownFilename = sumac_login2_get_dropdown_filename($pkg);
		if ($provinceOrStateDropdownFilename != '')
		{
			switch ($provinceOrStateDropdownFilename)
			{
				case 'can':
					$filename = 'sumac_can_province_dropdown.htm';
					break;
				case 'us':
					$filename = 'sumac_us_state_dropdown.htm';
					break;
				case 'uscan':
					$filename = 'sumac_states_and_provinces_dropdown.htm';
					break;
				case 'canus':
					$filename = 'sumac_provinces_and_states_dropdown.htm';
					break;
				default:
					$filename = '';
					break;
			}
			if ($filename != '') $dropdownoptions = sumac_getFileContents('.',$filename);
			else $dropdownoptions = sumac_getFileContents('user',$provinceOrStateDropdownFilename);
		}
		if ($dropdownoptions != '')
		{
			$html .= sumac_geth2_select_with_label($pkg,'province','select','province',($isopt?'':$reqby),$dropdownoptions,'NF5','above',true);
		}
		else
		{
			$attrs = 'type="text" size="20" maxlength="35" value=""'.($isopt?$ph:$reqby);
			$html .= sumac_geth2_input_with_label($pkg,'province','input','province',$attrs,'NF5','above',true);
		}
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_POSTCODE) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'postcode','data_entry').sumac_geth2_tdtag($pkg,'postcode','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_POSTCODE) === false);
		$attrs = 'type="text" size="10" maxlength="10" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'postcode','input','postcode',$attrs,'NF6','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_COUNTRY) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'country','data_entry').sumac_geth2_tdtag($pkg,'country','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_COUNTRY) === false);
		$attrs = 'type="text" size="20" maxlength="35" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'country','input','country',$attrs,'NF7','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_PHONE) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'phone','data_entry').sumac_geth2_tdtag($pkg,'phone','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_PHONE) === false);
		$attrs = 'type="text" size="16" maxlength="25" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'phone','input','phone',$attrs,'NF8','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	if (sumac_login2_omitted_field($pkg,SUMAC_FIELD_CELLPHONE) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'cellphone','data_entry').sumac_geth2_tdtag($pkg,'cellphone','entry_field',$cols);
		$isopt = (sumac_login2_mandatory_field($pkg,SUMAC_FIELD_CELLPHONE) === false);
		$attrs = 'type="text" size="16" maxlength="25" value=""'.($isopt?$ph:$reqby);
		$html .= sumac_geth2_input_with_label($pkg,'cellphone','input','cellphone',$attrs,'NF9','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag($pkg,'email','data_entry').sumac_geth2_tdtag($pkg,'email','entry_field',$cols);
	$attrs = 'type="text" size="30" maxlength="60" value=""'.$reqby;
	$html .= sumac_geth2_input_with_label($pkg,'email','input','email',$attrs,'NF10','above',true);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	if (($onlynewuser === false) && (sumac_login2_no_user_password($pkg) === false))
	{
		$html .= sumac_geth2_trtag($pkg,'newpassword','data_entry').sumac_geth2_tdtag($pkg,'newpassword','entry_field',$cols);
		$attrs = 'type="password" size="20" maxlength="20" value=""'.$reqby;
		$html .= sumac_geth2_input_with_label($pkg,'newpassword','input','newpassword',$attrs,'NF11','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_login2_login_table($pkg,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag($pkg,'login_info','data_entry');

	$cols = (sumac_login2_no_user_password($pkg) === false) ? 2 : 1;
	$html .= sumac_geth2_trtag($pkg,'loginemail','data_entry').sumac_geth2_tdtag($pkg,'loginemail','entry_field',$cols);
	$attrs = 'data-sumac-reqby="LF" type="text" size="30" maxlength="60" value=""';
	$html .= sumac_geth2_input_with_label($pkg,'loginemail','input','loginemail',$attrs,'NF12','above',true);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	if (sumac_login2_no_user_password($pkg) === false)
	{
		$html .= sumac_geth2_trtag($pkg,'loginpassword','data_entry').sumac_geth2_tdtag($pkg,'loginpassword','entry_field',$cols);
		$attrs = 'data-sumac-reqby="L" type="password" size="20" maxlength="20" value=""';
		$html .= sumac_geth2_input_with_label($pkg,'loginpassword','input','loginpassword',$attrs,'NF13','above',true);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}

	if (($previousRequest == 'login') || ($previousRequest == 'login-np') || ($previousRequest == 'requestpassword'))
	{
		$html .= sumac_geth2_trtag($pkg,'login_status','login_status').sumac_geth2_tdtag($pkg,'login_status','status',$cols,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag($pkg,'login_entry_status','login_status').sumac_geth2_tdtag($pkg,'login_entry_status','nodisplay',$cols);
	$html .= sumac_geth2_spantext($pkg,'NE1');
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	$html .= sumac_geth2_trtag($pkg,'forgotten_entry_status','login_status').sumac_geth2_tdtag($pkg,'forgotten_entry_status','nodisplay',$cols);
	$html .= sumac_geth2_spantext($pkg,'NE2');
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'loginbutton','buttons');
	$html .= sumac_geth2_tdtag($pkg,'loginbutton','login_button');
	$attrs = 'type="submit" value="" onclick="if (sumac_check_for_missing_fields(\'L\',\''.sumac_geth2_textid_for_span($pkg,'NE1').'\')) return false;"';
	$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input','NL1'),'submit','login',$attrs);
	$html .= '</td>'.PHP_EOL;

	if (sumac_login2_no_user_password($pkg) === false)
	{
		$html .= sumac_geth2_tdtag($pkg,'forgotpwd','forgot_password_button');
		$attrs = 'type="submit" value="" onclick="if (sumac_check_for_missing_fields(\'F\',\''.sumac_geth2_textid_for_span($pkg,'NE2').'\')) return false;"';
		$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input','NL2'),'submit','requestpassword',$attrs);
		$html .= '</td>'.PHP_EOL;
	}
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_login2_known_person_section($pkg,$accountDocument,$goBackLink)
{
	$html = sumac_geth2_divtag($pkg,'knowndetails','titled');
	if ($goBackLink == null) $html .= sumac_geth2_sumac_title_with_line($pkg,'NH6');
	else $html .= sumac_geth2_sumac_title_with_gobacklink_and_line($pkg,'NH6',$goBackLink);

	$html .= sumac_geth2_tabletag($pkg,'knownperson','known_personal_info');

	$html .= sumac_geth2_trtag($pkg,'knownname','known_data').sumac_geth2_tdtag($pkg,'knownname','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_FIRSTNAME).'&nbsp;'
				.sumac_getElementValue($accountDocument,SUMAC_ELEMENT_LASTNAME);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knownaddress','known_data').sumac_geth2_tdtag($pkg,'knownaddress','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_ADDRESSLINE1).'<br />'
				.sumac_getElementValue($accountDocument,SUMAC_ELEMENT_ADDRESSLINE2);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knowncity','known_data').sumac_geth2_tdtag($pkg,'knowncity','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_CITY);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knownprovince','known_data').sumac_geth2_tdtag($pkg,'knownprovince','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_PROVINCE);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knownpostcode','known_data').sumac_geth2_tdtag($pkg,'knownpostcode','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_POSTCODE);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knowncountry','known_data').sumac_geth2_tdtag($pkg,'knowncountry','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_COUNTRY);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knownphone','known_data').sumac_geth2_tdtag($pkg,'knownphone','known_data');
	$html .= sumac_getElementValue($accountDocument,SUMAC_ELEMENT_HOMEPHONE);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= sumac_geth2_trtag($pkg,'knownemail','known_data').sumac_geth2_tdtag($pkg,'knownemail','known_data');
	$html .= $_SESSION[SUMAC_SESSION_EMAILADDRESS];
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_login2_no_user_password($pkg)
{
	return isset($_SESSION[SUMAC_USERPAR_NOUSERPW][$pkg])
			? $_SESSION[SUMAC_USERPAR_NOUSERPW][$pkg]
			: $_SESSION[SUMAC_USERPAR_NOUSERPW]['login2'];
}

function sumac_login2_omitted_field($pkg,$fieldcode)
{
	$omitfields = isset($_SESSION[SUMAC_USERPAR_OMITFIELDS][$pkg])
				? $_SESSION[SUMAC_USERPAR_OMITFIELDS][$pkg]
				: $_SESSION[SUMAC_USERPAR_OMITFIELDS]['login2'];
	return strpos($omitfields,$fieldcode);
}

function sumac_login2_mandatory_field($pkg,$fieldcode)
{
	$mustfields = isset($_SESSION[SUMAC_USERPAR_MUSTFIELDS][$pkg])
				? $_SESSION[SUMAC_USERPAR_MUSTFIELDS][$pkg]
				: $_SESSION[SUMAC_USERPAR_MUSTFIELDS]['login2'];
	return strpos($mustfields,$fieldcode);
}

function sumac_login2_get_dropdown_filename($pkg)
{
	return isset($_SESSION[SUMAC_USERPAR_STATESDD][$pkg])
			? strtolower($_SESSION[SUMAC_USERPAR_STATESDD][$pkg])
			: strtolower($_SESSION[SUMAC_USERPAR_STATESDD]['login2']);
}

?>