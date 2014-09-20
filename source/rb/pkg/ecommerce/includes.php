<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// if Rb_Ecommerce already loaded, then do not load it again
if(defined('RB_ECOMMERCE_CORE_LOADED')){
	return;
}

define('RB_ECOMMERCE_CORE_LOADED', true);

// include defines
include_once dirname(__FILE__).'/defines.php';

// include the event file so that events can be registered
require_once RB_ECOMMERCE_PATH_CORE.'/base/event.php';
require_once 'api.php';