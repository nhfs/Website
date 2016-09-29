<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

function sumac_countTicketOrdersInBasket()
{
	if (!isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) return 0;
	$t = 0;
	foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $p) $t = $t + count($p);
	return $t;
}

function sumac_getHTMLBodyForTicketingActionsNavbar($navbarId,$hrefParameters,$newEvent,$checkout)
{
	$html = '<div id="' . $navbarId . '">' . "\n";
	$html .= sumac_getHTMLTicketingNavbarActionLinks($navbarId,$hrefParameters,$newEvent,$checkout);
	$html .= '</div>' . "\n";

	return $html;
}

function sumac_getHTMLTicketingNavbarActionLinks($navbarId,$hrefParameters,$newEvent,$checkout)
{
	$html = ' <div class="sumac_navbar_small_links" style="float:right">' . "\n";
	$navClass = (sumac_countTicketOrdersInBasket() > 0) ? 'sumac_navlink' : 'sumac_disabled_navlink';

	$href = '';
	if ($newEvent)
	{
		$hrefvalue = 'sumac_ticketing_redirect.php?function=more' . $hrefParameters;
		$nothref = ' nothref="' . $hrefvalue . '"';
		//if (isset($_SESSION[SUMAC_SESSION_EVENT_NAMES]) && (count($_SESSION[SUMAC_SESSION_EVENT_NAMES]) > 1))
			$href = ' href="' . $hrefvalue . '"';
		$html .= '  <a class="sumac_navlink sumac_action_link_event"' . $href . $nothref
					. ' title="' . $_SESSION[SUMAC_STR]["TT1"] . '">' . $_SESSION[SUMAC_STR]["TL3"] . '</a> |' . "\n";
	}

	$href = '';
	if ($checkout)
	{
		$fullNavClass = $navClass . " sumac_action_link_checkout";
		$hrefvalue = 'sumac_ticketing_redirect.php?function=order' . $hrefParameters;
		$nothref = ' nothref="' . $hrefvalue . '"';
		if (sumac_countTicketOrdersInBasket() > 0) $href = ' href="' . $hrefvalue . '"';
		$html .= '  <a class="' . $fullNavClass . '"' . $href . $nothref
					. ' title="' . $_SESSION[SUMAC_STR]["TT3"] . '">' . $_SESSION[SUMAC_STR]["TL4"] . '</a> |' . "\n";
	}

	$href = '';
	$fullNavClass = $navClass . " sumac_action_link_restart";
	$hrefvalue = 'sumac_ticketing_redirect.php?function=restart' . $hrefParameters;
	$nothref = ' nothref="' . $hrefvalue . '"';
	if (sumac_countTicketOrdersInBasket() > 0) $href = ' href="' . $hrefvalue . '"';
	$html .= '  <a class="' . $fullNavClass . '"' . $href . $nothref
				. ' title="' . $_SESSION[SUMAC_STR]["TT4"] . '">'	. $_SESSION[SUMAC_STR]["TL5"] . '</a>' . "\n";

	$html .= ' </div>' . "\n";
	return $html;
}

function sumac_getBasketAndExtrasHTML($theatreDocument,$extrasDocument,$orderNotYetTaken)
{
	$html = '<div class="sumac_showhide_div"><button class="sumac_basket_showhide" style="float:right"'
				. ' onclick="sumac_hide_table(this,\'' . SUMAC_ID_DIV_ORDERBASKET . '\',\'' . $_SESSION[SUMAC_STR]["TL8"]
				. '\',\'' . $_SESSION[SUMAC_STR]["TL7"] . '\',\'sumac_maintable\')">' . $_SESSION[SUMAC_STR]["TL7"] . '</button></div>' . "\n";
	$html .= '<div id="' . SUMAC_ID_DIV_ORDERBASKET . '" class="sumac_maintable">' . "\n";
	$html .= '<table class="sumac_basket">' . "\n";

	$basket = null;
	if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET])) $basket = $_SESSION[SUMAC_SESSION_TICKET_BASKET];
	$html .= sumac_getHTMLTableRowsForBasket($basket,$theatreDocument,$orderNotYetTaken);

	$html .= sumac_getHTMLTableRowsForExtras($extrasDocument);

	$html .= '</table>' . "\n";
	$html .= '</div><br />' . "\n";

	return $html;
}

