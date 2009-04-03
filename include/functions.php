<?php
/**
* Helper functions available in the ImpressCMS process
*
* @copyright	http://www.xoops.org/ The XOOPS Project
* @copyright	XOOPS_copyrights.txt
* @copyright	http://www.impresscms.org/ The ImpressCMS Project
* @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @package	core
* @since		XOOPS
* @author		http://www.xoops.org The XOOPS Project
* @author		modified by marcan <marcan@impresscms.org>
* @version	$Id: functions.php 1683 2008-04-19 13:50:00Z malanciault $
*/

/**
* The header
* 
* Implements all functions that are executed within the header of the page
* (meta tags, header expiration, etc)
* It will all be echoed, so no return in this function
*
* @param bool  $closehead  close the <head> tag
*/
function xoops_header($closehead=true)
{
	global $xoopsConfig, $xoopsTheme, $xoopsConfigMetaFooter;
	$myts =& MyTextSanitizer::getInstance();

	if(!headers_sent())
	{
		header('Content-Type:text/html; charset='._CHARSET);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header('Cache-Control: no-store, no-cache, max-age=1, s-maxage=1, must-revalidate, post-check=0, pre-check=0');
		header("Pragma: no-cache");
	}
	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'._LANGCODE.'" lang="'._LANGCODE.'">
	<head>
	<meta http-equiv="content-type" content="text/html; charset='._CHARSET.'" />
	<meta http-equiv="content-language" content="'._LANGCODE.'" />
	'.htmlspecialchars($xoopsConfigMetaFooter['google_meta']).'
	<meta name="robots" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_robots']).'" />
	<meta name="keywords" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_keywords']).'" />
	<meta name="description" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_desc']).'" />
	<meta name="rating" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_rating']).'" />
	<meta name="author" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_author']).'" />
	<meta name="copyright" content="'.htmlspecialchars($xoopsConfigMetaFooter['meta_copyright']).'" />
	<meta name="generator" content="ImpressCMS" />
	<title>'.htmlspecialchars($xoopsConfig['sitename']).'</title>
	<script type="text/javascript" src="'.ICMS_URL.'/include/xoops.js"></script>
	<script type="text/javascript" src="'.ICMS_URL.'/include/linkexternal.js"></script>
	<link rel="stylesheet" type="text/css" media="all" href="' . ICMS_URL . '/icms'.(( defined('_ADM_USE_RTL') && _ADM_USE_RTL )?'_rtl':'').'.css" />';
	$config_handler =& xoops_gethandler('config');
	$icmsConfigPlugins =& $config_handler->getConfigsByCat(ICMS_CONF_PLUGINS);
	$jscript = '';
	if(class_exists('XoopsFormDhtmlTextArea')){
		foreach ($icmsConfigPlugins['sanitizer_plugins'] as $key) {
			if(empty($key)) continue;
			if(file_exists(ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.js')){
				echo '<script type="text/javascript" src="'.ICMS_URL.'/plugins/textsanitizer/'.$key.'/'.$key.'.js"></script>';
			}else{
				$extension = include_once ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.php';
				$func = 'javascript_'.$key;
				if ( function_exists($func) ) {
					@list($encode, $jscript) = $func($ele_name);
					if (!empty($jscript)) {
						if(!file_exists(ICMS_ROOT_PATH.'/'.$jscript)){
							echo '<script type="text/javascript">'.$jscript.'</script>';
						}else{
							echo '<script type="text/javascript" src="'.$jscript.'"></script>';
						}
					}
				}
			}
		}
	}

	$style_info = '';
	foreach ($icmsConfigPlugins['sanitizer_plugins'] as $key) {
		if(empty($key)) continue;
		if(file_exists(ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.css')){
			echo '<link rel="stylesheet" media="screen" href="'.ICMS_URL.'/plugins/textsanitizer/'.$key.'/'.$key.'.css" type="text/css" />';
		}else{
			$extension = include_once ICMS_ROOT_PATH.'/plugins/textsanitizer/'.$key.'/'.$key.'.php';
			$func = 'style_'.$key;
			if ( function_exists($func) ) {
				$style_info = $func();
				if (!empty($style_info)) {
					if(!file_exists(ICMS_ROOT_PATH.'/'.$style_info)){
						echo '<style media="screen" type="text/css">
						'.$style_info.'
						</style>';
					}else{
						echo '<link rel="stylesheet" media="screen" href="'.$style_info.'" type="text/css" />';
					}
				}
			}
		}
	}

	$themecss = getcss($xoopsConfig['theme_set']);
	if ($themecss) {
		echo '<link rel="stylesheet" type="text/css" media="all" href="'.$themecss.'" />';
		//echo '<style type="text/css" media="all"><!-- @import url('.$themecss.'); --></style>';
	}
	if ($closehead) {
		echo '</head><body>';
	}
}

/**
* The footer
* 
* Implements all functions that are executed in the footer
*/
function xoops_footer()
{
	global $xoopsConfigMetaFooter;
	echo ''.htmlspecialchars($xoopsConfigMetaFooter['google_analytics']).'</body></html>';
	ob_end_flush();
}

/**
 * ImpressCMS Error Message Function
 *
 * @since ImpressCMS 1.2
 * @version $Id$
 *
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 *
 * @param string $msg	The Error Message
 * @param string $title	The Error message title
 * @param bool $render	Whether to echo (render) or return the HTML string
 * @return string $ret The entire error message in a HTML string
 * @todo Make this work with templates ;)
 */
function icms_error_msg($msg, $title='', $render = true){
	$ret = '<div class="errorMsg">';
	if($title != '') {$ret .= '<h4>'.$title.'</h4>';}
	if(is_array($msg))
	{
		foreach($msg as $m) {$ret .= $m.'<br />';}
	}
	else {$ret .= $msg;}
	$ret .= '</div>';
	if($render)
		echo $ret;
	else
		return $ret;
}

/**
 * Backwards Compatibility Function
 *
 * @since XOOPS
 * @version $Id$
 * @deprecated
 * @see icms_error_msg
 *
 * @author The XOOPS Project <http://www.xoops.org>
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 *
 * @param string $msg
 * @param string $title
 */
function xoops_error($msg, $title=''){ icms_error_msg($msg, $title, true); }

/**
 * ImpressCMS Warning Message Function
 *
 * @since ImpressCMS 1.2
 * @version $Id$
 *
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 *
 * @param string $msg	The Error Message
 * @param string $title	The Error Message title
 * @param	bool	$render	Whether to echo (render) or return the HTML string
 *
 * @todo Make this work with templates ;)
 */
function icms_warning_msg($msg, $title='', $render = false){
	$ret = '<div class="warningMsg">';
	if($title != '') {$ret .= '<h4>'.$title.'</h4>';}
	if(is_array($msg))
	{
		foreach($msg as $m) {$ret .= $m.'<br />';}
	}
	else {$ret .= $msg;}
	$ret .= '</div>';
	if($render)
		echo $ret;
	else
		return $ret;
}

/**
 * Backwards Compatibility Function
 *
 * @since XOOPS
 * @version $Id$
 * @deprecated
 * @see icms_warning_msg
 *
 * @author The XOOPS Project <http://www.xoops.org>
 * @author Gustavo Pilla (aka nekro) <nekro@impresscms.org>
 *
 * @param string $msg
 * @param string $title
 */
function xoops_warning($msg, $title=''){ icms_warning_msg($msg, $title, true); }

/**
 * Render result message (echo, so no return string)
 * @param string $msg
 * @param string $title
 */
function xoops_result($msg, $title='')
{
	echo '<div class="resultMsg">';
	if($title != '') {echo '<h4>'.$title.'</h4>';}
	if(is_array($msg))
	{
		foreach($msg as $m) {echo $m.'<br />';}
	}
	else {echo $msg;}
	echo '</div>';
}

/**
* Generates a confirm form
* 
* Will render (echo) the form so no return in this function
*
* @param array  $hiddens  Array of Hidden values
* @param string  $action  The Form action
* @param string  $msg  The message in the confirm form
* @param string  $submit  The text on the submit button
* @param bool  $addtoken  Whether or not to add a security token
*/
function xoops_confirm($hiddens, $action, $msg, $submit='', $addtoken = true)
{
	$submit = ($submit != '') ? trim($submit) : _SUBMIT;
	echo '<div class="confirmMsg">
			<h4>'.$msg.'</h4>
			<form method="post" action="'.$action.'">';
	foreach($hiddens as $name => $value)
	{
		if(is_array($value))
		{
			foreach($value as $caption => $newvalue) {echo '<input type="radio" name="'.$name.'" value="'.htmlspecialchars($newvalue).'" /> '.$caption;}
			echo '<br />';
		}
		else {echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';}
	}
	if($addtoken != false) {echo $GLOBALS['xoopsSecurity']->getTokenHTML();}
	echo '<input type="submit" name="confirm_submit" value="'.$submit.'" /> <input type="button" name="confirm_back" value="'._CANCEL.'" onclick="javascript:history.go(-1);" />
	</form></div>';
}

/**
* Deprecated, use {@link XoopsSecurity} class instead
**/
function xoops_refcheck($docheck=1) {return $GLOBALS['xoopsSecurity']->checkReferer($docheck);}

/**
* Get the timestamp based on the user settings
* 
* @param string  $time  String with time
* @param string  $timeoffset  The time offset string
* @return string  $usertimestamp  The generated user timestamp
*/
function xoops_getUserTimestamp($time, $timeoffset="")
{
	global $xoopsConfig, $xoopsUser;
	if($timeoffset == '')
	{
		if($xoopsUser) {$timeoffset = $xoopsUser->getVar('timezone_offset');}
		else {$timeoffset = $xoopsConfig['default_TZ'];}
	}
	$usertimestamp = intval($time) + (floatval($timeoffset) - $xoopsConfig['server_TZ'])*3600;
	return $usertimestamp;
}


/*
 * Function to display formatted times in user timezone
 * 
 * @param string  $time  String with time
 * @param string  $format  The time format based on PHP function format parameters
 * @param string  $timeoffset  The time offset string
 * @return string  $usertimestamp  The generated user timestamp
 */
function formatTimestamp($time, $format = "l", $timeoffset = null)
{
	global $xoopsConfig, $xoopsUser;
	
	$format_copy = $format;
	$format = strtolower($format);
	
	if ($format == "rss" || $format == "r"){
		$TIME_ZONE = "";
		if (!empty($GLOBALS['xoopsConfig']['server_TZ'])){
			$server_TZ = abs(intval($GLOBALS['xoopsConfig']['server_TZ'] * 3600.0));
			$prefix = ($GLOBALS['xoopsConfig']['server_TZ'] < 0) ?  " -" : " +";
			$TIME_ZONE = $prefix.date("Hi", $server_TZ);
		}
		$date = gmdate("D, d M Y H:i:s", intval($time)) . $TIME_ZONE;
		return $date;
	}
	
	if ( ($format == "elapse" || $format == "e") && $time < time() ) {
		$elapse = time() - $time;
		if ( $days = floor( $elapse / (24 * 3600) ) ) {
			$num = $days > 1 ? sprintf(_DAYS, $days) : _DAY;
		} elseif ( $hours = floor( ( $elapse % (24 * 3600) ) / 3600 ) ) {
			$num = $hours > 1 ? sprintf(_HOURS, $hours) : _HOUR;
		} elseif ( $minutes = floor( ( $elapse % 3600 ) / 60 ) ) {
			$num = $minutes > 1 ? sprintf(_MINUTES, $minutes) : _MINUTE;
		} else {
			$seconds = $elapse % 60;
			$num = $seconds > 1 ? sprintf(_SECONDS, $seconds) : _SECOND;
		}
		$ret = sprintf(_ELAPSE, icms_conv_nr2local($num));
		return $ret;
	}
	
	// disable user timezone calculation and use default timezone,
	// for cache consideration
	if ($timeoffset === null) {
		$timeoffset = ($xoopsConfig['default_TZ'] == '') ? '0.0' : $xoopsConfig['default_TZ'];
	}
	
	$usertimestamp = xoops_getUserTimestamp($time, $timeoffset);
	
	switch ($format) {
		case 'daynumber':
		$datestring = 'd';
		break;
		case 'D':
		$datestring = 'D';
		break;
		case 'F':
		$datestring = 'F';
		break;
		case 'hs':
		$datestring = 'h';
		break;
		case 'H':
		$datestring = 'H';
		break;
		case 'gg':
		$datestring = 'g';
		break;
		case 'G':
		$datestring = 'G';
		break;
		case 'i':
		$datestring = 'i';
		break;
		case 'j':
		$datestring = 'j';
		break;
		case 'l':
		$datestring = _DATESTRING;
		break;
		case 'm':
		$datestring = _MEDIUMDATESTRING;
		break;
		case 'monthnr':
		$datestring = 'm';
		break;
		case 'mysql':
		$datestring = 'Y-m-d H:i:s';
		break;
		case 'month':
		$datestring = 'M';
		break;
		case 'n':
		$datestring = 'n';
		break;
		case 's':
		$datestring = _SHORTDATESTRING;
		break;
		case 'seconds':
		$datestring = 's';
		break;
		case 'suffix':
		$datestring = 'S';
		break;
		case 't':
		$datestring = 't';
		break;
		case 'w':
		$datestring = 'w';
		break;
		case 'shortyear':
		$datestring = 'y';
		break;
		case 'Y':
		$datestring = 'Y';
		break;
		case 'c':
		case 'custom':
		static $current_timestamp, $today_timestamp, $monthy_timestamp;
		if (!isset($current_timestamp)) {
			$current_timestamp = xoops_getUserTimestamp(time(), $timeoffset);
		}
		if (!isset($today_timestamp)) {
			$today_timestamp = mktime(0, 0, 0, date("m", $current_timestamp), date("d", $current_timestamp), date("Y", $current_timestamp));
		}
		
		if ( abs($elapse_today = $usertimestamp - $today_timestamp) < 24*60*60) {
			$datestring = ($elapse_today > 0) ? _TODAY : _YESTERDAY;
		} else {
			if (!isset($monthy_timestamp)) {
				$monthy_timestamp[0] = mktime(0, 0, 0, 0, 0, date("Y", $current_timestamp));
				$monthy_timestamp[1] = mktime(0, 0, 0, 0, 0, date("Y", $current_timestamp) + 1);
			}
			if ($usertimestamp >= $monthy_timestamp[0] && $usertimestamp < $monthy_timestamp[1]) {
				$datestring = _MONTHDAY;
			} else{
				$datestring = _YEARMONTHDAY;
			}
		}
		break;

		default:
			if ($format != '') {
				$datestring = $format_copy;
			} else {
				$datestring = _DATESTRING;
			}
		break;
	}

	$basecheck = $xoopsConfig['use_ext_date'] == 1 && defined ('_CALENDAR_TYPE') && $format != 'mysql';
	if($basecheck && file_exists(ICMS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/local.date.php'))
	{
		include_once ICMS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/local.date.php';
		return ucfirst(local_date($datestring,$usertimestamp));
	}elseif ($basecheck && _CALENDAR_TYPE != "jalali" && $xoopsConfig['language'] != 'english'){
		return ucfirst(icms_conv_nr2local(ext_date($datestring,$usertimestamp)));
	}elseif ($basecheck && _CALENDAR_TYPE == "jalali"){
		return ucfirst(icms_conv_nr2local(jdate($datestring,$usertimestamp)));
	}else{
		return ucfirst(date($datestring,$usertimestamp));
	}
}

/*
 * Function to calculate server timestamp from user entered time (timestamp)
 * 
 * @param string  $timestamp  String with time
 * @return string  $timestamp  The generated timestamp
 */
function userTimeToServerTime($timestamp, $userTZ=null)
{
	global $xoopsConfig;
	if(!isset($userTZ)) {$userTZ = $xoopsConfig['default_TZ'];}
	$timestamp = $timestamp - (($userTZ - $xoopsConfig['server_TZ']) * 3600);
	return $timestamp;
}

/*
* Function to generate password
* 
* @return string  $makepass  The generated password
*/
function xoops_makepass() {
	$makepass = '';
	$syllables = array("er","in","tia","wol","fe","pre","vet","jo","nes","al","len","son","cha","ir","ler","bo","ok","tio","nar","sim","ple","bla","ten","toe","cho","co","lat","spe","ak","er","po","co","lor","pen","cil","li","ght","wh","at","the","he","ck","is","mam","bo","no","fi","ve","any","way","pol","iti","cs","ra","dio","sou","rce","sea","rch","pa","per","com","bo","sp","eak","st","fi","rst","gr","oup","boy","ea","gle","tr","ail","bi","ble","brb","pri","dee","kay","en","be","se");
	srand((double)microtime()*1000000);
	for($count = 1; $count <= 4; $count++)
	{
		if(rand()%10 == 1) {$makepass .= sprintf("%0.0f",(rand()%50)+1);}
		else {$makepass .= sprintf("%s",$syllables[rand()%62]);}
	}
	return $makepass;
}


/*
* Function to display dhtml loading image box
*/
function OpenWaitBox()
{
	echo "<div id='waitDiv' style='position:absolute;left:40%;top:50%;visibility:hidden;text-align: center;'>
	<table cellpadding='6' border='2' class='bg2'>
		<tr>
		<td align='center'><b><big>" ._FETCHING."</big></b><br /><img src='".ICMS_URL."/images/await.gif' alt='' /><br />" ._PLEASEWAIT."</td>
		</tr>
	</table>
	</div>
	<script type='text/javascript'>
	<!--//
	var DHTML = (document.getElementById || document.all || document.layers);
	function ap_getObj(name) {
		if (document.getElementById) {
			return document.getElementById(name).style;
		} else if (document.all) {
			return document.all[name].style;
		} else if (document.layers) {
			return document.layers[name];
		}
	}
	function ap_showWaitMessage(div,flag)  {
		if (!DHTML) {
			return;
		}
		var x = ap_getObj(div);
		x.visibility = (flag) ? 'visible' : 'hidden';
		if (!document.getElementById) {
			if (document.layers) {
				x.left=280/2;
			}
		}
		return true;
	}
	ap_showWaitMessage('waitDiv', 1);
	//-->
	</script>";
}

/*
* Function to display the finish of the dhtml wait box
* 
*/
function CloseWaitBox()
{
	echo "<script type='text/javascript'>
	<!--//
	ap_showWaitMessage('waitDiv', 0);
	//-->
	</script>
	";
}

/*
* Checks if email is of correct formatting
* 
* @param string  $email  The email address
* @param string  $antispam  Generate an email address that is protected from spammers
* @return string  $email  The generated email address
*/
function checkEmail($email,$antispam = false)
{
	if(!$email || !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i",$email)) {return false;}
	if($antispam)
	{
		$email = str_replace('@', ' at ', $email);
		$email = str_replace('.', ' dot ', $email);
	}
	return $email;
}

/*
* Format an URL
* 
* @param string  $url  The URL to format
* @return string  $url The generated URL
*/
function formatURL($url)
{
	$url = trim($url);
	if($url != '')
	{
		if((!preg_match("/^http[s]*:\/\//i", $url)) && (!preg_match("/^ftp*:\/\//i", $url)) && (!preg_match("/^ed2k*:\/\//i", $url))) {$url = 'http://'.$url;}
	}
	return $url;
}

/*
* Function to display banners in all pages
*/
function showbanner() {echo xoops_getbanner();}

/*
* Gets banner HTML for use in templates
* 
* @return object  $bannerobject  The generated banner HTML string
*/
function xoops_getbanner()
{
	global $xoopsConfig;
	$db =& Database::getInstance();
	$bresult = $db->query("SELECT COUNT(*) FROM ".$db->prefix('banner'));
	list($numrows) = $db->fetchRow($bresult);
	if($numrows > 1)
	{
		$numrows = $numrows-1;
		mt_srand((double)microtime()*1000000);
		$bannum = mt_rand(0, $numrows);
	}
	else {$bannum = 0;}
	if($numrows > 0)
	{
		$bresult = $db->query("SELECT * FROM ".$db->prefix('banner'), 1, $bannum);
		list($bid, $cid, $imptotal, $impmade, $clicks, $imageurl, $clickurl, $date, $htmlbanner, $htmlcode) = $db->fetchRow($bresult);
		if($xoopsConfig['my_ip'] == xoops_getenv('REMOTE_ADDR')) {}
		else {$db->queryF(sprintf("UPDATE %s SET impmade = impmade+1 WHERE bid = '%u'", $db->prefix('banner'), intval($bid)));}
		/* Check if this impression is the last one and print the banner */
		if($imptotal == $impmade)
		{
			$newid = $db->genId($db->prefix('bannerfinish').'_bid_seq');
			$sql = sprintf("INSERT INTO %s (bid, cid, impressions, clicks, datestart, dateend) VALUES ('%u', '%u', '%u', '%u', '%u', '%u')", $db->prefix('bannerfinish'), intval($newid), intval($cid), intval($impmade), intval($clicks), intval($date), time());
			$db->queryF($sql);
			$db->queryF(sprintf("DELETE FROM %s WHERE bid = '%u'", $db->prefix('banner'), intval($bid)));
		}
		if($htmlbanner) {$bannerobject = $htmlcode;}
		else
		{
			$bannerobject = '<div><a href="'.ICMS_URL.'/banners.php?op=click&amp;bid='.$bid.'" rel="external">';
			if(stristr($imageurl, '.swf'))
			{
				$bannerobject = $bannerobject
					.'<object type="application/x-shockwave-flash" data="'.$imageurl.'" width="468" height="60">'
					.'<param name="movie" value="'.$imageurl.'"></param>'
					.'<param name="quality" value="high"></param>'
					.'</object>';
			}
			else {$bannerobject = $bannerobject.'<img src="'.$imageurl.'" alt="" />';}
			$bannerobject = $bannerobject.'</a></div>';
		}
		return $bannerobject;
	}
}

/*
* Function to redirect a user to certain pages
* 
* @param string  $url  The URL to redirect to
* @param int  $time  The time it takes to redirect to the URL
* @param string  $message  The message to show while redirecting
* @param bool  $addredirect  Add a link to the redirect URL?
* @param string  $allowExternalLink  Allow external links
*/
function redirect_header($url, $time = 3, $message = '', $addredirect = true, $allowExternalLink = false)
{
	global $xoopsConfig, $xoopsLogger, $xoopsUserIsAdmin;
    $config_handler =& xoops_gethandler('config');
    $imConfigPersona =& $config_handler->getConfigsByCat(XOOPS_CONF_PERSONA);
	if(preg_match("/[\\0-\\31]|about:|script:/i", $url))
	{
		if(preg_match('/^\b(java)?script:([\s]*)history\.go\(-[0-9]*\)([\s]*[;]*[\s]*)$/si', $url)) {$url = ICMS_URL;}
	}
	if(!$allowExternalLink && $pos = strpos($url, '://' ))
	{
		$xoopsLocation = substr(ICMS_URL, strpos(ICMS_URL, '://') + 3);
		if(substr($url, $pos + 3, strlen($xoopsLocation)) != $xoopsLocation) {$url = ICMS_URL;}
		elseif(substr($url, $pos + 3, strlen($xoopsLocation)+1) == $xoopsLocation.'.') {$url = ICMS_URL;}
	}
	$theme = $xoopsConfig['theme_set'];
	// if the user selected a theme in the theme block, let's use this theme
	if(isset($_SESSION['xoopsUserTheme']) && in_array($_SESSION['xoopsUserTheme'], $xoopsConfig['theme_set_allowed'])) {$theme = $_SESSION['xoopsUserTheme'];}

	require_once ICMS_ROOT_PATH.'/class/template.php';
   	require_once ICMS_ROOT_PATH.'/class/theme.php';

	$xoopsThemeFactory =& new xos_opal_ThemeFactory();
	$xoopsThemeFactory->allowedThemes = $xoopsConfig['theme_set_allowed'];
	$xoopsThemeFactory->defaultTheme = $theme;
	$xoTheme =& $xoopsThemeFactory->createInstance(array("plugins" => array()));
	$xoopsTpl =& $xoTheme->template;
	$xoopsTpl->assign(array(
		'icms_style' => ICMS_URL.'/icms'.(( defined('_ADM_USE_RTL') && _ADM_USE_RTL )?'_rtl':'').'.css',
		'icms_theme' => $theme,
		'icms_imageurl' => ICMS_THEME_URL.'/'.$theme.'/',
		'icms_themecss'=> xoops_getcss($theme),
		'icms_requesturi' => htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES),
		'icms_sitename' => htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES),
		'icms_slogan' => htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES),
		'icms_dirname' => @$xoopsModule ? $xoopsModule->getVar('dirname') : 'system',
		'icms_banner' => $xoopsConfig['banners'] ? xoops_getbanner() : '&nbsp;',
		'icms_pagetitle' => isset($xoopsModule) && is_object($xoopsModule) ? $xoopsModule->getVar('name') : htmlspecialchars( $xoopsConfig['slogan'], ENT_QUOTES),
		'xoops_theme' => $theme,
		'xoops_imageurl' => ICMS_THEME_URL.'/'.$theme.'/',
		'xoops_themecss'=> xoops_getcss($theme),
		'xoops_requesturi' => htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES),
		'xoops_sitename' => htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES),
		'xoops_slogan' => htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES),
		'xoops_dirname' => @$xoopsModule ? $xoopsModule->getVar('dirname') : 'system',
		'xoops_banner' => $xoopsConfig['banners'] ? xoops_getbanner() : '&nbsp;',
		'xoops_pagetitle' => isset($xoopsModule) && is_object($xoopsModule) ? $xoopsModule->getVar('name') : htmlspecialchars( $xoopsConfig['slogan'], ENT_QUOTES),
	));

	if($xoopsConfig['debug_mode'] == 2 && $xoopsUserIsAdmin)
	{
		$xoopsTpl->assign('time', 300);
		$xoopsTpl->assign('xoops_logdump', $xoopsLogger->dump());
	}
	else {$xoopsTpl->assign('time', intval($time));}
	if(!empty($_SERVER['REQUEST_URI']) && $addredirect && strstr($url, 'user.php'))
	{
		if(!strstr($url, '?')) {$url .= '?xoops_redirect='.urlencode($_SERVER['REQUEST_URI']);}
		else {$url .= '&amp;xoops_redirect='.urlencode($_SERVER['REQUEST_URI']);}
	}
	if(defined('SID') && SID && (!isset($_COOKIE[session_name()]) || ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '' && !isset($_COOKIE[$xoopsConfig['session_name']]))))
	{
		if(!strstr($url, '?')) {$url .= '?' . SID;}
		else {$url .= '&amp;'.SID;}
	}
	$url = preg_replace("/&amp;/i", '&', htmlspecialchars($url, ENT_QUOTES));
	$xoopsTpl->assign('url', $url);
	$message = trim($message) != '' ? $message : _TAKINGBACK;
	$xoopsTpl->assign('message', $message);
	$xoopsTpl->assign('lang_ifnotreload', sprintf(_IFNOTRELOAD, $url));
    // GIJ start
    if( ! headers_sent() && $imConfigPersona['use_custom_redirection']==1) {
        $_SESSION['redirect_message'] = $message ;
        header( "Location: ".preg_replace("/[&]amp;/i",'&',$url) ) ;
        exit();
    }else{
        $xoopsTpl->display('db:system_redirect.html');
    }
    // GIJ end
}

