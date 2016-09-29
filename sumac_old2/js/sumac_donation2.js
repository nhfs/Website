//version520//

	function sumac_donation2_change_frequency()
	{
		var fcode = document.getElementById("sumac_input_donation2_frequency").value;
		document.getElementById("sumac_input_donation2_frequency").value = (fcode < 0) ? 1 : -1;
	}

	function sumac_donation2_set_amountpaid()
	{
		var onlyamounts = document.getElementsByName('onlyamount');
		if (onlyamounts.length == 1)
		{
			document.getElementById('sumac_input_donation2_amountpaid').value = Math.round(onlyamounts[0].value * 100);
			return;
		}
		var radioamounts = document.getElementsByName('donationamount');
		var found = false;
		for (var i = 0; i < radioamounts.length; i++)
		{
			if (radioamounts[i].checked)
			{
				if (radioamounts[i].value != '')
				{
					document.getElementById('sumac_input_donation2_amountpaid').value = radioamounts[i].value;
					found = true;
					break;
				}
				else
				{
					var otheramounts = document.getElementsByName('otheramount');
					if (otheramounts.length == 1)
					{
						var otheramount = otheramounts[0].value;
						document.getElementById('sumac_input_donation2_amountpaid').value = Math.round(otheramount * 100);
						found = true;
						break;
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
				if (otheramount != '') document.getElementById('sumac_input_donation2_amountpaid').value = Math.round(otheramount * 100);
				return;
			}
		}
	}
