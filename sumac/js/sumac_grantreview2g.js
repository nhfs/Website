//version564//

	function sumac_show_selected_div(id,classToHide)
	{
		var elements = document.getElementsByClassName(classToHide);
		for (var i = 0; i < elements.length; i++)
		{
			var div = elements[i];
			var isHidden = (div.className.indexOf('sumac_nodisplay') > 0);
			if (div.id != id)	// set nodisplay if necessary
			{
				if (!isHidden)
					if (div.className == '') div.className = 'sumac_nodisplay';
					else div.className = div.className + ' sumac_nodisplay';
			}
			else	// clear nodisplay if necessary
			{
				if (isHidden)
					if (div.className == 'sumac_nodisplay') div.className = '';
					else div.className = div.className.replace(' sumac_nodisplay','');
			}
		}
	}
