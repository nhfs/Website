<?php
//version512//

include_once 'sumac_constants.php';
include_once 'sumac_session_utilities.php';

function sumac_set_parameters($package,$resume=false)
{
//fetch the HTTP GET parameters into a new array with guaranteed lowercase keys
	$getKeys = array();
	foreach ($_GET as $x => $y) $getKeys[strtolower($x)] = $y;

	if ($resume === false)
	{
		if (isset($_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS])) unset($_SESSION[SUMAC_SESSION_PARAMETER_SETTINGS]);
		if (isset($_SESSION[SUMAC_SESSION_FATAL_ERROR])) unset($_SESSION[SUMAC_SESSION_FATAL_ERROR]);
		$_SESSION[SUMAC_SESSION_ORGANISATION_NAME] = 'main';
		$_SESSION[SUMAC_SESSION_DEBUG] = '';

		$_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] = $package;
//the only time we start without a package is for logging in
		$loginEntry = (isset($getKeys['entry']) && ($getKeys['entry'] == 'login'));
		if (($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] == '') && ($loginEntry === false))
		{
			sumac_destroy_session(SUMAC_ERROR_NO_INITIAL_PACKAGE_GIVEN . $_SESSION[SUMAC_STR]["AE5"]);
			return false;
		}
		else if ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != '')
		{
			$packagelist = array(SUMAC_PACKAGE_DONATION, SUMAC_PACKAGE_TICKETING, SUMAC_PACKAGE_COURSES,
								SUMAC_PACKAGE_DIRECTORIES, SUMAC_PACKAGE_MEMBERSHIP, SUMAC_PACKAGE_CONTACT_UPDATE);
			if (!in_array($package,$packagelist))
			{
				sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_INITIAL_PACKAGE_NOT_VALID,$package) . $_SESSION[SUMAC_STR]["AE5"]);
				return false;
			}
		}
	}

// set key parameters (allowhttp, debug, source, port, userdata)
	if (($resume === false) || isset($getKeys['allowhttp']))
	{
		$_SESSION[SUMAC_SESSION_ALLOWHTTP] = sumac_get_parameter('false','allowhttp',(isset($getKeys['allowhttp']) ? $getKeys['allowhttp'] : null));
		$usingHTTPS = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != '') && ($_SERVER['HTTPS'] != 'off'));
		if (($usingHTTPS == false) && (strtolower(substr($_SESSION[SUMAC_SESSION_ALLOWHTTP],0,1)) != 't'))
		{
			sumac_destroy_session(SUMAC_ERROR_REQUIRES_SSL . $_SESSION[SUMAC_STR]["AE5"]);
			return false;
		}
	}
	if (($resume === false) || isset($getKeys['debug']))
		$_SESSION[SUMAC_SESSION_DEBUG] = strtolower(sumac_get_parameter('','debug',(isset($getKeys['debug']) ? $getKeys['debug'] : null)));

	if (($resume === false) || isset($getKeys['source']))
	{
		$source = sumac_get_parameter(null,'source',(isset($getKeys['source']) ? $getKeys['source'] : null));
		if ($source == null)
		{
			sumac_destroy_session(sumac_formatMessage(SUMAC_ERROR_NO_SOURCE_ADDRESS,'<b>source</b>') . $_SESSION[SUMAC_STR]["AE5"]);
			return false;
		}
		$_SESSION[SUMAC_SESSION_SOURCE] = $source;
	}

	if (($resume === false) || isset($getKeys['port']))
		$_SESSION[SUMAC_SESSION_PORT] = sumac_get_parameter('80','port',(isset($getKeys['port']) ? $getKeys['port'] : null));

 	if (($resume === false) || isset($getKeys['userdata']))
 	{
		$_SESSION[SUMAC_SESSION_USERDATA] = sumac_get_parameter($_SERVER["HTTP_HOST"],'userdata',(isset($getKeys['userdata']) ? $getKeys['userdata'] : null));
		$_SESSION[SUMAC_SESSION_WEBSITE_DATA] = '&' . SUMAC_REQUEST_KEYWORD_VERSION . '=' . SUMAC_CODE_VERSION .
												'&' . SUMAC_REQUEST_KEYWORD_USERIPADDR . '=' . $_SERVER["REMOTE_ADDR"] .
												'&' . SUMAC_REQUEST_KEYWORD_USERDATA . '=' . $_SESSION[SUMAC_SESSION_USERDATA];
	}



