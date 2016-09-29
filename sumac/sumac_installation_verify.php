<?php
//version568//

//this is 'sumac_installation_verify.php', a stand-alone utility

/**********************************************************************************************
	Every PHP file should have a comment with the version that the file belongs to in the form:
	//versionNNNN//
	where NNNN is a number, e.g. 404 for version 4.0.4
	The number should match the version number of the file in the arrays folowing these comments.

	Every DTD or HTM file should have a similar comment in the form:
	<!--versionNNNN-->
	Again, the number NNNN should match the number associated with the file in the arrays folowing these comments.

	JPG,ICO files contain only binary image data and therefore have no version checking.

	Folders also have no version checking.

	No other file types are used at present.
	***** the version number comments must be contained in the first 100 bytes of the file *****
*************************************************************************************************/

	$mainfiles = array(
						'certs' => '0',
						'css' => '0',
						'js' => '0',
						'user' => '0',
						'accountdetails.dtd' => '568',
						'attachment.dtd' => '5642',
						'bottomnavbg.jpg' => '0',
						'bottomnavbgwus2.jpg' => '0',
						'contactdetails.dtd' => '404',
						'course_catalog.dtd' => '450',
						'directory.dtd' => '5002',
						'directory_entries.dtd' => '552',
						'extras.dtd' => '404',
						'formtemplate.dtd' => '5645',
						'narrownavbg.jpg' => '0',
						'newsletter.php' => '5681',
						'organisation.dtd' => '568',
						'phpinfo.php' => '0',
						'raocu_start.php' => '563',
						'raods_start.php' => '563',
						'raomr_start.php' => '563',
						'raots_start.php' => '5101',
						'response.dtd' => '568',
						'seatsales.dtd' => '404',
						'smalldelete.ico' => '0',
						'sumac.htm' => '568',
						'sumac_can_province_dropdown.htm' => '520',
						'sumac_constants.php' => '5681',
						'sumac_contact_updated.php' => '568',
						'sumac_course_chosen.php' => '5631',
						'sumac_course_unchosen.php' => '565',
						'sumac_courses_redirect.php' => '563',
						'sumac_directory_chosen.php' => '5122',
						'sumac_donation2.php' => '568',
						'sumac_donation2_made.php' => '568',
						'sumac_donation_made.php' => '568',
						'sumac_event_chosen.php' => '567',
						'sumac_form2.php'  => '5647',
						'sumac_form2_attmt_delete.php'  => '567',
						'sumac_form2_attmt_dl_iframe.htm'  => '5647',
						'sumac_form2_attmt_dldone.php'  => '5647',
						'sumac_form2_attmt_download.php'  => '567',
						'sumac_form2_attmt_iframe.htm'  => '5647',
						'sumac_form2_attmt_upload.php'  => '565',
						'sumac_form2_submitted.php'  => '567',
						'sumac_form_chosen.php' => '5644',
						'sumac_form_updated.php' => '563',
						'sumac_form_update_abandoned.php' => '510',
						'sumac_geth2.php' => '568',
						'sumac_grantreview2_formchosen.php' => '5645',
						'sumac_grantreview2_forms.php' => '567',
						'sumac_grantreview2_grantee.php' => '5681',
						'sumac_grantreview2_reviewer.php' => '567',
						'sumac_http.php' => '564',
						'sumac_identify_user.php' => '568',
						'sumac_installation_verify.php' => '568',
						'sumac_leave.php' => '551',
						'sumac_login2.php' => '568',
						'sumac_make_donation.php' => '567',
						'sumac_manage_forms.php' => '567',
						'sumac_membership2.php' => '567',
						'sumac_membership2_submit.php' => '567',
						'sumac_membership_renewed.php' => '568',
						'sumac_parameter_settings.guide' => '568',
						'sumac_payment2.php' => '568',
						'sumac_payment_made.php' => '568',
						'sumac_pay_for_course.php' => '567',
						'sumac_pay_for_tickets.php' => '567',
						'sumac_pay_without_purchase.php' => '567',
						'sumac_personal_history.php' => '567',
						'sumac_pick_tickets.php' => '567',
						'sumac_pick_tickets_layout_stage.php' => '530',
						'sumac_pick_tickets_seating_plan.php' => '450',
						'sumac_provinces_and_states_dropdown.htm' => '520',
						'sumac_redirect.php' => '567',
						'sumac_renew_membership.php' => '567',
						'sumac_select_course.php' => '567',
						'sumac_select_directory.php' => '567',
						'sumac_select_event.php' => '567',
						'sumac_session_utilities.php' => '5125',
						'sumac_set_parameters.php' => '568',
						'sumac_show_directory_entries.php' => '567',
						'sumac_show_session_status.php' => '5647',
						'sumac_singleform2.php' => '567',
						'sumac_start.php' => '551',
						'sumac_start_new_session.php' => '567',
						'sumac_states_and_provinces_dropdown.htm' => '520',
						'sumac_strings.settings' => '568',
						'sumac_test_response.php' => '568',
						'sumac_ticketing_redirect.php' => '567',
						'sumac_ticketing_utilities.php' => '567',
						'sumac_ticketing2.php' => '567',
						'sumac_ticketing2_ordered.php' => '567',
						'sumac_ticketing2_pay.php' => '567',
						'sumac_ticketing2_seats.php' => '567',
						'sumac_update_contact_details.php' => '568',
						'sumac_us_state_dropdown.htm' => '520',
						'sumac_utilities.php' => '568',
						'sumac_xml.php' => '568',
						'theatre.dtd' => '540',
						'topnavbg.jpg' => '0',
						'topnavbg70grey.jpg' => '0',
						'topnavbgwus2.jpg' => '0',

						'css/sumac.css' => '568',
						'css/sumac_contact.css' => '563',
						'css/sumac_courses.css' => '563',
						'css/sumac_donation.css' => '563',
						'css/sumac_donation2.css' => '566',
						'css/sumac_grantreview2g.css' => '567',
						'css/sumac_grantreview2r.css' => '567',
						'css/sumac_membership.css' => '563',
						'css/sumac_membership2.css' => '5681',
						'css/sumac_protected.css' => '520',
						'css/sumac_signup2.css' => '530',
						'css/sumac_singleform2.css' => '5647',
						'css/sumac_ticketing.css' => '563',
						'css/sumac_ticketing2.css' => '567',
						'css/sumac_signup2.css' => '550',

						'js/sumac.js' => '567',
						'js/sumac_contact.js' => '563',
						'js/sumac_contact_text.js' => '568',
						'js/sumac_courses.js' => '563',
						'js/sumac_courses_text.js' => '568',
						'js/sumac_donation.js' => '563',
						'js/sumac_donation_text.js' => '568',
						'js/sumac_donation2.js' => '567',
						'js/sumac_donation2_text.js' => '568',
						'js/sumac_grantreview2g.js' => '564',
						'js/sumac_grantreview2g_text.js' => '568',
						'js/sumac_grantreview2r.js' => '564',
						'js/sumac_grantreview2r_text.js' => '567',
						'js/sumac_membership.js' => '563',
						'js/sumac_membership_text.js' => '568',
						'js/sumac_membership2.js' => '5661',
						'js/sumac_membership2_text.js' => '568',
						'js/sumac_preloaded.js' => '5681',
						'js/sumac_shared_text.js' => '568',
						'js/sumac_signup2.js' => '550',
						'js/sumac_signup2_text.js' => '568',
						'js/sumac_singleform2.js' => '530',
						'js/sumac_singleform2_text.js' => '568',
						'js/sumac_ticketing.js' => '563',
						'js/sumac_ticketing_text.js' => '568',
						'js/sumac_ticketing2.js' => '567',
						'js/sumac_ticketing2_text.js' => '568',
						);


