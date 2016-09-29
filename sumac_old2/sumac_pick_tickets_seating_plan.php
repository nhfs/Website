<?php
//version450//

function sumac_getHTMLBodyForSeatingPlanFromLayout($theatreDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement)
{
	$eventHeaderHTML = sumac_getEventHeaderForSeatingPlanHTML($theatreDocument,$eventElement,$locationElement);
	$blockElements = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_BLOCK);
	$seatingPlanHTML = sumac_getSeatingPlanHTML($theatreDocument,$layoutElement);

	$html = '<div id="' . SUMAC_ID_DIV_SEATINGPLAN . '" class="sumac_maintable">' . "\n";

	$html .= $eventHeaderHTML;
	$html .= sumac_getPickInstructionsForSeatingPlanHTML($theatreDocument,$seatsalesDocument);

	$html .= '<table border="4" rules="none" cellpadding="3" cellspacing="3" class="' . SUMAC_CLASS_THEATRE . '">';


	$html .= $seatingPlanHTML;


	$html .= '</table></div>' . "\n";
	return $html;
}

function sumac_getPickInstructionsForSeatingPlanHTML($theatreDocument,$seatsalesDocument)
{
	$html =	'<table class="sumac_instructions"><tr><td class="sumac_instructions">';

	if ((count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]) > 1) || (count($_SESSION[SUMAC_SESSION_SEAT_GRADES]) > 1))
		$html .= $_SESSION[SUMAC_STR]["TI12"];
	$html .= $_SESSION[SUMAC_STR]["TI13"];

	$html .= ((count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) > 1)
				? sumac_formatMessage($_SESSION[SUMAC_SESSION_CHOSENTEXT2],$_SESSION[SUMAC_STR]["TI1"],$_SESSION[SUMAC_STR]["TI9"])
				: sumac_formatMessage($_SESSION[SUMAC_SESSION_CHOSENTEXT1],$_SESSION[SUMAC_STR]["TI1"]));

	$html .= '</td></tr></table>' . "\n";
	return $html;
}

function sumac_getEventHeaderForSeatingPlanHTML($theatreDocument,$eventElement,$locationElement)
{
	$productionNode = $eventElement->parentNode;
	$productionName = $productionNode->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$eventHappening = $eventElement->getAttribute(SUMAC_ATTRIBUTE_HAPPENING);
	$addressElements = $locationElement->getElementsByTagName(SUMAC_ELEMENT_ADDRESS);
	$locationAddress = ($addressElements->item(0)->childNodes->item(0) != null) ? $addressElements->item(0)->childNodes->item(0)->nodeValue : 'unknown address';
	return '<table class="sumac_eventheader">' .
			'<tr><td class="' . SUMAC_CLASS_EVENTHEADER . '" align="center">' .
			$productionName . $_SESSION[SUMAC_STR]["TC1"] . $locationAddress . ': ' . $eventHappening .
			'</td></tr></table>' . "\n";
}

function sumac_getSeatingPlanHTML($theatreDocument,$layoutElement)
{
	$layoutSellingunit = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_SELLING_UNIT);
	$seatingPlanElement = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_SEATING_PLAN)->item(0);

	$blockElements = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_BLOCK);
	$blocks = array();
	for ($i = 0; $i < $blockElements->length; $i++)
	{
		$blockElement = $blockElements->item($i);
		$blockX = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_X));
		$blockY = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_Y));
		$blocks[] = sumac_getBlockDetailsForSeatingPlan($blockElement,$i);
	}

	$seatHTML = sumac_getSeatHTMLAndPricingsAndWeightsUsed($theatreDocument,$blocks);
