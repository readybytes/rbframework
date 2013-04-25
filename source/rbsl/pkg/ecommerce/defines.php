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

// If file is already included
if(defined('RBECOMMERCE_LOADED')){
	return;
}

//mark core loaded
define('RBECOMMERCE_LOADED', true);
define('RBECOMMERCE_COMPONENT_NAME','rbecommerce');


// define versions
define('RBECOMMERCE_VERSION', '0.0.1');
define('RBECOMMERCE_REVISION','v0.0.1-5-gcdee801');

//shared paths
define('RBECOMMERCE_PATH_CORE', dirname(__FILE__).'/rbecommerce');

// Expiration Types
define('RBECOMMERCE_EXPIRATION_TYPE_FIXED', 	'fixed');
define('RBECOMMERCE_EXPIRATION_TYPE_FOREVER', 	'forever');
define('RBECOMMERCE_EXPIRATION_TYPE_RECURRING','recurring');

// object to identify extension, create once, so same can be consumed by constructors
Rb_Extension::getInstance(RBECOMMERCE_COMPONENT_NAME, array('prefix_css'=>'rbecommerce'));
