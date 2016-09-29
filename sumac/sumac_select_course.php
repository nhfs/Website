<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execSelectCourse($courseChosenPHP,$group,$editid)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;
	$catalogElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_COURSE_CATALOG)->item(0);

	if ($catalogElement == false)
	{
		$_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES] = array();
		$_SESSION[SUMAC_SESSION_COURSE_GROUPINGS] = array();
		$_SESSION[SUMAC_SESSION_COURSE_COUNT] = 0;
		$_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT] = 0;
		$html = sumac_getNoCoursesToRegisterForHTML($organisationDocument);
		echo $html;
		return true;
	}
	$_SESSION[SUMAC_SESSION_CATALOG_NAME] = $catalogElement->getAttribute(SUMAC_ATTRIBUTE_NAME);

	if (!isset($_SESSION[SUMAC_SESSION_COURSE_GROUPINGS]))
	{
		sumac_setCourseGroupings($organisationDocument);
		$courseElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_COURSE);
		if ($courseElements->length < 1)
		{
			$_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES] = array();
			$_SESSION[SUMAC_SESSION_COURSE_GROUPINGS] = array();
			$_SESSION[SUMAC_SESSION_COURSE_COUNT] = 0;
			$_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT] = 0;
			$html = sumac_getNoCoursesToRegisterForHTML($organisationDocument);
			echo $html;
			return true;
		}
		$_SESSION[SUMAC_SESSION_COURSE_COUNT] = $courseElements->length;
		$_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT] = count($_SESSION[SUMAC_SESSION_COURSE_GROUPINGS]);
	}

	$prechosen = null;
	if ($editid != null)
	{
		$prechosen = $editid;
	}
	else
	{
		$selectedSessionElements = $catalogElement->getElementsByTagName(SUMAC_ELEMENT_SELECTED_SESSION);
		if ($selectedSessionElements->length > 0)
		{
			$prechosen = $selectedSessionElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_ID);
		}
	}

	$html = sumac_getCourseSelectionHTML($organisationDocument,$courseChosenPHP,$group,$prechosen);
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

function sumac_setCourseGroupings($organisationDocument)
{
	$_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES] = array();
	$_SESSION[SUMAC_SESSION_COURSE_GROUPINGS] = array();
//	$_SESSION[SUMAC_SESSION_COURSE_DETAIL_SHOWING] = array();
	$_SESSION[SUMAC_SESSION_ACTIVE_COURSE_GROUPING] = 0;

	$groupingElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_COURSE_GROUPING);
	$_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES][0] = $_SESSION[SUMAC_STR]["CU2"];
	$_SESSION[SUMAC_SESSION_COURSE_GROUPINGS][0] = '0';
	for ($i = 0; $i < $groupingElements->length; $i++)
	{
		$groupingElement = $groupingElements->item($i);
		$_SESSION[SUMAC_SESSION_COURSE_GROUPINGS][] = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES][] = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	}
//we now have the list of group/category names to use in a dropdown
//and a similarly ordered list of the matching group/category IDs to associate with courses

}

function sumac_getNoCoursesToRegisterForHTML($organisationDocument)
{
	$html = sumac_getHTMLHeadForCourseSelection(0);
	$html .= '<body>' . "\n";
	$html .= sumac_addParsedXmlIfDebugging($organisationDocument,'coursecatalog');
	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectcourse') . sumac_getSubtitle();
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','catalog');

	$html .= '<table class="sumac_function_impossible">' . "\n";
	$html .= '<tr><td class="sumac_status">' . $_SESSION[SUMAC_SESSION_CATALOG_MISSING] . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','catalog');
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_COURSES) . sumac_getUserHTML(SUMAC_USER_BOTTOM);
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

function sumac_getCourseSelectionHTML($organisationDocument,$courseChosenPHP,$group,$prechosen)
{
	$html = sumac_getHTMLHeadForCourseSelection($group);

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($organisationDocument,'coursecatalog');

	$catalogElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_COURSE_CATALOG)->item(0);
	$subtitle= $catalogElement->getAttribute(SUMAC_ATTRIBUTE_TITLE);

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectcourse') . sumac_getSubtitle($subtitle);

	if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
	{
		$extrasDocument = sumac_reloadExtrasDocument();
		if ($extrasDocument != false)
		{
			$html .= sumac_addParsedXmlIfDebugging($extrasDocument,'extras');
			$html .= sumac_getSelectedSessionHTML($organisationDocument,$extrasDocument);
		}
	}

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$activeFunction = ($prechosen == null) ? 'catalog' : 'register';
	$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar',$activeFunction);

	$HTMLBodyForSelect = sumac_getHTMLBodyForSelect($organisationDocument,$courseChosenPHP,$group,$prechosen);

	if ($HTMLBodyForSelect !== false)
	{

		$html .= $HTMLBodyForSelect;

		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar',$activeFunction);

		$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

		$html .= sumac_getSumacFooter(SUMAC_PACKAGE_COURSES) . sumac_getUserHTML(SUMAC_USER_BOTTOM);

		if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
		{
			$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
			if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
			$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
		}

		$html .= '<script type="text/javascript">';
		$html .= 'sumac_sort_list(null,' . $_SESSION[SUMAC_SESSION_ACTIVE_SORT_COLUMN] . ');';
//		$html .= 'document.getElementById("sumac_course_list_titles").scrollIntoView();';
		$html .= '</script>' . "\n";

//		$html .= sumac_showSessionVariables('sumac_');

		$html .= '</body>' . "\n";
		$html .= '</html>' . "\n";

		return $html;
	}
	else
	{
		return false;
	}
}

function sumac_getHTMLHeadForCourseSelection($group)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getCommonCourseCatalogHTMLStyleElements();
	$html .= sumac_getCourseSelectionHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();

	$html .= '</style>' . "\n";

	$html .= '<script type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCourseSelectionHTMLScriptVariables($group);
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getCourseSelectionHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";
	return $html;
}

