<?php
//version567//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_form2.php';
include_once 'sumac_xml.php';

function sumac_grantreview2_forms($pkg,$previousRequest,$responseMessage,$whereNext,$category,$loadedAccountDocument,$grappid,$loadedFormTemplateElements)
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
	if ($accountDocument == null) return false;

	$formTemplateElements = null;
	if ($loadedFormTemplateElements != null)
	{
		$formTemplateElements = $loadedFormTemplateElements;
	}
	else
	{
//@@@ why do all this?
		$formTemplateElement = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE)->item(0);
		if ($formTemplateElement == null)
		{
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] =  SUMAC_ERROR_NO_FORMTEMPLATE_DATA;
			return;
		}
		$formTemplateElements[0] = $formTemplateElement;
	}

	$html = sumac_grantreview2_forms_HTML($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage);
	echo $html;
	return true;
}

function sumac_grantreview2_forms_HTML($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage)
{
	return sumac_geth2_head($pkg)
			.sumac_grantreview2_forms_body($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage);
}

function sumac_grantreview2_forms_body($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage)
{
	$retryform = ($previousRequest != '') ? 'all' : null;
	$parsedXML = '';
	for ($i = 0; $i < count($formTemplateElements); $i++)
	{
		$parsedXML .= sumac_addParsedXmlIfDebugging($formTemplateElements[$i],$pkg.'_formtemplate_'.$i);
	}
	return '<body>'.PHP_EOL
			.sumac_geth2_user('top',$pkg)
			.$parsedXML
			.sumac_grantreview2_forms_content($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage)
			.sumac_geth2_body_footer($pkg,true,$retryform,'');
}