$extensionsRequired = array('curl','openssl');
$phperrors = sumac_statusReport_checkPHPConfiguration('5','2',$extensionsRequired);

$maindirlist = array_merge(
						scandir('.'),
						sumac_statusReport_getSubFolderFileList('css'),
						sumac_statusReport_getSubFolderFileList('js')
						);

$missing = sumac_statusReport_checkMasterFileList($maindirlist,$mainfiles);
$incorrect = sumac_statusReport_checkCodeFileVersions($maindirlist,$mainfiles);
$extra = sumac_statusReport_checkForExtraFiles($maindirlist,$mainfiles);
$missingFromSubfolders = sumac_statusReport_checkForMissingSubfolderFiles($maindirlist);
$extraInSubfolders = sumac_statusReport_checkForExtraSubfolderFiles($maindirlist);


//the installation is judged to have been successful if
//	no files are missing and
//	no code files have incorrect version numbers
//otherwise it is an invalid installation

//if there are extra files or if there are missing or extra files in a subfolder they are reported

//if there are problems with the PHP configuration they are reported

$self = $_SERVER['PHP_SELF'];
$folder = substr($self,0,strrpos($self,'/'));
$version = sumac_statusReport_getRequiredVersionNumber('sumac_constants.php',$mainfiles);
$codeVersion = substr($version,0,1);
for ($i = 1; $i < strlen($version); $i++) $codeVersion .= '.' . substr($version,$i,1);
$userhtml = '';
if (isset($_GET['userhtml'])) $userhtml = $_GET['userhtml'];
else if (isset($_GET['USERHTML'])) $userhtml = $_GET['USERHTML'];
else if (isset($_GET['userHTML'])) $userhtml = $_GET['userHTML'];
$goodInstallation = (count($missing) == 0) && (count($incorrect) == 0);
$status = ($goodInstallation
		? 'This is a valid installation of version '
			. $codeVersion . ' of the Sumac online components in folder '
			. $folder . ' on host ' . $_SERVER['HTTP_HOST'] . '.'
		: 'This  installation of version '
			. $codeVersion . ' of the Sumac online components in folder '
			. $folder . ' on host ' . $_SERVER['HTTP_HOST'] . ' is invalid.'
		);
