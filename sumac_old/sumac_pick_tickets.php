<?php
//version5125//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_execPickTicketsForEvent($source,$port,$event)
{
	$theatreDocument = sumac_reloadOrganisationDocument();
	if ($theatreDocument == false) return false;

	$seatsalesDocument = sumac_loadSeatsalesDocument($source,$port,$event);
	if ($seatsalesDocument == false) return false;

	//$eventElement = $theatreDocument->getElementById($event);
	//in version 2.0 we have to support events occurring more than once in the 'theatre' document
	$eventElement = null;
	$eventElements = $theatreDocument->getElementsByTagName(SUMAC_ELEMENT_EVENT);
	for ($i = 0; $i < $eventElements->length; $i++)
	{
		if ($eventElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID) == $event)
		{
			$eventElement = $eventElements->item($i);
			break;
		}
	}
	if ($eventElement == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_EVENT_NOT_IN_THEATRE,$event) . $_SESSION[SUMAC_STR]["AE5"];
		return false;
	}

	$extrasDocument = null;
	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$extrasDocument = sumac_reloadExtrasDocument();
		if ($extrasDocument == false) return false;
	}

	$html = sumac_getTicketPickingHTML($theatreDocument,$seatsalesDocument,$eventElement,$extrasDocument);

	if ($_SESSION[SUMAC_SESSION_ERROR_COUNT] > 0)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_ERRORS_IN_THEATRE,$_SESSION[SUMAC_SESSION_ERROR_COUNT]) . $_SESSION[SUMAC_STR]["AE5"];
		listXMLElementsAndAttributes($theatreDocument,0);
		listXMLElementsAndAttributes($seatsalesDocument,0);
		return false;
	}

	echo $html;
	return true;
}

function sumac_getTicketPickingHTML($theatreDocument,$seatsalesDocument,$eventElement,$extrasDocument)
{
	$htmlTitle = sumac_getHTMLTitle('','');

	$locationId = $eventElement->getAttribute(SUMAC_ATTRIBUTE_LOCATED_AT);
	$locationElement = $theatreDocument->getElementById($locationId);

//in getting the HTML for the stage, lists of classes used for seats and sections are built
//and in getting the HTML for the seating plan, lists of block labels, weights, prices, and details are built
	$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] = array();
	$_SESSION[SUMAC_SESSION_SEATS_AVAILABLE] = array();
	$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] = array();
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES] = array();
	$_SESSION[SUMAC_SESSION_BORDERCOLORS_FOR_TABLES] = array();
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_ROWS] = array();
	$_SESSION[SUMAC_SESSION_BLOCKS_LTOR] = array();	//ordered list of block labels
	$_SESSION[SUMAC_SESSION_BLOCKS_DETAILS] = array();	//ordered list of block detail filenames
	$_SESSION[SUMAC_SESSION_SEAT_GRADES] = array();	//list of seat weights used
	$_SESSION[SUMAC_SESSION_SEAT_PRICINGS] = array();	//list of seat pricings used
//these lists are referred to in building the legend and script elements
//so the stage/seatingplan HTML must be built before the legend and the script
	$_SESSION[SUMAC_SESSION_AVAILABLE_SEAT_COUNT] = 0;	//count will be set when HTML for seats has been built

	$layoutId = $eventElement->getAttribute(SUMAC_ATTRIBUTE_USING_LAYOUT);
	$layoutElement = $theatreDocument->getElementById($layoutId);
	$theatreColour = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_THEATRE_COLOUR);
	if ($theatreColour == null) $theatreColour = $_SESSION[SUMAC_SESSION_ETBGCOLOUR];
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] = $theatreColour;

	$usingStage = ($layoutElement->getElementsByTagName(SUMAC_ELEMENT_STAGE)->length > 0);
	if ($usingStage)
	{
		$htmlBody['stage'] = sumac_getHTMLBodyForStageFromLayout($theatreDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement) . "\n";
		$htmlStyle = sumac_getHTMLStyleForTheatre($theatreDocument,$usingStage,$htmlBody['stage']);
	}
	else //there must be a seatingplanElement - the DTD enforces that
	{
		$htmlBody['seatingplan'] = sumac_getHTMLBodyForSeatingPlanFromLayout($theatreDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement) . "\n";
		$htmlStyle = sumac_getHTMLStyleForTheatre($theatreDocument,$usingStage,$htmlBody['seatingplan']);
	}
	$htmlScript = sumac_getHTMLScriptForTicketPicks($theatreDocument);
	$htmlBody['legend'] = sumac_getHTMLBodyForLegendFromLayout($theatreDocument,$seatsalesDocument,$layoutElement);

	$html = '';
	//$html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"' .
	//				' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	$html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";

	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= $htmlTitle . "\n";
	$html .= $htmlStyle . "\n";
	$html .= $htmlScript . "\n";

	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($seatsalesDocument,'seatsales');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'picktickets') . sumac_getSubtitle();

	$html .= sumac_getBasketAndExtrasHTML($theatreDocument,$extrasDocument,true);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);
	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_top_ticketing_navbar",'',true,true);

	if ($usingStage) $html .= $htmlBody['stage'] . "\n";//only one of the two possible units will have been created
	else $html .= $htmlBody['seatingplan'] . "\n";
	$html .= $htmlBody['legend'] . "\n";

	$html .= sumac_getHTMLBodyForTicketingActionsNavbar("sumac_bottom_ticketing_navbar",'',true,true);
	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if (sumac_basketContainsTicketsForEvent($_SESSION[SUMAC_SESSION_TICKETING_EVENT]))
	{
		$html .= '<script type="text/javascript">' . "\n";
		$html .= sumac_getHTMLScriptToSetPreviousPicksFromBasket($usingStage);
		$html .= '</script>' . "\n";
	}
	if ($usingStage == false) $html .= sumac_getStartingScriptForSeatingPlan();
	$html .= '</body></html>';
	return $html;
}

function sumac_getStartingScriptForSeatingPlan()
{
	return <<<EOSSFSPJS
<script type="text/javascript">
// **** initial settings for seating plan seat selection ****
for (var i = 0; i < document.getElementById('sumac_hidden_seats').getElementsByTagName('A').length; i++)
	sumac_hidden_seat_ids[i] = 1; // all seats start off as hidden
var sumac_number_of_seats_in_party = 1;
document.getElementById('sumac_seat_block_selector').selectedIndex = 0; //set 'ALL' blocks as requested
sumac_sort_seat_list(null,3); //sort seatlist in left-to-right order - and populate sumac_seat_ids
for (var i = 0; i < sumac_seat_ids.length; i++) sumac_seat_ids_ltor[i] = sumac_seat_ids[i]; //copy id array
for (var x = 0, i = sumac_seat_ids.length - 1; i >= 0; i--) // array of number-of-neighbouring-seats-available
{
	if (sumac_seat_ids_ltor[i].substr(-2) == '_Y') x = 1;
	else x = x + 1;
	sumac_seat_set_size[i] = x;
}
</script>

EOSSFSPJS;
}

//******************************************
//methods for building the html Head element
//******************************************

function sumac_getHTMLStyleForTheatre($theatreDocument,$usingStage,$stageOrSeatingPlanHTML)
{
	$html = '<style type="text/css">' . "\n";
	$html .= sumac_getCommonHTMLStyleElements() . "\n";

	if ($usingStage) $html .= sumac_getFixedHTMLStyleElementsForStage() . "\n";
	else  $html .= sumac_getFixedHTMLStyleElementsForSeatingPlan() . "\n";

	$html .= sumac_getFixedHTMLStyleElementsForLegend() . "\n";

	if ($usingStage) $html .= sumac_getVariableHTMLStyleElementsForStage($theatreDocument,$stageOrSeatingPlanHTML) . "\n";
	else $html .= sumac_getVariableHTMLStyleElementsForSeatingPlan($theatreDocument,$stageOrSeatingPlanHTML) . "\n";

	$html .= sumac_getVariableHTMLStyleElementsForLegend($theatreDocument) . "\n";
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	return $html . '</style>' . "\n";
}

