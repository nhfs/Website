<?php
//version568//

include_once 'sumac_constants.php';

function sumac_geth2_divtag($pkg,$id,$cls,$moreattrs='')
{
	return '<div id="'.sumac_geth2_id($pkg,$id,'div').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_spantag($pkg,$id,$cls,$moreattrs='')
{
	return '<span id="'.sumac_geth2_id($pkg,$id,'span').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_formtag($pkg,$id,$cls,$formhandler,$method='post',$enctype='')
{
	$html = '<form id="'.sumac_geth2_id($pkg,$id,'form').'"'
			.' class="'.sumac_geth2_class($cls).'"'
			.' accept-charset="UTF-8" action="'.$formhandler.'"';
	if (strtolower($method) == 'get') return $html.' method="get">'.PHP_EOL;
	else return $html.' method="'.$method.'" enctype="'.$enctype.'">'.PHP_EOL;
}

function sumac_geth2_ptag($pkg,$id,$cls,$moreattrs='')
{
	return '<p id="'.sumac_geth2_id($pkg,$id,'p').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_tabletag($pkg,$id,$cls,$moreattrs='')
{
	return '<table id="'.sumac_geth2_id($pkg,$id,'table').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_tbodytag($pkg,$id,$cls,$moreattrs='')
{
	return '<tbody id="'.sumac_geth2_id($pkg,$id,'tbody').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_theadtag($pkg,$id,$cls,$moreattrs='')
{
	return '<thead id="'.sumac_geth2_id($pkg,$id,'thead').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>'.PHP_EOL;
}

function sumac_geth2_trtag($pkg,$id,$cls,$moreattrs='')
{
	return '<tr id="'.sumac_geth2_id($pkg,$id,'tr').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs.'>';
}

function sumac_geth2_tdtag($pkg,$id,$cls,$colspan=1,$moreattrs='')
{
	return '<td id="'.sumac_geth2_id($pkg,$id,'td').'" class="'.sumac_geth2_class($cls).'"'.(($colspan>1)?' colspan="'.$colspan.'"':'').$moreattrs.'>';
}

function sumac_geth2_sumac_title_with_gobacklink_and_line($pkg,$text,$linktextid,$textisonlyid=true,$inserts=null)
{
	return sumac_geth2_sumac_title($pkg,$text,$textisonlyid,$inserts).sumac_geth2_sumac_return_link($pkg,$linktextid).'<hr class="sumac_hline" />'.PHP_EOL;
}

function sumac_geth2_sumac_title_with_line($pkg,$text,$textisonlyid=true,$inserts=null)
{
	return sumac_geth2_sumac_title($pkg,$text,$textisonlyid,$inserts).'<hr class="sumac_hline" />'.PHP_EOL;
}

function sumac_geth2_sumac_title($pkg,$text,$textisonlyid,$inserts=null)
{
	return '<p class="sumac_title"'.sumac_geth2_inserts($inserts).'>'.($textisonlyid?sumac_geth2_spantext($pkg,$text):$text).'</p>'.PHP_EOL;
}

function sumac_geth2_sumac_subtitle($pkg,$text,$level,$textisonlyid=true,$inserts=null)
{
	return '<p class="sumac_subtitle'.$level.'"'.sumac_geth2_inserts($inserts).'>'.($textisonlyid?sumac_geth2_spantext($pkg,$text):$text).'</p>'.PHP_EOL;
}

function sumac_geth2_sumac_button($pkg,$id,$cls,$onclick,$text,$moreattrs='')
{
	return '<button type="button" id="'.sumac_geth2_id($pkg,$id,'button').'" class="'.sumac_geth2_class($cls).'" onclick="'.$onclick.'"'.$moreattrs.'>'.sumac_geth2_spantext($pkg,$text).'</button>'.PHP_EOL;
}

function sumac_geth2_iframe($pkg,$id,$cls,$name,$attrs)
{
	return '<iframe id="'.sumac_geth2_id($pkg,$id,'iframe').'" class="'.sumac_geth2_class($cls).'" name="'.$name.'" '.$attrs.'></iframe>'.PHP_EOL;
}

function sumac_geth2_input_with_label($pkg,$id,$cls,$name,$attrs,$label,$labelposition,$labelisonlyid=true,$inserts=null)
{
	$inputid = sumac_geth2_id($pkg,$id,'input');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$inputid,$label,$labelisonlyid,$inserts).sumac_geth2_input($inputid,$cls,$name,$attrs);
		case 'after':
			return sumac_geth2_input($inputid,$cls,$name,$attrs).sumac_geth2_label($pkg,$inputid,$label,$labelisonlyid,$inserts);
		case 'above':
		default:
			return sumac_geth2_label($pkg,$inputid,$label,$labelisonlyid,$inserts).'<br />'.sumac_geth2_input($inputid,$cls,$name,$attrs);
	}
}

function sumac_geth2_input($fullid,$cls,$name,$attrs)
{
	return '<input id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.' />'.PHP_EOL;
}

function sumac_geth2_textarea_with_label($pkg,$id,$cls,$name,$attrs,$longtext,$label,$labelposition,$labelisonlyid=true)
{
	$textareaid = sumac_geth2_id($pkg,$id,'textarea');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$textareaid,$label,$labelisonlyid).sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext);
		case 'after':
			return sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext).sumac_geth2_label($pkg,$textareaid,$label,$labelisonlyid);
		case 'above':
		default:
			return sumac_geth2_label($pkg,$textareaid,$label,$labelisonlyid).'<br />'.sumac_geth2_textarea($textareaid,$cls,$name,$attrs,$longtext);
	}
}

