<?php
//version5681//

//this is 'newsletter.php'
//first included in Sumac 4.3.0, July 2013
//control is passed here by a link in a client email
//apologetic note: this code was not working between November 2013 and February 2015

//its job is to tell the associated Sumac transaction server that it has received a link request
//and then redirect the operation to that link

//it can (but perhaps it should not) report who is making the request as well as what the request is

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_session_utilities.php';

	//no session needed
	$_SESSION[SUMAC_SESSION_DEBUG] = '';	//no debugging here

//	$version = explode('.', PHP_VERSION);
//	if ($version[0] < '5') return;
//	if (($version[0] == '5') && ($version[1] < '2')) return;
// We do say that we only support PHP 5.2 and above, but there is no point in giving up before we have to.

//there are two parameters
//the first, l=, specifies the web address to which to redirect this request; it is required; without it there is nothing to be done
//the second, u=, is optional; it may allow the Sumac transaction server to identify the caller

//the following example connects to Toronto Islands weather forecast
// http://127.0.0.1/sumac/newsletter.php/?L=http%3A%2F%2Fweather.gc.ca%2Fcity%2Fpages%2Fon-128_metric_e.html&U=a_sumac_user_id

	$linkaddr = null;
	if (isset($_GET['L'])) $linkaddr = $_GET['L'];
	else if (isset($_GET['l'])) $linkaddr = $_GET['l'];
	else return;	//nowhere to go ...

	$userid = null;
	if (isset($_GET['U'])) $userid = $_GET['U'];
	else if (isset($_GET['u'])) $userid = $_GET['u'];

	$source = sumac_get_parameter(null,'source',null);
	$port = sumac_get_parameter('80','port',null);
	$userdata = sumac_get_parameter($_SERVER["HTTP_HOST"],'userdata',null);
	$websitedata = '&' . SUMAC_REQUEST_KEYWORD_VERSION . '=' . SUMAC_CODE_VERSION .
					'&' . SUMAC_REQUEST_KEYWORD_USERIPADDR . '=' . $_SERVER["REMOTE_ADDR"] .
					'&' . SUMAC_REQUEST_KEYWORD_USERDATA . '=' . $userdata;
	$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_EMAILSTATS . $websitedata;
	$variableValues .= '&' . SUMAC_REQUEST_KEYWORD_EMAILLINK . '=' . $linkaddr;
	if ($userid != null) $variableValues .= '&' . SUMAC_REQUEST_KEYWORD_EMAILUSER . '=' . $userid;

	if ($source != null)
	{
		$error_level = error_reporting();
		$new_level = error_reporting($error_level ^ E_WARNING);
		$fp = fsockopen($source,$port, $errno, $errstr, 30);
		$error_level = error_reporting($error_level);
		if ($fp)
		{
			$out = "GET /" . '?' . $variableValues . " HTTP/1.0\r\n";
			$out .= "Host: " . $source . ":" . $port . "\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp,$out);
		}
		//else ... unable to connect to Sumac ... ignore the statistics and redirect as requested
    }
	//else ... no connection address for Sumac ... ignore the statistics and redirect as requested

	header( 'Location: ' . $linkaddr);
	exit();
?>
