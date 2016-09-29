<?php
//version5123//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execSelectDirectory($directoryChosenPHP)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;
	$directoryElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_DIRECTORY);

	if ($directoryElements->length < 1)
	{
		$html = sumac_getNoDirectorieToSearchHTML($organisationDocument);
		echo $html;
		return true;
	}

	$html = sumac_getDirectorySelectionHTML($organisationDocument,$directoryChosenPHP);
	if ($html !== false)
	{
		echo $html;
		return true;
	}
	else
	{
		return false;
	}
}

function sumac_getNoDirectorieToSearchHTML($organisationDocument)
{
	$html = sumac_getHTMLHeadForDirectorySelection();
	$html .= '<body>' . "\n";
	$html .= sumac_addParsedXmlIfDebugging($organisationDocument,'directory');
	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectdirectory') . sumac_getSubtitle();
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= '<table class="sumac_function_impossible">' . "\n";
	$html .= '<tr><td class="sumac_status">' . $_SESSION[SUMAC_STR]["EE1"] . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);
	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
		$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
	}
	$html .= '</body>' . "\n";
	$html .= '</html>' . "\n";

	return $html;
}

function sumac_getDirectorySelectionHTML($organisationDocument,$directoryChosenPHP)
{
	$html = sumac_getHTMLHeadForDirectorySelection();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($organisationDocument,'directory');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectdirectory') . sumac_getSubtitle();
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$HTMLBodyForSelect = sumac_getHTMLBodyForSelect($organisationDocument,$directoryChosenPHP);

	if ($HTMLBodyForSelect !== false)
	{
		$html .= $HTMLBodyForSelect;
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
		$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

		if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
		{
			$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
			if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
			$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
		}

//		$html .= sumac_showSessionVariables('sumac_');

		$html .= '<script  type="text/javascript">document.getElementById("sumac_search_button_0").focus();</script>' . "\n";
		$html .= '</body>' . "\n";
		$html .= '</html>' . "\n";

		return $html;
	}
	else
	{
		return false;
	}
}

function sumac_getHTMLHeadForDirectorySelection()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements(true);
	$html .= sumac_getDirectorySelectionHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();

	$html .= '</style>' . "\n";

	$html .= '<script type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getDirectorySelectionHTMLScriptVariables();
	$html .= sumac_getDirectorySelectionHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";
	return $html;
}

function sumac_getDirectorySelectionHTMLStyleElements()
{
//assign values to more manageable variables (the defined constants do not work in the 'heredoc' anyway)
	$scrollableSelectorHeight = $_SESSION[SUMAC_USERPAR_SELECTORHT];
	$selectorBackgroundColour = $_SESSION[SUMAC_USERPAR_SELECTORBG];
	$selectedBackgroundColour = $_SESSION[SUMAC_USERPAR_SELECTEDBG];

	return <<<EODSS

div.sumac_directory_caption
{
	text-align:center; font-weight:bold; font-size:125%;
}
div.sumac_directory_div
{
	margin-left:auto; margin-right: auto;
}
table.sumac_directory
{
	margin-left:auto; margin-right:auto; width:66%;
}
table.sumac_selector_text
{
	margin-left:auto; margin-right:auto;
}
td.sumac_selector_text
{
	border:medium solid; border-radius:7px; -moz-border-radius:7px;
	padding:12px 5px; text-align:justify;
}
td.sumac_selector_name
{
	width:10px;white-space:nowrap;
}
table.sumac_choose_new_or_same_window
{
	margin-left:auto; margin-right:auto;
}
td.sumac_choose_new_or_same_window
{
	border:medium solid; border-radius:7px; -moz-border-radius:7px;
	padding:12px 5px; text-align:justify;
}
div.sumac_selector_scroll
{
	border:1px solid; border-radius:5px; -moz-border-radius:5px;
	padding:3px 3px; text-align:left;
	font-size: 80%;	background-color:$selectorBackgroundColour;
	max-height:$scrollableSelectorHeight; overflow:scroll;
}
div.sumac_selector_noscroll
{
	border:1px solid; border-radius:5px; -moz-border-radius:5px;
	padding:3px 3px; text-align:left;
	font-size:80%; background-color:$selectorBackgroundColour;
}
sumac_selector_instructions
{
	font-size:75%; font-style:italic;
}
select.sumac_selector_dropdown
{
	background-color:$selectorBackgroundColour;
}
input.sumac_selector_entry
{
	background-color:$selectorBackgroundColour;
}
input.sumac_directory_search
{
border:3px outset;border-radius:7px;-moz-border-radius:7px;
background:lightgrey;color:black;
padding:0px 0px;text-align:center;
margin-right:auto; margin-top:10px; margin-bottom:10px;
height:40px; max-width:400px; font-size:125%; display:block;
}
a.sumac_notselected
{
 	display:block;
 	padding:3px 5px 3px 5px;
}
a.sumac_selected
{
	display:block;
 	padding:2px 4px 2px 4px;
	font-weight:bold; background-color:$selectedBackgroundColour; color:white;
}
EODSS;
}