//v4.1.8 individual parameters for those that were shared between packages
 	if (($resume === false) || isset($getKeys['dptitle']))
		$_SESSION[SUMAC_SESSION_H1_DPTITLE] = sumac_get_parameter($_SESSION[SUMAC_STR]["DH1"],'dptitle',(isset($getKeys['dptitle']) ? $getKeys['dptitle'] : null));

 	if (($resume === false) || isset($getKeys['titlem']))
		$_SESSION[SUMAC_SESSION_H1_TITLE_MONTHLY] = sumac_get_parameter($_SESSION[SUMAC_STR]["DH2"],'titlem',(isset($getKeys['titlem']) ? $getKeys['titlem'] : null));

 	if (($resume === false) || isset($getKeys['cutitle']))
		$_SESSION[SUMAC_SESSION_H1_CUTITLE] = sumac_get_parameter($_SESSION[SUMAC_STR]["UH1"],'cutitle',(isset($getKeys['cutitle']) ? $getKeys['cutitle'] : null));

 	if (($resume === false) || isset($getKeys['mrtitle']))
		$_SESSION[SUMAC_SESSION_H1_MRTITLE] = sumac_get_parameter($_SESSION[SUMAC_STR]["MH1"],'mrtitle',(isset($getKeys['mrtitle']) ? $getKeys['mrtitle'] : null));

 	if (($resume === false) || isset($getKeys['totitle']))
		$_SESSION[SUMAC_SESSION_H1_TOTITLE] = sumac_get_parameter($_SESSION[SUMAC_STR]["TH1"],'totitle',(isset($getKeys['totitle']) ? $getKeys['totitle'] : null));

 	if (($resume === false) || isset($getKeys['crtitle']))
		$_SESSION[SUMAC_SESSION_H1_CRTITLE] = sumac_get_parameter($_SESSION[SUMAC_STR]["CH1"],'crtitle',(isset($getKeys['crtitle']) ? $getKeys['crtitle'] : null));

 	if (($resume === false) || isset($getKeys['dppayment']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_DONATE] = sumac_get_parameter($_SESSION[SUMAC_STR]["DI7"],'dppayment',(isset($getKeys['dppayment']) ? $getKeys['dppayment'] : null));

 	if (($resume === false) || isset($getKeys['mrpayment']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP_ONLYPLAN] = sumac_get_parameter($_SESSION[SUMAC_STR]["MI6"],'mrpayment',(isset($getKeys['mrpayment']) ? $getKeys['mrpayment'] : null));

 	if (($resume === false) || isset($getKeys['topayment']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_BUY] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI20"],'topayment',(isset($getKeys['topayment']) ? $getKeys['topayment'] : null));

 	if (($resume === false) || isset($getKeys['dpaddcontact']))
		$_SESSION[SUMAC_SESSION_DPADDCONTACT] = sumac_get_parameter($_SESSION[SUMAC_STR]["DI6"],'dpaddcontact',(isset($getKeys['dpaddcontact']) ? $getKeys['dpaddcontact'] : null));

 	if (($resume === false) || isset($getKeys['craddcontact']))
		$_SESSION[SUMAC_SESSION_CRADDCONTACT] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI7"],'craddcontact',(isset($getKeys['craddcontact']) ? $getKeys['craddcontact'] : null));

 	if (($resume === false) || isset($getKeys['toaddcontact']))
		$_SESSION[SUMAC_SESSION_TOADDCONTACT] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI17"],'toaddcontact',(isset($getKeys['toaddcontact']) ? $getKeys['toaddcontact'] : null));

 	if (($resume === false) || isset($getKeys['dpaddorlogin']))
		$_SESSION[SUMAC_SESSION_DPADDORLOGIN] = sumac_get_parameter($_SESSION[SUMAC_STR]["DI8"],'dpaddorlogin',(isset($getKeys['dpaddorlogin']) ? $getKeys['dpaddorlogin'] : null));

 	if (($resume === false) || isset($getKeys['craddorlogin']))
		$_SESSION[SUMAC_SESSION_CRADDORLOGIN] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI6"],'craddorlogin',(isset($getKeys['craddorlogin']) ? $getKeys['craddorlogin'] : null));

 	if (($resume === false) || isset($getKeys['toaddorlogin']))
		$_SESSION[SUMAC_SESSION_TOADDORLOGIN] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI16"],'toaddorlogin',(isset($getKeys['toaddorlogin']) ? $getKeys['toaddorlogin'] : null));

 	if (($resume === false) || isset($getKeys['mrloginwopw']))
		$_SESSION[SUMAC_SESSION_MRLOGINWOPW] = sumac_get_parameter($_SESSION[SUMAC_STR]["MI5"],'mrloginwopw',(isset($getKeys['mrloginwopw']) ? $getKeys['mrloginwopw'] : null));

 	if (($resume === false) || isset($getKeys['culoginwopw']))
		$_SESSION[SUMAC_SESSION_CULOGINWOPW] = sumac_get_parameter($_SESSION[SUMAC_STR]["UI3"],'culoginwopw',(isset($getKeys['culoginwopw']) ? $getKeys['culoginwopw'] : null));

 	if (($resume === false) || isset($getKeys['login']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_LOGIN] = sumac_get_parameter($_SESSION[SUMAC_STR]["AI2"],'login',(isset($getKeys['login']) ? $getKeys['login'] : null));

 	if (($resume === false) || isset($getKeys['culogin']))
		$_SESSION[SUMAC_SESSION_CULOGIN] = sumac_get_parameter($_SESSION[SUMAC_STR]["UI2"],'culogin',(isset($getKeys['culogin']) ? $getKeys['culogin'] : null));

// set defaults according to package parameter
 	if (($resume === false) || isset($getKeys['title']))
 	{
		$title = sumac_get_parameter('','title',(isset($getKeys['title']) ? $getKeys['title'] : null));
	//the title parameter is only used to set a single-package title (v4.1.8)
	//individual title settings are available for each package
		if (($title != '') && ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] != ''))
		{
			if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) $_SESSION[SUMAC_SESSION_H1_DPTITLE] = $title;
			else if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $_SESSION[SUMAC_SESSION_H1_MRTITLE] = $title;
			else if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE) $_SESSION[SUMAC_SESSION_H1_CUTITLE] = $title;
			else if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_TICKETING) $_SESSION[SUMAC_SESSION_H1_TOTITLE] = $title;
		}
	}

 	if (($resume === false) || isset($getKeys['payment']))
 	{
		$payment = sumac_get_parameter('','payment',(isset($getKeys['payment']) ? $getKeys['payment'] : null));
	//the payment parameter is only used to set a single-package payment instructions (v4.1.8)
	//individual payment instructions settings are available for each package
		if (($payment != '') && (($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) || ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) || ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_TICKETING)))
		{
			if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) $_SESSION[SUMAC_SESSION_INSTRUCTIONS_DONATE] = $payment;
			else if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $_SESSION[SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP_ONLYPLAN] = $payment;
			else $_SESSION[SUMAC_SESSION_INSTRUCTIONS_BUY] = $payment;	//must be ticketing package
		}
	}

 	if (($resume === false) || isset($getKeys['addcontact']))
 	{
		$addcontact = sumac_get_parameter('','addcontact',(isset($getKeys['addcontact']) ? $getKeys['addcontact'] : null));
	//the addcontact parameter is only used to set a single-package addcontact instructions (v4.1.8)
	//individual addcontact instructions settings are available for each package
		if (($addcontact != '') && (($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) || ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_TICKETING)))
		{
			if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) $_SESSION[SUMAC_SESSION_DPADDCONTACT] = $addcontact;
			else $_SESSION[SUMAC_SESSION_TOADDCONTACT] = $addcontact;	//must be ticketing package
		}
	}

 	if (($resume === false) || isset($getKeys['addorlogin']))
 	{
		$addorlogin = sumac_get_parameter('','addorlogin',(isset($getKeys['addorlogin']) ? $getKeys['addorlogin'] : null));
	//the addorlogin parameter is only used to set a single-package addorlogin instructions (v4.1.8)
	//individual addorlogin instructions settings are available for each package
		if (($addorlogin != '') && (($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) || ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_TICKETING)))
		{
			if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_DONATION) $_SESSION[SUMAC_SESSION_DPADDORLOGIN] = $addorlogin;
			else $_SESSION[SUMAC_SESSION_TOADDORLOGIN] = $addorlogin;	//must be ticketing package
		}
	}


 	if (($resume === false) || isset($getKeys['loginwopw']))
 	{
		$loginwopw = sumac_get_parameter('','loginwopw',(isset($getKeys['loginwopw']) ? $getKeys['loginwopw'] : null));
	//the loginwopw parameter is only used to set a single-package loginwopw instructions (v4.1.8)
	//individual loginwopw instructions settings are available for each package
		if (($loginwopw != '') && (($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) || ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_CONTACT_UPDATE)))
		{
			if ($_SESSION[SUMAC_SESSION_SINGLE_PACKAGE] == SUMAC_PACKAGE_MEMBERSHIP) $_SESSION[SUMAC_SESSION_MRLOGINWOPW] = $loginwopw;
			else $_SESSION[SUMAC_SESSION_CULOGINWOPW] = $loginwopw;	//must be contact update package
		}
	}