function sumac_grantreview2_forms_content($pkg,$formTemplateElements,$grappid,$organisationDocument,$whereNext,$category,$accountDocument,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

//get grant-application data from account document
	$grantApplicationElement = ($grappid == '') ? null : $accountDocument->getElementById($grappid);

	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the stringids are not duplicated
	$html .= sumac_geth2_divtag($pkg,$category,'mainpage');
	$html .= sumac_grantreview2_forms_instructions($pkg);
	$html .= sumac_grantreview2_application_header($pkg,$grantApplicationElement);
	$formTemplateElement = $formTemplateElements[0];
	$formcount = count($formTemplateElements);
	$ownerId = $grappid;
	$retryData = 'sumac_grantreview2_forms.php|'.$ownerId;
	for ($i = 0; $i < $formcount; $i++)
	{
		$retryData .= '|'.$formTemplateElements[$i]->getAttribute(SUMAC_ATTRIBUTE_ID);
	}
//	$html .= sumac_grantreview2_forms_HTMLform($formTemplateElement,$organisationDocument,$whereNext,$accountDocument,$previousRequest,$responseMessage);
	$withLogin = 'none';
	$loginbuttons = 'none';
	$buttoncode = 'N';
	$formbuttons = true;
	$panelLayout = '';
	$html .= sumac_form2_embedded($pkg,$withLogin,$formTemplateElement,$buttoncode,$panelLayout,$organisationDocument,
									$whereNext,$ownerId,$retryData,$accountDocument,
									$previousRequest,$responseMessage,$formbuttons,$loginbuttons);

	for ($i = 1; $i < $formcount; $i++)
	{
//		$html .= sumac_grantreview2_forms_HTMLform($pkg,'none',$formTemplateElements[$i],$organisationDocument,'',$accountDocument,'','',false);
//		$html .= sumac_form2_embedded($pkg,'none',$formTemplateElements[$i],$buttoncode,'',$organisationDocument,'','',
//									$accountDocument,'','',false);
		$html .= sumac_form2_otherform($pkg,$formTemplateElements[$i],$i);
	}
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_grantreview2_forms_instructions($pkg)
{
	$html = sumac_geth2_divtag($pkg,'instructions','instructions');
	$html .= sumac_geth2_spantext($pkg,'I2');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_grantreview2_application_header($pkg,$grantApplication)
{
	$html = sumac_geth2_divtag($pkg,'appheader','appheader');
	if ($grantApplication == null)
	{
		$html .= sumac_geth2_span($pkg,'noform','grant_new','H6',true);
	}
	else
	{
		$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
		$status = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_STATUS);	//%a
		$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);	//%b
		$grantee = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_GRANTEE);	//%c
//all other fields are optional so they may be null/blank
		$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);	//%d
		$begun2 = sumac_isNotNullDate($begun) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU1"],$begun) : '';
		$rank = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REVIEW_RANK);	//%e
		$rank2 = sumac_isNotNullValue($rank) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU2"],$rank) : '';
		$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);	//%f
		$requested2 = sumac_isNotNullAmount($requested) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU3"],sumac_centsToPrintableDollars($requested)) : '';
		$granted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_GRANTED);	//%g
		$granted2 = sumac_isNotNullAmount($granted) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU4"],sumac_centsToPrintableDollars($granted)) : '';
		$submitted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_SUBMITTED);	//%h
		$submitted2 = sumac_isNotNullDate($submitted) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU5"],$submitted) : '';
		$reviewed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_REVIEWED);	//%i
		$reviewed2 = sumac_isNotNullDate($reviewed) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU6"],$reviewed) : '';
		$accepted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_ACCEPTED);	//%j
		$accepted2 = sumac_isNotNullDate($accepted) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU7"],$accepted) : '';
		$closed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_CLOSED);	//%k
		$closed2 = sumac_isNotNullDate($closed) ? sumac_formatMessage($_SESSION[SUMAC_STR]["RGU8"],$closed) : '';
		switch ($status)
		{
			case 'in_progress':
				$html .= sumac_geth2_span($pkg,$formid,'grant_title','C1',true,array($status,$type,$grantee,$begun2,'',$requested2));
				break;
			case 'under_review':
				$html .= sumac_geth2_span($pkg,$formid,'grant_title','C2',true,array($status,$type,$grantee,$begun2,$rank2,$requested2,'',$submitted2,$reviewed2));
				break;
			case 'rejected':
				$html .= sumac_geth2_span($pkg,$formid,'grant_title','C3',true,array($status,$type,$grantee,$begun2,$rank2,$requested2,'',$submitted2,$reviewed2,'',$closed2));
				break;
			case 'accepted':
				$html .= sumac_geth2_span($pkg,$formid,'grant_title','C4',true,array($status,$type,$grantee,$begun2,$rank2,$requested2,$granted2,$submitted2,$reviewed2,$accepted2));
				break;
			case 'completed':
				$html .= sumac_geth2_span($pkg,$formid,'grant_title','C5',true,array($status,$type,$grantee,$begun2,$rank2,$requested2,$granted2,$submitted2,$reviewed2,$accepted2,$closed2));
				break;
			case 'withdrawn':
				if (($submitted != null) && ($submitted != '') && ($submitted != '0'))
				{
					$html .= sumac_geth2_span($pkg,$formid,'grant_title','C6',true,array($status,$type,$grantee,$begun2,$rank2,$requested2,'',$submitted2,$reviewed2,'',$closed2));
				}
				else
				{
					$html .= sumac_geth2_span($pkg,$formid,'grant_title','C7',true,array($status,$type,$grantee,$begun2,'',$requested2,'','','','',$closed2));
				}
				break;
		}
	}
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_form2_retry_submission($pkg,$formId,$responseStatus,$request,$responseMessage,$next,$retryData)
{
//for bad responses, retry the form submission
//the first retryData element is sumac_grantreview2_forms.php - the form submitter
//the second retryData element is the grant-application (owner) id
//the rest are form ids to be put in the array of formTemplateElements
	$formTemplateElements = array();
	$rdarray = explode('|',$retryData);
	$rdcount = count($rdarray);
	$grappid = ($rdcount > 1) ? $rdarray[1] : '';
	for ($i = 2; $i < $rdcount; $i++)
	{
		$formTemplateElement = sumac_getFormtemplateElement($rdarray[$i],false);
		if ($formTemplateElement !== false) $formTemplateElements[] = $formTemplateElement;
	}
	sumac_grantreview2_forms($pkg,$request,$responseMessage,$next,'',null,$grappid,$formTemplateElements);
}

?>