function sumac_getHTMLTableRowsForBasket($basket,$theatreDocument,$orderNotYetTaken)
{
//May2012: each row has five elements:
//'revisit ticket selection page' icon and link
//seat or number of tickets
//performance (i.e. production name and happening-time)
//cost (before extras)
//'delete from order' icon and link

	if ($orderNotYetTaken) $html = '<tr><td colspan="4" align="left"><b>' . $_SESSION[SUMAC_STR]["TU4"] . '</b></td></tr>' . "\n";
	else $html = '<tr><td colspan="4" align="left"><b>' . $_SESSION[SUMAC_STR]["TU5"] . '</b></td></tr>' . "\n";

	if (sumac_countTicketOrdersInBasket() > 0)
	{
		foreach ($basket as $eventId => $eventTickets)
		{
			foreach ($eventTickets as $tid => $t)
			{
				if ($t['quantity'] == 'seat')
				{
					$seat = $_SESSION[SUMAC_STR]["TU7"] . $t['label'] . '&nbsp;[' . $t['requirement'] . ']';
				}
				else
				{
					$sellingunitElement = $theatreDocument->getElementById($t['sellingUnit']);
					$sellingunitName = $sellingunitElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
					if ($t['quantity'] == 1) $seat = $_SESSION[SUMAC_STR]["TU29"];
					else $seat = $t['quantity'] . $_SESSION[SUMAC_STR]["TU8"];
					$seat .= $_SESSION[SUMAC_STR]["TU6"] . sumac_centsToPrintableDollars($t['price']) .
//								'&nbsp;[' . $t['requirement'] . '] ' . $sellingunitName ;
								'&nbsp;[' . $t['requirement'] . ']<br />' . $sellingunitName ;
				}

				$html .= '<td>' . $seat . '</td>';

				$nothref = '';
				$href = '';
				if ($orderNotYetTaken)
				{
					$hrefvalue = 'sumac_ticketing_redirect.php?function=revisit&amp;event=' . $eventId;
					$nothref = ' nothref="' . $hrefvalue . '"';
					$href = ' href="' . $hrefvalue . '"';
				}

				$html .= '<td><a class="sumac_action_link_revisit"' . $nothref . $href
							. ' title="revisit this selection page">' . $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] . '</a></td>';
				$cost = ($t['quantity'] == 'seat') ? sumac_centsToPrintableDollars($t['price']) : sumac_centsToPrintableDollars($t['price'] * $t['quantity']);
				$html .= '<td align="right">' . $cost. '</td>';

//another possible mechanism would be a separate td element with its own linked icon, something more like this ...
//				$html .= '<td align="right"><a href="?????" title="delete from order" >'
//							. '<img src="smalldelete.ico" alt="delete?" width="16" height="16"'
//							. ' onclick="return (confirm(\'Click OK to delete ' . $seat . ' for ' . $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] . '\'));" /> /></a></td>' . "\n";
//or this,
//				$html .= '<td align="right"><a href="?????" title="delete this from order" >'
//							. '<img class="greyreddelete" src="img_trans.gif" alt="delete?" width="16" height="16"'
//							. ' onclick="return (confirm(\'Click OK to delete ' . $seat . ' for ' . $_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId] . '\'));" /> /></a></td>' . "\n";
// using classes like
//img.greyreddelete {width:16px;height:16px;background:url(deleterevisitboth.ico) 0 0;}
//img.greyreddelete:hover {width:16px;height:16px;background:url(deleterevisitboth.ico) 0 -16px;}
//combined with a composite image (sprite) with four 16x16 icons and a blank image, img_trans.gif

				//$html .= '<td>' . $_SESSION[SUMAC_STR]["TU3"] . getSeatSection($tid) . '</td>';
				$html .= '</tr>' . "\n";
			}
		}
	}
	return $html;
}

