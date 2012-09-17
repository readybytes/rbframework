<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		XiFramework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

// if already loaded do not load
if(defined('XI_FRAMEWORK_LOADED')){
	return;
}

//include basic required files
jimport('joomla.utilities.string');
jimport('joomla.utilities.date');

//load basic defines
require_once dirname(__FILE__).DS.'defines.php'	;
require_once XI_PATH_FRAMEWORK.DS.'filetree.php'; // load filetree, will help in reducing filesystem IO
require_once XI_PATH_FRAMEWORK.DS.'loader.php'	;

// adding JRegistryFormatXiINI formatter
require_once XI_PATH_INCLUDES.DS.'ini.php'	;

// System profiler
if (JDEBUG) {
	jimport( 'joomla.error.profiler' );
	$_PROFILER =& JProfiler::getInstance( 'Application' );
}

JDEBUG ? $_PROFILER->mark( 'payplans-XiFramework-Before-Load' ) : null;

//autoload core library
XiHelperLoader::addAutoLoadFolder(XI_PATH_CORE,		'',				'Xi');
XiHelperLoader::addAutoLoadFolder(XI_PATH_ELEMENTS, 	'Element', 		'J');
XiHelperLoader::addAutoLoadFolder(XI_PATH_ELEMENTS, 	'FormField',	'J');
XiHelperLoader::addAutoLoadFolder(XI_PATH_JOOMLA_EXTENDED, 	'',	'J');

JDEBUG ? $_PROFILER->mark( 'payplans-XiFramework-After-Load' ) : null;