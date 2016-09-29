<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_execSelectEvent($eventChosenPHP)
{
	$_SESSION[SUMAC_SESSION_ERROR_COUNT] = 0;
	$theatreDocument = sumac_reloadOrganisationDocument();
	if ($theatreDocument == false) return false;
	$theatreElement = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_THEATRE)->item(0);
	if ($theatreElement == false)
	{
		$_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS] = array();
		$_SESSION[SUMAC_SESSION_EVENT_NAMES] = array();
		$_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = 0;
		$html = sumac_getNoTicketableEventsHTML($theatreDocument);
		echo $html;
		return true;
	}
	$_SESSION[SUMAC_SESSION_THEATRE_NAME] = $theatreElement->getAttribute(SUMAC_ATTRIBUTE_NAME);

	if (!isset($_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS])) sumac_setProductionNames($theatreDocument);
	if (count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) < 1)
	{
//this cannot normally happen because the DTD insists that a theatre has at least one production_grouping, and a production_grouping has at least one production, and a production has at least one event
//so this would mean there was no theatre element - which would have been caught above - so leave alarming message
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_EVENTS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$extrasDocument = null;
	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$extrasDocument = sumac_reloadExtrasDocument();
		if ($extrasDocument == false) return false;
	}

	$html = sumac_getEventSelectionHTML($theatreDocument,$extrasDocument,$eventChosenPHP);
	echo $html;

	return true;
}

function sumac_setProductionNames($theatreDocument)
{
	$_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS] = array();
	$_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] = array();
	$_SESSION[SUMAC_SESSION_EVENT_NAMES] = array();
	$_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = 0;

	$groupingElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
	for ($i = 0; $i < $groupingElements->length; $i++)
	{
		$groupingElement = $groupingElements->item($i);
		$groupId = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS][$groupId] = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_NAME);

		$productionElements = $groupingElement->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION);
		for ($j = 0; $j < $productionElements->length; $j++)
		{
			$productionElement = $productionElements->item($j);
			$productionId = 'gp' . $i . '_' . $j;
			$productionName = $productionElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING][$productionId] = 'f';

			$eventElements = $productionElement->getElementsByTagName(SUMAC_ELEMENT_EVENT);
			for ($k = 0; $k < $eventElements->length; $k++)
			{
				$eventElement = $eventElements->item($k);
				$eventId = $eventElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$eventHappening = $eventElement->getAttribute(SUMAC_ATTRIBUTE_HAPPENING);
				$_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] = $productionName;
				if (strlen($eventHappening) > 0) $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] .= ', ' . $eventHappening;
			}
		}
	}
}

function sumac_getNoTicketableEventsHTML($theatreDocument)
{
//	leave the link alone for now - it's not doing any harm really
//	$_SESSION[SUMAC_SESSION_TICKETING_LINK] = false;

	$html = sumac_getHTMLHeadForEventSelection();
	$html .= '<body>' . "\n";
	$html .= sumac_addParsedXmlIfDebugging($theatreDocument,'theatre');
	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectevent') . sumac_getSubtitle();
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= '<table class="sumac_function_impossible">' . "\n";
	$html .= '<tr><td class="sumac_status">' . $_SESSION[SUMAC_SESSION_THEATRE_MISSING] . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);
	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
		$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
	}
	$html .= '</body></html>' . "\n";
	return $html;
}

function sumac_getEventSelectionHTML($theatreDocument,$extrasDocument,$eventChosenPHP)
{
	$html = sumac_getHTMLHeadForEventSelection();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($theatreDocument,'theatre');
	if ($extrasDocument != null) $html .= sumac_addParsedXmlIfDebugging($extrasDocument,'extras');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectevent') . sumac_getSubtitle();

	$html .= sumac_getBasketAndExtrasHTML($theatreDocument,$extrasDocument,true);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= sumac_getHTMLBodyForSelect($theatreDocument,$eventChosenPHP);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
		$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
	}

	$html .= '</body></html>' . "\n";

	return $html;
}