/*
* Gets environment key from the $_SERVER or $_ENV superglobal
* 
* @param string  $key  The key to get
* @return string  $ret  The retrieved key
*/
function xoops_getenv($key)
{
	$ret = '';
	if(array_key_exists($key, $_SERVER) && isset($_SERVER[$key]))
	{
		$ret = $_SERVER[$key];
		return $ret;
	}
	if(array_key_exists($key, $_ENV) && isset($_ENV[$key]))
	{
		$ret = $_ENV[$key];
		return $ret;
	}
	return $ret;
}

/*
* This function is deprecated. Do not use!
*/
function getTheme() {return $GLOBALS['xoopsConfig']['theme_set'];}

/*
* Function to get css file for a certain theme
* This function will be deprecated.
*/
function getcss($theme = '') {return xoops_getcss($theme);}

/*
* Function to get css file for a certain themeset
* 
* @param string  $theme  The theme set from the config
* @return mixed  The generated theme HTML string or an empty string
*/
function xoops_getcss($theme = '')
{
	if($theme == '') {$theme = $GLOBALS['xoopsConfig']['theme_set'];}
	$uagent = xoops_getenv('HTTP_USER_AGENT');
	if(stristr($uagent, 'mac')) {$str_css = 'styleMAC.css';}
	elseif(preg_match("/MSIE ([0-9]\.[0-9]{1,2})/i", $uagent)) {$str_css = 'style.css';}
	else {$str_css = 'styleNN.css';}
	if(is_dir(ICMS_THEME_PATH.'/'.$theme))
	{
		if(file_exists(ICMS_THEME_PATH.'/'.$theme.'/'.$str_css)) {return ICMS_THEME_URL.'/'.$theme.'/'.$str_css;}
		elseif(file_exists(ICMS_THEME_PATH.'/'.$theme.'/style.css')) {return ICMS_THEME_URL.'/'.$theme.'/style.css';}
	}
	if(is_dir(ICMS_THEME_PATH.'/'.$theme.'/css'))
	{
		if(file_exists(ICMS_THEME_PATH.'/'.$theme.'/css/'.$str_css)) {return ICMS_THEME_URL.'/'.$theme.'/css/'.$str_css;}
		elseif(file_exists(ICMS_THEME_PATH.'/'.$theme.'/css/style.css')) {return ICMS_THEME_URL.'/'.$theme.'/css/style.css';}
	}
	return '';
}