//the global arrays SUMAC_SESSION_SEAT_PRICINGS and SUMAC_SESSION_SEAT_GRADES have now been populated
//and  they should be in their presentation order - low-to-high pricing and high-to-low weights

	$html = '<tr><td>' . "\n";

	$height = $seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_HEIGHT);
	$width = $seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_WIDTH);
	$planfile = $seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_FILE);
	if ($planfile != '')
	{
		$imgsize = getimagesize($planfile);
//echo 'img width = ' . $imgsize[0] . ', img height = ' . $imgsize[1]  . '<br />';
		if (($width == '') && ($imgsize[0] > 0)) $width = $imgsize[0];
		if (($height == '') && ($imgsize[1] > 0)) $height = $imgsize[1];
		$html .= '<img src="' . $planfile .'" width="' . $width . '" height="' . $height . '" alt="Seating plan" usemap="#sumac_seatingplan" />' . "\n";
		$html .= sumac_getSeatingPlanMapHTML($blocks);
	}
	else
	{
		if ($width == '') $width = 300; else $width = intval($width);
		if ($height == '') $height = 50; else $height = intval($height);
	}

	$html .= '</td><td>' . "\n";


	$fieldsetSeatOrderClass = ((count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]) > 1) || (count($_SESSION[SUMAC_SESSION_SEAT_GRADES]) > 1)) ? 'sumac_fieldset_seatorder' : 'sumac_nodisplay';
	$html .= '  <fieldset class="' . $fieldsetSeatOrderClass . '"><legend class="sumac_so_pref">' . $_SESSION[SUMAC_STR]["TU24"] . '</legend>' . "\n";
	$html .= '      <input id="sumac_choose_from_ltor" type="radio" name="seatorder" value="ltor" onchange="sumac_sort_seat_list(event,3);" checked="checked" />' .
			'<label class="sumac_so_pref" for="sumac_choose_from_ltor">Left to right</label><br />' . "\n";
	if (count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]) > 1)
	{
		$html .= '      <input id="sumac_choose_from_price" type="radio" name="seatorder" value="price" onchange="sumac_sort_seat_list(event,4);" />' .
				'<label class="sumac_so_pref" for="sumac_choose_from_price">Price</label><br />' . "\n";
	}
	if (count($_SESSION[SUMAC_SESSION_SEAT_GRADES]) > 1)
	{
		$html .= '      <input id="sumac_choose_from_quality" type="radio" name="seatorder" value="quality" onchange="sumac_sort_seat_list(event,5);" />' .
				'<label class="sumac_so_pref" for="sumac_choose_from_quality">Quality</label><br />' . "\n";
	}
	$html .= '  </fieldset>' . "\n";

	$fieldsetSeatsetClass = ($_SESSION[SUMAC_SESSION_ALLOW_PARTY_BOOKING]) ? 'sumac_fieldset_seatset' : 'sumac_nodisplay';
	$html .= '  <fieldset class="' . $fieldsetSeatsetClass . '"><legend class="sumac_so_pref">' . $_SESSION[SUMAC_STR]["TU25"] . '</legend>' . "\n" .
			'    <input id="sumac_choose_singles" type="radio" name="seatset" value="individual" onchange="sumac_individual_seats();" checked="checked" />' .
			'<label class="sumac_so_pref" for="sumac_choose_singles">Single seats</label><br />' . "\n" .
			'    <input id="sumac_choose_sets" type="radio" name="seatset" value="seatset" onchange="sumac_seats_together();" />' .
			'<input id="sumac_together_count" type="text" size="1" value="" disabled="disabled" class="sumac_so_pref" onkeyup="sumac_set_unusable_seats(this.value);" />' .
			'<label class="sumac_so_pref" for="sumac_choose_sets"> together</label>' . "\n";
	$html .= '  </fieldset>' . "\n";

	$selectorDivClass = ((!$_SESSION[SUMAC_SESSION_OMIT_BLOCK_SELECTOR]) && (count($blocks) > 1)) ? 'sumac_div_block_selector' : 'sumac_nodisplay';
	$html .= '  <div class="' . $selectorDivClass . '">' . "\n";
	$html .= '  <fieldset class="sumac_fieldset_blockpick"><legend class="sumac_so_pref">' . $_SESSION[SUMAC_STR]["TU26"] . '</legend>' . "\n" .
			'    <select id="sumac_seat_block_selector" name="1" onchange="sumac_select_list_and_detail();">' . "\n";
	$html .= '      <option value="*" >' . $_SESSION[SUMAC_STR]["TU27"] . '</option>' . "\n";
	for ($i = 0; $i < count($blocks); $i++)
	{
		$blockLabel = $blocks[$i][SUMAC_ATTRIBUTE_LABEL];
		$html .= '      <option value="' . $blockLabel . '" >' . $_SESSION[SUMAC_STR]["TU28"] . $blockLabel . '</option>' . "\n";
	}
	$html .= '    </select>' . "\n";
	$html .= '  </fieldset>' . "\n";
	$html .= '  </div>' . "\n";

	$quantitySpanClass = 'sumac_seat_quantity_class';
	if (!$_SESSION[SUMAC_SESSION_ALLOW_PARTY_BOOKING]) $quantitySpanClass = 'sumac_nodisplay';
	$html .= '  <span id="sumac_seat_set_text" class="' . $quantitySpanClass . '">' . $_SESSION[SUMAC_STR]["TU14"] . '</span>';
	if ($_SESSION[SUMAC_SESSION_ALLOW_PARTY_BOOKING]) $html .= '<br />';
	$html .= "\n";

	$availabilitySpanClass = 'sumac_seat_availability_class';
	if (!($seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY) == "true")) $availabilitySpanClass = 'sumac_nodisplay';
	$html .= '  <span id="sumac_seat_selector_label" class="' . $availabilitySpanClass . '"></span>';
	if ($seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY) == "true") $html .= '<br />';
	$html .= "\n";

	$selectorHeight = $height;
	$fsoc = ((count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]) > 1) && (count($_SESSION[SUMAC_SESSION_SEAT_GRADES]) > 1)) ? 95 : 72;
	if ($fieldsetSeatOrderClass != 'sumac_nodisplay') $selectorHeight = $selectorHeight - $fsoc;
	if ($selectorDivClass != 'sumac_nodisplay') $selectorHeight = $selectorHeight - 60;
	if ($_SESSION[SUMAC_SESSION_ALLOW_PARTY_BOOKING]) $selectorHeight = $selectorHeight - 115;
	if ($seatingPlanElement->getAttribute(SUMAC_ATTRIBUTE_DISPLAY_AVAILABILITY) == "true") $selectorHeight = $selectorHeight - 22;
	if ($selectorHeight < 100) $selectorHeight = 100; //minimum of 4 seat entries
	$html .= '  <div id="sumac_visible_seat_selector" style="max-height: ' . $selectorHeight . 'px; overflow: scroll; font-size: 80%; background-color: transparent; text-align: center; margin: 4px;"></div>' . "\n";

	$html .= sumac_getGroupHolderHTML($blocks);

	$html .= '  <div id="sumac_hidden_seats" class="sumac_nodisplay">' . "\n" .$seatHTML . '  </div>' . "\n";

	$html .= '</td><td>' . "\n";
	if (sumac_blocksHaveDetail($blocks))
	{
		$html .= '  <iframe id="plandetail" class="sumac_nodisplay" style="min-width:600px;height:450px;"></iframe>' . "\n";
	}
	$html .= '</td></tr>' . "\n";
	return $html;
}

