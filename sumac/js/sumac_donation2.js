//version567//

	function sumac_donation2_change_frequency()
	{
		var fcode = document.getElementById("sumac_input_donation2_frequency").value;
		document.getElementById("sumac_input_donation2_frequency").value = (fcode < 0) ? 1 : -1;
	}

	function sumac_donation2_set_amountpaid()
	{
//nothing to do here - all done already in sumac_donation2_show_amounttopay()
	}

	function sumac_donation2_show_amounttopay(frequency)
	{
		var amounttopay = 0;
		var onlyamounts = document.getElementsByName('onlyamount');
		if (onlyamounts.length == 1)
		{
			amounttopay = Math.round(onlyamounts[0].value * 100);
			found = true;
		}
		else
		{
			var radioamounts = document.getElementsByName('donationamount');
			var found = false;
			for (var i = 0; i < radioamounts.length; i++)
			{
				if (radioamounts[i].checked)
				{
					if (radioamounts[i].value != '')
					{
						amounttopay = radioamounts[i].value;
						found = true;
						break;
					}
					else
					{
						var otheramounts = document.getElementsByName('otheramount');
						if (otheramounts.length == 1)
						{
							var otheramount = otheramounts[0].value;
							amounttopay = Math.round(otheramount * 100);
							found = true;
							break;
						}
					}
				}
			}
		}
		if (!found)
		{
			var otheramounts = document.getElementsByName('otheramount');
			if (otheramounts.length == 1)
			{
				var otheramount = otheramounts[0].value;
				if (otheramount != '') amounttopay = Math.round(otheramount * 100);
			}
		}
//set the amount that will be sent to Sumac
		document.getElementById('sumac_input_donation2_amountpaid').value = amounttopay;
//show the same amount in the warning
		var changeable = (frequency == 'C');
		var monthly = (frequency == 'M');
		if (changeable) monthly = document.getElementById('sumac_input_donation2_makemonthly').checked;
		var newWarningId = (monthly ? 'PI2' : 'PI1');
		//var oldWarningId = ((frequency == 'M') ? 'PI2' : 'PI1');
		var warning = document.getElementById('sumac_sid_D2PI1'); // + oldWarningId);
		if (warning) warning.innerHTML = sumac_get_string_from_id('D2',newWarningId,[sumac_get_string_from_id('D2','PL1'),sumac_centsToPrintableDollars(amounttopay)]);
	}

	function sumac_donation2_filter_dropdown(match)
	{
		var words = sumac_form_word_array(match);
		var newmatch = (words.length > 0) ? words[words.length-1] : match;
		if ((newmatch.length < 3) && (match.length > 0)) return;

		var selector = document.getElementById('sumac_select_donation2_funds');
		if (selector.selectedIndex == -1) { return; }
		var optcount = selector.length;
		for (var i = (optcount-1); i >= 0; i--)
		{
			selector.remove(i);
		}

		var hidden_selector = document.getElementById('sumac_select_donation2_funds_hidden');
		var hidden_optcount = hidden_selector.length;
		if (match.length < 1)
		{
			for (var i = 0; i < hidden_optcount; i++)
			{
				var opt = hidden_selector.options[i];
				var newopt = opt.cloneNode(true);
				selector.add(newopt);
			}
			document.getElementById('sumac_td_donation2_kw_matches').innerHTML = '';
			if (selector.length == 1) selector.options[0].text = sumac_get_string_from_id('D2','J1');
			else if (selector.length == 2) selector.options[0].text = sumac_get_string_from_id('D2','J2');
			else selector.options[0].text = sumac_get_string_from_id('D2','J3',[String(selector.length - 1)]);
			return;
		}

		var foundkws = [];
		var matchedkeywords = '';
		for (var i = 0; i < sumac_fundkws.length; i++)
		{
			for (var j = 0; j < words.length; j++)
			{
				if (sumac_fundkws[i].toUpperCase().indexOf(words[j].toUpperCase()) >= 0)
				{
					foundkws.push(sumac_fundkws[i]);
					matchedkeywords = (matchedkeywords == '') ? sumac_fundkws[i] : matchedkeywords + ', ' + sumac_fundkws[i];
					break;
				}
			}
		}
		for (var i = 0; i < hidden_optcount; i++)
		{
			var opt = hidden_selector.options[i];
			var newopt = opt.cloneNode(true);
			var value = opt.value;
			var text = opt.text;
			var keywords = opt.getAttribute('title');
			if (i == 0) selector.add(newopt);
			else
			{
				newopt.setAttribute('title',keywords.replace(/[ ;,\t]+/gm,'  '));
				//newopt.text = newopt.text + ' [' + keywords + ']';
				if (sumac_donation2_keyword_matches(keywords,foundkws)) { selector.add(newopt); }
				else if (sumac_donation2_text_matches(text,words)) { selector.add(newopt); }
			}
		}
		var kwreport = '';
		if (foundkws.length > 0)
		{
			kwreport = '<br />' + matchedkeywords;
		}

		document.getElementById('sumac_td_donation2_kw_matches').innerHTML = kwreport;
		if (selector.length == 1) selector.options[0].text = sumac_get_string_from_id('D2','J1');
		else if (selector.length == 2) selector.options[0].text = sumac_get_string_from_id('D2','J2');
		else selector.options[0].text = sumac_get_string_from_id('D2','J3',[String(selector.length - 1)]);
	}

	function sumac_donation2_keyword_matches(keywords,foundkws)
	{
		if (keywords == null) return false;
		for (var i = 0; i < foundkws.length; i++)
		{
			if (keywords.toUpperCase().indexOf(foundkws[i].toUpperCase()) >= 0) return true;
		}
		return false;
	}

	function sumac_donation2_text_matches(text,words)
	{
		if (words.length == 0) return false;
		for (var i = 0; i < words.length; i++)
		{
			if (text.toUpperCase().indexOf(words[i].toUpperCase()) >= 0) return true;
		}
		return false;
	}