function sumac_getFixedHTMLStyleElementsForStage()
{
	$html = '#' . SUMAC_ID_DIV_STAGE . ' td.' . SUMAC_CLASS_EVENTHEADER . ' {border-style:solid; border-width:thick; font-size:' . $_SESSION[SUMAC_SESSION_PH_SCALE] . '; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button {color:white; vertical-align:top; width:' . $_SESSION[SUMAC_SESSION_BUTTON_WIDTH] . '; height:' . $_SESSION[SUMAC_SESSION_BUTTON_HEIGHT] . '; padding:0; border: none; margin:0; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_NONSEAT . ' {background-color:white; color:black;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_NONSEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . ' {background-color:white; color:black; width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . ';}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_SOLD_SEAT . ' {background-color:lightgrey;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_SOLD_SEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . ' {background-color:lightgrey; width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . ';}' . "\n";
	return $html;
}

function sumac_getFixedHTMLStyleElementsForSeatingPlan()
{
	$html = '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_EVENTHEADER . ' {margin-left:auto;margin-right:auto;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_EVENTHEADER . ' td {border-style:solid; border-width:thick; border-radius:9px; -moz-border-radius:9px; padding:5px 5px; text-align:justify; font-size:' . $_SESSION[SUMAC_SESSION_PH_SCALE] . '; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' span.sumac_seat_availability_class {font-size:75%;font-weight:bold;padding:5px;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' span.sumac_seat_quantity_class {font-size:75%;font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' .sumac_so_pref {font-size:75%; text-align: center;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' .sumac_seat_quantity_class {font-size:75%; text-align: center;}' . "\n";
	return $html;
}

function sumac_getFixedHTMLStyleElementsForLegend()
{
//this button definition is almost the same as the stage one, but the sizes are always standard
//note that it does not apply to all legend buttons
	$html = '#' . SUMAC_ID_DIV_LEGEND . ' #' . SUMAC_ID_TABLE_BUTTONTABLE . ' button {vertical-align:top; width:1.4em; height:1.4em; padding:0; border: none; margin:0;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_LEGEND . ' input[type="text"] {width:2.0em;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_LEGEND . ' button.' . SUMAC_CLASS_ADD_SUBTRACT . ' {border-style:outset; border-width:medium; width:1.8em; height:1.8em;}' . "\n";
	return $html;
}

function sumac_getVariableHTMLStyleElementsForStage($theatreDocument,$stageHTML)
{
	$html = '';

	foreach ($_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES] as $tableClassName => $bgcolor)
	{
		if ($tableClassName == SUMAC_CLASS_THEATRE)
		{
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' table.' . $tableClassName . ' {width:100%; font-size:' . $_SESSION[SUMAC_SESSION_TH_SCALE] . '; background-color:' . $bgcolor . ';}' . "\n";
		}
		else if ((substr($tableClassName,0,strlen(SUMAC_CLASS_PREFIX_AREA)) == SUMAC_CLASS_PREFIX_AREA) &&
				(strpos($stageHTML, $tableClassName . SUMAC_CLASS_SUFFIX_EMPTY) !== false))
		{
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' table.' . $tableClassName . SUMAC_CLASS_SUFFIX_EMPTY . ' {display:none;}' . "\n";
		}
		else if ($tableClassName == SUMAC_CLASS_STAGE)
		{
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' table.' . $tableClassName . ' {width:100%; background-color:' . $bgcolor . ';}' . "\n";
		}
		else
		{
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' table.' . $tableClassName . ' {background-color:' . $bgcolor . ';}' . "\n";
		}
	}

	foreach ($_SESSION[SUMAC_SESSION_BORDERCOLORS_FOR_TABLES] as $tableClassName => $bordercolor)
	{
		$html .= '#' . SUMAC_ID_DIV_STAGE . ' table.' . $tableClassName . ' {border-color:' . $bordercolor . ';}' . "\n";
	}

	foreach ($_SESSION[SUMAC_SESSION_BGCOLORS_FOR_ROWS] as $rowClassName => $bgcolor)
	{
		$html .= '#' . SUMAC_ID_DIV_STAGE . ' tr.' . $rowClassName . ' {background-color:' . $bgcolor . ';}' . "\n";
	}

	$pricingElements = $theatreDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_PRICING);
	for ($i = 0; $i < $pricingElements->length; $i++)
	{
		$pricingId = $pricingElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$seatclass = SUMAC_CLASS_PREFIX_SEAT . $pricingId;
		$bgcolour = $pricingElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
		$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . $seatclass . ' {background-color:' . $bgcolour . ';}' . "\n";
		if (strpos($stageHTML, $seatclass . SUMAC_CLASS_SUFFIX_INWARD_FACING) !== false)
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . $seatclass . SUMAC_CLASS_SUFFIX_INWARD_FACING .
							' {width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . '; background-color:' . $bgcolour . ';}' . "\n";
		if (strpos($stageHTML, $seatclass . SUMAC_CLASS_SUFFIX_UNNUMBERED) !== false)
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . $seatclass . SUMAC_CLASS_SUFFIX_UNNUMBERED. ' {background-color:' . $bgcolour . ';}' . "\n";
		if (strpos($stageHTML, $seatclass . SUMAC_CLASS_SUFFIX_INWARD_FACING . SUMAC_CLASS_SUFFIX_UNNUMBERED) !== false)
			$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . $seatclass . SUMAC_CLASS_SUFFIX_INWARD_FACING . SUMAC_CLASS_SUFFIX_UNNUMBERED .
							' {width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . '; background-color:' . $bgcolour . ';}' . "\n";
	}
	return $html;
}

function sumac_getVariableHTMLStyleElementsForSeatingPlan($theatreDocument,$seatingPlanHTML)
{
//	$html = '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' {font-size:' . $_SESSION[SUMAC_SESSION_TH_SCALE] . '; background-color:' . $_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] . ';}' . "\n";
//	$html = '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' td {vertical-align:top;}' . "\n";
	$html = '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' td {vertical-align:top; font-size:' . $_SESSION[SUMAC_SESSION_TH_SCALE] . '; background-color:' . $_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] . ';}' . "\n";

	$pricingElements = $theatreDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_PRICING);
	for ($i = 0; $i < $pricingElements->length; $i++)
	{
		$pricingId = $pricingElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$seatclass = SUMAC_CLASS_PREFIX_SEAT . $pricingId;
		$bgcolour = $pricingElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
		$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' a.' . $seatclass . ' {color:white;background-color:' . $bgcolour . ';display:block;border:2px solid white;text-decoration:none;}' . "\n";
		$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' a.' . $seatclass . '_disabled {color:white;background-color:' . $bgcolour . ';display:block;border-style:solid;border-color:black;border-width:2px 10px;text-decoration:line-through;}' . "\n";
	}
	return $html;
}

function sumac_getVariableHTMLStyleElementsForLegend($theatreDocument)
{
	$html = '';
	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] as $seatClassName => $pricingId)
	{
		if (strpos($seatClassName,SUMAC_CLASS_SUFFIX_INWARD_FACING) !== false) continue;	//we always have (and prefer) the forward facing button
		$pricingElement = $theatreDocument->getElementById($pricingId);
		$bgcolour = $pricingElement->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
		$ticketClass = SUMAC_CLASS_PREFIX_TICKET . $pricingId;
		$html .= '#' . SUMAC_ID_DIV_LEGEND . ' #' . SUMAC_ID_TABLE_BUTTONTABLE . ' button.' . $seatClassName . ' {background-color:' . $bgcolour . ';}' . "\n";
		$html .= '#' . SUMAC_ID_DIV_LEGEND . ' span.' . $ticketClass . ' {color:white; background-color:' . $bgcolour . '; white-space:nowrap;}' . "\n";
	}
	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] as $sectionClassName => $pricingId)
	{
		$pricingElement = $theatreDocument->getElementById($pricingId);
		//version 3.0.2, Sept 2012, only use Sumac-supplied background colouring when there is a diagram
		$individualSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES]) > 0);
		if ($individualSeating) $bgcolour = $pricingElement->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
		else $bgcolour = $_SESSION[SUMAC_SESSION_SBAVCOLOUR];
		$html .= '#' . SUMAC_ID_DIV_LEGEND . ' span.' . $sectionClassName . ' {background-color:' . $bgcolour . ';}' . "\n";
		$html .= '#' . SUMAC_ID_DIV_LEGEND . ' td.' . $sectionClassName . ' {font-weight:bold; outline-style:solid; outline-width:medium; background-color:' . $bgcolour . '; white-space:nowrap;}' . "\n";
	}
	return $html;
}

function sumac_getHTMLScriptForTicketPicks($theatreDocument)
{
	$html = '<script type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getTicketPicksHTMLScriptVariables($theatreDocument);
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getTicketPicksHTMLScriptFunctions();
	$html .= '</script>' . "\n";
	return $html;
}

function sumac_getTicketPicksHTMLScriptVariables($theatreDocument)
{
	$html = '';
	$html .= 'var sumac_id_basket_of_tickets = "' . SUMAC_ID_BASKET_OF_TICKETS . '";' . "\n";
	$html .= 'var sumac_id_button_order_tickets = "' . SUMAC_ID_BUTTON_CHECK_OUT . '";' . "\n";
	$html .= 'var sumac_id_button_another_event = "' . SUMAC_ID_BUTTON_ANOTHER_EVENT . '";' . "\n";
	$html .= 'var sumac_id_button_cancel = "' . SUMAC_ID_BUTTON_CANCEL . '";' . "\n";
	$html .= 'var sumac_id_tr_picked_list = "' . SUMAC_ID_TR_PICKED_LIST . '";' . "\n";
	$html .= 'var sumac_id_span_total_cost = "' . SUMAC_ID_SPAN_TOTAL_COST . '";' . "\n";
	$html .= 'var sumac_id_span_seats_available_prefix = "' . SUMAC_ID_SPAN_SEATS_AVAILABLE_PREFIX . '";' . "\n";
	$html .= 'var sumac_id_span_block_available_prefix = "' . SUMAC_ID_SPAN_BLOCK_AVAILABLE_PREFIX . '";' . "\n";
	$html .= 'var sumac_id_span_initial_available_suffix = "' . SUMAC_ID_SPAN_INITIAL_AVAILABLE_SUFFIX . '";' . "\n";
	$html .= 'var sumac_label_seats_picked = "' . $_SESSION[SUMAC_STR]["TU20"] . '";' . "\n";
	$html .= 'var sumac_label_seats_sold = "' . $_SESSION[SUMAC_STR]["TU19"] . '";' . "\n";
	$html .= 'var sumac_label_seat_sold = "' . $_SESSION[SUMAC_STR]["TU18"] . '";' . "\n";
	$html .= 'var sumac_label_basket_is_empty = "' . $_SESSION[SUMAC_STR]["TJ3"] . '";' . "\n";
	$html .= 'var sumac_class_prefix_section = "' . SUMAC_CLASS_PREFIX_SECTION . '";' . "\n";
	$html .= 'var sumac_class_prefix_ticket = "' . SUMAC_CLASS_PREFIX_TICKET . '";' . "\n";
	$html .= 'var sumac_id_button_prefix = "' . SUMAC_ID_BUTTON_PREFIX . '";' . "\n";
	$html .= 'var sumac_id_button_add_prefix = "' . SUMAC_ID_BUTTON_ADD_PREFIX . '";' . "\n";
	$html .= 'var sumac_name_button_add_prefix = "' . SUMAC_NAME_BUTTON_ADD_PREFIX . '";' . "\n";
	$html .= 'var sumac_id_button_subtract_prefix = "' . SUMAC_ID_BUTTON_SUBTRACT_PREFIX . '";' . "\n";
	$html .= 'var sumac_id_input_text_prefix = "' . SUMAC_ID_INPUT_TEXT_PREFIX . '";' . "\n";
	$html .= 'var sumac_name_input_prefix_how_many = "' . SUMAC_NAME_INPUT_PREFIX_HOW_MANY . '";' . "\n";
	$html .= 'var sumac_tickets_for_other_events = "' . sumac_countSeatsOrderedInBasket() . '";' . "\n";

	$html .= 'var sumac_pricings_for_classes = { };' . "\n";
	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] as $seatClassName => $pricingId)
	{
		$html .= 'sumac_pricings_for_classes["' . $seatClassName . '"] = "' . $pricingId . '";' . "\n";
	}
	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] as $sectionClassName => $pricingId)
	{
		$html .= 'sumac_pricings_for_classes["' . $sectionClassName . '"] = "' . $pricingId . '";' . "\n";
	}
	$html .= 'var sumac_categories_for_pricings = { };' . "\n";
	$html .= 'var sumac_suggested_category_for_pricing = { };' . "\n";
	$html .= 'var sumac_category_queries_for_pricings = { };' . "\n";