function sumac_getCourseSelectionHTMLStyleElements()
{
//assign values to more manageable variables (the defined constants do not work in the 'heredoc' anyway)
	$topnavbg70grey = $_SESSION[SUMAC_SESSION_NAVBGFOLDER] . 'topnavbg70grey.' . $_SESSION[SUMAC_SESSION_NAVBGFILEXT];

	return <<<EOCSS

/* the bar containing a dropdown choice of groups/categories of courses */
#sumac_group_selection_bar
{
	height:40px;background-image:url("$topnavbg70grey");
	background-position:top;background-repeat:repeat-x;
}
.sumac_group_selection_dropdown
{
	font-size:120%;text-align:center;padding:5px 0px 5px 0px;
}

/* the table that form the course list */
#sumac_course_list_table
{
	margin-left:auto;margin-right:auto;
}
table.sumac_cl td
{
	vertical-align:top; border-bottom-style:solid; border-bottom-width:thin;
}
thead.sumac_cl
{
	text-align:left;
}
th.sumac_cl_title_sort
{
	padding:15px 25px 15px 0px;
}

/* make certain objects stack up vertically when otherwise they would spread across the page */
span.sumac_stacked_buttons
{
	display:block;
}
a.sumac_cl_instructor
{
	display:block;
	font-style:italic;
	padding:0 0 5px;
}
span.sumac_cl_instructor
{
	display:block;
}
input[type="number"]
{
	max-width:40px;
}
EOCSS;
}

function sumac_getCourseSelectionHTMLScriptVariables($group)
{
	$html = '';
	$html .= 'var sumac_grouping_count = ' . $_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT] . ';' . "\n";
	$html .= 'var sumac_active_grouping_index = ' . $group . ';' . "\n";
	$html .= 'var sumac_active_sort_column = -1;' . "\n";
//	$html .= 'var sumac_active_sort_dirs = ["a","a","a","a"];' . "\n";
	$html .= 'var sumac_active_sort_dirs = ["a","a","a","a","a"];' . "\n";
	$html .= 'sumac_active_sort_dirs[' . $_SESSION[SUMAC_SESSION_ACTIVE_SORT_COLUMN] . '] = "' . $_SESSION[SUMAC_SESSION_ACTIVE_SORT_DIRECTION] . '";' . "\n";
	$html .= 'var sumac_id_registration_options = "' . SUMAC_ID_REGISTRATION_OPTIONS . '";' . "\n";
	$html .= 'var sumac_id_registration_defaults = "' . SUMAC_ID_REGISTRATION_DEFAULTS . '";' . "\n";
	return $html;
}