/*
* Gets Mailer object
* 
* @return		object  $inst  Reference to the (@link XoopsMailerLocal) or (@link XoopsMailer) object
*/
function &getMailer()
{
	global $xoopsConfig;
	$inst = false;
	include_once ICMS_ROOT_PATH.'/class/xoopsmailer.php';
    icms_loadLanguageFile('core', 'xoopsmailerlocal');
	if(class_exists('XoopsMailerLocal')) {$inst =& new XoopsMailerLocal();}
	if(!$inst) {$inst =& new XoopsMailer();}
	return $inst;
}

/*
* Gets the handler for a class
* 
* @param string  $name  The name of the handler to get
* @param bool  $optional	Is the handler optional?
* @return		object		$inst		The instance of the object that was created
*/
function &xoops_gethandler($name, $optional = false )
{
	static $handlers;
	$name = strtolower(trim($name));
	if(!isset($handlers[$name]))
	{
		if(file_exists($hnd_file = ICMS_ROOT_PATH.'/kernel/'.$name.'.php')) {require_once $hnd_file;}
		else
		{
			if(file_exists($hnd_file = ICMS_ROOT_PATH.'/class/'.$name.'.php')) {require_once $hnd_file;}
		}
		$class = 'Xoops'.ucfirst($name).'Handler';
		if(class_exists($class)) {$handlers[$name] =& new $class($GLOBALS['xoopsDB']);}
		else
		{
			$class = 'Icms'.ucfirst($name).'Handler';
			if(class_exists($class)) {$handlers[$name] =& new $class($GLOBALS['xoopsDB']);}
		}
	}
	if(!isset($handlers[$name]) && !$optional) {trigger_error(sprintf(_CORE_COREHANDLER_NOTAVAILABLE, $class, $name), E_USER_ERROR);}
	if(isset($handlers[$name])) {return $handlers[$name];}
	$inst = false;
	return $inst;
}

/*
* Gets module handler
* 
* @param	string  $name  The name of the module
* @param	string	$module_dir		The module directory where to get the module class
* @return	object  $inst	The reference to the generated object
*/
function &xoops_getmodulehandler($name = null, $module_dir = null, $optional = false)
{
	static $handlers;
	// if $module_dir is not specified
	if(!isset($module_dir))
	{
		//if a module is loaded
		if(isset($GLOBALS['xoopsModule']) && is_object($GLOBALS['xoopsModule'])) {$module_dir = $GLOBALS['xoopsModule']->getVar('dirname');}
		else {trigger_error(_CORE_NOMODULE, E_USER_ERROR);}
	}
	else {$module_dir = trim($module_dir);}
	$name = (!isset($name)) ? $module_dir : trim($name);
	if(!isset($handlers[$module_dir][$name]))
	{
		if($module_dir != 'system') {$hnd_file = ICMS_ROOT_PATH."/modules/{$module_dir}/class/{$name}.php";}
		else {$hnd_file = ICMS_ROOT_PATH."/modules/{$module_dir}/admin/{$name}/class/{$name}.php";}
		if(file_exists($hnd_file)) {include_once $hnd_file;}
		$class = ucfirst(strtolower($module_dir)).ucfirst($name).'Handler';
		if(class_exists($class)) {$handlers[$module_dir][$name] =& new $class($GLOBALS['xoopsDB']);}
	}
	if(!isset($handlers[$module_dir][$name]) && !$optional)
	{
		trigger_error(sprintf(_CORE_MODULEHANDLER_NOTAVAILABLE, $module_dir, $name), E_USER_ERROR);
	}
	if(isset($handlers[$module_dir][$name])) {return $handlers[$module_dir][$name];}
	$inst = false;
	return $inst;
}

/*
* Gets rank
* 
* @param	int  $rank_id  The Rank ID to get
* @param 	int	 $posts		The number of posts to match for the rank
* @return	array	$rank		The fetched rank array
*/
function xoops_getrank($rank_id =0, $posts = 0)
{
	$db =& Database::getInstance();
	$myts =& MyTextSanitizer::getInstance();
	$rank_id = intval($rank_id);
	$posts = intval($posts);
	if($rank_id != 0)
	{
		$sql = "SELECT rank_title AS title, rank_image AS image FROM ".$db->prefix('ranks')." WHERE rank_id = '".$rank_id."'";
	}
	else
	{
		$sql = "SELECT rank_title AS title, rank_image AS image FROM ".$db->prefix('ranks')." WHERE rank_min <= '".$posts."' AND rank_max >= '".$posts."' AND rank_special = '0'";
	}
	$rank = $db->fetchArray($db->query($sql));
	$rank['title'] = $myts->makeTboxData4Show($rank['title']);
	$rank['id'] = $rank_id;
	return $rank;
}

/**
* Function maintained only for compatibility
*
* @todo Search all places that this function is called
*       and rename it to icms_substr.
*       After this function can be removed.
*
*/
function xoops_substr($str, $start, $length, $trimmarker = '...')
{
	return icms_substr($str, $start, $length, $trimmarker);
}

/**
* Returns the portion of string specified by the start and length parameters.
* If $trimmarker is supplied, it is appended to the return string.
* This function works fine with multi-byte characters if mb_* functions exist on the server.
*
* @param    string    $str
* @param    int       $start
* @param    int       $length
* @param    string    $trimmarker
*
* @return   string
*/
function icms_substr($str, $start, $length, $trimmarker = '...')
{
	$config_handler =& xoops_gethandler('config');
	$im_multilanguageConfig =& $config_handler->getConfigsByCat(IM_CONF_MULILANGUAGE);

	if($im_multilanguageConfig['ml_enable'])
	{
		$tags = explode(',',$im_multilanguageConfig['ml_tags']);
		$strs = array();
		$hasML = false;
		foreach($tags as $tag)
		{
			if(preg_match("/\[".$tag."](.*)\[\/".$tag."\]/sU",$str,$matches))
			{
				if(count($matches) > 0)
				{
					$hasML = true;
					$strs[] = $matches[1];
				}
			}
		}
	}
	else {$hasML = false;}

	if(!$hasML) {$strs = array($str);}

	for($i = 0; $i <= count($strs)-1; $i++)
	{
		if(!XOOPS_USE_MULTIBYTES)
		{
			$strs[$i] = (strlen($strs[$i]) - $start <= $length) ? substr($strs[$i], $start, $length) : substr($strs[$i], $start, $length - strlen($trimmarker)).$trimmarker;
		}
		if(function_exists('mb_internal_encoding') && @mb_internal_encoding(_CHARSET))
		{
			$str2 = mb_strcut($strs[$i] , $start , $length - strlen($trimmarker));
			$strs[$i] = $str2.(mb_strlen($strs[$i])!=mb_strlen($str2) ? $trimmarker : '');
		}

		$DEP_CHAR=127;
		$pos_st=0;
		$action = false;
		for($pos_i = 0; $pos_i < strlen($strs[$i]); $pos_i++ )
		{
			if(ord(substr($strs[$i], $pos_i, 1)) > 127) {$pos_i++;}
			if($pos_i<=$start) {$pos_st=$pos_i;}
			if($pos_i>=$pos_st+$length)
			{
				$action = true;
				break;
			}
		}
		$strs[$i] = ($action) ? substr($strs[$i], $pos_st, $pos_i - $pos_st - strlen($trimmarker)).$trimmarker : $strs[$i];
		$strs[$i] = ($hasML)?'['.$tags[$i].']'.$strs[$i].'[/'.$tags[$i].']':$strs[$i];
	}
	$str = implode('',$strs);
	return $str;
}

// RMV-NOTIFY
// ################ Notification Helper Functions ##################
/*
* We want to be able to delete by module, by user, or by item.
* How do we specify this??
* 
* @param	int  $module_id	The ID of the module to unsubscribe from
* @return	bool	Did the unsubscribing succeed?
*/
function xoops_notification_deletebymodule ($module_id)
{
	$notification_handler =& xoops_gethandler('notification');
	return $notification_handler->unsubscribeByModule ($module_id);
}

/**
* Deletes / unsubscribes by user ID
* 
* @param	int  $user_id	The User ID to unsubscribe
* @return	bool	Did the unsubscribing succeed?
*/
function xoops_notification_deletebyuser ($user_id)
{
	$notification_handler =& xoops_gethandler('notification');
	return $notification_handler->unsubscribeByUser ($user_id);
}

/**
* Deletes / unsubscribes by Item ID
* 
* @param	int  $module_id	The Module ID to unsubscribe
* @param	int  $category	The Item ID to unsubscribe
* @param	int  $item_id	The Item ID to unsubscribe
* @return	bool	Did the unsubscribing succeed?
*/
function xoops_notification_deletebyitem ($module_id, $category, $item_id)
{
	$notification_handler =& xoops_gethandler('notification');
	return $notification_handler->unsubscribeByItem ($module_id, $category, $item_id);
}

// ################### Comment helper functions ####################
/**
* Count the comments belonging to a certain item in a certain module
* 
* @param	int  $module_id	The Module ID to count the comments for
* @param	int  $item_id	The Item ID to count the comments for
* @return	int	The number of comments
*/
function xoops_comment_count($module_id, $item_id = null)
{
	$comment_handler =& xoops_gethandler('comment');
	$criteria = new CriteriaCompo(new Criteria('com_modid', intval($module_id)));
	if(isset($item_id)) {$criteria->add(new Criteria('com_itemid', intval($item_id)));}
	return $comment_handler->getCount($criteria);
}

/**
* Delete the comments belonging to a certain item in a certain module
* 
* @param	int  $module_id	The Module ID to delete the comments for
* @param	int  $item_id	The Item ID to delete the comments for
* @return	bool	Did the deleting of the comments succeed?
*/
function xoops_comment_delete($module_id, $item_id)
{
	if(intval($module_id) > 0 && intval($item_id) > 0)
	{
		$comment_handler =& xoops_gethandler('comment');
		$comments =& $comment_handler->getByItemId($module_id, $item_id);
		if(is_array($comments))
		{
			$count = count($comments);
			$deleted_num = array();
			for($i = 0; $i < $count; $i++)
			{
				if(false != $comment_handler->delete($comments[$i]))
				{
					// store poster ID and deleted post number into array for later use
					$poster_id = $comments[$i]->getVar('com_uid');
					if($poster_id != 0) {$deleted_num[$poster_id] = !isset($deleted_num[$poster_id]) ? 1 : ($deleted_num[$poster_id] + 1);}
				}
			}
			$member_handler =& xoops_gethandler('member');
			foreach($deleted_num as $user_id => $post_num)
			{
				// update user posts
				$com_poster = $member_handler->getUser($user_id);
				if(is_object($com_poster)) {$member_handler->updateUserByField($com_poster, 'posts', $com_poster->getVar('posts') - $post_num);}
			}
			return true;
		}
	}
	return false;
}

// ################ Group Permission Helper Functions ##################
/**
* Deletes group permissions by module and item id
* 
* @param	int  $module_id	The Module ID to delete the permissions for
* @param	string  $perm_name	The permission name (for the module_id and item_id to delete
* @param	int  $item_id	The Item ID to delete the permissions for
* @return	int	Did the deleting of the group permissions succeed?
*/
function xoops_groupperm_deletebymoditem($module_id, $perm_name, $item_id = null)
{
	// do not allow system permissions to be deleted
	if(intval($module_id) <= 1) {return false;}
	$gperm_handler =& xoops_gethandler('groupperm');
	return $gperm_handler->deleteByModule($module_id, $perm_name, $item_id);
}