//	$html .= 'var sumac_quantity_query = "' . SUMAC_QUANTITY_QUERY . '";' . "\n";
	$html .= 'var sumac_event_id = "' . $_SESSION[SUMAC_SESSION_TICKETING_EVENT] . '";' . "\n";
	$theseTickets = sumac_basketContainsTicketsForEvent($_SESSION[SUMAC_SESSION_TICKETING_EVENT]);
	$otherTickets = ((($theseTickets == false) && (sumac_countEventsInBasket() > 0))
						|| (($theseTickets == true) && (sumac_countEventsInBasket() > 1)));
	$html .= 'var sumac_orders_for_other_events = ' . ($otherTickets ? 'true;' : 'false;') . "\n";
	$pricingElements = $theatreDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_PRICING);
	for ($i = 0; $i < $pricingElements->length; $i++)
	{
		$pricingId = $pricingElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_ID);
		$html .= 'var category_for_pricing' . $i . ' = { };' . "\n";
		$categoryElements = $pricingElements->item($i)->getElementsByTagName(SUMAC_ELEMENT_CATEGORY);
		$categoryCodes = array();
		for ($j = 0; $j < $categoryElements->length; $j++)
		{
			$onsale = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_ONSALE);
			if (($onsale != null) && ($onsale != SUMAC_VALUE_TRUE)) continue;	//we only want the ones you can buy
			$letterCode = null;
			$withRequirement = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_WITH_REQUIREMENT);
			if (($withRequirement == null) || ($withRequirement == ''))
			{
				$letterCode = $_SESSION[SUMAC_STR]["TU2"];
				$text = $_SESSION[SUMAC_STR]["TU1"];
				$withRequirement = '0';
			}
			else
			{
				$requirementElement = $theatreDocument->getElementById($withRequirement);
				$letterCode = strtoupper($requirementElement->getAttribute(SUMAC_ATTRIBUTE_LETTER_CODE));
				$text = ($requirementElement->childNodes->item(0) != null) ? $requirementElement->childNodes->item(0)->nodeValue : '';
			}
			$html .= 'category_for_pricing' . $i . '["' . $letterCode . '"] = "' . $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE) . '";' . "\n";
			//$html .= 'category_for_pricing' . $i . '["' . $withRequirement . '"] = "' . $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE) . '";' . "\n";
			$categoryCodes[] = $letterCode;
			if (count($categoryCodes) == 1) $html .= 'sumac_suggested_category_for_pricing["' . $pricingId . '"] = "' . $letterCode . '";' . "\n";
		}
		$html .= 'sumac_categories_for_pricings["' . $pricingId . '"] = category_for_pricing' . $i . ';' . "\n";
		if (count($categoryCodes) > 1)
		{
			$choices = implode(' or ', $categoryCodes);
			$html .= 'sumac_category_queries_for_pricings["' . $pricingId . '"] = "' .
							$_SESSION[SUMAC_STR]["TJ2"] . $choices .$_SESSION[SUMAC_STR]["TJ1"] . '";' . "\n";
		}
	}

//added for seatingplan implementation

	$html .= 'var sumac_seat_ids = new Array();' . "\n";
	$html .= 'var sumac_hidden_seat_ids = new Array();' . "\n";
	$html .= 'var sumac_seat_ids_ltor = new Array();' . "\n";
	$html .= 'var sumac_seat_set_size = new Array();' . "\n";
	$html .= 'var sumac_seat_set_requested = 1;' . "\n";
	$html .= 'var sumac_text_ticketing_individual_seats = "' . $_SESSION[SUMAC_STR]["TU14"] . '";' . "\n";
	$html .= 'var sumac_text_ticketing_sets_of_seats = "' . $_SESSION[SUMAC_STR]["TU13"] . '";' . "\n";
	$html .= 'var sumac_text_click_to_cancel = "' . $_SESSION[SUMAC_STR]["TT6"] . '";' . "\n";
	$html .= 'var sumac_text_added_to_order = "' . $_SESSION[SUMAC_STR]["TT7"] . '";' . "\n";
	$html .= 'var sumac_text_available = "' . $_SESSION[SUMAC_STR]["TT8"] . '";' . "\n";
	$html .= 'var sumac_text_none_available = "' . $_SESSION[SUMAC_STR]["TT9"] . '";' . "\n";

	$html .= 'var sumac_groups_left_to_right = [';
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_BLOCKS_LTOR]); $i++)
	{
		$html .= (($i > 0) ? ',' : '') . '"' . $_SESSION[SUMAC_SESSION_BLOCKS_LTOR][$i] . '"';
	}
	$html .= '];' . "\n";
	$html .= 'var sumac_detail_for_groups = ["ALL.htm"';
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_BLOCKS_DETAILS]); $i++)
	{
		$html .= ',"' . $_SESSION[SUMAC_SESSION_BLOCKS_DETAILS][$i] . '"';
	}
	$html .= '];' . "\n";
	$html .= 'var sumac_pricings_low_to_high = [';
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]); $i++)
	{
		$html .= (($i > 0) ? ',' : '') . '"' . $_SESSION[SUMAC_SESSION_SEAT_PRICINGS][$i] . '"';
	}
	$html .= '];' . "\n";
	$html .= 'var sumac_grades_high_to_low = [';
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_GRADES]); $i++)
	{
		$html .= (($i > 0) ? ',' : '') . '"' . $_SESSION[SUMAC_SESSION_SEAT_GRADES][$i] . '"';
	}
	$html .= '];' . "\n";

	return $html;
}

