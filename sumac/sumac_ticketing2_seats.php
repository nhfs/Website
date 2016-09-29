<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_geth2.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_ticketing2_seats($source,$port,$event)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;
//@@@ needs new error exit

	$seatsalesDocument = sumac_loadSeatsalesDocument($source,$port,$event);
	if ($seatsalesDocument == false) return false;
//@@@ needs new error exit

	$eventElement = null;
	$eventElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_EVENT);
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
//@@@ needs new error exit
	}

	$extrasDocument = null;
	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$extrasDocument = sumac_reloadExtrasDocument();
		if ($extrasDocument == false) return false;
//@@@ needs new error exit
	}

	$html = sumac_ticketing2_seats_HTML($organisationDocument,$seatsalesDocument,$eventElement,$extrasDocument);

	if ($_SESSION[SUMAC_SESSION_ERROR_COUNT] > 0)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage(SUMAC_ERROR_ERRORS_IN_THEATRE,$_SESSION[SUMAC_SESSION_ERROR_COUNT]) . $_SESSION[SUMAC_STR]["AE5"];
		listXMLElementsAndAttributes($organisationDocument,0);
		listXMLElementsAndAttributes($seatsalesDocument,0);
		return false;
	}

	echo $html;
	return true;
}

function sumac_ticketing2_seats_HTML($organisationDocument,$seatsalesDocument,$eventElement,$extrasDocument)
{
	$htmlTitle = sumac_getHTMLTitle('','');

	$locationId = $eventElement->getAttribute(SUMAC_ATTRIBUTE_LOCATED_AT);
	$locationElement = $organisationDocument->getElementById($locationId);

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
	$layoutElement = $organisationDocument->getElementById($layoutId);
	$theatreColour = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_THEATRE_COLOUR);
	if ($theatreColour == null) $theatreColour = $_SESSION[SUMAC_SESSION_ETBGCOLOUR];
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] = $theatreColour;

	$usingStage = ($layoutElement->getElementsByTagName(SUMAC_ELEMENT_STAGE)->length > 0);
	if ($usingStage)
	{
		$htmlBody['stage'] = sumac_getHTMLBodyForStageFromLayout($organisationDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement) . "\n";
		//$htmlStyle = sumac_getHTMLStyleForTheatre($organisationDocument,$usingStage,$htmlBody['stage']);
		$generatedCSS = sumac_ticketing2_seats_css($organisationDocument,$usingStage,$htmlBody['stage']);
	}
	else //there must be a seatingplanElement - the DTD enforces that
	{
		$htmlBody['seatingplan'] = sumac_getHTMLBodyForSeatingPlanFromLayout($organisationDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement) . "\n";
		//$htmlStyle = sumac_getHTMLStyleForTheatre($organisationDocument,$usingStage,$htmlBody['seatingplan']);
		$generatedCSS = sumac_ticketing2_seats_css($organisationDocument,$usingStage,$htmlBody['seatingplan']);
	}
	$htmlBody['legend'] = sumac_getHTMLBodyForLegendFromLayout($organisationDocument,$seatsalesDocument,$layoutElement,$eventElement);

	return sumac_geth2_head('ticketing2',false,$generatedCSS)
			.sumac_ticketing2_seats_body($organisationDocument,$seatsalesDocument,$extrasDocument,$eventElement,$usingStage,$htmlBody);
}

function sumac_ticketing2_seats_body($organisationDocument,$seatsalesDocument,$extrasDocument,$eventElement,$usingStage,$htmlBody)
{
	$retryform = null;

	$extrajs = sumac_ticketing2_seats_js($organisationDocument,$eventElement);
	if (sumac_basketContainsTicketsForEvent($_SESSION[SUMAC_SESSION_TICKETING_EVENT]))
	{
		$extrajs .= sumac_ticketing2_seats_js_to_set_previous_picks($usingStage);
	}
	if ($usingStage == false)
	{
		$extrajs .= sumac_ticketing2_seats_js_to_start_seating_plan();
	}
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($seatsalesDocument,'seatsales')
			.(($extrasDocument != null) ? sumac_addParsedXmlIfDebugging($extrasDocument,'extras') : '')
			.sumac_geth2_user('top','ticketing2')
			.sumac_ticketing2_seats_content($organisationDocument,$extrasDocument,$usingStage,$htmlBody)
			.sumac_geth2_body_footer('ticketing2',true,$retryform,'',$extrajs);
}

