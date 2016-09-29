<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execShowDirectoryEntries($directoryEntriesDocument)
{
	$directoryElements = $directoryEntriesDocument->getElementsByTagName(SUMAC_ELEMENT_DIRECTORY);
	if ($directoryElements == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_DIRECTORY;
		return false;
	}
	if ($directoryElements->length != 1)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_TOOMANY_DIRECTORIES;
		return false;
	}
	$directoryName = $directoryElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$directoryText = $directoryElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_TEXT);

	$entryDefinitionElements = $directoryEntriesDocument->getElementsByTagName(SUMAC_ELEMENT_ED);
	if ($entryDefinitionElements == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_ENTRYDEFINITION;
		return false;
	}
	if ($entryDefinitionElements->length != 1)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_TOOMANY_ENTRYDEFINITIONS;
		return false;
	}

	$fieldDefinitionElements = $entryDefinitionElements->item(0)->getElementsByTagName(SUMAC_ELEMENT_FD);
	$fieldLabels = array();
	for ($i = 0; $i < $fieldDefinitionElements->length; $i++)
	{
		$fieldLabels[] = $fieldDefinitionElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_LABEL);;
	}

	$html = sumac_getHTMLHeadForDirectoryEntries($directoryName);
	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($directoryEntriesDocument,'directoryentries');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'directoryentries') . sumac_getSubtitle();

	if ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] != '1')	//show results in SAME tab/window
	{
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	}

	if ($_SESSION[SUMAC_SESSION_ALLOW_EITHER_FORMAT])	//give user choice of tabulation or list
	{
		$changeFormatButtonLabel = ($_SESSION[SUMAC_SESSION_RESULTS_FORMAT_CHOICE] == '1') ? $_SESSION[SUMAC_STR]["EL5"] : $_SESSION[SUMAC_STR]["EL6"];
		$html .= '<br /><button id="sumac_results_format_button" type="button" class="sumac_change_results_format"'
					. ' onclick="sumac_change_results_format();">' . $changeFormatButtonLabel . '</button><br />';
	}

	$html .= sumac_getHTMLBodyForDirectoryEntries($directoryName,$directoryText,$fieldLabels,$directoryEntriesDocument);

	if ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] != '1')	//show results in SAME tab/window
	{
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	}

	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_DIRECTORIES) . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForDirectoryEntries($directoryName)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] == '1');
	$html .= sumac_getHTMLTitle('','',$directoryName);

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getDirectoryEntriesHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getDirectoryEntriesHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getDirectoryEntriesHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}


function sumac_getDirectoryEntriesHTMLStyleElements()
{
	return <<<EODSS

div.sumac_directory_caption
{
	text-align:center; font-weight:bold; font-size:125%;
}
table.sumac_entries_as_list,
table.sumac_entries_as_table
{
	margin-left:auto; margin-right:auto; width:66%;
}
table.sumac_directory_text
{
	margin-left:auto; margin-right:auto;
}
td.sumac_directory_text
{
	border:medium solid; border-radius:7px; -moz-border-radius:7px;
	padding:12px 5px; text-align:justify;
}

#sumac_entries_as_list tr.sumac_row_1 td {padding-top:30px}
#sumac_entries_as_table td.sumac_label {font-weight:bold;border:1px solid lightgrey;}
#sumac_entries_as_table td.sumac_data {border:1px solid lightgrey;}

EODSS;
}

function sumac_getDirectoryEntriesHTMLScriptVariables()
{
	$html = '';
	$html .= 'var sumac_button_results_format_choice_1 = "' . $_SESSION[SUMAC_STR]["EL5"] . '";' . "\n";
	$html .= 'var sumac_button_results_format_choice_2 = "' . $_SESSION[SUMAC_STR]["EL6"] . '";' . "\n";
	$html .= 'var sumac_results_format_choice = ' . $_SESSION[SUMAC_SESSION_RESULTS_FORMAT_CHOICE] . ';' . "\n";
	return $html;
}

function sumac_getDirectoryEntriesHTMLScriptFunctions()
{
	return <<<EOJS

	function sumac_change_results_format()
	{

		var button = document.getElementById("sumac_results_format_button");
		var listdiv = document.getElementById("sumac_entries_as_list");
		var tablediv = document.getElementById("sumac_entries_as_table");

		if (sumac_results_format_choice == 1)
		{
			button.innerHTML = sumac_button_results_format_choice_2;
			listdiv.className = "sumac_nodisplay";
			tablediv.className = "sumac_emptyclass";
			sumac_results_format_choice = 2;
		}
		else
		{
			button.innerHTML = sumac_button_results_format_choice_1;
			tablediv.className = "sumac_nodisplay";
			listdiv.className = "sumac_emptyclass";
			sumac_results_format_choice = 1;
		}
	}

EOJS;
}