$instructions = (!$goodInstallation ? 'PLEASE REINSTALL IT <br />OR CONTACT SUMAC TO OBTAIN THE NECESSARY CORRECTIONS.' : '');

echo sumac_statusReport_getHeaderHTML($userhtml);

echo sumac_statusReport_getStatusHTML($goodInstallation,$status,$instructions);

if (count($missing) > 0)
	echo sumac_statusReport_getDetailHTML('Files missing from the main Sumac online folders and subfolders',$missing,true);
if (count($incorrect) > 0)
	echo sumac_statusReport_getDetailHTML('Incorrect versions of Sumac online code files',$incorrect,true);
if (count($extra) > 0)
	echo sumac_statusReport_getDetailHTML('Extra files in the main Sumac online folders and subfolders',$extra);
if (count($missingFromSubfolders) > 0)
	echo sumac_statusReport_getDetailHTML('Files missing from the Sumac online user subfolders',$missingFromSubfolders);
if (count($extraInSubfolders) > 0)
	echo sumac_statusReport_getDetailHTML('Extra files in the Sumac online user subfolders',$extraInSubfolders);

if (count($phperrors) > 0)
{
	$status = 'The PHP configuration on this host is not suitable for running the Sumac online system';
	$instructions = 'PLEASE CONTACT YOUR WEBSITE MANAGER.';
	echo sumac_statusReport_getStatusHTML(false,$status,$instructions);
	echo sumac_statusReport_getDetailHTML('PHP configuration problems',$phperrors,true);
}

echo sumac_statusReport_v568_omitfieldsCheck();

echo sumac_statusReport_getFooterHTML($userhtml,$codeVersion);

exit();


