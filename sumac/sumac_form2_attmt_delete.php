<?php
//version567//

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
//check that we have a contact id for the person filling out the form, a form id, and an attachment id.
	$missingidMessage = false;
	if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) $missingidMessage = SUMAC_ERROR_SETDELETED_CONTACTID_MISSING;
	else if (!isset($_GET['sumac_delete_owner'])) $missingidMessage = SUMAC_ERROR_SETDELETED_OWNERID_MISSING;
	else if (!isset($_GET['sumac_delete_form'])) $missingidMessage = SUMAC_ERROR_SETDELETED_FORMID_MISSING;
	else if (!isset($_GET['sumac_delete'])) $missingidMessage = SUMAC_ERROR_SETDELETED_ATTMTID_MISSING;
	if ($missingidMessage !== false)
	{
		echo sumac_form2_attmt_delete_ended_HTML($pkg,$missingidMessage,null,null,null,null,null,-1,false);	// inserts $formId,$attmtId not available
		return;
	}

	$maxAttachments = $_GET['sumac_maxattmt'];
	$uniqueTypes = $_GET['sumac_unique_types'];

	$attmtId = $_GET['sumac_delete'];
	$formId = $_GET['sumac_delete_form'];
	$ownerId = $_GET['sumac_delete_owner'];

	$xml = SUMAC_XML_HEADER;
	$xml .= '<setattachment ai="' . sumac_convertXMLSpecialChars($attmtId)
					. '" fi="' . sumac_convertXMLSpecialChars($formId)
					. '" oi="' . sumac_convertXMLSpecialChars($ownerId)
					. '" id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
					. '" as="1" />';

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];

	$attachmentDocument = sumac_loadAttachmentDocument($source,$port,SUMAC_REQUEST_PARAM_SET_ATTMT,
														SUMAC_REQUEST_KEYWORD_ATTACHMENT,$xml);
	$errormessage = '';
	$attname = '?';
	$atttype = '?';
	$attsize = '?';
	if ($attachmentDocument == false)
	{
		$errormessage = $_SESSION[SUMAC_SESSION_REQUEST_ERROR];
	}
	else
	{
		$attname = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
		if (($attname == null) || ($attname == '')) $attname = '?';
		$atttype = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_TYPE);
		if (($atttype == null) || ($atttype == '')) $atttype = '?';
		$attsize = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_FILESIZE);
		if (($attsize == null) || ($attsize == '')) $attsize = '?';
	}
	$html = sumac_form2_attmt_delete_ended_HTML($pkg,$errormessage,$attname,$atttype,$attsize,$attmtId,$formId,$maxAttachments,$uniqueTypes);
	echo $html;
	return;

function sumac_form2_attmt_delete_ended_HTML($pkg,$errormessage,$attname,$atttype,$attsize,$attmtId,$formId,$maxAttachments,$uniqueTypes)
{
	$html = sumac_geth2_head($pkg)
			.'<body>'.PHP_EOL
			.'<div id="sumac_attachment_mainpage" class="sumac_deletion">'.PHP_EOL;
	if ($errormessage == '')
	{
		$html .= sumac_geth2_sumac_subtitle($pkg,'ML17','_attdel',true,array($attname,$atttype,$attsize,$attmtId,$formId));
		//$html .= '<p>'.$attname.'<br />deleted from form '.$formId.'</p>'.PHP_EOL;
	}
	else
	{
		$html .= sumac_geth2_sumac_title($pkg,$errormessage,false,array($attname,$atttype,$attsize,$attmtId,$formId));
	}
	$html .= '</div>'.PHP_EOL;
//add a button for closing this iframe window
	$html .= sumac_geth2_sumac_button($pkg,'closeme','closeme','closeme();','ML16');
//add a hidden overlay division with the circling dot in it so that it can be displayed next time the iframe is used
	$html .= '<div id="sumac_attachment_hidepage" class="sumac_nodisplay">'.PHP_EOL
			.'<table class="sumac_please_wait_table">'.PHP_EOL
			.'<tr><td class="sumac_please_wait_image"><img  src="user/pleasewait.gif" alt="circling spot" /></td></tr>'.PHP_EOL
			.'</table>'.PHP_EOL
			.'</div>'.PHP_EOL;
	if ($errormessage == '')
	{
		$html .= '<script type="text/javascript">'.PHP_EOL
				.'sumac_attachment_delete_from_list('
							.'"'.$pkg.'",'
							.'"'.$formId.'",'
							.'"'.$attmtId.'");'.PHP_EOL;
//ensure that the maxattachments limit is respected and choosable types are correct
		if ($uniqueTypes)
		{
			$html .= 'sumac_disable_used_attachment_types(window.top.document,'
								.'"'.$pkg.'",'
								.'"'.$formId.'");'.PHP_EOL;
		}
		$html .= 'sumac_allow_or_disallow_more_attachments(window.top.document,'
							.'"'.$pkg.'",'
							.'"'.$formId.'",'
							.'"'.$maxAttachments.'",'
							.'"'.$uniqueTypes.'");'.PHP_EOL;
		$html .= '</script>'.PHP_EOL;
	}
	$html .= '<script>function closeme(){var iframes=window.top.document.getElementsByTagName(\'IFRAME\');for (var i=0;i<iframes.length;i++){if (iframes[i].className==\'sumac_attachment_response\'){iframes[i].className=\'sumac_nodisplay\';}}}</script>'.PHP_EOL;

	$html .= sumac_geth2_body_footer($pkg,false,null,'');
	//$html .= '</body>'.PHP_EOL
	//		.'</html>';
	return $html;
}

?>