function sumac_getHTMLHeadForEventSelection()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getEventSelectionHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getEventSelectionHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getEventSelectionHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";
	return $html;
}

function sumac_getEventSelectionHTMLStyleElements()
{
// the table listing events that you can get tickets for
	$html = 'table.sumac_events' . "\n" .
				'{' . "\n" .
				'	margin-left:10%;margin-right:auto;' . "\n" .
				'	padding:20px 5px;text-align:left;' . "\n" .
				'}' . "\n";

// buttons in the event selection page that hide and show detail and link to the seat pick page
// smaller in both directions than the action buttons
	$html .= 'input.sumac_order_seats' . "\n" .
				'{' . "\n" .
				'	border:2px outset;border-radius:5px;-moz-border-radius:5px;' . "\n" .
				'	width:100px;height:20px;background:lightgrey;color:black;' . "\n" .
				'	padding:0px 0px;text-align:center;' . "\n" .
				'	font-size:75%;' . "\n" .
				'}' . "\n" ;

// the iframe areas that hold the event/performance detail
	$html .= 'td.sumac_detailpanel' . "\n" .
				'{' . "\n" .
				'	margin-left:auto;margin-right:auto;' . "\n" .
				'}' . "\n";

// the display of the name of the production beside its list of performances in event selection
	$html .= 'td.sumac_production_name' . "\n" .
				'{' . "\n" .
				'	font-weight: bold;font-size: 125%;' . "\n" .
				'	text-align:left;padding:15px 20px 0px 0px;' . "\n" .
				'}' . "\n";

	return $html;
}

function sumac_getEventSelectionHTMLScriptVariables()
{
	$html = '';
	$html .= 'var sumac_label_hide_detail = "' . $_SESSION[SUMAC_STR]["TL9"] . '";' . "\n";
	$html .= 'var sumac_label_show_detail = "' . $_SESSION[SUMAC_STR]["TL10"] . '";' . "\n";

	$groupCount = count($_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS]);
	$html .= 'var sumac_prod_group_count = ' . $groupCount . ';' . "\n";
	return $html;
}

