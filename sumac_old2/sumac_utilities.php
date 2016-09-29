<?php
//version551//

include_once 'sumac_constants.php';

function sumac_centsToPrintableDollars($cents,$blankWhenZero=false,$withCents=true,$thousandsComma=true)
{
	$dollars = $cents / 100;
	if ($blankWhenZero && ($dollars == 0)) return '';
	if ($withCents)
	{
		if ($thousandsComma) return ($_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . number_format($dollars,2));
		else return ($_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . number_format($dollars,2,'.',''));
	}
	else
	{
		if ($thousandsComma) return ($_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . number_format($dollars,0));
		else return ($_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . number_format($dollars,0,'',''));
	}
}

function sumac_oldFormatMessageUsingAmpersand($message)
{
	$replacementCount = func_num_args() - 1;
	$toBeReplaced = array("&0", "&1", "&2", "&3", "&4", "&5", "&6", "&7", "&8", "&9");
	array_splice($toBeReplaced,$replacementCount);	//remove ones for which there arent replacements
	$toReplaceThem = func_get_args();
	array_splice($toReplaceThem,0,1);				//remove message from replacement array
	$fm = str_replace($toBeReplaced,$toReplaceThem,$message);
	return $fm;
}

function sumac_formatMessage($message)
{
	$replacementCount = func_num_args() - 1;
	$toBeReplaced = array("%0", "%1", "%2", "%3", "%4", "%5", "%6", "%7", "%8", "%9");
	array_splice($toBeReplaced,$replacementCount);	//remove ones for which there arent replacements
	$toReplaceThem = func_get_args();
	array_splice($toReplaceThem,0,1);				//remove message from replacement array
	$fm = str_replace($toBeReplaced,$toReplaceThem,$message);
	return $fm;
}

function sumac_formatDate($yyyymmdd,$format)
{
	$pattern = ($format == null) ? $_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT] : $format;
	$pattern = str_replace('yyyy',substr($yyyymmdd,0,4),$pattern);
	if (strpos($pattern,'yyy') !== false)
	{
		date_default_timezone_set('UTC');	//avoid tiresome warning message
		$dateArray = getdate();
		if (substr($yyyymmdd,0,4) == $dateArray['year']) $pattern = str_replace('yyy','',$pattern);
		else $pattern = str_replace('yyy',substr($yyyymmdd,0,4),$pattern);
	}
	$pattern = str_replace('yy',substr($yyyymmdd,2,2),$pattern);
	$pattern = str_replace('mmmm',sumac_getFullMonthName(substr($yyyymmdd,5,2)),$pattern);
	$pattern = str_replace('mmm',sumac_getShortMonthName(substr($yyyymmdd,5,2)),$pattern);
	$pattern = str_replace('mm',substr($yyyymmdd,5,2),$pattern);
	$pattern = str_replace('m',(substr($yyyymmdd,5,2) + 0),$pattern);
	$pattern = str_replace('dd',substr($yyyymmdd,8,2),$pattern);
	$pattern = str_replace('d',(substr($yyyymmdd,8,2) + 0),$pattern);
	$pattern = str_replace('ww',sumac_getFullDayName($yyyymmdd),$pattern);
	$pattern = str_replace('w',sumac_getShortDayName($yyyymmdd),$pattern);

	return $pattern;
}

function sumac_getFullMonthName($mm)
{
	if (($mm < 1) || ($mm > 12)) return 'invalid-month-' . $mm;
	$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
	return $months[$mm - 1];
}

function sumac_getShortMonthName($mm)
{
	if (($mm < 1) || ($mm > 12)) return '???';
	$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	return $months[$mm - 1];
}

function sumac_getFullDayName($yyyymmdd)
{
	date_default_timezone_set('UTC');	//avoid tiresome warning message
//	$date = date_create_from_format('Y-m-d',$yyyymmdd);
	$unixtimestamp = strtotime($yyyymmdd);
	$dateArray = getdate($unixtimestamp);
	return $dateArray['weekday'];
}

function sumac_getShortDayName($yyyymmdd)
{
	return substr(sumac_getFullDayName($yyyymmdd),0,3);
}

function sumac_addParsedXmlIfDebugging($document,$name)
{
	$html = '';
	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'parsedxml') !== false)
	{
		$html .= '<div id="parsed' . $name . 'xml"><p>';
		$html .= sumac_getXMLElementsAndAttributesAsHTML($document,0);
		$html .= '</p></div>' . "\n";
	}
	return $html;
}

function sumac_echoRawXMLIfDebugging($xmldata,$name)
{
	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'rawxml') !== false)
	{
		echo '<div id="raw' . $name . 'xml"><p>';
		if (strpos($xmldata,'<?xml ') !== false)
		{
			$endxmlheader = strpos($xmldata,'>');
			if ($endxmlheader !== false) echo substr($xmldata,($endxmlheader+1));
		}
		else echo $xmldata;
		echo '</p></div>' . "\n";
	}
}

function sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed()
{
	$html = '';
	if ($_SESSION[SUMAC_SESSION_SUPPRESS_USER_OVERRIDE_CSS] == false)
	{
		$html .= "\n" . '/**************** THIS IS NOT SUMAC CSS ****************/' . "\n";
		$html .= '/************ THIS IS OVERLAYING SUMAC CSS *************/' . "\n";
		$html .= sumac_getUserFile(SUMAC_USER_OVER_SUMAC . '.css');
		$html .= "\n" . '/********** END OF CSS OVERLAYING SUMAC CSS ************/' . "\n";
	}
	return $html;
}