/**
* Converts text to UTF-8 encoded text
* 
* @param	string	$text	The Text to convert
* @return	string	$text	The converted text
*/
function xoops_utf8_encode(&$text)
{
	if(XOOPS_USE_MULTIBYTES == 1)
	{
		if(function_exists('mb_convert_encoding')) {return mb_convert_encoding($text, 'UTF-8', 'auto');}
		return $text;
	}
	return utf8_encode($text);
}

/**
* Converts text to UTF-8 encoded text
* @see xoops_utf8_encode
*/
function xoops_convert_encoding(&$text) {return xoops_utf8_encode($text);}

/**
* Converts text to UTF-8 encoded text
* 
* @param	int	$userid	The User ID
* @return	string	The linked username (from userID or "Anonymous")
*/
function xoops_getLinkedUnameFromId($userid)
{
	$userid = intval($userid);
	if($userid > 0)
	{
		$member_handler =& xoops_gethandler('member');
		$user =& $member_handler->getUser($userid);
		if(is_object($user))
		{
			$linkeduser = '<a href="'.ICMS_URL.'/userinfo.php?uid='.$userid.'">'.$user->getVar('uname').'</a>';
			return $linkeduser;
		}
    }
    return $GLOBALS['xoopsConfig']['anonymous'];
}

/**
* Trims certain text
* 
* @param	string	$text	The Text to trim
* @return	string	$text	The trimmed text
*/
function xoops_trim($text)
{
    if(function_exists('xoops_language_trim')) {return xoops_language_trim($text);}
    return trim($text);
}

/**
* Copy a file, or a folder and its contents
*
* @author	Aidan Lister <aidan@php.net>
* @param	string	$source    The source
* @param	string  $dest      The destination
* @return   bool    Returns true on success, false on failure
*/
function icms_copyr($source, $dest)
{
	// Simple copy for a file
	if(is_file($source)) {return copy($source, $dest);}
	// Make destination directory
	if(!is_dir($dest)) {mkdir($dest);}
	// Loop through the folder
	$dir = dir($source);
	while(false !== $entry = $dir->read())
	{
		// Skip pointers
		if($entry == '.' || $entry == '..') {continue;}
		// Deep copy directories
		if(is_dir("$source/$entry") && ($dest !== "$source/$entry")) {copy("$source/$entry", "$dest/$entry");}
		else {copy("$source/$entry", "$dest/$entry");}
	}
	// Clean up
	$dir->close();
	return true;
}

/**
* Create a folder
*
* @author	Newbb2 developpement team
* @param	string	$target    folder being created
* @return   bool    Returns true on success, false on failure
*/
function icms_mkdir($target)
{
	// http://www.php.net/manual/en/function.mkdir.php
	// saint at corenova.com
	// bart at cdasites dot com
	if(is_dir($target) || empty($target)) {return true;}
	if(file_exists($target) && !is_dir($target)) {return false;}
	if(icms_mkdir(substr($target, 0, strrpos($target, '/'))))
	{
		if(!file_exists($target))
		{
			$res = mkdir($target, 0777); // crawl back up & create dir tree
			icms_chmod($target);
			return $res;
		}
	}
	$res = is_dir($target);
	return $res;
}

/**
* Change the permission of a file or folder
*
* @author	Newbb2 developpement team
* @param	string	$target  target file or folder
* @param	int		$mode    permission
* @return   bool    Returns true on success, false on failure
*/
function icms_chmod($target, $mode = 0777) {return @chmod($target, $mode);}

/**
* Get the XoopsModule object of a specified module
*
* @param string $moduleName dirname of the module
* @return object XoopsModule object of the specified module
*/
function &icms_getModuleInfo($moduleName = false)
{
	static $icmsModules;
	if(isset($icmsModules[$moduleName]))
	{
		$ret =& $icmsModules[$moduleName];
		return $ret;
	}
	global $xoopsModule;
	if(!$moduleName)
	{
		if(isset($xoopsModule) && is_object($xoopsModule))
		{
			$icmsModules[$xoopsModule->getVar('dirname')] = & $xoopsModule;
			return $icmsModules[$xoopsModule->getVar('dirname')];
		}
	}
	if(!isset($icmsModules[$moduleName]))
	{
		if(isset($xoopsModule) && is_object($xoopsModule) && $xoopsModule->getVar('dirname') == $moduleName) {$icmsModules[$moduleName] = & $xoopsModule;}
		else
		{
			$hModule = & xoops_gethandler('module');
			if($moduleName != 'icms') {$icmsModules[$moduleName] = & $hModule->getByDirname($moduleName);}
			else {$icmsModules[$moduleName] = & $hModule->getByDirname('system');}
		}
	}
	return $icmsModules[$moduleName];
}

/**
* Get the config array of a specified module
*
* @param string $moduleName dirname of the module
* @return array of configs
*/
function &icms_getModuleConfig($moduleName = false)
{
	static $icmsConfigs;
	if(isset ($icmsConfigs[$moduleName]))
	{
		$ret = & $icmsConfigs[$moduleName];
		return $ret;
	}
	global $xoopsModule, $xoopsModuleConfig;
	if(!$moduleName)
	{
		if(isset($xoopsModule) && is_object($xoopsModule))
		{
			$icmsConfigs[$xoopsModule->getVar('dirname')] = & $xoopsModuleConfig;
			return $icmsConfigs[$xoopsModule->getVar('dirname')];
		}
	}
	// if we still did not found the xoopsModule, this is because there is none
	if(!$moduleName)
	{
		$ret = false;
		return $ret;
	}
	if(isset($xoopsModule) && is_object($xoopsModule) && $xoopsModule->getVar('dirname') == $moduleName) {$icmsConfigs[$moduleName] = & $xoopsModuleConfig;}
	else
	{
		$module = & icms_getModuleInfo($moduleName);
		if(!is_object($module))
		{
			$ret = false;
			return $ret;
		}
		$hModConfig = & xoops_gethandler('config');
		$icmsConfigs[$moduleName] = & $hModConfig->getConfigsByCat(0, $module->getVar('mid'));
	}
	return $icmsConfigs[$moduleName];
}

/**
* Get a specific module config value
*
* @param string $key
* @param string $moduleName
* @param mixed $default
* @return mixed
*/
function icms_getConfig($key, $moduleName = false, $default = 'default_is_undefined')
{
	if(!$moduleName) {$moduleName = icms_getCurrentModuleName();}
	$configs = icms_getModuleConfig($moduleName);
	if(isset($configs[$key])) {return $configs[$key];}
	else
	{
		if($default === 'default_is_undefined') {return null;}
		else {return $default;}
	}
}

/**
* Get the dirname of the current module
*
* @return mixed dirname of the current module or false if no module loaded
*/
function icms_getCurrentModuleName()
{
	global $xoopsModule;
	if(is_object($xoopsModule)) {return $xoopsModule->getVar('dirname');}
	else {return false;}
}

/**
* Checks if a user is admin of $module
*
* @param mixed	Module to check or false if no module is passed
* @return bool : true if user is admin
*/
function icms_userIsAdmin($module = false)
{
	global $xoopsUser;
	static $icms_isAdmin;
	if(!$module)
	{
		global $xoopsModule;
		$module = $xoopsModule->getVar('dirname');
	}
	if(isset ($icms_isAdmin[$module])) {return $icms_isAdmin[$module];}
	if(!$xoopsUser)
	{
		$icms_isAdmin[$module] = false;
		return $icms_isAdmin[$module];
	}
	$icms_isAdmin[$module] = false;
	$icmsModule = icms_getModuleInfo($module);
	if(!is_object($icmsModule)) {return false;}
	$module_id = $icmsModule->getVar('mid');
	$icms_isAdmin[$module] = $xoopsUser->isAdmin($module_id);
	return $icms_isAdmin[$module];
}

/**
* Load a module language file
*
* If $module = core, file will be loaded from ICMS_ROOT_PATH/language/
*
* @param string $module dirname of the module
* @param string $file name of the file without ".php"
* @param bool $admin is this for a core admin side feature ?
*/
function icms_loadLanguageFile($module, $file, $admin=false)
{
	global $xoopsConfig;
	if($module == 'core') {$languagePath = ICMS_ROOT_PATH.'/language/';}
	else {$languagePath = ICMS_ROOT_PATH.'/modules/'.$module.'/language/';}
	$extraPath = $admin ? 'admin/' : '';
	$filename = $languagePath.$xoopsConfig['language'].'/'.$extraPath.$file.'.php';
	if(!file_exists($filename)) {$filename = $languagePath.'english/'.$extraPath.$file.'.php';}
	if(file_exists($filename)) {include_once($filename);}
}

/**
* @author pillepop2003 at yahoo dot de
*
* Use this snippet to extract any float out of a string. You can choose how a single dot is treated with the (bool) 'single_dot_as_decimal' directive.
* This function should be able to cover almost all floats that appear in an european environment.
* 
* @param string $str	String to get float value from
* @param mixed	$set	Array of settings of False if no settings were passed
* @param mixed	Float value or 0 if no match was found in the string
*/
function icms_getfloat($str, $set=FALSE)
{
	if(preg_match("/([0-9\.,-]+)/", $str, $match))
	{
		// Found number in $str, so set $str that number
		$str = $match[0];
		if(strstr($str, ','))
		{
			// A comma exists, that makes it easy, cos we assume it separates the decimal part.
			$str = str_replace('.', '', $str); // Erase thousand seps
			$str = str_replace(',', '.', $str); // Convert , to . for floatval command
			return floatval($str);
		}
		else
		{
			// No comma exists, so we have to decide, how a single dot shall be treated
			if(preg_match("/^[0-9\-]*[\.]{1}[0-9-]+$/", $str) == TRUE && $set['single_dot_as_decimal'] == TRUE) {return floatval($str);}
			else
			{
				$str = str_replace('.', '', $str);    // Erase thousand seps
				return floatval($str);
			}
		}
	}
	else {return 0;}
}

/**
* Use this snippet to extract any currency out of a string
* 
* @param string $var	String to get currency value from
* @param mixed	$currencyObj	Currency object or false if no object was passed
* @return string	$ret The returned value
*/
function icms_currency($var, $currencyObj=false)
{
	$ret = icms_getfloat($var,  array('single_dot_as_decimal'=> TRUE));
	$ret = round($ret, 2);
	// make sure we have at least .00 in the $var
	$decimal_section_original = strstr($ret, '.');
	$decimal_section = $decimal_section_original;
	if($decimal_section)
	{
		if(strlen($decimal_section) == 1) {$decimal_section = '.00';}
		elseif(strlen($decimal_section) == 2) {$decimal_section = $decimal_section . '0';}
		$ret = str_replace($decimal_section_original, $decimal_section, $ret);
	}
	else {$ret = $ret . '.00';}
	if($currencyObj) {$ret = $ret.' '.$currencyObj->getCode();}
	return $ret;
}

/**
* Use this snippet to extract any currency out of a string
* 
* @see icms_currency
*/
function icms_float($var) {return icms_currency($var);}

/**
* Strip text from unwanted text (purify)
* 
* @param string $text	String to purify
* @param mixed	$keyword	The keyword string or false if none was passed
* @return string	$text The purified text
*/
function icms_purifyText($text, $keyword = false)
{
	$myts = MyTextsanitizer::getInstance();
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('<br />', ' ', $text);
	$text = str_replace('<br/>', ' ', $text);
	$text = str_replace('<br', ' ', $text);
	$text = strip_tags($text);
	$text = html_entity_decode($text);
	$text = $myts->undoHtmlSpecialChars($text);
	$text = str_replace(')', ' ', $text);
	$text = str_replace('(', ' ', $text);
	$text = str_replace(':', ' ', $text);
	$text = str_replace('&euro', ' euro ', $text);
	$text = str_replace('&hellip', '...', $text);
	$text = str_replace('&rsquo', ' ', $text);
	$text = str_replace('!', ' ', $text);
	$text = str_replace('?', ' ', $text);
	$text = str_replace('"', ' ', $text);
	$text = str_replace('-', ' ', $text);
	$text = str_replace('\n', ' ', $text);
	$text = str_replace('&#8213;', ' ', $text);

	if($keyword)
	{
		$text = str_replace('.', ' ', $text);
		$text = str_replace(',', ' ', $text);
		$text = str_replace('\'', ' ', $text);
	}
	$text = str_replace(';', ' ', $text);

	return $text;
}

/**
* Converts HTML to text equivalents
* 
* @param string $document	The document string to convert
* @return string	$text The converted text
*/
function icms_html2text($document)
{
	// PHP Manual:: function preg_replace
	// $document should contain an HTML document.
	// This will remove HTML tags, javascript sections
	// and white space. It will also convert some
	// common HTML entities to their text equivalent.
	// Credits : newbb2
	$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
	"'<img.*?/>'si",       // Strip out img tags
	"'<[\/\!]*?[^<>]*?>'si",          // Strip out HTML tags
	"'([\r\n])[\s]+'",                // Strip out white space
	"'&(quot|#34);'i",                // Replace HTML entities
	"'&(amp|#38);'i",
	"'&(lt|#60);'i",
	"'&(gt|#62);'i",
	"'&(nbsp|#160);'i",
	"'&(iexcl|#161);'i",
	"'&(cent|#162);'i",
	"'&(pound|#163);'i",
	"'&(copy|#169);'i",
	"'&#(\d+);'e");                    // evaluate as php

	$replace = array ("",
	"",
	"",
	"\\1",
	"\"",
	"&",
	"<",
	">",
	" ",
	chr(161),
	chr(162),
	chr(163),
	chr(169),
	"chr(\\1)");

	$text = preg_replace($search, $replace, $document);
	return $text;

}

