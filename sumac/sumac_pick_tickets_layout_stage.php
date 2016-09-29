<?php
//version530//

function sumac_getHTMLBodyForStageFromLayout($theatreDocument,$seatsalesDocument,$eventElement,$locationElement,$layoutElement)
{
	$eventHeaderHTML = sumac_getEventHeaderForStageDiagramHTML($theatreDocument,$eventElement,$locationElement);
	$blockElements = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_BLOCK);
//	$stageDiagramHTML = ($blockElements->length > 0) ? sumac_getBlockLayoutHTML($layoutElement) : sumac_getAreaLayoutHTML($layoutElement);
	$stageDiagramHTML = sumac_getBlockLayoutHTML($layoutElement);

	$html = '<div id="' . SUMAC_ID_DIV_STAGE . '" class="sumac_maintable">' . "\n";

	$html .= sumac_getPickInstructionsForStageDiagramHTML($theatreDocument,$seatsalesDocument);

	$html .= '<table border="4" rules="none" cellpadding="3" cellspacing="3" class="' . SUMAC_CLASS_THEATRE . '">';
	$html .= $eventHeaderHTML;
	$individualSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES]) > 0);
	if ($individualSeating) $html .= $stageDiagramHTML;
	$html .= '</table></div>' . "\n";
	return $html;
}

function sumac_getPickInstructionsForStageDiagramHTML($theatreDocument,$seatsalesDocument)
{
	$html =	'<table class="sumac_instructions"><tr><td class="sumac_instructions">';

	$individualSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES]) > 0);
	$unassignedSeating = (count($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES]) > 0);

	if ($unassignedSeating)
	{
		$first = true;
		$blockCount = 0;
		$blockHTML = $_SESSION[SUMAC_STR]["TI10"];
		foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] as $sectionClassName => $pricingId)
		{
			$sellingunitId = substr($sectionClassName,strlen(SUMAC_CLASS_PREFIX_SECTION));
			$sellingunitElement = $theatreDocument->getElementById($sellingunitId);
			$sellingunitName = $sellingunitElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
			if (($sellingunitName == null) || (($sellingunitName == ""))) continue;
			if ($first) $first = false;
			else $blockHTML .= ' or ';
			$blockHTML .= $sellingunitName;
			$blockCount++;
		}
		if ($individualSeating) $html .= $blockHTML . $_SESSION[SUMAC_STR]["TI4"];
		else $html .= $_SESSION[SUMAC_STR]["TI14"];
		$multipleCategories = false;
		foreach ($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES] as $sectionClassName => $pricingId)
		{
			$categoryCount = 0;
			$pricingId = reset($_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES]);
			$pricingElement = $_SESSION[SUMAC_SESSION_ORGANISATION_DOC]->getElementById($pricingId);
			$categoryElements = $pricingElement->getElementsByTagName(SUMAC_ELEMENT_CATEGORY);
			$categoryCodes = array();
			for ($j = 0; $j < $categoryElements->length; $j++)
			{
				$onsale = $categoryElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_ONSALE);
				if (($onsale != null) && ($onsale != SUMAC_VALUE_TRUE)) continue;	//we only want the ones you can buy
				$categoryCount++;
			}
			if ($categoryCount > 1) $multipleCategories = true;
		}
		if ($multipleCategories) $html .= $_SESSION[SUMAC_STR]["TI6"];
		$html .= '. ';
	}

	if ($individualSeating)
	{
		$html .= ($unassignedSeating ? $_SESSION[SUMAC_STR]["TI2"] : $_SESSION[SUMAC_STR]["TI3"]) . $_SESSION[SUMAC_STR]["TI8"];
		$html .= '<i>' . $_SESSION[SUMAC_STR]["TI5"] . '</i>';
	}

	//$html .= SUMAC_INSTRUCTIONS_WHEN_YOU_HAVE_CHOSEN;
	//if (count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) > 1) $html .= SUMAC_INSTRUCTIONS_EVENT_OR;
	//$html .= $_SESSION[SUMAC_STR]["TI11"];
	$html .= ((count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) > 1)
				? sumac_formatMessage($_SESSION[SUMAC_SESSION_CHOSENTEXT2],$_SESSION[SUMAC_STR]["TI1"],$_SESSION[SUMAC_STR]["TI9"])
				: sumac_formatMessage($_SESSION[SUMAC_SESSION_CHOSENTEXT1],$_SESSION[SUMAC_STR]["TI1"]));

	$html .= '</td></tr></table>' . "\n";
	return $html;
}

