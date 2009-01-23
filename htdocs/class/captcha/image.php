<?php
/**
 * Image Creation class for CAPTCHA
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		installer
 * @since		XOOPS
 * @author		http://www.xoops.org/ The XOOPS Project
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @author	   Sina Asghari (aka stranger) <pesian_stranger@users.sourceforge.net>
 * @version		$Id:$
*/

class IcmsCaptchaImage {
	//var $config	= array();
	
	function IcmsCaptchaImage()
	{
		//$this->name = md5( session_id() );
	}
	
	function &instance()
	{
		static $instance;
		if(!isset($instance)) {
			$instance =& new IcmsCaptchaImage();
		}
		return $instance;
	}
	
	/**
	 * Loading configs from CAPTCHA class
	 */
	function loadConfig($config = array())
	{
		// Loading default preferences
		$this->config =& $config;
	}

	function render()
	{
		$config_handler =& xoops_gethandler('config');
		$IcmsConfigCaptcha =& $config_handler->getConfigsByCat(ICMS_CONF_CAPTCHA);
		$form = "<input type='text' name='".$this->config["name"]."' id='".$this->config["name"]."' size='" . $IcmsConfigCaptcha['captcha_num_chars'] . "' maxlength='" . $IcmsConfigCaptcha['captcha_num_chars'] . "' value='' /> &nbsp; ". $this->loadImage();
		$rule = htmlspecialchars(ICMS_CAPTCHA_REFRESH, ENT_QUOTES);
		if($IcmsConfigCaptcha['captcha_maxattempt']) {
			$rule .=  " | ". sprintf( constant("ICMS_CAPTCHA_MAXATTEMPTS"), $IcmsConfigCaptcha['captcha_maxattempt'] );
		}
		$form .= "&nbsp;&nbsp;<small>{$rule}</small>";
		
		return $form;
	}


	function loadImage()
	{
		$config_handler =& xoops_gethandler('config');
		$IcmsConfigCaptcha =& $config_handler->getConfigsByCat(ICMS_CONF_CAPTCHA);
		$rule = $IcmsConfigCaptcha['captcha_casesensitive'] ? constant("ICMS_CAPTCHA_RULE_CASESENSITIVE") : constant("ICMS_CAPTCHA_RULE_CASEINSENSITIVE");
		$ret = "<img id='captcha' src='" . ICMS_URL. "/class/captcha/scripts/img.php' onclick=\"this.src='" . ICMS_URL. "/class/captcha/scripts/img.php?refresh='+Math.random()"."\" style='cursor: pointer;margin-left: auto;margin-right: auto;text-align:center;' alt='".htmlspecialchars($rule, ENT_QUOTES)."' />";
		return $ret;
	}

}
?>