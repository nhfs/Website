<?php
//version5647//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_login2.php';
include_once 'sumac_xml.php';

function sumac_form2($pkg,$withLogin,$previousRequest,$responseMessage,$whereNext,$loadedAccountDocument,$loadedFormTemplateElements)
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

	$formTemplateElements = null;
	if ($loadedFormTemplateElements != null)
	{
		$formTemplateElements = $loadedFormTemplateElements;
	}
	else
	{
		$formTemplateElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		if ($formTemplateElement == null)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FORMTEMPLATE_DATA;
			return;
		}
		$formTemplateElements[0] = $formTemplateElement;
	}

	$html = sumac_form2_HTML($pkg,$withLogin,$formTemplateElements,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage);
	echo $html;
	return true;
}

function sumac_form2_HTML($pkg,$withLogin,$formTemplateElements,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage)
{
	return sumac_geth2_head($pkg)
			.sumac_form2_body($pkg,$withLogin,$formTemplateElements,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage);
}

function sumac_form2_body($pkg,$withLogin,$formTemplateElements,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage)
{
	$retryform = ($previousRequest != '') ? 'all' : null;

	return '<body>'.PHP_EOL
			.sumac_geth2_user('top',$pkg)
			.sumac_form2_content($pkg,$withLogin,$formTemplateElements,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage)
			.sumac_geth2_body_footer($pkg,true,$retryform,'');
}

function sumac_form2_content($pkg,$withLogin,$formTemplateElement,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_divtag($pkg,'form_mainpage','mainpage');

	$buttoncode = 'N';
	$panelLayout = 'L/CR';
	$ownerId = '';
	$retryData = '||';	//two (or three?) empty elements
	$html .= sumac_form2_embedded($pkg,$withLogin,$formTemplateElement,$buttoncode,$panelLayout,$organisationDocument,$whereNext,
									$ownerId,$retryData,$accountDocument,$previousRequest,$responseMessage);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_form2_embedded($pkg,$withLogin,$formTemplateElement,$buttoncode,$panelLayout,$organisationDocument,$whereNext,
								$ownerId,$retryData,$accountDocument,$previousRequest,$responseMessage,
								$formbuttons=true,$loginbuttons='none')
{
	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	$formStatus = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
	$formName = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$maxAttachments = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_MAXATTACHMENTS);
	if ($formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ATTACHMENTS) == 'none') $maxAttachments = 0;
	$uniqueTypes = ($formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_REUSE_ATTACHMENT_TYPES) == 'false');

//if someone is logged in, the handler for the form is 'sumac_form2_submitted'; otherwise it is 'sumac_identify_user' to attempt a login
	$formhandler = isset($_SESSION[SUMAC_SESSION_CONTACT_ID]) ? 'sumac_form2_submitted.php' : 'sumac_identify_user.php';

	$html = sumac_geth2_divtag($pkg,'mainform','untitled');
	$html .= sumac_geth2_formtag($pkg,'all','form',$formhandler,$method='post',$enctype='application/x-www-form-urlencoded');

	if ($withLogin == 'before')
	{
		$html .= sumac_login2_embedded($pkg,$buttoncode,$panelLayout,$organisationDocument,$accountDocument,$previousRequest,$responseMessage,'ML13',$loginbuttons);
		$html .= sumac_geth2_sumac_title_with_line($pkg,$formName,false);
	}
	else
	{
		$html .= sumac_geth2_sumac_title_with_gobacklink_and_line($pkg,$formName,'ML13',false);
	}

//	$html .= sumac_form2_embedded($pkg,$formTemplateElement,$buttoncode,$whereNext,true); //original interface
	if ($formStatus == 'completed')
	{
		$html .= sumac_form2_completed_form($pkg,$formTemplateElement,$filledFormElement,$whereNext,$retryData,0);
	}
	else
	{
		$html .= sumac_form2_active_form($pkg,$formTemplateElement,$filledFormElement,$buttoncode,$whereNext,$retryData,$maxAttachments);
	}
	$html .= sumac_form2_status_table($pkg,$accountDocument,$previousRequest,$responseMessage);
//include the form ids and the package id and the 'where next' filename in the posted output
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$html .= sumac_form2_formid_HTML($pkg,$formId,$fformId,$whereNext,$ownerId,$retryData);

	if ($withLogin == 'after')
	{
		if ($formStatus != 'completed')
		{
			$html .= sumac_login2_embedded($pkg,$buttoncode,$panelLayout,$organisationDocument,$accountDocument,$previousRequest,$responseMessage,null,$loginbuttons);
		}
	}