function sumac_getCourseSelectionHTMLScriptFunctions()
{
	return <<<EOJS

//does not block the load event
	function sumac_createIframe(session,iftype,ifid,filename)
	{
		var tdid = "sumac_cl_cdetail_" + session;
		var td = document.getElementById(tdid);
		var iframe = document.createElement("iframe");
		iframe.id = ifid;
		iframe.src = filename;
		iframe.scrolling = "auto";
		iframe.frameborder = "0";
		iframe.className = "sumac_" + iftype + "_detail"; // sumac_nodisplay
		td.appendChild(iframe);
	};

	function sumac_unhide_detail(session,iftype,instructor,filename)
	{
		var panel = document.getElementById("sumac_cl_trb_" + session);
		panel.className = "sumac_detailpanel"; //that gets rid of the class suppressing display

		var ifid = "sumac_no_iframe";
		var ifclass = "sumac_no_detail";
		if (iftype != '')
		{
			ifid = "sumac_if_" + iftype + instructor + '_' + session;
			var ifelement = document.getElementById(ifid);
			if (ifelement == null) sumac_createIframe(session,iftype,ifid,filename);
			ifclass = "sumac_" + iftype + "_detail";
		}
		var iframes = panel.getElementsByTagName('IFRAME');
		for (var i = 0; i < iframes.length; i++)
		{
			var e = iframes[i];
			var isVisible = (e.className.indexOf(' sumac_nodisplay') < 0);
			if (isVisible && (e.id != ifid)) e.className = e.className + ' sumac_nodisplay';
			else if ((!isVisible) && (e.id == ifid)) e.className = ifclass;
		}
//and hide the registration form (and any other tables) if necessary
		var tables = panel.getElementsByTagName('TABLE');
		for (var i = 0; i < tables.length; i++)
		{
			var e = tables[i];
			if (e.className.indexOf('sumac_nodisplay') < 0) e.className = 'sumac_nodisplay';
		}
		//event.preventDefault();	//do NOT go off to href link

//@@@? mark javascript array that can be loaded as href parameter when leaving
	}

	function sumac_unhide_registration_panel(session)
	{
		var panel = document.getElementById("sumac_cl_trb_" + session);
		panel.className = "sumac_detailpanel"; //that gets rid of the class suppressing display of everything
//now make the registration panel visible and hide any iframes
		var regpanel = document.getElementById("sumac_cl_registration_" + session);
		if (regpanel.className.indexOf('sumac_nodisplay') >= 0) regpanel.className = "sumac_course_registration";
		var iframes = panel.getElementsByTagName('IFRAME');
		for (var i = 0; i < iframes.length; i++)
		{
			var e = iframes[i];
			var isVisible = (e.className.indexOf(' sumac_nodisplay') < 0);
			if (isVisible) e.className = e.className + ' sumac_nodisplay';
		}
	}

	function sumac_hide_detail(session)
	{
		var panel = document.getElementById("sumac_cl_trb_" + session);
		panel.className = "sumac_detailpanel sumac_nodisplay";
		var tds = panel.getElementsByTagName('TD');
		for (var i = 0; i < tds.length; i++)
		{
			var td = tds[i];
			var iframes = td.getElementsByTagName('IFRAME');
			for (var j = (iframes.length - 1); j >= 0; j--) td.removeChild(iframes[j]);
		}
	}

//the 'payable' column starts by showing a non-bold $0.00 for any non-selected optional fees or extras,
//but a bold value for any selected or required ones
//a 'checkbox' item (one per session) is switched to its bold value when it is selected
//an item with a non-session multiplier is set to its calculated value
//the payable total is always recalculated

	function sumac_calculate_payable(e)
	{
		var mult = 0;
		if (e.parentNode.className.indexOf('sumac_cc_multiplier') == 0) mult = Number(e.value);
		else if (e.parentNode.className.indexOf('sumac_cc_selector') == 0) mult = (e.checked) ? 1 : 0;
		var tr = e.parentNode.parentNode;
		if (tr.tagName == 'TR')
		{
			var tds = tr.getElementsByTagName('TD');
			var tdPayable = null;
			var tdPayableCents = null;
			var cents = 0;
			for (var i =0; i < tds.length; i++)
			{
				var td = tds[i];
				if (td.className.indexOf('sumac_cc_payable') == 0) tdPayable = td;
				else if (td.className.indexOf('sumac_cc_paycents') == 0) tdPayableCents = td;
				else if (td.className.indexOf('sumac_cc_cents') == 0) cents = td.innerHTML;
			}
			var newcents = mult * cents;
			if (tdPayable != null)
			{
				tdPayable.innerHTML = sumac_centsToPrintableDollars(newcents);
				tdPayable.className = (newcents == 0) ? 'sumac_cc_payable_nonbold' : 'sumac_cc_payable_bold';
				if (tdPayableCents != null) tdPayableCents.innerHTML = newcents;
				var total = Number(0);
				var tdTotalPayable = null;
				var tbody = tr.parentNode;
				var tds = tbody.getElementsByTagName('TD');
				for (var i =0; i < tds.length; i++)
				{
					td = tds[i];
					if (td.className.indexOf('sumac_cc_paycents') == 0) total = total + Number(td.innerHTML);
					else if (td.className.indexOf('sumac_cc_paytotal') == 0) tdTotalPayable = td;
				}
				if (tdTotalPayable != null) tdTotalPayable.innerHTML = '= ' + sumac_centsToPrintableDollars(total);
			}
		}
	}

	function sumac_change_selected_group()
	{
		var selectedIndex = document.getElementById('sumac_course_grouping_options').selectedIndex;
		if (sumac_active_grouping_index == selectedIndex) return;
		var table = document.getElementById('sumac_course_list_table');
		var rows = table.getElementsByTagName('TR');
		var visiblerows = 0;
		for (var i = 0; i < rows.length; i++)
		{
			var row = rows[i];
			var id = row.id;
			if (id.indexOf('sumac_cl_tra_') != 0) continue;
			var tds = row.cells;
			var groupingsForThisRow = tds[tds.length-1].innerHTML; //a string of ones and zeroes
			if (groupingsForThisRow.substr(selectedIndex,1) == '1')
			{
				row.className = 'sumac_cl';
				visiblerows++;
			}
			else
			{
				row.className = 'sumac_cl sumac_nodisplay';
				sumac_hide_detail(sumac_get_id_sortkey(id,0));
			}
		}
		sumac_active_grouping_index = selectedIndex;
		if (visiblerows >0)
		{
			document.getElementById('sumac_course_list_titles').className = 'sumac_cl_titles';
			document.getElementById('sumac_course_list_empty').className = 'sumac_cl_titles sumac_nodisplay';
		}
		else
		{
			document.getElementById('sumac_course_list_titles').className = 'sumac_cl_titles sumac_nodisplay';
			document.getElementById('sumac_course_list_empty').className = 'sumac_cl_titles';
		}
	}

	function sumac_sort_list(event,column)
	{
		var ncol = Number(column);
		var dir = sumac_active_sort_dirs[ncol];
		if (ncol == sumac_active_sort_column) dir = (dir == 'a') ? 'd' : 'a';
		var table = document.getElementById('sumac_course_list_table');
		if (table == null) return;	//table isn't always created
		var tbody = table.getElementsByTagName('TBODY')[0];
		var rows = tbody.getElementsByTagName('TR');
		var ids = new Array();
		for (var i = 0; i < rows.length; i++)
		{
			var row = rows[i];
			var id = row.id;
			if (id.indexOf('sumac_cl_tra_') != 0) continue;
			var spot = Number(sumac_get_id_sortkey(id,ncol));
			ids[spot] = id;
		}
		var newtbody = document.createElement("tbody");
		for (var i = 0; i < ids.length; i++)
		{
			var raid = (dir == 'a') ? ids[i] : ids[(ids.length - 1) - i];
			var ra = document.getElementById(raid);
			var rbid = 'sumac_cl_trb_' + sumac_get_id_sortkey(raid,0);
			var rb = document.getElementById(rbid);
			newtbody.appendChild(tbody.removeChild(ra));
			newtbody.appendChild(tbody.removeChild(rb));
		}
		table.removeChild(tbody);
		table.appendChild(newtbody);
		sumac_active_sort_column = ncol;
		sumac_active_sort_dirs[ncol] = dir;
		if (event != null) event.preventDefault();	//do NOT go off to href link
	}

	function sumac_get_id_sortkey(id,col)
	{
		var kp = 'sumac_cl_tra_'.length;
//		for (var i = 0; i < 4; i++)
		for (var i = 0; i < 5; i++)
		{
			var nextus = id.indexOf('_',kp);
			if (nextus < 0) nextus = id.length;
//alert('id=' + id + ', kp=' + kp + ', nextus=' + nextus +  ', key=' + id.substring(kp,nextus + '.'));
			if (col == i) return id.substring(kp,nextus);
			kp = nextus + 1;
		}
		return '?';
	}

	function sumac_choose_options()
	{
		var options_id = sumac_id_registration_options + arguments[0];
		var options = document.getElementById(options_id).getAttribute('value');
		for (var i = 1; i < arguments.length; i += 6) //id,text,ignore,min,max,pa for each query
		{
			var uqid = arguments[i];
			//var def = arguments[i+2];
			var def = '';
			var uqidsemi = uqid + ';';
			var uql = uqidsemi.length;
			var opti = options.indexOf(uqidsemi);
			if (opti >= 0)
			{
				optend = options.indexOf(';',opti+uql);
				if (optend < 0) optend = options.length;
				def = options.substring(opti+uql,optend);
			}

			var text = arguments[i+1];
			var min = arguments[i+3];
			var max = arguments[i+4];
			var ok = false;
			while (!ok)
			{
				var choice = prompt(text,def);
				if (choice == null)	//user cancelled query
				{
					ok = true;	//option value not changed
				}
				else if ((min != '') && (choice < min))
				{
					text = 'Too small! ' + arguments[i];
				}
				else if ((max != '') && (choice > max))
				{
					text = 'Too big! ' + arguments[i];
				}
				else
				{
					ok = true;
					choice = choice.replace(';',',');
					if ((opti >= 0) && (optend >= 0))
					{
						options = options.substr(0,opti+uql) + choice + options.substr(optend);
					}
				}
			}
		}
		document.getElementById(options_id).setAttribute('value',options);
	}

	function sumac_reset_all(session)
	{
		var regtable = document.getElementById("sumac_cl_registration_" + session);
		if (!regtable) regtable = document.getElementById("sumac_cl_registration_only");

		var inputs = regtable.getElementsByTagName('INPUT');
		for (var i = 0; i < inputs.length; i++)
		{
			var e = inputs[i];
			if (e.type == "checkbox")
			{
				e.checked = false;
				sumac_calculate_payable(e);
			}
			else if (e.type == "number")
			{
				e.value = 0;
				sumac_calculate_payable(e);
			}
		}
		document.getElementById("sumac_registration_options_" + session).value =
							document.getElementById("sumac_registration_defaults_" + session).value;
		//alert("default queries restored");
	}
EOJS;
}