function sumac_getEventHeaderForStageDiagramHTML($theatreDocument,$eventElement,$locationElement)
{
	$productionNode = $eventElement->parentNode;
	$productionName = $productionNode->getAttribute(SUMAC_ATTRIBUTE_NAME);
	$eventHappening = $eventElement->getAttribute(SUMAC_ATTRIBUTE_HAPPENING);
	$addressElements = $locationElement->getElementsByTagName(SUMAC_ELEMENT_ADDRESS);
	$locationAddress = ($addressElements->item(0)->childNodes->item(0) != null) ? $addressElements->item(0)->childNodes->item(0)->nodeValue : 'unknown address';

	return '<tr><td colspan="21" class="' . SUMAC_CLASS_EVENTHEADER . '" align="center">' . "\n" .
			$productionName . $_SESSION[SUMAC_STR]["TC1"] . $locationAddress . ': ' . $eventHappening .
			'</td></tr>' . "\n";
				// . '<tr><td colspan="21"><br /></td></tr>' . "\n";
}

function sumac_getBlockLayoutHTML($layoutElement)
{
	$layoutAreaColour = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_AREA_COLOUR);
	if ($layoutAreaColour == null) $layoutAreaColour = $_SESSION[SUMAC_STR]["TX1"];
	$layoutRowColour = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_ROW_COLOUR);
	$layoutSellingunit = $layoutElement->getAttribute(SUMAC_ATTRIBUTE_SELLING_UNIT);
	$stageElement = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_STAGE)->item(0);
	$stageEdge = $stageElement->getAttribute(SUMAC_ATTRIBUTE_EDGE);
	$stageX = $stageElement->getAttribute(SUMAC_ATTRIBUTE_X);
	if (($stageX != null) && ($stageX != "")) $stageX = intval($stageX); else $stageX = 0;
	$stageY = $stageElement->getAttribute(SUMAC_ATTRIBUTE_Y);
	if (($stageY != null) && ($stageY != "")) $stageY = intval($stageY); else $stageY = 0;

	$blockElements = $layoutElement->getElementsByTagName(SUMAC_ELEMENT_BLOCK);
	$minX = $stageX;
	$maxX = $stageX;
	$minY = $stageY;
	$maxY = $stageY;
	for ($i = 0; $i < $blockElements->length; $i++)
	{
		$blockElement = $blockElements->item($i);
		$blockX = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_X));
		$blockY = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_Y));
		if (($blockX - $minX) < 0) $minX = $blockX;
		if (($blockX - $maxX) > 0) $maxX = $blockX;
		if (($blockY - $minY) < 0) $minY = $blockY;
		if (($blockY - $maxY) > 0) $maxY = $blockY;
	}
	$normalMaxX = $maxX - $minX;
	$normalMaxY = $maxY - $minY;
	$normalStageX = $stageX - $minX;
	$normalStageY = $stageY - $minY;
	$rangeX = $normalMaxX + 1;
//echo 'rangeX=' . $rangeX . ', minX=' . $minX . ', maxX=' . $maxX . '<br />';
	$sortedBlocks = array();
	for ($i = 0; $i < $blockElements->length; $i++)
	{
		$blockElement = $blockElements->item($i);
		$blockX = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_X));
		$blockY = intval($blockElement->getAttribute(SUMAC_ATTRIBUTE_Y));
		$blockW = $blockElement->getAttribute(SUMAC_ATTRIBUTE_WIDTH);
		$blockH = $blockElement->getAttribute(SUMAC_ATTRIBUTE_HEIGHT);
//echo '(' . $blockX . ',' . $blockY . ')<br />';
		$normalX = $blockX - $minX;
		$normalY = $blockY - $minY;
		$normalYAugmented = ($normalY * $rangeX);
		$sortedBlocks[($normalYAugmented + $normalX)] = $blockElement;
		//this indexing gets us the blocks sorted by ascending X within descending Y
		//i.e. the first will have the largest Y with the least X in that Y row
		if (($blockW != null) && ($blockW != ""))
			$sortedBlocks = addDummyRowBlocks($sortedBlocks,$normalX,$normalYAugmented,intval($blockW),$normalMaxX);
		if (($blockH != null) && ($blockH != ""))
			$sortedBlocks = addDummyColumnBlocks($sortedBlocks,$normalX,$normalY,intval($blockH),$normalMaxY,$rangeX,$blockW,$normalMaxX);
		//these functions mark unused elements that will not need <td></td> fillers because
		//this block will spread into them
	}

	$stageId = (($normalStageY * $rangeX) + $normalStageX);