function sumac_getTicketPicksHTMLScriptFunctions()
{
	return <<<EOJS

var sumac_seats_in_order_basket = new Array();
var sumac_last_category_picked = '';

	function sumac_blockPick(increase,su_id,cat_id,cat_code)
	{
		if (increase == 0) return;

		var available_id = sumac_id_span_block_available_prefix + su_id;
		var available = Number(document.getElementById(available_id).innerHTML);
		var new_available = available - increase;

		var spanbutton = document.getElementById(available_id);
		var spanbutton_pricing_class = spanbutton.className;
		var class_separator = spanbutton_pricing_class.indexOf(' ');
		if (class_separator > 0) spanbutton_pricing_class = spanbutton_pricing_class.substr(0,class_separator);

		var newpick = sumac_getNewPick(spanbutton,null,cat_id,cat_code);
		var entry = -1;
		var old_quantity = 0;
		for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
		{
			var p = sumac_seats_in_order_basket[i];
			if ((p.pricing_class == spanbutton_pricing_class) && (p.letter_code == newpick.letter_code))	//already picked - change
			{
				newpick = p;
				entry = i;
				old_quantity = p.quantity;
				break;
			}
		}

		if (new_available < 0)
		{
			alert("Number too large. Please re-enter.");
			var text_input_id = sumac_id_input_text_prefix + su_id + cat_id;
			document.getElementById(text_input_id).value = old_quantity;
			return;
		}

		var new_quantity = old_quantity + increase;
		if ((old_quantity == 0) && (new_quantity > 0)) sumac_seats_in_order_basket.push(newpick);
		newpick.quantity = Number(new_quantity);
		if ((new_quantity == 0) && (entry >= 0)) sumac_seats_in_order_basket.splice(entry,1);

		var text_input_id = sumac_id_input_text_prefix + su_id + cat_id;
		document.getElementById(text_input_id).value = new_quantity;
		document.getElementById(available_id).innerHTML = new_available;
		sumac_enableAdditionButtons(su_id,(new_available > 0));
		sumac_enableSubtractionButton(su_id,cat_id,(new_quantity > 0));
		sumac_updateTheatre();
	}

	function sumac_add(su_id,cat_id,cat_code)
	{
		var button = document.getElementById(sumac_id_button_add_prefix + su_id + cat_id);
		button.disabled = 'disabled';
		var increase = 1;
		sumac_blockPick(increase,su_id,cat_id,cat_code);
	}

	function sumac_subtract(su_id,cat_id,cat_code)
	{
		var button = document.getElementById(sumac_id_button_subtract_prefix + su_id + cat_id);
		button.disabled = 'disabled';
		var increase = -1;
		sumac_blockPick(increase,su_id,cat_id,cat_code);
	}

	function sumac_change(su_id,cat_id,cat_code)
	{
		var available_id = sumac_id_span_block_available_prefix + su_id;
		var spanbutton = document.getElementById(available_id);
		var spanbutton_pricing_class = spanbutton.className;
		var class_separator = spanbutton_pricing_class.indexOf(' ');
		if (class_separator > 0) spanbutton_pricing_class = spanbutton_pricing_class.substr(0,class_separator);
		var old_quantity = 0;
		for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
		{
			var p = sumac_seats_in_order_basket[i];
			if ((p.pricing_class == spanbutton_pricing_class) && (p.letter_code == cat_code))
			{
				old_quantity = p.quantity;
				break;
			}
		}
		var text_input_id = sumac_id_input_text_prefix + su_id + cat_id;
		var text_input = Number(document.getElementById(text_input_id).value);
		if (isNaN(text_input)|| (Number(text_input) < 0))
		{
			alert("Illegal value. Please re-enter.");
			document.getElementById(text_input_id).value = old_quantity;
			return;
		}
		text_input = Number(text_input);
		var increase = text_input - old_quantity;
		sumac_blockPick(increase,su_id,cat_id,cat_code);
	}

	function sumac_enableAdditionButtons(su_id,someAvailable)
	{
		var add_name = sumac_name_button_add_prefix + su_id;
		var add_buttons = document.getElementsByName(add_name);
		for (var i = 0; i < add_buttons.length; i++)
		{
			if (someAvailable) add_buttons[i].removeAttribute('disabled');
			else add_buttons[i].disabled = 'disabled';
		}
	}

	function sumac_enableSubtractionButton(su_id,cat_id,someBought)
	{
		var sub_button = document.getElementById(sumac_id_button_subtract_prefix + su_id + cat_id);
		if (someBought) sub_button.removeAttribute('disabled');
		else sub_button.disabled = 'disabled';
	}

	function sumac_pick(button_or_anchor,is_button,seat_id,letter_code)
	{
		sumac_disable(button_or_anchor);

		var pricing_class = button_or_anchor.className;
		var class_separator = pricing_class.indexOf(' ');
		if (class_separator > 0) pricing_class = pricing_class.substr(0,class_separator);
		var disabled_separator = pricing_class.lastIndexOf('_disabled');
		if (disabled_separator > 0) pricing_class = pricing_class.substring(0,disabled_separator);
		var pricing_id = sumac_pricings_for_classes[pricing_class];
		var span_id = sumac_id_span_seats_available_prefix + pricing_id;
		var seats_available = 0;
		//if (document.getElementById(span_id).innerHTML != sumac_text_no_seats_available)
		seats_available = Number(document.getElementById(span_id).innerHTML);
		var newpick = null;
		var entry = -1;
		for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
		{
			var p = sumac_seats_in_order_basket[i];
			if (p.seat_id == seat_id)
			{
				newpick = p;
				entry = i;
				break;
			}
		}
		if (newpick == null)
		{
			newpick = sumac_getNewPick(button_or_anchor,seat_id,null,letter_code);
			if (newpick == null)	//do not pick after all
			{
				sumac_reenable(button_or_anchor);
				return;
			}
			sumac_seats_in_order_basket.push(newpick);
			--seats_available;
			if (is_button) button_or_anchor.innerHTML = newpick.letter_code;
			//else button_or_anchor.innerHTML = newpick.seat_label + ' [' + newpick.letter_code + ']';
		}
		else //already picked - unpick
		{
			sumac_seats_in_order_basket.splice(entry,1);
			++seats_available;
			if (is_button) button_or_anchor.innerHTML = '';
			//else button_or_anchor.innerHTML = newpick.seat_label;
		}
		//if (seats_available > 0) document.getElementById(span_id).innerHTML = seats_available;
		//else document.getElementById(span_id).innerHTML = sumac_text_no_seats_available;
		document.getElementById(span_id).innerHTML = seats_available;
		if (is_button) sumac_reenable(button_or_anchor);	//anchors in list remain disabled
		//if (!is_button) sumac_updateSeatSets(button_or_anchor,true);
		sumac_updateTheatre();
	}

	function sumac_getNewPick(button_or_anchor,seat_id,cat_id,cat_code)
	{
		var letter_code = cat_code;
		var cents_price = null;

		var pricing_class = button_or_anchor.className;
		var class_separator = pricing_class.indexOf(' ');
		if (class_separator > 0) pricing_class = pricing_class.substr(0,class_separator);
		var disabled_separator = pricing_class.lastIndexOf('_disabled');
		if (disabled_separator > 0) pricing_class = pricing_class.substring(0,disabled_separator);
		var pricing_id = sumac_pricings_for_classes[pricing_class];
		var categories = sumac_categories_for_pricings[pricing_id];
		var category_query = sumac_category_queries_for_pricings[pricing_id];
		if ((category_query != null) && (letter_code == null))
		{
			var suggested_category = (categories[sumac_last_category_picked] != null) ? sumac_last_category_picked
																	: sumac_suggested_category_for_pricing[pricing_id];
			letter_code = prompt(category_query,suggested_category);
			if (letter_code == null) return null;
			while ((cents_price = sumac_getSeatPrice(letter_code,categories)) < 0)
			{
				letter_code = prompt(category_query,suggested_category);
				if (letter_code == null) return null;;
			}
			sumac_last_category_picked = letter_code.toUpperCase();
			letter_code = letter_code.toUpperCase();
		}
		else if (letter_code == null)
		{
			letter_code = sumac_suggested_category_for_pricing[pricing_id];
			cents_price = categories[letter_code];
		}
		else
		{
			cents_price = categories[letter_code];
		}

		var seat_label = (button_or_anchor.tagName == 'BUTTON') ? button_or_anchor.title : button_or_anchor.innerHTML;
		var p =
		{
			button_or_anchor : button_or_anchor,
			seat_id : seat_id,
			seat_label : seat_label,
			pricing_id : pricing_id,
			cat_id : cat_id,
			pricing_class : pricing_class,
			letter_code : letter_code,
			price : Number(cents_price),
			quantity : Number(1)
		}
		return p;
	}

	function sumac_updateTheatre()
	{
		//var tickets_in_basket = '<td>' + sumac_label_seats_picked + '</td>';
		var tickets_list = document.getElementById(sumac_id_tr_picked_list);	//that gets us the <tr> element
		var td1 = tickets_list.firstChild;	//that's the first <td>, the one that says something like "Seats picked:"
		var nexttd = td1.nextSibling;	//now we want to get rid of all the rest (if there are any), i.e. ticket + ticket + ...
		var span1 = td1.firstChild.nextSibling.nextSibling;	//that's the first <span>, past the break
		var nextspan = span1.nextSibling;	//now we want to get rid of all the rest (if there are any), i.e. ticket + ticket + ...
		while (nextspan != null)
		{
			var child_to_delete = nextspan;
			nextspan = nextspan.nextSibling;
			var removed = td1.removeChild(child_to_delete);
		}
		var newspan = null;

		var totalcents = 0;
		var thesetickets = 0;
		if (sumac_seats_in_order_basket.length == 0)
		{
			//tickets_in_basket = tickets_in_basket + '<td>' + sumac_label_basket_is_empty + '</td>';
			newspan = span1.cloneNode('false');
			newspan.innerHTML = sumac_label_basket_is_empty;
			td1.appendChild(newspan);
		}
		else
		{
			for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
			{
				var p = sumac_seats_in_order_basket[i];
				var seatcents = p.price * p.quantity;
				totalcents += seatcents;
				thesetickets += p.quantity;
				if (i > 0)
				{
					//tickets_in_basket  = tickets_in_basket + '<td>+</td>';
					newspan = span1.cloneNode('false');
					newspan.innerHTML = ' + ';
					td1.appendChild(newspan);
				}
				if (p.seat_id != null)
				{
					//tickets_in_basket = tickets_in_basket + '<td class="' + sumac_class_prefix_ticket + p.pricing_id + '">'
					//						+ p.seat_label + '<sup>[' + p.letter_code + ']</sup>' + '</td>';
					newspan = span1.cloneNode('false');
					//newspan.innerHTML = p.seat_label + '<sup>[' + p.letter_code + ']</sup>';
					newspan.innerHTML = p.seat_label + '[' + p.letter_code + ']';
					newspan.className = sumac_class_prefix_ticket + p.pricing_id;
					newspan.title = sumac_centsToPrintableDollars(seatcents);
					var newspanchild = newspan.firstChild;
					while (newspanchild != null)
					{
						var nextspanchild = newspanchild.nextSibling;
						if (newspanchild.tagName == 'BR') newspan.removeChild(newspanchild);
						newspanchild = nextspanchild;
					}
					td1.appendChild(newspan);
					var xbutton = document.createElement('button');
					xbutton.className = "xdelete";
					xbutton.type = "button";
					xbutton.value = p.seat_id;
					xbutton.title = sumac_text_click_to_cancel + newspan.innerHTML;
					xbutton.onclick = function () { sumac_unpick(this); };
					var ximg = document.createElement('img');
					ximg.src = 'smalldelete.ico';
					ximg.className = "xdelete";
					xbutton.appendChild(ximg);
					td1.appendChild(xbutton);
				}
				else
				{
					//tickets_in_basket = tickets_in_basket + '<td class="' + p.pricing_class + '">'
					//					+ p.quantity + ((p.quantity > 1) ? sumac_label_seats_sold : sumac_label_seat_sold) + '<sup>[' + p.letter_code + ']</sup>' + '</td>';
					newspan = span1.cloneNode('false');
					//newspan.innerHTML = p.quantity + ((p.quantity > 1) ? sumac_label_seats_sold : sumac_label_seat_sold) + '<sup>[' + p.letter_code + ']</sup>';
					newspan.innerHTML = p.quantity + ((p.quantity > 1) ? sumac_label_seats_sold : sumac_label_seat_sold) + '[' + p.letter_code + ']';
					newspan.className = p.pricing_class;
					td1.appendChild(newspan);
				}
			}
		}
		var totalcost = sumac_centsToPrintableDollars(totalcents);
		//document.getElementById(sumac_id_tr_picked_list).innerHTML = tickets_in_basket;
		document.getElementById(sumac_id_span_total_cost).innerHTML = totalcost;

		//version 2.0 of OTS uses nav bar links that use HTTP GET operations rather than POSTs
		var ticket_count = sumac_tickets_for_other_events + thesetickets;
		var links = document.getElementsByTagName('a');
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if ((link.className.indexOf('sumac_action_link_checkout') >= 0)
				|| (link.className.indexOf('sumac_action_link_restart') >= 0)
				|| (link.className.indexOf('sumac_action_link_revisit') >= 0))
			{
				var href = link.getAttribute('href');
				if ((href == null) && (ticket_count > 0))
				{
					var nothref = link.getAttribute('nothref');
					link.setAttribute('href',nothref);
					var navClass = link.className.replace('sumac_disabled_navlink','sumac_navlink');
					link.className = navClass;
				}
				else if ((href != null) && (ticket_count == 0))
				{
					link.removeAttribute('href');
					var navClass = link.className.replace('sumac_navlink','sumac_disabled_navlink');
					link.className = navClass;
				}
			}
		}
		//if (sumac_seats_in_order_basket.length > 0)
		//{
		//	document.getElementById(sumac_id_button_order_tickets).removeAttribute('disabled');
		//	//document.getElementById(sumac_id_button_order_tickets).focus();
		//}
		//else
		//{
		//	document.getElementById(sumac_id_button_order_tickets).disabled = 'disabled';
		//}
		sumac_updateBuyables();
	}

	function sumac_updateBuyables()
	{
		var basketlist = sumac_event_id + '=';
		for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
		{
			var p = sumac_seats_in_order_basket[i];
			//if (i > 0) basketlist = basketlist + '+';
			//Version 2, using HTTP GET means that the plus sign will be converted to a space - I want the plus
			if (i > 0) basketlist = basketlist + '%2B';
			if (p.seat_id != null)
			{
				basketlist = basketlist + p.seat_id + ',' + p.seat_label + ',' + p.pricing_id + ',' +
											p.letter_code + ',' + p.price + ',' + '1,0';
			}
			else
			{
				basketlist = basketlist + '' + ',' + p.pricing_class + ',' + p.pricing_id + ',' +
											p.letter_code + ',' + p.price + ',' +
											p.quantity + ',' + p.cat_id;
			}
		}
		//alert(basketlist);
		//alert("Browser CodeName: " + navigator.appCodeName);
		//document.getElementById(sumac_id_basket_of_tickets).setAttribute('value',basketlist);
		//version 2.0 of OTS uses nav bar links that use HTTP GET operations rather than POSTs

		var links = document.getElementsByTagName('a');
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if ((link.className.indexOf('sumac_action_link_checkout') >= 0)
				|| (link.className.indexOf('sumac_action_link_event') >= 0))
			{
				var href = link.getAttribute('href');
				if (href != null)
				{
					var firstAmpersand = href.indexOf('&');
					if (firstAmpersand > 0) href = href.substr(0,firstAmpersand);
					href = href + '&basket=' + basketlist;
					link.setAttribute('href',href);
				}
			}
			if (link.className.indexOf('sumac_action_link_revisit') >= 0)
			{
				var href = link.getAttribute('href');
				if (href != null)
				{
					var firstAmpersand = href.indexOf('&');
					var secondAmpersand = (firstAmpersand > 0) ? href.substr(firstAmpersand+1).indexOf('&') : 0;
					if (secondAmpersand > 0) href = href.substr(0,firstAmpersand + 1 + secondAmpersand);
					href = href + '&basket=' + basketlist;
					link.setAttribute('href',href);
				}
			}
		}
	}

	function sumac_getSeatPrice(pc,validCategories)
	{
		var cents_price = validCategories[pc.toUpperCase()];
		return (cents_price == null) ? -1 : cents_price;
	}

	function sumac_recreateAnchorPick(seat_id,su_id,seat_label,pricing_id,letter_code,cents_price,quantity,cat_id)
	{
		var anchors = document.getElementById('sumac_hidden_seats').childNodes;	//assumed to be all and only seat-anchors
		for (var i = 0; i < anchors.length; i++)
		{
			var href = anchors[i].href;
			if ((href == null) || (href.length == 0)) continue;
			var lastslash = href.lastIndexOf('/');
			if (lastslash >= 0) href = href.substr(lastslash + 1);
			var value = href.substring('add_'.length,href.indexOf('_to_order'));
			if (value == seat_id)
			{
				sumac_recreatePick(anchors[i],seat_id,su_id,seat_label,
										pricing_id,letter_code,cents_price,quantity,cat_id);
				sumac_disable(anchors[i]);	//mark seat in list as already added to order
				break;
			}
		}
	}

	function sumac_recreateButtonPick(seat_id,su_id,seat_label,pricing_id,letter_code,cents_price,quantity,cat_id)
	{
		var button = null;
		if (seat_id == null)
		{
			var available_id = sumac_id_span_block_available_prefix + su_id;
			button = document.getElementById(available_id);
		}
		else
		{
			button = document.getElementById(sumac_id_button_prefix + seat_id);
		}
		sumac_recreatePick(button,seat_id,su_id,seat_label,
								pricing_id,letter_code,cents_price,quantity,cat_id);
		if (seat_id != null) button.innerHTML = letter_code;	//mark seat in stage as already added to order
	}

	function sumac_recreatePick(button_or_anchor,seat_id,su_id,seat_label,pricing_id,letter_code,cents_price,quantity,cat_id)
	{
		if (seat_id == null)
		{
			var available_id = sumac_id_span_block_available_prefix + su_id;
			var seats_available = Number(document.getElementById(available_id).innerHTML);
			seats_available = seats_available - quantity;
			document.getElementById(available_id).innerHTML = seats_available;
			var text_input_id = sumac_id_input_text_prefix + su_id + cat_id;
			document.getElementById(text_input_id).value = quantity;
			sumac_enableAdditionButtons(su_id,(seats_available > 0));
			sumac_enableSubtractionButton(su_id,cat_id,(quantity > 0));
		}
		else
		{
			var span_id = sumac_id_span_seats_available_prefix + pricing_id;
			var seats_available = 0;
			//if (document.getElementById(span_id).innerHTML != sumac_text_no_seats_available)
			seats_available = Number(document.getElementById(span_id).innerHTML);
			--seats_available;
			//if (seats_available != 0) document.getElementById(span_id).innerHTML = seats_available;
			//else document.getElementById(span_id).innerHTML = sumac_text_no_seats_available;
			document.getElementById(span_id).innerHTML = seats_available;
		}

		var pricing_class = button_or_anchor.className;
		var class_separator = pricing_class.indexOf(' ');
		if (class_separator > 0) pricing_class = pricing_class.substr(0,class_separator);
		var disabled_separator = pricing_class.lastIndexOf('_disabled');
		if (disabled_separator > 0) pricing_class = pricing_class.substring(0,disabled_separator);
		var p =
		{
			button_or_anchor : button_or_anchor,
			seat_id : seat_id,
			seat_label : seat_label,
			pricing_id : pricing_id,
			cat_id : cat_id,
			pricing_class : pricing_class,
			letter_code : letter_code,
			price : Number(cents_price),
			quantity : Number(quantity)
		}
		sumac_seats_in_order_basket.push(p);
	}

	function sumac_pwa(event,anchor)
	{
		var href = anchor.href;
		if ((href == null) || (href.length == 0)) return;
		var lastslash = href.lastIndexOf('/');
		if (lastslash >= 0) href = href.substr(lastslash + 1);
		var seat_id = href.substring('add_'.length,href.indexOf('_to_order'));

		if (document.getElementById('sumac_choose_singles').checked)
		{
			sumac_pick(anchor,false,seat_id,null);
		}
		else
		{
			//picked anchor identifies the leftmost seat
			var spot = 0;
			var highspot = sumac_seat_ids_ltor.length
			for (var id = anchor.id ; spot < highspot; spot++) if (id == sumac_seat_ids_ltor[spot]) break;
			if (spot >= highspot) { alert(anchor.id+' not in ltor'); return; } //error??
			for (var i = 0; i < sumac_seat_set_requested; i++)
			{
				var seat = document.getElementById(sumac_seat_ids_ltor[spot]);
				href = seat.href;
				if ((href == null) || (href.length == 0)) continue;
				lastslash = href.lastIndexOf('/');
				if (lastslash >= 0) href = href.substr(lastslash + 1);
				seat_id = href.substring('add_'.length,href.indexOf('_to_order'));
				//var letter_code = '*'; //anyone
				//sumac_pick(seat,false,seat_id,letter_code);
				sumac_pick(seat,false,seat_id,null);
				spot++;
			}
		}
		if (event != null) event.preventDefault();	//do NOT go off to href link
	}

	function sumac_pwb(button) //originally plain sumac_pick
	{
		sumac_pick(button,true,button.id.substr(sumac_id_button_prefix.length),null);
	}

	function sumac_reenable(button_or_anchor)
	{
		if (button_or_anchor.tagName == 'BUTTON')
		{
			button_or_anchor.removeAttribute('disabled');
		}
		else	//anchors get their hrefs restored from their titles and get a change of class
		{
			var suffix = button_or_anchor.title.lastIndexOf(sumac_text_added_to_order);
			var value = button_or_anchor.title.substring(0,suffix);
			button_or_anchor.href = 'add_' + value + '_to_order';
			button_or_anchor.removeAttribute('title');
			var disabled_suffix = button_or_anchor.className.lastIndexOf('_disabled');
			if (disabled_suffix > 0) button_or_anchor.className = button_or_anchor.className.substring(0,disabled_suffix);
		}
	}

	function sumac_disable(button_or_anchor)
	{
		if (button_or_anchor.tagName == 'BUTTON') //buttons are simply set disabled
		{
			button_or_anchor.disabled = 'disabled';
		}
		else	//anchors lose their hrefs and get a change of class
		{
			var href = button_or_anchor.href;
			var lastslash = href.lastIndexOf('/');
			if (lastslash >= 0) href = href.substr(lastslash + 1);
			var value = href.substring('add_'.length,href.indexOf('_to_order'));
			button_or_anchor.removeAttribute('href');
			button_or_anchor.title = value + sumac_text_added_to_order;
			button_or_anchor.className = button_or_anchor.className + '_disabled';
		}
	}

	function sumac_is_disabled(button_or_anchor)
	{
		if (button_or_anchor.tagName == 'BUTTON') return button_or_anchor.disabled;
		else return (button_or_anchor.className.lastIndexOf('_disabled') > 0);
	}

	function sumac_unpick(xbutton)
	{
		var seat_id = xbutton.value;
		for (var i = 0; i < sumac_seats_in_order_basket.length; i++)
		{
			var p = sumac_seats_in_order_basket[i];
			if (p.seat_id == seat_id)
			{
				if (p.button_or_anchor.tagName == 'BUTTON')
				{
					p.button_or_anchor.innerHTML = '';
				}
				else
				{
					p.button_or_anchor.innerHTML = p.seat_label;
				}
				sumac_reenable(p.button_or_anchor);	//though the button was not disabled
				sumac_seats_in_order_basket.splice(i,1);
				var pricing_class = p.button_or_anchor.className;
				var class_separator = pricing_class.indexOf(' ');
				if (class_separator > 0) pricing_class = pricing_class.substr(0,class_separator);
				var disabled_separator = pricing_class.lastIndexOf('_disabled');
				if (disabled_separator > 0) pricing_class = pricing_class.substring(0,disabled_separator);
				var pricing_id = sumac_pricings_for_classes[pricing_class];
				var span_id = sumac_id_span_seats_available_prefix + pricing_id;
				var seats_available = 0;
				seats_available = Number(document.getElementById(span_id).innerHTML);
				++seats_available;
				document.getElementById(span_id).innerHTML = seats_available;
				sumac_updateTheatre();
				visible = document.getElementById('sumac_visible_seat_selector');
				//if ((p.button_or_anchor.parentNode != null)
				//	&& (p.button_or_anchor.parentNode.parentNode != null)
				//	&& (p.button_or_anchor.parentNode.parentNode.id == visible.id))
				//		visible.firstChild.focus();
				//sumac_updateSeatSets(p.button_or_anchor,false);
				break;
			}
		}
	}

	function sumac_updateSeatSets(anchor,blocked)
	{
		var spot = 0;
		var highspot = sumac_seat_ids_ltor.length
		for (var id = anchor.id ; spot < highspot; spot++) if (id == sumac_seat_ids_ltor[spot]) break;
		if (spot >= highspot) return; //error??
		var x = Number(sumac_seat_set_size[spot]);
//alert(sumac_seat_ids_ltor[spot] + ' x=' + sumac_seat_set_size[spot]);
		if (blocked)
		{
			for (--spot; (spot >= 0) && (sumac_seat_set_size[spot] > x); spot--)
			{
				sumac_seat_set_size[spot] -= x;
//alert(sumac_seat_ids_ltor[spot] + ' sizedownto ' + sumac_seat_set_size[spot]);
				if (sumac_is_disabled(document.getElementById(sumac_seat_ids_ltor[spot]))) break;
			}
		}
		else //ticket returned
		{
			spot--;
			if (spot > 0) sumac_seat_set_size[spot] += x;
			if (!sumac_is_disabled(document.getElementById(sumac_seat_ids_ltor[spot])))
			{
				for (--spot; (spot >= 0) && (sumac_seat_set_size[spot] > 1); spot--)
				{
					sumac_seat_set_size[spot] += x;
//alert(document.getElementById(sumac_seat_ids_ltor[spot]).value + ' sizebackto' + sumac_seat_set_size[spot]);
					if (sumac_is_disabled(document.getElementById(sumac_seat_ids_ltor[spot]))) break;
				}
			}
		}
		sumac_set_unusable_seats(sumac_number_of_seats_in_party);
	}

	function sumac_select_list_and_detail_from_map(event,area)
	{
		var select = document.getElementById('sumac_seat_block_selector');
		var options = select.options;
		select.selectedIndex = 0;					//default to 'all'
		for (var i = 0; i < options.length; i++)
		{
			if (options[i].value == area.alt) { select.selectedIndex = i; break; }
		}
		sumac_select_list_and_detail();
		if (event != null) event.preventDefault();	//do NOT go off to href link
	}

	function sumac_select_list_and_detail()
	{
		var select = document.getElementById('sumac_seat_block_selector');
		var block = select.options[select.selectedIndex].value;
		if (select.selectedIndex == 0) block = '<>';
		sumac_show_seat_block(block);
		sumac_set_seat_selector();

		var iframe = document.getElementById('plandetail');
		if (iframe != null)
		{
			var detailfile = sumac_detail_for_groups[select.selectedIndex];
			if (detailfile == '') fulldetailfile = 'nodetail.htm';
			else fulldetailfile = detailfile;
			iframe.src = fulldetailfile;
		}
	}

	function sumac_show_seat_block(block)
	{
		var visible = document.getElementById('sumac_visible_seat_selector');
		sumac_hide_all_visible_seats();

		var ncol = Number(document.getElementById('sumac_seat_block_selector').name);	//current sort order

		//now transfer the seats in the selected block (or all blocks) to the visible selector
		if (block == '')
		{
			//do nothing
		}
		else if (block != '<>')
		{
			var blockindex = 0;
			for (var i = 0; i < sumac_groups_left_to_right.length; i++)
			{
				if (sumac_groups_left_to_right[i] == block)
				{
					blockindex = i;
					break;
				}
			}
			visible.appendChild(sumac_get_group_from_hidden(blockindex,0));
		}
		else	//do all
		{
			var groupcount = sumac_groups_left_to_right.length; // in original order, grouped in blocks
			if (ncol == 4) groupcount = sumac_pricings_low_to_high.length;	// in price order
			else if (ncol == 5) groupcount = sumac_grades_high_to_low.length;	// in 'quality' order
			for (var i = 0; i < groupcount; i++)
			{
				visible.appendChild(sumac_get_group_from_hidden(i,(ncol - 3)));
			}
		}
	}

	function sumac_set_seat_selector()
	{
		var visible = document.getElementById('sumac_visible_seat_selector');
		var available = visible.getElementsByTagName('A').length;		//seats are HTML anchors/links
//		var select = document.getElementById('sumac_seat_block_selector');
//		if (select.selectedIndex == 0) document.getElementById('sumac_seat_selector_label').innerHTML = available + ' available';
		if (available > 0) document.getElementById('sumac_seat_selector_label').innerHTML = available + sumac_text_available;
		else document.getElementById('sumac_seat_selector_label').innerHTML = sumac_text_none_available;
		if (available > 0) visible.getElementsByTagName('A')[0].focus();	//go back to the top
	}

	function sumac_hide_all_visible_seats()
	{
	//first transfer any seats from any visible groups back to the hidden selector
		var hidden = document.getElementById('sumac_hidden_seats');
		var ncol = Number(document.getElementById('sumac_seat_block_selector').name);	//current sort order
		var visible = document.getElementById('sumac_visible_seat_selector');
		var vgroups = visible.childNodes;	//visible seats are ALWAYS in subgroup divisions
		while (vgroups.length > 0)
		{
			var group =visible.firstChild;
			var seats = group.childNodes;
			var seat = group.firstChild;
			while (seat != null)
			{
				var nextseat = seat.nextSibling;
				if ((seat.id != null) && (seat.id.indexOf('sumac_seat_') == 0)) //if there is anything that is not a seat, skip it
				{
					sumac_hide_one_seat(seat,ncol);
				}
				seat = nextseat;
			}
			//the group now has no seats and is only the subgroup holder (and rubbish perhaps)
			//so put the subgroup holder back in storage
			document.getElementById('sumac_hidden_group_holder').appendChild(visible.removeChild(group));
		}
	}

	function sumac_hide_one_seat(seat,ncol)
	{
		var hidden = document.getElementById('sumac_hidden_seats');
		var seatcount = sumac_hidden_seat_ids.length;
		var spot = Number(sumac_get_id_sortkey(seat.id,ncol));

		var nextspot = spot + 1; //the one to insert before
		while ((nextspot < seatcount) && (sumac_hidden_seat_ids[nextspot] == 0)) nextspot++;
		if (nextspot >= seatcount) hidden.appendChild(seat.parentNode.removeChild(seat));
		else
		{
//			var nextparentid = document.getElementById(sumac_seat_ids[nextspot]).parentNode.id;
//			if (nextparentid != hidden.id) alert('seat ' + sumac_seat_ids[nextspot] + ' at ' + nextspot + ' belongs to ' + nextparentid);
			hidden.insertBefore(seat.parentNode.removeChild(seat),document.getElementById(sumac_seat_ids[nextspot]));
		}
		sumac_hidden_seat_ids[spot] = 1;	//flag seat as being present in 'hidden'
	}

	function sumac_show_one_seat(seat,ncol,visiblegroup)
	{
		var spot = Number(sumac_get_id_sortkey(seat.id,ncol));
		if (spot == (sumac_seat_ids.length - 1))
		{
			visiblegroup.appendChild(seat.parentNode.removeChild(seat));
		}
		else
		{
			var nextspot = spot + 1;
			while ((nextspot < sumac_seat_ids.length)
				&& (document.getElementById(sumac_seat_ids[nextspot]).parentNode.id != visiblegroup.id)) nextspot++;
			if (nextspot < sumac_seat_ids.length) visiblegroup.insertBefore(seat.parentNode.removeChild(seat),document.getElementById(sumac_seat_ids[nextspot]));
			else visiblegroup.appendChild(seat.parentNode.removeChild(seat));
		}
	}

	function sumac_get_group_from_hidden(index,column)
	{
	//NOTE: 'column' is the key to the group element in the seat id.
	//It USED TO BE 0 for price, 1 for weight/quality, or 2 for block
	//It is 0 for block, 1 for price, or 2 for weight/quality
	//NOTE: 'index' USED TO BE the selector value but now it is only the index of that selector in its array
		var groupholder = document.getElementById('sumac_hidden_group_holder');
		var group = document.getElementById('sumac_og' + column + '_' + index);
		if (group == null) group = document.getElementById('sumac_ogxxx');
		var newgroup = groupholder.removeChild(group);

		var hidden = document.getElementById('sumac_hidden_seats');
		var ncol = Number(document.getElementById('sumac_seat_block_selector').name);	//current sort order
		var hseats = hidden.childNodes;	//assumed to be all and only seat-anchors
		if (hseats.length < 1) return newgroup;
		var hseat = hidden.firstChild;
		while (hseat != null)
		{
			var nextseat = hseat.nextSibling;
			if ((hseat.id) && (sumac_get_id_sortkey(hseat.id,column) == index))
			{
				newgroup.appendChild(hidden.removeChild(hseat));
				var spot = Number(sumac_get_id_sortkey(hseat.id,ncol));
				sumac_hidden_seat_ids[spot] = 0;	//flag seat as no longer being present in 'hidden'
			}
			hseat = nextseat;
		}
		return newgroup;
	}

	function sumac_sort_seat_list(event,column)
	{
	//column use: 0-2 invalid; 3=original(rear-to-front,left-to-right); 4=price(low-to-high); 5=quality(high-to-low)
		sumac_hide_all_visible_seats();
		var ncol = Number(column);
		var hidden = document.getElementById('sumac_hidden_seats');
		var seats = hidden.getElementsByTagName('A');				//seats are HTML anchors/links
		for (var i = 0; i < sumac_hidden_seat_ids.length; i++) sumac_hidden_seat_ids[i] = 0; // all seats will be removed from 'hidden'
		var ids = new Array();
		for (var i = 0; i < seats.length; i++)
		{
			var seat = seats[i];
			var id = seat.id;
			var spot = Number(sumac_get_id_sortkey(id,ncol));
			ids[spot] = id;
			sumac_hidden_seat_ids[spot] = 1;	//flag seat as going to be present in 'hidden' in new position
		}
		//and if there are any unusable seats (because sets of seats are being selected) they must be included in the id array too
		var unusables = document.getElementById('sumac_unusable_seats').getElementsByTagName('A');
		for (var i = 0; i < unusables.length; i++)
		{
			var seat = unusables[i];
			var id = seat.id;
			var spot = Number(sumac_get_id_sortkey(id,ncol));
			ids[spot] = id;
		}

		//now snip each hidden seat-anchor out in order, and add it back in at the end
		for (var i = 0; i < ids.length; i++)
		{
			var s = document.getElementById(ids[i]);
			if (s.parentNode.id == hidden.id) hidden.appendChild(hidden.removeChild(s)); //only the ones from hidden
		}
		document.getElementById('sumac_seat_block_selector').name = ncol;
		sumac_seat_ids = ids;				//set the new id order where it can be used
		sumac_select_list_and_detail();		//make the right block (or all) visible

		if (event != null) event.preventDefault();	//do NOT go off to href link
	}

	function sumac_get_id_sortkey(id,col)
	{
		var kp = 'sumac_seat_'.length;
		for (var i = 0; i < 6; i++)		//col must not exceed 5
		{
			var nextus = id.indexOf('_',kp);
			if (nextus < 0) nextus = id.length;
			//alert('id=' + id + ', kp=' + kp + ', nextus=' + nextus +  ', key=' + id.substring(kp,nextus + '.'));
			if (col == i) return id.substring(kp,nextus);
			kp = nextus + 1;
		}
		return '?';
	}

	function sumac_individual_seats()
	{
		document.getElementById('sumac_together_count').disabled = 'disabled';
		document.getElementById('sumac_together_count').value = '';
		sumac_set_unusable_seats(1);
	}

	function sumac_seats_together()
	{
		document.getElementById('sumac_together_count').removeAttribute('disabled');
		document.getElementById('sumac_together_count').value = '2';
		sumac_set_unusable_seats(2);
	}

	function sumac_set_unusable_seats(setsize)
	{
//alert('sumac_set_unusable_seats('+setsize+')');
		if (isNaN(setsize) || (setsize < 1) || (setsize > 9)) return;
		sumac_seat_set_requested = setsize;

		if (setsize == 1) document.getElementById('sumac_seat_set_text').innerHTML = sumac_text_ticketing_individual_seats;
		else document.getElementById('sumac_seat_set_text').innerHTML = sumac_formatMessage(sumac_text_ticketing_sets_of_seats,setsize);

		var ncol = Number(document.getElementById('sumac_seat_block_selector').name);	//current sort order
		var kcol = 0;	//default seat-anchor grouping (by block)
		if (document.getElementById('sumac_seat_block_selector').selectedIndex == 0) kcol = ncol - 3;
	//alert('kcol='+kcol);
	//alert('first visible group is ' + document.getElementById('sumac_visible_seat_selector').firstChild.id);
		for (var i = 0; i < sumac_seat_ids_ltor.length; i++)
		{
			var seat = document.getElementById(sumac_seat_ids_ltor[i]);
			var visiblegroup = document.getElementById('sumac_og' + String(kcol) + '_' + sumac_get_id_sortkey(seat.id,kcol));
	//alert(visiblegroup.id + ' is group that ' + seat.value + ' belongs in');
			var parentid = seat.parentNode.id;
			if (sumac_seat_set_size[i] >= setsize) //usable
			{
				if (parentid == 'sumac_unusable_seats')
				{
					if (visiblegroup.parentNode.id == 'sumac_visible_seat_selector')	//it should be visible
					{
						sumac_show_one_seat(seat,ncol,visiblegroup);
					}
					else //it should be hidden
					{
						sumac_hide_one_seat(seat,ncol);
					}
				}
				//otherwise the seat is already set as usable
			}
			else //unusable
			{
				if (parentid != 'sumac_unusable_seats')
				{
					if (parentid == 'sumac_hidden_seats')
					{
						var spot = Number(sumac_get_id_sortkey(seat.id,ncol));
						sumac_hidden_seat_ids[spot] = 0;	//flag seat as no longer being present in 'hidden'
					}
					document.getElementById('sumac_unusable_seats').appendChild(seat.parentNode.removeChild(seat));
	//alert(seat.href + ' being set unusable because ' + sumac_seat_set_size[i] + '>=' + setsize);
				}
				//otherwise the seat is already set as unusable
			}
		}
		sumac_set_seat_selector();
	}