function sumac_getHTMLBodyForSelect($organisationDocument,$courseChosenPHP,$group,$prechosen)
{
	$html = '<div id="' . SUMAC_ID_DIV_SELECT . '" class="sumac_maintable">' . "\n";
	$html .= '<table class="sumac_instructions">' . "\n";
	$html .= '<tr><td class="sumac_instructions">' . "\n";
	$registerButtonLabel = (($prechosen == null) ? $_SESSION[SUMAC_STR]["CL10"] : $_SESSION[SUMAC_STR]["CL28"]);
	$html .= (($prechosen == null)
				? sumac_formatMessage($_SESSION[SUMAC_STR]["CI2"],$registerButtonLabel)
				: sumac_formatMessage($_SESSION[SUMAC_STR]["CI11"],$registerButtonLabel));
	$html .= '</td></tr>';
	$html .= '</table>' . "\n";

	if ($prechosen == null) $html .= sumac_getHTMLGroupSelectionBar($group);

	$HTMLBodyForCourseList = sumac_getHTMLBodyForCourseList($organisationDocument,$courseChosenPHP,$group,$prechosen);
	if ($HTMLBodyForCourseList !== false)
	{
		$html .= $HTMLBodyForCourseList . '</div>' . "\n";
		return $html;
	}
	else
	{
		return false;
	}
}

function sumac_getHTMLGroupSelectionBar($group)
{
	$html = '<div id="sumac_group_selection_bar">' . "\n";
	$html .= ' <div class="sumac_group_selection_dropdown">' . "\n";
	$html .= '  <label for="sumac_course_grouping_options">' .
					$_SESSION[SUMAC_STR]["CU3"] . '</label>' . "\n";
	$html .= '  <select id="sumac_course_grouping_options"' .
					' name="grouping" onchange="sumac_change_selected_group()">' . "\n";
	$html .= sumac_getHTMLFormOptionsForValueArrayUsingIndex($_SESSION[SUMAC_SESSION_COURSE_GROUPING_NAMES],false,$group);
	$html .= '  </select>' . "\n";
	$html .= ' </div>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLBodyForCourseList($organisationDocument,$courseChosenPHP,$group,$prechosen)
{
	$html = '';
//begin by extracting the session_cost data and the requirement queries
	$sessionCostElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_SESSION_COST);
	$costs = array();
	for ($i = 0; $i < $sessionCostElements->length; $i++)
	{
		$sessionCostElement = $sessionCostElements->item($i);
		$id = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$cost = array();
		$cost['name'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$cost['cents'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE);
		$cost['uom'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_UNIT_OF_MEASURE);
		$cost['detail'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_DETAIL);
		$cost['queries'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_QUERIES);
		$cost['label'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_QUERY_LABEL);
		$cost['pp'] = $sessionCostElement->getAttribute(SUMAC_ATTRIBUTE_PER_PERSON);
		$costs[$id] = $cost;
	}

	$sessionQueryElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_REQUIREMENT_QUERY);
	$queries = array();
	for ($i = 0; $i < $sessionQueryElements->length; $i++)
	{
		$sessionQueryElement = $sessionQueryElements->item($i);
		$id = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$query = array();
		$query['text'] = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_TEXT);
		$query['def'] = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_DEFAULT);
		$query['min'] = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_MIN);
		$query['max'] = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_MAX);
		$query['pa'] = $sessionQueryElement->getAttribute(SUMAC_ATTRIBUTE_PER_ARTICLE);
		$queries[$id] = $query;
	}

