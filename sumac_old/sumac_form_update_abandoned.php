<?php
//version510//

//this is 'sumac_form_update_abandoned.php'

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
	if (
			($referer != '/sumac_form_chosen.php') &&
			($referer != '/sumac_form_updated.php')
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

	if (isset($_GET['releaseform']))
	{
		$formId = $_GET['releaseform'];
		unset($_SESSION[SUMAC_SESSION_FORM][$formId]);
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
							' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
		echo '<html><body><p>' . SUMAC_INFO_NO_NEED_TO_WAIT . '</p>' . "\n";
		echo '<script type="text/javascript">window.close();</script>' . "\n";
		echo '</body></html>' . "\n";
		exit();
	}

	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_form_update_abandoned without variable releaseform set');
	}
	return;

?>