//echo 'stage at ' . $stageId . '(' . $normalStageX . ',' . $normalStageY . ')<br />';
	$sortedBlocks[$stageId] = $stageElement;
	$stageW = $stageElement->getAttribute(SUMAC_ATTRIBUTE_WIDTH);
	$stageH = $stageElement->getAttribute(SUMAC_ATTRIBUTE_HEIGHT);
	if (($stageW != null) && ($stageW != ""))
		$sortedBlocks = addDummyRowBlocks($sortedBlocks,$normalStageX,($normalStageY * $rangeX),intval($stageW),$normalMaxX);
	if (($stageH != null) && ($stageH != ""))
		$sortedBlocks = addDummyColumnBlocks($sortedBlocks,$normalStageX,$normalStageY,intval($stageH),$normalMaxY,$rangeX,$stageW);

	ksort($sortedBlocks,SORT_NUMERIC);
	//the blocks are now assembled as rows of a single table with members of a row having a common Y value,
	// and table elements lining up vertically according to their X value.
	$html = '<tr align="center">';
	$thisY = 0;
	$nextX = 0;

	foreach ($sortedBlocks as $id => $savedBlockElement)
	{
		$x = $id % $rangeX;
		$y = intval($id / $rangeX);
		if ($savedBlockElement == "x") //an element that is occupied by extension of one to the left
		{
			//no new html neeeded
			$nextX = $x + 1;
//echo 'norow at ' . $id . '(' . $x . ',' . $y . ')<br />';
			continue;
		}
		else if ($savedBlockElement == "y") //an element that is occupied by extension of one above
		{
			if (($thisY - $y) < 0)
			{
				for ($j = $nextX; ($j - ($normalMaxX + 1)) < 0; $j++)
				{
					$html .= '<td></td>'; //more missing X elements
				}
				$html .= '</tr><tr align="center">';
				$thisY = $y;
				$nextX = 0;
			}
			for ($j = $nextX; ($j - $x) < 0; $j++)
			{
				$html .= '<td></td>'; //missing X elements
			}
			$nextX = $x + 1;
//echo 'nocol at ' . $id . '(' . $x . ',' . $y . ')<br />';
			continue;
		}

//echo $id . ' (' . $x . ',' . $y . ')<br />';
		if ($id != $stageId)
		{
//$blockX = intval($savedBlockElement->getAttribute(SUMAC_ATTRIBUTE_X));
//$blockY = intval($savedBlockElement->getAttribute(SUMAC_ATTRIBUTE_Y));
//echo 'block(' . $blockX . ',' . $blockY . ')<br />';
			$blockDetails = sumac_getBlockDetailsForStage($savedBlockElement,$id,$layoutAreaColour,$layoutRowColour,$layoutSellingunit);
		}
		if (($thisY - $y) < 0)
		{
			for ($j = $nextX; ($j - ($normalMaxX + 1)) < 0; $j++)
			{
				$html .= '<td></td>'; //more missing X elements
			}
			$html .= '</tr><tr align="center">';
			$thisY = $y;
			$nextX = 0;
		}
		for ($j = $nextX; ($j - $x) < 0; $j++)
		{
			$html .= '<td></td>'; //missing X elements
		}

		if ($id == $stageId)
		{
//echo 'stage<br />';
			$stageColour = $stageElement->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
			if ($stageColour == null) $stageColour = $_SESSION[SUMAC_STR]["TX2"];
			$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][SUMAC_CLASS_STAGE] = $stageColour;
			$stageText = ($stageElement->childNodes->item(0) != null) ? $stageElement->childNodes->item(0)->nodeValue : '';
			if (strlen(rtrim($stageText)) == 0) $stageText = $_SESSION[SUMAC_STR]["TX3"];
			$stageTdSpan = '';
			$stageWidth = $stageElement->getAttribute(SUMAC_ATTRIBUTE_WIDTH);
			$stageHeight = $stageElement->getAttribute(SUMAC_ATTRIBUTE_HEIGHT);
			if (($stageWidth != null) && ($stageWidth != "")) $stageTdSpan .= ' colspan="' . $stageWidth . '"';
			if (($stageHeight != null) && ($stageHeight != "")) $stageTdSpan .= ' rowspan="' . $stageHeight . '"';
			$html .= '<td' . $stageTdSpan . '><table frame="border" cellpadding="1" cellspacing="1" class="' . SUMAC_CLASS_STAGE . '">' .
						'<tr align="center">' .
						'<td align="center">' . $stageText . '</td>' .
						'</tr>' .
						'</table></td>';
		}
		else if ($blockDetails[SUMAC_ATTRIBUTE_FACE] == SUMAC_VALUE_FORWARD)
		{
			$html .= sumac_getForwardFacingSectionTableHTML($blockDetails,(($y - $normalStageY) > 0));
		}
		else
		{
			if (($x - $normalStageX) <= 0)
			{
				$html .= sumac_getInwardFacingLeftSectionTableHTML($blockDetails);
			}
			else
			{
				$html .= sumac_getInwardFacingRightSectionTableHTML($blockDetails);
			}
		}
		$nextX = $x + 1;
	}
	$html .= '</tr>';
	return $html;
}

function addDummyRowBlocks($a,$nx,$nya,$w,$nmaxX)
{
	if (($w > 1) && ($nx < $nmaxX))
	{
		for ($i = ($nx + 1); (($i < ($nx + $w)) && ($i <= $nmaxX)); $i++)
		{
			$a[($nya + $i)] = "x";
//echo 'dummyrow at ' . ($nya + $i) . ' (' . $i . ',' . ($nya/($nmax+1)) . ')<br />';
		}
	}
	return $a;
}

