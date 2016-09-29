//version530//

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
{	var fm = message;
	var rc = arguments.length;
	for (var i = 1; i < rc; i++)
	{	var r = '%' + (i-1);
		var re = new RegExp(r,'g');
		var fm = fm.replace(re,arguments[i]);
	}
	return fm;
}

function sumac_set_strings()
{	var otitle = sumac_get_override_string(sumac_title_sid.id);
	if (otitle != null) document.title = sumac_formatMessage(otitle,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
	else document.title = sumac_formatMessage(sumac_title_sid.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
	for (var i = 0; i < sumac_innerHTML_sids.length; i++)
	{	var entry = sumac_innerHTML_sids[i];
		var id = entry.id;
		var el = document.getElementById('sumac_sid_' + id);
		if (!el) continue; //not in this page today
		var ostr = sumac_get_override_string(id);
		if (entry.ref) // only a variant - ref identifies shared master entry
		{
			id = entry.ref;
			entry = sumac_get_innerHTML_shared_entry(id);
			if (entry == null) continue;	//bad news
			if (ostr == null)	//user hasn't defined a variant
			{
				ostr = sumac_get_override_string(id);
			}
		}
		if (ostr != null) str = sumac_formatMessage(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		else str = sumac_formatMessage(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
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
		el.innerHTML = str;
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
		if (ostr != null) str = sumac_formatMessage(ostr,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
		else str = sumac_formatMessage(entry.str,arguments[0],arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);
//alert('str=' + str + ',elid=' + 'sumac_' + entry.tag + '_' + id + ', ostr=' + ostr);
		el.value = str;
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
			if ((nf.form.id == formid) && (nf.type == 'hidden')) nf.value = nvarray[i][1]; //same as text
			else notused.push(nvarray[i]);
		}
		else notused.push(nvarray[i]);
	//ignore cases where a name has multiple associated fields
	}
//for (var i = 0; i < notused.length; i++) alert('name='+notused[i][0]+',value='+notused[i][1]);
	return notused;
}

function sumac_show_string_ids()
{	
	var id = sumac_title_sid.id;
	//if (sumac_get_override_string(id) != null) id = id + '*';
	document.title = '[' + id + '=]' + document.title;

	for (var i = 0; i < sumac_innerHTML_sids.length; i++)
	{	var id = sumac_innerHTML_sids[i].id; 
		var el = document.getElementById('sumac_sid_' + id);
		//if (sumac_get_override_string(id) != null) id = id + '*';
		if (el) el.innerHTML = '[' + id + '=]' + el.innerHTML;
	}

	for (var i = 0; i < sumac_value_sids.length; i++)
	{	var id = sumac_value_sids[i].id;
		var el = document.getElementById('sumac_' + sumac_value_sids[i].tag + '_' + id);
		//if (sumac_get_override_string(id) != null) id = id + '*';
		if (el) el.value = '[' + id + '=]' + el.value;
	}
}

function sumac_set_focus_to_error_message()
{
	var errormessages = document.getElementsByClassName('sumac_status');
	if (errormessages.length >= 1) errormessages[0].focus();
}