function sumac_statusReport_getHeaderHTML($userhtml)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";
	$html .= '<meta http-equiv="Content-Script-Type" content="text/javascript" />' . "\n";
	$html .= '<title>Sumac installation status</title>' . "\n";
	$html .= '<style type="text/css">';
	$html .= sumac_statusReport_getHTMLStyleElements();
	if ($userhtml != '')
	{
		$html .= sumac_statusReport_getFile('user/top.css');
		$html .= sumac_statusReport_getFile('user/bottom.css');
		$html .= sumac_statusReport_getFile('user/over_sumac.css');
		switch ($userhtml)
		{
			case 'donation2':
				 $html .= sumac_statusReport_getFile('user/over_sumac_donation2.css');
				 break;
			case 'membership2':
				 $html .= sumac_statusReport_getFile('user/over_sumac_membership2.css');
				 break;
			case 'ticketing2':
				 $html .= sumac_statusReport_getFile('user/over_sumac_ticketing2.css');
				 break;
			case 'grantreview2g':
				 $html .= sumac_statusReport_getFile('user/over_sumac_grantreview2g.css');
				 break;
			case 'grantreview2r':
				 $html .= sumac_statusReport_getFile('user/over_sumac_grantreview2r.css');
				 break;
			case 'singleform2':
				 $html .= sumac_statusReport_getFile('user/over_sumac_singleform2.css');
				 break;
			case 'signup2':
				 $html .= sumac_statusReport_getFile('user/over_sumac_signup2.css');
				 break;
			default:
				//do nothing
		}
	}
	$html .= '</style>' . "\n";
//	$html .= '<script  type="text/javascript">' . "\n";
//	$html .= '</script>' . "\n";
	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";
	if ($userhtml != '')
	{
		switch ($userhtml)
		{
			case 'donation2':
				 $html .= sumac_statusReport_getFile('user/donation2_top.htm');
				 break;
			case 'membership2':
				 $html .= sumac_statusReport_getFile('user/membership2_top.htm');
				 break;
			case 'ticketing2':
				 $html .= sumac_statusReport_getFile('user/ticketing2_top.htm');
				 break;
			case 'grantreview2g':
				 $html .= sumac_statusReport_getFile('user/grantreview2g_top.htm');
				 break;
			case 'grantreview2r':
				 $html .= sumac_statusReport_getFile('user/grantreview2r_top.htm');
				 break;
			case 'singleform2':
				 $html .= sumac_statusReport_getFile('user/singleform2_top.htm');
				 break;
			case 'signup2':
				 $html .= sumac_statusReport_getFile('user/signup2_top.htm');
				 break;
			default:
				 $html .= sumac_statusReport_getFile('user/top.htm');
				 break;
		}
	}

	$html .= '<div id="sumac_divstatusreport">' . "\n";
	return $html;
}

function sumac_statusReport_getStatusHTML($goodInstallation,$status,$instructions)
{
	if ($goodInstallation)
	{
		$html = '<p class="sumac_sr_status sumac_sr_success"><span id="sumac_sr_OK">OK</span>'
			. $status . '</p>';
	}
	else
	{
		$html = '<p class="sumac_sr_status sumac_sr_failure"><span id="sumac_sr_X">&nbsp;X&nbsp;</span>' . $status . '</p>'
			. '<p class="sumac_sr_status sumac_sr_alert">' . $instructions  . '</p>';
	}
	return $html;
}

function sumac_statusReport_getDetailHTML($heading,$messages,$fatal=false)
{
	$detailClass= 'sumac_sr_detail' . ($fatal ? ' sumac_sr_fatal' : '');
	$headingClass= 'sumac_sr_heading' . ($fatal ? ' sumac_sr_fatal' : '');
	$html = '<br /><table class="' . $detailClass . '"><tr><td class="' . $headingClass . '" colspan="4">'
			. $heading . '</td></tr>';
	for ($i = 0; $i < count($messages); $i++)
	{
		if (is_array($messages[$i]))
		{
			$html .= '<tr>';
			$itemClass= 'sumac_sr_item' . ($fatal ? ' sumac_sr_fatal' : '');
			for ($j = 0; $j < count($messages[$i]); $j++)
			{
				$html .= '<td class="' . $itemClass . '">' . $messages[$i][$j] . '</td>';
			}
			$html .= '</tr>';
		}
		else
		{
			$html .= '<tr><td class="' . $detailClass . '" colspan="4">' . $messages[$i] . '</td></tr>';
		}
	}
	$html .= '</table>';
	return $html;
}