function sumac_addXMLTicketsFromBasket()
{
	$xml = '<tickets>';
	foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $eventId => $eventTickets)
	{
		$eventpromo = $_SESSION[SUMAC_SESSION_TICKETING_PROMOTIONS][$eventId];
//v5.4.0 add an event element (with optional promotion) instead of the performance id in the ticket
//			but retain the performance id in the ticket for now for backward compatibility
		$xml .= '<event ei="' . $eventId . '"' . (($eventpromo != '' ) ? ' ep="' . $eventpromo . '"' : '') .'>';
		foreach ($eventTickets as $tid => $t)
		{
//v1.6, Jan 2012: send the requirement id instead of the letter-code which means nothing to Sumac
			$xml .= '<ticket p="' . $eventId . '" c="' . $t['reqid'] . '" v="' . $t['price'];
			if ($t['quantity'] == 'seat')
			{
				$xml .= '" s="' . $tid;
			}
			else
			{
				$xml .= '" b="' . $t['sellingUnit'] . '" q="' . $t['quantity'];
			}
			$xml .= '"></ticket>';
		}
		$xml .= '</event>';
	}
	$xml .= '</tickets>';
	return $xml;
}

function sumac_ticketing2_summary_table($organisationDocument,$extrasDocument,$orderNotYetTaken)
{
/*
cost summary table
	top row spanning all columns = title 'Summary' with button to hide/show the rest of the table
	one row for each ticket: seatcount+price+requirement+sellingunit, event, cost
	one [optional] row for text explaining about the extras
	one row for each extra cost: name+explanation, cost
	one row for total
*/
/*
<!ELEMENT extra_cents (#PCDATA)>
	name CDATA #REQUIRED
	explanation CDATA #IMPLIED

<!ELEMENT total_cents (#PCDATA)>
*/
	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the stringids are not duplicated
	$html = sumac_geth2_tabletag('ticketing2','summary','summary');
	$html .= sumac_geth2_tbodytag('ticketing2','summary_title','summary_title');
	$html .= sumac_geth2_trtag('ticketing2','summary_title','summary_title').PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','summary_title','summary_title',2);
	$html .= sumac_geth2_sumac_subtitle('ticketing2','H3',1,true);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','summary_hideshow','summary_hideshow');
	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$html .= sumac_geth2_hideshow_buttons('ticketing2','summary_detail','summary_detail','tbody','L6','L7');
	}
	$html .= '</td></tr></tbody>'.PHP_EOL;
	$html .= sumac_geth2_tbodytag('ticketing2','summary_detail',array('summary_detail','showing_tbody'));

	$basket = isset($_SESSION[SUMAC_SESSION_TICKET_BASKET]) ? $_SESSION[SUMAC_SESSION_TICKET_BASKET] : null;
	$rowid = 0;
	$totalcents = 0;

	if (sumac_countTicketOrdersInBasket() > 0)
	{
		foreach ($basket as $eventId => $eventTickets)
		{
			foreach ($eventTickets as $tid => $t)
			{
				$html .= sumac_ticketing2_summary_row($rowid,$eventId,$tid,$t,$organisationDocument,$orderNotYetTaken);
				$rowid++;
			}
		}

		$html .= sumac_geth2_trtag('ticketing2','extras_title','extras_title').PHP_EOL;
		$html .= sumac_geth2_tdtag('ticketing2','extras_title','extras_title',2);
		$html .= sumac_geth2_span('ticketing2','extras_title','extras_title','U3');
		$html .= '</td>'.PHP_EOL;
		$html .= '<td></td>'.PHP_EOL;
		$html .= '</tr>'.PHP_EOL;

		$extraElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_EXTRA_CENTS);
		$extraCount = $extraElements->length;
		for ($i = 0; $i < $extraCount; $i++)
		{
			$extraElement = $extraElements->item($i);
			$extracents = ($extraElement->childNodes->item(0) != null) ? $extraElement->childNodes->item(0)->nodeValue : '0';
			$html .= sumac_geth2_trtag('ticketing2','extra_'.$i,'extra').PHP_EOL;
			$html .= '<td></td>'.PHP_EOL;
			$html .= sumac_geth2_tdtag('ticketing2','extraname_'.$i,'extraname');
			$html .= $extraElement->getAttribute(SUMAC_ATTRIBUTE_NAME).' '.$extraElement->getAttribute(SUMAC_ATTRIBUTE_EXPLANATION);
			$html .= '</td>'.PHP_EOL;
			$html .= sumac_geth2_tdtag('ticketing2','extracost_'.$i,'extracost');
			$html .= sumac_centsToPrintableDollars($extracents);
			$html .= '</td>'.PHP_EOL;
			$html .= '</tr>'.PHP_EOL;
		}

		$totalcentsElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_TOTAL_CENTS);
		$totalcents = ($totalcentsElements->item(0)->childNodes->item(0) != null) ? $totalcentsElements->item(0)->childNodes->item(0)->nodeValue : '0';
	}

	$html .= '</tbody>'.PHP_EOL;
	$html .= sumac_geth2_tbodytag('ticketing2','summary_total','summary_total');
	$html .= sumac_geth2_trtag('ticketing2','total','total').PHP_EOL;
	$html .= '<td></td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','total','total');
	$totalstring = $orderNotYetTaken ? 'H4' : 'H8';
	$html .= sumac_geth2_sumac_subtitle('ticketing2',$totalstring,'_total',true);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','totalcost','totalcost');
	$html .= sumac_centsToPrintableDollars($totalcents);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

	if (sumac_countTicketOrdersInBasket() > 0)
	{
		$remarkElements = $extrasDocument->getElementsByTagName(SUMAC_ELEMENT_REMARK);
		if ($remarkElements->item(0)->childNodes->item(0) != null)
		{
			$html .= sumac_geth2_trtag('ticketing2','remarks','remarks').'<td></td>'
						.sumac_geth2_tdtag('ticketing2','remarks','remarks',2)
						.$remarkElements->item(0)->childNodes->item(0)->nodeValue
						.'</td></tr>'.PHP_EOL;
		}
	}

	$html .= '<tr>'.sumac_geth2_tdtag('ticketing2','total_now','nodisplay').$totalcents.'</td></tr>'.PHP_EOL;
	$html .= '</tbody>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);
	return $html;
}

