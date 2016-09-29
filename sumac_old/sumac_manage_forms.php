<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execViewOrUpdateForm($updateFormStatus,$formUpdatedPHP,$formTemplateDocument)
{
	$filledFormElement = $formTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	if ($filledFormElement == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FILLEDFORM;
		return false;
	}

	$formTemplateElement = $formTemplateDocument->documentElement;
//but document may be response document with embedded (erroneous) formtemplate ...
	if ($formTemplateDocument->documentElement->tagName == SUMAC_ELEMENT_RESPONSE)
	{
		$formTemplateElement = $formTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
	}

	$formName = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$html = sumac_getHTMLHeadForFormViewOrUpdate($formName);

//@@@ do we need "onunload" code to clean up when form-updating is abandoned ???
	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($formTemplateDocument,'form');

	$formStatus = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
	$formChangedDate = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_WHEN_MODIFIED);
	$formChanged = ($formChangedDate != '') ? ('<br />(last changed ' . sumac_formatDate($formChangedDate,$_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT]) . ')') : '';
	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'formview') . sumac_getSubtitle($formName . $formChanged);
//include navbars only if form was NOT opened in a new window
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] != '1')	//open forms in SAME tab/window
	{
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','');
	}

	if ($formStatus == 'completed')
	{
		$html .= sumac_getHTMLFormForCompletedSumacForm($formTemplateElement,$filledFormElement);
	}
	else
	{
		$html .= sumac_getHTMLFormForActiveSumacForm($updateFormStatus,$formUpdatedPHP,$formTemplateElement,$filledFormElement);
//		$html .= sumac_getEscapeFromFormUpdateHTML($formId);
	}

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

//	if ($updateFormStatus != '') $html .= sumac_getJSToRestoreEnteredValues('',SUMAC_ID_FORM_SUMACFORM);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForFormViewOrUpdate($formName)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1');
	$html .= sumac_getHTMLTitle('','',$formName);

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
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

function sumac_getHTMLFormForActiveSumacForm($updateFormStatus,$formUpdatedPHP,$formTemplateElement,$filledFormElement)
{
	$html = '<div id="' . SUMAC_ID_DIV_SUMAC_FORM . '" class="sumac_maintable">' . "\n";

	$html .= '<form id="' . SUMAC_ID_FORM_SUMAC_FORM . '" accept-charset="UTF-8" method="post" action="' . $formUpdatedPHP . '">' . "\n";
	$instructions = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_INSTRUCTIONS);
	if ($instructions != '')
	{
		$html .= '<table align="center"><tr><td class="sumac_instructions">' . $instructions . '</td></tr></table>' . "\n";
	}

	$html .= '<table class="sumac_activesumacform">' . "\n";
	$html .= '<tr><td colspan="2">' . sumac_getHTMLTableOfFormStatus($updateFormStatus) . '</td></tr>' . "\n";
	$fformNeededDate = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_WHEN_NEEDED_BY);
	if ($fformNeededDate != "")
	{
		$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
		$neededDate = sumac_formatDate($fformNeededDate,$dateformat);
		$html .= '<tr><td colspan="2">' . sumac_formatMessage($_SESSION[SUMAC_STR]["CU21"],$neededDate) . '</td></tr>' . "\n";
	}

	$formDataElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMDATA);
	$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
	if ($formFieldElements->length > 0)
	{
		//@@@ do we need another date format selection?
		$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
		$requiredCheck = 'if (sumac_checknamedfields([';
		for ($i = 0; $i < $formFieldElements->length; $i++)
		{
			$formField = $formFieldElements->item($i);
			$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
			$formDataElement = sumac_getFormDataElement($formDataElements,$fieldId);
			$fieldData = '';
			$formDataInvalid = false;
			$formDataError = '';
			if ($formDataElement != null)
			{
				$fieldData = $formDataElement->textContent;
				$formDataInvalid  = ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_INVALID) == 'true');
				$formDataError = $formDataElement->getAttribute(SUMAC_ATTRIBUTE_ERROR);
			}
			$fieldLabel = $formField->getAttribute(SUMAC_ATTRIBUTE_LABEL);
			if ($fieldLabel == '') $fieldLabel = $fieldId;
			$labelClass = 'sumac_formfieldlabel';
			if ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) != 'false') $labelClass .= 'required';
			$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
			$dataClass = 'sumac_formdatafield';
			if ($fieldDataType == 'flag')
			{
				$fieldInitialValue = $formField->getAttribute(SUMAC_ATTRIBUTE_INITIALVALUE);
				$value = ($fieldData != '') ? $fieldData : $fieldInitialValue;
				$checked = ($value == '1') ? 'checked' : '';
				$fieldValue = '<input type="checkbox" name="' . $fieldId . '" value="' . $value . '" ' . $checked . ' />';
				$html .= '<tr><td></td><td colspan="1" class="' . $dataClass . '">' . $fieldValue . '&nbsp;&nbsp;'
						. '<span class="' . $labelClass . '">' . $fieldLabel . '</span></td></tr>' . "\n";
			}
			else
			{
				$fieldValue = sumac_getHTMLForFormFieldValue($formField,$fieldData,$fieldId,$fieldDataType,$formDataInvalid,$formDataError);
				$html .= '<tr><td class="' . $labelClass . '">' . $fieldLabel . '</td>'
						. '<td colspan="1" class="' . $dataClass . '">' . $fieldValue . '</td></tr>' . "\n";
				if ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) != 'false')
				{
					if ($fieldDataType == 'lookuplist')
					{
						if ($formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE) != 'checkboxes')
						{
							if (substr($requiredCheck,-1) != '[') $requiredCheck .= ',';
							$requiredCheck .= "'$fieldId'";
						}
					}
					else
					{
						if (substr($requiredCheck,-1) != '[') $requiredCheck .= ',';
						$requiredCheck .= "'$fieldId'";
					}
				}
			}
		}
		$requiredCheck .= '])) return false; return true;';
	}
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
	if ($fformVersion == '') $fformVersion = 0;