function sumac_ticketing2_seats_content($organisationDocument,$extrasDocument,$usingStage,$htmlBody)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_sumac_div_hide_mainpage('ticketing2',sumac_geth2_spantext('ticketing2','H1'));

	$html .= sumac_geth2_divtag('ticketing2','seats_mainpage','mainpage');

	//$html .= sumac_ticketing2_seats_instructions();
	//still using old instructions embedded in main ticketing stage code

	$html .= sumac_ticketing2_title_with_user();
	$html .= sumac_ticketing2_summary_table($organisationDocument,$extrasDocument,true);

	$html .= sumac_getHTMLBodyForTicketingActionsNavbar('sumac_top_ticketing_navbar','',true,true);

	$html .= ($usingStage ? $htmlBody['stage'] : $htmlBody['seatingplan']).PHP_EOL //only one of the two possible units will have been created
				.$htmlBody['legend'].PHP_EOL;

	$html .= sumac_getHTMLBodyForTicketingActionsNavbar('sumac_bottom_ticketing_navbar','',true,true);

	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content

	return $html;
}

function sumac_ticketing2_seats_instructions()
{
	$html = sumac_geth2_divtag('ticketing2','instructions','instructions');
	$html .= sumac_geth2_spantext('ticketing2','I2');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_seats_js_to_start_seating_plan()
{
	return <<<EOSSFSPJS
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

EOSSFSPJS;
}

//******************************************
//methods for building the html Head element
//******************************************

//function sumac_getHTMLStyleForTheatre($theatreDocument,$usingStage,$stageOrSeatingPlanHTML)
function sumac_ticketing2_seats_css($organisationDocument,$usingStage,$stageOrSeatingPlanHTML)
{
	$html = '<style type="text/css">' . "\n";

	//if ($usingStage) $html .= sumac_getFixedHTMLStyleElementsForStage() . "\n";
	//else  $html .= sumac_getFixedHTMLStyleElementsForSeatingPlan() . "\n";
	//$html .= sumac_getFixedHTMLStyleElementsForLegend() . "\n";

	if ($usingStage) $html .= sumac_getVariableHTMLStyleElementsForStage($organisationDocument,$stageOrSeatingPlanHTML) . "\n";
	else $html .= sumac_getVariableHTMLStyleElementsForSeatingPlan($organisationDocument,$stageOrSeatingPlanHTML) . "\n";
	$html .= sumac_getVariableHTMLStyleElementsForLegend($organisationDocument) . "\n";

	//$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	//$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	//$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	return $html . '</style>' . "\n";
}

function sumac_getVariableHTMLStyleElementsForStage($theatreDocument,$stageHTML)
{
	$html = '#' . SUMAC_ID_DIV_STAGE . ' td.' . SUMAC_CLASS_EVENTHEADER . ' {border-style:solid; border-width:thick; font-size:' . $_SESSION[SUMAC_SESSION_PH_SCALE] . '; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button {color:white; vertical-align:top; width:' . $_SESSION[SUMAC_SESSION_BUTTON_WIDTH] . '; height:' . $_SESSION[SUMAC_SESSION_BUTTON_HEIGHT] . '; padding:0; border: none; margin:0; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_NONSEAT . ' {background-color:white; color:black;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_NONSEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . ' {background-color:white; color:black; width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . ';}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_SOLD_SEAT . ' {background-color:lightgrey;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_STAGE . ' button.' . SUMAC_CLASS_SOLD_SEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . ' {background-color:lightgrey; width:' . $_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] . ';}' . "\n";

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
	$html = '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_EVENTHEADER . ' {margin-left:auto;margin-right:auto;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_EVENTHEADER . ' td {border-style:solid; border-width:thick; border-radius:9px; -moz-border-radius:9px; padding:5px 5px; text-align:justify; font-size:' . $_SESSION[SUMAC_SESSION_PH_SCALE] . '; font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' span.sumac_seat_availability_class {font-size:75%;font-weight:bold;padding:5px;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' span.sumac_seat_quantity_class {font-size:75%;font-weight:bold;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' .sumac_so_pref {font-size:75%; text-align: center;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' .sumac_seat_quantity_class {font-size:75%; text-align: center;}' . "\n";

//	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' {font-size:' . $_SESSION[SUMAC_SESSION_TH_SCALE] . '; background-color:' . $_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] . ';}' . "\n";
//	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' td {vertical-align:top;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_SEATINGPLAN . ' table.' . SUMAC_CLASS_THEATRE . ' td {vertical-align:top; font-size:' . $_SESSION[SUMAC_SESSION_TH_SCALE] . '; background-color:' . $_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_THEATRE] . ';}' . "\n";

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
//this button definition is almost the same as the stage one, but the sizes are always standard
//note that it does not apply to all legend buttons
	$html = '#' . SUMAC_ID_DIV_LEGEND . ' #' . SUMAC_ID_TABLE_BUTTONTABLE . ' button {vertical-align:top; width:1.4em; height:1.4em; padding:0; border: none; margin:0;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_LEGEND . ' input[type="text"] {width:2.0em;}' . "\n";
	$html .= '#' . SUMAC_ID_DIV_LEGEND . ' button.' . SUMAC_CLASS_ADD_SUBTRACT . ' {border-style:outset; border-width:medium; width:1.8em; height:1.8em;}' . "\n";

	foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES] as $seatClassName => $pricingId)
	{
		if (strpos($seatClassName,SUMAC_CLASS_SUFFIX_INWARD_FACING) !== false) continue;	//we always have (and prefer) the forward facing button
		$pricingElement = $theatreDocument->getElementById($pricingId);
		//if ($pricingElement == null) continue; //no need to dodge the inward-facing short-row bug - fixed in v5.1.2.6
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

function sumac_ticketing2_seats_js($theatreDocument,$eventElement)
{
	//$html .= sumac_getCommonHTMLScriptVariables();
	$html = sumac_getTicketPicksHTMLScriptVariables($theatreDocument,$eventElement);
	//$html .= sumac_getCommonHTMLScriptFunctions();
	//$html .= sumac_getTicketPicksHTMLScriptFunctions();
	return $html;
}

function sumac_getTicketPicksHTMLScriptVariables($theatreDocument,$eventElement)
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
	$html .= 'var sumac_id_span_promotion_discount = "' . SUMAC_ID_SPAN_PROMOTION_DISCOUNT . '";' . "\n";
	$html .= 'var sumac_id_span_cost_after_discount = "' . SUMAC_ID_SPAN_COST_AFTER_DISCOUNT . '";' . "\n";
	$html .= 'var sumac_id_td_promotion_discount = "' . SUMAC_ID_TD_PROMOTION_DISCOUNT . '";' . "\n";
	$html .= 'var sumac_id_td_cost_after_discount = "' . SUMAC_ID_TD_COST_AFTER_DISCOUNT . '";' . "\n";
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
	$html .= 'var sumac_less_discount_of = "' . $_SESSION[SUMAC_STR]["TT14"] . '";' . "\n";

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

	$html .= 'var sumac_active_promocode = "";' . "\n";
	$html .= 'var sumac_active_promotion = -1;' . "\n";
	$html .= 'var sumac_promotions = new Array();' . "\n";
	$promotions = $eventElement->getAttribute(SUMAC_ATTRIBUTE_PROMOTIONS);
	if (($promotions != null) && ($promotions != ''))
	{
		$promotionIds = array_values(array_filter(explode(' ',$promotions)));
		if (count($promotionIds) > 0) $html .= sumac_getTicketPicksHTMLPromotionScriptVariables($theatreDocument,$promotionIds);
	}

	return $html;
}

function sumac_getTicketPicksHTMLPromotionScriptVariables($theatreDocument,$promotionIds)
{
	$html = 'var sumac_id_input_promocode = "' . SUMAC_ID_INPUT_PROMOCODE . '";' . "\n";
	for ($i = 0; $i < count($promotionIds); $i++)
	{
		$id = $promotionIds[$i];
		$pe = $theatreDocument->getElementById($id);
		$min = ($pe->getAttribute(SUMAC_ATTRIBUTE_MIN_TICKETS) != '') ? $pe->getAttribute(SUMAC_ATTRIBUTE_MIN_TICKETS) : '-1';
		$max = ($pe->getAttribute(SUMAC_ATTRIBUTE_MAX_TICKETS) != '') ? $pe->getAttribute(SUMAC_ATTRIBUTE_MAX_TICKETS) : 'Number.MAX_VALUE';
		$html .= 'var sumac_promo' . $i . ' = {'
			. 'id:"' . $id . '",'
			. 'code:"' . strtoupper($pe->getAttribute(SUMAC_ATTRIBUTE_CODE)) . '",'
			. 'name:"' . $pe->getAttribute(SUMAC_ATTRIBUTE_NAME) . '",'
			. 'discpc:' . (($pe->getAttribute(SUMAC_ATTRIBUTE_IS_PERCENTAGE) == 'true')
							? ('Number(' . $pe->getAttribute(SUMAC_ATTRIBUTE_DISCOUNT) . '),') : 'null,')
			. 'discamt:' . (($pe->getAttribute(SUMAC_ATTRIBUTE_IS_PERCENTAGE) != 'true')
							? ('Number(' . $pe->getAttribute(SUMAC_ATTRIBUTE_DISCOUNT) . '),') : 'null,')
			. 'min:Number(' . $min . '),'
			. 'max:Number(' . $max . ')'
			. '};' . "\n"
			. 'sumac_promotions[' . $i . '] = sumac_promo' . $i . ';' . "\n";
	}
	return $html;
}

function sumac_ticketing2_seats_js_to_set_previous_picks($usingStage)
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
	$promo_id = $_SESSION[SUMAC_SESSION_TICKETING_PROMOTIONS][$_SESSION[SUMAC_SESSION_TICKETING_EVENT]];
	if ($promo_id != '') $html .= 'sumac_reenter_promotion("' . $promo_id . '");' . "\n";
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

function sumac_getHTMLBodyForLegendFromLayout($theatreDocument,$seatsalesDocument,$layoutElement,$eventElement)
{
	$html = '';
//first a field for entering a promotion code if this event has such a thing
	$promotions = $eventElement->getAttribute(SUMAC_ATTRIBUTE_PROMOTIONS);
	if (($promotions != null) && ($promotions != ''))
	{
		$promotionIds = array_values(array_filter(explode(' ',$promotions)));
		if (count($promotionIds) > 0)
		{
			$html .= '<div><table><tr><td class="">' . "\n";
			$html .= '<label for="' . SUMAC_ID_INPUT_PROMOCODE . '">' . $_SESSION[SUMAC_STR]["TF4"] . '</label>' . "\n";
			$html .= '<input id="' . SUMAC_ID_INPUT_PROMOCODE . '" name="promocode" type="text" size="15" maxlength="35" value="" onchange="sumac_promotion_entered(this);"/>' . "\n";
			$html .= '</td></tr></table></div>' . "\n";
		}
	}

	$html .= '<div id="' . SUMAC_ID_DIV_LEGEND . '" class="sumac_maintable">' . "\n";
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
		//if ($pricingElement == null) continue; //no need to dodge the inward-facing short-row bug - fixed in v5.1.2.6
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
	$html .= '<tr><td id="' . SUMAC_ID_TD_PROMOTION_DISCOUNT . '" class="sumac_nodisplay">' . $_SESSION[SUMAC_STR]["TU30"] . '<span id="' . SUMAC_ID_SPAN_PROMOTION_DISCOUNT . '">' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '0.00' . '</span></td></tr>' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_COST_AFTER_DISCOUNT . '" class="sumac_nodisplay">' . $_SESSION[SUMAC_STR]["TU31"] . '<span id="' . SUMAC_ID_SPAN_COST_AFTER_DISCOUNT . '">' . $_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] . '0.00' . '</span></td></tr>' . "\n";
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
