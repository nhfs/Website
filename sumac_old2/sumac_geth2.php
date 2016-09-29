<?php
//version552//

function sumac_geth2_divtag($pkg,$id,$cls,$moreattrs='')
{
	return '<div id="'.sumac_geth2_id($pkg,$id,'div').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_formtag($pkg,$id,$cls,$formhandler)
{
	return '<form id="'.sumac_geth2_id($pkg,$id,'form').'" class="'.sumac_geth2_class($cls).'" accept-charset="UTF-8" method="post" action="'.$formhandler.'">'.PHP_EOL;
}

function sumac_geth2_tabletag($pkg,$id,$cls,$moreattrs='')
{
	return '<table id="'.sumac_geth2_id($pkg,$id,'table').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_trtag($pkg,$id,$cls,$moreattrs='')
{
	return '<tr id="'.sumac_geth2_id($pkg,$id,'tr').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_tdtag($pkg,$id,$cls,$colspan=1,$moreattrs='')
{
	return '<td id="'.sumac_geth2_id($pkg,$id,'td').'" class="'.sumac_geth2_class($cls).'"'.(($colspan>1)?' colspan="'.$colspan.'"':'').$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_sumac_title_with_gobacklink_and_line($pkg,$text,$linktextid,$textisonlyid=true)
{
	return sumac_geth2_sumac_title($pkg,$text,$textisonlyid).sumac_geth2_sumac_return_link($pkg,$linktextid).'<hr class="sumac_hline" />'.PHP_EOL;
}

function sumac_geth2_sumac_title_with_line($pkg,$text,$textisonlyid=true)
{
	return sumac_geth2_sumac_title($pkg,$text,$textisonlyid).'<hr class="sumac_hline" />'.PHP_EOL;
}

function sumac_geth2_sumac_title($pkg,$text,$textisonlyid)
{
	return '<p class="sumac_title">'.($textisonlyid?sumac_geth2_spantext($pkg,$text):$text).'</p>'.PHP_EOL;
}

function sumac_geth2_sumac_subtitle($pkg,$textid,$level)
{
	return '<p class="sumac_subtitle'.$level.'">'.sumac_geth2_spantext($pkg,$textid).'</p>'.PHP_EOL;
}

function sumac_geth2_input_with_label($pkg,$id,$cls,$name,$attrs,$labelid,$labelposition,$span=true)
{
	$inputid = sumac_geth2_id($pkg,$id,'input');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$inputid,$labelid,$span).sumac_geth2_input($inputid,$cls,$name,$attrs).PHP_EOL;
		case 'after':
			return sumac_geth2_input($inputid,$cls,$name,$attrs).sumac_geth2_label($pkg,$inputid,$labelid,$span).PHP_EOL;
		case 'above':
		default:
			return sumac_geth2_label($pkg,$inputid,$labelid,$span).'<br />'.sumac_geth2_input($inputid,$cls,$name,$attrs).PHP_EOL;
	}
}

function sumac_geth2_input($fullid,$cls,$name,$attrs)
{
	return '<input id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.' />'.PHP_EOL;
}

function sumac_geth2_textarea_with_label($pkg,$id,$cls,$name,$attrs,$longtext,$labelid,$labelposition,$span=true)
{
	$textareaid = sumac_geth2_id($pkg,$id,'textarea');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$textareaid,$labelid,$span).sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext).PHP_EOL;
		case 'after':
			return sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext).sumac_geth2_label($pkg,$textareaid,$labelid,$span).PHP_EOL;
		case 'above':
		default:
			return sumac_geth2_label($pkg,$textareaid,$labelid,$span).'<br />'.sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext).PHP_EOL;
	}
}

function sumac_geth2_textarea($fullid,$cls,$name,$attrs,$longtext)
{
	return '<textarea id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.'>'.$longtext.'</textarea>'.PHP_EOL;
}

function sumac_geth2_select_with_label($pkg,$id,$cls,$name,$attrs,$options,$labelid,$labelposition,$span=true)
{
	$selectid = sumac_geth2_id($pkg,$id,'select');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$selectid,$labelid,$span).sumac_geth2_select($selectid,$cls,$name,$attrs,$options).PHP_EOL;
		case 'after':
			return sumac_geth2_select($selectid,$cls,$name,$attrs,$options).sumac_geth2_label($pkg,$selectid,$labelid,$span).PHP_EOL;
		case 'above':
		default:
			return sumac_geth2_label($pkg,$selectid,$labelid,$span).'<br />'.sumac_geth2_select($selectid,$cls,$name,$attrs,$options).PHP_EOL;
	}
}

function sumac_geth2_select($fullid,$cls,$name,$attrs,$options)
{
	return '<select id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.'>'.$options.'</select>'.PHP_EOL;
}

