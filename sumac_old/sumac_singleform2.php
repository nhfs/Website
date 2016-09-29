<?php
//version530//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_login2.php';
include_once 'sumac_xml.php';

function sumac_singleform2($previousRequest,$responseMessage,$formhandler,$loadedAccountDocument,$loadedFormTemplateElement)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$accountDocument = null;
	if ($loadedAccountDocument != null) $accountDocument = $loadedAccountDocument;
	else
	{
		$savedAccountDocument = sumac_reloadLoginAccountDocument();
		if ($savedAccountDocument !== false) $accountDocument = $savedAccountDocument;
	}

	$formTemplateElement = null;
	if ($loadedFormTemplateElement != null)
	{
		$formTemplateElement = $loadedFormTemplateElement;
	}
	else
	{
		$formTemplateElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		if ($formTemplateElement == null)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FORMTEMPLATE_DATA;
			return;
		}
	}
	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	if ($filledFormElement == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FILLEDFORM;
		return false;
	}

	$html = sumac_singleform2_HTML($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage);
	echo $html;
	return true;
}

function sumac_singleform2_HTML($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	return '<!DOCTYPE html>'.PHP_EOL
			.sumac_geth2_head('singleform2')
			.sumac_singleform2_body($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage);
}

function sumac_singleform2_body($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	$retryform = ($previousRequest != '') ? 'all' : null;

	return '<body>'.PHP_EOL
			.sumac_geth2_user('top','singleform2')
			.sumac_singleform2_content($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage)
			.sumac_geth2_user('bottom','singleform2')
			.sumac_geth2_body_footer('singleform2',true,$retryform,'')
			.'</body>'.PHP_EOL;
}