function sumac_ticketing2_summary_row($rowid,$eventId,$tid,$t,$organisationDocument,$orderNotYetTaken)
{
	$html = sumac_geth2_trtag('ticketing2','summary_seat_'.$rowid,'summary_seat').PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','summary_seat_'.$rowid,'summary_seat');
	if ($t['quantity'] == 'seat')
	{
//T2U1|TextSummarySeat|seat %a [%b]|
		$html .= sumac_geth2_span('ticketing2','summary_seat_'.$rowid,'summary_seat','U1',true,array($t['label'],$t['requirement']));
	}
	else
	{
		$sellingunitElement = $organisationDocument->getElementById($t['sellingUnit']);
		$sellingunitName = $sellingunitElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
//T2U2|TextSummaryTickets|%a ticket%b at %c [%d]<br />%e|
		$html .= sumac_geth2_span('ticketing2','summary_seat_'.$rowid,'summary_tickets','U2',true,
								array($t['quantity'],(($t['quantity'] == 1)?'':'s'),
										sumac_centsToPrintableDollars($t['price']),$t['requirement'],
										$sellingunitName));
	}
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','summary_event_'.$rowid,'summary_event');
	$hrefvalue = 'sumac_ticketing_redirect.php?function=revisit&amp;event='.$eventId;
	$href = $orderNotYetTaken ? $hrefvalue : '';
	$nothref = $orderNotYetTaken ? (' data-href="'.$hrefvalue.'"') : '';
	$moreattrs = $nothref.sumac_geth2_attrtext('ticketing2','T1','title');
	$html .= sumac_geth2_link('ticketing2','summary_event_'.$rowid,'action_link_revisit',$href,
								$_SESSION[SUMAC_SESSION_EVENT_NAMES][$eventId],false,$moreattrs);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('ticketing2','summary_cost_'.$rowid,'summary_cost');
	$html .= ($t['quantity'] == 'seat') ? sumac_centsToPrintableDollars($t['price']) : sumac_centsToPrintableDollars($t['price'] * $t['quantity']);
	$html .= '</td>'.PHP_EOL;
	return $html;
}

function sumac_ticketing2_title_with_user()
{
	if (isset($_SESSION[SUMAC_SESSION_ACCOUNT_DETAILS]))	//the user is logged in
	{
		$savedAccountDocument = sumac_reloadLoginAccountDocument();
		return sumac_geth2_sumac_title_with_gobacklink_and_line('ticketing2','H2','L1',true,
						array(sumac_getElementValue($savedAccountDocument,SUMAC_ELEMENT_FIRSTNAME),
							sumac_getElementValue($savedAccountDocument,SUMAC_ELEMENT_LASTNAME),
							$_SESSION[SUMAC_SESSION_EMAILADDRESS]));
	}
	else
	{
		return sumac_geth2_sumac_title_with_gobacklink_and_line('ticketing2','H5','L1',true);
	}
}

?>