function addDummyColumnBlocks($a,$nx,$ny,$h,$nmaxY,$rx,$w,$nmaxX)
{
	if (($h > 1) && ($ny < $nmaxY))
	{
		for ($i = ($ny + 1); (($i < ($ny + $h)) && ($i <= $nmaxY)); $i++)
		{
			$a[($i * $rx) + $nx] = "y";
//echo 'dummycol at ' . (($ny * $rx) + $nx) . ' (' . $nx . ',' . $ny . ')<br />';
			if (($w != null) && ($w != "")) $a = addDummyRowBlocks($a,$nx,($i * $rx),intval($w),$nmaxX);
		}
	}
	return $a;
}

function sumac_getBlockDetailsForStage($blockElement,$id,$layoutAreaColour,$layoutRowColour,$layoutSellingunit)
{
	$rowElements = $blockElement->getElementsByTagName(SUMAC_ELEMENT_ROW);
	$blockHasIndividualSeats = true;
	$blockRowCount = $blockElement->getAttribute(SUMAC_ATTRIBUTE_ROW_COUNT);
	$blockSeatsPerRow = $blockElement->getAttribute(SUMAC_ATTRIBUTE_SEATS_PER_ROW);
	$blockSellingunit = $blockElement->getAttribute(SUMAC_ATTRIBUTE_SELLING_UNIT);
	if ($blockSellingunit == null) $blockSellingunit = $layoutSellingunit;

//RULE: if a valid row_count is supplied, any row elements will be ignored
//RULE: if row_count is used in place of row elements,
//		(a) seats_per_row must also be supplied because without row elements we have no seat elements
//		(b) selling_unit must also be supplied because without rows or seats we have no seat-ids for individual sales

	if ($blockRowCount != null) //row_count supplied
	{
		if ($blockSeatsPerRow == null)
		{
			// row_count not valid without seats_per_row
			$blockRowCount = $rowElements->length;
			echo SUMAC_WARNING_SEATS_PER_ROW . $blockRowCount . '<br />';
		}
		else if ($blockSellingunit == null)
		{
			// row_count not valid without selling_unit
			$blockRowCount = $rowElements->length;
			echo SUMAC_WARNING_SELLING_UNIT . $blockRowCount . '<br />';
		}
		else
		{
			$blockHasIndividualSeats = false;
		}
	}
	else
	{
		$blockRowCount = $rowElements->length;
	}
	$blockMaxSeatsInRow = ($blockHasIndividualSeats ? sumac_getMaxSeatsInRowsOfBlock($rowElements)
								: $blockSeatsPerRow);
	$blockColour = $blockElement->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
	if  (($blockColour == null) || ($blockColour == "")) $blockColour = $layoutAreaColour;
	$blockFace = $blockElement->getAttribute(SUMAC_ATTRIBUTE_FACE);
	$blockTitle = $blockElement->getAttribute(SUMAC_ATTRIBUTE_TITLE);
	$isSeatNumberedSection = ($blockElement->getAttribute(SUMAC_ATTRIBUTE_SEAT_NUMBERING) != SUMAC_VALUE_FALSE);
	$blockRowLabeling = ($blockHasIndividualSeats ? $blockElement->getAttribute(SUMAC_ATTRIBUTE_ROW_LABELING)
								: SUMAC_VALUE_NONE);
	$blockRowColour = $layoutRowColour;
	$blockPricedAt = null;
	if ($blockSellingunit != null)
	{
		$sellingunitElement = $_SESSION[SUMAC_SESSION_ORGANISATION_DOC]->getElementById($blockSellingunit);
		$blockPricedAt = $sellingunitElement->getAttribute(SUMAC_ATTRIBUTE_PRICED_AT);
		$pricingElement = $_SESSION[SUMAC_SESSION_ORGANISATION_DOC]->getElementById($blockPricedAt);
		$blockRowColour = $pricingElement->getAttribute(SUMAC_ATTRIBUTE_COLOUR);
	}
	$blockWidth = $blockElement->getAttribute(SUMAC_ATTRIBUTE_WIDTH);
	$blockHeight = $blockElement->getAttribute(SUMAC_ATTRIBUTE_HEIGHT);
	return array(
			SUMAC_ATTRIBUTE_ID => $id,
			SUMAC_ATTRIBUTE_ROW_COUNT => $blockRowCount,
			SUMAC_ATTRIBUTE_SEATS_PER_ROW => $blockSeatsPerRow,
			SUMAC_ATTRIBUTE_SELLING_UNIT => $blockSellingunit,
			SUMAC_ATTRIBUTE_COLOUR => $blockColour,
			SUMAC_ATTRIBUTE_FACE => $blockFace,
			SUMAC_ATTRIBUTE_TITLE => $blockTitle,
			SUMAC_ATTRIBUTE_SEAT_NUMBERING => $isSeatNumberedSection,
			SUMAC_ATTRIBUTE_ROW_LABELING => $blockRowLabeling,
			SUMAC_ATTRIBUTE_ROW_COLOUR => $blockRowColour,
			SUMAC_ATTRIBUTE_PRICED_AT => $blockPricedAt,
			SUMAC_ATTRIBUTE_WIDTH => $blockWidth,
			SUMAC_ATTRIBUTE_HEIGHT => $blockHeight,
			SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS => $blockMaxSeatsInRow,
			SUMAC_DERIVED_HAS_INDIVIDUAL_SEATS => $blockHasIndividualSeats,
			SUMAC_DERIVED_ROWS => $rowElements
			);
}

