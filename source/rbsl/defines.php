<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

// If file is already included
if(defined('XI_FRAMEWORK_LOADED')){
	return;
}

define('XI_FRAMEWORK_LOADED', true);

//paths 
define('XI_PATH_FRAMEWORK', dirname(__FILE__));
define('XI_PATH_CORE',		XI_PATH_FRAMEWORK.DS.'base');
define('XI_PATH_INCLUDES',	XI_PATH_FRAMEWORK.DS.'includes');
define('XI_PATH_MEDIA',		XI_PATH_FRAMEWORK.DS.'media');
define('XI_PATH_ELEMENTS',	XI_PATH_FRAMEWORK.DS.'elements');
define('XI_PATH_JOOMLA_EXTENDED',	XI_PATH_FRAMEWORK.DS.'joomla');


// define the joomla version
$version = new JVersion();

$xi_major   = str_replace('.', '', $version->RELEASE);
$xi_version = str_replace('.', '', $version->getShortVersion());
$xi_family  = '15';
	switch($xi_major){	
		case '15':
			$xi_family='15';
			break;
			
		case '16':
		case '17':
		case '25':
			$xi_family='16';
			break;
	}

// version is current code
define('XI_JVERSION', 		$xi_major);
define('XI_JEXACTVERSION', 	$xi_version);
define('XI_JFAMILY', 		$xi_family);


define('XI_ENABLE_STATE',  1);
define('XI_DISABLE_STATE', 0);

define('XI_IS_AJAX_REQUEST', JRequest::getBool('isAjax',	false));
