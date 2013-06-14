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
 * Modifier Lib
 * @author Gaurav Jain
 */
class Rb_EcommerceModifier extends Rb_EcommerceLib
{
	protected $modifier_id 		= 0;	
	protected $invoice_id 		= 0;
	protected $buyer_id 		= 0;
	protected $amount	 		= 0.00;
	protected $value 			= 0.00;
	protected $object_type		= '';
	protected $object_id		= '';
	protected $message 			= '';
	protected $percentage		= true;
	protected $serial 			= '';
	protected $frequency		= '';	
	protected $created_date 	= null;
	protected $consumed_date 	= null;
	
	/**
	 * Before Discount Modifier means any addition or substraction
	 * which should be applied before discount and tax are being applied
	 *
	 * FIXED amount will be applied before PERCENTAGE amount
	 * @var constant int
	 */
	const FIXED_BEFORE_DISCOUNT		= 10;
	const PERCENT_BEFORE_DISCOUNT	= 15;
	
	/**
	 * Discount Modifier means discount on order/invocie	 
	 *
	 * FIXED discount will be applied before PERCENTAGE discount
	 * @var constant int
	 */
	const FIXED_DISCOUNT		= 20;
	const PERCENT_DISCOUNT		= 25;
	
	/**
	 * After Discount Before Tax Modifier means any addition or substraction
	 * which should be applied aftrer applying discount and before applying tax
	 *
	 * FIXED amount will be applied before PERCENTAGE amount
	 * @var constant int
	 */
	const FIXED_AFTER_DSCOUNT_BEFORE_TAX	= 30;
	const PERCENT_AFTER_DSCOUNT_BEFORE_TAX	= 35;
	
	/**
	 * Tax Modifier means tax on order/invocie
	 * which should be applied after Discount modifier
	 *
	 * FIXED tax will be applied before PERCENTAGE tax
	 * @var constant int
	 */
	const FIXED_TAX				= 40;
	const PERCENT_TAX			= 45;
	
	/**
	 * AFTER TAX Modifier means any addition or substraction
	 * which should be applied after applying discount and tax
	 *
	 * FIXED amount will be applied before PERCENTAGE amount
	 * @var constant int
	 */
	const FIXED_AFTER_TAX 	= 50;
	const PERCENT_AFTER_TAX	= 55;
	
	/**
	 * Constants for frequency of modifire on invoice
	 */
	const FREQUENCY_ONLY_THIS 		= 'ONLY_THIS';
	const FREQUENCY_THIS_AND_LATER 	= 'THIS_AND_LATER';
	
	/**
	 * Gets the instance of Rb_EcommerceModifier
	 * 
	 * @param  integer  $id    		Unique identifier of input entity
	 * @param  string   $type  		
	 * @param  mixed    $data  		Data to be binded with the object
	 * @param  mixed	$dummy		Dummy arg, if its not here then PHP will give warning (while development mode is on)
	 * 
	 * @return Object Rb_EcommerceModifier  Instance of Rb_EcommerceModifier
	 */	
	public static function getInstance($id = 0, $data = null, $dummy = null)
	{
		return parent::getInstance('modifier', $id, $data);
	}
	
	public static function getSerialList()
	{
		return array(	Rb_EcommerceModifier::FIXED_BEFORE_DISCOUNT 			=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_FIXED_BEFORE_DISCOUNT'),
						Rb_EcommerceModifier::PERCENT_BEFORE_DISCOUNT 			=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_PERCENT_BEFORE_DISCOUNT'),
						Rb_EcommerceModifier::FIXED_DISCOUNT 					=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::PERCENT_DISCOUNT				 	=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::FIXED_AFTER_DSCOUNT_BEFORE_TAX 	=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::PERCENT_AFTER_DSCOUNT_BEFORE_TAX 	=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::FIXED_TAX 						=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::PERCENT_TAX 						=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::FIXED_AFTER_TAX 					=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_'),
						Rb_EcommerceModifier::PERCENT_AFTER_TAX 				=> Rb_Text::_('COM_RB_ECOMMERCE_MODIFIER_SERIAL_')
					);
	}
	
	/**
	 * Reset all the properties  of  curent object to their default values
	 * 
	 * @return  Object Rb_EcommercePaymentmethod Instance of Rb_EcommercePaymentmethod
	 */
	public function reset()
	{
		$this->modifier_id 		= 0;	
		$this->invoice_id 		= 0;
		$this->buyer_id 		= 0;
		$this->amount	 		= 0.00;
		$this->value 			= 0.00;
		$this->object_type		= '';
		$this->object_id		= '';
		$this->message 			= 0.00;
		$this->percentage		= 0.00;
		$this->serial 			= 0.00;
		$this->frequency		= 0.00;	
		$this->created_date 	= new Rb_Date();
		$this->consumed_date 	= null;
	
		return $this;
	} 
	
	public function getInvoice($requireinstance = false)
	{
		if($requireinstance == PAYPLANS_INSTANCE_REQUIRE){
			return PayplansInvoice::getInstance($this->invoice_id);
		}
		
		return $this->invoice_id;
	}
	
	public function getSerial()
	{
		return $this->serial;
	}
	
	public function getAmount()
	{
		return $this->amount;
	}
	
	public function isPercentage()
	{
		return $this->percentage;
	}
	
	public function getFrequency()
	{
		return $this->frequency;
	}
}