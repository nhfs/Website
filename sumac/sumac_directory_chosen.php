<?php
//version5122//

//this is 'sumac_directory_chosen.php'
//control is passed here by the search button on the entry selection page

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';

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
	$firstParamPos = strpos($referer,'?');
	if (!($firstParamPos === false) && ($firstParamPos > 0)) $referer = substr($referer,0,$firstParamPos);
	if (
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_directory_chosen.php')
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

	if (isset($_POST['search']) && isset($_POST['directory']))
	{
		$_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] = $_POST['resultsshow'];

/*
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
						' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html><body><p>';
			$html = '<br />' . 'These are the $_POST variables now';
			$html .= ' ... there are ' . count($_POST) . '.<br /><br />';
			foreach ($_POST as $x => $y)
			{
				if (is_Array($y))
				{
					foreach($y as $p => $q) $html .= '$_POST[' . $x . '] ' . '[' . $p . ']="' . $q . '"<br />';
//					for ($i=0;$i<count($y);$i++) $html .= '$_POST[' . $x . '][' . $i . ']="' . $y[$i] . '"<br />';
				}
				else
				{
					$html .= '$_POST[' . $x . ']="' . $y . '"<br />';
				}
			}
			$html .= '<br />' . '... and these are the $_GET variables now';
			$html .= ' ... there are ' . count($_GET) . '.<br /><br />';
			foreach ($_GET as $x => $y)
			{
				$html .= '$_GET[' . $x . ']="' . $y . '"<br />';
			}
	echo $html . '</p></body></html>' . "\n";
*/

include_once 'sumac_xml.php';
		$xml = SUMAC_XML_HEADER . sumac_addXMLdirectorySelectorData($_POST['directory']);
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$directoryEntriesDocument = sumac_loadDirectoryEntriesDocument($source,$port,$xml);
		if ($directoryEntriesDocument == true)
		{
include_once 'sumac_show_directory_entries.php';
			if (sumac_execShowDirectoryEntries($directoryEntriesDocument) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else
		{
			echo sumac_getAbortHTML();
			sumac_destroy_session('');
		}
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_directory_chosen: variables search or directory not set');
	}

function sumac_addXMLdirectorySelectorData($dirId)
{

	$xml = '<directory d="' . sumac_convertXMLSpecialChars($dirId) . '">';
	foreach ($_POST as $pn => $pv)
	{
		if ((strlen($pn) > 9) && (substr($pn,0,9) == 'selector='))
		{
			$parts = explode('=',$pn);
			$xml .= '<selector s="' . sumac_convertXMLSpecialChars($parts[1]) . '">';
			if (is_Array($pv))
			{
				foreach ($pv as $p => $q)
				{
					if ($q != '') $xml .= '<choice c="' . sumac_convertXMLSpecialChars($q) . '"></choice>';
				}
			}
			else
			{
				$xml .= '<choice c="' . sumac_convertXMLSpecialChars($pv) . '"></choice>';
			}
			$xml .= '</selector>';
		}
		//there shouldn't be any other 'post' variables, but we don't want them anyway
	}
	$xml .= '</directory>';
	return $xml;
}

?>