//the login parameter is used for all packages except contact update (v4.1.8)
//an individual login instruction settings is now available for contact update

//set parameters common to all packages
 	if (($resume === false) || isset($getKeys['titlecolour']))
		$_SESSION[SUMAC_SESSION_H1_TITLE_COLOUR] = sumac_get_parameter('Black','titlecolour',(isset($getKeys['titlecolour']) ? $getKeys['titlecolour'] : null));

 	if ($resume === false)	//MUST COME FROM FILE
		$_SESSION[SUMAC_SESSION_USE_PASSWORDS] = (strtolower(substr(sumac_get_parameter_from_file('false','nouserpw'),0,1)) !== 't');

 	if (($resume === false) || isset($getKeys['password']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_EMAIL_PASSWORD] = sumac_get_parameter($_SESSION[SUMAC_STR]["AI1"],'password',(isset($getKeys['password']) ? $getKeys['password'] : null));

 	$fontsize = '';
 	if (($resume === false) || isset($getKeys['fontsize']))
 	{
		$fontsize = strtoupper(sumac_get_parameter('normal','fontsize',(isset($getKeys['fontsize']) ? $getKeys['fontsize'] : null)));
		$_SESSION[SUMAC_SESSION_SCALE] = '100%';	//normal
		if ($fontsize == 'SMALL') $_SESSION[SUMAC_SESSION_SCALE] = '75%';
		else if ($fontsize == 'BIG') $_SESSION[SUMAC_SESSION_SCALE] = '125%';
	}

 	if (($resume === false) || isset($getKeys['fonttype']))
		$_SESSION[SUMAC_SESSION_FONTS] = sumac_get_parameter('sans-serif','fonttype',(isset($getKeys['fonttype']) ? $getKeys['fonttype'] : null));

 	if (($resume === false) || isset($getKeys['textcolour']))
		$_SESSION[SUMAC_SESSION_TEXTCOLOUR] = sumac_get_parameter('Black','textcolour',(isset($getKeys['textcolour']) ? $getKeys['textcolour'] : null));

 	if (($resume === false) || isset($getKeys['bodycolour']))
		$_SESSION[SUMAC_SESSION_BODYCOLOUR] = sumac_get_parameter('White','bodycolour',(isset($getKeys['bodycolour']) ? $getKeys['bodycolour'] : null));

 	if (($resume === false) || isset($getKeys['connection']))
		$_SESSION[SUMAC_SESSION_CONNECTION_FAILED] = sumac_get_parameter($_SESSION[SUMAC_STR]["AE2"],'connection',(isset($getKeys['connection']) ? $getKeys['connection'] : null));

 	if (($resume === false) || isset($getKeys['badresponse']))
		$_SESSION[SUMAC_SESSION_INVALID_SERVER_RESPONSE] = sumac_get_parameter($_SESSION[SUMAC_STR]["AE4"],'badresponse',(isset($getKeys['badresponse']) ? $getKeys['badresponse'] : null));

 	if (($resume === false) || isset($getKeys['othererror']))
		$_SESSION[SUMAC_SESSION_ANY_OTHER_ERROR] = sumac_get_parameter($_SESSION[SUMAC_STR]["AE1"],'othererror',(isset($getKeys['othererror']) ? $getKeys['othererror'] : null));

 	if (($resume === false) || isset($getKeys['dateformat']))
		$_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT] = sumac_get_parameter(SUMAC_DATE_DISPLAY_FORMAT,'dateformat',(isset($getKeys['dateformat']) ? $getKeys['dateformat'] : null));

 	if (($resume === false) || isset($getKeys['nooversumac']))
		$_SESSION[SUMAC_SESSION_SUPPRESS_USER_OVERRIDE_CSS] = (substr(strtolower(sumac_get_parameter('false','nooversumac',(isset($getKeys['nooversumac']) ? $getKeys['nooversumac'] : null))),0,1) != 'f');

 	if (($resume === false) || isset($getKeys['precursym']))
		$_SESSION[SUMAC_SESSION_PRE_CURRENCY_SYMBOL] = sumac_get_parameter(SUMAC_DOLLAR_SYMBOL,'precursym',(isset($getKeys['precursym']) ? $getKeys['precursym'] : null));

