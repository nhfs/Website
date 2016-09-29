<?php
//version5647//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_xml.php';

	$sid = session_id();
	if ($sid == "")
	{
		session_name(SUMAC_SESSION_NAME);
		session_start();
	}

	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'displayerrors') !== false)
	{
		$new_level = error_reporting(-1);
		ini_set("display_errors",1);
	}

include_once 'sumac_session_utilities.php';
	if (!isset($_SESSION[SUMAC_SESSION_SOURCE])) //or we could check 'port'
	{
		sumac_destroy_session(sumac_formatMessage($_SESSION[SUMAC_STR]["AE7"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
	if (($usingHTTPS == false) && (strtolower(substr($_SESSION[SUMAC_SESSION_ALLOWHTTP],0,1)) != 't'))
	{
		sumac_destroy_session(SUMAC_ERROR_REQUIRES_SSL . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	$referer = sumac_get_referer(SUMAC_SESSION_FOLDER);
	if ($referer == false)
	{
		sumac_destroy_session(SUMAC_ERROR_VERIFY_REFERER . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	if (
			($referer != '/sumac_grantreview2_formchosen.php')
		)
	{
		sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INVALID_REFERER,$referer) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	if (time() - $_SESSION[SUMAC_SESSION_TIMESTAMP] > $_SESSION[SUMAC_USERPAR_SESSEXPIRY])
	{
		sumac_forced_session_restart();
		return;
	}
	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();

	$pkg = isset($_POST['sumac_package']) ? $_POST['sumac_package'] : $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE];
	$attmtId = $_GET['sumac_dldone'];
	$counter = isset($_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId]) ? $_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] : 0;
	if ($counter < 0)
	{
		$status = $counter;
		$counter = 99999;
		$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = 99999;
		//$html = sumac_form2_attmt_download_worked_HTML($pkg);
	}
	$html = '<!DOCTYPE html><html>'
				.'<head><meta charset="utf-8" /></head>'
				.'<body>'
				.'<p>Hidden download iframe for '
				.$attmtId
				.' in sid '
				.session_id()
				.'</p>'
				.'<p id="counter">'
				.$counter
				.'</p>'
				.'<p id="status">'
				.$status
				.'</p>'
				.'<script>'
				.'if (document.getElementById("counter").innerHTML != "99999")'
				.'  setTimeout(function(){location.reload(true);}, 3000);'
				.'else'
				.'{ if (document.getElementById("status").innerHTML == "-1")'
				.'  { var iframes=window.top.document.getElementsByTagName("IFRAME");'
				.' 	  for (var i=0;i<iframes.length;i++)'
				.'    { if (iframes[i].className=="sumac_attachment_response")'
				.'      { iframes[i].className="sumac_nodisplay";'
				.'      }'
				.'    }'
				.'  }'
				.'}'
				.'</script>'
				.'</body>';
	echo $html;
    exit;

function sumac_form2_attmt_download_worked_HTML($pkg)
{
	$html = sumac_geth2_head($pkg)
			.'<body>'.PHP_EOL;

//add a hidden overlay division with the circling dot in it so that it can be displayed next time the iframe is used
	$html .= '<div id="sumac_attachment_hidepage" class="sumac_nodisplay">'.PHP_EOL
			.'<table class="sumac_please_wait_table">'.PHP_EOL
			.'<tr><td class="sumac_please_wait_image"><img  src="user/pleasewait.gif" alt="circling spot" /></td></tr>'.PHP_EOL
			.'</table>'.PHP_EOL
			.'</div>'.PHP_EOL;

	$html .= sumac_geth2_body_footer($pkg,false,null,'');
	return $html;
}

?>