function sumac_getDirectorySelectionHTMLScriptVariables()
{
	$html = '';
	$html .= 'var sumac_results_show_target_1 = "_blank";' . "\n";
	$html .= 'var sumac_results_show_target_2 = "_self";' . "\n";
	$html .= 'var sumac_head_results_show_choice_1 = "' . $_SESSION[SUMAC_STR]["EH2"] . '";' . "\n";
	$html .= 'var sumac_head_results_show_choice_2 = "' . $_SESSION[SUMAC_STR]["EH3"] . '";' . "\n";
	$html .= 'var sumac_button_results_show_choice_1 = "' . $_SESSION[SUMAC_STR]["EL3"] . '";' . "\n";
	$html .= 'var sumac_button_results_show_choice_2 = "' . $_SESSION[SUMAC_STR]["EL2"] . '";' . "\n";
	$html .= 'var sumac_results_show_choice = ' . $_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] . ';' . "\n";
	return $html;
}

function sumac_getDirectorySelectionHTMLScriptFunctions()
{
	return <<<EOJS

	function sumac_show_directory_selectors(event,index,classname)
	{
		var divs = document.getElementsByTagName('DIV');
		for (var i = 0; i < divs.length; i++)
		{
			if (divs[i].id == "sumac_directory_div_" + index) divs[i].className = classname;
			else if (divs[i].className == classname) divs[i].className = divs[i].className + ' sumac_nodisplay';
		}
		document.getElementById("sumac_search_button_"  + index).focus();
		if (event != null) event.preventDefault();	//do NOT go off to href link
	}

	function sumac_select_multiple_choices(thisChoice,inputId,choiceId)
	{
		if (thisChoice.className == 'sumac_notselected')
		{
			thisChoice.className = 'sumac_selected';
			document.getElementById(inputId).value = choiceId;
		}
		else
		{
			thisChoice.className = 'sumac_notselected';
			document.getElementById(inputId).value = '';
		}
	}

	function sumac_change_results_show_choice()
	{

		var form = document.getElementById("sumac_form_selectdirectory");
		var text =  document.getElementById("sumac_text_results_show_choice");
		var button = document.getElementById("sumac_button_results_show_choice");
		var input =  document.getElementById("sumac_input_results_show_choice");

		if (sumac_results_show_choice == 1)
		{
			form.target = sumac_results_show_target_2;
			text.innerHTML = sumac_head_results_show_choice_2;
			button.innerHTML = sumac_button_results_show_choice_2;
			input.value = '2';
			sumac_results_show_choice = 2;
		}
		else
		{
			form.target = sumac_results_show_target_1;
			text.innerHTML = sumac_head_results_show_choice_1;
			button.innerHTML = sumac_button_results_show_choice_1;
			input.value = '1';
			sumac_results_show_choice = 1;
		}
	}

EOJS;
}

