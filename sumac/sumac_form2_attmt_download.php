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
	if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) $missingidMessage = SUMAC_ERROR_DOWNLOAD_CONTACTID_MISSING;
	else if (!isset($_GET['sumac_download_owner'])) $missingidMessage = SUMAC_ERROR_DOWNLOAD_OWNERID_MISSING;
	else if (!isset($_GET['sumac_download_form'])) $missingidMessage = SUMAC_ERROR_DOWNLOAD_FORMID_MISSING;
	else if (!isset($_GET['sumac_download'])) $missingidMessage = SUMAC_ERROR_DOWNLOAD_ATTMTID_MISSING;
	if ($missingidMessage !== false)
	{
		echo sumac_form2_attmt_download_failed_HTML($pkg,$missingidMessage,false,null,null,null);	// inserts $formId,$attmtId not available
		$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = -2;
		return;
	}
	$attmtId = $_GET['sumac_download'];
	$formId = $_GET['sumac_download_form'];
	$ownerId = $_GET['sumac_download_owner'];
	$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = 0;

	$xml = SUMAC_XML_HEADER;
	$xml .= '<openattachment ai="' . sumac_convertXMLSpecialChars($attmtId)
					. '" fi="' . sumac_convertXMLSpecialChars($formId)
					. '" oi="' . sumac_convertXMLSpecialChars($ownerId)
					. '" id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
					. '" />';

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];

	$attachmentDocument = sumac_loadAttachmentDocument($source,$port,SUMAC_REQUEST_PARAM_OPEN_ATTMT,
														SUMAC_REQUEST_KEYWORD_ATTACHMENT,$xml);
	if ($attachmentDocument == false)
	{
		echo sumac_form2_attmt_download_failed_HTML($pkg,'ME3',true,$formId,$attmtId,$_SESSION[SUMAC_SESSION_REQUEST_ERROR]);
		$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = -3;
		return;
	}

/* ************* this version uses an intermediate file - not necessary, I now think *********************************
				$attname = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
				$blocknumber = 0;
				$filesize = 0;
				$blocksize = $_SESSION[SUMAC_USERPAR_ATTMTBLOCK]; // blocksize should come from the openattachment response???

				$bytes = '';
				$filename = 'downloads/'.$attname;
				//$fd = fopen($filename,'w+b');
				$fd = tmpfile();
				while (true)
				{
					$attachmentDocument = sumac_download_attachment_block($source,$port,$attmtId,$blocknumber,$blocksize);
					if ($attachmentDocument == false) break;
					$blocksentElements = $attachmentDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_BLOCKSENT);
					//$thisblocksize = $blocksentElements->item(0)->getAttribute(SUMAC_ATTRIBUTE_SIZE);
					//if ($thisblocksize == 0) break;
					$block = $blocksentElements->item(0)->childNodes->item(0)->nodeValue;
					$bytes = base64_decode($block);
					$filesize += strlen($bytes);
					fwrite($fd,$bytes);
					$blocknumber++;
					if (strlen($bytes) < $blocksize) break;
				}
				header("Content-Type: image/jpg");	// content type should come from openattachment response
				header("Content-Disposition: attachment; filename=".$attname);
				header("Content-Length: ".$filesize);

				//readfile($fd);
				fseek($fd,0);
				$toread = $filesize;
				while ($toread > 1024)
				{
					echo fread($fd,1024);
					$toread = $toread - 1024;
				}
				if ($toread > 0) echo fread($fd,$toread);
				fclose($fd);
*************************************************************************************************************************/

	$attsize = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_FILESIZE);
	if (($attsize == null) || ($attsize == '') || ($attsize == 0))
	{
		echo sumac_form2_attmt_download_failed_HTML($pkg,'ME4',true,$formId,$attmtId,null);
		$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = -4;
		return;
	}

	$attname = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_NAME);
	if ($attname == '') $attname = 'form.'.$formId.'.attachment.'.$attmtid;
