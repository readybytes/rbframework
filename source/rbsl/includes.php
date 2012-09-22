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

//include basic required files
jimport('joomla.utilities.string');

// load basic function to check CMS details
include_once 'function.php';

// mark to profiler
_rb_cms_profiler_mark('RB-Framework-Before-Load');


//load basic defines
require_once dirname(__FILE__).'/defines.php'	;

// load filetree, will help in reducing filesystem IO
require_once RB_PATH_FRAMEWORK.'/filetree.php'; 

// load the loader
require_once RB_PATH_FRAMEWORK.'/loader.php'	;

// adding JRegistryFormatRb_INI formatter
// require_once RB_PATH_INCLUDES.'/ini.php'	;


//autoload core library
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_CMS,			'Cms',			'Rb_');
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_ABSTRACT,	'Abstract',		'Rb_');
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_CORE,		'',				'Rb_');

// mark to profiler
_rb_cms_profiler_mark('RB-Framework-After-Load');