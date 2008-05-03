<?php
/**
* Automatic update of the system module
*
* @copyright	The ImpressCMS Project http://www.impresscms.org/
* @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
* @package		core
* @since		1.0
* @author		malanciault <marcan@impresscms.org)
* @version		$Id: update.php 429 2008-003-25 22:21:41Z malanciault $
*/

function xoops_module_update_system(&$module) {
    /**
     * For compatibility upgrade...
     */
     $moduleVersion  = $module->getVar('version');
	if ($moduleVersion < 102) {
        $result = $xoopsDB->query("SELECT t1.tpl_id FROM ".$xoopsDB->prefix('tplfile')." t1, ".$xoopsDB->prefix('tplfile')." t2 WHERE t1.tpl_module = t2.tpl_module AND t1.tpl_tplset=t2.tpl_tplset AND t1.tpl_file = t2.tpl_file AND t1.tpl_id > t2.tpl_id");

        $tplids = array();
        while (list($tplid) = $xoopsDB->fetchRow($result)) {
            $tplids[] = $tplid;
        }
        if (count($tplids) > 0) {
            $tplfile_handler =& xoops_gethandler('tplfile');
            $duplicate_files = $tplfile_handler->getObjects(new Criteria('tpl_id', "(".implode(',', $tplids).")", "IN"));

            if (count($duplicate_files) > 0) {
                foreach (array_keys($duplicate_files) as $i) {
                    $tplfile_handler->delete($duplicate_files[$i]);
                }
            }
        }
    }

    $icmsDatabaseUpdater = XoopsDatabaseFactory::getDatabaseUpdater();
    
    ob_start();
    
    $dbVersion  = $module->getDBVersion();

	echo "<code>" . _DATABASEUPDATER_UPDATE_UPDATING_DATABASE . "<br />";

	// db migrate version = 1
    $newDbVersion = 1;

    if ($dbVersion < $newDbVersion) {
    	echo "Database migrate to version " . $newDbVersion . "<br />";
    	    	
		// Now, first, let's increment the conf_order of user option starting at new_user_notify
		$table = new IcmsDatabasetable('config');
		$criteria = new CriteriaCompo();
		$criteria->add(new Criteria('conf_order', 3, '>'));
    	$table->addUpdateAll('conf_order', 'conf_order + 2', $criteria, true);
	    $icmsDatabaseUpdater->updateTable($table);	
	    unset($table);	
	    
	    // create extended date function's config option
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF, 'use_ext_date', '_MD_AM_EXT_DATE', 0, '_MD_AM_EXT_DATEDSC', 'yesno', 'int', 8);
	    // create editors config option
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF, 'editor_default', '_MD_AM_EDITOR_DEFAULT', 'default', '_MD_AM_EDITOR_DEFAULT_DESC', 'editor', 'text', 13);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF, 'editor_enabled_list', '_MD_AM_EDITOR_ENABLED_LIST', ".addslashes(serialize(array('default'))).", '_MD_AM_EDITOR_ENABLED_LIST_DESC', 'editor_multi', 'array', 13);
	    // create captcha options
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF, 'use_captchaf', '_MD_AM_USECAPTCHAFORM', 1, '_MD_AM_USECAPTCHAFORMDSC', 'yesno', 'int', 30);

	    // create 4 new user config options
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'pass_level', '_MD_AM_PASSLEVEL', '20', '_MD_AM_PASSLEVEL_DESC', 'select', 'int', 2);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'use_captcha', '_MD_AM_USECAPTCHA', 1, '_MD_AM_USECAPTCHADSC', 'yesno', 'int', 4);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'welcome_msg', '_MD_AM_WELCOMEMSG', 0, '_MD_AM_WELCOMEMSGDSC', 'yesno', 'int', 4);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'allwshow_sig', '_MD_AM_ALLWSHOWSIG', 1, '_MD_AM_ALLWSHOWSIGDSC', 'yesno', 'int', 6);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'allow_htsig', '_MD_AM_ALLWHTSIG', 1, '_MD_AM_ALLWHTSIGDSC', 'yesno', 'int', 6);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'avatar_allow_gravatar', '_MD_AM_GRAVATARALLOW', '1', '_MD_AM_GRAVATARALWDSC', 'yesno', 'int', 12);

	    // get the default content of the mail
	    global $xoopsConfig;
	    $default_msg_content_file = XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/mail_template/' . 'welcome.tpl';
	    if (!file_exists($default_msg_content_file)) {
	    	$default_msg_content_file = XOOPS_ROOT_PATH . '/language/english/mail_template/' . 'welcome.tpl';
	    }
	    $fp = fopen($default_msg_content_file, 'r');
        if ($fp) {
            $default_msg_content = fread($fp, filesize($default_msg_content_file));
        }
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_USER, 'welcome_msg_content', '_MD_AM_WELCOMEMSG_CONTENT', $default_msg_content, '_MD_AM_WELCOMEMSG_CONTENTDSC', 'textarea', 'text', 5);	   
	    
	    // Adding configurations of meta tag&footer
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_METAFOOTER, 'google_meta', '_MD_AM_METAGOOGLE', '', '_MD_AM_METAGOOGLE_DESC', 'textbox', 'text', 6);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_METAFOOTER, 'google_analytics', '_MD_AM_GOOGLE_ANA', '', '_MD_AM_GOOGLE_ANA_DESC', 'textarea', 'text', 7);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_METAFOOTER, 'footadm', '_MD_AM_FOOTADM', 'Powered by ImpressCMS &copy; 2007-' . date("Y", time()) . ' <a href=\"http://www.impresscms.org/\" rel=\"external\">The ImpressCMS Project</a>', '_MD_AM_FOOTADM_DESC', 'textarea', 'text', 7);

	    // Adding configurations of search preferences
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_SEARCH, 'search_user_date', '_MD_AM_SEARCH_USERDATE', '1', '_MD_AM_SEARCH_USERDATE', 'yesno', 'int', 2);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_SEARCH, 'search_no_res_mod', '_MD_AM_SEARCH_NO_RES_MOD', '1', '_MD_AM_SEARCH_NO_RES_MODDSC', 'yesno', 'int', 2);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_SEARCH, 'search_per_page', '_MD_AM_SEARCH_PER_PAGE', '20', '_MD_AM_SEARCH_PER_PAGEDSC', 'textbox', 'int', 2);

		// Adding new cofigurations added for multi language
	    $icmsDatabaseUpdater->insertConfig(IM_CONF_MULILANGUAGE, 'ml_autoselect_enabled', '_MD_AM_ML_AUTOSELECT_ENABLED', '0', '_MD_AM_ML_AUTOSELECT_ENABLED_DESC', 'yesno', 'int', 1);
	    
	    // Adding new function of content manager
	    $icmsDatabaseUpdater->insertConfig(IM_CONF_CONTENT, 'default_page', '_MD_AM_DEFAULT_CONTPAGE', '0', '_MD_AM_DEFAULT_CONTPAGEDSC', 'select_pages', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(IM_CONF_CONTENT, 'show_nav', '_MD_AM_CONT_SHOWNAV', '1', '_MD_AM_CONT_SHOWNAVDSC', 'yesno', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(IM_CONF_CONTENT, 'show_subs', '_MD_AM_CONT_SHOWSUBS', '1', '_MD_AM_CONT_SHOWSUBSDSC', 'yesno', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(IM_CONF_CONTENT, 'show_pinfo', '_MD_AM_CONT_SHOWPINFO', '1', '_MD_AM_CONT_SHOWPINFODSC', 'yesno', 'int', 1);
	    global $xoopsConfig;
	    $default_login_content_file = XOOPS_ROOT_PATH . '/upgrade/language/' . $xoopsConfig['language'] . '/' . 'login.tpl';
	    if (!file_exists($default_login_content_file)) {
	    	$default_login_content_file = XOOPS_ROOT_PATH . '/upgrade/language/english/' . 'login.tpl';
	    }
	    $fp = fopen($default_login_content_file, 'r');
        if ($fp) {
            $default_login_content = fread($fp, filesize($default_login_content_file));
        }
	    // Adding new function of Personalization
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'rss_local', '_MD_AM_RSSLOCAL', 'http://www.impresscms.org/modules/smartsection/backend.php', '_MD_AM_RSSLOCAL_DESC', 'textbox', 'text', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'editre_block', '_MD_AM_EDITREMOVEBLOCK', '1', '_MD_AM_EDITREMOVEBLOCKDSC', 'yesno', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'multi_login', '_MD_AM_MULTLOGINPREVENT', '0', '_MD_AM_MULTLOGINPREVENTDSC', 'yesno', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'multi_login_msg', '_MD_AM_MULTLOGINMSG', $default_login_content, '_MD_AM_MULTLOGINMSG_DESC', 'textarea', 'text', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'email_protect', '_MD_AM_EMAILPROTECT', '0', '_MD_AM_EMAILPROTECTDSC', 'yesno', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'email_font', '_MD_AM_EMAILTTF', 'arial.ttf', '_MD_AM_EMAILTTF_DESC', 'select_font', 'text', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'email_font_len', '_MD_AM_EMAILLEN', '12', '_MD_AM_EMAILLEN_DESC', 'textbox', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'email_cor', '_MD_AM_EMAILCOLOR', '#000000', '_MD_AM_EMAILCOLOR_DESC', 'color', 'text', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'email_sombra', '_MD_AM_EMAILSOMBRA', '#cccccc', '_MD_AM_EMAILSOMBRA_DESC', 'color', 'text', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'sombra_x', '_MD_AM_SOMBRAX', '2', '_MD_AM_SOMBRAX_DESC', 'textbox', 'int', 1);
	    $icmsDatabaseUpdater->insertConfig(XOOPS_CONF_PERSONA, 'sombra_y', '_MD_AM_SOMBRAY', '2', '_MD_AM_SOMBRAY_DESC', 'textbox', 'int', 1);
    }
    
	// db migrate version = 2
	// new content_manager by The_Rplima
    $newDbVersion = 2;

    if ($dbVersion < $newDbVersion) {
    	echo "Database migrate to version " . $newDbVersion . "<br />";
    	    	
		// Create table icmspage
		$table = new IcmsDatabasetable('icmspage');
		if (!$table->exists()) {
	    $table->setStructure("page_id mediumint(8) unsigned NOT NULL auto_increment,
		  page_moduleid mediumint(8) unsigned NOT NULL default '1',
		  page_title varchar(255) NOT NULL default '',
		  page_url varchar(255) NOT NULL default '',
		  page_status tinyint(1) unsigned NOT NULL default '1',
		  PRIMARY KEY  (page_id)");
		}
	    if (!$icmsDatabaseUpdater->updateTable($table)) {
	        /**
	         * @todo trap the errors
	         */
	    }
	    unset($table);

 		$table = new IcmsDatabasetable('block_module_link');
 		if (!$table->fieldExists('page_id')) {
 			$table->addNewField('page_id', "smallint(5) NOT NULL default '0'");
 		}
	    if (!$icmsDatabaseUpdater->updateTable($table)) {
	        /**
	         * @todo trap the errors
	         */
	    }
	    
	    $icmsDatabaseUpdater->runQuery('UPDATE '.$table->name().' SET module_id=0, page_id=1 WHERE module_id=-1','Block Visibility Restructured Successfully', 'Failed in Restructure the Block Visibility');
	    
		unset($table);	    
    }
    
	// db migrate version = 3
	// customtag feature added by marcan
    $newDbVersion = 3;

    if ($dbVersion < $newDbVersion) {
    	echo "Database migrate to version " . $newDbVersion . "<br />";
    	    	
		// Create table system_customtag
		$table = new IcmsDatabasetable('system_customtag');
		if (!$table->exists()) {
	    $table->setStructure("customtagid int(11) unsigned NOT NULL auto_increment,
		  name varchar(255) NOT NULL default '',
		  description text NOT NULL default '',
		  content text NOT NULL default '',
		  language varchar(100) NOT NULL default '',
		  customtag_type tinyint(1) NOT NULL default 0,
		  PRIMARY KEY (customtagid)");
		}
	    if (!$icmsDatabaseUpdater->updateTable($table)) {
	        /**
	         * @todo trap the errors
	         */
	    }
	    unset($table);
    }    
    
	echo "</code>";

   $feedback = ob_get_clean();
    if (method_exists($module, "setMessage")) {
        $module->setMessage($feedback);
    } else {
        echo $feedback;
    }
    $icmsDatabaseUpdater->updateModuleDBVersion($newDbVersion, 'system');
    return true;    
}

?>