//continue by extracting the course+session data into parallel arrays, one element per session
//and create keyed arrays to be sorted
	$courseElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_COURSE);
	for ($i = 0; $i < $courseElements->length; $i++)
	{
		$courseElement = $courseElements->item($i);
		$sessionElements = $courseElement->getElementsByTagName(SUMAC_ELEMENT_SESSION);
		for ($j = 0; $j < $sessionElements->length; $j++)
		{
			$sessionElement = $sessionElements->item($j);
			$courseId = $courseElement->getAttribute(SUMAC_ATTRIBUTE_ID);
			$cids[] = $courseId;
			$sessionId = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_ID);
			$ids[] = $sessionId;
			$sessionName = $courseElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$names[] = $sessionName;
			$sessionDate = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_START_DATE);
			$dates[] = $sessionDate;
			$sessionDuration = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_DURATION);
			$durations[] = $sessionDuration;
			$durationUnits[] = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_DURATION_UNIT);
			$fees[] = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_CENTS_FEE);
			$underInstructor = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_UNDER_INSTRUCTOR);
			$instIds = array_values(array_filter(explode(' ',$underInstructor)));
			$instNames = array();
			$instLinks = array();
			for ($k = 0; $k < count($instIds); $k++)
			{
				$instructorElement = $organisationDocument->getElementById($instIds[$k]);
				$instNames[] = $instructorElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
				$instLinks[] = $instructorElement->getAttribute(SUMAC_ATTRIBUTE_DETAIL);
			}
			if (count($instNames) < 1)
			{
				$instNames[] = $_SESSION[SUMAC_STR]["CU1"];
				$instLinks[] = '';
			}
			$instructors[] = $instNames;
			$instrDetails[] = $instLinks;
			$courseDetails[] = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_DETAIL);

			$courseGroupings = $courseElement->getAttribute(SUMAC_ATTRIBUTE_COURSE_GROUPINGS);
			$groupIds = array_values(array_filter(explode(' ',$courseGroupings)));
			$groupFlags = array();
			$groupFlags[0] = '1'; //all courses are part of group-0
			for ($kk = 1; $kk < $_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT]; $kk++) $groupFlags[$kk] = '0';
			for ($k = 0; $k < count($groupIds); $k++)
			{
				for ($kk = 1; $kk < $_SESSION[SUMAC_SESSION_COURSE_GROUP_COUNT]; $kk++)
				{
					if ($_SESSION[SUMAC_SESSION_COURSE_GROUPINGS][$kk] == $groupIds[$k])
					{
						$groupFlags[$kk] = '1';
						break;
					}
				}
			}
			$groupings[] = implode('',$groupFlags); //convert the array of ones and zeroes to a string

			$optcostIds = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_OPTIONAL_COSTS);
			$optionalCosts[] = array_values(array_filter(explode(' ',$optcostIds)));
			$reqcostIds = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_REQUIRED_COSTS);
			$requiredCosts[] = array_values(array_filter(explode(' ',$reqcostIds)));

			$bookingStatuses[] = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_BOOKING_STATUS);
			$bookingMessages[] = $sessionElement->getAttribute(SUMAC_ATTRIBUTE_BOOKING_MESSAGE);

			$namekeys[] = $sessionName . $sessionId;
			$datekeys[] = $sessionDate . $sessionId;
			$durationkeys[] = (10000 + $sessionDuration) . $sessionDate . $sessionId;
			$instructorkeys[] = implode('',$instNames) . $sessionDate . $sessionId;
		}
	}
//sort key arrays
	sort($namekeys,SORT_REGULAR);
	sort($datekeys,SORT_REGULAR);
	sort($durationkeys,SORT_REGULAR);
	sort($instructorkeys,SORT_REGULAR);

//foreach ($namekeys as $name => $index)
//{
//	echo $name . '=' . $index . '<br />';
//}

	$dateformat = $_SESSION[SUMAC_SESSION_CATALOG_DATE_DISPLAY_FORMAT];
	if ($prechosen == null)
	{
//create a table of course/session rows
		$html .= sumac_getHTMLCourseListTableHead();
		$html .= '   <tbody>' . "\n";
		for ($i = 0; $i < count($ids); $i++)
		{
			$id = $ids[$i];
			$name = $names[$i];
			$date = $dates[$i];
			$duration = $durations[$i];
			$instructornames = implode('',$instructors[$i]);
	//string the four sort keys indices togther to be used as an ID
			$namepos = array_search($name . $id,$namekeys);
			$datepos = array_search($date . $id,$datekeys);
			$durationpos = array_search((10000 + $duration) . $date . $id,$durationkeys);
			$instructorpos = array_search($instructornames . $date . $id,$instructorkeys);
//			$idaskeys = $namepos . '_' . $datepos . '_' . $durationpos;
			$idaskeys = $namepos . '_' . $datepos . '_' . $durationpos . '_' . $instructorpos;
	//add an entry to the list
			$html .= sumac_getHTMLForOneSessionInCourseList($i,$id,$idaskeys,$courseChosenPHP,
							$groupings[$i],$name,sumac_formatDate($date,$dateformat),$duration,$durationUnits[$i],
							$fees[$i],$instructors[$i],$courseDetails[$i],$instrDetails[$i],
							$queries,$costs,$requiredCosts[$i],$optionalCosts[$i],
							$bookingStatuses[$i],$bookingMessages[$i]);
		}
		$html .= '   </tbody></table>' . "\n";
		$html .= ' </div>' . "\n";
		return $html;
	}
//
//
	else
	{
		$index = -1;
		for ($i = 0; $i < count($ids); $i++) if ($ids[$i] == $prechosen) $index = $i;
		if ($index >= 0)
		{
			$html .= sumac_getHTMLForSessionRegistrationPanel($index,$prechosen,$courseChosenPHP,
							$names[$index],sumac_formatDate($dates[$index],$dateformat),
							$durations[$index],$durationUnits[$index],$fees[$index],
							$queries,$costs,$requiredCosts[$index],$optionalCosts[$index],
							$bookingStatuses[$index],$bookingMessages[$index],true);
			return $html;
		}
//
		else
		{
			$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_REQUESTED_COURSE_ID_INVALID,$prechosen);
			return false;
		}
	}
}

function sumac_getHTMLCourseListTableHead()
{
	$html = ' <div id="sumac_course_list">' . "\n";
	$html .= '  <table id="sumac_course_list_table" class="sumac_cl">' . "\n";
	$html .= '   <thead class="sumac_cl">' . "\n";
	$html .= '    <tr id="sumac_course_list_titles" class="sumac_cl_titles">' . "\n";
	$html .= sumac_getHTMLCourseListColumnTitle($_SESSION[SUMAC_STR]["CC3"],1);
	$html .= sumac_getHTMLCourseListColumnTitle($_SESSION[SUMAC_STR]["CC17"],2);
	$html .= sumac_getHTMLCourseListColumnTitle($_SESSION[SUMAC_STR]["CC8"],3);
	//$html .= sumac_getHTMLCourseListColumnTitle($_SESSION[SUMAC_STR]["CC12"],-1);
	$html .= sumac_getHTMLCourseListColumnTitle($_SESSION[SUMAC_STR]["CC12"],4);
	$html .= '    </tr>' . "\n";
	$html .= '    <tr id="sumac_course_list_empty" class="sumac_cl sumac_nodisplay">' . "\n";
	$html .= '    <td colspan="4">' . $_SESSION[SUMAC_STR]["CU6"] . '</td>' . "\n";
	$html .= '    </tr>' . "\n";
	$html .= '   </thead>' . "\n";

	return $html;
}

