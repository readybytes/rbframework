<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		Rb_Framework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

// if already loaded do not load
if(defined('RB_FRAMEWORK_LOADED')){
	return;
}

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

//include basic required files
jimport('joomla.utilities.string');

//load basic defines
require_once dirname(__FILE__).'/defines.php'	;
require_once RB_PATH_FRAMEWORK.'/filetree.php'; // load filetree, will help in reducing filesystem IO
require_once RB_PATH_FRAMEWORK.'/loader.php'	;

// adding JRegistryFormatRb_INI formatter
require_once RB_PATH_INCLUDES.'/ini.php'	;

// System profiler
if (JDEBUG) {
	jimport( 'joomla.error.profiler' );
	$_PROFILER =& JProfiler::getInstance( 'Application' );
}

JDEBUG ? $_PROFILER->mark( 'RB-Framework-Before-Load' ) : null;

//autoload core library
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_CORE,		'',				'Rb_');
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_ELEMENTS, 	'Element', 		'J');
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_ELEMENTS, 	'FormField',	'J');
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_JOOMLA_EXTENDED, 	'',	'J');

JDEBUG ? $_PROFILER->mark('RB-Framework-After-Load') : null;