function sumac_geth2_textarea($fullid,$cls,$name,$attrs,$longtext)
{
	return '<textarea id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.'>'.$longtext.'</textarea>'.PHP_EOL;
}

function sumac_geth2_select_with_label($pkg,$id,$cls,$name,$attrs,$options,$label,$labelposition,$labelisonlyid=true)
{
	$selectid = sumac_geth2_id($pkg,$id,'select');
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$selectid,$label,$labelisonlyid).sumac_geth2_select($selectid,$cls,$name,$attrs,$options);
		case 'after':
			return sumac_geth2_select($selectid,$cls,$name,$attrs,$options).sumac_geth2_label($pkg,$selectid,$label,$labelisonlyid);
		case 'above':
		default:
			return sumac_geth2_label($pkg,$selectid,$label,$labelisonlyid).'<br />'.sumac_geth2_select($selectid,$cls,$name,$attrs,$options);
	}
}

function sumac_geth2_select($fullid,$cls,$name,$attrs,$options)
{
	return '<select id="'.$fullid.'" name="'.$name.'" class="'.sumac_geth2_class($cls).'" '.$attrs.'>'.$options.'</select>'.PHP_EOL;
}

function sumac_geth2_element_with_label($pkg,$id,$element,$elementtag,$label,$labelposition,$labelisonlyid=true)
{
	$elementid = ($elementtag != '') ? sumac_geth2_id($pkg,$id,$elementtag) : '';
	switch ($labelposition)
	{
		case 'before':
			return sumac_geth2_label($pkg,$elementid,$label,$labelisonlyid).$element.PHP_EOL;
		case 'after':
			return $element.sumac_geth2_label($pkg,$elementid,$label,$labelisonlyid).PHP_EOL;
		case 'above':
		default:
			return sumac_geth2_label($pkg,$elementid,$label,$labelisonlyid).'<br />'.$element.PHP_EOL;
	}
}

function sumac_geth2_link($pkg,$id,$cls,$href,$text,$textisonlyid=true,$moreattrs='',$inserts=null,$target='_self')
{
	return '<a id="'.sumac_geth2_id($pkg,$id,'a').'" class="'.sumac_geth2_class($cls).'"'
			.' href="'.$href.'" target="'.$target.'"'.$moreattrs
			.sumac_geth2_inserts($inserts).'>'.($textisonlyid?sumac_geth2_spantext($pkg,$text):$text).'</a>'.PHP_EOL;
}

