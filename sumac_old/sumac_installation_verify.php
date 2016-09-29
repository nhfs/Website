<?php
//version510//

//this is 'sumac_installation_verify.php', a stand-alone utility

/**********************************************************************************************
	Every PHP file should have a comment with the version that the file belongs to in the form:
	//versionNNNN//
	where NNNN is a number, e.g. 404 for version 4.0.4
	The number should match the version number of the file in the sumac_statusReport_checkMasterFileList() array.

	Every DTD or HTM file should have a similar comment in the form:
	<!--versionNNNN-->
	Again, the number NNNN should match the number associated with the file in the sumac_statusReport_checkMasterFileList() array.

	JPG,ICO files contain only binary image data and therefore have no version checking.

	Folders also have no version checking.

	No other file types are used at present.
	***** the version number comments must be contained in the first 100 bytes of the file *****
*************************************************************************************************/

$extensionsRequired = array('curl','openssl');
$phperrors = sumac_statusReport_checkPHPConfiguration('5','2',$extensionsRequired);
$dirlist = scandir('.');
$missing = sumac_statusReport_checkMasterFileList($dirlist);
$incorrect = sumac_statusReport_checkCodeFileVersions($dirlist);
$extra = sumac_statusReport_checkForExtraFiles($dirlist);
$missingFromSubfolders = sumac_statusReport_checkForMissingSubfolderFiles($dirlist);
$extraInSubfolders = sumac_statusReport_checkForExtraSubfolderFiles($dirlist);


//the installation is judged to have been successful if
//	no files are missing and
//	no code files have incorrect version numbers
//otherwise it is an invalid installation

//if there are extra files or if there are missing or extra files in a subfolder they are reported

//if there are problems with the PHP configuration they are reported

$self = $_SERVER['PHP_SELF'];
$folder = substr($self,0,strrpos($self,'/'));
$version = sumac_statusReport_checkMasterFileList('sumac_constants.php');
$codeVersion = substr($version,0,1);
for ($i = 1; $i < strlen($version); $i++) $codeVersion .= '.' . substr($version,$i,1);
$userhtml = (isset($_GET['userhtml']) || isset($_GET['USERHTML']) || isset($_GET['userHTML']));
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
	echo sumac_statusReport_getDetailHTML('Files missing from the main Sumac online folder',$missing,true);
if (count($incorrect) > 0)
	echo sumac_statusReport_getDetailHTML('Incorrect versions of Sumac online code files',$incorrect,true);
if (count($extra) > 0)
	echo sumac_statusReport_getDetailHTML('Extra files in the main Sumac online folder',$extra);
if (count($missingFromSubfolders) > 0)
	echo sumac_statusReport_getDetailHTML('Files missing from the Sumac online subfolders',$missingFromSubfolders);
if (count($extraInSubfolders) > 0)
	echo sumac_statusReport_getDetailHTML('Extra files in the Sumac online subfolders',$extraInSubfolders);

if (count($phperrors) > 0)
{
	$status = 'The PHP configuration on this host is not suitable for running the Sumac online system';
	$instructions = 'PLEASE CONTACT YOUR WEBSITE MANAGER.';
	echo sumac_statusReport_getStatusHTML(false,$status,$instructions);
	echo sumac_statusReport_getDetailHTML('PHP configuration problems',$phperrors,true);
}

echo sumac_statusReport_getFooterHTML($userhtml,$codeVersion);

exit();


function sumac_statusReport_getHeaderHTML($showUserHTML)
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
	}
	$html .= '</style>' . "\n";
//	$html .= '<script  type="text/javascript">' . "\n";
//	$html .= '</script>' . "\n";
	$html .= '</head>' . "\n";

	$html .= '<body>' . "\n";
	if ($showUserHTML) $html .= sumac_statusReport_getFile('user/top.htm');

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

function sumac_statusReport_getFooterHTML($showUserHTML,$codeVersion)
{
	$html = '';
	if (!$showUserHTML)
	{
		$html .= '<br />' . '[You can display your user header and footer HTML by adding the \'userhtml\' parameter.'
				. '&nbsp;E.G. sumac_installation_verify.php?userhtml&nbsp;]' . '<br />';
	}
	$html .= '</div>' . "\n";
	$html .= '<table width="100%"><tr><td align="center" style="font-size:1.0em">' .
			'<a style="color:gray" href="http://sumac.com">' .
			'Powered by Sumac Software ' . $codeVersion .
			'</a></td></tr></table>';
	if ($showUserHTML) $html .= sumac_statusReport_getFile('user/bottom.htm');
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

function sumac_statusReport_checkCodeFileVersions($dirlist)
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
		$versionExpected = sumac_statusReport_checkMasterFileList($f);
		if (($versionExpected != null) && ($versionExpected != '0'))
		{
			$extension = substr($f,strrpos($f,'.')+1);
			$version = '-1';
			if ((strtolower($extension) == 'php') || (strtolower($extension) == 'guide'))
				$version = sumac_statusReport_getVersionNumberFromFile($f,'//');
			else if ((strtolower($extension) == 'dtd') || (strtolower($extension) == 'htm'))
				$version = sumac_statusReport_getVersionNumberFromFile($f,'--');
			if ($version != '-1')
			{
				if ($version === false) $messages[] = array($f,'no version','should be ' . $versionExpected);
				else
				{
					if (($version !== false) && ($version != $versionExpected))
						$messages[] = array($f,'version is ' . $version,'should be ' . $versionExpected);
				}
			}
		}
	}
	return $messages;
}