// ----- New Password System
/**
* This Function checks whether a users password has been expired
*
* @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
*
* @since 1.1
* @param string	$uname		The username of the account to be checked
*
* @return int	returns 1 if password is expired, 0 if password is ot expired.
*/
function icms_passExpired($uname = '')
{
	$db =& Database::getInstance();
	if($uname !== '')
	{
	    	$sql = $db->query("SELECT uname, pass_expired FROM ".$db->prefix('users')." WHERE uname = '".@htmlspecialchars($uname, ENT_QUOTES, _CHARSET)."'");
		list($uname, $pass_expired) = $db->fetchRow($sql);
	}
	else	{redirect_header('user.php',2,_US_SORRYNOTFOUND);}
	return $pass_expired;
}
/**
* This Function creates a unique random Salt Key for use with password encryptions
* It can also be used to generate a random AlphaNumeric key sequence of any given length.
*
* @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
*
* @since 1.1
* @param string	$slength		The length of the key to produce
*
* @return string	returns the generated random key.
*/
function icms_createSalt($slength=64)
{
	$salt= '';
	$base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$microtime = function_exists('microtime') ? microtime() : time();
    	srand((double)$microtime * 1000000);
    	for($i=0; $i<=$slength; $i++)
		$salt.= substr($base, rand() % strlen($base), 1);
    	return $salt;
}

/**
* This Function returns the User Salt key belonging to username.
*
* @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
*
* @since 1.1
* @param string	$uname		Username to find User Salt key for..
*
* @return string	returns the Salt key of the user.
*/
function icms_getUserSaltFromUname($uname = '')
{
	$db =& Database::getInstance();
	if($uname !== '')
	{
		include_once ICMS_ROOT_PATH . '/class/database/databaseupdater.php';
		$table = new IcmsDatabasetable('users');
		if ($table->fieldExists('loginname')) {
			$sql = $db->query("SELECT loginname, salt FROM ".$db->prefix('users')." WHERE loginname = '".@htmlspecialchars($uname, ENT_QUOTES, _CHARSET)."'");
			list($loginname, $salt) = $db->fetchRow($sql);
		}elseif($table->fieldExists('login_name')){
			$sql = $db->query("SELECT login_name, salt FROM ".$db->prefix('users')." WHERE login_name = '".@htmlspecialchars($uname, ENT_QUOTES, _CHARSET)."'");
			list($login_name, $salt) = $db->fetchRow($sql);
		}else{
			$sql = $db->query("SELECT uname, salt FROM ".$db->prefix('users')." WHERE uname = '".@htmlspecialchars($uname, ENT_QUOTES, _CHARSET)."'");
			list($uname, $salt) = $db->fetchRow($sql);
		}
	}
	else	{redirect_header('user.php',2,_US_SORRYNOTFOUND);}
	return $salt;
}

/**
* This Function returns the Username of the account linked to the inputted email address
*
* @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
*
* @since 1.1
* @param string	$email		Email address to find username for.
*
* @return string	returns the username of the account linked to supplied email.
*/
function icms_getUnameFromUserEmail($email = '')
{
	$db =& Database::getInstance();
	if($email !== '')
	{
		include_once ICMS_ROOT_PATH . '/class/database/databaseupdater.php';
		$table = new IcmsDatabasetable('users');
		if (!$table->fieldExists('login_name')) {
			$sql = $db->query("SELECT uname, email FROM ".$db->prefix('users')." WHERE email = '".@htmlspecialchars($email, ENT_QUOTES, _CHARSET)."'");
			list($uname, $email) = $db->fetchRow($sql);
		}else{
			$sql = $db->query("SELECT login_name, email FROM ".$db->prefix('users')." WHERE email = '".@htmlspecialchars($email, ENT_QUOTES, _CHARSET)."'");
			list($uname, $email) = $db->fetchRow($sql);
		}
	}
	else	{redirect_header('user.php',2,_US_SORRYNOTFOUND);}
	return $uname;
}

