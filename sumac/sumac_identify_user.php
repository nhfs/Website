<?php
//version568//

include_once 'sumac_constants.php';
include_once 'sumac_utilities.php';
include_once 'sumac_geth2.php';

//this is 'sumac_identify_user.php'

	$sid = session_id();
	if ($sid == "")
	{
		session_name(SUMAC_SESSION_NAME);
		session_start();
	}

	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'displayerrors') !== false)
	{
		$new_level = error_reporting(-1);
		ini_set("display_errors",1);
	}

include_once 'sumac_session_utilities.php';
	if (!isset($_SESSION[SUMAC_SESSION_SOURCE])) //or we could check 'port'
	{
		sumac_destroy_session(sumac_formatMessage($_SESSION[SUMAC_STR]["AE7"],$_SESSION[SUMAC_SESSION_ORGANISATION_NAME]) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
	if (($usingHTTPS == false) && (strtolower(substr($_SESSION[SUMAC_SESSION_ALLOWHTTP],0,1)) != 't'))
	{
		sumac_destroy_session(SUMAC_ERROR_REQUIRES_SSL . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	$referer = sumac_get_referer(SUMAC_SESSION_FOLDER);
	if ($referer == false)
	{
		sumac_destroy_session(SUMAC_ERROR_VERIFY_REFERER . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}
	if (
			($referer != '/sumac_start.php') &&
			($referer != '/sumac_redirect.php') &&
			($referer != '/raocu_start.php') &&
			($referer != '/raods_start.php') &&
			($referer != '/raomr_start.php') &&
			($referer != '/raots_start.php') &&
			($referer != '/sumac_ticketing_redirect.php') &&
			($referer != '/sumac_courses_redirect.php') &&
			($referer != '/sumac_course_chosen.php') &&
			($referer != '/sumac_course_unchosen.php') &&
			($referer != '/sumac_contact_updated.php') &&
			($referer != '/sumac_directory_chosen.php') &&
			($referer != '/sumac_donation_made.php') &&
			($referer != '/sumac_event_chosen.php') &&
			($referer != '/sumac_form_chosen.php') &&
			($referer != '/sumac_form_update_abandoned.php') &&
			($referer != '/sumac_form_updated.php') &&
			($referer != '/sumac_membership_renewed.php') &&
			($referer != '/sumac_payment_made.php') &&
			($referer != '/sumac_start_new_session.php') &&
			($referer != '/sumac_identify_user.php')
		)
	{
		sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INVALID_REFERER,$referer) . $_SESSION[SUMAC_STR]["AE5"]);
		return;
	}

	if (time() - $_SESSION[SUMAC_SESSION_TIMESTAMP] > $_SESSION[SUMAC_USERPAR_SESSEXPIRY])
	{
		sumac_forced_session_restart();
		return;
	}
	$_SESSION[SUMAC_SESSION_TIMESTAMP] = time();

	//figure out which of the Login/EmailPassword/Adduser buttons was clicked
	if (isset($_POST['login']))
	{
//check for email-address-as-username and password
include_once 'sumac_xml.php';
		if (isset($_POST['password']) || isset($_POST['loginpassword']))
		{
			if (isset($_POST['loginemailusername'])) $loginemail = $_POST['loginemailusername'];
			else if (isset($_POST['loginemail'])) $loginemail = $_POST['loginemail'];
			else
			{
				sumac_destroy_session('Login with password posted but no recognised email address posted in sumac_identify_user');
				return;
			}
			$_SESSION[SUMAC_SESSION_EMAILADDRESS] = $loginemail;
			$password = isset($_POST['password']) ? $_POST['password'] : $_POST['loginpassword'];
			$_SESSION[SUMAC_SESSION_FORMER_PASSWORD] = $password;
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$xml = SUMAC_XML_HEADER . '<login u="' . sumac_convertXMLSpecialChars($loginemail)
									. '" w="' . sumac_convertXMLSpecialChars($password) . '"></login>';
			$includes = array(SUMAC_REQUEST_INCLUDE_CHUNKS,SUMAC_REQUEST_INCLUDE_MEMBERSHIP);
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G) $includes[] = SUMAC_REQUEST_INCLUDE_GRANTEE_GRANT_APPLICATIONS;
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2R) $includes[] = SUMAC_REQUEST_INCLUDE_REVIEWER_GRANT_APPLICATIONS;

			$accountDocument = sumac_loadLoginAccountDocument($source,$port,$xml,SUMAC_REQUEST_PARAM_ACCOUNTLOGIN,$includes);
			unset($_POST['password']);
			sumac_processLoginResult($accountDocument,'login',true,isset($_POST['withadduser']));
		}
//check for email-address-as-username without a password
		else if (isset($_POST['email']) || isset($_POST['loginemail']))
		{
include_once 'sumac_xml.php';
			$loginemail = isset($_POST['email']) ? $_POST['email'] : '';
			if (($loginemail == '') && isset($_POST['loginemail'])) $loginemail = $_POST['loginemail'];
			$_SESSION[SUMAC_SESSION_EMAILADDRESS] = $loginemail;
			$_SESSION[SUMAC_SESSION_FORMER_PASSWORD] = SUMAC_SUMAC_NULL_PASSWORD;
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$xml = SUMAC_XML_HEADER . '<login u="' . sumac_convertXMLSpecialChars($loginemail)
									. '" w="' . SUMAC_SUMAC_NULL_PASSWORD . '"></login>';
			$includes = array(SUMAC_REQUEST_INCLUDE_CHUNKS,SUMAC_REQUEST_INCLUDE_MEMBERSHIP);
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G) $includes[] = SUMAC_REQUEST_INCLUDE_GRANTEE_GRANT_APPLICATIONS;
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2R) $includes[] = SUMAC_REQUEST_INCLUDE_REVIEWER_GRANT_APPLICATIONS;

			$accountDocument = sumac_loadLoginAccountDocument($source,$port,$xml,SUMAC_REQUEST_PARAM_ACCOUNTLOGIN,$includes);
			sumac_processLoginResult($accountDocument,'login-np',false,isset($_POST['withadduser']));
		}
		else	// should never happen
		{
			sumac_destroy_session('Login posted but no recognised email address posted in sumac_identify_user');
		}
	}
	else if (isset($_POST['requestpassword']))
	{
//check for emailusername
include_once 'sumac_http.php';
		if (isset($_POST['requestemailusername']) || isset($_POST['loginemail']))
		{
			$loginemail = isset($_POST['requestemailusername']) ? $_POST['requestemailusername'] : $_POST['loginemail'];
			$xml = SUMAC_XML_HEADER . '<password e="' . sumac_convertXMLSpecialChars($loginemail)
									. '"></password>';
			$variableValues = SUMAC_REQUEST_KEYWORD_REQUEST . '=' . SUMAC_REQUEST_PARAM_PASSWORD . $_SESSION[SUMAC_SESSION_WEBSITE_DATA];
			$source = $_SESSION[SUMAC_SESSION_SOURCE];
			$port = $_SESSION[SUMAC_SESSION_PORT];
			$result = sumac_postEncryptedXMLData($source,$port, '?' . $variableValues,$xml,'sumac.pem');
			$status = ($result == false) ? $_SESSION[SUMAC_SESSION_REQUEST_ERROR] : $_SESSION[SUMAC_STR]["AU1"];
			//unset($_POST['requestemailusername']);
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION2)
			{
include_once 'sumac_donation2.php';
				if (sumac_donation2('requestpassword',null,$status,'sumac_identify_user.php',null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SINGLEFORM2)
			{
include_once 'sumac_singleform2.php';
				if (sumac_singleform2('requestpassword',$status,null,null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else	// all other packages retry through new user login code
			{
//include_once 'sumac_user_login.php';
//				if (sumac_execLogin($status,'sumac_identify_user.php',true,isset($_POST['withadduser'])) == false)
//				{
//					echo sumac_getAbortHTML();
//					sumac_destroy_session('');
//				}
				if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
				{
include_once 'sumac_login2.php';
					if (sumac_login2(SUMAC_PACKAGE_CONTACT_UPDATE,'L','requestpassword',$status,'sumac_identify_user.php') == false)
					{
						echo sumac_getAbortHTML();
						sumac_destroy_session('');
					}
				}
				else
				{
include_once 'sumac_login2.php';
					if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],'L/CR','requestpassword',$status,'sumac_identify_user.php') == false)
					{
						echo sumac_getAbortHTML();
						sumac_destroy_session('');
					}
				}
			}
		}
		else	// should never happen
		{
			sumac_destroy_session('Password request posted but no recognised email address posted in sumac_identify_user');
		}
	}
	else if (isset($_POST['adduser']) || isset($_POST['pay']) || isset($_POST['submitform']))
	{
include_once 'sumac_xml.php';
		$emailaddress = isset($_POST['email']) ? $_POST['email'] : $_POST['newemail'];
		$_SESSION[SUMAC_SESSION_EMAILADDRESS] = $emailaddress;
		$password = isset($_POST['newpassword']) ? $_POST['newpassword'] : SUMAC_SUMAC_NULL_PASSWORD;
		$nameprefix =  isset($_POST['nameprefix']) ? $_POST['nameprefix'] : '';
		$firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
		$address1 = isset($_POST['address1']) ? $_POST['address1'] : '';
		$address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
		$city = isset($_POST['city']) ? $_POST['city'] : '';
		$province = isset($_POST['province']) ? $_POST['province'] : '';
		$postcode = isset($_POST['postcode']) ? $_POST['postcode'] : '';
		$country = isset($_POST['country']) ? $_POST['country'] : '';
		$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
		$cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] : '';
		$contactsourceid =  isset($_POST['contactsourceid']) ? $_POST['contactsourceid'] : '';
		$commprefs = '';
		foreach ($_POST as $checkbox_name => $pv)
		{
			if ((strlen($checkbox_name) > 5) && (substr($checkbox_name,0,5) == 'cpip_'))
			{
				$id = substr($checkbox_name,5);
				$commprefs .= (($commprefs=='') ? $id : ('|'.$id));
			}
		}

//checking that the remaining fields are non-blank should have been done by JS before returning here
		$xml = SUMAC_XML_HEADER;
		$xml .= '<adduser>';
		$xml .= '<buyer ea="' . sumac_convertXMLSpecialChars($emailaddress)
				. '" np="' . sumac_convertXMLSpecialChars($nameprefix)
				. '" fn="' . sumac_convertXMLSpecialChars($firstname)
				. '" ln="' . sumac_convertXMLSpecialChars($_POST['lastname'])
				. '" a1="' . sumac_convertXMLSpecialChars($address1)
				. '" a2="' . sumac_convertXMLSpecialChars($address2)
				. '" ci="' . sumac_convertXMLSpecialChars($city)
				. '" pr="' . sumac_convertXMLSpecialChars($province)
				. '" pc="' . sumac_convertXMLSpecialChars($postcode)
				. '" co="' . sumac_convertXMLSpecialChars($country)
				. '" ph="' . sumac_convertXMLSpecialChars($phone)
				. '" cp="' . sumac_convertXMLSpecialChars($cellphone)
				. '" pw="' . sumac_convertXMLSpecialChars($password)
				. '" si="' . sumac_convertXMLSpecialChars($contactsourceid)
				. '" pf="' . sumac_convertXMLSpecialChars($commprefs) . '">';
		$xml .= '</buyer>';
		$xml .= '</adduser>';
//echo $xml;
		$isNewPassword = isset($_POST['newpassword']);
		unset($_POST['newpassword']);
		$source = $_SESSION[SUMAC_SESSION_SOURCE];
		$port = $_SESSION[SUMAC_SESSION_PORT];
		$includes = null;
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G) $includes = SUMAC_REQUEST_INCLUDE_GRANTEE_GRANT_APPLICATIONS;
		$accountDocument = sumac_loadLoginAccountDocument($source,$port,$xml,SUMAC_REQUEST_PARAM_ADDUSER,$includes);
		sumac_processLoginResult($accountDocument,'adduser',$isNewPassword,true);
	}
	else 	//should never happen
	{
		sumac_destroy_session('Unexpected arrival in sumac_identify_user');
	}

