<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_form2.php';
include_once 'sumac_login2.php';
include_once 'sumac_xml.php';

function sumac_singleform2($previousRequest,$responseMessage,$loadedAccountDocument,$loadedFormTemplateElement)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$accountDocument = null;
	if ($loadedAccountDocument != null) $accountDocument = $loadedAccountDocument;
	else
	{
		$savedAccountDocument = sumac_reloadLoginAccountDocument();
		if ($savedAccountDocument !== false) $accountDocument = $savedAccountDocument;
	}

	$formTemplateElement = null;
	if ($loadedFormTemplateElement != null)
	{
		$formTemplateElement = $loadedFormTemplateElement;
	}
	else
	{
		$formTemplateElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		if ($formTemplateElement == null)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FORMTEMPLATE_DATA;
			return;
		}
	}
	$filledFormElement = $formTemplateElement->getElementsByTagName(SUMAC_ELEMENT_FILLEDFORM)->item(0);
	if ($filledFormElement == null)
	{
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FILLEDFORM;
		return false;
	}

	$html = sumac_singleform2_HTML($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage);
	echo $html;
	return true;
}

function sumac_singleform2_HTML($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
{
	return sumac_geth2_head('singleform2')
			.sumac_singleform2_body($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage);
}

function sumac_singleform2_body($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
{
	$retryform = ($previousRequest != '') ? 'all' : null;

	return '<body>'.PHP_EOL
			.(($previousRequest == '') ? sumac_addParsedXmlIfDebugging($organisationDocument,'singleform2_organisation')
									: sumac_addParsedXmlIfDebugging($accountDocument,'singleform2_accountdetails'))
			.(($formTemplateElement != null) ? sumac_addParsedXmlIfDebugging($formTemplateElement,'singleform2_formtemplate') : '')
			.sumac_geth2_user('top','singleform2')
			.sumac_singleform2_content($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
			.sumac_geth2_body_footer('singleform2',true,$retryform,'');
}

function sumac_singleform2_content($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_divtag('singleform2','mainpage','mainpage');
	$html .= sumac_singleform2_instructions();
//	$html .= sumac_singleform2_HTMLform($formTemplateElement,$organisationDocument,$accountDocument,$previousRequest,$responseMessage);
	$buttoncode = 'O';
	$panelLayout = 'CL';
	$whereNext = '';
	$ownerId = '';
	$retryData = 'sumac_singleform2.php|'.$ownerId;
	$html .= sumac_form2_embedded('singleform2','after',$formTemplateElement,$buttoncode,$panelLayout,
									$organisationDocument,$whereNext,$ownerId,$retryData,
									$accountDocument,$previousRequest,$responseMessage,true,'login');
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_singleform2_instructions()
{
	$html = sumac_geth2_divtag('singleform2','instructions','instructions');
	$html .= sumac_geth2_spantext('singleform2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_retry_submission($pkg,$formId,$responseStatus,$request,$responseMessage,$next,$retryData)
{
	sumac_singleform2($request,$responseMessage,null,sumac_getFormtemplateElement($formId,true));
}

?>