function sumac_geth2_sumac_return_link($pkg,$textid,$link='',$cls='leavesumac_link')
{
	$actuallink = ($link == '') ? sumac_get_exiturl() : $link;
	return '<a class="'.sumac_geth2_class($cls).'" href="'.$actuallink.'">'.sumac_geth2_spantext($pkg,$textid).'</a>';
}

function sumac_geth2_hideshow_buttons($pkg,$id,$cls,$element,$hidetext,$showtext,$moreattrs='',$show=true)
{
	$onclick = 'sumac_hide_show_element(\'' . $pkg . '\',\'' . $element . '\',\'' . $id . '\');';
	$hidebuttonClass = $show ? $element.'_hide' : array($element.'_hide','nodisplay');
	$showbuttonClass = $show ? array($element.'_show','nodisplay') : $element.'_show';
	return sumac_geth2_sumac_button($pkg,'hide_'.$element.'_'.$id,$hidebuttonClass,$onclick,$hidetext,$moreattrs)
			.sumac_geth2_sumac_button($pkg,'show_'.$element.'_'.$id,$showbuttonClass,$onclick,$showtext,$moreattrs);
}

function sumac_geth2_label($pkg,$inputid,$label,$labelisonlyid,$inserts=null)
{
	$for = ($inputid != '') ? (' for="'.$inputid.'"') : '';
	if ($labelisonlyid) return '<label'.sumac_geth2_inserts($inserts).$for.'>'.sumac_geth2_spantext($pkg,$label).'</label>'.PHP_EOL;
	else return '<label'.sumac_geth2_inserts($inserts).$for.'>'.$label.'</label>'.PHP_EOL;
}

function sumac_geth2_attrtext($pkg,$textid,$attr)
{
	return ' '.$attr.'="'.sumac_geth2_fulltextid($pkg,$textid).'"';
}

function sumac_geth2_span($pkg,$id,$cls,$text,$textisonlyid=true,$inserts=null,$moreattrs='')
{
	return '<span id="'.sumac_geth2_id($pkg,$id,'span').'" class="'.sumac_geth2_class($cls).'"'.$moreattrs
			.sumac_geth2_inserts($inserts).'>'.($textisonlyid?sumac_geth2_spantext($pkg,$text):$text).'</span>'.PHP_EOL;
}

function sumac_geth2_spantext($pkg,$textid)
{
	$t = sumac_geth2_textid_for_span($pkg,$textid);
	if (isset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]))
	{
		if (!isset($_SESSION[SUMAC_SESSION_REPEATABLE_STR][$t]))
		{
			$_SESSION[SUMAC_SESSION_REPEATABLE_STR][$t] = '1';
		}
		else
		{
			$inst = $_SESSION[SUMAC_SESSION_REPEATABLE_STR][$t];
			$_SESSION[SUMAC_SESSION_REPEATABLE_STR][$t] = $inst + 1;
			$t = $t.'-'.$inst;
		}
	}
	return '<span id="'.$t.'"></span>';
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
	case SUMAC_PACKAGE_DONATION:
		return 'D1'.$textid;
	case SUMAC_PACKAGE_MEMBERSHIP:
	case SUMAC_PACKAGE_MEMBERSHIP2:
		return 'M2'.$textid;
	case SUMAC_PACKAGE_TICKETING:
	case SUMAC_PACKAGE_TICKETING2:
		return 'T2'.$textid;
	case SUMAC_PACKAGE_COURSES:
		return 'C2'.$textid;
	case SUMAC_PACKAGE_CONTACT_UPDATE:
		return 'U2'.$textid;
	case SUMAC_PACKAGE_DIRECTORIES:
		return 'E2'.$textid;
	case SUMAC_PACKAGE_DONATION2:
		return 'D2'.$textid;
	case SUMAC_PACKAGE_SINGLEFORM2:
		return 'F2'.$textid;
	case SUMAC_PACKAGE_SIGNUP2:
		return 'S2'.$textid;
	case SUMAC_PACKAGE_GRANTREVIEW2G:
		return 'R2G'.$textid;
	case SUMAC_PACKAGE_GRANTREVIEW2R:
		return 'R2R'.$textid;
	default:
		return 'SUMAC'.$textid;
	}
}