function sumac_singleform2_content($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_divtag('singleform2','mainpage','mainpage');
	$html .= sumac_singleform2_instructions();
	$html .= sumac_singleform2_HTMLform($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage);
	$html .= '</div>'.PHP_EOL;	//mainpage

	$html .= sumac_geth2_sumac_software_link('singleform2','/fundraising-software',SUMAC_INFO_FOOTER_SUMAC_DONATION2_TEXT);

	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_singleform2_instructions()
{
	$html = sumac_geth2_divtag('singleform2','instructions','instructions');
	$html .= sumac_geth2_spantext('singleform2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_singleform2_HTMLform($formTemplateElement,$formhandler,$accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_formtag('singleform2','all','form',$formhandler);
//	$html .= sumac_login2('singleform2','O',false,$accountDocument,$previousRequest,$responseMessage,'L1');
	$html .= sumac_singleform2_form($formTemplateElement,$previousRequest,$responseMessage);
	$html .= sumac_singleform2_form_status_table($accountDocument,$previousRequest,$responseMessage);
	$html .= sumac_login2('singleform2','O',false,$accountDocument,$previousRequest,$responseMessage,null);
	$html .= sumac_singleform2_buttons($accountDocument,$previousRequest,$responseMessage);

//now add the OK and Cancel buttons - and lets assume that the user doesnt want to edit in a new window/tab
//	$cancelOnclick = ' onclick="document.getElementById(\'' . SUMAC_ID_FORM_SUMAC_FORM . '\').action=\'sumac_courses_redirect.php\';"';

	$html .= '</form>'.PHP_EOL;
	return $html;
}

function sumac_singleform2_form($formTemplateElement,$previousRequest,$responseMessage)
{
	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	$formName = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
//	$html = sumac_geth2_sumac_title_with_line('singleform2',$formName,false);
	$html = sumac_geth2_sumac_title_with_gobacklink_and_line('singleform2',$formName,'L1',false);

	$instructions = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_INSTRUCTIONS);
	if ($instructions != '') $html .= sumac_geth2_divtag('singleform2','form_instructions','form_instructions').$instructions.'</div>'.PHP_EOL;

	$html .= sumac_geth2_tabletag('singleform2','formfields','formfields');
	$cols = 2; //default

	$fformNeededDate = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_WHEN_NEEDED_BY);
	if ($fformNeededDate != "")
	{
		$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
		$neededDate = sumac_formatDate($fformNeededDate,$dateformat);
		$html .= sumac_geth2_trtag('singleform2','needed','date').sumac_geth2_tdtag('singleform2','needed','date',$cols);
		$html .= sumac_formatMessage($_SESSION[SUMAC_STR]["CU21"],$neededDate);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}

	$formDataElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMDATA);
	$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
	for ($i = 0; $i < $formFieldElements->length; $i++)
	{
		$formField = $formFieldElements->item($i);
		$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
		$formDataElement = sumac_singleform2_get_formDataElement($formDataElements,$fieldId);

		$fieldContent = sumac_singleform2_get_field_content($formField,$formDataElement);
		$fieldInvalid = (($formDataElement != null) && ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_INVALID) == 'true'));
	 	$fieldTitle = $fieldInvalid ? sumac_singleform2_get_field_title($formDataElement) : '';
		$fieldLabel = sumac_singleform2_get_field_label($formField,$fieldId);
		$fieldRequired = ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) != 'false');


		$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
		$html .= sumac_geth2_trtag('singleform2',$fieldId,'formfield');
		$html .= sumac_geth2_tdtag('singleform2',$fieldId,array('field',$fieldDataType,$fieldId),$cols);
		switch ($fieldDataType)
		{
			case 'flag':

				//no sense in making it 'required' because a checkbox always has a value
				//for the same reason, it cant be in error so it cant need a title
				$attrs = 'type="checkbox" value="'.$fieldContent.'"'.(($fieldContent == '1')?' checked="checked"':'');
				$html .= sumac_geth2_input_with_label('singleform2',$fieldId,'input',$fieldId,$attrs,$fieldLabel,'after',false);
				break;

			case 'lookuplist':

				if ($formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE) == 'checkboxes')
				{
					//no sense in making it 'required' because checkboxes always have a values
					$checkboxlist = sumac_singleform2_get_checkbox_list($formField,$fieldContent,$fieldId,$fieldTitle);
					$html .= sumac_geth2_element_with_label('singleform2',$fieldId,$checkboxlist,'checkboxlist',$fieldLabel,'above',false);
				}
				else	// otherwise, a dropdown
				{
					$fieldValueList = $formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST);
					$fieldValueArray = array_values(array_filter(explode(';',$fieldValueList)));
					$selected = array_search($fieldContent,$fieldValueArray);	//may yield 'false', which can be mistaken for 0
					if ($selected === false) $selected = -1;
					$firstOption = $fieldRequired ? (($selected > -1) ? false : '') : '';
					$attrs = $fieldRequired ? 'data-sumac-reqby="O"' : '';
					$html .= sumac_geth2_select_with_label('singleform2',$fieldId,'select',$fieldId,$attrs,
								sumac_getHTMLFormOptionsForValueArrayUsingValue($fieldValueArray,$firstOption,$selected),$fieldLabel,'above',false);
				}
				break;

			case 'longtext':

				$attrs = 'title="'.$fieldTitle.'"'.($fieldRequired?' data-sumac-reqby="O"':'')
							.($fieldInvalid?' onclick="if (this.className.indexOf(\'sumac_invalid\') >= 0) sumac_set_field_valid(this);"':'');
				$cls = ($fieldInvalid?array('textarea','invalid'):'textarea');
				$html .= sumac_geth2_textarea_with_label('singleform2',$fieldId,$cls,$fieldId,$attrs,$fieldContent,$fieldLabel,'above',false);
				break;

			case 'text':
			case 'date':
			case 'currency':
			case 'number':
			default:

				$attrs = 'type="text" value="'.$fieldContent.'" title="'.$fieldTitle.'"'
							.($fieldRequired?' data-sumac-reqby="O"':'')
							.($fieldInvalid?' onclick="if (this.className.indexOf(\'sumac_invalid\') >= 0) sumac_set_field_valid(this);"':'');
				$cls = ($fieldInvalid?array('input','invalid'):'input');
				$html .= sumac_geth2_input_with_label('singleform2',$fieldId,$cls,$fieldId,$attrs,$fieldLabel,'above',false);
				break;
		}
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
	if ($fformVersion == '') $fformVersion = 0;