function sumac_getMaxSeatsInRowsOfBlock($rowElements)
{
//this code is the same as the function sumac_getMaxSeatsInRowsOfSection() which is commented out for now
	$maxseats = 0;
	for ($i = 0; $i < $rowElements->length; $i++)
	{
		$seatElements = $rowElements->item($i)->getElementsByTagName(SUMAC_ELEMENT_SEAT);
		if ($seatElements->length > $maxseats) $maxseats = $seatElements->length;
	}
	return $maxseats;
}

function sumac_getForwardFacingSectionTableHTML($sectionDetails,$areaIsBelowStageOnScreen)
{
	$html = '';
	$sectionTdSpan = '';
	$sectionWidth = $sectionDetails[SUMAC_ATTRIBUTE_WIDTH];
	$sectionHeight = $sectionDetails[SUMAC_ATTRIBUTE_HEIGHT];
	if (($sectionWidth != null) && ($sectionWidth != "")) $sectionTdSpan .= ' colspan="' . $sectionWidth . '"';
	if (($sectionHeight != null) && ($sectionHeight != "")) $sectionTdSpan .= ' rowspan="' . $sectionHeight . '"';
	if ($sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT] == null)
	{
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . '><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="rows" frame="border">';
	}
	else
	{
		$sectionTdClass = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT];
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES][$sectionTdClass] = $sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT];
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BORDERCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . ' class="' . $sectionTdClass . '"><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="rows" border="6">';
	}
	$colspan = $sectionDetails[SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS]
				+ (($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT) ? 1 : 0)
				+ (($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT) ? 1 : 0);
	if ($sectionDetails[SUMAC_ATTRIBUTE_TITLE] != null)
		$html .= '<tr align="center"><td align="center" colspan="' . $colspan . '">' . $sectionDetails[SUMAC_ATTRIBUTE_TITLE] . '</td></tr>';
	if ($areaIsBelowStageOnScreen)
	{
		if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING])
			$html .= sumac_getForwardFacingSeatNumberingHTML($sectionDetails,$areaIsBelowStageOnScreen);
		$html .= sumac_getForwardFacingRowsHTMLForSection($sectionDetails,$areaIsBelowStageOnScreen);
	}
	else
	{
		$html .= sumac_getForwardFacingRowsHTMLForSection($sectionDetails,$areaIsBelowStageOnScreen);
		if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING])
			$html .= sumac_getForwardFacingSeatNumberingHTML($sectionDetails,$areaIsBelowStageOnScreen);
	}
	$html .= '</table></td>';
	return $html;
}

function sumac_getForwardFacingSeatNumberingHTML($sectionDetails,$areaIsBelowStageOnScreen)
{
	$html = '<tr align="center">';
	$rowLabelingIsBeforeRow = (($areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT))
			|| (!$areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT)));
	$rowLabelingIsAfterRow = ((!$areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT))
			|| ($areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT)));
	if ($rowLabelingIsBeforeRow) $html .= '<td> </td>';
	$i = 0;
	while ($sectionDetails[SUMAC_DERIVED_HAS_INDIVIDUAL_SEATS] && sumac_isEmptyEndColumn($sectionDetails[SUMAC_DERIVED_ROWS],$i))
	{
		 $html .= '<td> </td>';
		 $i++;
	}
	$first = $i;
	while ($i < $sectionDetails[SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS])
	{
		$html .= '<td>' . ($i-$first+1) . '</td>';
		$i++;
	}
	if ($rowLabelingIsAfterRow) $html .= '<td> </td>';
	return $html . '</tr>';
}

function sumac_isEmptyEndColumn($rowElements,$column)
{
	for ($i = 0; $i < $rowElements->length; $i++)
	{
		$seatElements = $rowElements->item($i)->getElementsByTagName(SUMAC_ELEMENT_SEAT);
		$last = (($seatElements->length > $column) ? $column : ($seatElements->length - 1));
		for ($j = 0; $j <= $last; $j++)
		{
			$isOccupiableSeat = ($seatElements->item($j)->getAttribute(SUMAC_ATTRIBUTE_OCCUPIABLE) != SUMAC_VALUE_FALSE);
			if ($isOccupiableSeat) return false;
		}
	}
	return true;
}

