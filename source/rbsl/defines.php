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

//mark framework loaded
define('RB_FRAMEWORK_LOADED', true);

//paths 
define('RB_PATH_FRAMEWORK', dirname(__FILE__));

define('RB_PATH_CMS',			RB_PATH_FRAMEWORK.'/cms');
define('RB_PATH_ABSTRACT',		RB_PATH_FRAMEWORK.'/abstract');
define('RB_PATH_CORE',			RB_PATH_FRAMEWORK.'/rb');

define('RB_PATH_MEDIA',		RB_PATH_FRAMEWORK.'/media');
define('RB_PATH_FILEDS',	RB_PATH_FRAMEWORK.'/fields');

list($prefix, $family, $major, $minor) = _rb_cms_version();

// version is current code
define('RB_CMS_PREFIX', 			$prefix); // J  for joomla
define('RB_CMS_VERSION', 			$major);  // 31 for 3.1
define('RB_CMS_VERSION_MINOR', 		$minor);  // 6  for 3.1.6
define('RB_CMS_VERSION_FAMILY', 	$family); // 35 for (3.0, 3.1, 3.5) or 25 for (1.6, 1.7, 2.5)


define('RB_STATE_ENABLE',  1);
define('RB_STATE_DISABLE', 0);

// define('RB_IS_AJAX_REQUEST', JRequest::getBool('isAjax',	false));
