<?php
//version568//

include_once 'sumac_constants.php';
include_once 'sumac_session_utilities.php';

	if (!isset($combinedWithLogin))
	{
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

		if (!isset($_SESSION[SUMAC_SESSION_SOURCE])) //or we could check 'port'
		{
			sumac_destroy_session(sumac_formatMessage($_SESSION[SUMAC_STR]["AE7"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . $_SESSION[SUMAC_STR]["AE5"]);
			return;
		}

		$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();
	}

	$html = '<!DOCTYPE html>'
				.'<html>'
				.'<head><meta charset="utf-8" /></head>'
				.'<body>'
				.'<div><h1>This Sumac application is still under development.</h1></div>'
				.'<div>';
	$referer = sumac_get_referer(SUMAC_SESSION_FOLDER);
	$html .= 'The referer was "' . $referer . '"<br />';
	$html .= 'The package is "' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . '"<br />';
	$html .= 'These are the ' . count($_POST) . ' $_POST variables:<br /><br />';
	foreach ($_POST as $x => $y)
	{
		$html .= '$_POST[' . $x . ']="' . $y . '"<br />';
		if (is_array($y))
		{
			foreach ($y as $p => $q)
			{
				$html .= '. . . . [' . $p . ']="' . $q . '"<br />';
			}
		}
	}
	$html .= '<br />' . '... and these are the ' . count($_GET) . ' $_GET variables:<br /><br />';
	foreach ($_GET as $x => $y)
	{
		$html .= '$_GET[' . $x . ']="' . $y . '"<br />';
		if (is_array($y))
		{
			foreach ($y as $p => $q)
			{
				$html .= '. . . . [' . $p . ']="' . $q . '"<br />';
			}
		}
	}
	$html .= '<br />' . '... and these are the ' . count($_FILES) . ' $_FILES variables:<br /><br />';
	foreach ($_FILES as $x => $y)
	{
		$html .= '$_FILES[' . $x . ']="' . $y . '"<br />';
		if (is_array($y))
		{
			foreach ($y as $p => $q)
			{
				$html .= '. . . . [' . $p . ']="' . $q . '"<br />';
			}
		}
	}
	$html .= '</div></body></html>';
	echo $html;
	return;

?>
