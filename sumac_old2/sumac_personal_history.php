<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execShowPersonalHistory($div,$formChosenPHP,$accountDocument)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$historyDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($historyDocument == false) return false;

	$financialAccountElement = $historyDocument->getElementsByTagName(SUMAC_ELEMENT_FINANCIALACCOUNT)->item(0);
	if ($financialAccountElement == false)
	{
		$_SESSION[SUMAC_SESSION_FINANCIALDETAIL_COUNT] = -1; // we have no financial account for you
	}
	else
	{
		$financialDetailElements = $financialAccountElement->getElementsByTagName(SUMAC_ELEMENT_FINANCIALDETAIL);
		$_SESSION[SUMAC_SESSION_FINANCIALDETAIL_COUNT] = $financialDetailElements->length;
	}

	$educationRecordsElement = $historyDocument->getElementsByTagName(SUMAC_ELEMENT_EDUCATIONRECORDS)->item(0);
	if ($educationRecordsElement == false)
	{
		$_SESSION[SUMAC_SESSION_EDUCATIONDETAIL_COUNT] = -1;
	}
	else
	{
		$educationDetailElements = $educationRecordsElement->getElementsByTagName(SUMAC_ELEMENT_EDUCATIONDETAIL);
		$_SESSION[SUMAC_SESSION_EDUCATIONDETAIL_COUNT] = $educationDetailElements->length;
	}

	$formsListElement = $historyDocument->getElementsByTagName(SUMAC_ELEMENT_FORMSLIST)->item(0);
	if ($formsListElement == false)
	{
		$_SESSION[SUMAC_SESSION_FORMTEMPLATE_COUNT] = -1;
	}
	else
	{
		$formTemplateElements = $formsListElement->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE);
		$_SESSION[SUMAC_SESSION_FORMTEMPLATE_COUNT] = $formTemplateElements->length;
	}

	$html = sumac_getPersonalHistoryHTML($historyDocument,$financialAccountElement,$educationRecordsElement,
											$formsListElement,$div,$formChosenPHP);
	echo $html;

	return true;
}

function sumac_getPersonalHistoryHTML($historyDocument,$financialAccountElement,$educationRecordsElement,
										$formsListElement,$div,$formChosenPHP)
{
	$html = sumac_getHTMLHeadForPersonalHistory($historyDocument);

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($historyDocument,'personalhistory');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'personal') . sumac_getSubtitle($_SESSION[SUMAC_STR]["CH8"]);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','personal');

	if (!$_SESSION[SUMAC_SESSION_OMIT_FINANCIAL_HISTORY]) $html .= sumac_getHTMLBodyForFinancialDetails($financialAccountElement);
	if (!$_SESSION[SUMAC_SESSION_OMIT_PERSONAL_HISTORY]) $html .= sumac_getHTMLBodyForEducationDetails($educationRecordsElement);
	if (!$_SESSION[SUMAC_SESSION_OMIT_FORMS_SUMMARY]) $html .= sumac_getHTMLBodyForFormsList($formsListElement,$formChosenPHP);

	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','personal');
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
		$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
	}

	if ($div != null) $html .= sumac_setRequestedPersonalDiv($div);
	$html .= '</body>' . "\n";
	$html .= '</html>' . "\n";

	return $html;
}

function sumac_getHTMLHeadForPersonalHistory($organisationDocument)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','',$_SESSION[SUMAC_STR]["CH8"]);

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getPersonalHistoryHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getPersonalHistoryHTMLScriptVariables();
	$html .= sumac_getPersonalHistoryHTMLScriptFunctions();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";
	return $html;
}