//now add the OK and Cancel buttons - and lets assume that the user doesnt want to edit in a new window/tab
	if ($formbuttons) $html .= sumac_form2_buttons($pkg,$buttoncode,$formTemplateElement,$whereNext,$accountDocument);

	$html .= '</form>'.PHP_EOL;
	$html .= '</div>'.PHP_EOL;
	$html .= sumac_form2_attachment_forms_HTML($pkg,$fformId,$ownerId,$maxAttachments,$uniqueTypes).PHP_EOL;
	return $html;
}

function sumac_form2_otherform($pkg,$formTemplateElement,$formnum=1)
{
	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	$formStatus = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
	$formName = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$html = sumac_geth2_divtag($pkg,'otherform_'.$formnum,'untitled');
	$html .= sumac_geth2_sumac_title_with_line($pkg,$formName,false);
	$html .= sumac_form2_completed_form($pkg,$formTemplateElement,$filledFormElement,'','',$formnum);
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_active_form($pkg,$formTemplateElement,$filledFormElement,$buttoncode,$whereNext,$retryData,$maxAttachments)
{
//note: if there is an active form in a page, there can only be ONE of them and it must be the FIRST FORM
	$formnum = 0;
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);

	$html = sumac_geth2_divtag($pkg,'form_'.$fformId,array('active_form','form_showing','form_expanded'));

	$instructions = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_INSTRUCTIONS);
	if ($instructions != '') $html .= sumac_geth2_divtag($pkg,'form_instructions_'.$fformId,'form_instructions').$instructions.'</div>'.PHP_EOL;

	$html .= sumac_geth2_tabletag($pkg,'formfields_'.$fformId,'formfields');
	$tabindexbase = ($formnum + 1) * 100;
	$fieldoptions = '';
	$cols = 2; //default

	$fformNeededDate = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_WHEN_NEEDED_BY);
	if ($fformNeededDate != "")
	{
		$dateformat = $_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT];
		$neededDate = sumac_formatDate($fformNeededDate,$dateformat);
		$html .= sumac_geth2_trtag($pkg,'needed','date').sumac_geth2_tdtag($pkg,'needed','date',$cols);
