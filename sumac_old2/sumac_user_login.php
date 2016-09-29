<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_http.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';
include_once 'sumac_ticketing_utilities.php';

function sumac_execLogin($loginStatus,$identifyUserPHP,$requestpasswordForm,$adduserForm)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$html = sumac_getHTMLHeadForLogin();
	$html .= sumac_getHTMLBodyForLogin($loginStatus,$organisationDocument,$identifyUserPHP,$requestpasswordForm,$adduserForm);

	echo $html;

	return true;
}

function sumac_getHTMLHeadForLogin()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)	$html .= sumac_getCommonCourseCatalogHTMLStyleElements();

	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= sumac_getLoginHTMLScriptFunctions();
	$html .= '</script>' . "\n";
//	$html .= '<script type="text/javascript" src="user/jquery.js"></script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}

function sumac_getLoginHTMLScriptFunctions()
{
	return <<<EOLJS

	function sumac_setnewemail(newemailid,loginemailid)
	{
		var newemail = document.getElementById(newemailid);
		var loginemail = document.getElementById(loginemailid);
		if ((newemail != null) && (loginemail != null)) newemail.value = loginemail.value;
		else alert(newemailid+" or "+loginemailid+" invalid id for email field");
		if (newemail.value == "")
		{
			loginemail.className = sumac_class_missing;
			loginemail.focus();
		}
		else
		{
			loginemail.className = "";
		}
	}
EOLJS;
}

function sumac_getHTMLBodyForLogin($loginStatus,$organisationDocument,$identifyUserPHP,$requestpasswordForm,$adduserForm)
{
	$html = '<body>' . "\n";

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'login') . sumac_getSubtitle();

	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
	{
		if (sumac_countTicketOrdersInBasket() > 0)
		{
			$extrasDocument = sumac_reloadExtrasDocument();
			if ($extrasDocument != false)
			{
				$html .= sumac_addParsedXmlIfDebugging($extrasDocument,'extras');
				$html .= sumac_getBasketAndExtrasHTML($organisationDocument,$extrasDocument,true);
			}
		}
	}
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
	{
		if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
		{
			$extrasDocument = sumac_reloadExtrasDocument();
			if ($extrasDocument != false)
			{
				$html .= sumac_addParsedXmlIfDebugging($extrasDocument,'extras');
				$html .= sumac_getSelectedSessionHTML($organisationDocument,$extrasDocument);
			}
		}
	}

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',true,false);

	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
		$html .= sumac_getHTMLBodyForTicketingActionsNavbar('sumac_top_ticketing_navbar','',true,false);
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_top_courses_navbar','login');

	$html .= sumac_getHTMLFormsForIdentifyingMember($loginStatus,$identifyUserPHP,$requestpasswordForm,$adduserForm);

	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
		$html .= sumac_getHTMLBodyForTicketingActionsNavbar('sumac_bottom_ticketing_navbar','',true,false);
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		$html .= sumac_getHTMLBodyForCoursesActionsNavbar('sumac_bottom_courses_navbar','login');

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',true,false);
//	$html .= sumac_getHTMLBodyForActions('ok',$identifyUserPHP,true);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if ($loginStatus != '')
	{
		if ($adduserForm) $html .= sumac_getJSToRestoreEnteredValues('adduser',SUMAC_ID_FORM_ADDUSER);
		$html .= sumac_getJSToRestoreEnteredValues('login',SUMAC_ID_FORM_LOGIN);
		if ($requestpasswordForm) $html .= sumac_getJSToRestoreEnteredValues('requestpassword',SUMAC_ID_FORM_PASSWORD);
		else if (isset($_POST['newemail'])) $html .= sumac_getJSToRestoreUsername($_POST['newemail']);
	}
	else
	{
		if (!isset($_SESSION[SUMAC_SESSION_HTTPCONFIRMED]))
		{
			$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
			if ($usingHTTPS == false) $html .= sumac_getJSToConfirmUseOfHTTP();
			$_SESSION[SUMAC_SESSION_HTTPCONFIRMED] = "once";
		}
	}

	$html .= '</body></html>' . "\n";
	return $html;
}

function sumac_getJSToRestoreUsername($newemailvalue)
{
	$html = '';
	if ((!isset($_POST['login'])) && (isset($_POST['adduser'])))
	{
		$html .= '<script  type="text/javascript">' . "\n";
		$html .= 'var userinput = document.getElementById("sumac_inputloginemail");';
		$html .= 'if (userinput != null) userinput.value = "' . $newemailvalue .'";' . "\n";
		$html .= '</script>' . "\n";
	}
	return $html;
}

