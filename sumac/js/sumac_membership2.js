//version5661//

function sumac_membership2_select_new_plan(id)
{
//first set the total-to-pay value so that it will be correct if/when the user clicks Buy
	var amounttopay = document.getElementById('sumac_td_membership2_totalcents_' + id).innerHTML;;
	document.getElementById('sumac_input_membership2_amountpaid').value = amounttopay;

//then put that total-to-pay into the associated warning
	var warning = document.getElementById('sumac_sid_M2PI1');
	if (warning) warning.innerHTML = sumac_get_string_from_id('M2','PI1',[sumac_get_string_from_id('M2','PL2'),sumac_centsToPrintableDollars(amounttopay)]);

	//alert(id+' selected');
	var tables = document.getElementsByTagName('TABLE');
	for (var i=0; i<tables.length; i++)
	{
		var table = tables[i];
		if (table.id.substr(0,32) == 'sumac_table_membership2_options_')
		{
			var tr = table.parentNode.parentNode;
			if (table.id.substr(32) == id) tr.className = tr.className.replace(' sumac_nodisplay','');
			else
			{
				if (tr.className.indexOf('sumac_nodisplay') < 0) tr.className = tr.className + ' sumac_nodisplay';
			}
		}
		else if (table.id.substr(0,32) == 'sumac_table_membership2_summary_')
		{
			var td = table.parentNode;
			if (table.id.substr(32) == id) td.className = td.className.replace(' sumac_nodisplay','');
			else
			{
				if (td.className.indexOf('sumac_nodisplay') < 0) td.className = td.className + ' sumac_nodisplay';
			}
		}
	}
	document.getElementById('sumac_input_membership2_addedoptions').value = '';
}

function sumac_membership2_add_option(planId,optId)
{
	var options = document.getElementById('sumac_input_membership2_addedoptions');
	if (options.value == '') options.value = optId;
	else options.value = options.value + ' ' + optId;
	var form = document.getElementById('sumac_form_membership2_all');
	//form.action = 'sumac_test_response.php';
	form.action = 'sumac_membership2_submit.php';
	form.submit();
}

function sumac_membership2_remove_option(planId,optId)
{
	var options = document.getElementById('sumac_input_membership2_addedoptions');
	if (options.value.indexOf(optId + ' ') < 0) options.value = options.value.replace(optId,'');
	else  options.value = options.value.replace(optId + ' ','');
	var form = document.getElementById('sumac_form_membership2_all');
	//form.action = 'sumac_test_response.php';
	form.action = 'sumac_membership2_submit.php';
	form.submit();
}

function sumac_membership2_set_amountpaid()
{
//nothing to do here - it was done in the PHP for the initial selection
// and updated for any new selection by sumac_membership2_select_new_plan()
}