function sumac_getPersonalHistoryHTMLStyleElements()
{
//assign values to more manageable variables (the constants do not work in the 'heredoc' anyway)

	return <<<EOCSSPH
#sumac_financial_history_list,
#sumac_education_history_list,
#sumac_forms_list
{
	margin-left:auto;margin-right:auto;
	border:8px outset blue;border-radius:10px;-moz-border-radius:10px;
	padding:3px 3px;
}
/* #sumac_education_history_list td, */
#sumac_financial_history_list td,
#sumac_forms_list td
{
	padding:3px 15px 3px 15px;
}
#sumac_financial_history_list th,
#sumac_education_history_list th,
#sumac_forms_list th
{
	text-align:left;padding:3px 15px 3px 15px;
}
#sumac_financial_history_head td,
#sumac_education_history_head td,
#sumac_forms_list_head
{
	text-align:center;font-weight:bold;background:white;
	border:3px solid;border-radius:10px;-moz-border-radius:10px;
}
td.sumac_flh1
{
	padding:2px 2px;font-size:120%;
}
td.sumac_flh2
{
	text-align:left;padding:2px 2px;
}
#sumac_fhl_final_balance td
{
	text-align:right;font-weight:bold; background:#e9e5d8;
}
tr:nth-child(odd) td {background:white;}
/*tr:nth-child(even) td {background:#e5e5e5;}*/
td.sumac_fhl_date
{
	text-align:left;
}
td.sumac_fhl_description
{
	text-align:left;
}
td.sumac_fhl_debit
{
	text-align:right;
}
td.sumac_fhl_credit
{
	text-align:right;
}
td.sumac_fhl_balance
{
	text-align:right;
}
td.sumac_ehlad
{
	vertical-align:top
}
td.sumac_ehlx_name
{
	text-align:left;
}
td.sumac_ehlx_date
{
	text-align:left;
}
td.sumac_ehlx_status
{
	text-align:center;
}
td.sumac_ehlx_mark
{
	text-align:center;
}
td.sumac_ehlx_remarks
{
	text-align:left;max-width:250px;
}
th.sumac_fhl_title,
th.sumac_ehl_title
{
	text-align:center;
}
thead.sumac_ehl th.sumac_ehl_title
{
	text-align:left;
}
EOCSSPH;
}

function sumac_getPersonalHistoryHTMLScriptVariables()
{
	$html = '';
	$html .= 'var sumac_forms_open_target_1 = "_blank";' . "\n";
	$html .= 'var sumac_forms_open_target_2 = "_self";' . "\n";
	$html .= 'var sumac_id_form_openform = "' . SUMAC_ID_FORM_OPENFORM . '";' . "\n";
	$html .= 'var sumac_head_forms_open_choice_1 = "' . $_SESSION[SUMAC_STR]["CH5"] . '";' . "\n";
	$html .= 'var sumac_head_forms_open_choice_2 = "' . $_SESSION[SUMAC_STR]["CH6"] . '";' . "\n";
	$html .= 'var sumac_button_forms_open_choice_1 = "' . $_SESSION[SUMAC_STR]["CL5"] . '";' . "\n";
	$html .= 'var sumac_button_forms_open_choice_2 = "' . $_SESSION[SUMAC_STR]["CL4"] . '";' . "\n";
	$html .= 'var sumac_forms_open_choice = ' . $_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] . ';' . "\n";
	return $html;
}

function sumac_getPersonalHistoryHTMLScriptFunctions()
{
	return <<<EOJSPH

	function sumac_change_forms_open_choice()
	{
		var form = document.getElementById(sumac_id_form_openform);
		var links = document.getElementsByTagName("A");
		var button = document.getElementById("sumac_id_button_forms_open_choice");
		var text =  document.getElementById("sumac_id_text_forms_open_choice");
		var input =  document.getElementById("sumac_id_input_forms_open_choice");

		if (sumac_forms_open_choice == 1)
		{
			form.target = sumac_forms_open_target_2;
			for (var i = 0; i < links.length; i++)
			{
				var a = links[i];
				if (a.className == "sumac_fl_formlink")
				{
					a.target = sumac_forms_open_target_2;
					var ah = a.href;
					var ahnew = ah.replace("&formsopen=1","&formsopen=2");
					a.href = ahnew;
				}
			}
			text.innerHTML = sumac_head_forms_open_choice_2;
			button.innerHTML = sumac_button_forms_open_choice_2;
			input.value = '2';
			sumac_forms_open_choice = 2;
		}
		else
		{
			form.target = sumac_forms_open_target_1;
			for (var i = 0; i < links.length; i++)
			{
				var a = links[i];
				if (a.className == "sumac_fl_formlink")
				{
					a.target = sumac_forms_open_target_1;
					var ah = a.href;
					var ahnew = ah.replace("&formsopen=2","&formsopen=1");
					a.href = ahnew;
				}
			}
			text.innerHTML = sumac_head_forms_open_choice_1;
			button.innerHTML = sumac_button_forms_open_choice_1;
			input.value = '1';
			sumac_forms_open_choice = 1;
		}
	}
EOJSPH;
}

