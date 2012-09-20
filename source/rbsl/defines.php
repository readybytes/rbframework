<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

// If file is already included
if(defined('RB_FRAMEWORK_LOADED')){
	return;
}

define('RB_FRAMEWORK_LOADED', true);

//paths 
define('RB_PATH_FRAMEWORK', dirname(__FILE__));
define('RB_PATH_CORE',		RB_PATH_FRAMEWORK.'/base');
define('RB_PATH_INCLUDES',	RB_PATH_FRAMEWORK.'/includes');
define('RB_PATH_MEDIA',		RB_PATH_FRAMEWORK.'/media');
define('RB_PATH_ELEMENTS',	RB_PATH_FRAMEWORK.'/elements');
define('RB_PATH_JOOMLA_EXTENDED',	RB_PATH_FRAMEWORK.'/joomla');


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
define('RB_JVERSION', 		$xi_major);
define('RB_JEXACTVERSION', 	$xi_version);
define('RB_JFAMILY', 		$xi_family);


define('RB_ENABLE_STATE',  1);
define('RB_DISABLE_STATE', 0);

define('RB_IS_AJAX_REQUEST', JRequest::getBool('isAjax',	false));
