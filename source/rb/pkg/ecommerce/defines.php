<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// If file is already included
if(defined('RB_ECOMMERCE_LOADED')){
	return;
}

//mark core loaded
define('RB_ECOMMERCE_LOADED', true);
define('RB_ECOMMERCE_COMPONENT_NAME','rb_ecommerce');


// define versions
define('RB_ECOMMERCE_VERSION', '0.0.1');
define('RB_ECOMMERCE_REVISION','v0.0.1-5-gcdee801');

//shared paths
define('RB_ECOMMERCE_PATH_CORE', dirname(__FILE__));

// Expiration Types
define('RB_ECOMMERCE_EXPIRATION_TYPE_FIXED', 	'fixed');
define('RB_ECOMMERCE_EXPIRATION_TYPE_FOREVER', 	'forever');
define('RB_ECOMMERCE_EXPIRATION_TYPE_RECURRING','recurring');

// object to identify extension, create once, so same can be consumed by constructors
Rb_Extension::getInstance(RB_ECOMMERCE_COMPONENT_NAME, array('prefix_css'=>'rb_ecommerce'));