function sumac_getCommonHTMLStyleElements($onlyDirectorySelectionPage=false)
{
//for version 5.0.2 make special arrangements for Directory Selection Page

//assign values to more manageable variables (the defined constants do not work in the 'heredoc' anyway)
	$scale = $_SESSION[SUMAC_SESSION_SCALE];
	$fonts = $_SESSION[SUMAC_SESSION_FONTS];
	$textcolour = $_SESSION[SUMAC_SESSION_TEXTCOLOUR];
	$bodycolour = $_SESSION[SUMAC_SESSION_BODYCOLOUR];
	$h1_title_colour = $_SESSION[SUMAC_SESSION_H1_TITLE_COLOUR];
	$narrownavbg = $_SESSION[SUMAC_SESSION_NAVBGFOLDER] . 'narrownavbg.' . $_SESSION[SUMAC_SESSION_NAVBGFILEXT];
	$sbbgcolour = $_SESSION[SUMAC_SESSION_SBBGCOLOUR];
	$mandfldcol = $_SESSION[SUMAC_SESSION_MANDFLDCOL];
	$topnavbg = $_SESSION[SUMAC_SESSION_NAVBGFOLDER] . 'topnavbg.' . $_SESSION[SUMAC_SESSION_NAVBGFILEXT];
	$topnavbgwus2 = $_SESSION[SUMAC_SESSION_NAVBGFOLDER] . 'topnavbgwus2.' . $_SESSION[SUMAC_SESSION_NAVBGFILEXT];
	$bottomnavbgwus2 = $_SESSION[SUMAC_SESSION_NAVBGFOLDER] . 'bottomnavbgwus2.' . $_SESSION[SUMAC_SESSION_NAVBGFILEXT];
	$divthanks = SUMAC_ID_DIV_THANKS;

	return <<<EOCSS1A

body {
font-size:$scale;
font-family:$fonts;
color:$textcolour;
background:$bodycolour;
margin:0px 0px 0px 0px;
}
#$divthanks {font-size:2.0em; font-weight:bold;}
h1.sumac_subtitle
{
	font-size:2.0em;font-weight:bold;
	color:$h1_title_colour;
}
#sumac_top_action_navbar
{
	height:28px;background-image:url("$narrownavbg");
	background-position:bottom;background-repeat:repeat-x;
}
#sumac_bottom_action_navbar
{
	height:28px;background-image:url("$narrownavbg");
	background-position:bottom;background-repeat:repeat-x;
}
/* [note that the pseudo-attribute -moz-border-radius is for Firefox 3.6 and earlier] */

/* all instructions come with a middling solid border and middling rounding with plenty of padding */
/* and the text in them is 'justified' i.e. spread across the whole width */
td.sumac_instructions
{
	border:medium solid;border-radius:7px;-moz-border-radius:7px;
	padding:12px 5px;text-align:justify;
}
/* and the panel containing the instructions should be centred */
table.sumac_instructions
{
	margin-left:auto;margin-right:auto;
}
/* tables for logging in or identifying yourself all have thin solid borders with larger rounding */
/* and they have a middling amount of padding and their columns always have left aligned fields */
/* and they are centred in the window (left and right margins 'auto') */
/* the same for the payment/donation table and the details update table */
/* the tables for small forms are restricted in width so that their instructions do not distort them */
table.sumac_new,
table.sumac_login,
table.sumac_neworlogin,
table.sumac_getpwd,
table.sumac_payment,
table.sumac_update
{
	margin-left:auto;margin-right:auto;
	border:1px solid;border-radius:10px;-moz-border-radius:10px;
	padding:3px 3px;text-align:left;
}
table.sumac_smallform
{
	max-width:360px;
}

EOCSS1A
 . ($onlyDirectorySelectionPage
 ?
<<<EOCSS1B1
/* all the action buttons in all the forms have middling rounding */
/* the buttons start as black text on light grey background, */
/* and are very pale with grey text when disabled */
/* and go to white text on dark grey when the mouse is over them */
/* (the hide/show basket/selections buttons are similar */
     /* input[type="submit"], this must be omitted for Directory Selection page */
button.sumac_close_window,
button.sumac_basket_showhide,
button.sumac_history_showhide
{
	border:3px outset;border-radius:7px;-moz-border-radius:7px;
	width:125px;background:lightgrey;color:black;
	padding:0px 0px;text-align:center;
}
button.sumac_history_showhide { width:160px; }

EOCSS1B1
:
<<<EOCSS1B2
/* all the action buttons in all the forms have middling rounding */
/* the buttons start as black text on light grey background, */
/* and are very pale with grey text when disabled */
/* and go to white text on dark grey when the mouse is over them */
/* (the hide/show basket/selections buttons are similar */
input[type="submit"],
button.sumac_close_window,
button.sumac_basket_showhide,
button.sumac_history_showhide
{
	border:3px outset;border-radius:7px;-moz-border-radius:7px;
	width:125px;background:lightgrey;color:black;
	padding:0px 0px;text-align:center;
}
button.sumac_history_showhide { width:160px; }

EOCSS1B2
) .
<<<EOCSS1C
/* buttons in the event selection page that hide and show detail */
/* smaller in both directions than the action buttons */
button.sumac_show_detail,
button.sumac_hide_detail
{
	border:2px outset;border-radius:5px;-moz-border-radius:5px;
	width:100px;height:20px;background:lightgrey;color:black;
	padding:0px 0px;text-align:center;
	font-size:75%;
}
/* buttons with more text need to be wider */
input[name="buycourse"]
{
	width:160px;
}
input[type="submit"]:hover,
button.sumac_close_window:hover,
button.sumac_basket_showhide:hover,
button.sumac_history_showhide:hover,
button.sumac_show_detail:hover,
button.sumac_hide_detail:hover
{
	background:slategrey;color:white;
}
input[type="submit"]:disabled,
button.sumac_close_window:disabled,
button.sumac_basket_showhide:disabled,
button.sumac_history_showhide:disabled,
button.sumac_show_detail:disabled,
button.sumac_hide_detail:disabled
{
	background:#E0E0E0;color:grey;
}
/* all data-entry fields (text/password/drop-down) have a thin solid border with half the rounding of the table border */
/* they have a middling amount of padding and their text is always left aligned */
input[type="text"],
input[type="password"],
select
{
	border:1px solid;border-radius:5px;-moz-border-radius:5px;
	padding:3px 3px;text-align:left;
}
/* when any data-entry field is missing, it gets a thicker crimson border */
input[type="text"].sumac_missing,
input[type="password"].sumac_missing,
textarea.sumac_missing,
select.sumac_missing
{
	border:2px solid crimson;border-radius:5px;-moz-border-radius:5px;
	padding:2px 3px;
}
/* when a data-entry field in a form is flagged as invalid, it gets a broken crimson border */
input[type="text"].sumac_invalid,
textarea.sumac_invalid
{
	border:2px dashed crimson;border-radius:5px;-moz-border-radius:5px;
	padding:2px 3px;
	text-decoration:line-through;
}
/* any text entry field that is optional will be shown in italic */
input[placeholder="OPTIONAL"]
{
	font-style:italic;
}
/* error messages are shown in crimson in bold italics */
td.sumac_status
{
	font-style:italic;font-weight:bold;
	color:crimson;
}
/* here we provide a small but visible margin around the main area */
/* this is because the original design used defaults that did that - which may not be a very good reason */
/* there is also a mysterious statement that prevents the 'floating' navigation bars from drifting around the main table */
.sumac_maintable
{
	margin:0px 10px 0px 10px;
	clear:both;
}
/* this class is used to suppress the display of anything */
/* it is often used in addition to other class settings */
.sumac_nodisplay
{
	display:none;
}
/* this class can be used as a place-holder in place of a null class */
.sumac_emptyclass
{
}
/* the links in the nav-bars in the ticketing event selection page use underlining to show which is selected */
a.sumac_navlink:link,a.sumac_navlink:visited
{
	color:black;text-decoration:none;
}
a.sumac_navlink:hover,a.sumac_navlink:active
{
	color:purple;text-decoration:none;
}
a.sumac_selected_navlink
{
	color:purple;text-decoration:underline;
}
a.sumac_disabled_navlink
{
	color:grey;text-decoration:line-through;
}
/* these navbar links are now (v4) used by all packages */
.sumac_navbar_small_links
{
	word-spacing:6px;font-size:100%;white-space:nowrap;text-align:left;
	padding-left:13px;padding-right:13px;padding-top:5px;
}

table.sumac_basket
{
	border:3px solid;border-radius:10px;-moz-border-radius:10px;
	width:100%;padding:0px 0px;text-align:left;
	background:$sbbgcolour;
}
#sumac_top_tall_navbar
{
	height:50px;background-image:url("$topnavbg");
	background-position:bottom;background-repeat:repeat-x;
}
#sumac_top_ticketing_navbar,#sumac_top_courses_navbar
{
	height:28px;background-image:url("$topnavbgwus2");
	background-position:bottom;background-repeat:repeat-x;
}
#sumac_bottom_ticketing_navbar,#sumac_bottom_courses_navbar
{
	height:28px;background-image:url("$bottomnavbgwus2");
	background-position:bottom;background-repeat:repeat-x;
}
.sumac_navbar_large_links
{
	word-spacing:12px;font-size:190%;white-space:nowrap;text-align:left;
	padding-left:15px;padding-right:15px;padding-top:6px;
}
div.sumac_showhide_div
{
	padding:2px 0px 2px 0px;margin:auto;width:200px;
}
td.sumac_formfieldlabelrequired,
span.sumac_formfieldlabelrequired
{
	color:$mandfldcol;
}
td.sumac_formdatafield textarea
{
	width:500px;height:50px;
}
table.sumac_function_impossible
{
	margin-left:auto;margin-right:auto;margin-top:50px;margin-bottom:50px;
	border:3px solid;border-radius:10px;-moz-border-radius:10px;
	padding:3px 3px;text-align:left;
}
.sumac_note_textarea
{
	width:400px;height:35px;
}

td.sumac_commpreflabel {vertical-align:top;}
label.sumac_commpref {display:inline;white-space:nowrap;padding-right:10px;}

EOCSS1C;
}