// set parameters common only to the DONATION and TICKETING and CONTACT_UPDATE packages
 	if ($resume === false)	//MUST COME FROM FILE
		$_SESSION[SUMAC_SESSION_FIELDS_MANDATORY] = strtolower(sumac_get_parameter_from_file('fatszcp','mustfields'));

 	if (($resume === false) || isset($getKeys['ppbgcolour']))
		$_SESSION[SUMAC_SESSION_PPBGCOLOUR] = sumac_get_parameter('White','ppbgcolour',(isset($getKeys['ppbgcolour']) ? $getKeys['ppbgcolour'] : null));

// set parameters common only to the DONATION and TICKETING packages
 	if ($resume === false)	//MUST COME FROM FILE
 		$_SESSION[SUMAC_SESSION_FIELDS_HIDDEN] = strtolower(sumac_get_parameter_from_file('','omitfields'));

// set parameters for the DONATION package alone
 	if (($resume === false) || isset($getKeys['frequency']))
		$_SESSION[SUMAC_SESSION_FREQUENCY] = strtoupper(sumac_get_parameter('','frequency',(isset($getKeys['frequency']) ? $getKeys['frequency'] : null)));
	//only first character will be checked - 'M' = monthly

 	if (($resume === false) || isset($getKeys['amounttopay']))
 	{
		$_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] = '';
		$amounttopay = sumac_get_parameter('','amounttopay',(isset($getKeys['amounttopay']) ? $getKeys['amounttopay'] : null));
		if (is_numeric($amounttopay)) $_SESSION[SUMAC_SESSION_AMOUNT_TO_PAY] = $amounttopay * 100;	//if valid, convert to cents
	}

 	if (($resume === false) || isset($getKeys['paybutton']))
		$_SESSION[SUMAC_SESSION_PAY_BUTTON] = sumac_get_parameter($_SESSION[SUMAC_STR]["DL1"],'paybutton',(isset($getKeys['paybutton']) ? $getKeys['paybutton'] : null));

 	if (($resume === false) || isset($getKeys['titlem']))
		$_SESSION[SUMAC_SESSION_H1_TITLE_MONTHLY] = sumac_get_parameter($_SESSION[SUMAC_STR]["DH2"],'titlem',(isset($getKeys['titlem']) ? $getKeys['titlem'] : null));

 	if (($resume === false) || isset($getKeys['commemform']))
		$_SESSION[SUMAC_SESSION_DONATION_COMMEM_LINES] = sumac_get_parameter('0','commemform',(isset($getKeys['commemform']) ? $getKeys['commemform'] : null));

 	if (($resume === false) || isset($getKeys['commemtext1']))
		$_SESSION[SUMAC_SESSION_DONATION_COMMEM_QUERY1] = sumac_get_parameter(
				(($_SESSION[SUMAC_SESSION_DONATION_COMMEM_LINES] == '1') ? $_SESSION[SUMAC_STR]["DU5"] : $_SESSION[SUMAC_STR]["DU6"]),
				'commemtext1',(isset($getKeys['commemtext1']) ? $getKeys['commemtext1'] : null));

 	if (($resume === false) || isset($getKeys['commemtext2']))
		$_SESSION[SUMAC_SESSION_DONATION_COMMEM_QUERY2] = sumac_get_parameter($_SESSION[SUMAC_STR]["DU4"],'commemtext2',(isset($getKeys['commemtext2']) ? $getKeys['commemtext2'] : null));

 	if (($resume === false) || isset($getKeys['combdonpref']))
		$_SESSION[SUMAC_SESSION_COMBINED_DONATION_PREF] = strtoupper(sumac_get_parameter('once','combdonpref',(isset($getKeys['combdonpref']) ? $getKeys['combdonpref'] : null)));

 	if (($resume === false) || isset($getKeys['sepmontext']))
		$_SESSION[SUMAC_SESSION_SEPARATE_MONTHLY_INSTRUCTIONS] = (substr(strtolower(sumac_get_parameter('false','sepmontext',(isset($getKeys['sepmontext']) ? $getKeys['sepmontext'] : null))),0,1) != 'f');

