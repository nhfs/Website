<?php
//version5125//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';

function sumac_get_parameter($default,$varname,$hrefvalue)
{
	$answer = $hrefvalue;
	if (($answer == null) || ($answer == ''))
	{
		$answer = sumac_get_parameter_from_file($default,$varname);
	}
	else
	{
		if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'parameters') !== false) echo '<I>Using</I> ' . $answer . ' <I>from <B>href</B> for</I> <B>' . $varname . '</B><br />';
	}
	return $answer;
}

function sumac_get_parameter_from_file($default,$varname)
{
	$answer = sumac_getUserParameterFromFile($varname);
	if ($answer == null)
	{
		$answer = $default;
		if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'fullparameters') !== false) echo '<I>Using</I> ' . $answer . ' <I>from <B>default</B> for</I> <B>' . $varname . '</B><br />';
	}
	else
	{
		if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'parameters') !== false) echo '<I>Using</I> ' . $answer . ' <I>from <B>file</B> for</I> <B>' . $varname . '</B><br />';
	}
	return $answer;
}

function sumac_get_referer($session_folder_name,$debug=false)
{
	if ($debug && isset($_SERVER['HTTP_HOST'])) echo 'host=' . $_SERVER['HTTP_HOST'] . '<br />';
	if (!isset($_SERVER['HTTP_HOST'])) return false;
	if ($debug && isset($_SERVER['HTTP_REFERER'])) echo 'referer=' . $_SERVER['HTTP_REFERER'] . '<br />';
	if (!isset($_SERVER['HTTP_REFERER'])) return false;
	$httpReferer = urldecode($_SERVER['HTTP_REFERER']);
	$queryPos = strpos($httpReferer,'?');
	if ($queryPos === false) $queryPos = strlen($httpReferer);
	$referer = substr($httpReferer,0,$queryPos);
	if (strtolower(substr($httpReferer,0,strlen('https:'))) == 'https:')
	{
		return substr($referer,strlen('https://' . $_SESSION[$session_folder_name]. $_SERVER['HTTP_HOST']));
	}
	else
	{
		return substr($referer,strlen('http://' . $_SESSION[$session_folder_name]. $_SERVER['HTTP_HOST']));
	}
}

function sumac_destroy_session($exit_message)
{
	if (strlen($exit_message) > 0) echo $exit_message . '<br />';
	//setcookie (SUMAC_SESSION_NAME, '', time() - 3600);
	session_destroy();
}

function sumac_forced_session_restart()
{
		$forcedrestart = array();
		$forcedrestart[SUMAC_SESSION_ACTIVE_PACKAGE] = $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE];
		$forcedrestart['entry'] = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DIRECTORIES) ? '' : 'login';
		$forcedrestart[SUMAC_SESSION_RETURN] = $_SESSION[SUMAC_SESSION_RETURN];
		$forcedrestart[SUMAC_USERPAR_SESSEXPIRY] = $_SESSION[SUMAC_USERPAR_SESSEXPIRY];
		$minutes = floor($_SESSION[SUMAC_USERPAR_SESSEXPIRY] / 60);
		$forcedrestart['message'] = sumac_formatMessage($_SESSION[SUMAC_STR]["AE11"],$minutes);
		require 'sumac_start_new_session.php';
		return;
}

function sumac_getLeaveSumacHTML($goto)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
						' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><body><p>' . SUMAC_INFO_PLEASE_WAIT_RETURNING . '</p>' . "\n";
	$html .= '<script type="text/javascript">window.location.assign("' . $goto . '#SUMACRETURN");</script>' . "\n";
	$html .= '</body></html>' . "\n";
	return($html);
}

/****** possibly useful session control function
function startSession($time = 3600, $ses = 'MYSES')
{
	session_set_cookie_params($time);
	session_name($ses);
	session_start();

	// Reset the expiration time upon page load
	if (isset($_COOKIE[$ses]))
	  setcookie($ses, $_COOKIE[$ses], time() + $time, "/");
}
*******/

function sumac_old_get_parameter($default,$varname)
{
	$alternatives = func_get_args();
	$answer = null;
	for ($i = 1; $i < func_num_args(); $i++)
	{
		if (isset($_GET[$alternatives[$i]])) $answer = $_GET[$alternatives[$i]];
	}
	if (($answer == null) || ($answer == ''))
	{
		$answer = sumac_get_parameter_from_file($default,$varname);
	}
	else
	{
		if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'parameters') !== false) echo '<I>Using</I> ' . $answer . ' <I>from <B>href</B> for</I> <B>' . $varname . '</B><br />';
	}
	return $answer;
}

?>