//	$attfiletype = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_FILETYPE);
//	if ($attfiletype == '') $attfiletype = 'application/octet-stream';
	$attfiletype = 'application/octet-stream';

    header('Content-Type: '.$attfiletype);
    header('Content-Disposition: attachment; filename="'.$attname.'"');
    header('Content-Length: '.$attsize);

	$blocknumber = 0;
	$filesize = 0;
	$blocksize = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_BLOCKSIZE);
	if (($blocksize == null) || ($blocksize == '') || ($blocksize > $_SESSION[SUMAC_USERPAR_ATTMTBLOCK]))
		$blocksize = $_SESSION[SUMAC_USERPAR_ATTMTBLOCK];	//a maximum

	$bytes = '';
	while (true)
	{
		$attachmentDocument = sumac_download_attachment_block($source,$port,$attmtId,$filesize,$blocksize);
		$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] += 1;
		if ($attachmentDocument == false)
		{
//signal to the other process (dldone) that we got to the end but NOT OK
			$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = -10 - $_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId];
			break;
		}
		$blocksentElements = $attachmentDocument->documentElement->getElementsByTagName(SUMAC_ELEMENT_BLOCKSENT);
		$block = $blocksentElements->item(0)->childNodes->item(0)->nodeValue;
		$bytes = base64_decode($block);

		echo $bytes;

		$filesize += strlen($bytes);
		if ($filesize >= $attsize)
		{
//signal to the other process (dldone) that we got to the end OK
			$_SESSION[SUMAC_DOWNLOAD_IN_PROGRESS][$attmtId] = -1;
			break;
		}
		$blocknumber++;
	}


    exit;

function sumac_download_attachment_block($source,$port,$attmtId,$byteoffset,$blocksize)
{

	$xml = SUMAC_XML_HEADER;
	$xml .= '<readattachment ai="' . sumac_convertXMLSpecialChars($attmtId)
			. '" bo="' . $byteoffset
			. '" bs="' . $blocksize . '">';
	$xml .= '</readattachment>';

	return sumac_loadAttachmentDocument($source,$port,SUMAC_REQUEST_PARAM_READ_ATTMT_BLOCK,
													SUMAC_REQUEST_KEYWORD_ATTACHMENT,$xml,false);
	//******** note the final 'false' parameter which prevents URLdecoding of the document
	//******** without it, the Base64-encoded data block gets corrupted - just a little
}

function sumac_form2_attmt_download_failed_HTML($pkg,$message,$messageIsOnlyId,$formId,$attmtId,$insert3)
{
	$html = sumac_geth2_head($pkg)
			.'<body>'.PHP_EOL
			.'<div id="sumac_attachment_mainpage">'.PHP_EOL
			.sumac_geth2_sumac_title($pkg,$message,$messageIsOnlyId,array($formId,$attmtId,$insert3))
			.'</div>'.PHP_EOL;
//add a button for closing this iframe window
//	$html .= '<a class="sumac_close_button" href="javascript: var iframes=window.top.document.getElementsByTagName(\'IFRAME\');for (var i=0;i<iframes.length;i++)if (iframes[i].className==\'sumac_attachment_response\')iframes[i].className=\'sumac_nodisplay\';">close this</a>'.PHP_EOL;
	$html .= '<button onclick="closeme();">close this</button>';
//add a hidden overlay division with the circling dot in it so that it can be displayed next time the iframe is used
	$html .= '<div id="sumac_attachment_hidepage" class="sumac_nodisplay">'.PHP_EOL
			.'<table class="sumac_please_wait_table">'.PHP_EOL
			.'<tr><td class="sumac_please_wait_image"><img  src="user/pleasewait.gif" alt="circling spot" /></td></tr>'.PHP_EOL
			.'</table>'.PHP_EOL
			.'</div>'.PHP_EOL;

	$html .= '<script>function closeme(){var iframes=window.top.document.getElementsByTagName(\'IFRAME\');for (var i=0;i<iframes.length;i++){if (iframes[i].className==\'sumac_attachment_response\'){iframes[i].className=\'sumac_nodisplay\';}}}</script>';

	$html .= sumac_geth2_body_footer($pkg,false,null,'');
	return $html;
}

?>