EOJS;
}

function sumac_getHTMLScriptToSetPreviousPicksFromBasket($usingStage)
{
	$html = '';
	foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET][$_SESSION[SUMAC_SESSION_TICKETING_EVENT]] as $tid => $t)
	{
		if ($t['quantity'] == 'seat')
		{
			if ($usingStage)
			{
				$html .= 'sumac_recreateButtonPick("' .$tid . '",null,"' . $t['label'] . '","' . $t['pricing'] . '","' .
							$t['letter'] . '","' . $t['price'] . '",1,"' . $t['category'] . '");' . "\n";
			}
			else
			{
				$html .= 'sumac_recreateAnchorPick("' .$tid . '",null,"' . $t['label'] . '","' . $t['pricing'] . '","' .
							$t['letter'] . '","' . $t['price'] . '",1,"' . $t['category'] . '");' . "\n";
			}
			$html .= 'sumac_tickets_for_other_events--;' . "\n";
		}
		else
		{
			$html .= 'sumac_recreateButtonPick(null,"' . $t['sellingUnit'] . '",null,"' . $t['pricing'] . '","' .
						$t['letter'] . '","' . $t['price'] . '","' .
						$t['quantity'] . '","' . $t['category'] . '");' . "\n";
			$html .= 'sumac_tickets_for_other_events = sumac_tickets_for_other_events - Number(' . $t['quantity'] . ');' . "\n";
		}
	}
	$html .= 'sumac_updateTheatre();' . "\n";
	return $html;
}