function sumac_getHTMLBodyForFinancialDetails($financialAccountElement)
{
	$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL22"];
	$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL22"];
	$html = '<div class="sumac_showhide_div"><button id="' .SUMAC_ID_BUTTON_FIN_HISTORY . '" type="button" class="sumac_history_showhide"'
				. ' onclick="sumac_unhide_table(this,\'' . SUMAC_ID_DIV_FIN_HISTORY . '\',\'' . $show
				. '\',\'' . $hide . '\',\'sumac_maintable\')">' . $show . '</button></div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_FIN_HISTORY . '" class="sumac_maintable sumac_nodisplay">' . "\n";

//create a table of financial detail (transaction) rows
	$html .= '  <table id="sumac_financial_history_list" class="sumac_fhl">' . "\n";
	$accountName = $_SESSION[SUMAC_SESSION_LOGGED_IN_NAME];
	$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];

	$html .= sumac_getHTMLFinancialListTableHead($accountName);
	$html .= '   <tbody>' . "\n";

	if ($_SESSION[SUMAC_SESSION_FINANCIALDETAIL_COUNT] > 0)
	{
		//$debitTotalCents = 0;
		//$creditTotalCents = 0;
		$initialCents = $financialAccountElement->getAttribute(SUMAC_ATTRIBUTE_INITIAL_CENTS);
		if (($initialCents != '') && ($initialCents != 0))
		{
			$openingBalance = ($initialCents < 0) ? sumac_centsToPrintableDollars(0 - $initialCents) : sumac_centsToPrintableDollars($initialCents) ;
			$html .= '    <tr id="sumac_fhl_opening_balance" class="sumac_fhl_opening_balance">';
			$html .= '<td></td><td class="sumac_fhl_description">' . $_SESSION[SUMAC_STR]["CU16"] . '</td><td colspan="2"></td>'
							. '<td class="sumac_fhl_balance">' . $openingBalance . '</td>'
							. '<td>' . (($initialCents < 0) ? '' : 'CR') . '</td>';
			$html .= '</tr>' . "\n";
		}
		else
		{
			$initialCents = 0;
		}
		$balanceCents = $initialCents;
		$financialDetailElements = $financialAccountElement->getElementsByTagName(SUMAC_ELEMENT_FINANCIALDETAIL);
		for ($i = 0; $i < $financialDetailElements->length; $i++)
		{
			$fdElement = $financialDetailElements->item($i);
			$html .= '    <tr class="sumac_fhl">' . "\n";
			$date = $fdElement->getAttribute(SUMAC_ATTRIBUTE_DATE);
			$html .= '     <td class="sumac_fhl_date">' . sumac_formatDate($date,$dateformat) . '</td>' . "\n";
			$description = $fdElement->getAttribute(SUMAC_ATTRIBUTE_DESCRIPTION);
			$html .= '     <td class="sumac_fhl_description">' . $description . '</td>' . "\n";
			$debitCents = $fdElement->getAttribute(SUMAC_ATTRIBUTE_DEBIT_CENTS);
			$html .= '     <td class="sumac_fhl_debit">' . sumac_centsToPrintableDollars($debitCents,true) . '</td>' . "\n";
			$creditCents = $fdElement->getAttribute(SUMAC_ATTRIBUTE_CREDIT_CENTS);
			$html .= '     <td class="sumac_fhl_credit">' . sumac_centsToPrintableDollars($creditCents,true) . '</td>' . "\n";
			$balanceCents = $balanceCents + $creditCents - $debitCents;
			$newBalance = ($balanceCents < 0) ? sumac_centsToPrintableDollars(0 - $balanceCents) : sumac_centsToPrintableDollars($balanceCents) ;
			$html .= '     <td class="sumac_fhl_balance">' . $newBalance . '</td>' . "\n";
			$html .= '	   <td>' . (($balanceCents <= 0) ? '' : 'CR') . '</td>';
			$html .= '    </tr>' . "\n";
			//$debitTotalCents += $debitCents;
			//$creditTotalCents += $creditCents;
		}
		//$html .= '    <tr class="sumac_fhl"><td></td>';
		//$html .= '<td class="sumac_fhl_description">' . $_SESSION[SUMAC_STR]["CU20"]. '</td>';
		//$html .= '<td class="sumac_fhl_debit">' . sumac_centsToPrintableDollars($debitTotalCents) . '</td>';
		//$html .= '<td class="sumac_fhl_credit">' . sumac_centsToPrintableDollars($creditTotalCents) . '</td>';
		//$html .= '</tr>' . "\n";
		$finalCents = $financialAccountElement->getAttribute(SUMAC_ATTRIBUTE_FINAL_CENTS);
		$owingOrCredit = ($finalCents < 0) ? $_SESSION[SUMAC_STR]["CU4"] : $_SESSION[SUMAC_STR]["CU5"];
		if ($finalCents < 0) $finalCents = 0 - $finalCents;
		$html .= '    <tr id="sumac_fhl_final_balance" class="sumac_fhl_balance">';
		$html .= '<td colspan="5">' . $owingOrCredit . sumac_centsToPrintableDollars($finalCents) . '</td>';
		$html .= '</tr>' . "\n";
	}

	$html .= '    <tr id="sumac_fhl_payment" class="sumac_fhl_payment">';
	$html .= '<td colspan="5"><a href="sumac_courses_redirect.php?function=payment">' . $_SESSION[SUMAC_STR]["CL15"] . '</a></td>';
	$html .= '</tr>' . "\n";
	$html .= '   </tbody>' . "\n";
	$html .= '   </table>' . "\n";

	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLFinancialListTableHead($accountName)
{
	$html = '   <thead class="sumac_fhl">' . "\n";
	if ($_SESSION[SUMAC_SESSION_FINANCIALDETAIL_COUNT] >= 0)
	{
		if ($_SESSION[SUMAC_SESSION_FINANCIALDETAIL_COUNT] > 0)
		{
			$html .= '    <tr id="sumac_financial_history_head" class="sumac_fhl_head">' . "\n";
			$html .= '    <td colspan="6">' . sumac_formatMessage($_SESSION[SUMAC_STR]["CH3"],$accountName) . '</td>' . "\n";
			$html .= '    </tr>' . "\n";
			$html .= '    <tr id="sumac_financial_history_list_titles" class="sumac_fhl_titles">' . "\n";
			$html .= '     <th class="sumac_fhl_title">' . $_SESSION[SUMAC_STR]["CC5"] . '</th>' . "\n";
			$html .= '     <th class="sumac_fhl_title">' . $_SESSION[SUMAC_STR]["CC7"] . '</th>' . "\n";
			$html .= '     <th class="sumac_fhl_title">' . $_SESSION[SUMAC_STR]["CC6"] . '</th>' . "\n";
			$html .= '     <th class="sumac_fhl_title">' . $_SESSION[SUMAC_STR]["CC4"] . '</th>' . "\n";
			$html .= '     <th class="sumac_fhl_title">' . $_SESSION[SUMAC_STR]["CC2"] . '</th>' . "\n";
			$html .= '    </tr>' . "\n";
		}
		else
		{
			$html .= '    <tr id="sumac_financial_history_head" class="sumac_fhl_head">' . "\n";
			$html .= '    <td colspan="6">' . sumac_formatMessage($_SESSION[SUMAC_STR]["CU9"],$accountName) . '</td>' . "\n";
			$html .= '    </tr>' . "\n";
		}
	}
	else
	{
		$html .= '    <tr id="sumac_no_financial_account" class="sumac_fhl_head">' . "\n";
		$html .= '    <td colspan="6">' . $_SESSION[SUMAC_STR]["CU13"] . '</td>' . "\n";
		$html .= '    </tr>' . "\n";
	}
	$html .= '   </thead>' . "\n";
	return $html;
}