function sumac_geth2_element_with_label($pkg,$id,$element,$elementtag,$labelid,$labelposition,$span=true)
{
	$elementid = sumac_geth2_id($pkg,$id,$elementtag);
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$elementid,$labelid,$span).$element.PHP_EOL;
		case 'after':
			return $element.sumac_geth2_label($pkg,$elementid,$labelid,$span).PHP_EOL;
		case 'above':
		default:
			return sumac_geth2_label($pkg,$elementid,$labelid,$span).'<br />'.$element.PHP_EOL;
	}
}

function sumac_geth2_sumac_return_link($pkg,$textid,$cls='leavesumac_link')
{
	return '<a class="'.sumac_geth2_class($cls).'" href="'.sumac_get_exiturl().'">'.sumac_geth2_spantext($pkg,$textid).'</a>';
}

function sumac_geth2_label($pkg,$inputid,$labelid,$span)
{
	if ($span) return '<label for="'.$inputid.'">'.sumac_geth2_spantext($pkg,$labelid).'</label>'.PHP_EOL;
	else return '<label for="'.$inputid.'">'.$labelid.'</label>'.PHP_EOL;
}

function sumac_geth2_spantext($pkg,$textid)
{
	return '<span id="'.sumac_geth2_textid_for_span($pkg,$textid).'"></span>';
}

function sumac_geth2_textid_for_span($pkg,$textid)
{
	return 'sumac_sid_'.sumac_geth2_fulltextid($pkg,$textid);
}

function sumac_geth2_textid_for_entry($pkg,$tag,$textid)
{
	return 'sumac_'.$tag.'_'.sumac_geth2_fulltextid($pkg,$textid);
}

function sumac_geth2_fulltextid($pkg,$textid)
{
	switch ($pkg)
	{
	case 'donation2':
		return 'D2'.$textid;
	case 'singleform2':
		return 'F2'.$textid;
	case 'signup2':
		return 'S2'.$textid;
	default:
		return 'SUMAC'.$textid;
	}
}

function sumac_geth2_id($pkg,$id,$elementtag)
{
	return 'sumac_'.$elementtag.'_'.$pkg.'_'.$id;
}

function sumac_geth2_class($cls)
{
	if (($cls == null) || ($cls == '')) return 'sumac_noclass';
	else if (is_array($cls))
	{
		$s = '';
		for ($i = 0; $i < count($cls); $i++)
		{
			$s = $s.(($i==0)?'':' ').'sumac_'.$cls[$i];
		}
		return $s;
	}
	else  return 'sumac_'.$cls;
}

// routines for building the HTML <HEAD> element

function sumac_geth2_head($pkg)
{
	return '<head>'.PHP_EOL
			.sumac_geth2_head_base($pkg)
			.sumac_geth2_head_title($pkg)
			.sumac_geth2_head_meta($pkg)
			.sumac_geth2_head_style($pkg)
			.sumac_geth2_head_script($pkg)
			.'</head>'.PHP_EOL;
}

function sumac_geth2_head_base($pkg)
{
	return '';
}

function sumac_geth2_head_title($pkg)
{
	$html = '<title>'.sumac_geth2_fulltextid($pkg,'B1').'</title>'.PHP_EOL;
	return $html;
}

function sumac_geth2_head_meta($pkg)
{
	return '<meta http-equiv="Content-Script-Type" content="text/javascript" />'.PHP_EOL
			.'<meta charset="utf-8" />'.PHP_EOL
			.'<meta name="keywords" content="HTML,CSS,JavaScript,Sumac,'.$pkg.'" />'.PHP_EOL
			.'<meta name="author" content="Richard Austin" />'.PHP_EOL;
}

