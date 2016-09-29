//version567//

	function sumac_check_for_missing_fields(buttoncode,emessageid)
	{
		var missing = 0;
		var firstel = null;
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
			if (input.offsetParent === null) continue;	//ignore invisible fields
			if (sumac_is_required_for_submit(input,buttoncode) === false) continue;
			if ((input.type == 'text') || (input.type == 'password'))
			{
				if (input.value == "")
				{
					++missing;
					input.className = input.className + ' sumac_missing';
					//if (missing == 1) input.focus();
					if (firstel == null) firstel = input;
					else if (input.compareDocumentPosition(firstel) == 4) firstel = input;
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
								//if (missing == 1) input.focus();
								if (firstel == null) firstel = input;
								else if (input.compareDocumentPosition(firstel) == 4) firstel = input;
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
			if (select.offsetParent === null) continue;	//ignore invisible fields
			if (sumac_is_required_for_submit(select,buttoncode) === false) continue;
			if (select.selectedIndex == 0)
			{
				++missing;
				select.className = select.className + ' sumac_missing';
				//if (missing == 1) select.focus();
				if (firstel == null) firstel = select;
				else if (select.compareDocumentPosition(firstel) == 4) firstel = select;
			}
		}
	    var textareas = document.getElementsByTagName('TEXTAREA');
		for (var i = 0; i < textareas.length; i++)
		{
			var textarea = textareas[i];
			if (textarea.offsetParent === null) continue;	//ignore invisible fields
			if (sumac_is_required_for_submit(textarea,buttoncode) === false) continue;
			if (textarea.value == "")
			{
				++missing;
				textarea.className = input.className + ' sumac_missing';
				//if (missing == 1) textarea.focus();
				if (firstel == null) firstel = textarea;
				else if (textarea.compareDocumentPosition(firstel) == 4) firstel = textarea;
			}
		}
		if (missing > 0)
		{
			document.getElementById(emessageid).parentNode.className = 'sumac_status';
			firstel.focus();
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

	function sumac_form2_show_new_attachment_panel(pkg,formid)
	{
		var div = document.getElementById('sumac_div_' + pkg + '_attach_new_' + formid);
		if (div.className.indexOf('sumac_nodisplay') < 0) return;
		if (div.className == 'sumac_nodisplay') div.className = '';
		else div.className = div.className.replace(' sumac_nodisplay','');

		document.getElementById('sumac_input_' + pkg + '_attfile_' + formid).value = '';
//		document.getElementById('sumac_input_' + pkg + '_attname_' + formid).value = '';
		document.getElementById('sumac_select_' + pkg + '_atttype_' + formid).selectedIndex = 0;
	}

	function sumac_form2_test_for_blank_filename(pkg,formid)
	{
		return (document.getElementById('sumac_input_' + pkg + '_attfile_' + formid).value == '');
	}

	function sumac_form2_initiate_upload(pkg,formid)
	{
		sumac_form2_show_initiate_iframe(pkg,formid);
//		var attname = document.getElementById('sumac_input_' + pkg + '_attname_' + formid).value;
//		if (attname == '') attname = document.getElementById('sumac_input_' + pkg + '_attfile_' + formid).value;
		var attname = document.getElementById('sumac_input_' + pkg + '_attfile_' + formid).value;
		sumac_set_hidden_input('sumac_upload_name',attname);
		var atttype = document.getElementById('sumac_select_' + pkg + '_atttype_' + formid).value;
		sumac_set_hidden_input('sumac_upload_type',atttype);

		var uploadform = document.getElementById('sumac_form_' + pkg + '_upload');
		var oldnode = document.getElementById('sumac_id_upload_file_ex');
		var newnode = document.getElementById('sumac_input_' + pkg + '_attfile_' + formid).cloneNode(true);
		newnode.id = 'sumac_id_upload_file'; newnode.name='sumac_upload_file';
		uploadform.replaceChild(newnode,oldnode);
		uploadform.target = 'sumac_attframe_' + formid;
		uploadform.submit();
		newnode.id = 'sumac_id_upload_file_ex';
	}

	function sumac_form2_initiate_delete(pkg,formid,attmtid)
	{
		sumac_form2_show_initiate_iframe(pkg,formid);
		sumac_set_hidden_input('sumac_delete',attmtid);

		var deleteform = document.getElementById('sumac_form_' + pkg + '_delete');
		deleteform.target = 'sumac_attframe_' + formid;
		deleteform.submit();
	}

	function sumac_form2_initiate_download(pkg,formid,attmtid)
	{
		sumac_form2_show_initiate_iframe(pkg,formid);
		sumac_set_hidden_input('sumac_download',attmtid);

		var downloadform = document.getElementById('sumac_form_' + pkg + '_download');
		downloadform.target = 'sumac_attframe_' + formid;
		downloadform.submit();
//after starting the download, submit a form that starts a little PHP task to detect
//the completion status of the download by finding the status SESSION value. It runs every
//few seconds up to a given limit. When it deteects the existence of the status, it sends the
//report to the usual iframe and exits.
		sumac_set_hidden_input('sumac_dldone',attmtid);

		var dldoneform = document.getElementById('sumac_form_' + pkg + '_dldone');
		dldoneform.target = 'sumac_dl_attframe_' + formid;
		dldoneform.submit();
	}

	function sumac_form2_show_initiate_iframe(pkg,formid)
	{
		var iframe = document.getElementById('sumac_iframe_' + pkg + '_attach_' + formid);
		iframe.className = 'sumac_attachment_response';
		var iwd = iframe.contentDocument || iframe.contentWindow.document;
		var main = iwd.getElementById('sumac_attachment_mainpage');
		if (main) main.className = 'sumac_nodisplay';
		var hide = iwd.getElementById('sumac_attachment_hidepage');
		if (hide) hide.className = 'sumac_visible';
	}

	function sumac_set_hidden_input(name,value)
	{
		var inputs = document.getElementsByName(name);
		for (var i = 0; i < inputs.length; i++)
		{
			if (inputs[i].type == 'hidden') inputs[i].value = value;
		}
	}

	function sumac_hide_show_form(pkg,formid)
	{
		var div = document.getElementById('sumac_div_'+pkg+'_form_'+formid);
		var compressor = document.getElementById('sumac_div_'+pkg+'_compression_'+formid);
		var buttontoHide = document.getElementById('sumac_button_'+pkg+'_hide_'+formid);
		var buttontoShow = document.getElementById('sumac_button_'+pkg+'_show_'+formid);
		if (div.className.indexOf('sumac_form_hidden') >= 0)
		{
			var button = buttontoHide;
			buttontoHide = buttontoShow;
			buttontoShow = button;
			div.className = div.className.replace('sumac_form_hidden','sumac_form_showing');
			compressor.className = compressor.className.replace('sumac_form_hidden','sumac_form_showing');
		}
		else
		{
			div.className = div.className.replace('sumac_form_showing','sumac_form_hidden');
			compressor.className = compressor.className.replace('sumac_form_showing','sumac_form_hidden');
		}
		if (buttontoShow.className.indexOf('sumac_nodisplay') >= 0)
		{
			if (buttontoShow.className == 'sumac_nodisplay') buttontoShow.className = '';
			else buttontoShow.className = buttontoShow.className.replace(' sumac_nodisplay','');
		}
		if (buttontoHide.className.indexOf('sumac_nodisplay') < 0)
		{
			buttontoHide.className = buttontoHide.className + ' sumac_nodisplay';
		}
	}
	function sumac_compress_expand_form(pkg,formid)
	{
		var div = document.getElementById('sumac_div_'+pkg+'_form_'+formid);
		var sel = document.getElementById('sumac_select_'+pkg+'_formfield_'+formid);
		var buttontoHide = document.getElementById('sumac_button_'+pkg+'_compress_'+formid);
		var buttontoShow = document.getElementById('sumac_button_'+pkg+'_expand_'+formid);
		if (div.className.indexOf('sumac_form_compressed') >= 0)
		{
			var button = buttontoHide;
			buttontoHide = buttontoShow;
			buttontoShow = button;
			div.className = div.className.replace('sumac_form_compressed','sumac_form_expanded');
		}
		else
		{
			div.className = div.className.replace('sumac_form_expanded','sumac_form_compressed');
			if (sel) document.getElementById(sel.value).focus();
			//sel.selectedIndex=0;
		}
		if (buttontoShow.className.indexOf('sumac_nodisplay') >= 0)
		{
			if (buttontoShow.className == 'sumac_nodisplay') buttontoShow.className = '';
			else buttontoShow.className = buttontoShow.className.replace(' sumac_nodisplay','');
		}
		if (buttontoHide.className.indexOf('sumac_nodisplay') < 0)
		{
			buttontoHide.className = buttontoHide.className + ' sumac_nodisplay';
		}
	}

	function sumac_switch_login_panel(thistr,right)
	{
		var p = thistr.parentNode;
		while (p.tagName != 'TD')
		{
			p = p.parentNode;
			if (p == null) { alert('null parent'); return; }
		}
		if (p.className == '') p.className = 'sumac_nodisplay';
		else p.className = p.className + ' sumac_nodisplay';

		var n = right ? p.nextSibling : p.previousSibling;
		if (n == null) { alert('null sibling1'); return; }
		while (n.tagName != 'TD')
		{
			n = right ? n.nextSibling : n.previousSibling;
			if (n == null) { alert('null sibling'); return; }
		}
		if (n.className == 'sumac_nodisplay') n.className = '';
		else n.className = n.className.replace(' sumac_nodisplay','');
	}

	function sumac_form_word_array(str)
	{
		var words = [];
		var pipestr = str.replace(/[ ;:,\t]+/gm,'|');
		var pipe = (pipestr.substr(0,1) == '|') ? 1 : 0;
		while (pipe < pipestr.length)
		{
			var nextpipe = pipestr.substr(pipe).indexOf('|');
			if (nextpipe < 0)
			{
				words.push(pipestr.substring(pipe));
				break;
			}
			nextpipe += pipe;
			if (nextpipe > pipe) words.push(pipestr.substring(pipe,nextpipe));
			pipe = nextpipe + 1;
		}
		return words;
	}

	function sumac_hide_show_element(pkg,eltype,id)
	{
		var element = document.getElementById('sumac_'+eltype+'_'+pkg+'_'+id);
		var buttontoHide = document.getElementById('sumac_button_'+pkg+'_hide_'+eltype+'_'+id);
		var buttontoShow = document.getElementById('sumac_button_'+pkg+'_show_'+eltype+'_'+id);
		if (element.className.indexOf('sumac_hidden_'+eltype) >= 0)
		{
			var button = buttontoHide;
			buttontoHide = buttontoShow;
			buttontoShow = button;
			element.className = element.className.replace('sumac_hidden_'+eltype,'sumac_showing_'+eltype);
		}
		else
		{
			element.className = element.className.replace('sumac_showing_'+eltype,'sumac_hidden_'+eltype);
		}
		if (buttontoShow.className.indexOf('sumac_nodisplay') >= 0)
		{
			if (buttontoShow.className == 'sumac_nodisplay') buttontoShow.className = '';
			else buttontoShow.className = buttontoShow.className.replace(' sumac_nodisplay','');
		}
		if (buttontoHide.className.indexOf('sumac_nodisplay') < 0)
		{
			buttontoHide.className = buttontoHide.className + ' sumac_nodisplay';
		}
	}