function sumac_geth2_id($pkg,$id,$elementtag)
{
	if ($id == null)
	{
		$nullidnum = 0;
		if (isset($_SESSION[SUMAC_SESSION_NULLID_COUNTER]))
		{
			$nullidnum = $_SESSION[SUMAC_SESSION_NULLID_COUNTER];
		}
		$_SESSION[SUMAC_SESSION_NULLID_COUNTER] = $nullidnum + 1;
		$id = 'nullid_'.$nullidnum;
	}
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

function sumac_geth2_inserts($inserts)
{
	if ($inserts == null) return '';
	$insertAttributes = '';
	if (is_array($inserts))
	{
		for ($i = 0; $i < count($inserts); $i++)
		{
			$insertAttributes = $insertAttributes.' data-ins'.$i.'="'.$inserts[$i].'"';
		}
	}
	else
	{
		$insertAttributes = ' data-ins0="'.$inserts.'"';
	}
	return $insertAttributes;
}

// routines for building the HTML <HEAD> element

function sumac_geth2_head($pkg,$jscanwait=true,$generatedCSS='',$title='B1')
{
	return '<!DOCTYPE html><html><head>'.PHP_EOL
			.sumac_geth2_head_base($pkg)
			.sumac_geth2_head_title($pkg,$title)
			.sumac_geth2_head_meta($pkg)
			.sumac_geth2_head_style($pkg,$generatedCSS)
			.sumac_geth2_head_script($pkg,$jscanwait)
			.'</head>'.PHP_EOL;
}

function sumac_geth2_head_base($pkg)
{
	return '';
}

function sumac_geth2_head_title($pkg,$title)
{
	$html = '<title>'.sumac_geth2_fulltextid($pkg,$title).'</title>'.PHP_EOL;
	return $html;
}

function sumac_geth2_head_meta($pkg)
{
	return '<meta charset="utf-8" />'.PHP_EOL
			.'<meta name="keywords" content="HTML,CSS,JavaScript,Sumac,'.$pkg.'" />'.PHP_EOL
			.'<meta name="author" content="Richard Austin" />'.PHP_EOL;
}

function sumac_geth2_head_style($pkg,$generatedCSS)
{
// get CSS style sheets - sumac general styling, page styling, client-replacement styling, non-overridable styling
	$allowcss = ($_SESSION[SUMAC_SESSION_SUPPRESS_USER_OVERRIDE_CSS] === false);
	return '<link href="css/sumac.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.'<link href="css/sumac_'.$pkg.'.css" rel="stylesheet" type="text/css" media="all" />'.PHP_EOL
			.sumac_geth2_head_style_parameter_settings()
			.$generatedCSS
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
			.'.sumac_title {color:'.$titlecolour.';}'
			.'[class^="sumac_subtitle"] {color:'.$titlecolour.';}'
			.'td.sumac_titlebutton input {background-color:'.$titlecolour.';}'
			.'div.sumac_exit {color:'.$titlecolour.';}'
			.'</style>'.PHP_EOL;
}

function sumac_geth2_head_script($pkg,$canwait)
{
// get javascript variables and functions
	$defer = $canwait ? ' defer="defer"' : '';
	return '<script src="js/sumac.js" defer="defer" type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_'.$pkg.'.js"'.$defer.' type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_preloaded.js" type="text/javascript"></script>'.PHP_EOL;
}

function sumac_geth2_body_footer($pkg,$startpage,$form,$pkginsert1='',$extrajs='')
{
	$retry = ($form != null);
	$debug = (strpos($_SESSION[SUMAC_SESSION_DEBUG],'stringids') !== false);
	return sumac_geth2_sumac_software_link($pkg)
			.sumac_geth2_user(SUMAC_USER_BOTTOM,$pkg)
			.'<script type="text/javascript">'.sumac_geth2_string_overrides($pkg).'</script>'.PHP_EOL
			.'<script src="js/sumac_'.$pkg.'_text.js" type="text/javascript"></script>'.PHP_EOL
			.'<script src="js/sumac_shared_text.js" type="text/javascript"></script>'.PHP_EOL
			.($retry ? sumac_getJSToRestoreAllEnteredValues('sumac_form_'.$pkg.'_'.$form) : '')
			.(isset($_POST['newemail']) ? sumac_getJSToRestoreUsername($_POST['newemail']) : '')
			.PHP_EOL
			.'<script type="text/javascript">'.PHP_EOL
			.'sumac_set_strings("'.$_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL].'","'.$_SESSION[SUMAC_SESSION_ORGANISATION_NAME].'","'.$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME].'","d","e","'.$pkginsert1.'");'.PHP_EOL
			.'document.onkeypress = keyPress;'.PHP_EOL