/**
* This Function is used to Encrypt User Passwords
*
* @copyright (c) 2007-2008 The ImpressCMS Project - www.impresscms.org
*
* @since 1.1
* @param string	$pass		plaintext password to be encrypted
* @param string	$salt		unique user salt key used in encryption process
* @param int		$enc_type		encryption type to use (this is required & only used when passwords are expired)
* @param int		$reset		set to 1 if we have determined that the user password has been expired
*							use in conjunction only with $enc_type above.
*
* @return string	returns the final encrypted hash of users password.
*/
function icms_encryptPass($pass, $salt, $enc_type = 0, $reset = 0)
{
	$config_handler =& xoops_gethandler('config');
	$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);
	$mainSalt = XOOPS_DB_SALT;

	if($reset == 0) {$enc_type = intval($xoopsConfigUser['enc_type']);}
	if(function_exists('hash'))
	{
		if($enc_type == 0) {$pass = md5($pass);} // no salt used for compatibility with external scripts such as ipb/phpbb etc.
		elseif($enc_type == 1) {$pass = hash('sha256', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 2) {$pass = hash('sha384', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 3) {$pass = hash('sha512', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 4) {$pass = hash('ripemd128', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 5) {$pass = hash('ripemd160', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 6) {$pass = hash('whirlpool', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 7) {$pass = hash('haval128,4', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 8) {$pass = hash('haval160,4', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 9) {$pass = hash('haval192,4', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 10) {$pass = hash('haval224,4', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 11) {$pass = hash('haval256,4', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 12) {$pass = hash('haval128,5', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 13) {$pass = hash('haval160,5', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 14) {$pass = hash('haval192,5', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 15) {$pass = hash('haval224,5', $salt.md5($pass).$mainSalt);}
		elseif($enc_type == 16) {$pass = hash('haval256,5', $salt.md5($pass).$mainSalt);}
	}
	elseif(!function_exists('hash')) {$pass = md5($pass);} // no salt used for compatibility with external scripts such as ipb/phpbb etc. no idea why this was requested though, but not recommended to use.
	unset($mainSalt);
	return $pass;
}
// ----- End New Password System

/**
* Function to keeps the code clean while removing unwanted attributes and tags.
* This function was got from http://www.php.net/manual/en/function.strip-tags.php#81553
*
* @var $sSource - string - text to remove the tags
* @var $aAllowedTags - array - tags that dont will be striped
* @var $aDisabledAttributes - array - attributes not allowed, will be removed from the text
*
* @return string
*/
function icms_cleanTags($sSource, $aAllowedTags = array('<h1>','<b>','<u>','<a>','<ul>','<li>'), $aDisabledAttributes = array('onabort', 'onblur', 'onchange', 'onclick', 'ondblclick', 'onerror', 'onfocus', 'onkeydown', 'onkeyup', 'onload', 'onmousedown', 'onmousemove', 'onmouseover', 'onmouseup', 'onreset', 'onresize', 'onselect', 'onsubmit', 'onunload'))
{
	if(empty($aDisabledAttributes)) return strip_tags($sSource, implode('', $aAllowedTags));
	return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(".implode('|', $aDisabledAttributes).")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($sSource, implode('', $aAllowedTags)));
}

/**
* Store a cookie
*
* @param string $name name of the cookie
* @param string $value value of the cookie
* @param int $time duration of the cookie
*/
function icms_setCookieVar($name, $value, $time = 0)
{
	if($time == 0) {$time = time() + 3600 * 24 * 365;}
	setcookie($name, $value, $time, '/');
}

/**
* Get a cookie value
*
* @param string $name name of the cookie
* @param string $default value to return if cookie not found
*
* @return string value of the cookie or default value
*/
function icms_getCookieVar($name, $default = '')
{
	$name = str_replace('.', '_', $name);
	if((isset($_COOKIE[$name])) && ($_COOKIE[$name] > '')) {return $_COOKIE[$name];}
	else {return $default;}
}

/**
* Get URL of the page before the form to be able to redirect their after the form has been posted
*
* @return string url before form
*/
function icms_get_page_before_form()
{
	global $impresscms;
	return isset($_POST['icms_page_before_form']) ? $_POST['icms_page_before_form'] : $impresscms->urls['previouspage'];
}

/**
* Get URL of the page before the form to be able to redirect their after the form has been posted
*
* @param	array	$matches	Array of matches to sanitize
* @return mixed The sanitized tag or empty string
*/
function icms_sanitizeCustomtags_callback($matches)
{
	global $icms_customtag_handler;
	if(isset($icms_customtag_handler->objects[$matches[1]]))
	{
		$customObj = $icms_customtag_handler->objects[$matches[1]];
		$ret = $customObj->renderWithPhp();
		return $ret;
	}
	else {return '';}
}

/**
* Sanitizes custom tags
* 
* @param string $text	Purifies passed text
* @return string	$text The purified text
*/
function icms_sanitizeCustomtags($text)
{
	$patterns = array();
	$replacements = array();

	global $icms_customtag_handler;

	$patterns[] = '/\[customtag](.*)\[\/customtag\]/sU';
	$text = preg_replace_callback($patterns, 'icms_sanitizeCustomtags_callback', $text);
	return $text;
}

/**
* Return a linked username or full name for a specific $userid
*
* @param integer $userid uid of the related user
* @param bool $name true to return the fullname, false to use the username; if true and the user does not have fullname, username will be used instead
* @param array $users array already containing XoopsUser objects in which case we will save a query
* @param bool $withContact true if we want contact details to be added in the value returned (PM and email links)
* @return string name of user with a link on his profile
*/
function icms_getLinkedUnameFromId($userid, $name = false, $users = array (), $withContact = false)
{
	if(!is_numeric($userid)) {return $userid;}
	$userid = intval($userid);
	if($userid > 0)
	{
		if($users == array())
		{
			//fetching users
			$member_handler = & xoops_gethandler('member');
			$user = & $member_handler->getUser($userid);
		}
		else
		{
			if(!isset($users[$userid])) {return $GLOBALS['xoopsConfig']['anonymous'];}
			$user = & $users[$userid];
		}
		if(is_object($user))
		{
			$ts = & MyTextSanitizer::getInstance();
			$username = $user->getVar('uname');
			$fullname = '';
			$fullname2 = $user->getVar('name');
			if(($name) && !empty($fullname2)) {$fullname = $user->getVar('name');}
			if(!empty ($fullname)) {$linkeduser = "$fullname [<a href='".ICMS_URL."/userinfo.php?uid=".$userid."'>".$ts->htmlSpecialChars($username)."</a>]";}
			else {$linkeduser = "<a href='".ICMS_URL."/userinfo.php?uid=".$userid."'>".$ts->htmlSpecialChars($username)."</a>";}
			// add contact info : email + PM
			if($withContact)
			{
				$linkeduser .= '<a href="mailto:'.$user->getVar('email').'"><img style="vertical-align: middle;" src="'.ICMS_URL.'/images/icons/'.$GLOBALS["xoopsConfig"]["language"].'/email.gif'.'" alt="'._US_SEND_MAIL.'" title="'._US_SEND_MAIL.'"/></a>';
				$js = "javascript:openWithSelfMain('".ICMS_URL.'/pmlite.php?send2=1&to_userid='.$userid."', 'pmlite',450,370);";
				$linkeduser .= '<a href="'.$js.'"><img style="vertical-align: middle;" src="'.ICMS_URL.'/images/icons/'.$GLOBALS["xoopsConfig"]["language"].'/pm.gif'.'" alt="'._US_SEND_PM.'" title="'._US_SEND_PM.'"/></a>';
			}
			return $linkeduser;
		}
	}
	return $GLOBALS['xoopsConfig']['anonymous'];
}

/**
* Get an array of the table used in a module
*
* @param string $moduleName name of the module
* @param $items array of items managed by the module
* @return array of tables used in the module
*/
function icms_getTablesArray($moduleName, $items)
{
	$ret = array();
	foreach($items as $item) {$ret[] = $moduleName.'_'.$item;}
	return $ret;
}

/**
* Function to create a navigation menu in content pages.
* This function was based on the function that do the same in mastop publish module
*
* @param integer $id
* @param string $separador
* @param string $style
* @return string
*/
function showNav($id = null, $separador = '/', $style="style='font-weight:bold'")
{
	$url = ICMS_URL.'/content.php';
	if($id == false) {return false;}
	else
	{
		if($id > 0)
		{
			$content_handler =& xoops_gethandler('content');
			$cont = $content_handler->get($id);
			if($cont->getVar('content_id') > 0)
			{
				$seo = $content_handler->makeLink($cont);
				$ret = "<a href='".$url."?page=".$seo."'>".$cont->getVar('content_title')."</a>";
				if($cont->getVar('content_supid') == 0) {return "<a href='".ICMS_URL."'>"._CT_NAV."</a> $separador ".$ret;}
				elseif($cont->getVar('content_supid') > 0) {$ret = showNav($cont->getVar('content_supid'), $separador)." $separador ".$ret;}
			}
		}
		else {return false;}
	}
	return $ret;
}

/**
* Searches text for unwanted tags and removes them
* 
* @param string $text	String to purify
* @return string	$text The purified text
*/
function StopXSS($text)
{
	if(!is_array($text))
	{
		$text = preg_replace("/\(\)/si", "", $text);
		$text = strip_tags($text);
		$text = str_replace(array("\"",">","<","\\"), "", $text);
	}
	else
	{
		foreach($text as $k=>$t)
		{
			if (is_array($t)) {
				StopXSS($t);
			} else {
				$t = preg_replace("/\(\)/si", "", $t);
				$t = strip_tags($t);
				$t = str_replace(array("\"",">","<","\\"), "", $t);
				$text[$k] = $t;
			}
		}
	}
	return $text;
}

/**
* Purifies the CSS that is put in the content (pages) system
* 
* @param string $text	String to purify
* @return string	$text The purified text
*/
function icms_sanitizeContentCss($text)
{
	if(preg_match_all('/(.*?)\{(.*?)\}/ie',$text,$css))
	{
		$css = $css[0];
		$perm = $not_perm = array();
		foreach($css as $k=>$v)
		{
			if(!preg_match('/^\#impress_content(.*?)/ie',$v)) {$css[$k] = '#impress_content '.icms_cleanTags(trim($v),array())."\r\n";}
			else {$css[$k] = icms_cleanTags(trim($v),array())."\r\n";}
		}
		$text = implode($css);
	}
	return $text;
}

/**
* Function to get the base domain name from a URL.
* credit for this function should goto Phosphorus and Lime, it is released under LGPL.
*
* @param string $url the URL to be stripped.
* @return string
*/
function icms_get_base_domain($url)
{
	$debug = 0;
	$base_domain = '';

	// generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
	$G_TLD = array(
	'biz','com','edu','gov','info','int','mil','name','net','org','aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel',
	'arpa','root','berlin','bzh','cym','gal','geo','kid','kids','lat','mail','nyc','post','sco','web','xxx',
	'nato', 'example','invalid','localhost','test','bitnet','csnet','ip','local','onion','uucp','co');

	// country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
	$C_TLD = array(
	// active
	'ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az',
	'ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bw','by','bz',
	'ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz',
	'de','dj','dk','dm','do','dz','ec','ee','eg','er','es','et','eu','fi','fj','fk','fm','fo',
	'fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw',
	'gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je',
	'jm','jo','jp','ke','kg','kh','ki','km','kn','kr','kw','ky','kz','la','lb','lc','li','lk',
	'lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq',
	'mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np',
	'nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pn','pr','ps','pt','pw','py','qa',
	're','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si','sk','sl','sm','sn','sr','st',
	'sv','sy','sz','tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tr','tt','tv','tw',
	'tz','ua','ug','uk','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yu',
	'za','zm','zw',
	// inactive
	'eh','kp','me','rs','um','bv','gb','pm','sj','so','yt','su','tp','bu','cs','dd','zr');

	// get domain
	if(!$full_domain = icms_get_url_domain($url)) {return $base_domain;}

	// break up domain, reverse
	$DOMAIN = explode('.', $full_domain);
	if($debug) print_r($DOMAIN);
	$DOMAIN = array_reverse($DOMAIN);
	if($debug) print_r($DOMAIN);

	// first check for ip address
	if(count($DOMAIN) == 4 && is_numeric($DOMAIN[0]) && is_numeric($DOMAIN[3])) {return $full_domain;}

	// if only 2 domain parts, that must be our domain
	if(count($DOMAIN) <= 2) return $full_domain;

	/*
	finally, with 3+ domain parts: obviously D0 is tld now,
	if D0 = ctld and D1 = gtld, we might have something like com.uk so,
	if D0 = ctld && D1 = gtld && D2 != 'www', domain = D2.D1.D0 else if D0 = ctld && D1 = gtld && D2 == 'www',
	domain = D1.D0 else domain = D1.D0 - these rules are simplified below.
	*/
	if(in_array($DOMAIN[0], $C_TLD) && in_array($DOMAIN[1], $G_TLD) && $DOMAIN[2] != 'www')
	{
		$full_domain = $DOMAIN[2].'.'.$DOMAIN[1].'.'.$DOMAIN[0];
	}
	else
	{
		$full_domain = $DOMAIN[1].'.'.$DOMAIN[0];
	}
	// did we succeed?
	return $full_domain;
}

/**
* Function to get the domain from a URL.
* credit for this function should goto Phosphorus and Lime, it is released under LGPL.
*
* @param string $url the URL to be stripped.
* @return string
*/
function icms_get_url_domain($url)
{
	$domain = '';
	$_URL = parse_url($url);

	if(!empty($_URL) || !empty($_URL['host'])) {$domain = $_URL['host'];}
	return $domain;
}

/**
* Function to wordwrap given text.
*
* @param string $str 	The text to be wrapped.
* @param string $width The column width - text will be wrapped when longer than $width.
* @param string $break The line is broken using the optional break parameter.
*			can be '/n' or '<br />'
* @param string $cut 	If cut is set to TRUE, the string is always wrapped at the specified width.
*			So if you have a word that is larger than the given width, it is broken apart..
* @return string
*/
function icms_wordwrap($str, $width, $break = '/n', $cut = false)
{
	if(strtolower(_CHARSET) !== 'utf-8')
	{
		$str = wordwrap($str, $width, $break, $cut);
		return $str;
	}
	else
	{
		$splitedArray = array();
		$lines = explode("\n", $str);
		foreach($lines as $line)
		{
			$lineLength = strlen($line);
			if($lineLength > $width)
			{
				$words = explode("\040", $line);
				$lineByWords = '';
				$addNewLine = true;
				foreach($words as $word)
				{
					$lineByWordsLength = strlen($lineByWords);
					$tmpLine = $lineByWords.((strlen($lineByWords) !== 0) ? ' ' : '').$word;
					$tmplineByWordsLength = strlen($tmpLine);
					if($tmplineByWordsLength > $width && $lineByWordsLength <= $width && $lineByWordsLength !== 0)
					{
						$splitedArray[] = $lineByWords;
						$lineByWords = '';
					}
					$newLineByWords = $lineByWords.((strlen($lineByWords) !== 0) ? ' ' : '').$word;
					$newLineByWordsLength = strlen($newLineByWords);
					if($cut && $newLineByWordsLength > $width)
					{
						for($i = 0; $i < $newLineByWordsLength; $i = $i + $width) {$splitedArray[] = mb_substr($newLineByWords, $i, $width);}
						$addNewLine = false;
					}
					else	{$lineByWords = $newLineByWords;}
				}
				if($addNewLine) {$splitedArray[] = $lineByWords;}
			}
			else	{$splitedArray[] = $line;}
		}
		return implode($break, $splitedArray);
	}
}

/**
* Function to reverse given text with utf-8 character sets
*
* credit for this function should goto lwc courtesy of php.net.
*
* @param string $str		The text to be reversed.
* @param string $reverse	true will reverse everything including numbers, false will reverse text only but numbers will be left intact.
*				example: when true: impresscms 2008 > 8002 smcsserpmi, false: impresscms 2008 > 2008 smcsserpmi
* @return string
*/
function icms_utf8_strrev($str, $reverse = false)
{
	preg_match_all('/./us', $str, $ar);
	if($reverse) {return join('',array_reverse($ar[0]));}
	else
	{
		$temp = array();
		foreach($ar[0] as $value)
		{
			if(is_numeric($value) && !empty($temp[0]) && is_numeric($temp[0]))
			{
				foreach ($temp as $key => $value2)
				{
					if(is_numeric($value2)) {$pos = ($key + 1);}
					else {break;}
					$temp2 = array_splice($temp, $pos);
					$temp = array_merge($temp, array($value), $temp2);
				}
			}
			else {array_unshift($temp, $value);}
		}
		return implode('', $temp);
	}
}

/**
* Function to get a query from DB
* 
* @param object $db	Reference to the database object
* @param string	$table	The table to get the value from
* @param string	$field	The table to get the value from
* @param string	$condition	The where condition (where clause) to use
* @return	mixed	
*/
function getDbValue(&$db, $table, $field, $condition = '')
 {
	$table = $db->prefix( $table );
	$sql = "SELECT `$field` FROM `$table`";
	if($condition) {$sql .= " WHERE $condition";}
	$result = $db->query($sql);
	if($result)
	{
		$row = $db->fetchRow($result);
		if($row) {return $row[0];}
	}
	return false;
}

/**
* Function to escape $value makes safe for DB Queries.
*
* @param string $quotes - true/false - determines whether to add quotes to the value or not.
* @param string $value - $variable that is being escaped for query.
* @return string
*/
function icms_escapeValue($value, $quotes = true)
{
	if(is_string($value))
	{
		if(get_magic_quotes_gpc) {$value = stripslashes($value);}
		$value = mysql_real_escape_string($value);
		if($quotes) {$value = '"'.$value.'"';}
	}
	elseif($value === null) {$value = 'NULL';}
	elseif(is_bool($value)) {$value = $value ? 1 : 0;}
	elseif(is_numeric($value)) {$value = intval($value);}
	elseif(is_int($value)) {$value = intval($value);}
	elseif(!is_numeric($value))
	{
		$value = mysql_real_escape_string($value);
		if($quotes) {$value = '"'.$value.'"';}
	}
	return $value;
}

/**
 * Clean up all the writeable folders
 * @param bool
 */
function icms_cleaning_write_folders() {
	return icms_clean_folders(array('templates_c' => ICMS_ROOT_PATH."/templates_c/", 'cache' => ICMS_ROOT_PATH."/cache/"));
}

/**
* Get a number value in other languages
*
* @param int $string Content to be transported into another language
* @return string inout with replaced numeric values
*
* Example: In Persian we use, (۱, ۲, ۳, ۴, ۵, ۶, ۷, ۸, ۹, ۰) instead of (1, 2, 3, 4, 5, 6, 7, 8, 9, 0)
* Now in a module and we are showing amount of reads, the output in Persian must be ۱۲ (which represents 12).
* To developers, please use this function when you are having a numeric output, as this is counted as a string in php so you should use %s.
* Like:
* $views = sprintf ( 'Viewed: %s Times.', icms_conv_nr2local($string) );
*/
function icms_conv_nr2local($string)
{
	$basecheck = defined('_USE_LOCAL_NUM') && _USE_LOCAL_NUM;
	if ( $basecheck ){
	$string = str_replace(
		array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
		array(_LCL_NUM0, _LCL_NUM1, _LCL_NUM2, _LCL_NUM3, _LCL_NUM4, _LCL_NUM5, _LCL_NUM6, _LCL_NUM7, _LCL_NUM8, _LCL_NUM9), $string);
	}
		return $string;
}

/**
 * Get a number value in other languages and transform it to English
 *
 * This function is exactly the opposite of icms_conv_nr2local();
 * Please view the notes there for more information.
 */
function icms_conv_local2nr($string)
{
	$basecheck = defined('_USE_LOCAL_NUM') && _USE_LOCAL_NUM;
	if ( $basecheck ){
	$string = str_replace(
		array(_LCL_NUM0, _LCL_NUM1, _LCL_NUM2, _LCL_NUM3, _LCL_NUM4, _LCL_NUM5, _LCL_NUM6, _LCL_NUM7, _LCL_NUM8, _LCL_NUM9),
		array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'),
		$string);
	}
		return $string;
}


/**
 * Get month name by its ID
 *
 * @param int $month_id ID of the month
 * @return string month name
 */
function Icms_getMonthNameById($month_id) {
	global $xoopsConfig;
	icms_loadLanguageFile('core', 'calendar');
	$month_id = icms_conv_local2nr($month_id);
	if( $xoopsConfig['use_ext_date'] == 1 && defined ('_CALENDAR_TYPE') && _CALENDAR_TYPE == "jalali"){
		switch($month_id) {
			case 1:
				return _CAL_FARVARDIN;
			break;
			case 2:
				return _CAL_ORDIBEHESHT;
			break;
			case 3:
				return _CAL_KHORDAD;
			break;
			case 4:
				return _CAL_TIR;
			break;
			case 5:
				return _CAL_MORDAD;
			break;
			case 6:
				return _CAL_SHAHRIVAR;
			break;
			case 7:
				return _CAL_MEHR;
			break;
			case 8:
				return _CAL_ABAN;
			break;
			case 9:
				return _CAL_AZAR;
			break;
			case 10:
				return _CAL_DEY;
			break;
			case 11:
				return _CAL_BAHMAN;
			break;
			case 12:
				return _CAL_ESFAND;
			break;
		}
	}else{
		switch($month_id) {
			case 1:
				return _CAL_JANUARY;
			break;
			case 2:
				return _CAL_FEBRUARY;
			break;
			case 3:
				return _CAL_MARCH;
			break;
			case 4:
				return _CAL_APRIL;
			break;
			case 5:
				return _CAL_MAY;
			break;
			case 6:
				return _CAL_JUNE;
			break;
			case 7:
				return _CAL_JULY;
			break;
			case 8:
				return _CAL_AUGUST;
			break;
			case 9:
				return _CAL_SEPTEMBER;
			break;
			case 10:
				return _CAL_OCTOBER;
			break;
			case 11:
				return _CAL_NOVEMBER;
			break;
			case 12:
				return _CAL_DECEMBER;
			break;
		}
	}
}

/**
 * These functions are some Persian users related functions
 * In ImpressCMS we are trying to bring different calendar type in core, so this is the place to place them
 * If you know other calendars, plaese contact ImpressCMS developers to add them to core ;-)
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @copyright (C) 2000  Roozbeh Pournader and Mohammad Toossi
 * @copyright (C) jalali Date function by Milad Rastian (miladmovie AT yahoo DOT com)
 * @copyright (C) 2003 FARSI PROJECTS GROUP
 * @since		1.1.2
 * @author		Roozbeh Pournader and Mohammad Toossi
 * @author		jalali Date function by Milad Rastian (miladmovie AT yahoo DOT com)
 * @author		FARSI PROJECTS GROUP
 * @author	   Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @param	int	$a	Integer to divide
 * @param	int	$b	Integer to divide to
 * @return	int
 */
function div($a,$b) {
    return (int) ($a / $b);
}

/**
 * Converts gregorian to jalali calendar
 *
 * @param int $g_y	The gregorian year
 * @param int $g_m	The gregorian month
 * @param int $g_d	The gregorian day
 * @return array The jalali date array
 */
function gregorian_to_jalali ($g_y, $g_m, $g_d)
{
	$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
	
	$gy = $g_y-1600;
	$gm = $g_m-1;
	$gd = $g_d-1;
	
	$g_day_no = 365*$gy+div($gy+3,4)-div($gy+99,100)+div($gy+399,400);
	
	for ($i=0; $i < $gm; ++$i)
	   $g_day_no += $g_days_in_month[$i];
	if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0)))
	   /* leap and after Feb */
	   $g_day_no++;
	$g_day_no += $gd;
	
	$j_day_no = $g_day_no-79;
	
	$j_np = div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */
	$j_day_no = $j_day_no % 12053;
	
	$jy = 979+33*$j_np+4*div($j_day_no,1461); /* 1461 = 365*4 + 4/4 */
	
	$j_day_no %= 1461;
	
	if ($j_day_no >= 366) {
	   $jy += div($j_day_no-1, 365);
	   $j_day_no = ($j_day_no-1)%365;
	}
	
	for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
	   $j_day_no -= $j_days_in_month[$i];
	$jm = $i+1;
	$jd = $j_day_no+1;
	
	return array($jy, $jm, $jd);
}

/**
 * Converts jalali to gregorian calendar
 *
 * @param int $j_y	The jalali year
 * @param int $j_m	The jalali month
 * @param int $j_d	The jalali day
 * @return array The gregorian date array
 */
function jalali_to_gregorian($j_y, $j_m, $j_d)
{
	$g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

  $jy = $j_y-979;
  $jm = $j_m-1;
  $jd = $j_d-1;

  $j_day_no = 365*$jy + div($jy, 33)*8 + div($jy%33+3, 4);
  for ($i=0; $i < $jm; ++$i)
     $j_day_no += $j_days_in_month[$i];

  $j_day_no += $jd;

  $g_day_no = $j_day_no+79;

  $gy = 1600 + 400*div($g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */
  $g_day_no = $g_day_no % 146097;

  $leap = true;
  if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */
  {
     $g_day_no--;
     $gy += 100*div($g_day_no,  36524); /* 36524 = 365*100 + 100/4 - 100/100 */
     $g_day_no = $g_day_no % 36524;

     if ($g_day_no >= 365)
        $g_day_no++;
     else
        $leap = false;
  }

  $gy += 4*div($g_day_no, 1461); /* 1461 = 365*4 + 4/4 */
  $g_day_no %= 1461;

  if ($g_day_no >= 366) {
     $leap = false;

     $g_day_no--;
     $gy += div($g_day_no, 365);
     $g_day_no = $g_day_no % 365;
  }

  for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
     $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
  $gm = $i+1;
  $gd = $g_day_no+1;

  return array($gy, $gm, $gd);
}

/*
 * Finds the begining day of the month
 *
 * @param int $month	The month
 * @param int $day	The day
 * @param int $year	The year
 * @return string The beginning day of the month
 */
function mstart($month,$day,$year)
{
	list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
	list( $year, $month, $day ) = jalali_to_gregorian($jyear, $jmonth, '1');
	$timestamp=mktime(0,0,0,$month,$day,$year);
	return date('w',$timestamp);
}
// End of finding the begining day Of months

/*
 * Finds the last day of the month
 *
 * @param int $month	The month
 * @param int $day	The day
 * @param int $year	The year
 * @return string The last day of the month
 */
function lastday ($month,$day,$year)
{
	$lastdayen=date('d',mktime(0,0,0,$month+1,0,$year));
	list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
	$lastdatep=$jday;
	while($jday!='1')
	{
		if($day<$lastdayen)
		{
			$day++;
			list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
			if($jday=='1') break;
			if($jday='1') $lastdatep++;
		}
		else
		{
			$day=0;
			$month++;
			if($month==13)
			{
					$month='1';
					$year++;
			}
		}

	}
	return $lastdatep-1;
}

/*
 * Make time for jalali calendar
 *
 * @param int $hour	The hour
 * @param int $minute	The minute
 * @param int $second	The second
 * @param int $jmonth	The jalali month
 * @param int $jday	The jalali day
 * @param int $jyear	The jalali year
 * @return string The mktime string
 */
function jmaketime($hour,$minute,$second,$jmonth,$jday,$jyear)
{
	$basecheck = defined('_USE_LOCAL_NUM') && _USE_LOCAL_NUM;
	if ( $basecheck ){
	$hour = icms_conv_local2nr($hour);
	$minute = icms_conv_local2nr($minute);
	$second = icms_conv_local2nr($second);
	$jmonth = icms_conv_local2nr($jday);
	$jyear = icms_conv_local2nr($jyear);
	}
	list( $year, $month, $day ) = jalali_to_gregorian($jyear, $jmonth, $jday);
	$i=mktime($hour,$minute,$second,$month,$day,$year);
	return $i;
}

/*
 * Make time for jalali calendar
 *
 * @param string $type	The type of date string?
 * @param string $maket	The date string type
 * @return mixed The mktime string
 */
function jdate($type,$maket='now')
{
    global $xoopsConfig;
	icms_loadLanguageFile('core', 'calendar');
	$result='';
	if($maket=='now'){
		$year=date('Y');
		$month=date('m');
		$day=date('d');
		list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
		$maket=jmaketime(date('h'),date('i'),date('s'),$jmonth,$jday,$jyear);
	}else{
		$date=date('Y-m-d',$maket);
		list( $year, $month, $day ) = preg_split ( '/-/', $date );

		list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
		}

	$need= $maket;
	$year=date('Y',$need);
	$month=date('m',$need);
	$day=date('d',$need);
	$i=0;
	while($i<strlen($type))
	{
		$subtype=substr($type,$i,1);
		switch ($subtype)
		{

			case 'A':
				$result1=date('a',$need);
				if($result1=='pm') $result.=_CAL_PM_LONG;
				else $result.=_CAL_AM_LONG;
				break;

			case 'a':
				$result1=date('a',$need);
				if($result1=='pm') $result.=_CAL_PM;
				else $result.=_CAL_AM;
				break;
			case 'd':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				if($jday<10)$result1='0'.$jday;
				else 	$result1=$jday;
				$result.=$result1;
				break;
			case 'D':
				$result1=date('D',$need);
				if($result1=='Sat') $result1=_CAL_SAT;
				else if($result1=='Sun') $result1=_CAL_SUN;
				else if($result1=='Mon') $result1=_CAL_MON;
				else if($result1=='Tue') $result1=_CAL_TUE;
				else if($result1=='Wed') $result1=_CAL_WED;
				else if($result1=='Thu') $result1=_CAL_THU;
                                else if($result1=='Fri') $result1=_CAL_FRI;
				$result.=$result1;
				break;
			case'F':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=Icms_getMonthNameById($jmonth);
				break;
			case 'g':
				$result.=date('g',$need);
				break;
			case 'G':
				$result.=date('G',$need);
				break;
				case 'h':
				$result.=date('h',$need);
				break;
			case 'H':
				$result.=date('H',$need);
				break;
			case 'i':
				$result.=date('i',$need);
				break;
			case 'j':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=$jday;
				break;
			case 'l':
				$result1=date('l',$need);
				if($result1=='Saturday') $result1=_CAL_SATURDAY;
				else if($result1=='Sunday') $result1=_CAL_SUNDAY;
				else if($result1=='Monday') $result1=_CAL_MONDAY;
				else if($result1=='Tuesday') $result1=_CAL_TUESDAY;
				else if($result1=='Wednesday') $result1=_CAL_WEDNESDAY;
				else if($result1=='Thursday') $result1=_CAL_THURSDAY;
				else if($result1=='Friday') $result1=_CAL_FRIDAY;
				$result.=$result1;
				break;
			case 'm':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				if($jmonth<10) $result1='0'.$jmonth;
				else	$result1=$jmonth;
				$result.=$result1;
				break;
			case 'M':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=Icms_getMonthNameById($jmonth);
				break;
			case 'n':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=$jmonth;
				break;
			case 's':
				$result.=date('s',$need);
				break;
			case 'S':
				$result.=_CAL_SUFFIX;
				break;
			case 't':
				$result.=lastday ($month,$day,$year);
				break;
			case 'w':
				$result.=date('w',$need);
				break;
			case 'y':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=substr($jyear,2,4);
				break;
			case 'Y':
				list( $jyear, $jmonth, $jday ) = gregorian_to_jalali($year, $month, $day);
				$result.=$jyear;
				break;
			default:
				$result.=$subtype;
		}
	$i++;
	}
	return $result;
}

/**
 * This function is to convert date() function outputs into local values
 *
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @since		1.1.2
 * @author	   Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @param int $type	The type of date string?
 * @param string $maket	The date string type
 * @return mixed The converted date string
 */
function ext_date($type,$maket='now')
{
	icms_loadLanguageFile('core', 'calendar');
	$result='';
	if($maket=='now'){
		$year=date('Y');
		$month=date('m');
		$day=date('d');
		$maket=mktime(date('H'), date('i'), date('s'), $month, $day, $year);;
	}else{
		$date=date('Y-m-d',$maket);
		list( $year, $month, $day ) = preg_split ( '/-/', $date );
		}
	$i=0;
	while($i<strlen($type))
	{
		$subtype=substr($type,$i,1);
		switch ($subtype)
		{

			case 'A':
				$result1=date('a',$maket);
				if($result1=='pm') $result.=_CAL_PM_LONG;
				else $result.=_CAL_AM_LONG;
				break;

			case 'a':
				$result1=date('a',$maket);
				if($result1=='pm') $result.=_CAL_PM;
				else $result.=_CAL_AM;
				break;
			case 'd':
				if($day<10) $result1='0'.$day;
				else	$result1=$day;
				$result.=$result1;
				break;
			case 'D':
				$result1=date('D',$maket);
				if($result1=='Sat') $result1=_CAL_SAT;
				else if($result1=='Sun') $result1=_CAL_SUN;
				else if($result1=='Mon') $result1=_CAL_MON;
				else if($result1=='Tue') $result1=_CAL_TUE;
				else if($result1=='Wed') $result1=_CAL_WED;
				else if($result1=='Thu') $result1=_CAL_THU;
                else if($result1=='Fri') $result1=_CAL_FRI;
				$result.=$result1;
				break;
			case'F':
				$result.=Icms_getMonthNameById($month);
				break;
			case 'g':
				$result.=date('g',$maket);
				break;
			case 'G':
				$result.=date('G',$maket);
				break;
				case 'h':
				$result.=date('h',$maket);
				break;
			case 'H':
				$result.=date('H',$maket);
				break;
			case 'i':
				$result.=date('i',$maket);
				break;
			case 'j':
				$result.=$day;
				break;
			case 'l':
				$result1=date('l',$maket);
				if($result1=='Saturday') $result1=_CAL_SATURDAY;
				else if($result1=='Sunday') $result1=_CAL_SUNDAY;
				else if($result1=='Monday') $result1=_CAL_MONDAY;
				else if($result1=='Tuesday') $result1=_CAL_TUESDAY;
				else if($result1=='Wednesday') $result1=_CAL_WEDNESDAY;
				else if($result1=='Thursday') $result1=_CAL_THURSDAY;
				else if($result1=='Friday') $result1=_CAL_FRIDAY;
				$result.=$result1;
				break;
			case 'm':
				if($month<10) $result1='0'.$month;
				else	$result1=$month;
				$result.=$result1;
				break;
			case 'M':
				$result.=Icms_getMonthNameById($month);
				break;
			case 'n':
				$result.= $month;
				break;
			case 's':
				$result.=date('s',$maket);
				break;
			case 'S':
				$result.=_CAL_SUFFIX;
				break;
			case 't':
				$result.=date('t',$maket);
				break;
			case 'w':
				$result.=date('w',$maket);
				break;
			case 'y':
				$result.=substr($year,2,4);
				break;
			case 'Y':
				$result.=$year;
				break;
			default:
				$result.=$subtype;
		}
	$i++;
	}
	return $result;
}

/**
 * Gets module handler instance
 *
 * @param string $name	The name of the module
 * @param string $module_dir	The module directory where to get the module class
 * @param string $module_basename	The basename of the module
 * @param bool $optional	Is the module optional? Is it bad when the module cannot be loaded?
 * @return object The module handler instance
 */
function &icms_getmodulehandler($name = null, $module_dir = null, $module_basename = null, $optional = false)
{
	static $handlers;
	// if $module_dir is not specified
	if(!isset($module_dir))
	{
		//if a module is loaded
		if(isset($GLOBALS['xoopsModule']) && is_object($GLOBALS['xoopsModule'])) {$module_dir = $GLOBALS['xoopsModule']->getVar('dirname');}
		else {trigger_error(_CORE_NOMODULE, E_USER_ERROR);}
	}
	else {$module_dir = trim($module_dir);}
	$name = (!isset($name)) ? $module_dir : trim($name);
	if(!isset($handlers[$module_dir][$name]))
	{
		if($module_dir != 'system') {$hnd_file = ICMS_ROOT_PATH."/modules/{$module_dir}/class/{$name}.php";}
		else {$hnd_file = ICMS_ROOT_PATH."/modules/{$module_dir}/admin/{$name}/class/{$name}.php";}
		if(file_exists($hnd_file)) {include_once $hnd_file;}
		$class = ucfirst(strtolower($module_basename)).ucfirst($name).'Handler';
		if(class_exists($class)) {$handlers[$module_dir][$name] =& new $class($GLOBALS['xoopsDB']);}
	}
	if(!isset($handlers[$module_dir][$name]) && !$optional)
	{
		trigger_error(sprintf(_CORE_MODULEHANDLER_NOTAVAILABLE, $module_dir, $name), E_USER_ERROR);
	}
	if(isset($handlers[$module_dir][$name])) {return $handlers[$module_dir][$name];}
	$inst = false;
	return $inst;
}

/**
 * Get URL of previous page
 *
 * @param string $default default page if previous page is not found
 * @return string previous page URL
 */
function icms_getPreviousPage($default=false) {
	global $impresscms;
	if (isset($impresscms->urls['previouspage'])) {
		return $impresscms->urls['previouspage'];
	} elseif($default) {
		return $default;
	} else {
		return ICMS_URL;
	}
}

/**
 * Get module admion link
 *
 * @param string $moduleName dirname of the moodule
 * @return string URL of the admin side of the module
 */
function icms_getModuleAdminLink($moduleName=false) {
	global $xoopsModule;
	if (!$moduleName && (isset ($xoopsModule) && is_object($xoopsModule))) {
		$moduleName = $xoopsModule->getVar('dirname');
	}
	$ret = '';
	if ($moduleName) {
		$ret = "<a href='" . ICMS_URL . "/modules/$moduleName/admin/index.php'>" . _CO_ICMS_ADMIN_PAGE . "</a>";
	}
	return $ret;
}

/**
 * Finds the width and height of an image (can also be a flash file)
 *
 * @credit phppp
 *
 * @var string $url path of the image file
 * @var string $width reference to the width
 * @var string $height reference to the height
 * @return bool false if impossible to find dimension
 */
function icms_getImageSize($url, & $width, & $height) {
	if (empty ($width) || empty ($height)) {
		if (!$dimension = @ getimagesize($url)) {
			return false;
		}
		if (!empty ($width)) {
			$height = $dimension[1] * $width / $dimension[0];
		}
		elseif (!empty ($height)) {
			$width = $dimension[0] * $height / $dimension[1];
		} else {
			list ($width, $height) = array (
				$dimension[0],
				$dimension[1]
			);
		}
		return true;
	} else {
		return true;
	}
}

/**
 * Gets all types of urls in one array
 *
 * @return array The array of urls
 */
function icms_getCurrentUrls() {
	$urls = array();
	$http = ((strpos(ICMS_URL, "https://")) === false) ? ("http://") : ("https://");
	$phpself = $_SERVER['PHP_SELF'];
	$httphost = $_SERVER['HTTP_HOST'];
	$querystring = $_SERVER['QUERY_STRING'];
	if ($querystring != '') {
		$querystring = '?' . $querystring;
	}
	$currenturl = $http . $httphost . $phpself . $querystring;
	$urls = array ();
	$urls['http'] = $http;
	$urls['httphost'] = $httphost;
	$urls['phpself'] = $phpself;
	$urls['querystring'] = $querystring;
	$urls['full_phpself'] = $http . $httphost . $phpself;
	$urls['full'] = $currenturl;
	$urls['isHomePage'] = (ICMS_URL . "/index.php") == ($http . $httphost . $phpself);
	return $urls;
}

/**
 * Deletes a file
 *
 * @param string $dirname path of the file
 * @return	The unlinked dirname
 */
function icms_deleteFile($dirname) {
	// Simple delete for a file
	if (is_file($dirname)) {
		return unlink($dirname);
	}
}

/**
 * Resizes an image to maxheight and maxwidth
 *
 * @param string $src	The image file to resize
 * @param string $maxWidth	The maximum width to resize the image to
 * @param string $maxHeight	The maximum height to resize the image to
 * @return array The resized image array
 */
function icms_imageResize($src, $maxWidth, $maxHeight) {
	$width = '';
	$height = '';
	$type = '';
	$attr = '';
	if (file_exists($src)) {
		list ($width, $height, $type, $attr) = getimagesize($src);
		if ($width > $maxWidth) {
			$originalWidth = $width;
			$width = $maxWidth;
			$height = $width * $height / $originalWidth;
		}
		if ($height > $maxHeight) {
			$originalHeight = $height;
			$height = $maxHeight;
			$width = $height * $width / $originalHeight;
		}
		$attr = " width='$width' height='$height'";
	}
	return array (
		$width,
		$height,
		$type,
		$attr
	);
}

/**
 * Generates the module name with either a link or not
 *
 * @param bool $withLink	Generate the modulename with in an anchor link?
 * @param bool $forBreadCrumb	Is the module name for the breadcrumbs?
 * @param mixed $moduleName	The passed modulename or false if no modulename was passed
 * @return array The resized image array
 */
function icms_getModuleName($withLink = true, $forBreadCrumb = false, $moduleName = false) {
	if (!$moduleName) {
		global $xoopsModule;
		$moduleName = $xoopsModule->getVar('dirname');
	}
	$icmsModule = icms_getModuleInfo($moduleName);
	$icmsModuleConfig = icms_getModuleConfig($moduleName);
	if (!isset ($icmsModule)) {
		return '';
	}

	if (!$withLink) {
		return $icmsModule->getVar('name');
	} else {
/*	    $seoMode = smart_getModuleModeSEO($moduleName);
	    if ($seoMode == 'rewrite') {
	    	$seoModuleName = smart_getModuleNameForSEO($moduleName);
	    	$ret = XOOPS_URL . '/' . $seoModuleName . '/';
	    } elseif ($seoMode == 'pathinfo') {
	    	$ret = XOOPS_URL . '/modules/' . $moduleName . '/seo.php/' . $seoModuleName . '/';
	    } else {
			$ret = XOOPS_URL . '/modules/' . $moduleName . '/';
	    }
*/
		$ret = ICMS_URL . '/modules/' . $moduleName . '/';
		return '<a href="' . $ret . '">' . $icmsModule->getVar('name') . '</a>';
	}
}

/**
 * Deletes users who registered but aren't yet active for X days.
 *
 * To be used in ImpressCMS 1.2
 * @return mixed Did the query succeed or not? Returns nothing if succeeded, false if not succeeded
 **/
function remove_usersxdays (){
	$db =& Database::getInstance();
	$config_handler =& xoops_gethandler('config');
	$xoopsConfigUser =& $config_handler->getConfigsByCat(XOOPS_CONF_USER);
	$days = $xoopsConfigUser['delusers'];
	$delete_regdate= time() - ( $days * 24 * 60 * 60 );  // X days/month * 24 hrs/day
	$sql = sprintf("DELETE FROM %s WHERE (level = '0' AND user_regdate < '%s')", $db->prefix('users'), $delete_regdate);
	if(!$result = $db->queryF($sql)){
		return false;
	}
}

/**
 * Converts size to human readable text
 *
 * @param int $size	The size to convert
 * @return string The converted size
 */
function icms_convert_size($size){
	if ($size >= 1073741824){
		$ret = ((($size/1024)/1024)/1024).' '._CORE_GIGABYTES_SHORTEN;
	}elseif($size >= 1048576 && $size < 1073741824){
		$ret = (($size/1024)/1024).' '._CORE_MEGABYTES_SHORTEN;
	}elseif($size >= 1024 && $size < 1048576){
		$ret = ($size/1024).' '._CORE_KILOBYTES_SHORTEN;
	}else{
		$ret = ($size).' '._CORE_BYTES;
	}
	return icms_conv_nr2local(round($ret,1));
}

/**
 * Generates a random string
 *
 * @param int $numchar	How many characters should the string consist of?
 * @return string The generated random string
 */
function icms_random_str($numchar){
	$letras = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,x,w,y,z,1,2,3,4,5,6,7,8,9,0";
	$array = explode(",", $letras);
	shuffle($array);
	$senha = implode($array, "");
	return substr($senha, 0, $numchar);
}

/**
 * Generates a random string
 *
 * @param int $currentoption	What current admin option are we in?
 * @param string $breadcrumb	The breadcrumb if it is passed, otherwise empty string
 */
function icms_adminMenu($currentoption = 0, $breadcrumb = '') {
	global $xoopsModule;
	$xoopsModule->displayAdminMenu( $currentoption, $xoopsModule -> name() . ' | ' . $breadcrumb );
}

/**
 * Loads common language file
 */
function icms_loadCommonLanguageFile() {
	icms_loadLanguageFile('system', 'common');
}

/**
 * Gets current page
 *
 * @return string The URL of the current page
 */
function icms_getCurrentPage() {
	$urls = icms_getCurrentUrls();
	return $urls['full'];
}

/*function icms_getModuleName($withLink = true, $forBreadCrumb = false, $moduleName = false) {
	if (!$moduleName) {
		global $xoopsModule;
		$moduleName = $xoopsModule->getVar('dirname');
	}
	$icmsModule = & icms_getModuleInfo($moduleName);
	$icmsModuleConfig = & icms_getModuleConfig($moduleName);
	if (!isset ($icmsModule)) {
		return '';
	}

	if ($forBreadCrumb && (isset ($icmsModuleConfig['show_mod_name_breadcrumb']) && !$icmsModuleConfig['show_mod_name_breadcrumb'])) {
		return '';
	}
	if (!$withLink) {
		return $icmsModule->getVar('name');
	} else {
	    $seoMode = icms_getModuleModeSEO($moduleName);
	    if ($seoMode == 'rewrite') {
	    	$seoModuleName = icms_getModuleNameForSEO($moduleName);
	    	$ret = XOOPS_URL . '/' . $seoModuleName . '/';
	    } elseif ($seoMode == 'pathinfo') {
	    	$ret = XOOPS_URL . '/modules/' . $moduleName . '/seo.php/' . $seoModuleName . '/';
	    } else {
			$ret = XOOPS_URL . '/modules/' . $moduleName . '/';
	    }

		return '<a href="' . $ret . '">' . $icmsModule->getVar('name') . '</a>';
	}
}*/

/**
 * Gets module name in SEO format
 *
 * @param mixed $moduleName	Modulename if it is passed, otherwise false
 * @return string The modulename in SEO format
 */
function icms_getModuleNameForSEO($moduleName = false) {
	$icmsModule = & icms_getModuleInfo($moduleName);
	$icmsModuleConfig = & icms_getModuleConfig($moduleName);
	if (isset ($icmsModuleConfig['seo_module_name'])) {
		return $icmsModuleConfig['seo_module_name'];
	}
	$ret = icms_getModuleName(false, false, $moduleName);
	return (strtolower($ret));
}

/**
 * Determines if the module is in SEO mode
 *
 * @param mixed $moduleName	Modulename if it is passed, otherwise false
 * @return bool Is the module in SEO format?
 */
function icms_getModuleModeSEO($moduleName = false) {
	$icmsModule = & icms_getModuleInfo($moduleName);
	$icmsModuleConfig = & icms_getModuleConfig($moduleName);
	return isset ($icmsModuleConfig['seo_mode']) ? $icmsModuleConfig['seo_mode'] : false;
}

/**
 * Gets the include ID if the module is in SEO format (otherwise nothing)
 *
 * @param mixed $moduleName	Modulename if it is passed, otherwise false
 * @return mixed The module include ID otherwise nothing
 */
function icms_getModuleIncludeIdSEO($moduleName = false) {
	$icmsModule = & icms_getModuleInfo($moduleName);
	$icmsModuleConfig = & icms_getModuleConfig($moduleName);
	return !empty ($icmsModuleConfig['seo_inc_id']);
}

/*
* Gets environment key from the $_SERVER or $_ENV superglobal
* 
* @param string  $key  The key to get
* @return string  $ret  The retrieved key
*/
function icms_getenv($key) {
	$ret = '';
	$ret = isset ($_SERVER[$key]) ? $_SERVER[$key] : (isset ($_ENV[$key]) ? $_ENV[$key] : '');
	return $ret;
}

/*
* Gets environment key from the $_SERVER or $_ENV superglobal
* 
* @param string  $key  The key to get
* @param string  $ret  The retrieved key
*/
function icms_get_module_status($module_name){
	$module_handler = xoops_gethandler('module');
	$this_module = $module_handler->getByDirname($module_name);
	if($this_module && $this_module->getVar('isactive')){
		return true;
	}
	return false;
}

/**
* Wrap a long term or word
*
* @author	<admin@jcink.com>
* @param	string	$string    The string
* @param	string  $width      The length
* @return   bool    Returns a long term, in several small parts with the length of $width
*/
function one_wordwrap($string,$width=false){
	$width = $width ? $width : '15';
	$new_string = '';
	$s=explode(" ", $string);
	foreach ($s as $k=>$v) {
	$cnt=strlen($v);
	if($cnt>$width) $v=wordwrap($v, $width, ' ', true);
		$new_string.="$v ";
	}
	return $new_string;
}

/**
* Removes the content of a folder.
*
* @author	Steve Kenow (aka skenow) <skenow@impresscms.org>
* @author	modified by Vaughan <vaughan@impresscms.org>
* @author	modified by Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
* @param	string	$path    The folder path to cleaned. Must be an array like: array('templates_c' => ICMS_ROOT_PATH."/templates_c/");
* @param	bool  $remove_admin_cache      True to remove admin cache, if required.
*/
function icms_clean_folders($dir, $remove_admin_cache=false) {
	global $xoopsConfig;
	foreach ($dir as $d)
	{
		$dd = opendir($d);
		while($file = readdir($dd))
		{
			$files_array = $remove_admin_cache ? ($file != 'index.html' && $file != 'php.ini' && $file != '.htaccess' && $file != '.svn') : ($file != 'index.html' && $file != 'php.ini' && $file != '.htaccess' && $file != '.svn' && $file != 'adminmenu_' . $xoopsConfig['language'] . '.php');
			if(is_file($d.$file) && $files_array)
			{
				unlink($d.$file);
			}
		}
		closedir($dd);
	}
	return true;
}

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param bool $deleteRootToo Delete specified top-level directory as well
 */
function icms_unlinkRecursive($dir, $deleteRootToo)
{
   if(!$dh = @opendir($dir))
   {
       return;
   }
   while (false !== ($obj = readdir($dh)))
   {
       if($obj == '.' || $obj == '..')
       {
           continue;
       }

       if (!@unlink($dir . '/' . $obj))
       {
           unlinkRecursive($dir.'/'.$obj, true);
       }
   }

   closedir($dh);
  
   if ($deleteRootToo)
   {
       @rmdir($dir);
   }
  
   return;
}
?>