//@@@ should not use old string from courses package
		$html .= sumac_formatMessage($_SESSION[SUMAC_STR]["CU21"],$neededDate);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}

	$formDataElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMDATA);
	$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
	for ($i = 0; $i < $formFieldElements->length; $i++)
	{
		$formField = $formFieldElements->item($i);
		$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
		$formDataElement = sumac_form2_get_formDataElement($formDataElements,$fieldId);

		$fieldContent = sumac_form2_get_field_content($formField,$formDataElement,true);
		$fieldInvalid = (($formDataElement != null) && ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_INVALID) == 'true'));
	 	$fieldTitle = $fieldInvalid ? sumac_form2_get_field_title($formDataElement) : '';
		$fieldLabel = sumac_form2_get_field_label($formField,$fieldId);
		$fieldRequired = ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) != 'false');


		$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
		$tabindex = ' tabindex="'.($tabindexbase + $i).'"';
		$html .= sumac_geth2_trtag($pkg,$fieldId,'formfield',$tabindex);
		$html .= sumac_geth2_tdtag($pkg,$fieldId,array('field',$fieldDataType,$fieldId),$cols);
		switch ($fieldDataType)
		{
			case 'flag':

				//no sense in making it 'required' because a checkbox always has a value
				//for the same reason, it cant be in error so it cant need a title
				$attrs = 'type="checkbox" value="'.$fieldContent.'"'.(($fieldContent == '1')?' checked="checked"':'');
				$html .= sumac_geth2_input_with_label($pkg,$fieldId,'input',$fieldId,$attrs,$fieldLabel,'after',false);
				break;

			case 'lookuplist':

				if ($formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE) == 'checkboxes')
				{
					//no sense in making it 'required' because checkboxes always have a values
					$checkboxlist = sumac_form2_get_checkbox_list($pkg,$formField,$fieldContent,$fieldId,$fieldTitle);
					$html .= sumac_geth2_element_with_label($pkg,$fieldId,$checkboxlist,'',$fieldLabel,'above',false);
				}
				else	// otherwise, a dropdown
				{
					$fieldValueList = $formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST);
					$fieldValueArray = array_values(array_filter(explode(';',$fieldValueList)));
					$selected = array_search($fieldContent,$fieldValueArray);	//may yield 'false', which can be mistaken for 0
					if ($selected === false) $selected = -1;
					//$firstOption = $fieldRequired ? (($selected > -1) ? false : '') : '';
					$firstOption = ''; //always have null top entry in list
					$attrs = $fieldRequired ? ('data-sumac-reqby="'.$buttoncode.'"') : '';
					$html .= sumac_geth2_select_with_label($pkg,$fieldId,'select',$fieldId,$attrs,
								sumac_getHTMLFormOptionsForValueArrayUsingValue($fieldValueArray,$firstOption,$selected),$fieldLabel,'above',false);
				}
				break;

			case 'longtext':

				$attrs = 'title="'.$fieldTitle.'"'.($fieldRequired ? (' data-sumac-reqby="'.$buttoncode.'"') : '')
							.($fieldInvalid?' onclick="if (this.className.indexOf(\'sumac_invalid\') >= 0) sumac_set_field_valid(this);"':'');
				$cls = ($fieldInvalid?array('textarea','invalid'):'textarea');
				$html .= sumac_geth2_textarea_with_label($pkg,$fieldId,$cls,$fieldId,$attrs,$fieldContent,$fieldLabel,'above',false);
				break;

			case 'text':
			case 'date':
			case 'currency':
			case 'number':
			default:

				$attrs = 'type="text" value="'.$fieldContent.'" title="'.$fieldTitle.'"'
							.($fieldRequired ? (' data-sumac-reqby="'.$buttoncode.'"') : '')
							.($fieldInvalid?' onclick="if (this.className.indexOf(\'sumac_invalid\') >= 0) sumac_set_field_valid(this);"':'');
				$cls = ($fieldInvalid?array('input','invalid'):'input');
				$html .= sumac_geth2_input_with_label($pkg,$fieldId,$cls,$fieldId,$attrs,$fieldLabel,'above',false);
				break;
		}
		$fieldHelpUrl = $formField->getAttribute(SUMAC_ATTRIBUTE_HELPURL);
		if ($fieldHelpUrl != '') $html .= sumac_geth2_helplink($fieldHelpUrl);
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
		$fieldoptions .= '<option value="'.sumac_geth2_id($pkg,$fieldId,'tr').'">'.$fieldLabel.'</option>';
	}

	$html .= '</table>'.PHP_EOL;

	$formAttachmentElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMATTACHMENT);
	$attachmentTypesAllowed = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ATTACHMENTS);
	$typelist = array();
	if ($attachmentTypesAllowed == 'any')
	{
		if (isset($_SESSION[SUMAC_USERPAR_ATTTYPES][$pkg]))
		{
			$typelist = explode(';',$_SESSION[SUMAC_USERPAR_ATTTYPES][$pkg]);
		}
	}
	else if ($attachmentTypesAllowed != 'none')
	{
		$typelist = explode(';',$attachmentTypesAllowed);
	}
	if ($maxAttachments != 0)
	{
		$html .= sumac_form2_attachments_HTML($pkg,$formTemplateElement,$filledFormElement,0,true);
		$html .= sumac_form2_new_attachment_HTML($pkg,$filledFormElement,$typelist);
	}
	$html .= '</div>'.PHP_EOL;
	$html .= sumac_form2_scroll_control($pkg,$fformId,$fieldoptions);

	$uniqueTypes = ($formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_REUSE_ATTACHMENT_TYPES) == 'false');
	$html .= sumac_form2_footer_HTML($pkg,$formId,'active',$filledFormElement,$maxAttachments,$uniqueTypes);
	return $html;
}