function sumac_getCommonCourseCatalogHTMLStyleElements()
{
//assign values to more manageable variables (the defined constants do not work in the 'heredoc' anyway)
	$sbbgcolour = $_SESSION[SUMAC_SESSION_SBBGCOLOUR];

	return <<<EOCSS2
/* the registration summary is full width, has its own background colour and a thickish border and larger rounding */
table.sumac_registration
{
	border:3px solid;border-radius:10px;-moz-border-radius:10px;
	width:100%;padding:0px 0px;text-align:left;
	background:$sbbgcolour;
}
/* course registration table settings */
table.sumac_course_registration
{
	border:3px outset;border-radius:15px;-moz-border-radius:15px;
	background:$sbbgcolour;
}
table.sumac_single_course_registration
{
	margin-left:auto;margin-right:auto;margin-top:20px;margin-bottom:20px;
	border:3px outset;border-radius:15px;-moz-border-radius:15px;
	background:$sbbgcolour;
}
input.sumac_single_course_register
{
	width:150px;

}
table.sumac_course_registration td
{
	 border-bottom-style:none;
}
td.sumac_cc_cents
{
	display:none;
}
td.sumac_cc_paycents
{
	display:none;
}
td.sumac_cc_selector
{
	float:right;
}
td.sumac_cc_name
{
	width:150px;
}
td.sumac_cc_payable_bold
{
	font-weight:bold;width:100px;text-align:right;
}
td.sumac_cc_payable_nonbold
{
	font-weight:normal;width:100px;text-align:right;
}
table.sumac_course_registration td.sumac_cc_paytotal
{
	font-weight:bold;width:100px;text-align:right;border:3px solid;border-bottom-style:solid;
}
th.sumac_course_registration_foot
{
	color:purple;
}
td.sumac_rs_course
{
	font-weight:bold;text-align:center;
}
td.sumac_rs_cost,
td.sumac_rs_price
{
	text-align:right;
}
td.sumac_rs_quantity
{
	text-align:left;
}
td.sumac_rs_query
{
	display:block;
}
/* the iframe areas that hold the event/performance detail */
td.sumac_detailpanel
{
	margin-left:auto;margin-right:auto;
}
iframe.sumac_course_detail
{
	 width:100%;height:400px;
}
iframe.sumac_instr_detail
{
	 width:100%;height:300px;
}
EOCSS2;
}

function sumac_getCommonHTMLScriptVariables()
{
	$html = 'var sumac_supply_missing_information = "' . $_SESSION[SUMAC_STR]["AJ1"] . '";' . "\n";
	$html .= 'var sumac_class_status = "sumac_status";' . "\n";
	$html .= 'var sumac_class_missing = "sumac_missing";' . "\n";
	$html .= 'var sumac_id_td_status = "' . SUMAC_ID_TD_STATUS . '";' . "\n";
	$html .= 'var sumac_currency_symbol = "' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '";' . "\n";
	return $html;
}

function sumac_getCommonHTMLScriptFunctions()
{
	return <<<EOCJS
	function sumac_centsToPrintableDollars(cents)
	{
		var dollars = Number(cents) / 100;
		return (sumac_currency_symbol + dollars.toFixed(2));
	}

	function sumac_formatMessage(message)
	{
		var fm = message;
		var rc = arguments.length;
		for (var i = 1; i < rc; i++)
		{
			var r = '%' + (i-1);
			var re = new RegExp(r,'g');
			var fm = fm.replace(re,arguments[i]);
		}
		return fm;
	}

	function sumac_checknamedfields(fieldarray)
	{
		var missing = 0;
		for (var i = 0; i <fieldarray.length; i++)
		{
			var namedfields = document.getElementsByName(fieldarray[i]);
			if (namedfields.length != 1)
			{
				alert("Unknown field name "+fieldarray[i]+" "+namedfields.length);
			}
			else if (namedfields[0].value == "")
			{
				++missing; /* thats the real error */
				namedfields[0].className = sumac_class_missing;
				if (missing == 1) namedfields[0].focus();
			}
			else
			{
				namedfields[0].className = "";
			}
		}
		if (missing > 0)
		{
			document.getElementById(sumac_id_td_status).innerHTML = sumac_supply_missing_information;
			document.getElementById(sumac_id_td_status).className = sumac_class_status;
			return true;	//that means field IS missing
		}
		//no blank fields
		document.getElementById(sumac_id_td_status).innerHTML = "";
		document.getElementById(sumac_id_td_status).className = "";
		return false;
	}

	function sumac_unhide_table(thisbutton,tableid,hiddenlabel,unhiddenlabel,unhiddenclass)
	{
		if (thisbutton.innerHTML == unhiddenlabel) return;
		document.getElementById(tableid).className = unhiddenclass;
		var onclick = "sumac_hide_table(this,'" + tableid + "','" + hiddenlabel + "','" + unhiddenlabel +
					"','" + unhiddenclass + "');";
		thisbutton.setAttribute("onclick",onclick);
		thisbutton.innerHTML = unhiddenlabel;
	}

	function sumac_hide_table(thisbutton,tableid,hiddenlabel,unhiddenlabel,unhiddenclass)
	{
		if (thisbutton.innerHTML == hiddenlabel) return;
		document.getElementById(tableid).className = unhiddenclass + " sumac_nodisplay";
		var onclick = "sumac_unhide_table(this,'" + tableid + "','" + hiddenlabel + "','" + unhiddenlabel +
					"','" + unhiddenclass + "');";
		thisbutton.setAttribute("onclick",onclick);
		thisbutton.innerHTML = hiddenlabel;
	}

EOCJS;
}

function sumac_getHTMLFormField($fl,$it,$fn,$fs,$fm,$opt,$fv='')
{
	$ph = ($opt ? SUMAC_FIELD_OPTIONAL : '');
	return '<tr><td>' .
			$fl . '</td><td><input type="' . $it . '" name="' . $fn .
			'" size="' . $fs . '" maxlength="' . $fm . '" placeholder="' . $ph .
			'" value="' . $fv . '" /></td></tr>' . "\n";
}

function sumac_getHTMLFormOptionsForValueArrayUsingValue($values,$firstOption,$preselectedOption)
{
	$html = ($firstOption === false) ? '' : '<option value="' . $firstOption . '"></option>';
	for ($i = 0; $i < count($values); $i++)
	{
		$selected = ($i == $preselectedOption) ? ' selected' : '';
		$html .= '<option' . $selected . ' value="' . $values[$i] . '">' . $values[$i] . '</option>';
	}
	return $html;
}

function sumac_getHTMLFormOptionsForValueArrayUsingIndex($values,$blankFirstOption,$preselectedOption)
{
	$html = $blankFirstOption ? '<option value=""></option>' : '';
	for ($i = 0; $i < count($values); $i++)
	{
		$selected = ($i == $preselectedOption) ? ' selected' : '';
		$html .= '<option' . $selected . ' value="' . $i . '">' . $values[$i] . '</option>';
	}
	return $html;
}

function sumac_getHTMLFormOptionsForExpiryMonth($firstoptid='')
{
	$optid = ($firstoptid != '') ? ('id="' . $firstoptid . '" ') : '';
	$html = '<option ' . $optid . 'value="">&nbsp;</option>';
	$html .= '<option value="01">01 ' . SUMAC_EXPIRY_MONTH_01 . '</option>';
	$html .= '<option value="02">02 ' . SUMAC_EXPIRY_MONTH_02 . '</option>';
	$html .= '<option value="03">03 ' . SUMAC_EXPIRY_MONTH_03 . '</option>';
	$html .= '<option value="04">04 ' . SUMAC_EXPIRY_MONTH_04 . '</option>';
	$html .= '<option value="05">05 ' . SUMAC_EXPIRY_MONTH_05 . '</option>';
	$html .= '<option value="06">06 ' . SUMAC_EXPIRY_MONTH_06 . '</option>';
	$html .= '<option value="07">07 ' . SUMAC_EXPIRY_MONTH_07 . '</option>';
	$html .= '<option value="08">08 ' . SUMAC_EXPIRY_MONTH_08 . '</option>';
	$html .= '<option value="09">09 ' . SUMAC_EXPIRY_MONTH_09 . '</option>';
	$html .= '<option value="10">10 ' . SUMAC_EXPIRY_MONTH_10 . '</option>';
	$html .= '<option value="11">11 ' . SUMAC_EXPIRY_MONTH_11 . '</option>';
	$html .= '<option value="12">12 ' . SUMAC_EXPIRY_MONTH_12 . '</option>';
	return $html;
}

function sumac_getHTMLFormOptionsForExpiryYear($firstoptid='')
{
	date_default_timezone_set('UTC');	//avoid tiresome warning message
	$today = getdate();
	$year0 = $today['year'];
	$optid = ($firstoptid != '') ? ('id="' . $firstoptid . '" ') : '';
	$html = '<option ' . $optid . 'value="">&nbsp;</option>';
	for ($i = $year0; $i < ($year0 + 10); $i++)
	{
		$html .= '<option value="' . $i . '">' . $i . '</option>';
	}
	return $html;
}

