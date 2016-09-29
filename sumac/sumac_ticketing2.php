<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_geth2.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_ticketing2()
{
	$_SESSION[SUMAC_SESSION_ERROR_COUNT] = 0;
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;
//@@@ needs new error exit
	$theatreElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_THEATRE)->item(0);
	if ($theatreElement == false)
	{
		$_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS] = array();
		$_SESSION[SUMAC_SESSION_EVENT_NAMES] = array();
		$_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = 0;
		$html = sumac_getNoTicketableEventsHTML($organisationDocument);
		echo $html;
//@@@ needs new error exit
		return true;
	}
	$_SESSION[SUMAC_SESSION_THEATRE_NAME] = $theatreElement->getAttribute(SUMAC_ATTRIBUTE_NAME);

	if (!isset($_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS])) sumac_setProductionNames($organisationDocument);
	if (count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) < 1)
	{
//this cannot normally happen because the DTD insists that a theatre has at least one production_grouping, and a production_grouping has at least one production, and a production has at least one event
//so this would mean there was no theatre element - which would have been caught above - so leave alarming message
//@@@ needs new error exit
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_NO_EVENTS . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}
	$extrasDocument = null;
	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$extrasDocument = sumac_reloadExtrasDocument();
//@@@ needs new error exit
		if ($extrasDocument == false) return false;
	}

	$html = sumac_ticketing2_HTML($organisationDocument,$extrasDocument);
	echo $html;
	return true;
}

function sumac_getNoTicketableEventsHTML($organisationDocument)
{
//	leave the link alone for now - it's not doing any harm really
//	$_SESSION[SUMAC_SESSION_TICKETING_LINK] = false;

	$html = sumac_geth2_head('ticketing2'); //sumac_getHTMLHeadForEventSelection();
	$html .= '<body>' . "\n";
	$html .= sumac_addParsedXmlIfDebugging($organisationDocument,'organisation');
	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'selectevent') . sumac_getSubtitle('Ticketing'); //sumac_getSubtitle(sumac_ticketing2_title_with_user());
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= '<table class="sumac_function_impossible">' . "\n";
	$html .= '<tr><td class="sumac_status">' . $_SESSION[SUMAC_SESSION_THEATRE_MISSING] . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
	$html .= sumac_getSumacFooter(SUMAC_PACKAGE_TICKETING2) . sumac_getUserHTML(SUMAC_USER_BOTTOM);
	if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
	{
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
		$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
	}
	$html .= '</body></html>' . "\n";
	return $html;
}

function sumac_setProductionNames($organisationDocument)
{
	$_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS] = array();
	$_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] = array();
	$_SESSION[SUMAC_SESSION_EVENT_NAMES] = array();
	$_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING] = 0;

	$groupingElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
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
//				if (strlen($eventHappening) > 0) $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] .= ', ' . $eventHappening;
				if (strlen($eventHappening) > 0) $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] .= '<br />' . $eventHappening;
			}
		}
	}
}

function sumac_ticketing2_HTML($organisationDocument,$extrasDocument)
{
	return sumac_geth2_head('ticketing2')
			.sumac_ticketing2_body($organisationDocument,$extrasDocument);
}

function sumac_ticketing2_body($organisationDocument,$extrasDocument)
{
	$retryform = null;
	$extrajs = 'var sumac_prod_group_count = '.count($_SESSION[SUMAC_SESSION_PRODUCTION_GROUPINGS]).';'.PHP_EOL;
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($organisationDocument,'ticketing2_organisation')
			.(($extrasDocument != null) ? sumac_addParsedXmlIfDebugging($extrasDocument,'extras') : '')
			.sumac_geth2_user('top','ticketing2')
			.sumac_ticketing2_content($organisationDocument,$extrasDocument)
			.sumac_geth2_body_footer('ticketing2',true,$retryform,'',$extrajs);
}

function sumac_ticketing2_content($organisationDocument,$extrasDocument)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_sumac_div_hide_mainpage('ticketing2',sumac_geth2_spantext('ticketing2','H1'));

	$html .= sumac_geth2_divtag('ticketing2','mainpage','mainpage');

	$html .= sumac_ticketing2_title_with_user();
	$html .= sumac_ticketing2_summary_table($organisationDocument,$extrasDocument,true);

	$html .= sumac_ticketing2_HTMLform($organisationDocument);

	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content

	return $html;
}

function sumac_ticketing2_instructions()
{
	$html = sumac_geth2_divtag('ticketing2','instructions','instructions');
	$html .= sumac_geth2_spantext('ticketing2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_HTMLform($organisationDocument)
{
	$activeGroup = $_SESSION[SUMAC_SESSION_ACTIVE_PRODUCTION_GROUPING];
	$hrefParameters = '&amp;ag=' . $activeGroup;
	foreach ($_SESSION[SUMAC_SESSION_PRODUCTION_DETAIL_SHOWING] as $prodId => $status)
	{
		$hrefParameters = $hrefParameters . '&amp;ds' . $prodId . '=' . $status;
	}

	$html = '<form id="' . SUMAC_ID_FORM_ACTION . '" action="sumac_event_chosen.php" accept-charset="UTF-8" method="post">' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_SELECT . '" class="sumac_maintable">' . "\n";
	$html .= sumac_ticketing2_instructions();
	//$html .= '<table class="sumac_instructions">' . "\n";
	//$html .= '<tr><td class="sumac_instructions">' . "\n";
	//$html .= $_SESSION[SUMAC_STR]["TI7"]  . "\n";
	//$html .= '</td></tr>';
	//$html .= '</table>';

	$html .= '<div id="sumac_top_tall_navbar">' . "\n";
	$html .= sumac_getHTMLTopNavbarForSelect($organisationDocument);
	$html .= sumac_getHTMLTicketingNavbarActionLinks('sumac_top_tall_navbar',$hrefParameters,false,true);
	$html .= '</div>' . "\n";

	$groupingElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
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
	$html .= sumac_getHTMLBottomNavbarForSelect($organisationDocument);
	$html .= sumac_getHTMLTicketingNavbarActionLinks('sumac_bottom_ticketing_navbar',$hrefParameters,false,true);
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLTopNavbarForSelect($organisationDocument)
{
	$html = ' <div class="sumac_navbar_large_links" style="float:left">' . "\n";
	$groupingElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
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

function sumac_getHTMLBottomNavbarForSelect($organisationDocument)
{
	$html = ' <div class="sumac_navbar_small_links" style="float:left">' . "\n";
	$groupingElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_PRODUCTION_GROUPING);
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
