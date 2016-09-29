<?php
//version5645//

//this is 'sumac_grantreview2_formchosen.php'

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

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
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_form2_submitted.php') &&
			($referer != '/sumac_grantreview2_formchosen.php') &&
			($referer != '/sumac_identify_user.php')
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
	if ((!isset($_GET['activeform'])) && (!isset($_GET['completedform'])))
	{
		sumac_destroy_session('Unexpected arrival in sumac_grantreview2_formchosen without either variable activeform or completedform');
		return;
	}

	$formTemplateElements = array();
	$source = $_SESSION[SUMAC_SESSION_SOURCE];
	$port = $_SESSION[SUMAC_SESSION_PORT];

	$pkg = isset($_GET['pkg']) ? $_GET['pkg'] : $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE];
	$next = isset($_GET['next']) ? $_GET['next'] : '';
	$cat = isset($_GET['cat']) ? $_GET['cat'] : 'any';
	$grappid = isset($_GET['grappid']) ? $_GET['grappid'] : '';

include_once 'sumac_xml.php';
	if (isset($_GET['activeform']))
	{
		$activeform = $_GET['activeform'];
		$formIdAsName = $activeform;

		$formTemplateElement = (isset($_GET['forminorgdoc'])) ? sumac_getFormtemplateElement($activeform,true) : false;
		if ($formTemplateElement == false)
		{
			$request = isset($_GET['newform']) ? SUMAC_REQUEST_PARAM_NEWFORM : SUMAC_REQUEST_PARAM_FILLEDFORM;
			$activeformTemplateDocument = sumac_loadFormTemplateDocument($source,$port,$request,$activeform,$grappid);
			if ($activeformTemplateDocument == false)
			{
				//echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FORMTEMPLATE,$formIdAsName);
				$msg = $formIdAsName.' - '.SUMAC_ERROR_NO_FORMTEMPLATE;
				if ($next != '')
				{
include_once $next;
					if (sumac_form2_dealt_with($pkg,'hopeless',$request,$msg,$next,'') == false)
					{
						echo sumac_geth2_abort_page($pkg,$msg,'','');
						sumac_destroy_session('');
					}
				}
				else
				{
					echo sumac_geth2_abort_page($pkg,$msg,'','');
				}
				return;
			}
			$formTemplateElement = ($activeformTemplateDocument->documentElement->tagName == SUMAC_ELEMENT_FORMTEMPLATE)
									? $activeformTemplateDocument->documentElement
									: $activeformTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		}
		$formTemplateElements[] = $formTemplateElement;
	}
	if (isset($_GET['completedform']))
	{
		$completedform = $_GET['completedform'];
		if (is_array($completedform))
		{
			$formcount = count($completedform);
			for ($i = 0; $i < $formcount; $i++)
			{
				$oneform = $completedform[$i];
				$formIdAsName = $oneform;
				$completedformTemplateDocument = sumac_loadFormTemplateDocument($source,$port,SUMAC_REQUEST_PARAM_FILLEDFORM,$oneform,$grappid);
				if ($completedformTemplateDocument == false)
				{
					//echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FORMTEMPLATE,$formIdAsName);
					$msg = $formIdAsName.' - '.SUMAC_ERROR_NO_FORMTEMPLATE;
					if ($next != '')
					{
include_once $next;
						if (sumac_form2_dealt_with($pkg,'hopeless',SUMAC_REQUEST_PARAM_FILLEDFORM,$msg,$next,'') == false)
						{
							echo sumac_geth2_abort_page($pkg,$msg,'','');
							sumac_destroy_session('');
						}
					}
					else
					{
						echo sumac_geth2_abort_page($pkg,$msg,'','');
					}
					return;
				}
				$formTemplateElements[] = ($completedformTemplateDocument->documentElement->tagName == SUMAC_ELEMENT_FORMTEMPLATE)
										? $completedformTemplateDocument->documentElement
										: $completedformTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
			}
		}
		else
		{
			$formIdAsName = $completedform;
			$completedformTemplateDocument = sumac_loadFormTemplateDocument($source,$port,SUMAC_REQUEST_PARAM_FILLEDFORM,$completedform,$grappid);
			if ($completedformTemplateDocument == false)
			{
				//echo sumac_getFormClosingHTML(SUMAC_ERROR_NO_FORMTEMPLATE,$formIdAsName);
				$msg = $formIdAsName.' - '.SUMAC_ERROR_NO_FORMTEMPLATE;
				if ($next != '')
				{
include_once $next;
					if (sumac_form2_dealt_with($pkg,'hopeless',SUMAC_REQUEST_PARAM_FILLEDFORM,$msg,$next,'') == false)
					{
						echo sumac_geth2_abort_page($pkg,$msg,'','');
						sumac_destroy_session('');
					}
				}
				else
				{
					echo sumac_geth2_abort_page($pkg,$msg,'','');
				}
				return;
			}
			$formTemplateElements[] = ($completedformTemplateDocument->documentElement->tagName == SUMAC_ELEMENT_FORMTEMPLATE)
									? $completedformTemplateDocument->documentElement
									: $completedformTemplateDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		}
	}

include_once 'sumac_grantreview2_forms.php';
	if (sumac_grantreview2_forms($pkg,'','',$next,$cat,null,$grappid,$formTemplateElements) == false)
	{
		echo sumac_getAbortHTML();
		sumac_destroy_session('');
	}

?>