function sumac_getHTMLBodyForSelect($organisationDocument,$directoryChosenPHP)
{
	$target = ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] == 1) ? '_blank' : '_self';
	$html = '<form id="' . SUMAC_ID_FORM_SELECT_DIRECTORY . '" action="' . $directoryChosenPHP . '" accept-charset="UTF-8" method="post" target="' . $target . '">' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_SELECT . '" class="sumac_maintable">' . "\n";
	$html .= '<table class="sumac_instructions">' . "\n";
	$html .= '<tr><td class="sumac_instructions">' . "\n";
	$directoryElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_DIRECTORY);
	if ($directoryElements->length == 1) $html .= sumac_formatMessage($_SESSION[SUMAC_STR]["EI1"],$_SESSION[SUMAC_STR]["EL1"]);
	else $html .= sumac_formatMessage($_SESSION[SUMAC_STR]["EI2"],$_SESSION[SUMAC_STR]["EL1"]);
	$html .= '</td></tr>';
	$html .= '</table>' . "\n";
	for ($i = 0; $i < $directoryElements->length; $i++)
	{
		$html .= sumac_getHTMLForSelectingFromOneDirectory($directoryElements->item($i),$i,($directoryElements->length == 1));
	}

	$html .= '<table class="sumac_choose_new_or_same_window">' . "\n";
	$html .= '    <tr><td class="sumac_choose_new_or_same_window">' . "\n";
	if ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] == '1')
		$html .= '    <span class="sumac_rsc" id="sumac_text_results_show_choice">' . $_SESSION[SUMAC_STR]["EH2"] . '</span>&nbsp;&nbsp;'
	     	  . '<button class="sumac_rsc" id="sumac_button_results_show_choice" type="button" onclick="sumac_change_results_show_choice();">' . $_SESSION[SUMAC_STR]["EL3"] . '</button>' . "\n";
	else
		$html .= '    <span class="sumac_rsc" id="sumac_text_results_show_choice">' . $_SESSION[SUMAC_STR]["EH3"] . '</span>&nbsp;&nbsp;'
	     	  . '<button class="sumac_rsc" id="sumac_button_results_show_choice" type="button" onclick="sumac_change_results_show_choice();">' . $_SESSION[SUMAC_STR]["EL2"] . '</button>' . "\n";
	$html .= '    </td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= '<input id="sumac_input_directory" type="hidden" name="directory" value="none" />' . "\n";
	$html .= '<input id="sumac_input_results_show_choice" type="hidden" name="resultsshow" value="' . $_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] . '" />' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</form>' . "\n";

	return $html;
}