//			.sumac_getCommonHTMLScriptVariables().PHP_EOL
			.'var sumac_currency_symbol = "'.$_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL].'";'.PHP_EOL
			.($debug ? 'var sumac_showing_strings = true; sumac_show_string_ids();' : '').PHP_EOL
			.($retry ? 'sumac_set_focus_to_error_message();' : '').PHP_EOL
			.$extrajs.PHP_EOL
			.'</script>'.PHP_EOL
			.($startpage ? sumac_geth2_confirm_use_of_http() : '')
			.PHP_EOL
			.'</body></html>'.PHP_EOL;
}

function sumac_geth2_sumac_software_link($pkg)
{
	return sumac_geth2_divtag($pkg,'link_to_sumac','link_to_sumac')
			.'<a class="sumac_website_link" href="'.sumac_getSumacWebsiteLink($pkg).'">'
					.sumac_getSumacWebsiteText($pkg).' '.SUMAC_CODE_VERSION.'</a>'.PHP_EOL
			.'</div>'.PHP_EOL;
}

function sumac_geth2_user($toporbottom,$pkg)
{
	$userhtml = sumac_getFileContents(SUMAC_USER_FOLDER,$pkg.'_'.$toporbottom.'.htm');
	if ($userhtml == '') $userhtml = sumac_getFileContents(SUMAC_USER_FOLDER,$toporbottom.'.htm');
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

function sumac_geth2_helplink($url,$size='16x16')
{
	return '<a href="'.$url.'" target="_blank" rel="help">'.PHP_EOL
			.'<img src="'.SUMAC_USER_FOLDER.'/help'.$size.'.jpg" alt="'.$size.' help icon" title="" />'.PHP_EOL
			.'</a>'.PHP_EOL;
}

function sumac_geth2_confirm_use_of_http()
{
	if (isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED])) return '';
	$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = 'once';

	$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
	return ($usingHTTPS == false) ? '<script type="text/javascript">sumac_confirm_use_of_HTTP("'.SUMAC_WARNING_CONFIRM_HTTP.'");</script>'.PHP_EOL : '';
}

function sumac_geth2_exit_package_page($pkg,$document,$message,$okbutton,$logoutbutton,$next,
										$extraHTMLabove='',$extraHTMLbelow='')
{
	$fixedamount = $_SESSION[SUMAC_USERPAR_D2FIXEDAMT];
	if ($fixedamount != '') $fixedamount = sumac_centsToPrintableDollars($_SESSION[SUMAC_USERPAR_D2FIXEDAMT],false,($fixedamount % 100 != 0),false);
	$button = $_SESSION[SUMAC_USERPAR_EXITLOGOUT] ? $logoutbutton : $okbutton;
	$buttoncls = $_SESSION[SUMAC_USERPAR_EXITLOGOUT] ? 'Logout_button' : 'OK_button';
	$nextURL = ($next == '') ? sumac_get_exiturl() : $next;
	$linkURL = $_SESSION[SUMAC_USERPAR_EXITLOGOUT] ? 'sumac_leave.php?homephp='.$nextURL : $nextURL;
	return sumac_geth2_head($pkg)
			.'<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($document,$pkg.'_response')
			.sumac_geth2_user(SUMAC_USER_TOP,$pkg)

			.sumac_geth2_divtag($pkg,'exit_mainpage','mainpage')
			.$extraHTMLabove
			.sumac_geth2_divtag($pkg,'exit','exit')
			.$message.PHP_EOL
			.'</div>'.PHP_EOL
			.$extraHTMLbelow
			.'</div>'.PHP_EOL

			.sumac_geth2_sumac_return_link($pkg,$button,$linkURL,$buttoncls).PHP_EOL
			.sumac_geth2_body_footer($pkg,false,null,$fixedamount);
}

