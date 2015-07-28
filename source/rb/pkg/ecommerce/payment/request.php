<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		Rb_Ecommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(RB_CMS_ECOM_ADAPTER==='j33'){
	class Rb_EcommerceRequest extends Rb_EcommerceAdaptJ33Request
	{}
}

if(RB_CMS_ECOM_ADAPTER==='j35'){
	class Rb_EcommerceRequest extends Rb_EcommerceAdaptJ35Request
	{}
}