//include the form ids in the posted output
	$html .= sumac_geth2_trtag('singleform2','formids','formids');
	$html .= sumac_geth2_tdtag('singleform2','filledform','filledform',$cols);
	$attrs = 'type="hidden" value="'.$fformId .'"';
	$html .= sumac_geth2_input('sumac_input_singleform2_filledform','hidden','sumac_filledform',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('singleform2','formtemplate','formtemplate',$cols);
	$attrs = 'type="hidden" value="'.$formId .'"';
	$html .= sumac_geth2_input('sumac_input_singleform2_form','hidden','sumac_form',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;

	$html .= '<p style="float:right"><i>form:' . $fformId . '/' . $formId . '/' . $fformVersion . '</i></p>' . "\n";

	return $html;
}

function sumac_singleform2_buttons($accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_divtag('singleform2','buttons','untitled');

	$html .= sumac_geth2_tabletag('singleform2','buttons','buttons');
	$html .= sumac_geth2_trtag('singleform2','buttons','buttons');

	$html .= sumac_geth2_tdtag('singleform2','okbutton','okbutton titlebutton');
	$attrs = 'type="submit" value="" onclick="if (sumac_check_for_missing_fields(\'O\',\''.sumac_geth2_textid_for_span('singleform2',(($accountDocument == null)?'E1':'E2')).'\')) return false;"';
	$html .= sumac_geth2_input(sumac_geth2_textid_for_entry('singleform2','input','L2'),'submit','submitform',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('singleform2','cancelbutton','cancelbutton titlebutton');
	//$attrs = 'type="submit" value=""';
	//$html .= sumac_geth2_input(sumac_geth2_textid_for_entry('singleform2','input','L3'),'submit','cancelform',$attrs);
	$html .= sumac_geth2_sumac_return_link('singleform2','L3').PHP_EOL;
	$html .= '</td>'.PHP_EOL;

	$html .= '</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;

	return $html;
}

function sumac_singleform2_form_status_table($accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag('singleform2','form_status','form_status');

	if ($previousRequest == 'submitform')
	{
		$html .= sumac_geth2_trtag('singleform2','form_status','form_status').sumac_geth2_tdtag('singleform2','form_status','status',1,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag('singleform2','form_entry_status','form_status').sumac_geth2_tdtag('singleform2','form_entry_status','nodisplay');
	$html .= sumac_geth2_spantext('singleform2',(($accountDocument == null)?'E1':'E2'));
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_singleform2_get_checkbox_list($formField,$fieldContent,$id,$title)
{
	$fieldValueList = $formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST);
	$fieldValueArray = array_values(array_filter(explode(';',$fieldValueList)));
	$dataValueArray = array_values(array_filter(explode(';',$fieldContent)));
	$html = '<span id="'.sumac_geth2_id('singleform2',$id,'checkboxlist').'">';
	for ($i = 0; $i < count($fieldValueArray); $i++)
	{
		$fv = $fieldValueArray[$i];
		$attrs = 'type="checkbox" value="'.$fv.'" title="'.$title.'"'.(in_array($fv,$dataValueArray)?' checked="checked"':'');
		$html .= sumac_geth2_input_with_label('singleform2','cb_'.$id.'_'.$i,'lookuplist',$id.'_'.$i,$attrs,$fv,'after',false);
	}
	return $html.'</span>'.PHP_EOL;
}

function sumac_singleform2_get_formDataElement($formDataElements,$fieldId)
{
	for ($i = 0; $i < $formDataElements->length; $i++)
	{
		$formDataElement = $formDataElements->item($i);
		if ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_FIELDID) == $fieldId) return $formDataElement;
	}
	return null;
}

function sumac_singleform2_get_field_content($formField,$formDataElement)
{
	$fieldInitialValue = $formField->getAttribute(SUMAC_ATTRIBUTE_INITIALVALUE);
	$fieldData = ($formDataElement != null) ? $formDataElement->textContent : '';
	return ($fieldData != '') ? $fieldData : $fieldInitialValue;
}

function sumac_singleform2_get_field_title($formDataElement)
{
	$formDataError = $formDataElement->getAttribute(SUMAC_ATTRIBUTE_ERROR);
	if ($formDataError != '') return $formDataError;
	else return $_SESSION[SUMAC_STR]["CT6"];
}

function sumac_singleform2_get_field_label($formField,$fieldId)
{
	$label = $formField->getAttribute(SUMAC_ATTRIBUTE_LABEL);
	return ($label != '') ? $label : $fieldId;
}

?>