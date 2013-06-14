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
 * Base Model Form
 * @author Gaurav Jain
 */
class Rb_EcommerceModelform extends Rb_Modelform
{
	public	$_component			= RB_ECOMMERCE_COMPONENT_NAME;
	
	protected $_forms_path 		= RB_ECOMMERCE_PATH_CORE_FORMS;
	protected $_fields_path 	= RB_ECOMMERCE_PATH_CORE_FIELDS;
}
