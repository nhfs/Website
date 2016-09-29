<?php
//version565//

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
	$missingidMessage = false;
	if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID])) $missingidMessage = SUMAC_ERROR_UPLOAD_CONTACTID_MISSING;
	else if (!isset($_POST['sumac_upload_owner'])) $missingidMessage = SUMAC_ERROR_UPLOAD_OWNERID_MISSING;
	else if (!isset($_POST['sumac_upload_form'])) $missingidMessage = SUMAC_ERROR_UPLOAD_FORMID_MISSING;
	if ($missingidMessage !== false)
	{
		echo sumac_form2_attmt_upload_ended_HTML($pkg,$missingidMessage,null,null,null,null,null,null,null,0,false);	// most variables not available
		return;
	}

	$maxAttachments = $_POST['sumac_maxattmt'];
	$uniqueTypes = $_POST['sumac_unique_types'];

	$formId = $_POST['sumac_upload_form'];
	$ownerId = $_POST['sumac_upload_owner'];
	$attname = $_POST['sumac_upload_name'];
	$atttype = $_POST['sumac_upload_type'];
	$date = new DateTime();
	$blocksize = $_SESSION[SUMAC_USERPAR_ATTMTBLOCK];

//@@@	if (count($_FILES) > 1) report error

	$file = $_FILES['sumac_upload_file'];
//@@@	if (!is_array($file)) report error
	$error = $file['error'];
//use the name captured when the file was selected (which will probably be the same as this basename)
//	$name = basename($file['name']);
//@@@	$filename = 'uploads/'.$name;
	if ($error == UPLOAD_ERR_OK)
	{

//@@@		if ( move_uploaded_file($file['tmp_name'],$filename) ) dont save uploaded file now
		$xml = SUMAC_XML_HEADER;
		$xml .= '<newattachment ff="' . sumac_convertXMLSpecialChars($formId)
				. '" oi="' . sumac_convertXMLSpecialChars($ownerId)
				. '" id="' . $_SESSION[SUMAC_SESSION_CONTACT_ID]
				. '" an="' . sumac_convertXMLSpecialChars($attname)
				. '" at="' . sumac_convertXMLSpecialChars($atttype)
//. '" fn="' . sumac_convertXMLSpecialChars($file['name'])
//. '" ft="' . sumac_convertXMLSpecialChars($file['type'])
				. '" fs="' . sumac_convertXMLSpecialChars($file['size'])
				. '" bs="' . sumac_convertXMLSpecialChars($blocksize)
				. '" />';

		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];

		$attachmentDocument = sumac_loadAttachmentDocument($source,$port,SUMAC_REQUEST_PARAM_CREATE_ATTMT,
															SUMAC_REQUEST_KEYWORD_ATTACHMENT,$xml);
		if ($attachmentDocument == false)
		{
			//echo sumac_getAbortHTML();
			echo sumac_form2_attmt_upload_ended_HTML($pkg,$_SESSION[SUMAC_SESSION_REQUEST_ERROR],$formId,
													'No attachment',$attname,$atttype,$file['size'],
													$file['type'],$date->format('Y-m-d H:i'),
													$maxAttachments,$uniqueTypes);

			return;
		}

		$attmtId = $attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_ID);

		$byteoffset = 0;
//@@@		$fd = fopen($filename,'rb');
		$fd = fopen($file['tmp_name'],'rb');
		$block = '';
		while (!feof($fd) && ($block !== false))
		{
			$block = fread($fd,$blocksize);
			if ($block !== false)
			{
				if (sumac_upload_attachment_block($source,$port,$attmtId,$byteoffset,strlen($block),$block) !== true) break;
//@@@should show error message
				$byteoffset += strlen($block);
			}
		}
		fclose($fd);

		$html = sumac_form2_attmt_upload_ended_HTML($pkg,'',$formId,$attmtId,$attname,$atttype,
						$file['size'],$file['type'],$date->format('Y-m-d H:i'),$maxAttachments,$uniqueTypes);

//@@@		else $html .= 'Could not move uploaded file "'.$file['tmp_name'].'" to "' .$attname. '"<br/>';
	}
	else
	{
		$errormessage = sumac_get_upload_err_message($error);
		$html = sumac_form2_attmt_upload_ended_HTML($pkg,$errormessage,$formId,'No attachment',$attname,$atttype,
						$file['size'],$file['type'],$date->format('Y-m-d H:i'),$maxAttachments,$uniqueTypes);
	}

	echo $html;
	return;


function sumac_get_upload_err_message($code)
{
	switch ($code)
	{
		case 1:	return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
		case 2:	return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
		case 3:	return 'The uploaded file was only partially uploaded';
		default: return $code;
	}
}

function sumac_upload_attachment_block($source,$port,$attmtId,$byteoffset,$blocksize,$block)
{
	$encodedData = base64_encode($block);
	$xml = SUMAC_XML_HEADER;
	$xml .= '<writeattachment ai="' . sumac_convertXMLSpecialChars($attmtId)
			. '" bo="' . $byteoffset
			. '" bs="' . $blocksize . '">';
	$xml .= '<attachmentblock l="' . strlen($encodedData) . '">' . $encodedData . '</attachmentblock>';
	$xml .= '</writeattachment>';
	$attachmentDocument = sumac_postRequestAndloadAttachmentDocument($source,$port,
										SUMAC_REQUEST_PARAM_WRITE_ATTMT_BLOCK,$xml);
	if ($attachmentDocument == false) return false;

//should have matching attachment id and status OK
//should have a blockreceived element confirming the block number and size

	if ($attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_ID) != $attmtId) return false;
	//if ($attachmentDocument->documentElement->getAttribute(SUMAC_ATTRIBUTE_STATUS) != 'good') return false;
	$blockReceivedElements = $attachmentDocument->getElementsByTagName(SUMAC_ELEMENT_BLOCKRECEIVED);
	if (!$blockReceivedElements) return false;
	$blockReceivedElement = $blockReceivedElements->item(0);
	if (!$blockReceivedElement) return false;
	//if ($blockReceivedElement->getAttribute(SUMAC_ATTRIBUTE_OFFSET) != $byteoffset) return false;
	if ($blockReceivedElement->getAttribute(SUMAC_ATTRIBUTE_SIZE) != $blocksize) return false;
	return true;
}

function sumac_form2_attmt_upload_ended_HTML($pkg,$errormessage,$formId,$attmtId,$attname,$atttype,
							$filesize,$filetype,$attdate,$maxAttachments,$uniqueTypes)
{
	$html = sumac_geth2_head($pkg)
			.'<body>'.PHP_EOL
			.'<div id="sumac_attachment_mainpage" class="sumac_attach">'.PHP_EOL;
	if ($errormessage == '')
	{
		$html .= sumac_geth2_sumac_subtitle($pkg,'ML18','_attach',true,array($attname,$atttype,$filesize,$attmtId,$formId));
	}
	else
	{
		$html .= '<p>UPLOAD ERROR<br />'.$errormessage.'</p>'.PHP_EOL;
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
				.'sumac_attachment_add_to_list('
							.'"'.$pkg.'",'
							.'"'.$formId.'",'
							.'"'.$attmtId.'",'
							.'"'.$attname.'",'
							.'"'.$atttype.'",'
							.'"'.$filesize.'",'
							.'"'.$filetype.'",'
							.'"'.$attdate.'");'.PHP_EOL;
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
//	$html .= '</body>'.PHP_EOL
//			.'</html>';
	return $html;
}

?>