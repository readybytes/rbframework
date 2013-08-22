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

// load filetree, will help in reducing filesystem IO
require_once RB_PATH_FRAMEWORK.'/filetree.php'; 

// load the loader
require_once RB_PATH_FRAMEWORK.'/loader.php'	;
require_once dirname(__FILE__).'/helper.php'	;

// adding JRegistryFormatRb_INI formatter
// require_once RB_PATH_INCLUDES.'/ini.php'	;


//autoload core library
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_FRAMEWORK.'/rb',		'');
// adapt common code as per CMS
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_FRAMEWORK.'/adapt/'.RB_CMS_ADAPTER,	'Adapt');
// use legacy code for legacy CMS
Rb_HelperLoader::addAutoLoadFolder(RB_PATH_FRAMEWORK.'/legacy/'.RB_CMS_ADAPTER, ''	, 'J');

// mark to profiler
_rb_cms_profiler_mark('RB-Framework-After-Load');