function sumac_getBlockDetailsForSeatingPlan($blockElement)
{
	$blockLabel = $blockElement->getAttribute(SUMAC_ATTRIBUTE_LABEL);
	$rowElements = $blockElement->getElementsByTagName(SUMAC_ELEMENT_ROW);

	$blockRowCount = $blockElement->getAttribute(SUMAC_ATTRIBUTE_ROW_COUNT);
	$blockSeatsPerRow = $blockElement->getAttribute(SUMAC_ATTRIBUTE_SEATS_PER_ROW);
	$blockSellingunit = $blockElement->getAttribute(SUMAC_ATTRIBUTE_SELLING_UNIT);
	if (($blockRowCount != null) || ($blockSeatsPerRow != null) || ($blockSellingunit != null))
	{
		echo SUMAC_WARNING_SEATING_PLAN . $blockRowCount . ', ' . $blockSeatsPerRow  . ', ' . $blockSellingunit . '<br />';
	}

	$blockX = $blockElement->getAttribute(SUMAC_ATTRIBUTE_X);
	$blockY = $blockElement->getAttribute(SUMAC_ATTRIBUTE_Y);
	$blockRadius = $blockElement->getAttribute(SUMAC_ATTRIBUTE_RADIUS);
	$blockDetail = $blockElement->getAttribute(SUMAC_ATTRIBUTE_DETAIL);
	return array(
			SUMAC_ATTRIBUTE_LABEL => $blockLabel,
			SUMAC_ATTRIBUTE_X => $blockX,
			SUMAC_ATTRIBUTE_Y => $blockY,
			SUMAC_ATTRIBUTE_RADIUS => $blockRadius,
			SUMAC_ATTRIBUTE_DETAIL => $blockDetail,
			SUMAC_DERIVED_ROWS => $rowElements
			);
}