function sumac_getForwardFacingRowsHTMLForSection($sectionDetails,$areaIsBelowStageOnScreen)
{
	$html = '';
	$rowClassName = SUMAC_CLASS_PREFIX_ROWS . $sectionDetails[SUMAC_ATTRIBUTE_ID];
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_ROWS][$rowClassName] = $sectionDetails[SUMAC_ATTRIBUTE_ROW_COLOUR];
	$rowLabelingIsBeforeRow = (($areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT))
			|| (!$areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT)));
	$rowLabelingIsAfterRow = ((!$areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT))
			|| ($areaIsBelowStageOnScreen && ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT)));
	if ($sectionDetails[SUMAC_DERIVED_HAS_INDIVIDUAL_SEATS])
	{
		$rowElements = $sectionDetails[SUMAC_DERIVED_ROWS];
		for ($i = 0; $i < $rowElements->length; $i++)
		{
			$rowNumber = ($areaIsBelowStageOnScreen ? $i : (($rowElements->length -1) - $i));
			$rowLabel = $rowElements->item($rowNumber)->getAttribute(SUMAC_ATTRIBUTE_LABEL);
			$html .= '<tr align="center" class="' . $rowClassName . '">';

			if ($rowLabelingIsBeforeRow) $html .= '<td>' . $rowLabel . '</td>';
			$html .= sumac_getForwardFacingSeatsHTMLForRow($rowNumber,$rowElements,$sectionDetails,$areaIsBelowStageOnScreen);
			if ($rowLabelingIsAfterRow) $html .= '<td>' . $rowLabel . '</td>';
			$html .= '</tr>';
		}
	}
	else
	{
		for ($i = 0; $i < $sectionDetails[SUMAC_ATTRIBUTE_ROW_COUNT]; $i++)
		{
			$html .= '<tr align="center" class="' . $rowClassName . '">';
			$html .= sumac_getForwardFacingSeatsHTMLForRow($i,null,$sectionDetails,null);
			$html .= '</tr>';
		}
	}
	return $html;
}

function sumac_getForwardFacingSeatsHTMLForRow($rowNumber,$rowElements,$sectionDetails,$areaIsBelowStageOnScreen)
{
	$html = '';
	if ($sectionDetails[SUMAC_DERIVED_HAS_INDIVIDUAL_SEATS])
	{
		$seatElements = $rowElements->item($rowNumber)->getElementsByTagName(SUMAC_ELEMENT_SEAT);
		for ($i = 0; $i < $seatElements->length; $i++)
		{
			$seatNumber = ($areaIsBelowStageOnScreen ? $i : (($seatElements->length -1) - $i));
			$html .= '<td>';
			$html .= sumac_getForwardFacingSeatButtonString(sumac_getSeatDetails($seatElements->item($seatNumber)),$sectionDetails);
			$html .= '</td>';
		}
	}
	else
	{
		for ($i = 0; $i < $sectionDetails[SUMAC_ATTRIBUTE_SEATS_PER_ROW]; $i++)
		{
			$html .= '<td>';
			$html .= sumac_getForwardFacingSeatButtonString(sumac_getSeatDetails(null),$sectionDetails);
			$html .= '</td>';
		}
	}
	return $html;
}

function sumac_getForwardFacingSeatButtonString($seatDetails,$sectionDetails)
{
	if ($seatDetails[SUMAC_ATTRIBUTE_OCCUPIABLE] == false)	return '<button type="button" class="' . SUMAC_CLASS_NONSEAT . '" disabled="disabled"></button>';
	if ($sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT] == null)
	{
		// no section pricing so individually assigned seat
		if ($seatDetails[SUMAC_ATTRIBUTE_SOLD])	return '<button type="button" class="' . SUMAC_CLASS_SOLD_SEAT . '" title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"></button>';
		$pricingId = $seatDetails[SUMAC_ATTRIBUTE_PRICED_AT];
		$seatClass = SUMAC_CLASS_PREFIX_SEAT . $pricingId;
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES][$seatClass] = $pricingId;
		if (isset($_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass]))
			$_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] = $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] + 1;
		else $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClass] = 1;
		return '<button id="' . SUMAC_ID_BUTTON_PREFIX . $seatDetails[SUMAC_ATTRIBUTE_ID] . '" type="button" class="' . $seatClass . '"' .
//									' onclick="sumac_pick(' . "'" . $seatDetails[SUMAC_ATTRIBUTE_ID] . "'" . ')"' .
									' onclick="sumac_pwb(this);"' .
									' title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"></button>';
	}
	//block seat
	$seatTitle = ($seatDetails[SUMAC_ATTRIBUTE_LABEL] == null) ? '' : ' title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"';
	$seatClass = SUMAC_CLASS_PREFIX_SEAT . $sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT] . SUMAC_CLASS_SUFFIX_UNNUMBERED;
	return '<button type="button" class="' . $seatClass . '"' . $seatTitle . '></button>';
}

