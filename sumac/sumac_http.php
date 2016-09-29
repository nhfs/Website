<?php
//version564//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';

function sumac_getXMLData($in_source,$in_port,$selection)
{
	$source = 'http://' . $in_source . $selection;

	$error_level = error_reporting();
	$new_level = error_reporting($error_level ^ E_WARNING);
    $fp = fsockopen($in_source, $in_port, $errno, $errstr, 30);
    $xmldata = '';
	$error_level = error_reporting($error_level);
    if (!$fp)
    {
        //echo "$errstr ($errno)<br />\n";
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_CONNECTION_FAILED];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage($_SESSION[SUMAC_STR]["AE3"],$in_source,$in_port,"$errstr ($errno)",$selection);
		return false;
    }
    else
    {
        $out = "GET /" . $selection . " HTTP/1.0\r\n";
        $out .= "Host: " . $in_source . ":" . $in_port . "\r\n";
       // $out .= "Accept: text/html,application/xhtml+xml,application/xml\r\n";
        //$out .= "Accept-Charset: ISO-8859-1,utf-8\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        while (!feof($fp))
        {
            $xmldata .= fgets($fp, 1024);
        }
        fclose($fp);
		$doubleCRLF = strpos($xmldata,"\r\n\r\n");
		if ($doubleCRLF === false)
		{
			$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage($_SESSION[SUMAC_STR]["AE3"],$in_source,$in_port,$_SESSION[SUMAC_STR]["AE4"],$selection);
			return false;
		}
		$firstCRLF = strpos($xmldata,"\r\n");
		$httpOK200 = strpos($xmldata,"200 OK");
		if (($httpOK200 === false) || ($httpOK200 >= $firstCRLF))
		{
			$http404 = strpos($xmldata," 404");
			if (($http404 === false) || ($http404 >= $firstCRLF))
			{
				$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
				$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage($_SESSION[SUMAC_STR]["AE3"],$in_source,$in_port,$_SESSION[SUMAC_STR]["AE4"],$selection);
				return false;
			}
			else
			{
				$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
				$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = urldecode(substr($xmldata,$doubleCRLF+4));
				return false;
			}
		}
		if (strlen($xmldata) <= ($doubleCRLF + 4))
		{
			$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
			$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = sumac_formatMessage($_SESSION[SUMAC_STR]["AE3"],$in_source,$in_port,$_SESSION[SUMAC_STR]["AE4"],$selection);
	        return false;
		}
    }
	return substr($xmldata,$doubleCRLF+4);
}

function sumac_postEncryptedXMLData($in_destination,$in_port,$selection,$cleardata,$certname)
{
	if (isset($_SESSION[SUMAC_SESSION_REQUEST_ERROR])) unset($_SESSION[SUMAC_SESSION_REQUEST_ERROR]);
	$pubkey = openssl_get_publickey(file_get_contents(realpath('certs/' . $certname)));
	// encrypt the data
	if (openssl_seal($cleardata, $sealed, $ekeys, array($pubkey)) === false)
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_STR]["AE12"];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = $_SESSION[SUMAC_STR]["AE12"];//SUMAC_ERROR_PROBLEM_POSTING_ENCRYPTED_DATA;
		return false;
	}
	//encode the encrypted data for transmission
	$postdata = base64_encode($sealed);
	$key = base64_encode($ekeys[0]);

	$destination = 'http://' . $in_destination . $selection;
	$headers = array();
	$headers[] = "X-Key-Length:" . strlen($key);
	$headers[] = "X-Data-Length:" . strlen($postdata);
	$headers[] = "Expect:";
	$ch = curl_init($destination);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 30 secs
	curl_setopt($ch, CURLOPT_PORT, $in_port);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($key . $postdata)); //adding encryption key and encrypted POST data
	curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
	$xmldata = curl_exec($ch);
	$httpcode = ($xmldata === false) ? '-1' : curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($httpcode == '200')
	{
		if (substr($xmldata,0,strlen(SUMAC_XML_HEADER)) == SUMAC_XML_HEADER) return $xmldata;
		else return sumac_createXMLResponseForUnstructured($httpcode,$xmldata,'good');
	}
	else if ($httpcode != '-1') return sumac_createXMLResponseForUnstructured($httpcode,$xmldata,'bad');
	else
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_PROBLEM_POSTING_ENCRYPTED_DATA;
		return false;
	}
}

function sumac_postUnencryptedXMLData($in_destination,$in_port,$selection,$cleardata)
{
	if (isset($_SESSION[SUMAC_SESSION_REQUEST_ERROR])) unset($_SESSION[SUMAC_SESSION_REQUEST_ERROR]);
	$destination = 'http://' . $in_destination . $selection;
	$headers = array();
	$headers[] = "X-Key-Length:0";
//	$headers[] = "Expect:";
	$ch = curl_init($destination);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); // times out after 30 secs
	curl_setopt($ch, CURLOPT_PORT, $in_port);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $cleardata); // POST data
	curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST
	$xmldata = curl_exec($ch);
	$httpcode = ($xmldata === false) ? '-1' : curl_getinfo($ch,CURLINFO_HTTP_CODE);
//echo 'status of POST ' . $httpcode . '<br />';
//echo 'XML data ' . $xmldata . '<br />';
	curl_close($ch);

	if ($httpcode == '200')
	{
		if (substr($xmldata,0,strlen(SUMAC_XML_HEADER)) == SUMAC_XML_HEADER) return $xmldata;
		else return sumac_createXMLResponseForUnstructured($httpcode,$xmldata,'good');
	}
	else if ($httpcode != '-1') return sumac_createXMLResponseForUnstructured($httpcode,$xmldata,'bad');
	else
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = SUMAC_ERROR_PROBLEM_POSTING_UNENCRYPTED_DATA;
		return false;
	}
/*
	else //old - pre-version 4.1 - handling of errors with HTTP code
	{
		$_SESSION[SUMAC_SESSION_FATAL_ERROR] = $_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE];
		$_SESSION[SUMAC_SESSION_REQUEST_ERROR] = urldecode($xmldata);
		return false;
	}
*/
}

//temporary function to replace unstructured Sumac responses with XML
function sumac_createXMLResponseForUnstructured($httpcode,$xmldata,$status)
{
	$newXMLdata = SUMAC_XML_HEADER;
	$newXMLdata .= '<!DOCTYPE response SYSTEM "response.dtd">';
	$newXMLdata .= '<response status="' . $status . '">';
	$newXMLdata .= '<message>' . $xmldata . ' [HTTP result ' . $httpcode . ']' . '</message>';
	$newXMLdata .= '</response>';
	return $newXMLdata;
}

?>