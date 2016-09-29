<?php
//version5681//

include_once 'sumac_xml.php';
include_once 'sumac_geth2.php';

function sumac_grantreview2_grantee($loadedAccountDocument,$previousResult)
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

	$html = sumac_grantreview2g_HTML($previousResult,$organisationDocument,$accountDocument);
	echo $html;
	return true;
}

function sumac_grantreview2g_HTML($previousResult,$organisationDocument,$accountDocument)
{
	return sumac_geth2_head('grantreview2g')
			.sumac_grantreview2g_body($previousResult,$organisationDocument,$accountDocument);
}

function sumac_grantreview2g_body($previousResult,$organisationDocument,$accountDocument)
{
	$retryform = null;
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($accountDocument,'grantreview2g_accountdetails')
			.sumac_geth2_user('top','grantreview2g')
			.sumac_grantreview2g_content($previousResult,$organisationDocument,$accountDocument)
			.sumac_geth2_body_footer('grantreview2g',true,$retryform,'');
}

function sumac_grantreview2g_content($previousResult,$organisationDocument,$accountDocument)
{
	//Get the organisations list of applications - if any
	$grantApplicationFormElements = $organisationDocument->getElementsByTagName(SUMAC_ELEMENT_GRANTAPPLICATION_FORM);

	//Get the users list of active and completed grant-applications
	$activeAndCompletedApplicationElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_GRANTAPPLICATION);

	$html = '<div id="sumac_content">'.PHP_EOL;
	$html .= sumac_geth2_divtag('grantreview2g','mainpage','mainpage');
	$html .= sumac_grantreview2g_instructions();
	if ($previousResult != '')
	{
		$html .= sumac_geth2_divtag('grantreview2g','previous_result','result').$previousResult.'</div>'.PHP_EOL;
	}
	$html .= sumac_geth2_sumac_title_with_gobacklink_and_line('grantreview2g','H1','L1',true);
	$html .= sumac_grantreview2g_old_applications_table($activeAndCompletedApplicationElements);
	$html .= sumac_grantreview2g_new_applications_table($grantApplicationFormElements);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_grantreview2g_instructions()
{
	$html = sumac_geth2_divtag('grantreview2g','instructions','instructions');
	$html .= sumac_geth2_spantext('grantreview2g','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_grantreview2g_old_applications_table($activeAndCompletedApplicationElements)
{
	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the stringids are not duplicated
	$unsubCount = $activeCount = $pastCount = 0;
	for ($i = 0; $i < $activeAndCompletedApplicationElements->length; $i++)
	{
		$grantApplication = $activeAndCompletedApplicationElements->item($i);
		$status = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_STATUS);
		if ($status == SUMAC_STATUS_APPLICATION_IN_PROGRESS) ++$unsubCount;
		else if (($status == SUMAC_STATUS_APPLICATION_UNDER_REVIEW) || ($status == SUMAC_STATUS_APPLICATION_ACCEPTED)) ++$activeCount;
		else ++$pastCount; //any other status assumed to be a completed/closed application
	}

	$html_u = sumac_geth2_sumac_subtitle('grantreview2g','H2',($unsubCount > 0)?'_R2G':'_R2G_empty');
	$html_u .= sumac_geth2_tabletag('grantreview2g','unsubmitted_applications','select_grant_application');

	$html_u .= sumac_geth2_trtag('grantreview2g','gip_head',($unsubCount > 0)?'gip_head':array('gip_head','empty_head'));
	$html_u .= sumac_geth2_tdtag('grantreview2g','gip_type_head','grant_link');
	$html_u .= sumac_geth2_span('grantreview2g','gip_type_head',array('grant_type','grant_head'),'C11',true);
	$html_u .= '</td>'.PHP_EOL;
	$html_u .= sumac_geth2_tdtag('grantreview2g','gip_status_head','grant_status');
	$html_u .= sumac_geth2_span('grantreview2g','gip_status_head',array('grant_status','grant_head'),'C12',true);
	$html_u .= '</td>'.PHP_EOL;
	$html_u .= sumac_geth2_tdtag('grantreview2g','gip_begun_head','grant_begun');
	$html_u .= sumac_geth2_span('grantreview2g','gip_begun_head',array('grant_begun','grant_head'),'C13',true);
	$html_u .= '</td>'.PHP_EOL;
	$html_u .= sumac_geth2_tdtag('grantreview2g','gip_requested_head','grant_requested');
	$html_u .= sumac_geth2_span('grantreview2g','gip_requested_head',array('grant_requested','grant_head'),'C15',true);
	$html_u .= '</td>'.PHP_EOL;
	$html_u .= '</tr>'.PHP_EOL;

	$html_a = sumac_geth2_sumac_subtitle('grantreview2g','H3',($activeCount > 0)?'_R2G':'_R2G_empty');
	$html_a .= sumac_geth2_tabletag('grantreview2g','active_applications','select_grant_application');

	$html_a .= sumac_geth2_trtag('grantreview2g','aga_head',($activeCount > 0)?'aga_head':array('aga_head','empty_head'));
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_type_head','grant_link');
	$html_a .= sumac_geth2_span('grantreview2g','aga_type_head',array('grant_type','grant_head'),'C11',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_status_head','grant_status');
	$html_a .= sumac_geth2_span('grantreview2g','aga_status_head',array('grant_status','grant_head'),'C12',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_begun_head','grant_begun');
	$html_a .= sumac_geth2_span('grantreview2g','aga_begun_head',array('grant_begun','grant_head'),'C13',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_submitted_head','grant_submitted');
	$html_a .= sumac_geth2_span('grantreview2g','aga_submitted_head',array('grant_submitted','grant_head'),'C14',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_requested_head','grant_requested');
	$html_a .= sumac_geth2_span('grantreview2g','aga_requested_head',array('grant_requested','grant_head'),'C15',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_granted_head','grant_granted');
	$html_a .= sumac_geth2_span('grantreview2g','aga_granted_head',array('grant_granted','grant_head'),'C16',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= sumac_geth2_tdtag('grantreview2g','aga_accepted_head','grant_accepted');
	$html_a .= sumac_geth2_span('grantreview2g','aga_accepted_head',array('grant_accepted','grant_head'),'C17',true);
	$html_a .= '</td>'.PHP_EOL;
	$html_a .= '</tr>'.PHP_EOL;

	$html_p = sumac_geth2_sumac_subtitle('grantreview2g','H4',($pastCount > 0)?'_R2G':'_R2G_empty');
	$html_p .= sumac_geth2_tabletag('grantreview2g','past_applications','select_grant_application');

	$html_p .= sumac_geth2_trtag('grantreview2g','pga_head',($pastCount > 0)?'pga_head':array('pga_head','empty_head'));
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_type_head','grant_link');
	$html_p .= sumac_geth2_span('grantreview2g','pga_type_head',array('grant_type','grant_head'),'C11',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_status_head','grant_status');
	$html_p .= sumac_geth2_span('grantreview2g','pga_status_head',array('grant_status','grant_head'),'C12',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_begun_head','grant_begun');
	$html_p .= sumac_geth2_span('grantreview2g','pga_begun_head',array('grant_begun','grant_head'),'C13',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_submitted_head','grant_submitted');
	$html_p .= sumac_geth2_span('grantreview2g','pga_submitted_head',array('grant_submitted','grant_head'),'C14',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_requested_head','grant_requested');
	$html_p .= sumac_geth2_span('grantreview2g','pga_requested_head',array('grant_requested','grant_head'),'C15',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_granted_head','grant_granted');
	$html_p .= sumac_geth2_span('grantreview2g','pga_granted_head',array('grant_granted','grant_head'),'C16',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_accepted_head','grant_accepted');
	$html_p .= sumac_geth2_span('grantreview2g','pga_accepted_head',array('grant_accepted','grant_head'),'C17',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= sumac_geth2_tdtag('grantreview2g','pga_closed_head','grant_closed');
	$html_p .= sumac_geth2_span('grantreview2g','pga_closed_head',array('grant_closed','grant_head'),'C18',true);
	$html_p .= '</td>'.PHP_EOL;
	$html_p .= '</tr>'.PHP_EOL;

	for ($i = 0; $i < $activeAndCompletedApplicationElements->length; $i++)
	{
		$grantApplication = $activeAndCompletedApplicationElements->item($i);
		$status = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_STATUS);

		if ($status == SUMAC_STATUS_APPLICATION_IN_PROGRESS)
		{
			$html_u .= sumac_grantreview2g_unsub_app($grantApplication,$status);
		}
		else if (($status == SUMAC_STATUS_APPLICATION_UNDER_REVIEW)
				|| ($status == SUMAC_STATUS_APPLICATION_ACCEPTED))
		{
			$html_a .= sumac_grantreview2g_active_app($grantApplication,$status);
		}
		else //any other status assumed to be a past (completed/closed) application
		{
			$html_p .= sumac_grantreview2g_past_app($grantApplication,$status);
		}
	}
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);

	$html_u .= '</table>'.PHP_EOL;
	$html_a .= '</table>'.PHP_EOL;
	$html_p .= '</table>'.PHP_EOL;
	return $html_u.$html_a.$html_p;
}

function sumac_grantreview2g_unsub_app($grantApplication,$status)
{
	$grappid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_ID);
	$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);
	$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
	$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);
	$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);

	$html = sumac_geth2_trtag('grantreview2g','gip_'.$grappid.'_row','gip_grant');
	$html .= sumac_geth2_tdtag('grantreview2g','gip_'.$grappid.'_type','grant_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;cat=unsub&amp;activeform='.$formid;
	$html .= sumac_geth2_link('grantreview2g','gip_'.$grappid.'_type',array('grant_type','grant_field'),$href,$type,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','gip_'.$grappid.'_status','grant_status');
	$html .= sumac_geth2_span('grantreview2g','gip_'.$grappid.'_status',array('grant_status','grant_field'),str_replace('_',' ',$status),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','gip_'.$grappid.'_begun','grant_begun');
	$html .= sumac_geth2_span('grantreview2g','gip_'.$grappid.'_begun',array('grant_begun','grant_field'),$begun,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','gip_'.$grappid.'_requested','grant_requested');
	$html .= sumac_geth2_span('grantreview2g','gip_'.$grappid.'_requested',array('grant_requested','grant_field'),sumac_centsToPrintableDollars($requested),false);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

/*
	$html = sumac_geth2_trtag('grantreview2g','gip_'.$formid,'gip_link').sumac_geth2_tdtag('grantreview2g','gip_'.$formid,'gip_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;activeform='.$formid;
	$html .= sumac_geth2_link('grantreview2g','gip_'.$formid,'gip_link',$href,$type,false);
	if (($requested != null) && ($requested != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2g','gip_'.$formid,array('grant_requested','grant_field'),'C1',true,sumac_centsToPrintableDollars($requested));
	}
	$html .= ', '.sumac_geth2_span('grantreview2g','gip_'.$formid,array('grant_begun','grant_field'),'C2',true,$begun);
	$html .= ' ['.sumac_geth2_span('grantreview2g','gip_'.$formid,array('grant_status','grant_field'),$status,false).']';
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
*/
	return $html;
}

function sumac_grantreview2g_active_app($grantApplication,$status)
{
	$grappid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_ID);
	$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);
	$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
	$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);
	$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);
	$granted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_GRANTED);
	$reportid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REPORT_ID);
	$submitted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_SUBMITTED);
	$accepted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_ACCEPTED);
	$newreport = ($grantApplication->getAttribute(SUMAC_ATTRIBUTE_REPORT_BEGUN) == 'false');

	$html = sumac_geth2_trtag('grantreview2g','aga_'.$grappid.'_row','aga_grant');
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_type','grant_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;cat=active&amp;completedform='.$formid;
	//only show the report form when the application has been through the review stage
	if (($status == SUMAC_STATUS_APPLICATION_ACCEPTED) && ($reportid != null) && ($reportid != ''))
	{
		$href .= '&amp;activeform='.$reportid;
		if ($newreport) $href .= '&amp;newform=t';
	}
	$html .= sumac_geth2_link('grantreview2g','aga_'.$grappid.'_type',array('grant_type','grant_field'),$href,$type,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_status','grant_status');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_status',array('grant_status','grant_field'),str_replace('_',' ',$status),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_begun','grant_begun');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_begun',array('grant_begun','grant_field'),$begun,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_submitted','grant_submitted');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_submitted',array('grant_submitted','grant_field'),$submitted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_requested','grant_requested');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_requested',array('grant_requested','grant_field'),sumac_centsToPrintableDollars($requested),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_granted','grant_granted');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_granted',array('grant_granted','grant_field'),sumac_centsToPrintableDollars($granted),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','aga_'.$grappid.'_accepted','grant_accepted');
	$html .= sumac_geth2_span('grantreview2g','aga_'.$grappid.'_accepted',array('grant_accepted','grant_field'),$accepted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

/*
	$html = sumac_geth2_trtag('grantreview2g','aga_'.$formid,'aga_link').sumac_geth2_tdtag('grantreview2g','aga_'.$formid,'aga_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;completedform='.$formid;
	//only show the report form when the application has been through the review stage
	if (($status == SUMAC_STATUS_APPLICATION_ACCEPTED) && ($reportid != null) && ($reportid != ''))
	{
		$href .= '&amp;activeform='.$reportid;
		if ($newreport) $href .= '&amp;newform=t';
	}
	$html .= sumac_geth2_link('grantreview2g','aga_'.$formid,'aga_link',$href,$type,false);
	if (($requested != null) && ($requested != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_requested','grant_field'),'C1',true,sumac_centsToPrintableDollars($requested));
	}
	if (($granted != null) && ($granted != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_granted','grant_field'),'C6',true,sumac_centsToPrintableDollars($granted));
	}
	$html .= ', '.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_begun','grant_field'),'C2',true,$begun);
	$html .= ', '.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_submitted','grant_field'),'C3',true,$submitted);
	if (($accepted != null) && ($accepted != '') && ($accepted != '0'))
	{
		$html .= ', '.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_accepted','grant_field'),'C4',true,$accepted);
	}
	$html .= ' ['.sumac_geth2_span('grantreview2g','aga_'.$formid,array('grant_status','grant_field'),$status,false).']';
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
*/
	return $html;
}

function sumac_grantreview2g_past_app($grantApplication,$status)
{
	$grappid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_ID);
	$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);
	$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
	$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);
	$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);
	$granted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_GRANTED);
	$reportid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REPORT_ID);
	$submitted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_SUBMITTED);
	$accepted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_ACCEPTED);
	$closed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_CLOSED);

	$html = sumac_geth2_trtag('grantreview2g','pga_'.$grappid.'_row','pga_grant');
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_type','grant_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;cat=past&amp;completedform='.$formid;
	//only show the report form when the application has been through the review stage
	if (($status == SUMAC_STATUS_APPLICATION_ACCEPTED) && ($reportid != null) && ($reportid != ''))
	{
		$href .= '&amp;activeform='.$reportid;
		if ($newreport) $href .= '&amp;newform=t';
	}
	$html .= sumac_geth2_link('grantreview2g','pga_'.$grappid.'_type',array('grant_type','grant_field'),$href,$type,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_status','grant_status');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_status',array('grant_status','grant_field'),str_replace('_',' ',$status),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_begun','grant_begun');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_begun',array('grant_begun','grant_field'),$begun,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_submitted','grant_submitted');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_submitted',array('grant_submitted','grant_field'),$submitted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_requested','grant_requested');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_requested',array('grant_requested','grant_field'),sumac_centsToPrintableDollars($requested),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_granted','grant_granted');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_granted',array('grant_granted','grant_field'),sumac_centsToPrintableDollars($granted),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_accepted','grant_accepted');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_accepted',array('grant_accepted','grant_field'),$accepted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2g','pga_'.$grappid.'_closed','grant_closed');
	$html .= sumac_geth2_span('grantreview2g','pga_'.$grappid.'_closed',array('grant_closed','grant_field'),$closed,false);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;

/*
	$html = sumac_geth2_trtag('grantreview2g','pga_'.$formid,'pga_link').sumac_geth2_tdtag('grantreview2g','pga_'.$formid,'pga_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php';
	if (($reportid != null) && ($reportid != '')) $href .= '&amp;completedform[0]='.$formid.'&amp;completedform[1]='.$reportid;
	else $href .= '&amp;completedform='.$formid;
	$html .= sumac_geth2_link('grantreview2g','pga_'.$formid,'pga_link',$href,$type,false);
	if (($requested != null) && ($requested != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_requested','grant_field'),'C1',true,sumac_centsToPrintableDollars($requested));
	}
	if (($granted != null) && ($granted != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_granted','grant_field'),'C6',true,sumac_centsToPrintableDollars($granted));
	}
	$html .= ', '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_begun','grant_field'),'C2',true,$begun);
	if (($submitted != null) && ($submitted != '') && ($submitted != '0'))
	{
		$html .= ', '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_submitted','grant_field'),'C3',true,$submitted);
	}
	if (($accepted != null) && ($accepted != '') && ($accepted != '0'))
	{
		$html .= ', '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_accepted','grant_field'),'C4',true,$accepted);
	}
	$html .= ', '.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_closed','grant_field'),'C5',true,$closed);
	$html .= ' ['.sumac_geth2_span('grantreview2g','pga_'.$formid,array('grant_status','grant_field'),$status,false).']';
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
*/
	return $html;
}