function sumac_statusReport_getFooterHTML($userhtml,$codeVersion)
{
	$html = '';
	if ($userhtml == '')
	{
		$html .= '<br />' . '[You can display your user top and bottom HTML by setting the \'userhtml\' parameter to the relevant package.'
				. '&nbsp;E.G. sumac_installation_verify.php?userhtml=donation2';   //. '<br />';
	}
	$html .= '</div>' . "\n";
	$html .= '<table width="100%"><tr><td align="center" style="font-size:1.0em">' .
			'<a style="color:gray" href="http://sumac.com">' .
			'Powered by Sumac Software ' . $codeVersion .
			'</a></td></tr></table>';
	if ($userhtml != '')
	{
		switch ($userhtml)
		{
			case 'donation2':
				 $html .= sumac_statusReport_getFile('user/donation2_bottom.htm');
				 break;
			case 'membership2':
				 $html .= sumac_statusReport_getFile('user/membership2_bottom.htm');
				 break;
			case 'ticketing2':
				 $html .= sumac_statusReport_getFile('user/ticketing2_bottom.htm');
				 break;
			case 'grantreview2g':
				 $html .= sumac_statusReport_getFile('user/grantreview2g_bottom.htm');
				 break;
			case 'grantreview2r':
				 $html .= sumac_statusReport_getFile('user/grantreview2r_bottom.htm');
				 break;
			case 'singleform2':
				 $html .= sumac_statusReport_getFile('user/singleform2_bottom.htm');
				 break;
			case 'signup2':
				 $html .= sumac_statusReport_getFile('user/signup2_bottom.htm');
				 break;
			default:
				 $html .= sumac_statusReport_getFile('user/bottom.htm');
				 break;
		}
	}
	$html .= '</body></html>' . "\n";

	return $html;
}

function sumac_statusReport_getHTMLStyleElements()
{
	return <<<EOSRCSS

#sumac_divstatusreport
{
	font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
}
.sumac_sr_status
{
	text-align: center;
	margin-left: auto;
	margin-right: auto;
	color:white;
	font-size:133%;
	width:70%;
}
.sumac_sr_success
{
	background-color:green;
}
.sumac_sr_failure
{
	background-color:red;
}
.sumac_sr_alert
{
	background-color:red;
	color:white;
	font-weight:bold;
}
.sumac_sr_detail
{
	text-align: left;
	margin-left: auto;
	margin-right: auto;
	background-color:white;
	color:black;
	font-size:100%;
	width:70%;
	border:2px solid orange;
	border-collapse:collapse;
}
.sumac_sr_heading
{
	text-align: center;
	font-weight:bold;
	border:5px solid orange;
}
.sumac_sr_item
{
	border:2px solid orange;
}
.sumac_sr_fatal
{
	border:5px solid red;
}
#sumac_sr_OK,
#sumac_sr_X
{
	float:left;
	font-size:150%;
	border:8px solid black;
	padding:3px;
	margin:3px 25px;
	border-radius: 33px;
}
.sumac_sr_warntitle
{
	text-align: center;
	margin-left: auto;
	margin-right: auto;
	color:white;
	background-color:orange;
	font-size:133%;
	width:70%;
}
.sumac_sr_warnbody
{
	text-align: center;
	margin-left: auto;
	margin-right: auto;
	color:black;
	background-color:orange;
	width:70%;
}
EOSRCSS;
}

function sumac_statusReport_checkPHPConfiguration($majorVersion,$minorVersion,$extensionsRequired)
{
	$messages = array();
	$version = explode('.', PHP_VERSION);
	if ($version[0] < $majorVersion)
	{
		$messages[] = 'This is PHP version ' . PHP_VERSION . '. Version ' . $majorVersion . '.' . $minorVersion . ' or higher is required.';
	}
	else if (($version[0] == $majorVersion) && ($version[1] < $minorVersion))
	{
		$messages[] = 'This is PHP version ' . PHP_VERSION . '. You may require Version ' . $majorVersion . '.' . $minorVersion . ' or higher.';
	}

	$phpextensions = get_loaded_extensions();
	for ($i = 0; $i < count($extensionsRequired); $i++)
	{
		if (in_array($extensionsRequired[$i],$phpextensions) == false)
			$messages[] = 'PHP extension "' . $extensionsRequired[$i] . '" must be enabled for the Sumac online code to work.';
	}
	return $messages;
}