//******************************************
//methods for building the html Body elements
//******************************************

include_once 'sumac_pick_tickets_layout_stage.php';	//sumac_getHTMLBodyForStageFromLayout() etc
include_once 'sumac_pick_tickets_seating_plan.php';	//sumac_getSeatingPlanHTML() etc

function sumac_getSeatDetails($seatElement)
{
	if ($seatElement != null)
	{
		$seatId = $seatElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		$seatsalesSeatElement = $_SESSION[SUMAC_SESSION_SEATSALES_DOC]->getElementById($seatId);
		$isSoldSeat = false;
		if ($seatsalesSeatElement != null) $isSoldSeat = ($seatsalesSeatElement->getAttribute(SUMAC_ATTRIBUTE_SOLD) != SUMAC_VALUE_FALSE);
		$seatLabel = $seatElement->getAttribute(SUMAC_ATTRIBUTE_LABEL);
		$seatPricedAt = $seatElement->getAttribute(SUMAC_ATTRIBUTE_PRICED_AT);
		$seatWeight = $seatElement->getAttribute(SUMAC_ATTRIBUTE_WEIGHT);
		$isOccupiableSeat = ($seatElement->getAttribute(SUMAC_ATTRIBUTE_OCCUPIABLE) != SUMAC_VALUE_FALSE);
		return array(
				SUMAC_ATTRIBUTE_ID => $seatId,
				SUMAC_ATTRIBUTE_SOLD => $isSoldSeat,
				SUMAC_ATTRIBUTE_LABEL => $seatLabel,
				SUMAC_ATTRIBUTE_PRICED_AT => $seatPricedAt,
				SUMAC_ATTRIBUTE_WEIGHT => $seatWeight,
				SUMAC_ATTRIBUTE_OCCUPIABLE => $isOccupiableSeat
				);
	}
	else
	{
		return array(
				SUMAC_ATTRIBUTE_ID => null,
				SUMAC_ATTRIBUTE_SOLD => false,
				SUMAC_ATTRIBUTE_LABEL => null,
				SUMAC_ATTRIBUTE_PRICED_AT => null,
				SUMAC_ATTRIBUTE_WEIGHT => null,
				SUMAC_ATTRIBUTE_OCCUPIABLE => true
				);
	}
}

