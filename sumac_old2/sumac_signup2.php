<?php
//version551//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';
include_once 'sumac_login2.php';
include_once 'sumac_xml.php';

function sumac_signup2($previousRequest,$responseMessage,$formhandler)
{
	$organisationDocument = sumac_reloadOrganisationDocument();
	if ($organisationDocument == false) return false;

	$html = sumac_signup2_HTML($organisationDocument,$formhandler,$previousRequest,$responseMessage);
	echo $html;
	return true;
}

function sumac_signup2_HTML($organisationDocument,$formhandler,$previousRequest,$responseMessage)
{
	return '<!DOCTYPE html>'.PHP_EOL
			.sumac_geth2_head('signup2')
			.sumac_signup2_body($organisationDocument,$formhandler,$previousRequest,$responseMessage);
}

function sumac_signup2_body($organisationDocument,$formhandler,$previousRequest,$responseMessage)
{
	$retryform = ($previousRequest != '') ? 'all' : null;

	return '<body>'.PHP_EOL
			.sumac_geth2_user('top','signup2')
			.sumac_signup2_content($organisationDocument,$formhandler,$previousRequest,$responseMessage)
			.sumac_geth2_user('bottom','signup2')
			.sumac_geth2_body_footer('signup2',true,$retryform,'')
			.'</body>'.PHP_EOL;
}

function sumac_signup2_content($organisationDocument,$formhandler,$previousRequest,$responseMessage)
{
	$html = '<div id="sumac_content">'.PHP_EOL;

	$html .= sumac_geth2_divtag('signup2','mainpage','mainpage');
	$html .= sumac_signup2_instructions();
	$html .= sumac_signup2_HTMLform($organisationDocument,$formhandler,$previousRequest,$responseMessage);
	$html .= '</div>'.PHP_EOL;	//mainpage

	$html .= sumac_geth2_sumac_software_link('signup2',SUMAC_INFO_FOOTER_SUMAC_CONNECT2_LINK,SUMAC_INFO_FOOTER_SUMAC_CONNECT2_TEXT);

	$html .= '</div>'.PHP_EOL;	//content
	return $html;
}

function sumac_signup2_instructions()
{
	$html = sumac_geth2_divtag('signup2','instructions','instructions');
	$html .= sumac_geth2_spantext('signup2','I1');
	$html .= '</div>'.PHP_EOL;
	return $html;
}

function sumac_signup2_HTMLform($organisationDocument,$formhandler,$previousRequest,$responseMessage)
{
	$html = sumac_geth2_formtag('signup2','all','form',$formhandler);
	$html .= sumac_login2('signup2','O',true,$organisationDocument,null,$previousRequest,$responseMessage,'L1');
	$html .= sumac_signup2_form_status_table($previousRequest,$responseMessage);
	$html .= sumac_signup2_buttons($previousRequest,$responseMessage);

//now add the OK and Cancel buttons - and lets assume that the user doesnt want to edit in a new window/tab
//	$cancelOnclick = ' onclick="document.getElementById(\'' . SUMAC_ID_FORM_SUMAC_FORM . '\').action=\'sumac_courses_redirect.php\';"';

	$html .= '</form>'.PHP_EOL;
	return $html;
}

function sumac_signup2_buttons($previousRequest,$responseMessage)
{
	$html = sumac_geth2_divtag('signup2','buttons','untitled');

	$html .= sumac_geth2_tabletag('signup2','buttons','buttons');
	$html .= sumac_geth2_trtag('signup2','buttons','buttons');

	$html .= sumac_geth2_tdtag('signup2','okbutton','okbutton titlebutton');
	$attrs = 'type="submit" value="" onclick="if (sumac_check_for_missing_fields(\'O\',\''.sumac_geth2_textid_for_span('signup2','E1').'\')) return false;"';
	$html .= sumac_geth2_input(sumac_geth2_textid_for_entry('signup2','input','L2'),'submit','signup',$attrs);
	$html .= '</td>'.PHP_EOL;
	$html .= sumac_geth2_tdtag('signup2','cancelbutton','cancelbutton titlebutton');
	//$attrs = 'type="submit" value=""';
	//$html .= sumac_geth2_input(sumac_geth2_textid_for_entry('signup2','input','L3'),'submit','cancelsignup',$attrs);
	$html .= sumac_geth2_sumac_return_link('signup2','L3').PHP_EOL;
	$html .= '</td>'.PHP_EOL;

	$html .= '</tr>'.PHP_EOL;
	$html .= '</table>'.PHP_EOL;

	$html .= '</div>'.PHP_EOL;

	return $html;
}

function sumac_signup2_form_status_table($previousRequest,$responseMessage)
{
	$html = sumac_geth2_tabletag('signup2','form_status','form_status');

	if ($previousRequest == 'submitform')
	{
		$html .= sumac_geth2_trtag('signup2','form_status','form_status').sumac_geth2_tdtag('signup2','form_status','status',1,' tabindex="1"');
		$html .= $responseMessage;
		$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;
	}
	$html .= sumac_geth2_trtag('signup2','form_entry_status','form_status').sumac_geth2_tdtag('signup2','form_entry_status','nodisplay');
	$html .= sumac_geth2_spantext('signup2','E1');
	$html .= '</td>'.PHP_EOL.'</tr>'.PHP_EOL;

	$html .= '</table>'.PHP_EOL;
	return $html;
}

?>