function sumac_getElementValue($document,$elementTagName)
{
	$elements = $document->getElementsByTagName($elementTagName);
	if ($elements->length == 0) return '';
	else
	{
		$element = $elements->item(0);
		//note that if there was more than one matching tag we take the first and don't complain
		if ($element->childNodes->item(0) != null) return $element->childNodes->item(0)->nodeValue;
		else return '';
	}
}

function sumac_getElementValuesAsArray($document,$elementTagName)
{
	$elementValues = array();
	$elements = $document->getElementsByTagName($elementTagName);
	for ($i = 0; $i < $elements->length; $i++)
	{
		$element = $elements->item($i);
		if ($element->childNodes->item(0) != null)
		{
			$elementValues[] = $element->childNodes->item(0)->nodeValue;
		}
	}
	return $elementValues;
}

function sumac_getJSToRestoreEnteredValues($postedName,$formId)
{
	//this restores values already entered (except passwords which should already have been 'unset' from $_POST)
	$html = '';
	if (($postedName == null) || isset($_POST[$postedName]))
	{
		$html .= '<script  type="text/javascript">var nf = "";' . "\n";
		foreach ($_POST as $name => $value)
		{
			$html .= 'nf = document.getElementsByName("'. $name . '");';
			$html .= 'if (nf.length == 1)';
			$html .= ' if (nf[0].form.id == "' . $formId . '")';
			$html .= ' nf[0].value = "' . $value .'";' . "\n";
		}
		$html .= '</script>' . "\n";
	}
	return $html;
}

function sumac_getJSToRestoreCheckedBoxes($postedName,$formId,$allChckBxs)
{
	//this restores checkboxes already checked
	$html = '';
	if (($postedName == null) || isset($_POST[$postedName]))
	{
		$html .= '<script  type="text/javascript">var nf = "";' . "\n";
		$chkBxs = Array();
		$i = 0;
		foreach ($_POST as $name => $value)
			if (substr($name,0,5) == 'cpip_') //CheckBox for Communication Preference
			{
				$chkBxs[$i] = $name;
				$i += 1;
			}

		//Clear all checkboxes
		foreach($allChckBxs as $name)
		{
			$html .= 'nf = document.getElementsByName("'. $name . '");';
			$html .= 'if (nf.length == 1)';
			$html .= ' if (nf[0].form.id == "' . $formId . '")';
			$html .= ' nf[0].checked = false;' . "\n";
		}
		//Check checkboxes
		foreach($allChckBxs as $name)
		{
			foreach($chkBxs as $i => $nameChecked)
			if($name == $nameChecked){
				$html .= 'nf = document.getElementsByName("'. $name . '");';
				$html .= 'if (nf.length == 1)';
				$html .= ' if (nf[0].form.id == "' . $formId . '")';
				$html .= ' nf[0].checked = true;'. "\n";
			}
		}
		$html .= '</script>' . "\n";
	}
	return $html;

}

function sumac_getJSToRestoreAllEnteredValues($formid)
{
	//this restores text values already entered
	$html = '<script type="text/javascript">var notused = sumac_restore_entered_text_value("'.$formid.'",[';
	foreach ($_POST as $name => $value)
	{
		//$html .= 'sumac_restore_entered_text_value("'.$name.'","'.$value.'","'.$formid.'");';
		$html .= '["'.$name.'","'.$value.'"],';
	}
	$html .= ']);'.PHP_EOL;
	$html .= 'notused = sumac_restore_selections_made("'.$formid.'",notused);'.PHP_EOL;
	$html .= 'notused = sumac_restore_radio_picks_made("'.$formid.'",notused);'.PHP_EOL;
	$html .= 'notused = sumac_restore_checked_boxes("'.$formid.'",notused);'.PHP_EOL;
	$html .= 'notused = sumac_restore_hidden_values("'.$formid.'",notused);'.PHP_EOL;
	$html .= '</script>'.PHP_EOL;
	return $html;
}

function sumac_getJSToRestoreEnteredTextValues($formid)
{
	//this restores text values already entered
	$html = '<script type="text/javascript">sumac_restore_entered_text_value("'.$formid.'",[';
	foreach ($_POST as $name => $value)
	{
		//$html .= 'sumac_restore_entered_text_value("'.$name.'","'.$value.'","'.$formid.'");';
		$html .= '["'.$name.'","'.$value.'"],';
	}
	$html .= ']);</script>'.PHP_EOL;
	return $html;
}

function sumac_getJSToRestoreSelectionsMade($formid)
{
	//this restores selections made
	$html = '<script type="text/javascript">sumac_restore_selections_made("'.$formid.'",[';
	foreach ($_POST as $name => $value)
	{
		$html .= '["'.$name.'","'.$value.'"],';
	}
	$html .= ']);</script>'.PHP_EOL;
	return $html;
}

function sumac_getJSToRestoreRadioPicksMade($formid)
{
	//this restores radio picks made
	$html = '<script type="text/javascript">sumac_restore_radio_picks_made("'.$formid.'",[';
	foreach ($_POST as $name => $value)
	{
		$html .= '["'.$name.'","'.$value.'"],';
	}
	$html .= ']);</script>'.PHP_EOL;
	return $html;
}

function sumac_getJSToConfirmUseOfHTTP()
{
//assign values to more manageable variables (the defined constants do not work in the 'heredoc' anyway)
	$confirmHTTPmessage = SUMAC_WARNING_CONFIRM_HTTP;

	return <<<EOJSCUH
<script type="text/javascript">
if (confirm("$confirmHTTPmessage") != true)
{
    document.body.style.backgroundColor="#404040";
    var inputs = document.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++)
    {
        var input = inputs[i];
        if (input.getAttribute("type") == "submit")
        {
            input.setAttribute("disabled","disabled"); input.style.backgroundColor = "#404040";
        }
        if ((input.type == "text") || (input.type == "password") || (input.type == "radio") || (input.type == "checkbox"))
        {
            input.setAttribute("disabled","disabled"); input.style.backgroundColor = "#202020";
        }
    }
    var buttons = document.getElementsByTagName("button");
    for (var i = 0; i < buttons.length; i++)
    {
			var button = buttons[i];
			button.setAttribute("disabled","disabled");
			button.style.backgroundColor="#404040";
    }
    var selects = document.getElementsByTagName("select");
    for (var i = 0; i < selects.length; i++)
    {
			var select = selects[i];
			select.setAttribute("disabled","disabled");
			select.style.backgroundColor="#404040";
    }
	var noFocus = true;
    var links = document.getElementsByTagName("a");
    for (var i = 0; i < links.length; i++)
    {
        var link = links[i];
        var isExit = ((link.id.substr(0,16) == "sumac_link_leave")
        			|| (link.id.substr(0,20) == "sumac_link_quit_HTTP")
        			|| (link.className.substr(0,19) == "sumac_goback_button"));
        if (noFocus && isExit)
        { link.focus(); link.style.textDecoration = "underline"; link.style.color = "Green"; noFocus = false; }
        else
        { link.className = "sumac_disabled_navlink"; link.removeAttribute("href"); link.setAttribute("onclick",";"); }
    }
}
else
{
    var links = document.getElementsByTagName("a");
    for (var i = 0; i < links.length; i++)
    {
        var link = links[i];
        if (link.id.substr(0,20) == "sumac_link_quit_HTTP") link.className = "sumac_nodisplay";
    }
}
</script>
EOJSCUH;
}

function sumac_getUserParameterFromFile($p)
{
	if (isset($_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS]) === false)
	{
		$filename = 'sumac_parameter.settings';
		$params = sumac_getUserFile($filename);
		if (trim($params) == '') return '';
		$paramArray = explode('|',$params);
		$pa = array();
		for ($i = 0; $i < (count($paramArray)+1); $i = $i + 2)
		{
			$pakey = trim($paramArray[$i]);
			if (($i+1) < count($paramArray)) $pavalue = $paramArray[$i+1]; else $pavalue = '';
//echo $pakey . '=' . $pavalue . '<br />';
			if ($pakey == '') continue;
			if ($pavalue == '') continue;
			$pa[$pakey] = $pavalue;
		}
		$_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS] = $pa;
	}
	if (isset($_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS][$p])) return $_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS][$p];
	else return null;
}