function sumac_getEventSelectionHTMLScriptFunctions()
{
	return <<<EOJS

	function sumac_unhide_detail(prod)
	{
		document.getElementById("sumac_detail_" + prod).className = "sumac_detailpanel";
		document.getElementById("sumac_button_showhide_" + prod).className = "sumac_hide_detail";
		document.getElementById("sumac_button_showhide_" + prod).innerHTML = sumac_label_hide_detail;
		document.getElementById("sumac_input_detail_" + prod).value = 't';
		var links = document.getElementsByTagName('a');
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if (link.className.indexOf('sumac_action_link_') >= 0)
			{
				var href = link.getAttribute('href');
				if (href != null)
				{
					var parameterPos = href.indexOf(prod);
					var valuePos = href.indexOf('=',parameterPos);
					href = href.substr(0,valuePos+1) + 't' + href.substr(valuePos+2);
					link.setAttribute('href',href);
				}
			}
		}
	}

	function sumac_hide_detail(prod)
	{
		document.getElementById("sumac_detail_" + prod).className = "sumac_detailpanel sumac_nodisplay";
		document.getElementById("sumac_button_showhide_" + prod).className = "sumac_show_detail";
		document.getElementById("sumac_button_showhide_" + prod).innerHTML = sumac_label_show_detail;
		document.getElementById("sumac_input_detail_" + prod).value = 'f';
		var links = document.getElementsByTagName('a');
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if (link.className.indexOf('sumac_action_link_') >= 0)
			{
				var href = link.getAttribute('href');
				if (href != null)
				{
					var parameterPos = href.indexOf(prod);
					var valuePos = href.indexOf('=',parameterPos);
					href = href.substr(0,valuePos+1) + 'f' + href.substr(valuePos+2);
					link.setAttribute('href',href);
				}
			}
		}
	}

	function sumac_enable_pickseats(prod)
	{
		if (document.getElementById("sumac_select_" + prod).selectedIndex == 0)
		{
			document.getElementById("sumac_button_orderseats_" + prod).setAttribute('disabled','disabled');
		}
		else
		{
			document.getElementById("sumac_button_orderseats_" + prod).removeAttribute('disabled');
		}
	}

	function sumac_set_chosen_event(prod)
	{
		var select = document.getElementById("sumac_select_" + prod);
		document.getElementById('sumac_input_chosen_event').value = select.options[select.selectedIndex].value;
	}

	function sumac_replace_group_panel(group)
	{
		var oldgroup = '0';
		var newgroup = '0';
		for (var i = 0; i < sumac_prod_group_count; i++)
		{
			if (group == ('group' + i))
			{
				oldgroup = document.getElementById('sumac_input_active_group').value;
				newgroup = i;
				document.getElementById("sumac_top_link_" + group).className = "sumac_selected_navlink";
				document.getElementById("sumac_bottom_link_" + group).className = "sumac_selected_navlink";
				document.getElementById("sumac_panel_" + group).style.display = "block";
				document.getElementById('sumac_input_active_group').value = newgroup;
			}
			else
			{
				document.getElementById("sumac_top_link_group" + i).className = "sumac_navlink";
				document.getElementById("sumac_bottom_link_group" + i).className = "sumac_navlink";
				document.getElementById("sumac_panel_group" + i).style.display = "none";
			}
		}
		var links = document.getElementsByTagName('a');
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if (link.className.indexOf('sumac_action_link_') >= 0)
			{
				var href = link.getAttribute('href');
				if (href != null)
				{
					var parameterPos = href.indexOf('ag');
					var valuePos = href.indexOf('=',parameterPos);
					href = href.substr(0,valuePos+1) + newgroup + href.substr(valuePos+1+oldgroup.length);
					link.setAttribute('href',href);
				}
			}
		}
	}
EOJS;
}