function sumac_getInwardFacingLeftSectionTableHTML($sectionDetails)
{
	$html = '';
	$sectionTdSpan = '';
	$sectionWidth = $sectionDetails[SUMAC_ATTRIBUTE_WIDTH];
	$sectionHeight = $sectionDetails[SUMAC_ATTRIBUTE_HEIGHT];
	if (($sectionWidth != null) && ($sectionWidth != "")) $sectionTdSpan .= ' colspan="' . $sectionWidth . '"';
	if (($sectionHeight != null) && ($sectionHeight != "")) $sectionTdSpan .= ' rowspan="' . $sectionHeight . '"';
	if ($sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT] == null)
	{
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . '><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="cols" frame="border">';
	}
	else
	{
		$sectionTdClass = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT];
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES][$sectionTdClass] = $sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT];
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BORDERCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . ' class="' . $sectionTdClass . '"><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="rows" border="6">';
	}
	$colspan = $sectionDetails[SUMAC_ATTRIBUTE_ROW_COUNT] + (($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) ? 1 : 0);
	if ($sectionDetails[SUMAC_ATTRIBUTE_TITLE] != null)
		$html .= '<tr align="center"><td align="center" colspan="' . $colspan . '">' . $sectionDetails[SUMAC_ATTRIBUTE_TITLE] . '</td></tr>';
	if ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT) $html .= sumac_getInwardFacingLeftRowLabelingHTML($sectionDetails);
	$html .= sumac_getInwardFacingLeftRowsHTMLForSection($sectionDetails);
	if ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT) $html .= sumac_getInwardFacingLeftRowLabelingHTML($sectionDetails);
	$html .= '</table></td>';
	return $html;
}

function sumac_getInwardFacingLeftRowLabelingHTML($sectionDetails)
{
	$html = '<tr align="center">';
	$rowElements = $sectionDetails[SUMAC_DERIVED_ROWS];
	for ($i = ($rowElements->length - 1); $i >= 0; $i--)
	{
		$html .= '<td>' . $rowElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_LABEL) . '</td>';
	}
	if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) $html .= '<td> </td>';
	return $html . '</tr>';
}

function sumac_getInwardFacingLeftRowsHTMLForSection($sectionDetails)
{
	$html = '';
	$rowClassName = SUMAC_CLASS_PREFIX_ROWS . $sectionDetails[SUMAC_ATTRIBUTE_ID];
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_ROWS][$rowClassName] = $sectionDetails[SUMAC_ATTRIBUTE_ROW_COLOUR];
	$rowElements = $sectionDetails[SUMAC_DERIVED_ROWS];
	for ($i = 0; $i < $sectionDetails[SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS]; $i++)
	{
		$html .= '<tr align="center" class="' . $rowClassName . '">';
		$html .= sumac_getInwardFacingLeftSeatsHTMLForRow($i,$rowElements,$sectionDetails);
		if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) $html .= '<td>' . ($i+1) . '</td>';
		$html .= '</tr>';
	}
	return $html;
}

function sumac_getInwardFacingLeftSeatsHTMLForRow($rowNumber,$rowElements,$sectionDetails)
{
	$html = '';
	for ($i = ($rowElements->length - 1); $i >= 0; $i--)
	{
		$seatElements = $rowElements->item($i)->getElementsByTagName(SUMAC_ELEMENT_SEAT);
		if ($seatElements->item($rowNumber) == null) continue;
		$html .= '<td>';
		$html .= sumac_getInwardFacingSeatButtonString(sumac_getSeatDetails($seatElements->item($rowNumber)),$sectionDetails);
		$html .= '</td>';
	}
	return $html;
}

function sumac_getInwardFacingSeatButtonString($seatDetails,$sectionDetails)
{
	if ($seatDetails[SUMAC_ATTRIBUTE_OCCUPIABLE] == false)	return '<button type="button" class="' . SUMAC_CLASS_NONSEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . '" disabled="disabled"></button>';
	if ($sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT] == null)
	{
		// no section pricing so individually assigned seat
		if ($seatDetails[SUMAC_ATTRIBUTE_SOLD])	return '<button type="button" class="' . SUMAC_CLASS_SOLD_SEAT . SUMAC_CLASS_SUFFIX_INWARD_FACING . '" title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"></button>';
		$pricingId = $seatDetails[SUMAC_ATTRIBUTE_PRICED_AT];
		$seatClass = SUMAC_CLASS_PREFIX_SEAT . $pricingId . SUMAC_CLASS_SUFFIX_INWARD_FACING;
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES][$seatClass] = $pricingId;
		$seatClassForwardFacing = SUMAC_CLASS_PREFIX_SEAT . $pricingId;
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SEAT_CLASSES][$seatClassForwardFacing] = $pricingId;
		if (isset($_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClassForwardFacing]))
			$_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClassForwardFacing] = $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClassForwardFacing] + 1;
		else $_SESSION[SUMAC_SESSION_SEATS_AVAILABLE][$seatClassForwardFacing] = 1;
		return '<button id="' . SUMAC_ID_BUTTON_PREFIX . $seatDetails[SUMAC_ATTRIBUTE_ID] . '" type="button" class="' . $seatClass . '"' .
//									' onclick="sumac_pick(' . "'" . $seatDetails[SUMAC_ATTRIBUTE_ID] . "'" . ')"' .
									' onclick="sumac_pwb(this);"' .
									' title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"></button>';
	}
	//block seat
	$seatClass = SUMAC_CLASS_PREFIX_SEAT . $sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT] . SUMAC_CLASS_SUFFIX_INWARD_FACING . SUMAC_CLASS_SUFFIX_UNNUMBERED;
	return '<button type="button" class="' . $seatClass . '" title="' . $seatDetails[SUMAC_ATTRIBUTE_LABEL] . '"></button>';
}