function sumac_getUserHTML($filename,$head=false,$page='unknown')
{
	//close off double content divs before adding user footer HTML
	$html = (($head == false) ? '</div></div>' : '') . "\n";
	$html .= sumac_getUserFile($filename . '.htm');
	//add double content divs following user header HTML
	$pageid = 'sumac_' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . '_' . $page . '_content';
	$html .= ($head ? '<div id="sumac_content"><div id="' . $pageid . '">' : '')  . "\n";
	return $html;
}

function sumac_getUserCSS($filename)
{
//	$classLimit = '.' . SUMAC_CLASS_PREFIX_USER . $filename . ' ';
//	$css = sumac_getUserFile($filename . '.css');
//	if (trim($css) == '') return '';
//	$cssArray = explode('}',$css);
//	//remove '@' elements because they cannot be prefixed with the class
//	$ruleArray = array();
//	for ($i = (count($cssArray) - 1); $i >= 0; $i--)
//	{
//		$selector = trim($cssArray[$i]);
//		if (substr($selector,0,1) == '@') { $ruleArray[] = $selector; array_splice($cssArray,$i,1); }
//	}
//	array_pop($cssArray);	//since the final entry is blank or null
//	$css = $classLimit . implode('} ' . $classLimit,$cssArray) . '}';
//	for ($i = 0; $i < count($ruleArray); $i++) $css .= $ruleArray[$i] . '}';
//	return $css;
//THAT VERSION DEPENDED ON THE HTML HAVING AN ENCLOSING DIV ELEMENT - AND WE HAVE DROPPED THAT

	return sumac_getUserFile($filename . '.css');
}

function sumac_getUserFile($filename)
{
//echo SUMAC_USER_FOLDER . '/' . $filename . '<br />';
	$error_level = error_reporting();
	$new_level = error_reporting($error_level ^ E_WARNING);
	$data = file_get_contents(SUMAC_USER_FOLDER . '/' . $filename,true);
	$error_level = error_reporting($error_level);
	return $data;
}

function sumac_getFileContents($folder,$filename)
{
	$error_level = error_reporting();
	$new_level = error_reporting($error_level ^ E_WARNING);
	$data = file_get_contents($folder.'/'.$filename,true);
	$error_level = error_reporting($error_level);
	return $data;
}

function sumac_getSubtitle($title="")
{
	$html = '<h1 class="sumac_subtitle" align="center">';
	if ($title != "") $html .= $title;
	else if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
		&& (isset($_SESSION[SUMAC_SESSION_FREQUENCY]) !== false)
		&& (substr($_SESSION[SUMAC_SESSION_FREQUENCY],0,1) == 'M'))
			$html .= $_SESSION[SUMAC_SESSION_H1_TITLE_MONTHLY];
	else $html .= sumac_get_h1_title();
	$html .= '</h1>';
	return $html;
}

function sumac_getHTMLTitle($prefix,$suffix,$title="")
{
	$html = '<title>' . $prefix;
	if ($title != "") $html .= $title;
	else if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
		&& (isset($_SESSION[SUMAC_SESSION_FREQUENCY]) !== false)
		&& (substr($_SESSION[SUMAC_SESSION_FREQUENCY],0,1) == 'M'))
			$html .= $_SESSION[SUMAC_SESSION_H1_TITLE_MONTHLY];
	else $html .= sumac_get_h1_title();
	$html .= ' for ' . $_SESSION[SUMAC_SESSION_ORGANISATION_NAME];
	$html .= $suffix . '</title>' . "\n";
	return $html;
}

function sumac_get_h1_title()
{
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION) return $_SESSION[SUMAC_SESSION_H1_DPTITLE];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) return $_SESSION[SUMAC_SESSION_H1_MRTITLE];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE) return $_SESSION[SUMAC_SESSION_H1_CUTITLE];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES) return $_SESSION[SUMAC_SESSION_H1_CRTITLE];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) return $_SESSION[SUMAC_SESSION_H1_TOTITLE];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DIRECTORIES) return $_SESSION[SUMAC_STR]["EH1"];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') return $_SESSION[SUMAC_STR]["AH1"];
	else return $_SESSION[SUMAC_STR]["AH2"]; //not usually valid
}

function sumac_getHTMLMetaSettings($disconnected=false)
{
	$html = '<meta http-equiv="Content-Script-Type" content="text/javascript" />' . "\n";
	$html .= '<meta charset="utf-8" />' . "\n";
	if ($disconnected) $html .= '<meta name="description" content="disconnected" />' . "\n";
	return $html;
}

function sumac_getFormClosingHTML($message,$formName,$failed=true)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";
	$html .= sumac_getHTMLMetaSettings($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1');
	$html .= '<title>' . $formName . ($failed ? $_SESSION[SUMAC_STR]["CB1"] : $_SESSION[SUMAC_STR]["CB2"]) . '</title>' . "\n";
	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getCommonCourseCatalogHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";
	$html .= '</head>' . "\n";
	$html .= '<body>' . "\n";

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'formclose') . sumac_getSubtitle($formName);

	$html .= '<div id="' . SUMAC_ID_DIV_THANKS . '" class="sumac_maintable">';
	if ($failed)
	{
		$html .= '<table class="sumac_function_impossible">' . "\n";
		$html .= '<tr><td class="sumac_status">' . $message . '</td></tr>' . "\n";
	}
	else
	{
		$html .= '<table class="sumac_function_completed">' . "\n";
		$html .= '<tr><td class="sumac_status">' . $message . '</td></tr>' . "\n";
	}
	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_CLOSE . '" class="sumac_div_close">' . "\n";

//OK button function depends on whether the form was opened in a new window or not
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] == '1')	//open forms in new tab/window
	{
		$html .= '<br /><button id="sumac_completed_form_button" type="button" class="sumac_close_window"'
					. ' onclick="window.close();">' . $_SESSION[SUMAC_STR]["CL6"] . '</button><br />';
	}
	else	//open forms in the existing tab/window - use form-submit mechanism to to get back to forms summary
	{
		$html .= '<br /><form id="sumac_OK_button_form" accept-charset="UTF-8" method="post" action="sumac_courses_redirect.php">'
					. '<input name="viewcomplete" type="submit" value="' . $_SESSION[SUMAC_STR]["CL6"] . '" /></form><br />';
	}
	$html .= '</div>' . "\n";
//include navbars only if form was NOT opened in a new window
	if ($_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] != '1')	//open forms in SAME tab/window
	{
		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','personal');
		$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	}
	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);
	$html .= '</body></html>' . "\n";
	return $html;
}

function sumac_getFunctionCompletedHTML($title,$message,$organisationDocument,$extrasDocument,$exitpackage)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";
	$html .= sumac_getHTMLMetaSettings();
	$html .= '<title>' . $title . '</title>' . "\n";
	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getCommonCourseCatalogHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";
	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= '</script>' . "\n";
	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'thankyou') . sumac_getSubtitle();

	if (($organisationDocument !== false) && ($extrasDocument !== false))
	{
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
		{
			$html .= sumac_getBasketAndExtrasHTML($organisationDocument,$extrasDocument,false);
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		{
			$html .= sumac_getSelectedSessionHTML($organisationDocument,$extrasDocument);
		}
	}

	if ($exitpackage == true)
	{
//empty basket, cancel selections, set order total back to zero, set no-package active
		if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) unset($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
		if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS])) unset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]);
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
		$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] = '';
	}

	$html .= '<div id="' . SUMAC_ID_DIV_THANKS . '" class="sumac_maintable">' . $message . "\n";
	$html .= '</div>' . "\n";

	if ($exitpackage == false)
	{
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
		{
include_once 'sumac_ticketing_utilities.php';
			$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_bottom_ticketing_navbar",'',true,false);
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		{
			$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','registration');
		}
	}

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
//			$html .= '<br /><a href="' . $_SESSION[SUMAC_SESSION_RETURN] . '#SUMACRETURN">' . sumac_formatMessage($_SESSION[SUMAC_STR]["AE6"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . '</a>';

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	$html .= '</body></html>' . "\n";

	return $html;
}

