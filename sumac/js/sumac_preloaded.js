//version5681//

function sumac_centsToPrintableDollars(cents)
{
	var dollars = Number(cents) / 100;
	return (sumac_currency_symbol + dollars.toFixed(2));
}

function keyPress(e)
{
	var x = e || window.event;
	var key = (x.keyCode || x.which);
	if (key == 13 || key == 3)
	{ return false; } else { return true; }
/*
	{
		var f = x.target.form;
		var bm = 'one of the buttons';
		var sbs = new Array();
		if (f)
		{
			var fels = f.elements;
			for (var i = 0; i < fels.length; i++)
			{
				if (fels[i].type == 'submit') sbs.push(fels[i].value);
			}
		}
		if (sbs.length > 0)
		{
			bm = 'the ' + sbs[0] + ' button';
			if (sbs.length > 1)
			{
				for (var i = 1; i < sbs.length; i++)
				{
					bm += ',\nor the ' + sbs[i] + ' button';
				}
			}
			alert('To submit this form, please click\n' + bm);
		}
		return false;
	}
	return true;
*/
}

function sumac_confirm_use_of_HTTP(confirmHTTPmessage)
{
	if (confirm(confirmHTTPmessage) != true)
	{
		document.body.style.backgroundColor="#404040";
		var inputs = document.getElementsByTagName("input");
		for (var i = 0; i < inputs.length; i++)
		{
			var input = inputs[i];
			if (input.getAttribute("type") == "submit")
			{
				input.setAttribute("disabled","disabled"); input.style.backgroundColor = "#404040";
			}
			if ((input.type == "text") || (input.type == "password") || (input.type == "radio") || (input.type == "checkbox"))
			{
				input.setAttribute("disabled","disabled"); input.style.backgroundColor = "#202020";
			}
		}
		var buttons = document.getElementsByTagName("button");
		for (var i = 0; i < buttons.length; i++)
		{
				var button = buttons[i];
				button.setAttribute("disabled","disabled");
				button.style.backgroundColor="#404040";
		}
		var selects = document.getElementsByTagName("select");
		for (var i = 0; i < selects.length; i++)
		{
				var select = selects[i];
				select.setAttribute("disabled","disabled");
				select.style.backgroundColor="#404040";
		}
		var textareas = document.getElementsByTagName("textarea");
		for (var i = 0; i < textareas.length; i++)
		{
				var textarea = textareas[i];
				textarea.setAttribute("disabled","disabled");
				textarea.style.backgroundColor="#202020";
		}
		var noFocus = true;
		var links = document.getElementsByTagName("a");
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			var isExit = ((link.id.substr(0,16) == "sumac_link_leave")
						|| (link.id.substr(0,20) == "sumac_link_quit_HTTP")
						|| (link.className.substr(0,21) == "sumac_leavesumac_link"));
			if (noFocus && isExit)
			{ link.focus(); link.style.textDecoration = "underline"; link.style.color = "Green"; noFocus = false; }
			else
			{ link.className = "sumac_disabled_navlink"; link.removeAttribute("href"); link.setAttribute("onclick",";"); }
		}
	}
	else
	{
		var links = document.getElementsByTagName("a");
		for (var i = 0; i < links.length; i++)
		{
			var link = links[i];
			if (link.id.substr(0,20) == "sumac_link_quit_HTTP") link.className = "sumac_nodisplay";
		}
	}
}

function sumac_formatMessage(message)
{
	var fm = message;
	var rc = arguments.length;
	for (var i = 1; i < rc; i++)
	{
		var r = '%' + (i-1);
		var re = new RegExp(r,'g');
		var fm = fm.replace(re,arguments[i]);
	}
	return fm;
}

function sumac_formatMessageWithLocalVariables()
{	var fm = this;
	var rc = arguments.length;
	for (var i = 0; i < rc; i++)
	{	//var r = '%-' + (i-1);	use of minus sign not liked by PT - use alphabet instead
		var r = '%' + ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p'][i];
		var re = new RegExp(r,'ig');
		var fm = fm.replace(re,arguments[i]);
	}
	return fm;
}