function sumac_statusReport_checkCodeFileVersions($dirlist,$filelist)
//returns array of names of files whose version numbers are not the required ones
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
		$requiredVersion = sumac_statusReport_getRequiredVersionNumber($f,$filelist);
		if (($requiredVersion != null) && ($requiredVersion != '0'))
		{
			$extension = substr($f,strrpos($f,'.')+1);
			$version = '-1';
			if ((strtolower($extension) == 'php') || (strtolower($extension) == 'js') || (strtolower($extension) == 'guide'))
				$version = sumac_statusReport_getVersionNumberFromFile($f,'//');
			else if ((strtolower($extension) == 'dtd') || (strtolower($extension) == 'htm'))
				$version = sumac_statusReport_getVersionNumberFromFile($f,'--');
			else if (strtolower($extension) == 'css')
				$version = sumac_statusReport_getVersionNumberFromFile($f,'*');
			else if (strtolower($extension) == 'settings')
				$version = sumac_statusReport_getVersionNumberFromFile($f,'||');
			if ($version != '-1')
			{
				if ($version === false) $messages[] = array($f,'no version','should be ' . $requiredVersion);
				else
				{
					if (($version !== false) && ($version != $requiredVersion))
						$messages[] = array($f,'version is ' . $version,'should be ' . $requiredVersion);
				}
			}
		}
	}
	return $messages;
}

function sumac_statusReport_checkForExtraFiles($dirlist,$filelist)
//returns array of names of files that shouldn't be in the directory
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
		if (sumac_statusReport_getRequiredVersionNumber($f,$filelist) == null)
		{
			if ((is_file($f) === false) && (is_dir($f) === false))
				$messages[] =  array($f,'neither file nor directory');
			else
				$messages[] = $f;
		}
	}
	return $messages;
}

function sumac_statusReport_checkForMissingSubfolderFiles($dirlist)
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (is_dir($f))
		{
			if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
			if (($f == 'js') || ($f == 'css')) continue; //ignore js, and css as well
			$subfolderlist = scandir($f);
			$missing = sumac_statusReport_checkSubfolderFileList($f,$subfolderlist);
			if ($missing == '')
				$missing = array();
			$messages = array_merge($messages,$missing);
		}
	}
	return $messages;
}

function sumac_statusReport_checkForExtraSubfolderFiles($dirlist)
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (is_dir($f))
		{
			if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
			if (($f == 'js') || ($f == 'css')) continue; //ignore js, and css as well
			$subfolderlist = scandir($f);
			$extra = array();
			foreach ($subfolderlist as $sn => $sf)
			{
				if (($sf == '.') || ($sf == '..')) continue; //ignore this directory and its parent
				if (sumac_statusReport_checkSubfolderFileList($f,$sf) == false)
				{
					$fullname = $f . '/' . $sf;
					if ((is_file($fullname) === false) && (is_dir($fullname) === false))
						$extra[] =  array($fullname,'neither file nor directory');
					if (is_dir($fullname))
						$extra[] = 'Folder ' . $fullname . ' and its contents';
					else
						$extra[] = $fullname;
				}
			}
			$messages = array_merge($messages,$extra);
		}
	}
	return $messages;
}

function sumac_statusReport_getVersionNumberFromFile($filename,$delimpair)
{
	$data = sumac_statusReport_getFile($filename,100);
	$dataArray = explode($delimpair,$data);
	for ($i = 0; $i < count($dataArray); $i++)
	{
		if (substr($dataArray[$i],0,7) == 'version') return substr($dataArray[$i],7);
	}
	return false;
}

function sumac_statusReport_checkMasterFileList($dirlist,$filelist)
//returns array of names of files missing from directory contents
{
	$missing = array();
	foreach ($filelist as $f => $v)
	{
		if (in_array($f,$dirlist) === false) $missing[] = $f . ' version ' . $v;
	}
	return $missing;
}