// set parameters for the TICKETING package alone
 	if (($resume === false) || isset($getKeys['order']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_ORDER] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI21"],'order',(isset($getKeys['order']) ? $getKeys['order'] : null));

 	if (($resume === false) || isset($getKeys['ordernd']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_ORDER] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI22"],'ordernd',(isset($getKeys['ordernd']) ? $getKeys['ordernd'] : null));

 	if (($resume === false) || isset($getKeys['sbavcolour']))
		$_SESSION[SUMAC_SESSION_SBAVCOLOUR] = sumac_get_parameter('White','sbavcolour',(isset($getKeys['sbavcolour']) ? $getKeys['sbavcolour'] : null));

 	if (($resume === false) || isset($getKeys['sbbgcolour']))
		$_SESSION[SUMAC_SESSION_SBBGCOLOUR] = sumac_get_parameter('Wheat','sbbgcolour',(isset($getKeys['sbbgcolour']) ? $getKeys['sbbgcolour'] : null));

 	if (($resume === false) || isset($getKeys['etbgcolour']))
		$_SESSION[SUMAC_SESSION_ETBGCOLOUR] = sumac_get_parameter(SUMAC_DEFAULT_THEATRE_COLOUR,'etbgcolour',(isset($getKeys['etbgcolour']) ? $getKeys['etbgcolour'] : null));

 	if (($resume === false) || isset($getKeys['detailwide']))
		$_SESSION[SUMAC_SESSION_DETAILWIDE] = sumac_get_parameter(600,'detailwide',(isset($getKeys['detailwide']) ? $getKeys['detailwide'] : null));

 	if (($resume === false) || isset($getKeys['detailtall']))
		$_SESSION[SUMAC_SESSION_DETAILTALL] = sumac_get_parameter(400,'detailtall',(isset($getKeys['detailtall']) ? $getKeys['detailtall'] : null));

 	if (($resume === false) || isset($getKeys['navbgfolder']))
		$_SESSION[SUMAC_SESSION_NAVBGFOLDER] = sumac_get_parameter('','navbgfolder',(isset($getKeys['navbgfolder']) ? $getKeys['navbgfolder'] : null));

 	if (($resume === false) || isset($getKeys['navbgfilext']))
		$_SESSION[SUMAC_SESSION_NAVBGFILEXT] = sumac_get_parameter('jpg','navbgfilext',(isset($getKeys['navbgfilext']) ? $getKeys['navbgfilext'] : null));

 	if (($resume === false) || isset($getKeys['chosentext1']))
		$_SESSION[SUMAC_SESSION_CHOSENTEXT1] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI18"],'chosentext1',(isset($getKeys['chosentext1']) ? $getKeys['chosentext1'] : null));;

 	if (($resume === false) || isset($getKeys['chosentext2']))
		$_SESSION[SUMAC_SESSION_CHOSENTEXT2] = sumac_get_parameter($_SESSION[SUMAC_STR]["TI19"],'chosentext2',(isset($getKeys['chosentext2']) ? $getKeys['chosentext2'] : null));;

 	if (($resume === false) || isset($getKeys['incpaynote']))
		$_SESSION[SUMAC_SESSION_INCPAYNOTE] = (substr(strtolower(sumac_get_parameter('false','incpaynote',(isset($getKeys['incpaynote']) ? $getKeys['incpaynote'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['paynotetext']))
		$_SESSION[SUMAC_SESSION_PAYNOTETEXT] = sumac_get_parameter($_SESSION[SUMAC_STR]["TF1"],'paynotetext',(isset($getKeys['paynotetext']) ? $getKeys['paynotetext'] : null));;

 	if (($resume === false) || isset($getKeys['noeventsnow']))
		$_SESSION[SUMAC_SESSION_THEATRE_MISSING] = sumac_get_parameter($_SESSION[SUMAC_STR]["TE1"],'noeventsnow',(isset($getKeys['noeventsnow']) ? $getKeys['noeventsnow'] : null));

 	if (($resume === false) || isset($getKeys['selectevent']))
		$_SESSION[SUMAC_SESSION_SELECT_AN_EVENT] = sumac_get_parameter($_SESSION[SUMAC_STR]["TU11"],'selectevent',(isset($getKeys['selectevent']) ? $getKeys['selectevent'] : null));

//two options not yet implemented
$_SESSION[SUMAC_SESSION_OMIT_BLOCK_SELECTOR] = false;
$_SESSION[SUMAC_SESSION_ALLOW_PARTY_BOOKING] = false;

// and some further secondary settings for the TICKETING package based on fontsize
// (see above for $_SESSION[SUMAC_SESSION_SCALE] setting)
	if ($fontsize == 'SMALL')
	{
		$_SESSION[SUMAC_SESSION_PH_SCALE] = '150%';
		$_SESSION[SUMAC_SESSION_TH_SCALE] = '80%';
		$_SESSION[SUMAC_SESSION_BUTTON_WIDTH] = '1.1em';
		$_SESSION[SUMAC_SESSION_BUTTON_HEIGHT] = '1.1em';
		$_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] = '0.8em';
	}
	else if ($fontsize == 'BIG')
	{
		$_SESSION[SUMAC_SESSION_PH_SCALE] = '125%';
		$_SESSION[SUMAC_SESSION_TH_SCALE] = '100%';
		$_SESSION[SUMAC_SESSION_BUTTON_WIDTH] = '1.7em';
		$_SESSION[SUMAC_SESSION_BUTTON_HEIGHT] = '1.7em';
		$_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] = '1.0em';
	}
	else	//normal
	{
		$_SESSION[SUMAC_SESSION_PH_SCALE] = '133%';
		$_SESSION[SUMAC_SESSION_TH_SCALE] = '100%';
		$_SESSION[SUMAC_SESSION_BUTTON_WIDTH] = '1.4em';
		$_SESSION[SUMAC_SESSION_BUTTON_HEIGHT] = '1.4em';
		$_SESSION[SUMAC_SESSION_INFACING_BUTTON_WIDTH] = '1.0em';
	}

// set parameters for the CONTACT_UPDATE package alone
 	if (($resume === false) || isset($getKeys['update']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_UPDATE] = sumac_get_parameter($_SESSION[SUMAC_STR]["UI1"],'update',(isset($getKeys['update']) ? $getKeys['update'] : null));

// set parameters for the MEMBERSHIP package alone
 	if (($resume === false) || isset($getKeys['emailaddress']))
		$_SESSION[SUMAC_SESSION_EMAILADDRESS] = sumac_get_parameter('','emailaddress',(isset($getKeys['emailaddress']) ? $getKeys['emailaddress'] : null));

 	if (($resume === false) || isset($getKeys['renew']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_MEMBERSHIP] = sumac_get_parameter($_SESSION[SUMAC_STR]["MI4"],'renew',(isset($getKeys['renew']) ? $getKeys['renew'] : null));

 	if (($resume === false) || isset($getKeys['norenew']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_CANNOT_OFFER] = sumac_get_parameter($_SESSION[SUMAC_STR]["MI3"],'norenew',(isset($getKeys['norenew']) ? $getKeys['norenew'] : null));

 	if (($resume === false) || isset($getKeys['optnatext']))
		$_SESSION[SUMAC_SESSION_OPTEXTRA_NATEXT] = sumac_get_parameter(SUMAC_MEMBERSHIP_OPTEXTRA_NATEXT,'optnatext',(isset($getKeys['optnatext']) ? $getKeys['optnatext'] : null));

// set parameters for the COURSES package alone
 	if (($resume === false) || isset($getKeys['order']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_COMPLETE_THE_COURSE_ORDER] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI9"],'order',(isset($getKeys['order']) ? $getKeys['order'] : null));

 	if (($resume === false) || isset($getKeys['ordernd']))
		$_SESSION[SUMAC_SESSION_INSTRUCTIONS_COURSE_ORDER] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI10"],'ordernd',(isset($getKeys['ordernd']) ? $getKeys['ordernd'] : null));

 	if (($resume === false) || isset($getKeys['dateformat1']))
		$_SESSION[SUMAC_SESSION_CATALOG_DATE_DISPLAY_FORMAT] = sumac_get_parameter($_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT],'dateformat1',(isset($getKeys['dateformat1']) ? $getKeys['dateformat1'] : null));

 	if (($resume === false) || isset($getKeys['dateformat2']))
		$_SESSION[SUMAC_SESSION_HISTORY_DATE_DISPLAY_FORMAT] = sumac_get_parameter($_SESSION[SUMAC_SESSION_DATE_DISPLAY_FORMAT],'dateformat2',(isset($getKeys['dateformat2']) ? $getKeys['dateformat2'] : null));

 	if (($resume === false) || isset($getKeys['sortcolumn']))
		$_SESSION[SUMAC_SESSION_DEFAULT_SORT_COLUMN] = strtolower(sumac_get_parameter(SUMAC_DEFAULT_SORT_COLUMN,'sortcolumn',(isset($getKeys['sortcolumn']) ? $getKeys['sortcolumn'] : null)));
	if (($_SESSION[SUMAC_SESSION_DEFAULT_SORT_COLUMN] > 3) || ($_SESSION[SUMAC_SESSION_DEFAULT_SORT_COLUMN] < 1))
		$_SESSION[SUMAC_SESSION_DEFAULT_SORT_COLUMN] = SUMAC_DEFAULT_SORT_COLUMN;
	$_SESSION[SUMAC_SESSION_ACTIVE_SORT_COLUMN] = $_SESSION[SUMAC_SESSION_DEFAULT_SORT_COLUMN];

 	if (($resume === false) || isset($getKeys['sortdir']))
		$_SESSION[SUMAC_SESSION_DEFAULT_SORT_DIRECTION] = strtolower(substr(sumac_get_parameter(SUMAC_DEFAULT_SORT_DIRECTION,'sortdir',(isset($getKeys['sortdir']) ? $getKeys['sortdir'] : null)),0,1));
	if (($_SESSION[SUMAC_SESSION_DEFAULT_SORT_DIRECTION] != 'a') && ($_SESSION[SUMAC_SESSION_DEFAULT_SORT_DIRECTION] != 'd'))
		$_SESSION[SUMAC_SESSION_DEFAULT_SORT_DIRECTION] = SUMAC_DEFAULT_SORT_DIRECTION;
	$_SESSION[SUMAC_SESSION_ACTIVE_SORT_DIRECTION] = $_SESSION[SUMAC_SESSION_DEFAULT_SORT_DIRECTION];

 	if (($resume === false) || isset($getKeys['deffeename']))
		$_SESSION[SUMAC_SESSION_DEFAULT_COURSE_FEE_NAME] = sumac_get_parameter(SUMAC_DEFAULT_COURSE_FEE_NAME,'deffeename',(isset($getKeys['deffeename']) ? $getKeys['deffeename'] : null));

 	if (($resume === false) || isset($getKeys['paycourse']))
		$_SESSION[SUMAC_SESSION_PAYCOURSE] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI8"],'paycourse',(isset($getKeys['paycourse']) ? $getKeys['paycourse'] : null));

 	if (($resume === false) || isset($getKeys['payaccount']))
		$_SESSION[SUMAC_SESSION_PAYACCOUNT] = sumac_get_parameter($_SESSION[SUMAC_STR]["CI1"],'payaccount',(isset($getKeys['payaccount']) ? $getKeys['payaccount'] : null));

 	if (($resume === false) || isset($getKeys['omitfinhist']))
		$_SESSION[SUMAC_SESSION_OMIT_FINANCIAL_HISTORY] = (substr(strtolower(sumac_get_parameter('false','omitfinhist',(isset($getKeys['omitfinhist']) ? $getKeys['omitfinhist'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['omiteduhist']))
		$_SESSION[SUMAC_SESSION_OMIT_PERSONAL_HISTORY] = (substr(strtolower(sumac_get_parameter('false','omiteduhist',(isset($getKeys['omiteduhist']) ? $getKeys['omiteduhist'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['omitformsum']))
		$_SESSION[SUMAC_SESSION_OMIT_FORMS_SUMMARY] = (substr(strtolower(sumac_get_parameter('false','omitformsum',(isset($getKeys['omitformsum']) ? $getKeys['omitformsum'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['omitcnavbar']))
		$_SESSION[SUMAC_SESSION_OMIT_COURSES_NAVBAR] = (substr(strtolower(sumac_get_parameter('false','omitcnavbar',(isset($getKeys['omitcnavbar']) ? $getKeys['omitcnavbar'] : null))),0,1) != 'f');

 	if (($resume === false) || isset($getKeys['formflag1']))
		$_SESSION[SUMAC_SESSION_FORM_FLAG_1] = sumac_get_parameter(SUMAC_TEXT_FORMFIELDFLAG_1,'formflag1',(isset($getKeys['formflag1']) ? $getKeys['formflag1'] : null));
 	if (($resume === false) || isset($getKeys['formflag0']))
		$_SESSION[SUMAC_SESSION_FORM_FLAG_0] = sumac_get_parameter(SUMAC_TEXT_FORMFIELDFLAG_0,'formflag0',(isset($getKeys['formflag0']) ? $getKeys['formflag0'] : null));

 	if (($resume === false) || isset($getKeys['nocoursenow']))
		$_SESSION[SUMAC_SESSION_CATALOG_MISSING] = sumac_get_parameter($_SESSION[SUMAC_STR]["CE1"],'nocoursenow',(isset($getKeys['nocoursenow']) ? $getKeys['nocoursenow'] : null));

 	if (($resume === false) || isset($getKeys['formsopen']))
		$_SESSION[SUMAC_SESSION_FORMS_OPEN_CHOICE] = sumac_get_parameter('1','formsopen',(isset($getKeys['formsopen']) ? $getKeys['formsopen'] : null));

 	if (($resume === false) || isset($getKeys['mandfldcol']))
		$_SESSION[SUMAC_SESSION_MANDFLDCOL] = sumac_get_parameter('Cyan','mandfldcol',(isset($getKeys['mandfldcol']) ? $getKeys['mandfldcol'] : null));

 	if (($resume === false) || isset($getKeys['catalogurl']))
		$_SESSION[SUMAC_SESSION_CATALOGURL] = sumac_get_parameter($_SESSION[SUMAC_SESSION_RETURN],'catalogurl',(isset($getKeys['catalogurl']) ? $getKeys['catalogurl'] : null));

	$_SESSION[SUMAC_SESSION_COURSES_NO_CATALOG] = false;	//only true for 'register' function

// set parameters for the DIRECTORIES package alone
 	if (($resume === false) || isset($getKeys['selectorht']))
		$_SESSION[SUMAC_USERPAR_SELECTORHT] = sumac_get_parameter('150px','selectorht',(isset($getKeys['selectorht']) ? $getKeys['selectorht'] : null));

 	if (($resume === false) || isset($getKeys['selectorbg']))
		$_SESSION[SUMAC_USERPAR_SELECTORBG] = sumac_get_parameter('cornsilk','selectorbg',(isset($getKeys['selectorbg']) ? $getKeys['selectorbg'] : null));

 	if (($resume === false) || isset($getKeys['selectedbg']))
		$_SESSION[SUMAC_USERPAR_SELECTEDBG] = sumac_get_parameter('aqua','selectedbg',(isset($getKeys['selectedbg']) ? $getKeys['selectedbg'] : null));

 	if (($resume === false) || isset($getKeys['resultsshow']))
		$_SESSION[SUMAC_SESSION_RESULTS_SHOW_CHOICE] = sumac_get_parameter('1','resultsshow',(isset($getKeys['resultsshow']) ? $getKeys['resultsshow'] : null));

 	if (($resume === false) || isset($getKeys['eitherformat']))
		$_SESSION[SUMAC_SESSION_ALLOW_EITHER_FORMAT] = (strtolower(substr(sumac_get_parameter('false','eitherformat',(isset($getKeys['eitherformat']) ? $getKeys['eitherformat'] : null)),0,1)) !== 'f');

 	if (($resume === false) || isset($getKeys['resultsformat']))
		$_SESSION[SUMAC_SESSION_RESULTS_FORMAT_CHOICE] = sumac_get_parameter('1','resultsformat',(isset($getKeys['resultsformat']) ? $getKeys['resultsformat'] : null));

// set parameters for navigation and connection to Sumac
 	if (($resume === false) || isset($getKeys['donationlnk']))
		$_SESSION[SUMAC_SESSION_DONATION_LINK] = (substr(strtolower(sumac_get_parameter('false','donationlnk',(isset($getKeys['donationlnk']) ? $getKeys['donationlnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['ticketlnk']))
		$_SESSION[SUMAC_SESSION_TICKETING_LINK] = (substr(strtolower(sumac_get_parameter('false','ticketlnk',(isset($getKeys['ticketlnk']) ? $getKeys['ticketlnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['renewallnk']))
		$_SESSION[SUMAC_SESSION_MEMBER_LINK] = (substr(strtolower(sumac_get_parameter('false','renewallnk',(isset($getKeys['renewallnk']) ? $getKeys['renewallnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['contactlnk']))
		$_SESSION[SUMAC_SESSION_CONTACT_LINK] = (substr(strtolower(sumac_get_parameter('false','contactlnk',(isset($getKeys['contactlnk']) ? $getKeys['contactlnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['courseslnk']))
		$_SESSION[SUMAC_SESSION_COURSES_LINK] = (substr(strtolower(sumac_get_parameter('false','courseslnk',(isset($getKeys['courseslnk']) ? $getKeys['courseslnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['directlnk']))
		$_SESSION[SUMAC_SESSION_DIRECTORIES_LINK] = (substr(strtolower(sumac_get_parameter('false','directlnk',(isset($getKeys['directlnk']) ? $getKeys['directlnk'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['leavelnk']))
		$_SESSION[SUMAC_SESSION_LEAVE_LINK] = (substr(strtolower(sumac_get_parameter('false','leavelnk',(isset($getKeys['leavelnk']) ? $getKeys['leavelnk'] : null))),0,1) != 'f');

// set parameters for connection to Sumac
 	if (($resume === false) || isset($getKeys['notheatre']))
		$_SESSION[SUMAC_SESSION_EXCLUDE_THEATRE] = (substr(strtolower(sumac_get_parameter('false','notheatre',(isset($getKeys['notheatre']) ? $getKeys['notheatre'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['nocatalog']))
		$_SESSION[SUMAC_SESSION_EXCLUDE_COURSECATALOG] = (substr(strtolower(sumac_get_parameter('false','nocatalog',(isset($getKeys['nocatalog']) ? $getKeys['nocatalog'] : null))),0,1) != 'f');
 	if (($resume === false) || isset($getKeys['nodirectory']))
		$_SESSION[SUMAC_SESSION_EXCLUDE_DIRECTORIES] = (substr(strtolower(sumac_get_parameter('false','nodirectory',(isset($getKeys['nodirectory']) ? $getKeys['nodirectory'] : null))),0,1) != 'f');

	$_SESSION[SUMAC_USERPAR_SESSEXPIRY] = sumac_get_parameter(1800,'sessexpiry',(isset($getKeys['sessexpiry']) ? $getKeys['sessexpiry'] : null));

// process the debug setting for the display of errors
	if (strpos($_SESSION[SUMAC_SESSION_DEBUG],'displayerrors') !== false)
	{
		$new_level = error_reporting(-1);
		ini_set("display_errors",1);
	}

	if ($resume === false)
	{
// ensure certain session variables are 'unset', i.e. start off with no value
		if (isset($_SESSION[SUMAC_SESSION_REQUEST_ERROR])) unset($_SESSION[SUMAC_SESSION_REQUEST_ERROR]);
//and others at least exist
		$_SESSION[SUMAC_SESSION_LOGGED_IN_NAME] = $_SESSION[SUMAC_STR]["AU3"];
		$_SESSION[SUMAC_SESSION_TOTAL_CENTS] = 0;
	}

	return true;
}

function sumac_use_single_package_parameter_defaults()
{
	$_SESSION[SUMAC_SESSION_DONATION_LINK] = false;
	$_SESSION[SUMAC_SESSION_TICKETING_LINK] = false;
	$_SESSION[SUMAC_SESSION_MEMBER_LINK] = false;
	$_SESSION[SUMAC_SESSION_CONTACT_LINK] = false;
	$_SESSION[SUMAC_SESSION_COURSES_LINK] = false;
	$_SESSION[SUMAC_SESSION_DIRECTORIES_LINK] = false;
	$_SESSION[SUMAC_SESSION_LEAVE_LINK] = true;
	$_SESSION[SUMAC_SESSION_EXCLUDE_THEATRE] = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_TICKETING);
	$_SESSION[SUMAC_SESSION_EXCLUDE_COURSECATALOG] = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_COURSES);
	$_SESSION[SUMAC_SESSION_EXCLUDE_DIRECTORIES] = ($_SESSION[SUMAC_SESSION_ACTIVE_PACKAGE] != SUMAC_PACKAGE_DIRECTORIES);
}

?>