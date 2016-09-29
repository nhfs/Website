<?php
//version510//

//this is 'sumac_form_chosen.php'
//control is passed here by the register button on the course selection page

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
			($referer != '/sumac_personal_history.php') &&
			($referer != '/sumac_identify_user.php') &&
			($referer != '/sumac_courses_redirect.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/sumac_form_chosen.php')
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


	//check that we have a contact id for the form owner
	if (!isset($_SESSION[SUMAC_SESSION_CONTACT_ID]))
	{
		sumac_destroy_session(SUMAC_ERROR_NO_OWNER_ID . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];
include_once 'sumac_xml.php';

	//first see if we have jumped here via an href link, i.e. with the HTTP GET method
	if (isset($_GET['filledform']))
	{
		$_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] = $_GET['formsopen'];
		$filledform = $_GET['filledform'];
		$formIdAsName = $filledform;
//echo 'filledform=' . $filledform  . '<br />';
		$formTemplateDocument = sumac_loadFormTemplateDocument($source,$port,SUMAC_REQUEST_PARAM_FILLEDFORM,$filledform);
	}
	//... not a filled form, so was it a new form?
	else if (isset($_POST['newform']))
	{
		$_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] = $_POST['formsopen'];
		$newform = $_POST['newform'];
		$formIdAsName = $newform;
//echo 'newform=' . $newform  . '<br />';
		$formTemplateDocument = sumac_loadFormTemplateDocument($source,$port,SUMAC_REQUEST_PARAM_NEWFORM,$newform);
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_form_chosen without either variable filledform or newform');
		return;
	}
	if ($formTemplateDocument == false)
	{
		echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FORMTEMPLATE,$formIdAsName);
		return;
	}
include_once 'sumac_manage_forms.php';
	if (sumac_execViewOrUpdateForm('','sumac_form_updated.php',$formTemplateDocument) == false)
	{
		echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FILLEDFORM,$formIdAsName);
	}

?>
