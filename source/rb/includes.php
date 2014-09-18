<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		Rb_Framework
* @contact 		shyam@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// if already loaded do not load
if(defined('RB_FRAMEWORK_LOADED')){
	return;
}

//include basic required files
jimport('joomla.utilities.string');

// load basic function to check CMS details
include_once 'function.php';

// mark to profiler
_rb_cms_profiler_mark('RB-Framework-Before-Load');


//load basic defines
require_once dirname(__FILE__).'/defines.php'	;

// load the loader
require_once RB_PATH_FRAMEWORK.'/loader.php'	;
require_once dirname(__FILE__).'/helper.php'	;

// set autoload for all classes
$classes = require_once RB_PATH_FRAMEWORK.'/classes.php';
foreach ($classes as $className => $filePath) {
	// if adapater and legacy files then only adpater version should be loaded
	if((strpos($filePath, 'legacy/')===0) && (strpos($filePath, 'legacy/'.RB_CMS_ADAPTER) !==0) ){
		continue;
	}
	Rb_HelperLoader::addAutoLoadFile(RB_PATH_FRAMEWORK.'/'.$filePath, $className);
	
}

// mark to profiler
_rb_cms_profiler_mark('RB-Framework-After-Load');