function sumac_getHTMLFormsForIdentifyingMember($loginStatus,$identifyUserPHP,$requestpasswordForm,$adduserForm)
{
	$html = '<div id="' . SUMAC_ID_DIV_IDENTIFY . '" class="sumac_maintable">' . "\n";
	$html .= '<table border="0" rules="none" width="100%">' . "\n";
//	$html .= '<table border="0" rules="none">' . "\n"; //adduser and password

	$tdForLoginStatus = ($adduserForm && $requestpasswordForm) ? '<td colspan="2">' : '<td>';
	$html .= '<tr>' . $tdForLoginStatus . "\n";
	$html .= sumac_getHTMLTableOfLoginStatus($loginStatus);
	$html .= '</td></tr>' . "\n";

	$html .= '<tr><td valign="top" align="center">' . "\n";

	$smallform = ($adduserForm ? '' : ' sumac_smallform'); // only pages with the adduser panel are unlimited in their width
	if ($adduserForm && $requestpasswordForm)
	{
		$html .= sumac_getHTMLFormForAddingUser($identifyUserPHP);
		$html .= '</td><td valign="top" align="center" width="35%">' . "\n";
		$html .= '<table cellspacing="10"><tr><td>' . "\n";
	}

	if ($adduserForm && !$requestpasswordForm)
	{
		$html .= sumac_getHTMLFormForMemberLoginOrAddingUser($identifyUserPHP);
	}
	else
	{
		if ($requestpasswordForm)
		{
			$html .= sumac_getHTMLFormForMemberLogin($identifyUserPHP,$smallform,$adduserForm); // with or without adduser as well as reqpass
		}
		else
		{
			$html .= sumac_getHTMLFormForMemberLoginNP($identifyUserPHP,$smallform); // without adduser or reqpass
		}
	}

	if ($requestpasswordForm)
	{
		$html .= '</td></tr>' . "\n";
//		$html .= '<tr><td>' . "\n"; // adduser
		$html .= '<tr><td align="center">' . "\n"; // no adduser
		$html .= sumac_getHTMLFormForForgottenPassword($identifyUserPHP,$smallform,$adduserForm);
		if ($adduserForm)
		{
			$html .= '</td></tr></table>' . "\n";
		}
	}

	$html .= '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableOfLoginStatus($loginStatus)
{
	$html = '<table title="Login error message" border="0" rules="none" width="100%">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $loginStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForMemberLogin($identifyUserPHP,$smallform,$adduserForm)
{
	$html = '<div id="' . SUMAC_ID_DIV_LOGIN . '">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_LOGIN . '" accept-charset="UTF-8" method="post" action="' . $identifyUserPHP . '">' . "\n";
	$html .= '<table class="sumac_login' . $smallform . '">' . "\n";
	$instructions = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
					? $_SESSION[SUMAC_SESSION_CULOGIN] : $_SESSION[SUMAC_SESSION_INSTRUCTIONS_LOGIN];
	$html .= '<tr><td class="sumac_instructions">' . $instructions . '</td></tr>' . "\n";
	$html .= '<tr>' . "\n";

	$html .= '<td>' . $_SESSION[SUMAC_STR]["AF17"] . '<input name="loginemailusername" type="text" size="20" maxlength="60" value="' . $_SESSION[SUMAC_SESSION_EMAILADDRESS] . '" /></td>' . "\n";
	$html .= '</tr><tr>' . "\n";
	$html .= '<td>' . $_SESSION[SUMAC_STR]["AF18"] . '<input name="password" type="password" size="12" maxlength="' . SUMAC_FIELD_MAXLENGTH_PASSWORD .  '" value="" /></td>' . "\n";
	$html .= '</tr><tr>' . "\n";
	$html .= '<td align="right"><input name="login" type="submit" value="' . $_SESSION[SUMAC_STR]["AL1"] . '" onclick="if (sumac_checknamedfields([\'loginemailusername\',\'password\'])) return false; return true;" /></td>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '</table>' . "\n";
	if ($adduserForm) $html .= '<input type="hidden" name="withadduser" value="true" />' . "\n";
	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLFormForMemberLoginNP($identifyUserPHP,$smallform)
{
	$html = '<div id="' . SUMAC_ID_DIV_LOGIN . '">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_LOGIN . '" accept-charset="UTF-8" method="post" action="' . $identifyUserPHP . '">' . "\n";
	$html .= '<table class="sumac_login' . $smallform . '">' . "\n";

	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $instructions = $_SESSION[SUMAC_SESSION_MRLOGINWOPW];
	else $instructions = $_SESSION[SUMAC_SESSION_CULOGINWOPW];	//must be contact update package
	$html .= '<tr><td class="sumac_instructions">' . $instructions . '</td></tr>' . "\n";
	$html .= '<tr>' . "\n";
	$html .= '<td>' . $_SESSION[SUMAC_STR]["AF17"] . '<input name="email" type="text" size="30" maxlength="60" value="' . $_SESSION[SUMAC_SESSION_EMAILADDRESS] . '" /></td>' . "\n";
	$html .= '</tr><tr>' . "\n";
	$html .= '<td align="right"><input name="login" type="submit" value="' . $_SESSION[SUMAC_STR]["AL1"] . '" onclick="if (sumac_checknamedfields([\'email\'])) return false; return true;" /></td>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '</table>' . "\n";
	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLFormForForgottenPassword($identifyUserPHP,$smallform,$adduserForm)
{
	$html = '<div id="' . SUMAC_ID_DIV_FORGOTTEN . '">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_PASSWORD . '" accept-charset="UTF-8" method="post" action="' . $identifyUserPHP . '">' . "\n";
	$html .= '<table class="sumac_getpwd' . $smallform . '">' . "\n";
	$html .= '<tr><td class="sumac_instructions">' . $_SESSION[SUMAC_SESSION_INSTRUCTIONS_EMAIL_PASSWORD] . '</td></tr>' . "\n";
	$html .= '<tr>' . "\n";
	$html .= '<td>' . $_SESSION[SUMAC_STR]["AF17"] . '<input name="requestemailusername" type="text" size="20" maxlength="60" value="' . $_SESSION[SUMAC_SESSION_EMAILADDRESS] . '" /></td>' . "\n";
	$html .= '</tr><tr>' . "\n";
	$html .= '<td align="right"><input name="requestpassword" type="submit" value="' . $_SESSION[SUMAC_STR]["AL2"] . '" onclick="if (sumac_checknamedfields([\'requestemailusername\'])) return false; return true;" /></td>' . "\n";
	$html .= '</tr>' . "\n";
	$html .= '</table>' . "\n";
	if ($adduserForm) $html .= '<input type="hidden" name="withadduser" value="true" />' . "\n";
	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLFormForAddingUser($identifyUserPHP)
{
	$html = '<div id="' . SUMAC_ID_DIV_ADDUSER . '">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_ADDUSER . '" accept-charset="UTF-8" method="post" action="' .  $identifyUserPHP . '">' . "\n";
	$html .= '<table class="sumac_new">' . "\n";

	$instructions = '';
	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION) $instructions = $_SESSION[SUMAC_SESSION_DPADDCONTACT];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES) $instructions = $_SESSION[SUMAC_SESSION_CRADDCONTACT];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) $instructions = $_SESSION[SUMAC_SESSION_TOADDCONTACT];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $instructions = $_SESSION[SUMAC_STR]["MI7"];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') $instructions = $_SESSION[SUMAC_STR]["AI3"];	//login without any package
	$html .= '<tr><td colspan="2" class="sumac_instructions">' . $instructions . '</td></tr>' . "\n";

	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF1"],'text','email','30','60',false);
	$mandatoryFieldList = "'email'";
	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF10"],'password','newpassword',SUMAC_FIELD_MAXLENGTH_PASSWORD,SUMAC_FIELD_MAXLENGTH_PASSWORD,false);
	$mandatoryFieldList .= ",'newpassword'";
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_FIRSTNAME) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_FIRSTNAME) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF8"],'text','firstname','20','20',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'firstname'";
	}
	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF3"],'text','lastname','30','30',false);
	$mandatoryFieldList .= ",'lastname'";
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_ADDRESS) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_ADDRESS) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF6"],'text','address1','30','35',$optional);
		$html .= sumac_getHTMLFormField('&nbsp;','text','address2','30','35',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'address1'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CITY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CITY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF7"],'text','city','25','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'city'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PROVINCE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PROVINCE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF11"],'text','province','20','40',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'province'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_POSTCODE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_POSTCODE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF5"],'text','postcode','10','10',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'postcode'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_COUNTRY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_COUNTRY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF2"],'text','country','20','35',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'country'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF9"],'text','phone','16','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'phone'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CELLPHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CELLPHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF4"],'text','cellphone','16','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'cellphone'";
	}

	$html .= '<tr><td align="right" colspan="2"><input type="submit" name="adduser" value="' . $_SESSION[SUMAC_STR]["AL12"] . '"' .
				 ' onclick="if (sumac_checknamedfields([' . $mandatoryFieldList . '])) return false; return true;" ' .
				 '/></td></tr>' . "\n";

	$html .= '</table>' . "\n";
	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLFormForMemberLoginOrAddingUser($identifyUserPHP)
{
	$html = '<div id="' . SUMAC_ID_DIV_ADDUSER . '">' . "\n";

	$html .= '<form id="' . SUMAC_ID_FORM_LOGIN . '" accept-charset="UTF-8" method="post" action="' . $identifyUserPHP . '">' . "\n";
	$html .= '<input type="hidden" name="withadduser" value="true" />' . "\n";

	if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION) $instructions = $_SESSION[SUMAC_SESSION_DPADDORLOGIN];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES) $instructions = $_SESSION[SUMAC_SESSION_CRADDORLOGIN];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING) $instructions = $_SESSION[SUMAC_SESSION_TOADDORLOGIN];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $instructions = $_SESSION[SUMAC_SESSION_MRLOGINWOPW];
	else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') $instructions = $_SESSION[SUMAC_STR]["AI4"];	//login without any package
	$html .= '<table><tr><td class="sumac_instructions">' . $instructions . '</td></tr></table>' . "\n";

	$html .= '<table class="sumac_neworlogin">' . "\n";


	//$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF1"],'text','loginemail','30','60',false);
	$html .= '<tr><td>' . $_SESSION[SUMAC_STR]["AF1"] . '</td>' .
	//				'<td><input id="sumac_inputloginemail" type="text" name="loginemail" size="30" maxlength="60" value="' . $_SESSION[SUMAC_SESSION_NEWEMAIL] . '" /></td></tr>' . "\n";
					'<td><input id="sumac_inputloginemail" type="text" name="loginemail" size="30" maxlength="60" value="" /></td></tr>' . "\n";

	$html .= '<tr><td colspan="2" align="right"><input name="login" type="submit" title="' . $_SESSION[SUMAC_STR]["AT10"] . '"' .
				' value="' . $_SESSION[SUMAC_STR]["AL1"] . '" onclick="if (sumac_checknamedfields([\'loginemail\'])) return false; return true;" /></td></tr>' . "\n";
	$html .= '</form>' . "\n";

	$html .= '<form id="' . SUMAC_ID_FORM_ADDUSER . '" accept-charset="UTF-8" method="post" action="' .  $identifyUserPHP . '">' . "\n";

	$html .= '<input id="sumac_inputnewemail" type="hidden" name="newemail" value="" />' . "\n";
	$mandatoryFieldList = "'newemail'";

	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_FIRSTNAME) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_FIRSTNAME) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF8"],'text','firstname','20','20',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'firstname'";
	}
	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF3"],'text','lastname','30','30',false);
	$mandatoryFieldList .= ",'lastname'";
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_ADDRESS) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_ADDRESS) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF6"],'text','address1','30','35',$optional);
		$html .= sumac_getHTMLFormField('&nbsp;','text','address2','30','35',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'address1'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CITY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CITY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF7"],'text','city','25','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'city'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PROVINCE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PROVINCE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF11"],'text','province','20','40',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'province'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_POSTCODE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_POSTCODE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF5"],'text','postcode','10','10',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'postcode'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_COUNTRY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_COUNTRY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF2"],'text','country','20','35',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'country'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF9"],'text','phone','16','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'phone'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CELLPHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CELLPHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["AF4"],'text','cellphone','16','25',$optional);
		if ($optional === false) $mandatoryFieldList .= ",'cellphone'";
	}

	$html .= '<tr><td align="right" colspan="2"><input type="submit" name="adduser"  title="' . $_SESSION[SUMAC_STR]["AT11"] . '" value="' . $_SESSION[SUMAC_STR]["AL12"] . '"' .
				 ' onclick="sumac_setnewemail(\'sumac_inputnewemail\',\'sumac_inputloginemail\'); if (sumac_checknamedfields([' . $mandatoryFieldList . '])) return false; return true;" ' .
				 '/></td></tr>' . "\n";

	$html .= '</table>' . "\n";
	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}
?>