function sumac_form2_completed_form($pkg,$formTemplateElement,$filledFormElement,$whereNext,$retryData,$formnum)
{
//note: there may be more than one completed form in a page. The 'formnum' will tell us which one it is.
//note: the 'formnum' of '0' is reserved for the case when there is no active form and the completed form is the first in the page.
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$formDataElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMDATA);
	$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);

	$tabindexbase = ($formnum + 1) * 100;
	$fieldoptions = '';
	$fieldcount = 0;

	$formhtml = sumac_geth2_tabletag($pkg,'formfields_'.$fformId,'formfields');
	$dateformat = $_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT];
	for ($i = 0; $i < $formFieldElements->length; $i++)
	{
		$formField = $formFieldElements->item($i);
		$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
		$formDataElement = sumac_form2_get_formDataElement($formDataElements,$fieldId);
		$fieldContent = sumac_form2_get_field_content($formField,$formDataElement,false);
		$fieldLabel = sumac_form2_get_field_label($formField,$fieldId);
		$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
		$fieldDisplayType = $formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE);
		$fieldValue = '';
		switch($fieldDataType)
		{
			case 'text':
			case 'number':
			case 'currency':
			case 'lookuplist':
			case 'longtext':
				$fieldValue = $fieldContent;
				break;
			case 'date':
				$fieldValue = sumac_formatDate($fieldContent,$dateformat);
				break;
			case 'flag':
				$fieldValue = ($fieldContent == '0') ? $_SESSION[SUMAC_SESSION_FORM_FLAG_0] : $_SESSION[SUMAC_SESSION_FORM_FLAG_1];
				break;
		}
		if ($fieldValue != '')
		{
			$fieldcount = $fieldcount + 1;
			$tabindex = ' tabindex="'.($tabindexbase + $fieldcount).'"';
			$formhtml .= sumac_geth2_trtag($pkg,$fieldId,'formfield',$tabindex);
			$fieldoptions .= '<option value="'.sumac_geth2_id($pkg,$fieldId,'tr').'">'.$fieldLabel.'</option>';
			$formhtml .= sumac_geth2_tdtag($pkg,$fieldId.'_label',array('completedformlabel',$fieldId),1).$fieldLabel.'</td>'.PHP_EOL;
			$formhtml .= sumac_geth2_tdtag($pkg,$fieldId,array('completedformdata',$fieldId),1).$fieldValue;
			$fieldHelpUrl = $formField->getAttribute(SUMAC_ATTRIBUTE_HELPURL);
			if ($fieldHelpUrl != '') $formhtml .= sumac_geth2_helplink($fieldHelpUrl);
			$formhtml .= '</td>'.PHP_EOL;
			$formhtml .= '</tr>'.PHP_EOL;
		}

	}

	$formhtml .= '</table>'.PHP_EOL;
	$formAttachmentElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMATTACHMENT);
	$formAttachmentCount = $formAttachmentElements->length;
	if ($formAttachmentCount > 0)
	{
		$formhtml .= sumac_form2_attachments_HTML($pkg,$formTemplateElement,$filledFormElement,$formnum,false);
//and they may need to be viewed ...
		$attrs = 'src="sumac_form2_attmt_iframe.htm"';
		$formhtml .= sumac_geth2_iframe($pkg,'attach_'.$fformId,array('iframe','nodisplay'),'sumac_attframe_'.$fformId,$attrs).'<br />'.PHP_EOL;
		$attrs = 'src="sumac_form2_attmt_dl_iframe.htm"';
		$formhtml .= sumac_geth2_iframe($pkg,'dl_attframe_'.$fformId,array('dl_iframe','nodisplay'),'sumac_dl_attframe_'.$fformId,$attrs).'<br />'.PHP_EOL;
	}

	$html = sumac_geth2_divtag($pkg,'form_'.$fformId,array('completed_form','form_showing','form_expanded'))
			.$formhtml
			.'</div>'.PHP_EOL
			.sumac_form2_scroll_control($pkg,$fformId,$fieldoptions)
			.sumac_form2_footer_HTML($pkg,$formId,'completed',$filledFormElement,-1,false);
	return $html;
}