function sumac_grantreview2g_new_applications_table($grantApplicationFormElements)
{
	$html = sumac_geth2_sumac_subtitle('grantreview2g','H5','_R2G');
	$html .= sumac_geth2_tabletag('grantreview2g','new_applications','select_grant_application');

	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the H5 stringids are not duplicated
	for ($i = 0; $i < $grantApplicationFormElements->length; $i++)
	{
		$applicationForm = $grantApplicationFormElements->item($i);
		$formid = $applicationForm->getAttribute(SUMAC_ATTRIBUTE_ID);
		$forminorgdoc = false;
		$grantFormElements = $applicationForm->getElementsByTagName(SUMAC_ELEMENT_FORMTEMPLATE);
		if ($grantFormElements->length > 0)
		{
			$firstGrantFormElement = $grantFormElements->item(0);
			if ($firstGrantFormElement->getAttribute(SUMAC_ATTRIBUTE_ID) == $formid) $forminorgdoc = true;
		}
		$html .= sumac_geth2_trtag('grantreview2g','nga_'.$formid,'nga_link').sumac_geth2_tdtag('grantreview2g','nga_'.$formid,'grant_link');
		$formname = $applicationForm->getAttribute(SUMAC_ATTRIBUTE_NAME);
		$href = 'sumac_grantreview2_formchosen.php?pkg=grantreview2g&amp;next=sumac_grantreview2_grantee.php&amp;newform=t&amp;activeform='.$formid;
		if ($forminorgdoc) $href .= '&amp;forminorgdoc=t';
		$html .= sumac_geth2_link('grantreview2g','nga_'.$formid,'nga_link',$href,$formname,false);
		$html .= '</td>'.PHP_EOL;
		$html .= sumac_geth2_tdtag('grantreview2g','nga_'.$formid.'_help','help_link');
		$helplink = trim($applicationForm->getAttribute(SUMAC_ATTRIBUTE_HELPURL));
		if ($helplink != '') $html .= sumac_geth2_helplink($applicationForm->getAttribute(SUMAC_ATTRIBUTE_HELPURL));
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);

	$html .= '</table>'.PHP_EOL;
	return $html;
}

function sumac_form2_dealt_with($pkg,$responseStatus,$request,$responseMessage,$next,$retryData)
{
	$rdarray = explode('|',$retryData);
	$rdcount = count($rdarray);
	$grappid = ($rdcount > 1) ? $rdarray[1] : '';
	// release the saved forms and return to the home pagee
	for ($i = 2; $i < $rdcount; $i++) unset($_SESSION[SUMAC_SESSION_FORM][$rdarray[$i]]);
	return sumac_grantreview2_grantee(null,$responseMessage);
}

?>