function sumac_getAbortHTML()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";
	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('',$_SESSION[SUMAC_STR]["AB1"]);
	$html .= '<style type="text/css">';

	$html .= sumac_getCommonHTMLStyleElements();
	$html .= '#' . SUMAC_ID_DIV_FATAL . ' {font-size:2.0em;font-weight:bold;text-align:center;margin:50px 50px 50px 50px;}' . "\n";

	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);

	$html .= '</style>' . "\n";
	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'failed') . sumac_getSubtitle();

	if (isset($_SESSION[SUMAC_SESSION_FATAL_ERROR]) === false) $_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_ANY_OTHER_ERROR];

	$html .= '<div id="' . SUMAC_ID_DIV_FATAL . '">' . $_SESSION[SUMAC_SESSION_FATAL_ERROR] . '</div>' . "\n";
	$html .= '<table border="0" rules="none" width="100%">' . "\n";
	$html .= '<tr><td class="sumac_status" align="center">' . $_SESSION[SUMAC_SESSION_REQUEST_ERROR] . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= '<br /><a href="sumac_leave.php?homephp=' . sumac_get_exiturl() . '">' . sumac_formatMessage($_SESSION[SUMAC_STR]["AE6"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . '</a>';
//	$html .= '<br /><a href="' . $_SESSION[SUMAC_SESSION_RETURN] . '#SUMACRETURN">' . sumac_formatMessage($_SESSION[SUMAC_STR]["AE6"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . '</a>';
//	$html .= '<br /><a href="javascript:history.go(-1)">' . 'Go back to previous page' . '</a>';

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	$html .= '</body></html>' . "\n";
	return $html;
}

function sumac_getSumacFooter()
{
	$color = 'gray';
	$bg = strtolower($_SESSION[SUMAC_SESSION_BODYCOLOUR]);
	if (($bg == 'gray') || ($bg == 'grey') || ($bg == '#808080')) $color = 'white';
	return '<table width="100%"><tr><td align="center" style="font-size:1.0em">' .
			'<a style="color:' . $color . '" href="' . SUMAC_INFO_FOOTER_SUMAC_LINK . '">' .
			SUMAC_INFO_FOOTER_SUMAC_TEXT . ' ' . SUMAC_CODE_VERSION . ' (' . SUMAC_CODE_DATE . ')' .
			'</a></td></tr></table>';
}

function sumac_getHTMLBodyForControlNavbar($navbarId,$loginPage,$basketHidden)
{
	$html = '<div id="' . $navbarId . '">' . "\n";
	$html .= sumac_getHTMLStatusLinks($loginPage,$basketHidden);
	$html .= sumac_getHTMLControlNavbarPackageLinks($navbarId);
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLStatusLinks($loginPage,$basketHidden)
{
	$hideLogin = ($loginPage) ? ' sumac_nodisplay' : '';
	$loginOrOut = (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS])) ? $_SESSION[SUMAC_STR]["AL8"] : $_SESSION[SUMAC_STR]["AL7"];
	$hrefLoginOrOut = (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))
							? ('sumac_redirect.php?package=&entry=' . SUMAC_FUNCTION_LOGOUT)
							: ('sumac_redirect.php?package=&entry=' . SUMAC_FUNCTION_LOGIN);
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') //no active package - current function has been completed (or non-package login)
	{
		$loginOrOut = $_SESSION[SUMAC_STR]["AL9"];
		$hrefLoginOrOut = 'sumac_leave.php';
	}
	//make sure that when the other navbar links are suppressed for courses, the logout option is suppressed too
	if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES) &&
		$_SESSION[SUMAC_SESSION_OMIT_COURSES_NAVBAR] &&
		isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))
			$hideLogin = ' sumac_nodisplay';

	$totalDollars = sumac_centsToPrintableDollars($_SESSION[SUMAC_SESSION_TOTAL_CENTS]);

	$html = ' <div class="sumac_navbar_small_links" style="float:left">' . "\n";

	$html .= '  <a class="sumac_navbar_user_status" title="' . $_SESSION[SUMAC_STR]["AT9"] . '">' . $_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] . '</a>' . "\n";
	$html .= '  <a class="sumac_navlink' . $hideLogin . '" href="' . $hrefLoginOrOut . '"'
				. ' title="' . $_SESSION[SUMAC_STR]["AT8"] . '">' . $loginOrOut . '</a>' . "\n";
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
//		$showHideBasketLabel = ($basketHidden) ? SUMAC_LABEL_SHOW_DETAIL : SUMAC_LABEL_HIDE_DETAIL;
		$html .= ' |';
		$html .= '  <a class="sumac_navbar_basket_status" title="' . $_SESSION[SUMAC_STR]["TT5"] . '">' . $_SESSION[SUMAC_STR]["TU23"] . $totalDollars . '</a>' . "\n";
//		$html .= '  <a id="sumac_basket_link" class="sumac_selected_navlink" href="#sumac_top_action_navbar" onclick="sumac_show_or_hide_basket()" title="' . SUMAC_TEXT_SHOW_OR_HIDE_BASKET . '">(' . $showHideBasketLabel . ')</a>' . "\n";
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		$html .= ' |';
		$html .= '  <a class="sumac_navbar_basket_status" title="' . $_SESSION[SUMAC_STR]["CT7"] . '">' . $_SESSION[SUMAC_STR]["CU25"] . $totalDollars . '</a>' . "\n";
	}
	$html .= ' </div>' . "\n";

	return $html;
}

function sumac_getHTMLControlNavbarPackageLinks($navbarId)
{
	$html = ' <div class="sumac_navbar_small_links" style="float:right">' . "\n";
	$notFirstLink = false;

	if ($_SESSION[SUMAC_SESSION_DONATION_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		if ($_SESSION[SUMAC_USERPAR_D2ASNAVLNK])
		{
			$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_DONATION2)
						? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_DONATION2 . '"'
						: 'class="sumac_selected_navlink"';
		}
		else
		{
			$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_DONATION)
						? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_DONATION . '"'
						: 'class="sumac_selected_navlink"';
		}
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT3"] . '">' . $_SESSION[SUMAC_STR]["AL6"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	if ($_SESSION[SUMAC_SESSION_TICKETING_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_TICKETING)
					? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_TICKETING . '"'
					: 'class="sumac_selected_navlink"';
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT5"] . '">' . $_SESSION[SUMAC_STR]["AL11"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	if ($_SESSION[SUMAC_SESSION_COURSES_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_COURSES)
					? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_COURSES . '"'
					: 'class="sumac_selected_navlink"';
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT2"] . '">' . $_SESSION[SUMAC_STR]["AL5"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	if ($_SESSION[SUMAC_SESSION_DIRECTORIES_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_DIRECTORIES)
					? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_DIRECTORIES . '"'
					: 'class="sumac_selected_navlink"';
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT12"] . '">' . $_SESSION[SUMAC_STR]["AL13"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	if ($_SESSION[SUMAC_SESSION_MEMBER_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_MEMBERSHIP)
					? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_MEMBERSHIP . '"'
					: 'class="sumac_selected_navlink"';
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT4"] . '">' . $_SESSION[SUMAC_STR]["AL10"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	if ($_SESSION[SUMAC_SESSION_CONTACT_LINK])
	{
		if ($notFirstLink) $html .= ' |';
		$navClassAndHref = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_CONTACT_UPDATE)
					? 'class="sumac_navlink" href="sumac_redirect.php?package=' . SUMAC_PACKAGE_CONTACT_UPDATE . '"'
					: 'class="sumac_selected_navlink"';
		$html .= '  <a ' . $navClassAndHref . ' title="' . $_SESSION[SUMAC_STR]["AT1"] . '">' . $_SESSION[SUMAC_STR]["AL4"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	$leaveLinkNeeded = false;
	$linkId = ($_SESSION[SUMAC_SESSION_LEAVE_LINK] ? "sumac_link_leave_" : "sumac_link_quit_HTTP_") . $navbarId;
	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $leaveLinkNeeded = true;
	}

	if ($leaveLinkNeeded || $_SESSION[SUMAC_SESSION_LEAVE_LINK])
	{
		if ($notFirstLink && $_SESSION[SUMAC_SESSION_LEAVE_LINK]) $html .= ' |';
//		$html .= '  <a id="' . $linkId . '" class="sumac_navlink" href="' . $_SESSION[SUMAC_SESSION_RETURN] . '#SUMACRETURN"'
		$html .= '  <a id="' . $linkId . '" class="sumac_navlink" href="sumac_leave.php"'
					. ' title="' . $_SESSION[SUMAC_STR]["AT6"] . $_SESSION[SUMAC_SESSION_ORGANISATION_NAME] . $_SESSION[SUMAC_STR]["AT7"] . '">'
					. $_SESSION[SUMAC_STR]["AL3"] . '</a>' . "\n";
		$notFirstLink = true;
	}

	$html .= ' </div>' . "\n";
	return $html;
}

