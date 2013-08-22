<?php

/**
 * @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @package 	Rb_Ecommerce
 * @subpackage	Front-end
 * @contact		team@readybytes.in
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/** 
 * Base Lib
 * @author Gaurav Jain
 */
class Rb_EcommerceLib extends Rb_Lib
{
	public	$_component	= RB_ECOMMERCE_COMPONENT_NAME;

	static public function getInstance($name, $id=0, $data=null, $dummy = null)
	{
		return parent::getInstance(RB_ECOMMERCE_COMPONENT_NAME, $name, $id, $data);
	}
	
	public function getHelper()
	{
		$helper = Rb_EcommerceFactory::getHelper();
		$name   = $this->getName();
		return isset($helper->$name) ? $helper->$name : false;  // assert if helper not found
	}
}
