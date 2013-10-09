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
 * Tax Rule Base Class
 * 
 * These tax can be included in price shown, and in items. Will be shown seperatly in total
 * 
 * @author shyam
 */
abstract class Rb_EcommerceTaxRule 
{
	// Id for rule
	protected $id;
	protected $title;
	protected $tax_rate;
	
	// tax message to be shown in invoice
	protected $tax_reference;
	
	// rule processor name e.g. eu-vat
	protected $rule_processor_type;
	
	// configuration for rule processor
	protected $rule_processor_config;
	
	// applicable on : item / cart / tax_amount / shipping amount
	protected $applicable_entity;
	
	// applicable amount :
	protected $applicable_amount;
		// Price
		// Price-Discount
		// Price-Discount + Shipping
		// Price + Shipping
	
	// address based selection
	protected $applicable_address;
	
	// group based selection
	protected $applicable_usergroup;
}