function sumac_getSelectedSessionHTML($organisationDocument,$extrasDocument)
{
	$html = '<div class="sumac_showhide_div"><button class="sumac_basket_showhide" style="float:right"'
				. ' onclick="sumac_hide_table(this,\'' . SUMAC_ID_DIV_ORDERBASKET . '\',\'' . $_SESSION[SUMAC_STR]["CL27"]
				. '\',\'' . $_SESSION[SUMAC_STR]["CL26"] . '\',\'sumac_maintable\')">' . $_SESSION[SUMAC_STR]["CL26"] . '</button></div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_ORDERBASKET . '" class="sumac_maintable">' . "\n";
	$html .= '<table class="sumac_registration">' . "\n";

	$html .= sumac_getHTMLTableRowsForSession($organisationDocument);

	$html .= sumac_getHTMLTableRowsForExtras($extrasDocument);

	$html .= '</table>' . "\n";
	$html .= '</div><br />' . "\n";

	return $html;
}


function sumac_getHTMLTableRowsForSession($organisationDocument)
{
	//$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'] = $_POST['session'];

	$html = '<tr><td align="left"><b>' . $_SESSION[SUMAC_STR]["CU18"] . '</b></td></tr>' . "\n";
	$dateformat = $_SESSION[SUMAC_SESSION_CATALOG_DATE_DISPLAY_FORMAT];

	if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
	{
		$sessionId = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'];
		$sessionElement = $organisationDocument->getElementById($sessionId);
		$courseNode = $sessionElement->parentNode;
		$sessionName =  $courseNode->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$sessionDate = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_START_DATE);
		$sessionDuration = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_DURATION);
		$sessionDurationUnit = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_DURATION_UNIT);
		$html .= '<tr><td colspan="5" class="sumac_rs_course">' . $sessionName . ' from ' . sumac_formatDate($sessionDate,$dateformat)
					. ' for ' . $sessionDuration . ' ' . $sessionDurationUnit . '</td></tr>';

		$costs = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'];
		//$_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'] = $prices;
		$requirements = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'];

		foreach ($costs as $costId => $quantity)
		{
			if (($quantity == '') || ($quantity == 0)) continue;
			$costName = '';
			$costUnitPrice = 0;
			$costUnitOfMeasure = '';
			$costQty = '';
			if ($costId == $sessionId) //the session fee
			{
				$costName = $_SESSION[SUMAC_SESSION_DEFAULT_COURSE_FEE_NAME];
				$costUnitPrice = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_CENTS_FEE);
			}
			else
			{
				$sessionCostElement = $organisationDocument->getElementById($costId);
				$costName = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
				$costUnitPrice = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE);
				$costUOM = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_UNIT_OF_MEASURE);
				if (($costUOM != null) && ($costUOM != '') && ($costUOM != 'session')) $costUnitOfMeasure = ' per ' . $costUOM;
				if ($quantity != 1) $costQty = 'quantity ' . $quantity;
			}

			$html .= '<tr>'
					. '<td class="sumac_rs_name">' . $costName . '</td>'
					. '<td class="sumac_rs_price">' . sumac_centsToPrintableDollars($costUnitPrice) . $costUnitOfMeasure . '</td>'
					. '<td class="sumac_rs_quantity">' . $costQty . '</td>'
					. '<td class="sumac_rs_cost">' . sumac_centsToPrintableDollars($costUnitPrice * $quantity) . '</td>';

			$html .= '<td>';
			if (isset($requirements[$costId]))
			{
				$query = $requirements[$costId];
				foreach ($query as $queryId => $response)
				{
					$sessionQueryElement = $organisationDocument->getElementById($queryId);
					$prompt = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_TEXT);
					$html .= '<span class="sumac_rs_query" title="' . $prompt . '">  (' . $response . ')</span>';
				}
			}
			$html .= '</td>';
			$html .= '</tr>';
		}
	}
	return $html;
}

function sumac_getHTMLTableRowsForExtras($extrasDocument)
{
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
		$datacols = 2;
		$heading = $_SESSION[SUMAC_STR]["TU12"];
		if (sumac_countTicketOrdersInBasket() <= 0)
//note that the $extrasDocument will be null if the ticket count is zero
		{
			return '<tr><td colspan="' . $datacols . '" align="right"><b>' . $_SESSION[SUMAC_STR]["TU21"] . '</b></td><td align="right"><b>0.00</b></td></tr>' . "\n";
		}
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		$datacols = 3;
		$heading = $_SESSION[SUMAC_STR]["CU17"];
	 	if (!isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
//note that the $extrasDocument will be null if there has been no session selected yet
		{
			return '<tr><td colspan="' . $datacols . '" align="right"><b>' . $_SESSION[SUMAC_STR]["CU23"] . '</b></td><td align="right"><b>0.00</b></td></tr>' . "\n";
		}
	}
	else	//neither ticketing nor courses - why are we here?
	{
		return '';
	}
	$html = '<tr><td colspan="' . $datacols . '" align="center"><i>' . $heading . '</i></td></tr>' . "\n";
	$extracentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_EXTRA_CENTS);
	for ($i = 0; $i < $extracentsElements->length; $i++)
	{
		$extracentsElement = $extracentsElements->item($i);
		$extracents = ($extracentsElement->childNodes->item(0) != null) ? $extracentsElement->childNodes->item(0)->nodeValue : '0';
		$html .= '<tr><td colspan="' . $datacols . '" align="right">' . $extracentsElement->getAttribute(SUMAC_ATTRIBUTE_NAME) .
					' ' . $extracentsElement->getAttribute(SUMAC_ATTRIBUTE_EXPLANATION) . '</td>';
		$html .= '<td align="right">' . sumac_centsToPrintableDollars($extracents) . '</td></tr>';
	}
	$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
	$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	$totalDollars = sumac_centsToPrintableDollars($totalcents);
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
		$html .= '<tr><td colspan="' . $datacols . '" align="right"><b>' . $_SESSION[SUMAC_STR]["TU21"] . '</b></td><td align="right"><b>' . $totalDollars . '</b></td></tr>';
	}
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		$html .= '<tr><td colspan="' . $datacols . '" align="right"><b>' . $_SESSION[SUMAC_STR]["CU23"] . '</b></td><td align="right"><b>' . $totalDollars . '</b></td></tr>';
	}
	$minimumcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_MINIMUM_CENTS);
	if (($minimumcentsElements->item(0) != null) && ($minimumcentsElements->item(0)->childNodes->item(0) != null))
	{
		$minimumcents = $minimumcentsElements->item(0)->childNodes->item(0)->nodeValue;
		$minimumDollars = sumac_centsToPrintableDollars($minimumcents);
		$html .= '<tr><td colspan="' . ($datacols+1) . '" align="center"><i><b>' . $_SESSION[SUMAC_STR]["CU12"] . $minimumDollars . '</b></i></td></tr>';
	}
	$remarkElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_REMARK);
	if ($remarkElements->item(0)->childNodes->item(0) != null)
	{
		$html .= '<tr><td colspan="' . ($datacols+1) . '" align="center"><i><b>' . $remarkElements->item(0)->childNodes->item(0)->nodeValue . '</b></i></td></tr>';
	}
	return $html;
}

