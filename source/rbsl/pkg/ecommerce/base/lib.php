<?php

/**
 * @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @package 	RbEcommerce
 * @subpackage	Front-end
 * @contact		team@readybytes.in
 */

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Base Lib
 * @author Gaurav Jain
 */
class RbEcommerceLib extends Rb_Lib
{
	public	$_component	= RBECOMMERCE_COMPONENT_NAME;

	static public function getInstance($name, $id=0, $data=null, $dummy = null)
	{
		return parent::getInstance(RBECOMMERCE_COMPONENT_NAME, $name, $id, $data);
	}
	
	public function getHelper()
	{
		$helper = RbEcommerceFactory::getHelper();
		$name   = $this->getName();
		return isset($helper->$name) ? $helper->$name : false;  // assert if helper not found
	}
}