function sumac_form2_scroll_control($pkg,$fformId,$fieldoptions)
{
	$html = sumac_geth2_divtag($pkg,'compression_'.$fformId,array('formcompression','form_showing'));
	if ($fieldoptions != null)
	{
		$html .= sumac_geth2_spantag($pkg,'select_'.$fformId,'formfield_select');
		$onchange = ' onchange="document.getElementById(this.value).focus();"';
		$html .= sumac_geth2_select_with_label($pkg,'formfield_'.$fformId,'formfield_select','formfield',$onchange,$fieldoptions,'ML10','before',true);
		$html .= '</span>'.PHP_EOL;
	}
	$html .= sumac_geth2_spantag($pkg,'scroll_'.$fformId,'form_scroll');
	$onclick = 'sumac_compress_expand_form(\'' . $pkg . '\',\'' . $fformId . '\');';
	$html .= sumac_geth2_sumac_button($pkg,'compress_'.$fformId,'form_compress',$onclick,'ML15');
	$html .= sumac_geth2_sumac_button($pkg,'expand_'.$fformId,array('form_expand','nodisplay'),$onclick,'ML14');
	$html .= '</span>'.PHP_EOL;
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_footer_HTML($pkg,$formId,$status,$filledFormElement,$maxatt,$uniqueTypes)
{
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
	if ($fformVersion == '') $fformVersion = 0;

	$html = sumac_geth2_divtag($pkg,'footer_'.$fformId,array('formfooter',$status.'_formfooter'));
	$html .= sumac_geth2_spantag($pkg,'hiding_'.$fformId,'formfield_hiding');
	$onclick = 'sumac_hide_show_form(\'' . $pkg . '\',\'' . $fformId . '\');';
	$moreattrs = ' title="form=' . $fformId . '/tmplt=' . $formId . '/vn=' . $fformVersion . '"';
	$html .= sumac_geth2_sumac_button($pkg,'hide_'.$fformId,'form_hide',$onclick,'ML9',$moreattrs);
	$html .= sumac_geth2_sumac_button($pkg,'show_'.$fformId,array('form_show','nodisplay'),$onclick,'ML8',$moreattrs);
	$html .= '</span>'.PHP_EOL;
	if (($status == 'active') && ($maxatt != 0))
	{
		if ($uniqueTypes) $html .= '<script type="text/javascript">sumac_disable_used_attachment_types(document,"'.$pkg.'","'.$fformId.'")</script>'.PHP_EOL;
		$html .= '<script type="text/javascript">sumac_allow_or_disallow_more_attachments(document,"'.$pkg.'","'.$fformId.'","'.$maxatt.'","'.$uniqueTypes.'")</script>'.PHP_EOL;
	}
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_formid_HTML($pkg,$formId,$fformId,$whereNext,$ownerId,$retryData)
{
	$html = sumac_geth2_tabletag($pkg,'formids','formids');
	$html .= sumac_geth2_trtag($pkg,'formids','formids').sumac_geth2_tdtag($pkg,'formids','hidden');
	$attrs = 'type="hidden" value="'.$fformId.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_filledform','hidden','sumac_filledform',$attrs);
	$attrs = 'type="hidden" value="'.$formId.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_form','hidden','sumac_form',$attrs);
	$attrs = 'type="hidden" value="'.$pkg.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_package','hidden','sumac_package',$attrs);
	$attrs = 'type="hidden" value="'.$whereNext.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_next','hidden','sumac_next',$attrs);
	$attrs = 'type="hidden" value="'.$ownerId.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_owner','hidden','sumac_owner',$attrs);
	$attrs = 'type="hidden" value="'.$retryData.'"';
	$html .= sumac_geth2_input('sumac_input_'.$pkg.'_retrydata','hidden','sumac_retrydata',$attrs);
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_form2_attachments_HTML($pkg,$formTemplateElement,$filledFormElement,$formnum,$allowDelete)
{
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);

	$html = sumac_geth2_divtag($pkg,'attachments_'.$fformId,'attachments_title');

	$maxatt = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_MAXATTACHMENTS);
	$uniqueTypes = ($formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_REUSE_ATTACHMENT_TYPES) == 'false');
	$attachmentsHead = ($maxatt == '1') ? 'MH3' : 'MH1';

	$viewOnclick = 'sumac_form2_initiate_download(\''.$pkg.'\',\''.$fformId.'\',this.parentNode.parentNode.title);';
	$deleteOnclick = 'if (confirm(\'Really delete this attachment?\')==false) return false; '
					.'sumac_form2_initiate_delete(\''.$pkg.'\',\''.$fformId.'\',this.parentNode.parentNode.title);';

	$html .= sumac_geth2_ptag($pkg,'attachments_'.$fformId,'attachments_title')
				.sumac_geth2_spantext($pkg,$attachmentsHead)
				.(($maxatt > 0) ? sumac_geth2_span($pkg,null,'noclass','MH4',true,$maxatt) : '')
				.($uniqueTypes ? sumac_geth2_spantext($pkg,'MH5') : '')
				.'</p>'.PHP_EOL;
	$html .= sumac_geth2_tabletag($pkg,'attachments_'.$fformId,'attachments').'<tbody>'.PHP_EOL;
	$html .= sumac_geth2_trtag($pkg,null,'attachment',' title="template"')
			.sumac_geth2_tdtag($pkg,null,array('attachment_id','nodisplay')).'</td>'
			.sumac_geth2_tdtag($pkg,null,'attachment_name').'</td>'
			.sumac_geth2_tdtag($pkg,null,'attachment_type').'</td>'
			.sumac_geth2_tdtag($pkg,null,'attachment_size').'</td>'
			.sumac_geth2_tdtag($pkg,null,'attachment_date').'</td>'
			.sumac_geth2_tdtag($pkg,null,'attachment_filetype').'</td>'
			.sumac_geth2_tdtag($pkg,null,'nodisplay').sumac_geth2_sumac_button($pkg,null,'view',$viewOnclick,'ML6').'</td>'
			.sumac_geth2_tdtag($pkg,null,'nodisplay').sumac_geth2_sumac_button($pkg,null,'delete',$deleteOnclick,'ML7').'</td>'.PHP_EOL
			.'</tr>'.PHP_EOL;

	$formAttachmentElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMATTACHMENT);
	$formAttachmentCount = $formAttachmentElements->length;
	for ($i = 0; $i < $formAttachmentCount; $i++)
	{
		$formAttachmentElement = $formAttachmentElements->item($i);
		$faid = $formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_ID);

		$html .= sumac_geth2_trtag($pkg,null,'attachment',' title="'.$faid.'"')
				.sumac_geth2_tdtag($pkg,null,array('attachment_id','nodisplay')).$faid.'</td>'
				.sumac_geth2_tdtag($pkg,null,'attachment_name').$formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_NAME).'</td>'
				.sumac_geth2_tdtag($pkg,null,'attachment_type').$formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_TYPE).'</td>'
				.sumac_geth2_tdtag($pkg,null,'attachment_size').$formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_FILESIZE).'</td>'
				.sumac_geth2_tdtag($pkg,null,'attachment_date').$formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_DATE).'</td>'
				//.sumac_geth2_tdtag($pkg,null,'attachment_filetype').$formAttachmentElement->getAttribute(SUMAC_ATTRIBUTE_FILETYPE).'</td>'
				.sumac_geth2_tdtag($pkg,null,'attachment_filetype').'</td>'	//empty - not used yet
				.sumac_geth2_tdtag($pkg,null,'attachment_viewer').sumac_geth2_sumac_button($pkg,null,'view',$viewOnclick,'ML6').'</td>'
				.($allowDelete ? (sumac_geth2_tdtag($pkg,null,'attachment_deleter').sumac_geth2_sumac_button($pkg,null,'delete',$deleteOnclick,'ML7').'</td>'.PHP_EOL) : PHP_EOL)
				.'</tr>'.PHP_EOL;
	}
	$html .= '</tbody></table>'.PHP_EOL;
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_get_formDataElement($formDataElements,$fieldId)
{
	for ($i = 0; $i < $formDataElements->length; $i++)
	{
		$formDataElement = $formDataElements->item($i);
		if ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_FIELDID) == $fieldId) return $formDataElement;
	}
	return null;
}