function sumac_geth2_abort_page($pkg,$msg,$okbutton,$logoutbutton,$next)
{
	$package = ($pkg != '') ? $pkg : $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE];
	$fatal = (isset($_SESSION[SUMAC_SESSION_FATAL_ERROR]) === false) ? $_SESSION[SUMAC_SESSION_ANY_OTHER_ERROR] : $_SESSION[SUMAC_SESSION_FATAL_ERROR];
	$message = ($msg != '') ? $msg : $fatal.'<br />'.$_SESSION[SUMAC_SESSION_REQUEST_ERROR];
	$okbutton = ($okbutton != '') ? $okbutton : 'NL4';
	$logoutbutton = ($logoutbutton != '') ? $logoutbutton : 'NL9';
	return sumac_geth2_exit_package_page($package,null,$message,$okbutton,$logoutbutton,$next);
}

function sumac_geth2_string_overrides($pkg)
{
	$pkgIdcode = sumac_geth2_fulltextid($pkg,'');
	$js = 'var sumac_override_sids=['; //that starts the new javascript array variable ...

	$error_level = error_reporting();
	$new_level = error_reporting($error_level ^ E_WARNING);
	$strings = file_get_contents(SUMAC_USER_FOLDER . '/' . 'sumac_strings.settings',true);
	$error_level = error_reporting($error_level);

	$strings = trim($strings);
	if ($strings == '') return $js.'];';

	$lineArray = array();
	// does file have 0A or 0D line-ending delimiter? or both, and in which order?
	$newLine = strpos($strings,"\n");
	$carriageReturn = strpos($strings,"\r");
	$lineBreak = '';
//echo 'NL='.$newLine.',CR='.$carriageReturn.',';
	if (($newLine === false) && ($carriageReturn !== false)) $lineBreak = "\r";
	else if (($newLine !== false) && ($carriageReturn === false)) $lineBreak = "\n";
	else if (($newLine !== false) && ($carriageReturn !== false))
	{
		if ($carriageReturn == ($newLine + 1)) $lineBreak = "\n\r";
		else if ($newLine == ($carriageReturn + 1)) $lineBreak = "\r\n";
	}
	if ($lineBreak != '') $lineArray = explode($lineBreak,$strings);
	else $lineArray[0] = $strings;

// an alternative, neater-looking, way of doing the same would be:
//		$lineArray = preg_split('/\r\n|\n\r|\n|\r/', $strings);

	$continuation = '';
	for ($line = 0; $line < count($lineArray); $line++)
	{
		$text = ($continuation=='') ? ($lineArray[$line]) : ($continuation.'\n'.$lineArray[$line]);
		$stringArray = explode('|',$text);
		if (count($stringArray) < 4) { $continuation = $text; continue; }
		$continuation = '';
		for ($i = 0; $i < (count($stringArray)); $i = $i + 3)
		{
			$sakey = trim($stringArray[$i]);
			//second element not used
			if (($i+2) < count($stringArray)) $savalue = $stringArray[$i+2]; else $savalue = '';
			if ($sakey == '') continue;
			if ((substr($sakey,0,strlen($pkgIdcode)) == $pkgIdcode) || (substr($sakey,0,2) == 'G2'))
			{
				$js .= '{id:"'.$sakey.'",str:"'.$savalue.'"},';
			}
		}
	}
	return $js.'];';
}
?>