function sumac_getHTMLCourseListColumnTitle($title,$sortcolumn)
{
	$html = '     <th><a class="sumac_cl_title';
	if ($sortcolumn >= 0) $html .= '_sort" onclick="sumac_sort_list(event,' . $sortcolumn . ');" href="x://sort column">';
	else $html .= '">';
	$html .= $title . '</a></th>' . "\n";
	return $html;
}

function sumac_getHTMLForOneSessionInCourseList($index,$id,$idaskeys,$courseChosenPHP,
				$groupingString,$name,$date,$duration,$durationUnit,$fee,
				$instructors,$courseDetail,$instrDetails,$queries,
				$costs,$requiredCosts,$optionalCosts,$bookingStatus,$bookingMessage)
{
//each course/session row in the course list consists of:
//(a) course/session name with link to detail to show in iframe
//(b) session start date (pre-formatted for display)
//(c) duration of session as number of somethings
//(d) names of one or more instructors with links to details to show in iframes
//(e) INVISIBLE string of ones and zeroes indicating groupings in which this course belongs
//the row also has an id that is made up of a constant and a set of sort keys


	$html = '    <tr id="sumac_cl_tra_' . $index . '_' . $idaskeys . '" class="sumac_cl">' . "\n";

	$noCourseDetail = (($courseDetail == null) || ($courseDetail == ''));
	$href = $noCourseDetail ? ' href="javascript:sumac_unhide_detail(\'' . $index . '\',\'\',\'\',\'\');"'
				: ' href="javascript:sumac_unhide_detail(\'' . $index . '\',\'course\',\'\',\'' . $courseDetail . '\');"';
	$activeflag = sumac_sessionIsIncludedInOrder($id) ? '&nbsp;&nbsp;[' . $_SESSION[SUMAC_STR]["CU27"] . ']' : '';
	$html .= '     <td><a class="sumac_cl_name" ' . $href . '>' . $name . '</a>' . $activeflag . '</td>' . "\n";
	$html .= '     <td class="sumac_cl_date">' . $date . '</td>' . "\n";
	$html .= '     <td class="sumac_cl_duration">' . $duration . '&nbsp;' . $durationUnit . '</td>' . "\n";
	$html .= '     <td class="sumac_cl_instructors">';
	$noInstructorDetails = true;
	for ($i = 0; $i < count($instructors); $i++)
	{
		if (($instrDetails[$i] != null) && ($instrDetails[$i] != ''))
		{
			$html .= '<a class="sumac_cl_instructor"' .
					' href="javascript:sumac_unhide_detail(\'' . $index . '\',\'instr\',\'' . $i . '\',\'' . $instrDetails[$i] . '\');">' .
					$instructors[$i] . '</a>';
			$noInstructorDetails = false;
		}
		else
		{
			$html .= '<span class="sumac_cl_instructor">' . $instructors[$i] . '</span>';
		}
	}
	$html .= '</td>' . "\n";
	$html .= '     <td class="sumac_nodisplay">' . $groupingString . '</td>';
	$html .= '    </tr>' . "\n";
	$html .= '    <tr id="sumac_cl_trb_' . $index . '" class="sumac_nodisplay">' . "\n";
	$html .= '     <td id="sumac_cl_cdetail_' . $index . '" colspan="4"><span class="sumac_stacked_buttons">' . "\n";
	if ($noCourseDetail && $noInstructorDetails)
	{
		$html .= '      <button type="button" class="sumac_hide_detail" onclick="sumac_hide_detail(\'' . $index . '\');">' .
					$_SESSION[SUMAC_STR]["CL12"] . '</button>';
	}
	else
	{
		$html .= '      <button type="button" class="sumac_hide_detail" onclick="sumac_hide_detail(\'' . $index . '\');">' .
					$_SESSION[SUMAC_STR]["CL13"] . '</button>';
	}
	if ($bookingStatus == 0)
	{
		$takingNoBookingsFor = ($bookingMessage != "") ? $bookingMessage : $_SESSION[SUMAC_STR]["CX3"];
		$html .= '<span class="sumac_cl_no_bookings">' . $takingNoBookingsFor . $name . $_SESSION[SUMAC_STR]["CU15"] . $date . '</span></span>' . "\n";
	}
	else
	{
		$reglabel = sumac_sessionIsIncludedInOrder($id) ? $_SESSION[SUMAC_STR]["CU26"] : $_SESSION[SUMAC_STR]["CU22"];
		$html .= '<button type="button" class="sumac_register" onclick="sumac_unhide_registration_panel(\'' . $index . '\');">' .
					$reglabel . $name . '</button></span>' . "\n";
		$html .= sumac_getHTMLForSessionRegistrationPanel($index,$id,$courseChosenPHP,$name,$date,$duration,$durationUnit,
								$fee,$queries,$costs,$requiredCosts,$optionalCosts,
								$bookingStatus,$bookingMessage,false);
	}
	$html .= '    </td></tr>' . "\n";

	return $html;
}