function sumac_getInwardFacingRightSectionTableHTML($sectionDetails)
{
	$html = '';
	$sectionTdSpan = '';
	$sectionWidth = $sectionDetails[SUMAC_ATTRIBUTE_WIDTH];
	$sectionHeight = $sectionDetails[SUMAC_ATTRIBUTE_HEIGHT];
	if (($sectionWidth != null) && ($sectionWidth != "")) $sectionTdSpan .= ' colspan="' . $sectionWidth . '"';
	if (($sectionHeight != null) && ($sectionHeight != "")) $sectionTdSpan .= ' rowspan="' . $sectionHeight . '"';
	if ($sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT] == null)
	{
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . '><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="cols" frame="border">';
	}
	else
	{
		$sectionTdClass = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_SELLING_UNIT];
		$_SESSION[SUMAC_SESSION_PRICINGS_FOR_SECTION_CLASSES][$sectionTdClass] = $sectionDetails[SUMAC_ATTRIBUTE_PRICED_AT];
		$sectionTableClassName = SUMAC_CLASS_PREFIX_SECTION . $sectionDetails[SUMAC_ATTRIBUTE_ID];
		$_SESSION[SUMAC_SESSION_BORDERCOLORS_FOR_TABLES][$sectionTableClassName] = $sectionDetails[SUMAC_ATTRIBUTE_COLOUR];
		$html .= '<td' . $sectionTdSpan . ' class="' . $sectionTdClass . '"><table class="' . $sectionTableClassName . '" cellpadding="1" cellspacing="1" rules="rows" border="6">';
	}
	$colspan = $sectionDetails[SUMAC_ATTRIBUTE_ROW_COUNT] + (($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) ? 1 : 0);
	if ($sectionDetails[SUMAC_ATTRIBUTE_TITLE] != null)
		$html .= '<tr align="center"><td align="center" colspan="' . $colspan . '">' . $sectionDetails[SUMAC_ATTRIBUTE_TITLE] . '</td></tr>';
	if ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_RIGHT) $html .= sumac_getInwardFacingRightRowLabelingHTML($sectionDetails);
	$html .= sumac_getInwardFacingRightRowsHTMLForSection($sectionDetails);
	if ($sectionDetails[SUMAC_ATTRIBUTE_ROW_LABELING] == SUMAC_VALUE_LEFT) $html .= sumac_getInwardFacingRightRowLabelingHTML($sectionDetails);
	$html .= '</table></td>';
	return $html;
}

function sumac_getInwardFacingRightRowLabelingHTML($sectionDetails)
{
	$html = '<tr align="center">';
	if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) $html .= '<td> </td>';
	$rowElements = $sectionDetails[SUMAC_DERIVED_ROWS];
	for ($i = 0; $i < $rowElements->length; $i++)
	{
		$html .= '<td>' . $rowElements->item($i)->getAttribute(SUMAC_ATTRIBUTE_LABEL) . '</td>';
	}
	return $html . '</tr>';
}

function sumac_getInwardFacingRightRowsHTMLForSection($sectionDetails)
{
	$html = '';
	$rowClassName = SUMAC_CLASS_PREFIX_ROWS . $sectionDetails[SUMAC_ATTRIBUTE_ID];
	$_SESSION[SUMAC_SESSION_BGCOLORS_FOR_ROWS][$rowClassName] = $sectionDetails[SUMAC_ATTRIBUTE_ROW_COLOUR];
	$rowElements = $sectionDetails[SUMAC_DERIVED_ROWS];
	for ($i = ($sectionDetails[SUMAC_DERIVED_MAX_OCCUPIABLE_SEATS] - 1); $i >= 0; $i--)
	{
		$html .= '<tr align="center" class="' . $rowClassName . '">';
		if ($sectionDetails[SUMAC_ATTRIBUTE_SEAT_NUMBERING]) $html .= '<td>' . ($i+1) . '</td>';
		$html .= sumac_getInwardFacingRightSeatsHTMLForRow($i,$rowElements,$sectionDetails);
		$html .= '</tr>';
	}
	return $html;
}

function sumac_getInwardFacingRightSeatsHTMLForRow($rowNumber,$rowElements,$sectionDetails)
{
	$html = '';
	for ($i = 0; $i < $rowElements->length; $i++)
	{
		$seatElements = $rowElements->item($i)->getElementsByTagName(SUMAC_ELEMENT_SEAT);
		if ($seatElements->item($rowNumber) == null) continue;
		$html .= '<td>';
		$html .= sumac_getInwardFacingSeatButtonString(sumac_getSeatDetails($seatElements->item($rowNumber)),$sectionDetails);
		$html .= '</td>';
	}
	return $html;
}

?>