//include the form ids in the posted output
	$html .= '<tr><td colspan="2"><input type="hidden" name="sumac_filledform" value="' . $fformId . '" /><input type="hidden" name="sumac_form" value="' . $formId . '" /></td></tr>' . "\n";
//now add the OK and Cancel buttons
//the Cancel button has to work differently depending on whether we are updating in a new tab/window or the current one
	$cancelOnclick = ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1')
						? '' //open forms in new tab/window so normal update routine can handle it ... otherwise go to redirect
						: ' onclick="document.getElementById(\'' . SUMAC_ID_FORM_SUMAC_FORM . '\').action=\'sumac_courses_redirect.php\';"';

	$html .= '<tr><td colspan="2"><input name="updateform" type="submit" value="' . $_SESSION[SUMAC_STR]["CL11"] . '" onclick="' . $requiredCheck . '" />' .
					'<input name="abandonupdate" type="submit" value="' . $_SESSION[SUMAC_STR]["CL2"] . '"' . $cancelOnclick .' />' .
				'</td></tr>';

	$html .= '</table>' . "\n";
	$html .= '</form>' . "\n";

	$html .= '<p style="float:right"><i>form:' . $fformId . '/' . $formId . '/' . $fformVersion . '</i></p>' . "\n";

	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLForFormFieldValue($formField,$fieldData,$fieldId,$fieldDataType,$formDataInvalid,$formDataError)
{
	$fieldDisplayType = $formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE);
	$fieldInitialValue = $formField->getAttribute(SUMAC_ATTRIBUTE_INITIALVALUE);
	$value = ($fieldData != '') ? $fieldData : $fieldInitialValue;
