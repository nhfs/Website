<?php
//version550//

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
						'accountdetails.dtd' => '552',
						'bottomnavbg.jpg' => '0',
						'bottomnavbgwus2.jpg' => '0',
						'contactdetails.dtd' => '404',
						'course_catalog.dtd' => '450',
						'directory.dtd' => '5002',
						'directory_entries.dtd' => '552',
						'extras.dtd' => '404',
						'formtemplate.dtd' => '412',
						'narrownavbg.jpg' => '0',
						'newsletter.php' => '432',
						'organisation.dtd' => '552',
						'phpinfo.php' => '0',
						'raocu_start.php' => '5101',
						'raods_start.php' => '5101',
						'raomr_start.php' => '520',
						'raots_start.php' => '5101',
						'response.dtd' => '552',
						'seatsales.dtd' => '404',
						'smalldelete.ico' => '0',
						'sumac.htm' => '550',
						'sumac_can_province_dropdown.htm' => '520',
						'sumac_constants.php' => '552',
						'sumac_contact_updated.php' => '541',
						'sumac_courses_redirect.php' => '5122',
						'sumac_course_chosen.php' => '5122',
						'sumac_directory_chosen.php' => '5122',
						'sumac_donation_made.php' => '510',
						'sumac_donation2.php' => '552',
						'sumac_donation2_made.php' => '551',
						'sumac_event_chosen.php' => '5122',
						'sumac_form_chosen.php' => '510',
						'sumac_form_updated.php' => '510',
						'sumac_form_update_abandoned.php' => '510',
						'sumac_geth2.php' => '552',
						'sumac_http.php' => '5002',
						'sumac_identify_user.php' => '551',
						'sumac_installation_verify.php' => '550',
						'sumac_leave.php' => '551',
						'sumac_login2.php' => '550',
						'sumac_make_donation.php' => '512',
						'sumac_manage_forms.php' => '510',
						'sumac_membership_renewed.php' => '510',
						'sumac_parameter_settings.guide' => '551',
						'sumac_payment2.php' => '552',
						'sumac_payment_made.php' => '510',
						'sumac_pay_for_course.php' => '511',
						'sumac_pay_for_tickets.php' => '511',
						'sumac_pay_without_purchase.php' => '511',
						'sumac_personal_history.php' => '510',
						'sumac_pick_tickets.php' => '551',
						'sumac_pick_tickets_layout_stage.php' => '530',
						'sumac_pick_tickets_seating_plan.php' => '450',
						'sumac_provinces_and_states_dropdown.htm' => '520',
						'sumac_redirect.php' => '550',
						'sumac_renew_membership.php' => '511',
						'sumac_select_course.php' => '510',
						'sumac_select_directory.php' => '5123',
						'sumac_select_event.php' => '510',
						'sumac_session_utilities.php' => '5125',
						'sumac_set_parameters.php' => '551',
						'sumac_show_directory_entries.php' => '510',
						'sumac_show_session_status.php' => '450',
						'sumac_signup2.php' => '551',
						'sumac_singleform2.php' => '551',
						'sumac_singleform2_submitted.php' => '551',
						'sumac_start.php' => '551',
						'sumac_start_new_session.php' => '551',
						'sumac_states_and_provinces_dropdown.htm' => '520',
						'sumac_ticketing_redirect.php' => '540',
						'sumac_ticketing_utilities.php' => '540',
						'sumac_update_contact_details.php' => '541',
						'sumac_us_state_dropdown.htm' => '520',
						'sumac_user_login.php' => '510',
						'sumac_utilities.php' => '551',
						'sumac_xml.php' => '510',
						'theatre.dtd' => '540',
						'topnavbg.jpg' => '0',
						'topnavbg70grey.jpg' => '0',
						'topnavbgwus2.jpg' => '0',

						'css/sumac.css' => '552',
						'css/sumac_donation2.css' => '5201',
						'css/sumac_protected.css' => '520',
						'css/sumac_singleform2.css' => '530',
						'css/sumac_signup2.css' => '550',

						'js/sumac.js' => '552',
						'js/sumac_donation2.js' => '520',
						'js/sumac_donation2_text.js' => '552',
						'js/sumac_preloaded.js' => '530',
						'js/sumac_shared_text.js' => '551',
						'js/sumac_singleform2.js' => '530',
						'js/sumac_singleform2_text.js' => '550',
						'js/sumac_signup2.js' => '550',
						'js/sumac_signup2_text.js' => '550',
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
$userhtml = (isset($_GET['userhtml']) || isset($_GET['USERHTML']) || isset($_GET['userHTML']));
$donationhtml = (isset($_GET['userdonationhtml']) || isset($_GET['USERDONATIONHTML']) || isset($_GET['userdonationHTML']));
$singleformhtml = (isset($_GET['usersingleformhtml']) || isset($_GET['USERSINGLEFORMHTML']) || isset($_GET['usersingleformHTML']));
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