function sumac_formatMessageWithGlobalVariables(message)
{	var fm = message;
	var rc = arguments.length;
	for (var i = 1; i < rc; i++)
	{	var r = '%' + (i-1);
		var re = new RegExp(r,'g');
		var fm = fm.replace(re,arguments[i]);
	}
	return fm;
}

function sumac_formatMessageToRemoveGlobalInserts(message)
{	var fm = message;
	var rc = 7;	//only six globals at present
	for (var i = 1; i < rc; i++)
	{	var r = '%' + (i-1);
		var re = new RegExp(r,'g');
		var fm = fm.replace(re,'');
	}
	return fm;
}

function sumac_set_strings()
{	var otitle = sumac_get_override_string(sumac_title_sid.id);
	if (otitle != null) document.title = sumac_formatMessageWithGlobalVariables(otitle,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
	else document.title = sumac_formatMessageWithGlobalVariables(sumac_title_sid.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
	for (var i = 0; i < sumac_innerHTML_sids.length; i++)
	{	var entry = sumac_innerHTML_sids[i];
		var id = entry.id;
		var el = document.getElementById('sumac_sid_' + id);
		if (!el) continue; //not in this page today
		var ostr = sumac_get_override_string(id);
		if (entry.ref) // only a variant - ref identifies shared master entry
		{
			var refid = entry.ref;
			entry = sumac_get_innerHTML_shared_entry(refid);
			if (entry == null) continue;	//bad news
			if (ostr == null)	//user hasn't defined a variant
			{
				ostr = sumac_get_override_string(refid);
			}
		}
		if (ostr != null) str = sumac_formatMessageWithGlobalVariables(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		else str = sumac_formatMessageWithGlobalVariables(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		var cls = entry.cls;
		if ((cls) && (str == ""))
		{
			var els = document.getElementsByClassName(cls);
			for (var j = 0; j < els.length; j++)
			{
				var e = els[j]
				if (e.className.indexOf('sumac_nodisplay') < 0) e.className = e.className + ' sumac_nodisplay';
			}
		}
		//el.innerHTML = '<span class="sumac_str_id sumac_nodisplay">'+ id + '=</span>' + str;
		var fstr = str;
		var elp = el.parentNode;
		var insert = elp.getAttribute('data-ins0');
		var insnum = '0';
		var inserts = new Array();
		while (insert != null)
		{
			if (insert.substr(0,7) == 'SUMACID') insert = sumac_get_string_from_fullid(insert.substr(7),null);
			inserts.push(insert);
			insnum = Number(insnum) + 1;
			insert = elp.getAttribute('data-ins'+String(insnum));
		}
		if (inserts.length > 0) fstr = sumac_formatMessageWithLocalVariables.apply(str,inserts);
		el.innerHTML = fstr;
		if (entry.dup)
		{
			var suffix = '1';
			while (true)
			{
				el = document.getElementById('sumac_sid_' + id + '-' + suffix);
				if (!el) break;
				fstr = str;
				elp = el.parentNode;
				var insert = elp.getAttribute("data-ins0");
				var insnum = '0';
				var inserts = new Array();
				while (insert != null)
				{
					if (insert.substr(0,7) == 'SUMACID') insert = sumac_get_string_from_fullid(insert.substr(7),null);
					inserts.push(insert);
					insnum = Number(insnum) + 1;
					insert = elp.getAttribute('data-ins'+String(insnum));
				}
				if (inserts.length > 0) fstr = sumac_formatMessageWithLocalVariables.apply(str,inserts);
				el.innerHTML = fstr;
				suffix = Number(suffix) + 1;
			}
		}
	}

	for (var i = 0; i < sumac_value_sids.length; i++)
	{	var entry = sumac_value_sids[i];
		var id = entry.id;
		var el = document.getElementById('sumac_' + entry.tag + '_' + id);
		if (!el) continue; //not in this page today
		var ostr = sumac_get_override_string(id);
		if (entry.ref) // only a variant - ref identifies shared master entry
		{
			id = entry.ref;
			entry = sumac_get_value_shared_entry(id);
			if (entry == null) continue;	//bad news
			if (ostr == null)	//user hasn't defined a variant
			{
				ostr = sumac_get_override_string(id);
			}
		}
		if (ostr != null) str = sumac_formatMessageWithGlobalVariables(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		else str = sumac_formatMessageWithGlobalVariables(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
//alert('str=' + str + ',elid=' + 'sumac_' + entry.tag + '_' + id + ', ostr=' + ostr);
		el.value = str;
	}

	for (var i = 0; i < sumac_attribute_sids.length; i++)
	{	var entry = sumac_attribute_sids[i];
		var id = entry.id;
		var attr= entry.attr;
		var els = document.querySelectorAll("["+attr+"=\""+id+"\"]");
		if (els.length < 1) continue; //none in this page today
		var ostr = sumac_get_override_string(id);
		if (entry.ref) // only a variant - ref identifies shared master entry
		{
			id = entry.ref;
			entry = sumac_get_attribute_shared_entry(id);
			if (entry == null) continue;	//bad news
			if (ostr == null)	//user hasn't defined a variant
			{
				ostr = sumac_get_override_string(id);
			}
		}
		if (ostr != null) str = sumac_formatMessageWithGlobalVariables(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		else str = sumac_formatMessageWithGlobalVariables(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		for (var j = 0; j < els.length; j++)
		{
			els[j].setAttribute("data-"+attr,id);
			els[j].setAttribute(attr,str);
		}
	}
}

function sumac_get_override_string(id)
{	for (var i = 0; i < sumac_override_sids.length; i++)
	{	if (sumac_override_sids[i].id == id) return sumac_override_sids[i].str;
	}
	return null;
}

function sumac_get_innerHTML_shared_entry(id)
{	for (var i = 0; i < sumac_innerHTML_shared_sids.length; i++)
	{	if (sumac_innerHTML_shared_sids[i].id == id) return sumac_innerHTML_shared_sids[i];
	}
	return null;
}

function sumac_get_value_shared_entry(id)
{	for (var i = 0; i < sumac_value_shared_sids.length; i++)
	{	if (sumac_value_shared_sids[i].id == id) return sumac_value_shared_sids[i];
	}
	return null;
}

function sumac_get_attribute_shared_entry(id)
{	for (var i = 0; i < sumac_attribute_shared_sids.length; i++)
	{	if (sumac_attribute_shared_sids[i].id == id) return sumac_attribute_shared_sids[i];
	}
	return null;
}

function sumac_show_string_ids()
{
	var id = sumac_title_sid.id;
	if (sumac_get_override_string(id) != null) id = '*' + id + '*';
	document.title = '[' + id + '=]' + document.title;

	for (var i = 0; i < sumac_innerHTML_sids.length; i++)
	{	var id = sumac_innerHTML_sids[i].id;
		var el = document.getElementById('sumac_sid_' + id);
		if (sumac_get_override_string(id) != null) id = '*' + id + '*';
		if (el) el.innerHTML = '[' + id + '=]' + el.innerHTML;
		if (sumac_innerHTML_sids[i].dup)
		{
			var suffix = '1';
			while (true)
			{
				el = document.getElementById('sumac_sid_' + id + '-' + suffix);
				if (!el) break;
				el.innerHTML = '[' + id + '=]' + el.innerHTML;
				suffix = Number(suffix) + 1;
			}
		}
	}

	for (var i = 0; i < sumac_value_sids.length; i++)
	{	var id = sumac_value_sids[i].id;
		var el = document.getElementById('sumac_' + sumac_value_sids[i].tag + '_' + id);
		if (sumac_get_override_string(id) != null) id = '*' + id + '*';
		if (el) el.value = '[' + id + '=]' + el.value;
	}

	for (var i = 0; i < sumac_attribute_sids.length; i++)
	{	var id = sumac_attribute_sids[i].id;
		var attr= sumac_attribute_sids[i].attr;
		var els = document.querySelectorAll("[data-"+attr+"=\""+id+"\"]");
		if (els.length < 1) continue; //none in this page today
		for (var j = 0; j < els.length; j++)
		{
			els[j].setAttribute(attr,'[' + id + '=]' + els[j].getAttribute(attr));
		}
	}
}

function sumac_get_string_from_id(pkgcode,id,inserts)
{
	var fullid = pkgcode + id;
	return sumac_get_string_from_fullid(fullid,inserts);
}

function sumac_get_string_from_fullid(fullid,inserts)
{
	var str = '';
	var entry = null;
	for (var i = 0; i < sumac_innerHTML_sids.length; i++)
	{
		if (sumac_innerHTML_sids[i].id == fullid)
		{
			entry = sumac_innerHTML_sids[i];
			if (entry.ref) // only a variant - ref identifies shared master entry
			{
				var refid = entry.ref;
				entry = sumac_get_innerHTML_shared_entry(refid);
				if (entry == null) return '';	//bad news
				if (ostr == null)	//user hasn't defined a variant
				{
					ostr = sumac_get_override_string(refid);
				}
			}
			break;
		}
	}
	if (entry == null)
	{
		for (var i = 0; i < sumac_value_sids.length; i++)
		{
			if (sumac_value_sids[i].id == fullid)
			{
				entry = sumac_value_sids[i];
				if (entry.ref) // only a variant - ref identifies shared master entry
				{
					var refid = entry.ref;
					entry = sumac_get_value_shared_entry(refid);
					if (entry == null) return '';	//bad news
					if (ostr == null)	//user hasn't defined a variant
					{
						ostr = sumac_get_override_string(refid);
					}
				}
				break;
			}
		}
	}
//does not handle attribute sids
	if (entry == null) return '';	//bad news

	var ostr = sumac_get_override_string(fullid);

//	if (ostr != null) str = sumac_formatMessageWithGlobalVariables(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
//	else str = sumac_formatMessageWithGlobalVariables(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
	if (ostr != null) { str = ostr; fullid = '*' + fullid + '*'; }
	else str = entry.str;
	var fstr = sumac_formatMessageToRemoveGlobalInserts(str);
	if ((inserts != null) && (inserts.length > 0)) fstr = sumac_formatMessageWithLocalVariables.apply(fstr,inserts);
	if (typeof(sumac_showing_strings) !== 'undefined') fstr = '[' + fullid + '=]' + fstr;

	return fstr;
}

function sumac_restore_entered_text_value(formid,nvarray)
{
	var notused = new Array();
	for (var i = 0; i < nvarray.length; i++)
	{
		var name = nvarray[i][0];
		var namedfields = document.getElementsByName(name);
		if (namedfields.length == 1)
		{
			var nf = namedfields[0];
			if ((nf.form.id == formid)
				&& ((nf.type == 'text')
					|| (nf.type == 'password')
					|| (nf.type == 'textarea'))
				) nf.value = nvarray[i][1];
			else notused.push(nvarray[i]);
			// ignore password fields for now
		}
		else notused.push(nvarray[i]);
	//ignore cases where a name has multiple associated fields
	}
	return notused;
}

function sumac_restore_selections_made(formid,nvarray)
{
	var notused = new Array();
	for (var i = 0; i < nvarray.length; i++)
	{
		var name = nvarray[i][0];
		var namedfields = document.getElementsByName(name);
		if (namedfields.length == 1)
		{
			var nf = namedfields[0];
			if ((nf.form.id == formid) && (nf.type.substr(0,6) == 'select'))
			{
				for (var j = 0; j < nf.length; j++)
				{
					if (nf.options[j].value == nvarray[i][1])
					{
						nf.selectedIndex = j;
						break;
					}
				}
			}
			else notused.push(nvarray[i]);
		}
		else notused.push(nvarray[i]);
	//ignore cases where a name has multiple associated fields
	}
	return notused;
}

function sumac_restore_radio_picks_made(formid,nvarray)
{
	var notused = new Array();
	var done_already = '';
	for (var i = 0; i < nvarray.length; i++)
	{
		var name = nvarray[i][0];
		if (done_already.indexOf(name) < 0)
		{
			var namedfields = document.getElementsByName(name);
			var nf = namedfields[0];
			if ((typeof(nf) !== 'undefined') && (nf.form.id == formid) && (nf.type == 'radio'))
			{
				for (var j= 0; j < namedfields.length; j++)
				{
					nf = namedfields[j];
					if ((nf.form.id == formid) && (nf.type == 'radio') && (nf.value == nvarray[i][1]))
					{
						nf.checked = true;
						break;
					}
				}
				done_already = done_already + ',' + name;
			}
			else notused.push(nvarray[i]);
		}
	}
	return notused;
}

function sumac_restore_checked_boxes(formid,nvarray)
{
//first turn ALL checkboxes OFF - because the names of the ones that were OFF will be MISSING from nvarray
	var inputs = document.getElementsByTagName("input");
	for (var i = 0; i < inputs.length; i++)
	{
		if (inputs[i].type == "checkbox") inputs[i].checked = false;
	}
	var notused = new Array();
	for (var i = 0; i < nvarray.length; i++)
	{
		var name = nvarray[i][0];
		var namedfields = document.getElementsByName(name);
		if (namedfields.length == 1)
		{
			var nf = namedfields[0];
			if ((nf.form.id == formid) && (nf.type == 'checkbox')) nf.checked = true; //whatever the value
			else notused.push(nvarray[i]);
		}
		else notused.push(nvarray[i]);
	//ignore cases where a name has multiple associated fields
	}
//for (var i = 0; i < notused.length; i++) alert('name='+notused[i][0]+',value='+notused[i][1]);
	return notused;
}

function sumac_restore_hidden_values(formid,nvarray)
{
	var notused = new Array();
	for (var i = 0; i < nvarray.length; i++)
	{
		var name = nvarray[i][0];
		var namedfields = document.getElementsByName(name);
		if (namedfields.length == 1)
		{
			var nf = namedfields[0];
			if ((nf.form.id == formid) && (nf.type == 'hidden'))
			{
				nf.value = nvarray[i][1]; //same as text
//alert('changed '+nf.id+' value to '+nf.value);
				if (nf.onchange) nf.onchange();	// so that any linked display or other action takes place
			}
			else notused.push(nvarray[i]);
		}
		else notused.push(nvarray[i]);
	//ignore cases where a name has multiple associated fields
	}
//for (var i = 0; i < notused.length; i++) alert('name='+notused[i][0]+',value='+notused[i][1]);
	return notused;
}

function sumac_set_focus_to_error_message()
{
	var errormessages = document.getElementsByClassName('sumac_status');
	if (errormessages.length >= 1) errormessages[0].focus();
}

function sumac_attachment_add_to_list(pkg,filledformid,attmtId,attname,atttype,filesize,filetype,attdate)
{
	var wtd = window.top.document;
	var table = wtd.getElementById("sumac_table_" + pkg + "_attachments_" + filledformid);
	var rows = table.getElementsByTagName('TR');
	var body = rows[0].parentNode;
	var tr = rows[0].cloneNode(true); var td1 = tr.firstChild;
	td1.innerHTML = attmtId; var td2 = td1.nextSibling;
	td2.innerHTML = attname; var td3 = td2.nextSibling;
	td3.innerHTML = atttype; var td4 = td3.nextSibling;
	td4.innerHTML = filesize; var td5 = td4.nextSibling;
	td5.innerHTML = attdate; var td6 = td5.nextSibling;
	//td5.innerHTML = filetype;
	var td7 = td6.nextSibling;
	td7.className = "sumac_attachment_viewer"; var td8 = td7.nextSibling;
	td8.className = "sumac_attachment_deleter";
	tr.title=attmtId;
	body.appendChild(tr);
	wtd.getElementById("sumac_div_" + pkg + "_attach_new_" + filledformid).className += " sumac_nodisplay";
}

function sumac_attachment_delete_from_list(pkg,filledformid,attmtId)
{
	var wtd = window.top.document;
	var rowtodelete = null;
	var table = wtd.getElementById("sumac_table_" + pkg + "_attachments_" + filledformid);
	if (table)
	{
		var rows = table.getElementsByTagName('TR');
		for (var i = 0; i < rows.length; i++)
		{
			if (rows[i].title == attmtId)
			{
				rowtodelete = rows[i];
				break;
			}
		}
	}
	if (rowtodelete == null)
	{
		table = wtd.getElementById("sumac_table_" + pkg + "_attachments_" + filledformid);
		if (table)
		{
			var rows = table.getElementsByTagName('TR');
			for (var i = 0; i < rows.length; i++)
			{
				if (rows[i].title == attmtId)
				{
					rowtodelete = rows[i];
					break;
				}
			}
		}
		if (rowtodelete == null) return;
	}
	rowtodelete.parentNode.removeChild(rowtodelete);
}

function sumac_disable_used_attachment_types(doc,pkg,filledformid)
{
	//var wtd = window.top.document;
	var table = doc.getElementById("sumac_table_" + pkg + "_attachments_" + filledformid);
	var atttypes = new Array();
	var usedoptions = new Array();
	var rows = table.getElementsByTagName('TR');
	//first row is template only so ignore it
	for (var i = 1; i < rows.length; i++)
	{
		var tr = rows[i];
		var td1 = tr.firstChild;
		var td2 = td1.nextSibling;
		var td3 = td2.nextSibling;
		atttypes.push(td3.innerHTML); //collect the type-names that have been used
//alert('atttype='+td3.innerHTML);
	}
	var selector = doc.getElementById("sumac_select_" + pkg + "_atttype_" + filledformid);
	var optcount = selector.length;
	for (var i = (optcount-1); i >= 0; i--)
	{
		var optionvalue = selector.options[i].value;
//alert('optionvalue='+optionvalue);
		var optionused = false;
		for (var j = 0; j < atttypes.length; j++)
		{
			if (optionvalue == atttypes[j])	//disable this choice - type has been used already
			{
				selector.options[i].disabled = true;
				selector.options[i].text = selector.options[i].value + ' (used)';
				optionused = true;
				//usedoptions.push(selector.remove(selector.options[i]));
				usedoptions.push(selector.options[i]);	//this apparently removes the option from the selector
				break;
			}
		}
		if (optionused == false) //type not used - make sure option is enabled and text equals value
		{
			selector.options[i].disabled = false;
			selector.options[i].text = selector.options[i].value;
		}
	}
	for (var i = 0; i < usedoptions.length; i++) selector.add(usedoptions[i]);	//put the used options back for information
}

function sumac_allow_or_disallow_more_attachments(doc,pkg,filledformid,maxatt,uniquetypes)
{
	//var wtd = window.top.document;
	var div = doc.getElementById("sumac_div_" + pkg + "_allow_attach_" + filledformid);
	var notypesleft = uniquetypes;
	var selector = doc.getElementById("sumac_select_" + pkg + "_atttype_" + filledformid);
	if (uniquetypes) //are any types still available?
	{
		var optcount = selector.length;
//alert('optcount='+optcount);
		for (var i = 0; i < optcount; i++)
		{
			if (selector.options[i].disabled == false)
			{
				notypesleft = false;
				break;
			}
		}
	}
	var table = doc.getElementById("sumac_table_" + pkg + "_attachments_" + filledformid);
	var rows = table.getElementsByTagName('TR'); //if the number of rows (i.e. attachments) has reached the max allowed
//alert ('maxatt='+maxatt+', rows='+rows.length+', notypes='+notypesleft);
	if ((Number(maxatt) >= 0) && (rows.length >= (Number(maxatt) + 1))
		|| (notypesleft == true)) //or every type has been used
	{
		div.className += " sumac_nodisplay";
	}
	else
	{
		if (div.className.indexOf('sumac_nodisplay') < 0) return;
		if (div.className == 'sumac_nodisplay') div.className = '';
		else div.className = div.className.replace(' sumac_nodisplay','');
	}
}