// fieldDataType of 'flag' is taken care of elsewhere because it is arbitrarily laid out differently from everything else
	if ($fieldDataType == 'lookuplist')
	{
		$fieldValueList = $formField->getAttribute(SUMAC_ATTRIBUTE_VALUELIST);
		$fieldValueArray = array_values(array_filter(explode(';',$fieldValueList)));
		$dataValueArray = array_values(array_filter(explode(';',$value)));
		$selected = -1;
		if ($fieldDisplayType == 'checkboxes')
		{
			$html = '';
			for ($i = 0; $i < count($fieldValueArray); $i++)
			{
				$boxlabel = $fieldValueArray[$i];
				$checked = '';
				for ($j = 0; $j < count($dataValueArray); $j++) if ($dataValueArray[$j] == $boxlabel)  { $checked = 'checked'; break; }
				$html .= '<label for="cb_' . $fieldId . '_' . $i . '">' . $boxlabel . '</label>'
						. '<input id="cb_' . $fieldId . '_' . $i . '" type="checkbox" name="' . $fieldId . '_' . $i . '" value="' . $boxlabel . '" ' . $checked . ' />'
						. '&nbsp;&nbsp;&nbsp;';
			}
			return $html;
		}
		else	// otherwise, a dropdown
		{
			$html = '';
			$firstOption = ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) != 'false') ? '' : false;
			for ($i = 0; $i < count($fieldValueArray); $i++) if ($fieldValueArray[$i] == $value)  { $selected = $i; break; }
			if ($selected > -1) $firstOption = false; // no point in blank entry when there is an initial value
			else if ($formField->getAttribute(SUMAC_ATTRIBUTE_REQUIRED) == 'false')  $firstOption = '';
			return '<select name="' . $fieldId . '">' . sumac_getHTMLFormOptionsForValueArrayUsingValue($fieldValueArray,$firstOption,$selected) . '</select>';
		}
	}
	else
	{
		$inputToolTip = '';
		$inputClass = '';
		if ($formDataInvalid)
		{
			$inputClass = ' class="sumac_invalid" onclick="if (this.className == \'sumac_invalid\') this.className = \'\';"';
			if ($formDataError != '') $inputToolTip = ' title="' . $formDataError . '"';
			else $inputToolTip = ' title="' . $_SESSION[SUMAC_STR]["CT6"] . '"';
		}
		if ($fieldDataType == 'longtext')
		{
			$onchange = ' onchange="if (this.value == \'' . $value . '\') this.className = \'sumac_invalid\';"';
			return '<textarea name="' . $fieldId . '"' . $inputClass . $inputToolTip . $onchange . '/>' . $value . '</textarea>';
		}
		else	//anything else is really just text
		{
			$onchange = ' onchange="if (this.value == \'' . $value . '\') this.className = \'sumac_invalid\';"';
			return '<input type="text" name="' . $fieldId . '" value="' . $value . '"' . $inputClass . $inputToolTip . $onchange . ' />';
		}
	}
}