function sumac_getHTMLBodyForEducationDetails($educationRecordsElement)
{
	$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL23"];
	$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL23"];
	$html = '<div class="sumac_showhide_div"><button id="' . SUMAC_ID_BUTTON_EDU_HISTORY . '" type="button" class="sumac_history_showhide"'
				. ' onclick="sumac_unhide_table(this,\'' . SUMAC_ID_DIV_EDU_HISTORY . '\',\'' . $show
				. '\',\'' . $hide . '\',\'sumac_maintable\')">' . $show . '</button></div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_EDU_HISTORY . '" class="sumac_maintable sumac_nodisplay">' . "\n";

//create a table of education detail (course/sessions) rows with sub-tables of exam detail
	$html .= '  <table id="sumac_education_history_list" class="sumac_ehl">' . "\n";
	$html .= sumac_getHTMLEducationListTableHead();
	$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];

	if ($_SESSION[SUMAC_SESSION_EDUCATIONDETAIL_COUNT] > 0)
	{
		$educationDetailElements = $educationRecordsElement->getElementsByTagName(SUMAC_ELEMENT_EDUCATIONDETAIL);
		for ($i = 0; $i < $educationDetailElements->length; $i++)
		{
			$edElement = $educationDetailElements->item($i);
			$courseName = $edElement->getAttribute(SUMAC_ATTRIBUTE_COURSE_NAME);
			$startDate = $edElement->getAttribute(SUMAC_ATTRIBUTE_START_DATE);
			$duration = $edElement->getAttribute(SUMAC_ATTRIBUTE_DURATION);
			$durationUnit = $edElement->getAttribute(SUMAC_ATTRIBUTE_DURATION_UNIT);
			$dateRegistered = $edElement->getAttribute(SUMAC_ATTRIBUTE_DATE_REGISTERED);
			$attendanceStatus = $edElement->getAttribute(SUMAC_ATTRIBUTE_ATTENDANCE_STATUS);
			$courseSummary = $courseName . ' (' . sumac_formatDate($startDate,$dateformat) . $_SESSION[SUMAC_STR]["CU24"] . $duration . '&nbsp;' . $durationUnit . ')';
			$html .= '   <tbody><tr class="sumac_ehla">' . "\n";
			$html .= '     <td class="sumac_ehlad">' . sumac_formatDate($dateRegistered,$dateformat) . '</td>' . "\n";
			$html .= '     <td class="sumac_ehlad">' . $attendanceStatus . '</td>' . "\n";
			$html .= '     <td class="sumac_ehlad">' . $courseSummary . '</td>' . "\n";

			$examResultElements = $edElement->getElementsByTagName(SUMAC_ELEMENT_EXAMRESULT);
			if ($examResultElements->length > 0)
			{
				$html .= '    </tr><tr class="sumac_ehla">' . "\n";
				$html .= '     <td colspan="2"></td>' . "\n";
				$html .= '     <td><table>' . "\n";
				$html .= sumac_getHTMLExamResultListTableHead();
				for ($j = 0; $j < $examResultElements->length; $j++)
				{
					$exrElement = $examResultElements->item($j);
					$html .= '       <tr class="sumac_ehlx">' . "\n";
					$name = $exrElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
					$html .= '         <td class="sumac_ehlx_name">' . $name. '</td>' . "\n";
					$date = $exrElement->getAttribute(SUMAC_ATTRIBUTE_DATE);
					$html .= '         <td class="sumac_ehlx_date">' . sumac_formatDate($date,$dateformat) . '</td>' . "\n";
					$status = $exrElement->getAttribute(SUMAC_ATTRIBUTE_STATUS);
					$html .= '         <td class="sumac_ehlx_status">' . $status . '</td>' . "\n";
					$mark = $exrElement->getAttribute(SUMAC_ATTRIBUTE_MARK);
					$html .= '         <td class="sumac_ehlx_mark">' . $mark . '</td>' . "\n";
					$remarks = $exrElement->getAttribute(SUMAC_ATTRIBUTE_REMARKS);
					$instructor = $exrElement->getAttribute(SUMAC_ATTRIBUTE_INSTRUCTOR);
					$instname = ($instructor != '') ? '&nbsp;[' . $instructor . ']' : '';
					$html .= '         <td class="sumac_ehlx_remarks">' . $remarks . $instname . '</td>' . "\n";
					$html .= '       </tr>' . "\n";
				}
				$html .= '       </table></td>' . "\n";
			}
// if there are no exam results, just carry on
			$html .= '   </tr></tbody>' . "\n";
		}
	}
	$html .= '   </table>' . "\n";

	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLEducationListTableHead()
{
	$html = '   ';
	if ($_SESSION[SUMAC_SESSION_EDUCATIONDETAIL_COUNT] > 0)
	{
		$html .= '<thead class="sumac_ehl">' . "\n";
		$html .= '  <tr id="sumac_education_history_head" class="sumac_ehl_head">'
				. '<td colspan="3">' . $_SESSION[SUMAC_STR]["CH2"] . '</td></tr>' . "\n";
		$html .= '  <tr class="sumac_ehl_head"><th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC15"] . '</th>'
					. '<th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC1"] . '</th><th class="sumac_ehl_title"></th></tr>' . "\n";
		$html .= '</thead>' . "\n";
	}
	else
	{
		$html .= '<thead class="sumac_ehl"><tr id="sumac_education_history_head" class="sumac_ehl_head">'
				. '<td colspan="3">' . $_SESSION[SUMAC_STR]["CU7"] . '</td></tr></thead>' . "\n";
	}
	return $html;
}