function sumac_geth2_head_style($pkg)
{
// get CSS style sheets - sumac general styling, page styling, client-replacement styling, non-overridable styling
	$allowcss = ($_SESSION[SUMAC_SESSION_SUPPRESS_USER_OVERRIDE_CSS] === false);
	return '<link href="css/sumac.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.'<link href="css/sumac_'.$pkg.'.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.sumac_geth2_head_style_parameter_settings()
			.'<link href="user/top.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.'<link href="user/bottom.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.($allowcss ? ('<link href="user/over_sumac.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL) : '')
			.($allowcss ? ('<link href="user/over_sumac_'.$pkg.'.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL) : '')
			.'<link href="css/sumac_protected.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL;
}

function sumac_geth2_head_style_parameter_settings()
{
	$scale = $_SESSION[SUMAC_SESSION_SCALE];
	$fonts = $_SESSION[SUMAC_SESSION_FONTS];
	$textcolour = $_SESSION[SUMAC_SESSION_TEXTCOLOUR];
	$bodycolour = $_SESSION[SUMAC_SESSION_BODYCOLOUR];
	$titlecolour = $_SESSION[SUMAC_SESSION_H1_TITLE_COLOUR];
	return '<style type="text/css">'
			.'body {font-size:'.$scale.';font-family:'.$fonts.';color:'.$textcolour.';background-color:'.$bodycolour.';}'
			.'p.sumac_title,p.sumac_subtitle1,p.sumac_subtitle2 {color:'.$titlecolour.';}'
			.'td.titlebutton input {background-color:'.$titlecolour.';}'
			.'div.sumac_exit {color:'.$titlecolour.';}'
			.'</style>'.PHP_EOL;
}

function sumac_geth2_head_script($pkg)
{
// get javascript variables and functions -
	return '<script src="js/sumac.js" defer="defer" type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_'.$pkg.'.js" defer="defer" type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_preloaded.js" type="text/javascript"></script>'.PHP_EOL;
}

function sumac_geth2_body_footer($pkg,$startpage,$form,$pkginsert1)
{
	$retry = ($form != null);
	$debug = (strpos($_SESSION[SUMAC_SESSION_DEBUG],'stringids') !== false);
	return '<script type="text/javascript">'.$_SESSION['sumac_override_text_js'].'</script>'.PHP_EOL
			.'<script src="js/sumac_'.$pkg.'_text.js" type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_shared_text.js" type="text/javascript"></script>'.PHP_EOL
			.($retry ? sumac_getJSToRestoreAllEnteredValues('sumac_form_'.$pkg.'_'.$form) : '')
			.(isset($_POST['newemail']) ? sumac_getJSToRestoreUsername($_POST['newemail']) : '')
			.($startpage ? sumac_geth2_confirm_use_of_http() : '')
			.PHP_EOL
			.'<script type="text/javascript">'.PHP_EOL
			.'sumac_set_strings("'.$_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL].'","'.$_SESSION[SUMAC_SESSION_ORGANISATION_NAME].'","c","d","e","'.$pkginsert1.'");'.PHP_EOL
			.'document.onkeypress = keyPress;'.PHP_EOL
			.($debug ? 'sumac_show_string_ids();' : '').PHP_EOL
			.($retry ? 'sumac_set_focus_to_error_message();' : '').PHP_EOL
			.'</script>'.PHP_EOL
			.PHP_EOL;
}

function sumac_geth2_sumac_software_link($pkg,$sumacsoftwarelink,$sumacsoftwarename)
{
	return sumac_geth2_tabletag($pkg,'link_to_sumac','link_to_sumac')
			.sumac_geth2_trtag($pkg,'link_to_sumac','link_to_sumac').sumac_geth2_tdtag($pkg,'link_to_sumac','link_to_sumac')
			.'<a class="sumac_website_link" href="'.SUMAC_INFO_FOOTER_SUMAC_LINK.$sumacsoftwarelink.'">'.$sumacsoftwarename.' '.SUMAC_CODE_VERSION.'</a>'.PHP_EOL
			.'</td>'.PHP_EOL.'</tr>'.PHP_EOL.'</table>'.PHP_EOL;
}

function sumac_geth2_user($toporbottom,$pkg)
{
	$userhtml = sumac_getFileContents('user',$pkg.'_'.$toporbottom.'.htm');
	if ($userhtml == '') $userhtml = sumac_getFileContents('user',$toporbottom.'.htm');
	return $userhtml.PHP_EOL;
}

function sumac_geth2_sumac_div_hide_mainpage($pkg,$message)
{
	return '<div id="sumac_div_'.$pkg.'_hide_mainpage" class="sumac_nodisplay" tabindex="1">'.PHP_EOL
			.'<table class="sumac_please_wait_table">'.PHP_EOL
			.'<tr><td class="sumac_please_wait_message"><p class="sumac_title">'.$message.'</p></td></tr>'.PHP_EOL
			.'<tr><td class="sumac_please_wait_image"><img  src="user/pleasewait.gif" alt="circling spot" /></td></tr>'.PHP_EOL
			.'</table>'.PHP_EOL
			.'</div>'.PHP_EOL;
}

function sumac_geth2_confirm_use_of_http()
{
	if (isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED])) return '';
	$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = 'once';

	$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
	return ($usingHTTPS == false) ? '<script type="text/javascript">sumac_confirm_use_of_HTTP("'.SUMAC_WARNING_CONFIRM_HTTP.'");</script>'.PHP_EOL : '';
}

function sumac_geth2_exit_package_page($pkg,$message,$okbutton,$sumacsoftwarelink,$sumacsoftwarename)
{
	$fixedamount = $_SESSION[SUMAC_USERPAR_D2FIXEDAMT];
	if ($fixedamount != '') $fixedamount = sumac_centsToPrintableDollars($_SESSION[SUMAC_USERPAR_D2FIXEDAMT],false,($fixedamount % 100 != 0),false);

	return '<!DOCTYPE html>'.PHP_EOL
			.sumac_geth2_head($pkg)

			.'<body>'.PHP_EOL
			.sumac_geth2_user('top',$pkg)

			.sumac_geth2_divtag($pkg,'exit','exit').$message.'</div>'.PHP_EOL
			.sumac_geth2_sumac_return_link($pkg,$okbutton,'OK_button').PHP_EOL
			.sumac_geth2_sumac_software_link($pkg,$sumacsoftwarelink,$sumacsoftwarename)

			.sumac_geth2_user('bottom',$pkg)
			.sumac_geth2_body_footer($pkg,false,null,$fixedamount)
			.'</body>'.PHP_EOL;
}

?>