function sumac_addXMLregistrationData()
{
	$xml = '<sessionselection s="' . $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session'] . '">';
	$costs = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['costs'];
	$prices = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['prices'];
	$requirements = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['requirements'];
	foreach ($costs as $cid => $quantity)
	{
		if ($quantity > 0)
			$xml .= '<cost c="' . sumac_convertXMLSpecialChars($cid)
					. '" q="' .sumac_convertXMLSpecialChars( $quantity)
					. '" u="' . sumac_convertXMLSpecialChars($prices[$cid]) . '">'
					. '</cost>';
	}
	foreach ($requirements as $cid => $req)
	{
		foreach ($req as $pid => $text)
		{
			if (isset($costs[$cid]) && ($costs[$cid] > 0))
				$xml .= '<query c="' . sumac_convertXMLSpecialChars($cid)
						. '" p="' . sumac_convertXMLSpecialChars($pid)
						. '" t="' . sumac_convertXMLSpecialChars($text) . '">'
						. '</query>';
		}
	}
	$xml .= '</sessionselection>';
	return $xml;
}

function sumac_getHTMLBodyForCoursesActionsNavbar($navbarId,$activeFunction)
{
	if ($_SESSION[SUMAC_SESSION_OMIT_COURSES_NAVBAR]) return '';
	$html = '<div id="' . $navbarId . '">' . "\n";
	$html .= sumac_getHTMLCoursesNavbarActionLinks($navbarId,$activeFunction);
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLCoursesNavbarActionLinks($navbarId,$activeFunction)
{
	$html = ' <div class="sumac_navbar_small_links" style="float:right">' . "\n";

	if (!$_SESSION[SUMAC_SESSION_OMIT_FINANCIAL_HISTORY])
	{
		$html .= sumac_getHTMLCoursesActionPersonalLink('personal','finhistory',$activeFunction,
							$_SESSION[SUMAC_STR]["CT4"],$_SESSION[SUMAC_STR]["CL19"],true,
							$_SESSION[SUMAC_STR]["CL22"]) . ' |' . "\n";

	}
	if (!$_SESSION[SUMAC_SESSION_OMIT_PERSONAL_HISTORY])
	{
		$html .= sumac_getHTMLCoursesActionPersonalLink('personal','eduhistory',$activeFunction,
							$_SESSION[SUMAC_STR]["CT3"],$_SESSION[SUMAC_STR]["CL18"],true,
							$_SESSION[SUMAC_STR]["CL23"]) . ' |' . "\n";

	}
	if (!$_SESSION[SUMAC_SESSION_OMIT_FORMS_SUMMARY])
	{
		$html .= sumac_getHTMLCoursesActionPersonalLink('personal','formslist',$activeFunction,
							$_SESSION[SUMAC_STR]["CT5"],$_SESSION[SUMAC_STR]["CL17"],true,
							$_SESSION[SUMAC_STR]["CL24"]) . ' |' . "\n";
	}

	$html .= sumac_getHTMLCoursesActionLink('payment',null,$activeFunction,
							$_SESSION[SUMAC_STR]["CT2"],$_SESSION[SUMAC_STR]["CL16"],null,true) . ' |' . "\n";


	if ($_SESSION[SUMAC_SESSION_COURSES_NO_CATALOG])
	{
		if ($_SESSION[SUMAC_SESSION_CATALOGURL] != '')
		{
			$html .=  '  <a class="sumac_navlink  sumac_action_link_catalog"' . ' href="' . $_SESSION[SUMAC_SESSION_CATALOGURL] . '"'
				. ' title="' . $_SESSION[SUMAC_STR]["CT1"] . '">' . $_SESSION[SUMAC_STR]["CL14"] . '</a>' . ' |' . "\n";
		}

		$html .= sumac_getHTMLCoursesActionLink('register',null,$activeFunction,
							$_SESSION[SUMAC_STR]["CT8"],$_SESSION[SUMAC_STR]["CL29"],null,false) . "\n";
	}
	else
	{
		$html .= sumac_getHTMLCoursesActionLink('catalog',null,$activeFunction,
							$_SESSION[SUMAC_STR]["CT1"],$_SESSION[SUMAC_STR]["CL14"],null,false) . "\n";
	}

	$html .= ' </div>' . "\n";
	return $html;
}

function sumac_getHTMLCoursesActionPersonalLink($action,$div,$activeFunction,$titletext,$linktext,$notWithLogin,$buttontext)
{
	$onclick = '';
	if ($activeFunction == 'personal')
	{
		$onclick = ' href="#sumac_div_' . $div . '" onclick="sumac_unhide_table(document.getElementById(\'sumac_button_' . $div
				. '\'),\'sumac_div_' . $div . '\',\'Show ' . $buttontext . '\',\'Hide ' . $buttontext
				. '\',\'sumac_maintable\');"';
	}
	return sumac_getHTMLCoursesActionLink($action,$div,$activeFunction,$titletext,$linktext,$onclick,$notWithLogin);
}

function sumac_getHTMLCoursesActionLink($action,$div,$activeFunction,$titletext,$linktext,$onclick,$notWithLogin)
{
	$hrefvalue = 'sumac_courses_redirect.php?function=' . $action;
	if ($div != null) $hrefvalue .= '&div=' . $div;
	$navlinkclass = ($action == $activeFunction) ? 'sumac_selected_navlink' : 'sumac_navlink';
	if ($notWithLogin && ($activeFunction == 'login')) $navlinkclass = 'sumac_disabled_navlink';
	$navclass = $navlinkclass . ' sumac_action_link_' . $action;
	return '  <a class="' . $navclass . '"' . (($action != $activeFunction) ? (' href="' . $hrefvalue . '"') : '')
			. (($onclick != null) ? $onclick : '') . ' title="' . $titletext . '">' . $linktext . '</a>';
}

function sumac_convertXMLSpecialChars($attributevalue)
{
//	return urlencode($attributevalue);
	$search = array('&','"','<','>');
	$replace = array('&amp;','&quot;','&lt;','&gt;');
	return str_replace($search,$replace,$attributevalue);
}

function sumac_get_exiturl()
{
	$exiturl = $_SESSION[SUMAC_SESSION_RETURN];
	if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != '') && (isset($_SESSION[SUMAC_USERPAR_EXITURL][$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE]])))
		$exiturl = $_SESSION[SUMAC_USERPAR_EXITURL][$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE]];
	else if (isset($_SESSION[SUMAC_USERPAR_EXITURL]['anypackage']))
		$exiturl = $_SESSION[SUMAC_USERPAR_EXITURL]['anypackage'];
	return $exiturl;
}

function sumac_showSessionVariables($prefix)
{
	$html = '';
	$prefixlen = strlen($prefix);
	$v = array();
	foreach($_SESSION as $x => $y)
		if(substr($x,0,$prefixlen) == $prefix) $v[] = $x;

	if (count($v) == 0)
	{
		$html .= 'There is no sumac PHP session<br />';
	}
	else
	{
		if (($_SERVER['HTTP_HOST'] != '127.0.0.1') && ($_SERVER['HTTP_HOST'] != 'localhost')
			&& ($_SERVER['HTTP_HOST'] != 'sumac.com'))
		{
			$html .= 'There is an active sumac PHP session on ' . $_SERVER['HTTP_HOST'] . '<br />';
		}
		else
		{
			$html .= 'These are the ' . $prefix . ' $_SESSION variables now';
			$html .= ' ... there are ' . count($v) . '.<br /><br />';
			sort($v);
			foreach ($v as $x)
			{
				if ($_SESSION[$x] instanceof DOMDocument) $html .= $x . ' is a DOMDocument object<br />';
				else if (is_array($_SESSION[$x]))
				{
					foreach($_SESSION[$x] as $p => $q) $html .= $x . '[' . $p . ']="' . $q . '"<br />';
				}
				else $html .= $x . '="' . $_SESSION[$x] . '"<br />';
			}
/*
			$html .= '<br />' . '... and these are the $_POST variables now';
			$html .= ' ... there are ' . count($_POST) . '.<br /><br />';
			foreach ($_POST as $x => $y)
			{
				$html .= '$_POST[' . $x . ']="' . $y . '"<br />';
			}
			$html .= '<br />' . '... and these are the $_GET variables now';
			$html .= ' ... there are ' . count($_GET) . '.<br /><br />';
			foreach ($_GET as $x => $y)
			{
				$html .= '$_GET[' . $x . ']="' . $y . '"<br />';
			}
*/
		}
	}
	return $html;
}

?>