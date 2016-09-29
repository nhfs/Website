//version552//

	function sumac_check_for_missing_fields(buttoncode,emessageid)
	{
		var missing = 0;
		var formermissing = document.getElementsByClassName('sumac_missing');
		var fmlen = formermissing.length;
		for (var i = fmlen-1; i >= 0; i--)
		{
			var e = formermissing[i];
			var smpos = e.className.indexOf('sumac_missing');
			if (smpos > 0) e.className = e.className.substr(0,smpos);
			else if (smpos == 0) e.className = '';
		}
		var formermessages = document.getElementsByClassName('sumac_status');
		var fmlen = formermessages.length;
		for (var i = fmlen-1; i >= 0; i--) formermessages[i].className = 'sumac_nodisplay';

	    var inputs = document.getElementsByTagName('INPUT');
		for (var i = 0; i < inputs.length; i++)
		{
			var input = inputs[i];
			if (sumac_is_required_for_submit(input,buttoncode) == false) continue;
			if ((input.type == 'text') || (input.type == 'password'))
			{
				if (input.value == "")
				{
					++missing;
					input.className = input.className + ' sumac_missing';
					if (missing == 1) input.focus();
				}
			}
			else if (input.type == 'radio')
			{
				var goodfield = null;
				var reqfields = document.getElementsByName(input.name);
				for (var j = 0; j < reqfields.length; j++)
				{
					if (reqfields[j].checked)
					{
						goodfield = reqfields[j];
						break;
					}
				}
				if (goodfield == null)
				{
					var parent = input.parentNode;
					while (parent != null)
					{
						if (parent.tagName == 'TABLE')
						{
							var smpos = parent.className.indexOf('sumac_missing');
							if (smpos < 0)
							{
								++missing;
								parent.className = parent.className + ' sumac_missing';
								if (missing == 1) input.focus();
							}
							break;
						}
						parent = parent.parentNode;
					}
				}
			}
		}
	    var selects = document.getElementsByTagName('SELECT');
		for (var i = 0; i < selects.length; i++)
		{
			var select = selects[i];
			if (sumac_is_required_for_submit(select,buttoncode) == false) continue;
			if (select.selectedIndex == 0)
			{
				++missing;
				select.className = select.className + ' sumac_missing';
				if (missing == 1) select.focus();
			}
		}
	    var textareas = document.getElementsByTagName('TEXTAREA');
		for (var i = 0; i < textareas.length; i++)
		{
			var textarea = textareas[i];
			if (sumac_is_required_for_submit(textarea,buttoncode) == false) continue;
			if (textarea.value == "")
			{
				++missing;
				textarea.className = input.className + ' sumac_missing';
				if (missing == 1) textarea.focus();
			}
		}
		if (missing > 0)
		{
			document.getElementById(emessageid).parentNode.className = 'sumac_status';
			return true;	//that means field IS missing
		}
		//no blank fields
		document.getElementById(emessageid).parentNode.className = 'sumac_nodisplay';
		return false;
	}

	function sumac_unhide_table(thisbutton,tableid,hiddenlabel,unhiddenlabel,unhiddenclass)
	{
		if (thisbutton.innerHTML == unhiddenlabel) return;
		document.getElementById(tableid).className = unhiddenclass;
		var onclick = "sumac_hide_table(this,'" + tableid + "','" + hiddenlabel + "','" + unhiddenlabel +
					"','" + unhiddenclass + "');";
		thisbutton.setAttribute("onclick",onclick);
		thisbutton.innerHTML = unhiddenlabel;
	}

	function sumac_hide_table(thisbutton,tableid,hiddenlabel,unhiddenlabel,unhiddenclass)
	{
		if (thisbutton.innerHTML == hiddenlabel) return;
		document.getElementById(tableid).className = unhiddenclass + " sumac_nodisplay";
		var onclick = "sumac_unhide_table(this,'" + tableid + "','" + hiddenlabel + "','" + unhiddenlabel +
					"','" + unhiddenclass + "');";
		thisbutton.setAttribute("onclick",onclick);
		thisbutton.innerHTML = hiddenlabel;
	}

	function sumac_set_required(elementid,buttoncode)
	{
		var element = document.getElementById(elementid);
		if (sumac_is_required_for_submit(element,buttoncode)) return;
/*
		var reqcl = 'sumac_reqby_';
		if (element.className.substr(0,reqcl.length) == reqcl)
		{
			element.className = element.className.substr(0,reqcl.length).concat(buttoncode,element.className.substr(reqcl.length));
		}
		else
		{
			if (element.className.length <= 0) element.className = reqcl + buttoncode;
			else element.className = reqcl + buttoncode + ' ' + element.className;
		}
*/
		if (element.hasAttribute('data-sumac-reqby') == false) element.setAttribute('data-sumac-reqby',buttoncode);
		else element.setAttribute('data-sumac-reqby',(element.getAttribute('data-sumac-reqby') + buttoncode));
	}

	function sumac_set_not_required(elementid,buttoncode)
	{
		var element = document.getElementById(elementid);
		if (sumac_is_required_for_submit(element,buttoncode) == false) return;
/*
		var reqcl = 'sumac_reqby_';
		var codepos = element.className.substr(reqcl.length).indexOf(buttoncode);
		element.className = element.className.substr(0,codepos).concat(element.className.substr(codepos+1));
*/
		var reqby = element.getAttribute('data-sumac-reqby');
		if (reqby == buttoncode)
		{
			element.removeAttribute('data-sumac-reqby');
		}
		else
		{
			var codepos = reqby.indexOf(buttoncode);
			element.setAttribute('data-sumac-reqby',reqby.substr(0,codepos).concat(reqby.substr(codepos+1)));
		}
	}

	function sumac_is_required_for_submit(element,submitbuttoncode)
	{
/*
		var reqcl = 'sumac_reqby_';
		if (element.className.substr(0,reqcl.length) != reqcl) return false;
		var codepos = element.className.substr(reqcl.length).indexOf(submitbuttoncode);
		if (codepos < 0) return false;
		var blankpos = element.className.substr(reqcl.length).indexOf(' ');
		if ((blankpos >= 0) && (blankpos < codepos)) return false;
*/
		if (element.hasAttribute('data-sumac-reqby') == false) return false;
		if (element.getAttribute('data-sumac-reqby').indexOf(submitbuttoncode) < 0) return false;
		return true;
	}

	function sumac_set_checked(elementid)
	{
		document.getElementById(elementid).checked='checked';
	}

	function sumac_set_field_valid(element)
	{
		var invpos = element.className.indexOf('sumac_invalid');
		if (invpos >= 0)
		{
			if (element.className.length == 'sumac_invalid'.length) element.className = '';
			else element.className = element.className.substr(0,invpos)
									.concat(element.className.substr(invpos+'sumac_invalid'.length));
		}
		return;
	}

	function sumac_cover_mainpage(pkg)
	{
		var mainpage = document.getElementById('sumac_div_'+pkg+'_mainpage');
		var coverpage = document.getElementById('sumac_div_'+pkg+'_hide_mainpage');
		if (mainpage && coverpage)
		{
			mainpage.className = mainpage.className + ' sumac_hide_mainpage sumac_hide_' + pkg + '_mainpage';
			coverpage.className = coverpage.className + ' sumac_show_coverpage sumac_show_' + pkg + '_coverpage';
			coverpage.focus();
		}
	}