function sumac_getHTMLExamResultListTableHead()
{
	$html = '    <tr class="sumac_ehl_titles">' . "\n";
	$html .= '     <th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC9"] . '</th>' . "\n";
	$html .= '     <th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC5"] . '</th>' . "\n";
	$html .= '     <th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC18"] . '</th>' . "\n";
	$html .= '     <th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC14"] . '</th>' . "\n";
	$html .= '     <th class="sumac_ehl_title">' . $_SESSION[SUMAC_STR]["CC16"] . '</th>' . "\n";
	$html .= '    </tr>' . "\n";
	return $html;
}

function sumac_getHTMLBodyForFormsList($formsListElement,$formChosenPHP)
{
	$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL24"];
	$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL24"];
	$html = '<div class="sumac_showhide_div"><button id="' . SUMAC_ID_BUTTON_FORMS_LIST . '" type="button" class="sumac_history_showhide"'
				. ' onclick="sumac_unhide_table(this,\'' . SUMAC_ID_DIV_FORMS_LIST . '\',\'' . $show
				. '\',\'' . $hide . '\',\'sumac_maintable\')">' . $show . '</button></div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_FORMS_LIST . '" class="sumac_maintable sumac_nodisplay">' . "\n";

//create a table of forms that have been completely or partially filled
	$html .= '  <table id="sumac_forms_list" class="sumac_fl">' . "\n";

	$ffCount = 0;
	$ufCount = 0;
	if ($_SESSION[SUMAC_SESSION_FORMTEMPLATE_COUNT] > 0)
	{
		$formTemplateElements = $formsListElement->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE);
		for ($i = 0; $i < $formTemplateElements->length; $i++)
		{
			$sfElement = $formTemplateElements->item($i);
			$filledFormElements = $sfElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM);
			if ($filledFormElements->length > 0)
			{
				for ($j = 0; $j < $filledFormElements->length; $j++)
				{
					$filledForm = $filledFormElements->item($j);
					$fformIds[] = $filledForm->getAttribute(SUMAC_ATTRIBUTE_ID);
					$fformNames[] = $sfElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
					$fformStatuses[] = $filledForm->getAttribute(SUMAC_ATTRIBUTE_STATUS);
					$fformChangedDates[] = $filledForm->getAttribute(SUMAC_ATTRIBUTE_WHEN_MODIFIED);
					$fformNeededDates[] = $filledForm->getAttribute(SUMAC_ATTRIBUTE_WHEN_NEEDED_BY);
					$ffCount = $ffCount + 1;
				}
				if ($sfElement->getAttribute(SUMAC_ATTRIBUTE_REPEATABLE) != 'false')
				{
					$uformIds[] = $sfElement->getAttribute(SUMAC_ATTRIBUTE_ID);
					$uformNames[] = $sfElement->getAttribute(SUMAC_ATTRIBUTE_NAME) . $_SESSION[SUMAC_STR]["CU11"];
					$ufCount = $ufCount + 1;
				}
			}
			else
			{
				$uformIds[] = $sfElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$uformNames[] = $sfElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
				$ufCount = $ufCount + 1;
			}
		}
		$html .= sumac_getHTMLFormsListTableHead($ffCount);
		if ($ffCount > 0)
		{
			$html .= '    <tbody>' . "\n";
			$html .= sumac_getHTMLFormsListColumnTitles();
			for ($i = 0; $i < $ffCount; $i++)
			{
				$html .= sumac_getHTMLForFilledForm($fformIds[$i],$fformNames[$i],$fformStatuses[$i],$fformChangedDates[$i],$fformNeededDates[$i],$formChosenPHP);
			}
			$html .= '    </tbody>' . "\n";
		}
		if ($ufCount > 0)
		{
			//$html .= '    <tr><td colspan="4">' . $_SESSION[SUMAC_STR]["CU19"] . '</td></tr>' . "\n";
			$target = ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1') ? '_blank' : '_self';
			$html .= '    <tr><td colspan="4">'
						. '<form id="' . SUMAC_ID_FORM_OPENFORM . '" action="' . $formChosenPHP . '" accept-charset="UTF-8" method="post" target="' . $target . '">'
//	dummy				. '<form id="' . SUMAC_ID_FORM_OPENFORM . '" accept-charset="UTF-8" method="post" target="_blank">'
						. '<input id="sumac_id_input_forms_open_choice" type="hidden" name="formsopen" value="' . $_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] . '" />'
						. '<select id="sumac_new_form_selection" name="newform" onchange="document.getElementById(\'' . SUMAC_ID_FORM_OPENFORM . '\').submit();this.selectedIndex=0;">'
