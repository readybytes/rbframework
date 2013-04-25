<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RbEcommerce
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

// if RbEcommerce already loaded, then do not load it again
if(defined('RBECOMMERCE_CORE_LOADED')){
	return;
}

define('RBECOMMERCE_CORE_LOADED', true);

// include defines
include_once dirname(__FILE__).'/defines.php';

//load core
Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/base',		     '',		 'RbEcommerce');

Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/models',		'Model',	 'RbEcommerce');
Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/models',		'Modelform', 'RbEcommerce');

Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/tables',		'Table',	 'RbEcommerce');
Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/libs',			'',			 'RbEcommerce');
Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/helpers',		'Helper',	 'RbEcommerce');
Rb_HelperLoader::addAutoLoadFolder(RBECOMMERCE_PATH_CORE.'/payment',		'',	 		 'RbEcommerce');

// include the event file so that events can be registered
require_once RBECOMMERCE_PATH_CORE.'/base/event.php';