function sumac_getHTMLBodyForLegendFromLayout($theatreDocument,$seatsalesDocument,$layoutElement)
{
	$html = '<div id="' . SUMAC_ID_DIV_LEGEND . '" class="sumac_maintable">' . "\n";
	$html .= '<table border="0" width="100%">' . "\n";

	$individualSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES]) > 0);
	$unassignedSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES]) > 0);

	if ($individualSeating)	$html .= sumac_getIndividualSeatCostHTMLTable($theatreDocument,$layoutElement);
	if ($unassignedSeating)
	{
		$ticketsAdded = $individualSeating;
		foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] as $sectionClassName => $pricingId)
		{
			$html .= '<tr>' . "\n";
			$sellingunitId = substr($sectionClassName,strlen(SUMAC_CLASS_PREFIX_SECTION));
			$sellingunitElement = $theatreDocument->getElementById($sellingunitId);
			$sellingunitName = $sellingunitElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			$ticketsForName = (($sellingunitName == null) || ($sellingunitName == "")) ? ""
								: $_SESSION[SUMAC_STR]["TU22"] . $sellingunitName;
			$seatsAvailable = 0;
			$displayAvailability = true;
			$seatsalesSellingunitElement = $seatsalesDocument->getElementById($sellingunitId);
			if ($seatsalesSellingunitElement != null) $seatsAvailable = $seatsalesSellingunitElement->getAttribute(SUMAC_ATTRIBUTE_SEATS_AVAILABLE);
			if ($seatsalesSellingunitElement != null) $displayAvailability = ($seatsalesSellingunitElement->getAttribute(SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY) != SUMAC_VALUE_FALSE);
			$html .= sumac_getSeatingBlockCostHTMLTable($theatreDocument,$sectionClassName,$pricingId,$sellingunitId,$ticketsForName,$seatsAvailable,$displayAvailability);
			if ($ticketsAdded == false) $html .= sumac_getTicketsAndTotalHTMLTable(true);
			$ticketsAdded = true;
			$html .= '</tr>' . "\n";
		}
	}

	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getRequirements($theatreDocument,$withText,$withCode)
{
	$categoryTextAndOrCode = array();
	if (($withCode == true) && ($withText == false)) $categoryTextAndOrCode['0'] = $_SESSION[SUMAC_STR]["TU2"];
	else $categoryTextAndOrCode['0'] = $_SESSION[SUMAC_STR]["TU1"] . ($withCode ? (' [' . $_SESSION[SUMAC_STR]["TU2"] . ']') : '');
	$requirementElements = $theatreDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_REQUIREMENT);
	for ($j = 0; $j < $requirementElements->length; $j++)
	{
		$requirementElement = $requirementElements->item($j);
		$letterCode = strtoupper($requirementElement->getAttribute(SUMAC_ATTRIBUTE_LETTER_CODE));
		$text = ($requirementElement->childNodes->item(0) != null) ? $requirementElement->childNodes->item(0)->nodeValue : '';
		$id = $requirementElement->getAttribute(SUMAC_ATTRIBUTE_ID);
		if (($withCode == true) && ($withText == false)) $categoryTextAndOrCode[$id] = $letterCode;
		else $categoryTextAndOrCode[$id] = $text . ($withCode ? (' [' . $letterCode . ']') : '');
//echo 'id:' . $requirementElement->getAttribute(SUMAC_ATTRIBUTE_ID) . ',cc:' . $text . ' [' . $letterCode . '].<br />';
	}
	return $categoryTextAndOrCode;
}