function sumac_getSeatHTMLAndPricingsAndWeightsUsed($theatreDocument,$blocks)
{

	$seats = array();
	$seatPricings = array();
	$prices = array();
	$pricekeys = array();
	$weightkeys = array();
	$seatindex = 0;
	for ($ib = 0; $ib < count($blocks); $ib++)
	{
		$block = $blocks[$ib];
		$rowElements = $block[SUMAC_DERIVED_ROWS];
		for ($ir = 0; $ir < $rowElements->length; $ir++)
		{
			$rowNumber = $ir; //(somecondition ? $ir : (($rowElements->length -1) - $ir));
			$row = $rowElements->item($rowNumber);
			$seatElements = $row->getElementsByTagName(SUMAC_ELEMENT_SEAT);
			for ($is = 0; $is < $seatElements->length; $is++)
			{
				$seatNumber = $is; //(somecondition ? $is : (($seatElements->length -1) - $is));
				$seat = sumac_getSeatDetails($seatElements->item($seatNumber));
					//we are not interested in seats that have been sold
					//nor seats that cannot be occupied
					//but we must note that the preceding seat had no available neighbour after it
				if (($seat[SUMAC_ATTRIBUTE_SOLD]) || ($seat[SUMAC_ATTRIBUTE_OCCUPIABLE] == false))
				{
					if ($seatindex > 0)
					{
						$oldseat = $seats[$seatindex - 1];
						$oldseat[SUMAC_DERIVED_LAST_OF_SET] = true;
					}
					continue;
				}

				$seat[SUMAC_DERIVED_BLOCK_INDEX] = $ib;
				$seatPricing = $seat[SUMAC_ATTRIBUTE_PRICED_AT];
				$ip = -1;
				for ($i = 0; $i < count($seatPricings); $i++)
				{
					if ($seatPricings[$i] == $seatPricing) { $ip = $i; break; }
				}
				if ($ip < 0)
				{
					$ip = count($seatPricings);
					$seatPricings[] = $seatPricing;

					$pricingElement = $theatreDocument->getElementById($seatPricing);
					$categoryElements = $pricingElement->getElementsByTagName(SUMAC_ELEMENT_CATEGORY);
					$price = 0;
					for ($ic = 0; $ic < $categoryElements->length; $ic++)
					{
						$withRequirement = $categoryElements->item($ic)->getAttribute(SUMAC_ATTRIBUTE_WITH_REQUIREMENT);
						if (($withRequirement == null) || ($withRequirement == ''))
						{
							$price = intval($categoryElements->item($ic)->getAttribute(SUMAC_ATTRIBUTE_CENTS_PRICE));
							break;
						}
					}
					$prices[] = $price;
 				}
				$pricekeys[] = strval(intval(1000000) + intval($prices[$ip])) . strval(intval(1000) + intval($seatindex));
				$seat[SUMAC_DERIVED_PRICE] = $prices[$ip];
				$seatWeight = $seat[SUMAC_ATTRIBUTE_WEIGHT];
				$iw = -1;
				for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_GRADES]); $i++)
				{
					if ($_SESSION[SUMAC_SESSION_SEAT_GRADES][$i] == $seatWeight) { $iw = $i; break; }
				}
				if ($iw < 0)
				{
					$iw = count($_SESSION[SUMAC_SESSION_SEAT_GRADES]);
					$_SESSION[SUMAC_SESSION_SEAT_GRADES][] = $seatWeight;
				}
				$weightkeys[] = strval(intval(100) + intval($seatWeight)) . strval(intval(1000) + intval($seatindex));
				if ($is == ($seatElements->length - 1)) $seat[SUMAC_DERIVED_LAST_OF_SET] = true; //end of row
				$seats[] = $seat;
				$seatindex++;	//count of seats but index of NEXT seat
			}
		}
	}
	sort($pricekeys,SORT_REGULAR);	//seat order within lowest price to highest
	rsort($weightkeys,SORT_REGULAR);	//seat order within highest grade to lowest
	$pka = array();
	for ($i = 0; $i < count($prices); $i++) { $pka[$prices[$i]] = $i; }	// echo $prices[$i] . ', ' . $seatPricings[$i] . '<br />';
	ksort($pka,SORT_REGULAR);
	foreach ($pka as $price => $pi) $_SESSION[SUMAC_SESSION_SEAT_PRICINGS][] = $seatPricings[$pi];
	rsort($_SESSION[SUMAC_SESSION_SEAT_GRADES],SORT_REGULAR);	//highest grade to lowest
	$html = '';
	for ($i = 0; $i < count($seats); $i++)
	{
		$seat = $seats[$i];
		$price = $seat[SUMAC_DERIVED_PRICE];
		$priceIndex = array_search(strval(intval(1000000) + intval($price)) . strval(intval(1000) + intval($i)),$pricekeys);
		$weight = $seat[SUMAC_ATTRIBUTE_WEIGHT];
		$weightIndex = array_search(strval(intval(100) + intval($weight)) . strval(intval(1000) + intval($i)),$weightkeys);
		$html .= sumac_getSeatListHTMLForOneSeat($seat,$i,$priceIndex,$weightIndex);
	}
	$_SESSION[SUMAC_SESSION_AVAILABLE_SEAT_COUNT] = count($seats);
	return $html;
}