function sumac_statusReport_checkForExtraFiles($dirlist)
{
	$messages = array();
	foreach ($dirlist as $n => $f)
	{
		if (($f == '.') || ($f == '..')) continue; //ignore this directory and its parent
		if (sumac_statusReport_checkMasterFileList($f) == null)
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

function sumac_statusReport_checkMasterFileList($filename)
{
	$filelist = array(
						'certs' => '0',
						'user' => '0',
						'accountdetails.dtd' => '430',
						'bottomnavbg.jpg' => '0',
						'bottomnavbgwus2.jpg' => '0',
						'contactdetails.dtd' => '404',
						'course_catalog.dtd' => '450',
						'directory.dtd' => '5002',
						'directory_entries.dtd' => '5002',
						'extras.dtd' => '404',
						'formtemplate.dtd' => '412',
						'narrownavbg.jpg' => '0',
						'newsletter.php' => '432',
						'organisation.dtd' => '440',
						'phpinfo.php' => '0',
						'raocu_start.php' => '5101',
						'raods_start.php' => '5101',
						'raomr_start.php' => '5101',
						'raots_start.php' => '5101',
						'response.dtd' => '413',
						'seatsales.dtd' => '404',
						'smalldelete.ico' => '0',
						'sumac.htm' => '512',
						'sumac_constants.php' => '5125',
						'sumac_contact_updated.php' => '510',
						'sumac_courses_redirect.php' => '5122',
						'sumac_course_chosen.php' => '5122',
						'sumac_directory_chosen.php' => '5122',
						'sumac_donation_made.php' => '510',
						'sumac_event_chosen.php' => '5122',
						'sumac_form_chosen.php' => '510',
						'sumac_form_updated.php' => '510',
						'sumac_form_update_abandoned.php' => '510',
						'sumac_http.php' => '5002',
						'sumac_identify_user.php' => '5122',
						'sumac_installation_verify.php' => '510',
						'sumac_leave.php' => '510',
						'sumac_make_donation.php' => '512',
						'sumac_manage_forms.php' => '510',
						'sumac_membership_renewed.php' => '510',
						'sumac_parameter_settings.guide' => '5121',
						'sumac_payment_made.php' => '510',
						'sumac_pay_for_course.php' => '511',
						'sumac_pay_for_tickets.php' => '511',
						'sumac_pay_without_purchase.php' => '511',
						'sumac_personal_history.php' => '510',
						'sumac_pick_tickets.php' => '5125',
						'sumac_pick_tickets_layout_stage.php' => '450',
						'sumac_pick_tickets_seating_plan.php' => '450',
						'sumac_redirect.php' => '5122',
						'sumac_renew_membership.php' => '511',
						'sumac_select_course.php' => '510',
						'sumac_select_directory.php' => '5123',
						'sumac_select_event.php' => '510',
						'sumac_session_utilities.php' => '5125',
						'sumac_set_parameters.php' => '512',
						'sumac_show_directory_entries.php' => '510',
						'sumac_show_session_status.php' => '450',
						'sumac_start.php' => '510',
						'sumac_start_new_session.php' => '510',
						'sumac_ticketing_redirect.php' => '510',
						'sumac_ticketing_utilities.php' => '440',
						'sumac_update_contact_details.php' => '510',
						'sumac_user_login.php' => '510',
						'sumac_utilities.php' => '510',
						'sumac_xml.php' => '510',
						'theatre.dtd' => '5124',
						'topnavbg.jpg' => '0',
						'topnavbg70grey.jpg' => '0',
						'topnavbgwus2.jpg' => '0',
						);
	if (is_array($filename))
	{
		$missing = array();
		foreach ($filelist as $f => $v)
		{
			if (in_array($f,$filename) === false) $missing[] = $f . ' version ' . $v;
		}
		return $missing;
	}
	else
	{
		if (array_key_exists($filename,$filelist)) return $filelist[$filename];
		else return null;
	}
}

function sumac_statusReport_checkSubfolderFileList($subfoldername,$filename)
{
	$certsfiles = array(
						'sumac.pem',
						);
	$userfiles = array(
						'bottom.css',
						'bottom.htm',
						'over_sumac.css',
						'sumac_parameter.settings',
						'sumac_strings.settings',
						'top.css',
						'top.htm',
						);
	if ($subfoldername == 'certs')
		return sumac_statusReport_checkSpecificSubfolderFileList($subfoldername,$certsfiles,$filename);
	else if ($subfoldername == 'user')
		return sumac_statusReport_checkSpecificSubfolderFileList($subfoldername,$userfiles,$filename);
	else
		return null;
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

?>