function sumac_getHTMLForSessionRegistrationPanel($index,$id,$courseChosenPHP,$name,$date,$duration,$durationUnit,
													$fee,$queries,$costs,$requiredCosts,$optionalCosts,
													$bookingStatus,$bookingMessage,$isPrechosen)
{
/*
each selectedsession has an index from 0 to however many are selected.
each session, selected or not, also has an on screen index identifying its current place in the course list
each cost has an id
every session will have exactly one mandatory (i.e. type "hidden") cost whose id equals the session id
other costs may either be 'selectors' or 'multipliers'.
a 'selector' cost is type "hidden" if it is mandatory
a 'selector' cost is type "checkbox" if it is optional, and it starts by being NOT checked
a 'multiplier' cost has a value that gives the quantity of the item to be paid for, and it starts as ZERO
any cost may have 'options' that can be accessed by a button that executes a javascript function sumac_choose_options()
each option query has an id
the option settings are stored in the value of two "hidden" inputs whose id contains the session index
one "hidden" input holds the current settings, the other holds the initial default settings
all the settings for one session are stored in a single string with values separated by semi-colons
the string consists of a series of triplets - costid;queryid;text;

when a session is added to the registration basket, the users choices are stored in a $_SESSION element ["sumac_course_selections"]
this element has four elements itself - "session","costs","requirements", and "prices" (the last is of no importance yet)
each of these elements consists of an array indexed by selectedsession [0],[1], etc
there is one session entry for each selectedsession giving its sessionid
there is one cost entry for each cost item in a selectedsession giving its costid and the quantity chosen, always greater than 0
there is one requirements entry for each query in a cost item in a selectedsession giving its queryid and the text value entered or defaulted for it

when a selected session is revisited:-
	its index in the $_SESSION["sumac_course_selections"] arrays can be found by matching the sessonid to a ["session"] entry
	any 'selector' cost of type "checkbox" should be set "checked" if the corresponding $_SESSION["sumac_course_selections"]["costs"] quantity is 1
	the value of the corresponding $_SESSION["sumac_course_selections"]["costs"] quantity should be assigned to the value of any 'multiplier' cost items
	the default text parameter passed to sumac_choose_options() should be set from the ["requirements"] elements
	the ["requirements"] elements should also be set in the value of the "hidden" input in place of the original defaults
*/
	$selectedsessionIndex = sumac_getSelectedsessionIndex($id);
	$html = '<form id="' . SUMAC_ID_FORM_REGISTER  . $index  . '" action="' . $courseChosenPHP . '" accept-charset="UTF-8" method="post">' . "\n";
	if ($isPrechosen)
	{
		$html .= '<table id="sumac_cl_registration_only" class="sumac_single_course_registration">' . "\n";
	}
	else
	{
		$html .= '<table id="sumac_cl_registration_' . $index . '" class="sumac_nodisplay">' . "\n";
	}
	$html .= '<thead>' . "\n";
	if ($bookingStatus == 2)
	{
		$waitListingFor = ($bookingMessage != "") ? $bookingMessage : $_SESSION[SUMAC_STR]["CX4"];
		$html .= '<tr><th class="sumac_course_registration_title" colspan="7">' .
					'<span class="sumac_cl_wait_listing">' . $waitListingFor . '</span></th></tr>' . "\n";
	}
	$html .= '<tr><th class="sumac_course_registration_title" colspan="7">' .
					$name . ' from ' . $date . ' for ' . $duration . ' ' . $durationUnit . '</th></tr>' . "\n";
	$html .= '</thead>' . "\n";
	$html .= '<tbody>' . "\n";

	$_SESSION['sumac_temp_input'] = 0;
	$_SESSION['sumac_temp_total'] = 0;
	$_SESSION['sumac_temp_options'] = '';
	$_SESSION['sumac_temp_defaults'] = '';

	if ($fee != 0) // the session fee comes first if there is one
	{
		$html .= '<tr class="sumac_cc_fee">' . "\n";
		$html .= '<td class="sumac_cc_name">' . $_SESSION[SUMAC_SESSION_DEFAULT_COURSE_FEE_NAME] . '</td>' . "\n";
		$html .= '<td class="sumac_cc_dollars">' . sumac_centsToPrintableDollars($fee) . '</td>' . "\n";
		$html .= '<td class="sumac_cc_selector"> '
					. '<input type="hidden" name="cost=' . $id . '=' . $fee . '" value="1" />'
					. '</td>' . "\n";
		$html .= '<td class="sumac_cc_options"> </td>' . "\n";
		$html .= '<td class="sumac_cc_payable_bold">' . sumac_centsToPrintableDollars($fee) . '</td>' . "\n";
		$html .= '<td class="sumac_cc_cents">' . $fee . '</td>' . "\n";
		$html .= '<td class="sumac_cc_paycents">' . $fee . '</td>' . "\n";
		$html .= '</tr>' . "\n";
		$_SESSION['sumac_temp_total'] = $_SESSION['sumac_temp_total'] + $fee;
	}
	$html .= sumac_getHTMLForOneRegistrationCostClass($index,$queries,$requiredCosts,$costs,'sumac_cc_reqextra',$selectedsessionIndex,false);
	$html .= sumac_getHTMLForOneRegistrationCostClass($index,$queries,$optionalCosts,$costs,'sumac_cc_optextra',$selectedsessionIndex,true);

	$html .= '<tr>' . "\n";
	$html .= '<td colspan="4"></td>' . "\n";
	$html .= '<td class="sumac_cc_paytotal">= ' . sumac_centsToPrintableDollars($_SESSION['sumac_temp_total']) . '</td>' . "\n";
	$html .= '</tr>' . "\n";

	$html .= '<tr>' . "\n";
	$html .= '<td class="sumac_course_registration_button" colspan="3">' . "\n";
	$registerButtonLabel = ($isPrechosen ? $_SESSION[SUMAC_STR]["CL28"] : $_SESSION[SUMAC_STR]["CL10"]);
	$registerButtonClass = ($isPrechosen ? 'sumac_single_course_register' : 'sumac_course_register');
	$html .= '<input class="' . $registerButtonClass . '" type="submit" name="register" value="' . $registerButtonLabel . '" />' . "\n";
	$html .= '</td>' . "\n";
	$html .= '<td class="sumac_cc_resetall">';
	$html .= '<button type="button" class="sumac_cc_resetall" onclick="sumac_reset_all(' . $index . ');">' . $_SESSION[SUMAC_STR]["CL32"] . '</button>';
	$html .= '</td>' . "\n";
	$html .= '</tr>' . "\n";

	$html .= '</tbody>' . "\n";
	$html .= '<tfoot>' . "\n";
	$html .= '<tr>' . "\n";
	$html .= '<th class="sumac_course_registration_foot" colspan="7">' . $_SESSION[SUMAC_STR]["CI3"] . '</th>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '</tfoot>' . "\n";
	$html .= '</table>' . "\n";
	$html .= '<input type="hidden" name="session" value="' . $id . '" />' . "\n";
	$html .= '<input id="' . SUMAC_ID_REGISTRATION_OPTIONS . $index . '" type="hidden" name="options" value="' . $_SESSION['sumac_temp_options'] .'" />' . "\n";
	$html .= '<input id="' . SUMAC_ID_REGISTRATION_DEFAULTS . $index . '" type="hidden" name="defaults" value="' . $_SESSION['sumac_temp_defaults'] .'" />' . "\n";

	$html .= '</form>' . "\n";

	return $html;
}

