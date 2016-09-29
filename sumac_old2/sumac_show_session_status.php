<?php
//version450//

//this is 'sumac_show_session_status.php'

	$sid = session_id();
	if ($sid == "")
	{
		session_name('SUMACSESS');
		session_start();
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' .
						' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html><body><p>';
include_once 'sumac_utilities.php';
	echo sumac_showSessionVariables('sumac_');
	echo '</p></body></html>' . "\n";
	exit();

?>