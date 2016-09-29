<?php
//version440//

include_once 'sumac_constants.php';

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
								'&nbsp;[' . $t['requirement'] . '] ' . $sellingunitName ;
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
	}
	$xml .= '</tickets>';
	return $xml;
}


?>