function sumac_blocksHaveDetail($blocks)
{
	for ($i = 0; $i < count($blocks); $i++)
	{
		if ($blocks[$i][SUMAC_ATTRIBUTE_DETAIL] != '') return true;
	}
	return false;
}

function sumac_getGroupHolderHTML($blocks)
{
	$html = '  <div id="sumac_hidden_group_holder" class="sumac_nodisplay">' . "\n" .
			'    <div id="sumac_ogxxx" title="Empty block"></div>' . "\n";
	for ($i = 0; $i < count($blocks); $i++)
	{
		$blocklabel = $blocks[$i][SUMAC_ATTRIBUTE_LABEL];
		$detail = $blocks[$i][SUMAC_ATTRIBUTE_DETAIL];
		$html .= '    <div id="sumac_og0_' . $i . '" title="' . $_SESSION[SUMAC_STR]["TT10"] . $blocklabel . '"></div>' . "\n";
		$_SESSION[SUMAC_SESSION_BLOCKS_LTOR][] = $blocklabel;
		$_SESSION[SUMAC_SESSION_BLOCKS_DETAILS][] = $detail;
	}
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]); $i++)
	{
		$pricing = $_SESSION[SUMAC_SESSION_SEAT_PRICINGS][$i];
		//$pricinglabel = $pricing[SUMAC_ATTRIBUTE_LABEL];
		$html .= '    <div id="sumac_og1_' . $i . '" title="' . $_SESSION[SUMAC_STR]["TT11"] . $pricing . '"></div>' . "\n";
	}
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_GRADES]); $i++)
	{
		$weight = $_SESSION[SUMAC_SESSION_SEAT_GRADES][$i];
		$html .= '    <div id="sumac_og2_' . $i . '" title="' . $_SESSION[SUMAC_STR]["TT12"] . $weight . '"></div>' . "\n";
	}
	$html .= '    <div id="sumac_unusable_seats"></div>' . "\n";
	$html .= '  </div>' . "\n";
	return $html;
}