//	dummy				. '<select id="sumac_new_form_selection" name="newform" onchange="alert(\'Forms management not yet implemented\');">'
						. '<option value="none">' . $_SESSION[SUMAC_STR]["CU19"] . '</option>';

			for ($i = 0; $i < $ufCount; $i++)
			{
				$html .= '<option value="' . $uformIds[$i] . '">' . $uformNames[$i] . '</option>';
			}
			$html .= '</select></form></td></tr>' . "\n";
		}
		else
		{
		}
	}
	else
	{
		$html .= '<thead class="sumac_fl"><tr><td>' . $_SESSION[SUMAC_STR]["CU14"] . '</td></tr></thead>' . "\n";
	}

	$html .= '   </table>' . "\n";

	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLForFilledForm($id,$name,$status,$changedDate,$neededDate,$formChosenPHP)
{
	$target = ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1') ? '_blank' : '_self';
	$formsOpenChoice = '&amp;formsopen=' . $_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE];
	$dateformat = $_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT];
	$html = '    <tr class="sumac_fl_row">';
	$html .= '<td class="sumac_fl_name"><a class="sumac_fl_formlink" href="' . $formChosenPHP . '?filledform=' . $id . $formsOpenChoice . '" target="' . $target . '">' . $name . '</a></td>';
