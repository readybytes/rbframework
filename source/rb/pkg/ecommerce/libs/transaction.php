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
 * Transation Lib
 * @author Gaurav Jain
 */
class Rb_EcommerceTransaction extends Rb_EcommerceLib
{	
	protected $transaction_id		= 0;
	protected $invoice_id			= 0;	
	protected $buyer_id				= 0;
	protected $processor_type		= '';
	protected $gateway_txn_id		= 0;
	protected $gateway_parent_txn	= 0;
	protected $gateway_subscr_id	= 0;
	protected $amount				= 0.00;
	protected $payment_status		= '';
	protected $message				= '';
	protected $created_date			= null;
	protected $params				= '';
	protected $signature				= '';
	
	
	/**
	 * Gets the instance of Rb_EcommercePaymentmethod
	 * 
	 * @param  integer  $id    		Unique identifier of input entity
	 * @param  string   $type  		
	 * @param  mixed    $data  		Data to be binded with the object
	 * @param  mixed	$dummy		Dummy arg, if its not here then PHP will give warning (while development mode is on)
	 * 
	 * @return Object Rb_EcommercePaymentmethod  Instance of Rb_EcommerceTransaction
	 */	
	public static function getInstance($id = 0, $data = null, $dummy = null)
	{
		return parent::getInstance('transaction', $id, $data);
	}
	
	/**
	 * Reset all the properties  of  curent object to their default values
	 * 
	 * @return  Object Rb_EcommerceTransaction Instance of Rb_EcommerceTransaction
	 */
	public function reset()
	{
		$this->transaction_id		= 0;
		$this->invoice_id			= 0;
		$this->buyer_id				= 0;
		$this->processor_type		= 0;
		$this->gateway_txn_id		= 0;
		$this->gateway_parent_txn	= 0;
		$this->gateway_subscr_id	= 0;
		$this->amount				= 0.00;
		$this->payment_status		= '';
		$this->message				= '';
		$this->created_date			= null;
		$this->params				= new Rb_Registry();
		$this->signature			= '';
		return $this;
	}
	
	public function setParams($params)
	{
		$this->params->bind($params);
		return $this;
	}
	
	public function getInvoice($instance_require = false)
	{
		if($instance_require === true){
			return Rb_EcommerceInvoice::getInstance($this->invoice_id);
		}
		
		return $this->invoice_id;
	}
	
	public function getPaymentStatus()
	{
		return strtolower($this->payment_status);
	}
	
	public function getGatewayTxnId()
	{
		return $this->gateway_txn_id;
	}
}