function sumac_getSeatListHTMLForOneSeat($seat,$seatNumber,$seatPriceIndex,$seatWeightIndex)
{
	$blockIndex = $seat[SUMAC_DERIVED_BLOCK_INDEX];
	$seatPricing = $seat[SUMAC_ATTRIBUTE_PRICED_AT];
	$pricingBlockIndex = 0;
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_PRICINGS]); $i++)
	{
		if ($_SESSION[SUMAC_SESSION_SEAT_PRICINGS][$i] == $seatPricing) { $pricingBlockIndex = $i; break; }
	}
	$seatWeight = $seat[SUMAC_ATTRIBUTE_WEIGHT];
	$weightBlockIndex = 0;
	for ($i = 0; $i < count($_SESSION[SUMAC_SESSION_SEAT_GRADES]); $i++)
	{
		if ($_SESSION[SUMAC_SESSION_SEAT_GRADES][$i] == $seatWeight) { $weightBlockIndex = $i; break; }
	}
	$seatClass = SUMAC_CLASS_PREFIX_SEAT . $seatPricing;
	$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES][$seatClass] = $seatPricing;
	if (isset($_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass]))
		$_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] = $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] + 1;
	else $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] = 1;
	$seatId = $seat[SUMAC_ATTRIBUTE_ID];
	$seatLabel = $seat[SUMAC_ATTRIBUTE_LABEL];
	$lastOfSet = (isset($seat[SUMAC_DERIVED_LAST_OF_SET])) ? 'Y' : 'N';

	return '    <a id="sumac_seat_' . $blockIndex . '_' . $pricingBlockIndex . '_' . $weightBlockIndex .
				'_' . $seatNumber . '_' . $seatPriceIndex . '_' . $seatWeightIndex . '_' . $lastOfSet . '" ' .
				'class="' . $seatClass . '" draggable="false"  onclick="sumac_pwa(event,this);" ' .
				'href="add_' . $seatId . '_to_order">' . $seatLabel . '</a>' . "\n";
}

function sumac_getSeatingPlanMapHTML($blocks)
{
	$html = '  <map name="sumac_seatingplan">' . "\n";
	for ($i = 0; $i < count($blocks); $i++)
	{
		$r = $blocks[$i][SUMAC_ATTRIBUTE_RADIUS];
		if ($r != 0)
		{
			$blocklabel = $blocks[$i][SUMAC_ATTRIBUTE_LABEL];
			$x = $blocks[$i][SUMAC_ATTRIBUTE_X];
			$y = $blocks[$i][SUMAC_ATTRIBUTE_Y];
			$html .= '    <area shape="circle" coords="' . $x . ',' . $y . ',' . $r . '" alt="' . $blocklabel . '" title="' . $_SESSION[SUMAC_STR]["TT13"] . $blocklabel . '" ' .
					'href="show_only_seats_in_block_' . $blocklabel . '" onclick="sumac_select_list_and_detail_from_map(event,this);" />' . "\n";
		}
	}
	$html .= '  </map>' . "\n";
	return $html;
}

?>