function sumac_getIndividualSeatCostHTMLTable($theatreDocument,$layoutElement)
{
//allow the 'seats available' counts to be shown or suppressed in the seating-plan version (version 5.1.2.5)
	$availabilityInLegendClass = '';
	$seatingPlanElement = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_SEATING_PLAN)->item(0);
	if (($seatingPlanElement != null) && ($seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY) != "true")) $availabilityInLegendClass = ' class="sumac_nodisplay"';

	$categoryTextWithCode = sumac_getRequirements($theatreDocument,true,true);
	$seatsInCategory = array();
	foreach ($categoryTextWithCode as $id => $textWithCode) $seatsInCategory[$id] = false;
	$seatClassList = array();

	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] as $seatClassName => $pricingId)
	{
		if (strpos($seatClassName,SUMAC_CLASS_SUFFIX_INWARD_FACING) !== false) continue;	//we always have (and prefer) the forward facing button
		$pricingElement = $theatreDocument->getElementById($pricingId);
		$categoryElements = $pricingElement->getElementsByTagName(SUMAC_ELEMENT_CATEGORY);
		$seatClassList[$seatClassName] = array();
		for ($j = 0; $j < $categoryElements->length; $j++)
		{
			$onsale = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_ONSALE);
			if (($onsale != null) && ($onsale != SUMAC_VALUE_TRUE)) continue;	//we only want the ones you can buy
			$withRequirement = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_WITH_REQUIREMENT);
			$centsPrice = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE);
			$id = (($withRequirement == null) || ($withRequirement == '')) ? '0' : $withRequirement;
			$seatClassList[$seatClassName][$id] = $centsPrice;
			$seatsInCategory[$id] = true;
		}
	}
	$html = '<tr><td><table id="' . SUMAC_ID_TABLE_BUTTONTABLE . '" border="1" rules="all" cellpadding="3">' . "\n";
	$html .= '<tr><td></td>';	//first position is for colour-coded seat class
	$html .= '<td' . $availabilityInLegendClass . '>' . $_SESSION[SUMAC_STR]["TC2"] . '</td>';	//next is for number of seats still available
	foreach ($categoryTextWithCode as $id => $textWithCode)
	{
		if ($seatsInCategory[$id] == true) $html .= '<td>' . $textWithCode . '</td>';
	}
	$html .= '</tr>' . "\n";
	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] as $seatClassName => $pricingId)
	{
		if (strpos($seatClassName,SUMAC_CLASS_SUFFIX_INWARD_FACING) !== false) continue;	//we always have (and prefer) the forward facing button
		$html .= '<tr>';
		$html .= '<td><button type="button" class="' . $seatClassName . '" disabled="disabled"></button></td>';
		$seatsAvailableSpanId = SUMAC_ID_SPAN_SEATS_AVAILABLE_PREFIX . $pricingId;
		$seatsAvailable = $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClassName];
		//if ($seatsAvailable == 0) $seatsAvailable = SUMAC_TEXT_NO_SEATS_AVAILABLE;
		$html .= '<td' . $availabilityInLegendClass . ' align="center"><span id="' . $seatsAvailableSpanId . '">' . $seatsAvailable . '</span></td>';
		foreach ($categoryTextWithCode as $id => $textWithCode)
		{
			if (isset($seatClassList[$seatClassName][$id]))
			{
				$html .= '<td align="center">' . sumac_centsToPrintableDollars($seatClassList[$seatClassName][$id]) . '</td>';
			}
			else if ($seatsInCategory[$id] == true)
			{
				$html .= '<td></td>';
			}
			//else the column has been dropped altogether
		}
		$html .= '</tr>' . "\n";
	}
	$html .= '</table></td>' . "\n";
	$html .= sumac_getTicketsAndTotalHTMLTable(true);
	$html .= '</tr>' . "\n";
	return $html;
}

function sumac_getTicketsAndTotalHTMLTable($showTickets)
{
	$html = '<td><table id="' . SUMAC_ID_TABLE_TICKETTABLE . '" border="0">' . "\n";
	$html .= '<tr id="' . SUMAC_ID_TR_PICKED_LIST. '"';
	$html .= ($showTickets ? '' : ' class="sumac_nodisplay"') . '><td>' . $_SESSION[SUMAC_STR]["TU20"] . '<br /><span> </span></td></tr>' . "\n";
	$html .= '<tr><td align="left"><br />' . $_SESSION[SUMAC_STR]["TU17"] . '<span id="' . SUMAC_ID_SPAN_TOTAL_COST . '">' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '0.00' . '</span></td></tr>' . "\n";
	$html .= '</table></td>' . "\n";
	return $html;
}

function sumac_getSeatingBlockCostHTMLTable($theatreDocument,$sectionClassName,$pricingId,$sellingunitId,$ticketsForName,$seatsAvailable,$displayAvailability)
{
	$categoryTextWithCode = sumac_getRequirements($theatreDocument,true,true);
	$categoryCode = sumac_getRequirements($theatreDocument,false,true);
	$pricingElement = $theatreDocument->getElementById($pricingId);
	$categoryElements = $pricingElement->getElementsByTagName(SUMAC_ELEMENT_CATEGORY);
	$prices = array();
	for ($j = 0; $j < $categoryElements->length; $j++)
	{
		$onsale = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_ONSALE);
		if (($onsale != null) && ($onsale != SUMAC_VALUE_TRUE)) continue;	//we only want the ones you can buy
		$withRequirement = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_WITH_REQUIREMENT);
		$centsPrice = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE);
		$id = (($withRequirement == null) || ($withRequirement == '')) ? '0' : $withRequirement;
		$prices[$id] = $centsPrice;
	}

	$html = '<td><table border="1" rules="all" cellpadding="6">' . "\n";
	$html .= sumac_getBlockSeatingAvailableHTML($sectionClassName,$sellingunitId,$ticketsForName,$seatsAvailable,$displayAvailability,count($prices));
	$html .= '<tr><td align="left">' . $_SESSION[SUMAC_STR]["TC3"] . '</td>';

	foreach ($categoryTextWithCode as $id => $textWithCode)
	{
		if (isset($prices[$id])) $html .= '<td align="center">' . $textWithCode . '<br />' . sumac_centsToPrintableDollars($prices[$id]) . '</td>';
	}

	$html .= '</tr><tr><td align="left">' . $_SESSION[SUMAC_STR]["TU9"] .'<br />' . $ticketsForName;
	if (count($prices) > 1) $html .= $_SESSION[SUMAC_STR]["TU10"];
	$html .= '</td>' . "\n";

	foreach ($categoryTextWithCode as $id => $textWithCode)
	{
		if (isset($prices[$id]))
		{
			$html .= '<td align="center" style="white-space: nowrap">';
			$html .= '<button id="' . SUMAC_ID_BUTTON_SUBTRACT_PREFIX . $sellingunitId . $id . '" type="button" class="' . SUMAC_CLASS_ADD_SUBTRACT . '" onclick="sumac_subtract(\'' . $sellingunitId . '\',\'' . $id . '\',\'' . $categoryCode[$id] . '\')" disabled="disabled">-</button>';
			$html .= '<input id="' . SUMAC_ID_INPUT_TEXT_PREFIX . $sellingunitId . $id . '" type="text" name="' . SUMAC_NAME_INPUT_PREFIX_HOW_MANY . $sellingunitId . '" size="2" value="0" onchange="sumac_change(\'' . $sellingunitId . '\',\'' . $id . '\',\'' . $categoryCode[$id] . '\')" />';
			$html .= '<button id="' . SUMAC_ID_BUTTON_ADD_PREFIX . $sellingunitId . $id . '" type="button" class="' . SUMAC_CLASS_ADD_SUBTRACT . '" name="' . SUMAC_NAME_BUTTON_ADD_PREFIX . $sellingunitId . '" onclick="sumac_add(\'' . $sellingunitId . '\',\'' . $id . '\',\'' . $categoryCode[$id] . '\')"' . (($seatsAvailable == 0) ? ' disabled="disabled"' : '') . '>+</button>';
			$html .= '</td>' . "\n";
		}
	}

	$html .= '</tr></table>' . "\n";
	$html .= '</td>' . "\n";
	return $html;
}

function sumac_getBlockSeatingAvailableHTML($sectionClassName,$sellingunitId,$ticketsForName,$seatsAvailable,$displayAvailability,$categoryCount)
{
	$seatsAvailableSpanId = SUMAC_ID_SPAN_BLOCK_AVAILABLE_PREFIX . $sellingunitId;
	//if ($seatsAvailable == 0) $seatsAvailable = SUMAC_TEXT_NO_SEATS_AVAILABLE;
	$html = ($displayAvailability || ($ticketsForName != "")) ? '<tr>' : '<tr class="sumac_nodisplay">';
	$html .= '<td colspan="' . ($categoryCount + 1) . '" align="center" class="' . $sectionClassName . '">';
	$html .= '<span class="sumac_nodisplay" id="' . $seatsAvailableSpanId . SUMAC_ID_SPAN_INITIAL_AVAILABLE_SUFFIX . '">' . $seatsAvailable . '</span>';
	if ($displayAvailability)
	{
		$html .= $_SESSION[SUMAC_STR]["TU16"] . $ticketsForName . ': ';
		$html .= '<span id="' . $seatsAvailableSpanId . '" class="' . $sectionClassName . '">' . $seatsAvailable . '</span></td></tr>';
	}
	else
	{
		$html .= $_SESSION[SUMAC_STR]["TU15"] . $ticketsForName . ': ';
		$html .= '<span id="' . $seatsAvailableSpanId . '" class="' . $sectionClassName . ' sumac_nodisplay">' . $seatsAvailable . '</span></td></tr>';
	}
	return $html;
}

function sumac_countEventsInBasket()
{
	if (!isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) return 0;
	return count($_SESSION[SUMAC_SESSION_TICKET_BASKET]);
}

function sumac_basketContainsTicketsForEvent($id)
{
	if (!isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) return false;
	if (count($_SESSION[SUMAC_SESSION_TICKET_BASKET]) < 1) return false;
	foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $eventId => $eventTickets)
	{
		if ($id == $eventId) return true;
	}
	return false;
}

function sumac_countSeatsOrderedInBasket()
{
	if (!isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) return 0;
	$s = 0;
	foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $eventId => $eventTickets)
	{
		foreach ($eventTickets as $tid => $t)
		{
			if ($t['quantity'] == 'seat') $s = $s + 1;
			else $s = $s + $t['quantity'];
		}
	}
	return $s;
}

?>
