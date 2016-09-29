<?php
//version567//

include_once 'sumac_xml.php';
include_once 'sumac_geth2.php';

function sumac_grantreview2_reviewer($loadedAccountDocument,$previousResult)
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

	$html = sumac_grantreview2r_HTML($previousResult,$accountDocument);
	echo $html;
	return true;
}

function sumac_grantreview2r_HTML($previousResult,$accountDocument)
{
	return sumac_geth2_head('grantreview2r')
			.sumac_grantreview2r_body($previousResult,$accountDocument);
}

function sumac_grantreview2r_body($previousResult,$accountDocument)
{
	$retryform = null;
	return '<body>'.PHP_EOL
			.sumac_addParsedXmlIfDebugging($accountDocument,'grantreview2r_accountdetails')
			.sumac_geth2_user('top','grantreview2r')
			.sumac_grantreview2r_content($previousResult,$accountDocument)
			.sumac_geth2_body_footer('grantreview2r',true,$retryform,'');
}

function sumac_grantreview2r_content($previousResult,$accountDocument)
{
	//Get the users list of grant-applications to review or already reviewed
	$grantApplicationElements = $accountDocument->getElementsByTagName(SUMAC_ELEMENT_GRANTAPPLICATION);

	$html = '<div id="sumac_content">'.PHP_EOL;
	$html .= sumac_geth2_divtag('grantreview2r','mainpage','mainpage');
	if ($previousResult != '')
	{
		$html .= sumac_geth2_divtag('grantreview2r','previous_result','result').$previousResult.'</div>'.PHP_EOL;
	}
	$html .= sumac_geth2_sumac_title_with_gobacklink_and_line('grantreview2r','H1','L1',true);
	$html .= sumac_grantreview2r_applications_table($grantApplicationElements);
	$html .= '</div>'.PHP_EOL;	//mainpage
	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_grantreview2r_applications_table($grantApplicationElements)
{
	$_SESSION[SUMAC_SESSION_TEXT_REPEATS] = true;	//make sure that the stringids are not duplicated

	$currentCount = $pastCount = 0;
	for ($i = 0; $i < $grantApplicationElements->length; $i++)
	{
		$grantApplication = $grantApplicationElements->item($i);
		$status = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_STATUS);
// an application whose status is 'in_progress' is never eligible for review.
// current applications are those whose status is 'under_review'
// any later status is considered as history whether the review was in fact done or not
		if ($status == SUMAC_STATUS_APPLICATION_IN_PROGRESS) ; //just ignore it - Sumac has made a mistake?
		else if ($status == SUMAC_STATUS_APPLICATION_UNDER_REVIEW) ++$currentCount;
		else ++$pastCount;
	}

	$html_c = sumac_geth2_sumac_subtitle('grantreview2r','H2',($currentCount > 0)?'_R2R':'_R2R_empty');
	$html_c .= sumac_geth2_tabletag('grantreview2r','current_applications','select_grant_application');

	$html_c .= sumac_geth2_trtag('grantreview2r','cga_head',($currentCount > 0)?'cga_head':array('cga_head','empty_head'));
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_type_head','grant_link');
	$html_c .= sumac_geth2_span('grantreview2r','cga_type_head',array('grant_type','grant_head'),'C11',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_status_head','grant_status');
	$html_c .= sumac_geth2_span('grantreview2r','cga_status_head',array('grant_status','grant_head'),'C12',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_begun_head','grant_begun');
	$html_c .= sumac_geth2_span('grantreview2r','cga_begun_head',array('grant_begun','grant_head'),'C13',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_grantee_head','grant_grantee');
	$html_c .= sumac_geth2_span('grantreview2r','cga_grantee_head',array('grant_grantee','grant_head'),'C19',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_submitted_head','grant_submitted');
	$html_c .= sumac_geth2_span('grantreview2r','cga_submitted_head',array('grant_submitted','grant_head'),'C14',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= sumac_geth2_tdtag('grantreview2r','cga_requested_head','grant_requested');
	$html_c .= sumac_geth2_span('grantreview2r','cga_requested_head',array('grant_requested','grant_head'),'C15',true);
	$html_c .= '</td>'.PHP_EOL;
	$html_c .= '</tr>'.PHP_EOL;

	$html_r = sumac_geth2_sumac_subtitle('grantreview2r','H3',($pastCount > 0)?'_R2R':'_R2R_empty');
	$html_r .= sumac_geth2_tabletag('grantreview2r','past_applications','select_grant_application');

	$html_r .= sumac_geth2_trtag('grantreview2r','rga_head',($pastCount > 0)?'rga_head':array('rga_head','empty_head'));
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_type_head','grant_link');
	$html_r .= sumac_geth2_span('grantreview2r','rga_type_head',array('grant_type','grant_head'),'C11',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_status_head','grant_status');
	$html_r .= sumac_geth2_span('grantreview2r','rga_status_head',array('grant_status','grant_head'),'C12',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_begun_head','grant_begun');
	$html_r .= sumac_geth2_span('grantreview2r','rga_begun_head',array('grant_begun','grant_head'),'C13',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_grantee_head','grant_grantee');
	$html_r .= sumac_geth2_span('grantreview2r','rga_grantee_head',array('grant_grantee','grant_head'),'C19',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_submitted_head','grant_submitted');
	$html_r .= sumac_geth2_span('grantreview2r','rga_submitted_head',array('grant_submitted','grant_head'),'C14',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_requested_head','grant_requested');
	$html_r .= sumac_geth2_span('grantreview2r','rga_requested_head',array('grant_requested','grant_head'),'C15',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_reviewed_head','grant_reviewed');
	$html_r .= sumac_geth2_span('grantreview2r','rga_reviewed_head',array('grant_reviewed','grant_head'),'C20',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_rank_head','grant_rank');
	$html_r .= sumac_geth2_span('grantreview2r','rga_rank_head',array('grant_rank','grant_head'),'C21',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_granted_head','grant_granted');
	$html_r .= sumac_geth2_span('grantreview2r','rga_granted_head',array('grant_granted','grant_head'),'C16',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_accepted_head','grant_accepted');
	$html_r .= sumac_geth2_span('grantreview2r','rga_accepted_head',array('grant_accepted','grant_head'),'C17',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= sumac_geth2_tdtag('grantreview2r','rga_closed_head','grant_closed');
	$html_r .= sumac_geth2_span('grantreview2r','rga_closed_head',array('grant_closed','grant_head'),'C18',true);
	$html_r .= '</td>'.PHP_EOL;
	$html_r .= '</tr>'.PHP_EOL;

	for ($i = 0; $i < $grantApplicationElements->length; $i++)
	{
		$grantApplication = $grantApplicationElements->item($i);
		$status = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_STATUS);
// an application whose status is 'in_progress' is never eligible for review.
// current applications are those whose status is 'under_review'
// any later status is considered as history whether the review was in fact done or not
		if ($status == SUMAC_STATUS_APPLICATION_IN_PROGRESS) ; //just ignore it - Sumac has made a mistake?
		else if ($status == SUMAC_STATUS_APPLICATION_UNDER_REVIEW)
		{
			$html_c .= sumac_grantreview2r_current_application($grantApplication,$status);
		}
		else //any other status is history
		{
			$accepted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_ACCEPTED);
			$closed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_CLOSED);
			$html_r .= sumac_grantreview2r_reviewed_application($grantApplication,$status);
		}
	}
	unset($_SESSION[SUMAC_SESSION_TEXT_REPEATS]);
	unset($_SESSION[SUMAC_SESSION_REPEATABLE_STR]);

	$html_c .= '</table>'.PHP_EOL;
	$html_r .= '</table>'.PHP_EOL;
	return $html_c.$html_r;
}

function sumac_grantreview2r_current_application($grantApplication,$status)
{
	$grappid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_ID);
	$grantee = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_GRANTEE);
	$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);
	$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
	$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);
	$submitted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_SUBMITTED);
	$reviewid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REVIEW_ID);
	$rank = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REVIEW_RANK);
	$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);

	$html = sumac_geth2_trtag('grantreview2r','cga_'.$grappid.'_row','cga_');
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_type','grant_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2r&amp;next=sumac_grantreview2_reviewer.php&amp;cat=current&amp;completedform='.$formid;
	if (($reviewid != null) && ($reviewid != '')) $href .= '&amp;activeform='.$reviewid;
	$html .= sumac_geth2_link('grantreview2r','cga_'.$grappid.'_type',array('grant_type','grant_field'),$href,$type,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_status','grant_status');
	$html .= sumac_geth2_span('grantreview2r','cga_'.$grappid.'_status',array('grant_status','grant_field'),str_replace('_',' ',$status),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_begun','grant_begun');
	$html .= sumac_geth2_span('grantreview2r','cga_'.$grappid.'_begun',array('grant_begun','grant_field'),$begun,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_grantee','grant_grantee');
	$html .= sumac_geth2_span('grantreview2r','cga_'.$grappid.'_grantee',array('grant_grantee','grant_field'),$grantee,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_submitted','grant_submitted');
	$html .= sumac_geth2_span('grantreview2r','cga_'.$grappid.'_submitted',array('grant_submitted','grant_field'),$submitted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','cga_'.$grappid.'_requested','grant_requested');
	$html .= sumac_geth2_span('grantreview2r','cga_'.$grappid.'_requested',array('grant_requested','grant_field'),sumac_centsToPrintableDollars($requested),false);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;
/*
	$html = sumac_geth2_trtag('grantreview2r','cga_'.$formid,'cga_link').sumac_geth2_tdtag('grantreview2r','cga_'.$formid,'cga_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2r&amp;next=sumac_grantreview2_reviewer.php&amp;completedform='.$formid;
	if (($reviewid != null) && ($reviewid != '')) $href .= '&amp;activeform='.$reviewid;
	$html .= sumac_geth2_link('grantreview2r','cga_'.$formid,'cga_link',$href,$type,false);
	$html .= ', '.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_grantee','grant_field'),'C7',true,$grantee);
	if (($requested != null) && ($requested != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_requested','grant_field'),'C1',true,sumac_centsToPrintableDollars($requested));
	}
	$html .= ', '.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_begun','grant_field'),'C2',true,$begun);
	$html .= ', '.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_submitted','grant_field'),'C3',true,$submitted);
	if (($rank != null) && ($rank != ''))
	{
		$html .= ', '.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_rank','grant_field'),'C8',true,$rank);
	}
	$html .= ' ['.sumac_geth2_span('grantreview2r','cga_'.$formid,array('grant_status','grant_field'),$status,false).']';
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
*/
	return $html;
}

function sumac_grantreview2r_reviewed_application($grantApplication,$status)
{
	$grappid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_ID);
	$grantee = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_GRANTEE);
	$type = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_TYPE);
	$formid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_FORM_ID);
	$begun = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_BEGUN);
	$submitted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_SUBMITTED);
	$reviewid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REVIEW_ID);
	$reviewed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_REVIEWED);
	$rank = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REVIEW_RANK);
	$requested = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_REQUESTED);
	$granted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_AMOUNT_GRANTED);
	$reportid = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_REPORT_ID);
	$accepted = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_ACCEPTED);
	$closed = $grantApplication->getAttribute(SUMAC_ATTRIBUTE_DATE_CLOSED);

	$html = sumac_geth2_trtag('grantreview2r','rga_'.$grappid.'_row','rga_grant');
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_type','grant_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2r&amp;next=sumac_grantreview2_reviewer.php&amp;cat=reviewed';
	$href .= '&amp;completedform[0]='.$formid.'&amp;completedform[1]='.$reviewid;
	if (($reportid != null) && ($reportid != '')) $href .= '&amp;completedform[2]='.$reportid;
	$html .= sumac_geth2_link('grantreview2r','rga_'.$grappid.'_type',array('grant_type','grant_field'),$href,$type,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_status','grant_status');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_status',array('grant_status','grant_field'),str_replace('_',' ',$status),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_begun','grant_begun');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_begun',array('grant_begun','grant_field'),$begun,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_begun','grant_grantee');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_grantee',array('grant_grantee','grant_field'),$grantee,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_submitted','grant_submitted');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_submitted',array('grant_submitted','grant_field'),$submitted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_requested','grant_requested');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_requested',array('grant_requested','grant_field'),sumac_centsToPrintableDollars($requested),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_reviewed','grant_reviewed');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_reviewed',array('grant_reviewed','grant_field'),$reviewed,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_rank','grant_rank');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_rank',array('grant_rank','grant_field'),$rank,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_granted','grant_granted');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_granted',array('grant_granted','grant_field'),sumac_centsToPrintableDollars($granted),false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_accepted','grant_accepted');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_accepted',array('grant_accepted','grant_field'),$accepted,false);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('grantreview2r','rga_'.$grappid.'_closed','grant_closed');
	$html .= sumac_geth2_span('grantreview2r','rga_'.$grappid.'_closed',array('grant_closed','grant_field'),$closed,false);
	$html .= '</td>'.PHP_EOL;
	$html .= '</tr>'.PHP_EOL;
