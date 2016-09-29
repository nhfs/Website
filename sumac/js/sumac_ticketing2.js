//version567//
function sumac_unhide_detail(prod)
{
	document.getElementById("sumac_detail_" + prod).className = "sumac_detailpanel";
	document.getElementById("sumac_button_showhide_" + prod).className = "sumac_hide_detail";
	document.getElementById("sumac_button_showhide_" + prod).innerHTML = sumac_get_string_from_id('T2','L4',null);

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
	document.getElementById("sumac_button_showhide_" + prod).innerHTML = sumac_get_string_from_id('T2','L5',null);
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

function sumac_ticketing2_set_amountpaid()
{
	document.getElementById('sumac_input_ticketing2_amountpaid').value = document.getElementById('sumac_td_ticketing2_total_now').innerHTML;
}

var sumac_seats_in_order_basket = new Array();
var sumac_last_category_picked = '';

function sumac_promotion_entered(promo_input)
{
	var promocode = promo_input.value.toUpperCase();
	if (sumac_active_promocode != promocode)
	{
		sumac_active_promocode = promocode;
		sumac_active_promotion = -1;
		for (var i = 0; i < sumac_promotions.length; i++)
		{
			var pm = sumac_promotions[i];
			if (sumac_active_promocode == pm.code)
			{
				sumac_active_promotion = i;
				document.getElementById(sumac_id_td_promotion_discount).className = 'sumac_discount_total';
				document.getElementById(sumac_id_td_cost_after_discount).className = 'sumac_discount_total';
				break;
			}
		}
		if (sumac_active_promotion < 0)
		{
			document.getElementById(sumac_id_td_promotion_discount).className = 'sumac_nodisplay';
			document.getElementById(sumac_id_td_cost_after_discount).className = 'sumac_nodisplay';
		}
		document.getElementById(sumac_id_span_promotion_discount).value = sumac_centsToPrintableDollars(0);
		document.getElementById(sumac_id_span_cost_after_discount).value = sumac_centsToPrintableDollars(0);
		sumac_updateTheatre();
	}
}

function sumac_reenter_promotion(promo_id)
{
	sumac_active_promotion = -1;
	for (var i = 0; i < sumac_promotions.length; i++)
	{
		var pm = sumac_promotions[i];
		if (promo_id == pm.id)
		{
			sumac_active_promotion = i;
			break;
		}
	}
	if (sumac_active_promotion >= 0)
	{
		sumac_active_promocode = sumac_promotions[sumac_active_promotion].code;
		document.getElementById(sumac_id_input_promocode).value = sumac_active_promocode;
		document.getElementById(sumac_id_td_promotion_discount).className = 'sumac_discount_total';
		document.getElementById(sumac_id_td_cost_after_discount).className = 'sumac_discount_total';
	}
}

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
	var promotionid = sumac_active_promotion;
	var seats_in_order = 0;
	for (var i = 0; i < sumac_seats_in_order_basket.length; i++) seats_in_order += sumac_seats_in_order_basket[i].quantity;
	if ((promotionid < 0) || (seats_in_order < sumac_promotions[promotionid].min)) promotionid = -1;
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
	var discounttotalcents = 0;
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
			var discountcents = (promotionid >= 0) ? sumac_calc_discount(promotionid,thesetickets,p) : Number(0);
			discounttotalcents += discountcents;
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
				if (discountcents > 0) newspan.title = sumac_formatMessage(sumac_less_discount_of,
														sumac_centsToPrintableDollars(seatcents),
														sumac_centsToPrintableDollars(discountcents));
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
	//document.getElementById(sumac_id_tr_picked_list).innerHTML = tickets_in_basket;
	document.getElementById(sumac_id_span_total_cost).innerHTML = sumac_centsToPrintableDollars(totalcents);
	document.getElementById(sumac_id_span_promotion_discount).innerHTML = sumac_centsToPrintableDollars(discounttotalcents);
	document.getElementById(sumac_id_span_cost_after_discount).innerHTML = sumac_centsToPrintableDollars(totalcents - discounttotalcents);

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

function sumac_calc_discount(pi,number_so_far,p)
{
	var available_for_discount = sumac_promotions[pi].max - Number(number_so_far);
	var discount = 0;
	var qty = p.quantity;
	if (qty > available_for_discount) qty = available_for_discount;
	if (qty > 0)
	{
		if (sumac_promotions[pi].discpc != null) discount = qty * (p.price * (sumac_promotions[pi].discpc/100));
		else if (sumac_promotions[pi].discamt != null) discount = qty * sumac_promotions[pi].discamt;
	}
	return discount;
}

function sumac_updateBuyables()
{
	var basketlist = sumac_event_id + '=';
	//the active promotionid is the first element (before the tickets)
	if (sumac_active_promotion >= 0) basketlist = basketlist + sumac_promotions[sumac_active_promotion].id;
	basketlist = basketlist + '%2B';	// and the promotion id is followed by a plus sign
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