function sumac_statusReport_getRequiredVersionNumber($filename,$filelist)
//returns version number that file should have, or null if it's an unknown file
{
	if (array_key_exists($filename,$filelist)) return $filelist[$filename];
	else return null;
}

function sumac_statusReport_checkSubfolderFileList($subfoldername,$filename)
{
	$certsfiles = array(
						'sumac.pem',
						);
	$userfiles = array(
						'bottom.css',
						'bottom.htm',
						'contact_bottom.htm',
						'contact_top.htm',
						'courses_bottom.htm',
						'courses_top.htm',
						'donation_bottom.htm',
						'donation_top.htm',
						'donation2_bottom.htm',
						'donation2_top.htm',
						'grantreview2g_bottom.htm',
						'grantreview2g_top.htm',
						'grantreview2r_bottom.htm',
						'grantreview2r_top.htm',
						'help.jpg',
						'help16x16.jpg',
						'logo.jpg',
						'membership_bottom.htm',
						'membership_top.htm',
						'membership2_bottom.htm',
						'membership2_top.htm',
						'over_sumac.css',
						'over_sumac_contact.css',
						'over_sumac_courses.css',
						'over_sumac_donation.css',
						'over_sumac_donation2.css',
						'over_sumac_grantreview2g.css',
						'over_sumac_grantreview2r.css',
						'over_sumac_membership.css',
						'over_sumac_membership2.css',
						'over_sumac_signup2.css',
						'over_sumac_singleform2.css',
						'over_sumac_ticketing.css',
						'pleasewait.gif',
						'signup2_bottom.htm',
						'signup2_top.htm',
						'singleform2_bottom.htm',
						'singleform2_top.htm',
						'sumac_parameter.settings',
						'sumac_strings.settings',
						'ticketing_bottom.htm',
						'ticketing_top.htm',
						'ticketing2_bottom.htm',
						'ticketing2_top.htm',
						'top.css',
						'top.htm',
						);
	switch ($subfoldername)
	{
	case 'certs':
		return sumac_statusReport_checkSpecificSubfolderFileList($subfoldername,$certsfiles,$filename);
	case 'user':
		return sumac_statusReport_checkSpecificSubfolderFileList($subfoldername,$userfiles,$filename);
	default:
		return null;
	}
}

function sumac_statusReport_checkSpecificSubfolderFileList($subfoldername,$subfolderlist,$filename)
{
	if (is_array($filename))
	{
		$missing = array();
		foreach ($subfolderlist as $n => $f)
		{
			if (in_array($f,$filename) === false) $missing[] = $subfoldername . '/' . $f;
		}
		return $missing;
	}
	else
	{
		return in_array($filename,$subfolderlist);
	}
}

function sumac_statusReport_showMessages($heading,$messages)
{
	echo '<br />' . $heading . '<br />';
	for ($i = 0; $i < count($messages); $i++)
	{
		echo $messages[$i] . '<br />';
	}
}

function sumac_statusReport_getFile($filename,$bytecount=null)
{
	$error_level = error_reporting();
	$new_level = error_reporting($error_level ^ E_WARNING);
	if ($bytecount != null) $data = file_get_contents($filename,true,null,-1,$bytecount);
	else $data = file_get_contents($filename,true);
	$error_level = error_reporting($error_level);
	return $data;
}

function sumac_statusReport_getSubFolderFileList($subfoldername)
{
	$subdirlist = scandir('./'.$subfoldername);
	$modifiedlist = array();
	for ($i = 0; $i < count($subdirlist); $i++)
	{
		$f = $subdirlist[$i];
		if (($f != '.') && ($f != '..')) $modifiedlist[] = $subfoldername.'/'.$f;
	}
	return $modifiedlist;
}

function sumac_statusReport_getUserParameter($p)
{
	$params = sumac_statusReport_getFile('user/sumac_parameter.settings');
	if (trim($params) == '') return '';
	$paramArray = explode('|',$params);
	for ($i = 0; $i < (count($paramArray)+1); $i = $i + 2)
	{
		$pakey = trim($paramArray[$i]);
		if (($i+1) < count($paramArray)) $pavalue = $paramArray[$i+1]; else $pavalue = '';
		if ($pakey != $p) continue;
		return $pavalue;
	}
}