function sumac_getHTMLForSelectingFromOneDirectory($directoryElement,$index,$isSingle)
{
	$directoryName = $directoryElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$directoryId = $directoryElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$html = '<div class="sumac_directory_caption">' . "\n";
	$html .= '<a class="sumac_directory_caption" href="x://show" onclick="sumac_show_directory_selectors(event,\'' . $index . '\',\'sumac_directory_div\')">' . $directoryName . '</a>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '<div id="sumac_directory_div_' . $index . '" class="sumac_directory_div' . ($isSingle ? '' : ' sumac_nodisplay') . '">' . "\n";
	$html .= '<table class="sumac_directory">' . "\n";
	$text = $directoryElement->getAttribute(SUMAC_ATTRIBUTE_TEXT);
	if ($text != null)
	{

		$html .= '<tr><td colspan="2">' . "\n";
		$html .= '    <table class="sumac_selector_text">' . "\n";
		$html .= '        <tr><td class="sumac_selector_text">' . $text . '</td></tr>' . "\n";
		$html .= '    </table>' . "\n";
		$html .= '</td></tr>' . "\n";
	}
	$selectorElements = $directoryElement->getElementsByTagName(SUMAC_ELEMENT_SELECTOR);
	for ($i = 0; $i < $selectorElements->length; $i++)
	{
		$html .= sumac_getHTMLForOneSelector($selectorElements->item($i));
	}

	$html .= '<tr><td></td><td><input id="sumac_search_button_' . $index . '" type="submit" class="sumac_directory_search" name="search"'
			. ' value="' . $_SESSION[SUMAC_STR]["EL1"] . '" onclick="document.getElementById(\'sumac_input_directory\').value=\'' . $directoryId . '\';" /></td></tr>' . "\n";

	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLForOneSelector($selectorElement)
{
	$html = '';
	$selectorId = $selectorElement->getAttribute(SUMAC_ATTRIBUTE_ID);
	$selectorName = $selectorElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$style= $selectorElement->getAttribute(SUMAC_ATTRIBUTE_STYLE);
	$multiple= ($selectorElement->getAttribute(SUMAC_ATTRIBUTE_MULTIPLE) == "true");
	$choiceElements = $selectorElement->getElementsByTagName(SUMAC_ELEMENT_CHOICE);
	$html .= '<tr class="sumac_selector">' . "\n";
	$html .= '    <td class="sumac_selector_name">' . $selectorName . '</td>' . "\n";
	$html .= '    <td>' . "\n";
	$html .= '        <table><tr>' . "\n";
	$hidden = '';
	if ($style == "dropdown")
	{
		if ($multiple)
		{
			$html .= '            <td><div class="sumac_selector_scroll">' . "\n";
			for ($i = 0; $i < $choiceElements->length; $i++)
			{
				$choiceElement = $choiceElements->item($i);
				$choiceValue = $choiceElement->textContent;
				$choiceId = $choiceElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$inputId = "sumac_selector_" . $selectorId . '_' . $i;
				$html .= '                <a class="sumac_notselected" draggable="false"'
						. ' onclick="sumac_select_multiple_choices(this,\'' . $inputId . '\',\'' . $choiceId . '\');">'
						. $choiceValue . '</a>' . "\n";
				$hidden .= '<input id="' . $inputId . '" type="hidden" name="selector=' . $selectorId . '[' . $i . ']" value="" />' . "\n";
			}
			$html .= '            </div></td>' . "\n";
			$html .= '            <td class="sumac_selector_instructions">' . $_SESSION[SUMAC_STR]["EI3"] . '</td>' . "\n";
		}
		else	//only single selection allowed
		{
			$html .= '            <td><select class="sumac_selector_dropdown" name="selector=' . $selectorId . '">' . "\n";
			for ($i = 0; $i < $choiceElements->length; $i++)
			{
				$choiceElement = $choiceElements->item($i);
				$choiceValue = $choiceElement->textContent;
				$choiceId = $choiceElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$html .= '                <option value="' . $choiceId . '">' . $choiceValue . '</option>' . "\n";
			}
			$html .= '            </select></td>' . "\n";
			$html .= '            <td class="sumac_selector_instructions">' . $_SESSION[SUMAC_STR]["EI4"] . '</td>' . "\n";
		}
	}
	else if ($style == "checkbox")
	{
		if ($multiple)
		{
			$html .= '            <td><div class="sumac_selector_noscroll">' . "\n";
			for ($i = 0; $i < $choiceElements->length; $i++)
			{
				$choiceElement = $choiceElements->item($i);
				$choiceValue = $choiceElement->textContent;
				$choiceId = $choiceElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$inputId = "sumac_selector_" . $selectorId . '_' . $i;
				$html .= '                <input id="' . $inputId . '" type="checkbox" name="selector=' . $selectorId . '[' . $i . ']" value="' . $choiceId . '" />'
						. '<label class="sumac_selector_checkbox" for="' . $inputId . '">' . $choiceValue . '</label><br />' . "\n";
			}
			$html .= '            </div></td>' . "\n";
			$html .= '            <td class="sumac_selector_instructions">' . $_SESSION[SUMAC_STR]["EI5"] . '</td>' . "\n";
		}
		else
		{
			$html .= '            <td><div class="sumac_selector_noscroll">' . "\n";
			for ($i = 0; $i < $choiceElements->length; $i++)
			{
				$choiceElement = $choiceElements->item($i);
				$choiceValue = $choiceElement->textContent;
				$choiceId = $choiceElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$inputId = "sumac_id_" . $choiceId;
				$checked = ($i == 0) ? ' checked="checked"' : '';
				$html .= '                <input id="' . $inputId . '" type="radio" name="selector=' . $selectorId . '" value="' . $choiceId . '"' . $checked . ' />'
						. '<label class="sumac_selector_checkbox" for="' . $inputId . '">' . $choiceValue . '</label><br />' . "\n";
			}
			$html .= '            </div></td>' . "\n";
			$html .= '            <td class="sumac_selector_instructions">' . $_SESSION[SUMAC_STR]["EI6"] . '</td>' . "\n";
		}
	}
	else if ($style == "entry")
	{
		$html .= '            <td>' . "\n";
		$html .= '                <input class="sumac_selector_entry" type="text" name="selector=' . $selectorId . '" value="" />';
		$html .= '            </td>' . "\n";
		$html .= '            <td class="sumac_selector_instructions">' . $_SESSION[SUMAC_STR]["EI7"] . '</td>' . "\n";
	}
	else
	{
		//error
	}
	$html .= '        </tr></table>' . "\n";
	$html .= '    </td>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '<tr><td colspan="2">' . $hidden . '</td></tr>' . "\n";
	return $html;
}

?>