function sumac_form2_get_field_content($formField,$formDataElement,$useInit)
{
	$fieldInitialValue = $useInit ? $formField->getAttribute(SUMAC_ATTRIBUTE_INITIALVALUE) : '';
	$fieldData = ($formDataElement != null) ? $formDataElement->textContent : '';
	return ($fieldData != '') ? $fieldData : $fieldInitialValue;
}

function sumac_form2_get_field_title($formDataElement)
{
	$formDataError = $formDataElement->getAttribute(SUMAC_ATTRIBUTE_ERROR);
	if ($formDataError != '') return $formDataError;
//@@@ should not use old string from courses package
//@@@ needs set-string logic to handle HTML title values
	else return $_SESSION[SUMAC_STR]["CT6"];
}

function sumac_form2_get_field_label($formField,$fieldId)
{
	$label = $formField->getAttribute(SUMAC_ATTRIBUTE_LABEL);
	return ($label != '') ? $label : $fieldId;
}

function sumac_form2_get_checkbox_list($pkg,$formField,$fieldContent,$id,$title)
{
	$fieldValueList = $formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST);
	$fieldValueArray = array_values(array_filter(explode(';',$fieldValueList)));
	$dataValueArray = array_values(array_filter(explode(';',$fieldContent)));
	$html = '<span id="'.sumac_geth2_id($pkg,$id,'checkboxlist').'">';
	for ($i = 0; $i < count($fieldValueArray); $i++)
	{
		$fv = $fieldValueArray[$i];
		$attrs = 'type="checkbox" value="'.$fv.'" title="'.$title.'"'.(in_array($fv,$dataValueArray)?' checked="checked"':'');
		$html .= sumac_geth2_input_with_label($pkg,'cb_'.$id.'_'.$i,'checkboxlist',$id.'_'.$i,$attrs,$fv,'after',false);
	}
	return $html.'</span>'.PHP_EOL;
}