function sumac_getHTMLBodyForSelect($theatreDocument,$eventChosenPHP)
{
	$activeGroup = $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING];
	$hrefParameters = '&amp;ag=' . $activeGroup;
	foreach ($_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] as $prodId => $status)
	{
		$hrefParameters = $hrefParameters . '&amp;ds' . $prodId . '=' . $status;
	}

	$html = '<form id="' . SUMAC_ID_FORM_ACTION . '" action="' . $eventChosenPHP . '" accept-charset="UTF-8" method="post">' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_SELECT . '" class="sumac_maintable">' . "\n";
	$html .= '<table class="sumac_instructions">' . "\n";
	$html .= '<tr><td class="sumac_instructions">' . "\n";
	$html .= $_SESSION[SUMAC_STR]["TI7"]  . "\n";
	$html .= '</td></tr>';
	$html .= '</table>';

	$html .= '<div id="sumac_top_tall_navbar">' . "\n";
	$html .= sumac_getHTMLTopNavbarForSelect($theatreDocument);
	$html .= sumac_getHTMLTicketingNavbarActionLinks('sumac_top_tall_navbar',$hrefParameters,false,true);
	$html .= '</div>' . "\n";

	$groupingElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
	for ($i = 0; $i < $groupingElements->length; $i++)
	{
		$groupingElement = $groupingElements->item($i);
		$productionElements = $groupingElement->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION);
		if ($productionElements->length < 1) continue;
		$html .= sumac_getHTMLGroupHeader($i);

		for ($j = 0; $j < $productionElements->length; $j++)
		{
			$productionElement = $productionElements->item($j);
			$productionName = $productionElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$selectionString = $productionElement->getAttribute(SUMAC_ATTRIBUTE_SELECTION);
			if ($selectionString == '') $selectionString = $_SESSION[SUMAC_SESSION_SELECT_AN_EVENT];
			$detailURL = $productionElement->getAttribute(SUMAC_ATTRIBUTE_DETAIL);
			$eventElements = $productionElement->getElementsByTagName(SUMAC_ELEMENT_EVENT);
			if ($eventElements->length < 1) continue;
			$html .= sumac_getHTMLProductionHeader($i,$j,$productionName,$selectionString,$eventElements->length);

			for ($k = 0; $k < $eventElements->length; $k++)
			{
				$eventElement = $eventElements->item($k);
				$eventId = $eventElement->getAttribute(SUMAC_ATTRIBUTE_ID);
				$eventHappening = $eventElement->getAttribute(SUMAC_ATTRIBUTE_HAPPENING);
				if (strlen($eventHappening) > 0) $eventName = $eventHappening;
				else $eventName = $productionName;
				$html .= sumac_getHTMLEventOptions($eventId,$eventName);
			}

			$html .= sumac_getHTMLProductionRemainder($i,$j,$detailURL,$eventElements->length);
		}
		$html .= sumac_getHTMLGroupRemainder($i);
	}

	$html .= '<input id="sumac_input_chosen_event" type="hidden" name="eventchosen" value="nonechosen" />' . "\n";
	$html .= '<input id="sumac_input_active_group" type="hidden" name="ag" value="' . $activeGroup . '" />' . "\n";
	foreach ($_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] as $prodId => $status)
	{
		$dsid = 'sumac_input_detail_' . $prodId;
		$dsname = 'ds' . $prodId;
		$html .= '<input id="' . $dsid . '" type="hidden" name="' . $dsname . '" value="' . $status . '" />' . "\n";
	}
	$html .= '<input id="' . SUMAC_ID_BASKET_OF_TICKETS . '" type="hidden" name="basket" value="" />' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</form>' . "\n";

	$html .= '<div id="sumac_bottom_ticketing_navbar">' . "\n";
	$html .= sumac_getHTMLBottomNavbarForSelect($theatreDocument);
	$html .= sumac_getHTMLTicketingNavbarActionLinks('sumac_bottom_ticketing_navbar',$hrefParameters,false,true);
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLTopNavbarForSelect($theatreDocument)
{
	$html = ' <div class="sumac_navbar_large_links" style="float:left">' . "\n";
	$groupingElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
	if ($groupingElements->length > 1)
	{
		for ($i = 0; $i < $groupingElements->length; $i++)
		{
			$groupingElement = $groupingElements->item($i);
			$name = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$explanation = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_EXPLANATION);
			$navlinkClass = ($i == $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING]) ? "sumac_selected_navlink" : "sumac_navlink";
			$separator = ($i < ($groupingElements->length - 1)) ? ' |' : '';
			$html .= '  <a id="sumac_top_link_group' . $i . '" class="' . $navlinkClass . '" href="#sumac_top_tall_navbar"'
						. ' onclick="sumac_replace_group_panel(\'group' . $i . '\')"'
						. ' title="' . $explanation. '">' . $name . '</a>' . $separator . "\n";
		}
	}
	$html .= ' </div>' . "\n";
	return $html;
}

function sumac_getHTMLGroupHeader($groupIndex)
{
	$display = ($groupIndex == 	$_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING]) ? 'block' : 'none';
	$html = '<div id="sumac_panel_group' . $groupIndex . '" style="display:' . $display . ';">' . "\n";
	$html .= '<table class="sumac_events">' . "\n";
	return $html;
}

function sumac_getHTMLProductionHeader($groupIndex,$productionIndex,$productionName,$selectionString,$eventCount)
{
	$groupProd = 'gp' . $groupIndex . '_' . $productionIndex;
	$html = '<tr class="eventrow">' . "\n";
	$html .= '<td class="sumac_production_name">' . $productionName . '</td>' . "\n";
	$html .= '<td align="left" style="padding:15px 20px 0px 30px;">' . "\n";
	$html .= '<select id="sumac_select_' . $groupProd . '" name="event"'
				. ' onchange="sumac_enable_pickseats(\'' . $groupProd . '\')">' . "\n";
	if ($eventCount > 1)
		$html .= '<option style="font-style:italic;" value="none">' . $selectionString . '</option>' . "\n";
	return $html;
}