// dummy	$html .= '<td class="sumac_fl_name"><a class="sumac_fl_formlink" href="#' . SUMAC_ID_DIV_FORMS_LIST . '" onclick="alert(\'Forms management not yet implemented\');">' . $name . '</a></td>';
	$html .= '<td class="sumac_fl_status">' . $status . '</td>';
	$date = ($changedDate != '') ? sumac_formatDate($changedDate,$dateformat) : '';
	$html .= '<td class="sumac_fl_changed">' . $date . '</td>';
	$date = ($neededDate != '') ? sumac_formatDate($neededDate,$dateformat) : '';
	$html .= '<td class="sumac_fl_needed">' . $date . '</td>';
	$html .= '</tr>' . "\n";
	return $html;
}

function sumac_getHTMLFormsListTableHead($ffCount)
{
	$html = '    <thead><tr><td colspan="4"><table id="sumac_forms_list_head" class="sumac_fl_head">';
	if ($ffCount > 0)
	{
		$html .= '<tr><td class="sumac_flh1">' . $_SESSION[SUMAC_STR]["CH4"] . '</td></tr>';
		if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1')
		{
			$html .= '<tr><td class="sumac_flh2"><span class="sumac_flh3" id="sumac_id_text_forms_open_choice">' . $_SESSION[SUMAC_STR]["CH5"] . '</span>&nbsp;&nbsp;'
						. '<button id="sumac_id_button_forms_open_choice" type="button" onclick="sumac_change_forms_open_choice();">' . $_SESSION[SUMAC_STR]["CL5"] . '</button>'
						. '</td></tr>';
		}
		else
		{
			$html .= '<tr><td class="sumac_flh2"><span class="sumac_flh3" id="sumac_id_text_forms_open_choice">' . $_SESSION[SUMAC_STR]["CH6"] . '</span>&nbsp;&nbsp;'
						. '<button id="sumac_id_button_forms_open_choice" type="button" onclick="sumac_change_forms_open_choice();">' . $_SESSION[SUMAC_STR]["CL4"] . '</button>'
						. '</td></tr>';
		}
	}
	else
	{
		$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["CU8"] . '</td></tr>';
	}
	$html .= '</table></td></tr></thead>' . "\n";
	return $html;
}