echo sumac_statusReport_getHeaderHTML($userhtml,$donationhtml,$singleformhtml);

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

echo sumac_statusReport_getFooterHTML($userhtml,$donationhtml,$singleformhtml,$codeVersion);

exit();


function sumac_statusReport_getHeaderHTML($showUserHTML,$showDonationHTML,$showSingleformHTML)
{
	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	$html .= '<html><head>' . "\n";
	$html .= '<meta http-equiv="Content-Script-Type" content="text/javascript" />' . "\n";
	$html .= '<title>Sumac installation status</title>' . "\n";
	$html .= '<style type="text/css">';
	$html .= sumac_statusReport_getHTMLStyleElements();
	if ($showUserHTML)
	{
		$html .= sumac_statusReport_getFile('user/top.css');
		$html .= sumac_statusReport_getFile('user/bottom.css');
		$html .= sumac_statusReport_getFile('user/over_sumac.css');
		if ($showDonationHTML) $html .= sumac_statusReport_getFile('user/over_sumac_donation2.css');
		if ($showSingleformHTML) $html .= sumac_statusReport_getFile('user/over_sumac_singleform2.css');
	}
	$html .= '</style>' . "\n";
//	$html .= '<script  type="text/javascript">' . "\n";
//	$html .= '</script>' . "\n";
	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";
	if ($showUserHTML) $html .= sumac_statusReport_getFile('user/top.htm');
	else if ($showDonationHTML) $html .= sumac_statusReport_getFile('user/donation2_top.htm');
	else if ($showSingleformHTML) $html .= sumac_statusReport_getFile('user/singleform2_top.htm');

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

function sumac_statusReport_getFooterHTML($showUserHTML,$showDonationHTML,$showSingleformHTML,$codeVersion)
{
	$html = '';
	if ((!$showUserHTML) && (!$showDonationHTML) && (!$showSingleformHTML))
	{
		$html .= '<br />' . '[You can display your user top and bottom HTML by adding the \'userhtml\' parameter.'
				. '&nbsp;E.G. sumac_installation_verify.php?userhtml' //. '<br />'
				. '<br />' . ' - or you can display your user header and footer HTML for the new Donations page by adding the \'userdonationhtml\' parameter.'
				. '&nbsp;E.G. sumac_installation_verify.php?userdonationhtml' //. '<br />'
				. '<br />' . ' - or you can display your user header and footer HTML for the Single Form page by adding the \'usersingleformhtml\' parameter.'
				. '&nbsp;E.G. sumac_installation_verify.php?usersingleformhtml&nbsp;]' . '<br />';
	}
	$html .= '</div>' . "\n";
	$html .= '<table width="100%"><tr><td align="center" style="font-size:1.0em">' .
			'<a style="color:gray" href="http://sumac.com">' .
			'Powered by Sumac Software ' . $codeVersion .
			'</a></td></tr></table>';
	if ($showUserHTML) $html .= sumac_statusReport_getFile('user/bottom.htm');
	else if ($showDonationHTML) $html .= sumac_statusReport_getFile('user/donation2_bottom.htm');
	else if ($showSingleformHTML) $html .= sumac_statusReport_getFile('user/singleform2_bottom.htm');
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
						'donation2_bottom.htm',
						'donation2_top.htm',
						'over_sumac.css',
						'over_sumac_donation2.css',
						'over_sumac_singleform2.css',
						'over_sumac_signup2.css',
						'singleform2_bottom.htm',
						'singleform2_top.htm',
						'signup2_bottom.htm',
						'signup2_top.htm',
						'sumac_parameter.settings',
						'sumac_strings.settings',
						'top.css',
						'top.htm',
						'logo.jpg',
						'pleasewait.gif',
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

?>