/*
	$html = sumac_geth2_trtag('grantreview2r','rga_'.$formid,'rga_link').sumac_geth2_tdtag('grantreview2r','rga_'.$formid,'rga_link');
	$href = 'sumac_grantreview2_formchosen.php?grappid='.$grappid.'&amp;pkg=grantreview2r&amp;next=sumac_grantreview2_reviewer.php';
	$href .= '&amp;completedform[0]='.$formid.'&amp;completedform[1]='.$reviewid;
	if (($reportid != null) && ($reportid != '')) $href .= '&amp;completedform[2]='.$reportid;
	$html .= sumac_geth2_link('grantreview2r','rga_'.$formid,'rga_link',$href,$type,false);
	$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_grantee','grant_field'),'C7',true,$grantee);
	if (($requested != null) && ($requested != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_requested','grant_field'),'C1',true,sumac_centsToPrintableDollars($requested));
	}
	if (($granted != null) && ($granted != ''))
	{
		$html .= ' '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_granted','grant_field'),'C6',true,sumac_centsToPrintableDollars($granted));
	}
	$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_begun','grant_field'),'C2',true,$begun);
	$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_submitted','grant_field'),'C3',true,$submitted);
	if (($rank != null) && ($rank != ''))
	{
		$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_rank','grant_field'),'C8',true,$rank);
	}
	$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_reviewed','grant_field'),'C9',true,$reviewed);
	if (($accepted != null) && ($accepted != '') && ($accepted != '0'))
	{
		$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_accepted','grant_field'),'C4',true,$accepted);
	}
	if (($closed != null) && ($closed != '') && ($closed != '0'))
	{
		$html .= ', '.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_closed','grant_field'),'C5',true,$closed);
	}
	$html .= ' ['.sumac_geth2_span('grantreview2r','rga_'.$formid,array('grant_status','grant_field'),$status,false).']';
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
*/
	return $html;
}

function sumac_form2_dealt_with($pkg,$responseStatus,$request,$responseMessage,$next,$retryData)
{
	$rdarray = explode('|',$retryData);
	$rdcount = count($rdarray);
	$grappid = ($rdcount > 1) ? $rdarray[1] : '';
	// release the saved forms and return to the home pagee
	for ($i = 2; $i < $rdcount; $i++) unset($_SESSION[SUMAC_SESSION_FORM][$rdarray[$i]]);
	return sumac_grantreview2_reviewer(null,$responseMessage);
}

?>