function sumac_form2_status_table($pkg,$accountDocument,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag($pkg,'form_status','form_status');

	if ($previousRequest == 'submitform')
	{
		$html .= sumac_geth2_trtag($pkg,'form_status','form_status').sumac_geth2_tdtag($pkg,'form_status','status',1,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag($pkg,'form_entry_status','form_status').sumac_geth2_tdtag($pkg,'form_entry_status','nodisplay');
	$html .= sumac_geth2_spantext($pkg,(($accountDocument == null)?'ME1':'ME2'));
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_form2_buttons($pkg,$buttoncode,$formTemplateElement,$whereNext,$accountDocument)
{
	$html = sumac_geth2_divtag($pkg,'buttons','untitled');

	$html .= sumac_geth2_tabletag($pkg,'buttons','buttons');
	$html .= sumac_geth2_trtag($pkg,'buttons','buttons');

	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	$formStatus = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
	if ($formStatus != 'completed')
	{
		$html .= sumac_geth2_tdtag($pkg,'okbutton',array('okbutton','titlebutton'));
		$attrs = 'type="submit" value="" onclick="if (sumac_check_for_missing_fields(\''.$buttoncode.'\',\''.sumac_geth2_textid_for_span($pkg,(($accountDocument == null)?'ME1':'ME2')).'\')) return false;"';
		$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input','ML2'),'submit','submitform',$attrs);
		$html .= '</td>'.PHP_EOL;
		$html .= sumac_geth2_tdtag($pkg,'cancelbutton',array('cancelbutton','titlebutton'));
		//$attrs = 'type="submit" value=""';
		//$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input','ML3'),'submit','cancelform',$attrs);
		$formhandler = isset($_SESSION[SUMAC_SESSION_CONTACT_ID]) ? 'sumac_form2_submitted.php' : 'sumac_identify_user.php';
		$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$html .= sumac_geth2_sumac_return_link($pkg,'ML3',$formhandler.'?pkg='.$pkg.'&amp;next='.$whereNext.'&amp;cancelform='.$formId).PHP_EOL;
		$html .= '</td>'.PHP_EOL;
	}
	else
	{
		$html .= sumac_geth2_tdtag($pkg,'okbutton',array('okbutton','titlebutton'));
		if ($whereNext == '')
		{
			$html .= sumac_geth2_sumac_return_link($pkg,'ML11',$whereNext,'OK_button').PHP_EOL;
		}
		else
		{
			$attrs = 'type="submit" value=""';
			$html .= sumac_geth2_input(sumac_geth2_textid_for_entry($pkg,'input','ML2'),'submit','closeform',$attrs);
		}
		$html .= '</td>'.PHP_EOL;
	}
	$html .= '</tr>'.PHP_EOL;

//formids used to go here
	$html .= '</table>'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;

	return $html;
}

function sumac_form2_new_attachment_HTML($pkg,$filledFormElement,$typelist)
{
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
//we must have a logged in user before we can add attachments
	$cls = isset($_SESSION[SUMAC_SESSION_CONTACT_ID]) ? 'attachments' : array('attachments','nodisplay');
	$html = sumac_geth2_divtag($pkg,'new_attachments_'.$fformId,$cls);

	$html .= sumac_geth2_divtag($pkg,'allow_attach_'.$fformId,'allow_attach');
	$html .= '<a href="javascript:sumac_form2_show_new_attachment_panel(\''.$pkg.'\',\''.$fformId.'\')">'
				.sumac_geth2_spantext($pkg,'ML1').'</a>';	//<br />
	$html .= '</div>'.PHP_EOL;

	$html .= sumac_geth2_divtag($pkg,'attach_new_'.$fformId,array('attach_new','nodisplay')).'<br />'.PHP_EOL
				.sumac_geth2_input_with_label($pkg,'attfile_'.$fformId,'input','sumac_attfile_'.$fformId,'type="file"','MF1','before',true).'<br />'.PHP_EOL;
//drop support for manually entered name (as opposed to the file's name on disc)
//				.sumac_geth2_input_with_label($pkg,'attname_'.$fformId,'input','sumac_attname_'.$fformId,'type="text" value=""','MF2','before',true).'<br />'.PHP_EOL;

	$optionlist = sumac_getHTMLFormOptionsForValueArrayUsingValue($typelist,false,-1);
	//if (count($typelist) > 1)
	//{
		$html .= sumac_geth2_select_with_label($pkg,'atttype_'.$fformId,'select','sumac_atttype_'.$fformId,'',$optionlist,'MF3','before',true).'<br />'.PHP_EOL;
	//}
	//else
	//{
	//	$html .= sumac_geth2_select(sumac_geth2_id($pkg,'atttype_'.$fformId,'select'),'nodisplay','sumac_atttype_'.$fformId,'',$optionlist);
	//}

	$onclick = 'if (sumac_form2_test_for_blank_filename(\''.$pkg.'\',\''.$fformId.'\')) return false; sumac_form2_initiate_upload(\''.$pkg.'\',\''.$fformId.'\');';
	$html .= sumac_geth2_sumac_button($pkg,'transmit','transmit',$onclick,'ML4');
	$onclick = 'this.parentNode.className = \'sumac_nodisplay\'; return true;';
	$html .= sumac_geth2_sumac_button($pkg,'cancel','cancel',$onclick,'ML5').'<br />'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;

//	$attrs = 'height="100" src="'.SUMAC_USER_FOLDER.'/pleasewait.gif"';
	$attrs = 'src="sumac_form2_attmt_iframe.htm"';
	$html .= sumac_geth2_iframe($pkg,'attach_'.$fformId,array('iframe','nodisplay'),'sumac_attframe_'.$fformId,$attrs).'<br />'.PHP_EOL;
	$attrs = 'src="sumac_form2_attmt_dl_iframe.htm"';
	$html .= sumac_geth2_iframe($pkg,'dl_attframe_'.$fformId,array('dl_iframe','nodisplay'),'sumac_dl_attframe_'.$fformId,$attrs).'<br />'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_attachment_forms_HTML($pkg,$fformId,$ownerId,$maxAttachments,$uniqueTypes)
{
	$html = '';
	$html .= sumac_geth2_divtag($pkg,'uploadform',array('uploadform','nodisplay'));
	$html .= sumac_geth2_formtag($pkg,'upload','upload','sumac_form2_attmt_upload.php','post','multipart/form-data');
//	$html .= sumac_geth2_formtag($pkg,'upload','upload','sumac_test_response.php','post','multipart/form-data');

	$html .= '<!-- MAX_FILE_SIZE must precede the file input field -->'.PHP_EOL
				.'<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />'.PHP_EOL
				.'<!-- Name of input element determines name in $_FILES array -->'.PHP_EOL
				.'<input id="sumac_id_upload_file_ex" name="sumac_upload_file" type="file" />'.PHP_EOL
				.'<input name="sumac_package" type="hidden" value="'.$pkg.'" />'.PHP_EOL
				.'<input name="sumac_maxattmt" type="hidden" value="'.$maxAttachments.'" />'.PHP_EOL
				.'<input name="sumac_unique_types" type="hidden" value="'.$uniqueTypes.'" />'.PHP_EOL
				.'<input name="sumac_upload_form" type="hidden" value="'.$fformId.'" />'.PHP_EOL
				.'<input name="sumac_upload_owner" type="hidden" value="'.$ownerId.'" />'.PHP_EOL
				.'<input name="sumac_upload_name" type="hidden" value="" />'.PHP_EOL
				.'<input name="sumac_upload_type" type="hidden" value="" />'.PHP_EOL;

	$html .= '</form>'.PHP_EOL.'</div>'.PHP_EOL;

	$html .= sumac_geth2_divtag($pkg,'downloadform',array('downloadform','nodisplay'));
	$html .= sumac_geth2_formtag($pkg,'download','download','sumac_form2_attmt_download.php','get');
//	$html .= sumac_geth2_formtag($pkg,'download','download','sumac_test_response.php','get');

	$html .= '<input id="sumac_input_download" name="sumac_download" type="hidden" value="" />'.PHP_EOL
				.'<input name="sumac_package" type="hidden" value="'.$pkg.'" />'.PHP_EOL
				.'<input name="sumac_download_form" type="hidden" value="'.$fformId.'" />'.PHP_EOL
				.'<input name="sumac_download_owner" type="hidden" value="'.$ownerId.'" />'.PHP_EOL;
	$html .= '</form>'.PHP_EOL.'</div>'.PHP_EOL;

	$html .= sumac_geth2_divtag($pkg,'dldoneform',array('dldoneform','nodisplay'));
	$html .= sumac_geth2_formtag($pkg,'dldone','dldone','sumac_form2_attmt_dldone.php','get');
	$html .= '<input id="sumac_input_dldone" name="sumac_dldone" type="hidden" value="" />'
				.'<input name="sumac_package" type="hidden" value="'.$pkg.'" />'.PHP_EOL;
	$html .= '</form>'.PHP_EOL.'</div>'.PHP_EOL;

	$html .= sumac_geth2_divtag($pkg,'deleteform',array('deleteform','nodisplay'));
	$html .= sumac_geth2_formtag($pkg,'delete','delete','sumac_form2_attmt_delete.php','get');
//	$html .= sumac_geth2_formtag($pkg,'delete','delete','sumac_test_response.php','get');

	$html .= '<input id="sumac_input_delete" name="sumac_delete" type="hidden" value="" />'
				.'<input name="sumac_package" type="hidden" value="'.$pkg.'" />'.PHP_EOL
				.'<input name="sumac_maxattmt" type="hidden" value="'.$maxAttachments.'" />'.PHP_EOL
				.'<input name="sumac_unique_types" type="hidden" value="'.$uniqueTypes.'" />'.PHP_EOL
				.'<input name="sumac_delete_form" type="hidden" value="'.$fformId.'" />'.PHP_EOL
				.'<input name="sumac_delete_owner" type="hidden" value="'.$ownerId.'" />'.PHP_EOL;
	$html .= '</form>'.PHP_EOL.'</div>'.PHP_EOL;

	return $html;
}

?>