function sumac_getHTMLForOneRegistrationCostClass($index,$queries,$theseCosts,$allSuchCosts,$trClass,$selectedsessionIndex,$isOptional=false)
{
	$html = '';
	for ($i = 0; $i < count($theseCosts); $i++)
	{
		$cid = $theseCosts[$i];
		$cost = $allSuchCosts[$cid];
		$html .= '<tr class="' . $trClass . '">' . "\n";

//@@@handle 'pre-selected' (also affects payable)
		$inputId = 'sumac_input_' . $index . '_' . $_SESSION['sumac_temp_input'];
		$tdName = $cost['name'];
		$tdPrice = sumac_centsToPrintableDollars($cost['cents']);
		$numberSelected = 0;
		if ($cost['uom'] != '')
		{
			if ($cost['uom'] != 'session')
			{
				$tdPrice .= ' per ' . $cost['uom'];
			}
			else if ($isOptional)
			{
				$_SESSION['sumac_temp_input']++;
				$tdName = '<label for="' . $inputId . '">' . $tdName . '</label>';
				$tdPrice = '<label for="' . $inputId . '">' . $tdPrice . '</label>';
			}
		}

		$html .= '<td class="sumac_cc_name">' . $tdName . '</td>' . "\n";
		$html .= '<td class="sumac_cc_dollars">' . $tdPrice . '</td>' . "\n";
		if ($isOptional == false)
		{
			$html .= '<td class="sumac_cc_selector"> '
					. '<input type="hidden" name="cost=' . $cid . '=' . $cost['cents'] . '" value="1" />'
					. '</td>' . "\n";
			$numberSelected = 1;
		}
		else if ($cost['uom'] == 'session')
		{
			$costSelected = (($selectedsessionIndex >= 0) && isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]["costs"][$selectedsessionIndex][$cid]));
			$checked = ($costSelected) ? ' checked="checked"' : '';
			if ($checked) $numberSelected = 1;
			$html .= '<td class="sumac_cc_selector">'
					. '<input id="' . $inputId . '" type="checkbox" name="cost=' . $cid . '=' . $cost['cents']
					. '" value="1"' . $checked . ' onclick="sumac_calculate_payable(this);" />'
					. '</td>' . "\n";
		}
		else
		{
			$costSelected = (($selectedsessionIndex >= 0) && isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]["costs"][$selectedsessionIndex][$cid]));
			if ($costSelected) $numberSelected = $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]["costs"][$selectedsessionIndex][$cid];
			$inputValue = $numberSelected;
			$html .= '<td class="sumac_cc_multiplier"> x '
					. '<input type="number" size="3" min="0" name="cost=' . $cid . '=' . $cost['cents']
					. '" value="' . $inputValue . '" onchange="sumac_calculate_payable(this);" />'
					. '</td>' . "\n";
		}

		if ($cost['queries'] != '')
		{
			$queryIds = array_values(array_filter(explode(' ',$cost['queries'])));
			$jsquery = "'" . $index . "'";
			for ($j = 0; $j < count($queryIds); $j++)
			{
				$qid = $queryIds[$j];
				$uqid = $cid . ';' . $qid;
				$query = $queries[$qid];
				$querySelected = (($selectedsessionIndex >= 0) && isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]["requirements"][$selectedsessionIndex][$cid][$qid]));
				$qvalue = ($querySelected) ? $_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]["requirements"][$selectedsessionIndex][$cid][$qid] : $query['def'];
				$jsquery .=  ",'" . $uqid . "','" . $query['text'] . "','" . 'ignore' . "','" . $query['min'] . "','" . $query['max'] . "','" . $query['pa'] . "'";
				$_SESSION['sumac_temp_options'] .= $uqid . ';' . $qvalue  . ';';
				$_SESSION['sumac_temp_defaults'] .= $uqid . ';' . $query['def']  . ';';
			}
			$choiceButtonLabel = ($cost['label'] != '') ? $cost['label'] : $_SESSION[SUMAC_STR]["CL3"];
			$optbuttons = '<button type="button" class="sumac_cc_setopts" onclick="sumac_choose_options(' . $jsquery . ');">' . $choiceButtonLabel . '</button>';
//							. '<button type="button" class="sumac_cc_reviewopts">' . SUMAC_BUTTON_REVIEW_CHOICE . '</button>';
		}
		else $optbuttons = ' ';

		$html .= '<td class="sumac_cc_options">' . $optbuttons . '</td>' . "\n";
		$centsToPay = $numberSelected * $cost['cents'];
		if ($centsToPay == 0) $html .= '<td class="sumac_cc_payable_nonbold">' . sumac_centsToPrintableDollars(0) . '</td>' . "\n";
		else $html .= '<td class="sumac_cc_payable_bold">' . sumac_centsToPrintableDollars($centsToPay) . '</td>' . "\n";
		$html .= '<td class="sumac_cc_cents">' . $cost['cents'] . '</td>' . "\n";
		$html .= '<td class="sumac_cc_paycents">' . $centsToPay . '</td>' . "\n";
		$html .= '</tr>' . "\n";
		$_SESSION['sumac_temp_total'] = $_SESSION['sumac_temp_total'] + $centsToPay;
	}

	return $html;
}

?>