function sumac_getHTMLFormForCompletedSumacForm($formTemplateElement,$filledFormElement)
{
	$fformId = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$formId = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$fformVersion = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_VERSION);
	if ($fformVersion == '') $fformVersion = 0;
	unset($_SESSION[SUMAC_SESSION_FORM][$formId]);

	$html = '<div id="' . SUMAC_ID_DIV_SUMAC_FORM . '" class="sumac_maintable">' . "\n";

	$html .= '<form id="' . SUMAC_ID_FORM_SUMAC_FORM . '" accept-charset="UTF-8" method="post" action="sumac_courses_redirect.php">' . "\n";
	//$instructions = $formTemplateElement->getAttribute(SUMAC_ATTRIBUTE_INSTRUCTIONS);
	//if ($instructions != '')
	//{
	//	$html .= '<table align="center"><tr><td class="sumac_instructions">' . $instructions . '</td></tr></table>' . "\n";
	//}

	$html .= '<table class="sumac_completedsumacform">' . "\n";
	$html .= '<tr><td colspan="2">' . sumac_getHTMLTableOfFormStatus($_SESSION[SUMAC_STR]["CU10"]) . '</td></tr>' . "\n";

	$formDataElements = $filledFormElement->getElementsByTagName(SUMAC_ELEMENT_FORMDATA);
	$formFieldElements = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FORMFIELD);
	if ($formFieldElements->length > 0)
	{
		//@@@ do we need another date format selection?
		$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
		for ($i = 0; $i < $formFieldElements->length; $i++)
		{
			$formField = $formFieldElements->item($i);
			$fieldId = $formField->getAttribute(SUMAC_ATTRIBUTE_ID);
			$fieldLabel = $formField->getAttribute(SUMAC_ATTRIBUTE_LABEL);
			if ($fieldLabel == '') $fieldLabel = $fieldId;
			$fieldDataType = $formField->getAttribute(SUMAC_ATTRIBUTE_DATATYPE);
			$fieldDisplayType = $formField->getAttribute(SUMAC_ATTRIBUTE_DISPLAYTYPE);
			$formDataElement = sumac_getFormDataElement($formDataElements,$fieldId);
			if ($formDataElement != null) $fieldData = $formDataElement->textContent;
			else $fieldData = '';
			$fieldValue = '';
			switch($fieldDataType)
			{
				case 'text':
				case 'number':
				case 'currency':
				case 'lookuplist':
				case 'longtext':
					$fieldValue = $fieldData;
					break;
				case 'date':
					$fieldValue = sumac_formatDate($fieldData,$dateformat);
					break;
				case 'flag':
					$fieldValue = ($fieldData == '0') ? $_SESSION[SUMAC_SESSION_FORM_FLAG_0] : $_SESSION[SUMAC_SESSION_FORM_FLAG_1];
					break;
			}

			$html .= '<tr><td class="sumac_completedformlabel">' . $fieldLabel . '</td><td class="sumac_completedformdata" colspan="1">' . $fieldValue . '</td></tr>' . "\n";
		}
	}

	$html .= '</table>' . "\n";

	$html .= '<p style="float:right"><i>form:' . $fformId . '/' . $formId . '/' . $fformVersion . '</i></p>' . "\n";

	$fformNeededDate = $filledFormElement->getAttribute(SUMAC_ATTRIBUTE_WHEN_NEEDED_BY);
	if ($fformNeededDate != "")
	{
		$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
		$neededDate = sumac_formatDate($fformNeededDate,$dateformat);
		//@@@$html .= '<tr><td colspan="2">' . sumac_formatMessage($_SESSION[SUMAC_STR]["CU21"],$neededDate) . '</td></tr>' . "\n";
	}

//OK button function depends on whether the form was opened in a new window or not
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1')	//open forms in new tab/window
	{
		$html .= '<br /><button id="sumac_completed_form_button" type="button" class="sumac_close_window"
					. onclick="window.close();">' . $_SESSION[SUMAC_STR]["CL6"] . '</button><br />';

	}
	else	//open forms in the existing tab/window - use form-submit mechanism to to get back to forms summary
	{
		$html .= '<br /><input name="viewcomplete" type="submit" value="' . $_SESSION[SUMAC_STR]["CL6"] . '" /><br />';
	}

	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getFormDataElement($formDataElements,$fieldId)
{
	for ($i = 0; $i < $formDataElements->length; $i++)
	{
		$formDataElement = $formDataElements->item($i);
		if ($formDataElement->getAttribute(SUMAC_ATTRIBUTE_FIELDID) == $fieldId) return $formDataElement;
	}
	return null;
}

function sumac_getHTMLTableOfFormStatus($updateFormStatus)
{
	$html = '<table title="Form update error message" border="0" rules="none" width="100%">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $updateFormStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getEscapeFromFormUpdateHTML($formId)
{
	$html = '';
//link destination depends on whether the form was opened in a new window or not
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1')	//open forms in new tab/window
	{
		$html .= '<br /><a class="sumac_return_to_forms_summary_link"'
				. ' href="sumac_form_update_abandoned.php?releaseform=' . $formId . '">'
				. SUMAC_LINK_TEXT_ABANDON_UPDATE . '</a><br />';
//		$html .= '<br /><button id="sumac_completed_form_button" type="button" class="sumac_close_window"
//					. onclick="window.close();">' . $_SESSION[SUMAC_STR]["CL1"]. '</button><br />';

	}
	else	//open forms in the existing tab/window
	{
		$html .= '<br /><a class="sumac_return_to_forms_summary_link"'
				. ' href="sumac_courses_redirect.php?releaseform=' . $formId . '&function=personal&div=formslist">'
				. SUMAC_LINK_TEXT_ABANDON_UPDATE . '</a><br />';
	}
	$html .= '</div>' . "\n";
//include navbars only if form was NOT opened in a new window
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] != '1')	//open forms in SAME tab/window
	{
		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','personal');
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	}
	return $html;
}

?>