function sumac_getHTMLFormsListColumnTitles()
{
	$html = '    <tr class="sumac_fl_titles">' . "\n";
	$html .= '     <th class="sumac_fl_title">' . $_SESSION[SUMAC_STR]["CC10"] . '</th>' . "\n";
	$html .= '     <th class="sumac_fl_title">' . $_SESSION[SUMAC_STR]["CC11"] . '</th>' . "\n";
	$html .= '     <th class="sumac_fl_title">' . $_SESSION[SUMAC_STR]["CC13"] . '</th>' . "\n";
	$html .= '     <th class="sumac_fl_title">' . $_SESSION[SUMAC_STR]["CC19"] . '</th>' . "\n";
	$html .= '    </tr>' . "\n";
	return $html;
}

function sumac_setRequestedPersonalDiv($div)
{
	$buttonId = '';
	$divId = '';
	$show = '';
	$hide = '';

	if ($div == 'finhistory')
	{
		$buttonId = SUMAC_ID_BUTTON_FIN_HISTORY;
		$divId = SUMAC_ID_DIV_FIN_HISTORY;
		$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL22"];
		$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL22"];
	}
	else if ($div == 'eduhistory')
	{
		$buttonId = SUMAC_ID_BUTTON_EDU_HISTORY;
		$divId = SUMAC_ID_DIV_EDU_HISTORY;
		$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL23"];
		$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL23"];
	}
	else if ($div == 'formslist')
	{
		$buttonId = SUMAC_ID_BUTTON_FORMS_LIST;
		$divId = SUMAC_ID_DIV_FORMS_LIST;
		$show = $_SESSION[SUMAC_STR]["CL21"] . $_SESSION[SUMAC_STR]["CL24"];
		$hide = $_SESSION[SUMAC_STR]["CL20"] . $_SESSION[SUMAC_STR]["CL24"];
	}
	else
	{
		return ''; //meaningless
	}

	return <<<EOJSRPD
<script type="text/javascript">
sumac_unhide_table(document.getElementById('$buttonId'),'$divId','$show','$hide','sumac_maintable');
</script>
EOJSRPD;
}

?>