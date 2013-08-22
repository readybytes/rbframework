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

/** 
 * Request Base Class
 * @author Gaurav Jain
 */
class Rb_EcommerceRequest extends Rb_Registry
{
	/**
	* Constructor
	*
	* @param mixed $data The data to bind to the new RB_Registry object.
	*
	* @since 11.1
	*/
	public function __construct($data = null)
	{
		// Construct JRegistry
		parent::__construct($data);
		
//		// Set default data
//		$this->data->regular_price 	= 0;
//		$this->data->trial_prices 	= array();
//		$this->data->trial_times		= array();
//		$this->data->is_recurring 	= false;
//		$this->data->item_name		= '';		
//		$this->data->currency 		= 'USD'; //XITODO : get default currency from configuration
	}
	
	public function set($path, $value)
	{
		parent::set($path, $value);		
		return $this;
	}
}
