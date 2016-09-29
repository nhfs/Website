<?php
//version510//

include_once 'sumac_constants.php';
include_once 'sumac_xml.php';
include_once 'sumac_utilities.php';

function sumac_execUpdate($updateStatus,$contactUpdatedPHP,$accountDocument)
{
	$chunkyContactDocument = ($accountDocument == null) ? sumac_reloadLoginAccountDocument() : $accountDocument;
	if ($chunkyContactDocument == false) return false;

	$html = sumac_getHTMLHeadForUpdate();

	$html .= '<body>' . "\n";

	$html .= sumac_addParsedXmlIfDebugging($chunkyContactDocument,'chunkycontactdetails');

	$html .= sumac_getUserHTML(SUMAC_USER_TOP,true,'update') . sumac_getSubtitle();

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_top_action_navbar',false,false);

	$html .= sumac_getHTMLFormForUpdate($updateStatus,$contactUpdatedPHP,$chunkyContactDocument);

	$html .= sumac_getHTMLBodyForControlNavbar('sumac_bottom_action_navbar',false,false);
//	$html .= sumac_getHTMLBodyForActions('ok',$contactUpdatedPHP,true);

	$html .= sumac_getSumacFooter() . sumac_getUserHTML(SUMAC_USER_BOTTOM);

	if ($updateStatus != '') $html .= sumac_getJSToRestoreEnteredValues('update',SUMAC_ID_FORM_UPDATE);

	$html .= '</body></html>' . "\n";

	echo $html;

	return true;
}

function sumac_getHTMLHeadForUpdate()
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";

	$html .= sumac_getHTMLMetaSettings();
	$html .= sumac_getHTMLTitle('','');

	$html .= '<style type="text/css">';
	$html .= sumac_getCommonHTMLStyleElements();
	$html .= sumac_getUserCSS(SUMAC_USER_TOP);
	$html .= sumac_getUserCSS(SUMAC_USER_BOTTOM);
	$html .= sumac_getUserOverrideHTMLStyleElementsIfNotSuppressed();
	$html .= '</style>' . "\n";

	$html .= '<script  type="text/javascript">' . "\n";
	$html .= sumac_getCommonHTMLScriptVariables();
	$html .= sumac_getCommonHTMLScriptFunctions();
	$html .= '</script>' . "\n";

	$html .= '</head>' . "\n";

	return $html;
}


function sumac_getHTMLFormForUpdate($updateStatus,$contactUpdatedPHP,$chunkyContactDocument)
{
	$html = '<div id="' . SUMAC_ID_DIV_UPDATE . '" class="sumac_maintable">' . "\n";
	$html .= '<form id="' . SUMAC_ID_FORM_UPDATE . '" accept-charset="UTF-8" method="post" action="' . $contactUpdatedPHP . '">' . "\n";

	$html .= '<table align="center"><tr><td class="sumac_instructions">' . $_SESSION[SUMAC_SESSION_INSTRUCTIONS_UPDATE] . '</td></tr></table>' . "\n";

	$html .= '<table border="0" rules="none" width="100%">' . "\n";

	$html .= '<tr><td align="center">' . "\n";
	$html .= sumac_getHTMLTableOfUpdateStatus($updateStatus);
	$html .= '</td></tr><tr><td align="center">' . "\n";
	$html .= sumac_getHTMLFormForUpdatingContactDetails($chunkyContactDocument);
	$html .= '</td></tr>' . "\n";

	$html .= '</table>' . "\n";

	$html .= '</form>' . "\n";
	$html .= '</div>' . "\n";
	return $html;
}

function sumac_getHTMLTableOfUpdateStatus($updateStatus)
{
	$html = '<table title="Update error message" border="0" rules="none" width="100%" align="center">' . "\n";
	$html .= '<tr><td id="' . SUMAC_ID_TD_STATUS . '" class="sumac_status" align="center">' . $updateStatus . '</td></tr>' . "\n";
	$html .= '</table>' . "\n";
	return $html;
}

function sumac_getHTMLFormForUpdatingContactDetails($chunkyContactDocument)
{
	$html = '<table class="sumac_update">' . "\n";

	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF4"],'text','email','30','60',false,$_SESSION[SUMAC_SESSION_EMAILADDRESS]);
	$mandatoryFieldList = "'email'";

	if ($_SESSION[SUMAC_SESSION_FORMER_PASSWORD] != SUMAC_SUMAC_NULL_PASSWORD)
	{
		$html .=  '<tr><td>' . $_SESSION[SUMAC_STR]["UF8"] .
					'</td><td><input type="password" autocomplete="off" name="newpassword" size="' .
					SUMAC_FIELD_MAXLENGTH_PASSWORD . '" maxlength="' . SUMAC_FIELD_MAXLENGTH_PASSWORD . '" placeholder="" value="" /></td></tr>';
	}
	//$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF8"],'password','newpassword',SUMAC_FIELD_MAXLENGTH_PASSWORD,SUMAC_FIELD_MAXLENGTH_PASSWORD,false,'');
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_FIRSTNAME) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_FIRSTNAME) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF5"],'text','firstname','20','20',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_FIRSTNAME));
		if ($optional === false) $mandatoryFieldList .= ",'firstname'";
	}
	$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF6"],'text','lastname','30','30',false,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_LASTNAME));
	$mandatoryFieldList .= ",'lastname'";
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_ADDRESS) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_ADDRESS) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF2"],'text','address1','30','35',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_ADDRESSLINE1));
		$html .= sumac_getHTMLFormField('&nbsp;','text','address2','30','35',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_ADDRESSLINE2));
		if ($optional === false) $mandatoryFieldList .= ",'address1'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CITY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CITY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF7"],'text','city','25','25',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_CITY));
		if ($optional === false) $mandatoryFieldList .= ",'city'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PROVINCE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PROVINCE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF10"],'text','province','20','40',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_PROVINCE));
		if ($optional === false) $mandatoryFieldList .= ",'province'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_POSTCODE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_POSTCODE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF11"],'text','postcode','10','10',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_POSTCODE));
		if ($optional === false) $mandatoryFieldList .= ",'postcode'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_COUNTRY) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_COUNTRY) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF3"],'text','country','20','35',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_COUNTRY));
		if ($optional === false) $mandatoryFieldList .= ",'country'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_PHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_PHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF9"],'text','phone','16','25',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_HOMEPHONE));
		if ($optional === false) $mandatoryFieldList .= ",'phone'";
	}
	if (strpos($_SESSION[SUMAC_SESSION_FIELDS_HIDDEN],SUMAC_FIELD_CELLPHONE) === false)
	{
		$optional = (strpos($_SESSION[SUMAC_SESSION_FIELDS_MANDATORY],SUMAC_FIELD_CELLPHONE) === false);
		$html .= sumac_getHTMLFormField($_SESSION[SUMAC_STR]["UF1"],'text','cellphone','16','25',$optional,sumac_getElementValue($chunkyContactDocument,SUMAC_ELEMENT_CELLPHONE));
		if ($optional === false) $mandatoryFieldList .= ",'cellphone'";
	}


	$html .= '<tr><td colspan="2" align="left"><input type="submit" name="update" value="' . $_SESSION[SUMAC_STR]["UL1"] . '"' .
				 ' onclick="if (sumac_checknamedfields([' . $mandatoryFieldList . '])) return false; return true;" ' .
				 '/></td></tr>' . "\n";

	$html .= '</table>' . "\n";
	return $html;
}

?>