function sumac_getHTMLBodyForDirectoryEntries($directoryName,$directoryText,$fieldLabels,$directoryEntriesDocument)
{
	$html = '<div class="sumac_directory_caption">' . "\n";
	$html .= '<a class="sumac_directory_caption">' . $directoryName . '</a>' . "\n";
	$html .= '</div>' . "\n";
	$nodisplay = ($_SESSION[SUMAC_SESSION_RESULTS_FORMAT_CHOICE] == '1') ? 'sumac_emptyclass' : 'sumac_nodisplay';
	$html .= '<div id="sumac_entries_as_list" class="' . $nodisplay . '">' . "\n";
	$html .= '<table class="sumac_entries_as_list">' . "\n";
	if ($directoryText != null)
	{
		$html .= '<tr><td colspan="2">' . "\n";
		$html .= '    <table class="sumac_directory_text">' . "\n";
		$html .= '        <tr><td class="sumac_directory_text">' . $directoryText . '</td></tr>' . "\n";
		$html .= '    </table>' . "\n";
		$html .= '</td></tr>' . "\n";
	}
	$entryElements = $directoryEntriesDocument->getElementsByTagName(SUMAC_ELEMENT_E);
	for ($i = 0; $i < $entryElements->length; $i++)
	{
		$html .= sumac_getHTMLForOneEntry($entryElements->item($i),$fieldLabels,$i);
	}

	$OKclick = ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] == '1') ? 'window.close();' : 'history.back();';
	$html .= '<tr><td colspan="2"><button id="sumac_results_shown__as_list_button" type="button" class="sumac_close_window"'
				. ' onclick="' . $OKclick . '">' . $_SESSION[SUMAC_STR]["EL4"] . '</button></td></tr>';

	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";

	$nodisplay = ($_SESSION[SUMAC_SESSION_RESULTS_FORMAT_CHOICE] != '1') ? 'sumac_emptyclass' : 'sumac_nodisplay';
	$html .= '<div id="sumac_entries_as_table" class="' . $nodisplay . '">' . "\n";
	$html .= '<table class="sumac_entries_as_table">' . "\n";

	if ($directoryText != null)
	{
		$html .= '<tr><td colspan="' . count($fieldLabels) . '">' . "\n";
		$html .= '    <table class="sumac_directory_text">' . "\n";
		$html .= '        <tr><td class="sumac_directory_text">' . $directoryText . '</td></tr>' . "\n";
		$html .= '    </table>' . "\n";
		$html .= '</td></tr>' . "\n";
	}

	$html .= '<tr class="sumac_row_head">' . "\n";
	for ($i = 0; $i < count($fieldLabels); $i++)
	{
		$html .= '<td class="sumac_label sumac_label_' . ($i+1) . '">' . $fieldLabels[$i] . '</td>' . "\n";
	}
	$html .= '</tr>' . "\n";

	$entryElements = $directoryEntriesDocument->getElementsByTagName(SUMAC_ELEMENT_E);
	for ($i = 0; $i < $entryElements->length; $i++)
	{
		$html .= sumac_getHTMLForOneRow($entryElements->item($i),$i);
	}

	$OKclick = ($_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] == '1') ? 'window.close();' : 'history.back();';
	$html .= '<tr><td colspan="' . $entryElements->length . '"><button id="sumac_results_shown_as_table_button" type="button" class="sumac_close_window"'
				. ' onclick="' . $OKclick . '">' . $_SESSION[SUMAC_STR]["EL4"] . '</button></td></tr>';

	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLForOneEntry($entryElement,$fieldLabels,$elementNumber)
{
	$evenodd = ((($elementNumber+1) % 2) > 0) ? 'odd' : 'even';	//entry numbering starts with 1
	$fieldElements = $entryElement->getElementsByTagName(SUMAC_ELEMENT_F);
	$html = '<tbody class="sumac_entry sumac_entry_' . $evenodd . '">' . "\n";	//entry numbering starts with 1
	for ($i = 0; $i < $fieldElements->length; $i++)
	{
		$fieldData = $fieldElements->item($i)->textContent;
		if ($fieldData != '')
		{
			$html .= '<tr class="sumac_row sumac_row_' . ($i+1) . '"><td class="sumac_label sumac_label_' . ($i+1) . '">' . $fieldLabels[$i] . ':</td>' . "\n";
			$html .= '<td class="sumac_data sumac_data_' . ($i+1) . '">' . $fieldData . '</td></tr>' . "\n";
		}
	}
	$html .= '</tbody>' . "\n";
	return $html;
}

function sumac_getHTMLForOneRow($entryElement,$elementNumber)
{
	$evenodd = ((($elementNumber+1) % 2) > 0) ? 'odd' : 'even';	//row numbering starts with 1
	$html = '<tr class="sumac_entry sumac_entry_' . $evenodd . '">' . "\n";
	$fieldElements = $entryElement->getElementsByTagName(SUMAC_ELEMENT_F);
	for ($i = 0; $i < $fieldElements->length; $i++)
	{
		$html .= '<td class="sumac_data sumac_data_' . ($i+1) . '">' . $fieldElements->item($i)->textContent . '</td>' . "\n";
	}
	$html .= '</tr>' . "\n";
	return $html;
}

?>