function sumac_getHTMLEventOptions($eventId,$eventName)
{
	$html = '<option value="' . $eventId . '">' . $eventName . '</option>' . "\n";
	return $html;
}

function sumac_getHTMLProductionRemainder($groupIndex,$productionIndex,$detailURL,$eventCount)
{
	$groupProd = 'gp' . $groupIndex . '_' . $productionIndex;
	$disablePickButton = ($eventCount > 1) ? ' disabled="disabled"' : '';
	$detailShowing = $_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING][$groupProd];
	$detailClass = ($detailShowing == 't') ? 'sumac_detailpanel' : 'sumac_detailpanel sumac_nodisplay';
	$detailButtonClass = ($detailShowing == 't') ? 'sumac_hide_detail' : 'sumac_show_detail';
	$html = '</select>' . "\n";
	$html .= '</td>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '<tr>' . "\n";
	if ($detailURL != null)
	{
		$html .= '<td align="right"><button id="sumac_button_showhide_' . $groupProd . '" type="button" class="' . $detailButtonClass . '"'
					. ' onclick="if (this.className == \'sumac_show_detail\') sumac_unhide_detail(\'' . $groupProd . '\');'
					. ' else sumac_hide_detail(\'' . $groupProd .'\')">' . $_SESSION[SUMAC_STR]["TL10"] . '</button></td>' . "\n";
	}
	else
	{
		$html .= '<td></td>' . "\n";
	}
	$html .= '<td align="right"><input class="sumac_order_seats" type="submit" id="sumac_button_orderseats_' . $groupProd . '"'
				. ' name="pickseats" value="' . $_SESSION[SUMAC_STR]["TL2"] . '"' . $disablePickButton
				. ' onclick="sumac_set_chosen_event(\'' . $groupProd . '\');" title="' . $_SESSION[SUMAC_STR]["TT2"] . '" /></td>' . "\n";
	$html .= '</tr>' . "\n";
	if ($detailURL != null)
	{
		$html .= '<tr>' . "\n";
		$html .= '<td colspan="2" align="center" id="sumac_detail_' . $groupProd . '" class="' . $detailClass . '" >'
					. '<iframe src="' . $detailURL . '" width="' . $_SESSION[SUMAC_SESSION_DETAILWIDE] . '"'
					. ' height="' . $_SESSION[SUMAC_SESSION_DETAILTALL] . '"></iframe></td>' . "\n";
		$html .= '</tr>' . "\n";
	}
	return $html;
}

function sumac_getHTMLGroupRemainder()
{
	$html = '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLBottomNavbarForSelect($theatreDocument)
{
	$html = ' <div class="sumac_navbar_small_links" style="float:left">' . "\n";
	$groupingElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
	if ($groupingElements->length > 1)
	{
		for ($i = 0; $i < $groupingElements->length; $i++)
		{
			$groupingElement = $groupingElements->item($i);
			$name = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$explanation = $groupingElement->getAttribute(SUMAC_ATTRIBUTE_EXPLANATION);
			$navlinkClass = ($i == $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING]) ? "sumac_selected_navlink" : "sumac_navlink";
			$separator = ($i < ($groupingElements->length - 1)) ? ' |' : '';
			$html .= '  <a id="sumac_bottom_link_group' . $i . '" class="' . $navlinkClass . '" href="#sumac_top_tall_navbar"'
						. ' onclick="sumac_replace_group_panel(\'group' . $i . '\')"'
						. ' title="' . $explanation. '">' . $name . '</a>' . $separator . "\n";
		}
	}
	$html .= ' </div>' . "\n";
	return $html;
}

?>