function sumac_statusReport_v568_omitfieldsCheck()
{
	$html = '';
	$g2omitfields = sumac_statusReport_getUserParameter('g2omitfields');
	$d2omitfields = sumac_statusReport_getUserParameter('d2omitfields');
	$f2omitfields = sumac_statusReport_getUserParameter('f2omitfields');
	$s2omitfields = sumac_statusReport_getUserParameter('s2omitfields');
	$d1omitfields = sumac_statusReport_getUserParameter('d1omitfields');
	$m2omitfields = sumac_statusReport_getUserParameter('m2omitfields');
	$c2omitfields = sumac_statusReport_getUserParameter('c2omitfields');
	$t2omitfields = sumac_statusReport_getUserParameter('t2omitfields');
	$r2gomitfields = sumac_statusReport_getUserParameter('r2gomitfields');
	$omitfields = $g2omitfields.$d2omitfields.$f2omitfields.$s2omitfields.$d1omitfields.$m2omitfields.$c2omitfields.$t2omitfields.$r2gomitfields;
	if (($omitfields != '') && (strpos($omitfields,'n') === false) && (strpos($omitfields,'o') === false))
	{

		$html .= '<br />'
				. '<p class="sumac_sr_warntitle">WARNING FOR USERS UPGRADING FROM A VERSION EARLIER THAN 5.6.8</p>'
				. '<p class="sumac_sr_warnbody">Version 5.6.8 of the Sumac online code adds two new fields to the Login screen - a Name Prefix (or Title)'
				. ' and a Contact Source. The default parameter settings make both these fields optional,'
				. ' like the Communications Preferences field added in version 5.4.1.</p>'
				. '<p class="sumac_sr_warntitle"><b>BUT SINCE YOU ALREADY HAVE "..omitfields" PARAMETER SETTINGS, THE NEW DEFAULT SETTINGS WILL BE BLOCKED'
				. ' AND THE NEW FIELDS WILL APPEAR, WHETHER OR NOT YOU WANT THEM TO.</b></p>'
				. '<p class="sumac_sr_warnbody">To prevent the new fields appearing,';
		if ($g2omitfields != '') $html .= '<br />change your setting for <b>"g2omitfields"</b> from "'.$g2omitfields.'" to <b>"'.$g2omitfields.'no"</b>';
		if ($d2omitfields != '') $html .= '<br />change your setting for <b>"d2omitfields"</b> from "'.$d2omitfields.'" to <b>"'.$d2omitfields.'no"</b>';
		if ($f2omitfields != '') $html .= '<br />change your setting for <b>"f2omitfields"</b> from "'.$f2omitfields.'" to <b>"'.$f2omitfields.'no"</b>';
		if ($s2omitfields != '') $html .= '<br />change your setting for <b>"s2omitfields"</b> from "'.$s2omitfields.'" to <b>"'.$s2omitfields.'no"</b>';
		if ($d1omitfields != '') $html .= '<br />change your setting for <b>"d1omitfields"</b> from "'.$d1omitfields.'" to <b>"'.$d1omitfields.'no"</b>';
		if ($m2omitfields != '') $html .= '<br />change your setting for <b>"m2omitfields"</b> from "'.$m2omitfields.'" to <b>"'.$m2omitfields.'no"</b>';
		if ($c2omitfields != '') $html .= '<br />change your setting for <b>"c2omitfields"</b> from "'.$c2omitfields.'" to <b>"'.$c2omitfields.'no"</b>';
		if ($t2omitfields != '') $html .= '<br />change your setting for <b>"t2omitfields"</b> from "'.$t2omitfields.'" to <b>"'.$t2omitfields.'no"</b>';
		if ($r2gomitfields != '') $html .= '<br />change your setting for <b>"r2gomitfields"</b> from "'.$r2gomitfields.'" to <b>"'.$r2gomitfields.'no"</b>';
		$html .= '</p>';
	}
	return $html;
}

?>