function sumac_processLoginResult($accountDocument,$postedFunction,$withPassword,$withAdduser)
{
	if ($accountDocument == false)
	{
//
//  foreach($_SESSION as $x=>$y) echo $x . '=>' . $y . '<br />';
//
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION2)
		{
include_once 'sumac_donation2.php';
			if (sumac_donation2($postedFunction,null,$_SESSION[SUMAC_SESSION_REQUEST_ERROR],'sumac_identify_user.php',null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SINGLEFORM2)
		{
include_once 'sumac_singleform2.php';
			if (sumac_singleform2($postedFunction,$_SESSION[SUMAC_SESSION_REQUEST_ERROR],null,null) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else	// all other packages retry through new user login code
		{
			$panelLayout = $withAdduser ? 'C/LR' : 'L/CR';
			if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE) $panelLayout = 'L';
			else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SIGNUP2) $panelLayout = 'C';
include_once 'sumac_login2.php';
			if (sumac_login2($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE],$panelLayout,$postedFunction,$_SESSION[SUMAC_SESSION_REQUEST_ERROR],'sumac_identify_user.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
	}
	else	// we got an account document so the login must have succeeded
	{
		if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION)
		{
include_once 'sumac_make_donation.php';
			if (sumac_execDonate('','sumac_donation_made.php',$accountDocument) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING)
		{
			$t = 0;
			if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET]))
			{
				foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $p) $t = $t + count($p);
			}
			if ($t > 0)
			{
include_once 'sumac_pay_for_tickets.php';
				if (sumac_execPayForTickets('','sumac_payment_made.php',$accountDocument) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else
			{
include_once 'sumac_select_event.php';
				if (sumac_execSelectEvent('sumac_event_chosen.php') == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_TICKETING2)
		{
			$t = 0;
			if (isset($_SESSION[SUMAC_SESSION_TICKET_BASKET]))
			{
				foreach ($_SESSION[SUMAC_SESSION_TICKET_BASKET] as $p) $t = $t + count($p);
			}
			if ($t > 0)
			{
include_once 'sumac_ticketing2_pay.php';
				if (sumac_ticketing2_pay($accountDocument,'','') == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else
			{
include_once 'sumac_ticketing2.php';
				if (sumac_ticketing2() == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_COURSES)
		{
			if (isset($_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION]))
			{
				$action = $_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION];
				$div = $_SESSION[SUMAC_SESSION_POST_LOGIN_DIV];
				unset($_SESSION[SUMAC_SESSION_POST_LOGIN_ACTION]);
				unset($_SESSION[SUMAC_SESSION_POST_LOGIN_DIV]);
				if ($action == 'personal')
				{
include_once 'sumac_personal_history.php';
					if (sumac_execShowPersonalHistory($div,'sumac_form_chosen.php',$accountDocument) == false)
					{
						echo sumac_getAbortHTML();
						sumac_destroy_session('');
					}
				}
				else if ($action == 'payment')
				{
include_once 'sumac_pay_without_purchase.php';
					if (sumac_execPayWithoutPurchase('','sumac_payment_made.php',$accountDocument) == false)
					{
						echo sumac_getAbortHTML();
						sumac_destroy_session('');
					}
				}
				else if ($action == 'catalog')
				{
include_once 'sumac_select_course.php';
					if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
					{
						echo sumac_getAbortHTML();
						sumac_destroy_session('');
					}
				}
				else 	//should never happen
				{
					sumac_destroy_session('unknown post-login action ' . $action . ' for sumac_identify_user (' . $postedFunction . ')');
				}
			}
			else if (isset($_SESSION[SUMAC_SESSION_COURSE_SELECTIONS]['session']))
//??? should we use a different test to see if a course has been selected for registration
			{
include_once 'sumac_pay_for_course.php';
				if (sumac_execPayForCourse('','sumac_payment_made.php',$accountDocument) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
			else
			{
include_once 'sumac_select_course.php';
				if (sumac_execSelectCourse('sumac_course_chosen.php',0,null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP)
		{
include_once 'sumac_renew_membership.php';
			if (sumac_execRenew('','sumac_membership_renewed.php',$accountDocument) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP2)
		{
include_once 'sumac_membership2.php';
			if (sumac_membership2($accountDocument,'','') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)
		{
include_once 'sumac_update_contact_details.php';
			if (sumac_execUpdate('','sumac_contact_updated.php',$accountDocument) == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DIRECTORIES)
		{
include_once 'sumac_select_directory.php';
			if (sumac_execSelectDirectory('sumac_directory_chosen.php') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_DONATION2)
		{
			if ($postedFunction == 'adduser')
			{
				$combinedWithLogin = true;
				require 'sumac_donation2_made.php';
				return;
			}
			else
			{
include_once 'sumac_donation2.php';
				if (sumac_donation2($postedFunction,null,'','sumac_donation2_made.php',$accountDocument) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SINGLEFORM2)
		{
			if ($postedFunction == 'adduser')
			{
				$combinedWithLogin = true;
				require 'sumac_form2_submitted.php';
				return;
			}
			else
			{
include_once 'sumac_singleform2.php';
				if (sumac_singleform2($postedFunction,'',$accountDocument,null) == false)
				{
					echo sumac_getAbortHTML();
					sumac_destroy_session('');
				}
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2G)
		{
include_once 'sumac_grantreview2_grantee.php';
			if (sumac_grantreview2_grantee($accountDocument,'') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_GRANTREVIEW2R)
		{
include_once 'sumac_grantreview2_reviewer.php';
			if (sumac_grantreview2_reviewer($accountDocument,'') == false)
			{
				echo sumac_getAbortHTML();
				sumac_destroy_session('');
			}
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == SUMAC_PACKAGE_SIGNUP2)
		{
			//echo sumac_getLeaveSumacHTML(sumac_get_exiturl());
			echo sumac_geth2_exit_package_page('signup2',$accountDocument,sumac_geth2_spantext('signup2','H1'),'L4','L5','');
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') //login without active package
		{
			echo sumac_getLeaveSumacHTML(sumac_get_exiturl());
		}
		else 	//should never happen
		{
			sumac_destroy_session('unknown package ' . $_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] . ' for sumac_identify_user (' . $postedFunction . ')');
		}
	}
}

?>